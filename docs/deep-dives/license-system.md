# Former License System (Historical — Removed in OSS Refactor)

**Version:** 2.19.1  
**Status:** **REMOVED** — This document describes behavior that existed before the MIT OSS refactor. The current codebase has no runtime license validation and **zero vendor phone-home** (no HTTP to rcmodules.com, activation endpoints, or vendor version-check URLs).

## What was removed

| Component | Former location | Replacement |
|-----------|-----------------|-------------|
| `rcfree_check_license()` | `resellerclubmods_tools.php` | Deleted |
| Bootstrap `switch ($results["status"])` | Five duplicate `output()` functions | Single `output()` always |
| `rcfree_license` config field | Addon config UI | Removed from `$base_fields` |
| FRLBT addon expiry check | Bootstrap post-verify | Deleted |
| Cron argv license bypass | `argv` contains sync/cron → force Active | Unnecessary |
| localkey write on Active | `mod_resellerclubmodstools.localkey` | No longer written |
| License panel on home | `tools/home.php` | Version/release info only |
| `checkip.php` diagnostic | `home.php` → rcmodules.com | Server IP from `$_SERVER` only |

## Former architecture (for upgrade/migration context)

The license system previously gated all addon functionality through:

1. A WHMCS-stored license key (`tbladdonmodules.rcfree_license`)
2. A cached **localkey** in `mod_resellerclubmodstools`
3. Remote validation against `rcmodules.com`
4. A **status switch** defining which `resellerclubmods_tools_output()` WHMCS called

### Former `rcfree_check_license()` flow

1. Decode localkey (7-day cache) with domain/IP/directory binding
2. If cache invalid: POST to `preverify.php` and `verify.php` on rcmodules.com
3. On HTTP failure: 9-day grace from stale localkey
4. On Active: encode and persist new localkey

### Former bootstrap gate

```php
// REMOVED — conceptual only
$rcfree_license = tbladdonmodules.rcfree_license;
if (!empty($rcfree_license)) {
    if (argv contains "sync" or "cron") {
        $results["status"] = "Active";
    } else {
        $results = rcfree_check_license($rcfree_license, $localkey);
    }
    // FRLBT addon check could force Invalid
    switch ($results["status"]) { /* Active|Invalid|Expired|Suspended|default */ }
}
```

### Former gate asymmetry (why docs existed)

| Subsystem | Former check | Symptom |
|-----------|--------------|---------|
| AdminHomeWidget | Key **presence** only | Widget worked with expired license |
| Admin addon page | Full remote validation | Invalid UI on full load |
| Cron | Presence + argv bypass | Cron ran while admin showed Invalid |
| Root hooks | Presence only | Hooks fired with stale key string |

This asymmetry is **eliminated** in the OSS tree — no license checks remain.

## Upgrade notes for existing WHMCS installs

- **`tbladdonmodules.rcfree_license`** rows may remain; they are ignored.
- **`mod_resellerclubmodstools.localkey` / `lastcheck`** columns remain in schema; no longer updated.
- **No re-validate action** on admin home; license panel removed.
- **Configure reseller account** (API URL, ID, key, display name) — this is the only prerequisite for tools.
- **Enable funds widget** via `fundsbalance` checkbox if desired.

## Related (current docs)

- [gate-matrix.md](../correlation/gate-matrix.md) — Current feature gates
- [eula-and-licensing.md](../compliance/eula-and-licensing.md) — MIT + EULA
