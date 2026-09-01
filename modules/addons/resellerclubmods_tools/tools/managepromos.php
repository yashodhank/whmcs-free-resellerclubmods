<?php
if (!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
echo "<script type=\"text/javascript\">// <![CDATA[\r\njQuery(document).ready(function(){\r\n  jQuery(\".scroll\").click(function(event){\r\n    event.preventDefault();\r\n    var offset = jQuery(jQuery(this).attr('href')).offset().top;\r\n    jQuery('html, body').animate({scrollTop:offset}, 1000);\r\n  });\r\n});\r\n// ]]></script>";
$sync_icon = "<i class=\"fa fa-refresh\"></i>";
if (0 <= version_compare(getWver(), "7.0.0")) {
    $is_v7 = 1;
    $sync_icon = "<i class=\"fas fa-sync fa-fw\"></i>";
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
$datenow = date("Y-m-d H:i:s");
$productkeys = newproductkeys($rcauth_userid, $rcauth_password, $rchttp_api);
$addondetails = getaddondetails($rcauth_userid, $rcauth_password, $rchttp_api);
foreach ($productkeys as $productkey => $productvalue) {
    foreach ($addondetails[$productkey]["tldlist"] as $addontld) {
        $addontld = $IDN->decode($addontld);
        if ($addondetails[$productkey]["maxregistrationyear"] != 10) {
            $max_reg_array["." . $addontld] = ["." . $addontld, $addondetails[$productkey]["maxregistrationyear"]];
        }
    }
}
$promoempty_errormessage = "<div class=\"alert alert-warning\"><p>" . $LANG["promopriceempty"] . "</p></div>";
$clientgroupid = "0";
if (isset($_POST["undopromo"]) && !empty($_POST["promotlds"])) {
    $realsellingprice = $_POST["sellingprice"];
    $mytlds = $_POST["promotlds"];
    $type = $_POST["type"];
    $mytlds = $_POST["promotlds"];
    $myfind = ",";
    $coma = strpos($mytlds, $myfind);
    if ($coma !== false) {
        $tldsextensions = explode(",", $mytlds);
    } else {
        $tldsextensions = [$mytlds];
    }
    echo "<div class=\"alert alert-success\"><p>" . $LANG["multipromoupdatesuccess1"] . " ";
    foreach ($tldsextensions as $tldstrings) {
        $result = Illuminate\Database\Capsule\Manager::table("tbldomainpricing")->where("extension", "=", $tldstrings)->select("id")->get();
        $relid = $result[0]->id;
        $tldrelid = $relid;
        $data = Illuminate\Database\Capsule\Manager::table("tblpricing")->where("relid", "=", $relid)->where("currency", "=", $currency)->where("type", "=", $type)->where("tsetupfee", "=", "0.00")->select("msetupfee", "qsetupfee", "ssetupfee", "asetupfee", "bsetupfee", "monthly", "quarterly", "semiannually", "annually", "biennially")->get();
        $promoprice = $data[0]->msetupfee;
        if ("0.00" < $data[0]->qsetupfee) {
            $qsetupfee = ($data[0]->qsetupfee - $promoprice) * 2;
        } else {
            $qsetupfee = "-1.00";
        }
        if ("0.00" < $data[0]->ssetupfee) {
            $ssetupfee = ($data[0]->ssetupfee - $promoprice) / 2 * 3;
        } else {
            $ssetupfee = "-1.00";
        }
        if ("0.00" < $data[0]->asetupfee) {
            $asetupfee = ($data[0]->asetupfee - $promoprice) / 3 * 4;
        } else {
            $asetupfee = "-1.00";
        }
        if ("0.00" < $data[0]->bsetupfee) {
            $bsetupfee = ($data[0]->bsetupfee - $promoprice) / 4 * 5;
        } else {
            $bsetupfee = "-1.00";
        }
        if ("0.00" < $data[0]->monthly) {
            $monthly = ($data[0]->monthly - $promoprice) / 5 * 6;
        } else {
            $monthly = "-1.00";
        }
        if ("0.00" < $data[0]->quarterly) {
            $quarterly = ($data[0]->quarterly - $promoprice) / 6 * 7;
        } else {
            $quarterly = "-1.00";
        }
        if ("0.00" < $data[0]->semiannually) {
            $semiannually = ($data[0]->semiannually - $promoprice) / 7 * 8;
        } else {
            $semiannually = "-1.00";
        }
        if ("0.00" < $data[0]->annually) {
            $annually = ($data[0]->annually - $promoprice) / 8 * 9;
        } else {
            $annually = "-1.00";
        }
        if ("0.00" < $data[0]->biennially) {
            $biennially = ($data[0]->biennially - $promoprice) / 9 * 10;
        } else {
            $biennially = "-1.00";
        }
        if ($max_reg_array[$tldstrings]) {
            if ($max_reg_array[$tldstrings][1] == 5) {
                $update = ["msetupfee" => $realsellingprice, "qsetupfee" => $qsetupfee, "ssetupfee" => $ssetupfee, "asetupfee" => $asetupfee, "bsetupfee" => $bsetupfee];
            } else {
                $update = ["msetupfee" => $realsellingprice];
            }
        } else {
            $update = ["msetupfee" => $realsellingprice, "qsetupfee" => $qsetupfee, "ssetupfee" => $ssetupfee, "asetupfee" => $asetupfee, "bsetupfee" => $bsetupfee, "monthly" => $monthly, "quarterly" => $quarterly, "semiannually" => $semiannually, "annually" => $annually, "biennially" => $biennially];
        }
        Illuminate\Database\Capsule\Manager::table("tblpricing")->where("relid", "=", $relid)->where("currency", "=", $currency)->where("type", "=", $type)->where("tsetupfee", "=", "0.00")->update($update);
        echo "<strong>" . $tldstrings . "</strong> ";
        $dodelete = Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodspromo")->where("extension", "=", $tldstrings)->where("type", "=", $type)->delete();
        if ($is_v7 == 1) {
            $update = ["group" => "none"];
            Illuminate\Database\Capsule\Manager::table("tbldomainpricing")->where("extension", "=", $tldstrings)->update($update);
        }
        if ($is_currencydoupd != "on") {
            domainCurrencyPricingupdate($clientgroupid, 0, $type, $tldrelid);
        }
    }
    echo $LANG["sellingundoupdatesuccess"] . " " . $currencycode . " " . $realsellingprice . "</p></div>";
}
if (isset($_POST["undoupd"])) {
    $realsellingprice = $_POST["sellingprice"];
    $tldstrings = $_POST["tld"];
    $relid = $_POST["relid"];
    $tldrelid = $relid;
    $type = $_POST["type"];
    $data = Illuminate\Database\Capsule\Manager::table("tblpricing")->where("relid", "=", $relid)->where("currency", "=", $currency)->where("type", "=", $type)->where("tsetupfee", "=", "0.00")->select("msetupfee", "qsetupfee", "ssetupfee", "asetupfee", "bsetupfee", "monthly", "quarterly", "semiannually", "annually", "biennially")->get();
    $promoprice = $data[0]->msetupfee;
    if ("0.00" < $data[0]->qsetupfee) {
        $qsetupfee = ($data[0]->qsetupfee - $promoprice) * 2;
    } else {
        $qsetupfee = "-1.00";
    }
    if ("0.00" < $data[0]->ssetupfee) {
        $ssetupfee = ($data[0]->ssetupfee - $promoprice) / 2 * 3;
    } else {
        $ssetupfee = "-1.00";
    }
    if ("0.00" < $data[0]->asetupfee) {
        $asetupfee = ($data[0]->asetupfee - $promoprice) / 3 * 4;
    } else {
        $asetupfee = "-1.00";
    }
    if ("0.00" < $data[0]->bsetupfee) {
        $bsetupfee = ($data[0]->bsetupfee - $promoprice) / 4 * 5;
    } else {
        $bsetupfee = "-1.00";
    }
    if ("0.00" < $data[0]->monthly) {
        $monthly = ($data[0]->monthly - $promoprice) / 5 * 6;
    } else {
        $monthly = "-1.00";
    }
    if ("0.00" < $data[0]->quarterly) {
        $quarterly = ($data[0]->quarterly - $promoprice) / 6 * 7;
    } else {
        $quarterly = "-1.00";
    }
    if ("0.00" < $data[0]->semiannually) {
        $semiannually = ($data[0]->semiannually - $promoprice) / 7 * 8;
    } else {
        $semiannually = "-1.00";
    }
    if ("0.00" < $data[0]->annually) {
        $annually = ($data[0]->annually - $promoprice) / 8 * 9;
    } else {
        $annually = "-1.00";
    }
    if ("0.00" < $data[0]->biennially) {
        $biennially = ($data[0]->biennially - $promoprice) / 9 * 10;
    } else {
        $biennially = "-1.00";
    }
    if ($max_reg_array[$tldstrings]) {
        if ($max_reg_array[$tldstrings][1] == 5) {
            $update = ["msetupfee" => $realsellingprice, "qsetupfee" => $qsetupfee, "ssetupfee" => $ssetupfee, "asetupfee" => $asetupfee, "bsetupfee" => $bsetupfee];
        } else {
            $update = ["msetupfee" => $realsellingprice];
        }
    } else {
        $update = ["msetupfee" => $realsellingprice, "qsetupfee" => $qsetupfee, "ssetupfee" => $ssetupfee, "asetupfee" => $asetupfee, "bsetupfee" => $bsetupfee, "monthly" => $monthly, "quarterly" => $quarterly, "semiannually" => $semiannually, "annually" => $annually, "biennially" => $biennially];
    }
    Illuminate\Database\Capsule\Manager::table("tblpricing")->where("relid", "=", $relid)->where("currency", "=", $currency)->where("type", "=", $type)->where("tsetupfee", "=", "0.00")->update($update);
    if ($is_currencydoupd != "on") {
        domainCurrencyPricingupdate($clientgroupid, 0, $type, $tldrelid);
    }
    $dodelete = Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodspromo")->where("relid", "=", $_POST["relid"])->where("type", "=", $_POST["type"])->delete();
    if ($is_v7 == 1) {
        $update = ["group" => "none"];
        Illuminate\Database\Capsule\Manager::table("tbldomainpricing")->where("id", "=", $relid)->update($update);
    }
    echo "<div class=\"alert alert-success\"><p>" . $LANG["multipromoupdatesuccess1"] . " ";
    echo $_POST["tld"] . " " . $LANG["sellingundoupdatesuccess"] . " " . $currencycode . " " . $_POST["sellingprice"] . "</p></div>";
}
if (isset($_REQUEST["promoprice"]) && empty($_REQUEST["promoprice"])) {
    echo $promoempty_errormessage;
} else if (isset($_POST["manualpromosetup"])) {
    $promoarray = explode("|", $_POST["manualpromosetup"]);
    list($extension, $type, $sellingprice, $relid) = $promoarray;
    $tldrelid = $relid;
    $promoprice = $_POST["promoprice"];
    $promoend = $_POST["promoend"];
    $registrar = "whmcs";
    $resellerid = "0";
    $data = Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodspromo")->where("extension", "=", $extension)->where("type", "=", $type)->select("sellingprice", "registrar", "resellerid")->get();
    if ($is_v7 == 1) {
        $update = ["group" => "sale"];
        Illuminate\Database\Capsule\Manager::table("tbldomainpricing")->where("extension", "=", $extension)->update($update);
    }
    if (empty($data[0]->sellingprice)) {
        $values = ["registrar" => $registrar, "resellerid" => $resellerid, "extension" => $extension, "sellingprice" => $sellingprice, "promoprice" => $promoprice, "relid" => $relid, "type" => $type, "promoend" => $promoend];
        Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodspromo")->insert($values);
        $data = Illuminate\Database\Capsule\Manager::table("tblpricing")->where("relid", "=", $relid)->where("currency", "=", $currency)->where("type", "=", $type)->where("tsetupfee", "=", "0.00")->select("msetupfee", "qsetupfee", "ssetupfee", "asetupfee", "bsetupfee", "monthly", "quarterly", "semiannually", "annually", "biennially")->get();
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
            } else {
                $update = ["msetupfee" => $msetupfee];
            }
        } else {
            $update = ["msetupfee" => $msetupfee, "qsetupfee" => $qsetupfee, "ssetupfee" => $ssetupfee, "asetupfee" => $asetupfee, "bsetupfee" => $bsetupfee, "monthly" => $monthly, "quarterly" => $quarterly, "semiannually" => $semiannually, "annually" => $annually, "biennially" => $biennially];
        }
        Illuminate\Database\Capsule\Manager::table("tblpricing")->where("relid", "=", $relid)->where("currency", "=", $currency)->where("type", "=", $type)->where("tsetupfee", "=", "0.00")->update($update);
        if ($is_currencydoupd != "on") {
            domainCurrencyPricingupdate($clientgroupid, 0, $type, $tldrelid);
        }
        echo "<div class=\"alert alert-success\"><p>" . $LANG["manualpromosuccess"] . " <strong>" . $extension . "</strong> " . $is_domtype . " </p></div>";
    } else {
        if ($type == "domainregister") {
            $is_domtype = $LANG["promvaliddomainregister"];
        }
        if ($type == "domaintransfer") {
            $is_domtype = $LANG["promvaliddomaintransfer"];
        }
        if ($type == "domainrenew") {
            $is_domtype = $LANG["promvaliddomainrenew"];
        }
        echo "<div class=\"alert alert-warning\"><p>" . $LANG["manualpromowarn1"] . " <strong>" . $extension . "</strong> " . $is_domtype . ". " . $LANG["manualpromowarn2"] . "</p></div>";
    }
}
if (isset($_POST["applypromo"]) && !empty($_POST["promotlds"])) {
    $promo_customerprice_change = $_POST["customerpricechange"];
    if (empty($promo_customerprice_change)) {
        $promo_customerprice = $_POST["customerprice"];
    } else {
        $promo_customerprice = $promo_customerprice_change;
    }
    if ($promo_customerprice * 1 <= round(0, 2)) {
        echo $promoempty_errormessage;
    } else {
        $promoend = $_POST["promoend"];
        $mytlds = $_POST["promotlds"];
        $type = $_POST["type"];
        $active_promo_registrar = $_POST["active_promo_registrar"];
        $myfind = ",";
        $coma = strpos($mytlds, $myfind);
        if ($coma !== false) {
            $tldsextensions = explode(",", $mytlds);
        } else {
            $tldsextensions = [$mytlds];
        }
        foreach ($tldsextensions as $tldstrings) {
            $data = Illuminate\Database\Capsule\Manager::table("tbldomainpricing")->where("extension", "=", $tldstrings)->select("id")->get();
            if (!empty($data)) {
                $relid_arr[$tldstrings] = $data[0]->id;
            }
        }
        if (!isset($relid_arr)) {
            echo "<div class=\"alert alert-warning\"><p>" . $LANG["tldnotsetup"] . "</p></div>";
        } else {
            foreach ($relid_arr as $key => $value) {
                if ($is_v7 == 1) {
                    $saleupdate = ["group" => "sale"];
                    Illuminate\Database\Capsule\Manager::table("tbldomainpricing")->where("id", "=", $value)->update($saleupdate);
                }
                $tldrelid = $value;
                $tld = $key;
                $data = Illuminate\Database\Capsule\Manager::table("tblpricing")->where("relid", "=", $tldrelid)->where("currency", "=", $currency)->where("type", "=", $type)->where("tsetupfee", "=", "0.00")->select("msetupfee", "qsetupfee", "ssetupfee", "asetupfee", "bsetupfee", "monthly", "quarterly", "semiannually", "annually", "biennially")->get();
                if (empty($data) || !isset($data[0]->msetupfee) || $data[0]->msetupfee === NULL) {
                    echo "<div class=\"alert alert-warning\"><p>" . $LANG["tldnotsetup"] . "</p></div>";
                } else {
                    $msetupfee = $data[0]->msetupfee;
                    $qsetupfee = $data[0]->qsetupfee;
                    $ssetupfee = $data[0]->ssetupfee;
                    $asetupfee = $data[0]->asetupfee;
                    $bsetupfee = $data[0]->bsetupfee;
                    $monthly = $data[0]->monthly;
                    $quarterly = $data[0]->quarterly;
                    $semiannually = $data[0]->semiannually;
                    $annually = $data[0]->annually;
                    $biennially = $data[0]->biennially;
                    if ("0.00" < $msetupfee) {
                        $update["msetupfee"] = $promo_customerprice;
                    } else {
                        $update["msetupfee"] = $msetupfee;
                    }
                    if ("0.00" < $qsetupfee) {
                        $update["qsetupfee"] = $qsetupfee / 2 + $promo_customerprice;
                    } else {
                        $update["qsetupfee"] = $qsetupfee;
                    }
                    if ("0.00" < $ssetupfee) {
                        $update["ssetupfee"] = $ssetupfee / 3 * 2 + $promo_customerprice;
                    } else {
                        $update["ssetupfee"] = $ssetupfee;
                    }
                    if ("0.00" < $asetupfee) {
                        $update["asetupfee"] = $asetupfee / 4 * 3 + $promo_customerprice;
                    } else {
                        $update["asetupfee"] = $asetupfee;
                    }
                    if ("0.00" < $bsetupfee) {
                        $update["bsetupfee"] = $bsetupfee / 5 * 4 + $promo_customerprice;
                    } else {
                        $update["bsetupfee"] = $bsetupfee;
                    }
                    if ("0.00" < $monthly) {
                        $update["monthly"] = $monthly / 6 * 5 + $promo_customerprice;
                    } else {
                        $update["monthly"] = $monthly;
                    }
                    if ("0.00" < $quarterly) {
                        $update["quarterly"] = $quarterly / 7 * 6 + $promo_customerprice;
                    } else {
                        $update["quarterly"] = $quarterly;
                    }
                    if ("0.00" < $semiannually) {
                        $update["semiannually"] = $semiannually / 8 * 7 + $promo_customerprice;
                    } else {
                        $update["semiannually"] = $semiannually;
                    }
                    if ("0.00" < $annually) {
                        $update["annually"] = $annually / 9 * 8 + $promo_customerprice;
                    } else {
                        $update["annually"] = $annually;
                    }
                    if ("0.00" < $biennially) {
                        $update["biennially"] = $biennially / 10 * 9 + $promo_customerprice;
                    } else {
                        $update["biennially"] = $biennially;
                    }
                    Illuminate\Database\Capsule\Manager::table("tblpricing")->where("relid", "=", $tldrelid)->where("currency", "=", $currency)->where("type", "=", $type)->where("tsetupfee", "=", "0.00")->update($update);
                    $result = Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodspromo")->where("extension", "=", $tld)->where("type", "=", $type)->select("sellingprice")->get();
                    $sellingprice = $result[0]->sellingprice;
                    if (empty($sellingprice)) {
                        $values = ["registrar" => $active_promo_registrar, "resellerid" => $rcauth_userid, "extension" => $tld, "sellingprice" => $msetupfee, "promoprice" => $promo_customerprice, "relid" => $tldrelid, "type" => $type, "promoend" => $promoend];
                        Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodspromo")->insert($values);
                    } else {
                        $values = ["registrar" => $active_promo_registrar, "resellerid" => $rcauth_userid, "promoprice" => $promo_customerprice, "relid" => $tldrelid, "type" => $type, "promoend" => $promoend];
                        Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodspromo")->where("extension", "=", $tld)->where("type", "=", $type)->update($values);
                    }
                    if ($is_currencydoupd != "on") {
                        domainCurrencyPricingupdate($clientgroupid, 0, $type, $tldrelid);
                    }
                    $concat_tlds .= $tld . " ";
                }
            }
            $adminhome = $systemurl . "/" . $customadminpath . "/addonmodules.php?module=resellerclubmods_tools&domain=showpromos&tld=" . $concat_tlds . "&currency=" . $currencycode . "&promoprice=" . $promo_customerprice . "";
            header("Location: " . $adminhome);
            exit;
        }
    }
}
$method = "GET";
if (empty($_SESSION["rcm_promodetails"][$rcauth_userid])) {
    $apifunction = "/api/resellers/promo-details.json";
    $xml_getpromosdetails = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
    $_SESSION["rcm_promodetails"][$rcauth_userid] = serialize($xml_getpromosdetails);
} else {
    $xml_getpromosdetails = unserialize($_SESSION["rcm_promodetails"][$rcauth_userid]);
}
$thirdlevelpromos = [];
$toplevelpromos = [];
if (!empty($xml_getpromosdetails)) {
    foreach ($xml_getpromosdetails as $promos) {
        $starttime = $promos["starttime"];
        $endtime = $promos["endtime"];
        if ($currencyswitch == "on") {
            $customerprice = round((float) $promos["customerprice"] * $multiplicator / $currencyrate, 2);
            $barrierprice = $reseller_buycurrency . " " . round((float) $promos["barrierprice"] * $multiplicator / $currencyrate, 2);
        } else {
            $customerprice = round((float) $promos["customerprice"] * $multiplicator, 2);
            $barrierprice = round((float) $promos["barrierprice"] * $multiplicator, 2);
        }
        if ($promos["actiontype"] == "addnewdomain") {
            $domtype = "domainregister";
        } else if ($promos["actiontype"] == "addtransferdomain") {
            $domtype = "domaintransfer";
        } else if ($promos["actiontype"] == "renewdomain") {
            $domtype = "domainrenew";
        }
        $period = $promos["period"];
        $actiontype = $promos["actiontype"];
        $productkey = $promos["productkey"];
        $promo_plans[] = ["Domain extension" => rtrim($productkeys[$productkey], ","), "starttime" => date("Y-m-d H:i:s", $starttime), "endtime" => date("Y-m-d H:i:s", $endtime), "customerprice" => $customerprice, "barrierprice" => $barrierprice, "period" => $period, "actiontype" => $actiontype, "type" => $domtype, "productkey" => $productkey];
    }
    foreach ($promo_plans as $promo_tlds) {
        if (strpos($promo_tlds["Domain extension"], ",") !== false) {
            $thirdlevelpromos = explode(",", $promo_tlds["Domain extension"]);
        }
        $toplevelpromos[] = $promo_tlds["Domain extension"];
    }
    $promotlds = array_merge($toplevelpromos, $thirdlevelpromos);
}
$domainextensions = [];
foreach (Illuminate\Database\Capsule\Manager::table("tbldomainpricing")->select("id", "extension")->orderBy("extension", "asc")->get() as $data) {
    $domainextensions[$data->id] = $data->extension;
}
foreach ($domainextensions as $relid => $tld) {
    foreach (Illuminate\Database\Capsule\Manager::table("tblpricing")->where("relid", "=", $relid)->where("currency", "=", $currency)->where("tsetupfee", "=", "0.00")->select("type", "msetupfee")->get() as $data) {
        if (0 < $data->msetupfee) {
            $option .= "<option value=\"" . $tld . "|" . $data->type . "|" . $data->msetupfee . "|" . $relid . "\">" . $tld . " - " . $data->type . " - Active Selling Price: " . $currencycode . " " . $data->msetupfee . "</option>\n";
        }
    }
}
if ($promo_auto_activate == "on") {
    $is_promoactivate = "<span " . $style_labelok . ">" . $LANG["activated"] . "</span>";
} else {
    $is_promoactivate = "<span " . $style_labelwarn . ">" . $LANG["disabled"] . "</span>";
}
if ($promo_update_active == "on") {
    $is_promoactive = "<span " . $style_labelok . ">" . $LANG["activated"] . "</span>";
} else {
    $is_promoactive = "<span " . $style_labelwarn . ">" . $LANG["disabled"] . "</span>";
}
if ($is_currencydoupd != "on") {
    $currency_update_active = "<span " . $style_labelok . ">" . $LANG["activated"] . "</span>";
} else {
    $currency_update_active = "<span " . $style_labelwarn . ">" . $LANG["disabled"] . "</span>";
}
if (isset($_REQUEST["tld"]) && isset($_REQUEST["currency"]) && isset($_REQUEST["promoprice"])) {
    echo "<div class=\"alert alert-success\"><p>" . $LANG["multipromoupdatesuccess1"] . " <strong>" . $_REQUEST["tld"] . "</strong> " . $LANG["multipromoupdatesuccess2"] . " " . $_REQUEST["currency"] . " " . $_REQUEST["promoprice"] . "</p></div>";
}
echo $configuredto;
echo "<h1>" . $LANG["domainpromos"] . "</h1>";
echo "<div class=\"alert alert-info\">";
echo "<p><strong>" . $LANG["domainpricelistwidget"] . "</strong><br />" . $LANG["domainpromospricelistdesc"] . " <a target=\"_blank\" href=\"https://www.resellerclub-mods.com/whmcs/resellerclub-tools-docs.php\">Howto install FREE RC & LB Tools</a></p>";
echo "</div>";
echo "<p><strong>" . $LANG["autopromoactivate"] . "</strong> <img class=\"absmiddle\" style=\"cursor:help;\" src=\"../modules/addons/resellerclubmods_tools/img/help.png\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["autoactivatepromohelp"] . "\" height=\"16\" width=\"16\" /> " . $is_promoactivate . "<br />";
echo "<strong>" . $LANG["autoupdatepromo"] . "</strong> <img class=\"absmiddle\" style=\"cursor:help;\" src=\"../modules/addons/resellerclubmods_tools/img/help.png\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["autoupdatepromohelp"] . "\" height=\"16\" width=\"16\" /> " . $is_promoactive . "<br />";
echo "<strong>" . $LANG["currencyupdatesellingprices"] . "</strong> <img class=\"absmiddle\" style=\"cursor:help;\" src=\"../modules/addons/resellerclubmods_tools/img/help.png\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["currencyupdatehelp"] . "\" height=\"16\" width=\"16\" /> " . $currency_update_active . "<br /></p>";
$inpromotlds = [];
foreach (Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodspromo")->orderBy("extension", "asc")->get() as $data) {
    $inpromotlds[] = json_decode(json_encode($data), true);
}
echo "<div style=\"padding: 5px 20px;border: 1px solid #CCCCCC;-moz-border-radius: 5px;-webkit-border-radius: 5px;-o-border-radius: 5px;border-radius: 5px;\">";
echo "<br /><h3 style=\"border-bottom: 1px solid #cccccc;\"><strong>" . $LANG["currentactivepromos"] . "</strong></h3>";
if (isset($inpromotlds[0])) {
    echo "<div class=\"alert alert-warning\">" . $LANG["promoupdatetable01"] . "<ul><li>" . $LANG["promoupdatetable02"] . "</li><li>" . $LANG["promoupdatetable03"] . "</li><li>" . $LANG["promoupdatetable04"] . "</li></ul></div>";
    echo "<table class=\"datatable\" border=\"0\" cellpadding=\"3\" cellspacing=\"1\" width=\"100%\"><tr><th>" . $LANG["activepromoregistrartitle"] . "</th><th>" . $LANG["tldextensions"] . "</th><th>" . $LANG["normalsellingprice"] . "</th><th>" . $LANG["customerprice"] . "</th><th>" . $LANG["appliesto"] . "</th><th>" . $LANG["promoend"] . "</th><th>" . $LANG["actionword"] . "</th></tr>";
    foreach ($inpromotlds as $oldpromotlds) {
        $is_reseller_id = "";
        if (!empty($oldpromotlds["resellerid"])) {
            $is_reseller_id = " (ID " . $oldpromotlds["resellerid"] . ")";
        }
        echo "<tr>";
        echo "<td>" . $oldpromotlds["registrar"] . " " . $is_reseller_id . "</td>";
        echo "<td>" . $oldpromotlds["extension"] . "</td>";
        echo "<td>" . $oldpromotlds["sellingprice"] . "</td>";
        echo "<td><span style=\"font-weight:bold;color:#46A546\">" . $oldpromotlds["promoprice"] . "</span></td>";
        echo "<td>" . $oldpromotlds["type"] . "</td>";
        foreach ($promo_plans as $rcpromos) {
            if (strpos($rcpromos["Domain extension"], ",") !== false && strpos($rcpromos["Domain extension"], $oldpromotlds["extension"]) !== false && $oldpromotlds["type"] == $rcpromos["type"]) {
                $isvalid = [$oldpromotlds["extension"]];
            } else if (preg_match("/^" . $rcpromos["Domain extension"] . "\$/", $oldpromotlds["extension"]) && $oldpromotlds["type"] == $rcpromos["type"]) {
                $isvalid = [$oldpromotlds["extension"]];
            } else {
                $isvalid = [];
            }
            if (!in_array($oldpromotlds["extension"], $isvalid) || in_array($oldpromotlds["extension"], $isvalid) && $oldpromotlds["type"] != $rcpromos["type"]) {
                if ($datenow <= $oldpromotlds["promoend"]) {
                    echo "<td><span style=\"color:#438743;\">" . $oldpromotlds["promoend"] . " UTC</span></td>";
                } else {
                    echo "<td><span style=\"color:#cc0000;\">" . $LANG["promoregistrarend"] . "</span></td>";
                }
                echo "<td>";
                echo "<form action=\"addonmodules.php?module=resellerclubmods_tools&domain=showpromos\" method=\"post\">\r\n\t\t\t\t <input type=\"hidden\" name=\"sellingprice\" value=\"" . $oldpromotlds["sellingprice"] . "\"/>\r\n\t\t\t\t <input type=\"hidden\" name=\"type\" value=\"" . $oldpromotlds["type"] . "\"/>\r\n\t\t\t \t <input type=\"hidden\" name=\"relid\" value=\"" . $oldpromotlds["relid"] . "\"/>\r\n\t\t\t\t <input type=\"hidden\" name=\"tld\" value=\"" . $oldpromotlds["extension"] . "\"/>\r\n\t\t\t \t <input class=\"btn btn-danger btn-sm\" name=\"undoupd\" value=\"" . $LANG["upddombutton"] . "\" type=\"submit\"></form>";
                echo "</td>";
            } else if ($oldpromotlds["promoend"] <= $datenow) {
                echo "<td><span style=\"color:#cc0000;\">" . $oldpromotlds["promoend"] . " UTC</span></td>";
                echo "<td>";
                echo "<form action=\"addonmodules.php?module=resellerclubmods_tools&domain=showpromos\" method=\"post\">\r\n\t\t\t\t <input type=\"hidden\" name=\"sellingprice\" value=\"" . $oldpromotlds["sellingprice"] . "\"/>\r\n\t\t\t\t <input type=\"hidden\" name=\"type\" value=\"" . $oldpromotlds["type"] . "\"/>\r\n\t\t\t \t <input type=\"hidden\" name=\"relid\" value=\"" . $oldpromotlds["relid"] . "\"/>\r\n\t\t\t\t <input type=\"hidden\" name=\"tld\" value=\"" . $oldpromotlds["extension"] . "\"/>\r\n\t\t\t \t <input class=\"btn btn-danger btn-sm\" name=\"undoupd\" value=\"" . $LANG["upddombutton"] . "\" type=\"submit\"></form>";
                echo "</td>";
            } else {
                echo "<td><span style=\"color:#438743;\">" . $oldpromotlds["promoend"] . " UTC</span></td>";
                echo "<td>" . $LANG["stillvalid"] . "</td>";
            }
            echo "</tr>";
        }
    }
    echo "</table><br />";
} else {
    echo "<div class=\"gracefulexit\">" . $LANG["noactivewhmcspromos"] . "</div>";
}
echo "</div><br />";
$enddate = date("Y-m-t") . " 23:59:59";
echo "<div style=\"padding: 5px 20px;border: 1px solid #CCCCCC;-moz-border-radius: 5px;-webkit-border-radius: 5px;-o-border-radius: 5px;border-radius: 5px;\">";
echo "<br /><h3 style=\"border-bottom: 1px solid #cccccc;\"><strong>" . $LANG["promomanualtitle"] . "</strong></h3>";
echo "<div style=\"width:100%\"><div class=\"homewidget\"><div class=\"widget-header\">";
echo "<span style=\"font-size:16px;\">" . $promo_arr["Domain extension"] . "</span></div>";
echo "<div class=\"widget-content\"><div><form action=\"addonmodules.php?module=resellerclubmods_tools&domain=showpromos\" method=\"post\"><table class=\"datatable\" border=\"0\" cellpadding=\"3\" cellspacing=\"1\" width=\"100%\">";
echo "<tr><th>" . $LANG["activepromoregistrartitle"] . "</th><th>manual</th></tr>";
echo "<tr><td>" . $LANG["promoend"] . "</td><td><input class=\"form-control\" style=\"display: inline; width: auto\" size=\"30\" type=\"text\" name=\"promoend\" value=\"" . $enddate . "\" />&nbsp;UTC " . $LANG["promodateformat"] . "</td></tr>";
echo "<tr><td>Select TLD and Domain type</td><td><select class=\"form-control\" style=\"display: inline; width: auto\" name=\"manualpromosetup\">" . $option . "</select></td></tr>";
echo "<tr><td>" . $LANG["customerprice"] . "</td><td>" . $currencycode . " <input class=\"form-control\" style=\"display: inline; width: auto\" size=\"5\" type=\"text\" name=\"promoprice\" /> " . $LANG["promopriceformat"] . "</td></tr>";
echo "<tr><td colspan=\"2\"><div align=\"center\" width=\"100%\"><input value=\"" . $LANG["promoapplybutton"] . " " . $LANG["enablelabel"] . "\" type=\"submit\" class=\"btn btn-success\" /></div></td></tr>";
echo "</table></form></div></div></div></div></div><br /><a id=\"top\"></a><div style=\"padding: 5px 20px;border: 1px solid #CCCCCC;-moz-border-radius: 5px;-webkit-border-radius: 5px;-o-border-radius: 5px;border-radius: 5px;\">";
echo "<br /><h3 style=\"border-bottom: 1px solid #cccccc;\"><strong>" . $LANG["availabledomainpromos"] . "</strong> (" . $LANG["reselleraccount"] . " ID " . $rcauth_userid . ")</h3>";
$in_whmcs_extensions = [];
foreach (Illuminate\Database\Capsule\Manager::table("tbldomainpricing")->select("extension")->get() as $ext_data) {
    $in_whmcs_extensions[] = $ext_data->extension;
}
if (!empty($xml_getpromosdetails)) {
    echo "<p>" . $LANG["promointro1"] . " \"<strong>" . $LANG["undopromobutton"] . " " . $LANG["enablelabel"] . "</strong>\"<br />" . $LANG["promointro2"] . " <strong>\"" . $LANG["undopromobutton"] . " " . $LANG["disablelabel"] . "\"</strong></p>";
    asort($promo_plans);
    echo "<div style=\"line-height:20px;\">" . $LANG["promotldaccess"] . "&nbsp;";
    $tldnum0 = 0;
    foreach ($promo_plans as $promo_arr) {
        $mytlds = $promo_arr["Domain extension"];
        $myfind = ",";
        $coma = strpos($mytlds, $myfind);
        if ($coma !== false) {
            $tldsextensions = explode(",", $mytlds);
            if (1 < count($tldsextensions)) {
                $tldsextensions[0] = $promo_arr["productkey"];
            }
        } else {
            $tldsextensions = [$mytlds];
        }
        foreach ($tldsextensions as $tldsextension) {
            if ($promo_arr["actiontype"] == "addnewdomain") {
                $promovalidfor = $LANG["promvaliddomainregister"];
                $promostatus = "label pending";
                $icon = "<i class=\"fa fa-globe\"></i>";
                $is_promo_status = $LANG["disabled"] . " - ";
                $result = Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodspromo")->where("extension", "=", $tldsextension)->where("type", "=", "domainregister")->select("extension", "type")->get();
                $data = json_decode(json_encode($result), true);
                if ($data) {
                    $promostatus = "label active";
                    $is_promo_status = $LANG["activated"] . " - ";
                }
            }
            if ($promo_arr["actiontype"] == "addtransferdomain") {
                $promovalidfor = $LANG["promvaliddomaintransfer"];
                $promostatus = "label pending";
                $icon = "<i class=\"fa fa-share\"></i>";
                $is_promo_status = $LANG["disabled"] . " - ";
                $result = Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodspromo")->where("extension", "=", $tldsextension)->where("type", "=", "domaintransfer")->select("extension", "type")->get();
                $data = json_decode(json_encode($result), true);
                if ($data) {
                    $promostatus = "label active";
                    $is_promo_status = $LANG["activated"] . " - ";
                }
            }
            if ($promo_arr["actiontype"] == "renewdomain") {
                $promovalidfor = $LANG["promvaliddomainrenew"];
                $promostatus = "label pending";
                $icon = $sync_icon;
                $is_promo_status = $LANG["disabled"] . " - ";
                $result = Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodspromo")->where("extension", "=", $tldsextension)->where("type", "=", "domainrenew")->select("extension", "type")->get();
                $data = json_decode(json_encode($result), true);
                if ($data) {
                    $promostatus = "label active";
                    $is_promo_status = $LANG["activated"] . " - ";
                }
            }
            $statustitle = $is_promo_status . $promovalidfor . " " . $LANG["domainpromofor"] . " " . str_replace(",", " - ", $promo_arr["Domain extension"]);
        }
        echo "&nbsp;<a style=\"color:#ffffff;font-weight:bold;\" class=\"scroll " . $promostatus . "\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"" . $statustitle . "\" href=\"#tld" . $tldnum0++ . "\">" . $icon . " &nbsp;" . $tldsextensions[0] . "</a> &nbsp;";
    }
    echo "</div><br /><br />";
    $tldnum1 = 0;
    foreach ($promo_plans as $promo_arr) {
        $mytlds = $promo_arr["Domain extension"];
        $myfind = ",";
        $coma = strpos($mytlds, $myfind);
        if ($coma !== false) {
            $tldsextensions = explode(",", $mytlds);
        } else {
            $tldsextensions = [$mytlds];
        }
        foreach ($tldsextensions as $tldsextension) {
            if (in_array($tldsextension, $in_whmcs_extensions)) {
                $inactive = "style=\"width:100%;\"";
                $inactive_message = "";
                $button_disable = "";
                if ($promo_arr["actiontype"] == "addnewdomain") {
                    $promovalidfor = $LANG["promvaliddomainregister"];
                    $icon = "<i class=\"fa fa-globe\"></i>";
                } else if ($promo_arr["actiontype"] == "addtransferdomain") {
                    $promovalidfor = $LANG["promvaliddomaintransfer"];
                    $icon = "<i class=\"fa fa-share\"></i>";
                } else if ($promo_arr["actiontype"] == "renewdomain") {
                    $promovalidfor = $LANG["promvaliddomainrenew"];
                    $icon = $sync_icon;
                }
                $data = Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodspromo")->where("extension", "=", $tldsextensions[0])->where("type", "=", $promo_arr["type"])->select("extension", "sellingprice", "promoprice", "type")->get();
                $realsellingprice = $data[0]->sellingprice;
                $realpromoprice = $data[0]->promoprice;
                $realpromotype = $data[0]->type;
                $data = Illuminate\Database\Capsule\Manager::table("tbldomainpricing")->where("extension", "=", $tldsextensions[0])->select("id")->get();
                $relid = $data[0]->id;
                $data = Illuminate\Database\Capsule\Manager::table("tblpricing")->where("relid", "=", $relid)->where("currency", "=", $currency)->where("type", "=", $promo_arr["type"])->where("tsetupfee", "=", "0.00")->select("msetupfee")->get();
                $active_selling_price = round($data[0]->msetupfee, 2);
                if ($promo_arr["period"] == "1") {
                    $periodvalidfor = $LANG["promvalidfirstyear"];
                } else {
                    $periodvalidfor = "Unknown (not implemented)";
                }
                if (empty($realpromoprice)) {
                    $realpromoprice = $promo_arr["customerprice"];
                }
                $promo_customerprice = round($realpromoprice, 2);
                $sellingprice = round($realsellingprice, 2);
                if ($active_selling_price < $sellingprice) {
                    $is_active_price = $promo_customerprice;
                } else {
                    $is_active_price = $sellingprice;
                }
                $normal_sellingprice_a = "<tr><td>" . $LANG["normalsellingprice"] . " <img class=\"absmiddle\" style=\"cursor:help;\" src=\"../modules/addons/resellerclubmods_tools/img/help.png\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["normalsellingpricehelp"] . "\" height=\"16\" width=\"16\" /></td><td>" . $currencycode . " " . $sellingprice . "</td></tr>";
                $normal_sellingprice_b = "<tr><td><span style=\"font-weight:bold;\">" . $LANG["actualsellingprice"] . " <img class=\"absmiddle\" style=\"cursor:help;\" src=\"../modules/addons/resellerclubmods_tools/img/help.png\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["actualsellingpricehelp"] . "\" height=\"16\" width=\"16\" /></span></td><td><span style=\"font-weight:bold;\">" . $currencycode . " " . $active_selling_price . "</span></td></tr>";
                $actual_sellingprice = "<tr><td><span style=\"font-weight:bold;\">" . $LANG["actualsellingprice"] . " <img class=\"absmiddle\" style=\"cursor:help;\" src=\"../modules/addons/resellerclubmods_tools/img/help.png\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["actualsellingpricehelp"] . "\" height=\"16\" width=\"16\" /></span></td><td><span style=\"font-weight:bold;\">" . $currencycode . " " . $is_active_price . "</span></td></tr>";
                $promo_sellingprice_change = "<tr><td>" . $LANG["customerpricechange"] . " <img class=\"absmiddle\" style=\"cursor:help;\" src=\"../modules/addons/resellerclubmods_tools/img/help.png\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["customerpricechangehelp"] . "\" height=\"16\" width=\"16\" /></td><td>" . $currencycode . " <input class=\"form-control\" style=\"display: inline; width: auto\" type=\"text\" size=\"5\" name=\"customerpricechange\" value=\"\" /></td></tr>";
                if (empty($realsellingprice) && empty($realpromotype)) {
                    $promo_sellingprice = "<tr><td>" . $LANG["customerprice"] . " <img class=\"absmiddle\" style=\"cursor:help;\" src=\"../modules/addons/resellerclubmods_tools/img/help.png\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["customerpricehelp"] . "\" height=\"16\" width=\"16\" /></td><td>" . $currencycode . " " . $promo_customerprice . "</td></tr>";
                    $apply_style = "style=\"border: solid 1px #F89406;\"";
                    $labeled = "class=\"label pending\"";
                    $apply_button = "<div style=\"padding:3px;\">\r\n\t\t\t<input type=\"hidden\" name=\"customerprice\" value=\"" . $promo_customerprice . "\"/>\r\n\t\t\t<input type=\"hidden\" name=\"active_promo_registrar\" value=\"" . $logicbox_registrar . "\"/>\r\n\t\t\t<input type=\"hidden\" name=\"type\" value=\"" . $promo_arr["type"] . "\"/>\r\n\t\t\t<input type=\"hidden\" name=\"promotlds\" value=\"" . $promo_arr["Domain extension"] . "\"/>\r\n\t\t\t<input type=\"hidden\" name=\"promoend\" value=\"" . $promo_arr["endtime"] . "\"/>\r\n\t\t\t<input class=\"btn btn-warning\" name=\"applypromo\" value=\"" . $LANG["promoapplybutton"] . " " . $LANG["enablelabel"] . "\" type=\"submit\" " . $button_disable . ">\r\n\t\t\t</div>";
                }
                if (!empty($realpromoprice) && $realpromotype == $promo_arr["type"]) {
                    $promo_sellingprice = "<tr><td>" . $LANG["customerprice"] . " <img class=\"absmiddle\" style=\"cursor:help;\" src=\"../modules/addons/resellerclubmods_tools/img/help.png\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["customerpricehelp"] . "\" height=\"16\" width=\"16\" /></td><td>" . $currencycode . " " . round($promo_arr["customerprice"], 2) . " (" . $LANG["promopriceapplied"] . " " . $promo_customerprice . ")</td></tr>";
                    $apply_style = "style=\"border: solid 1px #46A546;\"";
                    $labeled = "class=\"label active\"";
                    $apply_button = "<div style=\"padding:3px;\">\r\n\t\t\t<input type=\"hidden\" name=\"sellingprice\" value=\"" . $sellingprice . "\"/>\r\n\t\t\t<input type=\"hidden\" name=\"active_promo_registrar\" value=\"" . $logicbox_registrar . "\"/>\r\n\t\t\t<input type=\"hidden\" name=\"type\" value=\"" . $promo_arr["type"] . "\"/>\r\n\t\t\t<input type=\"hidden\" name=\"promotlds\" value=\"" . $promo_arr["Domain extension"] . "\"/>\r\n\t\t\t<input type=\"hidden\" name=\"promoend\" value=\"" . $promo_arr["endtime"] . "\"/>\r\n\t\t\t<input class=\"btn btn-success\" name=\"undopromo\" value=\"" . $LANG["undopromobutton"] . " " . $LANG["disablelabel"] . "\" type=\"submit\">\r\n\t\t\t</div>";
                }
                echo "<a id=\"tld" . $tldnum1++ . "\"></a>";
                echo "<div " . $inactive . ">";
                echo "<div class=\"homewidget\"><div class=\"widget-header\">";
                echo "<a style=\"text-decoration:none;\" class=\"scroll\" href=\"#top\">(top)</a>&nbsp;&nbsp;<span style=\"font-size:14px;opacity: 0.5\">" . str_replace(",", " - ", $promo_arr["Domain extension"]) . "</span></div>";
                echo "<div class=\"widget-content\"><div>";
                echo $inactive_message;
                echo "<form action=\"addonmodules.php?module=resellerclubmods_tools&domain=showpromos\" method=\"post\"><table class=\"datatable\" border=\"0\" cellpadding=\"3\" cellspacing=\"1\" width=\"100%\">";
                echo "<tr><th>" . $LANG["activepromoregistrartitle"] . "</th><th>" . $logicbox_registrar . "</th></tr>";
                echo "<tr><td>" . $LANG["promostart"] . " - " . $LANG["promoend"] . "</td><td>" . $LANG["fromdate"] . " <strong>" . $promo_arr["starttime"] . " UTC</strong> " . $LANG["untildate"] . " <strong>" . $promo_arr["endtime"] . " UTC</strong></td></tr>";
                echo $promo_sellingprice;
                if ($sellingprice != "0.00" && $realpromotype == $promo_arr["type"]) {
                    echo $normal_sellingprice_a;
                    echo $actual_sellingprice;
                } else {
                    echo $promo_sellingprice_change;
                    echo $normal_sellingprice_b;
                }
                echo "<tr><td>" . $LANG["appliesto"] . "</td><td><span style=\"font-size:14px;font-weight:bold;\" " . $labeled . ">" . $icon . " " . $promovalidfor . " - " . $periodvalidfor . "</span></td></tr>";
                echo "<tr>";
                echo "<td colspan=\"2\">\r\n\t\t\t <div align=\"center\" width=\"100%\" " . $apply_style . ">";
                echo $apply_button;
                echo "</div></table></form></div></div></div></div>";
            } else {
                $inactive = "style=\"width:100%;opacity: 0.4;\"";
                $inactive_message = "<div style=\"position:static;text-align:center;background:#C43C35;padding:10px;\"><a style=\"font-size:16px;font-weight:bold;color:#fff;\" href=\"addonmodules.php?module=resellerclubmods_tools&domain=domain-pricing-import#" . $tldsextension . "\">" . $LANG["tldnotsetupmessage"] . "</a></div>";
                $button_disable = "disabled=\"disabled\"";
            }
        }
    }
    echo "</div><br />";
} else {
    echo "<div class=\"gracefulexit\">" . $LANG["nopromosthere"] . " (ID " . $rcauth_userid . ")</div></div><br />";
}
date_default_timezone_set($timezone);

?>