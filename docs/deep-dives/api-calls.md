# API Calls Deep Dive

**Version:** 2.19.1 | **Wrapper:** `call_api()` in `incs/functions.php:470-492`

## `call_api()` wrapper

```470:491:modules/addons/resellerclubmods_tools/incs/functions.php
    function call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method)
    {
        $data = "auth-userid=" . $rcauth_userid . "&api-key=" . rawurlencode($rcauth_password) . serialize_data($data);
        $ch = curl_init();
        ...
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        ...
        if ($method == "POST") {
            curl_setopt($ch, CURLOPT_URL, $rchttp_api . $apifunction);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        } else {
            curl_setopt($ch, CURLOPT_URL, $rchttp_api . $apifunction . "?" . $data);
        }
        $result = curl_exec($ch);
        $result = json_decode($result, true);
```

### Behavior summary

| Aspect | Value |
|--------|-------|
| Auth | `auth-userid` + `api-key` query/body params |
| Base URL | `$rchttp_api` (config, default `https://httpapi.com`) |
| Methods | GET (query string) or POST (body) |
| Response | JSON decoded to associative array |
| Timeouts | Connect 60s, total 120s |
| **SSL verification** | **Enabled** (`SSL_VERIFYHOST=2`, `SSL_VERIFYPEER=true`) |

**Security posture:** Outbound TLS to LogicBoxes/ResellerClub API hosts is verified. Hosts must present a valid certificate chain trusted by the PHP/cURL CA store.

### Alternate API host

`tools/whois.php` uses `https://domaincheck.httpapi.com` for availability checks (`whois.php:38`).

## AdminHomeWidget calls (`hooks.php`)

Gate: `$vars["fundsbalance"]` config only (`hooks.php`).

Per configured account where `*_show_fundsbalance != "on"`:

| Step | Endpoint | Lines (account 1 example) |
|------|----------|---------------------------|
| Reseller details | `/api/resellers/details.json` | 143-145 |
| Balance | `/api/billing/reseller-balance.json` | 171-173 |

Repeated for second (205-235), third (267-297), fourth (329-359) accounts—up to **8 API calls** per admin home page load (4× details + 4× balance).

## Active admin router call

Every Active addon page load calls `/api/resellers/details.json` once for the selected account (`resellerclubmods_tools.php:611-612`) before routing to tools or home.

## Endpoint inventory

Unique LogicBoxes `$apifunction` paths in v2.19.1 (grep-backed):

| Endpoint | Used in |
|----------|---------|
| `/api/actions/search-current.json` | `transfercheck.php`, `cron/resellerclubmods_transfercheck.php` |
| `/api/billing/customer-balance.json` | `importusers.php` |
| `/api/billing/reseller-balance.json` | `fundsbalance.php`, `hooks.php`, `fundthreshold.php` |
| `/api/customers/authenticate-token-without-history.json` | `authlogin.php` |
| `/api/customers/change-password.json` | `rcchangepwd.php` |
| `/api/customers/delete.json` | `rcdeleteclient.php` |
| `/api/customers/details.json` | hooks, movedomain, bulkdomainmove, clientareatools, importusers, rcsignup, rcmodifyclient, rcchangepwd, rcdeleteclient |
| `/api/customers/modify.json` | `rcmodifyclient.php` |
| `/api/customers/search.json` | importdomains, exportusers, importusers, resellerclubmods_tools.php |
| `/api/customers/v2/signup.json` | `incs/functions.php` (signup helper) |
| `/api/domains/available.json` | `whois.php` |
| `/api/domains/details.json` | transfercheck, raadomainreport, raamanagement, cron transfercheck |
| `/api/domains/raa/resend-verification.json` | `raamanagement.php` |
| `/api/domains/search.json` | importdomains, transfercheck, raadomainreport, raamanagement, cron |
| `/api/domains/tlds-in-phase.json` | importdompricing, domainpricelist widget |
| `/api/domains/v5/suggest-names.json` | `clientareatools.php` |
| `/api/products/category-keys-mapping.json` | `incs/functions.php` |
| `/api/products/customer-price.json` | importdompricing, dompricesync cron, domainpricelist |
| `/api/products/details.json` | `incs/functions.php` |
| `/api/products/move.json` | movedomain, bulkdomainmove, clientareatools |
| `/api/products/reseller-cost-price.json` | `importdompricing.php` |
| `/api/resellers/details.json` | hooks, fundthreshold, resellerclubmods_tools.php, home parent |
| `/api/resellers/promo-details.json` | managepromos, promoactivate |

**Total unique endpoints:** 22

## `call_api()` invocation count

Grep `call_api\(` across `*.php`: **90** call sites (includes repeated calls in loops).

## `logModuleCall()` sites

WHMCS module debug log—**12** sites:

| File | Line | Typical action |
|------|------|----------------|
| `incs/functions.php` | 465 | Customer signup |
| `rcchangepwd.php` | 43 | Password change |
| `rcdeleteclient.php` | 43 | Customer delete |
| `rcmodifyclient.php` | 146 | Customer modify |
| `domainrecurring.php` | 118 | Recurring update |
| `movedomain.php` | 210 | Domain move |
| `bulkdomainmove.php` | 65 | Bulk move |
| `clientareatools.php` | 262 | Client-area move |
| `whois.php` | 114 | Availability check |
| `authlogin.php` | 85 | Auto-login token |
| `raamanagement.php` | 39, 74 | RAA resend |

Request strings often redact credentials: `"[reseller data protected]"` pattern (`movedomain.php:208`).

## `localAPI()` WHMCS internal calls

**15** call sites—WHMCS native API, not LogicBoxes:

| File | Commands used |
|------|---------------|
| `incs/functions.php:25` | Admin email helper |
| `transfercheck.php` | Domain/email operations (5 calls) |
| `cron/resellerclubmods_transfercheck.php` | Domain/email operations (5 calls) |
| `tldmanage.php:122` | TLD management |
| `clientareatools.php:276` | Post-move WHMCS update |

Uses `$maileradmin` config username as API admin context.

## External non-LogicBoxes URLs

| URL | Purpose | Source |
|-----|---------|--------|
| `https://domaincheck.httpapi.com` | Whois availability | `whois.php` |

No runtime calls to rcmodules.com or vendor version-check URLs.

## Helper functions (non-`call_api` product lookups)

`incs/functions.php` also defines:

- `newproductkeys()` → `/api/products/category-keys-mapping.json` (141)
- `getaddondetails()` → `/api/products/details.json` (160)

Used by promo hooks/tools.

## Grep commands (verification)

```bash
rg 'call_api\(' --glob '*.php' -n    # 90 matches
rg 'logModuleCall' --glob '*.php' -n # 12 matches
rg '\$apifunction\s*=' --glob '*.php' -n
rg 'localAPI\(' --glob '*.php' -n    # 15 matches
```

## Related

- [admin-home.md](admin-home.md) — Home API test
- [license-system.md](license-system.md) — License server calls
- [../evidence/verification-index.md](../evidence/verification-index.md)
