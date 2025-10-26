<?php
declare(strict_types=1);

$ROOT = dirname(__DIR__);

// boot
require $ROOT . '/core/Bootstrap.php';
require $ROOT . '/core/Router.php';   // <-- must be loaded before routes

use Core\Bootstrap;
use Core\Router;

Bootstrap::init($ROOT);

// routes
require $ROOT . '/routes/web.php';

// dispatch
Router::dispatch();
