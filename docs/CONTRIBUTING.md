# Contributing

## Table of contents

- [Code of Conduct](#code-of-conduct)
- [Setup](#setup)
- [Adding a rule](#adding-a-rule)
- [Quality gates](#quality-gates)
- [Git](#git)
- [AI co-author trailers](#ai-co-author-trailers)
- [Cursor Agent (REQ-IDE-006)](#cursor-agent-req-ide-006)

## Code of Conduct

This project follows the [Contributor Covenant Code of Conduct](../CODE_OF_CONDUCT.md). By participating, you are expected to uphold it.

## Setup

```bash
make setup-hooks
make ensure-up
make test
```

## Adding a rule

1. Choose the level (`Classic`, `Worker`, or `Hardening`) and justify it in `docs/RULES.md`.
2. Implement `src/Rule/<Level>/YourRule.php` with a stable `frankenphp.<level>.*` identifier.
3. Register the service in `rules/<level>.neon`.
4. Add fixtures under `tests/Fixtures/<Level>/` and a `RuleTestCase`.
5. Mirror bad/good samples under `demo/<level>/`.
6. Update `docs/RULES.md`, `demo/README.md`, and `docs/CHANGELOG.md`. Prefer checking [ROADMAP.md](ROADMAP.md) before inventing new rule families.

## Quality gates

```bash
make cs-fix
make phpstan
make test
make demo-all
make demo-classic-good demo-worker-good demo-hardening-good
```

## Git

- Run `make setup-hooks` so `commit-msg` strips Cursor co-author trailers (REQ-GIT-001).
- Run `make igor` for the Igor FrankenPHP worker-state audit (REQ-CS-008).
- After commits that will be pushed: `make check-no-cursor-coauthor`.
- If CI fails because trailers are already on the remote, run `make strip-cursor-coauthor-from-history` before `git push --force-with-lease`. See [GITHUB_CI.md](GITHUB_CI.md).

## AI co-author trailers

Do **not** add AI co-author trailers to commit messages: neither `Co-authored-by: Cursor` / `cursoragent@cursor.com` nor `Co-Authored-By: Claude …` / `noreply@anthropic.com` (REQ-GIT-001). Run `make setup-hooks` so the `commit-msg` hook strips them.

## Cursor Agent (REQ-IDE-006)

Contributors using Cursor Agent on this repository should:

1. **Submit with Ctrl+Enter** (macOS: **⌘+Enter**). **Enter** inserts a newline; **Ctrl+Enter** / **⌘+Enter** submits the prompt. UI: **Cursor Settings → Agents / Chat → Submit with Ctrl + Enter**.
2. **Queue follow-ups** while the agent is running. In user `settings.json` (not a repo file):

```json
{
  "cursor.composer.queueMessageDefaultBehavior": "queue"
}
```

Do **not** use `"steer"` as the org default: steer interrupts the running agent as soon as a follow-up is sent.
