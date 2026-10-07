<?php
/**
 * Pure-PHP asserts for rcm_compute_funds / rcm_funds_priority (no WHMCS bootstrap).
 */
define("WHMCS", true); // satisfy security.php guard for CLI include
require_once dirname(__DIR__) . "/modules/addons/resellerclubmods_tools/incs/security.php";

$failures = 0;
function assert_true($cond, $msg)
{
    global $failures;
    if (!$cond) {
        fwrite(STDERR, "FAIL: $msg\n");
        $failures++;
    } else {
        echo "OK: $msg\n";
    }
}

// Normal currency: available = total - blocked (single scale)
$f = rcm_compute_funds(100, 20, "USD");
assert_true($f["total"] === 100.0, "USD total");
assert_true($f["blocked"] === 20.0, "USD blocked");
assert_true($f["available"] === 80.0, "USD available = total - blocked (no double scale)");
assert_true($f["multiplicator"] === 1, "USD multiplicator 1");

// Thousands currency: scale once
$f2 = rcm_compute_funds(10, 2, "VND");
assert_true($f2["multiplicator"] === 1000, "VND multiplicator 1000");
assert_true($f2["total"] === 10000.0, "VND total scaled once");
assert_true($f2["blocked"] === 2000.0, "VND blocked scaled once");
assert_true($f2["available"] === 8000.0, "VND available not double-scaled");

// Priority bands: high <= threshold; medium < 1.5x; low >= 1.5x
assert_true(rcm_funds_priority(50, 100) === "high", "high when at/below threshold");
assert_true(rcm_funds_priority(100, 100) === "high", "high at exact threshold");
assert_true(rcm_funds_priority(120, 100) === "medium", "medium between threshold and 1.5x");
assert_true(rcm_funds_priority(150, 100) === "low", "low at 1.5x");
assert_true(rcm_funds_priority(200, 100) === "low", "low above 1.5x");

// Medium band is not dead (* 0)
assert_true(rcm_funds_priority(110, 100) === "medium", "medium band reachable");

if ($failures > 0) {
    fwrite(STDERR, "$failures assertion(s) failed\n");
    exit(1);
}
echo "All funds math asserts passed\n";
exit(0);
