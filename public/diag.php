<?php
if (($_ENV['APP_ENV'] ?? 'production') === 'production') { http_response_code(404); exit; }
echo "<pre>";
echo "CWD: ".getcwd()."\n";
echo "__DIR__: ".__DIR__."\n";
echo "APP_ENV: ".($_ENV['APP_ENV'] ?? 'n/a')."\n";
echo "APP_URL: ".($_ENV['APP_URL'] ?? 'n/a')."\n";
