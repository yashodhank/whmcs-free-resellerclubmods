<?php
if (!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
$releasedate = "2026-01-29";
$softversion = "2.19.1";
if (!function_exists("adminemailmessages")) {
    function adminemailmessages($mailtpltype, $mailsubject, $mailmessage, $apiadminuser)
    {
        $check_result = Illuminate\Database\Capsule\Manager::table("tblemailtemplates")->where("type", "=", "admin")->where("name", "=", $mailtpltype)->select("name", "message")->get();
        $checktplname = isset($check_result[0]) ? $check_result[0]->name : "";
        $checktplmessage = isset($check_result[0]) ? $check_result[0]->message : "";
        if (empty($checktplname)) {
            $values = ["type" => "admin", "name" => $mailtpltype, "subject" => "{\$atr_subject}", "message" => "{\$atr_message|unescape:\"htmlall\"}"];
            $result = Illuminate\Database\Capsule\Manager::table("tblemailtemplates")->insert($values);
        } else if (stripos($checktplmessage, "unescape:\"htmlall\"") === false) {
            $update = ["message" => "{\$atr_message|unescape:\"htmlall\"}"];
            Illuminate\Database\Capsule\Manager::table("tblemailtemplates")->where("type", "=", "admin")->where("name", "=", $mailtpltype)->update($update);
        }
        $command = "sendadminemail";
        $atr_subject = $mailsubject;
        $atr_message = $mailmessage;
        $values["messagename"] = $mailtpltype;
        $values["mergefields"] = ["atr_subject" => $atr_subject, "atr_message" => $atr_message];
        $apiresults = localAPI($command, $values, $apiadminuser);
    }
}
if (!function_exists("foreignCurrencies")) {
    function foreignCurrencies()
    {
        $othercurrencies = [];
        foreach (Illuminate\Database\Capsule\Manager::table("tblcurrencies")->where("default", "!=", "1")->select("code")->get() as $currencies) {
            $othercurrencies[] = $currencies->code;
        }
        return $othercurrencies;
    }
}
if (!function_exists("domainCurrencyPricingupdate")) {
    function domainCurrencyPricingupdate($clientgroupid, $is_type, $domtype, $tldrelid = NULL)
    {
        if ($is_type == 1) {
            $whereIn = ["domainregister", "domaintransfer", "domainrenew"];
        } else {
            $whereIn = [$domtype];
        }
        $result = Illuminate\Database\Capsule\Manager::table("tblcurrencies")->where("default", "=", "1")->select("id")->get();
        $defaultcurrencyid = isset($result[0]) ? $result[0]->id : 1;
        $currencies = [];
        foreach (Illuminate\Database\Capsule\Manager::table("tblcurrencies")->where("id", "!=", $defaultcurrencyid)->select("id", "rate")->get() as $currency) {
            $currencies[$currency->id] = $currency->rate;
        }
        if (empty($currencies)) {
            return NULL;
        }
        if (empty($clientgroupid)) {
            $clientgroup_tsetupfee = "0.00";
        } else {
            $clientgroup_tsetupfee = $clientgroupid . ".00";
        }
        $baseQuery = Illuminate\Database\Capsule\Manager::table("tblpricing")->where("currency", "=", $defaultcurrencyid)->where("tsetupfee", "=", $clientgroup_tsetupfee)->whereIn("type", $whereIn);
        if (!is_null($tldrelid)) {
            $baseQuery = $baseQuery->where("relid", "=", $tldrelid);
        }
        $baseRows = $baseQuery->select("type", "relid", "msetupfee", "qsetupfee", "ssetupfee", "asetupfee", "bsetupfee", "tsetupfee", "monthly", "quarterly", "semiannually", "annually", "biennially", "triennially")->get();
        if (!$baseRows || !count($baseRows)) {
            return NULL;
        }
        $baseEntries = [];
        $allRelids = [];
        foreach ($baseRows as $row) {
            $type = $row->type;
            $relid = (int) $row->relid;
            $allRelids[] = $relid;
            if ($clientgroup_tsetupfee === "0.00") {
                $tsetupfee = $row->tsetupfee;
            } else {
                $tsetupfee = $clientgroup_tsetupfee;
            }
            $baseEntries[] = ["type" => $type, "relid" => $relid, "tsetupfee" => $tsetupfee, "msetupfee" => $row->msetupfee, "qsetupfee" => $row->qsetupfee, "ssetupfee" => $row->ssetupfee, "asetupfee" => $row->asetupfee, "bsetupfee" => $row->bsetupfee, "monthly" => $row->monthly, "quarterly" => $row->quarterly, "semiannually" => $row->semiannually, "annually" => $row->annually, "biennially" => $row->biennially, "triennially" => $row->triennially];
        }
        $allRelids = array_values(array_unique($allRelids));
        if (empty($allRelids)) {
            return NULL;
        }
        foreach ($currencies as $currencyId => $rate) {
            if ($rate <= 0) {
            } else {
                $existingRows = Illuminate\Database\Capsule\Manager::table("tblpricing")->where("currency", "=", $currencyId)->whereIn("type", $whereIn)->whereIn("relid", $allRelids)->select("id", "type", "relid", "tsetupfee")->get();
                $existingMap = [];
                foreach ($existingRows as $erow) {
                    $key = $erow->type . "|" . (int) $erow->relid . "|" . $erow->tsetupfee;
                    $existingMap[$key] = $erow->id;
                }
                foreach ($baseEntries as $entry) {
                    $type = $entry["type"];
                    $relid = $entry["relid"];
                    $tsetupfee = $entry["tsetupfee"];
                    $msetupfee = $entry["msetupfee"];
                    $qsetupfee = $entry["qsetupfee"];
                    $ssetupfee = $entry["ssetupfee"];
                    $asetupfee = $entry["asetupfee"];
                    $bsetupfee = $entry["bsetupfee"];
                    $monthly = $entry["monthly"];
                    $quarterly = $entry["quarterly"];
                    $semiannually = $entry["semiannually"];
                    $annually = $entry["annually"];
                    $biennially = $entry["biennially"];
                    $triennially = $entry["triennially"];
                    $upd_msetupfee = 0 < $msetupfee ? round($msetupfee * $rate, 2) : $msetupfee;
                    $upd_qsetupfee = 0 < $qsetupfee ? round($qsetupfee * $rate, 2) : $qsetupfee;
                    $upd_ssetupfee = 0 < $ssetupfee ? round($ssetupfee * $rate, 2) : $ssetupfee;
                    $upd_asetupfee = 0 < $asetupfee ? round($asetupfee * $rate, 2) : $asetupfee;
                    $upd_bsetupfee = 0 < $bsetupfee ? round($bsetupfee * $rate, 2) : $bsetupfee;
                    $upd_monthly = 0 < $monthly ? round($monthly * $rate, 2) : $monthly;
                    $upd_quarterly = 0 < $quarterly ? round($quarterly * $rate, 2) : $quarterly;
                    $upd_semiannually = 0 < $semiannually ? round($semiannually * $rate, 2) : $semiannually;
                    $upd_annually = 0 < $annually ? round($annually * $rate, 2) : $annually;
                    $upd_biennially = 0 < $biennially ? round($biennially * $rate, 2) : $biennially;
                    $upd_triennially = 0 < $triennially ? round($triennially * $rate, 2) : $triennially;
                    $update = ["msetupfee" => $upd_msetupfee, "qsetupfee" => $upd_qsetupfee, "ssetupfee" => $upd_ssetupfee, "asetupfee" => $upd_asetupfee, "bsetupfee" => $upd_bsetupfee, "tsetupfee" => $tsetupfee, "monthly" => $upd_monthly, "quarterly" => $upd_quarterly, "semiannually" => $upd_semiannually, "annually" => $upd_annually, "biennially" => $upd_biennially, "triennially" => $upd_triennially];
                    $key = $type . "|" . $relid . "|" . $tsetupfee;
                    if (isset($existingMap[$key])) {
                        Illuminate\Database\Capsule\Manager::table("tblpricing")->where("id", "=", $existingMap[$key])->update($update);
                    } else {
                        $values = $update + ["type" => $type, "currency" => $currencyId, "relid" => $relid];
                        Illuminate\Database\Capsule\Manager::table("tblpricing")->insert($values);
                    }
                }
            }
        }
    }
}
if (!function_exists("newproductkeys")) {
    function newproductkeys($rcauth_userid, $rcauth_password, $rchttp_api)
    {
        if (!class_exists("idna_convert")) {
            require_once ROOTDIR . "/modules/addons/resellerclubmods_tools/incs/idnclass.php";
        }
        $IDN = new idna_convert();
        $method = "GET";
        $apifunction = "/api/products/category-keys-mapping.json";
        $productmap = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
        $newproductkeys = [];
        foreach ($productmap["domorder"] as $mapkeys) {
            foreach ($mapkeys as $k => $v) {
                asort($v);
                foreach ($v as $s) {
                    $t = "." . $s . ",";
                    $newproductkeys[$k] .= "." . $IDN->decode($s) . ",";
                }
            }
        }
        return $newproductkeys;
    }
}
if (!function_exists("getaddondetails")) {
    function getaddondetails($rcauth_userid, $rcauth_password, $rchttp_api)
    {
        $method = "GET";
        $apifunction = "/api/products/details.json";
        $addondetails = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
        return $addondetails;
    }
}
if (!function_exists("arraytolower")) {
    function arraytolower(array $array, $round = 0)
    {
        return unserialize(strtolower(serialize($array)));
    }
}
if (!function_exists("convToUtf8")) {
    function convToUtf8($str)
    {
        if (preg_match("//u", $str)) {
            return $str;
        }
        $str = utf8_encode($str);
        return $str;
    }
}
if (!function_exists("rcm_trim")) {
    function rcm_trim($value)
    {
        $value = trim($value);
        return $value;
    }
}
if (!function_exists("rcm_array_trim")) {
    function rcm_array_trim(&$value)
    {
        $value = trim($value);
    }
}
if (!function_exists("passGen")) {
    function passGen($l = 8, $c = 1, $n = 1, $s = 0)
    {
        $count = $c + $n + $s;
        if (!is_int($l) || !is_int($c) || !is_int($n) || !is_int($s)) {
            trigger_error("Argument(s) not an integer", 512);
            return false;
        }
        if ($l < 0 || 15 < $l || $c < 0 || $n < 0 || $s < 0) {
            return false;
        }
        if ($l < $c) {
            return false;
        }
        if ($l < $n) {
            return false;
        }
        if ($l < $s) {
            return false;
        }
        if ($l < $count) {
            return false;
        }
        $chars = "abcdefghijklmnopqrstuvwxyz";
        $caps = strtoupper($chars);
        $nums = "0123456789";
        $syms = "~!@#\$%*_+?:,{}";
        $out = "";
        for ($i = 0; $i < $l; $i++) {
            $out .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        if ($count) {
            $tmp1 = str_split($out);
            $tmp2 = [];
            for ($i = 0; $i < $c; $i++) {
                array_push($tmp2, substr($caps, mt_rand(0, strlen($caps) - 1), 1));
            }
            for ($i = 0; $i < $n; $i++) {
                array_push($tmp2, substr($nums, mt_rand(0, strlen($nums) - 1), 1));
            }
            for ($i = 0; $i < $s; $i++) {
                array_push($tmp2, substr($syms, mt_rand(0, strlen($syms) - 1), 1));
            }
            $tmp1 = array_slice($tmp1, 0, $l - $count);
            $tmp1 = array_merge($tmp1, $tmp2);
            shuffle($tmp1);
            $out = implode("", $tmp1);
        }
        return $out;
    }
}
if (!function_exists("getAdminlang")) {
    function getAdminlang($adminlang)
    {
        if ($adminlang == "english") {
            $require_adminlang = "/modules/addons/resellerclubmods_tools/lang/english.php";
        } else if ($adminlang == "spanish") {
            $require_adminlang = "/modules/addons/resellerclubmods_tools/lang/spanish.php";
        } else if ($adminlang == "portugues") {
            $require_adminlang = "/modules/addons/resellerclubmods_tools/lang/portugues.php";
        } else if ($adminlang == "russian") {
            $require_adminlang = "/modules/addons/resellerclubmods_tools/lang/russian.php";
        } else if ($adminlang == "german" || $adminlang == "germany") {
            $require_adminlang = "/modules/addons/resellerclubmods_tools/lang/german.php";
        } else if ($adminlang == "czech") {
            $require_adminlang = "/modules/addons/resellerclubmods_tools/lang/czech.php";
        } else if ($adminlang == "french") {
            $require_adminlang = "/modules/addons/resellerclubmods_tools/lang/french.php";
        } else {
            $require_adminlang = "/modules/addons/resellerclubmods_tools/lang/english.php";
        }
        return $require_adminlang;
    }
}
if (!function_exists("checkLang")) {
    function checkLang($language)
    {
        switch ($language) {
            case "arabic":
                $langPref = "ar";
                break;
            case "azerbaijani":
                $langPref = "en";
                break;
            case "catalan":
                $langPref = "es";
                break;
            case "chinese":
                $langPref = "ch";
                break;
            case "croatian":
                $langPref = "en";
                break;
            case "czech":
                $langPref = "en";
                break;
            case "danish":
                $langPref = "en";
                break;
            case "dutch":
                $langPref = "nl";
                break;
            case "english":
                $langPref = "en";
                break;
            case "estonian":
                $langPref = "en";
                break;
            case "farsi":
                $langPref = "en";
                break;
            case "french":
                $langPref = "fr";
                break;
            case "german":
                $langPref = "de";
                break;
            case "hebrew":
                $langPref = "en";
                break;
            case "hungarian":
                $langPref = "en";
                break;
            case "italian":
                $langPref = "en";
                break;
            case "macedonian":
                $langPref = "en";
                break;
            case "norwegian":
                $langPref = "en";
                break;
            case "polish":
                $langPref = "en";
                break;
            case "portuguese-br":
                $langPref = "pt";
                break;
            case "portuguese-pt":
                $langPref = "pt";
                break;
            case "romanian":
                $langPref = "en";
                break;
            case "russian":
                $langPref = "ru";
                break;
            case "spanish":
                $langPref = "es";
                break;
            case "swedish":
                $langPref = "en";
                break;
            case "turkish":
                $langPref = "tr";
                break;
            case "ukranian":
                $langPref = "en";
                break;
            default:
                $langPref = "en";
                return $langPref;
        }
    }
}
if (!function_exists("serialize_data")) {
    function serialize_data($data)
    {
        $query = "";
        if (!is_array($data)) {
            return false;
        }
        foreach ($data as $key => $value) {
            if (!is_array($value)) {
                $query .= "&" . $key . "=" . rawurlencode($value);
            } else {
                foreach ($value as $val) {
                    if (!is_array($val)) {
                        $query .= "&" . $key . "=" . rawurlencode($val);
                    }
                }
            }
        }
        return $query;
    }
}
if (!function_exists("createOBcustomer")) {
    function createOBcustomer($newcustomer, $rchttp_api, $rcauth_userid, $rcauth_password, $whmcscupwd)
    {
        $rcmdebuginfo = getDebuginfos();
        $debug_addinfo = $rcmdebuginfo["debug_addinfo"];
        $modulename = $rcmdebuginfo["modulename"];
        $CONFIG = $rcmdebuginfo["config"];
        $novalid = ["\\", "/", "º", "ª", "!", "\"", "\$", "%", "&", "(", ")", "=", "?", "¿", "¡", "[", "]", "{", "}", "_"];
        if (getWver() < "7.0.0") {
            require ROOTDIR . "/includes/countriescallingcodes.php";
        } else {
            $result = file_get_contents(ROOTDIR . "/resources/country/dist.countries.json");
            $json = json_decode($result, true);
            $countries = [];
            foreach ($json as $k => $v) {
                $countrycallingcodes[$k] = $v["callingCode"];
            }
        }
        $result = Illuminate\Database\Capsule\Manager::table("tblclients")->where("email", "=", $newcustomer)->select("firstname", "lastname", "companyname", "address1", "address2", "city", "state", "postcode", "country", "phonenumber", "email", "language", "id")->get();
        $check_arr = json_decode(json_encode($result[0]), true);
        $check_arr = foreignChrReplace($check_arr);
        $userName = $check_arr["email"];
        if (!empty($whmcscupwd)) {
            $passwd = $whmcscupwd;
            $action = "create customer with whmcs pwd";
        } else {
            $passwd = passGen(9, 1, 1, 1);
            $action = "create customer with random pwd";
        }
        $first_name = $check_arr["firstname"];
        $last_name = $check_arr["lastname"];
        $name = $first_name . " " . $last_name;
        $name = convToUtf8($name);
        if (empty($check_arr["companyname"])) {
            $company = "Not Applicable";
        } else {
            $company = $check_arr["companyname"];
            $company = convToUtf8($company);
        }
        $address1 = $check_arr["address1"];
        $address1 = convToUtf8($address1);
        $address1 = str_replace($novalid, "", $address1);
        if (64 < strlen($address1)) {
            $address1 = substr($address1, 0, 63);
        }
        $address2 = $check_arr["address2"];
        $address2 = convToUtf8($address2);
        $address2 = str_replace($novalid, "", $address2);
        if (64 < strlen($address2)) {
            $address2 = substr($address2, 0, 63);
        }
        $address3 = "";
        $city = $check_arr["city"];
        $city = convToUtf8($city);
        $city = str_replace($novalid, "", $city);
        $stateName = ucfirst(strtolower($check_arr["state"]));
        $stateName = str_replace($novalid, "", $stateName);
        $zip = $check_arr["postcode"];
        $country = $check_arr["country"];
        $nospaces = [" ", "&nbsp;", "-", ".", "+", "/", "_", "(", ")", ","];
        $telNoCc = $countrycallingcodes[$country];
        $telNo = $check_arr["phonenumber"];
        if (empty($telNo)) {
            $telNo = "000000000000";
        } else {
            $telNo = str_replace($nospaces, "", $telNo);
            $telNo = substr($telNo, 0, 12);
        }
        $altTelNoCc = "";
        $altTelNo = "";
        $faxNoCc = "";
        $faxNo = "";
        $language = strtolower($check_arr["language"]);
        if (empty($language)) {
            $language = strtolower($CONFIG["Language"]);
        }
        $langPref = checkLang($language);
        $mobileNoCc = "";
        $mobileNo = "";
        $method = "POST";
        $apifunction = "/api/customers/v2/signup.json";
        $data = ["username" => $userName, "passwd" => $passwd, "name" => $name, "company" => $company, "address-line-1" => $address1, "address-line-2" => $address2, "address-line-3" => $address3, "city" => $city, "state" => $stateName, "country" => $country, "zipcode" => $zip, "phone-cc" => $telNoCc, "phone" => $telNo, "alt-phone-cc" => $altTelNoCc, "alt-phone" => $altTelNo, "fax-cc" => $faxNoCc, "fax" => $faxNo, "mobile-cc" => $mobileNoCc, "mobile" => $mobileNo, "lang-pref" => $langPref];
        $signup_rc_customer_id = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
        $requeststring = $apifunction . " [reseller data protected] " . serialize_data($data);
        $responsedata = ["rcmdebug" => $debug_addinfo, "apidebug" => $signup_rc_customer_id];
        logModuleCall($modulename, $action, $requeststring, $responsedata);
        return $signup_rc_customer_id;
    }
}
if (!function_exists("call_api")) {
    function call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method)
    {
        $data = "auth-userid=" . $rcauth_userid . "&api-key=" . rawurlencode($rcauth_password) . serialize_data($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        if ($method == "POST") {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_URL, $rchttp_api . $apifunction);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        } else {
            curl_setopt($ch, CURLOPT_URL, $rchttp_api . $apifunction . "?" . $data);
        }
        $result = curl_exec($ch);
        $result = json_decode($result, true);
        curl_close($ch);
        return $result;
    }
}
if (!function_exists("registrardropdown")) {
    function registrardropdown($setautoreg, $is_registrar)
    {
        $tblregistrars = [];
        foreach (Illuminate\Database\Capsule\Manager::table("tblregistrars")->get() as $registrars) {
            $tblregistrars[] = $registrars->registrar;
        }
        array_push($tblregistrars, "None");
        $tblregistrars = array_unique($tblregistrars);
        sort($tblregistrars);
        $registrar_dropdown .= "<select name=\"autoreg\" class=\"form-control\" style=\"display: inline; width: auto\">";
        foreach ($tblregistrars as $lbregistrars) {
            if (!empty($setautoreg) && $is_registrar == $lbregistrars) {
                $registrar_dropdown .= "<option value=\"" . $lbregistrars . "\" " . $setautoreg . ">" . $lbregistrars . "</option>";
            } else {
                $registrar_dropdown .= "<option value=\"" . $lbregistrars . "\">" . $lbregistrars . "</option>";
            }
        }
        $registrar_dropdown .= "</select>";
        return $registrar_dropdown;
    }
}
if (!function_exists("getWver")) {
    function getWver()
    {
        global $CONFIG;
        return strstr($CONFIG["Version"], "-", true);
    }
}
if (!function_exists("is_inthousands")) {
    function is_inthousands($is_1000s)
    {
        $currency_thousands = ["AMD", "BYR", "BIF", "COP", "CDF", "GNF", "IDR", "KHR", "KPW", "LAK", "LBP", "MGA", "MNT", "PYG", "RWF", "STD", "SLL", "SOS", "TZS", "UGX", "VND", "YER"];
        if (in_array($is_1000s, $currency_thousands)) {
            return true;
        }
    }
}
if (!function_exists("getDebuginfos")) {
    function getDebuginfos()
    {
        global $releasedate;
        global $softversion;
        global $customadminpath;
        global $CONFIG;
        // Environment metadata for WHMCS module debug logs (plaintext OSS source).
        $is_full_php = phpversion();
        $is_php = substr(phpversion(), 0, 3);
        $mdf = sha1(file_get_contents(__FILE__));
        $fse = filesize(__FILE__);
        $modulename = "RCLBT";
        $debug_addinfo = ["whmcsversion" => $CONFIG["Version"], "phpversion" => $is_full_php, "modulename" => $modulename, "moduleversion" => $softversion];
        return ["modulename" => $modulename, "is_php" => $is_php, "fse" => $fse, "mdf" => $mdf, "debug_addinfo" => $debug_addinfo, "softversion" => $softversion, "releasedate" => $releasedate, "customadminpath" => $customadminpath, "config" => $CONFIG];
    }
}

?>