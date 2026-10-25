<?php
/**
 * Pure-PHP asserts for request bounds and the patched-PHP predicate (no WHMCS bootstrap).
 */
define("WHMCS", true); // satisfy security.php guard for CLI include
require_once dirname(__DIR__) . "/modules/addons/resellerclubmods_tools/incs/security.php";

$failures = 0;
function assert_true($cond, $msg)
{
    global $failures;
    if (!$cond) {
        fwrite(STDERR, "FAIL: $msg\n");
        $failures++;
    } else {
        echo "OK: $msg\n";
    }
}

assert_true(rcm_php_security_release_ok() === rcm_php_security_release_ok(PHP_VERSION), "live PHP_VERSION matches explicit predicate call");
assert_true(rcm_php_security_release_ok("8.2.34") === false, "8.2.34 is outside the patched set");
assert_true(rcm_php_security_release_ok("8.2.35") === true, "8.2.35 is patched");
assert_true(rcm_php_security_release_ok("8.3.35") === false, "8.3.35 is outside the patched set");
assert_true(rcm_php_security_release_ok("8.3.36") === true, "8.3.36 is patched");
assert_true(rcm_php_security_release_ok("8.1.33") === false, "8.1.33 has no official fix");
assert_true(rcm_php_security_release_ok("7.4") === false, "7.4 has no official fix");
assert_true(rcm_php_security_release_ok("7.4.33") === false, "7.4.33 has no official fix");
assert_true(rcm_php_security_release_ok("8.4.26") === false, "8.4.26 is outside the patched set");
assert_true(rcm_php_security_release_ok("8.4.27") === true, "8.4.27 is patched");
assert_true(rcm_php_security_release_ok("8.5.11") === false, "8.5.11 is outside the patched set");
assert_true(rcm_php_security_release_ok("8.5.12") === true, "8.5.12 is patched");
assert_true(rcm_php_security_release_ok("8.5.13") === true, "later 8.5 patch stays inside the 8.5 window");
assert_true(rcm_php_security_release_ok("8.6.0") === false, "future major 8.6 warns until the predicate is updated");
assert_true(rcm_php_security_release_ok("9.0.0") === false, "future major 9.0 warns until the predicate is updated");

$short = fopen("php://temp", "r+");
fwrite($short, "hello");
rewind($short);
$shortRead = rcm_read_stream_capped($short, 100);
assert_true($shortRead === "hello", "capped reader returns a short body");
fclose($short);

$exact = fopen("php://temp", "r+");
fwrite($exact, str_repeat("A", 32));
rewind($exact);
$exactRead = rcm_read_stream_capped($exact, 32);
assert_true($exactRead === str_repeat("A", 32), "capped reader keeps a body that is exactly the limit");
fclose($exact);

$over = fopen("php://temp", "r+");
fwrite($over, str_repeat("A", 32) . "TAIL");
rewind($over);
$overRead = rcm_read_stream_capped($over, 32);
assert_true($overRead === rcm_stream_cap_error(), "capped reader returns the short error at the limit");
assert_true(is_string($overRead) && strpos($overRead, "TAIL") === false, "capped reader does not keep the tail");
assert_true(strlen($overRead) < 32, "capped reader does not return the truncated body");
fclose($over);

assert_true(rcm_domain_max_bytes() === 253, "domain cap is 253 bytes");
assert_true(rcm_bytes_within_cap(str_repeat("a", 253), rcm_domain_max_bytes()) === true, "253-byte domain is accepted");
assert_true(rcm_bytes_within_cap(str_repeat("a", 254), rcm_domain_max_bytes()) === false, "254-byte domain is rejected");
assert_true(rcm_tid_max_bytes() === 2048, "tid cap is 2048 bytes");
assert_true(rcm_bytes_within_cap(str_repeat("t", 2048), rcm_tid_max_bytes()) === true, "2048-byte tid is accepted");
assert_true(rcm_bytes_within_cap(str_repeat("t", 2049), rcm_tid_max_bytes()) === false, "2049-byte tid is rejected");

assert_true(rcm_whois_byte_cap() === 262144, "WHOIS read cap is 256KB");
assert_true(rcm_whois_deadline_seconds() === 8, "WHOIS deadline is 8 seconds");
assert_true(rcm_whois_socket_seconds() === 3, "WHOIS socket timeout is 3 seconds");
assert_true(rcm_public_curl_timeout() > 0 && rcm_public_curl_timeout() < rcm_whois_deadline_seconds(), "public curl timeout is under the WHOIS deadline");
assert_true(rcm_public_curl_timeout() < 120, "public curl timeout is below the admin call_api default");
assert_true(rcm_domain_import_max_ids() === 3000, "domain import id cap is 3000");

if ($failures > 0) {
    fwrite(STDERR, "$failures assertion(s) failed\n");
    exit(1);
}
echo "All request bounds asserts passed\n";
exit(0);
