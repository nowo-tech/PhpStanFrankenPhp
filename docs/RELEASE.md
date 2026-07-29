# Release

Current stable: **v1.0.2** (2026-07-29).

## Checklist

1. `make setup-hooks` (once per clone).
2. `make release-check` (QA, coverage gate, fixture demos, Symfony 8 smoke).
3. Move notes from `[Unreleased]` into a new version section in [`CHANGELOG.md`](CHANGELOG.md); refresh [`UPGRADING.md`](UPGRADING.md) if consumers must act.
4. Commit release changes (no Cursor co-author trailers — REQ-GIT-001).
5. `make check-no-cursor-coauthor` (after the release commit, before push).
6. Annotated tag and push:
   ```bash
   git tag -a v1.0.2 -m "Release v1.0.2 - FrankenPHP banner, Make demos/smoke, security checklist"
   git push origin main
   git push origin v1.0.2
   ```
7. GitHub Actions [`release.yml`](../.github/workflows/release.yml) creates the GitHub Release from the tag and changelog section.

Never include Cursor co-author trailers (REQ-GIT-001).
