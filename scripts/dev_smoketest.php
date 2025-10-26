<?php
declare(strict_types=1);

$base = rtrim(getenv('APP_URL') ?: 'http://127.0.0.1:8081', '/');
$phpBinary = PHP_BINARY;
$docRoot = __DIR__ . '/../public';

$serverCmd = sprintf('%s -S 127.0.0.1:8081 -t %s', escapeshellarg($phpBinary), escapeshellarg($docRoot));
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
    for ($i = 0; $i < 5; $i++) {
        $body = @file_get_contents($url, false, $ctx);
        $status = $http_response_header[0] ?? 'NO-RESPONSE';
        if ($body !== false && strpos($status, '200') !== false) {
            return [$status, $body];
        }
        usleep(200000);
    }
    return [$status ?? 'NO-RESPONSE', $body];
};

$endpoints = [
    '/ping.txt' => function ($status, $body) {
        $text = is_string($body) ? trim($body) : '';
        if (strpos($status, '200') === false || $text !== 'ok') {
            throw new RuntimeException("ping.txt failed: {$status} body={$text}");
        }
    },
    '/health' => function ($status, $body) {
        if (strpos($status, '200') === false) {
            throw new RuntimeException("health returned {$status}");
        }
        $json = json_decode($body, true);
        if (!is_array($json) || ($json['db'] ?? null) !== 'ok') {
            throw new RuntimeException("health payload unexpected: {$body}");
        }
    },
    '/company/TCS' => function ($status, $body) {
        if (strpos($status, '200') === false) {
            throw new RuntimeException("company page returned {$status}");
        }
        if (strpos($body, 'TCS · Tata Consultancy Services') === false) {
            throw new RuntimeException("company page missing company heading");
        }
    },
    '/dashboard/superadmin/users' => function ($status, $body) {
        if (strpos($status, '403') === false) {
            throw new RuntimeException("superadmin route should be forbidden without session");
        }
    },
];

foreach ($endpoints as $ep => $assert) {
    $url = $base . $ep;
    echo "Checking {$url}\n";
    [$status, $body] = $request($url);
    echo "  Status: {$status}\n";
    try {
        $assert($status, $body ?? '');
        echo "  OK\n";
    } catch (RuntimeException $e) {
        fwrite(STDERR, "  FAIL: ".$e->getMessage()."\n");
        exit(1);
    }
}

echo "Smoketest completed successfully.\n";
