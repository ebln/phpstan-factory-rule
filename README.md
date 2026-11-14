ebln/phpstan-factory-rule
=========================

Enforce that your classes get only instantiated by the factories you define!

## Installation

Require this extension via [Composer](https://getcomposer.org/):

```shell
composer require --dev ebln/phpstan-factory-rule
```

If you also use [phpstan/extension-installer](https://github.com/phpstan/extension-installer), no further setup is needed.
Otherwise include the rules in your project's PHPStan config:

```neon
includes:
    - vendor/ebln/phpstan-factory-rule/rules.neon
```

Requires PHP 8.2+ and PHPStan 2.2.13+.

## Usage

Add the `ForceFactory` attribute to your class and list the classes that are allowed to instantiate it:

```php
<?php
// […]

#[\Ebln\Attrib\ForceFactory(GoodFactory::class, BetterFactory::class)]
class OnlyViaFactory
{
}
```

PHPStan then checks every instantiation, in CI pipelines, git hooks or IDE integrations.
If somebody introduces a rogue factory:

```php
<?php
// […]

class FailingFactory
{
    public function create(): OnlyViaFactory
    {
        return new OnlyViaFactory();
    }
}
```

…PHPStan reports it:

```
OnlyViaFactory must be instantiated by GoodFactory or BetterFactory.
💡 Move this instantiation into GoodFactory or BetterFactory, or add FailingFactory to the #[ForceFactory] attribute of OnlyViaFactory.
```

## Rules

Every error carries an identifier, so you can silence a single kind in `phpstan.neon`:

```neon
parameters:
    ignoreErrors:
        - identifier: ebln.forceFactory.outsideFactory
```

| Identifier | Reported when |
|---|---|
| `ebln.forceFactory.outsideFactory` | a marked class is instantiated outside the classes its attribute lists |
| `ebln.forceFactory.reflectionOutsideFactory` | a marked class is created through `ReflectionClass` outside the classes its attribute lists: `newInstance()`, `newInstanceArgs()`, `newInstanceWithoutConstructor()`, `newLazyGhost()` or `newLazyProxy()` |
| `ebln.forceFactory.noFactories` | the attribute lists no factory, or none that PHPStan can resolve to an existing class; reported on the attribute, at every `new` of the class and at every reflection call that creates it |
| `ebln.forceFactory.unknownFactory` | the attribute names a class that does not exist (one error per name) |
| `ebln.forceFactory.invalidFactory` | the attribute names an interface or a trait as a factory |
| `ebln.forceFactory.namedArgument` | the attribute passes a named argument; the rule reads positional arguments only (one error per name) |
| `ebln.forceFactory.invalidTarget` | the attribute sits on an interface, trait or enum |
| `ebln.forceFactory.factoryNotCovariant` | a marked subclass lists a factory that is neither a factory of its nearest marked ancestor nor a subclass of one |
| `ebln.forceFactory.legacyInterface` | a class still implements the removed `Ebln\PHPStan\EnforceFactory\ForceFactoryInterface` |

The reflection rule has its own identifier. A project can therefore ignore reflection in its tests and still
have `new` checked there:

```neon
parameters:
    ignoreErrors:
        -
            identifier: ebln.forceFactory.reflectionOutsideFactory
            path: tests/*
```

## Semantics

- **Nearest attribute wins.** A class inherits the attribute of its closest marked ancestor. A marked child of a marked parent is governed by its own list only, so the parent's factory may not create the child unless the child lists it.
- **Factories narrow with the product.** An instance of a subclass is also an instance of the parent, so the parent's promise covers it. A marked subclass may therefore only list factories of its nearest marked ancestor or subclasses of them; anything else is reported by the definition rule. Declaring the parent's factories `final` closes the set: no subclass of a factory can exist, so only the listed factories remain.
- **Closures count as inside.** A closure or arrow function inside a listed factory may instantiate the product. An anonymous class inside a factory may not, and neither may a subclass of a factory: list the subclass explicitly.
- **Reflection follows the same rules as `new`.** `ReflectionClass::newInstance()`, `newInstanceArgs()`, `newInstanceWithoutConstructor()`, `newLazyGhost()` and `newLazyProxy()` create an instance, so they are checked like `new`, with the same nearest attribute, closure handling and tip. The rule reads the class from the caller's type `ReflectionClass<T>`; `ReflectionObject` works too. The case of the method name does not matter. A nullsafe call is checked too. A first-class callable such as `$reflection->newInstance(...)` is reported where the Closure is created.
- **A factory is a class.** The attribute takes class name constants of classes (or enums). An interface or a trait in the list is reported by the definition rule, because no `new` ever runs in their scope: naming an interface does not allow its implementations.
- **Factory names are canonicalised.** Casing and a leading backslash do not matter. A name that does not resolve to a class is dropped and reported by the definition rule when PHPStan analyses the marked class's own file.
- **A broken list fails closed.** When no name is left, nothing may create the class: every `new` is reported with `noFactories` until the attribute is fixed. A mistyped list in a path PHPStan does not analyse, such as `vendor/`, therefore still shows up where the class is used.
- **Unknown classes are skipped.** PHPStan itself reports `new Missing()`. This rule skips it, and the rest of the file is still checked.

### Known loopholes

The rule follows PHPStan's type inference for `new $name`. Object-typed and `class-string<T>` typed variables are
checked, but a plain `string` variable is not, because its type does not say which class it holds. A union of a class
name constant with a plain string is absorbed into `string` by PHPStan and is not checked either.

Reflection is checked only when the class is known. `ReflectionClass<object>` and `new ReflectionClass($name)` with
a plain `string` name are not checked, for the same reason as `new $name`.
A caller whose type is a union with `ReflectionClass<object>`, or with a type that is not a `ReflectionClass` such as
`false`, is not checked either. PHPStan merges `ReflectionClass<A>|ReflectionClass<object>` into
`ReflectionClass<object>`, in the same way as it absorbs a union of a class name constant and a plain string.

Reflection calls are checked only when the code names the method directly, as in `$reflection->newInstance()`. `$reflection->$method()`,
`call_user_func([$reflection, 'newInstance'])` and the later call of a Closure from `$reflection->newInstance(...)`
are not checked.

Some ways to create an instance are not checked: `unserialize()`, `clone`, and libraries that create the instance
inside their own code, such as instantiators.

`resetAsLazyGhost()` and `resetAsLazyProxy()` are not reported. They change an object that already exists, and that
object was created at a place the rules check.

The declared type also decides which attribute applies. `new $product` with `$product` typed as a marked parent
class is checked against the parent's list, even when the object at runtime is a marked subclass with a list of
its own. This applies to reflection too: `new ReflectionClass($base)` with `$base` typed as a marked parent class is
checked against the parent's list.

`new static()` follows the same rule. In an unmarked base class it is typed as the base class. A marked subclass
that inherits a static constructor such as `public static function create(): static` can therefore be created
anywhere through it. Marking the base class closes this: the `new static()` is then checked against the base
class's list.

### Why `--dev` is enough

PHP does not load an attribute's class unless something reflects on it, so the attribute class may come from a
`require-dev` package. The `Ebln\Attrib\ForceFactory` class ships in this package; nothing else is needed at runtime.

## Upgrading from 2.x

- Register the rules through `rules.neon` (see Installation). A manual `rules:` entry naming
  `ForceFactoryRule`, the 2.x way without extension-installer, now fails at container build with
  "Service of type Ebln\PHPStan\EnforceFactory\AllowedFactoriesResolver … not found", and it never
  registers the definition rule.
- The marker interface is gone. Remove `ebln/phpstan-factory-mark` from your project: this package declares a
  composer conflict with every version of it, and `ebln.forceFactory.legacyInterface` reports every class that
  still implements the interface.
- Attribute arguments are unchanged: positional class name constants. The rule reads only those. A named argument
  such as `factory: MyFactory::class` is reported by `ebln.forceFactory.namedArgument`: PHP keeps it in `$factories`
  at runtime, but PHPStan drops it, and the factory it names would not be allowed.
- `ForceFactory::getAllowedFactories()` is replaced by the public readonly property `$factories`.
- Identifiers: `ebln.forceFactory.outOfFactoryInstanciation` became `ebln.forceFactory.outsideFactory`;
  `ebln.forceFactory.bogusDefinition` was split into the definition rule's identifiers above.
- Inheritance: 2.x merged the attributes of the whole parent chain; 3.0 uses the nearest attribute only.
- Floors: PHP 8.2 and PHPStan 2.2.13.
