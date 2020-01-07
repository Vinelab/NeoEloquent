# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## [Unreleased]
_In progress…_

## [1.3.0] - 2019-03-14
### Added
- Support string casting for resources

## [1.2.1] - 2018-08-28
### Fixed
- Heavy socket payloads are no longer unreadable in slow environments
- Fix an issue where the console object can't be set in some environments

## [1.2.0] - 2018-08-20
### Added
- Add a `log_node_console` option to log the output of console methods (`console.log`, `console.debug`, `console.table`, etc…) to the PHP logger

### Changed
- Drastically improve the log contents

### Fixed
- Fix a bug where some standard streams logs were missing
- Fix double declarations of some JS classes due to a require with two different paths
- Fix a bug where sending `null` values was crashing the Node process

## [1.1.0] - 2018-07-20
### Added
- Support passing Node resources in JS functions
- Add chaining methods to the `JsFunction` class
- Add an `async()` method to the `JsFunction` class to allow developers to write `await` instructions in their JS code
- The `idle_timeout` and `read_timeout` options can be disabled by setting them to `null`

### Deprecated
- Deprecate the `JsFunction::create` method in favor of the new chaining methods

## [1.0.2] - 2018-06-18
### Fixed
- Fix an issue where the socket port couldn't be retrieved

## [1.0.1] - 2018-06-12
### Fixed
- Fix `false` values being parsed as `null` by the unserializer
- Fix Travis tests

## [1.0.0] - 2018-06-05
### Changed
- Change PHP's vendor name from `extractr-io` to `nesk`
- Change NPM's scope name from `@extractr-io` to `@nesk`

## [0.1.2] - 2018-04-09
### Added
- Support PHPUnit v7
- Add Travis integration

### Changed
- Improve the conditions to throw `ReadSocketTimeoutException`

### Fixed
- Support heavy socket payloads containing non-ASCII characters

## [0.1.1] - 2018-01-29
### Fixed
- Fix an issue on an internal type check

## 0.1.0 - 2018-01-29
First release


[Unreleased]: https://github.com/nesk/rialto/compare/1.3.0...HEAD
[1.3.0]: https://github.com/nesk/rialto/compare/1.2.1...1.3.0
[1.2.1]: https://github.com/nesk/rialto/compare/1.2.0...1.2.1
[1.2.0]: https://github.com/nesk/rialto/compare/1.1.0...1.2.0
[1.1.0]: https://github.com/nesk/rialto/compare/1.0.2...1.1.0
[1.0.2]: https://github.com/nesk/rialto/compare/1.0.1...1.0.2
[1.0.1]: https://github.com/nesk/rialto/compare/1.0.0...1.0.1
[1.0.0]: https://github.com/nesk/rialto/compare/0.1.2...1.0.0
[0.1.2]: https://github.com/nesk/rialto/compare/0.1.1...0.1.2
[0.1.1]: https://github.com/nesk/rialto/compare/0.1.0...0.1.1
