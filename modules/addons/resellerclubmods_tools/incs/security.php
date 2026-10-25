<?php
/**
 * Shared security helpers for RC & LB Tools (CSRF, XSS, logging, cron, funds math).
 * Required from incs/functions.php — do not include standalone without WHMCS bootstrap.
 */
if (!defined("WHMCS") && PHP_SAPI !== "cli") {
    exit("This file cannot be accessed directly");
}

if (!function_exists("rcm_e")) {
    /**
     * HTML-escape for admin/client output (XSS sink helper).
     */
    function rcm_e($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
    }
}

if (!function_exists("rcm_token_field")) {
    /**
     * CSRF hidden field using WHMCS generate_token when available.
     */
    function rcm_token_field()
    {
        if (function_exists("generate_token")) {
            return generate_token("form");
        }
        return "";
    }
}

if (!function_exists("rcm_require_post_token")) {
    /**
     * Require a valid WHMCS CSRF token on mutating POST requests. Fail closed.
     */
    function rcm_require_post_token()
    {
        if (strtoupper((string) ($_SERVER["REQUEST_METHOD"] ?? "GET")) !== "POST") {
            return;
        }
        if (function_exists("check_token")) {
            check_token("WHMCS.admin.default");
            return;
        }
        if (function_exists("generate_token") && isset($_POST["token"])) {
            $expected = generate_token("plain");
            if (is_string($expected) && $expected !== "" && hash_equals($expected, (string) $_POST["token"])) {
                return;
            }
        }
        http_response_code(403);
        exit("Invalid CSRF token");
    }
}

if (!function_exists("rcm_deny_direct_http")) {
    /**
     * Cron scripts must not be reachable over HTTP (RCM-001). No access-key backdoor.
     */
    function rcm_deny_direct_http()
    {
        if (PHP_SAPI === "cli") {
            return;
        }
        $method = strtoupper((string) ($_SERVER["REQUEST_METHOD"] ?? ""));
        if ($method === "GET" || $method === "POST" || $method === "HEAD" || isset($_SERVER["HTTP_HOST"])) {
            http_response_code(403);
            header("Content-Type: text/plain; charset=UTF-8");
            exit("Forbidden: this script is CLI-only. Use php -q …/cron/….php [reseller_id]");
        }
    }
}

if (!function_exists("rcm_redact_for_log")) {
    /**
     * Deep-redact secrets from arrays/strings before module logging.
     */
    function rcm_redact_for_log($value)
    {
        $secretKeys = [
            "passwd", "password", "new-passwd", "new_passwd", "api-key", "api_key",
            "token", "tid", "auth-password", "rcauth_password", "autoauthkey",
            "wid_key", "secret", "authorization", "auth-userid",
        ];
        if (is_array($value)) {
            $out = [];
            foreach ($value as $k => $v) {
                $keyLower = strtolower((string) $k);
                $isSecret = false;
                foreach ($secretKeys as $sk) {
                    if ($keyLower === $sk || strpos($keyLower, $sk) !== false) {
                        $isSecret = true;
                        break;
                    }
                }
                if ($isSecret) {
                    $out[$k] = "[REDACTED]";
                } else {
                    $out[$k] = rcm_redact_for_log($v);
                }
            }
            return $out;
        }
        if (is_string($value)) {
            $redacted = preg_replace("/(api-key|passwd|password|token|tid)=([^&\\s]+)/i", "$1=[REDACTED]", $value);
            return $redacted === null ? "[REDACTED]" : $redacted;
        }
        return $value;
    }
}

if (!function_exists("rcm_log_module_call")) {
    /**
     * Only supported door for module debug logging — always redacts secrets.
     */
    function rcm_log_module_call($module, $action, $request, $response = "", $data2 = "", $data3 = "")
    {
        if (!function_exists("logModuleCall")) {
            return;
        }
        logModuleCall(
            $module,
            $action,
            rcm_redact_for_log($request),
            rcm_redact_for_log($response),
            rcm_redact_for_log($data2),
            rcm_redact_for_log($data3)
        );
    }
}

