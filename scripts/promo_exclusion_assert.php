<?php
/**
 * Pure-PHP asserts for the promo TLD exclusion list (incs/promo_exclusion.php). No WHMCS bootstrap.
 */
define("WHMCS", true); // satisfy the include guard for CLI use
$root = dirname(__DIR__);
require_once $root . "/modules/addons/resellerclubmods_tools/incs/promo_exclusion.php";

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

assert_true(rcm_promo_excluded_tlds([]) === [], "no setting -> empty list (feature off)");
assert_true(rcm_promo_excluded_tlds(["promo_exclude_tlds" => ""]) === [], "empty setting -> empty list");
assert_true(rcm_promo_excluded_tlds("junk") === [], "non-array conf -> empty list");
assert_true(
    rcm_promo_excluded_tlds(["promo_exclude_tlds" => " .Services, fyi;.FYI\n co.in ,, . "]) === [".services", ".fyi", ".co.in"],
    "mixed separators, case, missing dot and duplicates normalise"
);
$ex = rcm_promo_excluded_tlds(["promo_exclude_tlds" => ".services,.fyi"]);
assert_true(rcm_promo_is_excluded("services", $ex) === true, "bare tld matches");
assert_true(rcm_promo_is_excluded(".SERVICES", $ex) === true, "case-insensitive match");
assert_true(rcm_promo_is_excluded(".com", $ex) === false, "other tld not excluded");
assert_true(rcm_promo_is_excluded("", $ex) === false, "empty tld never excluded");
assert_true(rcm_promo_is_excluded(".fyi", []) === false, "empty list excludes nothing");
$line = rcm_promo_log_skip("auto-update", "fyi", "addnewdomain", "2026-12-01 00:00:00");
assert_true(strpos($line, " .fyi (addnewdomain)") !== false && strpos($line, "promo end 2026-12-01 00:00:00 UTC") !== false, "skip log line names tld, type and end");

// Wiring: functions.php loads it, both cron hooks call it, and the setting is exposed.
$functions = file_get_contents($root . "/modules/addons/resellerclubmods_tools/incs/functions.php");
assert_true(strpos($functions, 'require_once __DIR__ . "/promo_exclusion.php";') !== false, "functions.php loads promo_exclusion.php");
foreach (["promoactivate", "promoupdate"] as $hook) {
    $src = file_get_contents($root . "/includes/hooks/resellerclubmods_{$hook}.php");
    assert_true(strpos($src, "rcm_promo_excluded_tlds(\$conf)") !== false && strpos($src, "rcm_promo_is_excluded(") !== false, "{$hook} hook honours the exclusion list");
}
$addon = file_get_contents($root . "/modules/addons/resellerclubmods_tools/resellerclubmods_tools.php");
assert_true(strpos($addon, '"promo_exclude_tlds" => ["FriendlyName"') !== false, "addon config exposes promo_exclude_tlds");

if ($failures > 0) {
    fwrite(STDERR, "$failures assert(s) failed\n");
    exit(1);
}
echo "All promo exclusion asserts passed\n";
