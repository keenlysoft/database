# Keenly Database

`keenlysoft/database` is the database and active record package for the [Keenly lightweight PHP framework](https://github.com/keenlysoft/keenly). It provides a small MySQL-oriented query layer, active record helpers, pagination, and an optional Redis wrapper.

## Features

- Active record models for common create, read, update, and delete operations
- Prepared query helpers through `pwhere()`
- Transaction helpers
- Pagination support
- Optional Redis connection wrapper
- PHP 7.4 and PHP 8.x compatibility baseline

## Requirements

- PHP 7.4 or later
- PDO and the PDO driver for your database
- The BCMath extension when using pagination
- The Redis extension when using the Redis wrapper
- `keenlysoft/keenly` when using framework configuration integration

## Installation

Install the package with Composer:

```bash
composer require keenlysoft/database
```

Applications created with [`keenlysoft/app`](https://github.com/keenlysoft/app) include this package automatically.

## Quick Start

Define an application model:

```php
<?php

namespace models;

class User extends \database\models
{
    public $table = 'users';
}
```

Find records:

```php
$user = User::find('*')->where(['id' => 1])->one();
$users = User::find(['id', 'name'])->pwhere(['status' => 'active'])->all();
```

Create, update, and delete records:

```php
$user = new User();
$user->name = 'Ada';
$user->save();

$user->Update(['name' => 'Grace'], ['id' => 1]);
$user->Delete(['id' => 1]);
```

Use transactions:

```php
$user = new User();
$user->begin();

try {
    // Perform database operations.
    $user->commit();
} catch (\Throwable $exception) {
    $user->back();
    throw $exception;
}
```

## Security Notes

Prefer prepared operations such as `pwhere()` when values originate from users or external systems. Methods that accept raw SQL fragments are intended for trusted application code only.

Store production database and Redis credentials outside version control. Review generated configuration before deployment.

## Testing

Run the local smoke test:

```bash
composer test
```

The smoke test does not require a live database server. It verifies SQL generation and model array access behavior.

## Roadmap

- Expand prepared-query coverage across the full active record API
- Add integration tests against a disposable MySQL service
- Review Redis extension compatibility and failure handling
- Add static analysis and coding-style checks

## Contributing

Contributions are welcome. Please read [CONTRIBUTING.md](CONTRIBUTING.md), follow the [Code of Conduct](CODE_OF_CONDUCT.md), and report security issues according to [SECURITY.md](SECURITY.md).

## License

Keenly Database is released under the [MIT License](LICENSE).
