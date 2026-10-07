# RC & LB Tools v2.19.3 — Security hardening audit report

## 1. Executive summary

Hardened the distributable WHMCS addon package against confirmed Critical/High defects (RCM-001–010) plus Medium/Low follow-ons (RCM-011–019). Cron HTTP mutation and access-key bypass are removed; module logging is redacted; funds math and standalone whois/SSO fail closed. Static verify gate passes. Live WHMCS+LogicBoxes runtime not exercised in CI.

**Verdict:** READY FOR PRODUCTION WITH LISTED RESIDUAL RISKS

## 2. Discovered WHMCS architecture (this package)

Custom extension package only: addon `resellerclubmods_tools`, 10 root hooks, addon hooks, 2 CLI cron runners, whois/authlogin standalones, client pricing widget. No payment/provisioning/registrar modules. Shared `call_api` + Capsule tables + `tbladdonmodules` credentials. See `docs/evidence/system-map.md`.

## 3. Audit coverage matrix

| Component | Discovered | Inspected | Tested | Modified | Re-tested |
|-----------|------------|-----------|--------|----------|-----------|
| Admin router | Y | Y | static | Y | verify-security |
| security.php kernel | Y | Y | funds assert | Y | verify-security |
| Cron/runners | Y | Y | static deny | Y | verify-security |
| Root hooks (10) | Y | Y | php -l | Y (log/funds/recurring) | php -l |
| whois/authlogin | Y | Y | static | Y | php -l |
| Widget | Y | Y | static | Y (session) | php -l |
| Custom tables | Y | Y | — | upgrade indexes | — |
| LB API / mail / AutoAuth | Y | Y | — | call_api/authlogin | runtime residual |

## 4. Findings table

See `docs/evidence/findings-2.19.3.md` (RCM-001…019 with Fix/Verification columns).

## 5. Changes made

- `incs/security.php` kernel; `call_api` ERROR model
- Cron CLI-only + runners; admin CSRF bulk sync; automationtools CLI recipes only
- Remove access bypass; clear API-key denorm; `rcm_log_module_call`
- Funds helpers; fundthreshold hardening; tblconfvars fix
- Whois HMAC/weak-key refuse; authlogin fail-closed; WHOIS hop bound; .immo typo
- CSRF/XSS; JSON session cache; domainrecurring preload; tldmanage batch 500
- Version 2.19.3; verify scripts; AGENTS/docs/system-map

## 6. Tests and verification

```
bash scripts/verify-security.sh  → VERIFY PASSED
php scripts/funds_math_assert.php → All asserts passed
php -l across package PHP → clean
```

Requires runtime verification: live price sync idempotency, real AutoAuth, transfer emails, upgrade indexes on production MySQL.

## 7. Remaining technical debt

- **Urgent:** Operators must migrate HTTP cron → CLI; regenerate `wid_key` ≥16 chars and update client WHOIS URLs (HMAC wid changed).
- **Recommended:** transactional price sync / locking; rate-limit whois; expand CSRF tokens to any remaining untagged forms.
- **Optional:** replace bundled idnclass; hide unimplemented multi-year promo UI; drop legacy `localkey` columns later.

## 8. Residual security/operational risk

- Partial `tblpricing` writes if sync interrupted (no DB transaction)
- Widget still burns LB API quota on client pages (by design)
- Legacy session unserialize migrate path once (allowed_classes=false)
- Deactivate still drops custom tables (product decision unchanged)
- WHMCS CSRF `check_token` behavior depends on WHMCS version at runtime

## 9. Final verdict

**READY FOR PRODUCTION WITH LISTED RESIDUAL RISKS**

### High-risk chains (from system-map)

1. Unauth price mutation via public cron — **mitigated**
2. Secret exfiltration via module log — **mitigated**
3. SSO fail-open — **mitigated**
4. Whois quota abuse via empty wid — **mitigated**
5. Funds mis-display — **mitigated**
6. DailyCronJob pile-up / no sync lock — **residual**
7. Credential denorm — **mitigated**

### Cycles

No hard import cycles. Soft data cycle: price sync ↔ widget/domainrecurring via `tblpricing` (temporal only).