if (!function_exists("rcm_api_ok")) {
    /**
     * True when an LB API response is a usable non-error payload.
     */
    function rcm_api_ok($result)
    {
        if ($result === null || $result === false || $result === "") {
            return false;
        }
        if (!is_array($result)) {
            // Some endpoints return bare integers/strings on success (e.g. customer id).
            return true;
        }
        if (isset($result["status"]) && strtoupper((string) $result["status"]) === "ERROR") {
            return false;
        }
        if (isset($result["error"])) {
            return false;
        }
        return true;
    }
}

if (!function_exists("rcm_load_addon_conf")) {
    /**
     * Cached tbladdonmodules read for module resellerclubmods_tools.
     */
    function rcm_load_addon_conf($force = false)
    {
        static $cache = null;
        if ($cache !== null && !$force) {
            return $cache;
        }
        $cache = [];
        try {
            foreach (Illuminate\Database\Capsule\Manager::table("tbladdonmodules")->where("module", "resellerclubmods_tools")->get() as $addonvars) {
                $cache[(string) $addonvars->setting] = (string) ($addonvars->value ?? "");
            }
        } catch (Exception $e) {
            $cache = [];
        }
        return $cache;
    }
}

if (!function_exists("rcm_account_apikey")) {
    /**
     * Resolve API key from addon config by account number (avoid DB denorm).
     */
    function rcm_account_apikey($accountNumber, $vars = null)
    {
        if ($vars === null) {
            $vars = rcm_load_addon_conf();
        }
        $map = [1 => "first", 2 => "second", 3 => "third", 4 => "fourth"];
        $n = (int) $accountNumber;
        if (!isset($map[$n])) {
            return "";
        }
        $key = $map[$n] . "_rcauth_apikey";
        return isset($vars[$key]) ? (string) $vars[$key] : "";
    }
}

if (!function_exists("rcm_compute_funds")) {
    /**
     * One scale pass: total/blocked scaled once; available = total − blocked (never re-scale blocked).
     *
     * @return array{total:float,blocked:float,available:float,multiplicator:int}
     */
    function rcm_compute_funds($sellingBalance, $lockedBalance, $currencyCode = "")
    {
        $mult = 1;
        $thousands = ["AMD", "BYR", "BIF", "COP", "CDF", "GNF", "IDR", "KHR", "KPW", "LAK", "LBP", "MGA", "MNT", "PYG", "RWF", "STD", "SLL", "SOS", "TZS", "UGX", "VND", "YER"];
        if ($currencyCode !== "" && in_array($currencyCode, $thousands, true)) {
            $mult = 1000;
        } else if ($currencyCode !== "" && function_exists("is_inthousands") && is_inthousands($currencyCode)) {
            $mult = 1000;
        }
        $total = round((float) $sellingBalance * $mult, 2);
        $blocked = round((float) $lockedBalance * $mult, 2);
        $available = round($total - $blocked, 2);
        return [
            "total" => $total,
            "blocked" => $blocked,
            "available" => $available,
            "multiplicator" => $mult,
        ];
    }
}

if (!function_exists("rcm_funds_priority")) {
    /**
     * Priority band: high (at/below threshold), medium (between threshold and 1.5×), low (above 1.5×).
     *
     * @return string high|medium|low
     */
    function rcm_funds_priority($available, $threshold)
    {
        $available = (float) $available;
        $threshold = (float) $threshold;
        $mediumCeiling = round($threshold * 1.5, 2);
        if ($available <= $threshold) {
            return "high";
        }
        if ($available < $mediumCeiling) {
            return "medium";
        }
        return "low";
    }
}

