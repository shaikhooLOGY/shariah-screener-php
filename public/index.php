<?php
declare(strict_types=1);

$root = __DIR__ . '/..'; // because local dev serves from /public
require $root.'/core/Bootstrap.php';
Core\Bootstrap::init($root);

require $root.'/core/Router.php';
require $root.'/routes/web.php';

// Important: Router is namespaced Core
\Core\dispatch();
