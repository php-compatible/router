<?php

/**
 * Get a request header value
 * @param string $header Header name
 * @return string Header value or empty string if not found
 */
function request_header($header)
{
    // Handle CLI/test environment where getallheaders() might not work
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        if (array_key_exists($header, $headers)) {
            return $headers[$header];
        }
    }

    // Fallback to $_SERVER for CLI/test environment
    $server_key = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
    if (isset($_SERVER[$server_key])) {
        return $_SERVER[$server_key];
    }

    return "";
}

/**
 * Check if the request accepts a given content type
 * @param string $content_type Content type to check
 * @return bool
 */
function accept($content_type)
{
    return strpos(request_header('Accept'), $content_type) !== false;
}

/**
 * Get the raw request body
 * @return string
 */
function request_body()
{
    return file_get_contents("php://input");
}

/**
 * Parse JSON from request body
 * @return array|null
 */
function json_body()
{
    return json_decode(request_body(), true);
}

/**
 * Get form data from request body (application/x-www-form-urlencoded or multipart/form-data)
 * @return array
 */
function form_body()
{
    return $_POST;
}

/**
 * Get uploaded file(s) from request
 * @param string|null $name File input name (null returns all files)
 * @return array|null File info array or null if not found
 */
function file_body($name = null)
{
    if ($name === null) {
        return $_FILES;
    }

    return isset($_FILES[$name]) ? $_FILES[$name] : null;
}

/**
 * Check if a file was uploaded
 * @param string $name File input name
 * @return bool
 */
function has_file($name)
{
    return isset($_FILES[$name]) && $_FILES[$name]['error'] === UPLOAD_ERR_OK;
}

/**
 * Move uploaded file to destination
 * @param string $name File input name
 * @param string $destination Destination path
 * @return bool
 */
function move_file($name, $destination)
{
    if (!has_file($name)) {
        return false;
    }

    return move_uploaded_file($_FILES[$name]['tmp_name'], $destination);
}
