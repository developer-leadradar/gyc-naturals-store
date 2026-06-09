<?php
/**
 * DB Connectivity Diagnostic — remove after fixing
 * Accessible at /test-db.php on the Vercel deployment
 */
header('Content-Type: text/plain; charset=utf-8');

echo "=== GYC Naturals — DB Diagnostic v3 ===\n\n";

// 1. Environment
echo "--- Environment ---\n";
echo "PHP version : " . PHP_VERSION . "\n";
echo "OS          : " . PHP_OS . "\n";
echo "SAPI        : " . PHP_SAPI . "\n\n";

// 2. DB env vars
echo "--- DB Env Vars ---\n";
$driver = getenv('DB_DRIVER') ?: '(not set)';
$host   = getenv('DB_HOST')   ?: '(not set)';
$port   = getenv('DB_PORT')   ?: '5432';
$dbname = getenv('DB_NAME')   ?: '(not set)';
$user   = getenv('DB_USER')   ?: '(not set)';
$pass   = getenv('DB_PASS')   ?: '(not set)';
echo "DB_DRIVER : $driver\n";
echo "DB_HOST   : $host\n";
echo "DB_PORT   : $port\n";
echo "DB_NAME   : $dbname\n";
echo "DB_USER   : $user\n\n";

// 3. DNS resolution
echo "--- DNS Resolution ---\n";
if ($host !== '(not set)') {
    $ip4 = gethostbyname($host);
    echo "gethostbyname => " . ($ip4 === $host ? "FAILED (returned same string)" : $ip4) . "\n";

    $records = @dns_get_record($host, DNS_A);
    if ($records) {
        foreach ($records as $r) echo "dns_get_record A  => " . $r['ip'] . "\n";
    } else {
        echo "dns_get_record A  => FAILED\n";
    }
    $aaaa = @dns_get_record($host, DNS_AAAA);
    if ($aaaa) {
        foreach ($aaaa as $r) echo "dns_get_record AAAA => " . ($r['ipv6'] ?? $r['ip'] ?? '?') . "\n";
    } else {
        echo "dns_get_record AAAA => FAILED\n";
    }
}
echo "\n";

// 4. Resolve first A-record IP for subsequent tests
$resolvedIp = null;
if ($host !== '(not set)') {
    $recs4 = @dns_get_record($host, DNS_A) ?: [];
    if (!empty($recs4)) {
        shuffle($recs4);
        $resolvedIp = $recs4[0]['ip'] ?? null;
    }
}

// 5. Neon HTTP Query API
// The HTTP query API works on the DIRECT endpoint (not pooler).
// Pooler only speaks the PostgreSQL wire protocol on port 5432/443.
$directHost = preg_replace('/-pooler(?=\.)/', '', $host);  // strip -pooler
$directIp   = null;
$dirRecs    = @dns_get_record($directHost, DNS_A) ?: [];
if (!empty($dirRecs)) { shuffle($dirRecs); $directIp = $dirRecs[0]['ip'] ?? null; }

