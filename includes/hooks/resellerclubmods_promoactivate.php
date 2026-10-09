<?php
if (!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
require ROOTDIR . "/modules/addons/resellerclubmods_tools/incs/functions.php";
add_hook("DailyCronJob", 2, "promo_auto_activate");
function promo_auto_activate($vars)
{
    $conf = [];
    foreach (Illuminate\Database\Capsule\Manager::table("tbladdonmodules")->where("module", "resellerclubmods_tools")->get() as $addonvars) {
        $conf[$addonvars->setting] = $addonvars->value;
    }
    $active_promo_registrar = $conf["first_domainregistrar"];
    $rcauth_userid = $conf["first_rcauth_userid"];
    $rcauth_password = $conf["first_rcauth_apikey"];
    $rchttp_api = $conf["rchttp_api"];
    $currencyswitch = $conf["first_currencyswitch"];
    $multiplicator = (float) $conf["first_multiplicator"];
    $defaultcurrency = $conf["first_defaultcurrency"];
    $is_currencydoupd = $conf["domainsync_currencydoupd"];
    $promo_auto_activate = $conf["promo_auto_activate"];
    $maileradmin = $conf["maileradmin"];
    $clientgroupid = "0.00";
    if ($promo_auto_activate == "on") {
        $rcm_daily_lock = rcm_try_lock("dailycron_hooks");
        if ($rcm_daily_lock === false) {
            logActivity("Cron Job (RCM): Skipping Auto Promo Activation — daily hooks busy");
            return;
        }
        try {
        if (0 <= version_compare(getWver(), "7.0.0")) {
            $is_v7 = 1;
        }
        $result = Illuminate\Database\Capsule\Manager::table("tbladmins")->where("username", "=", $maileradmin)->select("language")->get();
        $adminlang = $result[0]->language;
        $get_langfile = getAdminlang($adminlang);
        require ROOTDIR . $get_langfile;
        $LANG = $_ADDONLANG;
        $clientgroupid = "0.00";
        $reseller_tblcurrencies = Illuminate\Database\Capsule\Manager::table("tblcurrencies")->where("code", "=", $defaultcurrency)->select("rate")->get();
        $currencyrate = (float) $reseller_tblcurrencies[0]->rate;
        $fila_tblcurrencies = Illuminate\Database\Capsule\Manager::table("tblcurrencies")->where("default", "=", 1)->select("id")->get();
        $currency = $fila_tblcurrencies[0]->id;
        $in_whmcs_extensions = [];
        foreach (Illuminate\Database\Capsule\Manager::table("tbldomainpricing")->select("extension", "id")->get() as $ext_data) {
            $in_whmcs_extensions[$ext_data->id] = $ext_data->extension;
        }
        $apifunction = "/api/resellers/promo-details.json";
        $xml_getpromosdetails = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
        $productkeys = newproductkeys($rcauth_userid, $rcauth_password, $rchttp_api);
        if (!empty($xml_getpromosdetails)) {
            foreach ($xml_getpromosdetails as $promos) {
                $start = "@" . $promos["starttime"];
                $end = "@" . $promos["endtime"];
                $starttime = new DateTime($start);
                $starttime->setTimeZone(new DateTimeZone("UTC"));
                $endtime = new DateTime($end);
                $endtime->setTimeZone(new DateTimeZone("UTC"));
                if ($currencyswitch == "on") {
                    $customerprice = round($promos["customerprice"] * $multiplicator / $currencyrate, 2);
                } else {
                    $customerprice = round($promos["customerprice"] * $multiplicator, 2);
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
                $tld = rtrim($productkeys[$productkey], ",");
                $tld_id = array_search($tld, $in_whmcs_extensions);
                if (is_array($in_whmcs_extensions) && in_array($tld, $in_whmcs_extensions)) {
                    $promo_plans[] = ["tld" => $tld, "id" => $tld_id, "starttime" => $starttime->format("Y-m-d H:i:s"), "endtime" => $endtime->format("Y-m-d H:i:s"), "customerprice" => $customerprice, "period" => $period, "actiontype" => $actiontype, "type" => $domtype, "productkey" => $productkey];
                }
                if (2 < substr_count($tld, ",")) {
                    $multi_tld = explode(",", $tld);
                    foreach ($multi_tld as $tld) {
                        $tld_id = array_search($tld, $in_whmcs_extensions);
                        if (is_array($in_whmcs_extensions) && in_array($tld, $in_whmcs_extensions)) {
                            $promo_plans[] = ["tld" => $tld, "id" => $tld_id, "starttime" => $starttime->format("Y-m-d H:i:s"), "endtime" => $endtime->format("Y-m-d H:i:s"), "customerprice" => $customerprice, "period" => $period, "actiontype" => $actiontype, "type" => $domtype, "productkey" => $productkey];
                        }
                    }
                }
            }
            foreach ($promo_plans as $promodetails) {
                $mod_resellerclubmodspromo = Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodspromo")->where("relid", "=", $promodetails["id"])->where("type", "=", $promodetails["type"])->select("extension", "promoprice")->get();
                $is_promo_tld = $mod_resellerclubmodspromo[0]->extension;
                $is_old_promoprice = $mod_resellerclubmodspromo[0]->promoprice;
                $tbldata = Illuminate\Database\Capsule\Manager::table("tblpricing")->where("relid", "=", $promodetails["id"])->where("currency", "=", $currency)->where("type", "=", $promodetails["type"])->where("tsetupfee", "=", "0.00")->select("msetupfee", "qsetupfee", "ssetupfee", "asetupfee", "bsetupfee", "monthly", "quarterly", "semiannually", "annually", "biennially")->get();
                $relid = $promodetails["id"];
                $tld = $promodetails["tld"];
                $type = $promodetails["type"];
                $endtime = $promodetails["endtime"];
                $promoprice = $promodetails["customerprice"];
                $msetupfee = $tbldata[0]->msetupfee;
                $qsetupfee = $tbldata[0]->qsetupfee;
                $ssetupfee = $tbldata[0]->ssetupfee;
                $asetupfee = $tbldata[0]->asetupfee;
                $bsetupfee = $tbldata[0]->bsetupfee;
                $monthly = $tbldata[0]->monthly;
                $quarterly = $tbldata[0]->quarterly;
                $semiannually = $tbldata[0]->semiannually;
                $annually = $tbldata[0]->annually;
                $biennially = $tbldata[0]->biennially;
                if ($is_v7 == 1) {
                    $saleupdate = ["group" => "sale"];
                    Illuminate\Database\Capsule\Manager::table("tbldomainpricing")->where("id", "=", $relid)->update($saleupdate);
                }
                if ($is_promo_tld) {
                    $promo_actiontype = "updated";
                    if ("0.00" < $msetupfee) {
                        $update["msetupfee"] = $promoprice;
                    } else {
                        $update["msetupfee"] = $msetupfee;
                    }
                    if ("0.00" < $qsetupfee) {
                        $qsetupfee = $qsetupfee - $is_old_promoprice * 2 + $promoprice;
                    } else {
                        $update["qsetupfee"] = $qsetupfee;
                    }
                    if ("0.00" < $ssetupfee) {
                        $ssetupfee = $ssetupfee - $is_old_promoprice * 3 + $promoprice;
                    } else {
                        $update["qsetupfee"] = $ssetupfee;
                    }
                    if ("0.00" < $asetupfee) {
                        $asetupfee = $asetupfee - $is_old_promoprice * 4 + $promoprice;
                    } else {
                        $update["qsetupfee"] = $asetupfee;
                    }
                    if ("0.00" < $bsetupfee) {
                        $bsetupfee = $bsetupfee - $is_old_promoprice * 5 + $promoprice;
                    } else {
                        $update["qsetupfee"] = $bsetupfee;
                    }
                    if ("0.00" < $monthly) {
                        $monthly = $monthly - $is_old_promoprice * 6 + $promoprice;
                    } else {
                        $update["qsetupfee"] = $monthly;
                    }
                    if ("0.00" < $quarterly) {
                        $quarterly = $quarterly - $is_old_promoprice * 7 + $promoprice;
                    } else {
                        $update["qsetupfee"] = $quarterly;
                    }
                    if ("0.00" < $semiannually) {
                        $semiannually = $semiannually - $is_old_promoprice * 8 + $promoprice;
                    } else {
                        $update["qsetupfee"] = $semiannually;
                    }
                    if ("0.00" < $annually) {
                        $annually = $annually - $is_old_promoprice * 9 + $promoprice;
                    } else {
                        $update["qsetupfee"] = $annually;
                    }
                    if ("0.00" < $biennially) {
                        $biennially = $biennially - $is_old_promoprice * 10 + $promoprice;
                    } else {
                        $update["qsetupfee"] = $biennially;
                    }
                } else {
                    $promo_actiontype = "activated";
                    if ("0.00" < $msetupfee) {
                        $update["msetupfee"] = $promoprice;
                    } else {
                        $update["msetupfee"] = $msetupfee;
                    }
                    if ("0.00" < $qsetupfee) {
                        $update["qsetupfee"] = $qsetupfee / 2 + $promoprice;
                    } else {
                        $update["qsetupfee"] = $qsetupfee;
                    }
                    if ("0.00" < $ssetupfee) {
                        $update["ssetupfee"] = $ssetupfee / 3 * 2 + $promoprice;
                    } else {
                        $update["ssetupfee"] = $ssetupfee;
                    }
                    if ("0.00" < $asetupfee) {
                        $update["asetupfee"] = $asetupfee / 4 * 3 + $promoprice;
                    } else {
                        $update["asetupfee"] = $asetupfee;
                    }
                    if ("0.00" < $bsetupfee) {
                        $update["bsetupfee"] = $bsetupfee / 5 * 4 + $promoprice;
                    } else {
                        $update["bsetupfee"] = $bsetupfee;
                    }
                    if ("0.00" < $monthly) {
                        $update["monthly"] = $monthly / 6 * 5 + $promoprice;
                    } else {
                        $update["monthly"] = $monthly;
                    }
                    if ("0.00" < $quarterly) {
                        $update["quarterly"] = $quarterly / 7 * 6 + $promoprice;
                    } else {
                        $update["quarterly"] = $quarterly;
                    }
                    if ("0.00" < $semiannually) {
                        $update["semiannually"] = $semiannually / 8 * 7 + $promoprice;
                    } else {
                        $update["semiannually"] = $semiannually;
                    }
                    if ("0.00" < $annually) {
                        $update["annually"] = $annually / 9 * 8 + $promoprice;
                    } else {
                        $update["annually"] = $annually;
                    }
                    if ("0.00" < $biennially) {
                        $update["biennially"] = $biennially / 10 * 9 + $promoprice;
                    } else {
                        $update["biennially"] = $biennially;
                    }
                }
                Illuminate\Database\Capsule\Manager::table("tblpricing")->where("relid", "=", $relid)->where("currency", "=", $currency)->where("type", "=", $type)->where("tsetupfee", "=", "0.00")->update($update);
                unset($update);
                $result = Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodspromo")->where("extension", "=", $tld)->where("type", "=", $type)->select("sellingprice")->get();
                $sellingprice = $result[0]->sellingprice;
                if (empty($sellingprice)) {
                    $values = ["registrar" => $active_promo_registrar, "resellerid" => $rcauth_userid, "extension" => $tld, "sellingprice" => $msetupfee, "promoprice" => $promoprice, "relid" => $relid, "type" => $type, "promoend" => $endtime];
                    Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodspromo")->insert($values);
                } else {
                    $values = ["registrar" => $active_promo_registrar, "resellerid" => $rcauth_userid, "promoprice" => $promoprice, "relid" => $relid, "type" => $type, "promoend" => $endtime];
                    Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodspromo")->where("extension", "=", $tld)->where("type", "=", $type)->update($values);
                }
                if ($is_currencydoupd != "on") {
                    domainCurrencyPricingupdate($clientgroupid, 0, $type, $relid);
                }
                logActivity("Cron Job: (RCM) " . $tld . " Domain Promo successfully " . $promo_actiontype . " in WHMCS for Reseller Account ID " . $rcauth_userid);
            }
        } else {
            logActivity("Cron Job: (RCM) No Active Promos found in Reseller Account ID " . $rcauth_userid);
        }
        } finally {
            rcm_release_lock($rcm_daily_lock);
        }
    } else {
        logActivity("Cron Job (RCM): Skipping Auto Promo Activation");
    }
}

?>