# Upgrading

## General process

1. Update the package:
   ```bash
   composer update nowo-tech/phpstan-frankenphp
   ```
2. Read [CHANGELOG.md](CHANGELOG.md) for the target version.
3. Re-run PHPStan with your chosen rulesets and the package demos if you maintain local forks of fixtures.

## Upgrading to 1.1.1

No application upgrade steps. **Demos only:** Hot Reload Bundle `^1.4` (FrankenPHP Mercure/`hot_reload`, `dev`/`test`). Continue requiring `nowo-tech/phpstan-frankenphp` as before.

## Upgrading to 1.1.0

**New rules** land in existing `ruleset-worker.neon` / `ruleset-hardening.neon`. If you already enable those rulesets, expect **new PHPStan findings** until you remediate or baseline them.

Constraint stays `nowo-tech/phpstan-frankenphp: ^1.0` (1.1.0 is a compatible minor).

### Consumer action

1. Re-run PHPStan after upgrade.
2. Fix process-state leaks (preferred) or ignore by stable identifier when intentional (CLI shim, vendor bridge):

| Level | Identifiers |
| --- | --- |
| Worker | `frankenphp.worker.noChdir`, `frankenphp.worker.noSetLocale`, `frankenphp.worker.noLocaleSetDefault`, `frankenphp.worker.noDateDefaultTimezoneSet`, `frankenphp.worker.noMbEncodingMutation`, `frankenphp.worker.noErrorReportingMutation`, `frankenphp.worker.noUmask` |
| Hardening | `frankenphp.hardening.noPcntlSignal` |

3. Reads that stay allowed: `mb_*` / `error_reporting()` / `umask()` **without** arguments; `setlocale($category, 0)` / `setlocale($category, "0")`; `locale_get_default()` / `Locale::getDefault()`.

See [RULES.md](RULES.md), [MIGRATION.md](MIGRATION.md), and [ROADMAP.md](ROADMAP.md).

## Upgrading to 1.0.3

Documentation, Spec Kit tooling, Dependabot CI bump (`actions/stale` v11), and internal Rector/CS cleanups. **No rule behaviour changes** and **no consumer action required**. Continue requiring `nowo-tech/phpstan-frankenphp: ^1.0`.

**Contributors:** `.specify/` Cursor Agent scaffold, deep baseline `FR-*` inventory (20/20 `src/` files at that release), and `.github/copilot-instructions.md`.

## Upgrading to 1.0.2

No rule behaviour changes and **no consumer action required**. Continue requiring `nowo-tech/phpstan-frankenphp: ^1.0`.

**Contributors:** new Make targets (`check-open-prs`, `demo-smoke`), Compose V2→V1 detection, Symfony 8 demo DebugBundle/Twig Inspector updates, and an explicit empty `ignoreErrors` in this package’s `phpstan.neon.dist`.

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
