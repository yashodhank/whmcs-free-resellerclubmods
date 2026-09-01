<?php
if (!defined("RCM_GLOBAL_ACCESS_KEY")) {
    define("RCM_GLOBAL_ACCESS_KEY", "%bubimanual%");
}
if (!defined("WHMCS") && PHP_SAPI !== "cli" && (!isset($_POST["rcm_pass"]) || $_POST["rcm_pass"] !== RCM_GLOBAL_ACCESS_KEY) && !isset($_REQUEST["coreorigin"])) {
    exit("This file cannot be accessed directly");
}
$whmcs_root_dir = realpath(dirname(__FILE__) . "/../../../");
if (!defined("ROOTDIR") && $whmcs_root_dir && file_exists($whmcs_root_dir . "/init.php")) {
    include_once $whmcs_root_dir . "/init.php";
}
if (!class_exists("idna_convert")) {
    require_once ROOTDIR . "/modules/addons/resellerclubmods_tools/incs/idnclass.php";
}
$IDN = new idna_convert();
require ROOTDIR . "/modules/addons/resellerclubmods_tools/incs/functions.php";
require ROOTDIR . "/modules/addons/resellerclubmods_tools/tools/clientareatools.php";
function resellerclubmods_tools_output($vars)
{
    echo "\r\n\t\t\t\t<script>\r\n\t\t\t\t\$(document).ready(function(){\r\n\t\t\t\t  \$('[data-toggle=\"tooltip\"]').tooltip();\r\n\t\t\t\t});\r\n\t\t\t\t</script>\t\t\t\r\n\t\t\t\r\n\t\t\t\t<style type=\"text/css\">\r\n\t\t\t\t\r\n\t\t\t\t.alert {\r\n\t\t\t\t  padding: 15px;\r\n\t\t\t\t  margin-bottom: 20px;\r\n\t\t\t\t  border: 1px solid transparent;\r\n\t\t\t\t  border-radius: 4px;\r\n\t\t\t\t}\r\n\t\t\t\t.alert-danger {\r\n\t\t\t\t  color: #a94442;\r\n\t\t\t\t  background-color: #f2dede;\r\n\t\t\t\t  border-color: #ebccd1;\r\n\t\t\t\t}\r\n\t\t\t\t.alert-warning {\r\n\t\t\t\t  color: #8a6d3b;\r\n\t\t\t\t  background-color: #fcf8e3;\r\n\t\t\t\t  border-color: #faebcc;\r\n\t\t\t\t}\r\n\t\t\t\t.alert-info {\r\n\t\t\t\t  color: #31708f;\r\n\t\t\t\t  background-color: #d9edf7;\r\n\t\t\t\t  border-color: #bce8f1;\r\n\t\t\t\t}\r\n\t\t\t\t.alert p, .alert ul {\r\n\t\t\t\t\tmargin-top: 5px;\r\n\t\t\t\t\tmargin-bottom: 5px;\r\n\t\t\t\t}\r\n\r\n\t\t\t\t/* Reset background back to normal */\r\n\t\t\t\t.rcm-row-ok td,\r\n\t\t\t\t.rcm-row-importable td,\r\n\t\t\t\t.rcm-row-zombie td {\r\n\t\t\t\t\tbackground-color: transparent !important;\r\n\t\t\t\t}\r\n\t\t\t\t\r\n\t\t\t\t/* Left border highlight per status */\r\n\t\t\t\t.rcm-row-ok td:first-child {\r\n\t\t\t\t\tborder-left: 4px solid #28a745 !important; /* green */\r\n\t\t\t\t}\r\n\t\t\t\t.rcm-row-importable td:first-child {\r\n\t\t\t\t\tborder-left: 4px solid #ffc107 !important; /* yellow */\r\n\t\t\t\t}\r\n\t\t\t\t.rcm-row-zombie td:first-child {\r\n\t\t\t\t\tborder-left: 4px solid #dc3545 !important; /* red */\r\n\t\t\t\t}\r\n\t\t\t\ta.rcm-tld-link.rcm-tld-link-active {\r\n\t\t\t\t\tfont-weight: 600;\r\n\t\t\t\t\ttext-decoration: underline;\r\n\t\t\t\t}\t\t\t\t\r\n\t\t\t\t</style>";
    if (!class_exists("idna_convert")) {
        require_once ROOTDIR . "/modules/addons/resellerclubmods_tools/incs/idnclass.php";
    }
    $IDN = new idna_convert();
    $rcmdebuginfo = getDebuginfos();
    $debug_addinfo = $rcmdebuginfo["debug_addinfo"];
    $modulename = $rcmdebuginfo["modulename"];
    $customadminpath = $rcmdebuginfo["customadminpath"];
    $CONFIG = $rcmdebuginfo["config"];
    $systemurl = !empty($CONFIG["SystemSSLURL"]) ? $CONFIG["SystemSSLURL"] : $CONFIG["SystemURL"];
    $freetools_home_redir = $systemurl . "/" . $customadminpath . "/addonmodules.php?module=resellerclubmods_tools";
    $maileradmin = $vars["maileradmin"];
    $sendconfmail = $vars["sendconfmail"];
    $emailtplname = $vars["templatename"];
    $templatenameunlock = $vars["templatenameunlock"];
    $templatenameeppcode = $vars["templatenameeppcode"];
    $templatenameidprotect = $vars["templatenameidprotect"];
    $templatenamefailed = $vars["templatenamefailed"];
    $brokentransfers = $vars["brokentransfers"];
    $promo_update_active = $vars["promo_end_check"];
    $promo_auto_activate = $vars["promo_auto_activate"];
    $raa_report_active = $vars["raa_domain_check"];
    $transferfree_tlds = $vars["transferfree_tlds"];
    $pagination_tlds = $vars["pagination_tlds"];
    if (empty($transferfree_tlds)) {
        $transferfree_tlds = "com.au,net.au,co.uk,me.uk,org.uk,com.ru,net.ru,org.ru,ru,es";
    }
    $transferfree_tlds_array = explode(",", $transferfree_tlds);
    array_walk($transferfree_tlds_array, "rcm_array_trim");
    $tblconf = [];
    try {
        foreach (Illuminate\Database\Capsule\Manager::table("tblconfiguration")->get() as $tblconfvars) {
            $tblconf[$tblconf->setting] = $tblconf->value;
        }
    } catch (Exception $e) {
        echo "<div class=\"alert alert-danger\">" . $e->getMessage() . "</div>";
    }
    try {
        $tblregistrars = [];
        foreach (Illuminate\Database\Capsule\Manager::table("tblregistrars")->get() as $registrars) {
            $tblregistrars[] = $registrars->registrar;
        }
        $tblregistrars = array_unique($tblregistrars);
        sort($tblregistrars);
    } catch (Exception $e) {
        echo "<div class=\"alert alert-danger\">" . $e->getMessage() . "</div>";
    }
    $style_label = "class=\"label terminated\"";
    $style_labelwarn = "class=\"label pending\"";
    $style_labelinfo = "class=\"label expired\"";
    $style_labelok = "class=\"label active\"";
    $style_promolabel = "class=\"label active\" style=\"cursor:pointer;\"";
    $LANG = $vars["_lang"];
    if (isset($_POST["changeaccount"]) && $_POST["changeaccount"] == "true") {
        unset($_SESSION["rcm_domain_customer_import_array"]);
        $table = "mod_resellerclubmodstools";
        if (is_numeric($_POST["account_number"]) && $_POST["account_number"] == 1) {
            $rcauth_userid = $vars["first_rcauth_userid"];
            $rcauth_password = $vars["first_rcauth_apikey"];
            $logicbox_registrar = $vars["first_domainregistrar"];
            $defaultcurrency = $vars["first_defaultcurrency"];
            $currencyswitch = $vars["first_currencyswitch"];
            $multiplicator = (float) $vars["first_multiplicator"];
            $account_name = $vars["first_acc_name"];
            $account_number = 1;
            $values = ["account_name" => $account_name, "account_number" => $account_number, "rcauth_userid" => $rcauth_userid, "rcauth_password" => $rcauth_password, "logicbox_registrar" => $logicbox_registrar, "defaultcurrency" => $defaultcurrency, "currencyswitch" => $currencyswitch, "multiplicator" => $multiplicator];
            $result_update = Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodstools")->update($values);
            echo "<div class=\"alert alert-success\"><p>" . $LANG["changeaccountsuccess"] . " <strong>" . $account_name . "</strong></p></div>";
        } else if (is_numeric($_POST["account_number"]) && $_POST["account_number"] == 2) {
            $rcauth_userid = $vars["second_rcauth_userid"];
            $rcauth_password = $vars["second_rcauth_apikey"];
            $logicbox_registrar = $vars["second_domainregistrar"];
            $defaultcurrency = $vars["second_defaultcurrency"];
            $currencyswitch = $vars["second_currencyswitch"];
            $multiplicator = (float) $vars["second_multiplicator"];
            $account_name = $vars["second_acc_name"];
            $account_number = 2;
            $values = ["account_name" => $account_name, "account_number" => $account_number, "rcauth_userid" => $rcauth_userid, "rcauth_password" => $rcauth_password, "logicbox_registrar" => $logicbox_registrar, "defaultcurrency" => $defaultcurrency, "currencyswitch" => $currencyswitch, "multiplicator" => $multiplicator];
            $result_update = Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodstools")->update($values);
            echo "<div class=\"alert alert-success\"><p>" . $LANG["changeaccountsuccess"] . " <strong>" . $account_name . "</strong></p></div>";
        } else if (is_numeric($_POST["account_number"]) && $_POST["account_number"] == 3) {
            $rcauth_userid = $vars["third_rcauth_userid"];
            $rcauth_password = $vars["third_rcauth_apikey"];
            $logicbox_registrar = $vars["third_domainregistrar"];
            $defaultcurrency = $vars["third_defaultcurrency"];
            $currencyswitch = $vars["third_currencyswitch"];
            $multiplicator = (float) $vars["third_multiplicator"];
            $account_name = $vars["third_acc_name"];
            $account_number = 3;
            $values = ["account_name" => $account_name, "account_number" => $account_number, "rcauth_userid" => $rcauth_userid, "rcauth_password" => $rcauth_password, "logicbox_registrar" => $logicbox_registrar, "defaultcurrency" => $defaultcurrency, "currencyswitch" => $currencyswitch, "multiplicator" => $multiplicator];
            $result_update = Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodstools")->update($values);
            echo "<div class=\"alert alert-success\"><p>" . $LANG["changeaccountsuccess"] . " <strong>" . $account_name . "</strong></p></div>";
        } else if (is_numeric($_POST["account_number"]) && $_POST["account_number"] == 4) {
            $rcauth_userid = $vars["fourth_rcauth_userid"];
            $rcauth_password = $vars["fourth_rcauth_apikey"];
            $logicbox_registrar = $vars["fourth_domainregistrar"];
            $defaultcurrency = $vars["fourth_defaultcurrency"];
            $currencyswitch = $vars["fourth_currencyswitch"];
            $multiplicator = (float) $vars["fourth_multiplicator"];
            $account_name = $vars["fourth_acc_name"];
            $account_number = 4;
            $values = ["account_name" => $account_name, "account_number" => $account_number, "rcauth_userid" => $rcauth_userid, "rcauth_password" => $rcauth_password, "logicbox_registrar" => $logicbox_registrar, "defaultcurrency" => $defaultcurrency, "currencyswitch" => $currencyswitch, "multiplicator" => $multiplicator];
            $result_update = Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodstools")->update($values);
            echo "<div class=\"alert alert-success\"><p>" . $LANG["changeaccountsuccess"] . " <strong>" . $account_name . "</strong></p></div>";
        } else {
            echo "<div class=\"alert alert-danger\"><p>" . $LANG["changeaccountinfo"] . "</p></div>";
        }
    }
    $result = Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodstools")->select()->get();
    $rcauth_userid = $result[0]->rcauth_userid;
    $rcauth_password = $result[0]->rcauth_password;
    $rchttp_api = $result[0]->rchttp_api;
    $logicbox_registrar = $result[0]->logicbox_registrar;
    $defaultcurrency = $result[0]->defaultcurrency;
    $currencyswitch = $result[0]->currencyswitch;
    $multiplicator = (float) $result[0]->multiplicator;
    $account_name = $result[0]->account_name;
    $lockey = $result[0]->localkey;
    $syncdate = $result[0]->lastcheck;
    $account_number = $result[0]->account_number;
    if ($logicbox_registrar == "resellerclub") {
        $registrar_label = "ResellerClub";
    }
    if ($logicbox_registrar == "netearthone") {
        $registrar_label = "NetEarthOne";
    }
    if ($logicbox_registrar == "stargate") {
        $registrar_label = "Resell.biz (stargate/uk2)";
    }
    if ($logicbox_registrar == "resellercamp") {
        $registrar_label = "ResellerCamp";
    }
    if ($account_number == 1) {
        $is_transfercheck = $vars["first_transer_check"];
        $is_hooksignup = $vars["first_hook_signup"];
        $is_pwdsignup = $vars["first_pwd_signup"];
        $is_hookmodify = $vars["first_hook_modify"];
        $is_hookmodify_email = $vars["first_hook_modify_useremail"];
        $is_pwdmodify = $vars["first_pwd_modify"];
        $is_hookdelete = $vars["first_hook_delete"];
        $is_threshold = $vars["first_threshold"];
        $is_domainsync = $vars["first_domainsync_check"];
        $is_domainsynctlds = $vars["first_domainsync_tlds"];
        $is_onlybaseslab = $vars["first_only_baseslab"];
        $is_defaultns = $vars["first_ns_override"];
        $is_domaintelescope = $vars["first_domainsync_telescope"];
        $is_domainsyncexcludetlds = $vars["first_exclude_domainsync_tlds"];
        $is_redemption = $vars["first_domainsync_redemption"];
    } else if ($account_number == 2) {
        $is_transfercheck = $vars["second_transer_check"];
        $is_hooksignup = $vars["second_hook_signup"];
        $is_pwdsignup = $vars["second_pwd_signup"];
        $is_hookmodify = $vars["second_hook_modify"];
        $is_pwdmodify = $vars["second_pwd_modify"];
        $is_hookdelete = $vars["second_hook_delete"];
        $is_threshold = $vars["second_threshold"];
        $is_domainsync = $vars["second_domainsync_check"];
        $is_domainsynctlds = $vars["second_domainsync_tlds"];
        $is_onlybaseslab = $vars["second_only_baseslab"];
        $is_defaultns = $vars["second_ns_override"];
        $is_domaintelescope = $vars["second_domainsync_telescope"];
        $is_domainsyncexcludetlds = $vars["second_exclude_domainsync_tlds"];
        $is_redemption = $vars["second_domainsync_redemption"];
    } else if ($account_number == 3) {
        $is_transfercheck = $vars["third_transer_check"];
        $is_hooksignup = $vars["third_hook_signup"];
        $is_pwdsignup = $vars["third_pwd_signup"];
        $is_hookmodify = $vars["third_hook_modify"];
        $is_pwdmodify = $vars["third_pwd_modify"];
        $is_hookdelete = $vars["third_hook_delete"];
        $is_threshold = $vars["third_threshold"];
        $is_domainsync = $vars["third_domainsync_check"];
        $is_domainsynctlds = $vars["third_domainsync_tlds"];
        $is_onlybaseslab = $vars["third_only_baseslab"];
        $is_defaultns = $vars["third_ns_override"];
        $is_domaintelescope = $vars["third_domainsync_telescope"];
        $is_domainsyncexcludetlds = $vars["third_exclude_domainsync_tlds"];
        $is_redemption = $vars["third_domainsync_redemption"];
    } else if ($account_number == 4) {
        $is_transfercheck = $vars["fourth_transer_check"];
        $is_hooksignup = $vars["fourth_hook_signup"];
        $is_pwdsignup = $vars["fourth_pwd_signup"];
        $is_hookmodify = $vars["fourth_hook_modify"];
        $is_pwdmodify = $vars["fourth_pwd_modify"];
        $is_hookdelete = $vars["fourth_hook_delete"];
        $is_threshold = $vars["fourth_threshold"];
        $is_domainsync = $vars["fourth_domainsync_check"];
        $is_domainsynctlds = $vars["fourth_domainsync_tlds"];
        $is_onlybaseslab = $vars["fourth_only_baseslab"];
        $is_defaultns = $vars["fourth_ns_override"];
        $is_domaintelescope = $vars["fourth_domainsync_telescope"];
        $is_domainsyncexcludetlds = $vars["fourth_exclude_domainsync_tlds"];
        $is_redemption = $vars["fourth_domainsync_redemption"];
    }
    $is_promoactivate = $vars["promo_auto_activate"];
    $is_promocheck = $vars["promo_end_check"];
    $is_raacheck = $vars["raa_domain_check"];
    $is_domainlookup = $vars["use_account_whois"];
    $is_recurringdomupd = $vars["update_domain_recurring"];
    $is_currencydoupd = $vars["domainsync_currencydoupd"];
    $modulelink = $vars["modulelink"];
    $version = $vars["version"];
    $currencysymbol = $vars["currencysymbol"];
    if ($vars["totalfundscolor"]) {
        $totalfundscolor = $vars["totalfundscolor"];
    } else {
        $totalfundscolor = "#000000";
    }
    if ($vars["blockedfundscolor"]) {
        $blockedfundscolor = $vars["blockedfundscolor"];
    } else {
        $blockedfundscolor = "#CC0000";
    }
    if ($vars["availfundscolor"]) {
        $availfundscolor = $vars["availfundscolor"];
    } else {
        $availfundscolor = "#779500";
    }
    if ($_GET["user"] == "rcuser-vs-whmcsusers") {
        $qtyrecords = 20;
        $qtypageno = 1;
        $method = "GET";
        $apifunction = "/api/customers/search.json";
        $data = ["no-of-records" => $qtyrecords, "page-no" => $qtypageno];
        $recsindb_arrXml = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
        $GLOBALS["recsindb_length"] = $recsindb_arrXml["recsindb"];
    }
    if (!class_exists("rcm_pagination")) {
        class rcm_pagination
        {
            public $page = 1;
            public $perPage = 20;
            public $showFirstAndLast = false;
            public $maxPageLinks = 0;
            protected $length = 0;
            protected $pages = 0;
            protected $start = 0;
            public function __construct($perPage = 20)
            {
                if (is_numeric($perPage) && 0 < (int) $perPage) {
                    $this->perPage = (int) $perPage;
                }
            }
            public function generate($array, $perPage = 20)
            {
                if (!empty($perPage) && is_numeric($perPage) && 0 < (int) $perPage) {
                    $this->perPage = (int) $perPage;
                }
                $page = isset($_GET["page"]) ? (int) $_GET["page"] : 1;
                if ($page < 1) {
                    $page = 1;
                }
                $this->page = $page;
                $userParam = isset($_GET["user"]) ? (string) $_GET["user"] : "";
                if ($userParam === "rcuser-vs-whmcsusers") {
                    $this->length = isset($GLOBALS["recsindb_length"]) && is_numeric($GLOBALS["recsindb_length"]) ? (int) $GLOBALS["recsindb_length"] : 0;
                    $this->pages = 0 < $this->perPage ? (int) ceil($this->length / $this->perPage) : 0;
                    $this->start = 0;
                    if (!is_array($array)) {
                        $array = (array) $array;
                    }
                    return array_slice($array, 0, $this->perPage);
                }
                if (!is_array($array)) {
                    $array = (array) $array;
                }
                $this->length = count($array);
                $this->pages = 0 < $this->perPage ? (int) ceil($this->length / $this->perPage) : 0;
                if (0 < $this->pages && $this->pages < $this->page) {
                    $this->page = $this->pages;
                }
                $this->start = ($this->page - 1) * $this->perPage;
                if ($this->start < 0) {
                    $this->start = 0;
                }
                return array_slice($array, $this->start, $this->perPage);
            }
            public function links()
            {
                global $_ADDONLANG;
                $plinks = [];
                $links = [];
                $slinks = [];
                $queryURL = "";
                if (!empty($_GET) && is_array($_GET)) {
                    foreach ($_GET as $key => $value) {
                        if ($key === "page") {
                        } else {
                            $queryURL .= "&" . rawurlencode($key) . "=" . rawurlencode((string) $value);
                        }
                    }
                }
                if ($this->pages <= 1) {
                    return "";
                }
                $script = isset($_SERVER["SCRIPT_NAME"]) ? $_SERVER["SCRIPT_NAME"] : "";
                $script = htmlspecialchars($script, ENT_QUOTES, "UTF-8");
                if (1 < $this->page) {
                    if ($this->showFirstAndLast) {
                        $plinks[] = "<a href=\"" . $script . "?page=1" . $queryURL . "\">&laquo;&laquo; " . $_ADDONLANG["pagefirst"] . " </a>";
                    }
                    $prev = $this->page - 1;
                    $plinks[] = "<a href=\"" . $script . "?page=" . $prev . $queryURL . "\">&laquo; " . $_ADDONLANG["pageprev"] . " </a>";
                }
                $startPage = 1;
                $endPage = $this->pages;
                if (0 < $this->maxPageLinks && $this->maxPageLinks < $this->pages) {
                    $half = (int) floor($this->maxPageLinks / 2);
                    $startPage = $this->page - $half;
                    $endPage = $this->page + $half;
                    if ($startPage < 1) {
                        $startPage = 1;
                        $endPage = $this->maxPageLinks;
                    }
                    if ($this->pages < $endPage) {
                        $endPage = $this->pages;
                        $startPage = $this->pages - $this->maxPageLinks + 1;
                        if ($startPage < 1) {
                            $startPage = 1;
                        }
                    }
                }
                if (1 < $startPage) {
                    $links[] = "<a href=\"" . $script . "?page=1" . $queryURL . "\">1</a>";
                    if (2 < $startPage) {
                        $links[] = "...";
                    }
                }
                for ($j = $startPage; $j <= $endPage; $j++) {
                    if ($this->page === $j) {
                        $links[] = "<a style=\"font-weight:bold;\">" . $j . "</a>";
                    } else {
                        $links[] = "<a href=\"" . $script . "?page=" . $j . $queryURL . "\">" . $j . "</a>";
                    }
                }
                if ($endPage < $this->pages) {
                    if ($endPage < $this->pages - 1) {
                        $links[] = "...";
                    }
                    $links[] = "<a href=\"" . $script . "?page=" . $this->pages . $queryURL . "\">" . $this->pages . "</a>";
                }
                if ($this->page < $this->pages) {
                    $next = $this->page + 1;
                    $slinks[] = "<a href=\"" . $script . "?page=" . $next . $queryURL . "\"> " . $_ADDONLANG["pagenext"] . " &raquo; </a>";
                    if ($this->showFirstAndLast) {
                        $slinks[] = "<a href=\"" . $script . "?page=" . $this->pages . $queryURL . "\"> " . $_ADDONLANG["pagelast"] . " &raquo;&raquo; </a>";
                    }
                }
                return implode(" ", $plinks) . implode(" ", $links) . implode(" ", $slinks);
            }
        }
    }
    $method = "GET";
    $apifunction = "/api/resellers/details.json";
    $resellerdetails_arrXml = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
    $reseller_buycurrency = $resellerdetails_arrXml["parentsellingcurrencysymbol"];
    $is_mplicator = 1;
    $is_1000s = is_inthousands($reseller_buycurrency);
    if ($is_1000s == 1) {
        $is_mplicator = 1000;
    }
    $lb_sellingcurrency = $resellerdetails_arrXml["sellingcurrencysymbol"];
    $result = Illuminate\Database\Capsule\Manager::table("tblcurrencies")->where("default", "=", "1")->select("id", "code")->get();
    $currency = $result[0]->id;
    $currencycode = $result[0]->code;
    $reseller_sellingcurrency = $currencycode;
    if ($currencyswitch == "on") {
        $result = Illuminate\Database\Capsule\Manager::table("tblcurrencies")->where("code", "=", $defaultcurrency)->select("rate")->get();
        $currencyrate = (float) $result[0]->rate;
    }
    echo "<table style=\"margin-bottom:10px;\"><tr><td><img style=\"float:left;border:0\" src=\"../modules/addons/resellerclubmods_tools/img/rcmini.png\" /></td>\r\n\t\t\t\t <td style=\"padding:5px 0px 0px 5px;\"><strong>" . $LANG["freetoolslike"] . "</strong> " . $LANG["followus"] . ": <a href=\"https://facebook.com/resmods\" target=\"_blank\">Facebook</a> <a href=\"https://twitter.com/resmods\" arget=\"_blank\">Twitter</a><br />\r\n\t\t\t\t " . $LANG["freetoolslikevote1"] . " <a href=\"https://marketplace.whmcs.com/product/534\" target=\"_blank\">WHMCS Marketplace</a>. " . $LANG["freetoolslikevote2"] . "<br />\r\n\t\t\t\t </td></tr></table>";
    $selectorarray = [];
    if (!empty($vars["first_acc_name"])) {
        $selectorarray[1] = $vars["first_acc_name"] . " - " . $vars["first_rcauth_userid"];
    }
    if (!empty($vars["second_acc_name"])) {
        $selectorarray[2] = $vars["second_acc_name"] . " - " . $vars["second_rcauth_userid"];
    }
    if (!empty($vars["third_acc_name"])) {
        $selectorarray[3] = $vars["third_acc_name"] . " - " . $vars["third_rcauth_userid"];
    }
    if (!empty($vars["fourth_acc_name"])) {
        $selectorarray[4] = $vars["fourth_acc_name"] . " - " . $vars["fourth_rcauth_userid"];
    }
    foreach ($selectorarray as $key => $value) {
        $selectoptions .= "<option value=\"" . $key . "\">" . $value . "</option>";
    }
    $tools_change_acc_dropdown = "\r\n\t\t\t<form class=\"form-inline\" action=\"" . $_SERVER["REQUEST_URI"] . "\" method=\"post\">\r\n\t\t\t<select class=\"form-control input-sm\" name=\"account_number\" onchange=\"submit();\" style=\"padding: 0;\">\r\n\t\t\t<option value=\"\">" . $LANG["changercacc"] . "</option>\r\n\t\t\t" . $selectoptions . "\r\n\t\t\t</select>&nbsp;<img class=\"absmiddle\" style=\"cursor:help;\" src=\"../modules/addons/resellerclubmods_tools/img/help.png\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["changeaccountnote"] . "\" height=\"16\" width=\"16\" />\r\n\t\t\t<input type=\"hidden\" name=\"changeaccount\" value=\"true\" />\r\n\t\t\t</form>";
    $configuredto = "\r\n\t\t\t<div style=\"float:left;padding:5px;width:auto;font-size:13px\"><strong>" . $LANG["configuredto"] . "</strong> " . $account_name . " - <strong>" . $LANG["lbtitle"] . "</strong> <span " . $style_label . ">" . $logicbox_registrar . "</span> - <strong>ID:</strong> " . $rcauth_userid . "</div>\r\n\t\t\t<div style=\"float:left;padding:0px;width:auto;font-size:13px\">" . $tools_change_acc_dropdown . "</div>\r\n\t\t\t<div></div><br />\r\n\t\t\t<div style=\"border-bottom:1px solid #cccccc;margin-top:10px;padding-top:10px;\"></div><br />";
    if ($_GET["trans"] == "showfunds") {
        include ROOTDIR . "/modules/addons/resellerclubmods_tools/tools/fundsbalance.php";
    } else if ($_GET["domain"] == "domain-pricing-import") {
        require ROOTDIR . "/includes/currencyfunctions.php";
        include ROOTDIR . "/modules/addons/resellerclubmods_tools/tools/importdompricing.php";
    } else if ($_GET["domain"] == "domainimport") {
        include ROOTDIR . "/modules/addons/resellerclubmods_tools/tools/importdomains.php";
    } else if ($_GET["domain"] == "showpromos") {
        require ROOTDIR . "/includes/currencyfunctions.php";
        include ROOTDIR . "/modules/addons/resellerclubmods_tools/tools/managepromos.php";
    } else if ($_GET["user"] == "whmcsuser-vs-rcusers") {
        include ROOTDIR . "/modules/addons/resellerclubmods_tools/tools/exportusers.php";
    } else if ($_GET["user"] == "rcuser-vs-whmcsusers") {
        include ROOTDIR . "/modules/addons/resellerclubmods_tools/tools/importusers.php";
    } else if ($_GET["domain"] == "transfercheck") {
        include ROOTDIR . "/modules/addons/resellerclubmods_tools/tools/transfercheck.php";
    } else if ($_GET["domain"] == "moveservices") {
        include ROOTDIR . "/modules/addons/resellerclubmods_tools/tools/movedomain.php";
    } else if ($_GET["domain"] == "bulkmove") {
        include ROOTDIR . "/modules/addons/resellerclubmods_tools/tools/bulkdomainmove.php";
    } else if ($_GET["domain"] == "tldmanage") {
        include ROOTDIR . "/modules/addons/resellerclubmods_tools/tools/tldmanage.php";
    } else if ($_GET["automation"] == "cronjobs") {
        include ROOTDIR . "/modules/addons/resellerclubmods_tools/tools/automationtools.php";
    } else if ($_GET["domain"] == "raamanagement") {
        include ROOTDIR . "/modules/addons/resellerclubmods_tools/tools/raamanagement.php";
    } else if ($_GET["domain"] == "rcmwhois") {
        if (getWver() < "7.0.0") {
            include ROOTDIR . "/modules/addons/resellerclubmods_tools/tools/guiwhois.php";
        } else {
            include ROOTDIR . "/modules/addons/resellerclubmods_tools/tools/gui7whois.php";
        }
    } else {
        include ROOTDIR . "/modules/addons/resellerclubmods_tools/tools/home.php";
    }
}
function resellerclubmods_tools_sidebar($vars)
{
    $modulelink = $vars["modulelink"];
    $version = $vars["version"];
    $LANG = $vars["_lang"];
    $versioninfo = $LANG["toolsversiontitle"] . $version;
    if (version_compare(getWver(), "7.10.9") <= 0) {
        $sidebar = "\r\n\t\t\t\t\t<span class=\"header\"><img src=\"images/icons/addonmodules.png\" class=\"absmiddle\" width=\"16\" height=\"16\" /> RC & LB Tools v2</span>\r\n\t\t\t\t";
    } else {
        $sidebar = "\r\n\t\t\t\t\t<div class=\"sidebar-header\">\r\n\t\t\t\t\t\t<i class=\"far fa-code\"></i>\r\n\t\t\t\t\t\tRC & LB Tools v2\r\n\t\t\t\t\t</div>\r\n\t\t\t\t";
    }
    $sidebar .= "\r\n\t\t\t<ul class=\"menu\">\r\n\t\t\t<li><a style=\"white-space:nowrap;font-size:12px;text-decoration:none;color:#cc0000;\" href=\"configaddonmods.php#resellerclubmods_tools\">" . $LANG["configaddontitle"] . "</a></li>\r\n\t\t\t<li><a style=\"white-space:nowrap;font-size:12px;text-decoration:none;\" href=\"" . $modulelink . "\">" . $LANG["toolshome"] . "</a></li>";
    if ($_SESSION["rcm_wrong_currencysetup"] != 1) {
        $sidebar .= "<li><a style=\"white-space:nowrap;font-size:12px;text-decoration:none;\" href=\"" . $modulelink . "&automation=cronjobs\">" . $LANG["automationtools"] . "</a></li>\r\n\t\t\t\t<li><a style=\"white-space:nowrap;font-size:12px;text-decoration:none;\" href=\"" . $modulelink . "&user=whmcsuser-vs-rcusers\">" . $LANG["whmcsvsrcusers"] . "</a></li>\r\n\t\t\t\t<li><a style=\"white-space:nowrap;font-size:12px;text-decoration:none;\" href=\"" . $modulelink . "&user=rcuser-vs-whmcsusers\">" . $LANG["rcvswhmcscusers"] . "</a></li>\r\n\t\t\t\t<li><a style=\"white-space:nowrap;font-size:12px;text-decoration:none;\" href=\"" . $modulelink . "&domain=domainimport\">" . $LANG["domainimporttitle"] . "</a></li>\r\n\t\t\t\t<li><a style=\"white-space:nowrap;font-size:12px;text-decoration:none;\" href=\"" . $modulelink . "&domain=domain-pricing-import\">" . $LANG["domainpriceimporttitle"] . "</a></li>\r\n\t\t\t\t<li><a style=\"white-space:nowrap;font-size:12px;text-decoration:none;\" href=\"" . $modulelink . "&domain=tldmanage\">" . $LANG["tldmanagesidebarlink"] . "</a></li>\r\n\t\t\t\t<li><a style=\"white-space:nowrap;font-size:12px;text-decoration:none;\" href=\"" . $modulelink . "&domain=showpromos\">" . $LANG["domainpromos"] . "</a></li>\r\n\t\t\t\t<li><a style=\"white-space:nowrap;font-size:12px;text-decoration:none;\" href=\"" . $modulelink . "&domain=raamanagement\">" . $LANG["raamanagementtitle"] . "</a></li>\r\n\t\t\t\t<li><a style=\"white-space:nowrap;font-size:12px;text-decoration:none;\" href=\"" . $modulelink . "&domain=moveservices\">" . $LANG["movetitle"] . "</a></li>\r\n\t\t\t\t<li><a style=\"white-space:nowrap;font-size:12px;text-decoration:none;\" href=\"" . $modulelink . "&domain=bulkmove\">" . $LANG["bulkmovesidebarlink"] . "</a></li>\r\n\t\t\t\t<li><a style=\"white-space:nowrap;font-size:12px;text-decoration:none;\" href=\"" . $modulelink . "&trans=showfunds\">" . $LANG["resellerclubbalance"] . "</a></li>\r\n\t\t\t\t<li><a style=\"white-space:nowrap;font-size:12px;text-decoration:none;\" href=\"" . $modulelink . "&domain=transfercheck\">" . $LANG["transferchecktitle"] . "</a></li>\r\n\t\t\t\t<li><a style=\"white-space:nowrap;font-size:12px;text-decoration:none;\" href=\"" . $modulelink . "&domain=rcmwhois\">" . $LANG["whoisaddonlinktitle"] . "</a></li>";
    }
    if (version_compare(getWver(), "7.10.9") <= 0) {
        $sidebar .= "</ul>\r\n\t\t\t\t\t<span class=\"plain_header\">" . $LANG["toolsinfotitle"] . "</span>\r\n\t\t\t\t\t<div class=\"smallfont\">" . $versioninfo . "</div><br />\r\n\t\t\t\t   ";
    } else {
        $sidebar .= "\r\n\t\t\t\t\t<div class=\"content-padded small\">\r\n\t\t\t\t\t<p>" . $versioninfo . "</p></div>\r\n\t\t\t\t\t</ul></li>\r\n\t\t\t\t\t";
    }
    return $sidebar;
}
function resellerclubmods_tools_config()
{
    if (isset($_REQUEST["doaccactivate"]) && $_REQUEST["doaccactivate"] == "2") {
        $values = ["module" => "resellerclubmods_tools", "setting" => "second_acc_name", "value" => "Second Account"];
        $result = Illuminate\Database\Capsule\Manager::table("tbladdonmodules")->insert($values);
    }
    if (isset($_REQUEST["doaccactivate"]) && $_REQUEST["doaccactivate"] == "3") {
        $values = ["module" => "resellerclubmods_tools", "setting" => "third_acc_name", "value" => "Third Account"];
        $result = Illuminate\Database\Capsule\Manager::table("tbladdonmodules")->insert($values);
    }
    if (isset($_REQUEST["doaccactivate"]) && $_REQUEST["doaccactivate"] == "4") {
        $values = ["module" => "resellerclubmods_tools", "setting" => "fourth_acc_name", "value" => "Fourth Account"];
        $result = Illuminate\Database\Capsule\Manager::table("tbladdonmodules")->insert($values);
    }
    if (isset($_REQUEST["doaccdeactivate"]) && $_REQUEST["doaccdeactivate"] == "2") {
        $result = Illuminate\Database\Capsule\Manager::table("tbladdonmodules")->where("module", "=", "resellerclubmods_tools")->where("setting", "LIKE", "second_%")->delete();
    }
    if (isset($_REQUEST["doaccdeactivate"]) && $_REQUEST["doaccdeactivate"] == "3") {
        $result = Illuminate\Database\Capsule\Manager::table("tbladdonmodules")->where("module", "=", "resellerclubmods_tools")->where("setting", "LIKE", "third_%")->delete();
    }
    if (isset($_REQUEST["doaccdeactivate"]) && $_REQUEST["doaccdeactivate"] == "4") {
        $result = Illuminate\Database\Capsule\Manager::table("tbladdonmodules")->where("module", "=", "resellerclubmods_tools")->where("setting", "LIKE", "fourth_%")->delete();
    }
    try {
        $vars = [];
        foreach (Illuminate\Database\Capsule\Manager::table("tbladdonmodules")->where("module", "resellerclubmods_tools")->get() as $addonvars) {
            $vars[$addonvars->setting] = $addonvars->value;
        }
    } catch (Exception $e) {
        echo "<div class=\"alert alert-danger\">" . $e->getMessage() . "</div>";
    }
    $conf_rchttp_api = $vars["rchttp_api"];
    $first_rcauth_userid = $vars["first_rcauth_userid"];
    $first_rcauth_password = $vars["first_rcauth_apikey"];
    $first_logicbox_registrar = $vars["first_domainregistrar"];
    $first_defaultcurrency = $vars["first_defaultcurrency"];
    $first_currencyswitch = $vars["first_currencyswitch"];
    $first_multiplicator = $vars["first_multiplicator"];
    $first_account_name = $vars["first_acc_name"];
    $first_account_number = 1;
    $default_promostyle = $vars["default_promostyle"];
    $default_sellingprice = $vars["default_sellingprice"];
    $showdefault_price = $vars["showdefault_price"];
    if ($showdefault_price == "Yes") {
        $showdefaultprice = "7.50";
    }
    if (!empty($conf_rchttp_api) && !empty($first_rcauth_userid) && !empty($first_rcauth_password) && !empty($first_account_name)) {
        $result = Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodstools")->select("account_number")->get();
        $tools_account_number = $result[0]->account_number;
        if (empty($tools_account_number)) {
            $values = ["account_name" => $first_account_name, "account_number" => $first_account_number, "rcauth_userid" => $first_rcauth_userid, "rcauth_password" => $first_rcauth_password, "rchttp_api" => $conf_rchttp_api, "logicbox_registrar" => $first_logicbox_registrar, "defaultcurrency" => $first_defaultcurrency, "currencyswitch" => $first_currencyswitch, "multiplicator" => $first_multiplicator];
            $result = Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodstools")->insert($values);
        }
    }
    try {
        $tblcurrencies = [];
        foreach (Illuminate\Database\Capsule\Manager::table("tblcurrencies")->get() as $currencies) {
            $tblcurrencies[] = $currencies->code;
        }
        $currency_comma_separated = implode(",", $tblcurrencies);
    } catch (Exception $e) {
        echo "<div class=\"alert alert-danger\">" . $e->getMessage() . "</div>";
    }
    try {
        $tblmailtpls = [];
        foreach (Illuminate\Database\Capsule\Manager::table("tblemailtemplates")->where("type", "domain")->select("language", "name")->get() as $emailtbls) {
            if (empty($emailtbls->language)) {
                $tblmailtpls[] = $emailtbls->name;
            }
        }
    } catch (Exception $e) {
        echo "<div class=\"alert alert-danger\">" . $e->getMessage() . "</div>";
    }
    $tblmailtpls_comma_separated = implode(",", $tblmailtpls);
    $tbldomtemplates = "None," . $tblmailtpls_comma_separated;
    try {
        $tbladmins = [];
        foreach (Illuminate\Database\Capsule\Manager::table("tbladmins")->get() as $admin) {
            $tbladmins[] = $admin->username;
        }
    } catch (Exception $e) {
        echo "<div class=\"alert alert-danger\">" . $e->getMessage() . "</div>";
    }
    $tbladmins_comma_separated = implode(",", $tbladmins);
    try {
        $tblregistrars = [];
        foreach (Illuminate\Database\Capsule\Manager::table("tblregistrars")->get() as $registrars) {
            $tblregistrars[] = $registrars->registrar;
        }
    } catch (Exception $e) {
        echo "<div class=\"alert alert-danger\">" . $e->getMessage() . "</div>";
    }
    $tblregistrars = array_unique($tblregistrars);
    $tblregistrars = array_diff($tblregistrars, ["resellerclub", "resellerclubrcm", "netearthone", "netearthonercm", "stargate", "stargatercm", "resellercamp", "resellercamprcm"]);
    $lbregistrars = implode(",", $tblregistrars);
    if (!empty($lbregistrars)) {
        $lbdropdown = "Select Registrar,resellerclub,netearthone,stargate,resellercamp," . $lbregistrars;
    } else {
        $lbdropdown = "Select Registrar,resellerclub,netearthone,stargate,resellercamp";
    }
    $doconfirm_second = "<script language=\"javascript\" type=\"text/javascript\">function secondDelete(){return confirm(\"You are about to delete the Account \\\"" . $vars["second_acc_name"] . "\\\". Are you sure?\");}</script>";
    $doconfirm_third = "<script language=\"javascript\" type=\"text/javascript\">function thirdDelete(){return confirm(\"You are about to delete the Account \\\"" . $vars["third_acc_name"] . "\\\". Are you sure?\");}</script>";
    $doconfirm_fourth = "<script language=\"javascript\" type=\"text/javascript\">function fourthDelete(){return confirm(\"You are about to delete the Account \\\"" . $vars["fourth_acc_name"] . "\\\". Are you sure?\");}</script>";
    if (!empty($vars["first_acc_name"])) {
        if (empty($vars["second_acc_name"])) {
            $doActivate_second = "<br /><div class=\"alert alert-success text-center\">To enable a &quot;<strong>Second Account</strong>&quot;, click <a href =\"configaddonmods.php?doaccactivate=2#resellerclubmods_tools\">here</a></div>";
        } else {
            $doActivate_second = $doconfirm_second . "<br /><div class=\"alert alert-danger text-center\"><a id=\"act02\"></a>To disable the &quot;2# Reseller Account&quot; (<strong>" . $vars["second_acc_name"] . "</strong>), click <a href =\"configaddonmods.php?doaccdeactivate=2#resellerclubmods_tools\" onclick=\"return secondDelete();\">here</a></div>";
            $alink_second = "&nbsp; <a class=\"scroll label label-primary\" href=\"#act02\">2# Account</a>";
        }
        if (empty($vars["third_acc_name"])) {
            $doActivate_third = "<br /><div class=\"alert alert-success text-center\">To enable a &quot;<strong>Third Account</strong>&quot;, click <a href =\"configaddonmods.php?doaccactivate=3#resellerclubmods_tools\">here</a></div>";
        } else {
            $doActivate_third = $doconfirm_third . "<br /><div class=\"alert alert-danger text-center\"><a id=\"act03\"></a>To disable the &quot;3# Reseller Account&quot; (<strong>" . $vars["third_acc_name"] . "</strong>), click <a href =\"configaddonmods.php?doaccdeactivate=3#resellerclubmods_tools\" onclick=\"return thirdDelete();\">here</a></div>";
            $alink_third = "&nbsp; <a class=\"scroll label label-primary\" href=\"#act03\">3# Account</a>";
        }
        if (empty($vars["fourth_acc_name"])) {
            $doActivate_fourth = "<br /><div class=\"alert alert-success text-center\">To enable a &quot;<strong>Fourth Account</strong>&quot;, click <a href =\"configaddonmods.php?doaccactivate=4#resellerclubmods_tools\">here</a></div>";
        } else {
            $doActivate_fourth = $doconfirm_fourth . "<br /><div class=\"alert alert-danger text-center\"><a id=\"act04\"></a>To disable the &quot;4# Reseller Account&quot; (<strong>" . $vars["fourth_acc_name"] . "</strong>), click <a href =\"configaddonmods.php?doaccdeactivate=4#resellerclubmods_tools\" onclick=\"return fourthDelete();\">here</a></div>";
            $alink_fourth = "&nbsp; <a class=\"scroll label label-primary\" href=\"#act04\">4# Account</a>";
        }
    }
    $scroller = "<script type=\"text/javascript\">\r\n\tjQuery(document).ready(function(){\r\n\t  jQuery(\".scroll\").click(function(event){\r\n\t\tevent.preventDefault();\r\n\t\tvar offset = jQuery(jQuery(this).attr('href')).offset().top;\r\n\t\tjQuery('html, body').animate({scrollTop:offset}, 1000);\r\n\t  });\r\n\t});\r\n\t</script>";
    $base_fields = ["sectionlinks" => ["FriendlyName" => "Goto Section", "Description" => $scroller . "<div style=\"line-height:20px;\"><a class=\"scroll label label-primary\" href=\"#act01\">1# Account</a>" . $alink_second . $alink_third . $alink_fourth . "&nbsp;<a class=\"scroll label label-primary\" href=\"#fdw\">Funds Widget Setup</a>&nbsp; <a class=\"scroll label label-primary\" href=\"#dth\">Domain Transfer Handling</a>&nbsp; <a class=\"scroll label label-primary\" href=\"#cat\">Client Area Tools</a>&nbsp; <a class=\"scroll label label-primary\" href=\"#rcdomainpromos\">Domain Promos</a>&nbsp; <a class=\"scroll label label-primary\" href=\"#dpp\">Domain Promo Pricelist</a>&nbsp; <a class=\"scroll label label-primary\" href=\"#whosrvsetup\">API Whois Server Setup</a>&nbsp; <a class=\"scroll label label-primary\" href=\"#rctldprices\">Domain TLDs & Prices</a>&nbsp; <a class=\"scroll label label-primary\" href=\"#shopint\">OrderBox -  Cart Integration</a>&nbsp; <a class=\"scroll label label-primary\" href=\"#miscsettings\">Miscellaneous</a></div>"], "textseparator1" => ["FriendlyName" => " ", "Description" => "<br /><div class=\"alert alert-info text-center\"><h1>Reseller Accounts Setup</h1></div>"], "rchttp_api" => ["FriendlyName" => "Reseller API <span style=\"color:#cc0000\">*</span>", "Type" => "text", "Size" => "30", "Default" => "https://httpapi.com", "Description" => "API Url: https://httpapi.com</strong>"]];
    $first_account_fields = ["first_acc" => ["FriendlyName" => "<a class=\"scroll\" style=\"text-decoration:none;\" href=\"#top\"><i class=\"fa fa-chevron-circle-up fa-2 fa-2x\" aria-hidden=\"true\"></i></a>", "Description" => "<a id=\"act01\"></a><h2><strong>1# Reseller Account</strong></h2>"], "first_acc_name" => ["FriendlyName" => "Display Name <span style=\"color:#cc0000\">*</span>", "Type" => "text", "Size" => "30", "Description" => "Set a Friendly Display Name"], "first_domainregistrar" => ["FriendlyName" => "Select your Registrar <span style=\"color:#cc0000\">*</span>", "Type" => "dropdown", "Options" => $lbdropdown, "Description" => "Select only a valid LogicBoxes Registrar!"], "first_rcauth_userid" => ["FriendlyName" => "Reseller ID <span style=\"color:#cc0000\">*</span>", "Type" => "text", "Size" => "8", "Description" => "Set your <strong>Reseller ID</strong> (Not the Parent ID nor the Reseller Username!)"], "first_rcauth_apikey" => ["FriendlyName" => "API Key <span style=\"color:#cc0000\">*</span>", "Type" => "text", "Size" => "50", "Description" => "Set your unique Reseller API Key"], "first_show_fundsbalance" => ["FriendlyName" => "Disable Fundsbalance on Widget", "Type" => "yesno", "Description" => "Tick to Disable Fundsbalance Output on the widget"], "first_ns_override" => ["FriendlyName" => "Default Nameservers", "Type" => "text", "Size" => "80", "Description" => "<br />You can specify here upto 5 Default Nameservers, comma separated, to be used for domain register and transfer for the selected Registrar. Leave empty to use the WHMCS Default Nameserver settings.<br /><strong>Example</strong>: ns1.domain.com,ns2.domain.com,ns3.domain.com,ns4.domain.com"], "first_transer_check" => ["FriendlyName" => "Disable Transfer Check Cron", "Type" => "yesno", "Description" => "Tick to Disable Transfer Check Cron on this account"], "first_hook_signup" => ["FriendlyName" => "Disable Auto Customer Signup", "Type" => "yesno", "Description" => "Tick to Disable Customer sign up in your reseller account when register a new customer in WHMCS"], "first_pwd_signup" => ["FriendlyName" => "Use WHMCS Password with Signup", "Type" => "yesno", "Description" => "Tick to use the WHMCS customer password instead of a randomly generated password with the &quot;Auto Customer Signup&quot; (Only valid for WHMCS versions below 8.0.0)"], "first_hook_modify" => ["FriendlyName" => "Disable Auto Customer Modify", "Type" => "yesno", "Description" => "Tick to Disable Customer modification in your reseller account when modify a customer in WHMCS"], "first_hook_modify_useremail" => ["FriendlyName" => "Update User account email", "Type" => "yesno", "Description" => "If enabled, the user account email address will be updated with the customer's email address when the customer's email address changes. Requires the Auto Customer Modify option to be enabled"], "first_pwd_modify" => ["FriendlyName" => "Apply WHMCS Password if changed", "Type" => "yesno", "Description" => "Tick to sync the password in the reseller customer account when the password in WHMCS got changed (Only valid for WHMCS versions below 8.0.0)"], "first_hook_delete" => ["FriendlyName" => "Disable Auto Customer Delete", "Type" => "yesno", "Description" => "Tick to Disable Customer deletion in your reseller account when a customer has been deleted in WHMCS"], "first_threshold" => ["FriendlyName" => "Disable Funds Threshold Check", "Type" => "yesno", "Description" => "Tick to Disable the hook for funds threshold mail alert"], "first_threshold_value" => ["FriendlyName" => "Override Reseller Funds Threshold", "Type" => "text", "Size" => "6", "Description" => "You can override the Threshold value from your reseller account settings. Leave blank to use your reseller settings"], "subseparator101" => ["FriendlyName" => "<span style=\"color:#cc0000;font-weight:bold;\">Domain Price Sync</span>"], "first_domainsync_check" => ["FriendlyName" => "Disable TLD Selling Price Sync Cron", "Type" => "yesno", "Description" => "If the Domain Price Sync Cron has been setup, then you can disable the cronjob here for this account. For further details, see &quot;Automation Tools&quot; from the left sidebar menu in WHMCS &raquo; Addons &raquo; RC & LB Tools v2"], "first_domainsync_telescope" => ["FriendlyName" => "Disable TLD Telescope Pricing", "Type" => "yesno", "Description" => "Tick to disable Telescope Pricing for the Sync Cron on this account"], "first_domainsync_redemption" => ["FriendlyName" => "Update Redemption Period and Fee", "Type" => "yesno", "Description" => "Tick to Import/Update Redemption period and fee for those TLDs who support Domain restoration. If activated, period and fees will be imported/updated each time the domain selling price sync cron runs."], "first_only_baseslab" => ["FriendlyName" => "Sync Clientgroup TLD Price Slabs", "Type" => "yesno", "Description" => "By default, only the Default Base Slab Pricing will be synced with the selling prices from your Reseller Account.<br /><span class=\"label label-danger\"><strong>Important!</strong></span> <span>If you activate this option, then all Client based Price Slabs will be updated with the <strong>default selling prices</strong></span>"], "first_domainsync_tlds" => ["FriendlyName" => "Sync Only these TLD's", "Type" => "text", "Size" => "40", "Description" => "The TLD's specified here will be synced and all others will be ignored completely!<br /><strong>Example:</strong> .us,.com,.co.uk,.net"], "first_exclude_domainsync_tlds" => ["FriendlyName" => "Exclude these TLD's", "Type" => "text", "Size" => "40", "Description" => "You can set the TLD's you would like to exclude from the Price sync<br /><strong>Example:</strong> .us,.com,.co.uk,.net"], "subseparator102" => ["FriendlyName" => "<span style=\"color:#cc0000;font-weight:bold;\">Currency Setup</span>"], "first_defaultcurrency" => ["FriendlyName" => "Reseller Selling Currencies", "Type" => "dropdown", "Options" => $currency_comma_separated, "Description" => "&nbsp;Select your <strong>Reseller Selling Currency</strong>! If the Currency is missing, then you need to add the Currency in your WHMCS. For eg. you will need to do this if your Selling Currency in your Reseller Account is USD but in WHMCS Default is set to EUR!"], "first_multiplicator" => ["FriendlyName" => "Default multiplicator", "Type" => "dropdown", "Options" => "1,10,100,1000", "Description" => "Change only if your Reseller Selling Currency is calculated in 10's, 100's or 1000's"], "first_currencyswitch" => ["FriendlyName" => "Activate Currency Conversion", "Type" => "yesno", "Description" => "<span class=\"label label-danger\"><strong>Important!</strong></span> <span>Tick this checkbox only if the Reseller Selling Currency is not equal to the WHMCS Default Currency.</span>"]];
    if ($vars["first_acc_name"]) {
        $first_activate = ["first_activate_second" => ["FriendlyName" => "", "Description" => $doActivate_second]];
    } else {
        $first_activate = [];
    }
    if ($vars["second_acc_name"]) {
        $second_account_fields = ["second_acc" => ["FriendlyName" => "<a class=\"scroll\" style=\"text-decoration:none;\" href=\"#top\"><i class=\"fa fa-chevron-circle-up fa-2 fa-2x\" aria-hidden=\"true\"></i></a>", "Description" => "<a id=\"act02\"></a><h2><strong>2# Reseller Account</strong></h2>"], "second_acc_name" => ["FriendlyName" => "Display Name", "Type" => "text", "Size" => "30", "Description" => "Set a Friendly Display Name"], "second_domainregistrar" => ["FriendlyName" => "Select your Registrar", "Type" => "dropdown", "Options" => $lbdropdown, "Description" => "Select only a valid LogicBoxes Registrar!"], "second_rcauth_userid" => ["FriendlyName" => "Reseller ID", "Type" => "text", "Size" => "8", "Description" => "Set your <strong>Reseller ID</strong> (Not the Parent ID nor the Reseller Username!)"], "second_rcauth_apikey" => ["FriendlyName" => "API Key", "Type" => "text", "Size" => "50", "Description" => "Set your unique Reseller API Key"], "second_show_fundsbalance" => ["FriendlyName" => "Disable Fundsbalance on Widget", "Type" => "yesno", "Description" => "Tick to Disable Fundsbalance Output on the widget"], "second_ns_override" => ["FriendlyName" => "Default Nameservers", "Type" => "text", "Size" => "80", "Description" => "<br />You can specify here upto 5 Default Nameservers, comma separated, to be used for domain register and transfer for the selected Registrar. Leave empty to use the WHMCS Default Nameserver settings.<br /><strong>Example:</strong> ns1.domain.com,ns2.domain.com,ns3.domain.com,ns4.domain.com"], "second_transer_check" => ["FriendlyName" => "Disable Transfer Check Cron", "Type" => "yesno", "Description" => "Tick to Disable Transfer Check Cron on this account"], "second_hook_signup" => ["FriendlyName" => "Disable Auto Customer Signup", "Type" => "yesno", "Description" => "Tick to Disable Customer sign up in your reseller account when register a new customer in WHMCS"], "second_pwd_signup" => ["FriendlyName" => "Use WHMCS Password with Signup", "Type" => "yesno", "Description" => "Tick to use the WHMCS customer password instead of a randomly generated password with the &quot;Auto Customer Signup&quot; (Only valid for WHMCS versions below 8.0.0)"], "second_hook_modify" => ["FriendlyName" => "Disable Auto Customer Modify", "Type" => "yesno", "Description" => "Tick to Disable Customer modification in your reseller account when modify a customer in WHMCS"], "second_pwd_modify" => ["FriendlyName" => "Apply WHMCS Password if changed", "Type" => "yesno", "Description" => "Tick to sync the password in the reseller customer account when the password in WHMCS got changed (Only valid for WHMCS versions below 8.0.0)"], "second_hook_delete" => ["FriendlyName" => "Disable Auto Customer Delete", "Type" => "yesno", "Description" => "Tick to Disable Customer deletion in your reseller account when a customer has been deleted in WHMCS"], "second_threshold" => ["FriendlyName" => "Disable Funds Threshold Check", "Type" => "yesno", "Description" => "Tick to Disable the hook for funds threshold mail alert"], "second_threshold_value" => ["FriendlyName" => "Override Reseller Funds Threshold", "Type" => "text", "Size" => "6", "Description" => "You can override the Threshold value from your reseller account settings. Leave blank to use your reseller settings"], "subseparator201" => ["FriendlyName" => "<span style=\"color:#cc0000;font-weight:bold;\">Domain Price Sync</span>"], "second_domainsync_check" => ["FriendlyName" => "Disable Domain TLD Price Sync", "Type" => "yesno", "Description" => "Tick to disable Domain Price Sync Cron on this account"], "second_domainsync_telescope" => ["FriendlyName" => "Disable TLD Telescope Pricing", "Type" => "yesno", "Description" => "Tick to disable Telescope Pricing for the Sync Cron on this account"], "second_domainsync_redemption" => ["FriendlyName" => "Update Redemption Period and Fee", "Type" => "yesno", "Description" => "Tick to Import/Update Redemption period and fee for those TLDs who support Domain restoration. If activated, period and fees will be imported/updated each time the domain selling price sync cron runs."], "second_only_baseslab" => ["FriendlyName" => "Sync Clientgroup TLD Price Slabs", "Type" => "yesno", "Description" => "By default, only the Default Base Slab Pricing will be synced with the selling prices from your Reseller Account.<br /><span class=\"label label-danger\"><strong>Important!</strong></span> <span>If you activate this option, then all Client based Price Slabs will be updated with the <strong>default selling prices</strong></span>"], "second_domainsync_tlds" => ["FriendlyName" => "Sync Only these TLD's", "Type" => "text", "Size" => "40", "Description" => "The TLD's specified here will be synced and all others will be ignored completely!<br /><strong>Example:</strong> .us,.com,.co.uk,.net"], "second_exclude_domainsync_tlds" => ["FriendlyName" => "Exclude these TLD's", "Type" => "text", "Size" => "40", "Description" => "You can set the TLD's you would like to exclude from the Price sync<br /><strong>Example:</strong> .us,.com,.co.uk,.net"], "subseparator202" => ["FriendlyName" => "<span style=\"color:#cc0000;font-weight:bold;\">Currency Setup</span>"], "second_defaultcurrency" => ["FriendlyName" => "Reseller Selling Currencies", "Type" => "dropdown", "Options" => $currency_comma_separated, "Description" => "&nbsp;Select your <strong>Reseller Selling Currency</strong>! If the Currency is missing, then you need to add the Currency in your WHMCS. For eg. you will need to do this if your Selling Currency in your Reseller Account is USD but in WHMCS Default is set to EUR!"], "second_multiplicator" => ["FriendlyName" => "Default multiplicator", "Type" => "dropdown", "Options" => "1,10,100,1000", "Description" => "Change only if your Reseller Selling Currency is calculated in 10's, 100's or 1000's"], "second_currencyswitch" => ["FriendlyName" => "Activate Currency Conversion", "Type" => "yesno", "Description" => "<span class=\"label label-danger\"><strong>Important!</strong></span> <span>Tick this checkbox only if the Reseller Selling Currency is not equal to the WHMCS Default Currency.</span>"]];
        $second_activate = ["second_activate_third" => ["FriendlyName" => "", "Description" => $doActivate_third]];
    } else {
        $second_account_fields = [];
        $second_activate = [];
    }
    if ($vars["third_acc_name"]) {
        $third_account_fields = ["third_acc" => ["FriendlyName" => "<a class=\"scroll\" style=\"text-decoration:none;\" href=\"#top\"><i class=\"fa fa-chevron-circle-up fa-2 fa-2x\" aria-hidden=\"true\"></i></a>", "Description" => "<a id=\"act03\"></a><h2><strong>3# Reseller Account</strong></h2>"], "third_acc_name" => ["FriendlyName" => "Display Name", "Type" => "text", "Size" => "30", "Description" => "Set a Friendly Display Name"], "third_domainregistrar" => ["FriendlyName" => "Select your Registrar", "Type" => "dropdown", "Options" => $lbdropdown, "Description" => "Select only a valid LogicBoxes Registrar!"], "third_rcauth_userid" => ["FriendlyName" => "Reseller ID", "Type" => "text", "Size" => "8", "Description" => "Set your <strong>Reseller ID</strong> (Not the Parent ID nor the Reseller Username!)"], "third_rcauth_apikey" => ["FriendlyName" => "API Key", "Type" => "text", "Size" => "50", "Description" => "Set your unique Reseller API Key"], "third_show_fundsbalance" => ["FriendlyName" => "Disable Fundsbalance on Widget", "Type" => "yesno", "Description" => "Tick to Disable Fundsbalance Output on the widget"], "third_ns_override" => ["FriendlyName" => "Default Nameservers", "Type" => "text", "Size" => "80", "Description" => "<br />You can specify here upto 5 Default Nameservers, comma separated, to be used for domain register and transfer for the selected Registrar. Leave empty to use the WHMCS Default Nameserver settings.<br /><strong>Example:</strong> ns1.domain.com,ns2.domain.com,ns3.domain.com,ns4.domain.com"], "third_transer_check" => ["FriendlyName" => "Disable Transfer Check Cron", "Type" => "yesno", "Description" => "Tick to Disable Transfer Check Cron on this account"], "third_hook_signup" => ["FriendlyName" => "Disable Auto Customer Signup", "Type" => "yesno", "Description" => "Tick to Disable Customer sign up in your reseller account when register a new customer in WHMCS"], "third_pwd_signup" => ["FriendlyName" => "Use WHMCS Password with Signup", "Type" => "yesno", "Description" => "Tick to use the WHMCS customer password instead of a randomly generated password with the &quot;Auto Customer Signup&quot; (Only valid for WHMCS versions below 8.0.0)"], "third_hook_modify" => ["FriendlyName" => "Disable Auto Customer Modify", "Type" => "yesno", "Description" => "Tick to Disable Customer modification in your reseller account when modify a customer in WHMCS"], "third_pwd_modify" => ["FriendlyName" => "Apply WHMCS Password if changed", "Type" => "yesno", "Description" => "Tick to sync the password in the reseller customer account when the password in WHMCS got changed (Only valid for WHMCS versions below 8.0.0)"], "third_hook_delete" => ["FriendlyName" => "Disable Auto Customer Delete", "Type" => "yesno", "Description" => "Tick to Disable Customer deletion in your reseller account when a customer has been deleted in WHMCS"], "third_threshold" => ["FriendlyName" => "Disable Funds Threshold Check", "Type" => "yesno", "Description" => "Tick to Disable the hook for funds threshold mail alert"], "third_threshold_value" => ["FriendlyName" => "Override Reseller Funds Threshold", "Type" => "text", "Size" => "6", "Description" => "You can override the Threshold value from your reseller account settings. Leave blank to use your reseller settings"], "subseparator301" => ["FriendlyName" => "<span style=\"color:#cc0000;font-weight:bold;\">Domain Price Sync</span>"], "third_domainsync_check" => ["FriendlyName" => "Disable Domain TLD Price Sync", "Type" => "yesno", "Description" => "Tick to disable Domain Price Sync Cron on this account"], "third_domainsync_telescope" => ["FriendlyName" => "Disable TLD Telescope Pricing", "Type" => "yesno", "Description" => "Tick to disable Telescope Pricing for the Sync Cron on this account"], "third_domainsync_redemption" => ["FriendlyName" => "Update Redemption Period and Fee", "Type" => "yesno", "Description" => "Tick to Import/Update Redemption period and fee for those TLDs who support Domain restoration. If activated, period and fees will be imported/updated each time the domain selling price sync cron runs."], "third_only_baseslab" => ["FriendlyName" => "Sync Clientgroup TLD Price Slabs", "Type" => "yesno", "Description" => "By default, only the Default Base Slab Pricing will be synced with the selling prices from your Reseller Account.<br /><span class=\"label label-danger\"><strong>Important!</strong></span> <span>If you activate this option, then all Client based Price Slabs will be updated with the <strong>default selling prices</strong></span>"], "third_domainsync_tlds" => ["FriendlyName" => "Sync Only these TLD's", "Type" => "text", "Size" => "40", "Description" => "The TLD's specified here will be synced and all others will be ignored completely!<br /><strong>Example:</strong> .us,.com,.co.uk,.net"], "third_exclude_domainsync_tlds" => ["FriendlyName" => "Exclude these TLD's", "Type" => "text", "Size" => "40", "Description" => "You can set the TLD's you would like to exclude from the Price sync<br /><strong>Example:</strong> .us,.com,.co.uk,.net"], "subseparator302" => ["FriendlyName" => "<span style=\"color:#cc0000;font-weight:bold;\">Currency Setup</span>"], "third_defaultcurrency" => ["FriendlyName" => "Reseller Selling Currencies", "Type" => "dropdown", "Options" => $currency_comma_separated, "Description" => "&nbsp;Select your <strong>Reseller Selling Currency</strong>! If the Currency is missing, then you need to add the Currency in your WHMCS. For eg. you will need to do this if your Selling Currency in your Reseller Account is USD but in WHMCS Default is set to EUR!"], "third_multiplicator" => ["FriendlyName" => "Default multiplicator", "Type" => "dropdown", "Options" => "1,10,100,1000", "Description" => "Change only if your Reseller Selling Currency is calculated in 10's, 100's or 1000's"], "third_currencyswitch" => ["FriendlyName" => "Activate Currency Conversion", "Type" => "yesno", "Description" => "<span class=\"label label-danger\"><strong>Important!</strong></span> <span>Tick this checkbox only if the Reseller Selling Currency is not equal to the WHMCS Default Currency.</span>"]];
        $third_activate = ["third_activate_fourth" => ["FriendlyName" => "", "Description" => $doActivate_fourth]];
    } else {
        $third_account_fields = [];
        $third_activate = [];
    }
    if ($vars["fourth_acc_name"]) {
        $fourth_account_fields = ["fourth_acc" => ["FriendlyName" => "<a class=\"scroll\" style=\"text-decoration:none;\" href=\"#top\"><i class=\"fa fa-chevron-circle-up fa-2 fa-2x\" aria-hidden=\"true\"></i></a>", "Description" => "<a id=\"act04\"></a><h2><strong>4# Reseller Account</strong></h2>"], "fourth_acc_name" => ["FriendlyName" => "Display Name", "Type" => "text", "Size" => "30", "Description" => "Set a Friendly Display Name"], "fourth_domainregistrar" => ["FriendlyName" => "Select your Registrar", "Type" => "dropdown", "Options" => $lbdropdown, "Description" => "Select only a valid LogicBoxes Registrar!"], "fourth_rcauth_userid" => ["FriendlyName" => "Reseller ID", "Type" => "text", "Size" => "8", "Description" => "Set your <strong>Reseller ID</strong> (Not the Parent ID nor the Reseller Username!)"], "fourth_rcauth_apikey" => ["FriendlyName" => "API Key", "Type" => "text", "Size" => "50", "Description" => "Set your unique Reseller API Key"], "fourth_show_fundsbalance" => ["FriendlyName" => "Disable Fundsbalance on Widget", "Type" => "yesno", "Description" => "Tick to Disable Fundsbalance Output on the widget"], "fourth_ns_override" => ["FriendlyName" => "Default Nameservers", "Type" => "text", "Size" => "80", "Description" => "<br />You can specify here upto 5 Default Nameservers, comma separated, to be used for domain register and transfer for the selected Registrar. Leave empty to use the WHMCS Default Nameserver settings.<br /><strong>Example:</strong> ns1.domain.com,ns2.domain.com,ns3.domain.com,ns4.domain.com"], "fourth_transer_check" => ["FriendlyName" => "Disable Transfer Check Cron", "Type" => "yesno", "Description" => "Tick to Disable Transfer Check Cron on this account"], "fourth_hook_signup" => ["FriendlyName" => "Disable Auto Customer Signup", "Type" => "yesno", "Description" => "Tick to Disable Customer sign up in your reseller account when register a new customer in WHMCS"], "fourth_pwd_signup" => ["FriendlyName" => "Use WHMCS Password with Signup", "Type" => "yesno", "Description" => "Tick to use the WHMCS customer password instead of a randomly generated password with the &quot;Auto Customer Signup&quot; (Only valid for WHMCS versions below 8.0.0)"], "fourth_hook_modify" => ["FriendlyName" => "Disable Auto Customer Modify", "Type" => "yesno", "Description" => "Tick to Disable Customer modification in your reseller account when modify a customer in WHMCS"], "fourth_pwd_modify" => ["FriendlyName" => "Apply WHMCS Password if changed", "Type" => "yesno", "Description" => "Tick to sync the password in the reseller customer account when the password in WHMCS got changed (Only valid for WHMCS versions below 8.0.0)"], "fourth_hook_delete" => ["FriendlyName" => "Disable Auto Customer Delete", "Type" => "yesno", "Description" => "Tick to Disable Customer deletion in your reseller account when a customer has been deleted in WHMCS"], "fourth_threshold" => ["FriendlyName" => "Disable Funds Threshold Check", "Type" => "yesno", "Description" => "Tick to Disable the hook for funds threshold mail alert"], "fourth_threshold_value" => ["FriendlyName" => "Override Reseller Funds Threshold", "Type" => "text", "Size" => "6", "Description" => "You can override the Threshold value from your reseller account settings. Leave blank to use your reseller settings"], "subseparator401" => ["FriendlyName" => "<span style=\"color:#cc0000;font-weight:bold;\">Domain Price Sync</span>"], "fourth_domainsync_check" => ["FriendlyName" => "Disable Domain TLD Price Sync", "Type" => "yesno", "Description" => "Tick to disable Domain Price Sync Cron on this account"], "fourth_domainsync_telescope" => ["FriendlyName" => "Disable TLD Telescope Pricing", "Type" => "yesno", "Description" => "Tick to disable Telescope Pricing for the Sync Cron on this account"], "fourth_domainsync_redemption" => ["FriendlyName" => "Update Redemption Period and Fee", "Type" => "yesno", "Description" => "Tick to Import/Update Redemption period and fee for those TLDs who support Domain restoration. If activated, period and fees will be imported/updated each time the domain selling price sync cron runs."], "fourth_only_baseslab" => ["FriendlyName" => "Sync Clientgroup TLD Price Slabs", "Type" => "yesno", "Description" => "By default, only the Default Base Slab Pricing will be synced with the selling prices from your Reseller Account. Tick to activate sync for all Domain Price Slabs"], "fourth_domainsync_tlds" => ["FriendlyName" => "Sync Only these TLD's", "Type" => "text", "Size" => "40", "Description" => "The TLD's specified here will be synced and all others will be ignored completely!<br /><strong>Example:</strong> .us,.com,.co.uk,.net"], "fourth_exclude_domainsync_tlds" => ["FriendlyName" => "Exclude these TLD's", "Type" => "text", "Size" => "40", "Description" => "You can set the TLD's you would like to exclude from the Price sync<br /><strong>Example:</strong> .us,.com,.co.uk,.net"], "subseparator402" => ["FriendlyName" => "<span style=\"color:#cc0000;font-weight:bold;\">Currency Setup</span>"], "fourth_defaultcurrency" => ["FriendlyName" => "Reseller Selling Currencies", "Type" => "dropdown", "Options" => $currency_comma_separated, "Description" => "&nbsp;Select your <strong>Reseller Selling Currency</strong>! If the Currency is missing, then you need to add the Currency in your WHMCS. For eg. you will need to do this if your Selling Currency in your Reseller Account is USD but in WHMCS Default is set to EUR!"], "fourth_multiplicator" => ["FriendlyName" => "Default multiplicator", "Type" => "dropdown", "Options" => "1,10,100,1000", "Description" => "Change only if your Reseller Selling Currency is calculated in 10's, 100's or 1000's"], "fourth_currencyswitch" => ["FriendlyName" => "Activate Currency Conversion", "Type" => "yesno", "Description" => "<span class=\"label label-danger\"><strong>Important!</strong></span> <span>Tick this checkbox only if the Reseller Selling Currency is not equal to the WHMCS Default Currency.</span>"]];
    } else {
        $fourth_account_fields = [];
    }
    $misc_fields = ["textseparator0" => ["FriendlyName" => "<a class=\"scroll\" style=\"text-decoration:none;\" href=\"#top\"><i class=\"fa fa-chevron-circle-up fa-2 fa-2x\" aria-hidden=\"true\"></i></a>", "Description" => "<a id=\"fdw\"></a><br /><div class=\"alert alert-info text-center\"><h1>Funds Widget Setup</h1></div>"], "fundsbalance" => ["FriendlyName" => "Funds Balance Widget", "Type" => "yesno", "Description" => "Tick to show the widget for your Resellerclub & LogicBoxes funds balance on the Admin Homepage"], "currencysymbol" => ["FriendlyName" => "Use Currency Symbol", "Type" => "yesno", "Description" => "Tick to show the currency symbol (&euro;) instead the currency code (EUR)"], "totalfundscolor" => ["FriendlyName" => "Font Color Total Funds", "Type" => "text", "Size" => "8", "Description" => "Set the font color for Total Funds, default = <font color='#000000'>#000000</font>"], "blockedfundscolor" => ["FriendlyName" => "Font Color Blocked Funds", "Type" => "text", "Size" => "8", "Description" => "Set the font color for blocked funds, default = <font color='#CC0000'>#CC0000</font>"], "availfundscolor" => ["FriendlyName" => "Font Color Available Funds", "Type" => "text", "Size" => "8", "Description" => "Set the font color for available funds, default = <font color='#779500'>#779500</font>"], "fontsize" => ["FriendlyName" => "Font Size", "Type" => "text", "Size" => "3", "Description" => "Set the font size for the ouput, default = 12"], "fontfamily" => ["FriendlyName" => "Font Family", "Type" => "text", "Size" => "80", "Description" => "<br />Set the font family for the ouput, default = Lucida Grande,Lucida Sans Unicode,Verdana,Arial,sans-serif"], "textseparator3" => ["FriendlyName" => "<a class=\"scroll\" style=\"text-decoration:none;\" href=\"#top\"><i class=\"fa fa-chevron-circle-up fa-2 fa-2x\" aria-hidden=\"true\"></i></a>", "Description" => "<a id=\"dth\"></a><br /><div class=\"alert alert-info text-center\"><h1>Domain Transfer Handling</h1></div>"], "brokentransfers" => ["FriendlyName" => "Broken Domain Transfers", "Type" => "radio", "Options" => "Pending Transfer,Cancelled,Delete", "Description" => "By default the Domain status is always &quot;Pending Transfer&quot; even if the Domain has been deleted from your Reseller Account. Set your preferred option on how to handle a non existent Domain Transfer in WHMCS.<br /><br /><strong>Pending Transfer</strong> = The domain status will not be changed even if the domain does not exist in your Reseller Account<br /><strong>Cancelled</strong> = The status for a Broken Domain Transfer will be set to Cancelled and the Do-To-Item List status to Incomplete<br /><strong>Delete</strong> = The Domain will be deleted in WHMCS and the Do-To-Item List status will be set to Incomplete<br />", "Default" => "Pending Transfer"], "raa_domain_check" => ["FriendlyName" => "RAA Domain Report", "Type" => "yesno", "Description" => "Tick to disable if you do not want receive the daily RAA Pending Domain Report"], "adminconfmail" => ["FriendlyName" => "Admin Mail Transfer Check Report", "Type" => "yesno", "Description" => "Tick to disable the Transfer Check Report Mail. If disabled, the admin will not receive the results via Email.<br /><span class=\"label label-primary\"><strong>Note:</strong></span> <span>The Results are available always in the ActivityLog regardless of this option</span>"], "sendconfmail" => ["FriendlyName" => "Send Customer Transfer Confirmation Email", "Type" => "yesno", "Description" => "Tick to send a confirmation Email to the customer when a Domain Transfer has been completed"], "templatename" => ["FriendlyName" => "Transfer Confirmation Email Template", "Type" => "dropdown", "Options" => $tbldomtemplates, "Default" => "Domain Transfer Complete", "Description" => "Select the Email Template to confirm Domain Transfer Completion"], "sendproactivemail" => ["FriendlyName" => "Pro Active Transfer Mails", "Type" => "yesno", "Description" => "Pro Active Transfer Mails will be sent to your customers using the below email templates if a domain transfer has been stalled or failed. The notification mails will be sent each 24 hours, independently on how many times the cronjob got executed. By activating the checkbox mails will be send each time the transfer check cronjob run. To setup a cronjob click the link <strong>Automation Tools</strong> from the RC & LB Tools v2 Addon &raquo; left Sidebar Menu"], "templatenameunlock" => ["FriendlyName" => "Transfer Unlock Email Template", "Type" => "dropdown", "Options" => $tbldomtemplates, "Default" => "Domain Unlock Request", "Description" => "Select the Email Template to request Unlock Domain Theft Protection"], "templatenameeppcode" => ["FriendlyName" => "Transfer EPP Code Email Template", "Type" => "dropdown", "Options" => $tbldomtemplates, "Default" => "Domain EPP Request", "Description" => "Select the Email Template to request Domain EPP code"], "templatenameidprotect" => ["FriendlyName" => "Transfer ID Protect Email Template", "Type" => "dropdown", "Options" => $tbldomtemplates, "Default" => "Domain ID Protection Request", "Description" => "Select the Email Template to request disable Domain ID Protection"], "templatenamefailed" => ["FriendlyName" => "Transfer Failed Email Template", "Type" => "dropdown", "Options" => $tbldomtemplates, "Default" => "Domain Transfer Failed", "Description" => "Select the Email Template to inform Domain Transfer Failed"], "textseparator4" => ["FriendlyName" => "<a class=\"scroll\" style=\"text-decoration:none;\" href=\"#top\"><i class=\"fa fa-chevron-circle-up fa-2 fa-2x\" aria-hidden=\"true\"></i></a>", "Description" => "<a id=\"cat\"></a><br /><div class=\"alert alert-info text-center\"><h1>Client Area Tools!</h1></div>"], "whmcs_theme" => ["FriendlyName" => "WHMCS Theme Style", "Type" => "dropdown", "Options" => "Twenty-One,Six,Nexus", "Description" => "If your Template Theme is WHMCS Six or based on the WHMCS Six theme, then select <strong>Six</strong>, otherwise select <strong>Twenty-One</strong> or <strong>Nexus</strong>. Feel free to modify the template files <strong>movedomain.tpl</strong> and <strong>suggestdomain.tpl</strong> located in /modules/addons/resellerclubmods_tools/ folder", "Default" => "Six"], "allow_movedomain" => ["FriendlyName" => "Move Domain Tool", "Type" => "yesno", "Description" => "Tick to activate the Move Domain / Services Tool on the Client Area Domain Details Page"], "movedomain_permission" => ["FriendlyName" => "Move Domain Permissions", "Type" => "radio", "Options" => "Only Admin,Client and Admin", "Description" => "Tick to allow &quot;Only Admin&quot; or &quot;Client and Admin&quot; to move their Domain and Services to another WHMCS Customer Account", "Default" => "Only Admin"], "domainbot_loggedin" => ["FriendlyName" => "Domainsbot require Login", "Type" => "yesno", "Description" => "By default the Domainsbot Tool does not need Login. Tick to activate Access only for logged in Clients"], "domainbot_extqty" => ["FriendlyName" => "Domainsbot tlds at once", "Type" => "text", "Size" => "6", "Description" => "Insert the number of TLDs to be checked. Do not set this too high. Default = 5", "Default" => 5], "domainbot_tlds" => ["FriendlyName" => "Domainsbot tlds", "Type" => "text", "Size" => "50", "Description" => "<br />Set the TLDs for the domainsbot tool separated by a comma. Make sure that these TLDs you specify here have been setup in your WHMCS! <strong>Example:</strong> com,net,org,info,biz,name,me,mobi,domains,webcam", "Default" => "com,net,org,info,biz,name,me,mobi,domains,webcam"], "domainbot_yrs" => ["FriendlyName" => "Register Period Dropdown", "Type" => "yesno", "Description" => "By default the Domainsbot Tool uses 1 year Register Period. Tick to activate the registration period drop down so that your clients can select the duration specifically"], "domainbot_nossl" => ["FriendlyName" => "Disable force SSL", "Type" => "yesno", "Description" => "By default the Domainsbot Tool page will be loaded with https if SSL support exists for your WHMCS. Tick to disable and use only http"], "textseparator8" => ["FriendlyName" => "<a class=\"scroll\" style=\"text-decoration:none;\" href=\"#top\"><i class=\"fa fa-chevron-circle-up fa-2 fa-2x\" aria-hidden=\"true\"></i></a>", "Description" => "<a id=\"rcdomainpromos\"></a><br /><div class=\"alert alert-info text-center\"><h1>Domain Promos</h1></div>"], "promo_auto_activate" => ["FriendlyName" => "Auto Activate Promo", "Type" => "yesno", "Description" => "Tick to automatically activate Domain Promos in WHMCS for all available active Promos in your Reseller Account.<br /><span class=\"label label-primary\"><strong>Note:</strong></span> <span>This applies always and only to the 1# Reseller Account</span>"], "promo_end_check" => ["FriendlyName" => "Auto Promo Update", "Type" => "yesno", "Description" => "Tick to activate the Auto Promo Pricing Update with the normal Selling Price when the Promo has been come to end.<br /><span class=\"label label-primary\"><strong>Note:</strong></span> <span>This applies always and only to the 1# Reseller Account</span>"], "promo_terminate_days" => ["FriendlyName" => "Terminate promo before end date", "Type" => "text", "Size" => "1", "Description" => "Set the number of days when a promo should be terminated before the real end date. Leave empty to disable"], "textseparator5" => ["FriendlyName" => "<a class=\"scroll\" style=\"text-decoration:none;\" href=\"#top\"><i class=\"fa fa-chevron-circle-up fa-2 fa-2x\" aria-hidden=\"true\"></i></a>", "Description" => "<a id=\"dpp\"></a><br /><div class=\"alert alert-info text-center\"><h1>Domain Promo Pricelist</h1>"], "whmcs_style" => ["FriendlyName" => "WHMCS Template Style", "Type" => "radio", "Options" => "Twenty-One,Six,Nexus,Custom", "Description" => "Select the WHMCS Style for the domain pricelist.<br /><br /><strong>Six</strong> = For WHMCS Six theme based template style<br /><strong>Twenty-One / Nexus</strong> = For WHMCS Twenty-One/Nexus theme based template style<br /><strong>Custom</strong> = For any other templates not based on the standard WHMCS template styles<br />", "Default" => "Six"], "whmcs_style_tblclass" => ["FriendlyName" => "Custom table CSS Class", "Type" => "text", "Size" => "50", "Description" => "CSS classes from your theme for the table tag"], "whmcs_style_trclass" => ["FriendlyName" => "Custom tr CSS Class", "Type" => "text", "Size" => "50", "Description" => "CSS classes from your theme for the tr tag"], "whmcs_style_thclass" => ["FriendlyName" => "Custom th CSS Class", "Type" => "text", "Size" => "50", "Description" => "CSS classes from your theme for the th tag"], "whmcs_style_tdclass" => ["FriendlyName" => "Custom td CSS Class", "Type" => "text", "Size" => "50", "Description" => "CSS classes from your theme for the td tag"], "showdefault_price" => ["FriendlyName" => "Show Default Selling Price", "Type" => "radio", "Options" => "Yes,No", "Description" => "Select &quot;Yes&quot; if the Default Selling Price should be displayed with the Promo Selling Price", "Default" => "No"], "default_sellingprice" => ["FriendlyName" => "Default Selling Price Style", "Type" => "text", "Size" => "80", "Description" => "<br />Insert the CSS style code for the Default Selling Price", "Default" => "text-decoration:line-through;"], "default_promostyle" => ["FriendlyName" => "Promo Selling Price Style", "Type" => "text", "Size" => "80", "Description" => "<br />Insert the CSS style code for the Promo Selling Price", "Default" => "background-color:#cc0000;color:#ffffff;text-align:center;padding:3px;"], "promo_price_output" => ["FriendlyName" => "Promo Price Style", "Description" => "<span style='" . $default_promostyle . "'>6.32 <span style='" . $default_sellingprice . "'>" . $showdefaultprice . "</span></span>"], "show_prega_label" => ["FriendlyName" => "Expose Pre GA Label", "Type" => "yesno", "Description" => "Tick to activate if you want to show Pre GA TLDs with a custom label"], "tld_prega_class" => ["FriendlyName" => "CSS Class for Label", "Type" => "text", "Size" => "50", "Description" => "Set a css class from your whmcs css file for the label", "Default" => "active label label-info"], "tld_prega_text" => ["FriendlyName" => "Text for Label", "Type" => "text", "Size" => "20", "Description" => "Set the text for the label", "Default" => "Pre GA"], "tld_prega_title" => ["FriendlyName" => "Title for Label", "Type" => "text", "Size" => "50", "Description" => "Set a title tag description for the label", "Default" => "Pre GA"], "show_restore_price" => ["FriendlyName" => "Expose Redemption Fees", "Type" => "yesno", "Description" => "Tick to activate if you want to show Domain Redemption Fees. Redemption/Restore prices will be taken via API from your Reseller Account."], "show_restore_local" => ["FriendlyName" => "Local Redemption Fees", "Type" => "yesno", "Description" => "Since WHMCS 7.5.0, Redemption Fees are now stored in WHMCS. If activated, Redemption/Restore prices are taken from the WHMCS Database and not from your Reseller Account<br /><span class=\"label label-danger\"><strong>Important!</strong></span> <span>Do not activate this option if your WHMCS is not greater than or equal to WHMCS 7.5.0</span>"], "textseparator7" => ["FriendlyName" => "<a class=\"scroll\" style=\"text-decoration:none;\" href=\"#top\"><i class=\"fa fa-chevron-circle-up fa-2 fa-2x\" aria-hidden=\"true\"></i></a>", "Description" => "<a id=\"whosrvsetup\"></a><br /><div class=\"alert alert-info text-center\"><h1>API Whois Server Setup</h1></div>"], "wid_key" => ["FriendlyName" => "Whois Lookup Secret", "Type" => "text", "Size" => "10", "Description" => "Set an alphanumeric key (use only 0-9 a-z A-Z and no special chars!) with an exact length of 10 to protect unauthorized use of whois lookups by others.<br /><span class=\"label label-danger\"><strong>IMPORTANT!</strong></span> <span>If you change the Secret key in some time later, then you need to re-generate the current whois lookup url for all TLDs which have been setup for domain lookup via api</span>", "Default" => "FB7koUY1aX"], "use_account_whois" => ["FriendlyName" => "Use Registrar for Domainlookup", "Type" => "yesno", "Description" => "If activated, domain availability lookup will use the reseller account for the registrar module defined in autoreg TLD settings, otherwise the first configured account registrar will be used<br /><span class=\"label label-danger\"><strong>Important!</strong></span> <span>Do not activate this option unless you have fully understood how it works! Please check TAB &quot;How it works&quot; from the <a href=\"https://www.resellerclub-mods.com/whmcs/resellerclub-tools-docs.php\" target=\"blank\">Installation Documentation</a></span>"], "textseparator9" => ["FriendlyName" => "<a class=\"scroll\" style=\"text-decoration:none;\" href=\"#top\"><i class=\"fa fa-chevron-circle-up fa-2 fa-2x\" aria-hidden=\"true\"></i></a>", "Description" => "<a id=\"rctldprices\"></a><br /><div class=\"alert alert-info text-center\"><h1>Domain TLD's & Prices</h1></div>"], "update_domain_recurring" => ["FriendlyName" => "Update Domain Recurring Prices", "Type" => "yesno", "Description" => "If activated, recurring prices for all domains will be updated with the daily cron using the current renew selling prices from the <strong>Pricing slab for default base slab</strong> or from the corrensponding <strong>Pricing slab for client groups</strong>. All Domains with recurring amount of 0.00, considered <strong>FREE Domains</strong> are excluded"], "domainsync_currencydoupd" => ["FriendlyName" => "Disable Domain Price Currency Update", "Type" => "yesno", "Description" => "Tick to disable Currency Update Domain selling prices. With the checkbox activated only the default currency selling prices will be updated. The option applies to <strong>Domain selling price sync cron</strong> (Automation Tools), <strong>Domain Promos</strong> (Manage Domain Promos), <strong>Auto Promo Update</strong> and <strong>Auto Activate Promo</strong> (Action Hooks)"], "transferfree_tlds" => ["FriendlyName" => "Transfer Free TLDs", "Type" => "text", "Size" => "70", "Description" => "<br />Transfer-ins for TLDs .com.au, .net.au, .co.uk, .me.uk, .org.uk, .com.ru, .net.ru, .org.ru, .ru and .es are always FREE of cost for Reseller and Customers and the transfer selling price for delcared TLDs will be set to 0.00. You can extend the list, comma separated. <strong>Example:</strong> co.uk,me.uk,org.uk,com.ru", "Default" => "com.au,net.au,co.uk,me.uk,org.uk,com.ru,net.ru,org.ru,ru,es"], "pagination_tlds" => ["FriendlyName" => "Import TLD & Prices Pagination", "Type" => "text", "Size" => "5", "Description" => "Set the number for TLD's to be displayed per page.", "Default" => "50"], "textseparator6" => ["FriendlyName" => "<a class=\"scroll\" style=\"text-decoration:none;\" href=\"#top\"><i class=\"fa fa-chevron-circle-up fa-2 fa-2x\" aria-hidden=\"true\"></i></a>", "Description" => "<a id=\"shopint\"></a><br /><div class=\"alert alert-info text-center\"><h1>OrderBox -  Cart Integration</h1></div>"], "ob_autoauth" => ["FriendlyName" => "Disable AutoAuth", "Type" => "yesno", "Description" => "Tick to Disable AutoAuth Customer login and only redirect to the destinations in WHMCS (AutoAuth was deprecated in WHMCS 7.10 and removed in WHMCS 8.1)"], "ob_home" => ["FriendlyName" => "Shopping Cart", "Type" => "text", "Size" => "30", "Description" => "Destination URL to the Shopping Cart", "Default" => "cart.php"], "ob_domain" => ["FriendlyName" => "Domain Register", "Type" => "text", "Size" => "30", "Description" => "Destination URL to Register a Domain", "Default" => "cart.php?a=add&domain=register"], "ob_bulk" => ["FriendlyName" => "Domain Bulkregister (WHMCS v6)", "Type" => "text", "Size" => "30", "Description" => "Destination URL to Bulkregister Domains", "Default" => "domainchecker.php?search=bulkregister"], "ob_domtransfer" => ["FriendlyName" => "Domain Transfer", "Type" => "text", "Size" => "30", "Description" => "Destination URL to Transfer a Domain", "Default" => "cart.php?a=add&domain=transfer"], "ob_bulktransfer" => ["FriendlyName" => "Domain Bulktransfer (WHMCS v6)", "Type" => "text", "Size" => "30", "Description" => "Destination URL to Bulktransfer Domains", "Default" => "domainchecker.php?search=bulktransfer"], "ob_digicert" => ["FriendlyName" => "SSL Certificate", "Type" => "text", "Size" => "30", "Description" => "Destination URL to Buy SSL Certs", "Default" => "cart.php"], "ob_sitebuilder" => ["FriendlyName" => "Sitebuilder", "Type" => "text", "Size" => "30", "Description" => "Destination URL to Buy Sitebuilder", "Default" => "cart.php"], "ob_email" => ["FriendlyName" => "Email Hosting", "Type" => "text", "Size" => "30", "Description" => "Destination URL to Buy Email Hosting", "Default" => "cart.php"], "ob_sitelock" => ["FriendlyName" => "Sitelock Protection", "Type" => "text", "Size" => "30", "Description" => "Destination URL to Buy Sitelock Website Protection", "Default" => "cart.php"], "ob_codeguard" => ["FriendlyName" => "CodeGuard Backup", "Type" => "text", "Size" => "30", "Description" => "Destination URL to Buy CodeGuard", "Default" => "cart.php"], "ob_comboplan" => ["FriendlyName" => "Combo Plans", "Type" => "text", "Size" => "30", "Description" => "Destination URL to Buy Compo Plans", "Default" => "cart.php"], "ob_impressly" => ["FriendlyName" => "Impress.ly", "Type" => "text", "Size" => "30", "Description" => "Destination URL to Buy Impressly Sitebuilder", "Default" => "cart.php"], "ob_singledomainhostinglinux" => ["FriendlyName" => "SDH Linux Hosting", "Type" => "text", "Size" => "30", "Description" => "Destination URL to Buy Single Domain Linux Hosting", "Default" => "cart.php"], "ob_singledomainhostingwindows" => ["FriendlyName" => "SDH Windows Hosting", "Type" => "text", "Size" => "30", "Description" => "Destination URL to Buy Single Domain Windows Hosting", "Default" => "cart.php"], "ob_multidomainhosting" => ["FriendlyName" => "MDH Linux Hosting", "Type" => "text", "Size" => "30", "Description" => "Destination URL to Buy Multi Domain Linux Hosting", "Default" => "cart.php"], "ob_multidomainwindowshosting" => ["FriendlyName" => "MDH Windows Hosting", "Type" => "text", "Size" => "30", "Description" => "Destination URL to Buy Multi Domain Windows Hosting", "Default" => "cart.php"], "ob_resellerhosting_group" => ["FriendlyName" => "Reseller Hosting", "Type" => "text", "Size" => "30", "Description" => "Destination URL to Buy Reseller Bulk Hosting (Linux/Windows Group)", "Default" => "cart.php"], "ob_resellerhosting" => ["FriendlyName" => "Reseller Linux Hosting", "Type" => "text", "Size" => "30", "Description" => "Destination URL to Buy Reseller Linux Bulk Hosting", "Default" => "cart.php"], "ob_resellerwindowshosting" => ["FriendlyName" => "Reseller Windows Hosting", "Type" => "text", "Size" => "30", "Description" => "Destination URL to Buy Reseller Windows Bulk Hosting", "Default" => "cart.php"], "ob_enterpriseemail" => ["FriendlyName" => "Enterprise Email Hosting", "Type" => "text", "Size" => "30", "Description" => "Destination URL to Buy Enterprise Email Hosting", "Default" => "cart.php"], "ob_businessemail" => ["FriendlyName" => "Business Email Hosting", "Type" => "text", "Size" => "30", "Description" => "Destination URL to Buy Business Email Hosting", "Default" => "cart.php"], "ob_dedicatedserver_group" => ["FriendlyName" => "Dedicated Servers", "Type" => "text", "Size" => "30", "Description" => "Destination URL to Buy Dedicated Servers", "Default" => "cart.php"], "ob_managedserver_group" => ["FriendlyName" => "Managed Servers", "Type" => "text", "Size" => "30", "Description" => "Destination URL to Buy Managed Servers", "Default" => "cart.php"], "ob_vps_group" => ["FriendlyName" => "VPS Servers", "Type" => "text", "Size" => "30", "Description" => "Destination URL to Buy VPS Servers", "Default" => "cart.php"], "ob_cloud_group" => ["FriendlyName" => "Cloud Hosting", "Type" => "text", "Size" => "30", "Description" => "Destination URL to Buy Cloud Hosting", "Default" => "cart.php"], "ob_gapps_group" => ["FriendlyName" => "Google Suite", "Type" => "text", "Size" => "30", "Description" => "Destination URL to Buy Google Suite", "Default" => "cart.php"], "ob_wordpresshosting_group" => ["FriendlyName" => "Wordpress Hosting", "Type" => "text", "Size" => "30", "Description" => "Destination URL to Buy Wordpress Hosting", "Default" => "cart.php"], "ob_weebly" => ["FriendlyName" => "Weebly Hosting", "Type" => "text", "Size" => "30", "Description" => "Destination URL to Buy Weebly Hosting", "Default" => "cart.php"], "textseparator2" => ["FriendlyName" => "<a class=\"scroll\" style=\"text-decoration:none;\" href=\"#top\"><i class=\"fa fa-chevron-circle-up fa-2 fa-2x\" aria-hidden=\"true\"></i></a>", "Description" => "<a id=\"miscsettings\"></a><br /><div class=\"alert alert-info text-center\"><h1>Miscellaneous</h1></div>"], "maileradmin" => ["FriendlyName" => "WHMCS Admin User", "Type" => "dropdown", "Options" => $tbladmins_comma_separated, "Description" => "Select an Admin User who has at least API access permission. The selected account must be an active admin account. You may create a new admin user with only API Access permission"], "userimport_value" => ["FriendlyName" => "Override max. value for User Import", "Type" => "text", "Size" => "3", "Description" => "You can set another max. value for the Import User Tool. Default value = 100", "Default" => 100], "userexport_value" => ["FriendlyName" => "Override max. value for User Export", "Type" => "text", "Size" => "3", "Description" => "You can set another max. value for the Export User Tool. Default value = 100", "Default" => 100]];
    $merged = array_merge($base_fields, $first_account_fields, $first_activate, $second_account_fields, $second_activate, $third_account_fields, $third_activate, $fourth_account_fields, $misc_fields);
    $tool_default_lang = "english";
    $is_adminlang = isset($_SESSION["adminlang"]) ? $_SESSION["adminlang"] : $tool_default_lang;
    $is_adminlang = strtolower($is_adminlang);
    if ($is_adminlang != $tool_default_lang && file_exists(ROOTDIR . "/modules/addons/resellerclubmods_tools/lang/" . $is_adminlang . ".php")) {
        $tool_default_lang = $is_adminlang;
    }
    $configarray = ["name" => "RC & LB Tools v2", "version" => "2.19.2", "author" => "<img src=\"../modules/addons/resellerclubmods_tools/img/rcmini.png\" alt=\"Resellerclub Mods RC & LB Tools v2\" title=\"Resellerclub Mods RC & LB Tools v2\"/>", "language" => $tool_default_lang, "description" => "<a id=\"top\"></a>This module shows your ResellerClub & LogicBoxes funds account balance on your admin home page and offers a set with other useful management tools for your Reseller account (MIT open source).", "fields" => $merged];
    return $configarray;
}
function resellerclubmods_tools_activate()
{
    $transfer_complete_message = "\r\n\t<p>Dear {\$client_name},</p>\r\n\t<p>The Domain transfer process for {\$domain_name} has been successfully completed. The details of the domain transfer are below:</p>\r\n\t<p>Domain: {\$domain_name}<br />\r\n\tRegistration Length: {\$domain_reg_period}<br />\r\n\tTransfer Price: {\$domain_first_payment_amount}<br />\r\n\tNext Due Date: {\$domain_next_due_date}</p>\r\n\t<p>You may login to your client area at {\$whmcs_url} to manage your domain.</p>\r\n\t<p>{\$signature}</p>\r\n\t";
    $transfer_unlock_message = "\r\n\t<p>Dear {\$client_name},</p>\r\n\t<p>Your domain {\$domain_name} is currently locked with your losing domain register. Before we can continue with your transfer this domain would need to be unlocked.</p>\r\n\t<p>If you need help getting the lock removed from your domain then you should contact your losing domain register. If you are still having trouble or need more help please feel free to contact us.</p>\r\n\t<p>{\$signature}</p>\r\n\t";
    $transfer_epp_message = "\r\n\t<p>Dear {\$client_name},</p>\r\n\t<p>The secret code for your domain {\$domain_name}, also known as authorization code or EPP code was not provided with the Domain Transfer approval or you have provided an invalid Transfer code. We have sent an approval mail requesting you to provide the EPP code.</p>\r\n\t<p>As soon as you have provided the EPP code, the domain transfer request will be send to your losing domain register.</p>\r\n\t<p>If you are having trouble with this step or if you have not received the mail to provide the EPP code please feel free to contact us.</p>\r\n\t<p>{\$signature}</p>\r\n\t";
    $transfer_idprotect_message = "\r\n\t<p>Dear {\$client_name},</p>\r\n\t<p>Your domain {\$domain_name} is currently WHOIS ID protected. Before we can continue with your transfer you need to disable the ID WHOIS Protection.</p>\r\n\t<p>If you need help getting the ID Protection removed from your domain then you should contact your losing domain register. If you are still having trouble or need more help please feel free to contact us.</p>\r\n\t<p>{\$signature}</p>\r\n\t";
    if (!Illuminate\Database\Capsule\Manager::schema()->hasTable("mod_resellerclubmodstools")) {
        try {
            Illuminate\Database\Capsule\Manager::schema()->create("mod_resellerclubmodstools", function ($mod_rclbt_1) {
                $mod_rclbt_1->increments("id");
                $mod_rclbt_1->text("account_name");
                $mod_rclbt_1->integer("account_number");
                $mod_rclbt_1->integer("rcauth_userid");
                $mod_rclbt_1->text("rcauth_password");
                $mod_rclbt_1->text("rchttp_api");
                $mod_rclbt_1->text("logicbox_registrar");
                $mod_rclbt_1->text("defaultcurrency");
                $mod_rclbt_1->text("currencyswitch");
                $mod_rclbt_1->text("multiplicator");
                $mod_rclbt_1->longText("localkey");
                $mod_rclbt_1->dateTime("lastcheck");
            });
        } catch (Exception $e) {
            echo (string) $e->getMessage();
        }
    }
    if (!Illuminate\Database\Capsule\Manager::schema()->hasTable("mod_resellerclubmodsfunds")) {
        try {
            Illuminate\Database\Capsule\Manager::schema()->create("mod_resellerclubmodsfunds", function ($mod_rclbt_2) {
                $mod_rclbt_2->increments("id");
                $mod_rclbt_2->integer("reseller_id");
                $mod_rclbt_2->decimal("fundavailable", 10, 2);
                $mod_rclbt_2->decimal("fundthreshold", 10, 2);
            });
        } catch (Exception $e) {
            echo (string) $e->getMessage();
        }
    }
    if (!Illuminate\Database\Capsule\Manager::schema()->hasTable("mod_resellerclubmodspromo")) {
        try {
            Illuminate\Database\Capsule\Manager::schema()->create("mod_resellerclubmodspromo", function ($mod_rclbt_3) {
                $mod_rclbt_3->increments("id");
                $mod_rclbt_3->text("registrar");
                $mod_rclbt_3->integer("resellerid");
                $mod_rclbt_3->longText("extension");
                $mod_rclbt_3->decimal("sellingprice", 10, 2);
                $mod_rclbt_3->decimal("promoprice", 10, 2);
                $mod_rclbt_3->integer("relid");
                $mod_rclbt_3->text("type");
                $mod_rclbt_3->dateTime("promoend");
            });
        } catch (Exception $e) {
            echo (string) $e->getMessage();
        }
    }
    if (!Illuminate\Database\Capsule\Manager::schema()->hasTable("mod_resellerclubmodsraa")) {
        try {
            Illuminate\Database\Capsule\Manager::schema()->create("mod_resellerclubmodsraa", function ($mod_rclbt_4) {
                $mod_rclbt_4->increments("id");
                $mod_rclbt_4->integer("rid");
                $mod_rclbt_4->text("registrar");
                $mod_rclbt_4->integer("domainid");
                $mod_rclbt_4->integer("userid");
                $mod_rclbt_4->text("domain");
                $mod_rclbt_4->dateTime("date");
                $mod_rclbt_4->integer("count");
            });
        } catch (Exception $e) {
            echo (string) $e->getMessage();
        }
    }
    if (!Illuminate\Database\Capsule\Manager::schema()->hasTable("mod_resellerclubmodstransfer")) {
        try {
            Illuminate\Database\Capsule\Manager::schema()->create("mod_resellerclubmodstransfer", function ($mod_resellerclubmodstransfer) {
                $mod_resellerclubmodstransfer->integer("domainid");
                $mod_resellerclubmodstransfer->integer("failed");
                $mod_resellerclubmodstransfer->integer("waitepp");
                $mod_resellerclubmodstransfer->integer("waitunlock");
                $mod_resellerclubmodstransfer->integer("waitidprotect");
                $mod_resellerclubmodstransfer->primary("domainid");
            });
        } catch (Exception $e) {
            echo (string) $e->getMessage();
        }
    }
    $check_data1 = Illuminate\Database\Capsule\Manager::table("tblemailtemplates")->where("type", "=", "domain")->where("name", "=", "Domain Transfer Complete")->select("name")->get();
    $emailtmpl_1 = $check_data1[0]->name;
    if (empty($emailtmpl_1)) {
        $values = ["type" => "domain", "name" => "Domain Transfer Complete", "subject" => "Domain Transfer successfully completed", "message" => $transfer_complete_message, "custom" => "1"];
        $result = Illuminate\Database\Capsule\Manager::table("tblemailtemplates")->insert($values);
    }
    $check_data2 = Illuminate\Database\Capsule\Manager::table("tblemailtemplates")->where("type", "=", "domain")->where("name", "=", "Domain Unlock Request")->select("name")->get();
    $emailtmpl_2 = $check_data2[0]->name;
    if (empty($emailtmpl_2)) {
        $values = ["type" => "domain", "name" => "Domain Unlock Request", "subject" => "Domain Transfer stalled due to theft protection", "message" => $transfer_unlock_message, "custom" => "1"];
        $result = Illuminate\Database\Capsule\Manager::table("tblemailtemplates")->insert($values);
    }
    $check_data3 = Illuminate\Database\Capsule\Manager::table("tblemailtemplates")->where("type", "=", "domain")->where("name", "=", "Domain EPP Request")->select("name")->get();
    $emailtmpl_3 = $check_data3[0]->name;
    if (empty($emailtmpl_3)) {
        $values = ["type" => "domain", "name" => "Domain EPP Request", "subject" => "Domain Transfer waiting EPP Code", "message" => $transfer_epp_message, "custom" => "1"];
        $result = Illuminate\Database\Capsule\Manager::table("tblemailtemplates")->insert($values);
    }
    $check_data4 = Illuminate\Database\Capsule\Manager::table("tblemailtemplates")->where("type", "=", "domain")->where("name", "=", "Domain ID Protection Request")->select("name")->get();
    $emailtmpl_4 = $check_data4[0]->name;
    if (empty($emailtmpl_4)) {
        $values = ["type" => "domain", "name" => "Domain ID Protection Request", "subject" => "Domain Transfer stalled due to ID Protection", "message" => $transfer_idprotect_message, "custom" => "1"];
        $result = Illuminate\Database\Capsule\Manager::table("tblemailtemplates")->insert($values);
    }
    return ["status" => "success", "description" => "ResellerClub & Logic Box Tools v2 successfully activated"];
}
function resellerclubmods_tools_deactivate()
{
    try {
        Illuminate\Database\Capsule\Manager::schema()->dropIfExists("mod_resellerclubmodstools");
        Illuminate\Database\Capsule\Manager::schema()->dropIfExists("mod_resellerclubmodshooks");
        Illuminate\Database\Capsule\Manager::schema()->dropIfExists("mod_resellerclubmodsfunds");
        Illuminate\Database\Capsule\Manager::schema()->dropIfExists("mod_resellerclubmodspromo");
        Illuminate\Database\Capsule\Manager::schema()->dropIfExists("mod_resellerclubmodsraa");
        Illuminate\Database\Capsule\Manager::schema()->dropIfExists("mod_resellerclubmodstransfer");
    } catch (Exception $e) {
        echo (string) $e->getMessage();
    }
    return ["status" => "success", "description" => "ResellerClub & Logic Box Tools v2 successfully deactivated"];
}
function resellerclubmods_tools_upgrade($vars)
{
    $version = $vars["version"];
    if ($version < "2.11.1" && !Illuminate\Database\Capsule\Manager::schema()->hasTable("mod_resellerclubmodstransfer")) {
        try {
            Illuminate\Database\Capsule\Manager::schema()->create("mod_resellerclubmodstransfer", function ($mod_resellerclubmodstransfer) {
                $mod_resellerclubmodstransfer->integer("domainid");
                $mod_resellerclubmodstransfer->integer("failed");
                $mod_resellerclubmodstransfer->integer("waitepp");
                $mod_resellerclubmodstransfer->integer("waitunlock");
                $mod_resellerclubmodstransfer->integer("waitidprotect");
                $mod_resellerclubmodstransfer->primary("domainid");
            });
        } catch (Exception $e) {
            echo (string) $e->getMessage();
        }
    }
}

?>