<?php
require_once __DIR__ . "/stop.php";
require_once __DIR__ . "/request.php";
require_once __DIR__ . "/response.php";

/** HTTP methods */
define('GET', 'GET');
define('HEAD', 'HEAD');
define('POST', 'POST');
define('PUT', 'PUT');
define('DELETE', 'DELETE');
define('CONNECT', 'CONNECT');
define('OPTIONS', 'OPTIONS');
define('TRACE', 'TRACE');
define('PATCH', 'PATCH');

/** @var string Root URL prefix for subsites */
$ROOT_URL = '';

function set_root_url($root)
{
    global $ROOT_URL;
    $root = str_replace('\\', '/', $root);
    $ROOT_URL = '/' . trim($root, '/');
    if ($ROOT_URL === '/') {
        $ROOT_URL = '';
    }
}

function root_url($path = '')
{
    global $ROOT_URL;
    if ($path === '' || $path === null) {
        return $ROOT_URL === '' ? '/' : $ROOT_URL;
    }
    $path = str_replace('\\', '/', $path);
    if (empty($ROOT_URL)) {
        return $path;
    }
    if ($path === '/') {
        return $ROOT_URL . '/';
    }
    if ($path[0] === '/') {
        return $ROOT_URL . $path;
    }
    return $ROOT_URL . '/' . $path;
}

function method($method)
{
    return $_SERVER['REQUEST_METHOD'] === $method;
}

function url_path($path)
{
    if ($path === '*') {
        return true;
    }
    if ($path === '/') {
        $path = '';
    } elseif (!empty($path) && $path[0] === '/') {
        $path = substr($path, 1);
    }
    global $PATH_PREFIX;
    if (!empty($PATH_PREFIX)) {
        $path = $PATH_PREFIX . "/" . $path;
    }
    if (strlen($path) > 0 && substr($path, -1) === '/') {
        $path = substr($path, 0, -1);
    }
    return $path === $_GET['url'];
}

function url_path_params($path)
{
    if ($path[0] === '/') {
        $path = substr($path, 1);
    }
    global $PATH_PREFIX;
    if (!empty($PATH_PREFIX)) {
        $path = $PATH_PREFIX . "/" . $path;
    }
    $query_string_parts = explode('/', $_GET['url']);
    $path_parts = explode('/', $path);
    if (count($query_string_parts) !== count($path_parts)) {
        return false;
    }
    $pos = strpos($path, ':');
    if ($pos !== false) {
        if (substr($path, 0, $pos) === substr($_GET['url'], 0, $pos)) {
            foreach ($query_string_parts as $part => $query_string_part) {
                if ($path_parts[$part][0] === ':') {
                    $_GET[$path_parts[$part]] = $query_string_part;
                }
            }
            return true;
        }
    }
    return false;
}

function routerGroup($prefix, $routes)
{
    global $PATH_PREFIX;
    $previous_prefix = $PATH_PREFIX;
    $PATH_PREFIX .= $prefix;
    if (!empty($PATH_PREFIX) && $PATH_PREFIX[0] === '/') {
        $PATH_PREFIX = substr($PATH_PREFIX, 1);
    }
    if (strlen($PATH_PREFIX) > 0 && substr($PATH_PREFIX, -1) === '/') {
        $PATH_PREFIX = substr($PATH_PREFIX, 0, -1);
    }
    call_user_func($routes);
    $PATH_PREFIX = $previous_prefix;
}

function render_error($e)
{
    $code = $e->getCode();
    if (!$code) {
        $code = HTTP_INTERNAL_SERVER_ERROR;
    }
    if (accept(JSON_CONTENT)) {
        echo json_response($code, array(
            'error' => array(
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            )
        ));
    } else {
        echo html_response($code, $e->getMessage());
    }
}

require_once __DIR__ . "/error_handler.php";

function router($routes)
{
    ini_set('display_errors', 'Off');
    error_reporting(0);
    $error = _router_run_routes($routes);
    if ($error !== null) {
        error_log("{$error->getCode()}: {$error->getMessage()}" . PHP_EOL . $error->getTraceAsString());
        render_error($error);
        return;
    }
    if (isset($GLOBALS['ROUTER_STOP_CODE'])) {
        return;
    }
    $notFound = new Exception("Not Found", 404);
    error_log("{$notFound->getCode()}: {$notFound->getMessage()}" . PHP_EOL . $notFound->getTraceAsString());
    render_error($notFound);
}

