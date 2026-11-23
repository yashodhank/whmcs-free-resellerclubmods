<?php
/**
 * Static contracts for the 2026-11-23 audit fixes (no WHMCS bootstrap; these paths need a live WHMCS to execute).
 */
$root = dirname(__DIR__);
$A = $root . "/modules/addons/resellerclubmods_tools/";
$failures = 0;
function hc_assert($cond, $msg)
{
    global $failures;
    if (!$cond) {
        fwrite(STDERR, "FAIL: $msg\n");
        $failures++;
    } else {
        echo "OK: $msg\n";
    }
}
$move = file_get_contents($A . "tools/movedomain.php");
hc_assert(strpos($move, '$oldowner = $_REQUEST["oldowner"];') === false, "T-01 oldowner is not read raw (reflected into value=)");
hc_assert(strpos($move, 'ctype_digit((string) $_REQUEST["oldowner"]) ? (int) $_REQUEST["oldowner"] : 0;') !== false, "T-01 oldowner is an int");

$cat = file_get_contents($A . "tools/clientareatools.php");
hc_assert(preg_match('/\$primarySidebar->getChild\("Domain Details Management"\)->getChild\("[^"]+"\)->/', $cat) === 0, "T-07 no unguarded sidebar child chains");
hc_assert(strpos($cat, 'isset($_REQUEST["tlds"]) && is_array($_REQUEST["tlds"])') !== false, "T-06 tlds must be an array before array_slice");

$hooks = file_get_contents($A . "hooks.php");
hc_assert(strpos($hooks, '$accessdenied = "";') !== false, "T-05 accessdenied initialised");
$fn = file_get_contents($A . "incs/functions.php");
hc_assert(strpos($fn, '$registrar_dropdown .= "<select') === false, "T-09 registrar_dropdown not appended before init");
$pa = file_get_contents($root . "/includes/hooks/resellerclubmods_promoactivate.php");
hc_assert(strpos($pa, '$apifunction, $data, $method)') === false, "T-12 promo-details call has defined args");

if ($failures > 0) {
    fwrite(STDERR, "$failures assert(s) failed\n");
    exit(1);
}
echo "All hardening contract asserts passed\n";
