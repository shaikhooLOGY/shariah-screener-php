<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/vendor/autoload.php';

use Core\Bootstrap;
use Core\Router;
use Core\ErrorHandler;

ErrorHandler::register();
$bootstrap = new Bootstrap();
$bootstrap->init();

header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');

$router = new Router();
require dirname(__DIR__) . '/routes/web.php';
require dirname(__DIR__) . '/routes/admin.php';
require dirname(__DIR__) . '/routes/superadmin.php';
require dirname(__DIR__) . '/routes/mufti.php';

$router->dispatch();