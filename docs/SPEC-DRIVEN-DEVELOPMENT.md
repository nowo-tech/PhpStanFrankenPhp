# Spec-driven development

This package follows Nowo’s spec-driven pattern (REQ-DOCS-013).

## Table of contents

- [Layers in sync](#layers-in-sync)
- [User stories](#user-stories)
- [Package functional scope](#package-functional-scope)
- [Validating the functional spec](#validating-the-functional-spec)
- [Requirement identifiers (REQ-*)](#requirement-identifiers-req-)
- [Contributor workflow](#contributor-workflow)
- [Relationship to Engram](#relationship-to-engram)
- [GitHub Spec Kit](#github-spec-kit)
- [See also](#see-also)

## Layers in sync

PhpStanFrankenPhp keeps three layers in sync:

1. **GitHub Spec Kit baseline** — [`specs/001-baseline/`](../specs/001-baseline/) ([`spec.md`](../specs/001-baseline/spec.md) for user stories, `FR-*` and `SC-*`; [`code-inventory.md`](../specs/001-baseline/code-inventory.md) for 100% `src/` traceability). Operator manual: [`SPEC-KIT.md`](SPEC-KIT.md).
2. **Product behavior** — the rules and rulesets consumers may rely on, documented in [`RULES.md`](RULES.md) (normative for rule identifiers), [`CONFIGURATION.md`](CONFIGURATION.md), [`USAGE.md`](USAGE.md) and [`MIGRATION.md`](MIGRATION.md), and proven by `RuleTestCase` tests plus the `bad/` / `good/` demos.
3. **Traceability anchors** — stable `REQ-*` identifiers in Makefiles and demos so PRs and issues can cite tooling behavior.

PHPUnit (`RuleTestCase`) and PHPStan itself are the mechanical proof; there is no separate executable spec language (no Gherkin).

## User stories

| ID | Story | Functional scope | Integrator docs |
| --- | --- | --- | --- |
| US-01 | Maintainer leaving PHP-FPM enables the **classic** ruleset to catch `exit`/`die`, `putenv`, FastCGI-only APIs and unlimited I/O timeouts | FR-CLS-001 … FR-CLS-005 | [RULES.md](RULES.md), [MIGRATION.md](MIGRATION.md) |
| US-02 | Maintainer enabling FrankenPHP workers adds the **worker** ruleset (optionally worker-strict / no-kernel-reset) to catch request-leaking state | FR-WRK-001 … FR-WRK-017 | [RULES.md](RULES.md), [CONFIGURATION.md](CONFIGURATION.md) |
| US-03 | Platform engineer adds the optional **hardening** ruleset to block fork, signals, blocking sleep and unlimited resource settings | FR-HRD-001 … FR-HRD-007 | [RULES.md](RULES.md) |
| US-04 | Adopter installs the extension without surprises: `extension.neon` enables no rule until a level is included | FR-RULESET-001 | [INSTALLATION.md](INSTALLATION.md), [CONFIGURATION.md](CONFIGURATION.md) |

## Package functional scope

**Goal:** detect code patterns that are unsafe when migrating from PHP-FPM to FrankenPHP **classic** mode, then **worker** mode, with an optional **hardening** level.

**In scope:** the PHPStan rules under `src/Rule/`, the shared `NodeHelper`, the level entrypoints (`ruleset-classic.neon`, `ruleset-worker.neon`, worker-strict / no-kernel-reset variants, `ruleset-hardening.neon`) and the rule catalog in `docs/RULES.md`.

**Explicit non-goals:**

- Not a FrankenPHP runtime or Caddy configuration generator.
- Not a Symfony bundle (package type `phpstan-extension`); no runtime code is loaded by applications.
- `extension.neon` never enables rules on its own: adoption is intentional, level by level.

**Not part of the Packagist API:** `demo/` (excluded from the dist archive, REQ-PKG-002) and its Symfony 8 FrankenPHP app are illustrative only.

## Validating the functional spec

```bash
composer test                # PHPUnit RuleTestCase suite
composer phpstan             # PHPStan on the package itself
composer cs-check            # PHP-CS-Fixer (dry run)
composer demo-all            # bad demos must report the expected errors
composer demo-classic-good   # good demos must report zero errors (also demo-worker-good, demo-hardening-good)
make release-check           # full pre-release gate (Docker)
```

Every new or changed rule requires a `RuleTestCase` under `tests/Rule/`, a `bad/` + `good/` demo pair and a `docs/RULES.md` entry.

## Requirement identifiers (REQ-*)

| ID | Location | Meaning |
| --- | --- | --- |
| REQ-GIT-001 | `Makefile` (`check-no-cursor-coauthor`), CI `git-hygiene` job | No AI co-author trailers in history |
| REQ-CS-008 | `Makefile` (`igor`) | Igor worker-state audit |
| REQ-DEMO-007 | `demo/symfony8/Makefile` (`update-bundle`) | Sync the mounted package before demo tests / release-verify |
| REQ-MAKE-008 | `demo/Makefile` | Update Composer dependencies across demos |
| REQ-MAKE-009 | `demo/Makefile` | Optional monorepo include absent on standalone checkouts |
| REQ-MAKE-010 | `demo/Makefile` | Compose V2 / V1 detection |
| REQ-PKG-002 | `composer.json` `archive.exclude` | Demos excluded from the dist archive |

When scripted or demo behavior changes, update the comment next to the existing `REQ-*` or add a new ID and cite it in the PR.

## Contributor workflow

1. Update the spec (`FR-*` rows and acceptance criteria) when behavior or rulesets change.
2. Keep `docs/RULES.md` as the human-facing rule catalog (normative for identifiers).
3. Implement the rule with its `RuleTestCase` and the `bad/` / `good/` demos.
4. Update `code-inventory.md` when files under `src/` are added, moved or removed (see the [maintainer checklist](SPEC-KIT.md#maintainer-checklist)).
5. Prefer small PRs: one rule (or one level) per change when possible; ship `CHANGELOG.md` / `UPGRADING.md` notes.

## Relationship to Engram

[`ENGRAM.md`](ENGRAM.md) is the persistent memory plane (product intent, invariants, useful commands) for assistants and maintainers. It does not define behavior: this document, the Spec Kit baseline and `docs/RULES.md` do. Engram points back here for local `REQ-*` traceability.

## GitHub Spec Kit

The repository is initialized with Specify CLI for **Cursor Agent** (`specify init --here --force --integration cursor-agent --script sh`): skills under `.cursor/skills/speckit-*`, infrastructure under `.specify/`, baseline under `specs/001-baseline/`. The full manual (install, structure, skills, checklist, troubleshooting) is [`SPEC-KIT.md`](SPEC-KIT.md). Keep the baseline inventory aligned with `src/` in every PR that touches production files.

## See also

- [GitHub Spec Kit manual](SPEC-KIT.md)
- [Baseline spec](../specs/001-baseline/spec.md) and [code inventory](../specs/001-baseline/code-inventory.md)
- [Rule catalog](RULES.md), [Usage](USAGE.md), [Configuration](CONFIGURATION.md), [Contributing](CONTRIBUTING.md), [Release](RELEASE.md)
- [FrankenPHP demos](DEMO-FRANKENPHP.md)
