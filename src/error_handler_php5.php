<?php
/**
 * Run routes with error handling for PHP 5.x
 *
 * @param callable $routes
 * @return Exception|null
 */
function _router_run_routes($routes)
{
    try {
        call_user_func($routes);
        return null;
    } catch (Exception $e) {
        return $e;
    }
}
