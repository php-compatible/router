<?php
namespace PhpCompatible\Router;

require_once __DIR__ . "/../router.php";

/**
 * Router class wrapper for cleaner route definitions
 *
 * Usage in routes/web.php:
 *   use PhpCompatible\Router\Router;
 *   
 *   Router::get('/', function() { ... });
 *   Router::post('/users', function() { ... });
 *   Router::group('/api', function() {
 *       Router::get('/users', function() { ... });
 *   });
 */
class Router
{
    /**
     * Check if path matches the URL (auto-detects params)
     * @param string $path URL path (may contain :param placeholders)
     * @return bool
     */
    private static function pathMatches($path)
    {
        if (strpos($path, ':') !== false) {
            return url_path_params($path);
        }
        return url_path($path);
    }

    /**
     * Define a GET route
     * @param string $path URL path (supports :param placeholders)
     * @param callable|string $action Handler function or class name
     * @return void
     */
    public static function get($path, $action)
    {
        route(method(GET), self::pathMatches($path), $action);
    }

    /**
     * Define a POST route
     * @param string $path URL path (supports :param placeholders)
     * @param callable|string $action Handler function or class name
     * @return void
     */
    public static function post($path, $action)
    {
        route(method(POST), self::pathMatches($path), $action);
    }

    /**
     * Define a PUT route
     * @param string $path URL path (supports :param placeholders)
     * @param callable|string $action Handler function or class name
     * @return void
     */
    public static function put($path, $action)
    {
        route(method(PUT), self::pathMatches($path), $action);
    }

    /**
     * Define a DELETE route
     * @param string $path URL path (supports :param placeholders)
     * @param callable|string $action Handler function or class name
     * @return void
     */
    public static function delete($path, $action)
    {
        route(method(DELETE), self::pathMatches($path), $action);
    }

    /**
     * Define a PATCH route
     * @param string $path URL path (supports :param placeholders)
     * @param callable|string $action Handler function or class name
     * @return void
     */
    public static function patch($path, $action)
    {
        route(method(PATCH), self::pathMatches($path), $action);
    }

    /**
     * Define a HEAD route
     * @param string $path URL path (supports :param placeholders)
     * @param callable|string $action Handler function or class name
     * @return void
     */
    public static function head($path, $action)
    {
        route(method(HEAD), self::pathMatches($path), $action);
    }

    /**
     * Define an OPTIONS route
     * @param string $path URL path (supports :param placeholders)
     * @param callable|string $action Handler function or class name
     * @return void
     */
    public static function options($path, $action)
    {
        route(method(OPTIONS), self::pathMatches($path), $action);
    }

    /**
     * Define a route that matches any HTTP method
     * @param string $path URL path (supports :param placeholders)
     * @param callable|string $action Handler function or class name
     * @return void
     */
    public static function any($path, $action)
    {
        route(true, self::pathMatches($path), $action);
    }

    /**
     * Define a route that matches multiple HTTP methods
     * @param array $methods Array of HTTP methods (e.g., array(GET, POST))
     * @param string $path URL path (supports :param placeholders)
     * @param callable|string $action Handler function or class name
     * @return void
     */
    public static function match($methods, $path, $action)
    {
        $method_match = in_array($_SERVER['REQUEST_METHOD'], $methods, true);
        route($method_match, self::pathMatches($path), $action);
    }

    /**
     * Group routes under a common prefix
     * @param string $prefix URL prefix for the group
     * @param callable $routes Function containing grouped routes
     * @return void
     */
    public static function group($prefix, $routes)
    {
        routerGroup($prefix, $routes);
    }

    /**
     * Run the router
     * @param callable $routes Function containing route definitions
     * @return void
     */
    public static function run($routes)
    {
        router($routes);
    }

    /**
     * Set the root URL prefix for subsites
     * @param string $root The root URL prefix
     * @return void
     */
    public static function setRoot($root)
    {
        set_root_url($root);
    }

    /**
     * Build a URL with the root prefix
     * @param string $path Path to append to root
     * @return string
     */
    public static function rootUrl($path = '')
    {
        return root_url($path);
    }

    /**
     * Redirect to a URL
     * @param string $url URL to redirect to
     * @return void
     */
    public static function redirect($url)
    {
        redirect($url);
    }

    /**
     * Serve static files from a folder
     * @param string $folder_path Absolute or relative path to the static folder
     * @param string $url_prefix URL prefix to match (e.g., '/static', '/assets')
     * @param array $options Optional settings (cache_time, allowed_extensions)
     * @return void
     */
    public static function staticFolder($folder_path, $url_prefix, $options = array())
    {
        static_folder($folder_path, $url_prefix, $options);
    }
}
