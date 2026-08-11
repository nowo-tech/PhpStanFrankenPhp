# Code inventory — 100% traceability

**Baseline spec**: [`spec.md`](spec.md)  
**Package**: `nowo-tech/phpstan-frankenphp`  
**Last audited**: 2026-08-10

Every production PHP file under `src/` is mapped below. Tests under `tests/` and demo fixtures under `demo/` are validated separately (see spec acceptance criteria).

## Support utilities

| Source file | Spec section | Requirement IDs |
| --- | --- | --- |
| `Support/NodeHelper.php` | Shared AST helpers for rules | FR-SUP-001 |

## Classic rules (`src/Rule/Classic`)

| Source file | Spec section | Requirement IDs |
| --- | --- | --- |
| `Rule/Classic/NoExitOrDieRule.php` | Classic level — no exit/die | FR-CLS-001 |
| `Rule/Classic/NoFastCgiFinishRequestRule.php` | Classic — no fastcgi_finish_request | FR-CLS-002 |
| `Rule/Classic/NoPutenvRule.php` | Classic — no putenv | FR-CLS-003 |
| `Rule/Classic/NoIgnoreUserAbortRule.php` | Classic — no ignore_user_abort | FR-CLS-004 |
| `Rule/Classic/NoUnlimitedIoTimeoutRule.php` | Classic — bounded I/O timeouts | FR-CLS-005 |

## Worker rules (`src/Rule/Worker`)

| Source file | Spec section | Requirement IDs |
| --- | --- | --- |
| `Rule/Worker/NoMutableStaticPropertyRule.php` | Worker — no mutable statics | FR-WRK-001 |
| `Rule/Worker/NoStaticLocalVariableRule.php` | Worker — no static locals | FR-WRK-002 |
| `Rule/Worker/NoGlobalStateWriteRule.php` | Worker — no global state writes | FR-WRK-003 |
| `Rule/Worker/NoSuperglobalAccessRule.php` | Worker — limit superglobal reads | FR-WRK-004 |
| `Rule/Worker/NoNativeSessionApiRule.php` | Worker — no native session API | FR-WRK-005 |
| `Rule/Worker/NoPersistentIniSetRule.php` | Worker — no persistent ini_set | FR-WRK-006 |
| `Rule/Worker/NoSingletonGetInstanceRule.php` | Worker — no singleton getInstance | FR-WRK-007 |
| `Rule/Worker/NoRegisterShutdownFunctionRule.php` | Worker — no register_shutdown_function | FR-WRK-008 |
| `Rule/Worker/NoSetErrorExceptionHandlerRule.php` | Worker — no custom error/exception handlers | FR-WRK-009 |
| `Rule/Worker/NoChdirRule.php` | Worker — no chdir | FR-WRK-010 |
| `Rule/Worker/NoSetLocaleRule.php` | Worker — no setlocale mutation | FR-WRK-011 |
| `Rule/Worker/NoLocaleSetDefaultRule.php` | Worker — no locale_set_default / Locale::setDefault | FR-WRK-016 |
| `Rule/Worker/NoDateDefaultTimezoneSetRule.php` | Worker — no date_default_timezone_set | FR-WRK-012 |
| `Rule/Worker/NoMbEncodingMutationRule.php` | Worker — no mbstring encoding/language mutation | FR-WRK-013 |
| `Rule/Worker/NoErrorReportingMutationRule.php` | Worker — no error_reporting mutation | FR-WRK-014 |
| `Rule/Worker/NoUmaskRule.php` | Worker — no umask mutation | FR-WRK-015 |

## Hardening rules (`src/Rule/Hardening`)

| Source file | Spec section | Requirement IDs |
| --- | --- | --- |
| `Rule/Hardening/NoUnlimitedExecutionTimeRule.php` | Hardening — bounded execution time | FR-HRD-001 |
| `Rule/Hardening/NoUnlimitedMemoryRule.php` | Hardening — bounded memory | FR-HRD-002 |
| `Rule/Hardening/NoPcntlForkRule.php` | Hardening — no pcntl_fork | FR-HRD-003 |
| `Rule/Hardening/NoBlockingSleepRule.php` | Hardening — no blocking sleep | FR-HRD-004 |
| `Rule/Hardening/NoRegisterTickFunctionRule.php` | Hardening — no register_tick_function | FR-HRD-005 |
| `Rule/Hardening/NoPcntlSignalRule.php` | Hardening — no pcntl signal APIs | FR-HRD-006 |

## Coverage summary

| Category | Files | Mapped |
| --- | ---: | ---: |
| Support utilities | 1 | 1 |
| Classic rules | 5 | 5 |
| Worker rules | 16 | 16 |
| Hardening rules | 6 | 6 |
| **Total production sources (`src/`)** | **28** | **28** |

## Out of `src/` (documented in spec, not inventory rows)

| Artifact | Requirement IDs |
| --- | --- |
| `extension.neon`, `rules.neon`, `ruleset-*.neon`, `rules/*.neon` | FR-RULESET-001 |
| `demo/{classic,worker,hardening}/{bad,good}` | FR-DEMO-001 |
| `tests/Rule/**/*RuleTest.php` | FR-TEST-001 |
