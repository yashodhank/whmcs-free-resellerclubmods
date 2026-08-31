<?php
error_reporting(5);
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
require ROOTDIR . "/modules/addons/resellerclubmods_tools/incs/functions.php";
$rcmdebuginfo = getDebuginfos();
$modulename = $rcmdebuginfo["modulename"];
$debug_addinfo = $rcmdebuginfo["debug_addinfo"];
$CONFIG = $rcmdebuginfo["config"];
$tid = $_REQUEST["tid"];
global $autoauthkey;
$systemurl = !empty($CONFIG["SystemSSLURL"]) ? $CONFIG["SystemSSLURL"] : $CONFIG["SystemURL"];
if (empty($tid)) {
    header("Location: " . $systemurl);
    exit;
}
$conf = [];
foreach (Illuminate\Database\Capsule\Manager::table("tbladdonmodules")->where("module", "resellerclubmods_tools")->get() as $addonvars) {
    $conf[$addonvars->setting] = $addonvars->value;
}
$ob_autoauth = $conf["ob_autoauth"];
$ob_home = $conf["ob_home"];
$ob_domain = $conf["ob_domain"];
$ob_bulk = $conf["ob_bulk"];
$ob_domtransfer = $conf["ob_domtransfer"];
$ob_bulktransfer = $conf["ob_bulktransfer"];
$ob_digicert = $conf["ob_digicert"];
$ob_sitebuilder = $conf["ob_sitebuilder"];
$ob_email = $conf["ob_email"];
$ob_sitelock = $conf["ob_sitelock"];
$ob_codeguard = $conf["ob_codeguard"];
$ob_comboplan = $conf["ob_comboplan"];
$ob_impressly = $conf["ob_impressly"];
$ob_singledomainhostinglinux = $conf["ob_singledomainhostinglinux"];
$ob_singledomainhostingwindows = $conf["ob_singledomainhostingwindows"];
$ob_multidomainhosting = $conf["ob_multidomainhosting"];
$ob_multidomainwindowshosting = $conf["ob_multidomainwindowshosting"];
$ob_resellerhosting_group = $conf["ob_resellerhosting_group"];
$ob_resellerhosting = $conf["ob_resellerhosting"];
$ob_resellerwindowshosting = $conf["ob_resellerwindowshosting"];
$ob_enterpriseemail = $conf["ob_enterpriseemail"];
$ob_businessemail = $conf["ob_businessemail"];
$ob_dedicatedserver_group = $conf["ob_dedicatedserver_group"];
$ob_managedserver_group = $conf["ob_managedserver_group"];
$ob_vps_group = $conf["ob_vps_group"];
$ob_cloud_group = $conf["ob_cloud_group"];
$ob_gapps_group = $conf["ob_gapps_group"];
$ob_wordpresshosting_group = $conf["ob_wordpresshosting_group"];
$ob_weebly = $conf["ob_weebly"];
    $conf_arr = [];
    $conf_arr["first"] = [$conf["first_rcauth_userid"], $conf["first_rcauth_apikey"]];
    $conf_arr["second"] = [$conf["second_rcauth_userid"], $conf["second_rcauth_apikey"]];
    $conf_arr["third"] = [$conf["third_rcauth_userid"], $conf["third_rcauth_apikey"]];
    $conf_arr["fourth"] = [$conf["fourth_rcauth_userid"], $conf["fourth_rcauth_apikey"]];
    if (is_numeric($conf_arr["first"][0]) && !empty($conf_arr["first"][1])) {
        $rcauth_userid = $conf_arr["first"][0];
        $rcauth_password = $conf_arr["first"][1];
    } else if (is_numeric($conf_arr["second"][0]) && !empty($conf_arr["second"][1])) {
        $rcauth_userid = $conf_arr["second"][0];
        $rcauth_password = $conf_arr["second"][1];
    } else if (is_numeric($conf_arr["third"][0]) && !empty($conf_arr["third"][1])) {
        $rcauth_userid = $conf_arr["third"][0];
        $rcauth_password = $conf_arr["third"][1];
    } else if (is_numeric($conf_arr["fourth"][0]) && !empty($conf_arr["fourth"][1])) {
        $rcauth_userid = $conf_arr["fourth"][0];
        $rcauth_password = $conf_arr["fourth"][1];
    }
    $rchttp_api = $conf["rchttp_api"];
    $apifunction = "/api/customers/authenticate-token-without-history.json";
    $data = ["token" => $tid];
    $get_tokendata = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
    $action = "auth token";
    $requeststring = $apifunction . " [reseller data protected] " . serialize_data($data);
    $responsedata = ["rcmdebug" => $debug_addinfo, "apidebug" => $get_tokendata];
    logModuleCall($modulename, $action, $requeststring, $responsedata);
    if ($_REQUEST["p"] == "home") {
        $gotodestination = $ob_home;
    } else if ($_REQUEST["p"] == "domain") {
        $gotodestination = $ob_domain;
    } else if ($_REQUEST["p"] == "bulk") {
        $gotodestination = $ob_bulk;
    } else if ($_REQUEST["p"] == "domtransfer") {
        $gotodestination = $ob_domtransfer;
    } else if ($_REQUEST["p"] == "bulktransfer") {
        $gotodestination = $ob_bulktransfer;
    } else if ($_REQUEST["p"] == "digicert" || $_REQUEST["p"] == "sslcert") {
        $gotodestination = $ob_digicert;
    } else if ($_REQUEST["p"] == "sitebuilder") {
        $gotodestination = $ob_sitebuilder;
    } else if ($_REQUEST["p"] == "email") {
        $gotodestination = $ob_email;
    } else if ($_REQUEST["p"] == "sitelock") {
        $gotodestination = $ob_sitelock;
    } else if ($_REQUEST["p"] == "codeguard") {
        $gotodestination = $ob_codeguard;
    } else if ($_REQUEST["p"] == "productbundle") {
        $gotodestination = $ob_comboplan;
    } else if ($_REQUEST["p"] == "impressly") {
        $gotodestination = $ob_impressly;
    } else if ($_REQUEST["p"] == "singledomainhostinglinux") {
        $gotodestination = $ob_singledomainhostinglinux;
    } else if ($_REQUEST["p"] == "singledomainhostingwindows") {
        $gotodestination = $ob_singledomainhostingwindows;
    } else if ($_REQUEST["p"] == "multidomainhosting") {
        $gotodestination = $ob_multidomainhosting;
    } else if ($_REQUEST["p"] == "multidomainwindowshosting") {
        $gotodestination = $ob_multidomainwindowshosting;
    } else if ($_REQUEST["p"] == "resellerhosting_group") {
        $gotodestination = $ob_resellerhosting_group;
    } else if ($_REQUEST["p"] == "resellerhosting") {
        $gotodestination = $ob_resellerhosting;
    } else if ($_REQUEST["p"] == "resellerwindowshosting") {
        $gotodestination = $ob_resellerwindowshosting;
    } else if ($_REQUEST["p"] == "enterpriseemail") {
        $gotodestination = $ob_enterpriseemail;
    } else if ($_REQUEST["p"] == "eelite_group") {
        $gotodestination = $ob_businessemail;
    } else if ($_REQUEST["p"] == "dedicatedserver_group") {
        $gotodestination = $ob_dedicatedserver_group;
    } else if ($_REQUEST["p"] == "managedserver_group") {
        $gotodestination = $ob_managedserver_group;
    } else if ($_REQUEST["p"] == "vps_group") {
        $gotodestination = $ob_vps_group;
    } else if ($_REQUEST["p"] == "cloudsites_group") {
        $gotodestination = $ob_cloud_group;
    } else if ($_REQUEST["p"] == "gapps_group") {
        $gotodestination = $ob_gapps_group;
    } else if ($_REQUEST["p"] == "wordpresshosting_group") {
        $gotodestination = $ob_wordpresshosting_group;
    } else if ($_REQUEST["p"] == "weebly") {
        $gotodestination = $ob_weebly;
    } else {
        $gotodestination = "cart.php";
    }
    if ($ob_autoauth != "on") {
        $useremail = $get_tokendata["username"];
        $timestamp = time();
        $autohash = sha1($useremail . $timestamp . $autoauthkey);
        $destinationurl = $systemurl . "/dologin.php?email=" . $useremail . "&timestamp=" . $timestamp . "&hash=" . $autohash . "&goto=" . urlencode($gotodestination);
    } else {
        $destinationurl = $systemurl . "/" . $gotodestination;
    }
    header("Location: " . $destinationurl);
    exit;

?>