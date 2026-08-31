<?php
if (!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
require ROOTDIR . "/modules/addons/resellerclubmods_tools/incs/functions.php";
add_hook("PreDomainRegister", 1, "override_rc_nameservers");
function override_rc_nameservers($vars)
{
    global $params;
    global $CONFIG;
    $registrar = $params["registrar"];
    $original_ns1 = $CONFIG["DefaultNameserver1"];
    $original_ns2 = $CONFIG["DefaultNameserver2"];
    $original_ns3 = $CONFIG["DefaultNameserver3"];
    $original_ns4 = $CONFIG["DefaultNameserver4"];
    $original_ns5 = $CONFIG["DefaultNameserver5"];
    $original_nsconfig = [$original_ns1, $original_ns2, $original_ns3, $original_ns4, $original_ns5];
    $original_nsconfig = array_filter($original_nsconfig);
    sort($original_nsconfig);
    $custom_ns1 = $params["ns1"];
    $custom_ns2 = $params["ns2"];
    $custom_ns3 = $params["ns3"];
    $custom_ns4 = $params["ns4"];
    $custom_ns5 = $params["ns5"];
    $custom_nsconfig = [$custom_ns1, $custom_ns2, $custom_ns3, $custom_ns4, $custom_ns5];
    $custom_nsconfig = array_filter($custom_nsconfig);
    sort($custom_nsconfig);
    $ns_diff = array_diff($custom_nsconfig, $original_nsconfig);
    if ($registrar == "resellerclubrcm") {
        $registrar = "resellerclub";
    }
    if ($registrar == "netearthonercm") {
        $registrar = "netearthone";
    }
    if ($registrar == "stargatercm") {
        $registrar = "stargate";
    }
    if ($registrar == "resellercamprcm") {
        $registrar = "resellercamp";
    }
    $conf = [];
    foreach (Illuminate\Database\Capsule\Manager::table("tbladdonmodules")->where("module", "resellerclubmods_tools")->get() as $addonvars) {
        $conf[$addonvars->setting] = $addonvars->value;
    }
    if (!empty($conf["first_ns_override"])) {
        $first_ns_override = explode(",", $conf["first_ns_override"]);
        $conf_arr[$conf["first_domainregistrar"]] = $first_ns_override;
    }
    if (!empty($conf["second_ns_override"])) {
        $second_ns_override = explode(",", $conf["second_ns_override"]);
        $conf_arr[$conf["second_domainregistrar"]] = $second_ns_override;
    }
    if (!empty($conf["third_ns_override"])) {
        $third_ns_override = explode(",", $conf["third_ns_override"]);
        $conf_arr[$conf["third_domainregistrar"]] = $third_ns_override;
    }
    if (!empty($conf["fourth_ns_override"])) {
        $fourth_ns_override = explode(",", $conf["fourth_ns_override"]);
        $conf_arr[$conf["fourth_domainregistrar"]] = $fourth_ns_override;
    }
    if (empty($ns_diff) && isset($conf_arr)) {
        $i = 1;
        foreach ($conf_arr as $regkey => $regval) {
            if ($regkey == $registrar) {
                foreach ($regval as $nsvalues) {
                    $params["ns" . $i++ . ""] = rcm_trim($nsvalues);
                }
            }
        }
    }
    return $params;
}

?>