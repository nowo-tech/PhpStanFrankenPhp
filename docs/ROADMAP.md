# Roadmap

Living plan for `nowo-tech/phpstan-frankenphp`. Ship rules only when they catch **real FrankenPHP classic/worker pitfalls**, keep false positives low, and preserve the classic → worker → hardening adoption order.

Current stable: **v1.2.3**.

**Primary target:** FrankenPHP worker with `FRANKENPHP_RESET_KERNEL` unset/false (kernel reused + `services_resetter`).

## Principles

- Prefer **stable identifiers** (`frankenphp.*`) over message churn.
- Prefer **mutation vs read** distinctions (`umask()`, `setlocale(..., 0)`, `mb_*(null)`).
- Do **not** ban entire ecosystems (all of `pcntl_*`, all of `posix_*`) when CLI / Messenger / supervisors legitimately need them outside the web SAPI.
- Every new rule: `rules/*.neon` + `docs/RULES.md` + RuleTestCase + `demo/*/bad|good` (see [CONTRIBUTING.md](CONTRIBUTING.md)).

## Shipped in 1.2.x

| Version | Focus |
| --- | --- |
| **1.2.3** | `pcntl_wait` / `pcntl_waitpid`; Form skips for missing ResetInterface; CLI/Messenger ignore docs |
| **1.2.2** | `NoMissingResetInterfaceRule` detects array-element / nested property / `unset` / `=&` writes (FN fix) |
| **1.2.1** | REQ-CS-008 Igor FrankenPHP worker audit (require-dev) |
| **1.2.0** | Classic / worker / hardening baseline + opt-in missing ResetInterface + posix process control |

## Near term (1.2.x / 1.3)

| Item | Level | Notes |
| --- | --- | --- |
| Optional `frankenphp.missingResetInterfaceSkipPathFragments` | Worker | Consumer path allowlists beyond built-in Form/Entity skips |
| `pcntl_waitid` (when relevant on supported PHP) | Hardening | Same family as wait/waitpid |
| Symfony `kernel.reset` tag / attribute without `ResetInterface` | Worker | Reduce FN when only the tag is used |
| Method-mutating collaborators (`$this->bag->set()`) guidance | Worker | Document gap; optional experimental rule later |

## Medium term

| Item | Level | Notes |
| --- | --- | --- |
| `ob_*` nesting / unmatched output buffer leaks | Worker | Needs solid FP analysis first |
| Dynamic `FuncCall` (`$fn = 'chdir'`) | All | Rare; PHPStan limited — documented as known gap in RULES |
| `header()` / early output that breaks worker streaming assumptions | Classic/Worker | Only with clear false-positive budget |
| `stream_select` / indefinite blocking I/O in request code | Hardening | Sibling to blocking sleep |

## Explicit non-goals (for now)

- Claiming static analysis alone proves zero leaks without `ResetInterface` / manual review of third-party bundles.
- Banning **all** `pcntl_*` or **all** `posix_*` in a monorepo that also analyzes CLI consumers.
- Runtime leak detection (memory growth).
- Auto-fixing Rector companion package.

## Feedback

Open issues/PRs against [GitHub](https://github.com/nowo-tech/PhpStanFrankenPhp) with a real worker/classic failure mode when proposing new rules.
