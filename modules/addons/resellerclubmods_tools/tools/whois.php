<?php
$include_path = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
require_once $include_path . "/init.php";
$conf = [];
foreach (Illuminate\Database\Capsule\Manager::table("tbladdonmodules")->where("module", "resellerclubmods_tools")->get() as $addonvars) {
    if (!isset($addonvars->setting)) {
    } else {
        $conf[(string) $addonvars->setting] = (string) ($addonvars->value ?? "");
    }
}
require_once ROOTDIR . "/modules/addons/resellerclubmods_tools/incs/whoislookup.php";
require_once ROOTDIR . "/modules/addons/resellerclubmods_tools/incs/functions.php";
if (!class_exists("idna_convert")) {
        require_once ROOTDIR . "/modules/addons/resellerclubmods_tools/incs/idnclass.php";
    }
    $IDN = new idna_convert();
    $widKey = $conf["wid_key"] ?? "";
    $wid = substr(md5($widKey), 6, 10);
    $md5_wid = $_GET["wid"] ?? NULL;
    $rawDomain = (string) ($_REQUEST["domain"] ?? "");
    $rawDomain = trim($rawDomain);
    $domain = $IDN->encode(strtolower($rawDomain));
    if (empty($md5_wid)) {
        $whoisresult = [];
        $whoisresult[$domain]["status"] = "you need to pass your wid";
        echo $whoisresult[$domain]["status"];
        exit;
    }
    if (!hash_equals($wid, (string) $md5_wid)) {
        $whoisresult = [];
        $whoisresult[$domain]["status"] = "Unauthorized Access Attempt";
        echo $whoisresult[$domain]["status"];
        exit;
    }
    $use_account_whois = $conf["use_account_whois"] ?? "";
    $rchttp_api = "https://domaincheck.httpapi.com";
    $conf_arr = [];
    $conf_arr["first"] = [$conf["first_rcauth_userid"] ?? "", $conf["first_rcauth_apikey"] ?? "", $conf["first_domainregistrar"] ?? ""];
    $conf_arr["second"] = [$conf["second_rcauth_userid"] ?? "", $conf["second_rcauth_apikey"] ?? "", $conf["second_domainregistrar"] ?? ""];
    $conf_arr["third"] = [$conf["third_rcauth_userid"] ?? "", $conf["third_rcauth_apikey"] ?? "", $conf["third_domainregistrar"] ?? ""];
    $conf_arr["fourth"] = [$conf["fourth_rcauth_userid"] ?? "", $conf["fourth_rcauth_apikey"] ?? "", $conf["fourth_domainregistrar"] ?? ""];
    $rcauth_userid = "";
    $rcauth_password = "";
    if (is_numeric($conf_arr["first"][0]) && !empty($conf_arr["first"][1])) {
        $rcauth_userid = (string) $conf_arr["first"][0];
        $rcauth_password = (string) $conf_arr["first"][1];
    } else if (is_numeric($conf_arr["second"][0]) && !empty($conf_arr["second"][1])) {
        $rcauth_userid = (string) $conf_arr["second"][0];
        $rcauth_password = (string) $conf_arr["second"][1];
    } else if (is_numeric($conf_arr["third"][0]) && !empty($conf_arr["third"][1])) {
        $rcauth_userid = (string) $conf_arr["third"][0];
        $rcauth_password = (string) $conf_arr["third"][1];
    } else if (is_numeric($conf_arr["fourth"][0]) && !empty($conf_arr["fourth"][1])) {
        $rcauth_userid = (string) $conf_arr["fourth"][0];
        $rcauth_password = (string) $conf_arr["fourth"][1];
    }
    if (!empty($rcauth_userid) && !empty($rcauth_password) && $rawDomain !== "") {
        $tld = ltrim((string) strstr($domain, "."), ".");
        $sld = (string) strstr($domain, ".", true);
        if ($tld === "" || $sld === "") {
            echo "unknown";
            exit;
        }
        if ($use_account_whois === "on") {
            $autoreg = Illuminate\Database\Capsule\Manager::table("tbldomainpricing")->where("extension", "=", "." . $tld)->value("autoreg");
            $tld_registrar = $conf_arr["first"][2] ?? "";
            if ($autoreg === "resellerclub" || $autoreg === "resellerclubrcm") {
                $tld_registrar = "resellerclub";
            } else if ($autoreg === "netearthone" || $autoreg === "netearthonercm") {
                $tld_registrar = "netearthone";
            } else if ($autoreg === "resellercamp" || $autoreg === "resellercamprcm") {
                $tld_registrar = "resellercamp";
            } else if ($autoreg === "stargate" || $autoreg === "stargatercm") {
                $tld_registrar = "stargate";
            }
            if (($conf_arr["first"][2] ?? "") === $tld_registrar) {
                $rcauth_userid = (string) $conf_arr["first"][0];
                $rcauth_password = (string) $conf_arr["first"][1];
            } else if (($conf_arr["second"][2] ?? "") === $tld_registrar) {
                $rcauth_userid = (string) $conf_arr["second"][0];
                $rcauth_password = (string) $conf_arr["second"][1];
            } else if (($conf_arr["third"][2] ?? "") === $tld_registrar) {
                $rcauth_userid = (string) $conf_arr["third"][0];
                $rcauth_password = (string) $conf_arr["third"][1];
            } else if (($conf_arr["fourth"][2] ?? "") === $tld_registrar) {
                $rcauth_userid = (string) $conf_arr["fourth"][0];
                $rcauth_password = (string) $conf_arr["fourth"][1];
            } else {
                $rcauth_userid = (string) $conf_arr["first"][0];
                $rcauth_password = (string) $conf_arr["first"][1];
                $tld_registrar = (string) ($conf_arr["first"][2] ?? "");
            }
        } else {
            $tld_registrar = (string) ($conf_arr["first"][2] ?? "");
        }
        $rcmdebuginfo = getDebuginfos();
        $modulename = (string) ($rcmdebuginfo["modulename"] ?? "resellerclubmods_tools");
        $debug_addinfo = $rcmdebuginfo["debug_addinfo"] ?? "";
        $method = "GET";
        $apifunction = "/api/domains/available.json";
        $data = ["domain-name" => $sld, "tlds" => $tld];
        $whoisresult = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
        if (empty($whoisresult)) {
            $rchttp_api_fallback = (string) ($conf["rchttp_api"] ?? "");
            if ($rchttp_api_fallback !== "") {
                $whoisresult = call_api($rcauth_userid, $rcauth_password, $rchttp_api_fallback, $apifunction, $data, $method);
            }
        }
        $action = "domainlookup with " . $tld_registrar;
        $requeststring = $apifunction . " [reseller data protected] " . serialize_data($data);
        $responsedata = ["rcmdebug" => $debug_addinfo, "apidebug" => $whoisresult];
        logModuleCall($modulename, $action, $requeststring, $responsedata);
        $result = $whoisresult[$domain]["status"] ?? NULL;
        if ($result === "available") {
            echo $result;
        } else if ($result === "regthroughothers" || $result === "regthroughus") {
            $res = domainlookup($domain);
            echo str_replace("available", "", $res);
        } else if (($whoisresult["status"] ?? NULL) === "ERROR") {
            echo (string) ($whoisresult["message"] ?? "");
        } else if ($result === "unknown") {
            echo $result;
        } else {
            echo "unknown";
        }
        exit;
}
function domainlookup($domain)
{
    global $public_whoisservers;
    $domain = trim($domain);
    if ($domain === "" || strpos($domain, ".") === false) {
        return "Invalid domain provided.";
    }
    $tldWithDot = strstr($domain, ".");
    $whoisserver = $public_whoisservers[$tldWithDot] ?? NULL;
    if (empty($whoisserver)) {
        return "No Whois Server found for " . $domain . " domain!";
    }
    $result = whoislookup($whoisserver, $domain);
    if (empty($result)) {
        return "Unable to retrieve results from " . $whoisserver . " server for " . $domain . " domain!";
    }
    while (strpos($result, "Whois Server:") !== false) {
        if (preg_match("/Whois Server:\\s*(.*)/", $result, $found)) {
            $sponsor_registrar = trim($found[1] ?? "");
            if ($sponsor_registrar !== "") {
                $result = whoislookup($sponsor_registrar, $domain);
                $whoisserver = $sponsor_registrar;
            }
        }
    }
    return $domain . " Whoislookup results from " . $whoisserver . " server:\n\n" . $result;
}
function whoislookup($whoisserver, string $domain)
{
    $fsp = @fsockopen($whoisserver, 43, $errno, $errstr, 20);
    if (!$fsp) {
        return "";
    }
    fwrite($fsp, $domain . "\r\n");
    $output = "";
    while (!feof($fsp)) {
        $chunk = fgets($fsp);
        if ($chunk === false) {
            break;
        }
        $output .= $chunk;
    }
    fclose($fsp);
    $result = "";
    $outLower = strtolower($output);
    if (strpos($outLower, "error") === false && strpos($outLower, "not allocated") === false) {
        $rows = explode("\n", $output);
        foreach ($rows as $row) {
            $row = trim($row);
            if ($row === "") {
            } else {
                $firstChar = $row[0] ?? "";
                if ($firstChar === "#" || $firstChar === "%") {
                } else {
                    $result .= $row . "\n";
                }
            }
        }
    }
    return $result;
}

?>