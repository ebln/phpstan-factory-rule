ebln/phpstan-factory-rule
=========================

Enforce that your classes get only instantiated by the factories you define!

## Installation

Require this extension via [Composer](https://getcomposer.org/):

```shell
composer require --dev ebln/phpstan-factory-rule
```

If you also use [phpstan/extension-installer](https://github.com/phpstan/extension-installer) you are all set.
Otherwise include the rules in your project's PHPStan config:

```neon
includes:
    - vendor/ebln/phpstan-factory-rule/rules.neon
```

Requires PHP 8.2+ and PHPStan 2.2+.

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

Now lean back and rely on PHPStan in CI pipelines, git hooks or IDE integrations.
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
| `ebln.forceFactory.noFactories` | the attribute lists no factory, or none that PHPStan can resolve to an existing class; reported on the attribute and at every instantiation of the class |
| `ebln.forceFactory.unknownFactory` | the attribute names a class that does not exist (one error per name) |
| `ebln.forceFactory.invalidTarget` | the attribute sits on an interface, trait or enum |
| `ebln.forceFactory.factoryNotCovariant` | a marked subclass lists a factory that is neither a factory of its nearest marked ancestor nor a subclass of one |
| `ebln.forceFactory.legacyInterface` | a class still implements the removed `Ebln\PHPStan\EnforceFactory\ForceFactoryInterface` |

## Semantics

- **Nearest attribute wins.** A class inherits the attribute of its closest marked ancestor. A marked child of a marked parent is governed by its own list only, so the parent's factory may not create the child unless the child lists it.
- **Factories narrow with the product.** An instance of a subclass is also an instance of the parent, so the parent's promise covers it. A marked subclass may therefore only list factories of its nearest marked ancestor or subclasses of them; anything else is reported by the definition rule. Declaring the parent's factories `final` closes the set: no subclass of a factory can exist, so only the listed factories remain.
- **Closures count as inside.** A closure or arrow function inside a listed factory may instantiate the product. An anonymous class inside a factory may not, and neither may a subclass of a factory: list the subclass explicitly.
- **A factory is a class.** The attribute takes class name constants of classes (or enums). Naming an interface does not allow its implementations; the instantiation inside them is reported.
- **Factory names are canonicalised.** Casing and a leading backslash do not matter. A name that does not resolve to a class is dropped and reported by the definition rule when PHPStan analyses the marked class's own file.
- **A broken list fails closed.** When no name is left, nothing may create the class: every `new` is reported with `noFactories` until the attribute is fixed. A mistyped list in a path PHPStan does not analyse, such as `vendor/`, therefore still shows up where the class is used.
- **Unknown classes are skipped.** `new Missing()` is PHPStan's own business and never prevents the rest of the file from being checked.

### Known loophole

The rule follows PHPStan's type inference for `new $name`. Object-typed and `class-string<T>` typed variables are
checked, but a plain `string` variable is not, because its type does not say which class it holds. A union of a class
name constant with a plain string is absorbed into `string` by PHPStan and is not checked either.

The declared type also decides which attribute applies. `new $product` with `$product` typed as a marked parent
class is checked against the parent's list, even when the object at runtime is a marked subclass with a list of
its own.

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
- Attribute arguments are unchanged: positional class name constants. The rule reads only those. An argument it
  cannot resolve, including a named one such as `factory: MyFactory::class`, counts as no factory and is reported
  by `ebln.forceFactory.noFactories`, although PHP keeps it in `$factories` at runtime.
- `ForceFactory::getAllowedFactories()` is replaced by the public readonly property `$factories`.
- Identifiers: `ebln.forceFactory.outOfFactoryInstanciation` became `ebln.forceFactory.outsideFactory`;
  `ebln.forceFactory.bogusDefinition` was split into the definition rule's identifiers above.
- Inheritance: 2.x merged the attributes of the whole parent chain; 3.0 uses the nearest attribute only.
- Floors: PHP 8.2 and PHPStan 2.2.
