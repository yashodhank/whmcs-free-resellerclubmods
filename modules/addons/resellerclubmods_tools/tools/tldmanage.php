<?php
if (!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
if (strtoupper((string) ($_SERVER["REQUEST_METHOD"] ?? "GET")) === "POST") {
    rcm_require_post_token();
}
$rcm_tld_batch_limit = 500;
echo "<script type=\"text/javascript\">// <![CDATA[\r\njQuery(document).ready(function(){\r\n  jQuery(\".scroll\").click(function(event){\r\n    event.preventDefault();\r\n    var offset = jQuery(jQuery(this).attr('href')).offset().top;\r\n    jQuery('html, body').animate({scrollTop:offset}, 1000);\r\n  });\r\n});\r\n// ]]></script>";
echo $configuredto;
echo "<a id=\"top\"></a>";
echo "<h1>" . $LANG["tldmanagetitle"] . "</h1>";
echo "<p>" . $LANG["tldmanagedesc"] . "</p>";
	echo "\r\n\t<ul><li><a class=\"scroll\" href=\"#tool01\">Tool 1# - " . $LANG["domtools01"] . "</a></li>\r\n\t<li><a class=\"scroll\" href=\"#tool02\">Tool 2# - " . $LANG["domtools02"] . "</a></li>\r\n\t<li><a class=\"scroll\" href=\"#tool03\">Tool 3# - " . $LANG["domtools03"] . "</a></li>\r\n\t<li><a class=\"scroll\" href=\"#tool06\">Tool 4# - " . $LANG["domtools06"] . "</a></li>\r\n\t<li><a class=\"scroll\" href=\"#tool04\">Tool 5# - " . $LANG["domtools04"] . "</a></li>\r\n\t<li><a class=\"scroll\" href=\"#tool05\">Tool 6# - " . $LANG["domtools05"] . "</a></li></ul>\r\n\t";
$registrar_filter = $_POST["reloadregistrar"] ?? "";
if (empty($registrar_filter)) {
    $filtered = $LANG["alldominwhmcstitle"];
    $filteredtld = $LANG["alltldsinwhmcstitle"];
} else {
    $filtered = $registrar_filter;
    $filteredtld = $registrar_filter;
}
$currencie_array = [];
foreach (Illuminate\Database\Capsule\Manager::table("tblcurrencies")->get() as $data) {
    $currencie_array[$data->code] = ["id" => $data->id, "code" => $data->code, "rate" => $data->rate, "default" => $data->default];
}
$status_array = ["Pending" => $LANG["pending"], "Pending Registration" => $LANG["pendingregistration"], "Pending Transfer" => $LANG["pendingtransfer"], "Active" => $LANG["active"], "Grace" => $LANG["graceperiod"], "Redemption" => $LANG["redemptionperiod"], "Expired" => $LANG["expired"], "Cancelled" => $LANG["cancelled"], "Fraud" => $LANG["fraud"], "Transferred Away" => $LANG["transferredaway"]];
$billingcycles_array = [1 => "1" . $LANG["domainyear"], 2 => "2" . $LANG["domainyears"], 3 => "3" . $LANG["domainyears"], 4 => "4" . $LANG["domainyears"], 5 => "5" . $LANG["domainyears"], 6 => "6" . $LANG["domainyears"], 7 => "7" . $LANG["domainyears"], 8 => "8" . $LANG["domainyears"], 9 => "9" . $LANG["domainyears"], 10 => "10" . $LANG["domainyears"]];
if (isset($_POST["changeregmodule"]) && $_POST["changeregmodule"] == "true") {
    if (empty($_POST["domainlist"])) {
        $result01 = "<div class=\"alert alert-danger\"><p>" . $LANG["selectadomtoregchange"] . "</p></div>";
    } else if (empty($_POST["registrar"])) {
        $result01 = "<div class=\"alert alert-danger\"><p>" . $LANG["selectregmoduleerror"] . "</p></div>";
    } else {
        $domainBatch = array_slice((array) $_POST["domainlist"], 0, $rcm_tld_batch_limit);
        foreach ($domainBatch as $domains) {
            $update = ["registrar" => $_POST["registrar"]];
            Illuminate\Database\Capsule\Manager::table("tbldomains")->where("domain", "=", $domains)->update($update);
        }
        $result01 = "<div class=\"alert alert-success\"><p>" . $LANG["changeregmodulesuccess"] . ": <strong>" . rcm_e($_POST["registrar"]) . "</strong></p></div>";
    }
}
if (isset($_POST["changeautoregmodule"]) && $_POST["changeautoregmodule"] == "true") {
    if (empty($_POST["tldlist"])) {
        $result02 = "<div class=\"alert alert-danger\"><p>" . $LANG["selectatldtoregchange"] . "</p></div>";
    } else if (empty($_POST["registrar"])) {
        $result02 = "<div class=\"alert alert-danger\"><p>" . $LANG["selectregmoduleerror"] . "</p></div>";
    } else {
        $tldBatch = array_slice((array) $_POST["tldlist"], 0, $rcm_tld_batch_limit);
        foreach ($tldBatch as $tlds) {
            $update = ["autoreg" => $_POST["registrar"]];
            Illuminate\Database\Capsule\Manager::table("tbldomainpricing")->where("extension", "=", $tlds)->update($update);
        }
        $result02 = "<div class=\"alert alert-success\"><p>" . $LANG["changetldregmodulesuccess"] . ": <strong>" . rcm_e($_POST["registrar"]) . "</strong></p></div>";
    }
}
$domainslist = $_POST["changetldsaddonslist"];
if (isset($_POST["changemanagetools"]) && $_POST["changemanagetools"] == "true") {
    if (empty($domainslist) && $_POST["doupdalldomains"] != "true") {
        $result03 = "<div class=\"alert alert-danger\"><p>" . $LANG["selectadomtoolchange"] . "</p></div>";
    } else {
        if (empty($_POST["dnsmanagement"])) {
            $dnsmanagement = "";
            $dnstool = $LANG["disabled"];
        } else if ($_POST["dnsmanagement"] == "on") {
            $dnstool = $LANG["activated"];
            $dnsmanagement = 1;
        } else {
            $dnstool = $LANG["notchanged"];
        }
        if (empty($_POST["emailforwarding"])) {
            $emailforwarding = "";
            $mailtool = $LANG["disabled"];
        } else if ($_POST["emailforwarding"] == "on") {
            $mailtool = $LANG["activated"];
            $emailforwarding = 1;
        } else {
            $mailtool = $LANG["notchanged"];
        }
        if (empty($_POST["idprotection"])) {
            $idprotection = "";
            $idtool = $LANG["disabled"];
        } else if ($_POST["idprotection"] == "on") {
            $idtool = $LANG["activated"];
            $idprotection = 1;
        } else {
            $idtool = $LANG["notchanged"];
        }
        $alldomains = false;
        if ($_POST["doupdalldomains"] == "true") {
            $alldomains = true;
            $domainslist = [];
            foreach (Illuminate\Database\Capsule\Manager::table("tbldomains")->select("domain")->get() as $data) {
                $domainslist[] = $data->domain;
            }
        }
        foreach ($domainslist as $domains) {
            if ($_POST["dnsmanagement"] != "x") {
                if (empty($_POST["dnsmanagement"])) {
                    $dnsmanagement = "";
                } else {
                    $dnsmanagement = 1;
                }
                $update = ["dnsmanagement" => $dnsmanagement];
                Illuminate\Database\Capsule\Manager::table("tbldomains")->where("domain", "=", $domains)->update($update);
            }
            if ($_POST["emailforwarding"] != "x") {
                if (empty($_POST["emailforwarding"])) {
                    $emailforwarding = "";
                } else {
                    $emailforwarding = 1;
                }
                $update = ["emailforwarding" => $emailforwarding];
                Illuminate\Database\Capsule\Manager::table("tbldomains")->where("domain", "=", $domains)->update($update);
            }
            if ($_POST["idprotection"] != "x") {
                if (empty($_POST["idprotection"])) {
                    $idprotection = "";
                } else {
                    $idprotection = 1;
                }
                $update = ["idprotection" => $idprotection];
                Illuminate\Database\Capsule\Manager::table("tbldomains")->where("domain", "=", $domains)->update($update);
            }
            if ($_POST["dorecalc"] == "true") {
                $command = "updateclientdomain";
                $values = ["domain" => $domains, "autorecalc" => true];
                $apiresults = localAPI($command, $values, $maileradmin);
                $resultmessage = "<br />" . $LANG["updaterecurringmessage"];
            }
            $postdomains[] = $domains;
            if ($alldomains) {
                $output = "all domains";
            } else {
                $output = implode(", ", $postdomains);
            }
        }
        $result03 = "<div class=\"alert alert-success\"><p>" . $LANG["selectadomtoolchangesuccess"] . " " . $output . "<br /><strong>" . $LANG["tldaddonsettingstitle"] . "</strong> dnsmanagement = <strong>" . $dnstool . "</strong>, emailforwarding = <strong>" . $mailtool . "</strong>, idprotection = <strong>" . $idtool . "</strong>" . $resultmessage . "</p></div>";
    }
}
$domainstldlist = $_POST["changetldsaddonslist"];
if (isset($_POST["changetldsaddons"]) && $_POST["changetldsaddons"] == "true") {
    if (empty($domainstldlist)) {
        $result06 = "<div class=\"alert alert-danger\"><p>" . $LANG["selectatldtoolchange"] . "</p></div>";
    } else {
        if (empty($_POST["dnsmanagement"])) {
            $dnsmanagement = "";
            $dnstool = $LANG["disabled"];
        } else if ($_POST["dnsmanagement"] == "on") {
            $dnstool = $LANG["activated"];
            $dnsmanagement = 1;
        } else {
            $dnstool = $LANG["notchanged"];
        }
        if (empty($_POST["emailforwarding"])) {
            $emailforwarding = "";
            $mailtool = $LANG["disabled"];
        } else if ($_POST["emailforwarding"] == "on") {
            $mailtool = $LANG["activated"];
            $emailforwarding = 1;
        } else {
            $mailtool = $LANG["notchanged"];
        }
        if (empty($_POST["idprotection"])) {
            $idprotection = "";
            $idtool = $LANG["disabled"];
        } else if ($_POST["idprotection"] == "on") {
            $idtool = $LANG["activated"];
            $idprotection = 1;
        } else {
            $idtool = $LANG["notchanged"];
        }
        foreach ($domainstldlist as $domainstlds) {
            if ($_POST["dnsmanagement"] != "x") {
                if (empty($_POST["dnsmanagement"])) {
                    $dnsmanagement = "";
                } else {
                    $dnsmanagement = 1;
                }
                $update = ["dnsmanagement" => $dnsmanagement];
                Illuminate\Database\Capsule\Manager::table("tbldomainpricing")->where("extension", "=", $domainstlds)->update($update);
            }
            if ($_POST["emailforwarding"] != "x") {
                if (empty($_POST["emailforwarding"])) {
                    $emailforwarding = "";
                } else {
                    $emailforwarding = 1;
                }
                $update = ["emailforwarding" => $emailforwarding];
                Illuminate\Database\Capsule\Manager::table("tbldomainpricing")->where("extension", "=", $domainstlds)->update($update);
            }
            if ($_POST["idprotection"] != "x") {
                if (empty($_POST["idprotection"])) {
                    $idprotection = "";
                } else {
                    $idprotection = 1;
                }
                $update = ["idprotection" => $idprotection];
                Illuminate\Database\Capsule\Manager::table("tbldomainpricing")->where("extension", "=", $domainstlds)->update($update);
            }
            $posttlds[] = $domainstlds;
        }
        $result06 = "<div class=\"alert alert-success\"><p>" . $LANG["selectatldtoolchangesuccess"] . " " . implode(", ", $posttlds) . "<br /><strong>" . $LANG["tldaddonsettingstitle"] . "</strong> dnsmanagement = <strong>" . $dnstool . "</strong>, emailforwarding = <strong>" . $mailtool . "</strong>, idprotection = <strong>" . $idtool . "</strong>" . $resultmessage . "</p></div>";
    }
}
if (isset($_POST["priceupdatetool"]) && $_POST["priceupdatetool"] == "true") {
    if (!isset($_POST["tldlist"])) {
        $result04 = "<div class=\"alert alert-danger\"><p>" . $LANG["recurringmessage01"] . "</p></div>";
    } else if ($_POST["customrecurringprice"] == "") {
        $result04 = "<div class=\"alert alert-danger\"><p>" . $LANG["recurringmessage02"] . "</p></div>";
    } else if (!is_numeric($_POST["customrecurringprice"])) {
        $result04 = "<div class=\"alert alert-danger\"><p>" . $LANG["recurringmessage03"] . "</p></div>";
    } else if (!isset($_POST["billingcycle"])) {
        $result04 = "<div class=\"alert alert-danger\"><p>" . $LANG["recurringmessage04"] . "</p></div>";
    } else if (!isset($_POST["status"])) {
        $result04 = "<div class=\"alert alert-danger\"><p>" . $LANG["recurringmessage06"] . "</p></div>";
    } else {
        foreach ($currencie_array as $currency_key => $currency_value) {
            if ($currency_value["id"] == $_POST["currency"]) {
                $currency_code = $currency_key;
            }
        }
        $counttlds = count($_POST["tldlist"]);
        foreach ($_POST["tldlist"] as $k => $v) {
            $tabledomains = "tbldomains";
            $fieldsdomains = "domain,userid,status,registrationperiod";
            $whmcs_domains = [];
            foreach (Illuminate\Database\Capsule\Manager::table("tbldomains")->where("domain", "LIKE", "%" . $v)->select("domain", "userid", "status", "registrationperiod")->get() as $fila_tbldomains) {
                if (in_array($fila_tbldomains->registrationperiod, $_POST["billingcycle"]) && in_array($fila_tbldomains->status, $_POST["status"])) {
                    if ($counttlds == 1) {
                        $uniquetld = $_POST["tldlist"][0];
                        if (preg_match("/^" . strstr($fila_tbldomains->domain, ".") . "\$/", $uniquetld)) {
                            $whmcs_domains[] = ["userid" => $fila_tbldomains->userid, "domain" => $fila_tbldomains->domain, "status" => $fila_tbldomains->status, "billingcycle" => $fila_tbldomains->registrationperiod];
                        }
                    } else {
                        $whmcs_domains[] = ["userid" => $fila_tbldomains->userid, "domain" => $fila_tbldomains->domain, "status" => $fila_tbldomains->status, "billingcycle" => $fila_tbldomains->registrationperiod];
                    }
                }
            }
        }
        foreach ($whmcs_domains as $updvalues) {
            $domain = $updvalues["domain"];
            $result = Illuminate\Database\Capsule\Manager::table("tbldomains")->where("domain", "=", $domain)->select("recurringamount")->get();
            $actualrecurring = $result[0]->recurringamount;
            $usercurrency = getCurrency($updvalues["userid"]);
            if ($usercurrency["id"] == $_POST["currency"] && $actualrecurring != $_POST["excluderecurring"]) {
                $update = ["recurringamount" => $_POST["customrecurringprice"] * $updvalues["billingcycle"]];
                Illuminate\Database\Capsule\Manager::table("tbldomains")->where("domain", "=", $domain)->where("registrationperiod", "=", $updvalues["billingcycle"])->update($update);
                $results_array[] = ["domain" => $domain, "status" => $updvalues["status"], "billingcycle" => $updvalues["billingcycle"], "recurringprices" => round($_POST["customrecurringprice"] * $updvalues["billingcycle"], 2), "currencylabel" => $usercurrency["code"]];
            }
        }
        if (empty($results_array)) {
            $result04 = "<div class=\"alert alert-warning\"><p>" . $LANG["recurringmessage05"] . "</p></div>";
            $control = 0;
        } else {
            $result04 = "<div class=\"alert alert-success\"><p>" . $LANG["recurringmessage00"] . "</p></div>";
            $control = 1;
        }
    }
}
if (isset($_POST["sorttlds"]) && $_POST["sorttlds"] == "true") {
    $sort_whmcs_tlds = [];
    foreach (Illuminate\Database\Capsule\Manager::table("tbldomainpricing")->select("extension")->get() as $data) {
        $sort_whmcs_tlds[] = $data->extension;
    }
    if (empty($sort_whmcs_tlds)) {
        $result01 = "<div class=\"alert alert-danger\"><p>" . $LANG["notldssetup"] . "</p></div>";
    } else if (is_array($sort_whmcs_tlds)) {
        sort($sort_whmcs_tlds);
        $sortnumber = 1;
        foreach ($sort_whmcs_tlds as $tldext) {
            $update = ["order" => $sortnumber++];
            Illuminate\Database\Capsule\Manager::table("tbldomainpricing")->where("extension", "=", $tldext)->update($update);
        }
        $result05 = "<div class=\"alert alert-success\"><p>" . $LANG["tldssortordersuccess"] . "</p></div>";
    }
}
$whmcs_tlds = [];
if (isset($registrar_filter) && !empty($registrar_filter)) {
    foreach (Illuminate\Database\Capsule\Manager::table("tbldomainpricing")->where("autoreg", "=", $_POST["reloadregistrar"])->OrderBy("order", "asc")->select("extension", "dnsmanagement", "emailforwarding", "idprotection", "autoreg")->get() as $data) {
        $whmcs_tlds[$data->extension] = $data->autoreg;
        $whmcs_tlds_addons[$data->extension] = ["dnsmanagement" => $data->dnsmanagement, "emailforwarding" => $data->emailforwarding, "idprotection" => $data->idprotection];
    }
} else {
    foreach (Illuminate\Database\Capsule\Manager::table("tbldomainpricing")->OrderBy("order", "asc")->select("extension", "dnsmanagement", "emailforwarding", "idprotection", "autoreg")->get() as $data) {
        $whmcs_tlds[$data->extension] = $data->autoreg;
        $whmcs_tlds_addons[$data->extension] = ["dnsmanagement" => $data->dnsmanagement, "emailforwarding" => $data->emailforwarding, "idprotection" => $data->idprotection];
    }
}
$tblregistrars = [];
foreach (Illuminate\Database\Capsule\Manager::table("tblregistrars")->select("registrar")->get() as $data) {
    $tblregistrars[] = $data->registrar;
}
$tblregistrars = array_unique($tblregistrars);
sort($tblregistrars);
foreach ($tblregistrars as $registrars) {
    $optionvalues .= "<option value=\"" . $registrars . "\">" . $registrars . "</option>";
}
$whmcs_domains = [];
$whmcs_domains_tools = [];
if (isset($registrar_filter) && !empty($registrar_filter)) {
    foreach (Illuminate\Database\Capsule\Manager::table("tbldomains")->where("registrar", "=", $_POST["reloadregistrar"])->select("domain", "registrar", "dnsmanagement", "emailforwarding", "idprotection", "status")->get() as $data) {
        $whmcs_domains[$data->domain] = $data->registrar;
        if ($data->status == "Active") {
            $whmcs_domains_tools[$data->domain] = ["dnsmanagement" => $data->dnsmanagement, "emailforwarding" => $data->emailforwarding, "idprotection" => $data->idprotection];
        }
    }
} else {
    foreach (Illuminate\Database\Capsule\Manager::table("tbldomains")->select("domain", "registrar", "dnsmanagement", "emailforwarding", "idprotection", "status")->get() as $data) {
        $whmcs_domains[$data->domain] = $data->registrar;
        if ($data->status == "Active") {
            $whmcs_domains_tools[$data->domain] = ["dnsmanagement" => $data->dnsmanagement, "emailforwarding" => $data->emailforwarding, "idprotection" => $data->idprotection];
        }
    }
}
ksort($whmcs_domains);
ksort($whmcs_domains_tools);
echo "<div class=\"alert alert-info\">";
echo "<form method=\"post\" action=\"" . $_SERVER["PHP_SELF"] . "?module=resellerclubmods_tools&domain=tldmanage\">" . rcm_token_field() . "";
echo "<p><strong>" . $LANG["domainlistfilter"] . "</strong>: " . $LANG["domainlistfilterdesc"] . " <select class=\"form-control\" style=\"display: inline; width: auto\" name=\"reloadregistrar\" onchange=\"submit();\">";
echo "<option value=\"\"></option>";
echo "<option value=\"\">" . $LANG["allregmodules"] . "</option>";
echo "<option value=\"none\">none</option>";
foreach ($tblregistrars as $registrars) {
    if ($filtered == $registrars) {
        $selected = "selected=\"selected\"";
    } else {
        $selected = "";
    }
    echo "<option value=\"" . $registrars . "\" " . $selected . ">" . $registrars . "</option>";
}
echo "</select></form></div><br /><br /><div style=\"padding: 5px 20px;border: 1px solid #CCCCCC;-moz-border-radius: 5px;-webkit-border-radius: 5px;-o-border-radius: 5px;border-radius: 5px;\"><a id=\"tool01\"></a>";
echo "<br /><h3 style=\"border-bottom: 1px solid #cccccc;\"><a style=\"text-decoration:none;\" href=\"#top\" class=\"scroll\">(Top)</a> <strong>" . $LANG["tooltitle01"] . "</strong></h3>";
if ($result01) {
    echo $result01;
}
echo "<div><form method=\"post\" action=\"" . $_SERVER["PHP_SELF"] . "?module=resellerclubmods_tools&domain=tldmanage#tool01\">" . rcm_token_field() . "";
echo "<input type=\"hidden\" name=\"changeregmodule\" value=\"true\">";
echo "<input type=\"hidden\" name=\"reloadregistrar\" value=\"" . $registrar_filter . "\">";
echo "<table class=\"table\"><tr><td style=\"vertical-align:top;border-top: none;width:50%;\">";
echo "<p><strong>" . $LANG["bulkallusing"] . "</strong> (" . $filtered . ")<br />" . $LANG["domaintitle"] . " &raquo; " . $LANG["registrarmoduletitle"] . "</p>";
echo "<select class=\"form-control\" style=\"display: inline;\" name=\"domainlist[]\" size=\"10\" multiple=\"multiple\">";
if (empty($whmcs_domains)) {
    echo "<option value=\"\">" . $LANG["rcuserdomnotfound"] . "</option>";
} else {
    foreach ($whmcs_domains as $domain => $registrar) {
        if (empty($registrar)) {
            $registrar = "none";
        }
        echo "<option value=\"" . $domain . "\">" . $domain . " &raquo; " . $registrar . "</option>";
    }
}
echo "</select></td>";
echo "<td style=\"vertical-align:middle;border-top: none;\"><p><strong>" . $LANG["activeregmodulestitle"] . "</strong><br /><select class=\"form-control\" style=\"display: inline; width: auto\" name=\"registrar\"></p>";
echo "<option value=\"\">" . $LANG["activeregmoduledropdown"] . "</option>";
echo $optionvalues;
echo "</select>";
echo "<p><input type=\"submit\" value=\"" . $LANG["regmodulechangebutton"] . "\" class=\"btn btn-success\" /></p></td>";
echo "</tr></table></form></div></div><br /><br /><div style=\"padding: 5px 20px;border: 1px solid #CCCCCC;-moz-border-radius: 5px;-webkit-border-radius: 5px;-o-border-radius: 5px;border-radius: 5px;\"><a id=\"tool02\"></a>";
echo "<br /><h3 style=\"border-bottom: 1px solid #cccccc;\"><a style=\"text-decoration:none;\" href=\"#top\" class=\"scroll\">(Top)</a> <strong>" . $LANG["tooltitle02"] . "</strong></h3>";
if ($result02) {
    echo $result02;
}
echo "<div><form method=\"post\" action=\"" . $_SERVER["PHP_SELF"] . "?module=resellerclubmods_tools&domain=tldmanage#tool02\">" . rcm_token_field() . "";
echo "<input type=\"hidden\" name=\"changeautoregmodule\" value=\"true\">";
echo "<input type=\"hidden\" name=\"reloadregistrar\" value=\"" . $registrar_filter . "\">";
echo "<table class=\"table\"><tr><td style=\"vertical-align:top;border-top: none;width:50%;\">";
echo "<p><strong>" . $LANG["bulkalltldusing"] . "</strong> (" . $filteredtld . ")<br />" . $LANG["tldtitle"] . " &raquo; " . $LANG["registrarmoduletitle"] . "</p>";
echo "<select class=\"form-control\" style=\"display: inline\"; name=\"tldlist[]\" size=\"10\" multiple=\"multiple\">";
if (empty($whmcs_tlds)) {
    echo "<option value=\"\">" . $LANG["tldnotfound"] . "</option>";
} else {
    foreach ($whmcs_tlds as $extension => $autoreg) {
        if (empty($autoreg)) {
            $autoreg = "none";
        }
        echo "<option value=\"" . $extension . "\">" . $extension . " &raquo; " . $autoreg . "</option>";
    }
}
echo "</select></td>";
echo "<td style=\"vertical-align:middle;border-top: none;\"><p><strong>" . $LANG["activeregmodulestitle"] . "</strong><br /><select class=\"form-control\" style=\"display: inline; width: auto\" name=\"registrar\"></p>";
echo "<option value=\"\">" . $LANG["activeregmoduledropdown"] . "</option>";
echo $optionvalues;
echo "</select>";
echo "<p><input type=\"submit\" value=\"" . $LANG["regmodulechangebutton"] . "\" class=\"btn btn-success\" /></p></td>";
echo "</tr></table></form></div></div><br /><br /><div style=\"padding: 5px 20px;border: 1px solid #CCCCCC;-moz-border-radius: 5px;-webkit-border-radius: 5px;-o-border-radius: 5px;border-radius: 5px;\"><a id=\"tool03\"></a>";
echo "<br /><h3 style=\"border-bottom: 1px solid #cccccc;\"><a style=\"text-decoration:none;\" href=\"#top\" class=\"scroll\">(Top)</a> <strong>" . $LANG["tooltitle03"] . "</strong></h3>";
echo "<div class=\"alert alert-warning\"><p>" . $LANG["note"] . " " . $LANG["domainmanagetoolsnote"] . "</p></div>";
if ($result03) {
    echo $result03;
}
echo "<div><form method=\"post\" action=\"" . $_SERVER["PHP_SELF"] . "?module=resellerclubmods_tools&domain=tldmanage#tool03\">" . rcm_token_field() . "";
echo "<input type=\"hidden\" name=\"changemanagetools\" value=\"true\">";
echo "<input type=\"hidden\" name=\"reloadregistrar\" value=\"" . $registrar_filter . "\">";
echo "<table class=\"table\"><tr><td style=\"vertical-align:top;border-top: none;width:50%;\">";
echo "<p><strong>" . $LANG["bulkallusing"] . "</strong> (" . $filtered . ")<br />" . $LANG["domaintitle"] . " &raquo; dnsmanagement = on/off, emailforwarding = on/off, idprotection = on/off</p>";
echo "<div style=\"width:auto;overflow-x:auto;overflow-y:auto;padding-right:20px;\"><select class=\"form-control\" style=\"display: inline; \" name=\"changetldsaddonslist[]\" size=\"15\" multiple=\"multiple\">";
if (empty($whmcs_domains_tools)) {
    echo "<option value=\"\">" . $LANG["rcuserdomnotfound"] . "</option>";
} else {
    foreach ($whmcs_domains_tools as $keydom => $valueoptions) {
        echo "<option value=\"" . $keydom . "\">" . $keydom . " &raquo; ";
        foreach ($valueoptions as $keyopt => $valueopt) {
            if (empty($valueopt)) {
                $valueopt = "off";
            }
            if ($valueopt == "1") {
                $valueopt = "on";
            }
            echo $keyopt . " = " . $valueopt . ", ";
        }
        echo "</option>";
    }
}
echo "</select></div></td><td style=\"vertical-align:top;border-top: none;\"><table class=\"table\">";
echo "<tr><td colspan=\"4\" style=\"border-top: none;\"><p><strong>" . $LANG["selecttoolsoptions"] . "</strong></p></td></tr>";
echo "<tr><td><strong>dnsmanagement</strong>:</td><td><ul><li>" . $LANG["toolactivate"] . " <input name=\"dnsmanagement\" type=\"radio\" value=\"on\" /></li><li>" . $LANG["tooldeactivate"] . " <input name=\"dnsmanagement\" type=\"radio\" value=\"\" /></li><li>" . $LANG["leaveasis"] . " <input name=\"dnsmanagement\" type=\"radio\" value=\"x\" checked /></li></ul></td></tr>";
echo "<tr><td><strong>emailforwarding</strong>:</td><td><ul><li>" . $LANG["toolactivate"] . " <input name=\"emailforwarding\" type=\"radio\" value=\"on\" /></li><li>" . $LANG["tooldeactivate"] . " <input name=\"emailforwarding\" type=\"radio\" value=\"\" /></li><li>" . $LANG["leaveasis"] . " <input name=\"emailforwarding\" type=\"radio\" value=\"x\" checked /></li></ul></td></tr>";
echo "<tr><td><strong>idprotection</strong>:</td><td><ul><li>" . $LANG["toolactivate"] . " <input name=\"idprotection\" type=\"radio\" value=\"on\" /></li><li>" . $LANG["tooldeactivate"] . " <input name=\"idprotection\" type=\"radio\" value=\"\" /></li><li>" . $LANG["leaveasis"] . " <input name=\"idprotection\" type=\"radio\" value=\"x\" checked /></li></ul></td></tr>";
echo "<tr><td colspan=\"4\"><p><strong>" . $LANG["updaterecurringtitle"] . "</strong> <input name=\"dorecalc\" type=\"checkbox\" value=\"true\" /></p></td></tr>";
echo "<tr><td colspan=\"4\"><p><strong style=\"color:#cc0000;\">Ignore Domain list and update ALL Domains</strong> <input name=\"doupdalldomains\" type=\"checkbox\" value=\"true\" /></p></td></tr></table>";
echo "<input type=\"submit\" value=\"" . $LANG["changetoolsettingsbutton"] . "\" class=\"btn btn-success\" /></td>";
echo "</tr></table></form></div></div><br /><br /><div style=\"padding: 5px 20px;border: 1px solid #CCCCCC;-moz-border-radius: 5px;-webkit-border-radius: 5px;-o-border-radius: 5px;border-radius: 5px;\"><a id=\"tool06\"></a>";
echo "<br /><h3 style=\"border-bottom: 1px solid #cccccc;\"><a style=\"text-decoration:none;\" href=\"#top\" class=\"scroll\">(Top)</a> <strong>" . $LANG["tooltitle06"] . "</strong></h3>";
if ($result06) {
    echo $result06;
}
echo "<div><form method=\"post\" action=\"" . $_SERVER["PHP_SELF"] . "?module=resellerclubmods_tools&domain=tldmanage#tool06\">" . rcm_token_field() . "";
echo "<input type=\"hidden\" name=\"changetldsaddons\" value=\"true\">";
echo "<input type=\"hidden\" name=\"reloadregistrar\" value=\"" . $registrar_filter . "\">";
echo "<table class=\"table\"><tr><td style=\"vertical-align:top;border-top: none;width:50%;\">";
echo "<p><strong>" . $LANG["bulkalltldsusing"] . "</strong> (" . $filtered . ")<br />" . $LANG["tldtitle"] . " &raquo; dnsmanagement = on/off, emailforwarding = on/off, idprotection = on/off</p>";
echo "<div style=\"width:auto;overflow-x:auto;overflow-y:auto;padding-right:20px;\"><select class=\"form-control\" style=\"display: inline; \" name=\"changetldsaddonslist[]\" size=\"15\" multiple=\"multiple\">";
if (empty($whmcs_tlds_addons)) {
    echo "<option value=\"\">No TLDs found in WHMCS</option>";
} else {
    foreach ($whmcs_tlds_addons as $keydom => $valueoptions) {
        echo "<option value=\"" . $keydom . "\">" . $keydom . " &raquo; ";
        foreach ($valueoptions as $keyopt => $valueopt) {
            if (empty($valueopt)) {
                $valueopt = "off";
            }
            if ($valueopt == "1") {
                $valueopt = "on";
            }
            echo $keyopt . " = " . $valueopt . ", ";
        }
        echo "</option>";
    }
}
echo "</select></div></td><td style=\"vertical-align:top;border-top: none;\"><table class=\"table\">";
echo "<tr><td colspan=\"4\" style=\"border-top: none;\"><p><strong>" . $LANG["selecttoolsoptions"] . "</strong></p></td></tr>";
echo "<tr><td><strong>dnsmanagement</strong>:</td><td><ul><li>" . $LANG["toolactivate"] . " <input name=\"dnsmanagement\" type=\"radio\" value=\"on\" /></li><li>" . $LANG["tooldeactivate"] . " <input name=\"dnsmanagement\" type=\"radio\" value=\"\" /></li><li>" . $LANG["leaveasis"] . " <input name=\"dnsmanagement\" type=\"radio\" value=\"x\" checked /></li></ul></td></tr>";
echo "<tr><td><strong>emailforwarding</strong>:</td><td><ul><li>" . $LANG["toolactivate"] . " <input name=\"emailforwarding\" type=\"radio\" value=\"on\" /></li><li>" . $LANG["tooldeactivate"] . " <input name=\"emailforwarding\" type=\"radio\" value=\"\" /></li><li>" . $LANG["leaveasis"] . " <input name=\"emailforwarding\" type=\"radio\" value=\"x\" checked /></li></ul></td></tr>";
echo "<tr><td><strong>idprotection</strong>:</td><td><ul><li>" . $LANG["toolactivate"] . " <input name=\"idprotection\" type=\"radio\" value=\"on\" /></li><li>" . $LANG["tooldeactivate"] . " <input name=\"idprotection\" type=\"radio\" value=\"\" /></li><li>" . $LANG["leaveasis"] . " <input name=\"idprotection\" type=\"radio\" value=\"x\" checked /></li></ul></td></tr>";
echo "</table>";
echo "<input type=\"submit\" value=\"" . $LANG["changetldaddonssettingsbutton"] . "\" class=\"btn btn-success\" /></td>";
echo "</tr></table></form></div></div><br /><br /><div style=\"padding: 5px 20px;border: 1px solid #CCCCCC;-moz-border-radius: 5px;-webkit-border-radius: 5px;-o-border-radius: 5px;border-radius: 5px;\"><a id=\"tool04\"></a>";
echo "<br /><h3 style=\"border-bottom: 1px solid #cccccc;\"><a style=\"text-decoration:none;\" href=\"#top\" class=\"scroll\">(Top)</a> <strong>" . $LANG["tooltitle04"] . "</strong></h3>";
if ($result04) {
    echo $result04;
    if ($_POST["output"] == "true" && $control == 1) {
        echo "<table class=\"table\">";
        echo "<tr><th>" . $LANG["domainsword"] . "</th><th>" . $LANG["statusword"] . "</th><th>" . $LANG["billingcycle"] . "</th><th>" . $LANG["newrecurrprice"] . "</th></tr>";
        foreach ($results_array as $resvals) {
            echo "<tr><td>" . $resvals["domain"] . "</td><td>" . $resvals["status"] . "</td><td>" . $resvals["billingcycle"] . "</td><td>" . $resvals["currencylabel"] . " " . $resvals["recurringprices"] . "</td></tr>";
        }
        echo "</table><br /><hr />";
    }
}
if (!empty($_POST["customrecurringprice"])) {
    $postrecurring = $_POST["customrecurringprice"];
}
if (!empty($_POST["excluderecurring"])) {
    $excluderecurring = $_POST["excluderecurring"];
}
echo "<div><form method=\"post\" action=\"" . $_SERVER["PHP_SELF"] . "?module=resellerclubmods_tools&domain=tldmanage#tool04\">" . rcm_token_field() . "";
echo "<input type=\"hidden\" name=\"priceupdatetool\" value=\"true\">";
echo "<p><strong>" . $LANG["newrecurrprice"] . ":</strong>&nbsp;";
echo "<input class=\"form-control input-100\" style=\"display: inline;\" name=\"customrecurringprice\" size=\"10\" value=\"" . $postrecurring . "\" type=\"text\">&nbsp;&nbsp;";
echo "<strong>" . $LANG["currencyword"] . ":</strong>&nbsp;<select class=\"form-control input-100\" style=\"display: inline;\" name=\"currency\">";
foreach ($currencie_array as $currency_values) {
    if ($_POST["currency"] == $currency_values["id"]) {
        $selected = "selected=\"\"";
    } else {
        $selected = "";
    }
    echo "<option " . $selected . " value=\"" . $currency_values["id"] . "\">" . $currency_values["code"] . "</option>";
}
echo "</select></p>";
echo "<p><strong>" . $LANG["excludedomrecurring"] . "</strong>:&nbsp; <input class=\"form-control input-100\" style=\"display: inline;\" name=\"excluderecurring\" size=\"10\" value=\"" . $excluderecurring . "\" type=\"text\"></p>";
echo "<table class=\"table\"><tr><td style=\"vertical-align:top;border-top: none; width:30%\">";
echo "<p><strong>" . $LANG["bulkalltldusing"] . "</strong></p>";
echo "<select class=\"form-control\" style=\"display: inline;\" name=\"tldlist[]\" size=\"10\" multiple=\"multiple\">";
foreach ($whmcs_tlds as $extension => $autoreg) {
    if (is_array($_POST["tldlist"]) && in_array($extension, $_POST["tldlist"])) {
        $selected = "selected=\"\"";
    } else {
        $selected = "";
    }
    echo "<option " . $selected . " value=\"" . $extension . "\">Domain - " . $extension . "</option>";
}
echo "</select></td><td style=\"vertical-align:top;border-top: none; width:30%\">";
echo "<p><strong>" . $LANG["statusword"] . ":</strong></p>";
echo "<select class=\"form-control\" style=\"display: inline;\" name=\"status[]\" size=\"10\" multiple=\"true\">";
foreach ($status_array as $key => $statuslabels) {
    if (is_array($_POST["status"]) && in_array($key, $_POST["status"])) {
        $selected = "selected=\"\"";
    } else {
        $selected = "";
    }
    echo "<option " . $selected . " value=\"" . $key . "\">" . $statuslabels . "</option>";
}
echo "</select></td><td style=\"vertical-align:top;border-top: none; width:30%\">";
echo "<p><strong>" . $LANG["billingcycle"] . ":</strong></p>";
echo "<select class=\"form-control\" style=\"display: inline;\" name=\"billingcycle[]\" size=\"10\" multiple=\"true\">";
foreach ($billingcycles_array as $key => $billinglabels) {
    if (is_array($_POST["billingcycle"]) && in_array($key, $_POST["billingcycle"])) {
        $selected = "selected=\"\"";
    } else {
        $selected = "";
    }
    echo "<option " . $selected . " value=\"" . $key . "\">" . $billinglabels . "</option>";
}
echo "</select></td></tr></table>";
echo "<p><strong>" . $LANG["note"] . "</strong> " . $LANG["recurringpriceupdatenotes"] . "</p>";
echo "<p style=\"vertical-align:middle;\">" . $LANG["showupdresults"] . " <input type=\"checkbox\" name=\"output\" value=\"true\" checked=\"\"/></p>";
echo "<p><input type=\"submit\" value=\"" . $LANG["recurringbutton"] . "\" class=\"btn btn-success\" /></p>";
echo "</form></div></div><br /><br /><div style=\"padding: 5px 20px;border: 1px solid #CCCCCC;-moz-border-radius: 5px;-webkit-border-radius: 5px;-o-border-radius: 5px;border-radius: 5px;\"><a id=\"tool05\"></a>";
echo "<br /><h3 style=\"border-bottom: 1px solid #cccccc;\"><a style=\"text-decoration:none;\" href=\"#top\" class=\"scroll\">(Top)</a> <strong>" . $LANG["tooltitle05"] . "</strong></h3>";
echo "<div class=\"alert alert-info\"><p>" . $LANG["tooltitle05desc1"] . "</p><p>" . $LANG["tooltitle05desc2"] . "</p></div>";
if ($result05) {
    echo $result05;
}
echo "<div><form method=\"post\" action=\"" . $_SERVER["PHP_SELF"] . "?module=resellerclubmods_tools&domain=tldmanage#tool05\">" . rcm_token_field() . "";
echo "<input type=\"hidden\" name=\"sorttlds\" value=\"true\">";
echo "<p><input type=\"submit\" value=\"" . $LANG["sortorderbutton"] . "\" class=\"btn btn-success\" /></p></td>";
echo "</form></div></div><br /><br />";

?>