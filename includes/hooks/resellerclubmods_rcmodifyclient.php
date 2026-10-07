<?php
if (!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
require ROOTDIR . "/modules/addons/resellerclubmods_tools/incs/functions.php";
add_hook("ClientEdit", 1, "modify_rc_account");
function modify_rc_account($vars)
{
    $vars = foreignChrReplace($vars);
    $rcmdebuginfo = getDebuginfos();
    $modulename = $rcmdebuginfo["modulename"];
    $debug_addinfo = $rcmdebuginfo["debug_addinfo"];
    $CONFIG = $rcmdebuginfo["config"];
    $conf = [];
    foreach (Illuminate\Database\Capsule\Manager::table("tbladdonmodules")->where("module", "resellerclubmods_tools")->get() as $addonvars) {
        $conf[$addonvars->setting] = $addonvars->value;
    }
    $rchttp_api = $conf["rchttp_api"];
    $conf_arr = [];
    $conf_arr["first"] = [$conf["first_rcauth_userid"], htmlspecialchars_decode($conf["first_rcauth_apikey"]), $conf["first_acc_name"], $conf["first_hook_modify"], $conf["first_hook_modify_useremail"]];
    $conf_arr["second"] = [$conf["second_rcauth_userid"], htmlspecialchars_decode($conf["second_rcauth_apikey"]), $conf["second_acc_name"], $conf["second_hook_modify"]];
    $conf_arr["third"] = [$conf["third_rcauth_userid"], htmlspecialchars_decode($conf["third_rcauth_apikey"]), $conf["third_acc_name"], $conf["third_hook_modify"]];
    $conf_arr["fourth"] = [$conf["fourth_rcauth_userid"], htmlspecialchars_decode($conf["fourth_rcauth_apikey"]), $conf["fourth_acc_name"], $conf["fourth_hook_modify"]];
            $novalid = ["\\", "/", "ª", "!", "\"", "\$", "%", "&", "(", ")", "=", "?", "¿", "¡", "[", "]", "{", "}", "_"];
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
        foreach ($conf_arr as $confstring) {
            if ($confstring[3] != "on" && !empty($confstring[0])) {
                list($rcauth_userid, $rcauth_password, $logicbox_registrar) = $confstring;
                $whmcs_email = $vars["olddata"]["email"];
                $method = "GET";
                $apifunction = "/api/customers/details.json";
                $data = ["username" => $whmcs_email];
                $arrXml = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
                if ($arrXml["status"] != "ERROR") {
                    $customerId = $arrXml["customerid"];
                    if (empty($vars["email"])) {
                        $userName = $vars["olddata"]["email"];
                    } else {
                        $userName = $vars["email"];
                    }
                    if (empty($vars["firstname"])) {
                        $first_name = $vars["olddata"]["firstname"];
                    } else {
                        $first_name = $vars["firstname"];
                    }
                    if (empty($vars["lastname"])) {
                        $last_name = $vars["olddata"]["lastname"];
                    } else {
                        $last_name = $vars["lastname"];
                    }
                    $name = $first_name . " " . $last_name;
                    $name = convToUtf8($name);
                    $name = str_replace($novalid, "", $name);
                    if (empty($vars["companyname"])) {
                        $company = "Not Applicable";
                    } else {
                        $company = $vars["companyname"];
                        $company = convToUtf8($company);
                        $company = str_replace($novalid, "", $company);
                    }
                    if (empty($vars["address1"])) {
                        $address1 = $vars["olddata"]["address1"];
                    } else {
                        $address1 = $vars["address1"];
                    }
                    $address1 = convToUtf8($address1);
                    $address1 = str_replace($novalid, "", $address1);
                    if (empty($vars["address2"])) {
                        $address2 = $vars["olddata"]["address2"];
                    } else {
                        $address2 = $vars["address2"];
                    }
                    $address2 = convToUtf8($address2);
                    $address2 = str_replace($novalid, "", $address2);
                    $address3 = "";
                    if (empty($vars["city"])) {
                        $city = $vars["olddata"]["city"];
                    } else {
                        $city = $vars["city"];
                    }
                    $city = convToUtf8($city);
                    $city = str_replace($novalid, "", $city);
                    if (empty($vars["state"])) {
                        $stateName = ucfirst(strtolower($vars["olddata"]["state"]));
                    } else {
                        $stateName = ucfirst(strtolower($vars["state"]));
                    }
                    $stateName = str_replace($novalid, "", $stateName);
                    if (empty($vars["postcode"])) {
                        $zip = $vars["olddata"]["postcode"];
                    } else {
                        $zip = $vars["postcode"];
                    }
                    if (empty($vars["country"])) {
                        $country = $vars["olddata"]["country"];
                    } else {
                        $country = $vars["country"];
                    }
                    $nospaces = [" ", "&nbsp;", "-", ".", "+", "/", "_", "(", ")", ","];
                    $telNoCc = $countrycallingcodes[$country];
                    $telNo = $vars["phonenumber"];
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
                    $mobileNoCc = "";
                    $mobileNo = "";
                    $language = strtolower($vars["language"]);
                    if (empty($language)) {
                        $language = strtolower($CONFIG["Language"]);
                    }
                    $langPref = checkLang($language);
                    if ("8.0.0" <= getWver() && $confstring[4] == "on") {
                        $result_tblusers = Illuminate\Database\Capsule\Manager::table("tblusers")->where("id", "=", $vars["olddata"]["owner_user_id"])->select("email")->get();
                        $tblusers_email = $result_tblusers[0]->email;
                        if ($vars["email"] != $tblusers_email) {
                            $update = ["email" => $vars["email"]];
                            Illuminate\Database\Capsule\Manager::table("tblusers")->where("id", "=", $vars["olddata"]["owner_user_id"])->update($update);
                            logActivity("Owner User Account Email address (" . $vars["olddata"]["email"] . ") successfully updated with Client Account Email address (" . $vars["email"] . ") ", $vars["userid"]);
                        }
                    }
                    $method = "POST";
                    $apifunction = "/api/customers/modify.json";
                    $data = ["customer-id" => $customerId, "username" => $userName, "name" => $name, "company" => $company, "address-line-1" => $address1, "address-line-2" => $address2, "address-line-3" => $address3, "city" => $city, "state" => $stateName, "country" => $country, "zipcode" => $zip, "phone-cc" => $telNoCc, "phone" => $telNo, "alt-phone-cc" => $altTelNoCc, "alt-phone" => $altTelNo, "fax-cc" => $faxNoCc, "fax" => $faxNo, "mobile-cc" => $mobileNoCc, "mobile" => $mobileNo, "lang-pref" => $langPref];
                    $modify_rc_customer = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
                    $action = "Modify Customer";
                    $requeststring = $apifunction . " [reseller data protected] " . serialize_data($data);
                    $responsedata = ["rcmdebug" => $debug_addinfo, "apidebug" => $modify_rc_customer];
                    rcm_log_module_call($modulename, $action, $requeststring, $responsedata);
                    if ($modify_rc_customer == "true") {
                        logActivity("Customer successfully modified at " . $logicbox_registrar . " (" . $rcauth_userid . ") - Old User Email: " . $vars["olddata"]["email"] . " - New User Email: " . $vars["email"] . " - User ID: " . $vars["userid"], $vars["userid"]);
                    } else {
                        logActivity("Customer modification for User " . $vars["email"] . " failed: " . $modify_rc_customer["message"], $vars["userid"]);
                    }
                } else {
                    logActivity("Customer modification for User " . $vars["email"] . " failed: User does not exist at " . $logicbox_registrar . " (" . $rcauth_userid . ") - User ID: " . $vars["userid"], $vars["userid"]);
                }
        }
    }
}

?>