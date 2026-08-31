# Licensing

**Product:** RC & LB Tools v2.19.1  
**Repository license:** [MIT License](../../LICENSE)  
**Bundled vendor terms:** [EULA.txt](../../EULA.txt) (Informatica Ferraro Software License Agreement)

This document explains how repository licensing and bundled vendor terms relate. It is not legal advice.

## Repository license (MIT)

This project is published under the **MIT License**. You may use, copy, modify, merge, publish, distribute, sublicense, and sell copies of the repository contents, subject to:

- Including the MIT copyright and permission notice in copies or substantial portions
- Accepting the software "as is" without warranty

See [LICENSE](../../LICENSE) for the full text.

## Historical release context

The codebase was maintained in a **private repository for an indefinite initial period** before being open-sourced under MIT. Documentation in this tree was authored during that private phase and updated for the public MIT release and subsequent OSS refactor (removal of runtime license validation).

## Bundled vendor EULA (`EULA.txt`)

The addon drop-in still includes `EULA.txt` from **Informatica Ferraro** (original commercial vendor). That file describes the **original proprietary distribution model** for the WHMCS addon as shipped by resellerclub-mods.com.

| Layer | What it governs | Current posture |
|-------|-----------------|-----------------|
| **MIT (`LICENSE`)** | This repository — source, docs, and redistribution of the published tree | Permissive open source; modification allowed with attribution |
| **EULA.txt (bundled)** | Original Informatica Ferraro commercial software terms as shipped with the addon | Retained for historical/vendor reference only |

**Operator note:** If you obtained the addon directly from Informatica Ferraro / resellerclub-mods.com under the original commercial model, verify which license terms apply to your deployment. The MIT license on **this repository** does not automatically replace separate contractual obligations you may have with the vendor.

## Runtime product licensing — REMOVED (OSS refactor)

Prior to the OSS refactor, the addon enforced **remote license validation** against rcmodules.com via `rcfree_check_license()`, `rcfree_license` config, localkey caching, and FRLBT addon expiry checks. That runtime gate has been **removed** from this MIT tree. **Zero vendor phone-home** is now policy: no HTTP to rcmodules.com, `preverify.php`, `verify.php`, `checkip.php`, or vendor version-check URLs at runtime.

| Former behavior | Current behavior |
|-----------------|------------------|
| `rcfree_license` required in WHMCS config | Field removed from config UI; stale DB rows ignored |
| Remote verify to rcmodules.com on admin load | No remote license or version calls |
| Vendor version check (`rcfreetools_v3.txt`) | Static version from `$softversion` / config only |
| Five duplicate `output()` per license status | Single `resellerclubmods_tools_output()` always |
| Widget gated on license key presence | Widget gated on `fundsbalance` config only |
| Hooks/cron gated on `!empty($rcfree_license)` | Hooks/cron run subject to feature config flags |

See [license-system.md](../deep-dives/license-system.md) for historical documentation of the removed system.

## Guidelines for agents and contributors

Under MIT open-source norms, agents **may**:

- Propose and implement changes to PHP, hooks, cron, widgets, and docs in this repository
- Fork, modify, and redistribute with proper attribution

Agents **must still**:

- **Never log secrets** — reseller API keys or other credentials (reference setting names and file:line only)
- Distinguish **repository license (MIT)** from bundled **EULA.txt** when advising operators

## Secret handling

| Secret type | Storage | Agent rule |
|-------------|---------|------------|
| Reseller API key | `*_rcauth_apikey` settings | Never log value |
| Whois secret | `wid_key` setting | Never log value |

Use placeholders: `YOUR-API-KEY`, `<redacted>`.

## WHMCS configuration

| Action | Notes |
|--------|-------|
| Configure reseller accounts, hook toggles | Expected operation |
| Enable funds widget (`fundsbalance`) | Controls AdminHomeWidget |
| Edit PHP under `modules/`, `includes/hooks/`, etc. | Permitted under MIT |

## Compliance sign-off (OSS refactor)

| Check | Status |
|-------|--------|
| MIT `LICENSE` at repo root | Done |
| Runtime license validation removed from code | Done |
| Zero vendor phone-home (no rcmodules / activation HTTP) | Done |
| AGENTS.md reflects no runtime license gate | Done |
| Historical license docs marked/updated | Done |
| No real secrets in doc tree | Verified |
