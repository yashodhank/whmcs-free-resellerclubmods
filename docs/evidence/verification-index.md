# Verification Index

**Documentation pack:** RC & LB Tools v2.19.1 dev docs  
**Verification date:** 2026-09-01 (updated post ionCube FP cleanup + SSL harden)  
**Method:** Read-only grep + PHP syntax check

## Gate checklist

| Gate | Threshold | Result |
|------|-----------|--------|
| G1 Architecture review | Doc tree covers home, API, correlation | **PASS** |
| G2 Foundation | All planned files exist; AGENTS.md complete | **PASS** |
| G3 OSS refactor | No runtime license gates in PHP | **PASS** |
| G4 Zero vendor phone-home | No rcmodules / activation HTTP in PHP | **PASS** |
| G4 Citation accuracy | Spot-checks resolve post-refactor | **PASS** |
| G4 No secrets in docs | Zero real keys | **PASS** |
| G4 Licensing alignment | MIT; historical license docs marked | **PASS** |
| G5 ionCube false positive | No Loader probe / no encoded blobs | **PASS** |
| G6 SSL verify in `call_api` | `VERIFYPEER` + `VERIFYHOST` enabled | **PASS** |
| G7 Human acceptance | Pending operator | — |

## Grep counts (zero vendor phone-home)

| Pattern | Count | Notes |
|---------|-------|-------|
| `rcfree_check_license` | **0** | Removed |
| `rcfree_license` | **0** in runtime PHP | May remain in lang strings / stale DB |
| `rcmodules.com` | **0** in runtime PHP | Removed from hooks, sidebar, widget |
| `checkip.php` | **0** in runtime PHP | home.php uses `$_SERVER` only |
| `preverify` / `verify.php` | **0** in runtime PHP | License endpoints removed |
| `call_api(` | ~90 | Unchanged (LogicBoxes API) |
| Direct `!empty($rcfree_license)` gates | **0** | Removed from all entry points |
| `ioncube` / `ioncube_loader_iversion` | **0** in runtime PHP | Former debug-only probe removed (audit FP) |

Command used:

```bash
rg -i 'rcmodules\.com|checkip\.php|preverify|verify\.php|rcfree_check_license|rcfree_license' --glob '*.php'
rg -i 'ioncube' --glob '*.php'
```

Expected: zero matches for license/phone-home patterns; ionCube only if intentionally documented as resolved FP (none in runtime PHP).

## PHP syntax verification

All modified PHP files pass `php -l` after zero vendor phone-home refactor and ionCube/SSL cleanup.

## Spot-checked changes

| # | Claim | Verified |
|---|-------|----------|
| 1 | Single `output()` in bootstrap | Yes — `resellerclubmods_tools.php` |
| 2 | No `rcfree_check_license()` | Yes — grep zero |
| 3 | No `check_versions()` / rcmodules version URL | Yes — removed from `hooks.php`, sidebar |
| 4 | Widget gated on `fundsbalance` | Yes — `hooks.php` |
| 5 | License panel removed from home | Yes — version panel only |
| 6 | API failure shows server IP from `$_SERVER` | Yes — `home.php` (no checkip.php) |

## Secret scan

Manual review: no API keys or credentials in docs.

## Historical reference

Pre-OSS grep counts (2026-09-01 initial docs): `rcfree_license` **43**, direct gates **17**, `rcmodules.com` **2** (version check). See [license-system.md](../deep-dives/license-system.md).

## Related

- [gate-matrix.md](../correlation/gate-matrix.md)
- [eula-and-licensing.md](../compliance/eula-and-licensing.md)
