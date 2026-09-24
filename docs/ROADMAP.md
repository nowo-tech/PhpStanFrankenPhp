# Roadmap

Living plan for `nowo-tech/phpstan-frankenphp`. Ship rules only when they catch **real FrankenPHP classic/worker pitfalls**, keep false positives low, and preserve the classic → worker → hardening adoption order.

Current stable: **v1.2.0**.

**Primary target:** FrankenPHP worker with `FRANKENPHP_RESET_KERNEL` unset/false (kernel reused + `services_resetter`).

## Principles

- Prefer **stable identifiers** (`frankenphp.*`) over message churn.
- Prefer **mutation vs read** distinctions (`umask()`, `setlocale(..., 0)`, `mb_*(null)`).
- Do **not** ban entire ecosystems (all of `pcntl_*`, all of `posix_*`) when CLI / Messenger / supervisors legitimately need them outside the web SAPI.
- Every new rule: `rules/*.neon` + `docs/RULES.md` + RuleTestCase + `demo/*/bad|good` (see [CONTRIBUTING.md](CONTRIBUTING.md)).

## Shipped in 1.2.0

| Level | Focus |
| --- | --- |
| Classic | `exit`/`die`, FastCGI, `putenv`, `ignore_user_abort`, unbounded I/O timeouts |
| Worker | Statics/globals/superglobals/sessions/singletons/handlers + process state + **opt-in missing ResetInterface** (`ruleset-worker-no-kernel-reset.neon`) |
| Hardening | Unlimited time/memory, pcntl fork/signal, blocking sleep, ticks, **posix process control** |

## Near term (1.2.x / 1.3)

| Item | Level | Notes |
| --- | --- | --- |
| Document CLI path ignores for pcntl/posix/Messenger | Docs | Sample `ignoreErrors` for `bin/` + Messenger consumers |
| Tighten `NoMissingResetInterfaceRule` heuristics | Worker | Fewer FPs on builders/forms; optional path allowlists |
| Selective `pcntl_wait` / `pcntl_waitpid` in request code | Hardening | Only with clear messaging |

## Medium term

| Item | Level | Notes |
| --- | --- | --- |
| `ob_*` nesting / unmatched output buffer leaks | Worker | Needs solid FP analysis first |
| Dynamic `FuncCall` (`$fn = 'chdir'`) | All | Rare; PHPStan limited — document as known gap |

## Explicit non-goals (for now)

- Claiming static analysis alone proves zero leaks without `ResetInterface` / manual review of third-party bundles.
- Banning **all** `pcntl_*` or **all** `posix_*` in a monorepo that also analyzes CLI consumers.
- Runtime leak detection (memory growth).
- Auto-fixing Rector companion package.

## Feedback

Open issues/PRs against [GitHub](https://github.com/nowo-tech/PhpStanFrankenPhp) with a real worker/classic failure mode when proposing new rules.
