<?php
if (!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
$introdesc = "<div style=\"width:100%\"><h3>" . $LANG["movetitle"] . "</h3><p>" . $LANG["movedesclong"] . "</p></div>";
echo "<script type=\"text/javascript\">\r\n\$(document).ready(function(){\r\n\t\$(\"#clientsearchval\").keyup(function () {\r\n\t\tvar useridsearchlength = \$(\"#clientsearchval\").val().length;\r\n\t\tif (useridsearchlength>2) {\r\n\t\t\$.post(\"search.php\", { clientsearch: 1, value: \$(\"#clientsearchval\").val(),token: \"" . generate_token("plain") . "\" },\r\n\t\t\tfunction(data){\r\n\t\t\t\tif (data) {\r\n\t\t\t\t\t\$(\"#clientsearchresults\").html(data);\r\n\t\t\t\t\t\$(\"#clientsearchresults\").slideDown(\"slow\");\r\n\t\t\t\t}\r\n\t\t\t});\r\n\t\t}\r\n\t});\r\n});\r\nfunction searchselectclient(userid,name,email) {\r\n\t\$(\"#newusername\").val(name);\r\n\t\$(\"#newuserid\").val(userid);\r\n\t\$(\"#clientsearchresults\").slideUp();\r\n}\r\n</script>";
if (isset($_POST["dobulkmove"]) && $_POST["dobulkmove"] == "true") {
    echo $configuredto;
    $domainlist = $_POST["domainlist"];
    $new_rcauth_userid = $_POST["moveto"];
    if (empty($domainlist)) {
        echo "<div class=\"alert alert-danger\"><p>" . $LANG["selectadomtomove"] . "</p></div>";
        echo "<h3><a href=\"" . $modulelink . "&domain=bulkmove\">" . $LANG["domaintoolbacklink"] . "</a></h3>";
    } else if (!is_numeric($new_rcauth_userid)) {
        echo "<div class=\"alert alert-danger\"><p>" . $LANG["selectreselleraccounterror"] . "</p></div>";
        echo "<h3><a href=\"" . $modulelink . "&domain=bulkmove\">" . $LANG["domaintoolbacklink"] . "</a></h3>";
    } else {
        $time = microtime();
        $time = explode(" ", $time);
        $time = $time[1] + $time[0];
        $start = $time;
        $rcmdebuginfo = getDebuginfos();
        $modulename = $rcmdebuginfo["modulename"];
        $debug_addinfo = $rcmdebuginfo["debug_addinfo"];
        $rcid_keylabel = array_keys($vars, $new_rcauth_userid);
        $rcapi_keylabel = str_replace("userid", "apikey", $rcid_keylabel[0]);
        $new_rcauth_password = $vars[$rcapi_keylabel];
        $num = 1;
        $numfails = 1;
        echo "<h3><strong>Domain Move Results</strong></h3> ";
        foreach ($domainlist as $domains) {
            $result = Illuminate\Database\Capsule\Manager::table("tbldomains")->where("domain", "=", $domains)->select("userid")->get();
            $userid = $result[0]->userid;
            $result1 = Illuminate\Database\Capsule\Manager::table("tblclients")->where("id", "=", $userid)->select("email")->get();
            $email = $result1[0]->email;
            $method = "GET";
            $apifunction = "/api/customers/details.json";
            $data = ["username" => $email];
            $arrXml = call_api($new_rcauth_userid, $new_rcauth_password, $rchttp_api, $apifunction, $data, $method);
            if ($arrXml["status"] == "ERROR") {
                $newcustomer = $email;
                $whmcscupwd = "";
                $rc_customer_id = createOBcustomer($newcustomer, $rchttp_api, $new_rcauth_userid, $new_rcauth_password, $whmcscupwd);
                if (is_numeric($rc_customer_id)) {
                    $user_result = "<span style=\"color:#0D6306;\">" . $LANG["createcustfor"] . " (" . $email . ") OK: ID " . $rc_customer_id . "</span><br />";
                } else {
                    $user_result = "<span style=\"color:#cc0000;\">" . $LANG["createcustfor"] . " (" . $email . ") KO: " . $rc_customer_id["message"] . "</span><br />";
                }
            } else {
                $rc_customer_id = $arrXml["customerid"];
                $user_result = "<span style=\"color:#0D6306;\">" . $LANG["foundcustfor"] . " (" . $email . ") OK: ID " . $rc_customer_id . "</span><br />";
            }
            $method = "GET";
            $apifunction = "/api/customers/details.json";
            $data = ["username" => $email];
            $rc_customerid = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
            $existing_customerid = $rc_customerid["customerid"];
            $method = "POST";
            $apifunction = "/api/products/move.json";
            $data = ["domain-name" => $IDN->encode($domains), "existing-customer-id" => $existing_customerid, "new-customer-id" => $rc_customer_id, "default-contact" => "oldcontact"];
            $movedomains = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
            $action = "Bulk Move";
            $requeststring = $apifunction . " [reseller data protected] " . serialize_data($data);
            $responsedata = ["rcmdebug" => $debug_addinfo, "apidebug" => $movedomains];
            rcm_log_module_call($modulename, $action, $requeststring, $responsedata);
            echo "<strong>#" . $num++ . "</strong> " . $user_result;
            if ($movedomains["status"] == "Success") {
                echo "<strong style=\"color:#0D6306;\">" . $domains . "</strong> " . $LANG["bulkmovesuccessto"] . " " . $new_rcauth_userid . "<hr /><br />";
            } else {
                $totalnumfails = $numfails++;
                echo "<strong style=\"color:#cc0000;\">" . $domains . " " . $LANG["bulkmoveerrorto"] . "</strong>: " . $movedomains["message"] . "<hr /><br />";
            }
        }
        $time = microtime();
        $time = explode(" ", $time);
        $time = $time[1] + $time[0];
        $finish = $time;
        $total_time = round($finish - $start, 4);
        $totalnum = $num - 1;
        $totalnums = $totalnum - $totalnumfails;
        echo "<p><strong>" . $totalnums . "</strong> " . $LANG["onlyofword"] . " " . $totalnum . " " . $LANG["domainsmovedin"] . " " . $total_time . " " . $LANG["seconds"] . " " . $LANG["fromreseller"] . " " . $rcauth_userid . " " . $LANG["toreseller"] . " " . $new_rcauth_userid . "</p>";
        echo "<h3><a href=\"" . $modulelink . "&domain=bulkmove\">" . $LANG["domaintoolbacklink"] . "</a></h3>";
    }
} else {
    $selectorarray = [];
    if (!empty($vars["first_acc_name"]) && $vars["first_rcauth_userid"] != $rcauth_userid) {
        $selectorarray[$vars["first_rcauth_userid"]] = $vars["first_acc_name"] . " - " . $vars["first_rcauth_userid"];
    }
    if (!empty($vars["second_acc_name"]) && $vars["second_rcauth_userid"] != $rcauth_userid) {
        $selectorarray[$vars["second_rcauth_userid"]] = $vars["second_acc_name"] . " - " . $vars["second_rcauth_userid"];
    }
    if (!empty($vars["third_acc_name"]) && $vars["third_rcauth_userid"] != $rcauth_userid) {
        $selectorarray[$vars["third_rcauth_userid"]] = $vars["third_acc_name"] . " - " . $vars["third_rcauth_userid"];
    }
    if (!empty($vars["fourth_acc_name"]) && $vars["fourth_rcauth_userid"] != $rcauth_userid) {
        $selectorarray[$vars["fourth_rcauth_userid"]] = $vars["fourth_acc_name"] . " - " . $vars["fourth_rcauth_userid"];
    }
    echo $configuredto;
    echo "<h1>" . $LANG["bulkmovetitle"] . "</h1>";
    echo "<p>" . $LANG["bulkmovedesc"] . "</p>";
    echo $LANG["bulkhowtouse"];
    echo $LANG["bulkhowitworks"];
    echo "<div class=\"alert alert-warning\"><p>" . $LANG["bulkmovenote"] . "</p>";
    echo $LANG["bulkmovenotedesc"];
    echo "<br /><p>" . $LANG["bulkgeneralnote"] . "</p></div>";
    $whmcs_domains = [];
    foreach (Illuminate\Database\Capsule\Manager::table("tbldomains")->where("status", "=", "Active")->where("registrar", "LIKE", $logicbox_registrar . "%")->select("domain")->get() as $data) {
        $whmcs_domains[] = $data->domain;
    }
    sort($whmcs_domains);
    echo "<div style=\"padding: 5px 20px;border: 1px solid #CCCCCC;-moz-border-radius: 5px;-webkit-border-radius: 5px;-o-border-radius: 5px;border-radius: 5px;\">";
    echo "<br /><h3 style=\"border-bottom: 1px solid #cccccc;\"><strong>" . $LANG["alldominwhmcstitle"] . "</strong> " . $LANG["configuredwith"] . " " . $LANG["registrarmoduletitle"] . ": <strong>" . $logicbox_registrar . "</strong></h3>";
    echo "<div><form method=\"post\" action=\"" . $_SERVER["PHP_SELF"] . "?module=resellerclubmods_tools&domain=bulkmove\">";
    echo "<input type=\"hidden\" name=\"dobulkmove\" value=\"true\"><table><tr>";
    echo "<td style=\"vertical-align:top\"><p>" . $LANG["bulkallusing"] . "</p><select class=\"form-control\" style=\"display: inline; width: auto\" name=\"domainlist[]\" size=\"10\" multiple=\"multiple\">";
    foreach ($whmcs_domains as $domvalues) {
        echo "<option value=\"" . $domvalues . "\">" . $domvalues . "</option>";
    }
    echo "</select></td>";
    echo "<td style=\"vertical-align:top;padding-left:20px;\"><p>" . $LANG["registrardropdowntitle"] . "</p><select class=\"form-control\" style=\"display: inline; width: auto\" name=\"moveto\">";
    echo "<option value=\"\">" . $LANG["registrardropdown"] . "</option>";
    foreach ($selectorarray as $key => $value) {
        echo "<option value=\"" . $key . "\">" . $value . "</option>";
    }
    echo "</select>";
    echo "&nbsp;<input type=\"submit\" value=\"" . $LANG["bulkmovebutton"] . "\" class=\"btn btn-success absmiddle\" /></td>";
    echo "</tr></table></form></div></div><br /><br /><div style=\"padding: 5px 20px;border: 1px solid #CCCCCC;-moz-border-radius: 5px;-webkit-border-radius: 5px;-o-border-radius: 5px;border-radius: 5px;\">";
    echo "<br /><h3 style=\"border-bottom: 1px solid #cccccc;\"><strong>" . $LANG["searchalldominwhmcstitle"] . "</strong></h3>";
    echo "<div>";
    echo "<form method=\"post\" action=\"" . $_SERVER["REQUEST_URI"] . "#domlist\">";
    echo "<input type=hidden name=\"movesearch\" value=\"true\" /><input type=hidden name=\"whmcsusername\" id=\"newusername\" /><div>";
    echo $LANG["searchtitle"] . " <input class=\"form-control\" style=\"display: inline; width: auto\" type=\"text\" id=\"clientsearchval\" size=\"25\" />";
    echo "</div><br /><div id=\"clientsearchresults\">";
    echo "<div class=\"searchresultheader\">" . $LANG["searchresult"] . "</div>";
    echo "<div class=\"searchresult\" align=\"center\">" . $LANG["searchmatches"] . "</div>";
    echo "</div><br />";
    echo "<p>" . $LANG["customeridtitle"] . " <input class=\"form-control\" style=\"display: inline; width: auto\" type=\"text\" name=\"whmcsuser\" id=\"newuserid\" size=\"10\" /> <input type=\"submit\" value=\"" . $LANG["fetchallcustdoms"] . "\" class=\"btn btn-primary absmiddle\" /></p>";
    echo "</form></div>";
    if (isset($_POST["movesearch"]) && $_POST["movesearch"] == "true") {
        echo "<a name=\"domlist\"></a>";
        if (empty($_POST["whmcsuser"])) {
            echo "<div class=\"alert alert-danger\"><p>" . $LANG["providecustomerdata"] . "</p></div>";
        } else {
            $whmcsuser_domains = [];
            foreach (Illuminate\Database\Capsule\Manager::table("tbldomains")->where("userid", "=", $_POST["whmcsuser"])->where("registrar", "LIKE", $logicbox_registrar . "%")->select("domain")->get() as $data) {
                $whmcsuser_domains[] = $data->domain;
            }
            sort($whmcsuser_domains);
            $whmcsusername = $_POST["whmcsusername"];
            if (empty($whmcsusername)) {
                $usernameorid = $LANG["customeridtitle"] . " " . $_POST["whmcsuser"];
            } else {
                $usernameorid = $_POST["whmcsusername"];
            }
            if (empty($whmcsuser_domains)) {
                echo "<div class=\"alert alert-warning\"><p>" . $LANG["nodomainsfoundfor"] . " " . $usernameorid . "</p></div>";
            } else {
                echo "<div class=\"alert alert-success\"><p>" . $LANG["readyselecttomove"] . "</p></div><br />";
                echo "<div><form method=\"post\" action=\"" . $_SERVER["PHP_SELF"] . "?module=resellerclubmods_tools&domain=bulkmove\">";
                echo "<input type=\"hidden\" name=\"dobulkmove\" value=\"true\"><table><tr>";
                echo "<td style=\"vertical-align:top\"><p>" . $LANG["bulkallusing"] . " " . $usernameorid . " (<strong>" . strtoupper($logicbox_registrar) . "</strong>)</p><select class=\"form-control\" style=\"display: inline; width: auto\" name=\"domainlist[]\" size=\"10\" multiple=\"multiple\">";
                foreach ($whmcsuser_domains as $userdomvalues) {
                    echo "<option value=\"" . $userdomvalues . "\">" . $userdomvalues . "</option>";
                }
                echo "</select></td>";
                echo "<td style=\"vertical-align:top;padding-left:20px;\"><p>" . $LANG["registrardropdowntitle"] . "</p><select class=\"form-control\" style=\"display: inline; width: auto\" name=\"moveto\">";
                echo "<option value=\"\">" . $LANG["registrardropdown"] . "</option>";
                foreach ($selectorarray as $key => $value) {
                    echo "<option value=\"" . $key . "\">" . $value . "</option>";
                }
                echo "</select>";
                echo "&nbsp;<input type=\"submit\" value=\"" . $LANG["bulkmovebutton"] . "\" class=\"btn btn-success absmiddle\" /></td>";
                echo "</tr></table></form></div>";
            }
        }
    }
    echo "</div><br /><br />";
}

?>