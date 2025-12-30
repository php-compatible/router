<?php

/**
 * Exits execution. If testing is running it returns the number instead
 * @param int $code Exit code
 * @return int
 */
function stop($code = 0)
{
    $GLOBALS["ROUTER_STOP_CODE"] = $code;

    if (!defined('PHPUNIT_COMPOSER_INSTALL') && !defined('__PHPUNIT_PHAR__')) {
        exit($code);
    }

    return $code;
}
