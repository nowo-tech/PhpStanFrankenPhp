## AI contribution guidelines (Nowo PHPStan extension)

Use this when suggesting code, tests, documentation, or CI changes for this repository.

### Scope

- This is a **PHPStan extension** (`phpstan-extension`), not a Symfony bundle.
- Published as `nowo-tech/phpstan-frankenphp` on Packagist.
- Respect **PHP** and **PHPStan** version ranges declared in `composer.json`.

### Domain

- Static analysis rules that detect code unsafe when migrating from PHP-FPM to **FrankenPHP** classic mode, then worker mode, with optional hardening rules.
- Rules are grouped in **classic**, **worker**, and **hardening** rulesets under `rules/` and `ruleset-*.neon`.
- Each rule needs a stable `frankenphp.*` identifier, docs entry in `docs/RULES.md`, and demo fixtures under `demo/{classic,worker,hardening}/{bad,good}`.

### Code

- Follow **PSR-12** and project conventions in `.php-cs-fixer.dist.php`.
- Use **strict comparison** (`===`) where appropriate.
- Keep changes **minimal** and consistent with existing patterns in `src/Rule/` and `tests/Rule/`.
- Align with `composer cs-check`, `composer phpstan`, and `composer test` expectations.

### Documentation

- User-facing documentation is **English** under `docs/` per Nowo standards.
- Only `README.md` at repository root (no extra root markdown files).

### Tests

- Add or update `RuleTestCase` coverage for new rules; run `composer demo-*-good` and `composer demo-*` on bad fixtures.

### Git

Never add Cursor co-author trailers to commits. See `docs/GITHUB_CI.md`.
