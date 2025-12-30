<?php
/**
 * Load appropriate error handler based on PHP version
 */
if (PHP_VERSION_ID >= 70000) {
    require_once __DIR__ . "/error_handler_php7.php";
} else {
    require_once __DIR__ . "/error_handler_php5.php";
}
