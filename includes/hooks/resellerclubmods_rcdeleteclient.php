<?php
if (!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
require ROOTDIR . "/modules/addons/resellerclubmods_tools/incs/functions.php";
add_hook("PreDeleteClient", 1, "delete_rc_account");
function delete_rc_account($vars)
{
    $conf = [];
    foreach (Illuminate\Database\Capsule\Manager::table("tbladdonmodules")->where("module", "resellerclubmods_tools")->get() as $addonvars) {
        $conf[$addonvars->setting] = $addonvars->value;
    }
    $rchttp_api = $conf["rchttp_api"];
    $conf_arr = [];
    $conf_arr["first"] = [$conf["first_rcauth_userid"], htmlspecialchars_decode($conf["first_rcauth_apikey"]), $conf["first_acc_name"], $conf["first_hook_delete"]];
    $conf_arr["second"] = [$conf["second_rcauth_userid"], htmlspecialchars_decode($conf["second_rcauth_apikey"]), $conf["second_acc_name"], $conf["second_hook_delete"]];
    $conf_arr["third"] = [$conf["third_rcauth_userid"], htmlspecialchars_decode($conf["third_rcauth_apikey"]), $conf["third_acc_name"], $conf["third_hook_delete"]];
    $conf_arr["fourth"] = [$conf["fourth_rcauth_userid"], htmlspecialchars_decode($conf["fourth_rcauth_apikey"]), $conf["fourth_acc_name"], $conf["fourth_hook_delete"]];
            $rcmdebuginfo = getDebuginfos();
        $modulename = $rcmdebuginfo["modulename"];
        $debug_addinfo = $rcmdebuginfo["debug_addinfo"];
        $userid = $vars["userid"];
        $result = Illuminate\Database\Capsule\Manager::table("tblclients")->where("id", "=", $userid)->select("email")->get();
        $email = strtolower($result[0]->email);
        foreach ($conf_arr as $confstring) {
            if ($confstring[3] != "on" && !empty($confstring[0])) {
                list($rcauth_userid, $rcauth_password, $logicbox_registrar) = $confstring;
                $method = "GET";
                $apifunction = "/api/customers/details.json";
                $data = ["username" => $email];
                $arrXml = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
                if ($arrXml["status"] != "ERROR") {
                    $customerId = $arrXml["customerid"];
                    $method = "POST";
                    $apifunction = "/api/customers/delete.json";
                    $data = ["customer-id" => $customerId];
                    $response_customerdelete = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
                    $action = "Delete Customer";
                    $requeststring = $apifunction . " [reseller data protected] " . serialize_data($data);
                    $responsedata = ["rcmdebug" => $debug_addinfo, "apidebug" => $response_customerdelete];
                    rcm_log_module_call($modulename, $action, $requeststring, $responsedata);
                    if ($response_customerdelete == "true") {
                        logActivity("Customer successfully deleted at " . $logicbox_registrar . " (" . $rcauth_userid . ") - User: " . $email);
                    } else {
                        logActivity("Automatic Customer Deletion for User " . $email . " at " . $logicbox_registrar . " (" . $rcauth_userid . ") failed: " . $response_customerdelete["message"] . " - User ID: " . $vars["userid"]);
                    }
                } else {
                    logActivity("Automatic Customer Deletion for User " . $email . " failed: User does not exist at " . $logicbox_registrar . " (" . $rcauth_userid . ") - User ID: " . $vars["userid"]);
                }
        }
    }
}

?>