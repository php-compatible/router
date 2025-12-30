<?php
namespace PhpCompatible\Router;

require_once __DIR__ . "/../request.php";

/**
 * PSR-7 compatible server request wrapper
 *
 * Provides an object-oriented interface to the current HTTP request.
 * Can be passed to Single Action Controllers as an optional parameter.
 */
class ServerRequest
{
    private $queryParams;
    private $parsedBody;
    private $serverParams;
    private $uploadedFiles;
    private $headers;
    private $method;
    private $uri;
    private $attributes;

    public function __construct()
    {
        $this->queryParams = $_GET;
        $this->serverParams = $_SERVER;
        $this->uploadedFiles = $_FILES;
        $this->method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
        $this->uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
        $this->attributes = array();
        $this->headers = $this->extractHeaders();
        $this->parsedBody = $this->extractParsedBody();
    }

    private function extractHeaders()
    {
        $headers = array();
        if (function_exists('getallheaders')) {
            $h = getallheaders();
            if ($h !== false) {
                $headers = $h;
            }
        }
        foreach ($this->serverParams as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $name = str_replace('_', '-', substr($key, 5));
                $name = ucwords(strtolower($name), '-');
                $headers[$name] = $value;
            }
        }
        return $headers;
    }

    private function extractParsedBody()
    {
        $contentType = $this->getHeaderLine('Content-Type');
        if (strpos($contentType, 'application/json') !== false) {
            return json_body();
        }
        if ($this->method === 'POST') {
            return $_POST;
        }
        return null;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function getPath()
    {
        return isset($this->queryParams['url']) ? $this->queryParams['url'] : '';
    }

    public function getQueryParams()
    {
        return $this->queryParams;
    }

    public function getQueryParam($name, $default = null)
    {
        return isset($this->queryParams[$name]) ? $this->queryParams[$name] : $default;
    }

    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    public function getBodyParam($name, $default = null)
    {
        if ($this->parsedBody === null) {
            return $default;
        }
        return isset($this->parsedBody[$name]) ? $this->parsedBody[$name] : $default;
    }

    public function getBody()
    {
        return request_body();
    }

    public function getServerParams()
    {
        return $this->serverParams;
    }

    public function getServerParam($name, $default = null)
    {
        return isset($this->serverParams[$name]) ? $this->serverParams[$name] : $default;
    }

    public function getUploadedFiles()
    {
        return $this->uploadedFiles;
    }

    public function getUploadedFile($name)
    {
        return isset($this->uploadedFiles[$name]) ? $this->uploadedFiles[$name] : null;
    }

    public function hasUploadedFile($name)
    {
        return has_file($name);
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function hasHeader($name)
    {
        return isset($this->headers[$name]);
    }

    public function getHeaderLine($name)
    {
        foreach ($this->headers as $key => $value) {
            if (strcasecmp($key, $name) === 0) {
                return is_array($value) ? implode(', ', $value) : $value;
            }
        }
        return '';
    }

    public function getAttributes()
    {
        $attributes = $this->attributes;
        foreach ($this->queryParams as $key => $value) {
            if (isset($key[0]) && $key[0] === ':') {
                $attributes[substr($key, 1)] = $value;
            }
        }
        return $attributes;
    }

    public function getAttribute($name, $default = null)
    {
        if (isset($this->attributes[$name])) {
            return $this->attributes[$name];
        }
        $key = ':' . $name;
        if (isset($this->queryParams[$key])) {
            return $this->queryParams[$key];
        }
        return $default;
    }

    public function getParam($name, $default = null)
    {
        return $this->getAttribute($name, $default);
    }

    public function withAttribute($name, $value)
    {
        $new = clone $this;
        $new->attributes[$name] = $value;
        return $new;
    }

    public function isAjax()
    {
        return $this->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';
    }

    public function isJson()
    {
        return strpos($this->getHeaderLine('Content-Type'), 'application/json') !== false;
    }

    public function isMethod($method)
    {
        return strcasecmp($this->method, $method) === 0;
    }

    public function accepts($contentType)
    {
        return strpos($this->getHeaderLine('Accept'), $contentType) !== false;
    }
}
