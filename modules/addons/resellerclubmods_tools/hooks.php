<?php
if (!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
if (!function_exists("getWver")) {
    function getWver()
    {
        global $CONFIG;
        $ver = isset($CONFIG["Version"]) ? $CONFIG["Version"] : "";
        $cut = strstr($ver, "-", true);
        return $cut !== false ? $cut : $ver;
    }
}
require ROOTDIR . "/modules/addons/resellerclubmods_tools/incs/functions.php";
add_hook("AdminHomeWidgets", 1, "widget_resellerclubmods_tools");
if (0 <= version_compare(getWver(), "6.0.0")) {
    add_hook("ClientAreaPrimarySidebar", 1, function (WHMCS\View\Menu\Item $primarySidebar) {
        global $CONFIG;
        require ROOTDIR . "/configuration.php";
        $vars = [];
        foreach (Illuminate\Database\Capsule\Manager::table("tbladdonmodules")->where("module", "resellerclubmods_tools")->get() as $addonvars) {
            $vars[$addonvars->setting] = $addonvars->value;
        }
        $allow_movedomain = $vars["allow_movedomain"];
        $movedomain_permission = $vars["movedomain_permission"];
        if ($movedomain_permission != "Client and Admin" && !isset($_SESSION["adminid"])) {
            $accessdenied = "on";
        }
        $domainid = 0;
        if (isset($_REQUEST["domainid"]) && ctype_digit((string) $_REQUEST["domainid"])) {
            $domainid = (int) $_REQUEST["domainid"];
        } else if (isset($_REQUEST["id"]) && ctype_digit((string) $_REQUEST["id"])) {
            $domainid = (int) $_REQUEST["id"];
        }
        if (!$domainid) {
            return NULL;
        }
        if ($allow_movedomain == "on" && $accessdenied != "on" && 0 < $domainid) {
            try {
                $domainRow = Illuminate\Database\Capsule\Manager::table("tbldomains")->where("id", "=", $domainid)->select("domain", "registrar", "status")->first();
            } catch (Throwable $e) {
                $domainRow = NULL;
            }
            if ($domainRow) {
                $domain = (string) $domainRow->domain;
                $isregistrar = (string) $domainRow->registrar;
                $isstatus = (string) $domainRow->status;
                $isregistrar_array = ["resellerclubrcm", "netearthonercm", "resellercamprcm", "stargatercm", "resellerclub", "netearthone", "resellercamp", "stargate"];
                if (isset($rcm_lb_registrars) && is_array($rcm_lb_registrars)) {
                    $isregistrar_array = array_merge($isregistrar_array, $rcm_lb_registrars);
                }
                if (in_array($isregistrar, $isregistrar_array, true) && !is_null($primarySidebar->getChild("Domain Details Management")) && $isstatus == "Active") {
                    $primarySidebar->getChild("Domain Details Management")->addChild("Move Domain", ["label" => $_LANG["clientareamovetitle"], "uri" => "index.php?m=resellerclubmods_tools&action=movedomain&domain=" . urlencode($domain) . "&domainid=" . (int) $domainid, "order" => 120]);
                }
            }
        }
    });
}
function widget_resellerclubmods_tools()
{
    $vars = [];
    foreach (Illuminate\Database\Capsule\Manager::table("tbladdonmodules")->where("module", "resellerclubmods_tools")->get() as $addonvars) {
        $vars[$addonvars->setting] = $addonvars->value;
    }
    if ($vars["fundsbalance"]) {
        $adminid = $_SESSION["adminid"];
        $result = Illuminate\Database\Capsule\Manager::table("tbladmins")->where("id", "=", $adminid)->select("language")->get();
        $adminlang = $result[0]->language;
        $is_adminlang = isset($_SESSION["adminlang"]) ? $_SESSION["adminlang"] : $adminlang;
        $is_adminlang = strtolower($is_adminlang);
        if ($is_adminlang != $adminlang && file_exists(ROOTDIR . "/modules/addons/resellerclubmods_tools/lang/" . $is_adminlang . ".php")) {
            $adminlang = $is_adminlang;
        }
        $get_langfile = getAdminlang($adminlang);
        require ROOTDIR . $get_langfile;
            if ($vars["totalfundscolor"]) {
                $totalfundscolor = $vars["totalfundscolor"];
            } else {
                $totalfundscolor = "#000000";
            }
            if ($vars["blockedfundscolor"]) {
                $blockedfundscolor = $vars["blockedfundscolor"];
            } else {
                $blockedfundscolor = "#CC0000";
            }
            if ($vars["availfundscolor"]) {
                $availfundscolor = $vars["availfundscolor"];
            } else {
                $availfundscolor = "#779500";
            }
            if ($vars["fontsize"]) {
                $fontsize = $vars["fontsize"];
            } else {
                $fontsize = 12;
            }
            if ($vars["fontfamily"]) {
                $fontfamily = $vars["fontfamily"] . ";";
            } else {
                $fontfamily = "Lucida Grande, Lucida Sans Unicode, Verdana, Arial, sans-serif;";
            }
            $method = "GET";
            $rchttp_api = $vars["rchttp_api"];
            $first_domainregistrar = $vars["first_domainregistrar"];
            $first_threshold_value = $vars["first_threshold_value"];
            $second_domainregistrar = $vars["second_domainregistrar"];
            $second_threshold_value = $vars["second_threshold_value"];
            $third_domainregistrar = $vars["third_domainregistrar"];
            $third_threshold_value = $vars["third_threshold_value"];
            $fourth_domainregistrar = $vars["fourth_domainregistrar"];
            $fourth_threshold_value = $vars["fourth_threshold_value"];
            $currencysymbol = $vars["currencysymbol"];
            $currency_array = ["EUR" => "&euro;", "CAD" => "\$", "MXN" => "\$", "USD" => "\$", "ARS" => "\$", "AUD" => "\$", "CLP" => "\$", "COP" => "\$", "ECS" => "\$", "GIP" => "&pound;", "HKD" => "&pound;", "GBP" => "&pound;"];
            $start_content = "<div class=\"widget-content-padded\">";
            if (!empty($vars["first_acc_name"]) && $vars["first_show_fundsbalance"] != "on") {
                if ($first_domainregistrar == "resellerclub") {
                    $registrar_icon = "<img src=\"../modules/addons/resellerclubmods_tools/img/resellerclubicon.png\" align=\"absmiddle\" border=\"0\">";
                } else if ($first_domainregistrar == "netearthone") {
                    $registrar_icon = "<img src=\"../modules/addons/resellerclubmods_tools/img/neoicon.png\" align=\"absmiddle\" border=\"0\">";
                } else if ($first_domainregistrar == "stargate") {
                    $registrar_icon = "<img src=\"../modules/addons/resellerclubmods_tools/img/stargateicon.png\" align=\"absmiddle\" border=\"0\">";
                } else if ($first_domainregistrar == "resellercamp") {
                    $registrar_icon = "<img src=\"../modules/addons/resellerclubmods_tools/img/resellercampicon.png\" align=\"absmiddle\" border=\"0\">";
                } else {
                    $registrar_icon = "<img src=\"../modules/addons/resellerclubmods_tools/img/otherlb.png\" align=\"absmiddle\" border=\"0\">";
                }
                $account_name = $vars["first_acc_name"];
                $rcauth_userid = $vars["first_rcauth_userid"];
                $rcauth_password = htmlspecialchars_decode($vars["first_rcauth_apikey"]);
                $apifunction = "/api/resellers/details.json";
                $data = ["reseller-id" => $rcauth_userid];
                $resellerdetails_arrXml = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
                $reseller_buycurrency = $resellerdetails_arrXml["parentsellingcurrencysymbol"];
                $is_mplicator = 1;
                $is_1000s = is_inthousands($reseller_buycurrency);
                if ($is_1000s == 1) {
                    $is_mplicator = 1000;
                }
                if (empty($reseller_buycurrency)) {
                    $content1 = "<div style=\"font-size:" . $fontsize . "px;font-family:" . $fontfamily . "\">\r\n\t\t\t\t\t\t\t\t" . $registrar_icon . "&nbsp;<u><strong>" . $account_name . "&nbsp;-&nbsp;" . $_ADDONLANG["fundstitle"] . "</strong></u><br />\r\n\t\t\t\t\t\t\t\t<div>Error: No API response received</div>\r\n\t\t\t\t\t\t\t\t</div><br />";
                } else {
                    if ($currencysymbol == "on") {
                        foreach ($currency_array as $k => $v) {
                            if ($k == $reseller_buycurrency) {
                                $currencycode = $v;
                            }
                        }
                        if (!empty($currencycode)) {
                            $reseller_buycurrency = "";
                        }
                        $reseller_buysymbol = $currencycode;
                    }
                    if (!empty($first_threshold_value) && is_numeric($first_threshold_value)) {
                        $reseller_fundthreshold = round($first_threshold_value * $is_mplicator, 2);
                    } else {
                        $reseller_fundthreshold = round($resellerdetails_arrXml["fundthreshold"] * $is_mplicator, 2);
                    }
                    $apifunction = "/api/billing/reseller-balance.json";
                    $data = ["reseller-id" => $rcauth_userid];
                    $xml_fundsbalancedetails = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
                    $newavailable_balance = $xml_fundsbalancedetails["sellingcurrencybalance"];
                    $newblocked_balance = $xml_fundsbalancedetails["sellingcurrencylockedbalance"];
                    $blockedbalance = round($newblocked_balance * $is_mplicator, 2);
                    $totalbalance = round($newavailable_balance * $is_mplicator, 2);
                    $availablebalance = round($totalbalance - $blockedbalance * $is_mplicator, 2);
                    $threshold_percent = round($reseller_fundthreshold * 0, 2);
                    if ($availablebalance < $threshold_percent && $reseller_fundthreshold < $availablebalance) {
                        $fundsalert1 = "<div style=\"font-size:" . $fontsize . "px;font-family:" . $fontfamily . "\"><img src=\"../modules/addons/resellerclubmods_tools/img/mediumpriority.png\" align=\"absmiddle\" border=\"0\"> " . $_ADDONLANG["prioritymedium"] . " (" . $reseller_buysymbol . $reseller_fundthreshold . " " . $reseller_buycurrency . ")</div>";
                    } else if ($reseller_fundthreshold < $availablebalance) {
                        $fundsalert1 = "<div style=\"font-size:" . $fontsize . "px;font-family:" . $fontfamily . "\"><img src=\"../modules/addons/resellerclubmods_tools/img/lowpriority.png\" align=\"absmiddle\" border=\"0\"> " . $_ADDONLANG["prioritylow"] . " (" . $reseller_buysymbol . $reseller_fundthreshold . " " . $reseller_buycurrency . ")</div>";
                    } else if ($availablebalance <= $reseller_fundthreshold) {
                        $fundsalert1 = "<div style=\"font-size:" . $fontsize . "px;font-family:" . $fontfamily . "\"><img src=\"../modules/addons/resellerclubmods_tools/img/highpriority.png\" align=\"absmiddle\" border=\"0\"> " . $_ADDONLANG["priorityhigh"] . " (" . $reseller_buysymbol . $reseller_fundthreshold . " " . $reseller_buycurrency . ")</div>";
                    }
                    $content1 = "<div style=\"font-size:" . $fontsize . "px;font-family:" . $fontfamily . "\">\r\n\t\t\t\t\t\t\t\t" . $registrar_icon . "&nbsp;<u><strong>" . $account_name . "&nbsp;-&nbsp;" . $_ADDONLANG["fundstitle"] . "</strong></u><br />" . $_ADDONLANG["totalfunds"] . "\r\n\t\t\t\t\t\t\t\t&nbsp;<strong style=\"color:" . $totalfundscolor . "\">" . $reseller_buysymbol . $totalbalance . "&nbsp;" . $reseller_buycurrency . "</strong>\r\n\t\t\t\t\t\t\t\t&nbsp;" . $_ADDONLANG["blockedfunds"] . "&nbsp;<strong style=\"color:" . $blockedfundscolor . "\">" . $reseller_buysymbol . $blockedbalance . "&nbsp;" . $reseller_buycurrency . "</strong>\r\n\t\t\t\t\t\t\t\t&nbsp; " . $_ADDONLANG["availablefunds"] . "&nbsp;<strong style=\"color:" . $availfundscolor . "\">" . $reseller_buysymbol . $availablebalance . "&nbsp;" . $reseller_buycurrency . "</strong>\r\n\t\t\t\t\t\t\t\t<br />" . $fundsalert1 . "</div><br />";
                }
            }
            if (!empty($vars["second_acc_name"]) && $vars["second_show_fundsbalance"] != "on") {
                if ($second_domainregistrar == "resellerclub") {
                    $registrar_icon = "<img src=\"../modules/addons/resellerclubmods_tools/img/resellerclubicon.png\" align=\"absmiddle\" border=\"0\">";
                } else if ($second_domainregistrar == "netearthone") {
                    $registrar_icon = "<img src=\"../modules/addons/resellerclubmods_tools/img/neoicon.png\" align=\"absmiddle\" border=\"0\">";
                } else if ($second_domainregistrar == "stargate") {
                    $registrar_icon = "<img src=\"../modules/addons/resellerclubmods_tools/img/stargateicon.png\" align=\"absmiddle\" border=\"0\">";
                } else if ($second_domainregistrar == "resellercamp") {
                    $registrar_icon = "<img src=\"../modules/addons/resellerclubmods_tools/img/resellercampicon.png\" align=\"absmiddle\" border=\"0\">";
                } else {
                    $registrar_icon = "<img src=\"../modules/addons/resellerclubmods_tools/img/otherlb.png\" align=\"absmiddle\" border=\"0\">";
                }
                $account_name = $vars["second_acc_name"];
                $rcauth_userid = $vars["second_rcauth_userid"];
                $rcauth_password = htmlspecialchars_decode($vars["second_rcauth_apikey"]);
                $apifunction = "/api/resellers/details.json";
                $data = ["reseller-id" => $rcauth_userid];
                $resellerdetails_arrXml = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
                $reseller_buycurrency = $resellerdetails_arrXml["parentsellingcurrencysymbol"];
                $is_mplicator = 1;
                $is_1000s = is_inthousands($reseller_buycurrency);
                if ($is_1000s == 1) {
                    $is_mplicator = 1000;
                }
                if (empty($reseller_buycurrency)) {
                    $content2 = "<div style=\"font-size:" . $fontsize . "px;font-family:" . $fontfamily . "\">\r\n\t\t\t\t\t\t\t\t" . $registrar_icon . "&nbsp;<u><strong>" . $account_name . "&nbsp;-&nbsp;" . $_ADDONLANG["fundstitle"] . "</strong></u><br />\r\n\t\t\t\t\t\t\t\t<div>Error: No API response received</div>\r\n\t\t\t\t\t\t\t\t</div><br />";
                } else {
                    if ($currencysymbol == "on") {
                        foreach ($currency_array as $k => $v) {
                            if ($k == $reseller_buycurrency) {
                                $currencycode = $v;
                            }
                        }
                        if (!empty($currencycode)) {
                            $reseller_buycurrency = "";
                        }
                        $reseller_buysymbol = $currencycode;
                    }
                    if (!empty($second_threshold_value) && is_numeric($second_threshold_value)) {
                        $reseller_fundthreshold = round($second_threshold_value * $is_mplicator, 2);
                    } else {
                        $reseller_fundthreshold = round($resellerdetails_arrXml["fundthreshold"] * $is_mplicator, 2);
                    }
                    $apifunction = "/api/billing/reseller-balance.json";
                    $data = ["reseller-id" => $rcauth_userid];
                    $xml_fundsbalancedetails = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
                    $newavailable_balance = $xml_fundsbalancedetails["sellingcurrencybalance"];
                    $newblocked_balance = $xml_fundsbalancedetails["sellingcurrencylockedbalance"];
                    $blockedbalance = round($newblocked_balance * $is_mplicator, 2);
                    $totalbalance = round($newavailable_balance * $is_mplicator, 2);
                    $availablebalance = round($totalbalance - $blockedbalance * $is_mplicator, 2);
                    $threshold_percent = round($reseller_fundthreshold * 0, 2);
                    if ($availablebalance < $threshold_percent && $reseller_fundthreshold < $availablebalance) {
                        $fundsalert2 = "<div style=\"font-size:" . $fontsize . "px;font-family:" . $fontfamily . "\"><img src=\"../modules/addons/resellerclubmods_tools/img/mediumpriority.png\" align=\"absmiddle\" border=\"0\"> " . $_ADDONLANG["prioritymedium"] . " (" . $reseller_buysymbol . $reseller_fundthreshold . " " . $reseller_buycurrency . ")</div>";
                    } else if ($reseller_fundthreshold < $availablebalance) {
                        $fundsalert2 = "<div style=\"font-size:" . $fontsize . "px;font-family:" . $fontfamily . "\"><img src=\"../modules/addons/resellerclubmods_tools/img/lowpriority.png\" align=\"absmiddle\" border=\"0\"> " . $_ADDONLANG["prioritylow"] . " (" . $reseller_buysymbol . $reseller_fundthreshold . " " . $reseller_buycurrency . ")</div>";
                    } else if ($availablebalance <= $reseller_fundthreshold) {
                        $fundsalert2 = "<div style=\"font-size:" . $fontsize . "px;font-family:" . $fontfamily . "\"><img src=\"../modules/addons/resellerclubmods_tools/img/highpriority.png\" align=\"absmiddle\" border=\"0\"> " . $_ADDONLANG["priorityhigh"] . " (" . $reseller_buysymbol . $reseller_fundthreshold . " " . $reseller_buycurrency . ")</div>";
                    }
                    $content2 = "<div style=\"font-size:" . $fontsize . "px;font-family:" . $fontfamily . "\">\r\n\t\t\t\t\t\t\t\t" . $registrar_icon . "&nbsp;<u><strong>" . $account_name . "&nbsp;-&nbsp;" . $_ADDONLANG["fundstitle"] . "</strong></u><br />" . $_ADDONLANG["totalfunds"] . "\r\n\t\t\t\t\t\t\t\t&nbsp;<strong style=\"color:" . $totalfundscolor . "\">" . $reseller_buysymbol . $totalbalance . "&nbsp;" . $reseller_buycurrency . "</strong>\r\n\t\t\t\t\t\t\t\t&nbsp;" . $_ADDONLANG["blockedfunds"] . "&nbsp;<strong style=\"color:" . $blockedfundscolor . "\">" . $reseller_buysymbol . $blockedbalance . "&nbsp;" . $reseller_buycurrency . "</strong>\r\n\t\t\t\t\t\t\t\t&nbsp; " . $_ADDONLANG["availablefunds"] . "&nbsp;<strong style=\"color:" . $availfundscolor . "\">" . $reseller_buysymbol . $availablebalance . "&nbsp;" . $reseller_buycurrency . "</strong>\r\n\t\t\t\t\t\t\t\t<br />" . $fundsalert2 . "</div><br />";
                }
            }
            if (!empty($vars["third_acc_name"]) && $vars["third_show_fundsbalance"] != "on") {
                if ($third_domainregistrar == "resellerclub") {
                    $registrar_icon = "<img src=\"../modules/addons/resellerclubmods_tools/img/resellerclubicon.png\" align=\"absmiddle\" border=\"0\">";
                } else if ($third_domainregistrar == "netearthone") {
                    $registrar_icon = "<img src=\"../modules/addons/resellerclubmods_tools/img/neoicon.png\" align=\"absmiddle\" border=\"0\">";
                } else if ($third_domainregistrar == "stargate") {
                    $registrar_icon = "<img src=\"../modules/addons/resellerclubmods_tools/img/stargateicon.png\" align=\"absmiddle\" border=\"0\">";
                } else if ($third_domainregistrar == "resellercamp") {
                    $registrar_icon = "<img src=\"../modules/addons/resellerclubmods_tools/img/resellercampicon.png\" align=\"absmiddle\" border=\"0\">";
                } else {
                    $registrar_icon = "<img src=\"../modules/addons/resellerclubmods_tools/img/otherlb.png\" align=\"absmiddle\" border=\"0\">";
                }
                $account_name = $vars["third_acc_name"];
                $rcauth_userid = $vars["third_rcauth_userid"];
                $rcauth_password = htmlspecialchars_decode($vars["third_rcauth_apikey"]);
                $apifunction = "/api/resellers/details.json";
                $data = ["reseller-id" => $rcauth_userid];
                $resellerdetails_arrXml = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
                $reseller_buycurrency = $resellerdetails_arrXml["parentsellingcurrencysymbol"];
                $is_mplicator = 1;
                $is_1000s = is_inthousands($reseller_buycurrency);
                if ($is_1000s == 1) {
                    $is_mplicator = 1000;
                }
                if (empty($reseller_buycurrency)) {
                    $content3 = "<div style=\"font-size:" . $fontsize . "px;font-family:" . $fontfamily . "\">\r\n\t\t\t\t\t\t\t\t" . $registrar_icon . "&nbsp;<u><strong>" . $account_name . "&nbsp;-&nbsp;" . $_ADDONLANG["fundstitle"] . "</strong></u><br />\r\n\t\t\t\t\t\t\t\t<div>Error: No API response received</div>\r\n\t\t\t\t\t\t\t\t</div><br />";
                } else {
                    if ($currencysymbol == "on") {
                        foreach ($currency_array as $k => $v) {
                            if ($k == $reseller_buycurrency) {
                                $currencycode = $v;
                            }
                        }
                        if (!empty($currencycode)) {
                            $reseller_buycurrency = "";
                        }
                        $reseller_buysymbol = $currencycode;
                    }
                    if (!empty($third_threshold_value) && is_numeric($third_threshold_value)) {
                        $reseller_fundthreshold = round($third_threshold_value * $is_mplicator, 2);
                    } else {
                        $reseller_fundthreshold = round($resellerdetails_arrXml["fundthreshold"] * $is_mplicator, 2);
                    }
                    $apifunction = "/api/billing/reseller-balance.json";
                    $data = ["reseller-id" => $rcauth_userid];
                    $xml_fundsbalancedetails = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
                    $newavailable_balance = $xml_fundsbalancedetails["sellingcurrencybalance"];
                    $newblocked_balance = $xml_fundsbalancedetails["sellingcurrencylockedbalance"];
                    $blockedbalance = round($newblocked_balance * $is_mplicator, 2);
                    $totalbalance = round($newavailable_balance * $is_mplicator, 2);
                    $availablebalance = round($totalbalance - $blockedbalance * $is_mplicator, 2);
                    $threshold_percent = round($reseller_fundthreshold * 0, 2);
                    if ($availablebalance < $threshold_percent && $reseller_fundthreshold < $availablebalance) {
                        $fundsalert3 = "<div style=\"font-size:" . $fontsize . "px;font-family:" . $fontfamily . "\"><img src=\"../modules/addons/resellerclubmods_tools/img/mediumpriority.png\" align=\"absmiddle\" border=\"0\"> " . $_ADDONLANG["prioritymedium"] . " (" . $reseller_buysymbol . $reseller_fundthreshold . " " . $reseller_buycurrency . ")</div>";
                    } else if ($reseller_fundthreshold < $availablebalance) {
                        $fundsalert3 = "<div style=\"font-size:" . $fontsize . "px;font-family:" . $fontfamily . "\"><img src=\"../modules/addons/resellerclubmods_tools/img/lowpriority.png\" align=\"absmiddle\" border=\"0\"> " . $_ADDONLANG["prioritylow"] . " (" . $reseller_buysymbol . $reseller_fundthreshold . " " . $reseller_buycurrency . ")</div>";
                    } else if ($availablebalance <= $reseller_fundthreshold) {
                        $fundsalert3 = "<div style=\"font-size:" . $fontsize . "px;font-family:" . $fontfamily . "\"><img src=\"../modules/addons/resellerclubmods_tools/img/highpriority.png\" align=\"absmiddle\" border=\"0\"> " . $_ADDONLANG["priorityhigh"] . " (" . $reseller_buysymbol . $reseller_fundthreshold . " " . $reseller_buycurrency . ")</div>";
                    }
                    $content3 = "<div style=\"font-size:" . $fontsize . "px;font-family:" . $fontfamily . "\">\r\n\t\t\t\t\t\t\t\t" . $registrar_icon . "&nbsp;<u><strong>" . $account_name . "&nbsp;-&nbsp;" . $_ADDONLANG["fundstitle"] . "</strong></u><br />" . $_ADDONLANG["totalfunds"] . "\r\n\t\t\t\t\t\t\t\t&nbsp;<strong style=\"color:" . $totalfundscolor . "\">" . $reseller_buysymbol . $totalbalance . "&nbsp;" . $reseller_buycurrency . "</strong>\r\n\t\t\t\t\t\t\t\t&nbsp;" . $_ADDONLANG["blockedfunds"] . "&nbsp;<strong style=\"color:" . $blockedfundscolor . "\">" . $reseller_buysymbol . $blockedbalance . "&nbsp;" . $reseller_buycurrency . "</strong>\r\n\t\t\t\t\t\t\t\t&nbsp; " . $_ADDONLANG["availablefunds"] . "&nbsp;<strong style=\"color:" . $availfundscolor . "\">" . $reseller_buysymbol . $availablebalance . "&nbsp;" . $reseller_buycurrency . "</strong>\r\n\t\t\t\t\t\t\t\t<br />" . $fundsalert3 . "</div><br />";
                }
            }
            if (!empty($vars["fourth_acc_name"]) && $vars["fourth_show_fundsbalance"] != "on") {
                if ($fourth_domainregistrar == "resellerclub") {
                    $registrar_icon = "<img src=\"../modules/addons/resellerclubmods_tools/img/resellerclubicon.png\" align=\"absmiddle\" border=\"0\">";
                } else if ($fourth_domainregistrar == "netearthone") {
                    $registrar_icon = "<img src=\"../modules/addons/resellerclubmods_tools/img/neoicon.png\" align=\"absmiddle\" border=\"0\">";
                } else if ($fourth_domainregistrar == "stargate") {
                    $registrar_icon = "<img src=\"../modules/addons/resellerclubmods_tools/img/stargateicon.png\" align=\"absmiddle\" border=\"0\">";
                } else if ($fourth_domainregistrar == "resellercamp") {
                    $registrar_icon = "<img src=\"../modules/addons/resellerclubmods_tools/img/resellercampicon.png\" align=\"absmiddle\" border=\"0\">";
                } else {
                    $registrar_icon = "<img src=\"../modules/addons/resellerclubmods_tools/img/otherlb.png\" align=\"absmiddle\" border=\"0\">";
                }
                $account_name = $vars["fourth_acc_name"];
                $rcauth_userid = $vars["fourth_rcauth_userid"];
                $rcauth_password = htmlspecialchars_decode($vars["fourth_rcauth_apikey"]);
                $apifunction = "/api/resellers/details.json";
                $data = ["reseller-id" => $rcauth_userid];
                $resellerdetails_arrXml = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
                $reseller_buycurrency = $resellerdetails_arrXml["parentsellingcurrencysymbol"];
                $is_mplicator = 1;
                $is_1000s = is_inthousands($reseller_buycurrency);
                if ($is_1000s == 1) {
                    $is_mplicator = 1000;
                }
                if (empty($reseller_buycurrency)) {
                    $content4 = "<div style=\"font-size:" . $fontsize . "px;font-family:" . $fontfamily . "\">\r\n\t\t\t\t\t\t\t\t" . $registrar_icon . "&nbsp;<u><strong>" . $account_name . "&nbsp;-&nbsp;" . $_ADDONLANG["fundstitle"] . "</strong></u><br />\r\n\t\t\t\t\t\t\t\t<div>Error: No API response received</div>\r\n\t\t\t\t\t\t\t\t</div><br />";
                } else {
                    if ($currencysymbol == "on") {
                        foreach ($currency_array as $k => $v) {
                            if ($k == $reseller_buycurrency) {
                                $currencycode = $v;
                            }
                        }
                        if (!empty($currencycode)) {
                            $reseller_buycurrency = "";
                        }
                        $reseller_buysymbol = $currencycode;
                    }
                    if (!empty($fourth_threshold_value) && is_numeric($fourth_threshold_value)) {
                        $reseller_fundthreshold = round($fourth_threshold_value * $is_mplicator, 2);
                    } else {
                        $reseller_fundthreshold = round($resellerdetails_arrXml["fundthreshold"] * $is_mplicator, 2);
                    }
                    $apifunction = "/api/billing/reseller-balance.json";
                    $data = ["reseller-id" => $rcauth_userid];
                    $xml_fundsbalancedetails = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
                    $newavailable_balance = $xml_fundsbalancedetails["sellingcurrencybalance"];
                    $newblocked_balance = $xml_fundsbalancedetails["sellingcurrencylockedbalance"];
                    $blockedbalance = round($newblocked_balance * $is_mplicator, 2);
                    $totalbalance = round($newavailable_balance * $is_mplicator, 2);
                    $availablebalance = round($totalbalance - $blockedbalance * $is_mplicator, 2);
                    $threshold_percent = round($reseller_fundthreshold * 0, 2);
                    if ($availablebalance < $threshold_percent && $reseller_fundthreshold < $availablebalance) {
                        $fundsalert4 = "<div style=\"font-size:" . $fontsize . "px;font-family:" . $fontfamily . "\"><img src=\"../modules/addons/resellerclubmods_tools/img/mediumpriority.png\" align=\"absmiddle\" border=\"0\"> " . $_ADDONLANG["prioritymedium"] . " (" . $reseller_buysymbol . $reseller_fundthreshold . " " . $reseller_buycurrency . ")</div>";
                    } else if ($reseller_fundthreshold < $availablebalance) {
                        $fundsalert4 = "<div style=\"font-size:" . $fontsize . "px;font-family:" . $fontfamily . "\"><img src=\"../modules/addons/resellerclubmods_tools/img/lowpriority.png\" align=\"absmiddle\" border=\"0\"> " . $_ADDONLANG["prioritylow"] . " (" . $reseller_buysymbol . $reseller_fundthreshold . " " . $reseller_buycurrency . ")</div>";
                    } else if ($availablebalance <= $reseller_fundthreshold) {
                        $fundsalert4 = "<div style=\"font-size:" . $fontsize . "px;font-family:" . $fontfamily . "\"><img src=\"../modules/addons/resellerclubmods_tools/img/highpriority.png\" align=\"absmiddle\" border=\"0\"> " . $_ADDONLANG["priorityhigh"] . " (" . $reseller_buysymbol . $reseller_fundthreshold . " " . $reseller_buycurrency . ")</div>";
                    }
                    $content4 = "<div style=\"font-size:" . $fontsize . "px;font-family:" . $fontfamily . "\">\r\n\t\t\t\t\t\t\t\t" . $registrar_icon . "&nbsp;<u><strong>" . $account_name . "&nbsp;-&nbsp;" . $_ADDONLANG["fundstitle"] . "</strong></u><br />" . $_ADDONLANG["totalfunds"] . "\r\n\t\t\t\t\t\t\t\t&nbsp;<strong style=\"color:" . $totalfundscolor . "\">" . $reseller_buysymbol . $totalbalance . "&nbsp;" . $reseller_buycurrency . "</strong>\r\n\t\t\t\t\t\t\t\t&nbsp;" . $_ADDONLANG["blockedfunds"] . "&nbsp;<strong style=\"color:" . $blockedfundscolor . "\">" . $reseller_buysymbol . $blockedbalance . "&nbsp;" . $reseller_buycurrency . "</strong>\r\n\t\t\t\t\t\t\t\t&nbsp; " . $_ADDONLANG["availablefunds"] . "&nbsp;<strong style=\"color:" . $availfundscolor . "\">" . $reseller_buysymbol . $availablebalance . "&nbsp;" . $reseller_buycurrency . "</strong>\r\n\t\t\t\t\t\t\t\t<br />" . $fundsalert4 . "</div><br />";
                }
            }
            $addonconfigure = "configaddonmods.php#resellerclubmods_tools";
            $toolspage = "addonmodules.php?module=resellerclubmods_tools";
            $contentlink = "<div style=\"text-align:right;\">\r\n\t\t\t\t\t\t\t<a class=\"btn btn-primary btn-sm\" href=\"" . $toolspage . "\"><i class=\"fa fa-pencil\" aria-hidden=\"true\"></i> " . $_ADDONLANG["toolshome"] . "</a> &nbsp;\r\n\t\t\t\t\t\t\t<a class=\"btn btn-success btn-sm\" href=\"" . $addonconfigure . "\"><i class=\"fa fa-pencil\" aria-hidden=\"true\"></i></i> " . $_ADDONLANG["configaddontitle"] . "</a>\r\n\t\t\t\t\t\t   </div>";
            $end_content = "</div>";
            $content = $start_content . $content1 . $content2 . $content3 . $content4 . $contentlink . $end_content;
            $query_result = Illuminate\Database\Capsule\Manager::table("tbladdonmodules")->where("module", "=", "resellerclubmods_tools")->where("setting", "=", "version")->select("value")->first();
            $version = $query_result->value;
            $toolstitle = "ResellerClub &amp; LogicBoxes Tools v" . $version;
            return ["title" => $toolstitle, "content" => $content];
    }
}

?>