if (!function_exists("rcm_session_cache_get")) {
    /**
     * JSON session cache get (avoids unserialize object injection).
     */
    function rcm_session_cache_get($key)
    {
        if (!isset($_SESSION[$key]) || $_SESSION[$key] === "" || $_SESSION[$key] === null) {
            return null;
        }
        $raw = $_SESSION[$key];
        if (is_array($raw)) {
            return $raw;
        }
        $decoded = json_decode((string) $raw, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }
        // Legacy migrate: one-shot unserialize of arrays only, then rewrite as JSON.
        if (is_string($raw) && function_exists("unserialize")) {
            $legacy = @unserialize($raw, ["allowed_classes" => false]);
            if (is_array($legacy)) {
                rcm_session_cache_set($key, $legacy);
                return $legacy;
            }
        }
        return null;
    }
}

if (!function_exists("rcm_session_cache_set")) {
    function rcm_session_cache_set($key, $value)
    {
        $_SESSION[$key] = json_encode($value);
    }
}

if (!function_exists("rcm_whois_wid_expected")) {
    /**
     * Derive public wid token from configured wid_key. Empty/short/default keys rejected by caller.
     */
    function rcm_whois_wid_expected($widKey)
    {
        return hash_hmac("sha256", "rcm-whois-v1", (string) $widKey);
    }
}

if (!function_exists("rcm_whois_key_acceptable")) {
    function rcm_whois_key_acceptable($widKey)
    {
        $widKey = (string) $widKey;
        if (strlen($widKey) < 16) {
            return false;
        }
        $banned = ["changeme", "change-me", "default", "password", "secret", "wid_key", "your-secret-key"];
        foreach ($banned as $b) {
            if (strcasecmp($widKey, $b) === 0) {
                return false;
            }
        }
        return true;
    }
}

if (!function_exists("rcm_safe_relative_goto")) {
    /**
     * Allow only relative WHMCS paths for AutoAuth goto (no open redirect).
     */
    function rcm_safe_relative_goto($destination, $fallback = "clientarea.php")
    {
        $destination = trim((string) $destination);
        if ($destination === "") {
            return $fallback;
        }
        if (preg_match('#^(https?:)?//#i', $destination) || strpos($destination, "\\") !== false) {
            return $fallback;
        }
        if (isset($destination[0]) && $destination[0] === "/") {
            return $fallback;
        }
        if (strpos($destination, "..") !== false) {
            return $fallback;
        }
        return $destination;
    }
}

if (!function_exists("rcm_try_lock")) {
    /**
     * Non-blocking exclusive flock (skip-if-busy).
     * Lock file under ROOTDIR/storage (preferred), else attachments, else sys temp.
     * Returns: open file handle on success; null if lock file cannot be created
     * (degraded proceed without exclusion); false if another process holds the lock
     * (caller should skip). Caller must rcm_release_lock($fh) when not false.
     *
     * Behavior (NP-002 / NP-003): overlapping cron/admin sync or a second DailyCronJob
     * process skips rather than double-writing tblpricing or stacking LB API calls.
     */
    function rcm_try_lock($name)
    {
        $safe = preg_replace("/[^a-zA-Z0-9_-]/", "", (string) $name);
        if ($safe === "") {
            return null;
        }
        $dir = sys_get_temp_dir();
        if (defined("ROOTDIR")) {
            if (is_dir(ROOTDIR . "/storage") && is_writable(ROOTDIR . "/storage")) {
                $dir = ROOTDIR . "/storage";
            } elseif (is_dir(ROOTDIR . "/attachments") && is_writable(ROOTDIR . "/attachments")) {
                $dir = ROOTDIR . "/attachments";
            }
        }
        $path = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . "rcm_" . $safe . ".lock";
        $fh = @fopen($path, "c+");
        if ($fh === false) {
            return null;
        }
        if (!flock($fh, LOCK_EX | LOCK_NB)) {
            fclose($fh);
            return false;
        }
        ftruncate($fh, 0);
        fwrite($fh, (string) getmypid() . "\n");
        fflush($fh);
        return $fh;
    }
}

if (!function_exists("rcm_release_lock")) {
    /**
     * Release a handle from rcm_try_lock(). Safe to call with false/null.
     */
    function rcm_release_lock($fh)
    {
        if ($fh === false || $fh === null) {
            return;
        }
        if (is_resource($fh)) {
            @flock($fh, LOCK_UN);
            @fclose($fh);
        }
    }
}

