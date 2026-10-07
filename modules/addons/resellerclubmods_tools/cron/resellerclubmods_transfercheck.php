<?php
/**
 * CLI-only transfer check entry (RCM-001). HTTP denied — no access-key backdoor.
 */
$filepath = substr(__FILE__, 0, -34);
if (file_exists($filepath . "/path.php")) {
    include $filepath . "/path.php";
}
if (isset($fullpath_to_whmcs)) {
    $include_path = $fullpath_to_whmcs;
} else {
    $include_path = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
}
require $include_path . "/init.php";
require_once ROOTDIR . "/modules/addons/resellerclubmods_tools/incs/functions.php";
rcm_deny_direct_http();

$conf = rcm_load_addon_conf();
$rid = null;
if (empty($conf["second_rcauth_userid"]) && empty($conf["third_rcauth_userid"]) && empty($conf["fourth_rcauth_userid"])) {
    $rid = $conf["first_rcauth_userid"] ?? "";
    if (!is_numeric($rid)) {
        exit("invalid id or missing id in first account configuration (cli)");
    }
} else if (isset($_SERVER["argv"][1]) && is_numeric($_SERVER["argv"][1])) {
    $rid = $_SERVER["argv"][1];
} else if (isset($argv[1]) && is_numeric($argv[1])) {
    $rid = $argv[1];
} else {
    exit("multiple accounts, invalid or missing id or php directive register_argc_argv disabled (cli)");
}

require ROOTDIR . "/modules/addons/resellerclubmods_tools/incs/runners/transfercheck_runner.php";
