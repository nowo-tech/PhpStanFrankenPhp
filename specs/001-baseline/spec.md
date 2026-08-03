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

### Hardening rules

| ID | Requirement |
|----|-------------|
| FR-HRD-001 | `NoUnlimitedExecutionTimeRule` flags unlimited execution time |
| FR-HRD-002 | `NoUnlimitedMemoryRule` flags unlimited memory ini |
| FR-HRD-003 | `NoPcntlForkRule` flags `pcntl_fork` |
| FR-HRD-004 | `NoBlockingSleepRule` flags blocking `sleep`/`usleep` |
| FR-HRD-005 | `NoRegisterTickFunctionRule` flags `register_tick_function` |

### Demos & tests

| ID | Requirement |
|----|-------------|
| FR-DEMO-001 | Each rule has `demo/{level}/bad` and `demo/{level}/good` fixtures referenced from `docs/RULES.md` |
| FR-TEST-001 | Each rule has a `RuleTestCase` under `tests/Rule/`; `composer test` and `composer demo-*` pass in CI |

---

## Success Criteria

- **SC-001**: **20/20** production PHP files under `src/` mapped in [`code-inventory.md`](code-inventory.md).
- **SC-002**: `composer test` passes; `composer demo-*-good` reports zero errors; bad demos fail as expected.
- **SC-003**: `docs/RULES.md` lists every shipped rule with identifier and justification.

---

## Validation

`composer qa`, PHPUnit rule tests, and demo PHPStan runs per Makefile targets.
