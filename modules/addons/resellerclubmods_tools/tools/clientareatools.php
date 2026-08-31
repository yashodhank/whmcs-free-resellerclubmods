<?php
if (!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
function resellerclubmods_tools_clientarea($vars)
{
    if (0 <= version_compare(getWver(), "6.0.0") && !function_exists("injectDomainObjectIfNecessary")) {
        include ROOTDIR . DIRECTORY_SEPARATOR . "includes" . DIRECTORY_SEPARATOR . "registrarfunctions.php";
    }
    $rcmdebuginfo = getDebuginfos();
    $CONFIG = $rcmdebuginfo["config"];
    $modulename = $rcmdebuginfo["modulename"];
    $debug_addinfo = $rcmdebuginfo["debug_addinfo"];
    if (!class_exists("idna_convert")) {
        require_once ROOTDIR . "/modules/addons/resellerclubmods_tools/incs/idnclass.php";
    }
    $IDN = new idna_convert();
    $LANG = $vars["_lang"];
    $allow_movedomain = $vars["allow_movedomain"];
    $movedomain_permission = $vars["movedomain_permission"];
    $domainbot_loggedin = $vars["domainbot_loggedin"];
    $domainbot_extqty = $vars["domainbot_extqty"];
    $domainbot_nossl = $vars["domainbot_nossl"];
    $maileradmin = $vars["maileradmin"];
    $themestyle = strtolower($vars["whmcs_theme"]);
    if ($vars["domainbot_yrs"] == "on") {
        $domainbot_yrs = "true";
    }
    $domain_tlds = explode(",", $vars["domainbot_tlds"]);
    array_walk($domain_tlds, "rcm_array_trim");
    if (count($domain_tlds) <= 5) {
        $domainbot_tlds = [$domain_tlds];
    } else {
        $domainbot_tlds = array_chunk($domain_tlds, 5);
    }
    $rchttp_api = $vars["rchttp_api"];
    global $_LANG;
    if ($movedomain_permission != "Client and Admin" && !isset($_SESSION["adminid"])) {
        $accessdenied = "on";
    }
    $currentuserid = isset($_SESSION["uid"]) ? $_SESSION["uid"] : "";
    $cid = isset($_SESSION["cid"]) ? $_SESSION["cid"] : "";
    if (isset($_REQUEST["action"]) && $_REQUEST["action"] == "movedomain") {
        if (!empty($currentuserid)) {
            $postdomain = $_REQUEST["domain"];
            $postdomainid = $_REQUEST["domainid"];
            if ($allow_movedomain == "on" && $accessdenied != "on") {
                $result = Illuminate\Database\Capsule\Manager::table("tbldomains")->where("domain", "=", $postdomain)->where("id", "=", $postdomainid)->select("userid", "id", "domain", "registrar")->get();
                $domainid = $result[0]->id;
                $domain = $result[0]->domain;
                $registrar = $result[0]->registrar;
                $isuserid = $result[0]->userid;
                if ($registrar == $vars["first_domainregistrar"] || $registrar == $vars["second_domainregistrar"] || $registrar == $vars["third_domainregistrar"] || $registrar == $vars["fourth_domainregistrar"] || $registrar == "resellerclubrcm" || $registrar == "netearthonercm" || $registrar == "stargatercm" || $registrar == "resellercamprcm") {
                    $registrarsupport = "true";
                }
                if ($registrar == "resellerclubrcm") {
                    $registrar = "resellerclub";
                }
                if ($registrar == "netearthonercm") {
                    $registrar = "netearthone";
                }
                if ($registrar == "stargatercm") {
                    $registrar = "stargate";
                }
                if ($registrar == "resellercamprcm") {
                    $registrar = "resellercamp";
                }
                if ($registrar == $vars["first_domainregistrar"]) {
                    $rcauth_userid = $vars["first_rcauth_userid"];
                    $rcauth_password = $vars["first_rcauth_apikey"];
                } else if ($registrar == $vars["second_domainregistrar"]) {
                    $rcauth_userid = $vars["second_rcauth_userid"];
                    $rcauth_password = $vars["second_rcauth_apikey"];
                } else if ($registrar == $vars["third_domainregistrar"]) {
                    $rcauth_userid = $vars["third_rcauth_userid"];
                    $rcauth_password = $vars["third_rcauth_apikey"];
                } else if ($registrar == $vars["fourth_domainregistrar"]) {
                    $rcauth_userid = $vars["fourth_rcauth_userid"];
                    $rcauth_password = $vars["fourth_rcauth_apikey"];
                }
                if ($isuserid != $currentuserid) {
                    exit("Unauthorized Access Attempt");
                }
                if ($domain != $postdomain) {
                    exit("Unauthorized Access Attempt");
                }
                if ($domainid != $postdomainid) {
                    exit("Unauthorized Access Attempt");
                }
                Menu::addContext("domain", WHMCS\Domain\Domain::find($domainid));
                $primarySidebar = Menu::primarySidebar("domainView");
                Menu::addContext("domain", WHMCS\Domain\Domain::find($domainid));
                $secondarySidebar = Menu::secondarySidebar("domainView");
                $islang = isset($_SESSION["Language"]) ? $_SESSION["Language"] : $CONFIG["Language"];
                $langstring = ucfirst($islang);
                $langdir = ROOTDIR . "/modules/addons/resellerclubmods_core/modlang/";
                $langdir_override = ROOTDIR . "/modules/addons/resellerclubmods_core/modlang/override/";
                $langclient_rcdns = "_client_rcdns.php";
                $langclient_rcmail = "_client_rcmail.php";
                if (file_exists($langdir . $langstring . $langclient_rcdns)) {
                    include $langdir . $langstring . $langclient_rcdns;
                } else {
                    include $langdir . "English_client_rcdns.php";
                }
                if (file_exists($langdir_override . $langstring . $langclient_rcdns)) {
                    include $langdir_override . $langstring . $langclient_rcdns;
                }
                if (file_exists($langdir . $langstring . $langclient_rcmail)) {
                    include $langdir . $langstring . $langclient_rcmail;
                } else {
                    include $langdir . "English_client_rcdns.php";
                }
                if (file_exists($langdir_override . $langstring . $langclient_rcmail)) {
                    include $langdir_override . $langstring . $langclient_rcmail;
                }
                $systemurl = !empty($CONFIG["SystemSSLURL"]) ? $CONFIG["SystemSSLURL"] : $CONFIG["SystemURL"];
                $data = Illuminate\Database\Capsule\Manager::table("tbldomains")->where("id", "=", $domainid)->select()->get();
                $isregistrar = $data[0]->registrar;
                $isstatus = $data[0]->status;
                $isdnsmanagement = $data[0]->dnsmanagement;
                $isemailforwarding = $data[0]->emailforwarding;
                $isregistrar_array = ["resellerclubrcm", "netearthonercm", "resellercamprcm", "stargatercm", "resellerclub", "netearthone", "resellercamp", "stargate"];
                if (isset($rcm_lb_registrars)) {
                    $isregistrar_array = array_merge($isregistrar_array, $rcm_lb_registrars);
                }
                if ($isstatus == "Active") {
                    if ($_REQUEST["m"] == "resellerclubmods_tools" && !is_null($primarySidebar->getChild("Domain Details Management"))) {
                        $primarySidebar->getChild("Domain Details Management")->addChild("Move Domain", ["label" => $_LANG["clientareamovetitle"], "uri" => "index.php?m=resellerclubmods_tools&action=movedomain&domain=" . $domain . "&domainid=" . $domainid, "order" => 120]);
                        if ($_REQUEST["action"] == "movedomain") {
                            $primarySidebar->getChild("Domain Details Management")->getChild("Move Domain")->setClass("active");
                        }
                    }
                    if (file_exists(ROOTDIR . "/dnsmanagement.php") && in_array($isregistrar, $isregistrar_array) && !empty($isdnsmanagement) && !is_null($primarySidebar->getChild("Domain Details Management"))) {
                        $primarySidebar->getChild("Domain Details Management")->getChild("Manage DNS Host Records")->setUri("dnsmanagement.php?action=managednszone&domain=" . $domain . "&domainid=" . $domainid);
                        $primarySidebar->getChild("Domain Details Management")->addChild("Domain Forwarding", ["label" => $_LANG["rcdns_domainfwd"], "uri" => "domainforwarding.php?action=managedomfwd&domain=" . $domain . "&domainid=" . $domainid, "order" => 110]);
                        if (App::getCurrentFilename() == "dnsmanagement") {
                            $primarySidebar->getChild("Domain Details Management")->getChild("Manage DNS Host Records")->setClass("active");
                        }
                        if (App::getCurrentFilename() == "domainforwarding") {
                            $primarySidebar->getChild("Domain Details Management")->getChild("Domain Forwarding")->setClass("active");
                        }
                    }
                    if (file_exists(ROOTDIR . "/emailmanagement.php") && in_array($isregistrar, $isregistrar_array) && !empty($isemailforwarding) && !is_null($primarySidebar->getChild("Domain Details Management"))) {
                        $primarySidebar->getChild("Domain Details Management")->getChild("Manage Email Forwarding")->setUri("emailmanagement.php?action=managemailhosting&domain=" . $domain . "&domainid=" . $domainid)->setLabel($_LANG["rcmail_managemailhosting"]);
                        if (App::getCurrentFilename() == "emailmanagement") {
                            $primarySidebar->getChild("Domain Details Management")->getChild("Manage Email Forwarding")->setClass("active");
                        }
                    }
                    if (file_exists(ROOTDIR . "/domainmanagement.php") && in_array($isregistrar, $isregistrar_array)) {
                        foreach (Illuminate\Database\Capsule\Manager::table("tbladdonmodules")->where("module", "resellerclubmods_core")->get() as $addonvars) {
                            $corevars[$addonvars->setting] = $addonvars->value;
                        }
                        $is_tld = strstr($domain, ".");
                        $is_tld = str_replace(".", "", $is_tld);
                        $is_dnssec = isset($corevars["rcdom_dnssec"]) ? $corevars["rcdom_dnssec"] : "";
                        $is_dnssec_tlds = isset($corevars["rcdom_dnssectlds"]) ? $corevars["rcdom_dnssectlds"] : "";
                        $is_dnssectld = 0;
                        if (!empty($is_dnssec_tlds)) {
                            $is_dnssec_tlds_array = explode(",", $is_dnssec_tlds);
                            if (in_array($is_tld, $is_dnssec_tlds_array)) {
                                $is_dnssectld = 1;
                            }
                        }
                        if (!is_null($primarySidebar->getChild("Domain Details Management"))) {
                            $primarySidebar->getChild("Domain Details Management")->getChild("Domain Contacts")->setUri("domainmanagement.php?action=domaincontacts&domain=" . $domain . "&domainid=" . $domainid);
                            $primarySidebar->getChild("Domain Details Management")->getChild("Manage Private Nameservers")->setUri("domainmanagement.php?action=childns&domain=" . $domain . "&domainid=" . $domainid);
                            if ($is_dnssec != "on" && $is_dnssectld == 1) {
                                $primarySidebar->getChild("Domain Details Management")->addChild("DNSSEC Management", ["label" => $_LANG["rcdom_dnssecsidebartitle"], "uri" => "domainmanagement.php?action=managednssec&domain=" . $domain . "&domainid=" . $domainid, "order" => 100]);
                            }
                        }
                    }
                }
                $is_allowed = 1;
                if (!empty($cid)) {
                    $is_allowed = 0;
                    $result_perm = Illuminate\Database\Capsule\Manager::table("tblcontacts")->where("userid", "=", $currentuserid)->where("id", "=", $cid)->select()->get();
                    $subaccount = $result_perm[0]->subaccount;
                    $permissions = $result_perm[0]->permissions;
                    if ($subaccount == 1) {
                        $permissions = explode(",", $permissions);
                        if (in_array("managedomains", $permissions)) {
                            $is_allowed = 1;
                        }
                    }
                }
                if ($is_allowed != 1) {
                    return ["pagetitle" => $LANG["movedomaintitle"], "breadcrumb" => ["clientarea.php?action=domains" => $_LANG["clientareanavdomains"], "clientarea.php?action=domaindetails&id=" . $domainid . "" => $domain, "# \"onclick=\"return false;\"" => $LANG["movedomaintitle"]], "templatefile" => "movedomain", "forcessl" => true, "requirelogin" => true, "vars" => ["RCMLANG" => $LANG, "domain" => $domain, "domainid" => $domainid, "validate_error" => $validate_error, "validate_success" => $validate_success, "datavalidated" => $datavalidated, "checked_email" => $checked_email, "move_error" => $move_error, "move_success" => $move_success, "rcmthemestyle" => $themestyle, "subaccount_deny" => $is_allowed, "allowedpermissions" => (array) $permissions]];
                }
                $result = Illuminate\Database\Capsule\Manager::table("tblclients")->where("id", "=", $isuserid)->select("email")->get();
                $rc_user_email = $result[0]->email;
                $method = "GET";
                $apifunction = "/api/customers/details.json";
                $data = ["username" => $rc_user_email];
                $rc_customerid = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
                $existing_customerid = $rc_customerid["customerid"];
                $tblproducts = [];
                foreach (Illuminate\Database\Capsule\Manager::table("tblhosting")->where("domain", $domain)->where("userid", $currentuserid)->select("id", "packageid", "domain")->get() as $data) {
                    $tblproducts[] = ["tldproduct" => $data->domain, "id" => $data->id, "packageid" => $data->packageid];
                }
                $productnames = [];
                if (isset($tblproducts[0])) {
                    $num = 0;
                    foreach ($tblproducts as $products) {
                        $packageid = $products["packageid"];
                        $productdomain = $products["tldproduct"];
                        $productid = $products["id"];
                        $result = Illuminate\Database\Capsule\Manager::table("tblproducts")->where("id", "=", $packageid)->select("name", "configoption1", "gid")->get();
                        $productname = $result[0]->name;
                        $productgroup = $result[0]->gid;
                        $product_registrar = $result[0]->configoption1;
                        $result1 = Illuminate\Database\Capsule\Manager::table("tblproductgroups")->where("id", "=", $productgroup)->select("name")->get();
                        $groupname = $result1[0]->name;
                        if ($product_registrar == "resellerclub" || $product_registrar == "netearthone" || $product_registrar == "resellercamp") {
                            $product_ids[] = $productid;
                            $productnames[] = $productname . " (" . $groupname . ")";
                        }
                    }
                }
                if (isset($_REQUEST["validateemail"]) && $_REQUEST["validateemail"] == "true") {
                    $posted_email = $_REQUEST["newcustomer"];
                    $result = Illuminate\Database\Capsule\Manager::table("tblclients")->where("email", "=", $posted_email)->select("email", "id")->get();
                    $checked_email = $result[0]->email;
                    $newcustomer_id = $result[0]->id;
                    if (empty($checked_email)) {
                        $validate_error = $LANG["validerror01"];
                    } else if ($newcustomer_id == $currentuserid) {
                        $validate_error = $LANG["validerror02"];
                    } else {
                        $validate_success = $LANG["validsuccess"];
                        $datavalidated = "true";
                    }
                }
                if (isset($_REQUEST["domove"]) && $_REQUEST["domove"] == "true") {
                    $movedomain = $_REQUEST["domain"];
                    $movedomainid = $_REQUEST["domainid"];
                    $newcustomer = $_REQUEST["newcustomer"];
                    $contactdetails = $_REQUEST["contact"];
                    $datavalidated = $_REQUEST["datavalidated"];
                    $result = Illuminate\Database\Capsule\Manager::table("tblclients")->where("email", "=", $newcustomer)->select("id")->get();
                    $newcustomer_id = $result[0]->id;
                    if (isset($_REQUEST["confirmed"]) && $_REQUEST["confirmed"] == "yes") {
                        $method = "GET";
                        $apifunction = "/api/customers/details.json";
                        $data = ["username" => $newcustomer];
                        $newarrXml = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
                        if ($newarrXml["status"] == "ERROR") {
                            $whmcscupwd = "";
                            $newrccustomerid = createOBcustomer($newcustomer, $rchttp_api, $rcauth_userid, $rcauth_password, $whmcscupwd);
                            if ($signup_rc_customer_id["status"] == "ERROR") {
                                $errormessage = $LANG["custcreaterr"] . " - " . $signup_rc_customer_id["message"];
                            }
                        } else {
                            $newrccustomerid = $newarrXml["customerid"];
                        }
                        $method = "POST";
                        $apifunction = "/api/products/move.json";
                        $data = ["domain-name" => $IDN->encode($movedomain), "existing-customer-id" => $existing_customerid, "new-customer-id" => $newrccustomerid, "default-contact" => $contactdetails];
                        $moveXml = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
                        $action = "clientarea move domain";
                        $requeststring = $apifunction . " [reseller data protected] " . serialize_data($data);
                        $responsedata = ["rcmdebug" => $debug_addinfo, "apidebug" => $moveXml];
                        logModuleCall($modulename, $action, $requeststring, $responsedata);
                        if ($_SESSION["adminloggedinstatus"] == "true") {
                            $adminmove = "WHMCS Admin on Behalf of";
                        }
                        if (!isset($errormessage)) {
                            if ($moveXml["status"] == "ERROR") {
                                $move_error = $moveXml["message"] . " - " . $LANG["contactsupport"];
                                logActivity($adminmove . " User ID " . $currentuserid . " tried to move the Domain " . $movedomain . " to User ID " . $newcustomer_id . ". Move Error: " . $moveXml["message"]);
                            } else {
                                $update = ["userid" => $newcustomer_id];
                                $result_update = Illuminate\Database\Capsule\Manager::table("tbldomains")->where("id", "=", $movedomainid)->update($update);
                                $command = "updateclientdomain";
                                foreach ($recurringprice_array as $thedomain => $recurringamount) {
                                    $values = ["id" => $movedomainid, "recalc" => 1];
                                    $apiresults = localAPI($command, $values, $maileradmin);
                                }
                                if (is_array($product_ids)) {
                                    foreach ($product_ids as $keyid) {
                                        $result_update = Illuminate\Database\Capsule\Manager::table("tblhosting")->where("id", "=", $keyid)->update($update);
                                    }
                                }
                                $move_success = $LANG["movesuccessfully"];
                                logActivity($adminmove . " User ID " . $currentuserid . " moved successfully the Domain " . $movedomain . " to User ID " . $newcustomer_id);
                            }
                        } else {
                            $move_error = $errormessage;
                            logActivity($adminmove . " User ID " . $currentuserid . " tried to move the Domain " . $movedomain . " to User ID " . $newcustomer_id . ". Reseller Customer Create Error: " . $signup_rc_customer_id["message"]);
                        }
                    } else {
                        $move_error = $LANG["noconfirmed"];
                    }
                }
            }
            return ["pagetitle" => $LANG["movedomaintitle"], "breadcrumb" => ["clientarea.php?action=domains" => $_LANG["clientareanavdomains"], "clientarea.php?action=domaindetails&id=" . $domainid . "" => $domain, "# \"onclick=\"return false;\"" => $LANG["movedomaintitle"]], "templatefile" => "movedomain", "forcessl" => true, "requirelogin" => true, "vars" => ["RCMLANG" => $LANG, "registrar" => $registrar, "domain" => $domain, "domainid" => $domainid, "validate_error" => $validate_error, "validate_success" => $validate_success, "datavalidated" => $datavalidated, "checked_email" => $checked_email, "move_error" => $move_error, "move_success" => $move_success, "registrarsupport" => $registrarsupport, "productnames" => implode("<br />", $productnames), "accessdenied" => $accessdenied, "newrccustomerid" => $newrccustomerid, "rcmthemestyle" => $themestyle]];
        }
    } else if (isset($_REQUEST["action"]) && $_REQUEST["action"] == "suggestdomain") {
        if ($domainbot_loggedin == "on") {
            if (!isset($currentuserid)) {
                $requirelogin = true;
            }
        } else {
            $requirelogin = false;
        }
        if (empty($domainbot_extqty)) {
            $domainbot_extqty = 6;
        }
        $do_forcessl = true;
        if ($domainbot_nossl == "on") {
            $do_forcessl = false;
        }
        if (isset($_REQUEST["suggest"]) && !empty($_REQUEST["keyword"]) && isset($_REQUEST["tlds"])) {
            $suggestresults = "true";
            $keyword = utf8_encode($_REQUEST["keyword"]);
            $rctlds = array_slice($_REQUEST["tlds"], 0, $domainbot_extqty);
            sort($rctlds);
            $rcauth_userid = $vars["first_rcauth_userid"];
            $rcauth_password = $vars["first_rcauth_apikey"];
            $method = "GET";
            $apifunction = "/api/domains/v5/suggest-names.json";
            $data = ["keyword" => $keyword, "tld-only" => $rctlds, "exact-match" => "false"];
            $xml_suggestnames_array = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
            if (!empty($xml_suggestnames_array)) {
                $suggestarray[0] = $rctlds;
                $suggestarray[1] = [];
                foreach ($xml_suggestnames_array as $key => $values) {
                    ksort($values);
                    $domname = strstr($key, ".", true);
                    $domtld = strstr($key, ".");
                    $domtld = str_replace(".", "", $domtld);
                    $status = $values["status"];
                    $suggestarray[1][$domname][$domtld] = $status;
                    foreach ($rctlds as $searched_tlds) {
                        if (!array_key_exists($searched_tlds, $suggestarray[1][$domname])) {
                            $suggestarray[1][$domname][$searched_tlds] = "na";
                        }
                    }
                    ksort($suggestarray[1][$domname]);
                }
                $suggestarray[2] = count($suggestarray[0]);
            }
            if (empty($suggestarray[1])) {
                $noresults = $LANG["noresults"];
            }
        }
        return ["pagetitle" => $LANG["suggestdomaintitle"], "breadcrumb" => ["cart.php" => $_LANG["carttitle"], "# \"onclick=\"return false;\"" => $LANG["suggestdomaintitle"]], "templatefile" => "suggestdomain", "forcessl" => $do_forcessl, "requirelogin" => $requirelogin, "vars" => ["RCMLANG" => $LANG, "suggestresults" => $suggestresults, "noresults" => $noresults, "suggestarray" => $suggestarray, "domainbot_extqty" => $domainbot_extqty, "domainbot_tlds" => $domainbot_tlds, "domainbot_yrs" => $domainbot_yrs, "rcmthemestyle" => $themestyle]];
    }
}

?>