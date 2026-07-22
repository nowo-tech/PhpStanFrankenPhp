# Installation

## Requirements

- PHP `>= 8.1 < 8.6`
- PHPStan `^2.0`

## Install

```bash
composer require --dev nowo-tech/phpstan-frankenphp
```

If you use [`phpstan/extension-installer`](https://github.com/phpstan/extension-installer), `extension.neon` is registered automatically. **No rules are enabled by default** — include a ruleset explicitly (see [CONFIGURATION.md](CONFIGURATION.md)).

Without the extension installer, add:

```neon
includes:
    - vendor/nowo-tech/phpstan-frankenphp/extension.neon
    - vendor/nowo-tech/phpstan-frankenphp/ruleset-classic.neon
```

## Verify

```bash
vendor/bin/phpstan analyse
```

You should see FrankenPHP identifiers (`frankenphp.classic.*`, …) when violations exist.
