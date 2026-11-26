<?php
namespace App\Core;

/**
 * Enrutador Ligero por Expresiones Regulares
 * Plataforma: XV Experience
 */
class Router
{
    private static array $routes = [];
    private static $notFoundCallback = null;

    public static function get(string $pattern, callable|array $handler): void
    {
        self::addRoute('GET', $pattern, $handler);
    }

    public static function post(string $pattern, callable|array $handler): void
    {
        self::addRoute('POST', $pattern, $handler);
    }

    public static function any(string $pattern, callable|array $handler): void
    {
        self::addRoute('GET|POST|OPTIONS', $pattern, $handler);
    }

    private static function addRoute(string $method, string $pattern, callable|array $handler): void
    {
        // Convertir formato /e/{slug} a regex #^/e/(?P<slug>[a-zA-Z0-9_-]+)$#
        $regex = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[a-zA-Z0-9_-]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        self::$routes[] = [
            'methods' => explode('|', $method),
            'pattern' => $pattern,
            'regex'   => $regex,
            'handler' => $handler
        ];
    }

    public static function setNotFound(callable $callback): void
    {
        self::$notFoundCallback = $callback;
    }

    public static function dispatch(?string $uri = null, ?string $method = null): void
    {
        $uri = $uri ?? parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $method = $method ?? ($_SERVER['REQUEST_METHOD'] ?? 'GET');

        // Limpiar subdirectorios si el script corre en subcarpeta
        $scriptName = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        if ($scriptName !== '/' && $scriptName !== '\\' && str_starts_with($uri, $scriptName)) {
            $uri = substr($uri, strlen($scriptName));
        }

        $uri = '/' . ltrim($uri, '/');
        // Eliminar trailing slash salvo que sea raíz
        if ($uri !== '/' && str_ends_with($uri, '/')) {
            $uri = rtrim($uri, '/');
        }

        foreach (self::$routes as $route) {
            if (in_array($method, $route['methods']) && preg_match($route['regex'], $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                
                http_response_code(200);
                if (is_callable($route['handler'])) {
                    call_user_func_array($route['handler'], $params);
                    return;
                }

                if (is_array($route['handler'])) {
                    [$class, $methodName] = $route['handler'];
                    if (class_exists($class) && method_exists($class, $methodName)) {
                        call_user_func_array([$class, $methodName], $params);
                        return;
                    }
                }
            }
        }

        if (self::$notFoundCallback) {
            call_user_func(self::$notFoundCallback);
        } else {
            http_response_code(404);
            echo "<!DOCTYPE html><html><head><title>404 No Encontrado</title></head><body style='background:#062E25;color:#FFD700;font-family:sans-serif;text-align:center;padding:50px;'><h1>404 · Celebración No Encontrada</h1><p style='color:#FFF;'>La invitación o página que buscas no existe o ha sido reubicada.</p><a href='/' style='color:#FFD700;'>Volver a la portada</a></body></html>";
        }
    }
}
