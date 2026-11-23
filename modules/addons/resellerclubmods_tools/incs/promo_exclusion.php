<?php
/**
 * Promo exclusion list (local delta, not upstream).
 *
 * Addon setting `promo_exclude_tlds` holds a comma/space separated list of TLDs
 * (".services, .fyi"). The Auto Activate Promo hook and the Auto Promo Update hook
 * skip every TLD on the list: no tblpricing write, no promo-row insert/update/delete,
 * no TLD group change. Each skip writes one activity-log line.
 *
 * Kept free of WHMCS dependencies so it can be unit-tested in isolation (PHP 7.4+).
 */
if (!defined("WHMCS")) {
    exit("This file cannot be accessed directly");
}

if (!function_exists("rcm_promo_normalize_tld")) {
    function rcm_promo_normalize_tld($tld)
    {
        $tld = strtolower(trim((string) $tld));
        $tld = trim($tld, ", \t\r\n");
        if ($tld === "" || $tld === ".") {
            return "";
        }
        if (substr($tld, 0, 1) !== ".") {
            $tld = "." . $tld;
        }
        return $tld;
    }
}

if (!function_exists("rcm_promo_excluded_tlds")) {
    /**
     * @param array $conf tbladdonmodules settings for resellerclubmods_tools
     * @return string[] normalised, unique TLDs with a leading dot
     */
    function rcm_promo_excluded_tlds($conf)
    {
        $raw = is_array($conf) && isset($conf["promo_exclude_tlds"]) ? (string) $conf["promo_exclude_tlds"] : "";
        $out = [];
        foreach (preg_split('/[\s,;]+/', $raw) ?: [] as $part) {
            $tld = rcm_promo_normalize_tld($part);
            if ($tld !== "" && !in_array($tld, $out, true)) {
                $out[] = $tld;
            }
        }
        return $out;
    }
}

if (!function_exists("rcm_promo_is_excluded")) {
    function rcm_promo_is_excluded($tld, array $excluded)
    {
        $tld = rcm_promo_normalize_tld($tld);
        return $tld !== "" && in_array($tld, $excluded, true);
    }
}

if (!function_exists("rcm_promo_log_skip")) {
    /**
     * One activity-log line per skipped promo entry.
     *
     * @param string $hook "auto-activate" or "auto-update"
     */
    function rcm_promo_log_skip($hook, $tld, $type, $promoend = "")
    {
        $line = "Cron Job (RCM): Promo " . $hook . " skipped for " . rcm_promo_normalize_tld($tld)
            . " (" . $type . ") - TLD is on the promo exclusion list (promo_exclude_tlds); no price or promo-row change";
        if ($promoend !== "") {
            $line .= "; promo end " . $promoend . " UTC";
        }
        if (function_exists("logActivity")) {
            logActivity($line);
        }
        return $line;
    }
}
