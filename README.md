# PhpStanFrankenPhp

[![CI](https://github.com/nowo-tech/PhpStanFrankenPhp/actions/workflows/ci.yml/badge.svg)](https://github.com/nowo-tech/PhpStanFrankenPhp/actions/workflows/ci.yml) [![Packagist Version](https://img.shields.io/packagist/v/nowo-tech/phpstan-frankenphp.svg?style=flat)](https://packagist.org/packages/nowo-tech/phpstan-frankenphp) [![Packagist Downloads](https://img.shields.io/packagist/dt/nowo-tech/phpstan-frankenphp.svg)](https://packagist.org/packages/nowo-tech/phpstan-frankenphp) [![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE) [![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4?logo=php)](https://php.net) [![PHPStan](https://img.shields.io/badge/PHPStan-2.x-4F5D95?logo=php)](https://phpstan.org) [![GitHub stars](https://img.shields.io/github/stars/nowo-tech/phpstan-frankenphp.svg?style=social&label=Star)](https://github.com/nowo-tech/PhpStanFrankenPhp) [![Coverage](https://img.shields.io/badge/Coverage-100%25-brightgreen)](#tests-and-coverage)

> ⭐ **Found this useful?** Install from [Packagist](https://packagist.org/packages/nowo-tech/phpstan-frankenphp) and star the repository on [GitHub](https://github.com/nowo-tech/PhpStanFrankenPhp).

PHPStan rules that help you migrate from **PHP-FPM** to **FrankenPHP classic**, then to **worker** mode. Rules are split by level, documented with justification, and shipped with demos for every case.

![FrankenPHP Friendly Worker Mode](docs/images/frankenphp-friendly.png)

This bundle is **FrankenPHP worker mode friendly**.

## Installation

```bash
composer require --dev nowo-tech/phpstan-frankenphp
```

Enable levels intentionally (rules are **not** auto-enabled by `extension.neon`):

```neon
# phpstan.neon
includes:
    - vendor/nowo-tech/phpstan-frankenphp/ruleset-classic.neon
    # - vendor/nowo-tech/phpstan-frankenphp/ruleset-worker.neon
    # - vendor/nowo-tech/phpstan-frankenphp/ruleset-worker-strict.neon
    # - vendor/nowo-tech/phpstan-frankenphp/ruleset-hardening.neon
```

See [docs/CONFIGURATION.md](docs/CONFIGURATION.md) and [docs/RULES.md](docs/RULES.md).

## Quick example

```php
// Flagged by classic rules:
exit(1);
fastcgi_finish_request();
$process->setTimeout(null);

// Flagged by worker rules:
private static array $cache = [];
static $count = 0;
$_ENV['TOKEN'] = $requestToken;

// Flagged by hardening rules:
set_time_limit(0);
sleep(5);
pcntl_fork();
```

## Why

FrankenPHP classic already changes process lifetime compared to FPM (`exit`/`die`, FastCGI APIs, unbounded I/O). Worker mode keeps the app in memory: statics, globals, and `$_ENV` mutations survive across requests. This extension surfaces those sites in CI so you can fix them in order.

**Fixes stay FPM-compatible.** The suggested remediations are portable patterns (no FrankenPHP-only APIs). They make the codebase more solid on FPM and safe for worker mode. Details: [docs/MIGRATION.md — FPM compatibility](docs/MIGRATION.md#fpm-compatibility-important).

## Levels (application order)

| Order | Ruleset | When |
|------:|---------|------|
| 1 | [`ruleset-classic.neon`](ruleset-classic.neon) | Moving FPM → FrankenPHP **classic** |
| 2 | [`ruleset-worker.neon`](ruleset-worker.neon) | Enabling FrankenPHP **worker** |
| 2b | [`ruleset-worker-strict.neon`](ruleset-worker-strict.neon) | Optional: also flag `$_GET`/`$_POST`/… |
| 3 | [`ruleset-hardening.neon`](ruleset-hardening.neon) | Production hardening (timeouts, fork, sleep) |

Full catalog: [docs/RULES.md](docs/RULES.md).

## Demos
### Symfony 8 + FrankenPHP

```bash
make -C demo/symfony8 up
# Demo started at: http://localhost:8090
make -C demo/symfony8 phpstan-classic   # expect findings on AntiPattern/
```

FrankenPHP worker mode: **supported** (tested in the Symfony 8 demo with `FRANKENPHP_MODE=worker` by default). See [docs/DEMO-FRANKENPHP.md](docs/DEMO-FRANKENPHP.md).

### Fixture demos

Intentional violations and clean counterparts under [`demo/`](demo/README.md):

```bash
composer demo-classic       # expect findings
composer demo-worker
composer demo-hardening
composer demo-classic-good  # must be clean
```

## Development

```bash
make setup-hooks
make ensure-up
make qa
make demo-all
```

## Documentation

- [Installation](docs/INSTALLATION.md)
- [Configuration](docs/CONFIGURATION.md)
- [Usage](docs/USAGE.md)
- [Contributing](docs/CONTRIBUTING.md)
- [Code of Conduct](CODE_OF_CONDUCT.md)
- [Changelog](docs/CHANGELOG.md)
- [Upgrading](docs/UPGRADING.md)
- [Release](docs/RELEASE.md)
- [Security](docs/SECURITY.md)
- [Engram](docs/ENGRAM.md)
- [Spec-driven development](docs/SPEC-DRIVEN-DEVELOPMENT.md)
- [GitHub Spec Kit](docs/SPEC-KIT.md)

### Additional documentation

- [Rule catalog](docs/RULES.md)
- [Migration guide (FPM → classic → worker)](docs/MIGRATION.md)
- [FrankenPHP demos](docs/DEMO-FRANKENPHP.md)
- [Demos](demo/README.md)
- [GitHub Actions CI requirements](docs/GITHUB_CI.md)
- [Branching strategy](docs/BRANCHING.md)

## Tests and coverage

```bash
make test
make test-coverage
```

- PHPUnit suites: `tests/Rule` (RuleTestCase per rule), `tests/Unit` (helpers / edge branches), `tests/Integration` (neon wiring).
- Line coverage on `src/`: **100%** (enforced via `composer coverage-check` / CI on PHP 8.2).

## License

MIT — see [LICENSE](LICENSE).
