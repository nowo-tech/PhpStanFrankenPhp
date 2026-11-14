# Feature Specification: PhpStanFrankenPhp baseline (100% code coverage)

**Feature Branch**: `001-baseline`  
**Created**: 2026-08-03  
**Status**: Active  
**Input**: Backfill GitHub Spec Kit baseline documenting 100% of production code in `src/`.

**Related docs**: [`docs/SPEC-DRIVEN-DEVELOPMENT.md`](../../docs/SPEC-DRIVEN-DEVELOPMENT.md), [`docs/RULES.md`](../../docs/RULES.md), [`docs/USAGE.md`](../../docs/USAGE.md)  
**Code inventory (traceability)**: [`code-inventory.md`](code-inventory.md)

---

## Summary

**Package**: `nowo-tech/phpstan-frankenphp`  
**Type**: PHPStan extension (not a Symfony bundle)

Static analysis rules that detect code patterns unsafe when migrating from PHP-FPM to FrankenPHP **classic** mode, then **worker** mode, with an optional **hardening** level. Each rule ships with demos (`bad/` + `good/`) and PHPUnit `RuleTestCase` coverage.

---

## User Scenarios & Testing

### User Story 1 — Classic migration (Priority: P1)

As a maintainer moving off PHP-FPM, I enable the **classic** ruleset so exit/die, putenv, and FastCGI-only APIs are flagged before FrankenPHP deployment.

### User Story 2 — Worker mode (Priority: P1)

As a maintainer enabling FrankenPHP workers, I enable the **worker** ruleset so request-local state, shutdown handlers, and superglobal misuse are caught.

### User Story 3 — Hardening (Priority: P2)

As a platform engineer, I optionally enable **hardening** rules to block fork, unbounded sleep, and unlimited resource ini settings in long-running processes.

---

## Requirements

### Package & rulesets

| ID | Requirement |
|----|-------------|
| FR-RULESET-001 | Package type `phpstan-extension`; rules grouped in classic, worker, and hardening neon entrypoints; `extension.neon` does not auto-enable rules |
| FR-SUP-001 | `NodeHelper` provides shared AST inspection utilities for rules |
| FR-SUP-002 | `AstFinder` walks function bodies without entering nested functions; `DoctrineExprResolver` tells entity managers / DBAL connections apart from PHPStan types, declared class names (classes not loadable) or receiver names (untyped) |

### Classic rules

| ID | Requirement |
|----|-------------|
| FR-CLS-001 | `NoExitOrDieRule` flags `exit`/`die` incompatible with FrankenPHP classic lifecycle |
| FR-CLS-002 | `NoFastCgiFinishRequestRule` flags `fastcgi_finish_request()` |
| FR-CLS-003 | `NoPutenvRule` flags `putenv()` |
| FR-CLS-004 | `NoIgnoreUserAbortRule` flags `ignore_user_abort()` |
| FR-CLS-005 | `NoUnlimitedIoTimeoutRule` flags unlimited stream/socket timeouts |

### Worker rules

