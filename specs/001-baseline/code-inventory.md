# Code inventory — baseline

## Rulesets

| File | Level |
|------|-------|
| `ruleset-classic.neon` → `rules/classic.neon` | 1 |
| `ruleset-worker.neon` / `ruleset-worker-strict.neon` → `rules/worker.neon` | 2 |
| `ruleset-hardening.neon` → `rules/hardening.neon` | 3 |
| `rules.neon` | all |
| `extension.neon` | empty (installer hook) |

## Rule classes

### Classic (`src/Rule/Classic`)

- `NoExitOrDieRule`
- `NoFastCgiFinishRequestRule`
- `NoPutenvRule`
- `NoIgnoreUserAbortRule`
- `NoUnlimitedIoTimeoutRule`

### Worker (`src/Rule/Worker`)

- `NoMutableStaticPropertyRule`
- `NoStaticLocalVariableRule`
- `NoGlobalStateWriteRule`
- `NoSuperglobalAccessRule`
- `NoNativeSessionApiRule`
- `NoPersistentIniSetRule`
- `NoSingletonGetInstanceRule`
- `NoRegisterShutdownFunctionRule`
- `NoSetErrorExceptionHandlerRule`

### Hardening (`src/Rule/Hardening`)

- `NoUnlimitedExecutionTimeRule`
- `NoUnlimitedMemoryRule`
- `NoPcntlForkRule`
- `NoBlockingSleepRule`
- `NoRegisterTickFunctionRule`

## Support

- `src/Support/NodeHelper.php`

## Tests

- `tests/Rule/**/*RuleTest.php`
- `tests/Fixtures/**`

## Demos

- `demo/classic/{bad,good}`
- `demo/worker/{bad,good}`
- `demo/hardening/{bad,good}`
