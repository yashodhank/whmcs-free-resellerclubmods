<?php
if (!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
$method = "GET";
$apifunction = "/api/billing/reseller-balance.json";
$data = ["reseller-id" => $rcauth_userid];
$xml_fundsbalancedetails = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
$newavailable_balance = $xml_fundsbalancedetails["sellingcurrencybalance"];
$newblocked_balance = $xml_fundsbalancedetails["sellingcurrencylockedbalance"];
$blockedbalance = $newblocked_balance * $is_mplicator;
$totalbalance = $newavailable_balance * $is_mplicator;
$availablebalance = $totalbalance - $blockedbalance * $is_mplicator;
echo $configuredto;
echo "<h3>" . $LANG["resellerclubbalance"] . "</h3>";
echo "<table bgcolor=\"#cccccc\" cellspacing=\"1\" width=\"50%\"><tr style=\"text-align: left; font-weight: bold;\" bgcolor=\"#efefef\">";
echo "<td>" . $LANG["availablefunds"] . "</td><td>" . $LANG["blockedfunds"] . "</td><td>" . $LANG["totalfunds"] . "</td>";
echo "</tr><tr style=\"text-align: center;\" bgcolor=\"#ffffff\">";
echo "<td><strong style=\"color:" . $availfundscolor . "\">" . round($availablebalance, 2) . "</strong>&nbsp;" . $reseller_buycurrency . "</td>";
echo "<td><strong style=\"color:" . $blockedfundscolor . "\">" . round($blockedbalance, 2) . "</strong>&nbsp;" . $reseller_buycurrency . "</td>";
echo "<td><strong style=\"color:" . $totalfundscolor . "\">" . round($totalbalance, 2) . "</strong>&nbsp;" . $reseller_buycurrency . "</td>";
echo "</tr></table>";

?>