| ID | Requirement |
|----|-------------|
| FR-WRK-001 | `NoMutableStaticPropertyRule` flags mutable static properties |
| FR-WRK-002 | `NoStaticLocalVariableRule` flags static locals |
| FR-WRK-003 | `NoGlobalStateWriteRule` flags writes to global state |
| FR-WRK-004 | `NoSuperglobalAccessRule` flags unsafe superglobal access (strict mode optional via ruleset) |
| FR-WRK-005 | `NoNativeSessionApiRule` flags native session API usage |
| FR-WRK-006 | `NoPersistentIniSetRule` flags persistent `ini_set` |
| FR-WRK-007 | `NoSingletonGetInstanceRule` flags singleton `getInstance` patterns |
| FR-WRK-008 | `NoRegisterShutdownFunctionRule` flags `register_shutdown_function` |
| FR-WRK-009 | `NoSetErrorExceptionHandlerRule` flags custom error/exception handlers |
| FR-WRK-010 | `NoChdirRule` flags `chdir()` |
| FR-WRK-011 | `NoSetLocaleRule` flags `setlocale()` mutations (queries with `0` / `"0"` allowed) |
| FR-WRK-012 | `NoDateDefaultTimezoneSetRule` flags `date_default_timezone_set()` |
| FR-WRK-013 | `NoMbEncodingMutationRule` flags mbstring encoding/language setters (with argument) |
| FR-WRK-014 | `NoErrorReportingMutationRule` flags `error_reporting(...)` mutations |
| FR-WRK-015 | `NoUmaskRule` flags `umask(...)` mutations |
| FR-WRK-016 | `NoLocaleSetDefaultRule` flags `locale_set_default()` / `Locale::setDefault()` |
| FR-WRK-017 | `NoMissingResetInterfaceRule` (opt-in) flags mutable `$this` state without `ResetInterface`, including writes through array elements (`$this->items[$k] = …`, `[] =`, assign-ops, inc/dec, `unset`) and nested properties (`$this->obj->prop = …`, `=&`); reads and method calls on the property are not reported; heuristic skips include Form helpers (`*Form`, `*FormType`, `*TypeExtension`, `*DataTransformer`, `*DataMapper`, `/form/` `/forms/` FQCN fragments) |
| FR-WRK-018 | `NoRollbackAfterNestedBeginRule` (opt-in, `flagRollbackAfterNestedBegin`) flags `rollBack()` in a catch / finally after a `beginTransaction()` before the try, or after `beginTransaction()` / `wrapInTransaction()` / `transactional()` / an ORM flush inside the try; also when guarded by `isTransactionActive()`; not when the rollback is wrapped in a try that closes the connection, follows a `close()`, or sits under a `getTransactionNestingLevel()` check (Beacon spec 239) |
| FR-WRK-019 | `NoFlushInHandWrittenTransactionRule` (opt-in, `flagFlushInHandWrittenTransaction`) flags an entity-manager flush (direct or `flush($em)` helper) between `beginTransaction()` and a later `commit()` outside catch / finally, or inside a closure passed to `wrapInTransaction()` / `transactional()`; classes named / implementing `*TransactionRunner*` are skipped (Beacon spec 239) |
| FR-WRK-020 | `NoFlushWithoutClosedManagerRecoveryRule` (opt-in, `flagFlushWithoutClosedManagerRecovery`, not in `ruleset-worker-load.neon`) flags direct entity-manager flushes in a class with no `isOpen()` on a manager, no `resetManager()`, no `flush($em)` / `recover…($em)` helper and no recovering dependency; tests, fixtures, migrations and commands are skipped (Beacon spec 239) |
| FR-WRK-021 | `NoUnboundedRecursionRule` (opt-in, `flagUnboundedRecursion`) flags a method (or self-capturing closure) that recurses over a relation (child/children, parent, descendant, ancestor, reply/replies, subtask, subpage, subfolder, subcategory, subcomment) with no guard parameter / captured variable (depth, level, limit, max, remaining, visited, seen, budget, hops) (Beacon spec 239) |
| FR-WRK-022 | `ruleset-worker-load.neon` includes `ruleset-worker.neon` and enables FR-WRK-018, FR-WRK-019 and FR-WRK-021 |

### Hardening rules

| ID | Requirement |
|----|-------------|
| FR-HRD-001 | `NoUnlimitedExecutionTimeRule` flags unlimited execution time |
| FR-HRD-002 | `NoUnlimitedMemoryRule` flags unlimited memory ini |
| FR-HRD-003 | `NoPcntlForkRule` flags `pcntl_fork` / `pcntl_exec` / `pcntl_rfork` / `pcntl_wait` / `pcntl_waitpid` |
| FR-HRD-004 | `NoBlockingSleepRule` flags blocking `sleep`/`usleep` |
| FR-HRD-005 | `NoRegisterTickFunctionRule` flags `register_tick_function` |
| FR-HRD-006 | `NoPcntlSignalRule` flags pcntl signal APIs (`pcntl_signal`, `pcntl_sigprocmask`, `pcntl_alarm`, …) |
| FR-HRD-007 | `NoPosixProcessControlRule` flags `posix_kill` / `posix_setuid` / … |

### Demos & tests

| ID | Requirement |
|----|-------------|
| FR-DEMO-001 | Each rule has `demo/{level}/bad` and `demo/{level}/good` fixtures referenced from `docs/RULES.md` |
| FR-TEST-001 | Each rule has a `RuleTestCase` under `tests/Rule/`; `composer test` and `composer demo-*` pass in CI |

---

## Success Criteria

- **SC-001**: **30/30** production PHP files under `src/` mapped in [`code-inventory.md`](code-inventory.md).
- **SC-002**: `composer test` passes; `composer demo-*-good` reports zero errors; bad demos fail as expected.
- **SC-003**: `docs/RULES.md` lists every shipped rule with identifier and justification.

---

## Explicit non-goals

- Not a FrankenPHP runtime or Caddy configuration generator.
- Not a Symfony bundle (package type `phpstan-extension`).
- `extension.neon` never enables rules by itself; consumers opt in level by level.

---

## Validation

```bash
composer qa                  # cs-check, phpstan, tests
composer test                # PHPUnit RuleTestCase suite
composer demo-all            # bad demos report the expected errors
composer demo-classic-good   # good demos report zero errors (also demo-worker-good, demo-hardening-good)
make release-check           # full pre-release gate (Docker)
find src -type f | wc -l     # must equal the inventory total (SC-001)
```
