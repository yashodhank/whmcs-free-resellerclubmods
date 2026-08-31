<?php
if (!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
$introdesc = "<div style=\"width:100%\"><h1>" . $LANG["movetitle"] . "</h1><p>" . $LANG["movedesclong"] . "</p></div>";
echo "<script type=\"text/javascript\">\r\n\$(document).ready(function(){\r\n\t\$(\"#clientsearchval\").keyup(function () {\r\n\t\tvar useridsearchlength = \$(\"#clientsearchval\").val().length;\r\n\t\tif (useridsearchlength>2) {\r\n\t\t\$.post(\"search.php\", { clientsearch: 1, value: \$(\"#clientsearchval\").val(),token: \"" . generate_token("plain") . "\" },\r\n\t\t\tfunction(data){\r\n\t\t\t\tif (data) {\r\n\t\t\t\t\t\$(\"#clientsearchresults\").html(data);\r\n\t\t\t\t\t\$(\"#clientsearchresults\").slideDown(\"slow\");\r\n\t\t\t\t}\r\n\t\t\t});\r\n\t\t}\r\n\t});\r\n});\r\nfunction searchselectclient(userid,name,email) {\r\n\t\$(\"#newuserid\").val(userid);\r\n\t\$(\"#clientsearchresults\").slideUp();\r\n}\r\n</script>";
if ($_REQUEST["movesearch"] == "true") {
    echo $configuredto;
    echo $introdesc;
    $oldowner = $_REQUEST["oldowner"];
    if (empty($oldowner)) {
        header("location: " . $modulelink . "&domain=moveservices");
        exit;
    }
    $tbldomains = [];
    foreach (Illuminate\Database\Capsule\Manager::table("tbldomains")->where("userid", "=", $oldowner)->select("domain")->get() as $data) {
        $tbldomains[] = $data->domain;
    }
    sort($tbldomains);
    $tblproducts = [];
    foreach (Illuminate\Database\Capsule\Manager::table("tblhosting")->where("userid", "=", $oldowner)->select("domain")->get() as $data) {
        $tblproducts[] = $data->domain;
    }
    sort($tblproducts);
    echo "<div style=\"padding: 5px 20px;border: 1px solid #CCCCCC;-moz-border-radius: 5px;-webkit-border-radius: 5px;-o-border-radius: 5px;border-radius: 5px;\">";
    echo "<br /><h3 style=\"border-bottom: 1px solid #cccccc;\"><strong>" . $LANG["movestep2"] . "</strong></h3>";
    echo $LANG["movestep2desc"] . "<br />" . $LANG["movestep2descextend"] . "<br /><br />";
    echo "<form method=\"post\" action=\"addonmodules.php?module=resellerclubmods_tools&domain=moveservices\">";
    if (!empty($tblproducts) || !empty($tbldomains)) {
        echo "<p>" . $LANG["domainword"] . ": <select class=\"form-control\" style=\"display: inline; width: auto\" name=\"domainname\">";
    } else {
        $break_step3 = 1;
        $disable = "disabled=\"disabled\"";
        echo "<h4 style=\"color:#CC0000;\">" . $LANG["nothingtomove"] . "</h4>";
    }
    if (!empty($tbldomains)) {
        foreach ($tbldomains as $domainname) {
            echo "<option value=\"" . $domainname . "\">" . $domainname . " (" . $LANG["domaintitle"] . ")</option>";
        }
    }
    if (!empty($tblproducts)) {
        foreach ($tblproducts as $productdomain) {
            echo "<option value=\"" . $productdomain . "\">" . $productdomain . " (" . $LANG["servicetitle"] . ")</option>";
        }
    }
    echo "</select></p><br /></div><br />";
    if ($break_step3 != 1) {
        echo "<div style=\"padding: 5px 20px;border: 1px solid #CCCCCC;-moz-border-radius: 5px;-webkit-border-radius: 5px;-o-border-radius: 5px;border-radius: 5px;\">";
        echo "<br /><h3 style=\"border-bottom: 1px solid #cccccc;\"><strong>" . $LANG["movestep3"] . "</strong></h3>";
        echo $LANG["movestep3desc"] . "<br /><br />";
        echo "<div><input type=hidden name=\"moveprepare\" value=\"true\" />";
        echo "<input type=hidden name=\"oldowner\" value=\"" . $oldowner . "\" />";
        echo "<div>";
        echo $LANG["searchtitle"] . " <input type=\"text\" class=\"form-control\" style=\"display: inline; width: auto\" id=\"clientsearchval\" size=\"25\" />";
        echo "</div><br /><div id=\"clientsearchresults\">";
        echo "<div class=\"searchresultheader\">" . $LANG["searchresults"] . "</div>";
        echo "<div class=\"searchresult\" align=\"center\">" . $LANG["searchmatches"] . "</div>";
        echo "</div><br />";
        echo "<p>" . $LANG["customeridtitle"] . " <input type=\"text\" class=\"form-control\" style=\"display: inline; width: auto\" name=\"newowner\" id=\"newuserid\" size=\"10\" /> <input type=\"submit\" value=\"" . $LANG["validatebutton"] . "\" class=\"btn btn-primary absmiddle\" " . $disable . "\" /></p>";
        echo "</form></div></div><br />";
    }
    echo "<form method=\"post\" action=\"addonmodules.php?module=resellerclubmods_tools&domain=moveservices\">";
    echo "<p><input type=\"submit\" value=\"" . $LANG["backtobutton"] . " " . $LANG["movetitle"] . "\" class=\"btn btn-primary\" /></p>";
    echo "</form>";
} else if ($_POST["moveprepare"] == "true") {
    echo $configuredto;
    echo $introdesc;
    $oldcustomerid = $_POST["oldowner"];
    $newcustomerid = $_POST["newowner"];
    $domainname = $_POST["domainname"];
    $result = Illuminate\Database\Capsule\Manager::table("tblclients")->where("id", "=", $oldcustomerid)->select("email")->get();
    $oldcustomeremail = $result[0]->email;
    if (empty($oldcustomeremail)) {
        echo "<div class=\"alert alert-danger\"><p>" . $LANG["custidnotfound1"] . " " . $oldcustomerid . " " . $LANG["custidnotfound2"] . "</p></div>";
    } else {
        $method = "GET";
        $apifunction = "/api/customers/details.json";
        $data = ["username" => $oldcustomeremail];
        $oldarrXml = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
        $oldrccustomerid = $oldarrXml["customerid"];
        if (!is_numeric($oldrccustomerid)) {
            $disable = "disabled=\"disabled\"";
            echo "<div class=\"alert alert-danger\"><p>" . $LANG["custnotfound1"] . " \"" . $oldcustomeremail . "\" " . $LANG["custnotfound2"] . "</p></div>";
        } else {
            $result = Illuminate\Database\Capsule\Manager::table("tblclients")->where("id", "=", $newcustomerid)->select("email")->get();
            $newcustomeremail = $result[0]->email;
            if (empty($newcustomeremail)) {
                echo "<div class=\"alert alert-danger\"><p>" . $LANG["custidnotfound1"] . " " . $newcustomerid . " " . $LANG["custidnotfound2"] . "</p></div>";
            } else {
                $method = "GET";
                $apifunction = "/api/customers/details.json";
                $data = ["username" => $newcustomeremail];
                $newarrXml = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
                if ($newarrXml["status"] == "ERROR") {
                    $newrccustomerid = "<span style=\"color:#CC0000\">" . $LANG["pendingcreate"] . "</span>";
                } else {
                    $newrccustomerid = $newarrXml["customerid"];
                }
                if ($newrccustomerid == $oldrccustomerid) {
                    echo "<div class=\"alert alert-danger\"><p>" . $LANG["oldcustomer"] . " <strong>" . $oldcustomeremail . " (" . $oldrccustomerid . ")</strong> and " . $LANG["newcustomer"] . " <strong>" . $newcustomeremail . " (" . $newrccustomerid . ")</strong> " . $LANG["arethesame"] . "</p></div>";
                } else {
                    $result = Illuminate\Database\Capsule\Manager::table("tbldomains")->where("domain", "=", $domainname)->select("id", "domain")->get();
                    $domainid = $result[0]->id;
                    $domain = $result[0]->domain;
                    $movearray["domain"][] = ["Type" => $LANG["domregister"], "tlddomain" => $domain, "id" => $domainid];
                    $tblproducts = [];
                    foreach (Illuminate\Database\Capsule\Manager::table("tblhosting")->where("domain", "=", $domainname)->select("id", "packageid", "domain")->get() as $data) {
                        $tblproducts[] = ["tldproduct" => $data->domain, "id" => $data->id, "packageid" => $data->packageid];
                    }
                    if (isset($tblproducts[1])) {
                        foreach ($tblproducts as $products) {
                            $packageid = $products["packageid"];
                            $productdomain = $products["tldproduct"];
                            $productid = $products["id"];
                            $result = Illuminate\Database\Capsule\Manager::table("tblproducts")->where("id", "=", $packageid)->select("name")->get();
                            $productname = $result[0]->name;
                            $movearray["product"][] = ["Type" => $productname, "productdomain" => $productdomain, "id" => $productid];
                        }
                    } else if (isset($tblproducts[0])) {
                        $packageid = $tblproducts[0]["packageid"];
                        $productdomain = $tblproducts[0]["tldproduct"];
                        $productid = $tblproducts[0]["id"];
                        $result = Illuminate\Database\Capsule\Manager::table("tblproducts")->where("id", "=", $packageid)->select("name")->get();
                        $productname = $result[0]->name;
                        $movearray["product"][] = ["Type" => $productname, "productdomain" => $productdomain, "id" => $productid];
                    }
                    echo "<div style=\"padding: 5px 20px;border: 1px solid #CCCCCC;-moz-border-radius: 5px;-webkit-border-radius: 5px;-o-border-radius: 5px;border-radius: 5px;\">";
                    echo "<br /><h3 style=\"border-bottom: 1px solid #cccccc;\"><strong>" . $LANG["movestep4"] . "</strong></h3>";
                    echo $LANG["movestep4desc"] . "<br /><br />";
                    echo "<div align=\"center\"><form method=\"post\" action=\"addonmodules.php?module=resellerclubmods_tools&domain=moveservices\">";
                    echo "<input type=\"hidden\" name=\"newcustomer\" value=\"" . $newcustomerid . "\" />";
                    echo "<input type=\"hidden\" name=\"newcustomermail\" value=\"" . $newcustomeremail . "\" />";
                    echo "<input type=\"hidden\" name=\"oldcustomermail\" value=\"" . $oldcustomeremail . "\" />";
                    echo "<input type=\"hidden\" name=\"oldrccustomerid\" value=\"" . $oldrccustomerid . "\" />";
                    echo "<input type=\"hidden\" name=\"oldcustomerid\" value=\"" . $oldcustomerid . "\" />";
                    echo "<input type=\"hidden\" name=\"domainname\" value=\"" . $domainname . "\" />";
                    echo "<table class=\"datatable\" border=\"0\" cellpadding=\"3\" cellspacing=\"1\" width=\"100%\"><tr><th>&nbsp;</th>";
                    echo "<th>" . $LANG["oldcustomer"] . "</th>";
                    echo "<th>" . $LANG["newcustomer"] . "</th>";
                    echo "</tr><tr>";
                    echo "<td><strong>" . $LANG["whmcsacc"] . "</strong></td>";
                    echo "<td>" . $oldcustomeremail . " (ID " . $oldcustomerid . ")</td>";
                    echo "<td>" . $newcustomeremail . " (ID " . $newcustomerid . ")</td>";
                    echo "<tr>";
                    echo "<td><strong>" . $LANG["rclbacc"] . "</strong></td>";
                    echo "<td>" . $oldcustomeremail . " (ID " . $oldrccustomerid . ")</td>";
                    echo "<td>" . $newcustomeremail . " (ID " . $newrccustomerid . ")</td>";
                    echo "</tr>";
                    if (!empty($movearray["domain"][0]["tlddomain"])) {
                        $tlddomain = $movearray["domain"][0]["tlddomain"];
                        $tldtype = $movearray["domain"][0]["Type"];
                        $tlddomainid = $movearray["domain"][0]["id"];
                        echo "<tr>";
                        echo "<td>" . $tldtype . "</td>";
                        echo "<td><span style=\"color:#CC0000\">" . $tlddomain . " (ID " . $tlddomainid . ")</span><input type=\"hidden\" name=\"domainid\" value=\"" . $tlddomainid . "\" /></td>";
                        echo "<td><span style=\"color:#086B1C\">" . $tlddomain . " (ID " . $tlddomainid . ")</span></td>";
                        echo "</tr>";
                    }
                    if (isset($movearray["product"][0])) {
                        foreach ($movearray["product"] as $productdetails) {
                            echo "<tr>";
                            echo "<td>" . $productdetails["Type"] . "</td>";
                            echo "<td><span style=\"color:#CC0000\">" . $productdetails["productdomain"] . " (ID " . $productdetails["id"] . ")</span><input type=\"hidden\" name=\"productid[]\" value=\"" . $productdetails["id"] . "\" /></td>";
                            echo "<td><span style=\"color:#086B1C\">" . $productdetails["productdomain"] . " (ID " . $productdetails["id"] . ")</span></td>";
                            echo "</tr>";
                        }
                    }
                    echo "<tr><td style=\"text-align:center;\"colspan=\"4\"><input name=\"contact\" type=\"hidden\" value=\"oldcontact\" /><input type=\"hidden\" name=\"domove\" value=\"true\" /></div>";
                    echo "<p><input type=\"submit\" value=\"" . $LANG["movebutton"] . "\" " . $disable . " class=\"btn btn-success\" />&nbsp;<a class=\"btn btn-primary\" href=\"" . $modulelink . "&domain=moveservices\">" . $LANG["cancellink"] . "</a></p></div></td>";
                    echo "</tr></table></form></div></div>";
                }
            }
        }
    }
} else if ($_POST["domove"] == "true") {
    $newcustomermail = $_POST["newcustomermail"];
    $oldcustomermail = $_POST["oldcustomermail"];
    $oldcustomerid = $_POST["oldcustomerid"];
    $existing_customerid = $_POST["oldrccustomerid"];
    $domainname = $_POST["domainname"];
    $newcustomerid = $_POST["newcustomer"];
    $contacttype = $_POST["contact"];
    $domainid = $_POST["domainid"];
    $productid = $_POST["productid"];
    $rcmdebuginfo = getDebuginfos();
    $modulename = $rcmdebuginfo["modulename"];
    $debug_addinfo = $rcmdebuginfo["debug_addinfo"];
    $method = "GET";
    $apifunction = "/api/customers/details.json";
    $data = ["username" => $newcustomermail];
    $newarrXml = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
    if ($newarrXml["status"] == "ERROR") {
        $newcustomer = $newcustomermail;
        $whmcscupwd = "";
        $signup_rc_customer_id = createOBcustomer($newcustomer, $rchttp_api, $rcauth_userid, $rcauth_password, $whmcscupwd);
        $newrccustomerid = $signup_rc_customer_id;
        if ($signup_rc_customer_id["status"] == "ERROR") {
            $errormessage = "The New Customer could not be created - " . $signup_rc_customer_id["message"];
        }
    } else {
        $newrccustomerid = $newarrXml["customerid"];
    }
    $method = "POST";
    $apifunction = "/api/products/move.json";
    $data = ["domain-name" => $IDN->encode($domainname), "existing-customer-id" => $existing_customerid, "new-customer-id" => $newrccustomerid, "default-contact" => $contacttype];
    $moveXml = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
    $action = "Move Domain/Service";
    $requeststring = $apifunction . " [reseller data protected] " . serialize_data($data);
    $responsedata = ["rcmdebug" => $debug_addinfo, "apidebug" => $moveXml];
    logModuleCall($modulename, $action, $requeststring, $responsedata);
    $movesuccess = "<div class=\"alert alert-success\"><p>" . $LANG["domainmovedsuccesfully"] . "</p></div>";
    $moveerror = "<div class=\"alert alert-danger\"><p>" . $moveXml["message"] . "</p></div>";
    $custcreateerror = "<div class=\"alert alert-danger\"><p>" . $errormessage . "</p></div>";
    if (!isset($errormessage)) {
        if ($moveXml["status"] == "ERROR") {
            echo $moveerror;
        } else {
            $output_domain = "";
            $output_product = "";
            $output_products = "";
            if ($domainid) {
                $update = ["userid" => $newcustomerid];
                Illuminate\Database\Capsule\Manager::table("tbldomains")->where("id", "=", $domainid)->update($update);
                $output_domain = "<p><strong>" . $LANG["domainnametitle"] . "</strong> <a target=\"_blank\" href=\"clientsdomains.php?userid=" . $newcustomerid . "&domainid=" . $domainid . "\">" . $domainname . "</a> <i class=\"fa fa-external-link\" aria-hidden=\"true\"></i><br /><small>" . $LANG["domainidtitle"] . " (" . $domainid . ")</small></p>";
            }
            if ($productid) {
                if (!is_array($productid)) {
                    $update = ["userid" => $newcustomerid];
                    Illuminate\Database\Capsule\Manager::table("tblhosting")->where("id", "=", $productid)->update($update);
                    $output_product = "<p><strong>" . $LANG["servicedomaintitle"] . "</strong> <a target=\"_blank\" href=\"clientsservices.php?userid=" . $newcustomerid . "&id=" . $productid . "\">" . $domainname . "</a> <i class=\"fa fa-external-link\" aria-hidden=\"true\"></i><br /><small>" . $LANG["serviceidtitle"] . " (" . $productid . ")</small></p>";
                } else {
                    foreach ($productid as $keyid) {
                        $update = ["userid" => $newcustomerid];
                        Illuminate\Database\Capsule\Manager::table("tblhosting")->where("id", "=", $keyid)->update($update);
                        $output_products .= "<p><strong>" . $LANG["servicedomaintitle"] . "</strong> <a target=\"_blank\" href=\"clientsservices.php?userid=" . $newcustomerid . "&id=" . $keyid . "\">" . $domainname . " (" . $keyid . ")</a> <i class=\"fa fa-external-link\" aria-hidden=\"true\"></i><br /><small>" . $LANG["serviceidtitle"] . " (" . $keyid . ")</small></p>";
                    }
                }
            }
            echo $movesuccess;
            $output_success = "\r\n\t\t\t\t<br />\r\n\t\t\t\t<div class=\"well well-sm\">\r\n\t\t\t\t<h3>" . $LANG["domainandservicedetailstitle"] . "</h3>\r\n\t\t\t\t" . $output_domain . $output_product . $output_products . "\r\n\t\t\t\t<hr />\r\n\t\t\t\t<h3>" . $LANG["fromandtocustomerdetailstitle"] . "</h3>\r\n\t\t\t\t<p><strong>" . $LANG["fromoldcustomer"] . "</strong> <a target=\"_blank\" href=\"clientssummary.php?userid=" . $oldcustomerid . "\">" . $oldcustomermail . "</a> <i class=\"fa fa-external-link\" aria-hidden=\"true\"></i>\r\n\t\t\t\t<br /><small>(" . $LANG["whmcscustid"] . " " . $oldcustomerid . " - " . $LANG["resellercustid"] . " " . $existing_customerid . ")</small></p>\r\n\t\t\t\t<p><strong>" . $LANG["tonewcustomer"] . "</strong> <a target=\"_blank\" href=\"clientssummary.php?userid=" . $newcustomerid . "\">" . $newcustomermail . "</a> <i class=\"fa fa-external-link\" aria-hidden=\"true\"></i>\r\n\t\t\t\t<br /><small>(" . $LANG["whmcscustid"] . " " . $newcustomerid . " - " . $LANG["resellercustid"] . " " . $newrccustomerid . ")</small></p>\r\n\t\t\t\t</div>\r\n\t\t\t";
        }
    } else {
        echo $custcreateerror;
    }
    echo $configuredto;
    if ($output_success) {
        echo $output_success;
    }
    echo "<form method=\"post\" action=\"addonmodules.php?module=resellerclubmods_tools&domain=moveservices\">";
    echo "<p><input type=\"submit\" value=\"" . $LANG["backtobutton"] . " " . $LANG["movetitle"] . "\" class=\"btn btn-primary\" /></p>";
    echo "</form>";
} else {
    echo $configuredto;
    echo $introdesc;
    echo "<div style=\"padding: 5px 20px;border: 1px solid #CCCCCC;-moz-border-radius: 5px;-webkit-border-radius: 5px;-o-border-radius: 5px;border-radius: 5px;\">";
    echo "<br /><h3 style=\"border-bottom: 1px solid #cccccc;\"><strong>" . $LANG["movestep1"] . "</strong></h3>";
    echo $LANG["movestep1desc"] . "<br /><br />";
    echo "<form method=\"post\" action=\"addonmodules.php?module=resellerclubmods_tools&domain=moveservices\"><input type=hidden name=\"movesearch\" value=\"true\" /><div>";
    echo $LANG["searchtitle"] . " <input type=\"text\" class=\"form-control\" style=\"display: inline; width: auto\" id=\"clientsearchval\" size=\"25\" />";
    echo "</div><br /><div id=\"clientsearchresults\">";
    echo "<div class=\"searchresultheader\">" . $LANG["searchresult"] . "</div>";
    echo "<div class=\"searchresult\" align=\"center\">" . $LANG["searchmatches"] . "</div>";
    echo "</div><br />";
    echo "<p>" . $LANG["customeridtitle"] . " <input type=\"text\" class=\"form-control\" style=\"display: inline; width: auto\" name=\"oldowner\" id=\"newuserid\" size=\"10\" /> <input type=\"submit\" value=\"" . $LANG["validatebutton"] . "\" class=\"btn btn-primary absmiddle\" /></p>";
    echo "</form></div></div>";
}

?>