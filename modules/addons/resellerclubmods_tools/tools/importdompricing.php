<?php
if (!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
if ($is_mplicator == 1000) {
    $input_size = "8";
} else {
    $input_size = "4";
}
$conf = [];
foreach (Illuminate\Database\Capsule\Manager::table("tbladdonmodules")->where("module", "resellerclubmods_tools")->get() as $addonvars) {
    $conf[$addonvars->setting] = $addonvars->value;
}
if (empty($conf["pagination_tlds"])) {
    $pagination_tlds = 50;
}
$post_updtldsettings = $_POST["updtldsettings"];
$post_order = isset($_POST["order"]) ? $_POST["order"] : "0";
$post_dnsmanagement = isset($_POST["dnsmanagement"]) ? $_POST["dnsmanagement"] : "";
$post_emailforwarding = isset($_POST["emailforwarding"]) ? $_POST["emailforwarding"] : "";
$post_idprotection = isset($_POST["idprotection"]) ? $_POST["idprotection"] : "";
$post_eppcode = isset($_POST["eppcode"]) ? $_POST["eppcode"] : "";
$post_autoreg = $_POST["autoreg"];
$post_bulkform = $_POST["bulkform"];
$post_dobulktldsetup = $_POST["dobulktldsetup"];
$clientgroup_filter = $_REQUEST["clientgroup"];
$clientgroupid = $_POST["clientgroup"];
$currencydoupd = $_POST["currencydoupd"];
$redemptionform = $_POST["redemptionform"];
$telescope = $_POST["telescope"];
if (isset($_POST["fetchapidata"]) && $_POST["fetchapidata"] == "true") {
    unset($_SESSION["rcm_dompricingsXml"]);
    unset($_SESSION["rcm_productkeys"]);
    unset($_SESSION["rcm_addondetails"]);
    unset($_SESSION["rcm_resellerpricingsXml"]);
    unset($_SESSION["rcm_pregatlds"]);
    echo "<div class=\"alert alert-success\"><p>New API data retrieved and successfully cached</p></div>";
}
$foreigncurrencies = foreignCurrencies();
if (empty($clientgroup_filter)) {
    $filtered = 0;
} else {
    $filtered = $clientgroup_filter;
}
$extension = $_POST["tldsetup"];
if (isset($_POST["tldsetup"]) && $_POST["tldsetup"] == $extension) {
    $id = "";
    $values = ["id" => $id, "extension" => $extension, "dnsmanagement" => $post_dnsmanagement, "emailforwarding" => $post_emailforwarding, "idprotection" => $post_idprotection, "eppcode" => $post_eppcode, "autoreg" => $post_autoreg, "order" => $post_order];
    Illuminate\Database\Capsule\Manager::table("tbldomainpricing")->insert($values);
    $tbldomainpricing_result = 1;
    $tldpointer = $extension;
}
if (isset($post_dobulktldsetup) && $post_dobulktldsetup == "true") {
    foreach ($post_bulkform as $bkey => $bvalue) {
        if (isset($bvalue["include"]) && $bvalue["include"] == "true") {
            if (empty($bvalue["order"]) || !is_numeric($bvalue["order"])) {
                $sortordernum = 0;
            } else {
                $sortordernum = $bvalue["order"];
            }
            $values = ["id" => "", "extension" => $bvalue["tld"], "dnsmanagement" => isset($bvalue["dnsmanagement"]) ? $bvalue["dnsmanagement"] : "", "emailforwarding" => isset($bvalue["emailforwarding"]) ? $bvalue["emailforwarding"] : "", "idprotection" => isset($bvalue["idprotection"]) ? $bvalue["idprotection"] : "", "eppcode" => isset($bvalue["eppcode"]) ? $bvalue["eppcode"] : "", "order" => isset($sortordernum) ? $sortordernum : "", "autoreg" => $bvalue["autoreg"]];
            Illuminate\Database\Capsule\Manager::table("tbldomainpricing")->insert($values);
        }
    }
    if (isset($post_bulkform["bulkimportprices"]) && $post_bulkform["bulkimportprices"] == "true") {
        $domainsyncpage = $systemurl . "/modules/addons/resellerclubmods_tools/cron/resellerclubmods_dompricesync.php?id=" . $rcauth_userid . "&clientgroup=" . $filtered . "&dobulksetupimport=true&currencydoupd=" . $currencydoupd . "&redemptionform=" . $redemptionform . "&telescope=" . $telescope;
        header("Location: " . $domainsyncpage);
        exit;
    }
}
if (isset($_REQUEST["dobulksync"]) && $_REQUEST["dobulksync"] == "success") {
    echo "<div class=\"alert alert-success\"><p>" . $LANG["bulksdomsyncsuccess"] . "</p></div>";
}
if (isset($post_bulkform["bulkimportprices"]) && $post_bulkform["bulkimportprices"] == "false") {
    echo "<div class=\"alert alert-success\"><p>" . $LANG["bulktldsetupsuccess"] . "</p></div>";
}
if (isset($_REQUEST["dobulksetupimport"]) && $_REQUEST["dobulksetupimport"] == "success") {
    echo "<div class=\"alert alert-success\"><p>" . $LANG["bulktldsetupimportsuccess"] . "</p></div>";
}
echo "<a name=\"top\"></a>";
echo $configuredto;
echo "<h1>" . $LANG["domainpriceimporttitle2"] . "</h1>";
$clientgroups[0] = $LANG["defaultdomslabtitle"];
foreach (Illuminate\Database\Capsule\Manager::table("tblclientgroups")->select("id", "groupname")->get() as $data) {
    $clientgroups[$data->id] = $data->groupname;
}
echo "<div style=\"width:auto\"><div class=\"alert alert-info\">";
echo "<form method=\"post\" action=\"" . $_SERVER["SCRIPT_NAME"] . "?module=resellerclubmods_tools&domain=domain-pricing-import\">";
echo "<br /><h3 style=\"border-bottom: 1px solid #cccccc;\"><strong>" . $LANG["domainpricing"] . "</strong></h3>";
echo "<p>" . $LANG["defaultdomslabdesc"] . "</p>";
echo "<p><strong>" . $LANG["note"] . "</strong> " . $LANG["synctoolnotes"] . "</p>";
echo "<strong>" . $LANG["domainpricing"] . "</strong>: <select class=\"form-control\" style=\"display: inline; width: auto\" name=\"clientgroup\" onchange=\"submit();\">";
foreach ($clientgroups as $key => $value) {
    if ($filtered == $key) {
        $selected = "selected=\"selected\"";
        $grouplabel = $value;
    } else {
        $selected = "";
        $grouplabel = $clientgroups[0];
    }
    echo "<option value=\"" . $key . "\" " . $selected . ">" . $value . "</option>";
}
echo "</select></form>";
echo "<br /><h3 style=\"border-bottom: 1px solid #cccccc;\"><strong>" . $LANG["apichachetitle"] . "</strong></h3>";
echo $LANG["apichachedesc"];
echo "<div style=\"padding:3px 0px 5px 0px;\">";
echo "<form method=\"post\" action=\"" . $_SERVER["REQUEST_URI"] . "\">";
echo "<input type=\"hidden\" name=\"fetchapidata\" value=\"true\"/>";
echo "<div style=\"padding:5px 0px 0px 0px;\"><input type=\"submit\" class=\"btn btn-primary\" value=\"" . $LANG["apichachereload"] . "\" /></div>";
echo "</form></div></div></div>";
if (isset($post_updtldsettings) && $post_updtldsettings == "true") {
    if (is_numeric($post_order)) {
        $update = ["dnsmanagement" => $post_dnsmanagement, "emailforwarding" => $post_emailforwarding, "idprotection" => $post_idprotection, "eppcode" => $post_eppcode, "autoreg" => $post_autoreg, "order" => $post_order];
        $result_update = Illuminate\Database\Capsule\Manager::table("tbldomainpricing")->where("extension", "=", $_POST["updtld"])->update($update);
        $updtld_result = true;
    } else {
        $updtld_result = false;
    }
    $tldpointer = $_POST["updtld"];
}
if (empty($_SESSION["rcm_dompricingsXml"][$rcauth_userid])) {
    $apifunction = "/api/products/customer-price.json";
    $dompricingsXml = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
    $_SESSION["rcm_dompricingsXml"][$rcauth_userid] = serialize($dompricingsXml);
} else {
    $dompricingsXml = unserialize($_SESSION["rcm_dompricingsXml"][$rcauth_userid]);
}
if (empty($_SESSION["rcm_productkeys"][$rcauth_userid])) {
    $productkeys = newproductkeys($rcauth_userid, $rcauth_password, $rchttp_api);
    if (array_key_exists("thirdleveldotname", $productkeys)) {
        unset($productkeys["thirdleveldotname"]);
    }
    $_SESSION["rcm_productkeys"][$rcauth_userid] = serialize($productkeys);
} else {
    $productkeys = unserialize($_SESSION["rcm_productkeys"][$rcauth_userid]);
}
if (empty($_SESSION["rcm_addondetails"][$rcauth_userid])) {
    $addondetails = getaddondetails($rcauth_userid, $rcauth_password, $rchttp_api);
    $_SESSION["rcm_addondetails"][$rcauth_userid] = serialize($addondetails);
} else {
    $addondetails = unserialize($_SESSION["rcm_addondetails"][$rcauth_userid]);
}
foreach ($productkeys as $productkey => $productvalue) {
    foreach ($addondetails[$productkey]["tldlist"] as $addontld) {
        $addontld = $IDN->decode($addontld);
        if ($addondetails[$productkey]["isprivacyprotectionallowed"] == "false") {
            $privacy_array[] = "." . $addontld;
        }
        if ($addondetails[$productkey]["istransfersecretrequired"] == "true") {
            $eppcode_array[] = "." . $addontld;
        }
        if ($addondetails[$productkey]["maxregistrationyear"] != 10) {
            $max_reg_array["." . $addontld] = ["." . $addontld, $addondetails[$productkey]["maxregistrationyear"]];
            if ($addondetails[$productkey]["minregistrationyear"] == $addondetails[$productkey]["maxregistrationyear"]) {
                $minmax_reg_array["." . $addontld] = ["." . $addontld, $addondetails[$productkey]["minregistrationyear"]];
            }
        }
        if ($addondetails[$productkey]["maxrenewalperiod"] != 10 && $addondetails[$productkey]["minrenewalperiod"] == $addondetails[$productkey]["maxrenewalperiod"]) {
            $minmax_ren_array["." . $addontld] = ["." . $addontld, $addondetails[$productkey]["minrenewalperiod"]];
        }
        if ($addondetails[$productkey]["redeemption_graceperiod"] != 0) {
            $redemption_array["." . $addontld] = ["." . $addontld, $addondetails[$productkey]["redeemption_graceperiod"]];
        }
    }
    foreach ($dompricingsXml as $key => $value) {
        if ($productkey == $key) {
            $multitld = explode(",", $productvalue);
            $multitld = array_filter($multitld);
            $rc_tldarray[] = ["tld" => $multitld];
            $rc_productkey_array[$key] = $multitld;
        }
    }
}
foreach ($rc_tldarray as $rc_tldarray_level1) {
    foreach ($rc_tldarray_level1["tld"] as $rc_tldarray_level2) {
        $new_rc_tldarray[0]["tld"][] = $rc_tldarray_level2;
    }
}
$rc_tldarray = $new_rc_tldarray;
if (empty($_SESSION["rcm_resellerpricingsXml"][$rcauth_userid])) {
    $apifunction = "/api/products/reseller-cost-price.json";
    $resellerpricingsXml = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
    $_SESSION["rcm_resellerpricingsXml"][$rcauth_userid] = serialize($resellerpricingsXml);
} else {
    $resellerpricingsXml = unserialize($_SESSION["rcm_resellerpricingsXml"][$rcauth_userid]);
}
foreach ($rc_productkey_array as $rc_productkey => $rc_productvals) {
    foreach ($rc_productvals as $rc_producttlds) {
        $rc_producttlds = $rc_producttlds;
        foreach ($dompricingsXml as $key1 => $value1) {
            if ($key1 == $rc_productkey) {
                $links_array[] = $rc_producttlds;
                $newproductprice_array[$rc_producttlds] = $value1;
            }
        }
        foreach ($resellerpricingsXml as $key2 => $value2) {
            if ($key2 == $rc_productkey) {
                $costnewproductprice_array[$rc_producttlds] = $value2;
            }
        }
    }
}
$costdb_array = [];
foreach ($costnewproductprice_array as $keytld => $valuetld) {
    $costdb_array["registerfee"][$keytld] = (float) $valuetld["addnewdomain"][1] * $is_mplicator;
    $costdb_array["renewfee"][$keytld] = (float) $valuetld["renewdomain"][1] * $is_mplicator;
    $costdb_array["transferfee"][$keytld] = (float) $valuetld["addtransferdomain"][1] * $is_mplicator;
    if (!empty($valuetld["restoredomain"][1])) {
        $costdb_array["restorefee"][$keytld] = (float) $valuetld["restoredomain"][1] * $is_mplicator;
    }
}
if ($filtered == 0) {
    $inpromotld = [];
    $promotable = [];
    $profitexception = [];
    foreach (Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodspromo")->select("sellingprice", "promoprice", "relid", "type", "extension")->get() as $data) {
        $inpromotld[$data->extension][$data->type] = $data->extension;
        $promotable[] = ["relid" => $data->relid, "type" => $data->type, "sellingprice" => $data->sellingprice, "promoprice" => $data->promoprice];
        $profitexception[$data->extension][$data->type] = $data->sellingprice;
    }
}
if (empty($_SESSION["rcm_pregatlds"][$rcauth_userid])) {
    $apifunction = "/api/domains/tlds-in-phase.json";
    $data = ["phase" => "prega"];
    $get_pregatlds = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
    $_SESSION["rcm_pregatlds"][$rcauth_userid] = serialize($get_pregatlds);
} else {
    $get_pregatlds = unserialize($_SESSION["rcm_pregatlds"][$rcauth_userid]);
}
foreach ($get_pregatlds as $prega_group) {
    foreach ($prega_group as $prega_tlds) {
        foreach ($prega_tlds as $prega_tld) {
            $is_prega[] = "." . $prega_tld;
        }
    }
}
if (is_array($is_prega)) {
    $pre_ga_tlds = array_uniqe($is_prega);
}
$whmcsextension = $_POST["tldpricings"];
if (isset($_POST["tldpricings"]) && $_POST["tldpricings"] == $whmcsextension) {
    if (is_numeric($_POST["regfee"])) {
        if ($currencyswitch == "on") {
            $regfee = $_POST["regfee"] * $currencyrate;
        } else {
            $regfee = $_POST["regfee"];
        }
    }
    if (is_numeric($_POST["renfee"])) {
        if ($currencyswitch == "on") {
            $renfee = $_POST["renfee"] * $currencyrate;
        } else {
            $renfee = $_POST["renfee"];
        }
    }
    if (is_numeric($_POST["trafee"])) {
        if ($currencyswitch == "on") {
            $trafee = $_POST["trafee"] * $currencyrate;
        } else {
            $trafee = $_POST["trafee"];
        }
    }
    if (isset($_POST["costpercentincrease"]) && $_POST["costpercentincrease"] == "true") {
        $is_costpercentimport = 1;
        if (is_numeric($_POST["regpercentincrease"])) {
            $regpercentincrease = $_POST["regpercentincrease"];
        }
        if (is_numeric($_POST["renpercentincrease"])) {
            $renpercentincrease = $_POST["renpercentincrease"];
        }
        if (is_numeric($_POST["trapercentincrease"])) {
            $trapercentincrease = $_POST["trapercentincrease"];
        }
    }
    if (isset($_POST["costincrease"]) && $_POST["costincrease"] == "true") {
        $is_costimport = 1;
        if (is_numeric($_POST["regincrease"])) {
            if ($currencyswitch == "on") {
                $regincrease = $_POST["regincrease"] * $currencyrate;
            } else {
                $regincrease = $_POST["regincrease"];
            }
        }
        if (is_numeric($_POST["renincrease"])) {
            if ($currencyswitch == "on") {
                $renincrease = $_POST["renincrease"] * $currencyrate;
            } else {
                $renincrease = $_POST["renincrease"];
            }
        }
        if (is_numeric($_POST["traincrease"])) {
            if ($currencyswitch == "on") {
                $traincrease = $_POST["traincrease"] * $currencyrate;
            } else {
                $traincrease = $_POST["traincrease"];
            }
        }
    }
    if (is_numeric($_POST["increase"])) {
        if ($currencyswitch == "on") {
            $increase = $_POST["increase"] * $currencyrate;
        } else {
            $increase = $_POST["increase"];
        }
    }
    $relid = $_POST["relid"];
    $result = Illuminate\Database\Capsule\Manager::table("tblpricing")->where("relid", "=", $relid)->where("type", "=", "domainregister")->where("currency", "=", $currency)->where("tsetupfee", "=", round($filtered))->select("relid")->get();
    $tblpricing_relid = $result[0]->relid;
    if (!empty($tblpricing_relid)) {
        $isrelid = $tblpricing_relid;
        $doUpdate = true;
    }
    $billingslabs = ["1" => "msetupfee", "2" => "qsetupfee", "3" => "ssetupfee", "4" => "asetupfee", "5" => "bsetupfee", "6" => "monthly", "7" => "quarterly", "8" => "semiannually", "9" => "annually", "10" => "biennially"];
    $mplicator_add = 1;
    foreach ($newproductprice_array as $add_key => $add_values) {
        if ($add_key == $whmcsextension) {
            ksort($add_values["addnewdomain"]);
            $tldpointer = $add_key;
            if ($add_key == $minmax_reg_array[$add_key][0]) {
                $addk = $billingslabs[$minmax_reg_array[$add_key][1]];
                if ($is_costimport) {
                    $dbadd_array[$addk] = ($regfee + $regincrease) * $minmax_reg_array[$add_key][1];
                } else if ($is_costpercentimport) {
                    $dbadd_array[$addk] = ($regfee / 100 * $regpercentincrease + $regfee) * $minmax_reg_array[$add_key][1];
                } else {
                    $dbadd_array[$addk] = ($add_values["addnewdomain"][$minmax_reg_array[$add_key][1]] + $increase) * $minmax_reg_array[$add_key][1];
                }
            } else {
                foreach ($add_values["addnewdomain"] as $k => $v) {
                    if ($k == 1) {
                        $addk = "msetupfee";
                    }
                    if ($k == 2) {
                        $addk = "qsetupfee";
                    }
                    if ($k == 3) {
                        $addk = "ssetupfee";
                    }
                    if ($k == 4) {
                        $addk = "asetupfee";
                    }
                    if ($k == 5) {
                        $addk = "bsetupfee";
                    }
                    if ($k == 6) {
                        $addk = "monthly";
                    }
                    if ($k == 7) {
                        $addk = "quarterly";
                    }
                    if ($k == 8) {
                        $addk = "semiannually";
                    }
                    if ($k == 9) {
                        $addk = "annually";
                    }
                    if ($k == 10) {
                        $addk = "biennially";
                    }
                    if ($is_costimport) {
                        $dbadd_array[$addk] = ($regfee + $regincrease) * $multiplicator * $mplicator_add++;
                    } else if ($is_costpercentimport) {
                        $dbadd_array[$addk] = ($regfee / 100 * $regpercentincrease + $regfee) * $multiplicator * $mplicator_add++;
                    } else {
                        $dbadd_array[$addk] = ($v + $increase) * $multiplicator * $mplicator_add++;
                    }
                }
            }
            $redemption_add = "-1";
            $redemption_days = "-1";
            if ($add_values["restoredomain"][1]) {
                $redemption_add = (float) $add_values["restoredomain"][1] * $multiplicator;
                $redemption_days = $redemption_array[$whmcsextension][1];
            }
        }
    }
    if ($_POST["telescope"] == 1) {
        if (!isset($dbadd_array["msetupfee"])) {
            $msetupfee_add = "-1";
        } else {
            $msetupfee_add = $dbadd_array["msetupfee"];
        }
        $qsetupfee_add = "-1";
        $ssetupfee_add = "-1";
        $asetupfee_add = "-1";
        $bsetupfee_add = "-1";
        $monthly_add = "-1";
        $quarterly_add = "-1";
        $semiannually_add = "-1";
        $annually_add = "-1";
        $biennially_add = "-1";
    } else {
        if (!isset($dbadd_array["msetupfee"]) || $dbadd_array["msetupfee"] == "0.00") {
            $msetupfee_add = "-1";
        } else {
            $msetupfee_add = $dbadd_array["msetupfee"];
        }
        if (!isset($dbadd_array["qsetupfee"]) || $dbadd_array["qsetupfee"] == "0.00") {
            $qsetupfee_add = "-1";
        } else {
            $qsetupfee_add = $dbadd_array["qsetupfee"];
        }
        if (!isset($dbadd_array["ssetupfee"]) || $dbadd_array["ssetupfee"] == "0.00") {
            $ssetupfee_add = "-1";
        } else {
            $ssetupfee_add = $dbadd_array["ssetupfee"];
        }
        if (!isset($dbadd_array["asetupfee"]) || $dbadd_array["asetupfee"] == "0.00") {
            $asetupfee_add = "-1";
        } else {
            $asetupfee_add = $dbadd_array["asetupfee"];
        }
        if (!isset($dbadd_array["bsetupfee"]) || $dbadd_array["bsetupfee"] == "0.00") {
            $bsetupfee_add = "-1";
        } else {
            $bsetupfee_add = $dbadd_array["bsetupfee"];
        }
        if (!isset($dbadd_array["monthly"]) || $dbadd_array["monthly"] == "0.00") {
            $monthly_add = "-1";
        } else {
            $monthly_add = $dbadd_array["monthly"];
        }
        if (!isset($dbadd_array["quarterly"]) || $dbadd_array["quarterly"] == "0.00") {
            $quarterly_add = "-1";
        } else {
            $quarterly_add = $dbadd_array["quarterly"];
        }
        if (!isset($dbadd_array["semiannually"]) || $dbadd_array["semiannually"] == "0.00") {
            $semiannually_add = "-1";
        } else {
            $semiannually_add = $dbadd_array["semiannually"];
        }
        if (!isset($dbadd_array["annually"]) || $dbadd_array["annually"] == "0.00") {
            $annually_add = "-1";
        } else {
            $annually_add = $dbadd_array["annually"];
        }
        if (!isset($dbadd_array["biennially"]) || $dbadd_array["biennially"] == "0.00") {
            $biennially_add = "-1";
        } else {
            $biennially_add = $dbadd_array["biennially"];
        }
    }
    if ($currencyswitch == "on") {
        if ($msetupfee_add != "-1") {
            $msetupfee_add = $msetupfee_add / $currencyrate;
        }
        if ($qsetupfee_add != "-1") {
            $qsetupfee_add = $qsetupfee_add / $currencyrate;
        }
        if ($ssetupfee_add != "-1") {
            $ssetupfee_add = $ssetupfee_add / $currencyrate;
        }
        if ($asetupfee_add != "-1") {
            $asetupfee_add = $asetupfee_add / $currencyrate;
        }
        if ($bsetupfee_add != "-1") {
            $bsetupfee_add = $bsetupfee_add / $currencyrate;
        }
        if ($monthly_add != "-1") {
            $monthly_add = $monthly_add / $currencyrate;
        }
        if ($quarterly_add != "-1") {
            $quarterly_add = $quarterly_add / $currencyrate;
        }
        if ($semiannually_add != "-1") {
            $semiannually_add = $semiannually_add / $currencyrate;
        }
        if ($annually_add != "-1") {
            $annually_add = $annually_add / $currencyrate;
        }
        if ($biennially_add != "-1") {
            $biennially_add = $biennially_add / $currencyrate;
        }
        if ($redemption_add != "-1") {
            $redemption_add = $redemption_add / $currencyrate;
        }
    }
    if ($redemptionform == "true") {
        $update_redemption = ["redemption_grace_period" => $redemption_days, "redemption_grace_period_fee" => $redemption_add];
        $result_update_redemption = Illuminate\Database\Capsule\Manager::table("tbldomainpricing")->where("id", "=", $relid)->where("extension", "=", $whmcsextension)->update($update_redemption);
    }
    if ($doUpdate) {
        $add_result = 1;
        $update = ["msetupfee" => $msetupfee_add, "qsetupfee" => $qsetupfee_add, "ssetupfee" => $ssetupfee_add, "asetupfee" => $asetupfee_add, "bsetupfee" => $bsetupfee_add, "monthly" => $monthly_add, "quarterly" => $quarterly_add, "semiannually" => $semiannually_add, "annually" => $annually_add, "biennially" => $biennially_add];
        $result_update = Illuminate\Database\Capsule\Manager::table("tblpricing")->where("relid", "=", $isrelid)->where("type", "=", "domainregister")->where("currency", "=", $currency)->where("tsetupfee", "=", round($filtered))->update($update);
        if (!empty($promotable)) {
            foreach ($promotable as $promodata) {
                $relid = $promodata["relid"];
                $type = $promodata["type"];
                $promoprice = $promodata["promoprice"];
                $extension = $promodata["extension"];
                if ($relid == $isrelid && $type == "domainregister") {
                    $update = ["sellingprice" => $msetupfee_add];
                    $result_update = Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodspromo")->where("relid", "=", $relid)->where("type", "=", $type)->update($update);
                    $table = "tblpricing";
                    $data = Illuminate\Database\Capsule\Manager::table($table)->where("relid", "=", $relid)->where("currency", "=", $currency)->where("type", "=", $type)->where("tsetupfee", "=", 0)->select("msetupfee", "qsetupfee", "ssetupfee", "asetupfee", "bsetupfee", "monthly", "quarterly", "semiannually", "annually", "biennially")->get();
                    $msetupfee = $promoprice;
                    if ("0.00" < $data[0]->qsetupfee) {
                        $qsetupfee = $data[0]->qsetupfee / 2 + $promoprice;
                    } else {
                        $qsetupfee = "-1.00";
                    }
                    if ("0.00" < $data[0]->ssetupfee) {
                        $ssetupfee = $data[0]->ssetupfee / 3 * 2 + $promoprice;
                    } else {
                        $ssetupfee = "-1.00";
                    }
                    if ("0.00" < $data[0]->asetupfee) {
                        $asetupfee = $data[0]->asetupfee / 4 * 3 + $promoprice;
                    } else {
                        $asetupfee = "-1.00";
                    }
                    if ("0.00" < $data[0]->bsetupfee) {
                        $bsetupfee = $data[0]->bsetupfee / 5 * 4 + $promoprice;
                    } else {
                        $bsetupfee = "-1.00";
                    }
                    if ("0.00" < $data[0]->monthly) {
                        $monthly = $data[0]->monthly / 6 * 5 + $promoprice;
                    } else {
                        $monthly = "-1.00";
                    }
                    if ("0.00" < $data[0]->quarterly) {
                        $quarterly = $data[0]->quarterly / 7 * 6 + $promoprice;
                    } else {
                        $quarterly = "-1.00";
                    }
                    if ("0.00" < $data[0]->semiannually) {
                        $semiannually = $data[0]->semiannually / 8 * 7 + $promoprice;
                    } else {
                        $semiannually = "-1.00";
                    }
                    if ("0.00" < $data[0]->annually) {
                        $annually = $data[0]->annually / 9 * 8 + $promoprice;
                    } else {
                        $annually = "-1.00";
                    }
                    if ("0.00" < $data[0]->biennially) {
                        $biennially = $data[0]->biennially / 10 * 9 + $promoprice;
                    } else {
                        $biennially = "-1.00";
                    }
                    if ($max_reg_array[$extension]) {
                        if ($max_reg_array[$extension][1] == 5) {
                            $update = ["msetupfee" => $msetupfee, "qsetupfee" => $qsetupfee, "ssetupfee" => $ssetupfee, "asetupfee" => $asetupfee, "bsetupfee" => $bsetupfee];
                        } else if ($max_reg_array[$extension][1] == 2) {
                            $update = ["msetupfee" => $msetupfee, "qsetupfee" => $qsetupfee];
                        } else {
                            $update = ["msetupfee" => $msetupfee];
                        }
                    } else {
                        $update = ["msetupfee" => $msetupfee, "qsetupfee" => $qsetupfee, "ssetupfee" => $ssetupfee, "asetupfee" => $asetupfee, "bsetupfee" => $bsetupfee, "monthly" => $monthly, "quarterly" => $quarterly, "semiannually" => $semiannually, "annually" => $annually, "biennially" => $biennially];
                    }
                    $result_update = Illuminate\Database\Capsule\Manager::table($table)->where("relid", "=", $relid)->where("currency", "=", $currency)->where("type", "=", $type)->where("tsetupfee", "=", 0)->update($update);
                }
            }
        }
    } else {
        $id = "";
        $type = "domainregister";
        $tsetupfee = round($filtered);
        $triennially_add = 0;
        $values = ["id" => $id, "type" => $type, "currency" => $currency, "relid" => $relid, "msetupfee" => $msetupfee_add, "qsetupfee" => $qsetupfee_add, "ssetupfee" => $ssetupfee_add, "asetupfee" => $asetupfee_add, "bsetupfee" => $bsetupfee_add, "tsetupfee" => $tsetupfee, "monthly" => $monthly_add, "quarterly" => $quarterly_add, "semiannually" => $semiannually_add, "annually" => $annually_add, "biennially" => $biennially_add, "triennially" => $triennially_add];
        Illuminate\Database\Capsule\Manager::table("tblpricing")->insert($values);
        $add_result = 1;
    }
    $mplicator_ren = 1;
    foreach ($newproductprice_array as $ren_key => $ren_values) {
        if ($ren_key == $whmcsextension) {
            ksort($ren_values["renewdomain"]);
            $tldpointer = $ren_key;
            if ($ren_key == $minmax_ren_array[$ren_key][0]) {
                $renk = $billingslabs[$minmax_ren_array[$ren_key][1]];
                if ($is_costimport) {
                    $dbren_array[$renk] = ($renfee + $renincrease) * $minmax_ren_array[$ren_key][1];
                } else if ($is_costpercentimport) {
                    $dbren_array[$renk] = ($renfee / 100 * $renpercentincrease + $renfee) * $minmax_ren_array[$ren_key][1];
                } else {
                    $dbren_array[$renk] = ($ren_values["renewdomain"][$minmax_ren_array[$ren_key][1]] + $increase) * $minmax_ren_array[$ren_key][1];
                }
            } else {
                foreach ($ren_values["renewdomain"] as $k => $v) {
                    if ($k == 1) {
                        $renk = "msetupfee";
                    }
                    if ($k == 2) {
                        $renk = "qsetupfee";
                    }
                    if ($k == 3) {
                        $renk = "ssetupfee";
                    }
                    if ($k == 4) {
                        $renk = "asetupfee";
                    }
                    if ($k == 5) {
                        $renk = "bsetupfee";
                    }
                    if ($k == 6) {
                        $renk = "monthly";
                    }
                    if ($k == 7) {
                        $renk = "quarterly";
                    }
                    if ($k == 8) {
                        $renk = "semiannually";
                    }
                    if ($k == 9) {
                        $renk = "annually";
                    }
                    if ($k == 10) {
                        $renk = "biennially";
                    }
                    if ($is_costimport) {
                        $dbren_array[$renk] = ($renfee + $renincrease) * $multiplicator * $mplicator_ren++;
                    } else if ($is_costpercentimport) {
                        $dbren_array[$renk] = ($renfee / 100 * $renpercentincrease + $renfee) * $multiplicator * $mplicator_ren++;
                    } else {
                        $dbren_array[$renk] = ($v + $increase) * $multiplicator * $mplicator_ren++;
                    }
                }
            }
        }
    }
    if ($_POST["telescope"] == 1) {
        if (!isset($dbren_array["msetupfee"])) {
            $msetupfee_ren = "-1";
        } else {
            $msetupfee_ren = $dbren_array["msetupfee"];
        }
        $qsetupfee_ren = "-1";
        $ssetupfee_ren = "-1";
        $asetupfee_ren = "-1";
        $bsetupfee_ren = "-1";
        $monthly_ren = "-1";
        $quarterly_ren = "-1";
        $semiannually_ren = "-1";
        $annually_ren = "-1";
        $biennially_ren = "-1";
    } else {
        if (!isset($dbren_array["msetupfee"]) || $dbren_array["msetupfee"] == "0.00") {
            $msetupfee_ren = "-1";
        } else {
            $msetupfee_ren = $dbren_array["msetupfee"];
        }
        if (!isset($dbren_array["qsetupfee"]) || $dbren_array["qsetupfee"] == "0.00") {
            $qsetupfee_ren = "-1";
        } else {
            $qsetupfee_ren = $dbren_array["qsetupfee"];
        }
        if (!isset($dbren_array["ssetupfee"]) || $dbren_array["ssetupfee"] == "0.00") {
            $ssetupfee_ren = "-1";
        } else {
            $ssetupfee_ren = $dbren_array["ssetupfee"];
        }
        if (!isset($dbren_array["asetupfee"]) || $dbren_array["asetupfee"] == "0.00") {
            $asetupfee_ren = "-1";
        } else {
            $asetupfee_ren = $dbren_array["asetupfee"];
        }
        if (!isset($dbren_array["bsetupfee"]) || $dbren_array["bsetupfee"] == "0.00") {
            $bsetupfee_ren = "-1";
        } else {
            $bsetupfee_ren = $dbren_array["bsetupfee"];
        }
        if (!isset($dbren_array["monthly"]) || $dbren_array["monthly"] == "0.00") {
            $monthly_ren = "-1";
        } else {
            $monthly_ren = $dbren_array["monthly"];
        }
        if (!isset($dbren_array["quarterly"]) || $dbren_array["quarterly"] == "0.00") {
            $quarterly_ren = "-1";
        } else {
            $quarterly_ren = $dbren_array["quarterly"];
        }
        if (!isset($dbren_array["semiannually"]) || $dbren_array["semiannually"] == "0.00") {
            $semiannually_ren = "-1";
        } else {
            $semiannually_ren = $dbren_array["semiannually"];
        }
        if (!isset($dbren_array["annually"]) || $dbren_array["annually"] == "0.00") {
            $annually_ren = "-1";
        } else {
            $annually_ren = $dbren_array["annually"];
        }
        if (!isset($dbren_array["biennially"]) || $dbren_array["biennially"] == "0.00") {
            $biennially_ren = "-1";
        } else {
            $biennially_ren = $dbren_array["biennially"];
        }
    }
    if ($currencyswitch == "on") {
        if ($msetupfee_ren != "-1") {
            $msetupfee_ren = $msetupfee_ren / $currencyrate;
        }
        if ($qsetupfee_ren != "-1") {
            $qsetupfee_ren = $qsetupfee_ren / $currencyrate;
        }
        if ($ssetupfee_ren != "-1") {
            $ssetupfee_ren = $ssetupfee_ren / $currencyrate;
        }
        if ($asetupfee_ren != "-1") {
            $asetupfee_ren = $asetupfee_ren / $currencyrate;
        }
        if ($bsetupfee_ren != "-1") {
            $bsetupfee_ren = $bsetupfee_ren / $currencyrate;
        }
        if ($monthly_ren != "-1") {
            $monthly_ren = $monthly_ren / $currencyrate;
        }
        if ($quarterly_ren != "-1") {
            $quarterly_ren = $quarterly_ren / $currencyrate;
        }
        if ($semiannually_ren != "-1") {
            $semiannually_ren = $semiannually_ren / $currencyrate;
        }
        if ($annually_ren != "-1") {
            $annually_ren = $annually_ren / $currencyrate;
        }
        if ($biennially_ren != "-1") {
            $biennially_ren = $biennially_ren / $currencyrate;
        }
    }
    if ($doUpdate) {
        $ren_result = 1;
        $update = ["msetupfee" => $msetupfee_ren, "qsetupfee" => $qsetupfee_ren, "ssetupfee" => $ssetupfee_ren, "asetupfee" => $asetupfee_ren, "bsetupfee" => $bsetupfee_ren, "monthly" => $monthly_ren, "quarterly" => $quarterly_ren, "semiannually" => $semiannually_ren, "annually" => $annually_ren, "biennially" => $biennially_ren];
        $result_update = Illuminate\Database\Capsule\Manager::table("tblpricing")->where("relid", "=", $isrelid)->where("type", "=", "domainrenew")->where("currency", "=", $currency)->where("tsetupfee", "=", round($filtered))->update($update);
        if (!empty($promotable)) {
            foreach ($promotable as $promodata) {
                $relid = $promodata["relid"];
                $type = $promodata["type"];
                $promoprice = $promodata["promoprice"];
                $extension = $promodata["extension"];
                if ($relid == $isrelid && $type == "domainrenew") {
                    $update = ["sellingprice" => $msetupfee_ren];
                    $result_update = Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodspromo")->where("relid", "=", $relid)->where("type", "=", $type)->update($update);
                    $table = "tblpricing";
                    $data = Illuminate\Database\Capsule\Manager::table($table)->where("relid", "=", $relid)->where("currency", "=", $currency)->where("type", "=", $type)->where("tsetupfee", "=", 0)->select("msetupfee", "qsetupfee", "ssetupfee", "asetupfee", "bsetupfee", "monthly", "quarterly", "semiannually", "annually", "biennially")->get();
                    $msetupfee = $promoprice;
                    if ("0.00" < $data[0]->qsetupfee) {
                        $qsetupfee = $data[0]->qsetupfee / 2 + $promoprice;
                    } else {
                        $qsetupfee = "-1.00";
                    }
                    if ("0.00" < $data[0]->ssetupfee) {
                        $ssetupfee = $data[0]->ssetupfee / 3 * 2 + $promoprice;
                    } else {
                        $ssetupfee = "-1.00";
                    }
                    if ("0.00" < $data[0]->asetupfee) {
                        $asetupfee = $data[0]->asetupfee / 4 * 3 + $promoprice;
                    } else {
                        $asetupfee = "-1.00";
                    }
                    if ("0.00" < $data[0]->bsetupfee) {
                        $bsetupfee = $data[0]->bsetupfee / 5 * 4 + $promoprice;
                    } else {
                        $bsetupfee = "-1.00";
                    }
                    if ("0.00" < $data[0]->monthly) {
                        $monthly = $data[0]->monthly / 6 * 5 + $promoprice;
                    } else {
                        $monthly = "-1.00";
                    }
                    if ("0.00" < $data[0]->quarterly) {
                        $quarterly = $data[0]->quarterly / 7 * 6 + $promoprice;
                    } else {
                        $quarterly = "-1.00";
                    }
                    if ("0.00" < $data[0]->semiannually) {
                        $semiannually = $data[0]->semiannually / 8 * 7 + $promoprice;
                    } else {
                        $semiannually = "-1.00";
                    }
                    if ("0.00" < $data[0]->annually) {
                        $annually = $data[0]->annually / 9 * 8 + $promoprice;
                    } else {
                        $annually = "-1.00";
                    }
                    if ("0.00" < $data[0]->biennially) {
                        $biennially = $data[0]->biennially / 10 * 9 + $promoprice;
                    } else {
                        $biennially = "-1.00";
                    }
                    if ($max_reg_array[$extension]) {
                        if ($max_reg_array[$extension][1] == 5) {
                            $update = ["msetupfee" => $msetupfee, "qsetupfee" => $qsetupfee, "ssetupfee" => $ssetupfee, "asetupfee" => $asetupfee, "bsetupfee" => $bsetupfee];
                        } else if ($max_reg_array[$extension][1] == 2) {
                            $update = ["msetupfee" => $msetupfee, "qsetupfee" => $qsetupfee];
                        } else {
                            $update = ["msetupfee" => $msetupfee];
                        }
                    } else {
                        $update = ["msetupfee" => $msetupfee, "qsetupfee" => $qsetupfee, "ssetupfee" => $ssetupfee, "asetupfee" => $asetupfee, "bsetupfee" => $bsetupfee, "monthly" => $monthly, "quarterly" => $quarterly, "semiannually" => $semiannually, "annually" => $annually, "biennially" => $biennially];
                    }
                    $result_update = Illuminate\Database\Capsule\Manager::table($table)->where("relid", "=", $relid)->where("currency", "=", $currency)->where("type", "=", $type)->where("tsetupfee", "=", 0)->update($update);
                }
            }
        }
    } else {
        $id = "";
        $type = "domainrenew";
        $tsetupfee = round($filtered);
        $triennially_ren = 0;
        $values = ["id" => $id, "type" => $type, "currency" => $currency, "relid" => $relid, "msetupfee" => $msetupfee_ren, "qsetupfee" => $qsetupfee_ren, "ssetupfee" => $ssetupfee_ren, "asetupfee" => $asetupfee_ren, "bsetupfee" => $bsetupfee_ren, "tsetupfee" => $tsetupfee, "monthly" => $monthly_ren, "quarterly" => $quarterly_ren, "semiannually" => $semiannually_ren, "annually" => $annually_ren, "biennially" => $biennially_ren, "triennially" => $triennially_ren];
        Illuminate\Database\Capsule\Manager::table("tblpricing")->insert($values);
        $ren_result = 1;
    }
    $mplicator_tra = 1;
    foreach ($newproductprice_array as $tra_key => $tra_values) {
        if ($tra_key == $whmcsextension) {
            $tldpointer = $tra_key;
            $tra_key_tmp = preg_replace("/./", "", $tra_key, 1);
            if (in_array($tra_key_tmp, $transferfree_tlds_array)) {
                if ($tra_key == $minmax_reg_array[$tra_key][0]) {
                    $trak = $billingslabs[$minmax_reg_array[$tra_key][1]];
                    $dbtra_array[$trak] = "0.00";
                } else {
                    $trak = "msetupfee";
                    $dbtra_array[$trak] = "0.00";
                }
            } else {
                $trak = "msetupfee";
                if ($is_costimport) {
                    $dbtra_array[$trak] = ($trafee + $traincrease) * $multiplicator * $mplicator_tra++;
                } else if ($is_costpercentimport) {
                    $dbtra_array[$trak] = ($trafee / 100 * $trapercentincrease + $trafee) * $multiplicator * $mplicator_tra++;
                } else {
                    $dbtra_array[$trak] = ($tra_values["addtransferdomain"][1] + $increase) * $multiplicator * $mplicator_tra++;
                }
            }
        }
    }
    if (!isset($dbtra_array["msetupfee"])) {
        $msetupfee_tra = "-1";
    } else {
        $msetupfee_tra = $dbtra_array["msetupfee"];
    }
    if (!isset($dbtra_array["qsetupfee"])) {
        $qsetupfee_tra = "-1";
    } else {
        $qsetupfee_tra = $dbtra_array["qsetupfee"];
    }
    if (!isset($dbtra_array["ssetupfee"])) {
        $ssetupfee_tra = "-1";
    } else {
        $ssetupfee_tra = $dbtra_array["ssetupfee"];
    }
    if (!isset($dbtra_array["asetupfee"])) {
        $asetupfee_tra = "-1";
    } else {
        $asetupfee_tra = $dbtra_array["asetupfee"];
    }
    if (!isset($dbtra_array["bsetupfee"])) {
        $bsetupfee_tra = "-1";
    } else {
        $bsetupfee_tra = $dbtra_array["bsetupfee"];
    }
    if (!isset($dbtra_array["monthly"])) {
        $monthly_tra = "-1";
    } else {
        $monthly_tra = $dbtra_array["monthly"];
    }
    if (!isset($dbtra_array["quarterly"])) {
        $quarterly_tra = "-1";
    } else {
        $quarterly_tra = $dbtra_array["quarterly"];
    }
    if (!isset($dbtra_array["semiannually"])) {
        $semiannually_tra = "-1";
    } else {
        $semiannually_tra = $dbtra_array["semiannually"];
    }
    if (!isset($dbtra_array["annually"])) {
        $annually_tra = "-1";
    } else {
        $annually_tra = $dbtra_array["annually"];
    }
    if (!isset($dbtra_array["biennially"])) {
        $biennially_tra = "-1";
    } else {
        $biennially_tra = $dbtra_array["biennially"];
    }
    if ($currencyswitch == "on") {
        if ($msetupfee_tra != "-1") {
            $msetupfee_tra = $msetupfee_tra / $currencyrate;
        }
        if ($qsetupfee_tra != "-1") {
            $qsetupfee_tra = $qsetupfee_tra / $currencyrate;
        }
        if ($ssetupfee_tra != "-1") {
            $ssetupfee_tra = $ssetupfee_tra / $currencyrate;
        }
        if ($asetupfee_tra != "-1") {
            $asetupfee_tra = $asetupfee_tra / $currencyrate;
        }
        if ($bsetupfee_tra != "-1") {
            $bsetupfee_tra = $bsetupfee_tra / $currencyrate;
        }
        if ($monthly_tra != "-1") {
            $monthly_tra = $monthly_tra / $currencyrate;
        }
        if ($quarterly_tra != "-1") {
            $quarterly_tra = $quarterly_tra / $currencyrate;
        }
        if ($semiannually_tra != "-1") {
            $semiannually_tra = $semiannually_tra / $currencyrate;
        }
        if ($annually_tra != "-1") {
            $annually_tra = $annually_tra / $currencyrate;
        }
        if ($biennially_tra != "-1") {
            $biennially_tra = $biennially_tra / $currencyrate;
        }
    }
    if ($doUpdate) {
        $tra_result = 1;
        $update = ["msetupfee" => $msetupfee_tra, "qsetupfee" => $qsetupfee_tra, "ssetupfee" => $ssetupfee_tra, "asetupfee" => $asetupfee_tra, "bsetupfee" => $bsetupfee_tra, "monthly" => $monthly_tra, "quarterly" => $quarterly_tra, "semiannually" => $semiannually_tra, "annually" => $annually_tra, "biennially" => $biennially_tra];
        $result_update = Illuminate\Database\Capsule\Manager::table("tblpricing")->where("relid", "=", $isrelid)->where("type", "=", "domaintransfer")->where("currency", "=", $currency)->where("tsetupfee", "=", round($filtered))->update($update);
        if (!empty($promotable)) {
            foreach ($promotable as $promodata) {
                $relid = $promodata["relid"];
                $type = $promodata["type"];
                $promoprice = $promodata["promoprice"];
                $extension = $promodata["extension"];
                if ($relid == $isrelid && $type == "domaintransfer") {
                    $update = ["sellingprice" => $msetupfee_tra];
                    $result_update = Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodspromo")->where("relid", "=", $relid)->where("type", "=", $type)->update($update);
                    $table = "tblpricing";
                    $data = Illuminate\Database\Capsule\Manager::table($table)->where("relid", "=", $relid)->where("currency", "=", $currency)->where("type", "=", $type)->where("tsetupfee", "=", 0)->select("msetupfee", "qsetupfee", "ssetupfee", "asetupfee", "bsetupfee", "monthly", "quarterly", "semiannually", "annually", "biennially")->get();
                    $msetupfee = $promoprice;
                    if ("0.00" < $data[0]->qsetupfee) {
                        $qsetupfee = $data[0]->qsetupfee / 2 + $promoprice;
                    } else {
                        $qsetupfee = "-1.00";
                    }
                    if ("0.00" < $data[0]->ssetupfee) {
                        $ssetupfee = $data[0]->ssetupfee / 3 * 2 + $promoprice;
                    } else {
                        $ssetupfee = "-1.00";
                    }
                    if ("0.00" < $data[0]->asetupfee) {
                        $asetupfee = $data[0]->asetupfee / 4 * 3 + $promoprice;
                    } else {
                        $asetupfee = "-1.00";
                    }
                    if ("0.00" < $data[0]->bsetupfee) {
                        $bsetupfee = $data[0]->bsetupfee / 5 * 4 + $promoprice;
                    } else {
                        $bsetupfee = "-1.00";
                    }
                    if ("0.00" < $data[0]->monthly) {
                        $monthly = $data[0]->monthly / 6 * 5 + $promoprice;
                    } else {
                        $monthly = "-1.00";
                    }
                    if ("0.00" < $data[0]->quarterly) {
                        $quarterly = $data[0]->quarterly / 7 * 6 + $promoprice;
                    } else {
                        $quarterly = "-1.00";
                    }
                    if ("0.00" < $data[0]->semiannually) {
                        $semiannually = $data[0]->semiannually / 8 * 7 + $promoprice;
                    } else {
                        $semiannually = "-1.00";
                    }
                    if ("0.00" < $data[0]->annually) {
                        $annually = $data[0]->annually / 9 * 8 + $promoprice;
                    } else {
                        $annually = "-1.00";
                    }
                    if ("0.00" < $data[0]->biennially) {
                        $biennially = $data[0]->biennially / 10 * 9 + $promoprice;
                    } else {
                        $biennially = "-1.00";
                    }
                    if ($max_reg_array[$extension]) {
                        if ($max_reg_array[$extension][1] == 5) {
                            $update = ["msetupfee" => $msetupfee, "qsetupfee" => $qsetupfee, "ssetupfee" => $ssetupfee, "asetupfee" => $asetupfee, "bsetupfee" => $bsetupfee];
                        } else if ($max_reg_array[$extension][1] == 2) {
                            $update = ["msetupfee" => $msetupfee, "qsetupfee" => $qsetupfee];
                        } else {
                            $update = ["msetupfee" => $msetupfee];
                        }
                    } else {
                        $update = ["msetupfee" => $msetupfee, "qsetupfee" => $qsetupfee, "ssetupfee" => $ssetupfee, "asetupfee" => $asetupfee, "bsetupfee" => $bsetupfee, "monthly" => $monthly, "quarterly" => $quarterly, "semiannually" => $semiannually, "annually" => $annually, "biennially" => $biennially];
                    }
                    $result_update = Illuminate\Database\Capsule\Manager::table($table)->where("relid", "=", $relid)->where("currency", "=", $currency)->where("type", "=", $type)->where("tsetupfee", "=", 0)->update($update);
                }
            }
        }
    } else {
        $id = "";
        $type = "domaintransfer";
        $tsetupfee = round($filtered);
        $triennially_tra = 0;
        $values = ["id" => $id, "type" => $type, "currency" => $currency, "relid" => $relid, "msetupfee" => $msetupfee_tra, "qsetupfee" => $qsetupfee_tra, "ssetupfee" => $ssetupfee_tra, "asetupfee" => $asetupfee_tra, "bsetupfee" => $bsetupfee_tra, "tsetupfee" => $tsetupfee, "monthly" => $monthly_tra, "quarterly" => $quarterly_tra, "semiannually" => $semiannually_tra, "annually" => $annually_tra, "biennially" => $biennially_tra, "triennially" => $triennially_tra];
        Illuminate\Database\Capsule\Manager::table("tblpricing")->insert($values);
        $tra_result = 1;
    }
    if ($currencydoupd == "true") {
        domainCurrencyPricingupdate($clientgroupid, 1, $domtype = "", $isrelid);
    }
}
if (1 <= count($foreigncurrencies)) {
    $currencydoupd_checked = "";
    if ($is_currencydoupd != "on") {
        $currencydoupd_checked = "checked=\"checked\"";
    }
    $currenciecodes = implode(", ", $foreigncurrencies);
    $doCurrencyupd = "<input type=\"checkbox\" name=\"currencydoupd\" value=\"true\" " . $currencydoupd_checked . " /> " . $LANG["updateallothercurrencytitle"] . " " . $currenciecodes;
} else {
    $doCurrencyupd = "";
}
if (version_compare(getWver(), "7.5.0", ">=")) {
    $redemption_checked = "";
    if ($is_redemption == "on") {
        $redemption_checked = "checked=\"checked\"";
    }
    $redemption_form = "<input type=\"checkbox\" name=\"redemptionform\" value=\"true\" " . $redemption_checked . " /> " . $LANG["updateallredemptiontitle"];
} else {
    $redemption_form = "";
}
$telescope_checked = "";
if ($is_domaintelescope == "on") {
    $telescope_checked = "checked=\"checked\"";
}
$bulktelescope = "<input type=\"checkbox\" name=\"telescope\" value=\"1\" " . $telescope_checked . " /> " . $LANG["disabletelescopepricing"];
$incompletetlds = [];
$activetlds = [];
$whmcs_tldarray = [];
foreach (Illuminate\Database\Capsule\Manager::table("tbldomainpricing")->get() as $data) {
    $count_tlds = Illuminate\Database\Capsule\Manager::table("tblpricing")->where("relid", "=", $data->id)->where("type", "=", "domainregister")->where("currency", "=", $currency)->where("tsetupfee", "=", round($filtered))->count();
    if ($count_tlds == 0) {
        $incompletetlds[] = $data->extension;
    } else {
        $activetlds[] = $data->extension;
    }
    $tldextension = $data->extension;
    $dnsmanagement = $data->dnsmanagement;
    if ($dnsmanagement == "0" || empty($dnsmanagement)) {
        $dnsmanagement = "";
    }
    $emailforwarding = $data->emailforwarding;
    if ($emailforwarding == "0" || empty($emailforwarding)) {
        $emailforwarding = "";
    }
    $idprotection = $data->idprotection;
    if ($idprotection == "0" || empty($idprotection)) {
        $idprotection = "";
    }
    $eppcode = $data->eppcode;
    if ($eppcode == "0" || empty($eppcode)) {
        $eppcode = "";
    }
    $autoreg = $data->autoreg;
    $order = $data->order;
    $idtld = $data->id;
    $redemption_fee = $data->redemption_grace_period_fee;
    $redemption_days = $data->redemption_grace_period;
    $whmcs_tldarray[] = ["tld" => [$tldextension], "dnsmanagement" => $dnsmanagement, "emailforwarding" => $emailforwarding, "idprotection" => $idprotection, "eppcode" => $eppcode, "autoreg" => $autoreg, "order" => $order, "id" => $idtld, "redemptionfee" => $redemption_fee, "redemptiondays" => $redemption_days];
}
$activetldlinks = "";
$incompletelinks = "";
$inactivetldlinks = "";
$rcAllTlds = [];
if (!empty($rc_tldarray) && !empty($rc_tldarray[0]["tld"]) && is_array($rc_tldarray[0]["tld"])) {
    $rcAllTlds = $rc_tldarray[0]["tld"];
}
$rcTldSet = $rcAllTlds ? array_flip($rcAllTlds) : [];
$rcIndex = [];
$rcOrder = 0;
$sortedWhmcs = $whmcs_tldarray;
if (!empty($sortedWhmcs)) {
    array_multisort($sortedWhmcs, SORT_ASC);
    foreach ($sortedWhmcs as $whmcs_key) {
        foreach ($whmcs_key["tld"] as $whmcs_value) {
        }
        if (!isset($rcTldSet[$whmcs_value])) {
        } else {
            $withDot = $whmcs_value;
            $noDot = ltrim($whmcs_value, ".");
            if (!isset($rcIndex[$withDot])) {
                $rcIndex[$withDot] = $rcOrder;
                $rcIndex[$noDot] = $rcOrder;
                $rcOrder++;
            }
        }
    }
}
$linksPerPage = $pagination_tlds;
asort($links_array);
foreach ($links_array as $links) {
    $decoded = $IDN->decode($links);
    $decodedNoDot = ltrim($decoded, ".");
    $orderIndex = NULL;
    if (isset($rcIndex[$decoded])) {
        $orderIndex = $rcIndex[$decoded];
    } else if (isset($rcIndex[$decodedNoDot])) {
        $orderIndex = $rcIndex[$decodedNoDot];
    }
    if ($orderIndex !== NULL && 0 < $linksPerPage) {
        $targetPage = (int) floor($orderIndex / $linksPerPage) + 1;
    } else {
        $targetPage = 1;
    }
    $qs = $_GET;
    $qs["page"] = $targetPage;
    $href = $_SERVER["PHP_SELF"] . "?" . http_build_query($qs) . "#" . $decoded;
    if (in_array($decoded, $incompletetlds, true)) {
        $incompletelinks .= "<a href=\"" . $href . "\"" . " class=\"rcm-tld-link\"" . " data-tld=\"" . htmlspecialchars($decoded, ENT_QUOTES, "UTF-8") . "\"" . ">" . $decoded . "</a> | ";
    } else if (in_array($decoded, $activetlds, true)) {
        $activetldlinks .= "<a href=\"" . $href . "\"" . " class=\"rcm-tld-link\"" . " data-tld=\"" . htmlspecialchars($decoded, ENT_QUOTES, "UTF-8") . "\"" . ">" . $decoded . "</a> | ";
    } else {
        $inactivetldlinks .= "<a href=\"#" . $decoded . "\"" . " class=\"rcm-tld-link\"" . " data-tld=\"" . htmlspecialchars($decoded, ENT_QUOTES, "UTF-8") . "\"" . ">" . $decoded . "</a> | ";
    }
}
if (!empty($is_domainsynctlds)) {
    $is_domainsynctlds_tlds = "<span>" . $LANG["onlytldlabel"] . "&nbsp;</span><span style=\"color:#46A546;\">" . str_replace(",", " ", $is_domainsynctlds) . "</span>";
} else if (!empty($is_domainsyncexcludetlds)) {
    $is_domainsynctlds_tlds = "<span>" . $LANG["alltldlabel"] . "&nbsp;" . $LANG["excludetldlabel"] . "</span><span style=\"color:#cc0000;\">&nbsp;" . str_replace(",", " ", $is_domainsyncexcludetlds) . "</span>";
} else {
    $is_domainsynctlds_tlds = "<span>" . $LANG["alltldlabel"] . "</span>";
}
if (empty($is_domaintelescope)) {
    $is_domaintelescope_tlds = "<span class=\"label active\">" . $LANG["telescopeenabled"] . "</span>";
} else {
    $is_domaintelescope_tlds = "<span class=\"label pending\">" . $LANG["telescopedisabled"] . "</span>";
}
if ($activetldlinks || $incompletelinks) {
    echo "<div style=\"padding: 5px 20px;border: 1px solid #CCCCCC;-moz-border-radius: 5px;-webkit-border-radius: 5px;-o-border-radius: 5px;border-radius: 5px;\">";
    echo "<br /><h3 style=\"border-bottom: 1px solid #cccccc;\"><strong>" . $LANG["manualbulkimporttitle"] . "</strong></h3>";
    echo "<div style=\"margin-right:3px;\">" . $LANG["greenyellowdomainsdesc"] . "<br /><br />" . $LANG["greenyellowdomainsdesc2"] . "</div><br />";
    echo "<div style=\"width:auto;border:1px solid #cccccc;background-color:#f5f5f5;padding:0px 2px 1px 2px;font-weight:bold;float:left;margin-bottom:5px;\">" . $is_domainsynctlds_tlds . "</div><br /><br />";
    echo "<div style=\"padding:3px 0px 5px 0px;\"><form method=\"post\" action=\"../modules/addons/resellerclubmods_tools/cron/resellerclubmods_dompricesync.php\"><input type=\"hidden\" name=\"dobulkupdate\" value=\"true\"/>";
    echo "<input type=\"hidden\" name=\"clientgroup\" value=\"" . $filtered . "\"/>";
    echo "<input type=\"hidden\" name=\"id\" value=\"" . $rcauth_userid . "\"/>";
    echo "<div style=\"padding:5px 0px 0px 0px;\"><p>" . $LANG["bulktooloptions"] . "</p>" . $bulktelescope . "<br />" . $doCurrencyupd . "<br />" . $redemption_form . "&nbsp;</div><p><input type=\"submit\" class=\"btn btn-success\" value=\"" . $LANG["greenyellowbulkbutton"] . "\" /></p><br />";
    echo "</form></div>";
    if ($activetldlinks) {
        echo "<h3 ...><strong>" . $LANG["manageactive"] . "</strong></h3>";
        echo "<div class=\"alert alert-success\">";
        echo $activetldlinks;
        echo "</div><br />";
    }
    if ($incompletelinks) {
        echo "<h3 ...><strong>" . $LANG["manageincomplete"] . "</strong></h3>";
        echo "<div class=\"alert alert-warning\">";
        echo $incompletelinks;
        echo "</div><br />";
    }
    echo "</div><br /><br />";
}
if ($inactivetldlinks && !isset($_POST["tldbulksetup"])) {
    echo "<div style=\"padding: 5px 20px;border: 1px solid #CCCCCC;-moz-border-radius: 5px;-webkit-border-radius: 5px;-o-border-radius: 5px;border-radius: 5px;\">";
    echo "<br /><h3 style=\"border-bottom: 1px solid #cccccc;\"><strong>" . $LANG["bulktldsetuptitle"] . "</strong></h3>";
    echo "<div class=\"alert alert-info\"><p>" . $LANG["reddomainsdesc1"] . "</p></div>";
    echo "<p>" . $LANG["reddomainsdesc"] . "</p>";
    echo "<form method=\"post\" action=\"" . $_SERVER["REQUEST_URI"] . "#tldbulksetup\">";
    echo "<input type=\"hidden\" name=\"tldbulksetup\" value=\"true\"/>";
    echo "<input type=\"hidden\" name=\"clientgroup\" value=\"" . $filtered . "\">";
    echo "<div style=\"padding:5px 0px 5px 0px;\"><input type=\"submit\" class=\"btn btn-primary\" value=\"" . $LANG["redbulkbutton"] . "\" " . $disable_button . " /></div><br />";
    echo "</form>";
    echo "<h3 style=\"border-bottom: 1px solid #cccccc;\"><strong>" . $LANG["setupnewtlds"] . "</strong></h3>";
    echo "<div class=\"alert alert-danger\">";
    echo $inactivetldlinks;
    echo "</div><br /></div><br />";
}
$showme1 = 1001;
$divboxes1 = 1001;
$showme2 = 2001;
$divboxes2 = 2001;
$showme3 = 3001;
$divboxes3 = 3001;
array_multisort($whmcs_tldarray, SORT_ASC);
$result = Illuminate\Database\Capsule\Manager::table("tblcurrencies")->where("code", "=", $reseller_buycurrency)->select("rate")->get();
$buycurrencyrate = $result[0]->rate;
$checkarray = [];
$rcAllTlds = [];
if (!empty($rc_tldarray) && !empty($rc_tldarray[0]["tld"]) && is_array($rc_tldarray[0]["tld"])) {
    $rcAllTlds = $rc_tldarray[0]["tld"];
}
$rcTldSet = $rcAllTlds ? array_flip($rcAllTlds) : [];
$pricingRows = Illuminate\Database\Capsule\Manager::table("tblpricing")->whereIn("type", ["domainregister", "domainrenew", "domaintransfer"])->where("currency", "=", $currency)->where("tsetupfee", "=", round($filtered))->select("relid", "type", "msetupfee", "qsetupfee", "ssetupfee", "asetupfee", "bsetupfee", "monthly", "quarterly", "semiannually", "annually", "biennially")->get();
$pricingMap = [];
foreach ($pricingRows as $row) {
    if (!isset($pricingMap[$row->relid])) {
        $pricingMap[$row->relid] = [];
    }
    $pricingMap[$row->relid][$row->type] = $row;
}
$renderEntries = [];
foreach ($whmcs_tldarray as $idx => $whmcs_key) {
    foreach ($whmcs_key["tld"] as $whmcs_value) {
    }
    if (!isset($rcTldSet[$whmcs_value])) {
    } else {
        $checkarray[] = $whmcs_value;
        $renderEntries[] = $idx;
    }
}
$perPage = $pagination_tlds;
$totalRows = count($renderEntries);
$totalPages = max(1, (int) ceil($totalRows / $perPage));
$page = isset($_GET["page"]) ? (int) $_GET["page"] : 1;
if ($page < 1) {
    $page = 1;
}
if ($totalPages < $page) {
    $page = $totalPages;
}
$offset = ($page - 1) * $perPage;
echo "<p>Showing " . ($totalRows ? $offset + 1 : 0) . "–" . min($offset + $perPage, $totalRows) . " of " . $totalRows . " TLDs. Page " . $page . " of " . $totalPages . ".</p>";
if (1 < $totalPages) {
    $qsBase = $_GET;
    echo "<p>Pages: ";
    for ($i = 1; $i <= $totalPages; $i++) {
        $qsBase["page"] = $i;
        $link = $_SERVER["PHP_SELF"] . "?" . http_build_query($qsBase);
        if ($i === $page) {
            echo " <strong>" . $i . "</strong> ";
        } else {
            echo " <a href=\"" . htmlspecialchars($link, ENT_QUOTES, "UTF-8") . "\">" . $i . "</a> ";
        }
    }
    echo "</p>";
}
for ($ri = $offset; $ri < $offset + $perPage && $ri < $totalRows; $ri++) {
    $idx = $renderEntries[$ri];
    $whmcs_key = $whmcs_tldarray[$idx];
    foreach ($whmcs_key["tld"] as $whmcs_value) {
    }
    $tldid = $whmcs_key["id"];
    $regRow = $renRow = $traRow = NULL;
    if (isset($pricingMap[$tldid])) {
        $rows = $pricingMap[$tldid];
        $regRow = isset($rows["domainregister"]) ? $rows["domainregister"] : NULL;
        $renRow = isset($rows["domainrenew"]) ? $rows["domainrenew"] : NULL;
        $traRow = isset($rows["domaintransfer"]) ? $rows["domaintransfer"] : NULL;
    }
    $msetupfee_register = $qsetupfee_register = $ssetupfee_register = $asetupfee_register = $bsetupfee_register = "";
    $monthly_register = $quarterly_register = $semiannually_register = $annually_register = $biennially_register = "";
    $msetupfee_renew = $qsetupfee_renew = $ssetupfee_renew = $asetupfee_renew = $bsetupfee_renew = "";
    $monthly_renew = $quarterly_renew = $semiannually_renew = $annually_renew = $biennially_renew = "";
    $msetupfee_transfer = $qsetupfee_transfer = $ssetupfee_transfer = $asetupfee_transfer = $bsetupfee_transfer = "";
    $monthly_transfer = $quarterly_transfer = $semiannually_transfer = $annually_transfer = $biennially_transfer = "";
    if ($regRow) {
        $msetupfee_register = $regRow->msetupfee;
        $qsetupfee_register = $regRow->qsetupfee;
        $ssetupfee_register = $regRow->ssetupfee;
        $asetupfee_register = $regRow->asetupfee;
        $bsetupfee_register = $regRow->bsetupfee;
        $monthly_register = $regRow->monthly;
        $quarterly_register = $regRow->quarterly;
        $semiannually_register = $regRow->semiannually;
        $annually_register = $regRow->annually;
        $biennially_register = $regRow->biennially;
    }
    if ($renRow) {
        $msetupfee_renew = $renRow->msetupfee;
        $qsetupfee_renew = $renRow->qsetupfee;
        $ssetupfee_renew = $renRow->ssetupfee;
        $asetupfee_renew = $renRow->asetupfee;
        $bsetupfee_renew = $renRow->bsetupfee;
        $monthly_renew = $renRow->monthly;
        $quarterly_renew = $renRow->quarterly;
        $semiannually_renew = $renRow->semiannually;
        $annually_renew = $renRow->annually;
        $biennially_renew = $renRow->biennially;
    }
    if ($traRow) {
        $msetupfee_transfer = $traRow->msetupfee;
        $qsetupfee_transfer = $traRow->qsetupfee;
        $ssetupfee_transfer = $traRow->ssetupfee;
        $asetupfee_transfer = $traRow->asetupfee;
        $bsetupfee_transfer = $traRow->bsetupfee;
        $monthly_transfer = $traRow->monthly;
        $quarterly_transfer = $traRow->quarterly;
        $semiannually_transfer = $traRow->semiannually;
        $annually_transfer = $traRow->annually;
        $biennially_transfer = $traRow->biennially;
    }
    $redemption_supported = $redemption_form;
    if (0 < $whmcs_key["redemptionfee"]) {
        $redemption_status = "<span class=\"label active\">" . $LANG["activated"] . "</span>";
    } else if (!isset($redemption_array[$whmcs_value])) {
        $redemption_status = "";
        $redemption_supported = "";
    } else {
        $redemption_status = "<span class=\"label pending\">" . $LANG["disabled"] . "</span>";
    }
    if (!empty($msetupfee_register)) {
        echo "<a name=\"" . $whmcs_value . "\"></a><div style=\"width:auto; padding: 0px 10px 10px 10px; background-color:#F7F7F7; border: 1px solid #0D6306;-moz-border-radius: 5px;-webkit-border-radius: 5px;-o-border-radius: 5px;border-radius: 5px;\">";
        $tldbutton = $LANG["domaintldconfigbutton"];
        $pricingaction = $LANG["doupdatenow"];
        $tlddesc = $LANG["domaintldconfigdesc"];
        $tlddesccol = "#0D6306;";
    } else {
        echo "<a name=\"" . $whmcs_value . "\"></a><div style=\"width:auto; padding: 0px 10px 10px 10px; background-color:#F7F7F7; border: 1px solid #E5A309;-moz-border-radius: 5px;-webkit-border-radius: 5px;-o-border-radius: 5px;border-radius: 5px;\">";
        $tldbutton = $LANG["domaintldconfigbutton1"];
        $pricingaction = $LANG["doimportnow"];
        $tlddesc = $LANG["domaintldconfigdesc1"];
        $tlddesccol = "#E5A309;";
    }
    if ($tldpointer == $whmcs_value) {
        echo "<a name=\"" . $whmcs_value . "\"></a>";
        if ($doUpdate) {
            $isimport = $LANG["domainupdated"];
        } else {
            $isimport = $LANG["domaininserted"];
        }
        if (isset($tbldomainpricing_result)) {
            if ($tbldomainpricing_result) {
                echo "<br /><div class=\"alert alert-success\"><p>";
                echo $extension . " " . $LANG["domaininsertsuccess"];
                echo "</p></div>";
            } else {
                echo "<br /><div class=\"alert alert-danger\"><p>";
                echo $extension . " " . $LANG["domaininserterror"];
                echo "</p></div>";
            }
        }
        if (isset($add_result)) {
            if ($add_result && $tra_result && $ren_result) {
                echo "<br /><div class=\"alert alert-success\"><p>";
                echo $whmcs_value . " " . $LANG["domainpriceinsertsuccess"] . " " . $isimport . "!</p></div>";
            } else {
                echo "<br /><div class=\"alert alert-danger\"><p>";
                echo $whmcs_value . " " . $LANG["domainpriceinserterror"] . " " . $isimport . "!</p></div>";
            }
        }
        if (isset($updtld_result)) {
            if ($updtld_result) {
                echo "<br /><div class=\"alert alert-success\"><p>";
                echo $whmcs_value . " " . $LANG["tldupdatemessage"] . "</p></div>";
            } else {
                echo "<br /><div class=\"alert alert-danger\"><p>";
                echo $LANG["tldupdatesortordererror"] . "</p></div>";
            }
        }
    }
    $registerfee = "<strong>" . $LANG["costregister"] . "</strong> = " . $costdb_array["registerfee"][$whmcs_value] . " " . $reseller_buycurrency;
    $renewfee = "<strong>" . $LANG["costrenew"] . "</strong> = " . $costdb_array["renewfee"][$whmcs_value] . " " . $reseller_buycurrency;
    if ($costdb_array["transferfee"][$whmcs_value] != "0.00" && !empty($costdb_array["transferfee"][$whmcs_value])) {
        $transferfee = "<strong>" . $LANG["costtransfer"] . "</strong> = " . $costdb_array["transferfee"][$whmcs_value] . " " . $reseller_buycurrency;
    } else {
        $transferfee = "<strong>" . $LANG["costtransfer"] . "</strong> = 0.00 " . $reseller_buycurrency;
    }
    if (isset($costdb_array["restorefee"][$whmcs_value])) {
        $restorefee = "<strong>" . $LANG["costrestore"] . "</strong> = " . $costdb_array["restorefee"][$whmcs_value] . " " . $reseller_buycurrency;
    } else {
        $restorefee = "<strong>" . $LANG["costrestore"] . "</strong> = 0.00 " . $reseller_buycurrency;
    }
    if ($reseller_sellingcurrency == $reseller_buycurrency) {
        $registercostprice = $costdb_array["registerfee"][$whmcs_value];
        $reg_converted = "";
        $transfercostprice = $costdb_array["transferfee"][$whmcs_value];
        $tra_converted = "";
        $renewcostprice = $costdb_array["renewfee"][$whmcs_value];
        $ren_converted = "";
        $restorecostprice = $costdb_array["restorefee"][$whmcs_value];
        $res_converted = "";
    } else if (empty($buycurrencyrate)) {
        $registercostprice = "0.00";
        $transfercostprice = "0.00";
        $renewcostprice = "0.00";
        $profit_margin = 0;
    } else {
        $registercostprice = round($costdb_array["registerfee"][$whmcs_value] / $buycurrencyrate, 2);
        $reg_converted = " (" . $registercostprice . " " . $reseller_sellingcurrency . ")";
        $transfercostprice = round($costdb_array["transferfee"][$whmcs_value] / $buycurrencyrate, 2);
        $tra_converted = " (" . $transfercostprice . " " . $reseller_sellingcurrency . ")";
        $renewcostprice = round($costdb_array["renewfee"][$whmcs_value] / $buycurrencyrate, 2);
        $ren_converted = " (" . $renewcostprice . " " . $reseller_sellingcurrency . ")";
        $restorecostprice = round($costdb_array["restorefee"][$whmcs_value] / $buycurrencyrate, 2);
        $res_converted = " (" . $restorecostprice . " " . $reseller_sellingcurrency . ")";
    }
    $profit_margin = 0;
    if (!empty($inpromotld[$whmcs_value]["domainregister"])) {
        if (0 < $registercostprice && isset($profitexception[$whmcs_value]["domainregister"]) && is_numeric($profitexception[$whmcs_value]["domainregister"])) {
            $profit_margin = round((float) $profitexception[$whmcs_value]["domainregister"] / (float) $registercostprice * 100 - 100, 2);
        }
    } else if (0 < $registercostprice && is_numeric($msetupfee_register) && 0 < (float) $msetupfee_register) {
        $profit_margin = round((float) $msetupfee_register / (float) $registercostprice * 100 - 100, 2);
    }
    if (!empty($buycurrencyrate)) {
        if ($profit_margin < 0) {
            $profit_inpercents = "<span style=\"background-color:#cc0000;color:#fff;padding:0px 3px 0px 3px;\">" . $profit_margin . "%</span>";
        } else {
            $profit_inpercents = "<span style=\"background-color:#138C08;color:#fff;padding:0px 3px 0px 3px;\">" . $profit_margin . "%</span>";
        }
    } else {
        $profit_inpercents = "<code>Reseller Buy Currency (" . $reseller_buycurrency . ") not Setup in WHMCS!</code>";
    }
    if (is_array($minmax_reg_array[$whmcs_value]) && in_array($whmcs_value, $minmax_reg_array[$whmcs_value])) {
        $telescope_checkbox = "";
    } else {
        $telescope_checkbox = "<input type=\"checkbox\" name=\"telescope\" value=\"1\" /> " . $LANG["disabletelescopepricing"];
    }
    if (empty($whmcs_key["dnsmanagement"])) {
        $dns_add = "";
    } else {
        $dns_add = "checked=\"checked\"";
    }
    if (empty($whmcs_key["emailforwarding"])) {
        $email_add = "";
    } else {
        $email_add = "checked=\"checked\"";
    }
    if (empty($whmcs_key["idprotection"])) {
        $privacy_add = "";
    } else {
        $privacy_add = "checked=\"checked\"";
    }
    if (empty($whmcs_key["eppcode"])) {
        $epp_add = "";
    } else {
        $epp_add = "checked=\"checked\"";
    }
    if ($whmcs_key["autoreg"] == "") {
        $is_registrar = "None";
        $setautoreg = "selected=\"selected\"";
    } else {
        $setautoreg = "selected=\"selected\"";
        $is_registrar = $whmcs_key["autoreg"];
    }
    if (is_array($pre_ga_tlds) && in_array($whmcs_value, $pre_ga_tlds)) {
        $is_prega = "<span style=\"font-size:16px;font-weight:bold;float:right;\" " . $style_labelinfo . ">Pre GA</span>";
    } else {
        $is_prega = "";
    }
    echo "<h2>" . $LANG["domaintldconfig"] . " - <strong>" . strtoupper($whmcs_value) . "</strong> (" . $grouplabel . ") " . $is_prega;
    if (!empty($inpromotld)) {
        foreach ($inpromotld[$whmcs_value] as $kp => $vp) {
            if ($vp == $whmcs_value) {
                if ($kp == "domainregister") {
                    echo $activepromo1 = "<span style=\"font-size:12px;" . $vars["default_promostyle"] . "\">" . $LANG["costregister"] . " " . $LANG["promoactivelabel"] . "</span>&nbsp;&nbsp;";
                } else {
                    echo $activepromo1 = "";
                }
                if ($kp == "domainrenew") {
                    echo $activepromo2 = "<span style=\"font-size:12px;" . $vars["default_promostyle"] . "\">" . $LANG["costrenew"] . " " . $LANG["promoactivelabel"] . "</span>&nbsp;&nbsp;";
                } else {
                    echo $activepromo2 = "";
                }
                if ($kp == "domaintransfer") {
                    echo $activepromo3 = "<span style=\"font-size:12px;" . $vars["default_promostyle"] . "\">" . $LANG["costtransfer"] . " " . $LANG["promoactivelabel"] . "</span>&nbsp;&nbsp;";
                } else {
                    echo $activepromo3 = "";
                }
            }
        }
    }
    echo "\r\n\t\t\t\t</h2><p style=\"color:" . $tlddesccol . "\"><strong>" . strtoupper($whmcs_value) . "</strong> " . $tlddesc . "</p>\r\n\t\t\t\t\r\n\t\t\t\t<table class=\"datatable\" border=\"0\" cellpadding=\"3\" cellspacing=\"1\" width=\"100%\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t<th>" . $LANG["domainextension"] . "</th>\r\n\t\t\t\t<th>" . $LANG["domaindnsmanagment"] . "</th>\r\n\t\t\t\t<th>" . $LANG["domainemailforwarding"] . "</th>\r\n\t\t\t\t<th>" . $LANG["domainidprotection"] . "</th>\r\n\t\t\t\t<th>" . $LANG["domaineppcode"] . "</th>\r\n\t\t\t\t<th>" . $LANG["domainautoreg"] . "</th>\r\n\t\t\t\t<th>" . $LANG["domainorder"] . "</th>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<form method=\"post\" action=\"" . $_SERVER["REQUEST_URI"] . "#" . $whmcs_value . "\">\r\n\t\t\t\t<tr>\r\n\t\t\t\t<td style=\"text-align:center;\">" . $whmcs_value . "</td>\r\n\t\t\t\t<td style=\"text-align:center;\"><input name=\"dnsmanagement\" type=\"checkbox\" value=\"1\" " . $dns_add . "/></td>\r\n\t\t\t\t<td style=\"text-align:center;\"><input name=\"emailforwarding\" type=\"checkbox\" value=\"1\" " . $email_add . "/></td>\r\n\t\t\t\t<td style=\"text-align:center;\"><input name=\"idprotection\" type=\"checkbox\" value=\"1\" " . $privacy_add . "/></td>\r\n\t\t\t\t<td style=\"text-align:center;\"><input name=\"eppcode\" type=\"checkbox\" value=\"1\" " . $epp_add . "/></td>\r\n\t\t\t\t<td style=\"text-align:center;\">" . registrardropdown($setautoreg, $is_registrar) . "</td>\r\n\t\t\t\t<td style=\"text-align:center;\"><input name=\"order\" class=\"form-control\" type=\"text\" value=\"" . $whmcs_key["order"] . "\" size=\"2\" /></td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t<td style=\"padding:5px;text-align:center;\" colspan=\"7\">\r\n\t\t\t\t<input type=\"hidden\" name=\"updtldsettings\" value=\"true\">\r\n\t\t\t\t<input type=\"hidden\" name=\"updtld\" value=\"" . $whmcs_value . "\">\r\n\t\t\t\t<input type=\"hidden\" name=\"clientgroup\" value=\"" . $filtered . "\">\r\n\t\t\t\t<input type=\"submit\" class=\"btn btn-danger\" value=\"" . $LANG["updtldsettingsbutton"] . "\">\r\n\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t</form>\r\n\t\t\t\t<tr>\r\n\t\t\t\t<td colspan=\"7\">\r\n\t\t\t\t<div style=\"text-align:center;padding:2px;\"><strong>" . $LANG["whmcstablepricingtitle"] . "</strong></div>\r\n\t\t\t\t<table class=\"datatable\" border=\"0\" cellpadding=\"3\" cellspacing=\"1\" width=\"100%\">\r\n\t\t\t\t  <tr>\r\n\t\t\t\t\t<th>" . $LANG["domtype"] . "&nbsp;&nbsp;</th>\r\n\t\t\t\t\t<th>1 " . $LANG["domainyear"] . "</th>\r\n\t\t\t\t\t<th>2 " . $LANG["domainyears"] . "</th>\r\n\t\t\t\t\t<th>3 " . $LANG["domainyears"] . "</th>\r\n\t\t\t\t\t<th>4 " . $LANG["domainyears"] . "</th>\r\n\t\t\t\t\t<th>5 " . $LANG["domainyears"] . "</th>\r\n\t\t\t\t\t<th>6 " . $LANG["domainyears"] . "</th>\r\n\t\t\t\t\t<th>7 " . $LANG["domainyears"] . "</th>\r\n\t\t\t\t\t<th>8 " . $LANG["domainyears"] . "</th>\r\n\t\t\t\t\t<th>9 " . $LANG["domainyears"] . "</th>\r\n\t\t\t\t\t<th>10 " . $LANG["domainyears"] . "</th>\r\n\t\t\t\t  </tr>\r\n\t\t\t\t  <tr>\r\n\t\t\t\t\t<td style=\"text-align:right;\">" . $LANG["domainregister"] . "&nbsp;&nbsp;" . $reseller_sellingcurrency . "&nbsp;&nbsp;</td>\r\n\t\t\t\t\t<td>" . $msetupfee_register . "</td>\r\n\t\t\t\t\t<td>" . $qsetupfee_register . "</td>\r\n\t\t\t\t\t<td>" . $ssetupfee_register . "</td>\r\n\t\t\t\t\t<td>" . $asetupfee_register . "</td>\r\n\t\t\t\t\t<td>" . $bsetupfee_register . "</td>\r\n\t\t\t\t\t<td>" . $monthly_register . "</td>\r\n\t\t\t\t\t<td>" . $quarterly_register . "</td>\r\n\t\t\t\t\t<td>" . $semiannually_register . "</td>\r\n\t\t\t\t\t<td>" . $annually_register . "</td>\r\n\t\t\t\t\t<td>" . $biennially_register . "</td>\r\n\t\t\t\t  </tr>\r\n\t\t\t\t  <tr>\r\n\t\t\t\t\t<td style=\"text-align:right;\">" . $LANG["domainrenew"] . "&nbsp;&nbsp;" . $reseller_sellingcurrency . "&nbsp;&nbsp;</td>\r\n\t\t\t\t\t<td>" . $msetupfee_renew . "</td>\r\n\t\t\t\t\t<td>" . $qsetupfee_renew . "</td>\r\n\t\t\t\t\t<td>" . $ssetupfee_renew . "</td>\r\n\t\t\t\t\t<td>" . $asetupfee_renew . "</td>\r\n\t\t\t\t\t<td>" . $bsetupfee_renew . "</td>\r\n\t\t\t\t\t<td>" . $monthly_renew . "</td>\r\n\t\t\t\t\t<td>" . $quarterly_renew . "</td>\r\n\t\t\t\t\t<td>" . $semiannually_renew . "</td>\r\n\t\t\t\t\t<td>" . $annually_renew . "</td>\r\n\t\t\t\t\t<td>" . $biennially_renew . "</td>\r\n\t\t\t\t  </tr>\r\n\t\t\t\t  <tr>\r\n\t\t\t\t\t<td style=\"text-align:right;\">" . $LANG["domaintransfer"] . "&nbsp;&nbsp;" . $reseller_sellingcurrency . "&nbsp;&nbsp;</td>\r\n\t\t\t\t\t<td>" . $msetupfee_transfer . "</td>\r\n\t\t\t\t\t<td>" . $qsetupfee_transfer . "</td>\r\n\t\t\t\t\t<td>" . $ssetupfee_transfer . "</td>\r\n\t\t\t\t\t<td>" . $asetupfee_transfer . "</td>\r\n\t\t\t\t\t<td>" . $bsetupfee_transfer . "</td>\r\n\t\t\t\t\t<td>" . $monthly_transfer . "</td>\r\n\t\t\t\t\t<td>" . $quarterly_transfer . "</td>\r\n\t\t\t\t\t<td>" . $semiannually_transfer . "</td>\r\n\t\t\t\t\t<td>" . $annually_transfer . "</td>\r\n\t\t\t\t\t<td>" . $biennially_transfer . "</td>\r\n\t\t\t\t  </tr>\r\n\t\t\t";
    if (!empty($redemption_supported)) {
        echo "\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t  <td style=\"text-align:right;\">" . $LANG["redemption"] . "&nbsp;&nbsp;" . $reseller_sellingcurrency . "&nbsp;&nbsp;</td>\r\n\t\t\t\t\t  <td colspan=\"10\" align=\"left\">" . $whmcs_key["redemptionfee"] . " (" . $LANG["redemptionperiod"] . " = " . $whmcs_key["redemptiondays"] . " " . $LANG["redemptiondays"] . ") " . $redemption_status . "</td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t  \t<tr>\r\n\t\t\t\t      <td colspan=\"11\" align=\"left\" style=\"padding:5px;border:none;\">\r\n\t\t\t\t\t    <span style=\"color:#CC0000;font-weight:bold;\">" . $LANG["costprice"] . ":</span> " . $registerfee . $reg_converted . " " . $renewfee . $ren_converted . " " . $transferfee . $tra_converted . " " . $restorefee . $res_converted . "\r\n\t\t\t\t\t  </td>\r\n\t\t\t\t  \t</tr>\r\n\t\t\t\t";
    } else {
        echo "\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t      <td colspan=\"11\" align=\"left\" style=\"padding:5px;border:none;\">\r\n\t\t\t\t\t    <span style=\"color:#CC0000;font-weight:bold;\">" . $LANG["costprice"] . ":</span> " . $registerfee . $reg_converted . " " . $renewfee . $ren_converted . " " . $transferfee . $tra_converted . "\r\n\t\t\t\t\t  </td>\r\n\t\t\t\t    </tr>";
    }
    echo "\r\n\t\t\t\t</table>\r\n\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t<tr>\r\n\t\t\t\t<td colspan=\"7\">\r\n\t\t\t\t<div style=\"margin-top:5px;\">\r\n\t\t\t\t<div style=\"float:left;padding-left:20px;\"><h4 style=\"margin:0;padding-right:20px;font-size:14px;\">" . $LANG["tldimportpricettitle"] . " </h4></div>\r\n\t\t\t\t<div style=\"float:left;padding-right:20px\">\r\n\t\t\t\t<a data-toggle=\"modal\" href=\"#showme" . $showme1++ . "\">" . $LANG["tldimportoption1"] . "</a><br />\r\n\t\t\t\t<a data-toggle=\"modal\" href=\"#showme" . $showme2++ . "\">" . $LANG["tldimportoption2"] . "</a><br />\r\n\t\t\t\t<a data-toggle=\"modal\" href=\"#showme" . $showme3++ . "\">" . $LANG["tldimportoption3"] . "</a><br /><br />\r\n\t\t\t\t</div>\r\n\t\t\t\t<div style=\"float:left;\">\r\n\t\t\t\t<span style=\"font-weight:bold;font-size:14px;\">" . $LANG["marginprofittitle"] . " " . $profit_inpercents . "</span>\r\n\t\t\t\t</div>\r\n\t\t\t\t<div class=\"clear\"></div>\r\n\r\n\t\t\t\t<div id=\"showme" . $divboxes1++ . "\" class=\"modal fade\" role=\"dialog\">\r\n\t\t\t\t<div class=\"modal-dialog\">\r\n\t\t\t\t<div class=\"modal-content\">\r\n\t\t\t\t<div class=\"modal-header\">\r\n\t\t\t\t<button type=\"button\" class=\"close\" data-dismiss=\"modal\">&times;</button>\r\n\t\t\t\t<h4 class=\"modal-title\">" . strtoupper($whmcs_value) . "</h4>\r\n\t\t\t\t</div>\r\n\t\t\t\t<div class=\"modal-body\">\r\n\t\t\t\t<form method=\"post\" action=\"" . $_SERVER["REQUEST_URI"] . "#" . $whmcs_value . "\">\r\n\t\t\t\t<input type=\"hidden\" name=\"tldpricings\" value=\"" . $whmcs_value . "\">\r\n\t\t\t\t<input type=\"hidden\" name=\"relid\" value=\"" . $tldid . "\">\r\n\t\t\t\t<input type=\"hidden\" name=\"clientgroup\" value=\"" . $filtered . "\">\r\n\t\t\t\t<h3>" . $LANG["tldimportdesc1"] . "</h3>\r\n\t\t\t\t<p>" . $LANG["increaseprices"] . " \r\n\t\t\t\t  <input class=\"form-control\" style=\"display: inline; width: auto\" name=\"increase\" type=\"text\" size=\"" . $input_size . "\" onfocus=\"if(this.value=='0.00')this.value=''\" value=\"0.00\" /> " . $reseller_sellingcurrency . "\r\n\t\t\t\t</p>\r\n\t\t\t\t<p>" . $telescope_checkbox . "<br />" . $doCurrencyupd . "<br />" . $redemption_supported . "</p>\r\n\t\t\t\t<p>" . $pricingaction . " <input class=\"btn btn-success\" type=\"submit\" value=\"" . $tldbutton . "\"></p>\r\n\t\t\t\t</form>\r\n\t\t\t\t</div>\r\n\t\t\t\t<div class=\"modal-footer\">\r\n\t\t\t\t<button type=\"button\" class=\"btn btn-default\" data-dismiss=\"modal\">Close</button>\r\n\t\t\t\t</div>\r\n\t\t\t\t</div>\r\n\t\t\t\t</div>\t\t\t\t\r\n\t\t\t\t</div>\r\n\r\n\t\t\t\t<div id=\"showme" . $divboxes2++ . "\" class=\"modal fade\" role=\"dialog\">\r\n\t\t\t\t<div class=\"modal-dialog\">\r\n\t\t\t\t<div class=\"modal-content\">\r\n\t\t\t\t<div class=\"modal-header\">\r\n\t\t\t\t<button type=\"button\" class=\"close\" data-dismiss=\"modal\">&times;</button>\r\n\t\t\t\t<h4 class=\"modal-title\">" . strtoupper($whmcs_value) . "</h4>\r\n\t\t\t\t</div>\r\n\t\t\t\t<div class=\"modal-body\">\r\n\t\t\t\t<form method=\"post\" action=\"" . $_SERVER["REQUEST_URI"] . "#" . $whmcs_value . "\">\r\n\t\t\t\t<input type=\"hidden\" name=\"tldpricings\" value=\"" . $whmcs_value . "\">\r\n\t\t\t\t<input type=\"hidden\" name=\"relid\" value=\"" . $tldid . "\">\r\n\t\t\t\t<input type=\"hidden\" name=\"clientgroup\" value=\"" . $filtered . "\">\r\n\t\t\t\t<input type=\"hidden\" name=\"costincrease\" value=\"true\">\r\n\t\t\t\t<h3 style=\"padding-bottom:5px;\">" . $LANG["tldimportdesc2"] . "</h3>\r\n\t\t\t\t<p>" . $LANG["importdesctitle1"] . " \r\n\t\t\t\t  <input class=\"form-control\" style=\"display: inline; width: auto\" name=\"regfee\" type=\"text\" size=\"" . $input_size . "\" value=\"" . $registercostprice . "\" /> " . $reseller_sellingcurrency . " " . $LANG["importincremental"] . " \r\n\t\t\t\t   <input class=\"form-control\" style=\"display: inline; width: auto\" name=\"regincrease\" type=\"text\" size=\"" . $input_size . "\" onfocus=\"if(this.value=='0.00')this.value=''\" value=\"0.00\" /> " . $reseller_sellingcurrency . "\r\n\t\t\t\t</p>\r\n\t\t\t\t<p>" . $LANG["importdesctitle2"] . " \r\n\t\t\t\t  <input class=\"form-control\" style=\"display: inline; width: auto\" name=\"renfee\" type=\"text\" size=\"" . $input_size . "\" value=\"" . $renewcostprice . "\" /> " . $reseller_sellingcurrency . " " . $LANG["importincremental"] . " \r\n\t\t\t\t  <input class=\"form-control\" style=\"display: inline; width: auto\" name=\"renincrease\" type=\"text\" size=\"" . $input_size . "\" onfocus=\"if(this.value=='0.00')this.value=''\" value=\"0.00\" /> " . $reseller_sellingcurrency . "\r\n\t\t\t\t</p>\r\n\t\t\t\t<p>" . $LANG["importdesctitle3"] . " \r\n\t\t\t\t  <input class=\"form-control\" style=\"display: inline; width: auto\" name=\"trafee\" type=\"text\" size=\"" . $input_size . "\" value=\"" . $transfercostprice . "\" /> " . $reseller_sellingcurrency . " " . $LANG["importincremental"] . " \r\n\t\t\t\t  <input class=\"form-control\" style=\"display: inline; width: auto\" name=\"traincrease\" type=\"text\" size=\"" . $input_size . "\" onfocus=\"if(this.value=='0.00')this.value=''\" value=\"0.00\" /> " . $reseller_sellingcurrency . "\r\n\t\t\t\t</p>\r\n\t\t\t\t<p>" . $telescope_checkbox . "<br />" . $doCurrencyupd . "<br />" . $redemption_supported . "</p>\r\n\t\t\t\t<p>" . $pricingaction . " <input class=\"btn btn-success\" type=\"submit\" value=\"" . $tldbutton . "\"></p>\r\n\t\t\t\t</form>\r\n\t\t\t\t</div>\r\n\t\t\t\t<div class=\"modal-footer\">\r\n\t\t\t\t<button type=\"button\" class=\"btn btn-default\" data-dismiss=\"modal\">Close</button>\r\n\t\t\t\t</div>\r\n\t\t\t\t</div>\r\n\t\t\t\t</div>\t\t\t\t\r\n\t\t\t\t</div>\r\n\r\n\t\t\t\t<div id=\"showme" . $divboxes3++ . "\" class=\"modal fade\" role=\"dialog\">\r\n\t\t\t\t<div class=\"modal-dialog\">\r\n\t\t\t\t<div class=\"modal-content\">\r\n\t\t\t\t<div class=\"modal-header\">\r\n\t\t\t\t<button type=\"button\" class=\"close\" data-dismiss=\"modal\">&times;</button>\r\n\t\t\t\t<h4 class=\"modal-title\">" . strtoupper($whmcs_value) . "</h4>\r\n\t\t\t\t</div>\r\n\t\t\t\t<div class=\"modal-body\">\r\n\t\t\t\t<form method=\"post\" action=\"" . $_SERVER["REQUEST_URI"] . "#" . $whmcs_value . "\">\r\n\t\t\t\t<input type=\"hidden\" name=\"tldpricings\" value=\"" . $whmcs_value . "\">\r\n\t\t\t\t<input type=\"hidden\" name=\"relid\" value=\"" . $tldid . "\">\r\n\t\t\t\t<input type=\"hidden\" name=\"clientgroup\" value=\"" . $filtered . "\">\r\n\t\t\t\t<input type=\"hidden\" name=\"costpercentincrease\" value=\"true\">\r\n\t\t\t\t<h3 style=\"padding-bottom:5px;\">" . $LANG["tldimportdesc3"] . "</h3>\r\n\t\t\t\t<p>" . $LANG["importdesctitle1"] . " \r\n\t\t\t\t  <input class=\"form-control\" style=\"display: inline; width: auto\" name=\"regfee\" type=\"text\" size=\"" . $input_size . "\" value=\"" . $registercostprice . "\" /> " . $reseller_sellingcurrency . " " . $LANG["importpercentincremental"] . " \r\n\t\t\t\t  <input class=\"form-control\" style=\"display: inline; width: auto\" name=\"regpercentincrease\" type=\"text\" size=\"2\" onfocus=\"if(this.value=='0.00')this.value=''\" value=\"0.00\" /> %\r\n\t\t\t\t</p>\r\n\t\t\t\t<p>" . $LANG["importdesctitle2"] . " \r\n\t\t\t\t  <input class=\"form-control\" style=\"display: inline; width: auto\" name=\"renfee\" type=\"text\" size=\"" . $input_size . "\" value=\"" . $renewcostprice . "\" /> " . $reseller_sellingcurrency . " " . $LANG["importpercentincremental"] . " \r\n\t\t\t\t  <input class=\"form-control\" style=\"display: inline; width: auto\" name=\"renpercentincrease\" type=\"text\" size=\"2\" onfocus=\"if(this.value=='0.00')this.value=''\" value=\"0.00\" /> %\r\n\t\t\t\t</p>\r\n\t\t\t\t<p>" . $LANG["importdesctitle3"] . " \r\n\t\t\t\t  <input class=\"form-control\" style=\"display: inline; width: auto\" name=\"trafee\" type=\"text\" size=\"" . $input_size . "\" value=\"" . $transfercostprice . "\" /> " . $reseller_sellingcurrency . " " . $LANG["importpercentincremental"] . " \r\n\t\t\t\t  <input class=\"form-control\" style=\"display: inline; width: auto\" name=\"trapercentincrease\" type=\"text\" size=\"2\" onfocus=\"if(this.value=='0.00')this.value=''\" value=\"0.00\" /> %\r\n\t\t\t\t</p>\r\n\t\t\t\t<p>" . $telescope_checkbox . "<br />" . $doCurrencyupd . "<br />" . $redemption_supported . "</p>\r\n\t\t\t\t<p>" . $pricingaction . " <input class=\"btn btn-success\" type=\"submit\" value=\"" . $tldbutton . "\"></p>\r\n\t\t\t\t</form>\r\n\t\t\t\t</div>\r\n\t\t\t\t<div class=\"modal-footer\">\r\n\t\t\t\t<button type=\"button\" class=\"btn btn-default\" data-dismiss=\"modal\">Close</button>\r\n\t\t\t\t</div>\r\n\t\t\t\t</div>\r\n\t\t\t\t</div>\t\t\t\t\r\n\t\t\t\t</div>\r\n\t\t\t\t\r\n\t\t\t\t<div><a href=\"#top\">" . $LANG["goup"] . "</a></div>\t\t\t\t\r\n\t\t\t\t</td>\r\n\t\t\t\t</tr>\r\n\t\t\t\t</table>\r\n\t\t\t";
    echo "</div><br />";
}
if (isset($_POST["tldbulksetup"]) && $_POST["tldbulksetup"] == "true") {
    $sortnum0 = $whmcs_key["order"] + 1;
    $num1 = 1;
    sort($rc_tldarray[0]["tld"]);
    echo "<a name=\"tldbulksetup\"></a><br /><div style=\"width:auto; padding: 0px 10px 10px 10px; background-color:#F7F7F7; border: 1px solid #CC0000;-moz-border-radius: 5px;-webkit-border-radius: 5px;-o-border-radius: 5px;border-radius: 5px;\">";
    echo "<h2>" . $LANG["bulktldsetuptitle"] . "</h2>";
    echo $LANG["preselectduerules"] . " <p>" . $LANG["bulktldsetupdesc2"] . "</p>";
    echo "<form method=\"post\" action=\"" . $_SERVER["REQUEST_URI"] . "\">\r\n\t\t<input type=\"hidden\" name=\"clientgroup\" value=\"" . $filtered . "\">\r\n\t\t<table id=\"bulktable\" class=\"datatable\" border=\"0\" cellpadding=\"3\" cellspacing=\"1\" width=\"100%\">\r\n\t\t<tr>\r\n\t\t<th><input id=\"multicheck\" type=\"checkbox\" checked=\"checked\" title=\"" . $LANG["checkboxinclude"] . "\"></th>\r\n\t\t<th>" . $LANG["domainextension"] . "</th>\r\n\t\t<th>" . $LANG["domaindnsmanagment"] . "</th>\r\n\t\t<th>" . $LANG["domainemailforwarding"] . "</th>\r\n\t\t<th>" . $LANG["domainidprotection"] . "</th>\r\n\t\t<th>" . $LANG["domaineppcode"] . "</th>\r\n\t\t<th>" . $LANG["domainautoreg"] . "</th>\r\n\t\t<th>" . $LANG["domainorder"] . "</th>\r\n\t\t</tr>\r\n\t";
    foreach ($tblregistrars as $lbregistrars) {
        $registrar_dropdown .= "<option value=\"" . $lbregistrars . "\">" . $lbregistrars . "</option>";
    }
    echo "\r\n\t<script type=\"text/javascript\">\r\n\t\$(document).ready(function(){\r\n\t\$(\"#Select1\").change(function()\r\n\t{ \r\n\t\t \$(\".bulkregistrars\").find(\"select\").val(\$(this).val());\r\n\t});\r\n\t});\r\n\t</script>\r\n\t";
    $numlimit = 0;
    foreach ($rc_tldarray as $rc_key) {
        foreach ($rc_key["tld"] as $rc_value) {
            if (!in_array($rc_value, $checkarray)) {
                if (49 < $numlimit++) {
                } else {
                    if (in_array($rc_value, $privacy_array)) {
                        $privacy_add = "";
                        $dns_add = "checked=\"checked\"";
                        $email_add = "checked=\"checked\"";
                    } else {
                        $privacy_add = "checked=\"checked\"";
                        $dns_add = "checked=\"checked\"";
                        $email_add = "checked=\"checked\"";
                    }
                    if (in_array($rc_value, $eppcode_array)) {
                        $epp_add = "checked=\"checked\"";
                        $dns_add = "checked=\"checked\"";
                        $email_add = "checked=\"checked\"";
                    } else {
                        $epp_add = "";
                        $dns_add = "checked=\"checked\"";
                        $email_add = "checked=\"checked\"";
                    }
                    if ($rc_value == ".tel") {
                        $privacy_add = "";
                        $dns_add = "";
                        $epp_add = "";
                        $email_add = "";
                    }
                    if (is_array($pre_ga_tlds) && in_array($rc_value, $pre_ga_tlds)) {
                        $is_prega = "<span style=\"font-size:16px;font-weight:bold;float:right;\" " . $style_labelinfo . ">Pre GA</span>";
                    } else {
                        $is_prega = "";
                    }
                    echo "\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t<td style=\"text-align:center;\"><input class=\"checkall\" name=\"bulkform[" . $IDN->encode($rc_value) . "][include]\" type=\"checkbox\" value=\"true\" checked=\"checked\"/></td>\r\n\t\t\t\t\t<td>" . $rc_value . " " . $is_prega . "<input type=\"hidden\" name=\"bulkform[" . $IDN->encode($rc_value) . "][tld]\" value=\"" . $rc_value . "\"></td>\r\n\t\t\t\t\t<td style=\"text-align:center;\"><input name=\"bulkform[" . $IDN->encode($rc_value) . "][dnsmanagement]\" type=\"checkbox\" value=\"1\" " . $dns_add . "/></td>\r\n\t\t\t\t\t<td style=\"text-align:center;\"><input name=\"bulkform[" . $IDN->encode($rc_value) . "][emailforwarding]\" type=\"checkbox\" value=\"1\" " . $email_add . "/></td>\r\n\t\t\t\t\t<td style=\"text-align:center;\"><input name=\"bulkform[" . $IDN->encode($rc_value) . "][idprotection]\" type=\"checkbox\" value=\"1\" " . $privacy_add . "/></td>\r\n\t\t\t\t\t<td style=\"text-align:center;\"><input name=\"bulkform[" . $IDN->encode($rc_value) . "][eppcode]\" type=\"checkbox\" value=\"1\" " . $epp_add . "/></td>\r\n\t\t\t\t\t<td style=\"text-align:center;\"><div class=\"bulkregistrars\"><select class=\"form-control\" id=\"Select" . $num1++ . "\" name=\"bulkform[" . $IDN->encode($rc_value) . "][autoreg]\">" . $registrar_dropdown . "</select></div></td>\r\n\t\t\t\t\t<td style=\"text-align:center;\"><input name=\"bulkform[" . $IDN->encode($rc_value) . "][order]\" class=\"form-control\" type=\"text\" value=\"" . $sortnum0++ . "\" size=\"2\" /></td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t";
                }
            }
        }
    }
    echo "\r\n\t\t <tr>\r\n\t\t <td style=\"text-align:center;\" colspan=\"8\">\r\n\t\t <br /><div class=\"alert alert-warning\" style=\"width:100%;text-align:left;\"><strong>" . $LANG["note"] . "</strong> " . $LANG["qnoteimportselling"] . "</div><br />\r\n\t\t <p style=\"vertical-align:text-bottom;\">" . $LANG["qimportselling"] . "&nbsp;\r\n\t\t <input name=\"bulkform[bulkimportprices]\" type=\"radio\" value=\"true\" checked=\"checked\"/> " . $LANG["yesimportselling"] . "&nbsp;&nbsp;\r\n\t\t <input name=\"bulkform[bulkimportprices]\" type=\"radio\" value=\"false\" /> " . $LANG["noimportselling"] . "<br /><p>" . $LANG["bulktooloptions"] . "</p>" . $bulktelescope . "<br />" . $doCurrencyupd . "<br />" . $redemption_form . "\r\n\t\t <br /><br />\r\n\t\t <input type=\"hidden\" name=\"dobulktldsetup\" value=\"true\" /><input type=\"submit\" class=\"btn btn-success\" value=\"" . $LANG["bulktldsetupbutton"] . "\" />&nbsp;<a href=\"addonmodules.php?module=resellerclubmods_tools&domain=domain-pricing-import&clientgroup=" . $filtered . "\" class=\"btn btn-default\">" . $LANG["cancellink"] . "</a></p>\r\n\t\t </tr>\r\n\t\t </table>\r\n\t\t </form>\r\n\t";
    echo "</div>";
} else {
    if ($whmcs_key["autoreg"] == "") {
        $is_registrar = "None";
        $setautoreg = "selected=\"selected\"";
    } else {
        $setautoreg = "selected=\"selected\"";
        $is_registrar = $whmcs_key["autoreg"];
    }
    $sortnum0 = $whmcs_key["order"] + 1;
    sort($rc_tldarray[0]["tld"]);
    foreach ($rc_tldarray as $rc_key) {
        foreach ($rc_key["tld"] as $rc_value) {
            if (!in_array($rc_value, $checkarray)) {
                if (in_array($rc_value, $privacy_array)) {
                    $privacy_add = "";
                    $dns_add = "checked=\"checked\"";
                    $email_add = "checked=\"checked\"";
                } else {
                    $privacy_add = "checked=\"checked\"";
                    $dns_add = "checked=\"checked\"";
                    $email_add = "checked=\"checked\"";
                }
                if (in_array($rc_value, $eppcode_array)) {
                    $epp_add = "checked=\"checked\"";
                    $dns_add = "checked=\"checked\"";
                    $email_add = "checked=\"checked\"";
                } else {
                    $epp_add = "";
                    $dns_add = "checked=\"checked\"";
                    $email_add = "checked=\"checked\"";
                }
                if ($rc_value == ".tel") {
                    $privacy_add = "";
                    $dns_add = "";
                    $epp_add = "";
                    $email_add = "";
                }
                $rc_value = $IDN->decode($rc_value);
                if (is_array($pre_ga_tlds) && in_array($rc_value, $pre_ga_tlds)) {
                    $is_prega = "<span style=\"font-size:16px;font-weight:bold;float:right;\" " . $style_labelinfo . ">Pre GA</span>";
                } else {
                    $is_prega = "";
                }
                echo "<div style=\"width:auto; padding: 0px 10px 10px 10px; background-color:#F7F7F7; border: 1px solid #CC0000;-moz-border-radius: 5px;-webkit-border-radius: 5px;-o-border-radius: 5px;border-radius: 5px;\">";
                echo "\r\n\t\t\t\t\t<a name=\"" . $rc_value . "\"></a>\r\n\t\t\t\t\t<h2>" . $LANG["domaintldsetup"] . " - " . strtoupper($rc_value) . " " . $is_prega . "</h2>\r\n\t\t\t\t\t<p style=\"color:#CC0000\"><strong>" . strtoupper($rc_value) . "</strong> " . $LANG["domaintldsetupdesc"] . " </p>\r\n\t\t\t\t\t<p><strong>" . $LANG["note"] . "</strong> " . $LANG["preselectduerules"] . "</p>\r\n\t\t\t\t\t<form method=\"post\" action=\"" . $_SERVER["REQUEST_URI"] . "#" . $rc_value . "\">\r\n\t\t\t\t\t<input type=\"hidden\" name=\"clientgroup\" value=\"" . $filtered . "\">\r\n\t\t\t\t\t<table class=\"datatable\" border=\"0\" cellpadding=\"3\" cellspacing=\"1\" width=\"100%\">\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t<th>" . $LANG["domainextension"] . "</th>\r\n\t\t\t\t\t<th>" . $LANG["domaindnsmanagment"] . "</th>\r\n\t\t\t\t\t<th>" . $LANG["domainemailforwarding"] . "</th>\r\n\t\t\t\t\t<th>" . $LANG["domainidprotection"] . "</th>\r\n\t\t\t\t\t<th>" . $LANG["domaineppcode"] . "</th>\r\n\t\t\t\t\t<th>" . $LANG["domainautoreg"] . "</th>\r\n\t\t\t\t\t<th>" . $LANG["domainorder"] . "</th>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t<td>" . $rc_value . "</td>\r\n\t\t\t\t\t<td style=\"text-align:center;\"><input name=\"dnsmanagement\" type=\"checkbox\" value=\"1\" " . $dns_add . "/></td>\r\n\t\t\t\t\t<td style=\"text-align:center;\"><input name=\"emailforwarding\" type=\"checkbox\" value=\"1\" " . $email_add . "/></td>\r\n\t\t\t\t\t<td style=\"text-align:center;\"><input name=\"idprotection\" type=\"checkbox\" value=\"1\" " . $privacy_add . "/></td>\r\n\t\t\t\t\t<td style=\"text-align:center;\"><input name=\"eppcode\" type=\"checkbox\" value=\"1\" " . $epp_add . "/></td>\r\n\t\t\t\t\t<td style=\"text-align:center;\">" . registrardropdown($setautoreg, $is_registrar) . "</td>\r\n\t\t\t\t\t<td style=\"text-align:center;\"><input class=\"form-control\" name=\"order\" type=\"text\" value=\"" . $sortnum0++ . "\" size=\"2\" /></td>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t<tr>\r\n\t\t\t\t\t<td style=\"text-align:center;\" colspan=\"7\"><p><a href=\"#top\">" . $LANG["goup"] . "</a>&nbsp;&nbsp;&nbsp;" . $LANG["dosetupnow"] . " <input type=\"hidden\" name=\"tldsetup\" value=\"" . $rc_value . "\"><input type=\"submit\" class=\"btn btn-success\" value=\"" . $LANG["domaintldsetupbutton"] . "\"></p>\r\n\t\t\t\t\t</tr>\r\n\t\t\t\t\t</table>\r\n\t\t\t\t\t</form>\r\n\t\t\t\t\t";
                echo "</div><br />";
            }
        }
    }
}
echo "\r\n<script type=\"text/javascript\">\r\n\$(document).ready(function() {\r\n    // Existing bulk checkbox logic (keep this)\r\n    \$(\"#multicheck\").click(function () {\r\n        \$(\"#bulktable .checkall\").prop(\"checked\", this.checked);\r\n    });\r\n\r\n    // ───────────────────────────────\r\n    // TLD nav highlight logic\r\n    // ───────────────────────────────\r\n\r\n\tfunction rcmHighlightTldLinkByName(tld) {\r\n\t\tif (!tld) {\r\n\t\t\treturn;\r\n\t\t}\r\n\t\tvar decoded = decodeURIComponent(tld);\r\n\t\tvar \$target = null;\r\n\t\r\n\t\t// First: find the matching link, if any\r\n\t\t\$(\".rcm-tld-link\").each(function() {\r\n\t\t\tif (\$(this).data(\"tld\") === decoded) {\r\n\t\t\t\t\$target = \$(this);\r\n\t\t\t\treturn false; // break out of .each()\r\n\t\t\t}\r\n\t\t});\r\n\t\r\n\t\t// Only if we found a match do we change the highlight\r\n\t\tif (\$target) {\r\n\t\t\t\$(\".rcm-tld-link\").removeClass(\"rcm-tld-link-active\");\r\n\t\t\t\$target.addClass(\"rcm-tld-link-active\");\r\n\t\t}\r\n\t}\r\n\r\n    // Initial highlight based on current URL hash\r\n    var hash = window.location.hash || \"\";\r\n    if (hash.length > 1) {\r\n        rcmHighlightTldLinkByName(hash.substring(1)); // remove leading \"#\"\r\n    }\r\n\r\n    // When the hash changes (e.g. via other in-page links), update highlight\r\n    \$(window).on(\"hashchange\", function() {\r\n        var h = window.location.hash || \"\";\r\n        if (h.length > 1) {\r\n            rcmHighlightTldLinkByName(h.substring(1));\r\n        }\r\n    });\r\n\r\n    // When user clicks a nav TLD link (on this page), move highlight immediately\r\n    \$(document).on(\"click\", \"a.rcm-tld-link\", function() {\r\n        var tld = \$(this).data(\"tld\") || \"\";\r\n        if (tld) {\r\n            rcmHighlightTldLinkByName(tld);\r\n        }\r\n        // Page change (page=X) will still reload and re-run the hash logic,\r\n        // so this is just for the cases where we stay on the same page.\r\n    });\r\n});\r\n</script>\r\n";

?>