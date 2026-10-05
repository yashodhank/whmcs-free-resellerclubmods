<?php
if (!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
if (!isset($_SESSION["rcm_domainimport_numqty"])) {
    $_SESSION["rcm_domainimport_numqty"] = 20;
}
if (!isset($_SESSION["rcm_domainimport_impqty"])) {
    $_SESSION["rcm_domainimport_impqty"] = 20;
}
if (!isset($_SESSION["rcm_domainimport_onlyimportable"])) {
    $_SESSION["rcm_domainimport_onlyimportable"] = 0;
}
if (isset($_REQUEST["numqty"]) && in_array($_REQUEST["numqty"], ["20", "50", "100", "500"], true)) {
    $_SESSION["rcm_domainimport_numqty"] = (int) $_REQUEST["numqty"];
}
if (isset($_REQUEST["impqty"]) && in_array($_REQUEST["impqty"], ["20", "50", "100", "500"], true)) {
    $_SESSION["rcm_domainimport_impqty"] = (int) $_REQUEST["impqty"];
}
if (isset($_REQUEST["onlyimportable"]) && $_REQUEST["onlyimportable"] == "1") {
    $_SESSION["rcm_domainimport_onlyimportable"] = 1;
} else if (isset($_REQUEST["searchmail"]) || isset($_REQUEST["alluser"])) {
    $_SESSION["rcm_domainimport_onlyimportable"] = 0;
}
$numqty = (int) $_SESSION["rcm_domainimport_numqty"];
$impqty = (int) $_SESSION["rcm_domainimport_impqty"];
$onlyImportable = $_SESSION["rcm_domainimport_onlyimportable"] == 1;
$allowedPerPage = [20, 50, 100, 500];
if (!in_array($numqty, $allowedPerPage, true)) {
    $numqty = 20;
    $_SESSION["rcm_domainimport_numqty"] = 20;
}
if (!in_array($impqty, $allowedPerPage, true)) {
    $impqty = 20;
    $_SESSION["rcm_domainimport_impqty"] = 20;
}
$a_checked = $b_checked = $c_checked = $d_checked = "";
switch ($numqty) {
    case 50:
        $b_checked = "checked";
        break;
    case 100:
        $c_checked = "checked";
        break;
    case 500:
        $d_checked = "checked";
        break;
    default:
        $a_checked = "checked";
        $numqty = 20;
        $_SESSION["rcm_domainimport_numqty"] = 20;
        $imp20_checked = $imp50_checked = $imp100_checked = $imp500_checked = "";
        switch ($impqty) {
            case 50:
                $imp50_checked = "checked";
                break;
            case 100:
                $imp100_checked = "checked";
                break;
            case 500:
                $imp500_checked = "checked";
                break;
            default:
                $imp20_checked = "checked";
                $impqty = 20;
                $_SESSION["rcm_domainimport_impqty"] = 20;
                $_GET["numqty"] = $numqty;
                $_GET["impqty"] = $impqty;
                $result = Illuminate\Database\Capsule\Manager::table("tblpaymentgateways")->where("setting", "=", "name")->where("order", "=", "1")->select("gateway")->get();
                $paymentmethod = isset($result[0]) && isset($result[0]->gateway) ? $result[0]->gateway : "";
                if (isset($_POST["domainimport"]) && $_POST["domainimport"] == "true") {
                    $start = microtime(true);
                    $rccustomerid = $_POST["rcuserid"];
                    $whmcsuserid = $_POST["whmcsuserid"];
                    $domainstrings = $_POST["domains"];
                    $domainarray = explode(",", $domainstrings);
                    $result = Illuminate\Database\Capsule\Manager::table("tblclients")->where("id", "=", $whmcsuserid)->select("currency", "groupid")->get();
                    $whmcsusercurrency = isset($result[0]) && isset($result[0]->currency) ? $result[0]->currency : 1;
                    $groupid_row = isset($result[0]) && isset($result[0]->groupid) ? $result[0]->groupid : "0";
                    $tsetupfee = $groupid_row . ".00";
                    $tldextensions = [];
                    foreach (Illuminate\Database\Capsule\Manager::table("tbldomainpricing")->select("extension")->get() as $fila_tlddata) {
                        array_push($tldextensions, $fila_tlddata->extension);
                    }
                    $domainaddons_row = Illuminate\Database\Capsule\Manager::table("tblpricing")->where("relid", 0)->where("type", "domainaddons")->where("currency", $whmcsusercurrency)->select("msetupfee", "qsetupfee", "ssetupfee")->first();
                    $dnsmanagement_price = $domainaddons_row ? (float) $domainaddons_row->msetupfee : 0;
                    $emailforwarding_price = $domainaddons_row ? (float) $domainaddons_row->qsetupfee : 0;
                    $idprotection_price = $domainaddons_row ? (float) $domainaddons_row->ssetupfee : 0;
                    echo $configuredto;
                    echo "<h3>" . $LANG["domainimportresult"] . "</h3>";
                    echo "<table bgcolor=\"#ffffff\" cellspacing=\"1\" width=\"100%\"><tr><td>";
                    foreach ($domainarray as $domainvalue) {
                        $method = "GET";
                        $apifunction = "/api/domains/search.json";
                        $data = ["customer-id" => $rccustomerid, "status" => "Active", "domain-name" => $IDN->encode($domainvalue), "no-of-records" => 500, "page-no" => 1];
                        $domsearchXml = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
                        foreach ($domsearchXml as $domdetails) {
                            if (is_array($domdetails)) {
                                $expdate = $domdetails["orders.endtime"];
                                $rcorderid = $domdetails["orders.orderid"];
                                $regdate = $domdetails["orders.creationdt"];
                                $idprotection = $domdetails["orders.privacyprotection"];
                                if ($idprotection == "true") {
                                    $idprotection = 1;
                                } else {
                                    $idprotection = 0;
                                    $idprotection_price = "0.00";
                                }
                                $domnameservers = "";
                                $IDN->decode($domainvalue);
                                $pos = strpos($domainvalue, ".");
                                $istld = substr($domainvalue, $pos, strlen($domainvalue) - $pos);
                                $whmcsuserid = $_POST["whmcsuserid"];
                                $id_tbldomains = "";
                                $type_tbldomains = "Register";
                                $registrationperiod = 1;
                                $registrationdate = date("Y-m-d H:i:s", $regdate);
                                $tbldomains_registrationdate = date("Y-m-d", $regdate);
                                $expiredate = date("Y-m-d", $expdate);
                                $nextduedate = $expiredate;
                                $status = "Active";
                                $extension = $istld;
                                $fila_tbldomainpricing = Illuminate\Database\Capsule\Manager::table("tbldomainpricing")->where("extension", "=", $extension)->get();
                                $dnsmanagement = isset($fila_tbldomainpricing[0]) && isset($fila_tbldomainpricing[0]->dnsmanagement) ? $fila_tbldomainpricing[0]->dnsmanagement : 0;
                                if ($dnsmanagement == "on" || $dnsmanagement == 1) {
                                    $dnsmanagement = 1;
                                } else {
                                    $dnsmanagement = 0;
                                    $dnsmanagement_price = "0.00";
                                }
                                $emailforwarding = isset($fila_tbldomainpricing[0]) && isset($fila_tbldomainpricing[0]->emailforwarding) ? $fila_tbldomainpricing[0]->emailforwarding : "";
                                if ($emailforwarding == "on" || $emailforwarding == 1) {
                                    $emailforwarding = 1;
                                } else {
                                    $emailforwarding = 0;
                                    $emailforwarding_price = "0.00";
                                }
                                $registrar = $logicbox_registrar;
                                if (in_array($logicbox_registrar . "rcm", $tblregistrars)) {
                                    $registrar = $logicbox_registrar . "rcm";
                                }
                                $id_tbldomainpricing = isset($fila_tbldomainpricing[0]) && isset($fila_tbldomainpricing[0]->id) ? $fila_tbldomainpricing[0]->id : "";
                                $type_register_tblpricing = "domainregister";
                                $type_renew_tblpricing = "domainrenew";
                                $fields = "msetupfee";
                                $fila_tblpricing_register = Illuminate\Database\Capsule\Manager::table("tblpricing")->where("relid", "=", $id_tbldomainpricing)->where("type", "=", $type_register_tblpricing)->where("currency", "=", $whmcsusercurrency)->where("tsetupfee", "=", $tsetupfee)->select($fields)->get();
                                if (empty($fila_tblpricing_register) || !isset($fila_tblpricing_register[0]) || empty($fila_tblpricing_register[0]->tsetupfee)) {
                                    $fila_tblpricing_register = Illuminate\Database\Capsule\Manager::table("tblpricing")->where("relid", "=", $id_tbldomainpricing)->where("type", "=", $type_register_tblpricing)->where("currency", "=", $whmcsusercurrency)->select("msetupfee")->get();
                                }
                                $firstpaymentamount = $fila_tblpricing_register[0]->msetupfee + $dnsmanagement_price + $emailforwarding_price + $idprotection_price;
                                $fila_tblpricing_renew = Illuminate\Database\Capsule\Manager::table("tblpricing")->where("relid", "=", $id_tbldomainpricing)->where("type", "=", $type_renew_tblpricing)->where("currency", "=", $whmcsusercurrency)->where("tsetupfee", "=", $tsetupfee)->select($fields)->get();
                                if (empty($fila_tblpricing_renew) || !isset($fila_tblpricing_renew[0]) || empty($fila_tblpricing_renew[0]->tsetupfee)) {
                                    $fila_tblpricing_renew = Illuminate\Database\Capsule\Manager::table("tblpricing")->where("relid", "=", $id_tbldomainpricing)->where("type", "=", $type_renew_tblpricing)->where("currency", "=", $whmcsusercurrency)->select("msetupfee")->get();
                                }
                                $recurringamount = $fila_tblpricing_renew[0]->msetupfee + $dnsmanagement_price + $emailforwarding_price + $idprotection_price;
                                $id_tblorders = "";
                                $num1 = rand(10000, 99999);
                                $num2 = rand(10000, 99999);
                                $ordernum = $num1 . $num2;
                                $contactid = 0;
                                $orderdate = $registrationdate;
                                $invoiceid = 0;
                                $orderstatus = "Active";
                                $notes = $LANG["importedfrom"] . " " . $registrar_label . " " . $LANG["atimported"] . " " . date("d-m-Y H:i:s");
                                $domainExists = Illuminate\Database\Capsule\Manager::table("tbldomains")->where("domain", $domainvalue)->exists();
                                if (in_array($istld, $tldextensions)) {
                                    if (!$domainExists) {
                                        $conn = Illuminate\Database\Capsule\Manager::connection();
                                        try {
                                            $conn->beginTransaction();
                                            echo "<p><u><strong>" . $domainvalue . " - " . $domnumber++ . "#</strong></u></p>";
                                            echo $LANG["inserttblorders"] . "<br />";
                                            $values = ["id" => $id_tblorders, "ordernum" => $ordernum, "userid" => $whmcsuserid, "contactid" => $contactid, "date" => $orderdate, "nameservers" => $domnameservers, "amount" => $firstpaymentamount, "paymentmethod" => $paymentmethod, "invoiceid" => $invoiceid, "status" => $orderstatus, "notes" => $notes];
                                            $tblorders_result = Illuminate\Database\Capsule\Manager::table("tblorders")->insert($values);
                                            if ($tblorders_result) {
                                                echo "<span style=\"color:#0D6306;\">" . $LANG["inserttblorderssuccess"] . " " . $domainvalue . "</span><br />";
                                                echo "<textarea class=\"form-control\" style=\"display: inline; width: 100%\" rows=\"1\" readonly=\"readonly\">ordernum = " . $ordernum . ", userid = " . $whmcsuserid . ", date = " . $orderdate . ", amount = " . $firstpaymentamount . ", paymentmethod = " . $paymentmethod . "</textarea><br />";
                                            } else {
                                                echo "<span style=\"color:#CC0000;\">" . $LANG["inserttblorderserror"] . " " . $domainvalue . "</span><br />";
                                            }
                                            $fila_tblorders = Illuminate\Database\Capsule\Manager::table("tblorders")->where("ordernum", "=", $ordernum)->select("id")->get();
                                            $orderid = isset($fila_tblorders[0]) && isset($fila_tblorders[0]->id) ? $fila_tblorders[0]->id : 0;
                                            $tbldomain_notes = "orderid " . $orderid . " - " . $LANG["importedfrom"] . " " . $registrar_label . " " . $LANG["atimported"] . " " . date("d-m-Y H:i:s");
                                            echo "Insert in tbldomains ...<br />";
                                            $values = ["id" => $id_tbldomains, "userid" => $whmcsuserid, "orderid" => $orderid, "type" => $type_tbldomains, "registrationdate" => $tbldomains_registrationdate, "domain" => $domainvalue, "firstpaymentamount" => $firstpaymentamount, "recurringamount" => $recurringamount, "registrar" => $registrar, "registrationperiod" => $registrationperiod, "expirydate" => $expiredate, "promoid" => "0", "status" => $status, "nextduedate" => $nextduedate, "nextinvoicedate" => $nextduedate, "additionalnotes" => $tbldomain_notes, "paymentmethod" => $paymentmethod, "dnsmanagement" => $dnsmanagement, "emailforwarding" => $emailforwarding, "idprotection" => $idprotection];
                                            $tbldomains_result = Illuminate\Database\Capsule\Manager::table("tbldomains")->insert($values);
                                            if ($tbldomains_result) {
                                                echo "<span style=\"color:#0D6306;\">" . $LANG["inserttbldomainssuccess"] . " " . $domainvalue . "</span><br />";
                                                echo "<textarea class=\"form-control\" style=\"display: inline; width: 100%\" rows=\"1\" readonly=\"readonly\">orderid = " . $orderid . ", userid = " . $whmcsuserid . ", registrationdate = " . $tbldomains_registrationdate . ", registrar = " . $registrar_label . ", expirydate = " . $expiredate . ", nextduedate = " . $nextduedate . "</textarea><br /><hr /><br>";
                                            } else {
                                                echo "<span style=\"color:#CC0000;\">" . $LANG["inserttbldomainserror"] . " " . $domainvalue . "</span><br /><br />";
                                            }
                                            $conn->commit();
                                        } catch (Throwable $e) {
                                            $conn->rollBack();
                                            echo "<span style=\"color:#CC0000;\">Database error while importing " . $domainvalue . ": " . $e->getMessage() . "</span><br />";
                                            logActivity("RCM Domain Import (manual) DB error for " . $domainvalue . ": " . $e->getMessage());
                                        }
                                    } else {
                                        echo "<span style=\"color:#043ED1;\">" . $LANG["duplicatedomainentry"] . " " . $domainvalue . "</span><br /><br />";
                                    }
                                } else {
                                    echo "<span style=\"color:#F79E04;\"><strong>&quot;" . strtoupper($istld) . "&quot;</strong> " . $LANG["inserttbldomainskipped"] . " " . $domainvalue . "</span><br /><br />";
                                }
                            }
                        }
                    }
                    echo "</td></tr></table><br />";
                    $totaldomains = $domnumber - 1;
                    if ($totaldomains == 1) {
                        $domainword = $LANG["domainword"];
                    } else {
                        $domainword = $LANG["domainsword"];
                    }
                    $totalProcessed = isset($domainarray) && is_array($domainarray) ? count($domainarray) : 0;
                    $totalSkipped = max(0, $totalProcessed - $totaldomains);
                    $finish = microtime(true);
                    $total_time = round($finish - $start, 4);
                    echo "<strong>" . $totaldomains . "</strong> " . $domainword . " " . $LANG["successfullyimportedin"] . " " . $total_time . " " . $LANG["seconds"] . "<br />";
                    echo "<span style=\"color:#666;font-size:11px;\">Processed: " . (int) $totalProcessed . " &nbsp;|&nbsp; Imported: " . (int) $totaldomains . " &nbsp;|&nbsp; Skipped: " . (int) $totalSkipped . "</span><br />";
                    logActivity("RCM Domain Import (manual): Processed=" . (int) $totalProcessed . ", Imported=" . (int) $totaldomains . ", Skipped=" . (int) $totalSkipped . ", Time=" . $total_time . "s");
                    echo "<br /><h3><a href=\"" . $modulelink . "&domain=domainimport\">" . $LANG["domaintoolbacklink"] . "</a></h3>";
                } else if (isset($_POST["customerids"]) && !empty($_POST["customerids"])) {
                    echo $configuredto;
                    $start = microtime(true);
                    $rccustids = $_POST["customerids"];
                    $checkifcomma_atend = substr($rccustids, -1);
                    if ($checkifcomma_atend == ",") {
                        $rccustids = substr_replace($rccustids, "", -1);
                    }
                    $custarray = explode(",", $rccustids);
                    $recordqty = count($custarray);
                    if (300 < $recordqty) {
                        $custarray = array_chunk($custarray, 300);
                    }
                    $method = "GET";
                    $apifunction = "/api/customers/search.json";
                    $statusarr = ["Active", "Suspended"];
                    if (300 < $recordqty) {
                        $steps = $recordqty / 300;
                        $noofrecords = 300;
                        $stepsresult = ceil($steps);
                        $i = 1;
                        while ($i <= $stepsresult) {
                            $data = ["customer-id" => $custarray, "status" => $statusarr, "no-of-records" => $noofrecords, "page-no" => $i++];
                            $custsearchXml = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
                            $search_array[] = $custsearchXml;
                        }
                    } else {
                        $noofrecords = 300;
                        $pageno = 1;
                        $data = ["customer-id" => $custarray, "status" => $statusarr, "no-of-records" => $noofrecords, "page-no" => $pageno];
                        $custsearchXml = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
                        $search_array[] = $custsearchXml;
                    }
                    $user_recsindb = $custsearchXml["recsindb"];
                    foreach ($search_array as $value) {
                        foreach ($value as $data) {
                            if (is_array($data)) {
                                $data_entcustname = $data["customer.username"];
                                $data_custid = $data["customer.customerid"];
                                $email = strtolower($data_entcustname);
                                $result = Illuminate\Database\Capsule\Manager::table("tblclients")->where("email", "=", $email)->select("id")->get();
                                if (!empty($result[0]->id)) {
                                    $wid = isset($result[0]) && isset($result[0]->id) ? $result[0]->id : "notfound";
                                } else {
                                    $wid = "notfound";
                                }
                                $custdata[$data_custid] = ["username" => $email, "whmcsid" => $wid];
                            }
                        }
                    }
                    $method = "GET";
                    $apifunction = "/api/domains/search.json";
                    $data = ["customer-id" => $custarray, "status" => $statusarr, "no-of-records" => 10, "page-no" => 1];
                    $recsindbsearch = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
                    $recsindb = $recsindbsearch["recsindb"];
                    if (300 < $recsindb) {
                        $steps = $recsindb / 300;
                        $noofrecords = 300;
                        $stepsresult = ceil($steps);
                        $i = 1;
                        while ($i <= $stepsresult) {
                            $data = ["customer-id" => $custarray, "status" => $statusarr, "no-of-records" => $noofrecords, "page-no" => $i++, "order-by" => "orderid"];
                            $domsearchXml = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
                            $domsearch_array[] = $domsearchXml;
                        }
                    } else {
                        $noofrecords = 300;
                        $pageno = 1;
                        $data = ["customer-id" => $custarray, "status" => $statusarr, "no-of-records" => $noofrecords, "page-no" => $pageno, "order-by" => "orderid"];
                        $domsearchXml = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
                        $domsearch_array[] = $domsearchXml;
                    }
                    $registrationperiod = 1;
                    $tldextensions = [];
                    $tldPricingMap = [];
                    foreach (Illuminate\Database\Capsule\Manager::table("tbldomainpricing")->select("id", "extension", "dnsmanagement", "emailforwarding", "idprotection")->get() as $fila_tlddata) {
                        $tldextensions[] = $fila_tlddata->extension;
                        $tldPricingMap[$fila_tlddata->extension] = $fila_tlddata;
                    }
                    $whmcsIds = [];
                    if (!empty($custdata) && is_array($custdata)) {
                        foreach ($custdata as $row) {
                            if (!empty($row["whmcsid"]) && $row["whmcsid"] !== "notfound") {
                                $whmcsIds[] = (int) $row["whmcsid"];
                            }
                        }
                    }
                    $whmcsIds = array_values(array_unique($whmcsIds));
                    $clientCurrency = [];
                    if (!empty($whmcsIds)) {
                        $clientRows = Illuminate\Database\Capsule\Manager::table("tblclients")->whereIn("id", $whmcsIds)->select("id", "currency")->get();
                        foreach ($clientRows as $cRow) {
                            $clientCurrency[$cRow->id] = (int) $cRow->currency;
                        }
                    }
                    $currencyIds = array_values(array_unique(array_values($clientCurrency)));
                    $domainAddonPricing = [];
                    if (!empty($currencyIds)) {
                        $addonRows = Illuminate\Database\Capsule\Manager::table("tblpricing")->where("relid", 0)->where("type", "domainaddons")->whereIn("currency", $currencyIds)->select("currency", "msetupfee", "qsetupfee", "ssetupfee")->get();
                        foreach ($addonRows as $aRow) {
                            $domainAddonPricing[$aRow->currency] = $aRow;
                        }
                    }
                    $domainPriceCacheRegister = [];
                    $domainPriceCacheRenew = [];
                    foreach ($domsearch_array as $value) {
                        foreach ($value as $data) {
                            if (is_array($data)) {
                                $data_custid = $data["entity.customerid"];
                                $data_entorderend = $data["orders.endtime"];
                                $data_entdomname = $IDN->decode($data["entity.description"]);
                                $data_entorderid = $data["orders.orderid"];
                                $data_entordercrdt = $data["orders.creationdt"];
                                $data_entisprivacy = $data["orders.privacyprotection"];
                                if ($data_entisprivacy == "true") {
                                    $is_privacystatus = 1;
                                } else {
                                    $is_privacystatus = 0;
                                    $idprotection_price = "0.00";
                                }
                                foreach ($custdata as $key => $value) {
                                    if ($key == $data_custid) {
                                        if (!empty($custdata[$key]["username"])) {
                                            $username = $custdata[$key]["username"];
                                        } else {
                                            $username = "notfound";
                                        }
                                        if (!empty($custdata[$key]["whmcsid"])) {
                                            $whmcs_id = $custdata[$key]["whmcsid"];
                                        } else {
                                            $whmcs_id = "notfound";
                                        }
                                    }
                                }
                                $domainvalue = $data_entdomname;
                                $pos = strpos($domainvalue, ".");
                                $istld = substr($domainvalue, $pos, strlen($domainvalue) - $pos);
                                if (in_array($istld, $tldextensions)) {
                                    $extension = $istld;
                                    $fila_tbldomainpricing = isset($tldPricingMap[$extension]) ? $tldPricingMap[$extension] : NULL;
                                    if (!$fila_tbldomainpricing) {
                                    } else {
                                        $whmcsusercurrency = isset($clientCurrency[$whmcs_id]) ? $clientCurrency[$whmcs_id] : 1;
                                        $addonRow = isset($domainAddonPricing[$whmcsusercurrency]) ? $domainAddonPricing[$whmcsusercurrency] : NULL;
                                        $dnsmanagement_price = $addonRow ? (float) $addonRow->msetupfee : 0;
                                        $emailforwarding_price = $addonRow ? (float) $addonRow->qsetupfee : 0;
                                        $idprotection_price = $addonRow ? (float) $addonRow->ssetupfee : 0;
                                        $registrar = $logicbox_registrar;
                                        if (in_array($logicbox_registrar . "rcm", $tblregistrars)) {
                                            $registrar = $logicbox_registrar . "rcm";
                                        }
                                        $dnsmanagement = isset($fila_tbldomainpricing->dnsmanagement) ? $fila_tbldomainpricing->dnsmanagement : 0;
                                        if ($dnsmanagement == "on" || $dnsmanagement == 1) {
                                            $dnsmanagement = 1;
                                        } else {
                                            $dnsmanagement = 0;
                                            $dnsmanagement_price = 0;
                                        }
                                        $emailforwarding = isset($fila_tbldomainpricing->emailforwarding) ? $fila_tbldomainpricing->emailforwarding : 0;
                                        if ($emailforwarding == "on" || $emailforwarding == 1) {
                                            $emailforwarding = 1;
                                        } else {
                                            $emailforwarding = 0;
                                            $emailforwarding_price = 0;
                                        }
                                        $is_privacystatus = (int) $is_privacystatus;
                                        $id_tbldomainpricing = isset($fila_tbldomainpricing->id) ? (int) $fila_tbldomainpricing->id : 0;
                                        $type_register_tblpricing = "domainregister";
                                        $fields = "msetupfee";
                                        $registerKey = $id_tbldomainpricing . "|" . $whmcsusercurrency . "|" . $tsetupfee;
                                        if (!isset($domainPriceCacheRegister[$registerKey])) {
                                            $rows = Illuminate\Database\Capsule\Manager::table("tblpricing")->where("relid", "=", $id_tbldomainpricing)->where("type", "=", $type_register_tblpricing)->where("currency", "=", $whmcsusercurrency)->where("tsetupfee", "=", $tsetupfee)->select($fields)->get();
                                            if (empty($rows) || !isset($rows[0]) || empty($rows[0]->tsetupfee)) {
                                                $rows = Illuminate\Database\Capsule\Manager::table("tblpricing")->where("relid", "=", $id_tbldomainpricing)->where("type", "=", $type_register_tblpricing)->where("currency", "=", $whmcsusercurrency)->select("msetupfee")->get();
                                            }
                                            $domainPriceCacheRegister[$registerKey] = $rows;
                                        }
                                        $fila_tblpricing_register = $domainPriceCacheRegister[$registerKey];
                                        $firstpaymentamount = (float) ($fila_tblpricing_register[0]->msetupfee ?? 0) + (float) $dnsmanagement_price + (float) $emailforwarding_price + (float) $idprotection_price;
                                        $type_renew_tblpricing = "domainrenew";
                                        $renewKey = $id_tbldomainpricing . "|" . $whmcsusercurrency . "|" . $tsetupfee;
                                        if (!isset($domainPriceCacheRenew[$renewKey])) {
                                            $rows = Illuminate\Database\Capsule\Manager::table("tblpricing")->where("relid", "=", $id_tbldomainpricing)->where("type", "=", $type_renew_tblpricing)->where("currency", "=", $whmcsusercurrency)->where("tsetupfee", "=", $tsetupfee)->select($fields)->get();
                                            if (empty($rows) || !isset($rows[0]) || empty($rows[0]->tsetupfee)) {
                                                $rows = Illuminate\Database\Capsule\Manager::table("tblpricing")->where("relid", "=", $id_tbldomainpricing)->where("type", "=", $type_renew_tblpricing)->where("currency", "=", $whmcsusercurrency)->select("msetupfee")->get();
                                            }
                                            $domainPriceCacheRenew[$renewKey] = $rows;
                                        }
                                        $fila_tblpricing_renew = $domainPriceCacheRenew[$renewKey];
                                        $recurringamount = (float) ($fila_tblpricing_renew[0]->msetupfee ?? 0) + (float) $dnsmanagement_price + (float) $emailforwarding_price + (float) $idprotection_price;
                                        $domdata[] = ["registrar" => $registrar, "customerid" => $data_custid, "orderid" => $data_entorderid, "domain" => $data_entdomname, "tld" => $istld, "registrationperiod" => $registrationperiod, "type" => "Register", "status" => "Active", "orderdate" => date("Y-m-d H:i:s", $data_entordercrdt), "registrationdate" => date("Y-m-d", $data_entordercrdt), "expiredate" => date("Y-m-d", $data_entorderend), "nextduedate" => date("Y-m-d", $data_entorderend), "nextinvoicedate" => date("Y-m-d", $data_entorderend), "whmcsusername" => $username, "whmcsid" => $whmcs_id, "paymentmethod" => $paymentmethod, "firstpaymentamount" => $firstpaymentamount, "recurringamount" => $recurringamount, "dnsmanagement" => $dnsmanagement, "emailforwarding" => $emailforwarding, "idprotection" => $is_privacystatus, "nameservers" => "", "contactid" => $contactid, "invoiceid" => $invoiceid];
                                        sort($domdata);
                                    }
                                }
                            }
                        }
                    }
                    if ($user_recsindb == 0) {
                        echo "<p><span style=\"color:#CC0000;\">" . $LANG["nocustomers"] . "</span></p>";
                    } else {
                        $domnumber = 1;
                        echo "<h3><strong>" . count($domdata) . " " . $LANG["founddesc1"] . " " . count($custdata) . " " . $LANG["founddesc2"] . "</strong></h3> ";
                        foreach ($domdata as $domvalues) {
                            $domainvalue = $domvalues["domain"];
                            $whmcsuserid = $domvalues["whmcsid"];
                            $contactid = $domvalues["contactid"];
                            $orderdate = $domvalues["orderdate"];
                            $tbldomains_registrationdate = $domvalues["registrationdate"];
                            $domnameservers = $domvalues["nameservers"];
                            $firstpaymentamount = $domvalues["firstpaymentamount"];
                            $paymentmethod = $domvalues["paymentmethod"];
                            $recurringamount = $domvalues["recurringamount"];
                            $invoiceid = $domvalues["invoiceid"];
                            $orderstatus = $domvalues["status"];
                            $type_tbldomains = $domvalues["type"];
                            $registrar = $domvalues["registrar"];
                            $registrationperiod = $domvalues["registrationperiod"];
                            $expiredate = $domvalues["expiredate"];
                            $nextduedate = $domvalues["nextduedate"];
                            $nextinvoicedate = $domvalues["nextduedate"];
                            $dnsmanagement = $domvalues["dnsmanagement"];
                            $emailforwarding = $domvalues["emailforwarding"];
                            $idprotection = $domvalues["idprotection"];
                            $domainExists = Illuminate\Database\Capsule\Manager::table("tbldomains")->where("domain", $domainvalue)->exists();
                            if (!$domainExists) {
                                if ($whmcsuserid != "notfound") {
                                    $conn = Illuminate\Database\Capsule\Manager::connection();
                                    try {
                                        $conn->beginTransaction();
                                        $num1 = rand(10000, 99999);
                                        $num2 = rand(10000, 99999);
                                        $ordernum = $num1 . $num2;
                                        $contactid = 0;
                                        $invoiceid = 0;
                                        $notes = $LANG["importedfrom"] . " " . $registrar_label . " " . $LANG["atimported"] . " " . date("d-m-Y H:i:s") . " - ordernum = " . $ordernum . ", userid = " . $whmcsuserid;
                                        echo "<p><u><strong>" . $domnumber++ . "# " . $domainvalue . " - " . $LANG["customerid"] . " " . $domvalues["customerid"] . " (" . $domvalues["whmcsusername"] . ")</strong></u></p>";
                                        echo $LANG["inserttblorders"] . "<br />";
                                        $values = ["id" => $id_tblorders, "ordernum" => $ordernum, "userid" => $whmcsuserid, "contactid" => $contactid, "date" => $orderdate, "nameservers" => $domnameservers, "amount" => $firstpaymentamount, "paymentmethod" => $paymentmethod, "invoiceid" => $invoiceid, "status" => $orderstatus, "notes" => $notes];
                                        $tblorders_result = Illuminate\Database\Capsule\Manager::table("tblorders")->insert($values);
                                        if ($tblorders_result) {
                                            echo "<span style=\"color:#0D6306;\">" . $LANG["inserttblorderssuccess"] . " " . $domainvalue . "</span><br />";
                                            echo "<textarea class=\"form-control\" style=\"display: inline; width: 100%\" rows=\"1\" readonly=\"readonly\">ordernum = " . $ordernum . ", userid = " . $whmcsuserid . ", registerdate = " . $orderdate . ", amount = " . $firstpaymentamount . ", paymentmethod = " . $paymentmethod . "</textarea><br />";
                                        } else {
                                            echo "<span style=\"color:#CC0000;\">" . $LANG["inserttblorderserror"] . " " . $domainvalue . "</span><br />";
                                        }
                                        $fila_tblorders = Illuminate\Database\Capsule\Manager::table("tblorders")->where("ordernum", "=", $ordernum)->select("id")->get();
                                        $orderid = isset($fila_tblorders[0]) && isset($fila_tblorders[0]->id) ? $fila_tblorders[0]->id : 0;
                                        $tbldomain_notes = $LANG["importedfrom"] . " " . $registrar_label . " " . $LANG["atimported"] . " " . date("d-m-Y H:i:s") . " - ordernum = " . $ordernum . ", orderid = " . $orderid . ", userid = " . $whmcsuserid;
                                        echo "Insert in tbldomains ...<br />";
                                        $values = ["id" => $id_tbldomains, "userid" => $whmcsuserid, "orderid" => $orderid, "type" => $type_tbldomains, "registrationdate" => $tbldomains_registrationdate, "domain" => $domainvalue, "firstpaymentamount" => $firstpaymentamount, "recurringamount" => $recurringamount, "registrar" => $registrar, "registrationperiod" => $registrationperiod, "expirydate" => $expiredate, "promoid" => "0", "status" => $orderstatus, "nextduedate" => $nextduedate, "nextinvoicedate" => $nextduedate, "additionalnotes" => $tbldomain_notes, "paymentmethod" => $paymentmethod, "dnsmanagement" => $dnsmanagement, "emailforwarding" => $emailforwarding, "idprotection" => $idprotection];
                                        $tbldomains_result = Illuminate\Database\Capsule\Manager::table("tbldomains")->insert($values);
                                        if ($tbldomains_result) {
                                            echo "<span style=\"color:#0D6306;\">" . $LANG["inserttbldomainssuccess"] . " " . $domainvalue . "</span><br />";
                                            echo "<textarea class=\"form-control\" style=\"display: inline; width: 100%\" rows=\"1\" readonly=\"readonly\">orderid = " . $orderid . ", userid = " . $whmcsuserid . ", registrationdate = " . $orderdate . ", registrar = " . $registrar_label . ", expirydate = " . $expiredate . ", nextduedate = " . $nextduedate . "</textarea><br /><hr /><br>";
                                        } else {
                                            echo "<span style=\"color:#CC0000;\">" . $LANG["inserttbldomainserror"] . " " . $domainvalue . "</span><br /><br />";
                                        }
                                        $conn->commit();
                                    } catch (Throwable $e) {
                                        $conn->rollBack();
                                        echo "<span style=\"color:#CC0000;\">Database error while importing " . $domainvalue . ": " . $e->getMessage() . "</span><br />";
                                        logActivity("RCM Domain Import (bulk) DB error for " . $domainvalue . ": " . $e->getMessage());
                                    }
                                } else {
                                    echo "<p><span style=\"color:#CC0000;\">" . $LANG["notfounddesc1"] . " <strong>" . $domvalues["customerid"] . "</strong> " . $LANG["notfounddesc2"] . " <strong>" . $domvalues["domain"] . "</strong> " . $LANG["notfounddesc3"] . "</span></p>";
                                }
                            } else {
                                echo "<span style=\"color:#043ED1;\">" . $LANG["duplicatedomainentry"] . " " . $domainvalue . "</span><br /><br />";
                            }
                        }
                    }
                    $totaldomains = $domnumber - 1;
                    if ($totaldomains == 1) {
                        $domainword = $LANG["domainword"];
                    } else {
                        $domainword = $LANG["domainsword"];
                    }
                    $totalProcessed = isset($domdata) && is_array($domdata) ? count($domdata) : 0;
                    $totalSkipped = max(0, $totalProcessed - $totaldomains);
                    $finish = microtime(true);
                    $total_time = round($finish - $start, 4);
                    echo "<strong>" . $totaldomains . "</strong> " . $domainword . " " . $LANG["successfullyimportedin"] . " " . $total_time . " " . $LANG["seconds"] . "<br />";
                    echo "<span style=\"color:#666;font-size:11px;\">Processed: " . (int) $totalProcessed . " &nbsp;|&nbsp; Imported: " . (int) $totaldomains . " &nbsp;|&nbsp; Skipped: " . (int) $totalSkipped . "</span><br />";
                    logActivity("RCM Domain Import (bulk): Processed=" . (int) $totalProcessed . ", Imported=" . (int) $totaldomains . ", Skipped=" . (int) $totalSkipped . ", Time=" . $total_time . "s");
                    echo "<br /><h3><a href=\"" . $modulelink . "&domain=domainimport\">" . $LANG["domaintoolbacklink"] . "</a></h3>";
                } else {
                    if (isset($_REQUEST["searchmail"]) || isset($_REQUEST["alluser"])) {
                        $display = "none";
                    } else if ($_POST["fetchcustomerids"]) {
                        $display = "block";
                    } else {
                        $display = "none";
                    }
                    if (isset($_REQUEST["fetchcustomerids"])) {
                        $method = "GET";
                        $apifunction = "/api/customers/search.json";
                        $pageno = 1;
                        $noofrecords = 10;
                        $data = ["status" => "Active", "no-of-records" => $noofrecords, "page-no" => $pageno];
                        $recsindb = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
                        $recordqty = $recsindb["recsindb"];
                        if (500 < $recordqty) {
                            $steps = $recordqty / 500;
                            $noofrecords = 500;
                            $stepsresult = ceil($steps);
                            $i = 1;
                            while ($i <= $stepsresult) {
                                $data = ["status" => "Active", "no-of-records" => $noofrecords, "page-no" => $i++];
                                $custsearchXml = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
                                $search_array[] = $custsearchXml;
                            }
                        } else {
                            $noofrecords = 500;
                            $pageno = 1;
                            $data = ["status" => "Active", "no-of-records" => $noofrecords, "page-no" => $pageno];
                            $custsearchXml = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
                            $search_array[] = $custsearchXml;
                        }
                        foreach ($search_array as $value) {
                            foreach ($value as $data) {
                                if (is_array($data)) {
                                    $customerid_array[] = $data["customer.customerid"];
                                }
                            }
                        }
                        $count = count($customerid_array);
                        if (!empty($customerid_array)) {
                            $comma_custids = implode(",", $customerid_array);
                            $cust_fechtresult = "<div class=\"alert alert-success\"><p>" . $count . " " . $LANG["allcustomersfound"] . "</p></div>";
                        } else {
                            $cust_fechtresult = "<div class=\"alert alert-danger\"><p>" . $LANG["nocustomersfound"] . "</p></div>";
                        }
                    }
                    echo $configuredto;
                    echo "<h1>" . $LANG["domainimporttitle"] . "</h1>";
                    echo "<div style=\"width:100%\"><p>" . $LANG["domainimportdesc2"] . "</p><div class=\"alert alert-warning\">" . $LANG["domainimportdesc3"] . $LANG["domainimportdesc4"] . "</div></div><br />";
                    echo "<div style=\"padding: 5px 20px;border: 1px solid #CCCCCC;-moz-border-radius: 5px;-webkit-border-radius: 5px;-o-border-radius: 5px;border-radius: 5px;\">";
                    echo "<br /><h3 style=\"border-bottom: 1px solid #cccccc;\"><strong>" . $LANG["domainimporttitle2"] . "</strong></h3>";
                    echo "<script type=\"text/javascript\">\r\n\t\t \$(document).ready(function(){\r\n\t\t\t\$(\"#clientsearchval\").keyup(function () {\r\n\t\t\t\tvar useridsearchlength = \$(\"#clientsearchval\").val().length;\r\n\t\t\t\tif (useridsearchlength>2) {\r\n\t\t\t\t\$.post(\"search.php\", { clientsearch: 1, value: \$(\"#clientsearchval\").val(),token: \"" . generate_token("plain") . "\" },\r\n\t\t\t\t\tfunction(data){\r\n\t\t\t\t\t\tif (data) {\r\n\t\t\t\t\t\t\t\$(\"#clientsearchresults\").html(data);\r\n\t\t\t\t\t\t\t\$(\"#clientsearchresults\").slideDown(\"slow\");\r\n\t\t\t\t\t\t}\r\n\t\t\t\t\t});\r\n\t\t\t\t}\r\n\t\t\t});\r\n\t\t });\r\n\t\t function searchselectclient(userid,name,email) {\r\n\t\t\t\$(\"#newuserid\").val(email);\r\n\t\t\t\$(\"#clientsearchresults\").slideUp();\r\n\t\t }\r\n\t\t </script>";
                    echo "<div>";
                    echo "<form method=\"post\" action=\"" . $_SERVER["PHP_SELF"] . "?module=resellerclubmods_tools&domain=domainimport\">";
                    echo "<div>";
                    echo $LANG["searchtitle"] . " <input type=\"text\" id=\"clientsearchval\" size=\"20\" class=\"form-control\" style=\"display: inline; width: auto\"/> ";
                    echo "</div><br /><div id=\"clientsearchresults\">";
                    echo "<div class=\"searchresultheader\">" . $LANG["searchresult"] . "</div>";
                    echo "<div class=\"searchresult\" align=\"center\">" . $LANG["searchmatches"] . "</div>";
                    echo "</div><br />";
                    echo "<p>" . $LANG["searchwhmcsusermail"] . " \r\n\t<input class=\"form-control\" style=\"display: inline; width: auto\" type=\"text\" name=\"useremail\" id=\"newuserid\" size=\"40\" />&nbsp;\r\n\t<input type=\"submit\" name=\"searchmail\" value=\"" . $LANG["searchuserbutton"] . "\" class=\"btn btn-primary absmiddle\" /> " . $LANG["orload"] . " \r\n\t<input type=\"submit\" name=\"alluser\" value=\"" . $LANG["allwhmcsusers"] . "\" class=\"btn btn-primary absmiddle\" /><br />" . $LANG["custperpage"] . " \r\n\t<input name=\"numqty\" type=\"radio\" value=\"20\" " . $a_checked . "/> 20 \r\n\t<input name=\"numqty\" type=\"radio\" value=\"50\" " . $b_checked . "/> 50 \r\n\t<input name=\"numqty\" type=\"radio\" value=\"100\" " . $c_checked . "/> 100 \r\n\t<input name=\"numqty\" type=\"radio\" value=\"500\" " . $d_checked . "/> 500\r\n\t<br />" . $LANG["domspercust"] . " \r\n\t<input name=\"impqty\" type=\"radio\" value=\"20\" " . $imp20_checked . "/> 20 \r\n\t<input name=\"impqty\" type=\"radio\" value=\"50\" " . $imp50_checked . "/> 50 \r\n\t<input name=\"impqty\" type=\"radio\" value=\"100\" " . $imp100_checked . "/> 100 \r\n\t<input name=\"impqty\" type=\"radio\" value=\"500\" " . $imp500_checked . "/> 500</p>";
                    $onlyImportableChecked = $onlyImportable ? " checked=\"checked\"" : "";
                    echo "<p>\r\n\t<input type=\"checkbox\" name=\"onlyimportable\" value=\"1\"" . $onlyImportableChecked . " /> " . $LANG["onlyimportablecustomers"] . "\r\n\t</p>";
                    echo "</form></div><br />";
                    if (isset($_POST["searchmail"])) {
                        $usermail = $_POST["useremail"];
                        $whmcs_userdata = [];
                        foreach (Illuminate\Database\Capsule\Manager::table("tblclients")->where("email", "=", $usermail)->select("id", "email")->get() as $data) {
                            array_push($whmcs_userdata, json_decode(json_encode($data), true));
                        }
                    }
                    if (isset($_REQUEST["alluser"])) {
                        $_GET["alluser"] = "true";
                        $whmcs_userdata = [];
                        foreach (Illuminate\Database\Capsule\Manager::table("tblclients")->select("id", "email")->get() as $data) {
                            array_push($whmcs_userdata, json_decode(json_encode($data), true));
                        }
                    }
                    if (empty($_SESSION["rcm_domain_customer_import_array"])) {
                        $pageno = 1;
                        $noofrecords = 500;
                        $method = "GET";
                        $apifunction = "/api/customers/search.json";
                        $data = ["no-of-records" => $noofrecords, "page-no" => $pageno];
                        $allarrXml = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
                        $recsindb_value = $allarrXml["recsindb"];
                        if (500 < $recsindb_value) {
                            $pageno = ceil($recsindb_value / $noofrecords);
                            $i = 1;
                            while ($i <= $pageno) {
                                $data = ["no-of-records" => $noofrecords, "page-no" => $i++];
                                $multi_allarrXml[] = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
                            }
                            $a = 0;
                            foreach ($multi_allarrXml as $mvalues) {
                                foreach ($mvalues as $sv) {
                                    $allarrXml[$a++] = $sv;
                                }
                            }
                        }
                        $_SESSION["rcm_domain_customer_import_array"] = $allarrXml;
                    } else {
                        $allarrXml = $_SESSION["rcm_domain_customer_import_array"];
                    }
                    foreach ($allarrXml as $rc_useremails) {
                        if (is_array($rc_useremails)) {
                            $rc_customer_array[$rc_useremails["customer.customerid"]] = $rc_useremails["customer.username"];
                        }
                    }
                    $pageno = 1;
                    $noofrecords = 500;
                    $method = "GET";
                    $apifunction = "/api/domains/search.json";
                    $statusarr = ["Active"];
                    $data = ["status" => $statusarr, "no-of-records" => $noofrecords, "page-no" => $pageno];
                    $domsearchXml = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
                    $recsindb_domvalue = $domsearchXml["recsindb"];
                    if (500 < $recsindb_domvalue) {
                        $pageno = ceil($recsindb_domvalue / $noofrecords);
                        $idom = 1;
                        while ($idom <= $pageno) {
                            $data = ["no-of-records" => $noofrecords, "page-no" => $idom++];
                            $multi_domsearchXml[] = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
                        }
                        $adom = 0;
                        foreach ($multi_domsearchXml as $mvalues) {
                            foreach ($mvalues as $sv) {
                                $domsearchXml[$adom++] = $sv;
                            }
                        }
                    }
                    $rcDomainCount = [];
                    if (isset($domsearchXml) && is_array($domsearchXml)) {
                        foreach ($domsearchXml as $entry) {
                            if (is_array($entry) && isset($entry["entity.customerid"])) {
                                $cid = $entry["entity.customerid"];
                                if (!isset($rcDomainCount[$cid])) {
                                    $rcDomainCount[$cid] = 0;
                                }
                                $rcDomainCount[$cid]++;
                            }
                        }
                    }
                    $whmcsImportUsers = [];
                    if (isset($whmcs_userdata) && is_array($whmcs_userdata)) {
                        foreach ($whmcs_userdata as $row) {
                            if (!isset($row["email"])) {
                            } else {
                                $emailLower = strtolower($row["email"]);
                                $rcCustomerId = array_search($emailLower, $rc_customer_array, true);
                                if ($rcCustomerId === false) {
                                } else if (empty($rcDomainCount[$rcCustomerId])) {
                                } else {
                                    $whmcsImportUsers[] = $row;
                                }
                            }
                        }
                    }
                    $onlyImportable = isset($_REQUEST["onlyimportable"]) && $_REQUEST["onlyimportable"] == "1";
                    $rcm_pagination = new RcmToolsPagination();
                    $rcm_pagination->maxPageLinks = 11;
                    $num0 = 0;
                    $num1 = 0;
                    $productPages = $rcm_pagination->generate($whmcsImportUsers, $numqty);
                    if (count($productPages) != 0) {
                        echo "<table class=\"datatable\" border=\"0\" cellpadding=\"3\" cellspacing=\"1\" width=\"100%\">";
                        echo "<tr>\r\n\t\t\t <th>" . $LANG["whmcsusermail"] . "</th>\r\n\t\t\t <th>" . $LANG["domainsinwhmcs"] . "</th>\r\n\t\t\t <th>" . $LANG["noofdomainsfound"] . " <img class=\"absmiddle\" style=\"cursor:help;\" src=\"../modules/addons/resellerclubmods_tools/img/help.png\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["qtyinwhmcs"] . "\" height=\"16\" width=\"16\" /></th>\r\n\t\t\t <th>" . $logicbox_registrar . "</th>\r\n\t\t\t <th>" . $LANG["noofdomainsfound"] . " <img class=\"absmiddle\" style=\"cursor:help;\" src=\"../modules/addons/resellerclubmods_tools/img/help.png\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $LANG["qtyinob1"] . " " . $logicbox_registrar . " " . $LANG["qtyinob2"] . "\" height=\"16\" width=\"16\" /></th>\r\n\t\t\t <th>" . $LANG["actiondetails"] . " / " . $LANG["actiondesc"] . "</th>\r\n\t\t\t </tr>";
                        $sumClientsShown = 0;
                        $sumImportableClients = 0;
                        $sumRcDomains = 0;
                        $sumWhmcsDomains = 0;
                        $sumImportableDomains = 0;
                        foreach ($productPages as $check_arr) {
                            $email = strtolower($check_arr["email"]);
                            $userid = $check_arr["id"];
                            if (in_array($email, $rc_customer_array)) {
                                $rc_customer_id = array_search($email, $rc_customer_array);
                                $usersarray = ["found" => [$email, $rc_customer_id]];
                            } else {
                                $usersarray = ["notfound" => [$email]];
                            }
                            if ($usersarray["found"][0] == $email) {
                                $whmcsusername = $usersarray["found"][0];
                                $rccustomerid = $usersarray["found"][1];
                                if (0 < $domsearchXml["recsindb"]) {
                                    foreach ($domsearchXml as $entrykey) {
                                        if (is_array($entrykey) && $rccustomerid == $entrykey["entity.customerid"]) {
                                            $is_idn = strpos($entrykey["entity.description"], "xn--");
                                            if ($is_idn !== false) {
                                                $entitydesc = $IDN->decode($entrykey["entity.description"]);
                                            } else {
                                                $entitydesc = $entrykey["entity.description"];
                                            }
                                            $domainnamesarr[$rccustomerid][] = $entitydesc;
                                            sort($domainnamesarr[$rccustomerid]);
                                        }
                                    }
                                }
                                $countresult = 0;
                                if (!empty($domainnamesarr[$rccustomerid]) && is_array($domainnamesarr[$rccustomerid])) {
                                    $countresult = count($domainnamesarr[$rccustomerid]);
                                }
                                $whmcsdomainarr[$rccustomerid] = [];
                                $whmcsRows = Illuminate\Database\Capsule\Manager::table("tbldomains")->where("userid", $userid)->where("registrar", "LIKE", $logicbox_registrar . "%")->select("domain")->get();
                                foreach ($whmcsRows as $data) {
                                    $whmcsdomainarr[$rccustomerid][] = $data->domain;
                                }
                                if (!empty($whmcsdomainarr[$rccustomerid])) {
                                    sort($whmcsdomainarr[$rccustomerid]);
                                }
                                $whmcsnoofdomains = 0;
                                if (!empty($whmcsdomainarr[$rccustomerid]) && is_array($whmcsdomainarr[$rccustomerid])) {
                                    $whmcsnoofdomains = count($whmcsdomainarr[$rccustomerid]);
                                }
                                $texareastyle = "font-size: 10px;color: #666;border: none;background:none;width:90%;";
                                $rcdomains = $LANG["nodomainsfound"];
                                $rcdomainnames = [];
                                if (is_array($domainnamesarr)) {
                                    foreach ($domainnamesarr as $rckey => $rcdomainnamesInner) {
                                        if ($rckey == $rccustomerid && is_array($rcdomainnamesInner)) {
                                            $rcdomainnames = $rcdomainnamesInner;
                                            $rcdomainsText = implode("\n", $rcdomainnamesInner);
                                            $rcdomains = "<textarea class=\"form-control\" style=\"" . $texareastyle . "\" cols=\"20\" rows=\"4\" readonly=\"readonly\">" . $rcdomainsText . "</textarea>";
                                        }
                                    }
                                }
                                $whmcsdomain = $LANG["nodomainsfound"];
                                $whmcsdomainnames = [];
                                if (is_array($whmcsdomainarr)) {
                                    foreach ($whmcsdomainarr as $whmcskey => $whmcsdomainnamesInner) {
                                        if ($whmcskey == $rccustomerid && is_array($whmcsdomainnamesInner)) {
                                            $whmcsdomainnames = $whmcsdomainnamesInner;
                                            if (is_array($rcdomainnames)) {
                                                $diffrcdomain = array_diff($whmcsdomainnames, $rcdomainnames);
                                                $countdiffrcdomain = count($diffrcdomain);
                                                $whmcsnoofdomains = $whmcsnoofdomains - $countdiffrcdomain;
                                            }
                                            $whmcsdomainsText = implode("\n", $whmcsdomainnamesInner);
                                            $whmcsdomain = "<textarea class=\"form-control\" style=\"" . $texareastyle . "\" cols=\"20\" rows=\"4\" readonly=\"readonly\">" . $whmcsdomainsText . "</textarea>";
                                        }
                                    }
                                }
                                if (!is_array($rcdomainnames)) {
                                    $rcdomainnames = [];
                                }
                                if (!is_array($whmcsdomainnames)) {
                                    $whmcsdomainnames = [];
                                }
                                $importdomain = array_diff($rcdomainnames, $whmcsdomainnames);
                                $importableCount = is_array($importdomain) ? count($importdomain) : 0;
                                if ($onlyImportable && $importableCount === 0) {
                                } else {
                                    if ($whmcsnoofdomains == $countresult) {
                                        $importqty = 0;
                                        $actiondesc = $LANG["nothingforimport"];
                                        $actionhelp = $LANG["nothingforimporthelp"];
                                        $actioncolor = "#0D6306";
                                        $importform = "";
                                    }
                                    if ($countresult < $whmcsnoofdomains) {
                                        $importqty = 0;
                                        $actiondesc = $LANG["nothingforimportwarn"];
                                        $actionhelp = $LANG["nothingforimportwarnhelp"];
                                        $actioncolor = "#F79E04";
                                        $importform = "";
                                    }
                                    if ($whmcsnoofdomains < $countresult) {
                                        $importqty = $countresult - $whmcsnoofdomains;
                                        if ($impqty < $importqty) {
                                            $steps = $importqty / $impqty;
                                            $stepsresult = ceil($steps);
                                        } else {
                                            $stepsresult = 1;
                                        }
                                        $actiondesc = $LANG["goforimport"];
                                        $actionhelp = $LANG["goforimporthelp"];
                                        $actioncolor = "#CC0000";
                                        $domainstrings = implode(",", $importdomain);
                                        $importform = "\r\n\t\t\t\t\t\t\t\t  <form method=\"post\" action=\"" . $_SERVER["REQUEST_URI"] . "\">\r\n\t\t\t\t\t\t\t\t  <input type=\"hidden\" name=\"domainimport\" value=\"true\"/>\r\n\t\t\t\t\t\t\t\t  <input type=\"hidden\" name=\"whmcsuserid\" value=\"" . $userid . "\"/>\r\n\t\t\t\t\t\t\t\t  <input type=\"hidden\" name=\"username\" value=\"" . $whmcsusername . "\"/>\r\n\t\t\t\t\t\t\t\t  <input type=\"hidden\" name=\"domains\" value=\"" . $domainstrings . "\"/>\r\n\t\t\t\t\t\t\t\t  <input type=\"hidden\" name=\"rcuserid\" value=\"" . $rccustomerid . "\"/>\r\n\t\t\t\t\t\t\t\t  <p align=\"center\"><input value=\"" . $LANG["domainimportbutton"] . "\" class=\"btn btn-success\" type=\"submit\"></p></form>\r\n\t\t\t\t\t\t\t\t  " . $stepsresult . "&nbsp;" . $LANG["importcycles"] . "</td>\r\n\t\t\t\t\t\t\t\t  ";
                                    }
                                    if (empty($whmcsusername)) {
                                        $whmcsusername = $LANG["whmcsuserfnotfound"];
                                    }
                                    if (empty($whmcsdomain)) {
                                        $whmcsdomain = $LANG["nodomainsfound"];
                                    }
                                    if (empty($rcdomains)) {
                                        $rcdomains = $LANG["nodomainsfound"];
                                    }
                                    $importableCount = is_array($importdomain) ? count($importdomain) : 0;
                                    $rowClass = "";
                                    if (0 < $importableCount) {
                                        $rowClass = "rcm-row-importable";
                                    } else if ($countresult < $whmcsnoofdomains) {
                                        $rowClass = "rcm-row-zombie";
                                    } else {
                                        $rowClass = "rcm-row-ok";
                                    }
                                    $sumClientsShown++;
                                    $sumRcDomains += (int) $countresult;
                                    $sumWhmcsDomains += (int) $whmcsnoofdomains;
                                    $sumImportableDomains += (int) $importableCount;
                                    if (0 < $importableCount) {
                                        $sumImportableClients++;
                                    }
                                    echo "<tr class=\"" . $rowClass . "\">";
                                    echo "\r\n\t\t\t\t\t <td valign=\"top\">" . $whmcsusername . "</td>\r\n\t\t\t\t\t <td valign=\"top\">" . $whmcsdomain . "</td>\r\n\t\t\t\t\t <td valign=\"top\"><span style=\"color:" . $actioncolor . "\"><strong>" . $whmcsnoofdomains . "</strong></span></td>\r\n\t\t\t\t\t <td valign=\"top\">" . $rcdomains . "</td>\r\n\t\t\t\t\t <td valign=\"top\"><span style=\"color:" . $actioncolor . "\"><strong>" . $countresult . "</strong></span></td>\r\n\t\t\t\t\t <td valign=\"top\"><span style=\"color:" . $actioncolor . ";vertical-align:middle;\"><a href=\"#\" data-toggle=\"tooltip\" data-placement=\"right\" title=\"" . $actionhelp . "\"> <img class=\"absmiddle\" src=\"../modules/addons/resellerclubmods_tools/img/help.gif\" alt=\"yes\" height=\"16\" width=\"16\" /></a>&nbsp;" . $actiondesc . " = " . $importqty . "</span>" . $importform . "</td>\r\n\t\t\t\t\t ";
                                    echo "</tr>";
                                }
                            }
                        }
                        echo "</table><br />";
                        echo "<br /><div class=\"infobox\" style=\"font-size:11px;\">\r\n\t\t\t<strong>" . $LANG["summarytitle"] . "</strong><br />\r\n\t\t\t" . $LANG["summary_clients"] . ": " . (int) $sumClientsShown . "<br />\r\n\t\t\t" . $LANG["summary_clients_importable"] . ": " . (int) $sumImportableClients . "<br />\r\n\t\t\t" . $LANG["summary_rcdomains"] . ": " . (int) $sumRcDomains . "<br />\r\n\t\t\t" . $LANG["summary_whmcsdomains"] . ": " . (int) $sumWhmcsDomains . "<br />\r\n\t\t\t" . $LANG["summary_importable_domains"] . ": " . (int) $sumImportableDomains . "\r\n\t\t</div><br />";
                        echo $pageNumbers = "<div>" . $rcm_pagination->links() . "</div><br />";
                    }
                    echo "</div><div></div><a name=\"bulk\"></a><br /><div style=\"padding: 5px 20px;border: 1px solid #CCCCCC;-moz-border-radius: 5px;-webkit-border-radius: 5px;-o-border-radius: 5px;border-radius: 5px;\"><script language=\"javascript\" type=\"text/javascript\">function showbulkform(){\$(\"#bulkimport\").slideToggle();}</script>";
                    echo "<h3 onclick=\"showbulkform();return false;\" style=\"cursor:pointer;border-bottom: 1px solid #cccccc;\"><strong>" . $LANG["bulkdomtitle"] . "</strong></h3>";
                    echo "<div style=\"display:" . $display . ";\" id=\"bulkimport\">";
                    if ($cust_fechtresult) {
                        echo $cust_fechtresult;
                    }
                    echo "<div class=\"alert alert-info\">" . $LANG["bulkdomdesc"] . "</div><br />";
                    echo "<form method=\"post\" action=\"" . $_SERVER["PHP_SELF"] . "?module=resellerclubmods_tools&domain=domainimport&fetchcustomerids#bulk\">";
                    echo "<p><input type=\"submit\" name=\"fetchcustomerids\" value=\"" . $LANG["buttonfetchallids"] . "\" class=\"btn btn-primary\" /></p></form>";
                    echo "<form method=\"post\" action=\"" . $_SERVER["PHP_SELF"] . "?module=resellerclubmods_tools&domain=domainimport&bulk\">";
                    echo "<textarea style=\"width:100%; height:200px;\" name=\"customerids\" class=\"form-control\">" . $comma_custids . "</textarea><br />";
                    echo "<p><input type=\"submit\" name=\"dombulk\" value=\"" . $LANG["domainimportbutton"] . "\" class=\"btn btn-success\" /></p></form></div>";
                    echo "</div><br />";
                }
        }
}

?>