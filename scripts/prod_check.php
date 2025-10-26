<?php
$base = rtrim($_ENV['APP_URL'] ?? 'https://halal.shaikhoology.com', '/');
$eps  = ['/prod-ok.txt', '/prod-health', '/company/TCS'];
$ctx  = stream_context_create(['http'=>['timeout'=>6]]);
foreach ($eps as $ep) {
    $url = $base.$ep;
    $body = @file_get_contents($url, false, $ctx);
    $status = $http_response_header[0] ?? 'NO-RESPONSE';
    echo ">> $ep : $status\n";
    if ($ep==='/prod-health' && $body) echo "BODY: $body\n";
}
