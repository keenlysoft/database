# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

## [1.28.0] - 2026-06-01

### Added

- Add PHP 7.4 and PHP 8.x CI validation.
- Add a dependency-free smoke test for SQL generation and model array access.
- Add open source maintenance files and Dependabot configuration.

### Changed

- Update Composer metadata and declare support for PHP 7.4 through PHP 8.x.
- Avoid Redis authentication when no password is configured.
- Raise a runtime exception when a PDO connection cannot be established.

### Fixed

- Prevent repeated update SQL generation from accumulating stale fields.
- Fix non-prepared update SQL field detection.
- Fix model `ArrayAccess` reads and unsets.
- Avoid exposing SQL text when a PDO query fails.

[Unreleased]: https://github.com/keenlysoft/database/compare/1.28.0...master
[1.28.0]: https://github.com/keenlysoft/database/compare/1.27...1.28.0
