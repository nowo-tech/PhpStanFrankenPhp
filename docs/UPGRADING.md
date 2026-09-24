# Upgrading

## General process

1. Update the package:
   ```bash
   composer update nowo-tech/phpstan-frankenphp
   ```
2. Read [CHANGELOG.md](CHANGELOG.md) for the target version.
3. Re-run PHPStan with your chosen rulesets and the package demos if you maintain local forks of fixtures.

## Upgrading to 1.2.0

**Target:** FrankenPHP worker with `FRANKENPHP_RESET_KERNEL` unset/false (kernel reused).

### Consumer action

1. Re-run PHPStan after upgrade (existing `ruleset-worker` / `ruleset-hardening` gain findings from expanded mb/ini/posix coverage).
2. For kernel-reuse deployments, include:
   ```neon
   includes:
       - vendor/nowo-tech/phpstan-frankenphp/ruleset-worker-no-kernel-reset.neon
   ```
   or set `frankenphp.flagMissingResetInterface: true`.
3. Implement `ResetInterface` (or keep services stateless) for flagged classes; ignore by identifier only for intentional exceptions.

| Level | New / expanded identifiers |
| --- | --- |
| Worker | `frankenphp.worker.noMissingResetInterface`; broader `noMbEncodingMutation` (`mb_detect_order`, `mb_substitute_character`, `null` reads allowed); broader sticky `ini_set` keys |
| Hardening | `frankenphp.hardening.noPosixProcessControl` |

Constraint stays `nowo-tech/phpstan-frankenphp: ^1.0` (1.2.0 is a compatible minor).

See [RULES.md](RULES.md), [MIGRATION.md](MIGRATION.md), [ROADMAP.md](ROADMAP.md).

## Upgrading to 1.1.3

PHP **8.2+** required. Review the [CHANGELOG](CHANGELOG.md) entry.

```bash
composer update nowo-tech/phpstan-frankenphp
```

## Upgrading to 1.1.2

No application upgrade steps.

```bash
composer update nowo-tech/phpstan-frankenphp
```

## Upgrading to 1.1.1

No application upgrade steps. **Demos only:** Hot Reload Bundle `^1.4` (FrankenPHP Mercure/`hot_reload`, `dev`/`test`).

## Upgrading to 1.1.0

**New rules** land in existing `ruleset-worker.neon` / `ruleset-hardening.neon`. If you already enable those rulesets, expect **new PHPStan findings** until you remediate or baseline them.

| Level | Identifiers |
| --- | --- |
| Worker | `frankenphp.worker.noChdir`, `frankenphp.worker.noSetLocale`, `frankenphp.worker.noLocaleSetDefault`, `frankenphp.worker.noDateDefaultTimezoneSet`, `frankenphp.worker.noMbEncodingMutation`, `frankenphp.worker.noErrorReportingMutation`, `frankenphp.worker.noUmask` |
| Hardening | `frankenphp.hardening.noPcntlSignal` |

Reads that stay allowed: `mb_*` / `error_reporting()` / `umask()` **without** arguments; `setlocale($category, 0)` / `"0"`; `locale_get_default()` / `Locale::getDefault()`.

## Upgrading to 1.0.3

Documentation / Spec Kit / CI only. **No rule behaviour changes.**

## Upgrading to 1.0.2 / 1.0.1

No rule behaviour changes. See [CHANGELOG.md](CHANGELOG.md).

## Adopting 1.0.0 (first stable release)

1. `composer require --dev nowo-tech/phpstan-frankenphp:^1.0`
2. Include rulesets explicitly (`extension.neon` does not enable rules).
3. Adopt **classic → worker → (optional no-kernel-reset / strict) → hardening**.
4. Prefer identifier-based `ignoreErrors` (`frankenphp.*`).

## PHPStan major upgrades

When upgrading PHPStan major versions, re-run this package’s test suite and demos (`composer qa`, `composer demo-*-good`) before relying on the rules in CI.
