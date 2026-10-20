# Demo with FrankenPHP (Symfony 8)

**REQ-DEMO-001:** FrankenPHP demos must install **Nowo Twig Inspector** and **Nowo Hot Reload** together (`nowo-tech/twig-inspector-bundle` + `nowo-tech/hot-reload-bundle` in `require-dev`). Caddyfile: Mercure + `hot_reload` (and `worker { file …; watch }` in worker mode). Do not enable Hot Reload in production.

The Symfony 8 demo under [`demo/symfony8`](../demo/symfony8) runs on **FrankenPHP** (Caddy + PHP) and exercises `nowo-tech/phpstan-frankenphp` rulesets against intentional anti-patterns.

## Table of contents

- [FRANKENPHP_MODE (REQ-DEMO-010)](#frankenphp_mode-req-demo-010)
- [PHP version (Symfony 8)](#php-version-symfony-8)
- [Worker-safe homepage](#worker-safe-homepage)
- [Run PHPStan levels inside the demo](#run-phpstan-levels-inside-the-demo)
- [Start the demo (development)](#start-the-demo-development)
- [Demo smoke](#demo-smoke)
- [Start the demo (production)](#start-the-demo-production)
- [Troubleshooting](#troubleshooting)

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

## Start the demo (development)

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

## Start the demo (production)

The worker Caddyfile (`docker/frankenphp/Caddyfile`, `php_server { worker { file /app/public/index.php; watch } }`) is the default runtime (`FRANKENPHP_MODE=worker`). To run the demo with a production profile:

1. In `demo/symfony8/.env` set `APP_ENV=prod`, `APP_DEBUG=0`, a real `APP_SECRET` and keep `FRANKENPHP_MODE=worker`.
2. Do not mount `docker/php-dev.ini` (dev-only OPcache revalidation) and do not enable Nowo Hot Reload / Twig Inspector (dev-only bundles).
3. Install without dev dependencies and warm the cache inside the container: `composer install --no-dev --optimize-autoloader` and `php bin/console cache:clear --env=prod`.
4. Recreate the container: `docker compose up -d` (a plain `restart` does not reload environment variables).

Worker mode keeps the kernel booted between requests; see the [worker audit](FRANKENPHP-WORKER-AUDIT.md) when it exists for the services reviewed for request-to-request state.

## Troubleshooting

- **Code or template changes not visible:** worker mode keeps the kernel in memory. Switch to `FRANKENPHP_MODE=classic` (or rely on `watch` in the worker Caddyfile), make sure `docker/php-dev.ini` is mounted and Twig cache is off in dev, then hard-refresh the browser.
- **`FRANKENPHP_MODE` change ignored:** run `docker compose up -d` (or `make up`) so the container is recreated; `docker compose restart` keeps the old environment.
- **Port already in use / demo does not answer:** change `PORT` in `demo/symfony8/.env`, then check `docker compose logs php` for Caddy or PHP errors.
- **Web Profiler toolbar missing:** check `APP_ENV=dev`, `APP_DEBUG=1` and that WebProfilerBundle is enabled for `dev` in `config/bundles.php`; clear the cache with `docker compose exec php php bin/console cache:clear`.
- **Bundle changes not picked up:** the bundle is mounted from the repository root; run `make -C demo/symfony8 update-bundle` (or `composer update nowo-tech/*`) and clear the cache.
