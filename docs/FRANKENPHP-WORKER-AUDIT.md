# FrankenPHP worker mode audit (kernel not reset between requests)

| Field | Value |
|-------|-------|
| Package | `nowo-tech/phpstan-frankenphp` (`phpstan-extension`) |
| Audited revision | `v1.2.0` |
| Audit date | 2026-09-24 |
| Method | Manual review of `src/`, neon rulesets, demos; rules aimed at apps running FrankenPHP worker with `FRANKENPHP_RESET_KERNEL` unset/false |
| **Verdict** | Rulesets cover process/global/static leaks and (opt-in) missing `ResetInterface`. The extension itself never runs inside the HTTP worker. |

## Execution model assumed

FrankenPHP worker boots the Symfony kernel once per worker and serves many requests.

- **A — `FRANKENPHP_RESET_KERNEL` unset/false (default):** kernel instance reused; `services_resetter` clears `ResetInterface` / `kernel.reset` services.
- **B — no reset at all:** nothing cleared (theoretical; not Symfony Runtime default).
- **C — `FRANKENPHP_RESET_KERNEL=1`:** kernel cloned each request (escape hatch; costly).

This package targets **A** as the production profile: enable `ruleset-classic` → `ruleset-worker` → **`ruleset-worker-no-kernel-reset`** → `ruleset-hardening`.

## Why the extension does not run in the worker

- `type: phpstan-extension`; analysis-time only (`require-dev`).
- Rules live in PHPStan's container via `phpstan.rules.rule` tags.

## What the rulesets enforce

| Ruleset | Coverage |
|---------|----------|
| `ruleset-classic.neon` | `exit`/`die`, FastCGI, `putenv`, `ignore_user_abort`, unbounded I/O |
| `ruleset-worker.neon` | Statics, globals, `$_ENV`/`$_SESSION`, sessions, sticky `ini_set` (incl. mbstring/intl), singletons, handlers, process state (`chdir`, locale C/ICU, timezone, mb*, `error_reporting`, `umask`) |
| `ruleset-worker-strict.neon` | Worker + request superglobals |
| `ruleset-worker-no-kernel-reset.neon` | Worker + `NoMissingResetInterfaceRule` |
| `ruleset-hardening.neon` | Unlimited time/memory, pcntl fork/signal, sleep, ticks, posix process control |

## Findings (extension itself)

No runtime worker findings (N/A).

### Residual limits (consumer apps)

- **Heuristic ResetInterface rule:** may miss unusual class names / false-positive on complex builders; not a substitute for reviewing third-party bundles.
- **Dynamic calls** (`$fn = 'chdir'`) not detected.
- **CLI / Messenger** paths that intentionally use pcntl/posix need `ignoreErrors` by path.

## Usage recommendations

```neon
includes:
    - vendor/nowo-tech/phpstan-frankenphp/ruleset-classic.neon
    - vendor/nowo-tech/phpstan-frankenphp/ruleset-worker-no-kernel-reset.neon
    - vendor/nowo-tech/phpstan-frankenphp/ruleset-hardening.neon
```

Keep `FRANKENPHP_RESET_KERNEL` unset/false for throughput; use `=1` only as a temporary isolation fallback.
