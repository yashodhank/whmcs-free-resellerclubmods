# Architecture Overview

**Version:** 2.19.1 | **Module:** `resellerclubmods_tools`

## Purpose

RC & LB Tools is a WHMCS addon that:

1. Exposes an admin dashboard with reseller account health, version info, and tool links
2. Registers WHMCS hooks and cron jobs for ResellerClub / LogicBoxes automation
3. Calls the LogicBoxes HTTP API via a shared `call_api()` wrapper

Runtime license validation against rcmodules.com was **removed** in the OSS refactor. **Zero vendor phone-home** is enforced: no HTTP to rcmodules.com, activation endpoints, or vendor version-check URLs at runtime. See [license-system.md](../deep-dives/license-system.md) (historical).

## Module layout

```
whmcs-free-resellerclubmods/
├── EULA.txt, README.txt, LICENSE
├── modules/addons/resellerclubmods_tools/
│   ├── resellerclubmods_tools.php    # Bootstrap, single output router, WHMCS addon API
│   ├── hooks.php                     # AdminHomeWidgets, ClientAreaPrimarySidebar
│   ├── incs/
│   │   ├── functions.php             # call_api, signup, helpers
│   │   ├── whoislookup.php
│   │   └── idnclass.php
│   ├── tools/                        # Admin pages (included, not routed URLs)
│   ├── cron/                         # CLI scripts (transfer check, price sync)
│   └── lang/                         # 24 language files
├── includes/hooks/                   # 10 WHMCS event hook files
└── widgets/domainpricelist.php       # Client-area JS pricing widget
```

## Entry points

| Entry | Trigger | Gate behavior |
|-------|---------|---------------|
| Admin addon | `addonmodules.php?module=resellerclubmods_tools` | Account setup required |
| Admin home widget | WHMCS `AdminHomeWidgets` hook | `fundsbalance` config |
| WHMCS hooks | Client/registrar/cron events | Feature config flags |
| Cron scripts | CLI / web cron | Account + per-account flags |
| `authlogin.php` | Direct HTTP (auto-login) | Account setup |
| `whois.php` | Direct HTTP (API whois) | `wid` HMAC gate |
| `domainpricelist.php` | Client order form JS | Always (when configured) |

## Bootstrap sequence

On every load of `resellerclubmods_tools.php`:

1. Load helpers (`functions.php`, `idnclass.php`)
2. Require `clientareatools.php`
3. Define single `resellerclubmods_tools_output()` and `resellerclubmods_tools_sidebar()`

No license read, no remote validation, no status switch.

## Routing table

`resellerclubmods_tools_output()` routes via `$_GET` query params:

| Query | Tool file |
|-------|-----------|
| `trans=showfunds` | `tools/fundsbalance.php` |
| `domain=domain-pricing-import` | `tools/importdompricing.php` |
| `domain=domainimport` | `tools/importdomains.php` |
| `domain=showpromos` | `tools/managepromos.php` |
| `user=whmcsuser-vs-rcusers` | `tools/exportusers.php` |
| `user=rcuser-vs-whmcsusers` | `tools/importusers.php` |
| `domain=transfercheck` | `tools/transfercheck.php` |
| `domain=moveservices` | `tools/movedomain.php` |
| `domain=bulkmove` | `tools/bulkdomainmove.php` |
| `domain=tldmanage` | `tools/tldmanage.php` |
| `automation=cronjobs` | `tools/automationtools.php` |
| `domain=raamanagement` | `tools/raamanagement.php` |
| `domain=rcmwhois` | `tools/guiwhois.php` or `gui7whois.php` (WHMCS version) |
| *(default)* | `tools/home.php` |

Before routing, `output()` calls `/api/resellers/details.json` for the selected account to populate currency and connection state consumed by `home.php`.

## Hook split

### Addon hooks (`modules/.../hooks.php`)

- `AdminHomeWidgets` → `widget_resellerclubmods_tools()` — funds balance widget (gated by `fundsbalance`)
- `ClientAreaPrimarySidebar` — "Move Domain" link when configured

### Root hooks (`includes/hooks/resellerclubmods_*.php`)

| File | WHMCS hook | Feature |
|------|------------|---------|
| `rcsignup.php` | `ClientAdd` | Auto-create RC customer |
| `rcmodifyclient.php` | `ClientEdit` | Sync client changes |
| `rcchangepwd.php` | `ClientChangePassword` | Sync password |
| `rcdeleteclient.php` | `ClientDelete` | Delete RC customer |
| `fundthreshold.php` | Registrar/module after-hooks | Low balance alert |
| `domainrecurring.php` | `DailyCronJob` | Update domain recurring |
| `nameservers.php` | `PreDomainRegister` | NS override |
| `promoactivate.php` | `DailyCronJob` | Auto-activate promos |
| `promoupdate.php` | `DailyCronJob` | Promo price updates |
| `raadomainreport.php` | `DailyCronJob` | RAA domain report |

Each file loads addon config and respects feature-specific disable flags (no license gate).

## Database tables

### `tbladdonmodules` (WHMCS core)

Stores all addon settings. Critical keys:

- `rchttp_api` — LogicBoxes API base URL (default `https://httpapi.com`)
- `first_*` … `fourth_*` — up to four reseller accounts
- Feature toggles: `fundsbalance`, `promo_end_check`, `update_domain_recurring`, etc.

Legacy `rcfree_license` rows may exist from upgrades; ignored by code.

### `mod_resellerclubmodstools`

| Column | Purpose |
|--------|---------|
| `localkey`, `lastcheck` | Legacy license cache (retained for upgrade compat; not written) |
| `account_name`, `account_number` | Active reseller account selection |
| `rcauth_userid`, `rcauth_password` | Active account credentials (denormalized) |
| `logicbox_registrar`, `defaultcurrency`, `currencyswitch`, `multiplicator` | Active account metadata |

### `mod_resellerclubmodspromo` / `mod_resellerclubmodstransfer`

Promo sync and transfer-check state tables.

## WHMCS addon API functions

| Function | Role |
|----------|------|
| `resellerclubmods_tools_config()` | Addon settings UI |
| `resellerclubmods_tools_activate()` | DB migrations |
| `resellerclubmods_tools_output()` | Admin page router |
| `resellerclubmods_tools_sidebar()` | Admin sidebar |

## External services

| URL | Purpose |
|-----|---------|
| `{rchttp_api}/api/...` | LogicBoxes API (operator-configured registrar HTTP API) |

No runtime calls to rcmodules.com, license activation endpoints, or vendor version-check URLs.

## Related docs

- [diagrams.md](diagrams.md) — Visual flows
- [../deep-dives/license-system.md](../deep-dives/license-system.md) — Historical
- [../correlation/gate-matrix.md](../correlation/gate-matrix.md)
