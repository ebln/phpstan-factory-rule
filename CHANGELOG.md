Changelog
=========

Intended to follow [«Keep a Changelog»](https://keepachangelog.com/en/)

----

## Upcomming

- Remove support for the interface
- Deprecate (abandon) the interface package
- create conflict with interface for version 2
	```json
	{
		"conflict": {
			"ebln/ebln/phpstan-factory-mark": "*"
		}
	}
	```

----

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