echo "--- Neon HTTP Query API ---\n";
echo "Direct host: $directHost (IP=$directIp)\n";
if ($host !== '(not set)' && function_exists('curl_init') && $pass !== '(not set)') {

    // Shared curl helper
    $doQuery = function(string $url, string $ip, string $connStr, string $label)
               use ($pass, $payload): void {
        $payload = json_encode(['query' => 'SELECT 1 AS ok', 'params' => []]);
        $host    = parse_url($url, PHP_URL_HOST);
        $opts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 12,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                "Neon-Connection-String: $connStr",
            ],
            CURLOPT_SSL_VERIFYPEER => true,
        ];
        if ($ip) $opts[CURLOPT_RESOLVE] = ["$host:443:$ip"];
        $ch = curl_init($url);
        curl_setopt_array($ch, $opts);
        $resp = curl_exec($ch);
        $err  = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        echo "$label: HTTP $code" . ($err ? " | $err" : "") . "\n";
        if ($resp) echo "  response: " . substr($resp, 0, 300) . "\n";
    };

    $connStrPooler = "postgresql://" . urlencode($user) . ":" . urlencode($pass) . "@$host/$dbname";
    $connStrDirect = "postgresql://" . urlencode($user) . ":" . urlencode($pass) . "@$directHost/$dbname";

    // Test direct endpoint /query (primary — this is what HTTP API uses)
    $doQuery("https://$directHost/query", $directIp ?? '', $connStrDirect,
             "DIRECT /query  [conn-string, IP=$directIp]");

    // Test direct endpoint /query with bearer auth too
    $ch4 = curl_init("https://$directHost/query");
    $opts4 = [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 12,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode(['query' => 'SELECT 1 AS ok', 'params' => []]),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            "Authorization: Bearer $pass",
            "Neon-Connection-String: $connStrDirect",
        ],
        CURLOPT_SSL_VERIFYPEER => true,
    ];
    if ($directIp) $opts4[CURLOPT_RESOLVE] = ["$directHost:443:$directIp"];
    curl_setopt_array($ch4, $opts4);
    $r4 = curl_exec($ch4); $e4 = curl_error($ch4); $c4 = curl_getinfo($ch4, CURLINFO_HTTP_CODE);
    curl_close($ch4);
    echo "DIRECT /query  [bearer+conn-str]:          HTTP $c4" . ($e4 ? " | $e4" : "") . "\n";
    if ($r4) echo "  response: " . substr($r4, 0, 300) . "\n";

    // Still test pooler endpoint for comparison
    $doQuery("https://$host/query", $resolvedIp ?? '', $connStrPooler,
             "POOLER /query  [conn-string, IP=$resolvedIp]");

} else {
    echo "curl unavailable or env vars missing\n";
}
echo "\n";

// 6. PDO pgsql with resolved IP + options=endpoint (fallback test)
echo "--- PDO pgsql: host=resolvedIP + options=endpoint ---\n";
if ($host !== '(not set)' && $resolvedIp && class_exists('PDO')) {
    $endpointId = preg_match('/^(ep-[a-z0-9-]+?)(?:-pooler)?(?:\.|$)/', $host, $m) ? $m[1] : null;
    $epOpt      = $endpointId ? ";options=endpoint=$endpointId" : '';
    $dsn        = "pgsql:host=$resolvedIp;port=$port;dbname=$dbname;sslmode=require$epOpt";
    echo "DSN: $dsn\n";
    try {
        $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_TIMEOUT => 10]);
        $row = $pdo->query("SELECT 1 AS ok")->fetch(PDO::FETCH_ASSOC);
        echo "PDO connected! ok=" . ($row['ok'] ?? '?') . "\n";
    } catch (PDOException $e) {
        echo "PDO failed: " . $e->getMessage() . "\n";
    }
} else {
    echo "Skipping (resolvedIp=$resolvedIp, host=$host)\n";
}
echo "\n";

// 7. TCP socket test
echo "--- TCP Socket port 5432 ---\n";
if ($host !== '(not set)') {
    $errno = $errstr = null;
    $s = @fsockopen($host, (int)$port, $errno, $errstr, 5);
    if ($s) { fclose($s); echo "fsockopen(hostname) => OK\n"; }
    else     echo "fsockopen(hostname) => FAILED: [$errno] $errstr\n";

    if ($resolvedIp) {
        $s2 = @fsockopen($resolvedIp, (int)$port, $errno, $errstr, 5);
        if ($s2) { fclose($s2); echo "fsockopen($resolvedIp) => OK\n"; }
        else      echo "fsockopen($resolvedIp) => FAILED: [$errno] $errstr\n";
    }
}
echo "\n";

// 8. General curl to well-known HTTPS host
echo "--- curl HTTPS sanity (https://httpbin.org/get) ---\n";
if (function_exists('curl_init')) {
    $ch = curl_init('https://httpbin.org/get');
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 6, CURLOPT_SSL_VERIFYPEER => false]);
    $r = curl_exec($ch);
    $e = curl_error($ch);
    $c = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo "httpbin.org/get => HTTP $c" . ($e ? " | error: $e" : "") . "\n";
    if ($r) echo "  " . substr($r, 0, 120) . "\n";
}
echo "\n";

// 9. PDO drivers
echo "--- PDO Drivers ---\n";
if (class_exists('PDO')) echo implode(', ', PDO::getAvailableDrivers()) . "\n";
else echo "PDO not loaded\n";
echo "\n";

echo "=== End of diagnostic ===\n";
