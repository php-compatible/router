<?php

use PHPUnit\Framework\TestCase;

/**
 * Tests for functional response functions (download_* helpers)
 */
class ResponseFunctionsTest extends TestCase
{
    public function test_download_xml()
    {
        ob_start();
        download_xml('<root/>', 'data.xml');
        $output = ob_get_clean();
        $this->assertEquals('<root/>', $output);
    }

    public function test_download_html()
    {
        ob_start();
        download_html('<html></html>', 'page.html');
        $output = ob_get_clean();
        $this->assertEquals('<html></html>', $output);
    }

    public function test_download_javascript()
    {
        ob_start();
        download_javascript('console.log("hi")', 'script.js');
        $output = ob_get_clean();
        $this->assertEquals('console.log("hi")', $output);
    }

    public function test_download_css()
    {
        ob_start();
        download_css('body {}', 'style.css');
        $output = ob_get_clean();
        $this->assertEquals('body {}', $output);
    }

    public function test_download_binary()
    {
        ob_start();
        download_binary("\x00\x01\x02", 'data.bin');
        $output = ob_get_clean();
        $this->assertEquals("\x00\x01\x02", $output);
    }

    public function test_download_response_no_filename()
    {
        ob_start();
        download_response('text/plain', 'inline content');
        $output = ob_get_clean();
        $this->assertEquals('inline content', $output);
    }

    public function test_content_type()
    {
        $result = content('text/plain', 'test');
        $this->assertEquals('test', $result);
    }

    public function test_text_response()
    {
        $result = text_response(HTTP_OK, 'plain text');
        $this->assertEquals('plain text', $result);
        $this->assertEquals(HTTP_OK, http_response_code());
    }

    public function test_xml_response()
    {
        $result = xml_response(HTTP_OK, '<root/>');
        $this->assertEquals('<root/>', $result);
        $this->assertEquals(HTTP_OK, http_response_code());
    }

    public function test_response_header()
    {
        // Just verify it doesn't throw
        response_header('X-Test', 'value');
        $this->assertTrue(true);
    }

    public function test_download_file_with_custom_name()
    {
        $tempFile = sys_get_temp_dir() . '/test_file_' . uniqid() . '.txt';
        file_put_contents($tempFile, 'content');

        ob_start();
        download_file($tempFile, 'custom.txt');
        $output = ob_get_clean();

        $this->assertEquals('content', $output);
        unlink($tempFile);
    }

    public function test_download_file_with_custom_mime()
    {
        $tempFile = sys_get_temp_dir() . '/test_file_' . uniqid() . '.dat';
        file_put_contents($tempFile, 'binary data');

        ob_start();
        download_file($tempFile, null, 'application/x-custom');
        $output = ob_get_clean();

        $this->assertEquals('binary data', $output);
        unlink($tempFile);
    }

    public function test_download_file_auto_mime()
    {
        $tempFile = sys_get_temp_dir() . '/test_file_' . uniqid() . '.txt';
        file_put_contents($tempFile, 'text content');

        ob_start();
        download_file($tempFile);
        $output = ob_get_clean();

        $this->assertEquals('text content', $output);
        unlink($tempFile);
    }

    public function test_download_file_not_found()
    {
        $this->expectException(Exception::class);
        download_file('/nonexistent/file.txt');
    }
}