function route($http_method_predicate, $path_predicate, $action)
{
    if (!$http_method_predicate || !$path_predicate) {
        return;
    }
    if (is_string($action)) {
        $action = new $action();
    }

    $result = _invoke_action($action);
    _handle_response($result);
    stop();
}

/**
 * Invoke an action, optionally passing PSR-7 style request
 * @param callable $action The action to invoke
 * @return mixed The action result
 */
function _invoke_action($action)
{
    // Check if action expects parameters using reflection
    if (is_object($action) && method_exists($action, '__invoke')) {
        $reflection = new ReflectionMethod($action, '__invoke');
    } elseif ($action instanceof Closure) {
        $reflection = new ReflectionFunction($action);
    } else {
        // Simple callable, just call it
        return call_user_func_array($action, array());
    }

    $params = $reflection->getParameters();

    if (count($params) === 0) {
        // No parameters expected
        return call_user_func_array($action, array());
    }

    // Build arguments based on parameter type hints
    $args = array();
    foreach ($params as $param) {
        $class = null;

        // PHP 5.x compatible way to get type hint
        if (method_exists($param, 'getClass')) {
            $classReflection = $param->getClass();
            if ($classReflection !== null) {
                $class = $classReflection->getName();
            }
        }

        if ($class !== null) {
            // Check for ServerRequest type hint
            if ($class === 'PhpCompatible\\Router\\ServerRequest' ||
                substr($class, -13) === 'ServerRequest') {
                require_once __DIR__ . '/Router/ServerRequest.php';
                $args[] = new \PhpCompatible\Router\ServerRequest();
            } else {
                // Unknown type, try to instantiate
                $args[] = new $class();
            }
        } else {
            // No type hint, pass ServerRequest by default for first param
            if (count($args) === 0) {
                require_once __DIR__ . '/Router/ServerRequest.php';
                $args[] = new \PhpCompatible\Router\ServerRequest();
            } else {
                $args[] = null;
            }
        }
    }

    return call_user_func_array($action, $args);
}

/**
 * Handle action response (PSR-7 style or raw)
 * @param mixed $result The action result
 * @return void
 */
function _handle_response($result)
{
    if ($result === null) {
        return;
    }

    // Check for JsonResponse
    if (is_object($result)) {
        $class = get_class($result);
        if ($class === 'PhpCompatible\\Router\\JsonResponse' ||
            substr($class, -12) === 'JsonResponse') {
            $result->send();
            return;
        }

        // Generic response object with send() method
        if (method_exists($result, 'send')) {
            $result->send();
            return;
        }
    }

    // Raw output (string, array converted to JSON)
    if (is_array($result)) {
        echo json_response(HTTP_OK, $result);
    } elseif (is_string($result)) {
        echo $result;
    }
}

function redirect($url)
{
    header('Location: ' . $url, true);
    stop();
}

function use_request_uri()
{
    $url = $_SERVER['REQUEST_URI'];
    if ($url[0] === '/') {
        $url = substr($url, 1);
    }
    $_GET['url'] = $url;
}

function file_router($routes, $file = null)
{
    global $PATH_PREFIX;
    if ($file === null) {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
        $file = $backtrace[0]['file'];
    }
    $document_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    $file_path = str_replace('\\', '/', $file);
    if (substr($document_root, -1) === '/') {
        $document_root = substr($document_root, 0, -1);
    }
    $relative_path = '';
    if (strpos($file_path, $document_root) === 0) {
        $relative_path = substr($file_path, strlen($document_root));
    }
    if (!empty($relative_path) && $relative_path[0] === '/') {
        $relative_path = substr($relative_path, 1);
    }
    if (substr($relative_path, -4) === '.php') {
        $relative_path = substr($relative_path, 0, -4);
    }
    if (substr($relative_path, -6) === '/index') {
        $relative_path = substr($relative_path, 0, -6);
    } elseif ($relative_path === 'index') {
        $relative_path = '';
    } elseif (substr($relative_path, -8) === '/default') {
        $relative_path = substr($relative_path, 0, -8);
    } elseif ($relative_path === 'default') {
        $relative_path = '';
    }
    $previous_prefix = $PATH_PREFIX;
    $PATH_PREFIX = $relative_path;
    router($routes);
    $PATH_PREFIX = $previous_prefix;
}

/**
 * Get MIME type from file extension
 * @param string $extension File extension (without dot)
 * @return string MIME type
 */
