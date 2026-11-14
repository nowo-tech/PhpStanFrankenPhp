# Rule catalog

Table of contents:

- [Application order](#application-order)
- [Level 1 — Classic (FPM → FrankenPHP classic)](#level-1--classic-fpm--frankenphp-classic)
- [Level 2 — Worker (classic → worker)](#level-2--worker-classic--worker)
- [Level 3 — Hardening](#level-3--hardening)
- [Identifiers](#identifiers)
- [Ignoring a finding](#ignoring-a-finding)

## Table of contents

- [Application order](#application-order)
- [Level 1 — Classic (FPM → FrankenPHP classic)](#level-1--classic-fpm--frankenphp-classic)
  - [`NoExitOrDieRule` — `frankenphp.classic.noExitOrDie`](#noexitordierule--frankenphpclassicnoexitordie)
  - [`NoFastCgiFinishRequestRule` — `frankenphp.classic.noFastCgiFinishRequest`](#nofastcgifinishrequestrule--frankenphpclassicnofastcgifinishrequest)
  - [`NoPutenvRule` — `frankenphp.classic.noPutenv`](#noputenvrule--frankenphpclassicnoputenv)
  - [`NoIgnoreUserAbortRule` — `frankenphp.classic.noIgnoreUserAbort`](#noignoreuserabortrule--frankenphpclassicnoignoreuserabort)
  - [`NoUnlimitedIoTimeoutRule` — `frankenphp.classic.noUnlimitedIoTimeout`](#nounlimitediotimeoutrule--frankenphpclassicnounlimitediotimeout)
- [Level 2 — Worker (classic → worker)](#level-2--worker-classic--worker)
  - [`NoMutableStaticPropertyRule` — `frankenphp.worker.noMutableStaticProperty`](#nomutablestaticpropertyrule--frankenphpworkernomutablestaticproperty)
  - [`NoStaticLocalVariableRule` — `frankenphp.worker.noStaticLocalVariable`](#nostaticlocalvariablerule--frankenphpworkernostaticlocalvariable)
  - [`NoGlobalStateWriteRule` — `frankenphp.worker.noGlobalStateWrite`](#noglobalstatewriterule--frankenphpworkernoglobalstatewrite)
  - [`NoSuperglobalAccessRule` — `frankenphp.worker.noEnvMutation` / `frankenphp.worker.noSuperglobalAccess`](#nosuperglobalaccessrule--frankenphpworkernoenvmutation--frankenphpworkernosuperglobalaccess)
  - [`NoNativeSessionApiRule` — `frankenphp.worker.noNativeSessionApi`](#nonativesessionapirule--frankenphpworkernonativesessionapi)
  - [`NoPersistentIniSetRule` — `frankenphp.worker.noPersistentIniSet`](#nopersistentinisetrule--frankenphpworkernopersistentiniset)
  - [`NoSingletonGetInstanceRule` — `frankenphp.worker.noSingletonGetInstance`](#nosingletongetinstancerule--frankenphpworkernosingletongetinstance)
  - [`NoRegisterShutdownFunctionRule` — `frankenphp.worker.noRegisterShutdownFunction`](#noregistershutdownfunctionrule--frankenphpworkernoregistershutdownfunction)
  - [`NoSetErrorExceptionHandlerRule` — `frankenphp.worker.noSetErrorExceptionHandler`](#noseterrorexceptionhandlerrule--frankenphpworkernoseterrorexceptionhandler)
  - [`NoChdirRule` — `frankenphp.worker.noChdir`](#nochdirrule--frankenphpworkernochdir)
  - [`NoSetLocaleRule` — `frankenphp.worker.noSetLocale`](#nosetlocalerule--frankenphpworkernosetlocale)
  - [`NoLocaleSetDefaultRule` — `frankenphp.worker.noLocaleSetDefault`](#nolocalesetdefaultrule--frankenphpworkernolocalesetdefault)
  - [`NoDateDefaultTimezoneSetRule` — `frankenphp.worker.noDateDefaultTimezoneSet`](#nodatedefaulttimezonesetrule--frankenphpworkernodatedefaulttimezoneset)
  - [`NoMbEncodingMutationRule` — `frankenphp.worker.noMbEncodingMutation`](#nombencodingmutationrule--frankenphpworkernombencodingmutation)
  - [`NoErrorReportingMutationRule` — `frankenphp.worker.noErrorReportingMutation`](#noerrorreportingmutationrule--frankenphpworkernoerrorreportingmutation)
  - [`NoUmaskRule` — `frankenphp.worker.noUmask`](#noumaskrule--frankenphpworkernoumask)
  - [`NoMissingResetInterfaceRule` — `frankenphp.worker.noMissingResetInterface`](#nomissingresetinterfacerule--frankenphpworkernomissingresetinterface)
  - [Worker load readiness (opt-in)](#worker-load-readiness-opt-in)
  - [`NoRollbackAfterNestedBeginRule` — `frankenphp.worker.noRollbackAfterNestedBegin`](#norollbackafternestedbeginrule--frankenphpworkernorollbackafternestedbegin)
  - [`NoFlushInHandWrittenTransactionRule` — `frankenphp.worker.noFlushInHandWrittenTransaction`](#noflushinhandwrittentransactionrule--frankenphpworkernoflushinhandwrittentransaction)
  - [`NoFlushWithoutClosedManagerRecoveryRule` — `frankenphp.worker.noFlushWithoutClosedManagerRecovery`](#noflushwithoutclosedmanagerrecoveryrule--frankenphpworkernoflushwithoutclosedmanagerrecovery)
  - [`NoUnboundedRecursionRule` — `frankenphp.worker.noUnboundedRecursion`](#nounboundedrecursionrule--frankenphpworkernounboundedrecursion)
- [Level 3 — Hardening](#level-3--hardening)
  - [`NoUnlimitedExecutionTimeRule` — `frankenphp.hardening.noUnlimitedExecutionTime`](#nounlimitedexecutiontimerule--frankenphphardeningnounlimitedexecutiontime)
  - [`NoUnlimitedMemoryRule` — `frankenphp.hardening.noUnlimitedMemory`](#nounlimitedmemoryrule--frankenphphardeningnounlimitedmemory)
  - [`NoPcntlForkRule` — `frankenphp.hardening.noPcntlFork`](#nopcntlforkrule--frankenphphardeningnopcntlfork)
  - [`NoBlockingSleepRule` — `frankenphp.hardening.noBlockingSleep`](#noblockingsleeprule--frankenphphardeningnoblockingsleep)
  - [`NoRegisterTickFunctionRule` — `frankenphp.hardening.noRegisterTickFunction`](#noregistertickfunctionrule--frankenphphardeningnoregistertickfunction)
  - [`NoPcntlSignalRule` — `frankenphp.hardening.noPcntlSignal`](#nopcntlsignalrule--frankenphphardeningnopcntlsignal)
  - [`NoPosixProcessControlRule` — `frankenphp.hardening.noPosixProcessControl`](#noposixprocesscontrolrule--frankenphphardeningnoposixprocesscontrol)
- [Identifiers](#identifiers)
- [Ignoring a finding](#ignoring-a-finding)
  - [CLI / Messenger / supervisors (pcntl & posix)](#cli--messenger--supervisors-pcntl--posix)
  - [Known gap — dynamic `FuncCall`](#known-gap--dynamic-funccall)

## Application order

Apply rulesets in this order. Do not enable worker rules until classic findings are resolved (or intentionally baselineed).

| Step | Ruleset file | Goal |
|------|--------------|------|
| 1 | `ruleset-classic.neon` | Survive the move off PHP-FPM onto FrankenPHP request/classic mode |
| 2 | `ruleset-worker.neon` | Make application state safe when the process stays alive |
| 2b (optional) | `ruleset-worker-strict.neon` | Also flag $_GET/$_POST/… (framework-only hygiene) |
| 2c (recommended for kernel reuse) | `ruleset-worker-no-kernel-reset.neon` | Worker + missing `ResetInterface` when `FRANKENPHP_RESET_KERNEL` is unset/false |
| 2d (optional, Doctrine apps under load) | `ruleset-worker-load.neon` | Worker + transactions that break under deadlocks and unbounded recursion ([details](#worker-load-readiness-opt-in)) |
| 3 | `ruleset-hardening.neon` | Bound resources and remove thread-hostile APIs |

**Target runtime:** FrankenPHP **worker** with **`FRANKENPHP_RESET_KERNEL` unset/false** (default): the Symfony kernel instance is reused; `services_resetter` still clears services that implement `ResetInterface`. Use step **2c** for that profile. Setting `FRANKENPHP_RESET_KERNEL=1` clones the kernel each request (slower) and is an escape hatch, not a substitute for fixing state.

**FPM remains supported.** Remediations are portable application hygiene: they harden the app for FrankenPHP worker (and classic) without requiring FrankenPHP-specific APIs, and they continue to work under PHP-FPM. See [MIGRATION.md — FPM compatibility](MIGRATION.md#fpm-compatibility-important).

```neon
# phpstan.neon (consumer project) — recommended phased includes
includes:
    - vendor/nowo-tech/phpstan-frankenphp/ruleset-classic.neon
    # enable after classic is clean:
    # - vendor/nowo-tech/phpstan-frankenphp/ruleset-worker.neon
    # enable last:
    # - vendor/nowo-tech/phpstan-frankenphp/ruleset-hardening.neon
```

All-at-once (not recommended for large legacy apps):

```neon
includes:
    - vendor/nowo-tech/phpstan-frankenphp/rules.neon
```

## Level 1 — Classic (FPM → FrankenPHP classic)

### `NoExitOrDieRule` — `frankenphp.classic.noExitOrDie`

**Detects:** `exit`, `die`.

**Why:** Under FPM, `exit` ends one worker process that is cheap to recycle. Under FrankenPHP, the same call can tear down the embedded PHP runtime or a long-lived worker thread and drop in-flight requests.

**Fix:** Throw a domain/HTTP exception or return a framework Response. Never terminate the process from request code.

**Demo:** `demo/classic/bad/NoExitOrDie.php` · good: `demo/classic/good/NoExitOrDieGood.php`

---

### `NoFastCgiFinishRequestRule` — `frankenphp.classic.noFastCgiFinishRequest`

**Detects:** `fastcgi_finish_request()`.

**Why:** The function is FastCGI/FPM-specific. FrankenPHP is not FastCGI; the call is unavailable or meaningless, and “finish response then keep working” patterns break.

**Fix:** Send the response via the framework, then enqueue post-response work (Messenger, queue, etc.).

**Demo:** `demo/classic/bad/NoFastCgiFinishRequest.php` · good: `demo/classic/good/NoFastCgiFinishRequestGood.php`

---

### `NoPutenvRule` — `frankenphp.classic.noPutenv`

**Detects:** `putenv()`.

**Why:** Environment mutations are process-wide and survive the HTTP request under FrankenPHP (worse in worker mode).

**Fix:** Use `.env`, container parameters, or explicit config objects.

**Demo:** `demo/classic/bad/NoPutenv.php` · good: `demo/classic/good/NoPutenvGood.php`

---

### `NoIgnoreUserAbortRule` — `frankenphp.classic.noIgnoreUserAbort`

**Detects:** `ignore_user_abort(true)` (and enabling variants).

**Why:** Continuing after the client disconnects pins a FrankenPHP thread and can exhaust the pool.

**Fix:** Acknowledge quickly and move remaining work to a queue.

**Demo:** `demo/classic/bad/NoIgnoreUserAbort.php` · good: `demo/classic/good/NoIgnoreUserAbortGood.php`

---

### `NoUnlimitedIoTimeoutRule` — `frankenphp.classic.noUnlimitedIoTimeout`

**Detects:** `Process::setTimeout(null|0)`, `setIdleTimeout(null|0)`, `curl_setopt` timeout options set to `0`/`null`, and raw `proc_open()`.

**Why:** A hung child process or HTTP call occupies a FrankenPHP thread indefinitely (see Nowo **REQ-RUNTIME-001**).

**Fix:** Always set finite timeouts; prefer Symfony Process with `setTimeout` + `setIdleTimeout`; kill/cleanup on failure.

**Demo:** `demo/classic/bad/NoUnlimitedIoTimeout.php` · good: `demo/classic/good/SafeRequestHandler.php`

## Level 2 — Worker (classic → worker)

### `NoMutableStaticPropertyRule` — `frankenphp.worker.noMutableStaticProperty`

**Detects:** Mutable `static` properties (every property of `static $a, $b;`) and writes to them: `=`, `=&`, compound assignments (`.=`, `+=`, `??=`, …) and `++` / `--`, including array offsets (`self::$cache['k'] .= …`).

**Why:** Static state is kept for the worker lifetime and leaks across users.

**Fix:** Instance properties + `ResetInterface` / `kernel.reset`, or **class constants** for immutable values. PHP does **not** allow `readonly` static properties.

**Demo:** `demo/worker/bad/NoMutableStaticProperty.php` · good: `demo/worker/good/RequestScopedService.php`

---

### `NoStaticLocalVariableRule` — `frankenphp.worker.noStaticLocalVariable`

**Detects:** `static $var` inside functions/methods.

**Why:** Documented FrankenPHP behaviour: static locals retain values between `frankenphp_handle_request()` iterations.

**Fix:** Use locals, request attributes, or a reset-aware service.

**Demo:** `demo/worker/bad/NoStaticLocalVariable.php` · good: `demo/worker/good/NoStaticLocalVariableGood.php`

---

### `NoGlobalStateWriteRule` — `frankenphp.worker.noGlobalStateWrite`

**Detects:** `global` keyword and writes to `$GLOBALS`: `=`, `=&`, compound assignments (`$GLOBALS['x'] .= …`) and `++` / `--`.

**Why:** Globals survive across worker requests.

**Fix:** Dependency injection.

**Demo:** `demo/worker/bad/NoGlobalStateWrite.php` · good: `demo/worker/good/NoGlobalStateWriteGood.php`

---

### `NoSuperglobalAccessRule` — `frankenphp.worker.noEnvMutation` / `frankenphp.worker.noSuperglobalAccess`

**Detects (default):** `$_ENV`, `$_SESSION`.

**Detects (strict):** also `$_GET`, `$_POST`, `$_COOKIE`, `$_FILES`, `$_REQUEST`, `$_SERVER` when `frankenphp.flagRequestSuperglobals: true` (`ruleset-worker-strict.neon`).

**Why:** FrankenPHP **does** reset most request superglobals between worker requests, but **`$_ENV` is not reset**. Native `$_SESSION` bypasses framework session lifecycle. Strict mode is optional hygiene for framework-only request access.

**Fix:** Container/config for env; framework Session / Request APIs.

**Demo:** `demo/worker/bad/NoSuperglobalAccess.php` · good: `demo/worker/good/NoSuperglobalAccessGood.php`

---

### `NoNativeSessionApiRule` — `frankenphp.worker.noNativeSessionApi`

**Detects:** `session_start`, `session_destroy`, `session_write_close`, and related natives.

**Why:** Manual session control fights framework + worker reset and can leave locks/state.

**Fix:** Framework session service only.

**Demo:** `demo/worker/bad/NoNativeSessionApi.php` · good: `demo/worker/good/NoNativeSessionApiGood.php`

---

### `NoPersistentIniSetRule` — `frankenphp.worker.noPersistentIniSet`

**Detects:** `ini_set()` and its alias `ini_alter()` for process-persistent keys (`memory_limit`, `max_execution_time`, timezone, session.*, …).

**Why:** Many INI settings stick on the worker and change later requests.

**Fix:** Configure in `php.ini` / FrankenPHP image; avoid per-request `ini_set`.

**Demo:** `demo/worker/bad/NoPersistentIniSet.php` · good: `demo/worker/good/NoPersistentIniSetGood.php`

---

### `NoSingletonGetInstanceRule` — `frankenphp.worker.noSingletonGetInstance`

**Detects:** `::getInstance()` / `->getInstance()` singleton access.

**Why:** Classic singletons retain mutable instance state for the worker lifetime.

**Fix:** Container services; implement `ResetInterface` when they hold request data.

**Demo:** `demo/worker/bad/NoSingletonGetInstance.php` · good: `demo/worker/good/NoSingletonGetInstanceGood.php`

### `NoRegisterShutdownFunctionRule` — `frankenphp.worker.noRegisterShutdownFunction`

**Detects:** `register_shutdown_function()`.

**Why:** In worker mode shutdown functions run when the **worker script ends**, not after each HTTP request. FPM “after response” patterns silently break.

**Fix:** Framework `kernel.terminate` / Octane listeners, Messenger, or a queue.

**Demo:** `demo/worker/bad/NoRegisterShutdownFunction.php` · good: `demo/worker/good/NoRegisterShutdownFunctionGood.php`

---

### `NoSetErrorExceptionHandlerRule` — `frankenphp.worker.noSetErrorExceptionHandler`

**Detects:** `set_error_handler()`, `set_exception_handler()`.

**Why:** Handlers are process-wide and persist on the worker unless restored.

**Fix:** Framework error/exception listeners; if natives are unavoidable, always restore the previous handler.

**Demo:** `demo/worker/bad/NoSetErrorExceptionHandler.php` · good: `demo/worker/good/NoSetErrorExceptionHandlerGood.php`

---

### `NoChdirRule` — `frankenphp.worker.noChdir`

**Detects:** `chdir()`.

**Why:** The process working directory survives across worker requests; later requests may resolve relative paths against another request's CWD.

**Fix:** Use absolute paths or inject a configured base path.

**Demo:** `demo/worker/bad/NoChdir.php` · good: `demo/worker/good/NoChdirGood.php`

---

### `NoSetLocaleRule` — `frankenphp.worker.noSetLocale`

**Detects:** `setlocale()` **when changing locale** (second argument not `0` / `"0"`).

**Why:** Locale is process-wide and leaks formatting/collation into later requests. Queries via `setlocale($category, 0)` are allowed.

**Fix:** Framework request locale (e.g. Symfony `Request::setLocale` / Translator) or configure locale in php.ini / the image.

**Demo:** `demo/worker/bad/NoSetLocale.php` · good: `demo/worker/good/NoSetLocaleGood.php`

---

### `NoLocaleSetDefaultRule` — `frankenphp.worker.noLocaleSetDefault`

**Detects:** `locale_set_default()` / `Locale::setDefault()`.

**Why:** The ICU default locale is process-wide and leaks intl formatting into later requests. `locale_get_default()` / `Locale::getDefault()` reads stay allowed.

**Fix:** Framework request locale (e.g. Symfony `Request::setLocale` / Translator) or configure intl defaults in php.ini / the image.

**Demo:** `demo/worker/bad/NoLocaleSetDefault.php` · good: `demo/worker/good/NoLocaleSetDefaultGood.php`

---

### `NoDateDefaultTimezoneSetRule` — `frankenphp.worker.noDateDefaultTimezoneSet`

**Detects:** `date_default_timezone_set()`.

**Why:** The default timezone sticks on the worker and changes date/time behaviour for later requests.

**Fix:** Configure `date.timezone` in php.ini / the FrankenPHP image, or pass explicit timezones to DateTime APIs.

**Demo:** `demo/worker/bad/NoDateDefaultTimezoneSet.php` · good: `demo/worker/good/NoDateDefaultTimezoneSetGood.php`

---

### `NoMbEncodingMutationRule` — `frankenphp.worker.noMbEncodingMutation`

**Detects:** `mb_internal_encoding` / `mb_regex_encoding` / `mb_http_output` / `mb_language` / `mb_detect_order` / `mb_substitute_character` **with a non-null argument**.

**Why:** mbstring defaults are process-wide. Calls without an argument, or with explicit `null` (PHP 8 getters), are allowed.

**Fix:** Configure mbstring in php.ini / the image, or pass encodings explicitly to `mb_*` functions.

**Demo:** `demo/worker/bad/NoMbEncodingMutation.php` · good: `demo/worker/good/NoMbEncodingMutationGood.php`

---

### `NoErrorReportingMutationRule` — `frankenphp.worker.noErrorReportingMutation`

**Detects:** `error_reporting(...)` **with a level argument**.

**Why:** Changing the reporting level sticks on the worker. Calls without an argument (reads) are allowed.

**Fix:** Configure `error_reporting` in php.ini / the FrankenPHP image.

**Demo:** `demo/worker/bad/NoErrorReportingMutation.php` · good: `demo/worker/good/NoErrorReportingMutationGood.php`

---

### `NoUmaskRule` — `frankenphp.worker.noUmask`

**Detects:** `umask(...)` **with a mask argument**.

**Why:** The file-creation mask is process-wide. Calls without an argument (reads) are allowed.

**Fix:** Set umask in the process supervisor / container entrypoint, not per request.

**Demo:** `demo/worker/bad/NoUmask.php` · good: `demo/worker/good/NoUmaskGood.php`

---

### `NoMissingResetInterfaceRule` — `frankenphp.worker.noMissingResetInterface`

**Detects:** classes that write to `$this->…` outside `__construct` / `reset` / lifecycle magic **without** implementing `Symfony\Contracts\Service\ResetInterface`. A write is any assignment (`=`, `=&`, `+=`, `??=`, …), increment/decrement or `unset()` targeting the property itself, one of its array elements (`$this->items[$k] = …`, `$this->items[] = …`, `unset($this->items[$k])`) or a nested property (`$this->obj->prop = …`). Reads (`isset`, `foreach`, local copies) and method calls on the property (`$this->collection->add()`) are not reported.

**Why:** With `FRANKENPHP_RESET_KERNEL` unset/false the kernel is reused; only services with `ResetInterface` (or `kernel.reset`) are cleared by `services_resetter`. Heuristic skips entities/DTOs/messages/tests **and** Symfony Form helpers (`*Form`, `*FormType`, `*TypeExtension`, `*DataTransformer`, `*DataMapper`, FQCN fragments `/form/` / `/forms/`) by name.

**Enable:** `frankenphp.flagMissingResetInterface: true` or include `ruleset-worker-no-kernel-reset.neon` (off by default).

**Fix:** Implement `reset()`, keep the service stateless, or use request-scoped design.

**Demo:** `demo/worker/bad/NoMissingResetInterface.php`, `demo/worker/bad/NoMissingResetInterfaceArrayWrite.php` · good: `demo/worker/good/NoMissingResetInterfaceGood.php`, `demo/worker/good/NoMissingResetInterfaceArrayWriteGood.php`

---

### Worker load readiness (opt-in)

Four heuristic rules for Doctrine apps that serve concurrent writes from long-lived workers (Beacon spec 239). Background: in DBAL 4 a nested `Connection::rollBack()` runs `ROLLBACK TO SAVEPOINT` before it lowers the nesting level. On a MySQL deadlock (1213) the server rolls back the whole transaction and drops every savepoint, so that rollback fails with 1305, the nesting level is never lowered, and the worker's connection stays nested for every later request (only `Connection::close()` resets it). The caller sees 1305 instead of the deadlock, so nothing retries.

All four are **off by default**, like `noMissingResetInterface`. Each has its own parameter:

| Parameter | Rule | In `ruleset-worker-load.neon` |
|-----------|------|:---:|
| `flagRollbackAfterNestedBegin` | `NoRollbackAfterNestedBeginRule` | yes |
| `flagFlushInHandWrittenTransaction` | `NoFlushInHandWrittenTransactionRule` | yes |
| `flagFlushWithoutClosedManagerRecovery` | `NoFlushWithoutClosedManagerRecoveryRule` | no (noisy) |
| `flagUnboundedRecursion` | `NoUnboundedRecursionRule` | yes |

Enable them with `ruleset-worker-load.neon`, or — when you already include `rules.neon` or another ruleset that includes `ruleset-worker.neon` (PHPStan refuses a file included twice) — with the parameters:

```neon
parameters:
    frankenphp:
        flagRollbackAfterNestedBegin: true
        flagFlushInHandWrittenTransaction: true
        flagUnboundedRecursion: true
        # flagFlushWithoutClosedManagerRecovery: true   # noisy: one finding per raw flush
```

**How Doctrine objects are recognised.** A receiver is an entity manager / DBAL connection when its PHPStan type is (or extends) `Doctrine\Persistence\ObjectManager`, `Doctrine\ORM\EntityManagerInterface` or `Doctrine\DBAL\Connection`. Local variables are followed through their first assignment (`$connection = $this->em->getConnection();`) and through parameters, closures included. When a declared class cannot be loaded (analysis without the vendor tree) its name decides (`…EntityManager`, `…ObjectManager`, `Doctrine\DBAL\…Connection`); a declared class with another name is not Doctrine. When there is no class type at all (untyped, `mixed`), the receiver name decides: `$em`, `$entityManager`, `$manager`, `getEntityManager()`, `getManager()`… for a manager, a name with `connection` / `conn` / `db` / `dbal` for a connection.

**Scope.** Methods of classes, traits, interfaces and enums, and the closures inside them. Free functions and code outside classes are not analysed.

---

### `NoRollbackAfterNestedBeginRule` — `frankenphp.worker.noRollbackAfterNestedBegin`

**Detects:** `rollBack()` / `rollback()` on a connection or entity manager inside a `catch` or `finally` of a `try` when
- `beginTransaction()` runs in the same function before the `try` (the TaskBoard `TaskApiTransaction` / AuthKit `InvitationAcceptor` pattern), or
- the `try` block calls `beginTransaction()`, `wrapInTransaction()` / `transactional()` or an ORM `flush()` (directly or through a `flush($entityManager)` helper).

A rollback guarded by `if ($connection->isTransactionActive())` is still reported (the message says why: after the failed savepoint rollback the transaction is still active, at the wrong level).

**Not reported:** a rollback inside its own `try` whose `catch` / `finally` calls `close()`; a rollback after a `close()` call in the same handler; a rollback under an `if` / `elseif` that checks `getTransactionNestingLevel()`; rollbacks in closures defined inside the handler; PDO / other non-Doctrine receivers.

**Known false positives:** a hand-written transaction that is never called inside another transaction and never flushes the ORM inside it (the rollback then runs at level 1 and is safe); the rule cannot see the call graph. **Known false negatives:** the begin in a caller or another method; a `close()` in a sibling branch earlier in the handler counts as recovery.

**Why:** see [Worker load readiness](#worker-load-readiness-opt-in): one deadlock produces a 1305 instead of a retryable deadlock and leaves the worker's connection nested for every later request.

**Fix:** run the work through `TransactionRunnerInterface::run()` (or the DoctrineDeadlockRetryBundle runner), or close the connection when the rollback fails:

```php
} catch (\Throwable $e) {
    try {
        $connection->rollBack();
    } catch (\Throwable) {
        $connection->close(); // savepoint gone: reset the nesting level for the next request
    }
    throw $e;
}
```

**Demo:** `demo/worker/bad/NoRollbackAfterNestedBegin.php` · good: `demo/worker/good/NoRollbackAfterNestedBeginGood.php`

---

### `NoFlushInHandWrittenTransactionRule` — `frankenphp.worker.noFlushInHandWrittenTransaction`

**Detects:** an entity-manager `flush()` (or a `flush($entityManager)` helper such as a recovering flusher)
- after a `beginTransaction()` in the same function and before a later `commit()` (or with no `commit()` after it), outside `catch` / `finally` blocks; or
- in the body of a closure / arrow function passed directly to `wrapInTransaction()` / `transactional()` (UserKit `PersonalDataEraser` pattern). For `EntityManager::wrapInTransaction()` the message adds that it already flushes after the callable.

**Not reported:** classes whose name or interfaces contain `TransactionRunner` (the runner owns the transaction); callables that are not closures (`$this->work(...)`, `[$this, 'work']`); flushes in a caller's callable (UptimeMonitor `TenantQuota::withTenantLock()` takes `$write` from its callers: the flush is not visible there).

**Known false positives:** a flush between two unrelated begin/commit pairs when the positions overlap (rare). **Known false negatives:** flushes in methods called from inside the transaction, and transactions opened in a caller.

**Why:** `flush()` opens its own transaction; inside another one it runs at nesting level 2 on a savepoint. After a deadlock the ORM's own rollback fails with 1305, so the deadlock is never retried and the connection stays nested.

**Fix:** let the transaction owner flush once at the outer level — `TransactionRunnerInterface::run()`, or `wrapInTransaction()` without an inner flush — or move the flush out of the transaction.

**Demo:** `demo/worker/bad/NoFlushInHandWrittenTransaction.php` · good: `demo/worker/good/NoFlushInHandWrittenTransactionGood.php`

---

### `NoFlushWithoutClosedManagerRecoveryRule` — `frankenphp.worker.noFlushWithoutClosedManagerRecovery`

**Detects:** a direct `$entityManager->flush()` in a class that never recovers a closed entity manager: no `isOpen()` on an entity manager, no `resetManager()`, no `flush($entityManager)` helper, no call whose name contains `recover`, `reopen`, `ensureOpen` or `resetIfClosed` with an entity-manager argument, and no call on a dependency whose short class name contains `Recover`, `Flusher`, `TransactionRunner` or `DeadlockRetry`. One finding per raw flush (ChatKit `ChatService` pattern).

**Skipped:** anonymous classes; classes whose short name ends with `Test`, `TestCase`, `Fixture(s)`, `Migration` or `Command`, or whose namespace has a `Tests`, `Test`, `Fixtures`, `DataFixtures`, `Migrations` or `Command` segment.

**Opt-in only, not in `ruleset-worker-load.neon`:** the check is class-level, so it is noisy — a controller or service that flushes in every action gets one finding per flush, including apps where DoctrineBundle's `Registry::reset()` (run by `services_resetter`) may already replace a closed lazy manager between requests — though not within the request that closed it. It is useful for bundles that must not rely on the host resetting the manager, and to find the services that need a recovery helper. **Known false negatives:** a class with any recovery call is clean even if other methods flush raw.

**Fix:** after a failed flush, `if (!$em->isOpen()) { $registry->resetManager(); }`, or flush through a recovering helper / `TransactionRunnerInterface`.

**Demo:** `demo/worker/bad/NoFlushWithoutClosedManagerRecovery.php` · good: `demo/worker/good/NoFlushWithoutClosedManagerRecoveryGood.php`

---

### `NoUnboundedRecursionRule` — `frankenphp.worker.noUnboundedRecursion`

**Detects:** recursion over a parent / child relation with no depth limit and no visited set:
- a method that calls itself (`$this->m()`, `self::m()`, `static::m()`) with an argument that walks a relation, e.g. `foreach ($this->childrenOf($task) as $child) { $this->descendantsOf($child); }` (TaskBoard `TaskTree::descendantsOf()`);
- a method that calls the same method on a related object (`$this->parent?->path()`, `$child->count()` in `foreach ($this->children as $child)`);
- a closure assigned to a variable it captures by reference (`$walk = function ($node) use (&$walk) { … $walk($reply); }`).

An expression walks a relation when it reads a property or calls a method whose name has one of the words child / children, parent(s), descendant(s), ancestor(s), reply / replies, subtask(s), subpage(s), subfolder(s), subcategory / subcategories, subcomment(s) — directly, or through the `foreach` variable bound to such an expression.

**Not reported:** a method / closure with a parameter (or captured variable) whose name has one of the words depth, level, limit, max, remaining, visited, seen, budget, hops; recursion that does not walk a relation (`factorial($n - 1)`).

**Known false positives:** trees whose shape is guaranteed acyclic and shallow by construction (e.g. built from a closure table with a depth limit elsewhere); a guard held in a property instead of a parameter. **Known false negatives:** mutual recursion (`a()` → `b()` → `a()`), relations named otherwise (`items`, `nodes`, `members`), recursion through `array_map()` callbacks, and `ClassName::m()` with the class written out.

**Why:** a cycle in the data (a task that became its own ancestor) or a very deep tree recurses until the worker hits the memory limit or overflows the stack; on FrankenPHP the crash takes down the worker thread and every request it was serving.

**Fix:** pass a depth counter and stop at a maximum, pass a visited set, or walk iteratively with an explicit stack.

**Demo:** `demo/worker/bad/NoUnboundedRecursion.php` · good: `demo/worker/good/NoUnboundedRecursionGood.php`

---

## Level 3 — Hardening

### `NoUnlimitedExecutionTimeRule` — `frankenphp.hardening.noUnlimitedExecutionTime`

**Detects:** `set_time_limit(0)`.

**Why:** Unlimited execution lets a stuck handler hold a worker forever.

**Fix:** Finite limit aligned with Caddy / FrankenPHP write timeouts.

**Demo:** `demo/hardening/bad/NoUnlimitedExecutionTime.php` · good: `demo/hardening/good/NoUnlimitedExecutionTimeGood.php`

---

### `NoUnlimitedMemoryRule` — `frankenphp.hardening.noUnlimitedMemory`

**Detects:** `ini_set('memory_limit', -1)` (and `'0'` / `'-1'` strings).

**Why:** Unbounded memory turns leaks into OOM on long-lived workers.

**Fix:** Finite `memory_limit` + `max_requests` worker recycling.

**Demo:** `demo/hardening/bad/NoUnlimitedMemory.php` · good: `demo/hardening/good/NoUnlimitedMemoryGood.php`

---

### `NoPcntlForkRule` — `frankenphp.hardening.noPcntlFork`

**Detects:** `pcntl_fork`, `pcntl_exec`, `pcntl_rfork`, `pcntl_wait`, `pcntl_waitpid`.

**Why:** FrankenPHP uses a threaded SAPI; forking from request threads is unsafe. Blocking with `pcntl_wait` / `pcntl_waitpid` on a child is the same anti-pattern for HTTP workers (messages distinguish wait vs fork/exec).

**Fix:** Separate process/container or queue worker. Keep fork/wait in CLI supervisors / Messenger consumers and ignore those paths (see [Ignoring a finding](#ignoring-a-finding)).

**Demo:** `demo/hardening/bad/NoPcntlFork.php` · good: `demo/hardening/good/NoPcntlForkGood.php`

---

### `NoBlockingSleepRule` — `frankenphp.hardening.noBlockingSleep`

**Detects:** `sleep`, `usleep`, `time_nanosleep`, `time_sleep_until`.

**Why:** Blocking sleeps waste a worker slot under load.

**Fix:** Queues, async HTTP, or deferred jobs.

**Demo:** `demo/hardening/bad/NoBlockingSleep.php` · good: `demo/hardening/good/NoBlockingSleepGood.php`

---

### `NoRegisterTickFunctionRule` — `frankenphp.hardening.noRegisterTickFunction`

**Detects:** `register_tick_function()`.

**Why:** Tick handlers remain on the worker and introduce re-entrancy/overhead across requests.

**Fix:** Middleware or explicit instrumentation.

**Demo:** `demo/hardening/bad/NoRegisterTickFunction.php` · good: `demo/hardening/good/NoRegisterTickFunctionGood.php`

---

### `NoPcntlSignalRule` — `frankenphp.hardening.noPcntlSignal`

**Detects:** `pcntl_signal`, `pcntl_async_signals`, `pcntl_signal_dispatch`, `pcntl_signal_get_handler`, `pcntl_sigprocmask`, `pcntl_sigwaitinfo`, `pcntl_sigtimedwait`, `pcntl_alarm`.

**Why:** FrankenPHP uses a threaded SAPI; installing, masking, waiting on, or dispatching process signals from request threads is unsafe.

**Fix:** Handle signals in the supervisor / a dedicated process, not from application request code.

**Demo:** `demo/hardening/bad/NoPcntlSignal.php` · good: `demo/hardening/good/NoPcntlSignalGood.php`

---

### `NoPosixProcessControlRule` — `frankenphp.hardening.noPosixProcessControl`

**Detects:** `posix_kill`, `posix_setuid`, `posix_seteuid`, `posix_setgid`, `posix_setegid`, `posix_setpgid`, `posix_setsid`.

**Why:** FrankenPHP/Caddy owns the OS process; uid/gid/signal mutations from request code are unsafe on a reused worker.

**Fix:** Supervisor / dedicated CLI process, not application request handlers.

**Demo:** `demo/hardening/bad/NoPosixProcessControl.php` · good: `demo/hardening/good/NoPosixProcessControlGood.php`

## Identifiers

All identifiers are stable and suitable for `ignoreErrors` baselines:

- `frankenphp.classic.*`
- `frankenphp.worker.*`
- `frankenphp.hardening.*`

## Ignoring a finding

Prefer fixing. When unavoidable (vendor shim, one-off CLI):

```neon
parameters:
    ignoreErrors:
        -
            identifier: frankenphp.worker.noSuperglobalAccess
            path: src/Legacy/Bridge.php
```

### CLI / Messenger / supervisors (pcntl & posix)

Hardening flags process APIs that are legitimate outside the HTTP worker. Scope ignores to those trees instead of disabling the ruleset (repeat per identifier / path as needed):

```neon
parameters:
    ignoreErrors:
        -
            identifier: frankenphp.hardening.noPcntlFork
            path: bin/*
        -
            identifier: frankenphp.hardening.noPcntlSignal
            path: bin/*
        -
            identifier: frankenphp.hardening.noPosixProcessControl
            path: bin/*
        # same three identifiers for src/MessageHandler/* and src/Command/* if needed
```

Adjust paths to your `bin/console` commands, Messenger consumers, and deploy supervisors. Alternatively exclude those paths from the hardening PHPStan run.

### Known gap — dynamic `FuncCall`

Calls via variables (`$fn = 'chdir'; $fn(...);`) are not detected. Prefer static calls or baseline rare dynamic sites explicitly.

Or inline:

```php
// @phpstan-ignore frankenphp.classic.noExitOrDie
exit(1);
```
