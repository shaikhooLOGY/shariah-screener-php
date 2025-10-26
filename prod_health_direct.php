<?php
declare(strict_types=1);
$root = __DIR__;
require $root.'/core/Bootstrap.php';
Core\Bootstrap::init($root);
require $root.'/app/Controllers/HealthController.php';
$hc = new \App\Controllers\HealthController();
$hc->index();
