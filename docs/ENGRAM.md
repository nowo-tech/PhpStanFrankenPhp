# Engram

Persistent AI / maintainer memory for **PhpStanFrankenPhp**.

## Product intent

Help teams migrate from PHP-FPM to FrankenPHP by detecting unsafe patterns in two steps (classic, then worker), plus an optional hardening level.

## Non-goals

- Not a FrankenPHP runtime or Caddy config generator.
- Not a Symfony bundle; type is `phpstan-extension`.
- `extension.neon` must stay rule-free so adoption is intentional by level.

## Ruleset levels

1. classic → 2. worker (optional 2b worker-strict) → 3. hardening
2. Default worker flags `$_ENV` + `$_SESSION` only; strict also flags request superglobals.
3. Includes shutdown / error / exception handler rules for worker lifecycle.

## Invariants

- Docs and README in English (REQ-DOCS-016).
- `declare(strict_types=1)` on every PHP file (REQ-CS-004).
- Demos excluded from dist via `archive.exclude` (REQ-PKG-002).
- Every rule has identifier, justification, fix, demo, and test.

## Useful commands

```bash
make test
make demo-all
make release-check
```
