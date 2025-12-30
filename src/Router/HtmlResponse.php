<?php
namespace PhpCompatible\Router;

require_once __DIR__ . "/../response.php";

/**
 * PSR-7 compatible HTML response
 *
 * Can be returned from Single Action Controllers.
 * The router will automatically send the response.
 */
class HtmlResponse
{
    private $content;
    private $statusCode;
    private $headers;

    /**
     * Create an HTML response
     * @param int $statusCode HTTP status code
     * @param string $content HTML content
     * @param array $headers Additional headers
     */
    public function __construct($statusCode = HTTP_OK, $content = '', $headers = array())
    {
        $this->statusCode = $statusCode;
        $this->content = $content;
        $this->headers = $headers;
    }

    /**
     * Get the response content
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Get the status code
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Get headers
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Return new instance with different content
     * @param string $content
     * @return HtmlResponse
     */
    public function withContent($content)
    {
        $new = clone $this;
        $new->content = $content;
        return $new;
    }

    /**
     * Return new instance with different status code
     * @param int $code
     * @return HtmlResponse
     */
    public function withStatus($code)
    {
        $new = clone $this;
        $new->statusCode = $code;
        return $new;
    }

    /**
     * Return new instance with additional header
     * @param string $name Header name
     * @param string $value Header value
     * @return HtmlResponse
     */
    public function withHeader($name, $value)
    {
        $new = clone $this;
        $new->headers[$name] = $value;
        return $new;
    }

    /**
     * Send the response
     * @return void
     */
    public function send()
    {
        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }
        echo html_response($this->statusCode, $this->content);
    }

    /**
     * Get the body
     * @return string
     */
    public function getBody()
    {
        return $this->content;
    }

    /**
     * Create an HTML response with status code and content
     * @param int $code HTTP status code (use HTTP_* constants)
     * @param string $content HTML content
     * @return HtmlResponse
     */
    public static function response($code, $content = '')
    {
        return new self($code, $content);
    }

    /**
     * Create a response from a view file
     * @param string $filepath Path to HTML/PHP file
     * @param array $data Variables to extract into view scope
     * @param int $statusCode HTTP status code
     * @return HtmlResponse
     */
    public static function view($filepath, $data = array(), $statusCode = HTTP_OK)
    {
        if (!file_exists($filepath)) {
            throw new \Exception('View not found: ' . $filepath, HTTP_NOT_FOUND);
        }

        // Use unique variable names to prevent $data from overwriting them
        $__filepath__ = $filepath;
        $__statusCode__ = $statusCode;
        unset($filepath, $statusCode);

        extract($data, EXTR_SKIP);
        ob_start();
        include $__filepath__;
        $__content__ = ob_get_clean();

        return new self($__statusCode__, $__content__);
    }
}
