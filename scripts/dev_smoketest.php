<?php
declare(strict_types=1);

$port = getenv('APP_DEV_PORT') ?: '8081';
$base = rtrim(getenv('APP_URL') ?: "http://127.0.0.1:{$port}", '/');
$phpBinary = PHP_BINARY;
$docRoot = __DIR__ . '/../public';

$serverCmd = sprintf('%s -S 127.0.0.1:%s -t %s', escapeshellarg($phpBinary), $port, escapeshellarg($docRoot));
$tmpDir = sys_get_temp_dir();
$stdoutLog = $tmpDir . '/smoke_server.log';
$stderrLog = $tmpDir . '/smoke_server.err';
$descriptors = [
    0 => ['pipe', 'r'],
    1 => ['file', $stdoutLog, 'w'],
    2 => ['file', $stderrLog, 'w'],
];

echo "Starting built-in server: {$serverCmd}\n";
$process = proc_open($serverCmd, $descriptors, $pipes);
if (!\is_resource($process)) {
    fwrite(STDERR, "Unable to start PHP built-in server.\n");
    exit(1);
}

$cleanup = function () use (&$process) {
    if (\is_resource($process)) {
        $status = proc_get_status($process);
        if ($status['running']) {
            proc_terminate($process);
            // Allow graceful shutdown
            usleep(200000);
            if ($status['running']) {
                proc_terminate($process, 9);
            }
        }
        proc_close($process);
    }
};
register_shutdown_function($cleanup);

// Give the server time to boot
usleep(500000);
$status = proc_get_status($process);
if (!$status['running']) {
    $cleanup();
    $err = file_exists($stderrLog) ? trim((string)file_get_contents($stderrLog)) : 'unknown error';
    fwrite(STDERR, "PHP built-in server failed to start: {$err}\n");
    exit(1);
}

$request = function (string $url) {
    $ctx = stream_context_create(['http' => ['timeout' => 5]]);
    $currentUrl = $url;
    $headers = [];
    $status = 'NO-RESPONSE';
    for ($i = 0; $i < 5; $i++) {
        $body = @file_get_contents($currentUrl, false, $ctx);
        $headers = $http_response_header ?? [];
        $status = $headers[0] ?? 'NO-RESPONSE';
        if ($body !== false && strpos($status, '200') !== false) {
            return [$status, $body, $headers];
        }
        if (preg_match('#^HTTP/\S+\s+30[1237]#', (string)$status)) {
            $location = null;
            foreach ($headers as $line) {
                if (stripos($line, 'Location:') === 0) {
                    $location = trim(substr($line, 9));
                    break;
                }
            }
            if ($location) {
                if (!preg_match('#^https?://#i', $location)) {
                    $parts = parse_url($currentUrl);
                    if ($parts && !empty($parts['host'])) {
                        $scheme = $parts['scheme'] ?? 'http';
                        $host = $parts['host'];
                        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
                        if ($location[0] !== '/') {
                            $path = rtrim(dirname($parts['path'] ?? ''), '/');
                            $location = $scheme . '://' . $host . $port . $path . '/' . $location;
                        } else {
                            $location = $scheme . '://' . $host . $port . $location;
                        }
                    }
                }
                if ($location) {
                    $currentUrl = $location;
                    continue;
                }
            }
        }
        usleep(200000);
    }
    return [$status, $body, $headers];
};

$endpoints = [
    '/ping.txt' => function ($status, $body, $headers) {
        $text = is_string($body) ? trim($body) : '';
        if (strpos($status, '200') === false || $text !== 'ok') {
            throw new RuntimeException("ping.txt failed: {$status} body={$text}");
        }
    },
    '/health' => function ($status, $body, $headers) {
        if (strpos($status, '200') === false) {
            throw new RuntimeException("health returned {$status}");
        }
        $json = json_decode($body, true);
        if (!is_array($json) || ($json['db'] ?? null) !== 'ok') {
            throw new RuntimeException("health payload unexpected: {$body}");
        }
    },
    '/company/TCS' => function ($status, $body, $headers) {
        if (strpos($status, '200') === false) {
            throw new RuntimeException("company page returned {$status}");
        }
        if (strpos($body, 'TCS · Tata Consultancy Services') === false) {
            throw new RuntimeException("company page missing company heading");
        }
    },
    '/dashboard/superadmin/users' => function ($status, $body, $headers) {
        if (strpos($status, '200') === false) {
            throw new RuntimeException("superadmin route redirect did not resolve to login (status {$status})");
        }
        if (strpos((string)$body, 'Sign in to continue screening') === false) {
            throw new RuntimeException("login form not rendered when accessing protected route");
        }
    },
];

foreach ($endpoints as $ep => $assert) {
    $url = $base . $ep;
    echo "Checking {$url}\n";
    [$status, $body, $headers] = $request($url);
    echo "  Status: {$status}\n";
    try {
        $assert($status, $body ?? '', $headers);
        echo "  OK\n";
    } catch (RuntimeException $e) {
        fwrite(STDERR, "  FAIL: ".$e->getMessage()."\n");
        exit(1);
    }
}

echo "Smoketest completed successfully.\n";
