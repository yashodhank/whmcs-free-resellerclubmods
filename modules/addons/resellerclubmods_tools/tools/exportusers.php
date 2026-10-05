<?php
if (!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
if (empty($vars["userexport_value"])) {
    $no_ofrecords = 100;
} else {
    $no_ofrecords = $vars["userexport_value"];
}
if (isset($_POST["register"]) && $_POST["register"] == "true") {
    $whmcscupwd = "";
    if ($_POST["bulk"] != "true") {
        $email = $_POST["username"];
        $newcustomer = $email;
        $signup_rc_customer_id = createOBcustomer($newcustomer, $rchttp_api, $rcauth_userid, $rcauth_password, $whmcscupwd);
        $rc_customerdetails["customerid"] = $signup_rc_customer_id;
        if (is_numeric($rc_customerdetails["customerid"])) {
            echo "<div class=\"alert alert-success\"><p>" . $LANG["usertitle"] . " " . $userName . " " . $LANG["exportsuccess"] . ": " . $rc_customerdetails["customerid"] . "</p></div>";
        } else {
            echo "<div class=\"alert alert-danger\"><p><strong>" . $LANG["unabletocreatecustomer"] . "</strong></p>";
            echo "<p><strong>" . $LANG["customeremail"] . "</strong> " . $newcustomer . "<br /><strong>" . $LANG["customerprofileissue"] . "</strong> " . $signup_rc_customer_id["message"] . "</p></div>";
        }
    } else if (isset($_POST["bulk"]) && $_POST["bulk"] == "true") {
        foreach ($_POST["usernames"] as $newcustomer) {
            $signup_rc_customer_id = createOBcustomer($newcustomer, $rchttp_api, $rcauth_userid, $rcauth_password, $whmcscupwd);
            if (!is_numeric($signup_rc_customer_id)) {
                $results["error"][$newcustomer] = $signup_rc_customer_id["message"];
            } else {
                $results["success"][$newcustomer] = "success";
            }
        }
        if (empty($results)) {
            echo "<div class=\"alert alert-danger\"><p>" . $LANG["nocustselected"] . "</p></div>";
        } else if ($results["error"]) {
            echo "<div class=\"alert alert-danger\"><p><strong>" . $LANG["onecustcreatefailed"] . "</strong></p>";
            foreach ($results["error"] as $resultkey => $resultvalue) {
                echo "<p><strong>" . $LANG["customeremail"] . "</strong> " . $resultkey . "<br /><strong>" . $LANG["customerprofileissue"] . "</strong> " . $resultvalue . "</p>";
            }
            echo "</p></div>";
        } else {
            echo "<div class=\"alert alert-success\"><p>" . $LANG["allcustcreated"] . "</p></div>";
        }
    }
    $rcm_pagination = new RcmToolsPagination();
    $whmcs_userdata = [];
    foreach (Illuminate\Database\Capsule\Manager::table("tblclients")->select("firstname", "lastname", "companyname", "address1", "address2", "city", "state", "postcode", "country", "phonenumber", "email")->get() as $customervars) {
        $whmcs_userdata[] = json_decode(json_encode($customervars), true);
    }
    echo $configuredto;
    echo "<h1>" . $LANG["whmcsvsrcuserslong"] . "</h1>";
    echo "<form method=\"post\" action=\"" . $_SERVER["REQUEST_URI"] . "\">";
    echo "<input class=\"form-control\" style=\"display: inline; width: auto\" size=\"50\" name=\"useremail\" value=\"" . $LANG["searchinputmessage"] . "\" onfocus=\"if(this.value=='" . $LANG["searchinputmessage"] . "')this.value=''\" type=\"text\">";
    echo "<input type=\"hidden\" name=\"searchrcuser\" value=\"true\"/>";
    echo "&nbsp;&nbsp;<input value=\"" . $LANG["searchuserbutton"] . "\" type=\"submit\" class=\"btn btn-primary absmiddle\"></form><br />";
    echo "<form method=\"POST\" action=\"" . $_SERVER["REQUEST_URI"] . "\">";
    echo "<input type=\"hidden\" name=\"register\" value=\"true\"/><input type=\"hidden\" name=\"bulk\" value=\"true\"/><table class=\"datatable\" border=\"0\" cellpadding=\"3\" cellspacing=\"1\" width=\"100%\"><tr>";
    echo "<th>" . $LANG["whmcsusermail"] . "</th><th>" . $LANG["rcusermail"] . "</th></tr>";
    $method = "GET";
    $apifunction = "/api/customers/search.json";
    $data = ["no-of-records" => 10, "page-no" => 1];
    $recsindb = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
    $remoteusers = $recsindb["recsindb"];
    $data = ["no-of-records" => $remoteusers, "page-no" => 1];
    $arrXml = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
    asort($whmcs_userdata);
    $productPages = $rcm_pagination->generate($whmcs_userdata, $no_ofrecords);
    if (count($productPages) != 0) {
        foreach ($productPages as $check_arr) {
            $email = strtolower($check_arr["email"]);
            foreach ($arrXml as $k => $v) {
                if (is_array($v) && in_array($email, $v)) {
                    $usersarray = ["found" => [$arrXml[$k]["customer.username"], $arrXml[$k]["customer.customerid"]]];
                    echo "<tr>";
                    if ($usersarray["found"]) {
                        echo "<td>" . $usersarray["found"][0] . "</td><td><span class=\"label active\">" . $LANG["itemfound"] . "</span>&nbsp;&nbsp;" . $usersarray["found"][0] . "&nbsp;(ID = <strong>" . $usersarray["found"][1] . "</strong>)</td>";
                    }
                    if ($usersarray["notfound"]) {
                        echo "<td>" . $usersarray["notfound"][0] . "</td>\r\n\t\t\t\t\t  <td>\r\n\t\t\t\t\t  <span class=\"label suspended\">" . $LANG["itemnotfound"] . "</span>\r\n\t\t\t\t\t  <input class=\"absmiddle\" type=\"checkbox\" name=\"usernames[]\" value=\"" . $usersarray["notfound"][0] . "\"/></td>";
                    }
                    echo "</tr>";
                } else {
                    $usersarray = ["notfound" => [$email]];
                }
            }
        }
    }
    echo "<tr><td colspan=\"2\"><input value=\"" . $LANG["rcexportbutton"] . "\" class=\"btn btn-success\" type=\"submit\"></td></tr>";
    echo "</form></table>";
} else {
    $rcm_pagination = new RcmToolsPagination();
    $whmcs_userdata = [];
    foreach (Illuminate\Database\Capsule\Manager::table("tblclients")->select("firstname", "lastname", "companyname", "address1", "address2", "city", "state", "postcode", "country", "phonenumber", "email")->get() as $customervars) {
        $whmcs_userdata[] = json_decode(json_encode($customervars), true);
    }
    if (isset($_POST["searchrcuser"]) && $_POST["searchrcuser"] == "true") {
        $rcemail = $_POST["useremail"];
        $pageno = 1;
        $noofrecords = 10;
        if (stripos($rcemail, "@")) {
            $method = "GET";
            $apifunction = "/api/customers/search.json";
            $data = ["username" => $rcemail, "no-of-records" => $noofrecords, "page-no" => $pageno];
            $arrXml = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
            $rcusermail = $arrXml[1]["customer.username"];
            $rcuserid = $arrXml[1]["customer.customerid"];
            foreach ($whmcs_userdata as $whmcs_usermail) {
                if (array_search($rcemail, $whmcs_usermail)) {
                    $whmcsusermail = $whmcs_usermail["email"];
                }
            }
            if (empty($whmcsusermail) && !empty($rcusermail)) {
                $resultmessage = "<div class=\"alert alert-warning\"><p>" . $rcusermail . "&nbsp;" . $LANG["existinrcbutnotwhmcs"] . "</p></div>";
                $htmlresult = "\r\n\t\t\t\t\t<table class=\"datatable\" border=\"0\" cellpadding=\"3\" cellspacing=\"1\" width=\"100%\">\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t<th>" . $LANG["searchwhmcsusermail"] . "</th><th>" . $LANG["searchrcusermail"] . "</th></tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t<td>" . $LANG["whmcsuserfnotfound"] . "</td><td><span class=\"label active\">" . $LANG["itemfound"] . "</span>&nbsp;&nbsp;ID = <strong>" . $rcuserid . "</strong></td></tr>\r\n\t\t\t\t\t</table>";
            } else if (!empty($whmcsusermail) && empty($rcusermail)) {
                $resultmessage = "<div class=\"alert alert-warning\"><p>" . $whmcsusermail . "&nbsp;" . $LANG["rcusernotfound"] . "</p></div>";
                $htmlresult = "\r\n\t\t\t\t\t<table class=\"datatable\" border=\"0\" cellpadding=\"3\" cellspacing=\"1\" width=\"100%\">\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t<th>" . $LANG["searchwhmcsusermail"] . "</th><th>" . $LANG["searchrcusermail"] . "</th></tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t<td>" . $whmcsusermail . "</td>\r\n\t\t\t\t\t<td>\r\n\t\t\t\t\t<form method=\"POST\" action=\"" . $_SERVER["REQUEST_URI"] . "\">\r\n\t\t\t\t\t<input type=\"hidden\" name=\"register\" value=\"true\"/>\r\n\t\t\t\t\t<span class=\"label suspended\">" . $LANG["itemnotfound"] . "</span>\r\n\t\t\t\t\t<input type=\"hidden\" name=\"username\" value=\"" . $whmcsusermail . "\"/>\r\n\t\t\t\t\t<input value=\"" . $LANG["rcexportbutton"] . "\" class=\"btn btn-success\" type=\"submit\"></form></td></tr>\r\n\t\t\t\t\t</table>";
            } else if (!empty($whmcsusermail) && !empty($rcusermail)) {
                $resultmessage = "<div class=\"alert alert-success\"><p>" . $whmcsusermail . "&nbsp;" . $LANG["existinrcandwhmcs"] . "</p></div>";
                $htmlresult = "\r\n\t\t\t\t\t<table class=\"datatable\" border=\"0\" cellpadding=\"3\" cellspacing=\"1\" width=\"100%\">\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t<th>" . $LANG["searchwhmcsusermail"] . "</th><th>" . $LANG["searchrcusermail"] . "</th></tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t<td>" . $whmcsusermail . "</td><td><span class=\"label active\">" . $LANG["itemfound"] . "</span>&nbsp;&nbsp;ID = <strong>" . $rcuserid . "</strong></td></tr>\r\n\t\t\t\t\t</table>";
            } else {
                $resultmessage = "<div class=\"alert alert-danger\"><p>" . $_POST["useremail"] . "&nbsp;" . $LANG["inexistentuser"] . "</p></div>";
            }
        } else {
            echo "<div class=\"alert alert-warning\"><p>" . $LANG["enterusermail"] . "</p></div>";
        }
    }
    if (isset($resultmessage)) {
        echo $resultmessage;
    }
    echo $configuredto;
    echo "<h1>" . $LANG["whmcsvsrcuserslong"] . "</h1>";
    echo "<form method=\"post\" action=\"" . $_SERVER["REQUEST_URI"] . "\">";
    echo "<input class=\"form-control\" style=\"display: inline; width: auto\" size=\"50\" name=\"useremail\" value=\"" . $LANG["searchinputmessage"] . "\" onfocus=\"if(this.value=='" . $LANG["searchinputmessage"] . "')this.value=''\" type=\"text\">";
    echo "<input type=\"hidden\" name=\"searchrcuser\" value=\"true\"/>";
    echo "&nbsp;&nbsp;<input value=\"" . $LANG["searchuserbutton"] . "\" type=\"submit\" class=\"btn btn-primary absmiddle\"></form><br />";
    if (isset($htmlresult)) {
        echo $htmlresult . "<br />";
    }
    echo "<script type=\"text/javascript\">\$(function () {\$('#checkall').click(function () {\$(this).parents('fieldset:eq(0)').find(':checkbox').attr('checked', this.checked);});});</script>";
    echo "<form method=\"POST\" action=\"" . $_SERVER["REQUEST_URI"] . "\">";
    echo "<fieldset style=\"border:none;padding:0;marging:0;\"><input type=\"hidden\" name=\"register\" value=\"true\"/><input type=\"hidden\" name=\"bulk\" value=\"true\"/><table class=\"datatable\" border=\"0\" cellpadding=\"3\" cellspacing=\"1\" width=\"100%\"><tr>";
    echo "<th>" . $LANG["whmcsusermail"] . "</th><th>" . $LANG["rcusermail"] . "</th></tr>";
    $method = "GET";
    $apifunction = "/api/customers/search.json";
    $data = ["no-of-records" => 10, "page-no" => 1];
    $recsindb = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
    $remoteusers = $recsindb["recsindb"];
    $data = ["no-of-records" => $remoteusers, "page-no" => 1];
    $arrXml = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
    asort($whmcs_userdata);
    $productPages = $rcm_pagination->generate($whmcs_userdata, $no_ofrecords);
    if (count($productPages) != 0) {
        foreach ($productPages as $check_arr) {
            $email = strtolower($check_arr["email"]);
            foreach ($arrXml as $k => $v) {
                if (is_array($v) && in_array($email, $v)) {
                    $usersarray = ["found" => [$arrXml[$k]["customer.username"], $arrXml[$k]["customer.customerid"]]];
                    echo "<tr>";
                    if ($usersarray["found"]) {
                        echo "<td>" . $usersarray["found"][0] . "</td><td><span class=\"label active\">" . $LANG["itemfound"] . "</span>&nbsp;&nbsp;" . $usersarray["found"][0] . "&nbsp;(ID = <strong>" . $usersarray["found"][1] . "</strong>)</td>";
                    }
                    if ($usersarray["notfound"]) {
                        echo "<td>" . $usersarray["notfound"][0] . "</td>\r\n\t\t\t\t\t  <td>\r\n\t\t\t\t\t  <span class=\"label suspended\">" . $LANG["itemnotfound"] . "</span>\r\n\t\t\t\t\t  <input class=\"absmiddle\" type=\"checkbox\" name=\"usernames[]\" value=\"" . $usersarray["notfound"][0] . "\"/></td>";
                    }
                    echo "</tr>";
                } else {
                    $usersarray = ["notfound" => [$email]];
                }
            }
        }
    }
    echo "<tr><td><input value=\"" . $LANG["rcexportbutton"] . "\" class=\"btn btn-success\" type=\"submit\"></td><td><input type=\"checkbox\" name=\"checkall\" id=\"checkall\"> " . $LANG["selectallcustomers"] . "</td></tr>";
    echo "</fieldset></form></table><br />";
    echo $pageNumbers = "<div>" . $rcm_pagination->links() . "</div>\n";
}

?>