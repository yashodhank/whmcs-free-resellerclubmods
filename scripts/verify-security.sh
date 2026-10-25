#!/usr/bin/env bash
# Static security gate for RC & LB Tools (RCM-001… hardening).
# Prefer ripgrep when present; fall back to grep -R so minimal CI images without rg still run the full gate.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"
fail=0

HAVE_RG=0
if command -v rg >/dev/null 2>&1; then
  HAVE_RG=1
fi

# Quiet search: exit 0 if any match, 1 if none. Args: PATTERN PATH [PATH...]
rcm_search_q() {
  local pattern="$1"
  shift
  if [[ "$HAVE_RG" -eq 1 ]]; then
    rg -q -- "$pattern" "$@"
  else
    grep -REq -- "$pattern" "$@" 2>/dev/null
  fi
}

# Print matching lines (with path:line); exit 0 if any match, 1 if none.
# Args: PATTERN PATH [PATH...]
rcm_search_n() {
  local pattern="$1"
  shift
  if [[ "$HAVE_RG" -eq 1 ]]; then
    rg -n -- "$pattern" "$@"
  else
    grep -REn -- "$pattern" "$@" 2>/dev/null
  fi
}

echo "== PHP syntax =="
while IFS= read -r -d '' f; do
  php -l "$f" >/dev/null || { echo "syntax fail: $f"; fail=1; }
done < <(find modules/addons/resellerclubmods_tools includes/hooks widgets scripts -name '*.php' -print0 2>/dev/null)

echo "== Funds math asserts =="
php scripts/funds_math_assert.php || fail=1

echo "== Request bounds asserts =="
php scripts/request_bounds_assert.php || fail=1

echo "== Deny: access bypass / hardcoded key =="
if rcm_search_n 'RCM_GLOBAL_ACCESS_KEY|coreorigin|%bubimanual%' modules/addons/resellerclubmods_tools includes/hooks widgets; then
  echo "FAIL: access bypass markers present"
  fail=1
else
  echo "OK: no access bypass markers"
fi

echo "== Deny: direct logModuleCall (must use rcm_log_module_call) =="
# Only security.php may call logModuleCall() (inside rcm_log_module_call). Portable: no PCRE lookbehind.
_log_hits=""
if [[ "$HAVE_RG" -eq 1 ]]; then
  _log_hits="$(rg -n 'logModuleCall\s*\(' modules/addons/resellerclubmods_tools includes/hooks widgets --glob '*.php' 2>/dev/null | grep -v '/incs/security\.php:' || true)"
else
  _log_hits="$(grep -REn --include='*.php' 'logModuleCall[[:space:]]*\(' modules/addons/resellerclubmods_tools includes/hooks widgets 2>/dev/null | grep -v '/incs/security\.php:' || true)"
fi
if [[ -n "$_log_hits" ]]; then
  printf '%s\n' "$_log_hits"
  echo "FAIL: raw logModuleCall found"
  fail=1
else
  echo "OK: logging goes through rcm_log_module_call"
fi
unset _log_hits

echo "== Deny: cron HTTP recipes in automationtools =="
if rcm_search_n 'lynx |GET https?://|cron/resellerclubmods_.*\.php\?id=' modules/addons/resellerclubmods_tools/tools/automationtools.php; then
  echo "FAIL: HTTP cron recipes still advertised"
  fail=1
else
  echo "OK: automationtools CLI-only"
fi

echo "== Deny: stale GET Method in optionflagsdesc0 (NP-001) =="
if rcm_search_n 'optionflagsdesc0.*GET Method|optionflagsdesc0.*método GET' modules/addons/resellerclubmods_tools/lang; then
  echo "FAIL: optionflagsdesc0 still advertises HTTP GET cron"
  fail=1
else
  echo "OK: optionflagsdesc0 CLI-only"
fi

echo "== Require: flock helpers (NP-002/NP-003) =="
if rcm_search_q 'function rcm_try_lock|function rcm_release_lock' modules/addons/resellerclubmods_tools/incs/security.php \
   && rcm_search_q 'rcm_try_lock\("dompricesync"\)' modules/addons/resellerclubmods_tools/incs/runners/dompricesync_runner.php \
   && rcm_search_q 'rcm_try_lock\("dailycron_hooks"\)' includes/hooks/resellerclubmods_domainrecurring.php \
      includes/hooks/resellerclubmods_promoactivate.php \
      includes/hooks/resellerclubmods_promoupdate.php \
      includes/hooks/resellerclubmods_raadomainreport.php; then
  echo "OK: price-sync + DailyCronJob skip-if-busy locks present"
else
  echo "FAIL: missing rcm_try_lock usage"
  fail=1
fi

