<?php
$base = getenv('APP_URL') ?: 'http://127.0.0.1:8081';
$eps = ['/ping.txt', '/health', '/company/TCS'];

function hit($url){
    $ctx = stream_context_create(['http'=>['timeout'=>5]]);
    $body = @file_get_contents($url, false, $ctx);
    $status = $http_response_header[0] ?? 'NO-RESPONSE';
    return [$status,$body];
}

echo "BASE: $base\n";
foreach ($eps as $ep) {
    [$status,$body] = hit($base.$ep);
    echo ">> $ep : $status\n";
    if ($ep==='/health' && $body) echo "BODY: $body\n";
}
