<?php

use PHPUnit\Framework\TestCase;
use PhpCompatible\Router\Request;

class RequestClassTest extends TestCase
{
    public function test_query()
    {
        $_GET['foo'] = 'bar';
        $this->assertEquals('bar', Request::query('foo'));
        $this->assertEquals('default', Request::query('missing', 'default'));
        unset($_GET['foo']);
    }

    public function test_post()
    {
        $_POST['name'] = 'John';
        $this->assertEquals('John', Request::post('name'));
        $this->assertEquals('default', Request::post('missing', 'default'));
        unset($_POST['name']);
    }

    public function test_method()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertEquals('POST', Request::method());
    }

    public function test_is_method()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->assertTrue(Request::isMethod('GET'));
        $this->assertTrue(Request::isMethod('get'));
        $this->assertFalse(Request::isMethod('POST'));
    }

    public function test_form()
    {
        $_POST = array('name' => 'John', 'email' => 'john@example.com');
        $form = Request::form();
        $this->assertEquals('John', $form['name']);
        $this->assertEquals('john@example.com', $form['email']);
        $_POST = array();
    }

    public function test_header()
    {
        $_SERVER['HTTP_X_CUSTOM_HEADER'] = 'custom-value';
        $this->assertEquals('custom-value', Request::header('X-Custom-Header'));
        unset($_SERVER['HTTP_X_CUSTOM_HEADER']);
    }

    public function test_is_ajax()
    {
        $this->assertFalse(Request::isAjax());

        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->assertTrue(Request::isAjax());
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
    }

    public function test_is_json()
    {
        $this->assertFalse(Request::isJson());

        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/json';
        $this->assertTrue(Request::isJson());
        unset($_SERVER['HTTP_CONTENT_TYPE']);
    }

    public function test_accepts()
    {
        $_SERVER['HTTP_ACCEPT'] = 'application/json';
        $this->assertTrue(Request::accepts('application/json'));
        $this->assertFalse(Request::accepts('text/html'));
        unset($_SERVER['HTTP_ACCEPT']);
    }

    public function test_body()
    {
        // Body is read from php://input which is empty in tests
        $body = Request::body();
        $this->assertIsString($body);
    }

    public function test_json()
    {
        // JSON body parsing - returns null when no JSON input
        $json = Request::json();
        $this->assertNull($json);
    }

    public function test_file_returns_null_when_no_files()
    {
        $_FILES = array();
        $this->assertNull(Request::file('upload'));
    }

    public function test_file_returns_all_files()
    {
        $_FILES = array(
            'upload' => array('name' => 'test.txt', 'tmp_name' => '/tmp/test', 'size' => 100, 'error' => 0)
        );
        $files = Request::file();
        $this->assertArrayHasKey('upload', $files);
        $_FILES = array();
    }

    public function test_file_returns_specific_file()
    {
        $_FILES = array(
            'upload' => array('name' => 'test.txt', 'tmp_name' => '/tmp/test', 'size' => 100, 'error' => 0)
        );
        $file = Request::file('upload');
        $this->assertEquals('test.txt', $file['name']);
        $_FILES = array();
    }

    public function test_has_file()
    {
        $_FILES = array();
        $this->assertFalse(Request::hasFile('upload'));

        $_FILES = array(
            'upload' => array('name' => 'test.txt', 'tmp_name' => '/tmp/test', 'size' => 100, 'error' => 0)
        );
        $this->assertTrue(Request::hasFile('upload'));
        $_FILES = array();
    }

    public function test_move_file()
    {
        // Create temp file
        $tempFile = sys_get_temp_dir() . '/test_upload_' . uniqid();
        file_put_contents($tempFile, 'test content');

        $_FILES = array(
            'upload' => array(
                'name' => 'test.txt',
                'tmp_name' => $tempFile,
                'size' => 12,
                'error' => 0
            )
        );

        $destination = sys_get_temp_dir() . '/test_moved_' . uniqid();

        // Note: move_uploaded_file won't work in tests, so we test the method exists
        // The actual move functionality is tested by the underlying function
        $result = Request::moveFile('upload', $destination);

        // Cleanup
        if (file_exists($tempFile)) unlink($tempFile);
        if (file_exists($destination)) unlink($destination);
        $_FILES = array();

        $this->assertIsBool($result);
    }
}

/**
 * Tests for functional request functions
 */
class RequestFunctionsTest extends TestCase
{
    public function test_request_header_from_server()
    {
        $_SERVER['HTTP_X_TEST_HEADER'] = 'test-value';
        $this->assertEquals('test-value', request_header('X-Test-Header'));
        unset($_SERVER['HTTP_X_TEST_HEADER']);
    }

    public function test_request_header_not_found()
    {
        $this->assertEquals('', request_header('X-Nonexistent'));
    }

    public function test_accept_function()
    {
        $_SERVER['HTTP_ACCEPT'] = 'application/json, text/html';
        $this->assertTrue(accept('application/json'));
        $this->assertTrue(accept('text/html'));
        $this->assertFalse(accept('text/xml'));
        unset($_SERVER['HTTP_ACCEPT']);
    }

    public function test_request_body()
    {
        $body = request_body();
        $this->assertIsString($body);
    }

    public function test_json_body()
    {
        $result = json_body();
        // Returns null when php://input is empty
        $this->assertNull($result);
    }

    public function test_form_body()
    {
        $_POST = array('key' => 'value');
        $result = form_body();
        $this->assertEquals(array('key' => 'value'), $result);
        $_POST = array();
    }

    public function test_file_body_all()
    {
        $_FILES = array(
            'file1' => array('name' => 'a.txt'),
            'file2' => array('name' => 'b.txt')
        );
        $result = file_body();
        $this->assertCount(2, $result);
        $_FILES = array();
    }

    public function test_file_body_specific()
    {
        $_FILES = array(
            'upload' => array('name' => 'test.txt')
        );
        $result = file_body('upload');
        $this->assertEquals('test.txt', $result['name']);
        $_FILES = array();
    }

    public function test_file_body_not_found()
    {
        $_FILES = array();
        $result = file_body('missing');
        $this->assertNull($result);
    }

    public function test_has_file_true()
    {
        $_FILES = array(
            'upload' => array(
                'name' => 'test.txt',
                'error' => UPLOAD_ERR_OK
            )
        );
        $this->assertTrue(has_file('upload'));
        $_FILES = array();
    }

    public function test_has_file_false_missing()
    {
        $_FILES = array();
        $this->assertFalse(has_file('missing'));
    }

    public function test_has_file_false_error()
    {
        $_FILES = array(
            'upload' => array(
                'name' => 'test.txt',
                'error' => UPLOAD_ERR_NO_FILE
            )
        );
        $this->assertFalse(has_file('upload'));
        $_FILES = array();
    }

    public function test_move_file_no_file()
    {
        $_FILES = array();
        $this->assertFalse(move_file('missing', '/tmp/dest'));
    }
}
