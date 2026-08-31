<?php
if (!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
require ROOTDIR . "/modules/addons/resellerclubmods_tools/incs/functions.php";
add_hook("AfterRegistrarRegistration", 1, "check_funds_threshold");
add_hook("AfterRegistrarTransfer", 1, "check_funds_threshold");
add_hook("AfterRegistrarRenewal", 1, "check_funds_threshold");
add_hook("AfterModuleCreate", 1, "check_funds_threshold");
add_hook("AfterModuleRenew", 1, "check_funds_threshold");
function check_funds_threshold($params)
{
    $conf = [];
    foreach (Illuminate\Database\Capsule\Manager::table("tbladdonmodules")->where("module", "resellerclubmods_tools")->get() as $addonvars) {
        $conf[$addonvars->setting] = $addonvars->value;
    }
    $rchttp_api = $conf["rchttp_api"];
    $maileradmin = $conf["maileradmin"];
    $conf_arr = [];
    $conf_arr["first"] = [$conf["first_rcauth_userid"], $conf["first_rcauth_apikey"], $conf["first_acc_name"], $conf["first_threshold"], $conf["first_threshold_value"]];
    $conf_arr["second"] = [$conf["second_rcauth_userid"], $conf["second_rcauth_apikey"], $conf["second_acc_name"], $conf["second_threshold"], $conf["second_threshold_value"]];
    $conf_arr["third"] = [$conf["third_rcauth_userid"], $conf["third_rcauth_apikey"], $conf["third_acc_name"], $conf["third_threshold"], $conf["third_threshold_value"]];
    $conf_arr["fourth"] = [$conf["fourth_rcauth_userid"], $conf["fourth_rcauth_apikey"], $conf["fourth_acc_name"], $conf["fourth_threshold"], $conf["fourth_threshold_value"]];
            $result = Illuminate\Database\Capsule\Manager::table("tbladmins")->where("username", "=", $maileradmin)->select("language")->get();
        $adminlang = $result[0]->language;
        $get_langfile = getAdminlang($adminlang);
        require ROOTDIR . $get_langfile;
        $LANG = $_ADDONLANG;
        foreach ($conf_arr as $confstring) {
            if ($confstring[3] != "on" && !empty($confstring[0])) {
                list($rcauth_userid, $rcauth_password, $account_name) = $confstring;
                $method = "GET";
                $apifunction = "/api/resellers/details.json";
                $resellerdetails_arrXml = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
                $ob_url = $resellerdetails_arrXml["parentbrandingurl"];
                if (!empty($confstring[4]) && is_numeric($confstring[4])) {
                    $reseller_fundthreshold = round($confstring[4], 2);
                } else {
                    $reseller_fundthreshold = round($resellerdetails_arrXml["fundthreshold"], 2);
                }
                $method = "GET";
                $apifunction = "/api/billing/reseller-balance.json";
                $data = ["reseller-id" => $rcauth_userid];
                $xml_fundsbalancedetails = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
                $sellingcurrency_lockedbalance = round($xml_fundsbalancedetails["sellingcurrencylockedbalance"], 2);
                $sellingcurrency_balance = round($xml_fundsbalancedetails["sellingcurrencybalance"], 2);
                $sellingcurrency_availablebalance = $sellingcurrency_balance - $sellingcurrency_lockedbalance;
                $sellingcurrency_availablebalance = round($sellingcurrency_availablebalance, 2);
                $result = Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodsfunds")->where("reseller_id", "=", $rcauth_userid)->select("reseller_id", "fundthreshold", "fundavailable")->get();
                $reseller_id = $result[0]->reseller_id;
                $fundthreshold = $result[0]->fundthreshold;
                $fundavailable = $result[0]->fundavailable;
                if (empty($reseller_id)) {
                    $values = ["reseller_id" => $rcauth_userid, "fundavailable" => $sellingcurrency_availablebalance, "fundthreshold" => $reseller_fundthreshold];
                    $result = Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodsfunds")->insert($values);
                }
                if ($fundthreshold != $reseller_fundthreshold) {
                    $values = ["fundthreshold" => $reseller_fundthreshold];
                    $result_update = Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodsfunds")->where("reseller_id", "=", $rcauth_userid)->update($values);
                }
                if ($fundavailable == "0.00") {
                    $values = ["fundavailable" => $sellingcurrency_availablebalance];
                    $result_update = Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodsfunds")->where("reseller_id", "=", $rcauth_userid)->update($values);
                }
                if ($sellingcurrency_availablebalance < $fundavailable) {
                    $values = ["fundavailable" => $sellingcurrency_availablebalance];
                    $result_update = Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodsfunds")->where("reseller_id", "=", $rcauth_userid)->update($values);
                    if ($sellingcurrency_availablebalance <= $reseller_fundthreshold) {
                        $fundsoutput .= "<div style=\"font-family: verdana; font-size: 11px; font-weight: normal;\">";
                        $fundsoutput .= "<p>" . $LANG["thresholdmail01"] . " " . $rcauth_userid . " " . $LANG["thresholdmail02"] . "</p>";
                        $fundsoutput .= $LANG["totalfunds"] . " <strong>" . $sellingcurrency_balance . "</strong><br />";
                        $fundsoutput .= $LANG["blockedfunds"] . " <strong>" . $sellingcurrency_lockedbalance . "</strong><br />";
                        $fundsoutput .= $LANG["availablefunds"] . " <strong style=\"color:#cc0000;\">" . $sellingcurrency_availablebalance . "</strong><br />";
                        $fundsoutput .= $LANG["threshold"] . " <strong style=\"color:#73CB0B;\">" . $reseller_fundthreshold . "</strong>";
                        $fundsoutput .= "<p>" . $LANG["thresholdmail03"] . "<br />";
                        $fundsoutput .= $LANG["thresholdmail04"] . " <a href=\"http://" . $ob_url . "/reseller\">http://" . $ob_url . "/reseller</a> " . $LANG["thresholdmail05"] . "</p>";
                        $mailtpltype = "RCM Funds Threshold Report";
                        $mailsubject = $LANG["thresholdmailsubject"] . " " . $account_name . " (" . $rcauth_userid . ")";
                        $mailmessage = $fundsoutput;
                        $apiadminuser = $maileradmin;
                        adminemailmessages($mailtpltype, $mailsubject, $mailmessage, $apiadminuser);
                        unset($fundsoutput);
                    }
                } else if ($fundavailable < $sellingcurrency_availablebalance) {
                    $values = ["fundavailable" => $sellingcurrency_availablebalance];
                    $result_update = Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodsfunds")->where("reseller_id", "=", $rcauth_userid)->update($values);
                }
        }
    }
}

?>