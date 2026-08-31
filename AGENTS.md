# AGENTS.md — RC & LB Tools v2.19.2

AI agent entry point for **FREE Resellerclub & LogicBoxes Tools** (WHMCS addon). Read this file before investigating or suggesting changes to this repository.

## Hard constraints

1. **MIT open source** — This repository is published under the [MIT License](LICENSE). Modification, distribution, and private use are permitted with attribution. See [docs/compliance/eula-and-licensing.md](docs/compliance/eula-and-licensing.md) for the relationship between MIT and bundled vendor [EULA.txt](EULA.txt).
2. **No runtime license gate** — As of the OSS refactor, there is no `rcfree_check_license()`, no rcmodules.com license validation, and no feature gating on `rcfree_license`. Features are controlled by addon config flags (e.g. `fundsbalance`, per-account hook toggles) and reseller account setup only.
3. **Zero vendor phone-home** — Runtime PHP must not HTTP to `rcmodules.com`, license activation endpoints (`preverify.php`, `verify.php`, `checkip.php`), or vendor version-check URLs. Version is shown from in-code `$softversion` / `$releasedate` only. Operator-configured LogicBoxes API calls via `call_api()` are not vendor phone-home.
4. **Plaintext PHP only** — No proprietary bytecode packaging; all product source is readable PHP under MIT. Do not reintroduce Loader API probes or ship non-plaintext PHP.
5. **Never log secrets** — Do not paste reseller API keys or other credentials into chat, docs, commits, or audit entries. Reference setting names and file:line only.

## Product identity

| Field | Value |
|-------|-------|
| Module name | `resellerclubmods_tools` |
| Display name | RC & LB Tools v2 |
| Version | **2.19.2** |
| Release date | 2026-09-01 (`incs/functions.php:5`) |
| PHP | 7.4–8.4 (CI matrix in `.github/workflows/php-compat.yml`) |
| WHMCS | 8.x / 9.x |
| Repository license | MIT ([LICENSE](LICENSE)) |
| Canonical external docs | https://www.resellerclub-mods.com/whmcs/resellerclub-tools-docs.php |

## WHMCS install paths

After deployment into a WHMCS root (`ROOTDIR`):

```
ROOTDIR/
├── modules/addons/resellerclubmods_tools/   # Addon core (routing, tools)
│   ├── resellerclubmods_tools.php           # Single output router (no license gate)
│   ├── hooks.php                            # AdminHomeWidgets + client sidebar
│   ├── incs/functions.php                   # call_api(), helpers
│   ├── tools/*.php                          # Admin tool pages (included by router)
│   └── cron/*.php                           # CLI cron scripts
├── includes/hooks/resellerclubmods_*.php    # WHMCS event hooks (10 files)
└── widgets/domainpricelist.php              # Client-area pricing widget JS
```

Admin entry URL: `{admin}/addonmodules.php?module=resellerclubmods_tools`

## Reading order

1. [docs/README.md](docs/README.md) — Human index and executive summary
2. [docs/compliance/eula-and-licensing.md](docs/compliance/eula-and-licensing.md) — MIT and vendor EULA
3. [docs/architecture/overview.md](docs/architecture/overview.md) — Layout, routing, DB tables
4. [docs/deep-dives/license-system.md](deep-dives/license-system.md) — **Historical:** former runtime license (removed)
5. [docs/deep-dives/admin-home.md](deep-dives/admin-home.md) — Dashboard panels and health checks
6. [docs/deep-dives/api-calls.md](deep-dives/api-calls.md) — `call_api()`, endpoints, logging
7. [docs/correlation/gate-matrix.md](correlation/gate-matrix.md) — Feature config gates (post-OSS)
8. [docs/architecture/diagrams.md](docs/architecture/diagrams.md) — Mermaid flows
9. [docs/evidence/verification-index.md](docs/evidence/verification-index.md) — Grep evidence

## File map (high-signal)

| Subsystem | Primary files |
|-----------|---------------|
| Admin router | `resellerclubmods_tools.php` — single `resellerclubmods_tools_output()` |
| Admin home UI | `tools/home.php` |
| Funds widget | `hooks.php` — gated by `fundsbalance` config only |
| API wrapper | `incs/functions.php:470-492` (`call_api`; TLS verify enabled) |
| Debug helpers | `getDebuginfos()` — WHMCS/PHP/module metadata only (plaintext OSS; no Loader probes) |
| WHMCS hooks | `includes/hooks/resellerclubmods_*.php` (10 files) |
| Cron | `cron/resellerclubmods_transfercheck.php`, `cron/resellerclubmods_dompricesync.php` |
| Client widget | `widgets/domainpricelist.php` |
| Standalone endpoints | `tools/authlogin.php`, `tools/whois.php` |

## Key diagnostic patterns

### “Funds widget not showing”

- Check **Funds Balance Widget** toggle: `fundsbalance` in addon config (`hooks.php`).
- Per-account **Disable Fundsbalance on Widget** flags (`*_show_fundsbalance`) suppress individual accounts.

### “Admin tools page empty / no account”

- First reseller account must be configured: `rchttp_api`, `first_rcauth_userid`, `first_rcauth_apikey`, `first_acc_name`.
- `mod_resellerclubmodstools` row is created on config save when those fields are set (no license key required).

### “Hook not firing”

- Check per-account disable flags (e.g. `first_hook_signup == on` disables signup sync).
- Global flags: `promo_auto_activate`, `promo_end_check`, `update_domain_recurring`, etc.

## Hook split

| Location | Role |
|----------|------|
| `modules/addons/resellerclubmods_tools/hooks.php` | Registered via addon; `AdminHomeWidgets`, `ClientAreaPrimarySidebar` |
| `includes/hooks/resellerclubmods_*.php` | WHMCS root hooks; auto-loaded on events |

Both families read `tbladdonmodules` for addon settings independently.

## Database tables

| Table | Purpose |
|-------|-------|
| `tbladdonmodules` | WHMCS addon settings |
| `mod_resellerclubmodstools` | Active account selection (`localkey`/`lastcheck` columns retained for upgrade compat; no longer written) |
| `mod_resellerclubmodspromo` | Promo sync state |
| `mod_resellerclubmodstransfer` | Transfer-check tracking |

## graphify (optional)

If `graphify-out/graph.json` exists, run `graphify query "<question>"` before broad grepping. After doc-only changes, `graphify update .` is optional.

## External references

- Requirements / install / changelog: [README.txt](README.txt) → resellerclub-mods.com (documentation links only; no runtime callbacks)
- Version display: `incs/functions.php` (`$softversion`, `$releasedate`) — no remote version check
