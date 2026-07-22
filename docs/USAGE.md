# Usage

## Day-to-day

```bash
vendor/bin/phpstan analyse
```

Filter by identifier (PHPStan 2):

```bash
vendor/bin/phpstan analyse --error-format=json | jq '.files[].messages[] | select(.identifier|startswith("frankenphp."))'
```

## Package demos (this repository)

```bash
composer demo-classic
composer demo-worker
composer demo-hardening
composer demo-all

# Compliant samples must be clean:
composer demo-classic-good
composer demo-worker-good
composer demo-hardening-good
```

Via Make (Docker):

```bash
make demo-all
```

## Suggested CI gate

1. Always run `ruleset-classic` on `main`.
2. Run `ruleset-worker` on a dedicated job or after classic is green.
3. Run `ruleset-hardening` before enabling worker in production.

## Related docs

- [RULES.md](RULES.md) — full catalog
- [MIGRATION.md](MIGRATION.md) — FPM → classic → worker
- [demo/README.md](../demo/README.md) — fixture map
