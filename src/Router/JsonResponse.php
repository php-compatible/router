<?php
namespace PhpCompatible\Router;

require_once __DIR__ . "/../response.php";

/**
 * PSR-7 compatible JSON response
 *
 * Can be returned from Single Action Controllers.
 * The router will automatically send the response.
 */
class JsonResponse
{
    private $data;
    private $statusCode;
    private $headers;

    /**
     * Create a JSON response
     * @param int $statusCode HTTP status code
     * @param mixed $data Data to encode as JSON
     * @param array $headers Additional headers
     */
    public function __construct($statusCode = HTTP_OK, $data = null, $headers = array())
    {
        $this->statusCode = $statusCode;
        $this->data = $data;
        $this->headers = $headers;
    }

    /**
     * Get the response data
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
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
     * Return new instance with different data
     * @param mixed $data
     * @return JsonResponse
     */
    public function withData($data)
    {
        $new = clone $this;
        $new->data = $data;
        return $new;
    }

    /**
     * Return new instance with different status code
     * @param int $code
     * @return JsonResponse
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
     * @return JsonResponse
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
        echo json_response($this->statusCode, $this->data);
    }

    /**
     * Get the JSON body
     * @return string
     */
    public function getBody()
    {
        return json_encode($this->data);
    }

    /**
     * Create a JSON response with status code and data
     * @param int $code HTTP status code (use HTTP_* constants)
     * @param mixed $data Response data
     * @return JsonResponse
     */
    public static function response($code, $data = null)
    {
        return new self($code, $data);
    }
}
