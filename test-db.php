<?php
/**
 * DB Connectivity Diagnostic — remove after fixing
 * Accessible at /test-db.php on the Vercel deployment
 */
header('Content-Type: text/plain; charset=utf-8');

echo "=== GYC Naturals — DB Diagnostic ===\n\n";

// 1. Environment
echo "--- Environment ---\n";
echo "PHP version : " . PHP_VERSION . "\n";
echo "OS          : " . PHP_OS . "\n";
echo "SAPI        : " . PHP_SAPI . "\n\n";

// 2. DB env vars (masked)
echo "--- DB Env Vars ---\n";
$driver = getenv('DB_DRIVER') ?: '(not set)';
$host   = getenv('DB_HOST')   ?: '(not set)';
$port   = getenv('DB_PORT')   ?: '(not set)';
$dbname = getenv('DB_NAME')   ?: '(not set)';
$user   = getenv('DB_USER')   ?: '(not set)';
echo "DB_DRIVER : $driver\n";
echo "DB_HOST   : $host\n";
echo "DB_PORT   : $port\n";
echo "DB_NAME   : $dbname\n";
echo "DB_USER   : $user\n\n";

// 3. DNS resolution test
echo "--- DNS Resolution ---\n";
if ($host !== '(not set)') {
    $ip = gethostbyname($host);
    if ($ip === $host) {
        echo "gethostbyname($host) FAILED — returned same string (no resolution)\n";
    } else {
        echo "gethostbyname($host) => $ip\n";
    }

    // Try dns_get_record
    $records = @dns_get_record($host, DNS_A);
    if ($records) {
        foreach ($records as $r) {
            echo "dns_get_record A => " . $r['ip'] . "\n";
        }
    } else {
        echo "dns_get_record($host, DNS_A) => (empty / failed)\n";
    }
} else {
    echo "DB_HOST not set — skipping DNS test\n";
}
echo "\n";

// 4. Curl / HTTP test (port 443 — does HTTPS work?)
echo "--- HTTPS Connectivity (curl) ---\n";
if (function_exists('curl_init')) {
    $ch = curl_init('https://api.neon.tech/ping');   // light endpoint
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $resp = curl_exec($ch);
    $err  = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo "curl https://api.neon.tech/ping => HTTP $code\n";
    if ($err) echo "  curl error: $err\n";
    if ($resp) echo "  response: " . substr($resp, 0, 200) . "\n";
} else {
    echo "curl not available\n";
}
echo "\n";

// 5. TCP socket test on port 5432
echo "--- TCP Socket port 5432 ---\n";
if ($host !== '(not set)') {
    $errno = $errstr = null;
    $sock = @fsockopen($host, (int)($port ?: 5432), $errno, $errstr, 5);
    if ($sock) {
        fclose($sock);
        echo "fsockopen($host:$port) => OK\n";
    } else {
        echo "fsockopen($host:$port) => FAILED: [$errno] $errstr\n";
    }
} else {
    echo "DB_HOST not set — skipping TCP test\n";
}
echo "\n";

// 6. PDO extensions loaded?
echo "--- PDO Drivers ---\n";
if (class_exists('PDO')) {
    echo implode(', ', PDO::getAvailableDrivers()) . "\n";
} else {
    echo "PDO not loaded\n";
}
echo "\n";

// 7. Try resolving a well-known host to confirm DNS works at all
echo "--- General DNS sanity (google.com) ---\n";
$gip = gethostbyname('google.com');
echo "gethostbyname(google.com) => $gip\n\n";

// 8. Try resolving with a public DNS API via curl
echo "--- DoH (DNS over HTTPS) lookup ---\n";
if (function_exists('curl_init') && $host !== '(not set)') {
    $url = 'https://dns.google/resolve?name=' . urlencode($host) . '&type=A';
    $ch  = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 5,
        CURLOPT_HTTPHEADER     => ['Accept: application/dns-json'],
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $resp = curl_exec($ch);
    $err  = curl_error($ch);
    curl_close($ch);
    if ($err) {
        echo "DoH lookup failed: $err\n";
    } else {
        $data = json_decode($resp, true);
        if (!empty($data['Answer'])) {
            foreach ($data['Answer'] as $ans) {
                echo "DoH A record => " . $ans['data'] . "\n";
            }
        } else {
            echo "DoH response: " . substr($resp, 0, 300) . "\n";
        }
    }
}
echo "\n";

echo "=== End of diagnostic ===\n";