echo "== Require: cron deny helper =="
for c in modules/addons/resellerclubmods_tools/cron/resellerclubmods_dompricesync.php \
         modules/addons/resellerclubmods_tools/cron/resellerclubmods_transfercheck.php; do
  if rcm_search_q 'rcm_deny_direct_http' "$c"; then
    echo "OK: $c denies HTTP"
  else
    echo "FAIL: $c missing rcm_deny_direct_http"
    fail=1
  fi
  if awk '
    /PHP_SAPI[[:space:]]*!==[[:space:]]*"cli"/ && sapi == 0 { sapi = NR }
    /http_response_code[[:space:]]*\([[:space:]]*403[[:space:]]*\)/ && code == 0 { code = NR }
    /require[[:space:]].*init\.php/ && req == 0 { req = NR }
    END {
      if (sapi > 0 && code > 0 && req > 0 && sapi < req && code < req) {
        exit 0
      }
      exit 1
    }
  ' "$c"; then
    echo "OK: $c returns HTTP 403 before init.php"
  else
    echo "FAIL: $c SAPI 403 must run before require init.php"
    fail=1
  fi
done

echo "== Ratchet: whois.php capped reader =="
WHOIS_PHP="modules/addons/resellerclubmods_tools/tools/whois.php"
if rcm_search_q 'while \(!feof' "$WHOIS_PHP"; then
  echo "FAIL: whois.php still reads to EOF"
  fail=1
else
  echo "OK: whois.php has no unbounded feof loop"
fi
if rcm_search_q 'rcm_read_stream_capped\(' "$WHOIS_PHP"; then
  echo "OK: whois.php calls the capped reader"
else
  echo "FAIL: whois.php missing rcm_read_stream_capped"
  fail=1
fi
if rcm_search_q 'rcm_public_curl_timeout\(' "$WHOIS_PHP" \
   && rcm_search_q 'rcm_public_curl_timeout\(' modules/addons/resellerclubmods_tools/tools/authlogin.php \
   && rcm_search_q '\$curlTimeout = 120' modules/addons/resellerclubmods_tools/incs/functions.php; then
  echo "OK: public callers use a short curl timeout; call_api default stays 120"
else
  echo "FAIL: public curl timeout or call_api default missing"
  fail=1
fi

echo "== Ratchet: direct-access guards =="
for g in modules/addons/resellerclubmods_tools/incs/whoislookup.php \
         modules/addons/resellerclubmods_tools/incs/idnclass.php; do
  if rcm_search_q '!defined\("WHMCS"\)' "$g"; then
    echo "OK: $g exits unless WHMCS is defined"
  else
    echo "FAIL: $g missing direct-access guard"
    fail=1
  fi
done

echo "== Deny: ini_set and memory_limit=-1 =="
if rcm_search_n 'ini_set[[:space:]]*\(|memory_limit[[:space:]]*=[[:space:]]*-1' \
    modules/addons/resellerclubmods_tools includes/hooks widgets; then
  echo "FAIL: ini_set or memory_limit=-1 present"
  fail=1
else
  echo "OK: no ini_set or memory_limit=-1"
fi

echo "== Require: security kernel =="
test -f modules/addons/resellerclubmods_tools/incs/security.php || { echo "FAIL: security.php missing"; fail=1; }
rcm_search_q 'rcm_compute_funds|rcm_redact_for_log|rcm_deny_direct_http' modules/addons/resellerclubmods_tools/incs/security.php || {
  echo "FAIL: security.php missing required helpers"
  fail=1
}

echo "== Require: runners =="
test -f modules/addons/resellerclubmods_tools/incs/runners/dompricesync_runner.php || fail=1
test -f modules/addons/resellerclubmods_tools/incs/runners/transfercheck_runner.php || fail=1

echo "== Deny: vendor phone-home =="
if rcm_search_n 'rcmodules\.com|preverify\.php|verify\.php|checkip\.php' modules/addons/resellerclubmods_tools includes/hooks widgets; then
  echo "FAIL: vendor phone-home"
  fail=1
else
  echo "OK: zero vendor phone-home"
fi

echo "== Version =="
if rcm_search_q '\$softversion = "2\.19\.5"' modules/addons/resellerclubmods_tools/incs/functions.php \
   && rcm_search_q '\$releasedate = "2026-10-25"' modules/addons/resellerclubmods_tools/incs/functions.php \
   && rcm_search_q '"version" => "2\.19\.5"' modules/addons/resellerclubmods_tools/resellerclubmods_tools.php \
   && rcm_search_q 'version_compare\(\(string\) \$version, "2\.19\.4", "<"\)' modules/addons/resellerclubmods_tools/resellerclubmods_tools.php; then
  echo "OK: version 2.19.5; upgrade predicate still < 2.19.4"
else
  echo "FAIL: version not 2.19.5 or upgrade predicate changed"
  fail=1
fi

echo "== System map =="
test -f docs/evidence/system-map.md || { echo "FAIL: system-map.md missing"; fail=1; }

if [[ "$fail" -ne 0 ]]; then
  echo "VERIFY FAILED"
  exit 1
fi
echo "VERIFY PASSED"
exit 0
