# Rule catalog

Table of contents:

- [Application order](#application-order)
- [Level 1 — Classic (FPM → FrankenPHP classic)](#level-1--classic-fpm--frankenphp-classic)
- [Level 2 — Worker (classic → worker)](#level-2--worker-classic--worker)
- [Level 3 — Hardening](#level-3--hardening)
- [Identifiers](#identifiers)
- [Ignoring a finding](#ignoring-a-finding)

## Application order

Apply rulesets in this order. Do not enable worker rules until classic findings are resolved (or intentionally baselineed).

| Step | Ruleset file | Goal |
|------|--------------|------|
| 1 | `ruleset-classic.neon` | Survive the move off PHP-FPM onto FrankenPHP request/classic mode |
| 2 | `ruleset-worker.neon` | Make application state safe when the process stays alive |
| 2b (optional) | `ruleset-worker-strict.neon` | Also flag $_GET/$_POST/… (framework-only hygiene) |
| 3 | `ruleset-hardening.neon` | Bound resources and remove thread-hostile APIs |

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

**Detects:** Mutable `static` properties and assignments to them.

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

**Detects:** `global` keyword and writes to `$GLOBALS`.

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

**Detects:** `ini_set()` for process-persistent keys (`memory_limit`, `max_execution_time`, timezone, session.*, …).

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

**Detects:** `pcntl_fork`, `pcntl_exec`, `pcntl_rfork`.

**Why:** FrankenPHP uses a threaded SAPI; forking from request threads is unsafe.

**Fix:** Separate process/container or queue worker.

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

Or inline:

```php
// @phpstan-ignore frankenphp.classic.noExitOrDie
exit(1);
```
