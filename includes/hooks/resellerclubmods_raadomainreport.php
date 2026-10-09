<?php
if (!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
require ROOTDIR . "/modules/addons/resellerclubmods_tools/incs/functions.php";
add_hook("DailyCronJob", 4, "raa_domain_report");
function raa_domain_report($vars)
{
    $conf = [];
    foreach (Illuminate\Database\Capsule\Manager::table("tbladdonmodules")->where("module", "resellerclubmods_tools")->get() as $addonvars) {
        $conf[$addonvars->setting] = $addonvars->value;
    }
    $raa_domain_check = $conf["raa_domain_check"];
    $maileradmin = $conf["maileradmin"];
    $rchttp_api = $conf["rchttp_api"];
    $conf_arr = [];
    $conf_arr[$conf["first_rcauth_userid"]] = [$conf["first_rcauth_userid"], $conf["first_rcauth_apikey"], $conf["first_domainregistrar"]];
    $conf_arr[$conf["second_rcauth_userid"]] = [$conf["second_rcauth_userid"], $conf["second_rcauth_apikey"], $conf["second_domainregistrar"]];
    $conf_arr[$conf["third_rcauth_userid"]] = [$conf["third_rcauth_userid"], $conf["third_rcauth_apikey"], $conf["third_domainregistrar"]];
    $conf_arr[$conf["fourth_rcauth_userid"]] = [$conf["fourth_rcauth_userid"], $conf["fourth_rcauth_apikey"], $conf["fourth_domainregistrar"]];
    $rcm_daily_lock = rcm_try_lock("dailycron_hooks");
    if ($rcm_daily_lock === false) {
        logActivity("Cron Job (RCM): Skipping RAA domain report — daily hooks busy");
        return;
    }
    try {
            if (!class_exists("idna_convert")) {
            require_once ROOTDIR . "/modules/addons/resellerclubmods_tools/incs/idnclass.php";
        }
        $IDN = new idna_convert();
        global $CONFIG;
        global $customadminpath;
        $systemurl = !empty($CONFIG["SystemSSLURL"]) ? $CONFIG["SystemSSLURL"] : $CONFIG["SystemURL"];
        $result = Illuminate\Database\Capsule\Manager::table("tbladmins")->where("username", "=", $maileradmin)->select("language")->get();
        $adminlang = $result[0]->language;
        $get_langfile = getAdminlang($adminlang);
        require ROOTDIR . $get_langfile;
        $LANG = $_ADDONLANG;
        if (date_default_timezone_get()) {
            $is_default_tz = date_default_timezone_get();
        }
        if ($is_default_tz != "UTC") {
            date_default_timezone_set("UTC");
        }
        $datetimenow = date("Y-m-d H:i:s");
        $raareportmail .= "<div style=\"font-family:Verdana;font-size:11px\">";
        $raareportmail .= "<p>" . $LANG["raareportheader"] . " - " . $datetimenow . " UTC</p>";
        $method = "GET";
        $countp = 1;
        foreach ($conf_arr as $confstring) {
            if (!empty($confstring[0]) && !empty($confstring[2]) && !empty($confstring[2])) {
                list($rcauth_userid, $rcauth_password, $registrar) = $confstring;
                $method = "GET";
                $apifunction = "/api/domains/search.json";
                $data = ["status" => "Pending Verification", "no-of-records" => 500, "page-no" => 1];
                $domainsearch_pending = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
                $apifunction = "/api/domains/search.json";
                $data = ["status" => "Failed Verification", "no-of-records" => 500, "page-no" => 1];
                $domainsearch_failed = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
                $domainsearch = array_merge($domainsearch_pending, $domainsearch_failed);
                if (isset($domainsearch[0])) {
                    foreach ($domainsearch as $domkey => $domvalue) {
                        if (is_array($domvalue)) {
                            $nump = $countp++;
                            $apifunction = "/api/domains/details.json";
                            $domaindetailsarr = ["DomainStatus", "RegistrantContactDetails"];
                            $data = ["order-id" => $domvalue["orders.orderid"], "options" => $domaindetailsarr];
                            $get_domaindetails = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
                            $domain_name = $IDN->decode($domvalue["entity.description"]);
                            $raastatus = $get_domaindetails["raaVerificationStatus"];
                            $registrantemail = $get_domaindetails["registrantcontact"]["emailaddr"];
                            if ($CONFIG["DateFormat"] == "DD/MM/YYYY") {
                                $raa_starttime = date("d/m/Y H:i:s", $get_domaindetails["raaVerificationStartTime"]);
                                $raa_endtime = date("d/m/Y H:i:s", $get_domaindetails["raaVerificationStartTime"] + 1296000);
                            } else if ($CONFIG["DateFormat"] == "DD.MM.YYYY") {
                                $raa_starttime = date("d.m.Y H:i:s", $get_domaindetails["raaVerificationStartTime"]);
                                $raa_endtime = date("d.m.Y H:i:s", $get_domaindetails["raaVerificationStartTime"] + 1296000);
                            } else if ($CONFIG["DateFormat"] == "DD-MM-YYYY") {
                                $raa_starttime = date("d-m-Y H:i:s", $get_domaindetails["raaVerificationStartTime"]);
                                $raa_endtime = date("d-m-Y H:i:s", $get_domaindetails["raaVerificationStartTime"] + 1296000);
                            } else if ($CONFIG["DateFormat"] == "MM/DD/YYYY") {
                                $raa_starttime = date("m/d/Y H:i:s", $get_domaindetails["raaVerificationStartTime"]);
                                $raa_endtime = date("m/d/Y H:i:s", $get_domaindetails["raaVerificationStartTime"] + 1296000);
                            } else if ($CONFIG["DateFormat"] == "YYYY/MM/DD") {
                                $raa_starttime = date("Y/m/d H:i:s", $get_domaindetails["raaVerificationStartTime"]);
                                $raa_endtime = date("Y/m/d H:i:s", $get_domaindetails["raaVerificationStartTime"] + 1296000);
                            } else if ($CONFIG["DateFormat"] == "YYYY-MM-DD") {
                                $raa_starttime = date("Y-m-d H:i:s", $get_domaindetails["raaVerificationStartTime"]);
                                $raa_endtime = date("Y-m-d H:i:s", $get_domaindetails["raaVerificationStartTime"] + 1296000);
                            }
                            $datediff = $get_domaindetails["raaVerificationStartTime"] + 1209600 - strtotime(toMySQLDate(getTodaysDate()));
                            $daysleft = ceil($datediff / 60 / 60 / 24) . " " . $LANG["raaenddaystitle"];
                            $raareportmail .= "<strong>" . $domain_name . "</strong> - " . $registrar . " (ID: " . $rcauth_userid . ") - " . $raastatus . " - " . $raa_starttime . " - " . $raa_endtime . " - " . $daysleft . "<br />";
                        }
                    }
                }
            }
        }
        date_default_timezone_set($is_default_tz);
        $raareportmail .= "<p>" . $LANG["raamanagementtitle"] . " - <a href=\"" . $systemurl . "/" . $customadminpath . "/addonmodules.php?module=resellerclubmods_tools&domain=raamanagement\">" . $systemurl . "/" . $customadminpath . "/addonmodules.php?module=resellerclubmods_tools&domain=raamanagement</a></p>";
        $raareportmail .= "</div>";
        if ($nump == 0) {
            logActivity("Cron Job (RCM): " . $LANG["noraapendingmessage"]);
        } else {
            logActivity("Cron Job (RCM): " . $nump . " " . $LANG["raapendingmessage"]);
            if ($raa_domain_check != "on") {
                $mailtpltype = "RCM Domain RAA Report";
                $mailsubject = $LANG["raamailsubject"];
                $mailmessage = $raareportmail;
                $apiadminuser = $maileradmin;
                adminemailmessages($mailtpltype, $mailsubject, $mailmessage, $apiadminuser);
            }
        }
    } finally {
        rcm_release_lock($rcm_daily_lock);
    }
}

?>