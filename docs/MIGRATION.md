# Migration guide: PHP-FPM → FrankenPHP classic → worker

## Mental model

| Runtime | Process lifetime | What resets per request |
|---------|------------------|-------------------------|
| PHP-FPM | Often one request per process (or few) | Almost everything |
| FrankenPHP **classic** | Longer-lived PHP inside Caddy | Most request state; process APIs still dangerous |
| FrankenPHP **worker** | App boots once; many requests | Framework reset + what you implement; statics/globals/`$_ENV` persist |

## FPM compatibility (important)

**Fixing findings from these rules does not make your application incompatible with PHP-FPM.**

The recommended replacements (exceptions/`Response` instead of `exit`, request-scoped services / `ResetInterface` instead of mutable statics, framework sessions instead of sticky native session globals, bounded timeouts, queues instead of `fastcgi_finish_request()`, …) are ordinary, portable PHP/framework patterns. They run correctly under:

- PHP-FPM (unchanged deploy target),
- FrankenPHP classic,
- FrankenPHP worker.

What changes is **solidity**, not the runtime contract: FPM already hides many of these bugs because the process dies often; worker mode surfaces them. Applying the fixes early is safe on FPM and required for a reliable worker.

Caveat: you may **stop using FPM-only APIs** (notably `fastcgi_finish_request()`). That removes an FPM convenience; it does not break FPM. Prefer framework response + async/queue work so the same code path works everywhere.

## Recommended sequence

1. **Deploy classic** with `FRANKENPHP_MODE=classic` (or `Caddyfile.dev` without `worker`).
2. Enable **`ruleset-classic.neon`** in PHPStan CI. Fix `exit`/`die`, FastCGI APIs, `putenv`, unbounded I/O.
3. Smoke-test under load; confirm no process-killing paths remain.
4. Enable **`ruleset-worker.neon`**. Fix statics, globals, process-state APIs (`chdir`, `setlocale`, `locale_set_default` / `Locale::setDefault`, timezone/mbstring/`error_reporting`/`umask` mutations), superglobals, singletons, native sessions, sticky `ini_set`.
5. Switch `FRANKENPHP_MODE=worker` in a staging environment.
6. Enable **`ruleset-hardening.neon`**. Align PHP timeouts with Caddy/FrankenPHP (REQ-RUNTIME-001).
7. Set worker `max_requests` as a safety net for residual leaks.

## Symfony notes

- Services that cache request data should implement `Symfony\Contracts\Service\ResetInterface` (or be tagged `kernel.reset`).
- Prefer `Request $request` injection over `$_GET` / `$_SERVER`.
- Use `runtime/frankenphp-symfony` (or native Symfony FrankenPHP support on recent versions) for the worker loop.

## Laravel notes

- Prefer Octane’s FrankenPHP driver lifecycle hooks over ad-hoc singletons.
- Avoid static caches on Facades’ underlying instances without reset.

## Verification with this package demos

```bash
composer demo-classic    # must report classic/bad findings
composer demo-worker
composer demo-hardening
composer demo-classic-good   # must pass
```

See [RULES.md](RULES.md) and [demo/README.md](../demo/README.md).

## Worker lifecycle handlers

After enabling `ruleset-worker.neon`, also fix:

- `register_shutdown_function()` — not per-request under workers
- `set_error_handler()` / `set_exception_handler()` — process-wide leakage
- `chdir()` / `setlocale()` / `locale_set_default()` / `Locale::setDefault()` / `date_default_timezone_set()` / mbstring encoding setters / `error_reporting(...)` / `umask(...)` — process-wide state leakage

Optionally enable `ruleset-worker-strict.neon` if you want framework-only access to `$_GET`/`$_POST`/… (FrankenPHP already resets those; default rules focus on `$_ENV` / `$_SESSION`).
