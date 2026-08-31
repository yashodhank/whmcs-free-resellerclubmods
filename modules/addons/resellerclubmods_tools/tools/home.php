<?php
if (!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
global $releasedate;
if ($resellerdetails_arrXml["status"] == "ERROR" || empty($resellerdetails_arrXml)) {
    $ipcheck = isset($_SERVER["SERVER_ADDR"]) ? $_SERVER["SERVER_ADDR"] : "";
}
$rc_conn_result = "<span style=\"color:#4B8207\">" . $LANG["apiconnok"] . "</span>";
if (!empty($rcauth_userid) && !empty($rcauth_password)) {
    if ($resellerdetails_arrXml["status"] == "ERROR") {
        $rc_conn_result = "<span style=\"color:#CC0000\">" . $LANG["apiconnfailed"] . " " . $LANG["idorpasswrong"] . "</span>" . "<br />" . $LANG["checkip1"] . " <strong>" . $_SERVER["SERVER_ADDR"] . " " . $_SERVER["LOCAL_ADDR"] . " & " . $ipcheck . "</strong> " . $LANG["checkip2"];
        $showdebug = 1;
    } else if (empty($resellerdetails_arrXml)) {
        $rc_conn_result = "<span style=\"color:#CC0000\">" . $LANG["apiconnfailed"] . "</span><br />" . $LANG["checkapiurl"] . "<br />" . $LANG["checkip1"] . " <strong>" . $_SERVER["SERVER_ADDR"] . " " . $_SERVER["LOCAL_ADDR"] . " & " . $ipcheck . "</strong> " . $LANG["checkip2"];
    } else if ($currencycode != $lb_sellingcurrency && $currencyswitch == "on") {
        $conversiondesc = "<p><span " . $style_labelok . "> " . $LANG["conversionnote0"] . "</span><br /><br />" . $LANG["conversionnote1"] . " " . $lb_sellingcurrency . " " . $LANG["conversionnote3"] . " " . $currencycode . " " . $LANG["conversionnote2"] . " " . $currencyrate . "</p>";
        if (isset($_SESSION["rcm_wrong_currencysetup"])) {
            unset($_SESSION["rcm_wrong_currencysetup"]);
            header("Location: " . $freetools_home_redir);
            exit;
        }
    } else if ($currencycode != $lb_sellingcurrency && $currencyswitch != "on") {
        $conversiondesc = "<div class=\"alert alert-danger\"><p>" . $LANG["currencywarning1"] . "</p></div>";
        if (!isset($_SESSION["rcm_wrong_currencysetup"])) {
            $_SESSION["rcm_wrong_currencysetup"] = 1;
            header("Location: " . $freetools_home_redir);
            exit;
        }
    } else if ($currencycode == $lb_sellingcurrency && $currencyswitch == "on") {
        $conversiondesc = "<div class=\"alert alert-danger\"><p>" . $LANG["currencywarning2"] . "</p></div>";
        $wrong_currency_setup = 1;
        if (!isset($_SESSION["rcm_wrong_currencysetup"])) {
            $_SESSION["rcm_wrong_currencysetup"] = 1;
            header("Location: " . $freetools_home_redir);
            exit;
        }
    } else {
        $rc_conn_result = "<span style=\"color:#4B8207\">" . $LANG["apiconnok"] . "</span>";
        if (isset($_SESSION["rcm_wrong_currencysetup"])) {
            unset($_SESSION["rcm_wrong_currencysetup"]);
            header("Location: " . $freetools_home_redir);
            exit;
        }
    }
    $rc_apitest = "<tr><td>" . $LANG["lbtitle"] . "&nbsp; </td><td><strong>" . $logicbox_registrar . "</strong></td></tr><tr><td>" . $LANG["apiresult"] . "&nbsp;</td><td>" . $rc_conn_result . "</td></tr>";
} else {
    $rc_apitest = "<tr><td>" . $LANG["lbtitle"] . "&nbsp; </td><td><strong>" . $logicbox_registrar . "</strong></td></tr><tr><td>" . $LANG["apiresult"] . "&nbsp;</td><td><span style=\"color:#A3A1A1;font-weight:bold;\">" . $LANG["noaccsetup"] . "</span></td></tr>";
}
if ($is_transfercheck == "on") {
    $is_transfercheck_on = "<span style=\"cursor:help;\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["accountsetuplabel"] . "\" " . $style_labelwarn . ">" . $LANG["disabled"] . "</span>";
} else {
    $is_transfercheck_on = "<span style=\"cursor:help;\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["accountsetuplabel"] . "\" " . $style_labelok . ">" . $LANG["activated"] . "</span>";
}
if ($is_hooksignup == "on") {
    $is_hooksignup_on = "<span style=\"cursor:help;\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["accountsetuplabel"] . "\" " . $style_labelwarn . ">" . $LANG["disabled"] . "</span>";
} else {
    $is_hooksignup_on = "<span style=\"cursor:help;\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["accountsetuplabel"] . "\" " . $style_labelok . ">" . $LANG["activated"] . "</span>";
}
if ($is_pwdsignup != "on") {
    $is_pwdsignup_on = "<span style=\"cursor:help;\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["accountsetuplabel"] . "\" " . $style_labelwarn . ">" . $LANG["disabled"] . "</span>";
} else {
    $is_pwdsignup_on = "<span style=\"cursor:help;\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["globalsetuplabel"] . "\" " . $style_labelok . ">" . $LANG["activated"] . "</span>";
}
if ($is_hookmodify == "on") {
    $is_hookmodify_on = "<span style=\"cursor:help;\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["accountsetuplabel"] . "\" " . $style_labelwarn . ">" . $LANG["disabled"] . "</span>";
} else {
    $is_hookmodify_on = "<span style=\"cursor:help;\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["accountsetuplabel"] . "\" " . $style_labelok . ">" . $LANG["activated"] . "</span>";
}
if ($is_pwdmodify != "on") {
    $is_pwdmodify_on = "<span style=\"cursor:help;\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["accountsetuplabel"] . "\" " . $style_labelwarn . ">" . $LANG["disabled"] . "</span>";
} else {
    $is_pwdmodify_on = "<span style=\"cursor:help;\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["globalsetuplabel"] . "\" " . $style_labelok . ">" . $LANG["activated"] . "</span>";
}
if ($is_hookdelete == "on") {
    $is_hookdelete_on = "<span style=\"cursor:help;\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["accountsetuplabel"] . "\" " . $style_labelwarn . ">" . $LANG["disabled"] . "</span>";
} else {
    $is_hookdelete_on = "<span style=\"cursor:help;\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["accountsetuplabel"] . "\" " . $style_labelok . ">" . $LANG["activated"] . "</span>";
}
if ($is_threshold == "on") {
    $is_threshold_on = "<span style=\"cursor:help;\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["accountsetuplabel"] . "\" " . $style_labelwarn . ">" . $LANG["disabled"] . "</span>";
} else {
    $is_threshold_on = "<span style=\"cursor:help;\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["accountsetuplabel"] . "\" " . $style_labelok . ">" . $LANG["activated"] . "</span>";
}
if ($is_promocheck != "on") {
    $is_promocheck_on = "<span style=\"cursor:help;\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["globalsetuplabel"] . "\" " . $style_labelwarn . ">" . $LANG["disabled"] . "</span>";
} else {
    $is_promocheck_on = "<span style=\"cursor:help;\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["globalsetuplabel"] . "\" " . $style_labelok . ">" . $LANG["activated"] . "</span>";
}
if ($is_promoactivate != "on") {
    $is_promoactivate_on = "<span style=\"cursor:help;\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["globalsetuplabel"] . "\" " . $style_labelwarn . ">" . $LANG["disabled"] . "</span>";
} else {
    $is_promoactivate_on = "<span style=\"cursor:help;\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["globalsetuplabel"] . "\" " . $style_labelok . ">" . $LANG["activated"] . "</span>";
}
if ($is_raacheck == "on") {
    $is_raacheck_on = "<span style=\"cursor:help;\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["globalsetuplabel"] . "\" " . $style_labelwarn . ">" . $LANG["disabled"] . "</span>";
} else {
    $is_raacheck_on = "<span style=\"cursor:help;\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["globalsetuplabel"] . "\" " . $style_labelok . ">" . $LANG["activated"] . "</span>";
}
if ($is_domainsync == "on") {
    $is_domainsync_on = "<span style=\"cursor:help;\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["accountsetuplabel"] . "\" " . $style_labelwarn . ">" . $LANG["disabled"] . "</span>";
} else {
    $is_domainsync_on = "<span style=\"cursor:help;\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["accountsetuplabel"] . "\" " . $style_labelok . ">" . $LANG["activated"] . "</span>";
}
if (!empty($is_domainsynctlds)) {
    $is_domainsynctlds_tlds = "<span style=\"cursor:help;\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["accountsetuplabel"] . "\">" . $LANG["onlytldlabel"] . "&nbsp;</span><span style=\"color:#46A546;\">" . str_replace(",", " ", $is_domainsynctlds) . "</span>";
} else if (!empty($is_domainsyncexcludetlds)) {
    $is_domainsynctlds_tlds = "<span style=\"cursor:help;\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["accountsetuplabel"] . "\">" . $LANG["alltldlabel"] . "&nbsp;" . $LANG["excludetldlabel"] . "</span><span style=\"color:#cc0000;\">&nbsp;" . str_replace(",", " ", $is_domainsyncexcludetlds) . "</span>";
} else {
    $is_domainsynctlds_tlds = "<span style=\"cursor:help;\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["accountsetuplabel"] . "\">" . $LANG["alltldlabel"] . "</span>";
}
if ($is_onlybaseslab != "on") {
    $is_onlybaseslab_on = "<span style=\"cursor:help;\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["accountsetuplabel"] . "\">" . $LANG["synconlydefault"] . "</span>";
} else {
    $is_onlybaseslab_on = "<span style=\"cursor:help;\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["accountsetuplabel"] . "\">" . $LANG["syncallslabs"] . "</span>";
}
if ($is_domainlookup != "on") {
    $is_domainlookup_on = "<span style=\"cursor:help;\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["globalsetuplabel"] . "\" " . $style_labelwarn . ">" . $LANG["disabled"] . "</span>";
} else {
    $is_domainlookup_on = "<span style=\"cursor:help;\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["globalsetuplabel"] . "\" " . $style_labelok . ">" . $LANG["activated"] . "</span>";
}
if ($is_recurringdomupd != "on") {
    $is_recurringdomupd_on = "<span style=\"cursor:help;\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["globalsetuplabel"] . "\" " . $style_labelwarn . ">" . $LANG["disabled"] . "</span>";
} else {
    $is_recurringdomupd_on = "<span style=\"cursor:help;\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["globalsetuplabel"] . "\" " . $style_labelok . ">" . $LANG["activated"] . "</span>";
}
if ($is_currencydoupd != "on") {
    $is_currencydoupd_on = "<span style=\"cursor:help;\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["globalsetuplabel"] . "\" " . $style_labelok . ">" . $LANG["activated"] . "</span>";
} else {
    $is_currencydoupd_on = "<span style=\"cursor:help;\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["globalsetuplabel"] . "\" " . $style_labelwarn . ">" . $LANG["disabled"] . "</span>";
}
if ($is_redemption != "on") {
    $is_redemption_on = "<span style=\"cursor:help;\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["accountsetuplabel"] . "\" " . $style_labelwarn . ">" . $LANG["disabled"] . "</span>";
} else {
    $is_redemption_on = "<span style=\"cursor:help;\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["accountsetuplabel"] . "\" " . $style_labelok . ">" . $LANG["activated"] . "</span>";
}
if ($is_domaintelescope == "on") {
    $is_domaintelescope_on = "<span style=\"cursor:help;\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["accountsetuplabel"] . "\" " . $style_labelwarn . ">" . $LANG["disabled"] . "</span><br /><br />";
} else {
    $is_domaintelescope_on = "<span style=\"cursor:help;\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["accountsetuplabel"] . "\" " . $style_labelok . ">" . $LANG["activated"] . "</span><br /><br />";
}
$whmcs_defaultns = [$CONFIG["DefaultNameserver1"], $CONFIG["DefaultNameserver2"], $CONFIG["DefaultNameserver3"], $CONFIG["DefaultNameserver4"], $CONFIG["DefaultNameserver5"]];
$whmcs_defaultns = array_filter($whmcs_defaultns);
if (empty($is_defaultns)) {
    $whmcs_defaultns = array_filter($whmcs_defaultns);
    $nameserverconfig = implode(", ", $whmcs_defaultns);
    $ns_label = "<span>" . $LANG["defaultnssettings"] . ":</span>&nbsp;";
    $is_defaultns_config = "<div style=\"font-weight:bold;\">" . $nameserverconfig . "</div>";
    $is_nsoverride_on = "<span style=\"cursor:help;\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["accountsetuplabel"] . "\" " . $style_labelwarn . ">" . $LANG["disabled"] . "</span>";
} else {
    $nameserverconfig = str_replace(",", ", ", $is_defaultns);
    $ns_label = "<span>" . $LANG["registrarnssettings"] . "</span>&nbsp;";
    $is_defaultns_config = "<div style=\"font-weight:bold;\">" . $nameserverconfig . "</div>";
    $is_nsoverride_on = "<span style=\"cursor:help;\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["accountsetuplabel"] . "\" " . $style_labelok . ">" . $LANG["activated"] . "</span>";
}
echo "\r\n\t <table width=\"100%\">\r\n\t <tr>\r\n\t <td>\r\n\t <div style=\"padding: 5px 20px;border: 1px solid #CCCCCC;-moz-border-radius: 5px;-webkit-border-radius: 5px;-o-border-radius: 5px;border-radius: 5px;\">\r\n\t <h1>" . $LANG["currentaccount"] . "</h1>";
echo "\r\n\t <table class=\"table table-sm table-hover\">\r\n\t <tr><td width=\"25%\">" . $LANG["accountname"] . " </td><td><strong>" . $account_name . "</strong></td></tr>\r\n\t <tr><td>" . $LANG["resellerid"] . " </td><td><strong>" . $rcauth_userid . "</strong></td></tr>\r\n\t " . $rc_apitest . "\r\n\t <tr><td>" . $LANG["changeto"] . "&nbsp;</td><td>" . $tools_change_acc_dropdown . "</td></tr>\r\n\t </table>";
echo "</div>";
echo "\r\n\t <br /><div style=\"padding: 5px 20px;border: 1px solid #CCCCCC;-moz-border-radius: 5px;-webkit-border-radius: 5px;-o-border-radius: 5px;border-radius: 5px;\">\r\n\t <h1>" . $LANG["currencyaccsetup"] . "</h1>\r\n\t <table class=\"table table-sm table-hover\">\r\n\t <tr><td width=\"25%\">" . $LANG["defaultsellingwhmcs"] . "&nbsp; </td><td><strong>" . $currencycode . "</strong></td></tr>\r\n\t <tr><td>" . $LANG["defaultsellinglb"] . "&nbsp; </td><td><strong>" . $lb_sellingcurrency . "</strong></td></tr>\r\n\t <tr><td>" . $LANG["defaultbuylb"] . "&nbsp; </td><td><strong>" . $reseller_buycurrency . "</strong></td></tr>\r\n\t </table>";
if (isset($conversiondesc)) {
    echo $conversiondesc;
}
echo "</div>";
echo "<br /><div style=\"padding: 5px 20px;border: 1px solid #CCCCCC;-moz-border-radius: 5px;-webkit-border-radius: 5px;-o-border-radius: 5px;border-radius: 5px;\">\r\n\t <h1>" . $LANG["reselleraccsetup"] . " <small>(" . $account_name . " - " . $logicbox_registrar . ")</small></h1>\r\n\t <div>\r\n\t <h2>" . $LANG["accountsettingsstitle"] . "</h2>\r\n\t <table class=\"table table-sm table-hover\">\r\n\t <tr>\r\n\t <td align=\"right\">" . $LANG["autocustsignup"] . " " . $is_hooksignup_on . "</td>\r\n\t <td align=\"right\">" . $LANG["autocustmodify"] . " " . $is_hookmodify_on . "</td>\r\n\t <td align=\"right\">" . $LANG["autocustdelete"] . " " . $is_hookdelete_on . "</td>\r\n\t </tr>\r\n\t <tr>\r\n\t <td align=\"right\">" . $LANG["uswpwdfromwhmcs"] . " " . $is_pwdsignup_on . "</td>\r\n\t <td align=\"right\">" . $LANG["applypwdfromwhmcs"] . " " . $is_pwdmodify_on . "</td>\r\n\t <td align=\"right\"> </td>\r\n\t </tr>\r\n\t <tr>\r\n\t <td align=\"right\">" . $LANG["transferchecktitle"] . " " . $is_transfercheck_on . "</td>\r\n\t <td align=\"right\">" . $LANG["fundsthresholdcheck"] . " " . $is_threshold_on . "</td>\r\n\t <td align=\"right\"> </td>\r\n\t </tr>\r\n\t </table>\r\n\t </div>\r\n\t <br />\r\n\t <div>\r\n\t <h2>" . $LANG["globalsettingsstitle"] . "</h2>\r\n\t <table class=\"table table-sm table-hover\">\r\n\t <tr>\r\n\t <td align=\"right\">" . $LANG["autopromoactivate"] . " " . $is_promoactivate_on . "</td>\r\n\t <td align=\"right\">" . $LANG["autopromoupd"] . " " . $is_promocheck_on . "</td>\r\n\t <td align=\"right\">" . $LANG["raareporttitle"] . " " . $is_raacheck_on . "</td>\r\n\t </tr>\r\n\t <tr>\r\n\t <td align=\"right\">" . $LANG["useregistrarlookup"] . " " . $is_domainlookup_on . "</td>\r\n\t <td align=\"right\">" . $LANG["updatedomainrecurring"] . " " . $is_recurringdomupd_on . "</td>\r\n\t <td align=\"right\">" . $LANG["currencyupdatesellingprices"] . " " . $is_currencydoupd_on . "</td>\r\n\t </tr>\r\n\t </table>\r\n\t </div>\r\n\t <br />\r\n\t <div>\r\n\t <h2>" . $LANG["nameserversettingstitle"] . "</h2>\r\n\t <table class=\"table table-sm table-hover\">\r\n\t <tr>\r\n\t <td align=\"left\" valign=\"top\" colspan=\"2\">" . $LANG["nsoverridetitle"] . " " . $is_nsoverride_on . "</td>\r\n\t </tr>\t \r\n\t <tr>\r\n\t <td align=\"left\" width=\"25%\" valign=\"top\">" . $ns_label . "</td>\r\n\t <td align=\"left\" valign=\"top\">" . $is_defaultns_config . "</td>\r\n\t </tr>\r\n\t </table>\r\n\t </div>\r\n\t <br />\r\n\t <div>\r\n\t <h2>" . $LANG["domsyncsettingsstitle"] . "</h2>\r\n\t <table class=\"table table-sm table-hover\">\r\n\t <tr>\r\n\t <td align=\"left\" width=\"25%\" valign=\"top\">" . $LANG["domainpricesynctitle2"] . ":&nbsp;</td>\r\n\t <td align=\"left\" valign=\"top\">" . $is_domainsync_on . "</td>\r\n\t </tr>\r\n\t <tr>\r\n\t <td align=\"left\" valign=\"top\">" . $LANG["homeredemptiontitle"] . ":&nbsp;</td>\r\n\t <td align=\"left\" valign=\"top\">" . $is_redemption_on . "</td>\r\n\t </tr>\r\n\t <tr>\r\n\t <td align=\"left\" valign=\"top\">" . $LANG["telescopepricingtitle"] . ":&nbsp;</td>\r\n\t <td align=\"left\" valign=\"top\">" . $is_domaintelescope_on . "</td>\r\n\t </tr>\r\n\t <tr>\r\n\t <td align=\"left\" valign=\"top\">" . $LANG["tldpriceslabslabel"] . ":&nbsp;</td>\r\n\t <td align=\"left\" valign=\"top\"><strong>" . $is_onlybaseslab_on . "</strong></td>\r\n\t </tr>\r\n\t <tr>\r\n\t <td align=\"left\" valign=\"top\">" . $LANG["tldsynclabel"] . ":&nbsp;</div></td>\r\n\t <td align=\"left\" valign=\"top\"><strong>" . $is_domainsynctlds_tlds . "</strong></td>\r\n\t </tr>\r\n\t </table>\r\n\t </div>\r\n\t <br />\r\n\t </div>\r\n\t ";
echo "<br /><div style=\"padding: 5px 20px;border: 1px solid #CCCCCC;-moz-border-radius: 5px;-webkit-border-radius: 5px;-o-border-radius: 5px;border-radius: 5px;\">\r\n\t <table class=\"table table-sm\">\r\n\t <tr><th colspan=\"2\" style=\"border-top: none;\"><h1><strong>" . $LANG["softversiontitle"] . "</strong></h1></th></tr>\r\n\t <tr><td width=\"25%\">" . $LANG["softversiontitle"] . " </td><td>" . $vars["version"] . "</td></tr>\r\n\t <tr><td>" . $LANG["releasedatetitle"] . " </td><td>" . $releasedate . "</td></tr>\r\n\t </table></div>";
echo "</td>\r\n\t </tr>\r\n\t </table><br />";

?>
