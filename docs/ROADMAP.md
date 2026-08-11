# Roadmap

Living plan for `nowo-tech/phpstan-frankenphp`. Ship rules only when they catch **real FrankenPHP classic/worker pitfalls**, keep false positives low, and preserve the classic → worker → hardening adoption order.

Current stable: **v1.1.0**.

## Principles

- Prefer **stable identifiers** (`frankenphp.*`) over message churn.
- Prefer **mutation vs read** distinctions (`umask()`, `setlocale(..., 0)`, `mb_*()` without args).
- Do **not** ban entire ecosystems (all of `pcntl_*`, all of `intl`) when CLI / Messenger / supervisors legitimately need them outside the web SAPI.
- Every new rule: `rules/*.neon` + `docs/RULES.md` + RuleTestCase + `demo/*/bad|good` (see [CONTRIBUTING.md](CONTRIBUTING.md)).

## Shipped in 1.1.0

| Level | Focus |
| --- | --- |
| Classic | `exit`/`die`, FastCGI, `putenv`, `ignore_user_abort`, unbounded I/O timeouts |
| Worker | Statics/globals/superglobals/sessions/singletons/handlers + process state (`chdir`, locale C/ICU, timezone, mbstring, `error_reporting`, `umask`) |
| Hardening | Unlimited time/memory, `pcntl_fork`/`exec`, blocking sleep, ticks, **pcntl signal surface** |

## Near term (1.1.x / 1.2)

| Item | Level | Notes |
| --- | --- | --- |
| Allow `mb_*(null)` / explicit `null` arg as read | Worker | PHP 8 nullable getters; reduces FP next to no-arg reads |
| `mb_detect_order` / `mb_substitute_character` setters | Worker | Same process-wide mbstring family as `NoMbEncodingMutationRule` |
| Expand `NoPersistentIniSetRule` keys (`mbstring.*`, `intl.default_locale`, …) | Worker | Complements dedicated APIs; only sticky keys |
| Document CLI path ignores for pcntl/Messenger | Docs | Avoid “ban pcntl everywhere” confusion when one PHPStan config covers web + CLI |

## Medium term

| Item | Level | Notes |
| --- | --- | --- |
| `posix_kill` / `posix_setuid` / `posix_setgid` in request code | Hardening | Process ownership under threaded SAPI; high value, low volume |
| Selective `pcntl_wait` / `pcntl_waitpid` in request code | Hardening | Only with clear messaging; keep CLI baselines easy |
| `ob_*` nesting / unmatched output buffer leaks | Worker | Optional; needs solid FP analysis first |
| Dynamic `FuncCall` (`$fn = 'chdir'`) | All | Rare; PHPStan limited — document as known gap |

## Explicit non-goals (for now)

- Banning **all** `pcntl_*` or **all** `posix_*` in a monorepo that also analyzes CLI consumers.
- Framework-specific “must implement `ResetInterface`” rules (better as docs / cookbook).
- Runtime leak detection (memory growth) — out of scope for PHPStan static rules.
- Auto-fixing Rector companion package (separate product if ever).

## Release track

1. ~~Land process-state + pcntl signal rules → **1.1.0**~~ (done).
2. Near-term FP polish + mb/ini expansions → **1.1.x** patches or **1.2.0** if behaviour widens further.
3. Hardening POSIX / wait APIs only after demo + ignore guidance for CLI paths.

## Feedback

Open issues/PRs against [GitHub](https://github.com/nowo-tech/PhpStanFrankenPhp) with a real worker/classic failure mode when proposing new rules. Prefer evidence (FrankenPHP docs, ZTS crashes, request bleed) over “looks impure”.
