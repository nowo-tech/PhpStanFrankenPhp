# Spec-driven development

This package follows Nowo’s spec-driven pattern (REQ-DOCS-013).

## Baseline

- Product spec: [`specs/001-baseline/spec.md`](../specs/001-baseline/spec.md)
- Code inventory: [`specs/001-baseline/code-inventory.md`](../specs/001-baseline/code-inventory.md)

## Workflow

1. Update the spec when behaviour or rulesets change.
2. Keep `docs/RULES.md` as the human-facing rule catalog (normative for identifiers).
3. Demos and RuleTestCase fixtures must stay aligned with the catalog.
4. Prefer small PRs: one rule (or one level) per change when possible.

## Spec Kit

Use [SPEC-KIT.md](SPEC-KIT.md) with Cursor Agent (`specify` / `.specify/`) to keep `specs/001-baseline/` aligned with this workflow.
