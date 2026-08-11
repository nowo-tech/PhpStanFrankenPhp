# Demos for nowo-tech/phpstan-frankenphp

## Symfony 8 + FrankenPHP

Full app demo (REQ-DEMO-*):

```bash
make -C demo up-symfony8
# http://localhost:8090
make -C demo/symfony8 phpstan-classic   # expect findings
```

See [`demo/symfony8/README.md`](symfony8/README.md) and [`docs/DEMO-FRANKENPHP.md`](../docs/DEMO-FRANKENPHP.md).

## Fixture demos (no HTTP)

Intentional violations (`bad/`) and compliant counterparts (`good/`) for each migration level.

## Levels

| Folder | Ruleset | Migration step |
|--------|---------|----------------|
| `classic/` | `ruleset-classic.neon` | PHP-FPM → FrankenPHP **classic** |
| `worker/` | `ruleset-worker.neon` | Classic → FrankenPHP **worker** |
| `worker/` + strict neon | `ruleset-worker-strict.neon` | Optional framework-only hygiene |
| `hardening/` | `ruleset-hardening.neon` | Production hardening |

## Run

```bash
composer demo-classic
composer demo-worker
composer demo-worker-strict
composer demo-hardening

composer demo-classic-good
composer demo-worker-good
composer demo-hardening-good
```

`*-good` targets must exit 0. `bad/` analyses must report findings.

## Mapping (bad → rule → good)

### Classic

| Bad | Identifier | Good |
|-----|------------|------|
| `NoExitOrDie.php` | `frankenphp.classic.noExitOrDie` | `NoExitOrDieGood.php` |
| `NoFastCgiFinishRequest.php` | `frankenphp.classic.noFastCgiFinishRequest` | `NoFastCgiFinishRequestGood.php` |
| `NoPutenv.php` | `frankenphp.classic.noPutenv` | `NoPutenvGood.php` |
| `NoIgnoreUserAbort.php` (+ `Extra`) | `frankenphp.classic.noIgnoreUserAbort` | `NoIgnoreUserAbortGood.php` |
| `NoUnlimitedIoTimeout.php` (+ `Extra`) | `frankenphp.classic.noUnlimitedIoTimeout` | `NoUnlimitedIoTimeoutGood.php` |

Overview sample: `SafeRequestHandler.php`.

### Worker

| Bad | Identifier | Good |
|-----|------------|------|
| `NoMutableStaticProperty.php` | `frankenphp.worker.noMutableStaticProperty` | `NoMutableStaticPropertyGood.php` |
| `NoStaticLocalVariable.php` | `frankenphp.worker.noStaticLocalVariable` | `NoStaticLocalVariableGood.php` |
| `NoGlobalStateWrite.php` | `frankenphp.worker.noGlobalStateWrite` | `NoGlobalStateWriteGood.php` |
| `NoSuperglobalAccess.php` | `frankenphp.worker.noEnvMutation` / `noSuperglobalAccess` | `NoSuperglobalAccessGood.php` |
| `NoNativeSessionApi.php` | `frankenphp.worker.noNativeSessionApi` | `NoNativeSessionApiGood.php` |
| `NoPersistentIniSet.php` (+ `Extra`) | `frankenphp.worker.noPersistentIniSet` | `NoPersistentIniSetGood.php` |
| `NoSingletonGetInstance.php` (+ `Extra`) | `frankenphp.worker.noSingletonGetInstance` | `NoSingletonGetInstanceGood.php` |
| `NoRegisterShutdownFunction.php` | `frankenphp.worker.noRegisterShutdownFunction` | `NoRegisterShutdownFunctionGood.php` |
| `NoSetErrorExceptionHandler.php` | `frankenphp.worker.noSetErrorExceptionHandler` | `NoSetErrorExceptionHandlerGood.php` |
| `NoChdir.php` | `frankenphp.worker.noChdir` | `NoChdirGood.php` |
| `NoSetLocale.php` | `frankenphp.worker.noSetLocale` | `NoSetLocaleGood.php` |
| `NoLocaleSetDefault.php` | `frankenphp.worker.noLocaleSetDefault` | `NoLocaleSetDefaultGood.php` |
| `NoDateDefaultTimezoneSet.php` | `frankenphp.worker.noDateDefaultTimezoneSet` | `NoDateDefaultTimezoneSetGood.php` |
| `NoMbEncodingMutation.php` | `frankenphp.worker.noMbEncodingMutation` | `NoMbEncodingMutationGood.php` |
| `NoErrorReportingMutation.php` | `frankenphp.worker.noErrorReportingMutation` | `NoErrorReportingMutationGood.php` |
| `NoUmask.php` | `frankenphp.worker.noUmask` | `NoUmaskGood.php` |

Overview sample: `RequestScopedService.php`.

### Hardening

| Bad | Identifier | Good |
|-----|------------|------|
| `NoUnlimitedExecutionTime.php` | `frankenphp.hardening.noUnlimitedExecutionTime` | `NoUnlimitedExecutionTimeGood.php` |
| `NoUnlimitedMemory.php` (+ `Extra`) | `frankenphp.hardening.noUnlimitedMemory` | `NoUnlimitedMemoryGood.php` |
| `NoPcntlFork.php` | `frankenphp.hardening.noPcntlFork` | `NoPcntlForkGood.php` |
| `NoBlockingSleep.php` | `frankenphp.hardening.noBlockingSleep` | `NoBlockingSleepGood.php` |
| `NoRegisterTickFunction.php` | `frankenphp.hardening.noRegisterTickFunction` | `NoRegisterTickFunctionGood.php` |
| `NoPcntlSignal.php` | `frankenphp.hardening.noPcntlSignal` | `NoPcntlSignalGood.php` |

Overview sample: `BoundedResources.php`.

See [docs/RULES.md](../docs/RULES.md).
