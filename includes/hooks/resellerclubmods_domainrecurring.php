<?php
if (!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
require ROOTDIR . "/modules/addons/resellerclubmods_tools/incs/functions.php";
add_hook("DailyCronJob", 1, "recurring_domain_update");
function recurring_domain_update($vars)
{
    $conf = rcm_load_addon_conf();
    $maileradmin = $conf["maileradmin"] ?? "";
    $update_domain_recurring = $conf["update_domain_recurring"] ?? "";
    if ($update_domain_recurring == "on") {
        // NP-003: skip if another DailyCronJob process still holds the shared RCM daily lock.
        $rcm_daily_lock = rcm_try_lock("dailycron_hooks");
        if ($rcm_daily_lock === false) {
            logActivity("Cron Job (RCM): Skipping Update Domain recurring prices — daily hooks busy");
            return;
        }
        try {
        $rcmdebuginfo = getDebuginfos();
        $modulename = $rcmdebuginfo["modulename"];
        $debug_addinfo = $rcmdebuginfo["debug_addinfo"];
        $result = Illuminate\Database\Capsule\Manager::table("tbladmins")->where("username", "=", $maileradmin)->select("language")->get();
        $adminlang = isset($result[0]) ? $result[0]->language : "english";
        $get_langfile = getAdminlang($adminlang);
        require ROOTDIR . $get_langfile;
        $LANG = $_ADDONLANG;
        $whmcs_addons = [];
        foreach (Illuminate\Database\Capsule\Manager::table("tblpricing")->where("type", "domainaddons")->select("msetupfee", "qsetupfee", "ssetupfee", "currency")->get() as $dataaddons) {
            $whmcs_addons[$dataaddons->currency] = ["dnsmanagement" => $dataaddons->msetupfee, "emailforwarding" => $dataaddons->qsetupfee, "idprotection" => $dataaddons->ssetupfee];
        }
        $periodMap = [
            1 => "msetupfee", 2 => "qsetupfee", 3 => "ssetupfee", 4 => "asetupfee", 5 => "bsetupfee",
            6 => "monthly", 7 => "quarterly", 8 => "semiannually", 9 => "annually", 10 => "biennially",
        ];
        $whmcs_domains = [];
        $tldsNeeded = [];
        foreach (Illuminate\Database\Capsule\Manager::table("tbldomains")->where("recurringamount", "!=", "0.00")->join("tblclients", "tbldomains.userid", "=", "tblclients.id")->select("domain", "userid", "tblclients.currency", "tblclients.groupid", "registrationperiod", "dnsmanagement", "emailforwarding", "idprotection")->get() as $data) {
            $domain = $data->domain;
            $tld = strstr($domain, ".");
            $currency = $data->currency;
            $groupid = $data->groupid;
            $registrationperiod = (int) $data->registrationperiod;
            $regperiod = $periodMap[$registrationperiod] ?? null;
            if ($regperiod === null) {
                continue;
            }
            $tldsNeeded[$tld] = true;
            $whmcs_domains[] = [
                "userid" => $data->userid,
                "tld" => $tld,
                "domain" => $domain,
                "currency" => $currency,
                "groupid" => $groupid,
                "regperiod" => $regperiod,
                "years" => $registrationperiod,
                "dnsmanagement" => !empty($data->dnsmanagement) ? ($whmcs_addons[$currency]["dnsmanagement"] ?? "0.00") : "0.00",
                "emailforwarding" => !empty($data->emailforwarding) ? ($whmcs_addons[$currency]["emailforwarding"] ?? "0.00") : "0.00",
                "idprotection" => !empty($data->idprotection) ? ($whmcs_addons[$currency]["idprotection"] ?? "0.00") : "0.00",
            ];
        }
        // Preload TLD ids and renew pricing (avoid N+1) — RCM-017.
        $tldIdByExt = [];
        if (!empty($tldsNeeded)) {
            foreach (Illuminate\Database\Capsule\Manager::table("tbldomainpricing")->whereIn("extension", array_keys($tldsNeeded))->select("id", "extension")->get() as $row) {
                $tldIdByExt[$row->extension] = (int) $row->id;
            }
        }
        $tldIds = array_values(array_unique(array_filter($tldIdByExt)));
        $pricingByKey = [];
        if (!empty($tldIds)) {
            foreach (Illuminate\Database\Capsule\Manager::table("tblpricing")->whereIn("relid", $tldIds)->where("type", "=", "domainrenew")->select("relid", "currency", "tsetupfee", "msetupfee", "qsetupfee", "ssetupfee", "asetupfee", "bsetupfee", "monthly", "quarterly", "semiannually", "annually", "biennially")->get() as $prow) {
                $pricingByKey[$prow->relid . "|" . $prow->currency . "|" . $prow->tsetupfee] = $prow;
            }
        }
        $recurringprice_array = [];
        foreach ($whmcs_domains as $values) {
            $tldid = $tldIdByExt[$values["tld"]] ?? null;
            if (!$tldid) {
                continue;
            }
            $regperiod = $values["regperiod"];
            $years = $values["years"];
            $currency = $values["currency"];
            $groupid = $values["groupid"] . ".00";
            $row = $pricingByKey[$tldid . "|" . $currency . "|" . $groupid] ?? null;
            if (!$row) {
                $row = $pricingByKey[$tldid . "|" . $currency . "|0.00"] ?? null;
            }
            if (!$row) {
                continue;
            }
            $is_regperiod = $row->{$regperiod} ?? null;
            if (!$is_regperiod) {
                continue;
            }
            $dns = $values["dnsmanagement"] * $years;
            $mail = $values["emailforwarding"] * $years;
            $idprod = $values["idprotection"] * $years;
            $recurringprice_array[$values["domain"]] = $is_regperiod + $dns + $mail + $idprod;
        }
        foreach ($recurringprice_array as $thedomain => $recurringamount) {
            if ("0.00" < $recurringamount) {
                $update = ["recurringamount" => $recurringamount];
                Illuminate\Database\Capsule\Manager::table("tbldomains")->where("domain", "=", $thedomain)->update($update);
            }
        }
        logActivity("Cron Job (RCM): " . $LANG["recurringupdatesuccess"]);
        $action = "domain recurring update";
        $requeststring = "sqlqueries";
        $responsedata = ["rcmdebug" => $debug_addinfo, "sqlresults" => $recurringprice_array];
        rcm_log_module_call($modulename, $action, $requeststring, $responsedata);
        } finally {
            rcm_release_lock($rcm_daily_lock);
        }
    } else {
        logActivity("Cron Job (RCM): Skipping Update Domain recurring prices");
    }
}
