<?php
namespace PhpCompatible\Router;

require_once __DIR__ . "/../request.php";

/**
 * Request class wrapper for cleaner request handling
 *
 * Usage:
 *   use PhpCompatible\Router\Request;
 *   
 *   $data = Request::json();
 *   $form = Request::form();
 *   $file = Request::file('upload');
 */
class Request
{
    /**
     * Get a request header value
     * @param string $header Header name
     * @return string
     */
    public static function header($header)
    {
        return request_header($header);
    }

    /**
     * Check if the request accepts a given content type
     * @param string $content_type Content type to check
     * @return bool
     */
    public static function accepts($content_type)
    {
        return accept($content_type);
    }

    /**
     * Get the raw request body
     * @return string
     */
    public static function body()
    {
        return request_body();
    }

    /**
     * Parse JSON from request body
     * @return array|null
     */
    public static function json()
    {
        return json_body();
    }

    /**
     * Get form data from request body
     * @return array
     */
    public static function form()
    {
        return form_body();
    }

    /**
     * Get uploaded file(s) from request
     * @param string|null $name File input name (null returns all files)
     * @return array|null
     */
    public static function file($name = null)
    {
        return file_body($name);
    }

    /**
     * Check if a file was uploaded
     * @param string $name File input name
     * @return bool
     */
    public static function hasFile($name)
    {
        return has_file($name);
    }

    /**
     * Move uploaded file to destination
     * @param string $name File input name
     * @param string $destination Destination path
     * @return bool
     */
    public static function moveFile($name, $destination)
    {
        return move_file($name, $destination);
    }

    /**
     * Get query string parameter
     * @param string $key Parameter name
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public static function query($key, $default = null)
    {
        return isset($_GET[$key]) ? $_GET[$key] : $default;
    }

    /**
     * Get POST parameter
     * @param string $key Parameter name
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public static function post($key, $default = null)
    {
        return isset($_POST[$key]) ? $_POST[$key] : $default;
    }

    /**
     * Get request method
     * @return string
     */
    public static function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Check if request method matches
     * @param string $method HTTP method to check
     * @return bool
     */
    public static function isMethod($method)
    {
        return $_SERVER['REQUEST_METHOD'] === strtoupper($method);
    }

    /**
     * Check if request is AJAX (XMLHttpRequest)
     * @return bool
     */
    public static function isAjax()
    {
        return self::header('X-Requested-With') === 'XMLHttpRequest';
    }

    /**
     * Check if request is JSON
     * @return bool
     */
    public static function isJson()
    {
        return strpos(self::header('Content-Type'), 'application/json') !== false;
    }
}
