ebln/phpstan-factory-rule
=========================

Enforce that your classes get only instantiated by the factories you define!

## Usage with support for attributes
Require this package: `composer require --dev ebln/phpstan-factory-rule`

Add the `ForceFactory` attribute to your class, and supply all class names as arguments,
which shall be allowed to instanciate your object.
```php
<?php
// […]

#[\Ebln\Attrib\ForceFactory(GoodFactory::class, BetterFactory::class)]
class OnlyViaFactory
{
}
```

Now lean back and rely on PHPStan in CI pipelines, git hooks or IDE integrations;
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
…that is supposed to fail, when you run PHPStan.

## Deprecated usage with `ebln/phpstan-factory-mark`

Require this extention and [the package containing the marking interface](https://github.com/ebln/phpstan-factory-mark) via [Composer](https://getcomposer.org/) alongside with PHPStan:
```shell
composer require ebln/phpstan-factory-mark
composer require --dev ebln/phpstan-factory-rule
```

Implement `\Ebln\PHPStan\EnforceFactory\ForceFactoryInterface` with the DTO you want to protect.
```php
<?php
// […]
use Ebln\PHPStan\EnforceFactory\ForceFactoryInterface;

class OnlyViaFactory implements ForceFactoryInterface
{
    // […]
    public static function getFactories(): array
    {   // Return a list of classes that are allowed to
        //   create new OnlyViaFactory instances…
        return [TheOnlyTrueFactory::class];
    }
}
```
Now rely on PHPStan in CI pipelines, git hooks or IDE integrations.

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
…that is supposed to fail, when you run PHPStan.

## Installation

Require this extention via [Composer](https://getcomposer.org/):

```php
composer require --dev ebln/phpstan-factory-rule
```

If you also install [phpstan/extension-installer](https://github.com/phpstan/extension-installer) then you're all set!

<details>
  <summary>Manual installation</summary>

If you don't want to use `phpstan/extension-installer`, just specify the rule in your project's PHPStan config:

```
rules:
    - \Ebln\PHPStan\EnforceFactory\ForceFactoryRule
```
</details>

