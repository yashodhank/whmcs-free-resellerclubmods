# PHP engine advisories and the 2.19.5 userland limits

This note is separate from [findings-2.19.3.md](findings-2.19.3.md). The module cannot patch PHP.

## Engine

Published 2026-10-21, CVSS v4 9.2:

- [GHSA-w8hw-w9fx-j79w](https://github.com/php/php-src/security/advisories/GHSA-w8hw-w9fx-j79w) / CVE-2026-109618 — multipart parsing can exhaust memory before any application code runs. Confirmed on Apache mod_php. A userland `Content-Length` or multipart reject does not run in time.
- [GHSA-6wmf-4rv5-p3rp](https://github.com/php/php-src/security/advisories/GHSA-6wmf-4rv5-p3rp) / CVE-2026-109619 — a fatal (memory, timeout, failed `require`, uncaught error) can leave request-owned state for the next request on the same worker.

Patched releases are **8.2.35, 8.3.36, 8.4.27, and 8.5.12**. PHP 8.1, 8.0, and 7.4 have no official fix. CVE-2026-115560 is not fixed by those releases; the mitigation is `session.upload_progress.enabled=0` plus a worker restart. `ini_set()` in this module is too late.

This tree has no `move_uploaded_file`, `$_FILES`, or `composer.json`.

## What 2.19.5 changes

Shrinks fatal producers on public handlers. It does not claim those advisories are fixed.

| Limit | Where |
|-------|--------|
| WHOIS read 256KB, 8s deadline, 3s per socket | `tools/whois.php` via `rcm_read_stream_capped()` |
| Domain 253 bytes before `idna_convert` | `tools/whois.php` |
| `whoislookup.php` loaded only after `wid` matches | `tools/whois.php` |
| `tid` 2048 bytes, then redirect home | `tools/authlogin.php` |
| Curl timeout 5s on those two public callers only | `call_api()` optional argument; default stays 120 |
| `details` is `price` or `promos` only | `widgets/domainpricelist.php` |
| Posted customer ids above 3000: English stop, no `memory_limit` advice | `tools/importdomains.php` (300-id chunking unchanged) |
| HTTP 403 before `require init.php` | both `cron/*.php`; `rcm_deny_direct_http()` remains after bootstrap |
| Direct access exits unless `WHMCS` is defined | `incs/whoislookup.php`, `incs/idnclass.php` |
| English admin-home warning, features stay on | `tools/home.php` + `rcm_php_security_release_ok()` |

CI syntax jobs stay on 7.4, 8.0, and 8.1, and pin 8.2.35, 8.3.36, and 8.4.27. Each job prints `PHP_VERSION` and fails if the installed build is below that pin or on another minor. That is a syntax gate, not proof of the production SAPI.

## What did not change

No engine patch, no upload path, no schema change. The upgrade predicate `version_compare(..., "2.19.4", "<")` is unchanged. Language files were not edited. There is no global response cap inside `call_api()`, no `ini_set`, and no `memory_limit=-1`.

Production image upgrade (replace the pinned `php:8.3-apache` digest, disable session upload progress, optional Apache body limits) is an infra follow-up. The bake steps live in `AGENTS.md`. This repository's PR does not deploy.
