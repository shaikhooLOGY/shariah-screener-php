<?php
namespace Core;
use FastRoute\RouteCollector;

class Router {
  public function dispatch(): void {
    $dispatcher = \FastRoute\simpleDispatcher(function(RouteCollector $r){
      foreach ((array)RouterRegistry::$routes as $row) {
        $r->addRoute($row[0], $row[1], $row[2]);
      }
    });

    $httpMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    if (false !== $pos = strpos($uri, '?')) { $uri = substr($uri, 0, $pos); }
    $uri = rawurldecode($uri);

    $routeInfo = $dispatcher->dispatch($httpMethod, $uri);
    switch ($routeInfo[0]) {
      case \FastRoute\Dispatcher::NOT_FOUND: http_response_code(404); echo '404'; break;
      case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED: http_response_code(405); echo '405'; break;
      case \FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1]; $vars = $routeInfo[2];
        if (is_array($handler)) { $instance = new $handler[0](); call_user_func_array([$instance,$handler[1]], $vars); }
        else { call_user_func_array($handler, $vars); }
        break;
    }
  }
}
class RouterRegistry { public static array $routes = []; }
function route($method, $path, $handler) { RouterRegistry::$routes[] = [$method, $path, $handler]; }
