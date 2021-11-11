ebln/phpstan-factory-rule
=========================

Enforce that your classes get only instantiated by the factories you define!

## Usage

* Implement the interface
* Define allowed factories
* rely on PHPStan

## Installation

Require this extention and [the package containing the interface](https://github.com/ebln/phpstan-factory-mark) via [Composer](https://getcomposer.org/):

```
composer require ebln/phpstan-factory-mark && composer require --dev ebln/phpstan-factory-rule
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

