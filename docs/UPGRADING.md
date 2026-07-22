# Upgrading

## General process

1. Update the package:
   ```bash
   composer update nowo-tech/phpstan-frankenphp
   ```
2. Read [CHANGELOG.md](CHANGELOG.md) for the target version.
3. Re-run PHPStan with your chosen rulesets and the package demos if you maintain local forks of fixtures.

## Upgrading to 1.0.1

Documentation-only release. No rule behaviour changes and **no consumer action required**.

Clarifies that remediations remain valid under PHP-FPM (portable hygiene for FrankenPHP classic/worker). See [MIGRATION.md — FPM compatibility](MIGRATION.md#fpm-compatibility-important).

## Adopting 1.0.0 (first stable release)

There is no prior public `0.x` line. Treat this as a greenfield install.

### What you need to do

1. Require the package as a **dev** dependency:
   ```bash
   composer require --dev nowo-tech/phpstan-frankenphp:^1.0
   ```
2. Include rulesets **explicitly**. `extension.neon` only registers the extension/schema; it does **not** enable rules:
   ```neon
   includes:
       - vendor/nowo-tech/phpstan-frankenphp/ruleset-classic.neon
       # then worker, optional worker-strict, then hardening
   ```
3. Adopt levels in order: **classic → worker → hardening** (see [MIGRATION.md](MIGRATION.md)).
4. Prefer identifier-based `ignoreErrors` (`frankenphp.*`) over message regexes (see [CONFIGURATION.md](CONFIGURATION.md)).

### Behaviour to expect

- Worker rules flag persistent process state (`static`, globals, `$_ENV` / `$_SESSION`, …).
- Request superglobals (`$_GET`, `$_POST`, …) are **not** flagged unless you include `ruleset-worker-strict.neon` or set `frankenphp.flagRequestSuperglobals: true`.
- Suggested fixes stay **FPM-compatible** (see [MIGRATION.md](MIGRATION.md#fpm-compatibility-important)).

## PHPStan major upgrades

When upgrading PHPStan major versions, re-run this package’s test suite and demos (`composer qa`, `composer demo-*-good`) before relying on the rules in CI.
