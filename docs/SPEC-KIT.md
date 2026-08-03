# GitHub Spec Kit

This repository keeps a baseline specification under `specs/001-baseline/` (REQ-SPECKIT-001 / REQ-SPECKIT-002).

## Layout

```text
.specify/                  Spec Kit Cursor Agent scaffold (constitution, templates)
specs/001-baseline/
  spec.md              Product behaviour, FR-* requirements, acceptance criteria
  code-inventory.md    100% map of production PHP under src/
```

## Maintaining the baseline

When adding or changing a rule:

1. Update `spec.md` acceptance criteria / `FR-*` rows.
2. Update `code-inventory.md` paths (keep 100% `src/` coverage).
3. Update `docs/RULES.md` and demos/tests.

Install upstream Spec Kit tooling only if you need the `specify` CLI locally; the committed Markdown baseline and `.specify/` scaffold are the source of truth in-repo.
