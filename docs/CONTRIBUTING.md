# Contributing

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
6. Update `docs/RULES.md`, `demo/README.md`, and `docs/CHANGELOG.md`.

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
- After commits that will be pushed: `make check-no-cursor-coauthor`.
