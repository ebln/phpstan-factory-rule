Changelog
=========

Intended to follow [«Keep a Changelog»](https://keepachangelog.com/en/)

----

## [3.0.0]

### Added
- `ForceFactoryDefinitionRule`, reporting attributes the instantiation rule cannot act on: `ebln.forceFactory.noFactories`, `ebln.forceFactory.unknownFactory`, `ebln.forceFactory.invalidTarget`, `ebln.forceFactory.factoryNotCovariant` for a marked subclass whose factories are not subclasses of its marked ancestor's, and `ebln.forceFactory.legacyInterface` for classes still implementing the removed marker interface
- Error tips telling where to move the instantiation or which class to add to the attribute

### Changed
- Nearest attribute wins: a class is governed by the attribute of its closest marked ancestor instead of the merged lists of the whole parent chain; a marked subclass may only narrow its ancestor's factories (same classes or subclasses of them)
- Identifier `ebln.forceFactory.outsideFactory` replaces `ebln.forceFactory.outOfFactoryInstanciation`
- Factory names are canonicalised (casing, leading backslash) before comparison; a name that is not a class is dropped, and a list with nothing left forbids every instantiation (`noFactories` at each `new`) instead of being ignored
- The attribute is read through PHPStan's reflection; it is never instantiated during analysis
- Composer conflict with every version of `ebln/phpstan-factory-mark`
- The rules are registered through `rules.neon` (or extension-installer); a manual `rules:` entry naming `ForceFactoryRule` no longer works
- Toolchain: PHPUnit 11, phpmd 3, php-cs-fixer 3.95; Psalm removed

### Removed
- Support for the marker interface `Ebln\PHPStan\EnforceFactory\ForceFactoryInterface`
- Dependency on `ebln/phpstan-factory-mark`
- Support for PHP < 8.2 and PHPStan < 2.2
- `ForceFactory::getAllowedFactories()`, replaced by the public readonly property `$factories`
- Identifier `ebln.forceFactory.bogusDefinition`
- The modified copy of PHPStan's `InstantiationRule::getClassNames` and its license notice; the rule reads the expression type through PHPStan's public API instead

### Fixed
- An unknown class in a `new` expression no longer aborts the analysis of the whole file
- A class reachable through several constant strings is reported once per `new`, not once per string
- `new $var` with `$var` typed as the marked class or as `class-string<MarkedClass>` is now checked

----

## [2.0.0]

### Addded
- Support for PHPStan 2
### Removed
- Dropped support for PHPStan < 2

## [1.0.1]

### Fixed
- Fix class name resolution for self & static

## [1.0.0]

### Added
- Support for attributes

### Removed
* Support for PHP < 7.4
* Support for PHPStan < 1.11

## [0.0.2]

### Added

- Supporting PHP upto 8.3

### Removed

- Support for PHP < 7.4

## [0.0.1]

### Added

- Rule to enforce Factory pattern via interface
