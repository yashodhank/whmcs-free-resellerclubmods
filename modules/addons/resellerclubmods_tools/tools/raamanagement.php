<?php
if (!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
if (date_default_timezone_get()) {
    $timezone = date_default_timezone_get();
}
if (ini_get("date.timezone")) {
    $timezone = ini_get("date.timezone");
}
if ($timezone != "UTC") {
    date_default_timezone_set("UTC");
}
echo $configuredto;
echo "<h1>" . $LANG["raamanagementtitlelong"] . "</h1>";
$page = $_REQUEST["page"];
if (isset($_REQUEST["raaaction"]) && $_REQUEST["raaaction"] == "success") {
    $raaemail = rcm_e($_REQUEST["email"] ?? "");
    echo "<div class=\"alert alert-success\"><p>" . $LANG["raasuccess"] . " <strong>" . $raaemail . "</strong></p></div>";
}
$datetimenow = date("Y-m-d H:i:s");
$rcmdebuginfo = getDebuginfos();
$modulename = $rcmdebuginfo["modulename"];
$debug_addinfo = $rcmdebuginfo["debug_addinfo"];
if (isset($_POST["multiraaresend"]) && $_POST["multiraaresend"] == "true") {
    rcm_require_post_token();
    $bulkform = $_POST["bulkform"];
    $bulkcount = 0;
    $successcount = 0;
    foreach ($bulkform as $bkey => $bvalue) {
        if ($bvalue["include"] == "true") {
            $bulkcount++;
            $method = "POST";
            $apifunction = "/api/domains/raa/resend-verification.json";
            $data = ["order-id" => $bvalue["orderid"]];
            $raa_response = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
            $action = "RAA resend verification email";
            $requeststring = $apifunction . " [reseller data protected] " . serialize_data($data);
            $responsedata = ["rcmdebug" => $debug_addinfo, "apidebug" => $raa_response];
            rcm_log_module_call($modulename, $action, $requeststring, $responsedata);
            if ($raa_response == 1) {
                $successcount++;
                $raadata = Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodsraa")->where("domain", "=", $bvalue["domain"])->select("count")->get();
                $count = $raadata[0]->count;
                if (empty($count) || $count == 0) {
                    $values = ["id" => "", "rid" => $rcauth_userid, "registrar" => $logicbox_registrar, "domainid" => $bvalue["orderid"], "userid" => $bvalue["uid"], "domain" => $bvalue["domain"], "date" => $datetimenow, "count" => 1];
                    Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodsraa")->insert($values);
                } else {
                    $update = ["domainid" => $bvalue["orderid"], "userid" => $bvalue["uid"], "date" => $datetimenow, "count" => $count + 1];
                    Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodsraa")->where("domain", "=", $bvalue["domain"])->update($update);
                }
            }
        }
    }
    if ($successcount == 0) {
        echo "<div class=\"alert alert-danger\"><p><strong>" . $successcount . "</strong> " . $LANG["onlyofword"] . " <strong>" . $bulkcount . "</strong> " . $LANG["verifysentsuccess1"] . "</p></div>";
    } else if (1 <= $successcount && $successcount < $bulkcount) {
        echo "<div class=\"alert alert-warning\"><p><strong>" . $successcount . "</strong> " . $LANG["onlyofword"] . " <strong>" . $bulkcount . "</strong> " . $LANG["verifysentsuccess1"] . "</p></div>";
    } else if ($bulkcount == $successcount) {
        echo "<div class=\"alert alert-success\"><p><strong>" . $successcount . "</strong> " . $LANG["onlyofword"] . " <strong>" . $bulkcount . "</strong> " . $LANG["verifysentsuccess1"] . "</p></div>";
    }
}
if (isset($_POST["raaresend"]) && $_POST["raaresend"] == "true") {
    rcm_require_post_token();
    $orderid = $_POST["orderid"] ?? "";
    $raaemail = $_POST["email"] ?? "";
    $raadomain = $_POST["dom"] ?? "";
    $uid = $_POST["uid"] ?? "";
    $method = "POST";
    $apifunction = "/api/domains/raa/resend-verification.json";
    $data = ["order-id" => $orderid];
    $raa_response = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
    $action = "RAA resend verification email";
    $requeststring = $apifunction . " [reseller data protected] " . serialize_data($data);
    $responsedata = ["rcmdebug" => $debug_addinfo, "apidebug" => $raa_response];
    rcm_log_module_call($modulename, $action, $requeststring, $responsedata);
    if ($raa_response == 1) {
        $raadata = Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodsraa")->where("domain", "=", $raadomain)->select("count")->get();
        $count = $raadata[0]->count;
        if (empty($count) || $count == 0) {
            $values = ["id" => "", "rid" => $rcauth_userid, "registrar" => $logicbox_registrar, "domainid" => $orderid, "userid" => $uid, "domain" => $raadomain, "date" => $datetimenow, "count" => 1];
            Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodsraa")->insert($values);
        } else {
            $update = ["domainid" => $orderid, "userid" => $uid, "date" => $datetimenow, "count" => $count + 1];
            Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodsraa")->where("domain", "=", $raadomain)->update($update);
        }
        $adminhome = $systemurl . "/" . $customadminpath . "/addonmodules.php?module=resellerclubmods_tools&domain=raamanagement&email=" . $raaemail . "&raaaction=success&page=" . $page;
        header("Location: " . $adminhome);
        exit;
    }
    echo "<div class=\"alert alert-danger\"><p>" . $LANG["raaerror"] . " <strong>" . $raaemail . "</strong>. " . $LANG["ptryaglater"] . "</p></div>";
}
$rcm_pagination = new RcmToolsPagination();
$pag_no_ofrecords = 50;
$method = "GET";
$apifunction = "/api/domains/search.json";
$data = ["status" => "Pending Verification", "no-of-records" => 500, "page-no" => 1];
$domainsearch_pending = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
$data = ["status" => "Failed Verification", "no-of-records" => 500, "page-no" => 1];
$domainsearch_failed = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
$domainsearch = array_merge($domainsearch_pending, $domainsearch_failed);
if (isset($domainsearch[0])) {
    $apifunction = "/api/domains/details.json";
    $domaindetailsarr = ["DomainStatus", "RegistrantContactDetails"];
    foreach ($domainsearch as $domvalue) {
        if (is_array($domvalue)) {
            $data = ["order-id" => $domvalue["orders.orderid"], "options" => $domaindetailsarr];
            $get_domaindetails = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
            $domain_name = $IDN->decode($domvalue["entity.description"]);
            $raastatus = $get_domaindetails["raaVerificationStatus"];
            $registrantemail = $get_domaindetails["registrantcontact"]["emailaddr"];
            $result = Illuminate\Database\Capsule\Manager::table("tbldomains")->where("domain", "=", $domain_name)->where("status", "=", "Active")->select("userid", "domain")->get();
            $userid = $result[0]->userid;
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
            $daysleft = ceil($datediff / 60 / 60 / 24) . " Days";
            if (10 <= $daysleft) {
                $daysleft = "<span style=\"color:#269900;font-weight:bold;\">" . $daysleft . "</span>";
            } else if (5 <= $daysleft) {
                $daysleft = "<span style=\"color:#F29500;font-weight:bold;\">" . $daysleft . "</span>";
            } else if (1 <= $daysleft) {
                $daysleft = "<span style=\"color:#cc0000;font-weight:bold;\">" . $daysleft . "</span>";
            } else {
                $daysleft = "<span style=\"color:#cc0000;font-weight:bold;\">" . $daysleft . "</span>";
            }
            $raasendlink = "<a href=\"addonmodules.php?module=resellerclubmods_tools&domain=raamanagement&raaresend=true&email=" . $registrantemail . "&orderid=" . $domvalue["orders.orderid"] . "&dom=" . $domain_name . "&uid=" . $userid . "&page=" . $page . "\">" . $LANG["raaresendemail"] . "</a>";
            $customerlink = "<a href=\"clientssummary.php?userid=" . $userid . "\">" . $userid . "</a>";
            if ($raastatus == "Pending") {
                $raastatus_label = "<span class=\"label pending\">" . $LANG["ispending"] . "</span>";
            } else if ($raastatus == "Suspended") {
                $raastatus_label = "<span class=\"label terminated\">" . $LANG["raaunverified"] . "</span>";
            }
            $raadata = Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodsraa")->where("domain", "=", $domain_name)->where("userid", "=", $userid)->select("date", "count")->get();
            $lastsend = $raadata[0]->date;
            $raacount = $raadata[0]->count;
            if (empty($lastsend)) {
                $lastsend = "00-00-0000 00:00:00";
            }
            if (empty($raacount)) {
                $raacount = "0";
            }
            $infolastsend = $lastsend . " (<strong>" . $raacount . "</strong>x)";
            if ($userid) {
                $raa_data_array[] = ["domain" => $domain_name, "raastart" => $raa_starttime, "raaend" => $raa_endtime, "daysleft" => $daysleft, "status" => $raastatus_label, "email" => $registrantemail, "action" => $raasendlink, "lastsent" => $infolastsend, "orderid" => $domvalue["orders.orderid"], "customer" => $customerlink, "uid" => $userid];
            }
        }
    }
    if (!empty($raa_data_array)) {
        $productPages = $rcm_pagination->generate($raa_data_array, $pag_no_ofrecords);
        function sortcompare($a, $b)
        {
            return strcmp($a["raaend"], $b["raaend"]);
        }
        usort($productPages, "sortcompare");
        echo "\r\n\t\t\t<script type=\"text/javascript\">\r\n\t\t\t\$(document).ready(function(){\r\n\t\t\t\$(\"#multicheck\").click(function () {\r\n\t\t\t\t\$(\"#bulktable .checkall\").attr(\"checked\",this.checked);\r\n\t\t\t});\r\n\t\t\t});\r\n\t\t\t</script>\r\n\t\t";
        echo "<form method=\"post\" action=\"addonmodules.php?module=resellerclubmods_tools&domain=raamanagement&page=" . $page . "\">" . rcm_token_field() . "";
        echo "<input type=\"hidden\" name=\"multiraaresend\" value=\"true\"><table id=\"bulktable\" class=\"datatable\" border=\"0\" cellpadding=\"3\" cellspacing=\"1\" width=\"100%\"><tr>";
        echo "<th><input id=\"multicheck\" type=\"checkbox\" /></th>\r\n\t\t\t  <th>" . $LANG["clienttitle"] . "</th>\r\n\t\t\t  <th>" . $LANG["domainword"] . "</th>\r\n\t\t\t  <th>" . $LANG["raastarttitle"] . "</th>\r\n\t\t\t  <th>" . $LANG["raaendtitle"] . "</th>\r\n\t\t\t  <th>" . $LANG["raaenddaystitle"] . "</th>\r\n\t\t\t  <th>" . $LANG["statusword"] . "</th>\r\n\t\t\t  <th>" . $LANG["regcemailtitle"] . "</th>\r\n\t\t\t  <th>" . $LANG["lastsendtitle"] . "</th>\r\n\t\t\t  <th>" . $LANG["actionword"] . "</th>";
        echo "</tr>";
        foreach ($productPages as $raa_data) {
            echo "<tr>";
            echo "<td style=\"text-align:center;\">\r\n\t\t\t\t  <input class=\"checkall\" name=\"bulkform[" . $raa_data["orderid"] . "][include]\" type=\"checkbox\" value=\"true\" />\r\n\t\t\t\t  <input type=\"hidden\" name=\"bulkform[" . $raa_data["orderid"] . "][uid]\" value=\"" . $raa_data["uid"] . "\" />\r\n\t\t\t\t  <input type=\"hidden\" name=\"bulkform[" . $raa_data["orderid"] . "][domain]\" value=\"" . $raa_data["domain"] . "\" />\r\n\t\t\t\t  <input type=\"hidden\" name=\"bulkform[" . $raa_data["orderid"] . "][orderid]\" value=\"" . $raa_data["orderid"] . "\" />\r\n\t\t\t\t  <input type=\"hidden\" name=\"bulkform[" . $raa_data["orderid"] . "][email]\" value=\"" . $raa_data["email"] . "\" />\r\n\t\t\t\t  </td>\r\n\t\t\t\t  <td>" . $raa_data["customer"] . "</td>\r\n\t\t\t\t  <td>" . $raa_data["domain"] . "</td>\r\n\t\t\t\t  <td><span style=\"font-size:11px;\">" . $raa_data["raastart"] . "</span></td>\r\n\t\t\t\t  <td><span style=\"font-size:11px;\">" . $raa_data["raaend"] . "</span></td>\r\n\t\t\t\t  <td>" . $raa_data["daysleft"] . "</td>\r\n\t\t\t\t  <td>" . $raa_data["status"] . "</td>\r\n\t\t\t\t  <td>" . $raa_data["email"] . "</td>\r\n\t\t\t\t  <td><span style=\"font-size:11px;\">" . $raa_data["lastsent"] . "</span></td>\r\n\t\t\t\t  <td>" . $raa_data["action"] . "</td>";
            echo "</tr>";
        }
        echo "</tr></table><br />";
        echo "<div>" . $LANG["withselected"] . " <input value=\"" . $LANG["raaresendemail"] . "\" class=\"btn\" type=\"submit\"></div>";
        echo "</form>";
        echo $pageNumbers = "<div style=\"margin-top:5px;\">" . $rcm_pagination->links() . "</div>";
    } else {
        echo "<div class=\"gracefulexit\">" . $LANG["noraapendingmessagewhmcs"] . "</div>";
    }
} else {
    echo "<div class=\"gracefulexit\">" . $LANG["noraapendingmessage"] . "<br />" . $LANG["reselleraccount"] . " (ID " . $rcauth_userid . ")</div>";
}
date_default_timezone_set($timezone);

?>