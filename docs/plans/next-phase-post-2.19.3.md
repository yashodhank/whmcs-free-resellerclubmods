# Next phase after RC & LB Tools v2.19.3 (prod-verified)

**Status:** Proposed backlog (not started)  
**Base:** `main` @ 2.19.3 (MIT OSS hardening complete; prod smoke 2026-10-09)  
**Does not replace:** `AUDIT-REPORT.md` residual risks — this amends them with runtime findings.

## Prod verification summary (2026-10-09)

| Check | Result |
|-------|--------|
| `/login` 200 | Pass |
| Admin addon home + API connect | Pass |
| Version `2.19.3` (UI + disk + `tbladdonmodules`) | Pass |
| Cron HTTP 403 + Automation Tools CLI recipes | Pass |
| Funds tool + AdminHome widget (`fundsbalance=on`) | Pass |
| Whois refuse missing/bad `wid` | Pass |
| Authlogin fail-closed redirect | Pass |
| Domain pricing import forms have CSRF tokens | Pass |
| `scripts/verify-security.sh` | Pass |

## Immediate operator (no code)

1. **CLI cron scheduled (2026-10-09):** installed host `/etc/cron.d/whmcs-rcm-resellerclubmods` on ded1 via `docker exec` into `whmcs-production-jvjwfo-app-1` (CLI `php -q` only; no HTTP/`cron_access_key`). Reseller IDs from live `tbladdonmodules`: `292570`, `283000`, `368170`, `1139557`. WHMCS system cron remains Dokploy-owned (`composeId` `L_GzFCnhhpQfZi9T4MECD`). Runners self-gate (`*_domainsync_check=on` / `*_transer_check=on` skip).
2. **`wid_key` hygiene:** length ≥16 and HMAC wid URL present; rotate only if operator wants a fresh secret, then re-copy whois URLs / `whois.json`. **Do not rotate without explicit confirmation.**
3. **Writable `whois.json` restored (2026-10-09):** seeded from `dist.whois.json` (was missing); `www-data:www-data` on file + dir; `is_writable` passes as www-data (GUI check = writable). Re-bake ownership in `whmcs-prod-new` overlay so redeploys do not regress to `root:root` / missing file.

## P0 / P1 — product amendments (2.19.4 candidate)

| ID | Priority | Why | Paths |
|----|----------|-----|-------|
| NP-001 | P1 | Stale Automation Tools yellow note still says use **GET Method** if `register_argc_argv` off — contradicts CLI-only red banner and confuses operators | `lang/*/optionflagsdesc0`, English source used by `tools/automationtools.php` |
| NP-002 | P1 | Price sync can leave partial `tblpricing` writes on interrupt; no lock | `incs/runners/dompricesync_runner.php`, admin import path in `tools/importdompricing.php` |
| NP-003 | P1 | DailyCronJob pile-up (recurring → promo → RAA) shares LB quota with no mutual exclusion | `includes/hooks/resellerclubmods_*.php`, promo/RAA hooks |
| NP-004 | P2 | Whois GUI cannot persist when `whois.json` missing / non-writable; fail clearer + optional seed from `dist.whois.json` | `tools/guiwhois.php` / `tools/gui7whois.php`, overlay bake |
| NP-005 | P2 | Whois still burns LB API quota; no rate limit | `tools/whois.php`, `incs/security.php` |

## P2 / P3 — deferred architecture

| ID | Priority | Why | Paths |
|----|----------|-----|-------|
| NP-006 | P2 | Expand CSRF audit to any remaining untagged forms beyond pricing import | grep forms without `rcm_token_field()` under `tools/` |
| NP-007 | P2 | Widget LB API burn on client pages (by design) — cache / TTL option | `widgets/domainpricelist.php`, session/cache helpers |
| NP-008 | P3 | Replace bundled `idnclass` | `incs/idnclass.php` |
| NP-009 | P3 | Hide unimplemented multi-year promo UI | promo tools under `tools/` |
| NP-010 | P3 | Drop legacy `localkey` / `lastcheck` columns after upgrade window | activate/upgrade in `resellerclubmods_tools.php` |
| NP-011 | P3 | Deactivate drops custom tables — document or soft-deactivate option | `_deactivate()` |

## Out-of-repo findings (track elsewhere)

- Admin dashboard NetIM widgets render raw `{{netim.widget.*}}` keys (unrelated to RC & LB Tools).
- WHMCS MCP `/.well-known/openid-configuration` 404 + empty JWKS (MCP/OIDC posture; not addon).

## Suggested ship shape

1. Branch `fix/post-2.19.3-ops-followups` from `main`.
2. Land NP-001 + clearer whois.json messaging first (low risk).
3. Design NP-002/NP-003 (transaction or flock + skip-if-locked) with dry-run tests; no live LB bulk writes in CI.
4. Bump to **2.19.4** only after verify-security + funds assert + operator cron/`whois.json` checklist.

## Operator how-to (wid_key rotate — do not auto-change)

1. Admin → **System Settings → Apps & Integrations** (or Addon Modules) → **RC & LB Tools v2** → Configure.
2. Set **Whois Lookup Secret** (`wid_key`) to a new random string ≥16 chars → Save.
3. Addon → **API Whois Server Setup** → copy new whoisserver URL (HMAC `wid`).
4. Update `resources/domains/whois.json` (once writable) and any external whois consumers.
5. Smoke: missing `wid` → refuse; bad `wid` → Unauthorized; valid URL → availability only (no write APIs).
