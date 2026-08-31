# Admin Home Deep Dive

**Version:** 2.19.1 | **Primary source:** `modules/addons/resellerclubmods_tools/tools/home.php`

## When home loads

`home.php` is the **default branch** of the admin router. It is included when no other `$_GET` route matches (`trans`, `domain`, `user`, `automation`).

There is no license gate — home loads whenever the addon admin page is opened and account setup exists.

## Pre-home setup (`output()`)

Before `home.php` runs, `output()` in `resellerclubmods_tools.php`:

1. Sets `$method = "GET"`
2. Calls `/api/resellers/details.json` → `$resellerdetails_arrXml`
3. Derives `$lb_sellingcurrency`, `$reseller_buycurrency`, currency multiplicator
4. Loads WHMCS default currency from `tblcurrencies`
5. Renders social/marketing header and account selector dropdown

Variables consumed by `home.php` are prepared in parent scope (globals + `$vars`).

## Dashboard panels

### 1. Current account

- Account name, reseller ID
- **API connection test** — uses `$resellerdetails_arrXml` from parent
- Account switcher dropdown (`$tools_change_acc_dropdown`)

### 2. Currency account setup

Displays WHMCS default selling currency vs LogicBoxes selling/buy currencies. Shows `$conversiondesc` when mismatch logic fires.

### 3. Reseller account setup matrix

Three sub-sections:

**Account settings** — per-account hook toggles (inverted logic: `on` = disabled in UI):

| Display | Config source |
|---------|---------------|
| Auto customer signup | `first_hook_signup` etc. |
| Auto customer modify | `first_hook_modify` |
| Auto customer delete | `first_hook_delete` |
| Password sync flags | `first_pwd_signup`, `first_pwd_modify` |
| Transfer check | `first_transfercheck` |
| Funds threshold | `first_threshold` |

**Global settings** — cross-account features:

| Display | Config |
|---------|--------|
| Auto promo activate | `promo_auto_activate` |
| Auto promo update | `promo_end_check` |
| RAA report | `raa_domain_check` |
| Registrar lookup | `domainlookup` |
| Domain recurring update | `update_domain_recurring` |
| Currency double update | `domainsync_currencydoupd` |

**Nameserver settings** — `first_ns_override` vs WHMCS defaults

**Domain sync settings** — `domainsync`, redemption, telescope, TLD filters

Label tooltips reference `$LANG["accountsetuplabel"]` vs `$LANG["globalsetuplabel"]` to indicate scope.

### 4. Version information

| Row | Source |
|-----|--------|
| Module version | `$vars["version"]` (2.19.1) |
| Release date | `$releasedate` global |

The former license information panel was removed in the OSS refactor.

## API connection test

On API failure, `home.php` displays `SERVER_ADDR` and `LOCAL_ADDR` to help whitelist LogicBoxes API access.

| API result | UI message | Extra action |
|------------|------------|--------------|
| `ERROR` | Connection failed, wrong ID/password | Server IP shown |
| Empty response | Connection failed, check API URL | Server IP shown |
| Success + currency OK | Green "connection OK" | Clear `rcm_wrong_currencysetup` session |

## Currency mismatch session loop

`home.php` implements a redirect loop via `$_SESSION["rcm_wrong_currencysetup"]`:

| Condition | Behavior |
|-----------|----------|
| WHMCS currency ≠ LB selling, `currencyswitch` off | Danger alert; set session=1; redirect home |
| WHMCS currency ≠ LB selling, `currencyswitch` on | Conversion note; clear session on revisit |
| WHMCS currency = LB selling, `currencyswitch` on | Wrong setup alert; set session; redirect |
| Currencies match | Clear session; show OK |

Sidebar tool links are **hidden** when `$_SESSION["rcm_wrong_currencysetup"] == 1`.

## Sidebar interaction

`resellerclubmods_tools_sidebar()`:

- Links to config, home, and all tools
- Static version from addon config (`$vars["version"]`); no remote version check
- Suppresses tool links on currency mismatch session

## Routing reference

Full tool routing table: [../architecture/overview.md](../architecture/overview.md#routing-table).

## Operator journeys

### First-time setup

1. Configure first reseller account credentials in `configaddonmods.php`
2. Open addon home → verify API green and version panel
3. Enable desired hooks in config; confirm matrix on home
4. Enable **Funds Balance Widget** if desired

### API failure

1. Home shows red API result with server IPs
2. Compare displayed IPs with LogicBoxes whitelist
3. Verify `rchttp_api` (default `https://httpapi.com`)

## Related

- [license-system.md](license-system.md) (historical)
- [api-calls.md](api-calls.md)
- [../correlation/gate-matrix.md](../correlation/gate-matrix.md)
