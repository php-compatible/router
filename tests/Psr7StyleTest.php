<?php

use PHPUnit\Framework\TestCase;
use PhpCompatible\Router\ServerRequest;
use PhpCompatible\Router\JsonResponse;
use PhpCompatible\Router\HtmlResponse;
use PhpCompatible\Router\DownloadResponse;

class Psr7StyleTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test';
        $_GET = array('url' => '');
        $_POST = array();
        $GLOBALS['ROUTER_STOP_CODE'] = true;
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['ROUTER_STOP_CODE']);
        $_GET = array();
        $_POST = array();
    }

    // ServerRequest tests

    public function test_server_request_method()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $request = new ServerRequest();
        $this->assertEquals('POST', $request->getMethod());
    }

    public function test_server_request_uri()
    {
        $_SERVER['REQUEST_URI'] = '/api/users';
        $request = new ServerRequest();
        $this->assertEquals('/api/users', $request->getUri());
    }

    public function test_server_request_query_params()
    {
        $_GET['foo'] = 'bar';
        $_GET['baz'] = '123';
        $request = new ServerRequest();

        $this->assertEquals('bar', $request->getQueryParam('foo'));
        $this->assertEquals('123', $request->getQueryParam('baz'));
        $this->assertEquals('default', $request->getQueryParam('missing', 'default'));
    }

    public function test_server_request_route_params()
    {
        $_GET[':id'] = '42';
        $_GET[':name'] = 'john';
        $request = new ServerRequest();

        $this->assertEquals('42', $request->getParam('id'));
        $this->assertEquals('john', $request->getAttribute('name'));
    }

    public function test_server_request_attributes()
    {
        $_GET[':id'] = '99';
        $request = new ServerRequest();

        $attributes = $request->getAttributes();
        $this->assertEquals('99', $attributes['id']);
    }

    public function test_server_request_with_attribute()
    {
        $request = new ServerRequest();
        $newRequest = $request->withAttribute('custom', 'value');

        $this->assertNull($request->getAttribute('custom'));
        $this->assertEquals('value', $newRequest->getAttribute('custom'));
    }

    public function test_server_request_is_method()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $request = new ServerRequest();

        $this->assertTrue($request->isMethod('POST'));
        $this->assertTrue($request->isMethod('post'));
        $this->assertFalse($request->isMethod('GET'));
    }

    public function test_server_request_headers()
    {
        $_SERVER['HTTP_X_CUSTOM'] = 'test-value';
        $request = new ServerRequest();

        $this->assertEquals('test-value', $request->getHeaderLine('X-Custom'));
    }

    // JsonResponse tests

    public function test_json_response_data()
    {
        $response = new JsonResponse(HTTP_OK, array('status' => 'ok'));

        $this->assertEquals(array('status' => 'ok'), $response->getData());
        $this->assertEquals(HTTP_OK, $response->getStatusCode());
    }

    public function test_json_response_status()
    {
        $response = new JsonResponse(HTTP_NOT_FOUND, null);
        $this->assertEquals(HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function test_json_response_with_status()
    {
        $response = new JsonResponse(HTTP_OK, array('error' => 'Not found'));
        $newResponse = $response->withStatus(HTTP_NOT_FOUND);

        $this->assertEquals(HTTP_OK, $response->getStatusCode());
        $this->assertEquals(HTTP_NOT_FOUND, $newResponse->getStatusCode());
    }

    public function test_json_response_with_data()
    {
        $response = new JsonResponse(HTTP_OK, array('old' => 'data'));
        $newResponse = $response->withData(array('new' => 'data'));

        $this->assertEquals(array('old' => 'data'), $response->getData());
        $this->assertEquals(array('new' => 'data'), $newResponse->getData());
    }

    public function test_json_response_with_header()
    {
        $response = new JsonResponse();
        $newResponse = $response->withHeader('X-Custom', 'value');

        $headers = $newResponse->getHeaders();
        $this->assertEquals('value', $headers['X-Custom']);
    }

    public function test_json_response_body()
    {
        $response = new JsonResponse(HTTP_OK, array('key' => 'value'));
        $this->assertEquals('{"key":"value"}', $response->getBody());
    }

    // Static factory methods

    public function test_json_response_method()
    {
        $response = JsonResponse::response(HTTP_ACCEPTED, array('status' => 'pending'));
        $this->assertEquals(HTTP_ACCEPTED, $response->getStatusCode());
        $this->assertEquals(array('status' => 'pending'), $response->getData());
    }

    public function test_json_response_ok()
    {
        $response = JsonResponse::response(HTTP_OK, array('data' => 'test'));
        $this->assertEquals(HTTP_OK, $response->getStatusCode());
        $this->assertEquals(array('data' => 'test'), $response->getData());
    }

    public function test_json_response_created()
    {
        $response = JsonResponse::response(HTTP_CREATED, array('id' => 1));
        $this->assertEquals(HTTP_CREATED, $response->getStatusCode());
    }

    public function test_json_response_no_content()
    {
        $response = JsonResponse::response(HTTP_NO_CONTENT, null);
        $this->assertEquals(HTTP_NO_CONTENT, $response->getStatusCode());
        $this->assertNull($response->getData());
    }

    public function test_json_response_bad_request()
    {
        $response = JsonResponse::response(HTTP_BAD_REQUEST, array('error' => 'Invalid'));
        $this->assertEquals(HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function test_json_response_unauthorized()
    {
        $response = JsonResponse::response(HTTP_UNAUTHORIZED);
        $this->assertEquals(HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function test_json_response_forbidden()
    {
        $response = JsonResponse::response(HTTP_FORBIDDEN);
        $this->assertEquals(HTTP_FORBIDDEN, $response->getStatusCode());
    }

    public function test_json_response_not_found()
    {
        $response = JsonResponse::response(HTTP_NOT_FOUND);
        $this->assertEquals(HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function test_json_response_server_error()
    {
        $response = JsonResponse::response(HTTP_INTERNAL_SERVER_ERROR);
        $this->assertEquals(HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
    }

    // Route integration tests

    public function test_route_with_closure_receives_request()
    {
        $receivedRequest = null;
        $_GET['url'] = 'test';
        $_GET[':id'] = '42';

        router(function() use (&$receivedRequest) {
            route(method(GET), url_path('/test'), function($request) use (&$receivedRequest) {
                $receivedRequest = $request;
            });
        });

        $this->assertInstanceOf(ServerRequest::class, $receivedRequest);
        $this->assertEquals('42', $receivedRequest->getParam('id'));
    }

    public function test_route_returns_array_as_json()
    {
        $_GET['url'] = 'api';

        ob_start();
        router(function() {
            route(method(GET), url_path('/api'), function() {
                return array('status' => 'ok');
            });
        });
        $output = ob_get_clean();

        $this->assertEquals('{"status":"ok"}', $output);
    }

    public function test_route_returns_string()
    {
        $_GET['url'] = 'text';

        ob_start();
        router(function() {
            route(method(GET), url_path('/text'), function() {
                return 'Hello World';
            });
        });
        $output = ob_get_clean();

        $this->assertEquals('Hello World', $output);
    }

    // HtmlResponse tests

    public function test_html_response_content()
    {
        $response = new HtmlResponse(HTTP_OK, '<h1>Hello</h1>');

        $this->assertEquals('<h1>Hello</h1>', $response->getContent());
        $this->assertEquals(HTTP_OK, $response->getStatusCode());
    }

    public function test_html_response_status()
    {
        $response = new HtmlResponse(HTTP_NOT_FOUND, '');
        $this->assertEquals(HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function test_html_response_with_status()
    {
        $response = new HtmlResponse(HTTP_OK, '<h1>Error</h1>');
        $newResponse = $response->withStatus(HTTP_INTERNAL_SERVER_ERROR);

        $this->assertEquals(HTTP_OK, $response->getStatusCode());
        $this->assertEquals(HTTP_INTERNAL_SERVER_ERROR, $newResponse->getStatusCode());
    }

    public function test_html_response_with_content()
    {
        $response = new HtmlResponse(HTTP_OK, '<p>Old</p>');
        $newResponse = $response->withContent('<p>New</p>');

        $this->assertEquals('<p>Old</p>', $response->getContent());
        $this->assertEquals('<p>New</p>', $newResponse->getContent());
    }

    public function test_html_response_with_header()
    {
        $response = new HtmlResponse();
        $newResponse = $response->withHeader('X-Custom', 'value');

        $headers = $newResponse->getHeaders();
        $this->assertEquals('value', $headers['X-Custom']);
    }

    public function test_html_response_body()
    {
        $response = new HtmlResponse(HTTP_OK, '<p>Test</p>');
        $this->assertEquals('<p>Test</p>', $response->getBody());
    }

    public function test_html_response_method()
    {
        $response = HtmlResponse::response(HTTP_ACCEPTED, '<p>Accepted</p>');
        $this->assertEquals(HTTP_ACCEPTED, $response->getStatusCode());
        $this->assertEquals('<p>Accepted</p>', $response->getContent());
    }

    public function test_html_response_ok()
    {
        $response = HtmlResponse::response(HTTP_OK, '<h1>Success</h1>');
        $this->assertEquals(HTTP_OK, $response->getStatusCode());
        $this->assertEquals('<h1>Success</h1>', $response->getContent());
    }

    public function test_html_response_not_found()
    {
        $response = HtmlResponse::response(HTTP_NOT_FOUND, '<h1>404 Not Found</h1>');
        $this->assertEquals(HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertStringContainsString('404', $response->getContent());
    }

    public function test_html_response_server_error()
    {
        $response = HtmlResponse::response(HTTP_INTERNAL_SERVER_ERROR);
        $this->assertEquals(HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
    }

    public function test_html_response_forbidden()
    {
        $response = HtmlResponse::response(HTTP_FORBIDDEN);
        $this->assertEquals(HTTP_FORBIDDEN, $response->getStatusCode());
    }

    // DownloadResponse tests

    public function test_download_response_content()
    {
        $response = new DownloadResponse('file content', 'text/plain', 'test.txt');

        $this->assertEquals('file content', $response->getContent());
        $this->assertEquals('text/plain', $response->getMimeType());
        $this->assertEquals('test.txt', $response->getFilename());
    }

    public function test_download_response_with_filename()
    {
        $response = new DownloadResponse('content', 'text/plain', 'old.txt');
        $newResponse = $response->withFilename('new.txt');

        $this->assertEquals('old.txt', $response->getFilename());
        $this->assertEquals('new.txt', $newResponse->getFilename());
    }

    public function test_download_response_with_content()
    {
        $response = new DownloadResponse('old', 'text/plain');
        $newResponse = $response->withContent('new');

        $this->assertEquals('old', $response->getContent());
        $this->assertEquals('new', $newResponse->getContent());
    }

    public function test_download_response_json()
    {
        $response = DownloadResponse::json(array('key' => 'value'), 'data.json');

        $this->assertEquals('{"key":"value"}', $response->getContent());
        $this->assertEquals(JSON_CONTENT, $response->getMimeType());
        $this->assertEquals('data.json', $response->getFilename());
    }

    public function test_download_response_csv()
    {
        $response = DownloadResponse::csv("a,b,c\n1,2,3", 'data.csv');

        $this->assertEquals(CSV_CONTENT, $response->getMimeType());
        $this->assertEquals('data.csv', $response->getFilename());
    }

    public function test_download_response_pdf()
    {
        $response = DownloadResponse::pdf('pdf content', 'document.pdf');

        $this->assertEquals(PDF_CONTENT, $response->getMimeType());
        $this->assertEquals('document.pdf', $response->getFilename());
    }

    public function test_download_response_zip()
    {
        $response = DownloadResponse::zip('zip content', 'archive.zip');

        $this->assertEquals(ZIP_CONTENT, $response->getMimeType());
        $this->assertEquals('archive.zip', $response->getFilename());
    }

    public function test_download_response_text()
    {
        $response = DownloadResponse::text('text content', 'readme.txt');

        $this->assertEquals(TEXT_CONTENT, $response->getMimeType());
        $this->assertEquals('readme.txt', $response->getFilename());
    }

    public function test_download_response_xml()
    {
        $response = DownloadResponse::xml('<root/>', 'data.xml');

        $this->assertEquals(XML_CONTENT, $response->getMimeType());
        $this->assertEquals('data.xml', $response->getFilename());
    }

    public function test_download_response_html()
    {
        $response = DownloadResponse::html('<html></html>', 'page.html');

        $this->assertEquals(HTML_CONTENT, $response->getMimeType());
        $this->assertEquals('page.html', $response->getFilename());
    }

    public function test_download_response_javascript()
    {
        $response = DownloadResponse::javascript('console.log("hi")', 'script.js');

        $this->assertEquals(JAVASCRIPT_CONTENT, $response->getMimeType());
        $this->assertEquals('script.js', $response->getFilename());
    }

    public function test_download_response_css()
    {
        $response = DownloadResponse::css('body { }', 'style.css');

        $this->assertEquals(CSS_CONTENT, $response->getMimeType());
        $this->assertEquals('style.css', $response->getFilename());
    }

    public function test_download_response_binary()
    {
        $response = DownloadResponse::binary("\x00\x01\x02", 'data.bin');

        $this->assertEquals(BINARY_CONTENT, $response->getMimeType());
        $this->assertEquals('data.bin', $response->getFilename());
    }

    public function test_download_response_body()
    {
        $response = new DownloadResponse('test content', 'text/plain');
        $this->assertEquals('test content', $response->getBody());
    }

    public function test_download_response_with_mime_type()
    {
        $response = new DownloadResponse('content', 'text/plain');
        $newResponse = $response->withMimeType('application/json');

        $this->assertEquals('text/plain', $response->getMimeType());
        $this->assertEquals('application/json', $newResponse->getMimeType());
    }

    public function test_download_response_send()
    {
        $response = new DownloadResponse('download content', 'text/plain', 'file.txt');

        ob_start();
        $response->send();
        $output = ob_get_clean();

        $this->assertEquals('download content', $output);
    }

    // Additional ServerRequest tests

    public function test_server_request_path()
    {
        $_GET['url'] = 'api/users';
        $request = new ServerRequest();
        $this->assertEquals('api/users', $request->getPath());
    }

    public function test_server_request_get_query_params()
    {
        $_GET['foo'] = 'bar';
        $_GET['baz'] = 'qux';
        $request = new ServerRequest();

        $params = $request->getQueryParams();
        $this->assertEquals('bar', $params['foo']);
        $this->assertEquals('qux', $params['baz']);
    }

    public function test_server_request_parsed_body_post()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = array('name' => 'John', 'email' => 'john@example.com');
        $request = new ServerRequest();

        $body = $request->getParsedBody();
        $this->assertEquals('John', $body['name']);
    }

    public function test_server_request_body_param()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = array('name' => 'Jane');
        $request = new ServerRequest();

        $this->assertEquals('Jane', $request->getBodyParam('name'));
        $this->assertEquals('default', $request->getBodyParam('missing', 'default'));
    }

    public function test_server_request_body_param_null_body()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $request = new ServerRequest();

        $this->assertEquals('default', $request->getBodyParam('name', 'default'));
    }

    public function test_server_request_body()
    {
        $request = new ServerRequest();
        $body = $request->getBody();
        $this->assertIsString($body);
    }

    public function test_server_request_server_params()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $request = new ServerRequest();

        $params = $request->getServerParams();
        $this->assertEquals('GET', $params['REQUEST_METHOD']);
    }

    public function test_server_request_server_param()
    {
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $request = new ServerRequest();

        $this->assertEquals('PUT', $request->getServerParam('REQUEST_METHOD'));
        $this->assertEquals('default', $request->getServerParam('MISSING', 'default'));
    }

    public function test_server_request_uploaded_files()
    {
        $_FILES = array(
            'upload' => array('name' => 'test.txt', 'tmp_name' => '/tmp/test', 'size' => 100, 'error' => 0)
        );
        $request = new ServerRequest();

        $files = $request->getUploadedFiles();
        $this->assertArrayHasKey('upload', $files);
        $_FILES = array();
    }

    public function test_server_request_uploaded_file()
    {
        $_FILES = array(
            'upload' => array('name' => 'test.txt', 'tmp_name' => '/tmp/test', 'size' => 100, 'error' => 0)
        );
        $request = new ServerRequest();

        $file = $request->getUploadedFile('upload');
        $this->assertEquals('test.txt', $file['name']);
        $this->assertNull($request->getUploadedFile('missing'));
        $_FILES = array();
    }

    public function test_server_request_has_uploaded_file()
    {
        $_FILES = array(
            'upload' => array('name' => 'test.txt', 'tmp_name' => '/tmp/test', 'size' => 100, 'error' => 0)
        );
        $request = new ServerRequest();

        $this->assertTrue($request->hasUploadedFile('upload'));
        $this->assertFalse($request->hasUploadedFile('missing'));
        $_FILES = array();
    }

    public function test_server_request_get_headers()
    {
        $_SERVER['HTTP_X_TEST'] = 'value';
        $request = new ServerRequest();

        $headers = $request->getHeaders();
        $this->assertIsArray($headers);
    }

    public function test_server_request_has_header()
    {
        $_SERVER['HTTP_X_TEST'] = 'value';
        $request = new ServerRequest();

        $this->assertTrue($request->hasHeader('X-Test'));
        $this->assertFalse($request->hasHeader('X-Missing'));
    }

    public function test_server_request_is_ajax()
    {
        $request = new ServerRequest();
        $this->assertFalse($request->isAjax());

        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $request = new ServerRequest();
        $this->assertTrue($request->isAjax());
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    public function test_server_request_is_json()
    {
        $request = new ServerRequest();
        $this->assertFalse($request->isJson());

        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/json';
        $request = new ServerRequest();
        $this->assertTrue($request->isJson());
        unset($_SERVER['HTTP_CONTENT_TYPE']);
    }

    public function test_server_request_accepts()
    {
        $_SERVER['HTTP_ACCEPT'] = 'application/json';
        $request = new ServerRequest();

        $this->assertTrue($request->accepts('application/json'));
        $this->assertFalse($request->accepts('text/html'));
        unset($_SERVER['HTTP_ACCEPT']);
    }

    public function test_server_request_header_line_array()
    {
        // Test header line when header value is an array
        $request = new ServerRequest();
        // getHeaderLine should return empty string for non-existent header
        $this->assertEquals('', $request->getHeaderLine('X-Nonexistent'));
    }

    public function test_server_request_default_values()
    {
        // Test defaults when server vars are not set
        unset($_SERVER['REQUEST_METHOD']);
        unset($_SERVER['REQUEST_URI']);

        $request = new ServerRequest();
        $this->assertEquals('GET', $request->getMethod());
        $this->assertEquals('/', $request->getUri());

        // Restore
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test';
    }

    public function test_server_request_get_attribute_default()
    {
        $request = new ServerRequest();
        $this->assertEquals('fallback', $request->getAttribute('nonexistent', 'fallback'));
    }

    // JsonResponse send test

    public function test_json_response_send()
    {
        $response = new JsonResponse(HTTP_OK, array('status' => 'ok'));

        ob_start();
        $response->send();
        $output = ob_get_clean();

        $this->assertEquals('{"status":"ok"}', $output);
    }

    public function test_json_response_send_with_headers()
    {
        $response = new JsonResponse(HTTP_OK, array('data' => 'test'));
        $response = $response->withHeader('X-Custom', 'value');

        ob_start();
        $response->send();
        $output = ob_get_clean();

        $this->assertEquals('{"data":"test"}', $output);
    }

    // HtmlResponse send test

    public function test_html_response_send()
    {
        $response = new HtmlResponse(HTTP_OK, '<h1>Hello</h1>');

        ob_start();
        $response->send();
        $output = ob_get_clean();

        $this->assertEquals('<h1>Hello</h1>', $output);
    }

    public function test_html_response_send_with_headers()
    {
        $response = new HtmlResponse(HTTP_OK, '<p>Test</p>');
        $response = $response->withHeader('X-Custom', 'value');

        ob_start();
        $response->send();
        $output = ob_get_clean();

        $this->assertEquals('<p>Test</p>', $output);
    }

    public function test_html_response_view()
    {
        $tempFile = sys_get_temp_dir() . '/test_view_' . uniqid() . '.php';
        file_put_contents($tempFile, '<h1><?php echo $title; ?></h1>');

        $response = HtmlResponse::view($tempFile, array('title' => 'Test Title'));

        $this->assertEquals(HTTP_OK, $response->getStatusCode());
        $this->assertEquals('<h1>Test Title</h1>', $response->getContent());

        unlink($tempFile);
    }

    public function test_html_response_view_with_status()
    {
        $tempFile = sys_get_temp_dir() . '/test_view_' . uniqid() . '.php';
        file_put_contents($tempFile, '<p>Content</p>');

        $response = HtmlResponse::view($tempFile, array(), HTTP_NOT_FOUND);

        $this->assertEquals(HTTP_NOT_FOUND, $response->getStatusCode());

        unlink($tempFile);
    }

    public function test_html_response_view_not_found()
    {
        $this->expectException(Exception::class);
        HtmlResponse::view('/nonexistent/view.php');
    }

    // DownloadResponse file tests

    public function test_download_response_file()
    {
        $tempFile = sys_get_temp_dir() . '/test_download_' . uniqid() . '.txt';
        file_put_contents($tempFile, 'file content');

        $response = DownloadResponse::file($tempFile);

        $this->assertEquals('file content', $response->getContent());
        $this->assertEquals(basename($tempFile), $response->getFilename());

        unlink($tempFile);
    }

    public function test_download_response_file_custom_name()
    {
        $tempFile = sys_get_temp_dir() . '/test_download_' . uniqid() . '.txt';
        file_put_contents($tempFile, 'file content');

        $response = DownloadResponse::file($tempFile, 'custom.txt');

        $this->assertEquals('custom.txt', $response->getFilename());

        unlink($tempFile);
    }

    public function test_download_response_file_custom_mime()
    {
        $tempFile = sys_get_temp_dir() . '/test_download_' . uniqid() . '.txt';
        file_put_contents($tempFile, 'file content');

        $response = DownloadResponse::file($tempFile, null, 'text/plain');

        $this->assertEquals('text/plain', $response->getMimeType());

        unlink($tempFile);
    }

    public function test_download_response_file_not_found()
    {
        $this->expectException(Exception::class);
        DownloadResponse::file('/nonexistent/file.txt');
    }

    public function test_download_response_get_headers()
    {
        $response = new DownloadResponse('content', 'text/plain');
        $response = $response->withHeader('X-Custom', 'value');

        $headers = $response->getHeaders();
        $this->assertEquals('value', $headers['X-Custom']);
    }

    public function test_download_response_send_with_headers()
    {
        $response = new DownloadResponse('content', 'text/plain', 'file.txt');
        $response = $response->withHeader('X-Custom', 'value');

        ob_start();
        $response->send();
        $output = ob_get_clean();

        $this->assertEquals('content', $output);
    }

    public function test_server_request_json_content_type()
    {
        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/json';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        // Note: php://input is empty in tests so parsedBody will be null
        $request = new ServerRequest();

        // The extractParsedBody path is exercised even though result is null
        $this->assertTrue($request->isJson());
        unset($_SERVER['HTTP_CONTENT_TYPE']);
    }

    public function test_server_request_get_method()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $request = new ServerRequest();

        // Non-POST should have null parsed body
        $this->assertNull($request->getParsedBody());
    }

    public function test_server_request_path_empty()
    {
        $_GET['url'] = '';
        $request = new ServerRequest();
        $this->assertEquals('', $request->getPath());
    }

    public function test_server_request_getallheaders_fallback()
    {
        // This tests the HTTP_ prefix extraction from $_SERVER
        $_SERVER['HTTP_X_CUSTOM_HEADER'] = 'custom-value';
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer token';
        $request = new ServerRequest();

        $headers = $request->getHeaders();
        // Headers should be extracted from HTTP_ prefixed SERVER vars
        $this->assertNotEmpty($headers);
        unset($_SERVER['HTTP_AUTHORIZATION']);
    }

    public function test_server_request_header_case_insensitive()
    {
        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/json';
        $request = new ServerRequest();

        // Test case-insensitive header lookup
        $this->assertEquals('application/json', $request->getHeaderLine('content-type'));
        $this->assertEquals('application/json', $request->getHeaderLine('Content-Type'));
        $this->assertEquals('application/json', $request->getHeaderLine('CONTENT-TYPE'));
        unset($_SERVER['HTTP_CONTENT_TYPE']);
    }

    public function test_server_request_missing_url()
    {
        unset($_GET['url']);
        $request = new ServerRequest();
        $this->assertEquals('', $request->getPath());
        $_GET['url'] = '';
    }

    public function test_server_request_attributes_merge()
    {
        $_GET[':id'] = '1';
        $_GET[':slug'] = 'test';
        $request = new ServerRequest();
        $request = $request->withAttribute('custom', 'value');

        $attrs = $request->getAttributes();
        $this->assertEquals('1', $attrs['id']);
        $this->assertEquals('test', $attrs['slug']);
        $this->assertEquals('value', $attrs['custom']);
    }

    public function test_json_response_empty_data()
    {
        $response = new JsonResponse(HTTP_OK, array());
        $this->assertEquals('[]', $response->getBody());
    }

    public function test_html_response_empty_content()
    {
        $response = new HtmlResponse(HTTP_OK, '');
        $this->assertEquals('', $response->getContent());
    }

    public function test_download_response_without_filename()
    {
        $response = new DownloadResponse('content', 'text/plain');
        $this->assertNull($response->getFilename());

        ob_start();
        $response->send();
        $output = ob_get_clean();
        $this->assertEquals('content', $output);
    }
}
