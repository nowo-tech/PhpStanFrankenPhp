# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## Table of contents

- [[Unreleased]](#unreleased)
- [[1.0.1] - 2026-07-22](#101-2026-07-22)
- [[1.0.0] - 2026-07-22](#100-2026-07-22)

## [Unreleased]

## [1.0.1] - 2026-07-22

### Documentation

- Clarified that rule remediations remain **PHP-FPM compatible**: they harden the app for FrankenPHP classic/worker without requiring FrankenPHP-only APIs, and remain valid under FPM ([MIGRATION.md](MIGRATION.md), [RULES.md](RULES.md), [USAGE.md](USAGE.md), README).

## [1.0.0] - 2026-07-22

First stable release of `nowo-tech/phpstan-frankenphp`: PHPStan rules to migrate from PHP-FPM to FrankenPHP classic, then worker mode.

### Added

- **PHPStan extension** (`type: phpstan-extension`) with `extension.neon` (schema/defaults only; rules are not auto-enabled).
- **Level 1 — classic** (`ruleset-classic.neon`): `exit`/`die`, `fastcgi_finish_request`, `putenv`, `ignore_user_abort`, unlimited I/O timeouts.
- **Level 2 — worker** (`ruleset-worker.neon`): mutable statics, static locals, globals, `$_ENV`/`$_SESSION`, native session API, persistent `ini_set`, singletons, `register_shutdown_function`, `set_error_handler` / `set_exception_handler`.
- **Level 2b — worker-strict** (`ruleset-worker-strict.neon`) and parameter `frankenphp.flagRequestSuperglobals` (default `false`) to also flag `$_GET`/`$_POST`/….
- **Level 3 — hardening** (`ruleset-hardening.neon`): `set_time_limit(0)`, `memory_limit -1`, `pcntl_fork`, blocking `sleep`, `register_tick_function`.
- **Aggregate ruleset** `rules.neon` (classic + worker + hardening).
- **Fixture demos** under `demo/{classic,worker,hardening}/{bad,good}` with Composer/Make targets (`demo-*`, `demo-*-good`).
- **Symfony 8 + FrankenPHP demo** (`demo/symfony8`) with `FRANKENPHP_MODE=worker` by default, anti-patterns, and leveled PHPStan configs.
- **Documentation**: RULES, MIGRATION, INSTALLATION, CONFIGURATION, USAGE, DEMO-FRANKENPHP, RELEASE, Spec Kit baseline (`specs/001-baseline/`).
- **QA**: PHPUnit RuleTestCase + unit/integration suites, **100%** line coverage on `src/`, `coverage-check` / `test-coverage-100`, CI matrix PHP 8.2–8.5, `release-check` + `release-check-demos`.

### Changed

- `NoSuperglobalAccessRule` defaults to `$_ENV` + `$_SESSION` only (aligned with FrankenPHP worker reset behaviour); request superglobals are opt-in via worker-strict / `flagRequestSuperglobals`.
- Mutable static guidance no longer recommends invalid `readonly static` properties.

[Unreleased]: https://github.com/nowo-tech/PhpStanFrankenPhp/compare/v1.0.1...HEAD
[1.0.1]: https://github.com/nowo-tech/PhpStanFrankenPhp/releases/tag/v1.0.1
[1.0.0]: https://github.com/nowo-tech/PhpStanFrankenPhp/releases/tag/v1.0.0
