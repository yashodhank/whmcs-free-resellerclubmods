#!/usr/bin/env bash
# Static security gate for RC & LB Tools (RCM-001… hardening).
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"
fail=0

need() {
  if ! command -v "$1" >/dev/null 2>&1; then
    echo "WARN: $1 not found; skipping related checks"
    return 1
  fi
  return 0
}

echo "== PHP syntax =="
while IFS= read -r -d '' f; do
  php -l "$f" >/dev/null || { echo "syntax fail: $f"; fail=1; }
done < <(find modules/addons/resellerclubmods_tools includes/hooks widgets scripts -name '*.php' -print0 2>/dev/null)

echo "== Funds math asserts =="
php scripts/funds_math_assert.php || fail=1

echo "== Deny: access bypass / hardcoded key =="
if rg -n 'RCM_GLOBAL_ACCESS_KEY|coreorigin|%bubimanual%' modules/addons/resellerclubmods_tools includes/hooks widgets 2>/dev/null; then
  echo "FAIL: access bypass markers present"
  fail=1
else
  echo "OK: no access bypass markers"
fi

echo "== Deny: direct logModuleCall (must use rcm_log_module_call) =="
if rg -n '(?<!rcm_)logModuleCall\s*\(' modules/addons/resellerclubmods_tools includes/hooks widgets --glob '*.php' 2>/dev/null; then
  echo "FAIL: raw logModuleCall found"
  fail=1
else
  echo "OK: logging goes through rcm_log_module_call"
fi

echo "== Deny: cron HTTP recipes in automationtools =="
if rg -n 'lynx |GET https?://|cron/resellerclubmods_.*\.php\?id=' modules/addons/resellerclubmods_tools/tools/automationtools.php 2>/dev/null; then
  echo "FAIL: HTTP cron recipes still advertised"
  fail=1
else
  echo "OK: automationtools CLI-only"
fi

echo "== Require: cron deny helper =="
for c in modules/addons/resellerclubmods_tools/cron/resellerclubmods_dompricesync.php \
         modules/addons/resellerclubmods_tools/cron/resellerclubmods_transfercheck.php; do
  if rg -q 'rcm_deny_direct_http' "$c"; then
    echo "OK: $c denies HTTP"
  else
    echo "FAIL: $c missing rcm_deny_direct_http"
    fail=1
  fi
done

echo "== Require: security kernel =="
test -f modules/addons/resellerclubmods_tools/incs/security.php || { echo "FAIL: security.php missing"; fail=1; }
rg -q 'rcm_compute_funds|rcm_redact_for_log|rcm_deny_direct_http' modules/addons/resellerclubmods_tools/incs/security.php || fail=1

echo "== Require: runners =="
test -f modules/addons/resellerclubmods_tools/incs/runners/dompricesync_runner.php || fail=1
test -f modules/addons/resellerclubmods_tools/incs/runners/transfercheck_runner.php || fail=1

echo "== Deny: vendor phone-home =="
if rg -n 'rcmodules\.com|preverify\.php|verify\.php|checkip\.php' modules/addons/resellerclubmods_tools includes/hooks widgets 2>/dev/null; then
  echo "FAIL: vendor phone-home"
  fail=1
else
  echo "OK: zero vendor phone-home"
fi

echo "== Version =="
if rg -q '\$softversion = "2\.19\.3"' modules/addons/resellerclubmods_tools/incs/functions.php \
   && rg -q '"version" => "2\.19\.3"' modules/addons/resellerclubmods_tools/resellerclubmods_tools.php; then
  echo "OK: version 2.19.3"
else
  echo "FAIL: version not 2.19.3"
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
