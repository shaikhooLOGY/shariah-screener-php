<?php
namespace Core;

use FastRoute\RouteCollector;

class Router {
    public static function dispatch(): void {
        $dispatcher = \FastRoute\simpleDispatcher(function (RouteCollector $r) {
            foreach ((array)RouterRegistry::$routes as $row) {
                $r->addRoute($row[0], $row[1], $row[2]);
            }
        });

        $httpMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        if (($pos = strpos($uri, '?')) !== false) { $uri = substr($uri, 0, $pos); }
        $uri = rawurldecode($uri);

        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);
        switch ($routeInfo[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                http_response_code(404);
                $f = dirname(__DIR__) . '/public/404.html';
                if (is_file($f)) { readfile($f); } else { echo '404'; }
                return;
            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                http_response_code(405); echo '405 Method Not Allowed'; return;
            default:
                $handler = $routeInfo[1]; $vars = $routeInfo[2];
                if (is_array($handler)) {
                    $instance = new $handler[0]();
                    call_user_func_array([$instance, $handler[1]], array_values($vars));
                } else {
                    call_user_func_array($handler, array_values($vars));
                }
        }
    }
}

class RouterRegistry { public static array $routes = []; }

/** Register a route (helper) */
function route($method, $path, $handler) {
    RouterRegistry::$routes[] = [strtoupper($method), $path, $handler];
}
