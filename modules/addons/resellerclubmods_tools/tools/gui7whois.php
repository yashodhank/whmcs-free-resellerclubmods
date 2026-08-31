<?php
if (!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
if ($CONFIG["domainLookupProvider"] != "WhmcsWhois" && $CONFIG["domainLookupProvider"] != "WhmcsDomains") {
    echo "<div class=\"alert alert-warning\"><p><strong>" . $CONFIG["domainLookupProvider"] . " " . $LANG["lookupprovidererror"] . "</strong></p><span>" . $LANG["lookupprovidermessage"] . "</span></a></div>";
} else {
    echo "<script type=\"text/javascript\">// <![CDATA[\r\n\tjQuery(document).ready(function(){\r\n\t  jQuery(\".scroll\").click(function(event){\r\n\t\tevent.preventDefault();\r\n\t\tvar offset = jQuery(jQuery(this).attr('href')).offset().top;\r\n\t\tjQuery('html, body').animate({scrollTop:offset}, 1000);\r\n\t  });\r\n\t});\r\n\t// ]]></script>";
    $md5_wid = substr(md5($vars["wid_key"]), 6, 10);
    $reseller_whoisurl = $CONFIG["SystemURL"] . "/modules/addons/resellerclubmods_tools/tools/whois.php?wid=" . $md5_wid . "&domain=";
    $url = "addonmodules.php?module=resellerclubmods_tools&domain=rcmwhois";
    $file_whoisservers = ROOTDIR . "/resources/domains/whois.json";
    echo $configuredto;
    if (empty($vars["wid_key"]) || $vars["wid_key"] == "FB7koUY1aX") {
        echo "<div class=\"alert alert-warning\"><p>" . $LANG["warnwhoissecret"] . " <a href=\"configaddonmods.php#resellerclubmods_tools\">" . $LANG["clicktoconfigure"] . "</a></p></div>";
    } else {
        if (file_exists($file_whoisservers) && !is_writable($file_whoisservers)) {
            $disablebuttons = "disabled=\"disabled\"";
            echo "<div class=\"alert alert-danger\"><p>" . $LANG["whoisjsonfilenotwritable1"] . "</p></div>";
        }
        echo "<a id=\"top\"></a>";
        echo "<h1>" . $LANG["whoisjsontooltitle"] . "</h1>";
        echo "<p>" . $LANG["whoisjsontoolsdesc"] . "</p>";
        echo "<ul><li><a class=\"scroll\" href=\"#tool01\">" . $LANG["whoisjsontool01"] . "</a></li><li><a class=\"scroll\" href=\"#tool02\">" . $LANG["whoisjsontool02"] . "</a></li></ul>";
        echo "<div style=\"padding: 5px 20px;border: 1px solid #CCCCCC;-moz-border-radius: 5px;-webkit-border-radius: 5px;-o-border-radius: 5px;border-radius: 5px;\"><a id=\"tool01\"></a>";
        echo "<br /><h3 style=\"border-bottom: 1px solid #cccccc;\"><a style=\"text-decoration:none;\" href=\"#top\" class=\"scroll\">(Top)</a> <strong>" . $LANG["whoisjsontooltitle01"] . "</strong></h3>";
        if (isset($_POST["autowhoissetup"]) && $_POST["autowhoissetup"] == "true") {
            if (!isset($_POST["tldlist"])) {
                echo "<div class=\"alert alert-warning\"><p>" . $LANG["whoiselectatld"] . "</p></div>";
            } else {
                $whslistarray = [];
                $result = file_get_contents($file_whoisservers);
                $whslistarray = json_decode($result, true);
                if (is_array($whslistarray)) {
                    foreach ($whslistarray as $key => $whslist) {
                        $pos = strpos($whslist["uri"], $reseller_whoisurl);
                        if ($pos !== false) {
                            if (!empty($whslist["extensions"])) {
                                $tlds_array = explode(",", $whslist["extensions"]);
                            } else {
                                $tlds_array = [];
                            }
                            foreach ($_POST["tldlist"] as $tldkey) {
                                $tlds_array[] = $tldkey;
                            }
                            $tlds_array = array_unique($tlds_array);
                            sort($tlds_array);
                            $whslistarray[$key] = ["extensions" => implode(",", $tlds_array), "uri" => $whslist["uri"], "available" => $whslist["available"]];
                        }
                    }
                    $whoislist = json_encode($whslistarray, JSON_PRETTY_PRINT);
                    $whoislist = str_replace("\\", "", $whoislist);
                    $result = file_put_contents($file_whoisservers, $whoislist);
                }
                echo "<div class=\"alert alert-success\"><p>";
                echo implode(", ", $_POST["tldlist"]) . " TLD have been added to the API WHOIS Server Definition!";
                echo "</p></div>";
            }
        }
        if (file_exists($file_whoisservers)) {
            $result = file_get_contents($file_whoisservers);
            $whoislist = json_decode($result, true);
            if (is_array($whoislist)) {
                $whslistarray = [];
                $result = file_get_contents($file_whoisservers);
                $whslistarray = json_decode($result, true);
                foreach ($whslistarray as $key => $whslist) {
                    $pos = strpos($whslist["uri"], "wid");
                    if ($pos !== false) {
                        $add_def = 1;
                        $widchange = 1;
                        if ($whslist["uri"] != $reseller_whoisurl) {
                            $widchange = "0";
                            $array_key = $key;
                            if (!empty($whslist["extensions"])) {
                                $tlds_array = explode(",", $whslist["extensions"]);
                            } else {
                                $tlds_array = "";
                            }
                        }
                    }
                }
                if ($add_def != 1) {
                    $whslistarray[] = ["extensions" => "", "uri" => $reseller_whoisurl, "available" => "available"];
                    $whoislist = json_encode($whslistarray, JSON_PRETTY_PRINT);
                    $whoislist = str_replace("\\", "", $whoislist);
                    $result = file_put_contents($file_whoisservers, $whoislist);
                }
                if ($widchange != 1) {
                    $whslistarray[$array_key] = ["extensions" => implode(",", $tlds_array), "uri" => $reseller_whoisurl, "available" => "available"];
                    $whoislist = json_encode($whslistarray, JSON_PRETTY_PRINT);
                    $whoislist = str_replace("\\", "", $whoislist);
                    $result = file_put_contents($file_whoisservers, $whoislist);
                }
            } else {
                $apiwhoisdefinition = ["extensions" => "", "uri" => $reseller_whoisurl, "available" => "available"];
                $whoislist = json_encode($apiwhoisdefinition, JSON_PRETTY_PRINT);
                $whoislist = str_replace("\\", "", $whoislist);
                $whoislist = str_replace("{", "[" . PHP_EOL . "    {", $whoislist);
                $whoislist = str_replace("}", "    }" . PHP_EOL . "]", $whoislist);
                $result = file_put_contents($file_whoisservers, $whoislist);
            }
        } else {
            $apiwhoisdefinition = ["extensions" => "", "uri" => $reseller_whoisurl, "available" => "available"];
            $whoislist = json_encode($apiwhoisdefinition, JSON_PRETTY_PRINT);
            $whoislist = str_replace("\\", "", $whoislist);
            $whoislist = str_replace("{", "[" . PHP_EOL . "    {", $whoislist);
            $whoislist = str_replace("}", "    }" . PHP_EOL . "]", $whoislist);
            $result = file_put_contents($file_whoisservers, $whoislist);
        }
        $tldextensions = [];
        foreach (Illuminate\Database\Capsule\Manager::table("tbldomainpricing")->orderBy("extension", "asc")->select("extension", "autoreg")->get() as $tldvars) {
            $tldextensions[$tldvars->extension] = $tldvars->autoreg;
        }
        echo "<p><strong>" . $LANG["howtotitlewhoistool2"] . "</strong></p>";
        echo "<ol><li>" . $LANG["howtotool201"] . "</li>";
        echo "<li>" . $LANG["whoisjsonhowtotool202"] . "</li></ol>";
        echo "<div class=\"alert alert-warning\"><p><strong>" . $LANG["note"] . "</strong> " . $LANG["onlyselectlbtlds"] . "</p></div>";
        echo "<table><tr><td style=\"vertical-align:top;\">";
        echo "<p><strong>WHMCS " . $LANG["bulkalltldusing"] . "</strong><br />" . $LANG["tldtitle"] . " >> " . $LANG["registrarmoduletitle"] . "</p>";
        echo "<form action=\"#tool01\" method=\"post\"><input type=\"hidden\" name=\"autowhoissetup\" value=\"true\" /><select class=\"form-control\" style=\"display: inline; width auto\" name=\"tldlist[]\" size=\"10\" multiple=\"multiple\">";
        foreach ($tldextensions as $extension => $autoreg) {
            if (empty($autoreg)) {
                $autoreg = "none";
            }
            echo "<option value=\"" . $extension . "\">" . $extension . " >> " . $autoreg . "</option>";
        }
        echo "</select>";
        echo "<br /><br /><p><input class=\"btn btn-success\" type=\"submit\" value=\"" . $LANG["whoisjsonupdatebutton"] . "\" " . $disablebuttons . " /></p>";
        echo "</form></td><td style=\"vertical-align:top;padding-left:30px;\">";
        echo "<p><strong>" . $LANG["whoisconfiguredtlds"] . ":</strong><br />" . $LANG["whoisconfiguredtldsdesc"] . " " . $LANG["resellerwhoisurltitle"] . "</p>";
        echo "<div style=\"width:auto;border:1px solid #cccccc;background-color:#f5f5f5;padding:0px 2px 1px 2px;font-weight:bold;\">";
        $whslistarray = [];
        $result = file_get_contents($file_whoisservers);
        $whslistarray = json_decode($result, true);
        if (is_array($whslistarray)) {
            foreach ($whslistarray as $whslist) {
                $pos = strpos($whslist["uri"], $reseller_whoisurl);
                if ($pos !== false) {
                    $tlds_array = explode(",", $whslist["extensions"]);
                }
            }
            if (is_array($tlds_array)) {
                sort($tlds_array);
                $tlds_strings = implode(", ", $tlds_array);
            }
        }
        if ($tlds_strings) {
            echo $tlds_strings;
        } else {
            echo $LANG["notldsusingwhois"] . " " . $LANG["resellerwhoisurltitle"];
        }
        echo "</div></td></tr></table></div><br /><div style=\"padding: 5px 20px;border: 1px solid #CCCCCC;-moz-border-radius: 5px;-webkit-border-radius: 5px;-o-border-radius: 5px;border-radius: 5px;\"><a id=\"tool02\"></a>";
        echo "<br /><h3 style=\"border-bottom: 1px solid #cccccc;\"><a style=\"text-decoration:none;\" href=\"#top\" class=\"scroll\">(Top)</a> <strong>" . $LANG["whoisjsontooltitle02"] . "</strong></h3>";
        if (isset($_SESSION["rcmwhois"])) {
            if ($_SESSION["rcmwhois"]["success"]) {
                echo "<div class=\"alert alert-success\"><p>" . $_SESSION["rcmwhois"]["success"] . "</p></div>";
            } else {
                echo "<div class=\"alert alert-danger\"><p>" . $_SESSION["rcmwhois"]["error"] . "</p></div>";
            }
            unset($_SESSION["rcmwhois"]);
        }
        echo "<div style=\"padding: 5px 20px;border: 1px solid #cc0000;-moz-border-radius: 5px;-webkit-border-radius: 5px;-o-border-radius: 5px;border-radius: 5px;\"><br />";
        echo "<div style=\"text-align:left;\"><strong>" . $LANG["resellerwhoisurltitle"] . "</strong></div>";
        echo "<input type=\"text\" class=\"form-control\" style=\"display: inline; width: 100%\" value=\"" . $reseller_whoisurl . "\" />";
        echo "<br /><br /><div class=\"alert alert-danger\"><strong>" . $LANG["note"] . "</strong><ul><li>" . $LANG["resellerwhoisurltitledesc"] . "</li><li>" . $LANG["resellerwhoisurltitledesc1"] . "</li></ul></div>";
        echo "<br /></div><br />";
        $writable = "<span class=\"label terminated\">" . $LANG["filenotwritable"] . "</span>";
        if (is_writable($file_whoisservers)) {
            $writable = "<span class=\"label active\">" . $LANG["fileiswritable"] . "</span>";
        }
        if (isset($_POST["whoisservertext"]) && isset($_POST["manualwhoissetup"])) {
            $mod_text = html_entity_decode($_POST["whoisservertext"]);
            $result = file_put_contents($file_whoisservers, $mod_text);
            $filesaved = $LANG["whoisjsonfilesaved"];
            $_SESSION["rcmwhois"]["success"] = $filesaved;
            logActivity($filesaved);
            header(sprintf("Location: %s", $url));
            printf("<a href=\"%s\">Moved</a>.", $url);
            exit;
        }
        $text = file_get_contents($file_whoisservers);
        echo "<div style=\"padding: 5px 20px;border: 1px solid #CCCCCC;-moz-border-radius: 5px;-webkit-border-radius: 5px;-o-border-radius: 5px;border-radius: 5px;\">";
        echo "<p><strong>whois.json</strong>: " . $writable . "</p>";
        echo "<div class=\"alert alert-warning\"><p><strong>" . $LANG["note"] . "</strong> " . $LANG["onlylbtlds"] . "</p></div>";
        echo "<p><strong>" . $LANG["whoishowtoaddtitle"] . "</strong></p>";
        echo "<div class=\"contentbox\" style=\"text-align:left;\">";
        echo "<h3>" . $LANG["whoisjsonexampletitle"] . "</strong></h3>";
        echo "<p><strong>" . $LANG["whoisjsondefinition"] . "</strong>:<br />" . $LANG["whoisjsondefinitionnote"];
        echo "<pre>[<br />    {<br />        \"extensions\": \".com,.net\",<br />";
        echo "        \"uri\": \"" . $reseller_whoisurl . "\",<br />";
        echo "        \"available\": \"available\"<br />    }<br />]<br /></pre></p><form action=\"#tool02\" method=\"post\">";
        echo "<textarea name=\"whoisservertext\" class=\"form-control\" style=\"font-size:12px;\" cols=\"180\" rows=\"20\">" . $text . "</textarea>";
        echo "<input type=\"hidden\" name=\"manualwhoissetup\" value=\"true\"/>";
        echo "<br /><p align=\"center\"><input class=\"btn btn-success\" type=\"submit\" value=\"" . $LANG["whoisjsonsavebutton"] . "\" " . $disablebuttons . "/>&nbsp;<input class=\"btn btn-primary\" type=\"reset\" value=\"" . $LANG["whoisresetbutton"] . "\" " . $disablebuttons . " /></p>";
        echo "</form></div><br /></div></div>";
    }
}

?>