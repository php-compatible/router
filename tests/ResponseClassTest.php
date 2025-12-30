<?php

use PHPUnit\Framework\TestCase;
use PhpCompatible\Router\Response;

class ResponseClassTest extends TestCase
{
    public function test_json()
    {
        $result = Response::json(HTTP_OK, array('status' => 'ok'));
        $this->assertEquals('{"status":"ok"}', $result);
        $this->assertEquals(HTTP_OK, http_response_code());
    }

    public function test_html()
    {
        $result = Response::html(HTTP_OK, '<h1>Hello</h1>');
        $this->assertEquals('<h1>Hello</h1>', $result);
        $this->assertEquals(HTTP_OK, http_response_code());
    }

    public function test_text()
    {
        $result = Response::text(HTTP_OK, 'Hello World');
        $this->assertEquals('Hello World', $result);
        $this->assertEquals(HTTP_OK, http_response_code());
    }

    public function test_xml()
    {
        $xml = '<?xml version="1.0"?><root><item>test</item></root>';
        $result = Response::xml(HTTP_OK, $xml);
        $this->assertEquals($xml, $result);
        $this->assertEquals(HTTP_OK, http_response_code());
    }

    public function test_content()
    {
        $result = Response::content('text/plain', 'test content');
        $this->assertEquals('test content', $result);
    }

    public function test_json_with_error_code()
    {
        $result = Response::json(HTTP_BAD_REQUEST, array('error' => 'Invalid input'));
        $this->assertEquals('{"error":"Invalid input"}', $result);
        $this->assertEquals(HTTP_BAD_REQUEST, http_response_code());
    }

    public function test_html_with_error_code()
    {
        $result = Response::html(HTTP_NOT_FOUND, '<h1>Not Found</h1>');
        $this->assertEquals('<h1>Not Found</h1>', $result);
        $this->assertEquals(HTTP_NOT_FOUND, http_response_code());
    }

    public function test_status()
    {
        Response::status(HTTP_CREATED);
        $this->assertEquals(HTTP_CREATED, http_response_code());
    }

    public function test_header()
    {
        // Headers can't be easily tested in CLI, but we can call the method
        Response::header('X-Test', 'value');
        $this->assertTrue(true); // Method executed without error
    }

    public function test_download()
    {
        ob_start();
        Response::download('text/plain', 'file content', 'test.txt');
        $output = ob_get_clean();
        $this->assertEquals('file content', $output);
    }

    public function test_download_json()
    {
        ob_start();
        Response::downloadJson(array('key' => 'value'), 'data.json');
        $output = ob_get_clean();
        $this->assertEquals('{"key":"value"}', $output);
    }

    public function test_download_csv()
    {
        ob_start();
        Response::downloadCsv("a,b,c\n1,2,3", 'data.csv');
        $output = ob_get_clean();
        $this->assertEquals("a,b,c\n1,2,3", $output);
    }

    public function test_download_pdf()
    {
        ob_start();
        Response::downloadPdf('pdf content', 'doc.pdf');
        $output = ob_get_clean();
        $this->assertEquals('pdf content', $output);
    }

    public function test_download_zip()
    {
        ob_start();
        Response::downloadZip('zip content', 'archive.zip');
        $output = ob_get_clean();
        $this->assertEquals('zip content', $output);
    }

    public function test_download_text()
    {
        ob_start();
        Response::downloadText('text content', 'readme.txt');
        $output = ob_get_clean();
        $this->assertEquals('text content', $output);
    }

    public function test_download_file()
    {
        $tempFile = sys_get_temp_dir() . '/test_download_' . uniqid() . '.txt';
        file_put_contents($tempFile, 'test file content');

        ob_start();
        Response::downloadFile($tempFile);
        $output = ob_get_clean();

        $this->assertEquals('test file content', $output);
        unlink($tempFile);
    }

    public function test_download_file_with_custom_name()
    {
        $tempFile = sys_get_temp_dir() . '/test_download_' . uniqid() . '.txt';
        file_put_contents($tempFile, 'custom name content');

        ob_start();
        Response::downloadFile($tempFile, 'custom.txt');
        $output = ob_get_clean();

        $this->assertEquals('custom name content', $output);
        unlink($tempFile);
    }

    public function test_download_file_not_found()
    {
        $this->expectException(Exception::class);
        Response::downloadFile('/nonexistent/file.txt');
    }
}

