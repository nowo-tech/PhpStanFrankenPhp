# Security

## Scope

This package is a **dev-time static analyser**. It does not execute analysed application code and does not open network connections at runtime for consumers.

## Reporting

Report vulnerabilities privately via GitHub Security Advisories on [nowo-tech/PhpStanFrankenPhp](https://github.com/nowo-tech/PhpStanFrankenPhp) or email security contacts listed in `.github/SECURITY.md`.

## Notes for consumers

- Findings are advisory for migration readiness; fixing them reduces operational risk under FrankenPHP but is not a substitute for security review.
- Do not commit secrets in demo fixtures or baselines.

## Release security checklist (12.4.1)

Before tagging a release, confirm:

| Item | Notes |
|------|-------|
| **`docs/SECURITY.md`** | This document is current and matches package behavior. |
| **`.github/SECURITY.md`** | Public policy present and product name is correct. |
| **No committed secrets** | No tokens, private keys, or real `.env` values in tracked files / fixtures. |
| **Recipe / demo config** | Demo apps use placeholders; `.env` gitignored. |
| **Input / output** | PHPStan rules analyse AST only; no eval of analysed code. |
| **Dependencies** | `composer audit` run and findings triaged (incl. PHPStan). |
| **Logging** | N/A for the extension itself; demos must not log secrets. |
| **Cryptography** | N/A. |
| **Permissions / exposure** | Package is `require-dev` only for consumers; demos are not production. |
| **Limits / DoS** | Analysis memory limits documented in demo scripts / README. |
| **Release notes** | Security-relevant changes reflected in `CHANGELOG.md` / `UPGRADING.md` when needed. |

Recommended commands:

```bash
composer audit
make release-check
```
