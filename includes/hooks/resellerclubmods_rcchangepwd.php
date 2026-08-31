<?php
if (!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
require ROOTDIR . "/modules/addons/resellerclubmods_tools/incs/functions.php";
add_hook("ClientChangePassword", 1, "change_rc_cupwd");
function change_rc_cupwd($vars)
{
    $conf = [];
    foreach (Illuminate\Database\Capsule\Manager::table("tbladdonmodules")->where("module", "resellerclubmods_tools")->get() as $addonvars) {
        $conf[$addonvars->setting] = $addonvars->value;
    }
    $rchttp_api = $conf["rchttp_api"];
    $conf_arr = [];
    $conf_arr["first"] = [$conf["first_rcauth_userid"], htmlspecialchars_decode($conf["first_rcauth_apikey"]), $conf["first_acc_name"], $conf["first_pwd_modify"]];
    $conf_arr["second"] = [$conf["second_rcauth_userid"], htmlspecialchars_decode($conf["second_rcauth_apikey"]), $conf["second_acc_name"], $conf["second_pwd_modify"]];
    $conf_arr["third"] = [$conf["third_rcauth_userid"], htmlspecialchars_decode($conf["third_rcauth_apikey"]), $conf["third_acc_name"], $conf["third_pwd_modify"]];
    $conf_arr["fourth"] = [$conf["fourth_rcauth_userid"], htmlspecialchars_decode($conf["fourth_rcauth_apikey"]), $conf["fourth_acc_name"], $conf["fourth_pwd_modify"]];
            $userid = $vars["userid"];
        $whmcscupwd = $vars["password"];
        $rcmdebuginfo = getDebuginfos();
        $modulename = $rcmdebuginfo["modulename"];
        $debug_addinfo = $rcmdebuginfo["debug_addinfo"];
        $result = Illuminate\Database\Capsule\Manager::table("tblclients")->where("id", "=", $userid)->select("email")->get();
        $email = strtolower($result[0]->email);
        foreach ($conf_arr as $confstring) {
            if ($confstring[3] == "on" && !empty($confstring[0])) {
                list($rcauth_userid, $rcauth_password, $logicbox_registrar) = $confstring;
                $method = "GET";
                $apifunction = "/api/customers/details.json";
                $data = ["username" => $email];
                $arrXml = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
                if (is_numeric($arrXml["customerid"])) {
                    $method = "POST";
                    $apifunction = "/api/customers/change-password.json";
                    $data = ["customer-id" => $arrXml["customerid"], "new-passwd" => $whmcscupwd];
                    $changepasswd = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
                    $action = "change password";
                    $requeststring = $apifunction . " [reseller data protected] " . serialize_data($data);
                    $responsedata = ["rcmdebug" => $debug_addinfo, "apidebug" => $changepasswd];
                    logModuleCall($modulename, $action, $requeststring, $responsedata);
                    if ($changepasswd["status"] != "ERROR") {
                        logActivity("Customer password successfully changed in " . $logicbox_registrar . " (" . $rcauth_userid . ") - User: " . $vars["email"] . " - User ID: " . $vars["userid"]);
                    } else {
                        logActivity("Customer password change failed for User " . $vars["email"] . " - User ID: " . $vars["userid"] . " in " . $logicbox_registrar . " (" . $rcauth_userid . "): " . $changepasswd["message"]);
                    }
                } else {
                    logActivity("No password change has been applied. Customer account not found for " . $email . " at " . $logicbox_registrar . " (" . $rcauth_userid . ")");
                }
        }
    }
}

?>