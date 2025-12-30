<?php

require_once __DIR__ . '/http_response_codes.php';

/** Content Types */
define('ATOM_CONTENT', 'application/atom+xml');
define('CSS_CONTENT', 'text/css');
define('JAVASCRIPT_CONTENT', 'text/javascript');
define('JSON_CONTENT', 'application/json');
define('PDF_CONTENT', 'application/pdf');
define('TEXT_CONTENT', 'text/plain');
define('HTML_CONTENT', 'text/html');
define('XML_CONTENT', 'text/xml');
define('CSV_CONTENT', 'text/csv');
define('ZIP_CONTENT', 'application/zip');
define('BINARY_CONTENT', 'application/octet-stream');

/**
 * Set content type header and return content
 * @param string $type Content type
 * @param string $content Content to return
 * @return string
 */
function content($type, $content)
{
    header('Content-Type: ' . $type, true);
    return $content;
}

/**
 * Configure and encode array to JSON
 * @param int $code HTTP status code (use HTTP_* constants)
 * @param array $data Data to encode as JSON
 * @return string
 */
function json_response($code, $data)
{
    http_response_code($code);
    return content(JSON_CONTENT, json_encode($data));
}

/**
 * Configure and return HTML content
 * @param int $code HTTP status code (use HTTP_* constants)
 * @param string $html HTML content
 * @return string
 */
function html_response($code, $html)
{
    http_response_code($code);
    return content(HTML_CONTENT, $html);
}

/**
 * Configure and return plain text content
 * @param int $code HTTP status code (use HTTP_* constants)
 * @param string $text Text content
 * @return string
 */
function text_response($code, $text)
{
    http_response_code($code);
    return content(TEXT_CONTENT, $text);
}

/**
 * Configure and return XML content
 * @param int $code HTTP status code (use HTTP_* constants)
 * @param string $xml XML content
 * @return string
 */
function xml_response($code, $xml)
{
    http_response_code($code);
    return content(XML_CONTENT, $xml);
}

/**
 * Set a response header
 * @param string $header Header name
 * @param string $value Header value
 * @return void
 */
function response_header($header, $value)
{
    header("$header: $value");
}

/**
 * Send a file download response
 * @param string $mime_type MIME type of the file
 * @param string $content File content
 * @param string|null $filename Suggested filename for download (null = inline display)
 * @return void
 */
function download_response($mime_type, $content, $filename = null)
{
    header('Content-Type: ' . $mime_type);
    header('Content-Length: ' . strlen($content));

    if ($filename !== null) {
        header('Content-Disposition: attachment; filename="' . $filename . '"');
    }

    echo $content;
}

/**
 * Send a file from disk as download
 * @param string $filepath Path to the file
 * @param string|null $filename Suggested filename (null = use original filename)
 * @param string|null $mime_type MIME type (null = auto-detect)
 * @return void
 */
function download_file($filepath, $filename = null, $mime_type = null)
{
    if (!file_exists($filepath)) {
        throw new Exception('File not found: ' . $filepath, 404);
    }

    if ($filename === null) {
        $filename = basename($filepath);
    }

    if ($mime_type === null) {
        $mime_type = BINARY_CONTENT;
        if (function_exists('mime_content_type')) {
            $detected = mime_content_type($filepath);
            if ($detected !== false) {
                $mime_type = $detected;
            }
        }
    }

    header('Content-Type: ' . $mime_type);
    header('Content-Length: ' . filesize($filepath));
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    readfile($filepath);
}

// Download helpers for specific content types

/**
 * Download JSON content
 * @param array $data Data to encode as JSON
 * @param string $filename Filename for download
 * @return void
 */
function download_json($data, $filename)
{
    download_response(JSON_CONTENT, json_encode($data), $filename);
}

/**
 * Download CSV content
 * @param string $content CSV content
 * @param string $filename Filename for download
 * @return void
 */
function download_csv($content, $filename)
{
    download_response(CSV_CONTENT, $content, $filename);
}

/**
 * Download PDF content
 * @param string $content PDF content
 * @param string $filename Filename for download
 * @return void
 */
function download_pdf($content, $filename)
{
    download_response(PDF_CONTENT, $content, $filename);
}

/**
 * Download ZIP content
 * @param string $content ZIP content
 * @param string $filename Filename for download
 * @return void
 */
function download_zip($content, $filename)
{
    download_response(ZIP_CONTENT, $content, $filename);
}

/**
 * Download plain text content
 * @param string $content Text content
 * @param string $filename Filename for download
 * @return void
 */
function download_text($content, $filename)
{
    download_response(TEXT_CONTENT, $content, $filename);
}

/**
 * Download XML content
 * @param string $content XML content
 * @param string $filename Filename for download
 * @return void
 */
function download_xml($content, $filename)
{
    download_response(XML_CONTENT, $content, $filename);
}

/**
 * Download HTML content
 * @param string $content HTML content
 * @param string $filename Filename for download
 * @return void
 */
function download_html($content, $filename)
{
    download_response(HTML_CONTENT, $content, $filename);
}

/**
 * Download JavaScript content
 * @param string $content JavaScript content
 * @param string $filename Filename for download
 * @return void
 */
function download_javascript($content, $filename)
{
    download_response(JAVASCRIPT_CONTENT, $content, $filename);
}

/**
 * Download CSS content
 * @param string $content CSS content
 * @param string $filename Filename for download
 * @return void
 */
function download_css($content, $filename)
{
    download_response(CSS_CONTENT, $content, $filename);
}

/**
 * Download binary content
 * @param string $content Binary content
 * @param string $filename Filename for download
 * @return void
 */
function download_binary($content, $filename)
{
    download_response(BINARY_CONTENT, $content, $filename);
}
