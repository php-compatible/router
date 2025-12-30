<?php
namespace PhpCompatible\Router;

require_once __DIR__ . "/../response.php";

/**
 * Response class wrapper for cleaner response handling
 *
 * Usage:
 *   use PhpCompatible\Router\Response;
 *
 *   echo Response::json(HTTP_OK, array('status' => 'ok'));
 *   echo Response::html(HTTP_OK, '<h1>Hello</h1>');
 *   Response::download('file.pdf', $content, PDF_CONTENT);
 */
class Response
{
    /**
     * Set content type header and return content
     * @param string $type Content type
     * @param string $content Content to return
     * @return string
     */
    public static function content($type, $content)
    {
        return content($type, $content);
    }

    /**
     * Configure and encode array to JSON
     * @param int $code HTTP status code (use HTTP_* constants)
     * @param array $data Data to encode
     * @return string
     */
    public static function json($code, $data)
    {
        return json_response($code, $data);
    }

    /**
     * Configure and return HTML content
     * @param int $code HTTP status code (use HTTP_* constants)
     * @param string $html HTML content
     * @return string
     */
    public static function html($code, $html)
    {
        return html_response($code, $html);
    }

    /**
     * Configure and return plain text content
     * @param int $code HTTP status code (use HTTP_* constants)
     * @param string $text Text content
     * @return string
     */
    public static function text($code, $text)
    {
        return text_response($code, $text);
    }

    /**
     * Configure and return XML content
     * @param int $code HTTP status code (use HTTP_* constants)
     * @param string $xml XML content
     * @return string
     */
    public static function xml($code, $xml)
    {
        return xml_response($code, $xml);
    }

    /**
     * Set a response header
     * @param string $header Header name
     * @param string $value Header value
     * @return void
     */
    public static function header($header, $value)
    {
        response_header($header, $value);
    }

    /**
     * Send a file download response
     * @param string $mime_type MIME type of the file
     * @param string $content File content
     * @param string|null $filename Suggested filename for download
     * @return void
     */
    public static function download($mime_type, $content, $filename = null)
    {
        download_response($mime_type, $content, $filename);
    }

    /**
     * Send a file from disk as download
     * @param string $filepath Path to the file
     * @param string|null $filename Suggested filename
     * @param string|null $mime_type MIME type
     * @return void
     */
    public static function downloadFile($filepath, $filename = null, $mime_type = null)
    {
        download_file($filepath, $filename, $mime_type);
    }

    /**
     * Download JSON content
     * @param array $data Data to encode as JSON
     * @param string $filename Filename for download
     * @return void
     */
    public static function downloadJson($data, $filename)
    {
        download_json($data, $filename);
    }

    /**
     * Download CSV content
     * @param string $content CSV content
     * @param string $filename Filename for download
     * @return void
     */
    public static function downloadCsv($content, $filename)
    {
        download_csv($content, $filename);
    }

    /**
     * Download PDF content
     * @param string $content PDF content
     * @param string $filename Filename for download
     * @return void
     */
    public static function downloadPdf($content, $filename)
    {
        download_pdf($content, $filename);
    }

    /**
     * Download ZIP content
     * @param string $content ZIP content
     * @param string $filename Filename for download
     * @return void
     */
    public static function downloadZip($content, $filename)
    {
        download_zip($content, $filename);
    }

    /**
     * Download plain text content
     * @param string $content Text content
     * @param string $filename Filename for download
     * @return void
     */
    public static function downloadText($content, $filename)
    {
        download_text($content, $filename);
    }

    /**
     * Set HTTP status code
     * @param int $code HTTP status code
     * @return void
     */
    public static function status($code)
    {
        http_response_code($code);
    }
}
