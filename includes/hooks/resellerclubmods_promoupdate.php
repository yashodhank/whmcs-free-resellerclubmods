<?php
if (!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
require ROOTDIR . "/modules/addons/resellerclubmods_tools/incs/functions.php";
add_hook("DailyCronJob", 3, "promo_update_price");
function promo_update_price($vars)
{
    $conf = [];
    foreach (Illuminate\Database\Capsule\Manager::table("tbladdonmodules")->where("module", "resellerclubmods_tools")->get() as $addonvars) {
        $conf[$addonvars->setting] = $addonvars->value;
    }
    $promo_end_check = $conf["promo_end_check"];
    $maileradmin = $conf["maileradmin"];
    $rcauth_userid = $conf["first_rcauth_userid"];
    $rcauth_password = $conf["first_rcauth_apikey"];
    $rchttp_api = $conf["rchttp_api"];
    $clientgroupid = "0";
    $is_currencydoupd = $conf["domainsync_currencydoupd"];
    $promo_terminate_days = 0;
    if ($conf["promo_terminate_days"]) {
        $promo_terminate_days = round($conf["promo_terminate_days"]);
        if (!is_numeric($promo_terminate_days)) {
            $promo_terminate_days = 0;
        }
    }
    if ($promo_end_check == "on") {
        $rcm_daily_lock = rcm_try_lock("dailycron_hooks");
        if ($rcm_daily_lock === false) {
            logActivity("Cron Job (RCM): Skipping Auto Promo Update Check — daily hooks busy");
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
        $date = new DateTime();
        $date->setTimeZone(new DateTimeZone("UTC"));
        $datenow = $date->format("Y-m-d H:i:s");
        $offset = $date->modify("+1 day");
        $datesafe = $offset->format("Y-m-d H:i:s");
        $custom_offset = $date->modify("+" . $promo_terminate_days . " day");
        $customdate = $custom_offset->format("Y-m-d H:i:s");
        $promo_array = [];
        foreach (Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodspromo")->get() as $data) {
            $promo_array[] = json_decode(json_encode($data), true);
        }
        if (!empty($promo_array)) {
            $productkeys = newproductkeys($rcauth_userid, $rcauth_password, $rchttp_api);
            $addondetails = getaddondetails($rcauth_userid, $rcauth_password, $rchttp_api);
            if (!class_exists("idna_convert")) {
                require_once ROOTDIR . "/modules/addons/resellerclubmods_tools/incs/idnclass.php";
            }
            $IDN = new idna_convert();
            foreach ($productkeys as $productkey => $productvalue) {
                foreach ($addondetails[$productkey]["tldlist"] as $addontld) {
                    $addontld = $IDN->decode($addontld);
                    if ($addondetails[$productkey]["maxregistrationyear"] != 10) {
                        $max_reg_array["." . $addontld] = ["." . $addontld, $addondetails[$productkey]["maxregistrationyear"]];
                    }
                }
            }
            $promomail .= "<div style=\"font-family: verdana; font-size: 11px; font-weight: normal;\">";
            $promomail .= "<p>" . $LANG["promomail01"] . "</p>";
            foreach ($promo_array as $promo_data) {
                if ($promo_data["promoend"] < $customdate || $promo_data["promoend"] < $datenow || $promo_data["promoend"] < $datesafe) {
                    $realsellingprice = $promo_data["sellingprice"];
                    $tldstrings = $promo_data["extension"];
                    $relid = $promo_data["relid"];
                    $type = $promo_data["type"];
                    if ($is_v7 == 1) {
                        $saleupdate = ["group" => "none"];
                        Illuminate\Database\Capsule\Manager::table("tbldomainpricing")->where("id", "=", $relid)->update($saleupdate);
                    }
                    $data = Illuminate\Database\Capsule\Manager::table("tblpricing")->where("relid", "=", $relid)->where("currency", "=", "1")->where("type", "=", $type)->where("tsetupfee", "=", "0.00")->select("msetupfee", "qsetupfee", "ssetupfee", "asetupfee", "bsetupfee", "monthly", "quarterly", "semiannually", "annually", "biennially")->get();
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
                    $table = "tblpricing";
                    if ($max_reg_array[$tldstrings]) {
                        if ($max_reg_array[$tldstrings][1] == 5) {
                            $update = ["msetupfee" => $realsellingprice, "qsetupfee" => $qsetupfee, "ssetupfee" => $ssetupfee, "asetupfee" => $asetupfee, "bsetupfee" => $bsetupfee];
                        } else {
                            $update = ["msetupfee" => $realsellingprice];
                        }
                    } else {
                        $update = ["msetupfee" => $realsellingprice, "qsetupfee" => $qsetupfee, "ssetupfee" => $ssetupfee, "asetupfee" => $asetupfee, "bsetupfee" => $bsetupfee, "monthly" => $monthly, "quarterly" => $quarterly, "semiannually" => $semiannually, "annually" => $annually, "biennially" => $biennially];
                    }
                    $result_update = Illuminate\Database\Capsule\Manager::table("tblpricing")->where("relid", "=", $relid)->where("currency", "=", "1")->where("type", "=", $type)->where("tsetupfee", "=", "0.00")->update($update);
                    $promomail .= "<div style=\"color:#cc0000;\">(" . $promo_data["registrar"] . ") " . $promo_data["type"] . " - <strong>" . $promo_data["extension"] . "</strong> " . $LANG["promomail03"] . " " . $promo_data["promoend"] . " UTC - " . $LANG["promomail04"] . " " . $promo_data["promoprice"] . " " . $LANG["toword"] . " " . $promo_data["sellingprice"] . "</div><br />";
                    logActivity("Cron Job (RCM): " . $LANG["promoupdatetitle"] . " (" . $promo_data["registrar"] . ") " . $promo_data["type"] . " - " . $promo_data["extension"] . " " . $LANG["promomail03"] . " " . $promo_data["promoend"] . " UTC - " . $LANG["promomail04"] . " " . $promo_data["promoprice"] . " " . $LANG["toword"] . " " . $promo_data["sellingprice"]);
                    $dodelete = Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodspromo")->where("relid", "=", $promo_data["relid"])->where("type", "=", $promo_data["type"])->delete();
                    if ($is_currencydoupd != "on") {
                        domainCurrencyPricingupdate($clientgroupid, 0, $type, $relid);
                    }
                } else {
                    $promomail .= "<div style=\"color:#3D660B;\">(" . $promo_data["registrar"] . ") " . $promo_data["type"] . " - <strong>" . $promo_data["extension"] . "</strong> " . $LANG["promomail03"] . " " . $promo_data["promoend"] . " UTC - " . $LANG["normalsellingprice"] . ": " . $promo_data["sellingprice"] . " " . $LANG["customerprice"] . ": " . $promo_data["promoprice"] . " - " . $LANG["promomail05"] . "</div><br />";
                    logActivity("Cron Job (RCM): " . $LANG["promoupdatetitle"] . " (" . $promo_data["registrar"] . ") " . $promo_data["type"] . " - " . $promo_data["extension"] . " " . $LANG["promomail03"] . " " . $promo_data["promoend"] . " UTC - " . $LANG["promomail05"]);
                }
            }
            $promomail .= "<p>" . $LANG["promomail06"] . "</p>";
            $promomail .= "</div>";
            $mailtpltype = "RCM Domain Promo Update Report";
            $mailsubject = $LANG["promomailsubject"];
            $mailmessage = $promomail;
            $apiadminuser = $maileradmin;
            adminemailmessages($mailtpltype, $mailsubject, $mailmessage, $apiadminuser);
        } else {
            logActivity("Cron Job: (RCM) " . $LANG["promoupdatetitle"] . " " . $LANG["nopromoactive"]);
        }
        } finally {
            rcm_release_lock($rcm_daily_lock);
        }
    } else {
        logActivity("Cron Job (RCM): Skipping Auto Promo Update Check");
    }
}

?>