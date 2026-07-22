# Configuration

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
