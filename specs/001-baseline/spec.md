# Spec: PhpStanFrankenPhp baseline

## Summary

PHPStan extension that detects code patterns unsafe when migrating from PHP-FPM to FrankenPHP classic mode, then to worker mode, with an optional hardening level.

## Actors

- Application maintainers running PHPStan in CI
- Package maintainers adding rules/demos

## Requirements

### Functional

1. Package type is `phpstan-extension` and integrates with `phpstan/extension-installer`.
2. Rules are grouped in three rulesets: classic, worker, hardening.
3. `extension.neon` does not enable rules by itself.
4. Each rule has a stable `frankenphp.*` identifier, documentation, demo (`bad` + `good`), and RuleTestCase.
5. Default worker rules align with FrankenPHP docs (`$_ENV` not reset; most other superglobals are reset). Strict mode is optional.
6. Worker rules cover shutdown/error/exception handlers that do not run per-request under workers.
5. Demos under `demo/` show intentional violations (`bad/`) and clean samples (`good/`).

### Non-functional

1. PHP `>=8.1 <8.6`, PHPStan `^2.0`.
2. English documentation only.
3. Complies with Nowo tooling REQ-* applicable to non-bundle packages.

## Acceptance

- `composer test` passes.
- `composer demo-*-good` reports zero errors.
- `composer demo-classic|worker|hardening` reports errors on `bad/` fixtures.
- `docs/RULES.md` lists every shipped rule with justification and order.
