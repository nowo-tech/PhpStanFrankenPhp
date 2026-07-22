# GitHub Spec Kit

This repository keeps a baseline specification under `specs/001-baseline/` (REQ-SPECKIT-001 / REQ-SPECKIT-002).

## Layout

```text
specs/001-baseline/
  spec.md              Product behaviour and acceptance criteria
  code-inventory.md    Source map of rules, neon files, demos, tests
```

## Maintaining the baseline

When adding or changing a rule:

1. Update `spec.md` acceptance criteria.
2. Update `code-inventory.md` paths.
3. Update `docs/RULES.md` and demos/tests.

Install upstream Spec Kit tooling only if you need the `specify` CLI locally; the committed Markdown baseline is mandatory regardless.
