<?php
global $CONFIG;
global $_ADDONLANG;
require "../init.php";
header("Content-type: application/javascript");
require "../includes/domainfunctions.php";
require_once ROOTDIR . "/modules/addons/resellerclubmods_tools/incs/functions.php";
$addonconf = [];
foreach (Illuminate\Database\Capsule\Manager::table("tbladdonmodules")->where("module", "resellerclubmods_tools")->get() as $addonvars) {
    $addonconf[$addonvars->setting] = $addonvars->value;
}
$tbl_dompricing = $addonconf["whmcs_style_tblclass"];
if (empty($tbl_dompricing)) {
    $tbl_dompricing = "";
}
$tr_dompricing = $addonconf["whmcs_style_trclass"];
if (empty($tr_dompricing)) {
    $tr_dompricing = "";
}
$th_dompricing = $addonconf["whmcs_style_thclass"];
if (empty($th_dompricing)) {
    $th_dompricing = "";
}
$td_dompricing = $addonconf["whmcs_style_tdclass"];
if (empty($td_dompricing)) {
    $td_dompricing = "";
}
$price_details = $_GET["details"];
    $is_language = isset($_SESSION["Language"]) ? $_SESSION["Language"] : $CONFIG["Language"];
    switch ($is_language) {
        case "arabic":
            include ROOTDIR . "/modules/addons/resellerclubmods_tools/lang/arabic.php";
            break;
        case "azerbaijani":
            include ROOTDIR . "/modules/addons/resellerclubmods_tools/lang/azerbaijani.php";
            break;
        case "catalan":
            include ROOTDIR . "/modules/addons/resellerclubmods_tools/lang/catalan.php";
            break;
        case "chinese":
            include ROOTDIR . "/modules/addons/resellerclubmods_tools/lang/chinese.php";
            break;
        case "croatian":
            include ROOTDIR . "/modules/addons/resellerclubmods_tools/lang/croatian.php";
            break;
        case "czech":
            include ROOTDIR . "/modules/addons/resellerclubmods_tools/lang/czech.php";
            break;
        case "danish":
            include ROOTDIR . "/modules/addons/resellerclubmods_tools/lang/danish.php";
            break;
        case "dutch":
            include ROOTDIR . "/modules/addons/resellerclubmods_tools/lang/dutch.php";
            break;
        case "english":
            include ROOTDIR . "/modules/addons/resellerclubmods_tools/lang/english.php";
            break;
        case "estonian":
            include ROOTDIR . "/modules/addons/resellerclubmods_tools/lang/estonian.php";
            break;
        case "farsi":
            include ROOTDIR . "/modules/addons/resellerclubmods_tools/lang/farsi.php";
            break;
        case "french":
            include ROOTDIR . "/modules/addons/resellerclubmods_tools/lang/french.php";
            break;
        case "german":
            include ROOTDIR . "/modules/addons/resellerclubmods_tools/lang/german.php";
            break;
        case "hebrew":
            include ROOTDIR . "/modules/addons/resellerclubmods_tools/lang/hebrew.php";
            break;
        case "hungarian":
            include ROOTDIR . "/modules/addons/resellerclubmods_tools/lang/hungarian.php";
            break;
        case "italian":
            include ROOTDIR . "/modules/addons/resellerclubmods_tools/lang/italian.php";
            break;
        case "macedonian":
            include ROOTDIR . "/modules/addons/resellerclubmods_tools/lang/macedonian.php";
            break;
        case "norwegian":
            include ROOTDIR . "/modules/addons/resellerclubmods_tools/lang/norwegian.php";
            break;
        case "polish":
            include ROOTDIR . "/modules/addons/resellerclubmods_tools/lang/polish.php";
            break;
        case "portuguese":
            include ROOTDIR . "/modules/addons/resellerclubmods_tools/lang/portuguese.php";
            break;
        case "portuguese-br":
            include ROOTDIR . "/modules/addons/resellerclubmods_tools/lang/portuguese-br.php";
            break;
        case "portuguese-pt":
            include ROOTDIR . "/modules/addons/resellerclubmods_tools/lang/portuguese-pt.php";
            break;
        case "romanian":
            include ROOTDIR . "/modules/addons/resellerclubmods_tools/lang/romanian.php";
            break;
        case "russian":
            include ROOTDIR . "/modules/addons/resellerclubmods_tools/lang/russian.php";
            break;
        case "spanish":
            include ROOTDIR . "/modules/addons/resellerclubmods_tools/lang/spanish.php";
            break;
        case "swedish":
            include ROOTDIR . "/modules/addons/resellerclubmods_tools/lang/swedish.php";
            break;
        case "turkish":
            include ROOTDIR . "/modules/addons/resellerclubmods_tools/lang/turkish.php";
            break;
        case "ukranian":
            include ROOTDIR . "/modules/addons/resellerclubmods_tools/lang/ukranian.php";
            break;
        default:
            include ROOTDIR . "/modules/addons/resellerclubmods_tools/lang/english.php";
            $showdefault_price = $addonconf["showdefault_price"];
            $whmcs_style = $addonconf["whmcs_style"];
            $default_promostyle = $addonconf["default_promostyle"];
            $default_sellingprice = $addonconf["default_sellingprice"];
            $tld_prega_class = $addonconf["tld_prega_class"];
            $tld_prega_title = $addonconf["tld_prega_title"];
            $tld_prega_text = $addonconf["tld_prega_text"];
            $show_prega_label = $addonconf["show_prega_label"];
            $show_restore_price = $addonconf["show_restore_price"];
            $show_restore_local = $addonconf["show_restore_local"];
            $rchttp_api = $addonconf["rchttp_api"];
            $conf_arr = [];
            $conf_arr["first"] = [$addonconf["first_rcauth_userid"], $addonconf["first_rcauth_apikey"], $addonconf["first_multiplicator"], $addonconf["first_defaultcurrency"]];
            $conf_arr["second"] = [$addonconf["second_rcauth_userid"], $addonconf["second_rcauth_apikey"], $addonconf["second_multiplicator"], $addonconf["second_defaultcurrency"]];
            $conf_arr["third"] = [$addonconf["third_rcauth_userid"], $addonconf["third_rcauth_apikey"], $addonconf["third_multiplicator"], $addonconf["third_defaultcurrency"]];
            $conf_arr["fourth"] = [$addonconf["fourth_rcauth_userid"], $addonconf["fourth_rcauth_apikey"], $addonconf["fourth_multiplicator"], $addonconf["fourth_defaultcurrency"]];
            if (is_numeric($conf_arr["first"][0]) && !empty($conf_arr["first"][1])) {
                $rcauth_userid = $conf_arr["first"][0];
                $rcauth_password = $conf_arr["first"][1];
                $mplicator = (float) $conf_arr["first"][2];
                $reseller_default_currency = $conf_arr["first"][3];
            } else if (is_numeric($conf_arr["second"][0]) && !empty($conf_arr["second"][1])) {
                $rcauth_userid = $conf_arr["second"][0];
                $rcauth_password = $conf_arr["second"][1];
                $mplicator = (float) $conf_arr["second"][2];
                $reseller_default_currency = $conf_arr["second"][3];
            } else if (is_numeric($conf_arr["third"][0]) && !empty($conf_arr["third"][1])) {
                $rcauth_userid = $conf_arr["third"][0];
                $rcauth_password = $conf_arr["third"][1];
                $mplicator = (float) $conf_arr["third"][2];
                $reseller_default_currency = $conf_arr["third"][3];
            } else if (is_numeric($conf_arr["fourth"][0]) && !empty($conf_arr["fourth"][1])) {
                $rcauth_userid = $conf_arr["fourth"][0];
                $rcauth_password = $conf_arr["fourth"][1];
                $mplicator = (float) $conf_arr["fourth"][2];
                $reseller_default_currency = $conf_arr["fourth"][3];
            }
            $result = Illuminate\Database\Capsule\Manager::table("tblcurrencies")->where("default", "=", "1")->select("code")->get();
            $whmcs_default_currency = $result[0]->code;
            $currency = $_SESSION["currency"];
            if (!is_numeric($currency)) {
                $currency = [];
            } else {
                $currency = getCurrency("", $currency);
            }
            if (!$currency || !is_array($currency) || !isset($currency["id"])) {
                $currency = getCurrency();
            }
            $currencycode = $currency["code"];
            $currencyid = $currency["id"];
            $currencyrate = $currency["rate"];
            $freeamt = formatCurrency(0);
            $none_default = 0;
            if ($_GET["salelistonly"]) {
                foreach (Illuminate\Database\Capsule\Manager::table("tbldomainpricing")->where("group", "=", "sale")->select("extension")->orderBy("order", "asc")->get() as $data) {
                    $tldslist[] = $data->extension;
                }
            } else {
                foreach (Illuminate\Database\Capsule\Manager::table("tbldomainpricing")->select("extension")->orderBy("order", "asc")->get() as $data) {
                    $tldslist[] = $data->extension;
                }
            }
            if ($show_restore_price == "on" && !isset($price_details)) {
                if ($show_restore_local != "on") {
                    if ($reseller_default_currency == $whmcs_default_currency) {
                        $is_calc = 0;
                        $mplicator = 1;
                    } else if ($reseller_default_currency != $currencycode) {
                        $result = Illuminate\Database\Capsule\Manager::table("tblcurrencies")->where("code", "=", $reseller_default_currency)->select("rate")->get();
                        $currencyrate = $result[0]->rate;
                        $is_calc = 1;
                        if ($currencyrate != "1.00000") {
                            $other_currencyrate = $currency["rate"];
                            $none_default = 1;
                        }
                    } else {
                        $is_calc = 0;
                        $currencyrate = "1.00000";
                    }
                } else {
                    $is_calc = 0;
                    $mplicator = 1;
                }
            }
            foreach (Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodspromo")->select("extension", "type", "sellingprice", "promoprice", "promoend")->get() as $data) {
                $pdata[] = json_decode(json_encode($data), true);
            }
            if (isset($_GET["tld"]) && !empty($_GET["tld"])) {
                $is_tld = $_GET["tld"];
            } else {
                $is_tld = ".com";
            }
            if (isset($_GET["type"]) && !empty($_GET["type"])) {
                $is_type = $_GET["type"];
            } else {
                $is_type = "register";
            }
            if (isset($_GET["currency"]) && !empty($_GET["currency"])) {
                $is_currency = $_GET["currency"];
            } else {
                $is_currency = $currencyid;
            }
            if (isset($_GET["format"]) && !empty($_GET["format"])) {
                $is_format = $_GET["format"];
            } else {
                $is_format = "";
            }
            if (isset($_GET["date"]) && !empty($_GET["date"])) {
                $is_date = $_GET["date"];
            } else {
                $is_date = "";
            }
            if (isset($_GET["promotitle"]) && !empty($_GET["promotitle"])) {
                $is_promotitle = $_GET["promotitle"];
            } else {
                $is_promotitle = "";
            }
            if (isset($_GET["limit"]) && !empty($_GET["limit"])) {
                $is_limit = $_GET["limit"];
            } else {
                $is_limit = "";
            }
            if (isset($_GET["regperiod"]) && !empty($_GET["regperiod"])) {
                $is_regperiod = $_GET["regperiod"];
            }
            if (!is_numeric($is_regperiod) || $is_regperiod < 1) {
                $is_regperiod = 1;
            }
            if (isset($_GET["details"]) && $_GET["details"] == "price") {
                $data = Illuminate\Database\Capsule\Manager::table("tbldomainpricing")->where("extension", "=", $is_tld)->join("tblpricing", "tbldomainpricing.id", "=", "tblpricing.relid")->where("type", "=", "domain" . $is_type)->where("currency", "=", $currencyid)->select("tbldomainpricing.id", "msetupfee", "qsetupfee", "ssetupfee", "asetupfee", "bsetupfee", "tsetupfee", "monthly", "quarterly", "semiannually", "annually", "biennially", "triennially")->get();
                if ($is_type == "transfer") {
                    $is_regperiod = 1;
                }
                if ($is_regperiod == 1) {
                    $regperiod = $data[0]->msetupfee;
                }
                if ($is_regperiod == 2) {
                    $regperiod = $data[0]->qsetupfee;
                }
                if ($is_regperiod == 3) {
                    $regperiod = $data[0]->ssetupfee;
                }
                if ($is_regperiod == 4) {
                    $regperiod = $data[0]->asetupfee;
                }
                if ($is_regperiod == 5) {
                    $regperiod = $data[0]->bsetupfee;
                }
                if ($is_regperiod == 6) {
                    $regperiod = $data[0]->tsetupfee;
                }
                if ($is_regperiod == 7) {
                    $regperiod = $data[0]->monthly;
                }
                if ($is_regperiod == 8) {
                    $regperiod = $data[0]->quarterly;
                }
                if ($is_regperiod == 9) {
                    $regperiod = $data[0]->semiannually;
                }
                if ($is_regperiod == 10) {
                    $regperiod = $data[0]->annually;
                }
                $price = $regperiod;
                if (is_array($pdata)) {
                    foreach ($pdata as $promodata) {
                        if ($is_tld == $promodata["extension"] && $promodata["type"] == "domain" . $is_type) {
                            if ($showdefault_price == "Yes") {
                                $sellingprice = " <span style=\"" . $default_sellingprice . "\">" . $promodata["sellingprice"] * $is_regperiod * $currencyrate . "</span>";
                            }
                            $promostyle_price = "<span style=\"" . $default_promostyle . "\">" . $price . $sellingprice . "</span>";
                            if ($is_format) {
                                $sellingprice = " <span style=\"" . $default_sellingprice . "\">" . formatCurrency($promodata["sellingprice"] * $is_regperiod * $currencyrate) . "</span>";
                                $promostyle_price = "<span style=\"" . $default_promostyle . "\">" . formatCurrency($price) . $sellingprice . "</span>";
                            }
                        } else {
                            $promostyle_price = $price;
                            $sellingprice = "";
                            if ($is_format) {
                                $promostyle_price = formatCurrency($price);
                            }
                        }
                    }
                } else {
                    $promostyle_price = $price;
                    $sellingprice = "";
                    if ($is_format) {
                        $promostyle_price = formatCurrency($price);
                    }
                }
                echo "document.write('" . $promostyle_price . "');";
            } else if (isset($_GET["details"]) && $_GET["details"] == "promos") {
                $promo_tldwidget = "";
                $limitcounter = 1;
                if (is_array($pdata)) {
                    sort($pdata);
                    $promo_tldwidget .= "<div class=\"div_dompromo\">";
                    if (!empty($is_promotitle)) {
                        $promo_tldwidget .= "<h3 class=\"h3_dompromo\">" . $_ADDONLANG["dompromotitle"] . "</h3>";
                    }
                    $promo_tldwidget .= "<table class=\"tbl_dompromo\">";
                    $promo_tldwidget .= "<thead><tr>";
                    $promo_tldwidget .= "<th class=\"th_dompromo\">" . $_ADDONLANG["dompromotld"] . "</th><th class=\"th_dompromo\">" . $_ADDONLANG["domainword"] . "</th><th class=\"th_dompromo\">" . $_ADDONLANG["dompromodefaultprice"] . "</th><th class=\"th_dompromo\">" . $_ADDONLANG["dompromoprice"] . "</th><th class=\"th_dompromo\">" . $_ADDONLANG["dompromovalid"] . "</th>";
                    $promo_tldwidget .= "</tr></thead>";
                    $promo_tldwidget .= "<tbody>";
                    foreach ($pdata as $promodata) {
                        if ($promodata["type"] == "domainregister") {
                            $promoapplyto = $_ADDONLANG["costregister"];
                        } else if ($promodata["type"] == "domaintransfer") {
                            $promoapplyto = $_ADDONLANG["costtransfer"];
                        } else if ($promodata["type"] == "domainrenew") {
                            $promoapplyto = $_ADDONLANG["costrenew"];
                        }
                        $promotld = $promodata["extension"];
                        if (empty($is_date)) {
                            $date = fromMySQLDate($promodata["promoend"], false, true);
                        } else {
                            $date = fromMySQLDate($promodata["promoend"], true);
                        }
                        $normal_sellingprice = number_format($promodata["sellingprice"] * $currencyrate, 2, ".", "");
                        $promo_sellingprice = number_format($promodata["promoprice"] * $currencyrate, 2, ".", "");
                        $sellingprice = " <span style=\"" . $default_sellingprice . "\">" . $normal_sellingprice . "</span>";
                        $promostyle_price = "<span style=\"" . $default_promostyle . "\">" . $promo_sellingprice . "</span>";
                        if ($is_format) {
                            $sellingprice = " <span style=\"" . $default_sellingprice . "\">" . formatCurrency($normal_sellingprice) . "</span>";
                            $promostyle_price = "<span style=\"" . $default_promostyle . "\">" . formatCurrency($promo_sellingprice) . "</span>";
                        }
                        $promo_tldwidget .= "<tr>";
                        $promo_tldwidget .= "<td class=\"td_dompromo\">" . $promotld . "</td><td class=\"td_dompromo\">" . $promoapplyto . "</td><td class=\"td_dompromo\">" . $sellingprice . "</td><td class=\"td_dompromo\">" . $promostyle_price . "</td><td class=\"td_dompromo\">" . $date . "</td>";
                        $promo_tldwidget .= "</tr>";
                        if (!empty($is_limit) && $is_limit < ++$limitcounter) {
                            $promo_tldwidget .= "</tbody></table></div>";
                        }
                    }
                }
                echo "document.write('" . $promo_tldwidget . "');";
            } else {
                if ($show_restore_price == "on" && !isset($price_details)) {
                    if (version_compare(getWver(), "7.5.0", ">=") && $show_restore_local == "on") {
                        foreach (Illuminate\Database\Capsule\Manager::table("tbldomainpricing")->get() as $fila_tlddata) {
                            $tldextension = $fila_tlddata->extension;
                            $redemption_fee = $fila_tlddata->redemption_grace_period_fee;
                            $restore[$tldextension] = $redemption_fee;
                        }
                    } else {
                        $apifunction = "/api/products/customer-price.json";
                        $data = "";
                        if (empty($_SESSION["rcm_get_restorepricing"]["clientarea"])) {
                            $get_restorepricing = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
                            $_SESSION["rcm_get_restorepricing"]["clientarea"] = serialize($get_restorepricing);
                        } else {
                            $get_restorepricing = unserialize($_SESSION["rcm_get_restorepricing"]["clientarea"]);
                        }
                        if (empty($_SESSION["rcm_productkeys"]["clientarea"])) {
                            $productkeys = newproductkeys($rcauth_userid, $rcauth_password, $rchttp_api);
                            $_SESSION["rcm_productkeys"]["clientarea"] = serialize($productkeys);
                        } else {
                            $productkeys = unserialize($_SESSION["rcm_productkeys"]["clientarea"]);
                        }
                        foreach ($productkeys as $productkey => $productvalue) {
                            foreach ($get_restorepricing as $key => $value) {
                                if ($productkey == $key) {
                                    $multitld = explode(",", $productvalue);
                                    $multitld = array_filter($multitld);
                                    $rc_productkey_array[$key] = $multitld;
                                }
                            }
                        }
                        foreach ($rc_productkey_array as $prodkey => $tldarray) {
                            foreach ($tldarray as $tld) {
                                $restoreprice = $get_restorepricing[$prodkey]["restoredomain"][1];
                                if (empty($restoreprice)) {
                                    $restoreprice = "0.00";
                                }
                                $restore[$tld] = $restoreprice;
                            }
                        }
                    }
                }
                if ($show_prega_label == "on" && !isset($price_details)) {
                    $apifunction = "/api/domains/tlds-in-phase.json";
                    $data = ["phase" => "prega"];
                    if (empty($_SESSION["rcm_get_pregatlds"]["clientarea"])) {
                        $get_pregatlds = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
                        $_SESSION["rcm_get_pregatlds"]["clientarea"] = serialize($get_pregatlds);
                    } else {
                        $get_pregatlds = unserialize($_SESSION["rcm_get_pregatlds"]["clientarea"]);
                    }
                }
                if ($whmcs_style == "Six") {
                    $tdstyleresponsive = "class=\"text-center hidden-xs\"";
                    $tdstyle = "class=\"text-center\"";
                    $visiblexs = "visible-xs";
                    $trstyle = "";
                    $code = "\\n<table class=\"table table-striped table-framed\">";
                    $code .= "<thead>";
                    $code .= "<tr>";
                    $code .= "<th class=\"text-center hidden-xs\">" . $_LANG["domaintld"] . "</th>";
                    $code .= "<th class=\"text-center\">" . $_LANG["domainsregister"] . "</th>";
                    $code .= "<th class=\"text-center\">" . $_LANG["domainstransfer"] . "</th>";
                    $code .= "<th class=\"text-center\">" . $_LANG["domainsrenew"] . "</th>";
                    if ($show_restore_price == "on") {
                        $code .= "<th class=\"text-center hidden-xs\">" . $_ADDONLANG["domainrestoretitle"] . "</th>";
                    }
                    $code .= "</tr>";
                    $code .= "</thead>";
                    $code .= "<tbody>";
                } else if ($whmcs_style == "Twenty-One") {
                    $tdstyleresponsive = "class=\"text-center d-none d-sm-block\"";
                    $tdstyle = "class=\"text-center\"";
                    $visiblexs = "d-block d-sm-none";
                    $trstyle = "";
                    $code = "\\n<table class=\"table table-striped table-framed\">";
                    $code .= "<thead>";
                    $code .= "<tr>";
                    $code .= "<th class=\"text-center d-none d-sm-block\">" . $_LANG["domaintld"] . "</th>";
                    $code .= "<th class=\"text-center\">" . $_LANG["domainsregister"] . "</th>";
                    $code .= "<th class=\"text-center\">" . $_LANG["domainstransfer"] . "</th>";
                    $code .= "<th class=\"text-center\">" . $_LANG["domainsrenew"] . "</th>";
                    if ($show_restore_price == "on") {
                        $code .= "<th class=\"text-center d-none d-sm-block\">" . $_ADDONLANG["domainrestoretitle"] . "</th>";
                    }
                    $code .= "</tr>";
                    $code .= "</thead>";
                    $code .= "<tbody>";
                } else {
                    $visiblexs = "";
                    $tdstyle = "class=\"" . $td_dompricing . "\"";
                    $tdstyleresponsive = "class=\"" . $td_dompricing . "\"";
                    $trstyle = "class=\"" . $tr_dompricing . "\"";
                    $code = "\\n<table class=\"" . $tbl_dompricing . "\">";
                    $code .= "<thead>";
                    $code .= "<tr>";
                    $code .= "<th class=\"" . $th_dompricing . "\">" . $_LANG["domaintld"] . "</th>";
                    $code .= "<th class=\"" . $th_dompricing . "\">" . $_LANG["domainsregister"] . "</th>";
                    $code .= "<th class=\"" . $th_dompricing . "\">" . $_LANG["domainstransfer"] . "</th>";
                    $code .= "<th class=\"" . $th_dompricing . "\">" . $_LANG["domainsrenew"] . "</th>";
                    if ($show_restore_price == "on") {
                        $code .= "<th class=\"" . $th_dompricing . "\">" . $_ADDONLANG["domainrestoretitle"] . "</th>";
                    }
                    $code .= "</tr>";
                    $code .= "</thead>";
                    $code .= "<tbody>";
                }
                foreach ($tldslist as $tld) {
                    if ($whmcs_style == "Six" || $whmcs_style == "Twenty-One") {
                        $six_tld = $tld;
                    }
                    $tldpricing = getTLDPriceList($tld, true);
                    $firstoption = current($tldpricing);
                    $year = key($tldpricing);
                    $transfer = $firstoption["transfer"] == $freeamt ? $_LANG["orderfree"] : $firstoption["transfer"];
                    if ($show_restore_price == "on") {
                        if ($restore[$tld] == "0.00" || $restore[$tld] == "-1.00" || empty($restore[$tld])) {
                            $promostyle_res = $_LANG["domainregnotavailable"];
                        } else if ($is_calc == 0) {
                            $promostyle_res = formatCurrency($restore[$tld] * $mplicator * $currencyrate);
                        } else if ($none_default == 1) {
                            $restore[$tld] = $restore[$tld] / $currencyrate;
                            $promostyle_res = formatCurrency($restore[$tld] * $mplicator * $other_currencyrate);
                        } else {
                            $promostyle_res = formatCurrency($restore[$tld] * $mplicator / $currencyrate);
                        }
                    }
                    if (is_array($get_pregatlds)) {
                        foreach ($get_pregatlds as $prega_group) {
                            foreach ($prega_group as $prega_tlds) {
                                $dottld = preg_replace("/./", "", strstr($tld, "."), 1);
                                if (in_array($dottld, $prega_tlds)) {
                                    $is_prega = " <span title=\"" . $tld_prega_title . "\" class=\"" . $tld_prega_class . "\">" . $tld_prega_text . "</span>";
                                } else {
                                    $is_prega = "";
                                }
                            }
                        }
                    }
                    if (is_array($pdata)) {
                        foreach ($pdata as $promodata) {
                            if ($tld == $promodata["extension"] && $promodata["type"] == "domainregister") {
                                if ($showdefault_price == "Yes") {
                                    $sellingprice = " <span style=\"" . $default_sellingprice . "\">" . formatCurrency($promodata["sellingprice"] * $currencyrate) . "</span>";
                                }
                                $promostyle_reg = "<span style=\"" . $default_promostyle . "\">" . $firstoption["register"] . $sellingprice . "</span>";
                                foreach ($pdata as $promodata) {
                                    if ($tld == $promodata["extension"] && $promodata["type"] == "domaintransfer") {
                                        if ($showdefault_price == "Yes") {
                                            $sellingprice = " <span style=\"" . $default_sellingprice . "\">" . formatCurrency($promodata["sellingprice"] * $currencyrate) . "</span>";
                                        }
                                        $promostyle_tra = "<span style=\"" . $default_promostyle . "\">" . $firstoption["transfer"] . $sellingprice . "</span>";
                                        foreach ($pdata as $promodata) {
                                            if ($tld == $promodata["extension"] && $promodata["type"] == "domainrenew") {
                                                if ($showdefault_price == "Yes") {
                                                    $sellingprice = " <span style=\"" . $default_sellingprice . "\">" . formatCurrency($promodata["sellingprice"] * $currencyrate) . "</span>";
                                                }
                                                $promostyle_ren = "<span style=\"" . $default_promostyle . "\">" . $firstoption["renew"] . $sellingprice . "</span>";
                                            } else {
                                                $promostyle_ren = $firstoption["renew"];
                                                if (empty($promostyle_ren)) {
                                                    $promostyle_ren = $_LANG["domainregnotavailable"];
                                                }
                                                $sellingprice = "";
                                            }
                                        }
                                    } else if ($is_prega) {
                                        $promostyle_tra = $_LANG["domainregnotavailable"];
                                        $sellingprice = "";
                                    } else {
                                        $promostyle_tra = $transfer;
                                        $sellingprice = "";
                                    }
                                }
                            } else {
                                $promostyle_reg = $firstoption["register"];
                                $sellingprice = "";
                            }
                        }
                    } else {
                        $promostyle_reg = $firstoption["register"];
                        $promostyle_tra = $transfer;
                        $promostyle_ren = $firstoption["renew"];
                    }
                    $code .= "<tr " . $trstyle . ">";
                    $code .= "<td " . $tdstyleresponsive . ">" . $tld . $is_prega . "</td>";
                    $code .= "<td " . $tdstyle . "><span class=\"" . $visiblexs . "\"><strong>" . $six_tld . "</strong></span>" . $promostyle_reg . "<br /><small>" . $year . " " . $_ADDONLANG["domainyear"] . "</small></td>";
                    $code .= "<td " . $tdstyle . "><span class=\"" . $visiblexs . "\"><strong>&nbsp;</strong></span>" . $promostyle_tra . "<br /><small>" . $year . " " . $_ADDONLANG["domainyear"] . "</small></td>";
                    $code .= "<td " . $tdstyle . "><span class=\"" . $visiblexs . "\"><strong>&nbsp;</strong></span>" . $promostyle_ren . "<br /><small>" . $year . " " . $_ADDONLANG["domainyear"] . "</small></td>";
                    if ($show_restore_price == "on") {
                        $code .= "<td " . $tdstyleresponsive . ">" . $promostyle_res . "<br /><small>" . $year . " " . $_ADDONLANG["domainyear"] . "</small></td>";
                    }
                    $code .= "</tr>";
                }
                $code .= "</tbody>";
                $code .= "</table>";
                echo "document.write('" . $code . "');";
            }
    }

?>