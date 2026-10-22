# Security

## Table of contents

- [Scope](#scope)
- [Reporting](#reporting)
- [Notes for consumers](#notes-for-consumers)
- [Attack surface](#attack-surface)
- [Threat model and mitigations](#threat-model-and-mitigations)
- [Dependencies and updates](#dependencies-and-updates)
- [Release security checklist (12.4.1)](#release-security-checklist-1241)

## Scope

This package is a **dev-time static analyser**. It does not execute analysed application code and does not open network connections at runtime for consumers.

## Reporting

Report vulnerabilities privately via GitHub Security Advisories on [nowo-tech/PhpStanFrankenPhp](https://github.com/nowo-tech/PhpStanFrankenPhp) or email security contacts listed in `.github/SECURITY.md`.

## Notes for consumers

- Findings are advisory for migration readiness; fixing them reduces operational risk under FrankenPHP but is not a substitute for security review.
- Do not commit secrets in demo fixtures or baselines.

## Attack surface

| Input | Where |
| --- | --- |
| Analysed PHP source | PHPStan parses the consumer's code into an AST; the rules under `src/Rule/` only read nodes and scopes |
| NEON configuration | `extension.neon` / `ruleset-*.neon` register the rules and parameters; consumers include them from `phpstan.neon` |
| PHPStan runtime | The extension runs inside the consumer's PHPStan process (CLI, CI), never in production requests |

## Threat model and mitigations

| Threat | Assessment / mitigation |
| --- | --- |
| Code execution from analysed sources | None: rules never `eval`, `include` or call the analysed code; they inspect AST nodes only |
| Network / filesystem side effects | None: rules perform no I/O beyond what PHPStan itself does to read sources |
| Malicious or huge input (DoS) | Bounded by PHPStan's own limits (memory limit, parallel workers); rules are linear in the number of visited nodes |
| False negatives used as a security signal | Documented in [Notes for consumers](#notes-for-consumers): findings are migration-readiness advice, not a security review |
| Supply chain | Installed as `require-dev` only; depends on `phpstan/phpstan` alone at runtime of the analysis |

## Dependencies and updates

`composer audit` runs in CI and in `release-check`; Dependabot PRs are reviewed and merged or closed with a reason (REQ-REL-003). The PHPStan constraint follows the supported major (`^2.0`); security fixes ship as patch releases.

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
| **AI security audit (REQ-SEC-004)** | Latest recorded grade in the nowo-tech bundles security analysis catalog (`BUNDLES_SECURITY_ANALYSIS.md`); re-run when rules gain I/O or new configuration inputs. |

Recommended commands:

```bash
composer audit
make release-check
```
