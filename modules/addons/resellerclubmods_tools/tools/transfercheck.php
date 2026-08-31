<?php
if (!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
echo $configuredto;
echo "<div style=\"width:100%\"><h1>" . $LANG["transferchecktitle"] . "</h1><p>" . $LANG["checktransferdesclong"] . "</p></div>";
echo "<div style=\"padding: 5px 20px;border: 1px solid #CCCCCC;-moz-border-radius: 5px;-webkit-border-radius: 5px;-o-border-radius: 5px;border-radius: 5px;\">";
echo "<br /><h3 style=\"border-bottom: 1px solid #cccccc;\"><strong>" . $LANG["transferchecktitle2"] . "</strong></h3>";
echo "<form method=\"post\" action=\"" . $_SERVER["REQUEST_URI"] . "\">";
echo "<input type=\"hidden\" name=\"checktransfers\" value=\"true\"/>";
echo "<input value=\"" . $LANG["transfercheckbutton"] . "\" type=\"submit\" class=\"btn btn-success\">";
echo "<br /><input type=\"checkbox\" name=\"sendcustomermails\" />&nbsp;" . $LANG["checkonlytransfers"] . "</form>";
echo "<br /><h3 style=\"border-bottom: 1px solid #cccccc;\"><strong>" . $LANG["transfercheckautotitle"] . "</strong></h3>";
echo "<p>" . $LANG["automatedtransfercheck"] . " <a href=\"addonmodules.php?module=resellerclubmods_tools&automation=cronjobs#transfercheck\">" . $LANG["automatedtransfercheck1"] . "</a></p>";
if (isset($_POST["checktransfers"]) && $_POST["checktransfers"] == "true") {
    $time = microtime();
    $time = explode(" ", $time);
    $time = $time[1] + $time[0];
    $start = $time;
    $sendcustomermails = $_POST["sendcustomermails"];
    $detailsmessage = "";
    echo "<h3 style=\"border-bottom: 1px solid #cccccc;\"><strong>" . $LANG["checkinitated"] . " " . date("Y-m-d H:i:s") . "</strong></h3>";
    $tbldomains = [];
    foreach (Illuminate\Database\Capsule\Manager::table("tbldomains")->where("status", "=", "Pending Transfer")->where("registrar", "LIKE", $logicbox_registrar . "%")->select("domain")->get() as $data) {
        $tbldomains[] = $data->domain;
    }
    echo "<strong>" . count($tbldomains) . "</strong> " . $LANG["founddpt"];
    if (!is_array($tbldomains)) {
        echo "<p><strong>" . $LANG["nopendingtransfer"] . "</strong></p>";
    } else {
        $is_p = 0;
        $is_c = 0;
        $is_a = 0;
        foreach ($tbldomains as $domains) {
            $method = "GET";
            $apifunction = "/api/domains/search.json";
            $data = ["domain-name" => $IDN->encode($domains), "no-of-records" => 20, "page-no" => 1, "status" => ["InActive", "Deleted", "Active"]];
            $domsearchXml = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
            $is_key = $domsearchXml["recsindb"];
            $is_orderid = $domsearchXml[$is_key]["orders.orderid"];
            $is_status = $domsearchXml[$is_key]["entity.currentstatus"];
            $is_endtime_raw = $domsearchXml[$is_key]["orders.endtime"];
            $is_endtime = date("Y-m-d", $is_endtime_raw);
            if (is_numeric($is_orderid)) {
                $apifunction = "/api/domains/details.json";
                $data = ["options" => "All", "order-id" => $is_orderid];
                $domdetailsXml = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
                $actionstatusdesc = $domdetailsXml["actionstatusdesc"];
            }
            $domains = $IDN->decode($domains);
            $domainid = Illuminate\Database\Capsule\Manager::table("tbldomains")->where("domain", "=", $domains)->where("status", "=", "Pending Transfer")->select("id")->value("id");
            if ($is_status == "Deleted") {
                $apifunction = "/api/actions/search-current.json";
                $data = ["no-of-records" => 10, "page-no" => 1, "order-id" => $is_orderid];
                $searchdeleted = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
                if (isset($searchdeleted[2]["actionstatusdesc"])) {
                    $actionstatusdesc = $LANG["transferrequestfailed"] . " " . $searchdeleted[2]["actionstatusdesc"];
                } else if (isset($searchdeleted[1]["actionstatusdesc"])) {
                    $actionstatusdesc = $LANG["transferrequestfailed"] . " " . $searchdeleted[1]["actionstatusdesc"];
                } else {
                    $actionstatusdesc = $LANG["transferrequestfailednoreason"];
                }
                if ($searchdeleted["status"] != "ERROR") {
                    $command = "SendEmail";
                    $is_customvars = ["domain_transfer_failure_reason" => $actionstatusdesc];
                    $is_customvars = base64_encode(serialize($is_customvars));
                    $values = ["messagename" => $templatenamefailed, "id" => $domainid, "customvars" => $is_customvars];
                    logActivity($domains . " - " . $LANG["sendmail01"] . $templatenamefailed . $LANG["sendmail02"]);
                    if ($sendcustomermails != "on" && $templatenamefailed != "None") {
                        $apiresults = localAPI($command, $values, $maileradmin);
                        if ($apiresults["result"] == "success") {
                            $detailsmessage = $LANG["sendmail01"] . $templatenamefailed . $LANG["sendmail02"] . "<br />";
                        } else {
                            $detailsmessage = $LANG["erroroccured"] . $apiresults["result"] . "<br />";
                        }
                    }
                }
            } else {
                $pos01 = stripos($actionstatusdesc, "Waiting for Domain-Secret");
                $pos02 = stripos($actionstatusdesc, "Action waiting for domain to be unlocked");
                $pos03 = stripos($actionstatusdesc, "Could not fetch Current Administrative Contact Email Address");
                if ($pos01 !== false) {
                    $command = "SendEmail";
                    $values = ["messagename" => $templatenameeppcode, "id" => $domainid];
                    logActivity($domains . " - " . $LANG["sendmail01"] . $templatenameeppcode . $LANG["sendmail02"]);
                    if ($sendcustomermails != "on" && $templatenameeppcode != "None") {
                        $apiresults = localAPI($command, $values, $maileradmin);
                        if ($apiresults["result"] == "success") {
                            $detailsmessage = $LANG["sendmail01"] . $templatenameeppcode . $LANG["sendmail02"] . "<br />";
                        } else {
                            $detailsmessage = $LANG["erroroccured"] . $apiresults["result"] . "<br />";
                        }
                    }
                } else if ($pos02 !== false) {
                    $command = "SendEmail";
                    $values = ["messagename" => $templatenameunlock, "id" => $domainid];
                    logActivity($domains . " - " . $LANG["sendmail01"] . $templatenameunlock . $LANG["sendmail02"]);
                    if ($sendcustomermails != "on" && $templatenameunlock != "None") {
                        $apiresults = localAPI($command, $values, $maileradmin);
                        if ($apiresults["result"] == "success") {
                            $detailsmessage = $LANG["sendmail01"] . $templatenameunlock . $LANG["sendmail02"] . "<br />";
                        } else {
                            $detailsmessage = $LANG["erroroccured"] . $apiresults["result"] . "<br />";
                        }
                    }
                } else if ($pos03 !== false) {
                    $command = "SendEmail";
                    $values = ["messagename" => $templatenameidprotect, "id" => $domainid];
                    logActivity($domains . " - " . $LANG["sendmail01"] . $templatenameidprotect . $LANG["sendmail02"]);
                    if ($sendcustomermails != "on" && $templatenameidprotect != "None") {
                        $apiresults = localAPI($command, $values, $maileradmin);
                        if ($apiresults["result"] == "success") {
                            $detailsmessage = $LANG["sendmail01"] . $templatenameidprotect . $LANG["sendmail02"] . "<br />";
                        } else {
                            $detailsmessage = $LANG["erroroccured"] . $apiresults["result"] . "<br />";
                        }
                    }
                }
            }
            if ($is_status == "Deleted") {
                if ($brokentransfers == "Cancelled") {
                    $update = ["status" => "Cancelled"];
                    Illuminate\Database\Capsule\Manager::table("tbldomains")->where("domain", "=", $domains)->update($update);
                    $todoid = Illuminate\Database\Capsule\Manager::table("tbltodolist")->where("description", "LIKE", "%" . $domains . "%")->select("id")->value("id");
                    if (!empty($todoid)) {
                        Illuminate\Database\Capsule\Manager::table("tbltodolist")->where("id", "=", $todoid)->update(["status" => "Incomplete"]);
                    }
                    logActivity($domains . " - " . $LANG["errordomaindata"] . " - " . $LANG["errordomaincancelled"] . " - " . $actionstatusdesc);
                    echo "<p><span>[" . $domains . "]</span><span style=\"color:#CC0000;\"> " . $LANG["errordomaindata"] . " - " . $LANG["errordomaincancelled"] . "</span> - " . $actionstatusdesc . "<br />" . $detailsmessage . "</p>";
                } else if ($brokentransfers == "Delete") {
                    $dodelete = Illuminate\Database\Capsule\Manager::table("tbldomains")->where("domain", "=", $domains)->delete();
                    $todoid = Illuminate\Database\Capsule\Manager::table("tbltodolist")->where("description", "LIKE", "%" . $domains . "%")->select("id")->value("id");
                    if (!empty($todoid)) {
                        Illuminate\Database\Capsule\Manager::table("tbltodolist")->where("id", "=", $todoid)->update(["status" => "Incomplete"]);
                    }
                    logActivity($domains . " - " . $LANG["errordomaindata"] . " - " . $LANG["errordomaindeleted"] . " - " . $actionstatusdesc);
                    echo "<p><span>[" . $domains . "]</span><span style=\"color:#CC0000;\"> " . $LANG["errordomaindata"] . " - " . $LANG["errordomaindeleted"] . "</span> - " . $actionstatusdesc . "<br />" . $detailsmessage . "</p>";
                } else {
                    logActivity($domains . " - " . $LANG["errordomaindata"] . " - " . $LANG["errordomainnoaction"] . " - " . $actionstatusdesc);
                    echo "<p><span>[" . $domains . "]</span><span style=\"color:#CC0000;\"> " . $LANG["errordomaindata"] . " - " . $LANG["errordomainnoaction"] . "</span> - " . $actionstatusdesc . "<br />" . $detailsmessage . "</p>";
                }
            } else if ($is_status == "InActive") {
                $is_p++;
                logActivity($domains . " - " . $LANG["ispending"] . " - " . $LANG["stillpending"] . " - " . $actionstatusdesc);
                echo "<p><span>[" . $domains . "]</span><span style=\"color:#333333;\"> " . $LANG["stillpending"] . " - " . $actionstatusdesc . "</span><br />" . $detailsmessage . "</p>";
            } else if ($is_status == "Active") {
                $is_a++;
                $todoid = Illuminate\Database\Capsule\Manager::table("tbltodolist")->where("status", "=", "In Progress")->where("description", "LIKE", "%" . $domains . "%")->select("id")->value("id");
                Illuminate\Database\Capsule\Manager::table("tbldomains")->where("domain", "=", $domains)->update(["status" => $is_status, "expirydate" => $is_endtime, "nextduedate" => $is_endtime, "nextinvoicedate" => $is_endtime]);
                logActivity($domains . " - " . $LANG["iscompleted"] . " - " . $LANG["transfercompleted"] . " " . $LANG["updatesuccessmessage"]);
                echo "<p><span style=\"color:#006633;font-weight:bold;\">[" . $domains . "] " . $LANG["iscompleted"] . "</span> " . $LANG["transfercompleted"] . " <span style=\"color:#006633\">" . $LANG["updatesuccessmessage"] . "</span><br />";
                if (!empty($todoid)) {
                    Illuminate\Database\Capsule\Manager::table("tbltodolist")->where("id", "=", $todoid)->update(["status" => "Completed"]);
                    logActivity($domains . " - " . $LANG["updatetodolist"] . " " . $LANG["updatesuccessmessage"]);
                    echo $LANG["updatetodolist"] . " <span style=\"color:#006633\">" . $LANG["updatesuccessmessage"] . "</span><br />";
                } else {
                    logActivity($domains . " - " . $LANG["noupdatetodolist"]);
                    echo $LANG["noupdatetodolist"] . "<br />";
                }
                if ($sendconfmail == "on") {
                    logActivity(domains . " - " . $LANG["sendmail01"] . $emailtplname . $LANG["sendmail02"]);
                    if ($emailtplname != "None") {
                        $command = "SendEmail";
                        $values = ["messagename" => $emailtplname, "id" => $domainid];
                        $apiresults = localAPI($command, $values, $maileradmin);
                        if ($apiresults["result"] == "success") {
                            echo $LANG["sendmail01"] . $emailtplname . $LANG["sendmail02"] . "<br />";
                        } else {
                            echo $LANG["erroroccured"] . $apiresults["result"] . "<br />";
                        }
                    }
                }
                echo "</p>";
            } else {
                logActivity($domains . " - " . $LANG["errordomaindata"] . " - " . $LANG["errordomainnoaction"]);
                echo "<p><span>[" . $domains . "]</span><span style=\"color:#CC0000;\"> " . $LANG["errordomaindata"] . " - " . $LANG["errordomainnoaction"] . "</span></p>";
            }
            unset($actionstatusdesc);
            unset($detailsmessage);
        }
    }
    echo "<p><strong>" . count($tbldomains) . "</strong> " . $LANG["domainschecked"] . " - <strong>" . $is_a++ . "</strong> " . $LANG["domaintransfered"] . " - <strong>" . $is_p++ . "</strong> " . $LANG["dompendtrans"] . " - <strong>" . $is_c++ . "</strong> " . $LANG["domtranscancelled"] . "</p>";
    $time = microtime();
    $time = explode(" ", $time);
    $time = $time[1] + $time[0];
    $finish = $time;
    $total_time = round($finish - $start, 4);
    echo "<p><strong>" . $LANG["checkfinished"] . "</strong> " . $total_time . " " . $LANG["seconds"] . "</p>";
}
echo "</div>";

?>