function mime_type($extension)
{
    $extension = strtolower($extension);
    $types = array(
        // Text
        'html' => 'text/html',
        'htm' => 'text/html',
        'css' => 'text/css',
        'js' => 'text/javascript',
        'json' => 'application/json',
        'xml' => 'text/xml',
        'txt' => 'text/plain',
        'csv' => 'text/csv',
        'md' => 'text/markdown',

        // Images
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'webp' => 'image/webp',
        'bmp' => 'image/bmp',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',

        // Fonts
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'otf' => 'font/otf',
        'eot' => 'application/vnd.ms-fontobject',

        // Documents
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',

        // Archives
        'zip' => 'application/zip',
        'gz' => 'application/gzip',
        'tar' => 'application/x-tar',
        'rar' => 'application/vnd.rar',
        '7z' => 'application/x-7z-compressed',

        // Audio
        'mp3' => 'audio/mpeg',
        'wav' => 'audio/wav',
        'ogg' => 'audio/ogg',
        'flac' => 'audio/flac',
        'm4a' => 'audio/mp4',

        // Video
        'mp4' => 'video/mp4',
        'webm' => 'video/webm',
        'avi' => 'video/x-msvideo',
        'mov' => 'video/quicktime',
        'mkv' => 'video/x-matroska',

        // Other
        'swf' => 'application/x-shockwave-flash',
        'wasm' => 'application/wasm',
    );

    if (isset($types[$extension])) {
        return $types[$extension];
    }

    return 'application/octet-stream';
}

/**
 * Check if URL starts with prefix and return the remaining path
 * @param string $prefix URL prefix to match
 * @return string|false Remaining path after prefix, or false if not matched
 */
function url_path_starts_with($prefix)
{
    if (!empty($prefix) && $prefix[0] === '/') {
        $prefix = substr($prefix, 1);
    }
    if (strlen($prefix) > 0 && substr($prefix, -1) !== '/') {
        $prefix = $prefix . '/';
    }

    global $PATH_PREFIX;
    $url = $_GET['url'];
    if (!empty($PATH_PREFIX)) {
        $full_prefix = $PATH_PREFIX . '/' . $prefix;
    } else {
        $full_prefix = $prefix;
    }

    // Check for exact prefix match (for files directly under the prefix)
    $prefix_without_slash = rtrim($full_prefix, '/');
    if (strpos($url, $prefix_without_slash) === 0) {
        $remaining = substr($url, strlen($prefix_without_slash));
        if ($remaining === '' || $remaining[0] === '/') {
            return ltrim($remaining, '/');
        }
    }

    return false;
}

/**
 * Serve static files from a folder
 * @param string $folder_path Absolute or relative path to the static folder
 * @param string $url_prefix URL prefix to match (e.g., '/static', '/assets')
 * @param array $options Optional settings (cache_time, allowed_extensions)
 * @return void
 */
function static_folder($folder_path, $url_prefix, $options = array())
{
    $relative_path = url_path_starts_with($url_prefix);
    if ($relative_path === false) {
        return;
    }

    // Normalize folder path
    $folder_path = str_replace('\\', '/', $folder_path);
    if (substr($folder_path, -1) !== '/') {
        $folder_path = $folder_path . '/';
    }

    // Security: prevent directory traversal
    $relative_path = str_replace('\\', '/', $relative_path);
    $relative_path = preg_replace('/\.\.+/', '', $relative_path);
    $relative_path = preg_replace('/\/+/', '/', $relative_path);
    $relative_path = ltrim($relative_path, '/');

    if (empty($relative_path)) {
        return;
    }

    $file_path = $folder_path . $relative_path;
    $real_folder = realpath($folder_path);
    $real_file = realpath($file_path);

    // Security: ensure file is within folder
    if ($real_folder === false || $real_file === false) {
        return;
    }

    $real_folder = str_replace('\\', '/', $real_folder);
    $real_file = str_replace('\\', '/', $real_file);

    if (strpos($real_file, $real_folder) !== 0) {
        return;
    }

    if (!is_file($real_file)) {
        return;
    }

    // Check allowed extensions if specified
    if (isset($options['allowed_extensions'])) {
        $ext = pathinfo($real_file, PATHINFO_EXTENSION);
        if (!in_array(strtolower($ext), $options['allowed_extensions'])) {
            return;
        }
    }

    // Get MIME type
    $extension = pathinfo($real_file, PATHINFO_EXTENSION);
    $mime = mime_type($extension);

    // Set cache headers
    $cache_time = isset($options['cache_time']) ? $options['cache_time'] : 86400;
    if ($cache_time > 0) {
        header('Cache-Control: public, max-age=' . $cache_time);
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $cache_time) . ' GMT');
    }

    // Set content type
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($real_file));

    // Output file
    readfile($real_file);
    stop();
}
