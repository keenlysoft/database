# Contributing to Keenly Database

Thank you for helping improve Keenly Database.

## Development Setup

```bash
git clone https://github.com/keenlysoft/database.git
cd database
composer install
composer test
```

## Pull Requests

- Keep changes focused and backward compatible where practical.
- Add or update tests for behavior changes.
- Run `composer validate --strict --no-check-publish`, PHP lint, and `composer test`.
- Document user-facing changes in `CHANGELOG.md`.
- Do not commit credentials, local configuration, `vendor/`, or `composer.lock`.

## Security Reports

Do not open public issues for suspected vulnerabilities. Follow [SECURITY.md](SECURITY.md).
