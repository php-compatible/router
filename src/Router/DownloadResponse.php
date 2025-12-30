<?php
namespace PhpCompatible\Router;

require_once __DIR__ . "/../response.php";

/**
 * PSR-7 compatible download response
 *
 * Can be returned from Single Action Controllers.
 * The router will automatically send the response.
 */
class DownloadResponse
{
    private $content;
    private $mimeType;
    private $filename;
    private $headers;

    /**
     * Create a download response
     * @param string $content File content
     * @param string $mimeType MIME type
     * @param string|null $filename Suggested filename (null for inline display)
     * @param array $headers Additional headers
     */
    public function __construct($content, $mimeType = BINARY_CONTENT, $filename = null, $headers = array())
    {
        $this->content = $content;
        $this->mimeType = $mimeType;
        $this->filename = $filename;
        $this->headers = $headers;
    }

    /**
     * Get the content
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Get the MIME type
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * Get the filename
     * @return string|null
     */
    public function getFilename()
    {
        return $this->filename;
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
     * @return DownloadResponse
     */
    public function withContent($content)
    {
        $new = clone $this;
        $new->content = $content;
        return $new;
    }

    /**
     * Return new instance with different filename
     * @param string $filename
     * @return DownloadResponse
     */
    public function withFilename($filename)
    {
        $new = clone $this;
        $new->filename = $filename;
        return $new;
    }

    /**
     * Return new instance with different MIME type
     * @param string $mimeType
     * @return DownloadResponse
     */
    public function withMimeType($mimeType)
    {
        $new = clone $this;
        $new->mimeType = $mimeType;
        return $new;
    }

    /**
     * Return new instance with additional header
     * @param string $name Header name
     * @param string $value Header value
     * @return DownloadResponse
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
        download_response($this->mimeType, $this->content, $this->filename);
    }

    /**
     * Get the body
     * @return string
     */
    public function getBody()
    {
        return $this->content;
    }

    // Static factory methods for common file types

    /**
     * Create a JSON download response
     * @param array $data Data to encode as JSON
     * @param string $filename Filename for download
     * @return DownloadResponse
     */
    public static function json($data, $filename)
    {
        return new self(json_encode($data), JSON_CONTENT, $filename);
    }

    /**
     * Create a CSV download response
     * @param string $content CSV content
     * @param string $filename Filename for download
     * @return DownloadResponse
     */
    public static function csv($content, $filename)
    {
        return new self($content, CSV_CONTENT, $filename);
    }

    /**
     * Create a PDF download response
     * @param string $content PDF content
     * @param string $filename Filename for download
     * @return DownloadResponse
     */
    public static function pdf($content, $filename)
    {
        return new self($content, PDF_CONTENT, $filename);
    }

    /**
     * Create a ZIP download response
     * @param string $content ZIP content
     * @param string $filename Filename for download
     * @return DownloadResponse
     */
    public static function zip($content, $filename)
    {
        return new self($content, ZIP_CONTENT, $filename);
    }

    /**
     * Create a plain text download response
     * @param string $content Text content
     * @param string $filename Filename for download
     * @return DownloadResponse
     */
    public static function text($content, $filename)
    {
        return new self($content, TEXT_CONTENT, $filename);
    }

    /**
     * Create an XML download response
     * @param string $content XML content
     * @param string $filename Filename for download
     * @return DownloadResponse
     */
    public static function xml($content, $filename)
    {
        return new self($content, XML_CONTENT, $filename);
    }

    /**
     * Create an HTML download response
     * @param string $content HTML content
     * @param string $filename Filename for download
     * @return DownloadResponse
     */
    public static function html($content, $filename)
    {
        return new self($content, HTML_CONTENT, $filename);
    }

    /**
     * Create a JavaScript download response
     * @param string $content JavaScript content
     * @param string $filename Filename for download
     * @return DownloadResponse
     */
    public static function javascript($content, $filename)
    {
        return new self($content, JAVASCRIPT_CONTENT, $filename);
    }

    /**
     * Create a CSS download response
     * @param string $content CSS content
     * @param string $filename Filename for download
     * @return DownloadResponse
     */
    public static function css($content, $filename)
    {
        return new self($content, CSS_CONTENT, $filename);
    }

    /**
     * Create a binary download response
     * @param string $content Binary content
     * @param string $filename Filename for download
     * @return DownloadResponse
     */
    public static function binary($content, $filename)
    {
        return new self($content, BINARY_CONTENT, $filename);
    }

    /**
     * Create a download response from a file on disk
     * @param string $filepath Path to the file
     * @param string|null $filename Suggested filename (null = use original)
     * @param string|null $mimeType MIME type (null = auto-detect)
     * @return DownloadResponse
     */
    public static function file($filepath, $filename = null, $mimeType = null)
    {
        if (!file_exists($filepath)) {
            throw new \Exception('File not found: ' . $filepath, 404);
        }

        if ($filename === null) {
            $filename = basename($filepath);
        }

        if ($mimeType === null) {
            $mimeType = BINARY_CONTENT;
            if (function_exists('mime_content_type')) {
                $detected = mime_content_type($filepath);
                if ($detected !== false) {
                    $mimeType = $detected;
                }
            }
        }

        $content = file_get_contents($filepath);
        return new self($content, $mimeType, $filename);
    }
}
