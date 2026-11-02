# AGENTS.md — RC & LB Tools v2.19.5

AI agent entry point for **FREE Resellerclub & LogicBoxes Tools** (WHMCS addon). Read this file before investigating or suggesting changes to this repository.

## Hard constraints

1. **MIT open source** — This repository is published under the [MIT License](LICENSE). Modification, distribution, and private use are permitted with attribution. See [docs/compliance/eula-and-licensing.md](docs/compliance/eula-and-licensing.md) for the relationship between MIT and bundled vendor [EULA.txt](EULA.txt).
2. **No runtime license gate** — As of the OSS refactor, there is no `rcfree_check_license()`, no rcmodules.com license validation, and no feature gating on `rcfree_license`. Features are controlled by addon config flags (e.g. `fundsbalance`, per-account hook toggles) and reseller account setup only.
3. **Zero vendor phone-home** — Runtime PHP must not HTTP to `rcmodules.com`, license activation endpoints (`preverify.php`, `verify.php`, `checkip.php`), or vendor version-check URLs. Version is shown from in-code `$softversion` / `$releasedate` only. Operator-configured LogicBoxes API calls via `call_api()` are not vendor phone-home.
4. **Plaintext PHP only** — No proprietary bytecode packaging; all product source is readable PHP under MIT. Do not reintroduce Loader API probes or ship non-plaintext PHP.
5. **Never log secrets** — Do not paste reseller API keys or other credentials into chat, docs, commits, or audit entries. Reference setting names and file:line only. Module debug logging must use `rcm_log_module_call()` (never raw `logModuleCall` with credentials/tokens).
6. **Cron is CLI-only** — `cron/*.php` must return HTTP 403 when `PHP_SAPI` is not `cli` before `require` of `init.php`, and must still call `rcm_deny_direct_http()` after bootstrap. No HTTP cron or `cron_access_key` backdoor. Admin bulk price sync posts to the addon with CSRF → runners.

## Product identity

