# Demo with FrankenPHP (Symfony 8)

**REQ-DEMO-001:** FrankenPHP demos must install **Nowo Twig Inspector** and **Nowo Hot Reload** together (`nowo-tech/twig-inspector-bundle` + `nowo-tech/hot-reload-bundle` in `require-dev`). Caddyfile: Mercure + `hot_reload` (and `worker { file …; watch }` in worker mode). Do not enable Hot Reload in production.

The Symfony 8 demo under [`demo/symfony8`](../demo/symfony8) runs on **FrankenPHP** (Caddy + PHP) and exercises `nowo-tech/phpstan-frankenphp` rulesets against intentional anti-patterns.

## FRANKENPHP_MODE (REQ-DEMO-010)

In `.env.example` / Compose:

```dotenv
FRANKENPHP_MODE=worker
```

| Value | Behaviour |
|-------|-----------|
| `worker` (default) | `php_server { worker ... }` — app stays in memory |
| `classic` | Classic `php_server` without worker |

Switch mode by editing `.env` and recreating containers (`docker compose up -d`); **no image rebuild**.

See `demo/symfony8/docker/entrypoint.sh` and `docker/frankenphp/Caddyfile` / `Caddyfile.dev`.

## PHP version (Symfony 8)

Dockerfile uses `dunglas/frankenphp:1-php8.5-alpine` (newest FrankenPHP PHP compatible with Symfony 8 / `require.php`).

## Worker-safe homepage

`App\Good\RequestScopedCounter` implements `ResetInterface` so hit counts do not leak across worker requests. Intentional violations live only under `src/AntiPattern/` for static analysis demos.

## Run PHPStan levels inside the demo

```bash
make -C demo/symfony8 phpstan-classic
make -C demo/symfony8 phpstan-worker
make -C demo/symfony8 phpstan-hardening
```

Findings on `src/AntiPattern/*` are expected.

## Start the demo

```bash
make -C demo/symfony8 up
# Demo started at: http://localhost:8090
```

Or via the demo aggregator:

```bash
make -C demo up-symfony8
```

## Demo smoke

From the package root:

```bash
make demo-smoke
```

Runs `demo/Makefile` `release-verify` (update-bundle → HTTP 200 → down).
