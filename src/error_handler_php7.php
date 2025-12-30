<?php
/**
 * Run routes with error handling for PHP 7+
 * Catches both Exception and Error
 *
 * @param callable $routes
 * @return Exception|Error|null
 */
function _router_run_routes($routes)
{
    try {
        call_user_func($routes);
        return null;
    } catch (Exception $e) {
        return $e;
    } catch (Error $e) {
        return $e;
    }
}