if (!function_exists("rcm_php_security_release_ok")) {
    /**
     * True only on PHP releases that include the October 2026 engine fixes.
     * 8.2.35–8.2.x, 8.3.36–8.3.x, 8.4.27–8.4.x, 8.5.12–8.5.x.
     * Older lines have no official fix. Future majors warn until this predicate is updated.
     * Passing $version lets CLI asserts check the ranges without the host PHP.
     */
    function rcm_php_security_release_ok($version = null)
    {
        $version = $version === null ? PHP_VERSION : (string) $version;
        if (version_compare($version, "8.2.35", ">=") && version_compare($version, "8.3.0", "<")) {
            return true;
        }
        if (version_compare($version, "8.3.36", ">=") && version_compare($version, "8.4.0", "<")) {
            return true;
        }
        if (version_compare($version, "8.4.27", ">=") && version_compare($version, "8.5.0", "<")) {
            return true;
        }
        if (version_compare($version, "8.5.12", ">=") && version_compare($version, "8.6.0", "<")) {
            return true;
        }
        return false;
    }
}

if (!function_exists("rcm_domain_max_bytes")) {
    function rcm_domain_max_bytes()
    {
        return 253;
    }
}

if (!function_exists("rcm_tid_max_bytes")) {
    function rcm_tid_max_bytes()
    {
        return 2048;
    }
}

if (!function_exists("rcm_domain_import_max_ids")) {
    function rcm_domain_import_max_ids()
    {
        return 3000;
    }
}

if (!function_exists("rcm_whois_byte_cap")) {
    function rcm_whois_byte_cap()
    {
        return 262144;
    }
}

if (!function_exists("rcm_whois_deadline_seconds")) {
    function rcm_whois_deadline_seconds()
    {
        return 8;
    }
}

if (!function_exists("rcm_whois_socket_seconds")) {
    function rcm_whois_socket_seconds()
    {
        return 3;
    }
}

if (!function_exists("rcm_public_curl_timeout")) {
    /**
     * Curl timeout for public whois.php and authlogin.php only.
     * Stays under the WHOIS deadline so curl_exec cannot sit until max_execution_time.
     * call_api() keeps its 120s default when this is not passed.
     */
    function rcm_public_curl_timeout()
    {
        return 5;
    }
}

if (!function_exists("rcm_stream_cap_error")) {
    function rcm_stream_cap_error()
    {
        return "response exceeded size limit";
    }
}

if (!function_exists("rcm_whois_timeout_error")) {
    function rcm_whois_timeout_error()
    {
        return "whois lookup timed out";
    }
}

if (!function_exists("rcm_bytes_within_cap")) {
    /**
     * Byte length cap for raw input. Use before idna_convert and before API calls.
     */
    function rcm_bytes_within_cap($value, $maxBytes)
    {
        return strlen((string) $value) <= (int) $maxBytes;
    }
}

if (!function_exists("rcm_read_stream_capped")) {
    /**
     * Read at most $maxBytes. Over the limit, return a short error and do not keep the tail.
     * Stops without reading the stream to EOF once the cap is exceeded.
     */
    function rcm_read_stream_capped($stream, $maxBytes = 262144)
    {
        $maxBytes = (int) $maxBytes;
        if (!is_resource($stream) || $maxBytes < 1) {
            return rcm_stream_cap_error();
        }
        $output = "";
        while (!feof($stream)) {
            $remaining = $maxBytes - strlen($output);
            if ($remaining <= 0) {
                break;
            }
            $chunk = fread($stream, $remaining);
            if ($chunk === false || $chunk === "") {
                break;
            }
            $output .= $chunk;
        }
        if (strlen($output) >= $maxBytes) {
            $extra = fread($stream, 1);
            if ($extra !== false && $extra !== "") {
                return rcm_stream_cap_error();
            }
        }
        return $output;
    }
}
