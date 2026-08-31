<?php
$filepath = substr(__FILE__, 0, -34);
if (file_exists($filepath . "/path.php")) {
    include $filepath . "/path.php";
}
if (isset($fullpath_to_whmcs)) {
    $include_path = $fullpath_to_whmcs;
} else {
    $include_path = dirname(dirname(dirname(dirname(dirname(__FILE__)))));
}
require $include_path . "/init.php";
$conf = [];
foreach (Illuminate\Database\Capsule\Manager::table("tbladdonmodules")->where("module", "resellerclubmods_tools")->get() as $addonvars) {
    $conf[$addonvars->setting] = $addonvars->value;
}
    if (isset($_SERVER["REQUEST_METHOD"]) && ($_SERVER["REQUEST_METHOD"] === "GET" || $_SERVER["REQUEST_METHOD"] === "POST")) {
        if (isset($_REQUEST["id"]) && is_numeric($_REQUEST["id"])) {
            $rid = $_REQUEST["id"];
        } else {
            exit("invalid id or missing id");
        }
    } else if (isset($_SERVER["argv"]) || isset($argv)) {
        if (empty($conf["second_rcauth_userid"]) && empty($conf["third_rcauth_userid"]) && empty($conf["fourth_rcauth_userid"])) {
            $rid = $conf["first_rcauth_userid"];
            if (!is_numeric($rid)) {
                exit("invalid id or missing id in first account configuration (cli)");
            }
        } else if (isset($_SERVER["argv"][1])) {
            $rid = $_SERVER["argv"][1];
            if (!is_numeric($_SERVER["argv"][1])) {
                exit("invalid or missing id or php directive register_argc_argv disabled (cli)");
            }
        } else if (isset($argv[1])) {
            $rid = $argv[1];
            if (!is_numeric($argv[1])) {
                exit("invalid or missing id or php directive register_argc_argv disabled (cli)");
            }
        } else {
            print_r($_SERVER);
            print_r($_SERVER) . PHP_EOL;
            exit("multiple accounts, invalid or missing id or php directive register_argc_argv disabled (cli)");
        }
    } else {
        print_r($_SERVER);
        print_r($_SERVER) . PHP_EOL;
        exit("missing id or php directive register_argc_argv disabled (cli)");
    }
    if (!class_exists("idna_convert")) {
        require_once ROOTDIR . "/modules/addons/resellerclubmods_tools/incs/idnclass.php";
    }
    $IDN = new idna_convert();
    require ROOTDIR . "/modules/addons/resellerclubmods_tools/incs/functions.php";
    $rchttp_api = $conf["rchttp_api"];
    $sendconfmail = $conf["sendconfmail"];
    $emailtplname = $conf["templatename"];
    $maileradmin = $conf["maileradmin"];
    $brokentransfers = $conf["brokentransfers"];
    $adminconfmail = $conf["adminconfmail"];
    $templatenameunlock = $conf["templatenameunlock"];
    $templatenameeppcode = $conf["templatenameeppcode"];
    $templatenameidprotect = $conf["templatenameidprotect"];
    $templatenamefailed = $conf["templatenamefailed"];
    $sendproactivemail = $conf["sendproactivemail"];
    $conf_arr = [];
    $conf_arr["first"] = [$conf["first_rcauth_userid"], $conf["first_rcauth_apikey"], $conf["first_domainregistrar"], $conf["first_transer_check"]];
    $conf_arr["second"] = [$conf["second_rcauth_userid"], $conf["second_rcauth_apikey"], $conf["second_domainregistrar"], $conf["second_transer_check"]];
    $conf_arr["third"] = [$conf["third_rcauth_userid"], $conf["third_rcauth_apikey"], $conf["third_domainregistrar"], $conf["third_transer_check"]];
    $conf_arr["fourth"] = [$conf["fourth_rcauth_userid"], $conf["fourth_rcauth_apikey"], $conf["fourth_domainregistrar"], $conf["fourth_transer_check"]];
    foreach ($conf_arr as $conf_values) {
        if (in_array($rid, $conf_values)) {
            list($rcauth_userid, $rcauth_password, $domainregistrar, $transer_check) = $conf_values;
        }
    }
    if ($rid != $rcauth_userid) {
        exit("Unauthorized Access Attempt");
    }
    if (!empty($rcauth_userid) && !empty($rcauth_password) && $transer_check != "on") {
        $result = Illuminate\Database\Capsule\Manager::table("tbladmins")->where("username", "=", $maileradmin)->select("language")->first();
        $adminlang = $result && isset($result->language) && $result->language !== "" ? $result->language : "english";
        $get_langfile = getAdminlang($adminlang);
        require ROOTDIR . $get_langfile;
        $LANG = $_ADDONLANG;
        $time = microtime();
        $time = explode(" ", $time);
        $time = $time[1] + $time[0];
        $start = $time;
        $tbldomains = [];
        foreach (Illuminate\Database\Capsule\Manager::table("tbldomains")->where("status", "=", "Pending Transfer")->where("registrar", "LIKE", $domainregistrar . "%")->select("domain")->get() as $data) {
            $tbldomains[] = $data->domain;
        }
        $cronoutput .= "<div style=\"font-family: verdana; font-size: 11px; font-weight: normal;\">";
        $cronoutput .= "<p><strong>" . $LANG["checkinitated"] . "</strong> " . date("Y-m-d H:i:s") . "</p>";
        $cronoutput .= "<p><strong>" . count($tbldomains) . "</strong> " . $LANG["founddpt"] . "</p>";
        if (!is_array($tbldomains) || empty($tbldomains)) {
            $cronoutput .= "<p><strong>" . $LANG["nopendingtransfer"] . "</strong></p>";
        } else {
            $timenow = time();
            if ($sendproactivemail == "on") {
                $timemax = "0";
            } else {
                $timemax = "86400";
            }
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
                $result = Illuminate\Database\Capsule\Manager::table("tbldomains")->where("domain", "=", $domains)->where("status", "=", "Pending Transfer")->select("id")->first();
                $domainRow = isset($result) ? $result : NULL;
                $domainid = $domainRow && isset($domainRow->id) ? $domainRow->id : 0;
                $result = Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodstransfer")->where("domainid", "=", $domainid)->select("domainid", "failed", "waitepp", "waitunlock", "waitidprotect")->first();
                $transferRow = isset($result) ? $result : NULL;
                $exist_domainid = $transferRow && isset($transferRow->domainid) ? $transferRow->domainid : 0;
                $domain_failed = $transferRow && isset($transferRow->failed) ? $transferRow->failed : "0";
                $domain_waitepp = $transferRow && isset($transferRow->waitepp) ? $transferRow->waitepp : "0";
                $domain_waitunlock = $transferRow && isset($transferRow->waitunlock) ? $transferRow->waitunlock : "0";
                $domain_waitidprotect = $transferRow && isset($transferRow->waitidprotect) ? $transferRow->waitidprotect : "0";
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
                    if ($searchdeleted["status"] != "ERROR" && $templatenamefailed != "None") {
                        $timelimit = $timenow - $domain_failed;
                        if (empty($exist_domainid)) {
                            $timelimit = $timenow;
                            $values = ["domainid" => $domainid, "failed" => $timenow, "waitepp" => "", "waitunlock" => "", "waitidprotect" => ""];
                            Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodstransfer")->insert($values);
                        }
                        if ($timemax < $timelimit) {
                            logActivity($domains . " - " . $LANG["sendmail01"] . $templatenamefailed . $LANG["sendmail02"]);
                            $result_update = Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodstransfer")->where("domainid", "=", $exist_domainid)->update(["failed" => $timenow]);
                            $command = "SendEmail";
                            $is_customvars = ["domain_transfer_failure_reason" => $actionstatusdesc];
                            $is_customvars = base64_encode(serialize($is_customvars));
                            $values = ["messagename" => $templatenamefailed, "id" => $domainid, "customvars" => $is_customvars];
                            $apiresults = localAPI($command, $values, $maileradmin);
                            if ($apiresults["result"] == "success") {
                                $detailsmessage .= $LANG["sendmail01"] . $templatenamefailed . $LANG["sendmail02"] . "<br />";
                            } else {
                                $detailsmessage .= $LANG["erroroccured"] . $apiresults["result"] . "<br />";
                            }
                        } else {
                            $detailsmessage .= "";
                        }
                    }
                } else {
                    $pos01 = stripos($actionstatusdesc, "Waiting for Domain-Secret");
                    $pos02 = stripos($actionstatusdesc, "Action waiting for domain to be unlocked");
                    $pos03 = stripos($actionstatusdesc, "Could not fetch Current Administrative Contact Email Address");
                    if ($pos01 !== false) {
                        if ($templatenameeppcode != "None") {
                            $timelimit = $timenow - $domain_waitepp;
                            if (empty($exist_domainid)) {
                                $timelimit = $timenow;
                                $values = ["domainid" => $domainid, "failed" => "", "waitepp" => $timenow, "waitunlock" => "", "waitidprotect" => ""];
                                Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodstransfer")->insert($values);
                            }
                            if ($timemax < $timelimit) {
                                logActivity($domains . " - " . $LANG["sendmail01"] . $templatenameeppcode . $LANG["sendmail02"]);
                                $result_update = Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodstransfer")->where("domainid", "=", $exist_domainid)->update(["waitepp" => $timenow]);
                                $command = "SendEmail";
                                $values = ["messagename" => $templatenameeppcode, "id" => $domainid];
                                $apiresults = localAPI($command, $values, $maileradmin);
                                if ($apiresults["result"] == "success") {
                                    $detailsmessage .= $LANG["sendmail01"] . $templatenameeppcode . $LANG["sendmail02"] . "<br />";
                                } else {
                                    $detailsmessage .= $LANG["erroroccured"] . $apiresults["result"] . "<br />";
                                }
                            } else {
                                $detailsmessage .= "";
                            }
                        }
                    } else if ($pos02 !== false) {
                        if ($templatenameunlock != "None") {
                            $timelimit = $timenow - $domain_waitunlock;
                            if (empty($exist_domainid)) {
                                $timelimit = $timenow;
                                $values = ["domainid" => $domainid, "failed" => "", "waitepp" => "", "waitunlock" => $timenow, "waitidprotect" => ""];
                                Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodstransfer")->insert($values);
                            }
                            if ($timemax < $timelimit) {
                                logActivity($domains . " - " . $LANG["sendmail01"] . $templatenameunlock . $LANG["sendmail02"]);
                                $result_update = Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodstransfer")->where("domainid", "=", $exist_domainid)->update(["waitunlock" => $timenow]);
                                $command = "SendEmail";
                                $values = ["messagename" => $templatenameunlock, "id" => $domainid];
                                $apiresults = localAPI($command, $values, $maileradmin);
                                if ($apiresults["result"] == "success") {
                                    $detailsmessage .= $LANG["sendmail01"] . $templatenameunlock . $LANG["sendmail02"] . "<br />";
                                } else {
                                    $detailsmessage .= $LANG["erroroccured"] . $apiresults["result"] . "<br />";
                                }
                            } else {
                                $detailsmessage .= "";
                            }
                        }
                    } else if ($pos03 !== false && $templatenameidprotect != "None") {
                        $timelimit = $timenow - $domain_waitidprotect;
                        if (empty($exist_domainid)) {
                            $timelimit = $timenow;
                            $values = ["domainid" => $domainid, "failed" => "", "waitepp" => "", "waitunlock" => "", "waitidprotect" => $timenow];
                            Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodstransfer")->insert($values);
                        }
                        if ($timemax < $timelimit) {
                            logActivity($domains . " - " . $LANG["sendmail01"] . $templatenameidprotect . $LANG["sendmail02"]);
                            $result_update = Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodstransfer")->where("domainid", "=", $exist_domainid)->update(["waitidprotect" => $timenow]);
                            $command = "SendEmail";
                            $values = ["messagename" => $templatenameidprotect, "id" => $domainid];
                            $apiresults = localAPI($command, $values, $maileradmin);
                            if ($apiresults["result"] == "success") {
                                $detailsmessage .= $LANG["sendmail01"] . $templatenameidprotect . $LANG["sendmail02"] . "<br />";
                            } else {
                                $detailsmessage .= $LANG["erroroccured"] . $apiresults["result"] . "<br />";
                            }
                        } else {
                            $detailsmessage .= "";
                        }
                    }
                }
                if ($is_status == "Deleted") {
                    if ($brokentransfers == "Cancelled") {
                        $result_update = Illuminate\Database\Capsule\Manager::table("tbldomains")->where("domain", "=", $domains)->update(["status" => "Cancelled"]);
                        $result = Illuminate\Database\Capsule\Manager::table("tbltodolist")->where("description", "LIKE", "%" . $domains . "%")->select("id")->first();
                        $todoRow = isset($result) ? $result : NULL;
                        $todoid = $todoRow && isset($todoRow->id) ? $todoRow->id : 0;
                        $result_update = Illuminate\Database\Capsule\Manager::table("tbltodolist")->where("id", "=", $todoid)->update(["status" => "Incomplete"]);
                        if (!empty($exist_domainid)) {
                            $dodelete = Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodstransfer")->where("domainid", "=", $domainid)->delete();
                        }
                        logActivity($domains . " - " . $LANG["errordomaindata"] . " - " . $LANG["errordomaincancelled"] . " - " . $actionstatusdesc);
                        $cronoutput .= "<p><span>[" . $domains . "]</span><span style=\"color:#CC0000;\"> " . $LANG["errordomaindata"] . " - " . $LANG["errordomaincancelled"] . "</span> - " . $actionstatusdesc . "<br />" . $detailsmessage . "</p>";
                    } else if ($brokentransfers == "Delete") {
                        $dodelete = Illuminate\Database\Capsule\Manager::table("tbldomains")->where("domain", "=", $domains)->delete();
                        $result = Illuminate\Database\Capsule\Manager::table("tbltodolist")->where("description", "LIKE", "%" . $domains . "%")->select("id")->first();
                        $todoRow = isset($result) ? $result : NULL;
                        $todoid = $todoRow && isset($todoRow->id) ? $todoRow->id : 0;
                        $result_update = Illuminate\Database\Capsule\Manager::table("tbltodolist")->where("id", "=", $todoid)->update(["status" => "Incomplete"]);
                        if (!empty($exist_domainid)) {
                            $dodelete = Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodstransfer")->where("domainid", "=", $domainid)->delete();
                        }
                        logActivity($domains . " - " . $LANG["errordomaindata"] . " - " . $LANG["errordomaindeleted"] . " - " . $actionstatusdesc);
                        $cronoutput .= "<p><span>[" . $domains . "]</span><span style=\"color:#CC0000;\"> " . $LANG["errordomaindata"] . " - " . $LANG["errordomaindeleted"] . "</span> - " . $actionstatusdesc . "<br />" . $detailsmessage . "</p>";
                    } else {
                        logActivity($domains . " - " . $LANG["errordomaindata"] . " - " . $LANG["errordomainnoaction"] . " - " . $actionstatusdesc);
                        $cronoutput .= "<p><span>[" . $domains . "]</span><span style=\"color:#CC0000;\"> " . $LANG["errordomaindata"] . " - " . $LANG["errordomainnoaction"] . "</span> - " . $actionstatusdesc . "<br />" . $detailsmessage . "</p>";
                    }
                } else if ($is_status == "InActive") {
                    $is_p++;
                    logActivity($domains . " - " . $LANG["ispending"] . " - " . $LANG["stillpending"] . " - " . $actionstatusdesc);
                    $cronoutput .= "<p><span>[" . $domains . "]</span><span style=\"color:#333333;\"> " . $LANG["stillpending"] . " - " . $actionstatusdesc . "</span><br />" . $detailsmessage . "</p>";
                } else if ($is_status == "Active") {
                    $is_a++;
                    $result = Illuminate\Database\Capsule\Manager::table("tbltodolist")->where("status", "=", "In Progress")->where("description", "LIKE", "%" . $domains . "%")->select("id")->first();
                    $todoRow = isset($result) ? $result : NULL;
                    $todoid = $todoRow && isset($todoRow->id) ? $todoRow->id : 0;
                    $result_update = Illuminate\Database\Capsule\Manager::table("tbldomains")->where("domain", "=", $domains)->update(["status" => $is_status, "expirydate" => $is_endtime, "nextduedate" => $is_endtime, "nextinvoicedate" => $is_endtime]);
                    logActivity($domains . " - " . $LANG["iscompleted"] . " - " . $LANG["transfercompleted"] . " " . $LANG["updatesuccessmessage"]);
                    $cronoutput .= "<p><span style=\"color:#006633;font-weight:bold;\">[" . $domains . "] " . $LANG["iscompleted"] . "</span> " . $LANG["transfercompleted"] . " <span style=\"color:#006633\">" . $LANG["updatesuccessmessage"] . "</span><br />";
                    if (!empty($todoid)) {
                        $result_update = Illuminate\Database\Capsule\Manager::table("tbltodolist")->where("id", "=", $todoid)->update(["status" => "Completed"]);
                        logActivity($domains . " - " . $LANG["updatetodolist"] . " " . $LANG["updatesuccessmessage"]);
                        $cronoutput .= $LANG["updatetodolist"] . " <span style=\"color:#006633\">" . $LANG["updatesuccessmessage"] . "</span><br />";
                    } else {
                        logActivity($domains . " - " . $LANG["noupdatetodolist"]);
                        $cronoutput .= $LANG["noupdatetodolist"] . "<br />";
                    }
                    if (!empty($exist_domainid)) {
                        $dodelete = Illuminate\Database\Capsule\Manager::table("mod_resellerclubmodstransfer")->where("domainid", "=", $domainid)->delete();
                    }
                    if ($sendconfmail == "on") {
                        logActivity($LANG["sendmail01"] . $emailtplname . $LANG["sendmail02"]);
                        if ($emailtplname != "None") {
                            $command = "SendEmail";
                            $values = ["messagename" => $emailtplname, "id" => $domainid];
                            $apiresults = localAPI($command, $values, $maileradmin);
                            if ($apiresults["result"] == "success") {
                                $cronoutput .= $LANG["sendmail01"] . $emailtplname . $LANG["sendmail02"] . "<br />";
                            } else {
                                $cronoutput .= $LANG["erroroccured"] . $apiresults["result"] . "<br />";
                            }
                        }
                    }
                    $cronoutput .= "</p>";
                } else {
                    logActivity($domains . " - " . $LANG["errordomaindata"] . " - " . $LANG["errordomainnoaction"]);
                    $cronoutput .= "<p><span>[" . $domains . "]</span><span style=\"color:#CC0000;\"> " . $LANG["errordomaindata"] . " - " . $LANG["errordomainnoaction"] . "</span></p>";
                }
                unset($actionstatusdesc);
                unset($detailsmessage);
            }
        }
        $cronoutput .= "<p><strong>" . count($tbldomains) . "</strong> " . $LANG["domainschecked"] . " - <strong>" . $is_a++ . "</strong> " . $LANG["domaintransfered"] . " - <strong>" . $is_p++ . "</strong> " . $LANG["dompendtrans"] . " - <strong>" . $is_c++ . "</strong> " . $LANG["domtranscancelled"] . "</p>";
        $time = microtime();
        $time = explode(" ", $time);
        $time = $time[1] + $time[0];
        $finish = $time;
        $total_time = round($finish - $start, 4);
        $cronoutput .= "<p><strong>" . $LANG["checkfinished"] . "</strong> " . $total_time . " " . $LANG["seconds"] . "</p>";
        $cronoutput .= "</div>";
        if (is_array($tbldomains) && !empty($tbldomains)) {
            if ($adminconfmail != "on") {
                $mailtpltype = "RCM Domain Transfer Check Report";
                $mailsubject = "Cron Transfer Domain Check - " . $domainregistrar . " (" . $rcauth_userid . ")";
                $mailmessage = $cronoutput;
                $apiadminuser = $maileradmin;
                adminemailmessages($mailtpltype, $mailsubject, $mailmessage, $apiadminuser);
            }
        } else {
            logActivity("Transfer Check Cron: " . $LANG["nopendingtransfer"]);
        }
    } else {
        logActivity("Transfer check stopped: Cron Job Disabled");
        exit("Transfer Cron Job Disabled");
    }

?>