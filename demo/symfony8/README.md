# Symfony 8 + FrankenPHP demo — PhpStanFrankenPhp

Minimal Symfony **8** app on FrankenPHP that demonstrates `nowo-tech/phpstan-frankenphp` rulesets against intentional anti-patterns.

## Quick start

```bash
cp .env.example .env
make up
# Demo started at: http://localhost:8090
```

## FRANKENPHP_MODE

| Value | Behaviour |
|-------|-----------|
| `worker` (default) | Long-lived workers (`Caddyfile`) |
| `classic` | Per-request PHP (`Caddyfile.dev`) |

Change in `.env`, then `docker compose up -d` (no rebuild).

## PHPStan levels (expect findings)

```bash
make phpstan-classic      # ruleset-classic → src/AntiPattern/Classic
make phpstan-worker       # + worker rules → src/AntiPattern/Worker
make phpstan-hardening    # all levels
```

Compliant samples live in `src/Good/` (excluded from analysis). The homepage controller uses `RequestScopedCounter` (`ResetInterface`) so worker mode stays safe.

## Layout

```text
src/AntiPattern/Classic|Worker|Hardening/  intentional violations
src/Good/                                   ResetInterface example
src/Controller/DemoController.php           safe homepage
phpstan.neon.dist                           classic
phpstan-worker.neon                         classic + worker
phpstan-hardening.neon                      all rulesets
```

## Tests

```bash
make test
make verify
```

See [docs/DEMO-FRANKENPHP.md](../../docs/DEMO-FRANKENPHP.md).
