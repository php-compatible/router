<?php

/**
 * HTTP Response Status Codes
 * Only load definitions if not already defined (some extensions may provide these)
 */
if (!defined('HTTP_OK')) {
    require __DIR__ . '/http_status_definitions.php';
}
