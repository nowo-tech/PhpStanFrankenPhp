# Configuration

## Table of contents

- [Phased rulesets (recommended)](#phased-rulesets-recommended)
- [What `extension.neon` does](#what-extensionneon-does)
- [Baselines](#baselines)
- [ignoreErrors](#ignoreerrors)
- [Paths](#paths)
- [Worker strict mode](#worker-strict-mode)
- [Worker load readiness](#worker-load-readiness)

## Phased rulesets (recommended)

```neon
# phpstan.neon / phpstan.neon.dist
includes:
    - vendor/nowo-tech/phpstan-frankenphp/ruleset-classic.neon
```

Later:

```neon
includes:
    - vendor/nowo-tech/phpstan-frankenphp/ruleset-classic.neon
    - vendor/nowo-tech/phpstan-frankenphp/ruleset-worker.neon
```

Production gate:

```neon
includes:
    - vendor/nowo-tech/phpstan-frankenphp/rules.neon
```

## What `extension.neon` does

It registers the package with PHPStan / extension-installer but **does not enable rules**. This forces intentional adoption by level.

## Baselines

During migration, generate a baseline only for the current level:

```bash
vendor/bin/phpstan analyse --generate-baseline phpstan-frankenphp-classic-baseline.neon
```

Shrink the baseline as you fix findings; do not jump to worker rules with a huge classic baseline still open.

## ignoreErrors

Prefer identifier-based ignores (stable across message wording tweaks):

```neon
parameters:
    ignoreErrors:
        -
            identifier: frankenphp.worker.noSuperglobalAccess
            path: src/Legacy/*
```

## Paths

Exclude CLI-only tools if needed:

```neon
parameters:
    excludePaths:
        - bin/legacy-migrator.php
```

## Worker strict mode

Default worker rules flag `$_ENV` and `$_SESSION` only. To also flag request superglobals that FrankenPHP already resets:

```neon
includes:
    - vendor/nowo-tech/phpstan-frankenphp/ruleset-worker-strict.neon
```

Or set the parameter manually:

```neon
parameters:
    frankenphp:
        flagRequestSuperglobals: true
```

## Worker load readiness

Opt-in rules for Doctrine apps under concurrent load: `rollBack()` after a nested begin, `flush()` inside a hand-written transaction, raw `flush()` without closed-manager recovery, recursion without a depth / visited guard. See [RULES.md](RULES.md#worker-load-readiness-opt-in).

With the phased rulesets:

```neon
includes:
    - vendor/nowo-tech/phpstan-frankenphp/ruleset-worker-load.neon
```

When you already include `rules.neon` (or another ruleset that includes `ruleset-worker.neon`), do **not** add the include — PHPStan refuses a file included twice. Set the parameters instead:

```neon
parameters:
    frankenphp:
        flagRollbackAfterNestedBegin: true
        flagFlushInHandWrittenTransaction: true
        flagUnboundedRecursion: true
        # Noisy (one finding per raw flush in a class without recovery); enable on its own when needed:
        # flagFlushWithoutClosedManagerRecovery: true
```

Adopt them like any new level: run once, fix or baseline the findings (`--generate-baseline`), then keep CI clean. Ignore a single finding by identifier:

```neon
parameters:
    ignoreErrors:
        -
            identifier: frankenphp.worker.noUnboundedRecursion
            path: src/Tree/ClosureTableWalker.php
```

### Symfony Beacon (`make check-fast`)

Beacon includes `rules.neon` in `phpstan.neon.dist`, and `make check-fast` runs that PHPStan configuration (`phpstan-analyse`). To enable the rules there, after bumping `nowo-tech/phpstan-frankenphp` to the release that ships them, add the parameters above under `parameters.frankenphp` in `phpstan.neon.dist` (no new include). Existing findings go into `phpstan-baseline.neon` or are fixed first; `check-fast` then fails on any new one.

