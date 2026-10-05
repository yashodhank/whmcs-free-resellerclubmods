<?php
if (!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
$rcm_pagination = new RcmToolsPagination();
$mypageno = $_GET["page"];
if (empty($vars["userimport_value"])) {
    $noofrecords = 100;
} else {
    $noofrecords = $vars["userimport_value"];
}
if (empty($mypageno)) {
    $pageno = 1;
} else {
    $pageno = $mypageno;
}
$method = "GET";
$apifunction = "/api/customers/search.json";
$data = ["no-of-records" => $noofrecords, "page-no" => $pageno];
$arrXml = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
foreach ($arrXml as $userdetails) {
    if (is_array($userdetails)) {
        $rcuser_array[] = ["email" => $userdetails["customer.username"]];
    }
}
if (isset($_POST["import"]) && $_POST["import"] == "true") {
    $emptyimporterror = "<div class=\"alert alert-danger\"><p>" . $LANG["nocustselected"] . "</p></div>";
    $successimport = "<div class=\"alert alert-success\"><p>" . $LANG["impallcustcreated"] . "</p></div>";
    $importerror = "<div class=\"alert alert-warning\"><p>" . $LANG["imponecustcreatefailed"] . "</p></div>";
    $charsetis = strtoupper($tblconf["Charset"]);
    if (!empty($_POST["rcusername"])) {
        $replace = [" ", "-", ".", "+", "/", "_", "(", ")", ","];
        foreach ($_POST["rcusername"] as $email) {
            $method = "GET";
            $apifunction = "/api/customers/details.json";
            $data = ["username" => $email];
            $detailsarrXml = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
            if ($detailsarrXml["status"] != "ERROR") {
                $splitup = explode(" ", $detailsarrXml["name"], 2);
                list($firstname, $lastname) = $splitup;
                if (empty($address2)) {
                    $address2 = "";
                } else {
                    $address2 = $detailsarrXml["address2"];
                }
                $langpref = $detailsarrXml["langpref"];
                if ($langpref == "nl") {
                    $language = "dutch";
                } else if ($langpref == "de") {
                    $language = "german";
                } else if ($langpref == "it") {
                    $language = "italian";
                } else if ($langpref == "pt") {
                    $language = "portuguese-pt";
                } else if ($langpref == "br") {
                    $language = "portuguese-br";
                } else if ($langpref == "es") {
                    $language = "spanish";
                } else if ($langpref == "e1") {
                    $language = "spanish";
                } else if ($langpref == "tr") {
                    $language = "turkish";
                } else if ($langpref == "en") {
                    $language = "english";
                } else {
                    $language = "english";
                }
                if ($_POST["fundsimport"] == "true") {
                    $rccustomer_id = $detailsarrXml["customerid"];
                    $method = "GET";
                    $apifunction = "/api/billing/customer-balance.json";
                    $data = ["username" => $email, "customer-id" => $rccustomer_id];
                    $customerfundsarrXml = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
                    $credit = $customerfundsarrXml["accountingcurrencybalance"];
                } else {
                    $credit = "";
                }
                $rcuser_multi_array[] = ["email" => $email, "companyname" => $detailsarrXml["company"], "city" => $detailsarrXml["city"], "country" => $detailsarrXml["country"], "rcuserid" => $detailsarrXml["customerid"], "address1" => $detailsarrXml["address1"], "address2" => $address2, "state" => $detailsarrXml["state"], "postcode" => $detailsarrXml["zip"], "phonenumber" => $detailsarrXml["telno"], "firstname" => $firstname, "lastname" => $lastname, "datecreated" => date("Y-m-d", $detailsarrXml["creationdt"]), "language" => $language, "credit" => $credit];
            } else {
                $results_array[] = "error";
            }
        }
        foreach ($rcuser_multi_array as $rcuser_multi) {
            if ($charsetis != "UTF-8") {
                $firstname = utf8_decode($rcuser_multi["firstname"]);
                $lastname = utf8_decode($rcuser_multi["lastname"]);
                $companyname = utf8_decode($rcuser_multi["companyname"]);
                $address1 = utf8_decode($rcuser_multi["address1"]);
                $address2 = utf8_decode($rcuser_multi["address2"]);
                $city = utf8_decode($rcuser_multi["city"]);
                $state = utf8_decode($rcuser_multi["state"]);
            } else {
                $firstname = str_replace($replace, "", $rcuser_multi["firstname"]);
                $lastname = str_replace($replace, "", $rcuser_multi["lastname"]);
                $companyname = $rcuser_multi["companyname"];
                $address1 = $rcuser_multi["address1"];
                $address2 = $rcuser_multi["address2"];
                $city = $rcuser_multi["city"];
                $state = $rcuser_multi["state"];
            }
            $email = $rcuser_multi["email"];
            $postcode = $rcuser_multi["postcode"];
            $country = $rcuser_multi["country"];
            $phonenumber = $rcuser_multi["phonenumber"];
            if ($_POST["fundsimport"] == "true") {
                $credit = $rcuser_multi["credit"];
            } else {
                $credit = "";
            }
            $datecreated = $rcuser_multi["datecreated"];
            $lastlogin = "0000-00-00 00:00:00";
            $status = "Active";
            $language = $rcuser_multi["language"];
            $values = ["firstname" => $firstname, "lastname" => $lastname, "companyname" => $companyname, "email" => $email, "address1" => $address1, "address2" => $address2, "city" => $city, "state" => $state, "postcode" => $postcode, "country" => $country, "phonenumber" => $phonenumber, "currency" => 1, "credit" => $credit, "datecreated" => $datecreated, "lastlogin" => $lastlogin, "status" => $status, "language" => $language];
            $tblclients_result = Illuminate\Database\Capsule\Manager::table("tblclients")->insert($values);
        }
        if (is_array($results_array) && in_array("error", $results_array)) {
            echo $importerror;
        } else {
            echo $successimport;
        }
    } else {
        echo $emptyimporterror;
    }
}
if (isset($_POST["searchwhmcsuser"]) && $_POST["searchwhmcsuser"] == "true") {
    $rcemail = $_POST["useremail"];
    if (stripos($rcemail, "@")) {
        $method = "GET";
        $apifunction = "/api/customers/search.json";
        $data = ["username" => $rcemail, "no-of-records" => $noofrecords, "page-no" => $pageno];
        $arrXml = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
        $rcusermail = $arrXml[1]["customer.username"];
        $result = Illuminate\Database\Capsule\Manager::table("tblclients")->where("email", "=", $rcemail)->select("id", "email")->get();
        $whmcsusermail = $result[0]->email;
        $whmcsuserid = $result[0]->id;
        if (empty($whmcsusermail) && !empty($rcusermail)) {
            $resultmessage = "<div class=\"alert alert-warning\"><p>" . $rcusermail . "&nbsp;" . $LANG["whmcsusernotfound"] . "</p></div>";
            $htmlresult = "\r\n\t\t\t\t<br /><table class=\"datatable\" border=\"0\" cellpadding=\"3\" cellspacing=\"1\" width=\"100%\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t<th>" . $LANG["searchrcusermail"] . "</th><th>" . $LANG["searchwhmcsusermail"] . "</th></tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t<td>" . $rcusermail . "</td>\r\n\t\t\t\t<td>\r\n\t\t\t\t<form method=\"POST\" action=\"" . $_SERVER["REQUEST_URI"] . "\">\r\n\t\t\t\t<input name=\"rcusername[]\" type=\"hidden\" value=\"" . $rcusermail . "\" />\r\n\t\t\t\t<input type=\"hidden\" name=\"import\" value=\"true\"/>\r\n\t\t\t\t<div class=\"abbsmiddle\"> " . $LANG["withfundsimportdesc"] . " <input name=\"fundsimport\" type=\"radio\" value=\"true\" /> " . $LANG["yesword"] . " <input name=\"fundsimport\" type=\"radio\" value=\"false\" checked=\"checked\" /> " . $LANG["noword"] . "&nbsp;&nbsp;\r\n\t\t\t\t<input value=\"" . $LANG["whmcsimportbutton"] . "\" class=\"btn btn-success\" type=\"submit\" " . $disabled . "></div>\r\n\t\t\t\t</form>\r\n\t\t\t\t</td></tr>\r\n\t\t\t\t</table>";
        } else if (!empty($whmcsusermail) && empty($rcusermail)) {
            $resultmessage = "<div class=\"alert alert-warning\"><p>" . $whmcsusermail . "&nbsp;" . $LANG["existinwhmcsbutnotrc"] . "</p></div>";
            $htmlresult = "\r\n\t\t\t\t<br /><table class=\"datatable\" border=\"0\" cellpadding=\"3\" cellspacing=\"1\" width=\"100%\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t<th>" . $LANG["searchrcusermail"] . "</th><th>" . $LANG["searchwhmcsusermail"] . "</th></tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t<td>" . $LANG["rcuserfnotfound"] . "</td><td><span class=\"label active\">" . $LANG["itemfound"] . "</span>&nbsp;&nbsp;ID = <strong>" . $whmcsuserid . "</strong></td></tr>\r\n\t\t\t\t</table>";
        } else if (!empty($whmcsusermail) && !empty($rcusermail)) {
            $resultmessage = "<div class=\"alert alert-success\"><p>" . $rcusermail . "&nbsp;" . $LANG["existinrcandwhmcs"] . "</p></div>";
            $htmlresult = "\r\n\t\t\t\t<br /><table class=\"datatable\" border=\"0\" cellpadding=\"3\" cellspacing=\"1\" width=\"100%\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t<th>" . $LANG["searchrcusermail"] . "</th><th>" . $LANG["searchwhmcsusermail"] . "</th></tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t<td>" . $rcusermail . "</td><td><span class=\"label active\">" . $LANG["itemfound"] . "</span>&nbsp;&nbsp;ID = <strong>" . $whmcsuserid . "</strong></td></tr>\r\n\t\t\t\t</table>";
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
echo "<h1>" . $LANG["rcvswhmcscuserslong"] . "</h1>";
echo "<form method=\"post\" action=\"" . $_SERVER["REQUEST_URI"] . "\">";
echo "<input class=\"form-control\" style=\"display: inline; width: auto\" size=\"50\" name=\"useremail\" value=\"" . $LANG["searchinputmessage"] . "\" onfocus=\"if(this.value=='" . $LANG["searchinputmessage"] . "')this.value=''\" type=\"text\">";
echo "<input type=\"hidden\" name=\"searchwhmcsuser\" value=\"true\"/>";
echo "&nbsp;&nbsp;<input value=\"" . $LANG["searchuserbutton"] . "\" type=\"submit\" class=\"btn btn-primary absmiddle\"></form>";
if (isset($result)) {
    echo $htmlresult . "<br />";
}
echo "<script type=\"text/javascript\">\$(function () {\$('#checkall').click(function () {\$(this).parents('fieldset:eq(0)').find(':checkbox').attr('checked', this.checked);});});</script>";
echo "<form method=\"POST\" action=\"" . $_SERVER["REQUEST_URI"] . "\">";
echo "<fieldset style=\"border:none;padding:0;marging:0;\"><table class=\"datatable\" border=\"0\" cellpadding=\"3\" cellspacing=\"1\" width=\"100%\"><tr>";
echo "<th>" . $LANG["rcusermail"] . "</th><th>" . $LANG["whmcsusermail"] . "</th></tr>";
$productPages = $rcm_pagination->generate($rcuser_array, $noofrecords);
foreach ($productPages as $whmcs_usersdata) {
    $email = $whmcs_usersdata["email"];
    $check_arr = Illuminate\Database\Capsule\Manager::table("tblclients")->where("email", "=", $email)->select("id", "firstname", "lastname", "companyname", "address1", "address2", "city", "state", "postcode", "country", "phonenumber", "email")->get();
    if ($check_arr[0]->email == "") {
        $usersarray = ["notfound" => [$email]];
        $isthere = count($usersarray["notfound"]);
    } else {
        $usersarray = ["found" => [$check_arr[0]->email, $check_arr[0]->id]];
    }
    echo "<tr>";
    if ($usersarray["found"]) {
        echo "<td>" . $usersarray["found"][0] . "</td><td><span class=\"label active\">" . $LANG["itemfound"] . "</span>&nbsp;&nbsp;" . $usersarray["found"][0] . "&nbsp;(ID = <strong>" . $usersarray["found"][1] . "</strong>)</td>";
    }
    if ($usersarray["notfound"]) {
        echo "<td>" . $usersarray["notfound"][0] . "</td>\r\n\t\t\t  <td>\r\n\t\t\t  <span class=\"label suspended\">" . $LANG["itemnotfound"] . "</span>\r\n\t\t\t  <input class=\"absmiddle\" name=\"rcusername[]\" type=\"checkbox\" value=\"" . $usersarray["notfound"][0] . "\" /></td>";
    }
}
if (empty($isthere)) {
    $disabled = "disabled=\"disabled\"";
}
echo "<tr><td><input type=\"hidden\" name=\"import\" value=\"true\"/>";
echo "<div class=\"abbsmiddle\"> " . $LANG["fundsimportdesc"] . " <input name=\"fundsimport\" type=\"radio\" value=\"true\" /> " . $LANG["yesword"] . " <input name=\"fundsimport\" type=\"radio\" value=\"false\" checked=\"checked\" /> " . $LANG["noword"] . "<br />" . $LANG["selectallcustomers"] . " <input type=\"checkbox\" name=\"checkall\" id=\"checkall\"></td>";
echo "<td><input value=\"" . $LANG["whmcsimportbutton"] . "\" class=\"btn btn-success\" type=\"submit\" " . $disabled . "></div>";
echo "</td></tr></table></fieldset></form><br />";
echo $pageNumbers = "<div>" . $rcm_pagination->links() . "</div>\n";

?>