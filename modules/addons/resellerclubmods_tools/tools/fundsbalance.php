<?php
if (!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}
$method = "GET";
$apifunction = "/api/billing/reseller-balance.json";
$data = ["reseller-id" => $rcauth_userid];
$xml_fundsbalancedetails = call_api($rcauth_userid, $rcauth_password, $rchttp_api, $apifunction, $data, $method);
$funds = rcm_compute_funds(
    $xml_fundsbalancedetails["sellingcurrencybalance"] ?? 0,
    $xml_fundsbalancedetails["sellingcurrencylockedbalance"] ?? 0,
    $reseller_buycurrency ?? ""
);
$blockedbalance = $funds["blocked"];
$totalbalance = $funds["total"];
$availablebalance = $funds["available"];
echo $configuredto;
echo "<h3>" . $LANG["resellerclubbalance"] . "</h3>";
echo "<table bgcolor=\"#cccccc\" cellspacing=\"1\" width=\"50%\"><tr style=\"text-align: left; font-weight: bold;\" bgcolor=\"#efefef\">";
echo "<td>" . $LANG["availablefunds"] . "</td><td>" . $LANG["blockedfunds"] . "</td><td>" . $LANG["totalfunds"] . "</td>";
echo "</tr><tr style=\"text-align: center;\" bgcolor=\"#ffffff\">";
echo "<td><strong style=\"color:" . $availfundscolor . "\">" . round($availablebalance, 2) . "</strong>&nbsp;" . rcm_e($reseller_buycurrency) . "</td>";
echo "<td><strong style=\"color:" . $blockedfundscolor . "\">" . round($blockedbalance, 2) . "</strong>&nbsp;" . rcm_e($reseller_buycurrency) . "</td>";
echo "<td><strong style=\"color:" . $totalfundscolor . "\">" . round($totalbalance, 2) . "</strong>&nbsp;" . rcm_e($reseller_buycurrency) . "</td>";
echo "</tr></table>";

?>
