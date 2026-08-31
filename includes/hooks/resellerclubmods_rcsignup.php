<?php
if (!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
require ROOTDIR . "/modules/addons/resellerclubmods_tools/incs/functions.php";
add_hook("ClientAdd", 1, "create_rc_account");
function create_rc_account($vars)
{
    global $CONFIG;
    $conf = [];
    foreach (Illuminate\Database\Capsule\Manager::table("tbladdonmodules")->where("module", "resellerclubmods_tools")->get() as $addonvars) {
        $conf[$addonvars->setting] = $addonvars->value;
    }
    $rchttp_api = $conf["rchttp_api"];
    $conf_arr = [];
    $conf_arr["first"] = [$conf["first_rcauth_userid"], htmlspecialchars_decode($conf["first_rcauth_apikey"]), $conf["first_acc_name"], $conf["first_hook_signup"], $conf["first_pwd_signup"]];
    $conf_arr["second"] = [$conf["second_rcauth_userid"], htmlspecialchars_decode($conf["second_rcauth_apikey"]), $conf["second_acc_name"], $conf["second_hook_signup"], $conf["second_pwd_signup"]];
    $conf_arr["third"] = [$conf["third_rcauth_userid"], htmlspecialchars_decode($conf["third_rcauth_apikey"]), $conf["third_acc_name"], $conf["third_hook_signup"], $conf["third_pwd_signup"]];
    $conf_arr["fourth"] = [$conf["fourth_rcauth_userid"], htmlspecialchars_decode($conf["fourth_rcauth_apikey"]), $conf["fourth_acc_name"], $conf["fourth_hook_signup"], $conf["fourth_pwd_signup"]];
            if (getWver() < "7.0.0") {
            require ROOTDIR . "/includes/countriescallingcodes.php";
        } else {
            $result = file_get_contents(ROOTDIR . "/resources/country/dist.countries.json");
            $json = json_decode($result, true);
            $countries = [];
            foreach ($json as $k => $v) {
                $countrycallingcodes[$k] = $v["callingCode"];
            }
        }
        $email = $vars["email"];
        $result = Illuminate\Database\Capsule\Manager::table("tblclients")->where("email", "=", $email)->select("language")->get();
        $language = strtolower($result[0]->language);
        if (empty($language)) {
            $language = strtolower($CONFIG["Language"]);
        }
        $langPref = checkLang($language);
        foreach ($conf_arr as $confstring) {
            if ($confstring[3] != "on" && !empty($confstring[0])) {
                list($rcauth_userid, $rcauth_password, $logicbox_registrar, $use_internal_cupwd) = $confstring;
                $method = "GET";
                $apifunction = "/api/customers/details.json";
                $data = ["username" => $email];
                $arrXml = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
                if ($arrXml["status"] == "ERROR") {
                    if ($use_internal_cupwd == "on") {
                        $whmcscupwd = $vars["password"];
                        $withpwd = "whmcs user";
                    } else {
                        $whmcscupwd = "";
                        $withpwd = "random generated";
                    }
                    $newcustomer = $email;
                    $signup_rc_customer_id = createOBcustomer($newcustomer, $rchttp_api, $rcauth_userid, $rcauth_password, $whmcscupwd);
                    if (is_numeric($signup_rc_customer_id)) {
                        logActivity("Customer successfully registered with " . $withpwd . " password at " . $logicbox_registrar . " (" . $rcauth_userid . ") - User: " . $vars["email"] . " - User ID: " . $vars["userid"]);
                    } else {
                        logActivity("Customer registration for User " . $vars["email"] . " failed at " . $logicbox_registrar . " (" . $rcauth_userid . "): " . $signup_rc_customer_id["message"] . " - User ID: " . $vars["userid"]);
                    }
                }
        }
    }
}

?>