| Field | Value |
|-------|-------|
| Module name | `resellerclubmods_tools` |
| Display name | RC & LB Tools v2 |
| Version | **2.19.5** |
| Release date | 2026-10-25 (`incs/functions.php` and the addon config `version`) |
| PHP | 7.4–8.4 syntax floor. CI keeps floating 7.4, 8.0, and 8.1, and pins 8.2.35, 8.3.36, and 8.4.27 (`.github/workflows/php-compat.yml`) |
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
│   ├── incs/functions.php                   # call_api(), helpers (requires security.php)
│   ├── incs/security.php                    # CSRF/XSS/log/funds/cron helpers
│   ├── incs/runners/*_runner.php            # Price sync / transfercheck bodies
│   ├── tools/*.php                          # Admin tool pages (included by router)
│   └── cron/*.php                           # CLI-only cron entrypoints (HTTP 403)
├── includes/hooks/resellerclubmods_*.php    # WHMCS event hooks (10 files)
└── widgets/domainpricelist.php              # Client-area pricing widget JS
```

Admin entry URL: `{admin}/addonmodules.php?module=resellerclubmods_tools`

## Reading order

1. [docs/README.md](docs/README.md) — Human index and executive summary
2. [docs/compliance/eula-and-licensing.md](docs/compliance/eula-and-licensing.md) — MIT and vendor EULA
3. [docs/architecture/overview.md](docs/architecture/overview.md) — Layout, routing, DB tables
4. [docs/evidence/system-map.md](docs/evidence/system-map.md) — Cross-component dependency / blast-radius map
5. [docs/deep-dives/license-system.md](docs/deep-dives/license-system.md) — **Historical:** former runtime license (removed)
6. [docs/deep-dives/admin-home.md](docs/deep-dives/admin-home.md) — Dashboard panels and health checks
7. [docs/deep-dives/api-calls.md](docs/deep-dives/api-calls.md) — `call_api()`, endpoints, logging
8. [docs/correlation/gate-matrix.md](docs/correlation/gate-matrix.md) — Feature config gates (post-OSS)
9. [docs/architecture/diagrams.md](docs/architecture/diagrams.md) — Mermaid flows
10. [docs/evidence/verification-index.md](docs/evidence/verification-index.md) — Grep evidence
11. [docs/evidence/findings-2.19.3.md](docs/evidence/findings-2.19.3.md) — Security findings RCM-001…019

## File map (high-signal)

| Subsystem | Primary files |
|-----------|---------------|
| Admin router | `resellerclubmods_tools.php` — single `resellerclubmods_tools_output()` |
| Admin home UI | `tools/home.php` |
| Security kernel | `incs/security.php` — CSRF/XSS/redaction/funds/cron helpers |
| Funds widget | `hooks.php` — gated by `fundsbalance`; uses `rcm_compute_funds` |
| API wrapper | `incs/functions.php` (`call_api`; TLS verify; ERROR on curl/JSON fail) |
| Debug helpers | `getDebuginfos()` + `rcm_log_module_call()` only |
| WHMCS hooks | `includes/hooks/resellerclubmods_*.php` (10 files) |
| Cron | `cron/*.php` CLI-only → `incs/runners/*_runner.php` |
| Client widget | `widgets/domainpricelist.php` |
| Standalone endpoints | `tools/authlogin.php` (fail-closed SSO), `tools/whois.php` (HMAC wid) |
| Verify gate | `scripts/verify-security.sh`, `scripts/funds_math_assert.php` |

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
| `tbladdonmodules` | WHMCS addon settings (**canonical API keys**) |
| `mod_resellerclubmodstools` | Active account selection (`rcauth_password` cleared; `localkey`/`lastcheck` retained for upgrade compat) |
| `mod_resellerclubmodsfunds` | Funds threshold snapshot (unique `reseller_id`) |
| `mod_resellerclubmodspromo` | Promo sync state |
| `mod_resellerclubmodsraa` | RAA resend tracking |
| `mod_resellerclubmodstransfer` | Transfer-check tracking |

## External references

- Requirements / install / changelog: [README.txt](README.txt) → resellerclub-mods.com (documentation links only; no runtime callbacks)
- Version display: `incs/functions.php` (`$softversion`, `$releasedate`) — no remote version check

## Learned User Preferences

- Prefer permanent root-cause fixes over temporary workarounds; do not rewrite working code without a concrete defect or security reason.
- When CI is green and the user asks to merge, land work on `main` and clean up leftover feature branches.
- For Dokploy/WHMCS production deploys, ask when unsure and consult `~/.ai-audit` for prior deployment learnings.
- Treat MIT open-source releases as drop-in replacements for prior encoded installs (no breakage for existing operators).

## Learned Workspace Facts

- Canonical GitHub repo is `yashodhank/whmcs-free-resellerclubmods` (route `gh` via account `yashodhank`).
- Addon tools pagination class is `RcmToolsPagination` in `incs/pagination.php`; do not name it `rcm_pagination` — WHMCS Core’s stub class lacks `links()` and fatals Domain Import and related tools.
- Ship WHMCS drop-in module zips under `dist/` for releases.
- 2.19.5 request limits (userland only; they do not patch the PHP engine): WHOIS reads stop at 256KB with an 8s deadline and 3s per socket; raw domain input is capped at 253 bytes before `idna_convert`; `authlogin.php` `tid` is capped at 2048 bytes; public `whois.php` and `authlogin.php` pass a 5s curl timeout into `call_api()`; the `call_api()` default stays 120s; Domain Import stops above 3000 posted customer ids and does not tell operators to raise `memory_limit`. Admin home shows an English warning when `PHP_VERSION` is outside 8.2.35 / 8.3.36 / 8.4.27 / 8.5.12 (8.5.x at or after 8.5.12 is quiet; future majors warn). The warning does not disable features. See [docs/evidence/php2shell.md](docs/evidence/php2shell.md).

### Dokploy bake path (do not run from a module-only PR)

1. Land the module on `yashodhank/whmcs-free-resellerclubmods` `main` (`gh-account` account `yashodhank`). Run `gh-account preflight` before any GitHub write.
2. Bake into `securiace-dev/whmcs-prod-new` (same account, org repo). Package `resellerclubmods_tools` has no `sync.sourceRepo`, so `scripts/sync-overlay-package.sh` will not refresh it. Copy the addon tree and these ten hook files by name: `domainrecurring`, `fundthreshold`, `nameservers`, `promoactivate`, `promoupdate`, `raadomainreport`, `rcchangepwd`, `rcdeleteclient`, `rcmodifyclient`, `rcsignup`. Do not `--delete` `includes/hooks/`; `resellerclubmods_navbar_compat.php` exists only in the overlay. Copy `widgets/domainpricelist.php` separately (catalog id `domainpricelist_widget`). Update `overlays/modules/addons/resellerclubmods_tools/.overlay-source-sha` (2.19.4 pin `9dab25976b263b8bcba550262b85451eefeba395`).
3. Overlay PR to `whmcs-prod-new` `main`. Wait until `production-image` and `release-identity` are green. Deploy the installer-removed `ghcr.io/securiace-dev/whmcs-prod-new@sha256:…` from artifact `whmcs-published-release-identity`.
4. Dokploy compose `L_GzFCnhhpQfZi9T4MECD` on ded1 (`my.securiace.com`): back up the panel env, `compose.update` only `WHMCS_IMAGE` / `VCS_REF` / `BUILD_DATE`, pre-pull, `compose.deploy`. Prove digest, `VCS_REF`, and both version strings. Host cron `/etc/cron.d/whmcs-rcm-resellerclubmods` must survive. `whois.json` is runtime state; the 2026-10-09 image seeds it if missing.
5. PHP upgrade is a different change in that repo: replace the `php:8.3-apache` digest with a build whose `PHP_VERSION` is `>= 8.3.36`, and set `session.upload_progress.enabled=0` until that image is the one actually running. Optionally reject `multipart/form-data` and apply `LimitRequestBody` at Apache for the public scripts. Do not set `memory_limit=-1` from this module.

Last tools bake before 2.19.5: overlay PR #105, image `sha256:5e4df068e518f931388fea357a6c015c40dcce256baa1b6e70cf4e011bc6c88d`, `softversion` 2.19.4 (ledger `20261009-121447Z`). Live base pin at the time of this note: `php:8.3-apache@sha256:bcf7ac6941725b08123df2732065b8ede097ed0977ba2891b411654efb35cc23`.
