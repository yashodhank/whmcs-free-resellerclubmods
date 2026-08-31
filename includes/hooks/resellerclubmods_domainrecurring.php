<?php
if (!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
require ROOTDIR . "/modules/addons/resellerclubmods_tools/incs/functions.php";
add_hook("DailyCronJob", 1, "recurring_domain_update");
function recurring_domain_update($vars)
{
    $conf = [];
    foreach (Illuminate\Database\Capsule\Manager::table("tbladdonmodules")->where("module", "resellerclubmods_tools")->get() as $addonvars) {
        $conf[$addonvars->setting] = $addonvars->value;
    }
    $maileradmin = $conf["maileradmin"];
    $update_domain_recurring = $conf["update_domain_recurring"];
    if ($update_domain_recurring == "on") {
        $rcmdebuginfo = getDebuginfos();
        $modulename = $rcmdebuginfo["modulename"];
        $debug_addinfo = $rcmdebuginfo["debug_addinfo"];
        $result = Illuminate\Database\Capsule\Manager::table("tbladmins")->where("username", "=", $maileradmin)->select("language")->get();
        $adminlang = $result[0]->language;
        $get_langfile = getAdminlang($adminlang);
        require ROOTDIR . $get_langfile;
        $LANG = $_ADDONLANG;
        $whmcs_addons = [];
        foreach (Illuminate\Database\Capsule\Manager::table("tblpricing")->where("type", "domainaddons")->select("msetupfee", "qsetupfee", "ssetupfee", "currency")->get() as $dataaddons) {
            $whmcs_addons[$dataaddons->currency] = ["dnsmanagement" => $dataaddons->msetupfee, "emailforwarding" => $dataaddons->qsetupfee, "idprotection" => $dataaddons->ssetupfee];
        }
        $whmcs_domains = [];
        foreach (Illuminate\Database\Capsule\Manager::table("tbldomains")->where("recurringamount", "!=", "0.00")->join("tblclients", "tbldomains.userid", "=", "tblclients.id")->select("domain", "userid", "tblclients.currency", "tblclients.groupid", "registrationperiod", "dnsmanagement", "emailforwarding", "idprotection")->get() as $data) {
            $domain = $data->domain;
            $tld = strstr($domain, ".");
            $currency = $data->currency;
            $groupid = $data->groupid;
            $registrationperiod = $data->registrationperiod;
            $is_dnsmanagement = $data->dnsmanagement;
            $is_emailforwarding = $data->emailforwarding;
            $is_idprotection = $data->idprotection;
            $is_userid = $data->userid;
            if ($registrationperiod == 1) {
                $regperiod = "msetupfee";
            }
            if ($registrationperiod == 2) {
                $regperiod = "qsetupfee";
            }
            if ($registrationperiod == 3) {
                $regperiod = "ssetupfee";
            }
            if ($registrationperiod == 4) {
                $regperiod = "asetupfee";
            }
            if ($registrationperiod == 5) {
                $regperiod = "bsetupfee";
            }
            if ($registrationperiod == 6) {
                $regperiod = "monthly";
            }
            if ($registrationperiod == 7) {
                $regperiod = "quarterly";
            }
            if ($registrationperiod == 8) {
                $regperiod = "semiannually";
            }
            if ($registrationperiod == 9) {
                $regperiod = "annually";
            }
            if ($registrationperiod == 10) {
                $regperiod = "biennially";
            }
            if (!empty($is_dnsmanagement)) {
                $dnsmanagement = $whmcs_addons[$currency]["dnsmanagement"];
            } else {
                $dnsmanagement = "0.00";
            }
            if (!empty($is_emailforwarding)) {
                $emailforwarding = $whmcs_addons[$currency]["emailforwarding"];
            } else {
                $emailforwarding = "0.00";
            }
            if (!empty($is_idprotection)) {
                $idprotection = $whmcs_addons[$currency]["idprotection"];
            } else {
                $idprotection = "0.00";
            }
            $whmcs_domains[] = ["userid" => $is_userid, "tld" => $tld, "domain" => $domain, "currency" => $currency, "groupid" => $groupid, "regperiod" => $regperiod, "years" => $registrationperiod, "dnsmanagement" => $dnsmanagement, "emailforwarding" => $emailforwarding, "idprotection" => $idprotection];
        }
        foreach ($whmcs_domains as $values) {
            $data1 = Illuminate\Database\Capsule\Manager::table("tbldomainpricing")->where("extension", "=", $values["tld"])->select("id")->get();
            $tldid = $data1[0]->id;
            $regperiod = $values["regperiod"];
            $years = $values["years"];
            $currency = $values["currency"];
            $groupid = $values["groupid"] . ".00";
            $dnsprice = $values["dnsmanagement"];
            $mailprice = $values["emailforwarding"];
            $idprodprice = $values["idprotection"];
            $data2 = Illuminate\Database\Capsule\Manager::table("tblpricing")->where("relid", "=", $tldid)->where("type", "=", "domainrenew")->where("currency", "=", $currency)->where("tsetupfee", "=", $groupid)->select($regperiod)->get();
            $is_regperiod = $data2[0]->{$regperiod};
            if (!$is_regperiod) {
                $data2 = Illuminate\Database\Capsule\Manager::table("tblpricing")->where("relid", "=", $tldid)->where("type", "=", "domainrenew")->where("currency", "=", $currency)->where("tsetupfee", "=", "0.00")->select($regperiod)->get();
                $is_regperiod = $data2[0]->{$regperiod};
            }
            $dns = $dnsprice * $years;
            $mail = $mailprice * $years;
            $idprod = $idprodprice * $years;
            $recurringprice_array[$values["domain"]] = $is_regperiod + $dns + $mail + $idprod;
        }
        foreach ($recurringprice_array as $thedomain => $recurringamount) {
            if ("0.00" < $recurringamount) {
                $update = ["recurringamount" => $recurringamount];
                $result_update = Illuminate\Database\Capsule\Manager::table("tbldomains")->where("domain", "=", $thedomain)->update($update);
            }
        }
        logActivity("Cron Job (RCM): " . $LANG["recurringupdatesuccess"]);
        $action = "domain recurring update";
        $requeststring = "sqlqueries";
        $responsedata = ["rcmdebug" => $debug_addinfo, "sqlresults" => $recurringprice_array];
        logModuleCall($modulename, $action, $requeststring, $responsedata);
    } else {
        logActivity("Cron Job (RCM): Skipping Update Domain recurring prices");
    }
}

?>