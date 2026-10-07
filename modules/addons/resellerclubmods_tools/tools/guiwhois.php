<?php
if (!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
echo "<script type=\"text/javascript\">// <![CDATA[\r\njQuery(document).ready(function(){\r\n  jQuery(\".scroll\").click(function(event){\r\n    event.preventDefault();\r\n    var offset = jQuery(jQuery(this).attr('href')).offset().top;\r\n    jQuery('html, body').animate({scrollTop:offset}, 1000);\r\n  });\r\n});\r\n// ]]></script>";
$md5_wid = rcm_whois_key_acceptable($vars["wid_key"] ?? "") ? rcm_whois_wid_expected($vars["wid_key"]) : "";
$reseller_whoisurl = $CONFIG["SystemURL"] . "/modules/addons/resellerclubmods_tools/tools/whois.php?wid=" . $md5_wid . "&domain=|HTTPREQUEST-available";
$url = "addonmodules.php?module=resellerclubmods_tools&domain=rcmwhois";
$file_whoisservers = ROOTDIR . "/includes/whoisservers.php";
echo $configuredto;
if (!rcm_whois_key_acceptable($vars["wid_key"] ?? "") || ($vars["wid_key"] ?? "") == "FB7koUY1aX") {
    echo "<div class=\"alert alert-warning\"><p>" . $LANG["warnwhoissecret"] . " <a href=\"configaddonmods.php#resellerclubmods_tools\">" . $LANG["clicktoconfigure"] . "</a> (min 16 chars; HTTP whois uses HMAC wid as of 2.19.3)</p></div>";
} else {
    if (!is_writable($file_whoisservers)) {
        $disablebuttons = "disabled=\"disabled\"";
        echo "<div class=\"alert alert-danger\"><p>" . $LANG["filenotwritable1"] . "</p></div>";
    }
    echo "<a id=\"top\"></a>";
    echo "<h1>" . $LANG["whoistooltitle"] . "</h1>";
    echo "<p>" . $LANG["whoistoolsdesc"] . "</p>";
    echo "<ul><li><a class=\"scroll\" href=\"#tool01\">" . $LANG["whoistool01"] . "</a></li><li><a class=\"scroll\" href=\"#tool02\">" . $LANG["whoistool02"] . "</a></li></ul>";
    echo "<div style=\"padding: 5px 20px;border: 1px solid #CCCCCC;-moz-border-radius: 5px;-webkit-border-radius: 5px;-o-border-radius: 5px;border-radius: 5px;\"><a id=\"tool01\"></a>";
    echo "<br /><h3 style=\"border-bottom: 1px solid #cccccc;\"><a style=\"text-decoration:none;\" href=\"#top\" class=\"scroll\">(Top)</a> <strong>" . $LANG["whoisttooltitle01"] . "</strong></h3>";
    if (isset($_POST["autowhoissetup"]) && $_POST["autowhoissetup"] == "true") {
        $text = file_get_contents($file_whoisservers);
        if (is_string($text)) {
            foreach ($_POST["tldlist"] as $tldkey) {
                $stringend = PHP_EOL;
                $pattern = sprintf("/^%s(.+?)%s/ims", preg_quote($tldkey . "|", "/"), preg_quote($stringend, "/"));
                if (preg_match($pattern, $text, $matches)) {
                    $match = $matches[1];
                    $strtoreplace = $tldkey . "|" . $match;
                    $newstring = $tldkey . "|" . $reseller_whoisurl;
                    if ($strtoreplace) {
                        $text = str_replace($strtoreplace, $newstring, $text);
                        $output .= "<p><strong style=\"color:#1A4D80;\">" . $LANG["whoisreplace"] . "</strong><br />" . $LANG["oldword"] . ": " . $strtoreplace . "<br />" . $LANG["newword"] . ": " . $newstring . "</p>";
                    }
                } else {
                    $newstring = $tldkey . "|" . $reseller_whoisurl;
                    $text = $text . PHP_EOL . $newstring . PHP_EOL;
                    $output .= "<p><strong style=\"color:#cc0000;\">" . $LANG["whoisnew"] . " " . $tldkey . " </strong><br />" . $newstring . "</p>";
                }
                $result = file_put_contents($file_whoisservers, $text);
            }
            if ($output && is_numeric($result)) {
                echo "<div class=\"alert alert-success\"><p>";
                echo $output;
                echo "</p></div>";
            } else {
                echo "<div class=\"alert alert-warning\"><p>" . $LANG["whoiselectatld"] . "</p></div>";
            }
        }
    }
    $tldextensions = [];
    foreach (Illuminate\Database\Capsule\Manager::table("tbldomainpricing")->orderBy("extension", "asc")->select("extension", "autoreg")->get() as $tldvars) {
        $tldextensions[$tldvars->extension] = $tldvars->autoreg;
    }
    echo "<p><strong>" . $LANG["howtotitlewhoistool2"] . "</strong></p>";
    echo "<ol><li>" . $LANG["howtotool201"] . "</li>";
    echo "<li>" . $LANG["howtotool202"] . "</li></ol>";
    echo "<div class=\"alert alert-warning\"><p><strong>" . $LANG["note"] . "</strong> " . $LANG["onlyselectlbtlds"] . "</p></div>";
    echo "<table><tr><td style=\"vertical-align:top;\">";
    echo "<p><strong>WHMCS " . $LANG["bulkalltldusing"] . "</strong><br />" . $LANG["tldtitle"] . " >> " . $LANG["registrarmoduletitle"] . "</p>";
    echo "<form action=\"#tool01\" method=\"post\">" . rcm_token_field() . "<input type=\"hidden\" name=\"autowhoissetup\" value=\"true\" /><select class=\"form-control\" style=\"display: inline; width auto\" name=\"tldlist[]\" size=\"10\" multiple=\"multiple\">";
    foreach ($tldextensions as $extension => $autoreg) {
        if (empty($autoreg)) {
            $autoreg = "none";
        }
        echo "<option value=\"" . $extension . "\">" . $extension . " >> " . $autoreg . "</option>";
    }
    echo "</select>";
    echo "<br /><br /><p><input class=\"btn btn-success\" type=\"submit\" value=\"" . $LANG["updatewhoisbutton"] . "\" " . $disablebuttons . " /></p>";
    echo "</form></td><td style=\"vertical-align:top;padding-left:30px;\">";
    echo "<p><strong>" . $LANG["whoisconfiguredtlds"] . ":</strong><br />" . $LANG["whoisconfiguredtldsdesc"] . " " . $LANG["resellerwhoisurltitle"] . "</p>";
    $whslistarray = explode(PHP_EOL, file_get_contents($file_whoisservers));
    sort($whslistarray);
    echo "<div style=\"width:auto;border:1px solid #cccccc;background-color:#f5f5f5;padding:0px 2px 1px 2px;font-weight:bold;\">";
    foreach ($whslistarray as $whslist) {
        $pos = strpos($whslist, $reseller_whoisurl);
        if ($pos !== false) {
            $strlen = strlen($whslist);
            $length = strpos($whslist, "|");
            if ($length === false) {
                $length = $strlen;
            }
            echo $result = substr($whslist, 0, $length) . " ";
        }
    }
    if (strlen($result) < 1) {
        echo $LANG["notldsusingwhois"] . " " . $LANG["resellerwhoisurltitle"];
    }
    echo "</div></td></tr></table></div><br /><div style=\"padding: 5px 20px;border: 1px solid #CCCCCC;-moz-border-radius: 5px;-webkit-border-radius: 5px;-o-border-radius: 5px;border-radius: 5px;\"><a id=\"tool02\"></a>";
    echo "<br /><h3 style=\"border-bottom: 1px solid #cccccc;\"><a style=\"text-decoration:none;\" href=\"#top\" class=\"scroll\">(Top)</a> <strong>" . $LANG["whoisttooltitle02"] . "</strong></h3>";
    echo "<div style=\"padding: 5px 20px;border: 1px solid #cc0000;-moz-border-radius: 5px;-webkit-border-radius: 5px;-o-border-radius: 5px;border-radius: 5px;\"><br />";
    echo "<div style=\"text-align:left;\"><strong>" . $LANG["resellerwhoisurltitle"] . "</strong></div>";
    echo "<input type=\"text\" class=\"form-control\" style=\"display: inline; width: 100%\" value=\"" . $reseller_whoisurl . "\" />";
    echo "<br /><br /><div class=\"alert alert-danger\"><strong>" . $LANG["note"] . "</strong><ul><li>" . $LANG["resellerwhoisurltitledesc"] . "</li><li>" . $LANG["resellerwhoisurltitledesc1"] . "</li></ul></div>";
    echo "<br /></div><br />";
    if (isset($_SESSION["rcmwhois"])) {
        if ($_SESSION["rcmwhois"]["success"]) {
            echo "<div class=\"alert alert-success\"><p>" . $_SESSION["rcmwhois"]["success"] . "</p></div>";
        } else {
            echo "<div class=\"alert alert-danger\"><p>" . $_SESSION["rcmwhois"]["error"] . "</p></div>";
        }
        unset($_SESSION["rcmwhois"]);
    }
    echo "<p><strong>" . $LANG["whoishowtomodtitle"] . "</strong><br /></p>";
    echo "<ol><li>" . $LANG["whoishowtomod1"] . "</li>";
    echo "<li>" . $LANG["whoishowtomod2"] . "</li>";
    echo "<li>" . $LANG["whoishowtomod3"] . "</li></ol>";
    echo "<p><strong>" . $LANG["whoishowtoaddtitle"] . "</strong></p>";
    echo "<ol><li>" . $LANG["whoishowtoadd1"] . "</li>";
    echo "<li>" . $LANG["whoishowtoadd2"] . "</li>";
    echo "<li>" . $LANG["whoishowtoadd3"] . "</li></ol>";
    echo "<div class=\"alert alert-warning\"><p><strong>" . $LANG["note"] . "</strong> " . $LANG["onlylbtlds"] . "</p></div>";
    echo "<div class=\"contentbox\" style=\"text-align:left;\">";
    echo "<h3>" . $LANG["whoisexampletitle"] . "</strong></h3>";
    echo "<p><strong>" . $LANG["whoisexamplereplace"] . "</strong>:<br />";
    echo ".com|whois.crsnic.net|No match for</p>";
    echo "<p><strong>" . $LANG["whoisexamplewith"] . "</strong>:<br />";
    echo ".com|" . $reseller_whoisurl . "</p></div><br />";
    $writable = "<span class=\"label terminated\">" . $LANG["filenotwritable"] . "</span>";
    if (is_writable($file_whoisservers)) {
        $writable = "<span class=\"label active\">" . $LANG["fileiswritable"] . "</span>";
    }
    if (isset($_POST["whoisservertext"]) && isset($_POST["manualwhoissetup"])) {
        $mod_text = html_entity_decode($_POST["whoisservertext"]);
        $result = file_put_contents($file_whoisservers, $mod_text);
        if (is_numeric($result) && 1 <= $result) {
            $filesaved = $LANG["whoisserverfilesaved"];
            $_SESSION["rcmwhois"]["success"] = $filesaved;
        } else {
            $filesaved = $LANG["whoisserverfilenotsaved"];
            $_SESSION["rcmwhois"]["error"] = $filesaved;
        }
        logActivity($filesaved);
        header(sprintf("Location: %s", $url));
        printf("<a href=\"%s\">Moved</a>.", $url);
        exit;
    }
    $text = file_get_contents($file_whoisservers);
    echo "<div style=\"padding: 5px 20px;border: 1px solid #CCCCCC;-moz-border-radius: 5px;-webkit-border-radius: 5px;-o-border-radius: 5px;border-radius: 5px;\">";
    echo "<p><strong>whoisservers.php</strong>: " . $writable . "</p>";
    echo "<form action=\"#tool02\" method=\"post\">" . rcm_token_field() . "";
    echo "<textarea name=\"whoisservertext\" class=\"form-control\" style=\"font-size:12px;\" cols=\"180\" rows=\"20\">" . $text . "</textarea>";
    echo "<input type=\"hidden\" name=\"manualwhoissetup\" value=\"true\"/>";
    echo "<br /><p align=\"center\"><input class=\"btn btn-success\" type=\"submit\" value=\"" . $LANG["whoissavebutton"] . "\" " . $disablebuttons . "/>&nbsp;<input class=\"btn btn-primary\" type=\"reset\" value=\"" . $LANG["whoisresetbutton"] . "\" " . $disablebuttons . " /></p>";
    echo "</form></div><br /></div>";
}

?>