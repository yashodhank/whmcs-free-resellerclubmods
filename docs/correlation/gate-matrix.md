# Feature Gate Correlation Matrix (Post-OSS)

**Version:** 2.19.3  
**Updated:** Security hardening — CLI-only cron; authlogin fail-closed; whois HMAC wid

Cross-reference of feature enablement: config flags, API calls on load, and behavior when prerequisites are missing.

## Legend

| Pattern | Meaning |
|---------|---------|
| **Config flag** | Feature controlled by a WHMCS addon setting |
| **Account setup** | Requires configured reseller ID + API key |
| **Always on** | No license or special gate; runs when invoked |

## Critical notes (post-OSS)

1. **No `rcfree_license` gate** — All former `!empty($rcfree_license)` checks removed.
2. **Single admin router** — One `resellerclubmods_tools_output()`; no Invalid/Expired/Suspended variants.
3. **Funds widget** — Controlled by `fundsbalance` config only (`hooks.php`).
4. **Cron** — Runs when invoked via CLI; no license bypass needed.

---

## Main module

| Component | Gate pattern | Config gates | API calls on load | Blocked when |
|-----------|--------------|--------------|-------------------|--------------|
| Bootstrap | Always loads | — | — | — |
| `resellerclubmods_tools_output()` | Always registered | Account in `mod_resellerclubmodstools` | `/api/resellers/details.json` | Missing account setup |
| `resellerclubmods_tools_sidebar()` | Always registered | — | — | — |
| Config UI | Always available | Reseller fields for account seed | — | — |

---

## Addon hooks (`modules/.../hooks.php`)

| Component | Gate pattern | Config gates | API calls on load | Blocked when |
|-----------|--------------|--------------|-------------------|--------------|
| `widget_resellerclubmods_tools` | `fundsbalance` | `*_show_fundsbalance != on` per account | Up to 4× `details.json` + 4× `reseller-balance.json` | Widget disabled or no accounts |
| `ClientAreaPrimarySidebar` | **No license check** | `allow_movedomain`, `movedomain_permission` | None | Move link only |

---

## WHMCS root hooks (`includes/hooks/`)

| Component | Gate pattern | Config gates | API calls on trigger | Blocked when |
|-----------|--------------|--------------|----------------------|--------------|
| `resellerclubmods_rcsignup.php` | Always runs hook body | Per-account `*_hook_signup != on` | `customers/details.json` | Signup disabled per account |
| `resellerclubmods_rcmodifyclient.php` | Always runs hook body | `*_hook_modify != on` | `details.json`, `modify.json` | Modify disabled |
| `resellerclubmods_rcchangepwd.php` | Always runs hook body | `*_pwd_modify == on` | `details.json`, `change-password.json` | Pwd sync disabled |
| `resellerclubmods_rcdeleteclient.php` | Always runs hook body | `*_hook_delete != on` | `details.json`, `delete.json` | Delete disabled |
| `resellerclubmods_fundthreshold.php` | Always runs hook body | `*_threshold != on` per account | `details.json`, `reseller-balance.json` | Threshold disabled |
| `resellerclubmods_domainrecurring.php` | Config flag | `update_domain_recurring == on` | WHMCS DB only | Flag off |
| `resellerclubmods_nameservers.php` | Config + state | `*_ns_override` set; `$ns_diff` empty | None | NS override unset |
| `resellerclubmods_promoactivate.php` | Config flag | `promo_auto_activate == on` | `promo-details.json` | Flag off |
| `resellerclubmods_promoupdate.php` | Config flag | `promo_end_check == on` | promo helpers | Flag off |
| `resellerclubmods_raadomainreport.php` | Always runs hook body | `raa_domain_check != on` for email | `domains/search.json`, `details.json` | Email suppressed if flag on |

---

## Cron scripts

| Component | Gate pattern | Config gates | API calls on run | Blocked when |
|-----------|--------------|--------------|------------------|--------------|
| `cron/resellerclubmods_transfercheck.php` | **CLI-only** (`rcm_deny_direct_http`) | Transfer check enabled per account | `search.json`, `details.json` | HTTP → 403; invalid/missing account ID |
| `cron/resellerclubmods_dompricesync.php` | **CLI-only** + admin CSRF runner | Domain sync settings | `customer-price.json` | HTTP → 403; sync disabled per account |

---

## Widgets and standalone tools

| Component | Gate pattern | Config gates | API calls on load | Blocked when |
|-----------|--------------|--------------|-------------------|--------------|
| `widgets/domainpricelist.php` | Always runs | Promo/pricing config | `customer-price.json`, `tlds-in-phase.json` | Missing config |
| `tools/authlogin.php` | Account setup | `ob_autoauth`; **API success + email required** (fail-closed) | `authenticate-token-without-history.json` | ERROR / missing username → home redirect |
| `tools/whois.php` | HMAC wid (`rcm_whois_wid_expected`) | `wid_key` ≥16 chars, not default | `domains/available.json` | Bad/missing/weak wid |

---

## Admin tools (router includes)

All tools reachable via single `output()` router when account is configured.

| Tool file | Route | API calls (typical) |
|-----------|-------|---------------------|
| `tools/home.php` | default | Inherited `details.json` from parent |
| `tools/fundsbalance.php` | `trans=showfunds` | `reseller-balance.json` |
| `tools/importdompricing.php` | `domain=domain-pricing-import` | pricing + tlds-in-phase |
| `tools/importdomains.php` | `domain=domainimport` | search.json (many) |
| `tools/managepromos.php` | `domain=showpromos` | `promo-details.json` |
| `tools/exportusers.php` | `user=whmcsuser-vs-rcusers` | `customers/search.json` |
| `tools/importusers.php` | `user=rcuser-vs-whmcsusers` | search, details, balance |
| `tools/transfercheck.php` | `domain=transfercheck` | search, details, actions |
| `tools/movedomain.php` | `domain=moveservices` | details, move |
| `tools/bulkdomainmove.php` | `domain=bulkmove` | details, move |
| `tools/tldmanage.php` | `domain=tldmanage` | localAPI only |
| `tools/automationtools.php` | `automation=cronjobs` | None (instructions) |
| `tools/raamanagement.php` | `domain=raamanagement` | search, details, raa |
| `tools/guiwhois.php` / `gui7whois.php` | `domain=rcmwhois` | Config UI |
| `tools/clientareatools.php` | Required at bootstrap | move, suggest-names |

---

## Entry point summary (post-OSS)

| Category | Count |
|----------|-------|
| Config-flag gates | ~15 |
| Account-setup prerequisites | All API paths |
| Former `rcfree_license` gates | **0** |

---

## Historical note

Prior to OSS refactor, 17+ entry points used `!empty($rcfree_license)` and admin UI used five duplicate `output()` functions keyed on remote license status. See [license-system.md](../deep-dives/license-system.md) (historical).

## Related

- [../architecture/diagrams.md](../architecture/diagrams.md)
- [../deep-dives/license-system.md](../deep-dives/license-system.md)
- [../evidence/verification-index.md](../evidence/verification-index.md)
