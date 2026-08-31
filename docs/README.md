# RC & LB Tools — Developer Documentation

**Product:** FREE Resellerclub & LogicBoxes Tools **v2.19.2** (release 2026-09-01)  
**Module ID:** `resellerclubmods_tools`

## Executive summary (SCQA)

**Situation:** WHMCS installations use RC & LB Tools to manage ResellerClub/LogicBoxes accounts, but the addon shipped without in-repo developer documentation—only external guides at resellerclub-mods.com.

**Complication:** Feature paths use **config flags** (funds widget, hook toggles, cron settings) rather than a single centralized gate.

**Question:** How should agents and integrators analyze, modify, and troubleshoot the addon under MIT open source?

**Answer:** This documentation pack. Start at [AGENTS.md](../AGENTS.md) and use the [gate matrix](correlation/gate-matrix.md) for feature-flag correlation.

**Policy:** **Zero vendor phone-home** — runtime PHP does not HTTP to rcmodules.com, license activation endpoints, or vendor version-check URLs. Version is in-code only (`$softversion` in `incs/functions.php`).

## Reading order

1. [AGENTS.md](../AGENTS.md) — MIT constraints, file map, config gates
2. [compliance/eula-and-licensing.md](compliance/eula-and-licensing.md) — MIT and vendor EULA
3. [architecture/overview.md](architecture/overview.md) — Module layout and routing
4. [deep-dives/license-system.md](deep-dives/license-system.md) — Historical: former runtime license
5. [deep-dives/admin-home.md](deep-dives/admin-home.md) — Dashboard panels and health checks
6. [deep-dives/api-calls.md](deep-dives/api-calls.md) — `call_api()`, endpoints, logging
7. [correlation/gate-matrix.md](correlation/gate-matrix.md) — Feature config gates (post-OSS)
8. [architecture/diagrams.md](architecture/diagrams.md) — Mermaid flows
9. [evidence/verification-index.md](evidence/verification-index.md) — Grep evidence and READY verdict

Supporting: [PRD.md](PRD.md) (documentation initiative scope).

## Quick links

| Topic | Doc |
|-------|-----|
| Funds widget not showing | [gate-matrix.md](correlation/gate-matrix.md) — `fundsbalance` config |
| Feature hook disabled | [gate-matrix.md](correlation/gate-matrix.md) — per-account flags |
| API endpoint list | [api-calls.md § Endpoint inventory](deep-dives/api-calls.md#endpoint-inventory) |
| Currency mismatch loop | [admin-home.md § Currency mismatch](deep-dives/admin-home.md#currency-mismatch-session-loop) |
| Licensing (MIT + vendor EULA) | [compliance/eula-and-licensing.md](compliance/eula-and-licensing.md) |
| Zero vendor phone-home policy | [AGENTS.md](../AGENTS.md), [compliance/eula-and-licensing.md](compliance/eula-and-licensing.md) |

## External canonical documentation

Per [README.txt](../README.txt):

| Topic | URL |
|-------|-----|
| Requirements | https://www.resellerclub-mods.com/whmcs/resellerclub-tools-docs.php |
| Install / update | Same (Howto Install section) |
| Changelog | Same (Changelog section) |
| Client area / licensing | https://www.resellerclub-mods.com/whmcs/clientarea.php |
| WHMCS Marketplace | https://marketplace.whmcs.com/product/534 |

In-repo docs supplement—not replace—vendor documentation. When versions diverge, trust `incs/functions.php` (`$softversion = "2.19.2"`) and vendor changelog.

## Compatibility matrix

| Component | Supported |
|-----------|-----------|
| PHP | 7.4, 8.0, 8.1, 8.2, 8.3, 8.4 |
| WHMCS | 8.x, 9.x |
| ionCube Loader | Not required (plaintext MIT OSS) |

CI runs `php -l` across all PHP files on PHP 7.4–8.4 (see `.github/workflows/php-compat.yml`).

## Repository layout

```
AGENTS.md
docs/
├── README.md              ← you are here
├── PRD.md
├── architecture/
├── deep-dives/
├── correlation/
├── compliance/
└── evidence/
```

## Version note

All documentation in this tree targets **v2.19.2**. Re-verify grep counts and line citations after upgrading the addon.

## Repository license

This project is open source under the [MIT License](../LICENSE). Bundled [EULA.txt](../EULA.txt) reflects original Informatica Ferraro commercial terms; see [compliance/eula-and-licensing.md](compliance/eula-and-licensing.md). Runtime license validation and vendor phone-home (rcmodules.com, activation endpoints, version-check URLs) were removed in the OSS refactor.

## Documentation initiative

Delivered under NEXUS-Sprint scope (initially docs-only during private development). See [PRD.md](PRD.md) and [evidence/verification-index.md](evidence/verification-index.md) for acceptance criteria and Reality Checker **READY** verdict.
