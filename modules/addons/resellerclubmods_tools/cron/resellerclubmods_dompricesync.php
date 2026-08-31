<?php
$filepath = substr(__FILE__, 0, -34);
if (file_exists($filepath . "/path.php")) {
    include $filepath . "/path.php";
}
if (isset($fullpath_to_whmcs)) {
    $include_path = $fullpath_to_whmcs;
} else {
    $include_path = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
}
require $include_path . "/init.php";
$conf = [];
foreach (Illuminate\Database\Capsule\Manager::table("tbladdonmodules")->where("module", "resellerclubmods_tools")->get() as $addonvars) {
    $conf[$addonvars->setting] = $addonvars->value;
}
    if (isset($_SERVER["REQUEST_METHOD"]) && ($_SERVER["REQUEST_METHOD"] === "GET" || $_SERVER["REQUEST_METHOD"] === "POST")) {
        if (isset($_REQUEST["id"]) && is_numeric($_REQUEST["id"])) {
            $rid = $_REQUEST["id"];
        } else {
            exit("invalid id or missing id");
        }
    } else if (isset($_SERVER["argv"]) || isset($argv)) {
        if (empty($conf["second_rcauth_userid"]) && empty($conf["third_rcauth_userid"]) && empty($conf["fourth_rcauth_userid"])) {
            $rid = $conf["first_rcauth_userid"];
            if (!is_numeric($rid)) {
                exit("invalid id or missing id in first account configuration (cli)");
            }
        } else if (isset($_SERVER["argv"][1])) {
            $rid = $_SERVER["argv"][1];
            if (!is_numeric($_SERVER["argv"][1])) {
                exit("invalid or missing id or php directive register_argc_argv disabled (cli)");
            }
        } else if (isset($argv[1])) {
            $rid = $argv[1];
            if (!is_numeric($argv[1])) {
                exit("invalid or missing id or php directive register_argc_argv disabled (cli)");
            }
        } else {
            exit("multiple accounts, invalid or missing id or php directive register_argc_argv disabled (cli)");
        }
    } else {
        print_r($_SERVER);
        print_r($_SERVER) . PHP_EOL;
        exit("missing id or php directive register_argc_argv disabled (cli)");
    }
    if (!class_exists("idna_convert")) {
        require_once ROOTDIR . "/modules/addons/resellerclubmods_tools/incs/idnclass.php";
    }
    $IDN = new idna_convert();
    require ROOTDIR . "/includes/currencyfunctions.php";
    require ROOTDIR . "/modules/addons/resellerclubmods_tools/incs/functions.php";
    $rchttp_api = $conf["rchttp_api"];
    $sendconfmail = $conf["sendconfmail"];
    $maileradmin = $conf["maileradmin"];
    $transferfree_tlds = $vars["transferfree_tlds"];
    $conf_arr = [];
    $conf_arr["first"] = [$conf["first_rcauth_userid"], $conf["first_rcauth_apikey"], $conf["first_currencyswitch"], $conf["first_multiplicator"], $conf["first_defaultcurrency"], $conf["first_domainsync_check"], $conf["first_domainsync_tlds"], $conf["first_only_baseslab"], $conf["first_domainsync_telescope"], $conf["domainsync_currencydoupd"], $conf["first_exclude_domainsync_tlds"], $conf["first_domainsync_redemption"]];
    $conf_arr["second"] = [$conf["second_rcauth_userid"], $conf["second_rcauth_apikey"], $conf["second_currencyswitch"], $conf["second_multiplicator"], $conf["second_defaultcurrency"], $conf["second_domainsync_check"], $conf["second_domainsync_tlds"], $conf["second_only_baseslab"], $conf["second_domainsync_telescope"], $conf["domainsync_currencydoupd"], $conf["second_exclude_domainsync_tlds"], $conf["second_domainsync_redemption"]];
    $conf_arr["third"] = [$conf["third_rcauth_userid"], $conf["third_rcauth_apikey"], $conf["third_currencyswitch"], $conf["third_multiplicator"], $conf["third_defaultcurrency"], $conf["third_domainsync_check"], $conf["third_domainsync_tlds"], $conf["third_only_baseslab"], $conf["third_domainsync_telescope"], $conf["domainsync_currencydoupd"], $conf["third_exclude_domainsync_tlds"], $conf["third_domainsync_redemption"]];
    $conf_arr["fourth"] = [$conf["fourth_rcauth_userid"], $conf["fourth_rcauth_apikey"], $conf["fourth_currencyswitch"], $conf["fourth_multiplicator"], $conf["fourth_defaultcurrency"], $conf["fourth_domainsync_check"], $conf["fourth_domainsync_tlds"], $conf["fourth_only_baseslab"], $conf["fourth_domainsync_telescope"], $conf["domainsync_currencydoupd"], $conf["fourth_exclude_domainsync_tlds"], $conf["fourth_domainsync_redemption"]];
    foreach ($conf_arr as $conf_values) {
        if (in_array($rid, $conf_values)) {
            $rcauth_userid = rcm_trim($conf_values[0]);
            $rcauth_password = rcm_trim($conf_values[1]);
            list($currencyswitch, $multiplicator) = $conf_values;
            $defaultcurrency = rcm_trim($conf_values[4]);
            $domainsynccheck = $conf_values[5];
            $domainsynctlds = rcm_trim($conf_values[6]);
            list($domainsyncall, $domainsynctelescope, $currencydoupd) = $conf_values;
            $domainsyncexcludetlds = rcm_trim($conf_values[10]);
            $redemptiondoupd = $conf_values[11];
        }
    }
    if ($rid != $rcauth_userid) {
        exit("Unauthorized Access Attempt");
    }
    if (isset($_REQUEST["dobulkupdate"]) || isset($_REQUEST["dobulksetupimport"])) {
        $domainsynccheck = "";
        if (isset($_REQUEST["currencydoupd"]) && $_REQUEST["currencydoupd"] == "true") {
            $currencydoupd = "";
        } else {
            $currencydoupd = "on";
        }
        if (isset($_REQUEST["redemptionform"]) && $_REQUEST["redemptionform"] == "true") {
            $redemptiondoupd = "on";
        } else {
            $redemptiondoupd = "";
        }
        if (isset($_REQUEST["telescope"]) && $_REQUEST["telescope"] == "1") {
            $domainsynctelescope = "on";
        } else {
            $domainsynctelescope = "";
        }
    }
    if (!empty($rcauth_userid) && !empty($rcauth_password) && $domainsynccheck != "on") {
        if (empty($transferfree_tlds)) {
            $transferfree_tlds = "com.au,net.au,co.uk,me.uk,org.uk,com.ru,net.ru,org.ru,ru,es";
        }
        $transferfree_tlds_array = explode(",", $transferfree_tlds);
        array_walk($transferfree_tlds_array, "rcm_array_trim");
        $adminlang = Illuminate\Database\Capsule\Manager::table("tbladmins")->where("username", "=", $maileradmin)->value("language");
        if (empty($adminlang)) {
            $adminlang = "english";
        }
        $get_langfile = getAdminlang($adminlang);
        require ROOTDIR . $get_langfile;
        $LANG = $_ADDONLANG;
        if (!function_exists("dnf")) {
            function dnf($str)
            {
                $str = round($str, 2);
                return $str;
            }
        }
        if (empty($domainsynctlds)) {
            $tbldomainpricing = [];
            foreach (Illuminate\Database\Capsule\Manager::table("tbldomainpricing")->select("id", "extension")->get() as $data) {
                $tbldomainpricing[$data->id] = $data->extension;
            }
            if (!empty($domainsyncexcludetlds)) {
                $exclude_tlds = explode(",", $domainsyncexcludetlds);
            }
            if (is_array($exclude_tlds)) {
                $tbldomainpricing = array_diff($tbldomainpricing, $exclude_tlds);
            }
        } else {
            $limited_tld_to = explode(",", $domainsynctlds);
            $tbldomainpricing = [];
            foreach ($limited_tld_to as $onlytlds) {
                foreach (Illuminate\Database\Capsule\Manager::table("tbldomainpricing")->where("extension", "=", $onlytlds)->select("id", "extension")->get() as $data) {
                    $tbldomainpricing[$data->id] = $data->extension;
                }
            }
        }
        if (is_array($tbldomainpricing) && !empty($tbldomainpricing)) {
            $promotable = [];
            foreach (Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodspromo")->select("sellingprice", "promoprice", "relid", "type")->get() as $data) {
                $promotable[] = ["relid" => $data->relid, "type" => $data->type, "sellingprice" => $data->sellingprice, "promoprice" => $data->promoprice];
            }
            $promoIndex = [];
            if (!empty($promotable)) {
                foreach ($promotable as $p) {
                    $rel = (int) $p["relid"];
                    $type = $p["type"];
                    if (!isset($promoIndex[$rel])) {
                        $promoIndex[$rel] = [];
                    }
                    $promoIndex[$rel][$type] = $p;
                }
            }
            $currencyrate = Illuminate\Database\Capsule\Manager::table("tblcurrencies")->where("code", "=", $defaultcurrency)->value("rate");
            if (empty($currencyrate)) {
                $currencyrate = 1;
            }
            $currency = Illuminate\Database\Capsule\Manager::table("tblcurrencies")->where("default", "=", 1)->value("id");
            if (empty($currency)) {
                $currency = 1;
            }
            $method = "GET";
            $apifunction = "/api/products/customer-price.json";
            $dompricingsXml = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
            $productkeys = newproductkeys($rcauth_userid, $rcauth_password, $rchttp_api);
            $addondetails = getaddondetails($rcauth_userid, $rcauth_password, $rchttp_api);
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
            $domainrelids = [];
            if (is_array($tbldomainpricing)) {
                foreach ($tbldomainpricing as $relid_tmp => $ext_tmp) {
                    if (!isset($domainrelids[$ext_tmp])) {
                        $domainrelids[$ext_tmp] = $relid_tmp;
                    }
                }
            }
            $newproductprice_array = [];
            if (is_array($rc_productkey_array) && is_array($dompricingsXml)) {
                foreach ($rc_productkey_array as $rc_productkey => $rc_productvals) {
                    if (!isset($dompricingsXml[$rc_productkey])) {
                    } else {
                        $value1 = $dompricingsXml[$rc_productkey];
                        foreach ($rc_productvals as $rc_producttlds) {
                            if (isset($domainrelids[$rc_producttlds])) {
                                $relid = $domainrelids[$rc_producttlds];
                                $newproductprice_array[$rc_producttlds] = $value1;
                                $newproductprice_array[$rc_producttlds]["relid"] = $relid;
                            }
                        }
                    }
                }
            }
            $relidsForPricing = [];
            if (!empty($newproductprice_array) && is_array($newproductprice_array)) {
                foreach ($newproductprice_array as $np_vals) {
                    if (isset($np_vals["relid"])) {
                        $relidsForPricing[] = (int) $np_vals["relid"];
                    }
                }
                $relidsForPricing = array_values(array_unique($relidsForPricing));
            }
            $existingRegisterRelids = [];
            $existingRenewRelids = [];
            $existingTransferRelids = [];
            if (!empty($relidsForPricing)) {
                $rows = Illuminate\Database\Capsule\Manager::table("tblpricing")->where("type", "=", "domainregister")->where("currency", "=", $currency)->where("tsetupfee", "=", $_REQUEST["clientgroup"] . ".00")->whereIn("relid", $relidsForPricing)->select("relid")->get();
                foreach ($rows as $row) {
                    $existingRegisterRelids[(int) $row->relid] = true;
                }
                $rows = Illuminate\Database\Capsule\Manager::table("tblpricing")->where("type", "=", "domainrenew")->where("currency", "=", $currency)->where("tsetupfee", "=", $_REQUEST["clientgroup"] . ".00")->whereIn("relid", $relidsForPricing)->select("relid")->get();
                foreach ($rows as $row) {
                    $existingRenewRelids[(int) $row->relid] = true;
                }
                $rows = Illuminate\Database\Capsule\Manager::table("tblpricing")->where("type", "=", "domaintransfer")->where("currency", "=", $currency)->where("tsetupfee", "=", $_REQUEST["clientgroup"] . ".00")->whereIn("relid", $relidsForPricing)->select("relid")->get();
                foreach ($rows as $row) {
                    $existingTransferRelids[(int) $row->relid] = true;
                }
            }
            $baseRegisterRows = [];
            $baseRenewRows = [];
            $baseTransferRows = [];
            if (!empty($relidsForPricing)) {
                $rows = Illuminate\Database\Capsule\Manager::table("tblpricing")->where("type", "=", "domainregister")->where("currency", "=", $currency)->where("tsetupfee", "=", "0.00")->whereIn("relid", $relidsForPricing)->select("relid", "msetupfee", "qsetupfee", "ssetupfee", "asetupfee", "bsetupfee", "monthly", "quarterly", "semiannually", "annually", "biennially")->get();
                foreach ($rows as $row) {
                    $baseRegisterRows[(int) $row->relid] = $row;
                }
                $rows = Illuminate\Database\Capsule\Manager::table("tblpricing")->where("type", "=", "domainrenew")->where("currency", "=", $currency)->where("tsetupfee", "=", "0.00")->whereIn("relid", $relidsForPricing)->select("relid", "msetupfee", "qsetupfee", "ssetupfee", "asetupfee", "bsetupfee", "monthly", "quarterly", "semiannually", "annually", "biennially")->get();
                foreach ($rows as $row) {
                    $baseRenewRows[(int) $row->relid] = $row;
                }
                $rows = Illuminate\Database\Capsule\Manager::table("tblpricing")->where("type", "=", "domaintransfer")->where("currency", "=", $currency)->where("tsetupfee", "=", "0.00")->whereIn("relid", $relidsForPricing)->select("relid", "msetupfee", "qsetupfee", "ssetupfee", "asetupfee", "bsetupfee", "monthly", "quarterly", "semiannually", "annually", "biennially")->get();
                foreach ($rows as $row) {
                    $baseTransferRows[(int) $row->relid] = $row;
                }
            }
            $billingslabs = ["1" => "msetupfee", "2" => "qsetupfee", "3" => "ssetupfee", "4" => "asetupfee", "5" => "bsetupfee", "6" => "monthly", "7" => "quarterly", "8" => "semiannually", "9" => "annually", "10" => "biennially"];
            $mplicator_add = 1;
            foreach ($newproductprice_array as $add_key => $add_values) {
                $insert_relid = (int) $add_values["relid"];
                $tblpricing_relid = NULL;
                if (isset($existingRegisterRelids[$insert_relid])) {
                    $tblpricing_relid = $insert_relid;
                    $doUpdate = true;
                }
                ksort($add_values["addnewdomain"]);
                if ($add_key == $minmax_reg_array[$add_key][0]) {
                    $addk = $billingslabs[$minmax_reg_array[$add_key][1]];
                    $dbadd_array[$addk] = $add_values["addnewdomain"][$minmax_reg_array[$add_key][1]] * $minmax_reg_array[$add_key][1];
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
                        $dbadd_array[$addk] = $v * $multiplicator * $mplicator_add++;
                    }
                }
                $redemption_add = "-1";
                $redemption_days = "-1";
                if ($add_values["restoredomain"][1]) {
                    $redemption_add = $add_values["restoredomain"][1] * $multiplicator;
                    $redemption_days = $redemption_array[$add_key][1];
                }
                if ($domainsynctelescope == "on") {
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
                if ($redemptiondoupd == "on") {
                    $update_redemption = ["redemption_grace_period" => $redemption_days, "redemption_grace_period_fee" => $redemption_add];
                    $result_update_redemption = Illuminate\Database\Capsule\Manager::table("tbldomainpricing")->where("id", "=", $add_values["relid"])->where("extension", "=", $add_key)->update($update_redemption);
                }
                if ($doUpdate) {
                    $table = "tblpricing";
                    $update = ["msetupfee" => dnf($msetupfee_add), "qsetupfee" => dnf($qsetupfee_add), "ssetupfee" => dnf($ssetupfee_add), "asetupfee" => dnf($asetupfee_add), "bsetupfee" => dnf($bsetupfee_add), "monthly" => dnf($monthly_add), "quarterly" => dnf($quarterly_add), "semiannually" => dnf($semiannually_add), "annually" => dnf($annually_add), "biennially" => dnf($biennially_add)];
                    if (isset($_REQUEST["clientgroup"])) {
                        $result_update = Illuminate\Database\Capsule\Manager::table($table)->where("relid", "=", $tblpricing_relid)->where("type", "=", "domainregister")->where("currency", "=", $currency)->where("tsetupfee", "=", $_REQUEST["clientgroup"] . ".00")->update($update);
                    } else if ($domainsyncall != "on") {
                        $result_update = Illuminate\Database\Capsule\Manager::table($table)->where("relid", "=", $tblpricing_relid)->where("type", "=", "domainregister")->where("currency", "=", $currency)->where("tsetupfee", "=", "0.00")->update($update);
                    } else {
                        $result_update = Illuminate\Database\Capsule\Manager::table($table)->where("relid", "=", $tblpricing_relid)->where("type", "=", "domainregister")->where("currency", "=", $currency)->update($update);
                    }
                    $add_result = 1;
                } else {
                    $id = "";
                    $type = "domainregister";
                    $tsetupfee = $_REQUEST["clientgroup"] . ".00";
                    $table = "tblpricing";
                    $values = ["id" => $id, "type" => $type, "currency" => $currency, "relid" => $insert_relid, "msetupfee" => dnf($msetupfee_add), "qsetupfee" => dnf($qsetupfee_add), "ssetupfee" => dnf($ssetupfee_add), "asetupfee" => dnf($asetupfee_add), "bsetupfee" => dnf($bsetupfee_add), "tsetupfee" => $_REQUEST["clientgroup"] . ".00", "monthly" => dnf($monthly_add), "quarterly" => dnf($quarterly_add), "semiannually" => dnf($semiannually_add), "annually" => dnf($annually_add), "biennially" => dnf($biennially_add), "triennially" => ""];
                    $result_insert = Illuminate\Database\Capsule\Manager::table($table)->insert($values);
                    $add_result = 1;
                }
                if (!empty($promotable) && !empty($tblpricing_relid) && isset($promoIndex[$tblpricing_relid]["domainregister"])) {
                    $promodata = $promoIndex[$tblpricing_relid]["domainregister"];
                    Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodspromo")->where("relid", "=", $tblpricing_relid)->where("type", "=", "domainregister")->update(["sellingprice" => dnf($msetupfee_add)]);
                    if (!isset($baseRegisterRows[$tblpricing_relid])) {
                        return NULL;
                    }
                    $data0 = $baseRegisterRows[$tblpricing_relid];
                    $tldstrings = "." . $tbldomainpricing[$insert_relid];
                    $normal1 = dnf($promodata["sellingprice"]);
                    $promo1 = dnf($promodata["promoprice"]);
                    $msetupfee = $promo1;
                    if ("0.00" < $data0->qsetupfee) {
                        $qsetupfee = dnf($promo1 + 1 * $normal1);
                    } else {
                        $qsetupfee = "-1.00";
                    }
                    if ("0.00" < $data0->ssetupfee) {
                        $ssetupfee = dnf($promo1 + 2 * $normal1);
                    } else {
                        $ssetupfee = "-1.00";
                    }
                    if ("0.00" < $data0->asetupfee) {
                        $asetupfee = dnf($promo1 + 3 * $normal1);
                    } else {
                        $asetupfee = "-1.00";
                    }
                    if ("0.00" < $data0->bsetupfee) {
                        $bsetupfee = dnf($promo1 + 4 * $normal1);
                    } else {
                        $bsetupfee = "-1.00";
                    }
                    if ("0.00" < $data0->monthly) {
                        $monthly = dnf($promo1 + 5 * $normal1);
                    } else {
                        $monthly = "-1.00";
                    }
                    if ("0.00" < $data0->quarterly) {
                        $quarterly = dnf($promo1 + 6 * $normal1);
                    } else {
                        $quarterly = "-1.00";
                    }
                    if ("0.00" < $data0->semiannually) {
                        $semiannually = dnf($promo1 + 7 * $normal1);
                    } else {
                        $semiannually = "-1.00";
                    }
                    if ("0.00" < $data0->annually) {
                        $annually = dnf($promo1 + 8 * $normal1);
                    } else {
                        $annually = "-1.00";
                    }
                    if ("0.00" < $data0->biennially) {
                        $biennially = dnf($promo1 + 9 * $normal1);
                    } else {
                        $biennially = "-1.00";
                    }
                    if ($max_reg_array[$tldstrings]) {
                        if ($max_reg_array[$tldstrings][1] == 5) {
                            $update = ["msetupfee" => $msetupfee, "qsetupfee" => $qsetupfee, "ssetupfee" => $ssetupfee, "asetupfee" => $asetupfee, "bsetupfee" => $bsetupfee];
                        } else {
                            $update = ["msetupfee" => $msetupfee];
                        }
                    } else {
                        $update = ["msetupfee" => $msetupfee, "qsetupfee" => $qsetupfee, "ssetupfee" => $ssetupfee, "asetupfee" => $asetupfee, "bsetupfee" => $bsetupfee, "monthly" => $monthly, "quarterly" => $quarterly, "semiannually" => $semiannually, "annually" => $annually, "biennially" => $biennially];
                    }
                    Illuminate\Database\Capsule\Manager::table("tblpricing")->where("relid", "=", $tblpricing_relid)->where("currency", "=", $currency)->where("type", "=", "domainregister")->where("tsetupfee", "=", $_REQUEST["clientgroup"] . ".00")->update($update);
                }
                unset($dbadd_array);
                unset($doUpdate);
                $mplicator_add = 1;
            }
            $mplicator_ren = 1;
            foreach ($newproductprice_array as $ren_key => $ren_values) {
                $insert_relid = (int) $ren_values["relid"];
                $tblpricing_relid = NULL;
                if (isset($existingRenewRelids[$insert_relid])) {
                    $tblpricing_relid = $insert_relid;
                    $doUpdate = true;
                }
                ksort($ren_values["renewdomain"]);
                if ($ren_key == $minmax_ren_array[$ren_key][0]) {
                    $renk = $billingslabs[$minmax_ren_array[$ren_key][1]];
                    $dbren_array[$renk] = $ren_values["renewdomain"][$minmax_ren_array[$ren_key][1]] * $minmax_ren_array[$ren_key][1];
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
                        $dbren_array[$renk] = $v * $multiplicator * $mplicator_ren++;
                    }
                }
                if ($domainsynctelescope == "on") {
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
                    $table = "tblpricing";
                    $update = ["msetupfee" => dnf($msetupfee_ren), "qsetupfee" => dnf($qsetupfee_ren), "ssetupfee" => dnf($ssetupfee_ren), "asetupfee" => dnf($asetupfee_ren), "bsetupfee" => dnf($bsetupfee_ren), "monthly" => dnf($monthly_ren), "quarterly" => dnf($quarterly_ren), "semiannually" => dnf($semiannually_ren), "annually" => dnf($annually_ren), "biennially" => dnf($biennially_ren)];
                    if (isset($_REQUEST["clientgroup"])) {
                        $result_update = Illuminate\Database\Capsule\Manager::table($table)->where("relid", "=", $tblpricing_relid)->where("type", "=", "domainrenew")->where("currency", "=", $currency)->where("tsetupfee", "=", $_REQUEST["clientgroup"] . ".00")->update($update);
                    } else if ($domainsyncall != "on") {
                        $result_update = Illuminate\Database\Capsule\Manager::table($table)->where("relid", "=", $tblpricing_relid)->where("type", "=", "domainrenew")->where("currency", "=", $currency)->where("tsetupfee", "=", "0.00")->update($update);
                    } else {
                        $result_update = Illuminate\Database\Capsule\Manager::table($table)->where("relid", "=", $tblpricing_relid)->where("type", "=", "domainrenew")->where("currency", "=", $currency)->update($update);
                    }
                    $ren_result = 1;
                } else {
                    $id = "";
                    $type = "domainrenew";
                    $tsetupfee = $_REQUEST["clientgroup"] . ".00";
                    $table = "tblpricing";
                    $values = ["id" => $id, "type" => $type, "currency" => $currency, "relid" => $insert_relid, "msetupfee" => dnf($msetupfee_ren), "qsetupfee" => dnf($qsetupfee_ren), "ssetupfee" => dnf($ssetupfee_ren), "asetupfee" => dnf($asetupfee_ren), "bsetupfee" => dnf($bsetupfee_ren), "tsetupfee" => $_REQUEST["clientgroup"] . ".00", "monthly" => dnf($monthly_ren), "quarterly" => dnf($quarterly_ren), "semiannually" => dnf($semiannually_ren), "annually" => dnf($annually_ren), "biennially" => dnf($biennially_ren), "triennially" => ""];
                    $result_insert = Illuminate\Database\Capsule\Manager::table($table)->insert($values);
                    $ren_result = 1;
                }
                if (!empty($promotable) && !empty($tblpricing_relid) && isset($promoIndex[$tblpricing_relid]["domainrenew"])) {
                    $promodata = $promoIndex[$tblpricing_relid]["domainrenew"];
                    Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodspromo")->where("relid", "=", $tblpricing_relid)->where("type", "=", "domainrenew")->update(["sellingprice" => dnf($msetupfee_ren)]);
                    if (!isset($baseRenewRows[$tblpricing_relid])) {
                        return NULL;
                    }
                    $data0 = $baseRenewRows[$tblpricing_relid];
                    $tldstrings = "." . $tbldomainpricing[$insert_relid];
                    $normal1 = dnf($promodata["sellingprice"]);
                    $promo1 = dnf($promodata["promoprice"]);
                    $msetupfee = $promo1;
                    if ("0.00" < $data0->qsetupfee) {
                        $qsetupfee = dnf($promo1 + 1 * $normal1);
                    } else {
                        $qsetupfee = "-1.00";
                    }
                    if ("0.00" < $data0->ssetupfee) {
                        $ssetupfee = dnf($promo1 + 2 * $normal1);
                    } else {
                        $ssetupfee = "-1.00";
                    }
                    if ("0.00" < $data0->asetupfee) {
                        $asetupfee = dnf($promo1 + 3 * $normal1);
                    } else {
                        $asetupfee = "-1.00";
                    }
                    if ("0.00" < $data0->bsetupfee) {
                        $bsetupfee = dnf($promo1 + 4 * $normal1);
                    } else {
                        $bsetupfee = "-1.00";
                    }
                    if ("0.00" < $data0->monthly) {
                        $monthly = dnf($promo1 + 5 * $normal1);
                    } else {
                        $monthly = "-1.00";
                    }
                    if ("0.00" < $data0->quarterly) {
                        $quarterly = dnf($promo1 + 6 * $normal1);
                    } else {
                        $quarterly = "-1.00";
                    }
                    if ("0.00" < $data0->semiannually) {
                        $semiannually = dnf($promo1 + 7 * $normal1);
                    } else {
                        $semiannually = "-1.00";
                    }
                    if ("0.00" < $data0->annually) {
                        $annually = dnf($promo1 + 8 * $normal1);
                    } else {
                        $annually = "-1.00";
                    }
                    if ("0.00" < $data0->biennially) {
                        $biennially = dnf($promo1 + 9 * $normal1);
                    } else {
                        $biennially = "-1.00";
                    }
                    if ($max_reg_array[$tldstrings]) {
                        if ($max_reg_array[$tldstrings][1] == 5) {
                            $update = ["msetupfee" => $msetupfee, "qsetupfee" => $qsetupfee, "ssetupfee" => $ssetupfee, "asetupfee" => $asetupfee, "bsetupfee" => $bsetupfee];
                        } else {
                            $update = ["msetupfee" => $msetupfee];
                        }
                    } else {
                        $update = ["msetupfee" => $msetupfee, "qsetupfee" => $qsetupfee, "ssetupfee" => $ssetupfee, "asetupfee" => $asetupfee, "bsetupfee" => $bsetupfee, "monthly" => $monthly, "quarterly" => $quarterly, "semiannually" => $semiannually, "annually" => $annually, "biennially" => $biennially];
                    }
                    Illuminate\Database\Capsule\Manager::table("tblpricing")->where("relid", "=", $tblpricing_relid)->where("currency", "=", $currency)->where("type", "=", "domainrenew")->where("tsetupfee", "=", $_REQUEST["clientgroup"] . ".00")->update($update);
                }
                unset($dbren_array);
                unset($doUpdate);
                $mplicator_ren = 1;
            }
            $mplicator_tra = 1;
            foreach ($newproductprice_array as $tra_key => $tra_values) {
                $insert_relid = (int) $tra_values["relid"];
                $tblpricing_relid = NULL;
                if (isset($existingTransferRelids[$insert_relid])) {
                    $tblpricing_relid = $insert_relid;
                    $doUpdate = true;
                }
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
                    $dbtra_array[$trak] = $tra_values["addtransferdomain"][1] * $multiplicator * $mplicator_tra++;
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
                    $table = "tblpricing";
                    $update = ["msetupfee" => dnf($msetupfee_tra), "qsetupfee" => dnf($qsetupfee_tra), "ssetupfee" => dnf($ssetupfee_tra), "asetupfee" => dnf($asetupfee_tra), "bsetupfee" => dnf($bsetupfee_tra), "monthly" => dnf($monthly_tra), "quarterly" => dnf($quarterly_tra), "semiannually" => dnf($semiannually_tra), "annually" => dnf($annually_tra), "biennially" => dnf($biennially_tra)];
                    if (isset($_REQUEST["clientgroup"])) {
                        $result_update = Illuminate\Database\Capsule\Manager::table($table)->where("relid", "=", $tblpricing_relid)->where("type", "=", "domaintransfer")->where("currency", "=", $currency)->where("tsetupfee", "=", $_REQUEST["clientgroup"] . ".00")->update($update);
                    } else if ($domainsyncall != "on") {
                        $result_update = Illuminate\Database\Capsule\Manager::table($table)->where("relid", "=", $tblpricing_relid)->where("type", "=", "domaintransfer")->where("currency", "=", $currency)->where("tsetupfee", "=", "0.00")->update($update);
                    } else {
                        $result_update = Illuminate\Database\Capsule\Manager::table($table)->where("relid", "=", $tblpricing_relid)->where("type", "=", "domaintransfer")->where("currency", "=", $currency)->update($update);
                    }
                    $tra_result = 1;
                } else {
                    $id = "";
                    $type = "domaintransfer";
                    $tsetupfee = $_REQUEST["clientgroup"] . ".00";
                    $table = "tblpricing";
                    $values = ["id" => $id, "type" => $type, "currency" => $currency, "relid" => $insert_relid, "msetupfee" => dnf($msetupfee_tra), "qsetupfee" => dnf($qsetupfee_tra), "ssetupfee" => dnf($ssetupfee_tra), "asetupfee" => dnf($asetupfee_tra), "bsetupfee" => dnf($bsetupfee_tra), "tsetupfee" => $_REQUEST["clientgroup"] . ".00", "monthly" => dnf($monthly_tra), "quarterly" => dnf($quarterly_tra), "semiannually" => dnf($semiannually_tra), "annually" => dnf($annually_tra), "biennially" => dnf($biennially_tra), "triennially" => ""];
                    $result_insert = Illuminate\Database\Capsule\Manager::table($table)->insert($values);
                    $tra_result = 1;
                }
                if (!empty($promotable) && !empty($tblpricing_relid) && isset($promoIndex[$tblpricing_relid]["domaintransfer"])) {
                    $promodata = $promoIndex[$tblpricing_relid]["domaintransfer"];
                    Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodspromo")->where("relid", "=", $tblpricing_relid)->where("type", "=", "domaintransfer")->update(["sellingprice" => dnf($msetupfee_tra)]);
                    if (!isset($baseTransferRows[$tblpricing_relid])) {
                        return NULL;
                    }
                    $data0 = $baseTransferRows[$tblpricing_relid];
                    $tldstrings = "." . $tbldomainpricing[$insert_relid];
                    $normal1 = dnf($promodata["sellingprice"]);
                    $promo1 = dnf($promodata["promoprice"]);
                    $msetupfee = $promo1;
                    if ("0.00" < $data0->qsetupfee) {
                        $qsetupfee = dnf($promo1 + 1 * $normal1);
                    } else {
                        $qsetupfee = "-1.00";
                    }
                    if ("0.00" < $data0->ssetupfee) {
                        $ssetupfee = dnf($promo1 + 2 * $normal1);
                    } else {
                        $ssetupfee = "-1.00";
                    }
                    if ("0.00" < $data0->asetupfee) {
                        $asetupfee = dnf($promo1 + 3 * $normal1);
                    } else {
                        $asetupfee = "-1.00";
                    }
                    if ("0.00" < $data0->bsetupfee) {
                        $bsetupfee = dnf($promo1 + 4 * $normal1);
                    } else {
                        $bsetupfee = "-1.00";
                    }
                    if ("0.00" < $data0->monthly) {
                        $monthly = dnf($promo1 + 5 * $normal1);
                    } else {
                        $monthly = "-1.00";
                    }
                    if ("0.00" < $data0->quarterly) {
                        $quarterly = dnf($promo1 + 6 * $normal1);
                    } else {
                        $quarterly = "-1.00";
                    }
                    if ("0.00" < $data0->semiannually) {
                        $semiannually = dnf($promo1 + 7 * $normal1);
                    } else {
                        $semiannually = "-1.00";
                    }
                    if ("0.00" < $data0->annually) {
                        $annually = dnf($promo1 + 8 * $normal1);
                    } else {
                        $annually = "-1.00";
                    }
                    if ("0.00" < $data0->biennially) {
                        $biennially = dnf($promo1 + 9 * $normal1);
                    } else {
                        $biennially = "-1.00";
                    }
                    if ($max_reg_array[$tldstrings]) {
                        if ($max_reg_array[$tldstrings][1] == 5) {
                            $update = ["msetupfee" => $msetupfee, "qsetupfee" => $qsetupfee, "ssetupfee" => $ssetupfee, "asetupfee" => $asetupfee, "bsetupfee" => $bsetupfee];
                        } else {
                            $update = ["msetupfee" => $msetupfee];
                        }
                    } else {
                        $update = ["msetupfee" => $msetupfee, "qsetupfee" => $qsetupfee, "ssetupfee" => $ssetupfee, "asetupfee" => $asetupfee, "bsetupfee" => $bsetupfee, "monthly" => $monthly, "quarterly" => $quarterly, "semiannually" => $semiannually, "annually" => $annually, "biennially" => $biennially];
                    }
                    Illuminate\Database\Capsule\Manager::table("tblpricing")->where("relid", "=", $tblpricing_relid)->where("currency", "=", $currency)->where("type", "=", "domaintransfer")->where("tsetupfee", "=", $_REQUEST["clientgroup"] . ".00")->update($update);
                }
                unset($dbtra_array);
                unset($doUpdate);
                $mplicator_tra = 1;
            }
            if ($currencydoupd != "on") {
                $clientgroupid = $_REQUEST["clientgroup"];
                domainCurrencyPricingupdate($clientgroupid, 1, $domtype = "");
            }
            logActivity($LANG["domainpricesync1"] . " " . $rcauth_userid . ": " . $LANG["domainpricesync2"] . " " . implode(", ", $tbldomainpricing) . " " . $LANG["domainpricesync3"]);
            if (isset($_REQUEST["dobulkupdate"]) && $_REQUEST["dobulkupdate"] == "true") {
                global $customadminpath;
                global $CONFIG;
                $systemurl = !empty($CONFIG["SystemSSLURL"]) ? $CONFIG["SystemSSLURL"] : $CONFIG["SystemURL"];
                $adminhome = $systemurl . "/" . $customadminpath . "/addonmodules.php?module=resellerclubmods_tools&domain=domain-pricing-import&clientgroup=" . $_REQUEST["clientgroup"] . "&dobulksync=success";
                header("Location: " . $adminhome);
                exit;
            }
            if (isset($_REQUEST["dobulksetupimport"]) && $_REQUEST["dobulksetupimport"] == "true") {
                global $customadminpath;
                global $CONFIG;
                $systemurl = !empty($CONFIG["SystemSSLURL"]) ? $CONFIG["SystemSSLURL"] : $CONFIG["SystemURL"];
                $adminhome = $systemurl . "/" . $customadminpath . "/addonmodules.php?module=resellerclubmods_tools&domain=domain-pricing-import&clientgroup=" . $_REQUEST["clientgroup"] . "&dobulksetupimport=success";
                header("Location: " . $adminhome);
                exit;
            }
        } else {
            logActivity("Domain Price Sync stopped: No active TLDs found in WHMCS");
            exit("No active TLDs found in WHMCS");
        }
    } else {
        logActivity("Domain Price Sync stopped: Cron Job Disabled");
        exit("Domain Price Sync Cron Job Disabled");
    }

?>