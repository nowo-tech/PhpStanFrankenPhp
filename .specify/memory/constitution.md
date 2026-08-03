# PhpStanFrankenPhp Constitution

## Core Principles

### I. Documented rule contract
Product behavior lives in `specs/001-baseline/spec.md`, `docs/SPEC-DRIVEN-DEVELOPMENT.md`, and `docs/RULES.md`. Each shipped rule has a stable `frankenphp.*` identifier, justification, and demo fixtures.

### II. Spec-first, test-proven
`RuleTestCase` suites and `composer demo-*` commands are the mechanical proof. New or changed rules require tests and bad/good demos.

### III. 100% code inventory traceability
Every production file under `src/` must appear in `specs/001-baseline/code-inventory.md`. New rules require spec updates in the same PR.

### IV. Cursor + Spec Kit
GitHub Spec Kit is initialized with **Cursor Agent** (`cursor-agent`). Skills live in `.cursor/skills/speckit-*`.

### V. PHPStan extension compatibility
Follow declared PHP and PHPStan ranges in `composer.json`. Rulesets are layered: classic → worker → hardening.

## Governance
Amendments update this file, baseline spec when principles affect behavior, and `CHANGELOG.md` when consumer-visible.

**Version**: 1.0.0 | **Ratified**: 2026-08-03 | **Last Amended**: 2026-08-03
