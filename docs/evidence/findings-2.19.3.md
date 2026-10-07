# Validated findings — v2.19.3 security hardening

Confidence = Confirmed (static reachability) unless noted. Speculation dropped.

| ID | Sev | Conf | Component | Root cause | Impact | Fix | Verification |
|----|-----|------|-----------|------------|--------|-----|--------------|
| RCM-001 | Critical | Confirmed | cron HTTP + importdompricing | Auth = public reseller id | Unauth price mutation / transfer | CLI-only + runners + admin CSRF | `verify-security.sh`; cron HTTP 403 |
| RCM-002 | Critical | Confirmed | logModuleCall sites | passwd/token in serialize | Secrets in module log | `rcm_log_module_call` only | rg deny raw logModuleCall |
| RCM-003 | Critical | Confirmed | addon bootstrap | Hardcoded access key / coreorigin | Direct-access bypass | Removed | rg deny markers |
| RCM-004 | High | Confirmed | funds widget/page | available = total − blocked×mult again | Wrong available funds | `rcm_compute_funds` | `funds_math_assert.php` |
| RCM-005 | High | Confirmed | hooks.php | medium `* 0` | Dead medium alert | `rcm_funds_priority` 1.5× | funds assert |
| RCM-006 | High | Confirmed | whois.php | empty/default wid accepted | Unauth WHOIS/API abuse | HMAC wid + key policy | refuse short/default |
| RCM-007 | High | Confirmed | authlogin.php | no success/email check; logs tid | SSO fail-open / token leak | fail-closed + redact | code review |
| RCM-008 | High | Confirmed | admin/client tools | weak CSRF; datavalidated from request | CSRF / confused deputy | `rcm_require_post_token`; re-validate move | forms + clientareatools |
| RCM-009 | High | Confirmed | admin echo sinks | raw $_POST/$_REQUEST | Reflected XSS | `rcm_e` on sinks | spot-check |
| RCM-010 | High | Confirmed | call_api | no curl/JSON error model | Fail-open / notices | ERROR structure + defaults | php -l; callers use `rcm_api_ok` |
| RCM-011 | Medium | Confirmed | output() tblconf | wrong loop var | Broken config map | `$tblconfvars` | code |
| RCM-012 | Medium | Confirmed | dompricesync | `$vars` undefined | Always default TLD list | `$conf["transferfree_tlds"]` | runner |
| RCM-013 | Medium | Confirmed | fundthreshold | `$result[0]` before empty; unset `$data` | Notices / wrong path | empty checks + `$data` | hook rewrite |
| RCM-014 | Medium | Confirmed | session caches | unserialize blobs | Object-injection class risk | JSON session helpers | importdompricing/widget |
| RCM-015 | Medium | Confirmed | mod_resellerclubmodstools | API key denorm | Extra secret in dumps | stop write / upgrade clear | upgrade + account switch |
| RCM-016 | Medium | High | schema | missing indexes | Dup/slow | upgrade indexes | upgrade path |
| RCM-017 | Medium | Confirmed | domainrecurring / tldmanage | N+1 / unbounded | Scale failure | preload + batch limit 500 | code |
| RCM-018 | Low | Confirmed | whoislookup .immo; hop loop | typo + unbounded while | Wrong WHOIS / hang | typo + max hops | whois.php |
| RCM-019 | Low | Confirmed | docs/AGENTS | omitted tables; overstated authlogin | Operator misguidance | system-map + AGENTS/docs | docs |

See also: [system-map.md](system-map.md) for dependency / blast-radius analysis.
