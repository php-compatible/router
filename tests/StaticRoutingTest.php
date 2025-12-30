<?php
use PHPUnit\Framework\TestCase;

class StaticRoutingTest extends TestCase
{
    private $staticFolder;

    public function setUp(): void
    {
        $_SERVER['DOCUMENT_ROOT'] = __DIR__;
        $this->staticFolder = __DIR__ . '/_data/static';
        error_reporting(0);
        ini_set('log_errors', '0');
    }

    public function tearDown(): void
    {
        global $PATH_PREFIX, $ROOT_URL;
        $PATH_PREFIX = '';
        $ROOT_URL = '';
        $_GET['url'] = '';
        if (isset($GLOBALS['ROUTER_STOP_CODE'])) {
            unset($GLOBALS['ROUTER_STOP_CODE']);
        }
    }

    public function test_mime_type_css()
    {
        $this->assertEquals('text/css', mime_type('css'));
    }

    public function test_mime_type_javascript()
    {
        $this->assertEquals('text/javascript', mime_type('js'));
    }

    public function test_mime_type_json()
    {
        $this->assertEquals('application/json', mime_type('json'));
    }

    public function test_mime_type_html()
    {
        $this->assertEquals('text/html', mime_type('html'));
        $this->assertEquals('text/html', mime_type('htm'));
    }

    public function test_mime_type_images()
    {
        $this->assertEquals('image/png', mime_type('png'));
        $this->assertEquals('image/jpeg', mime_type('jpg'));
        $this->assertEquals('image/jpeg', mime_type('jpeg'));
        $this->assertEquals('image/gif', mime_type('gif'));
        $this->assertEquals('image/svg+xml', mime_type('svg'));
        $this->assertEquals('image/webp', mime_type('webp'));
    }

    public function test_mime_type_fonts()
    {
        $this->assertEquals('font/woff', mime_type('woff'));
        $this->assertEquals('font/woff2', mime_type('woff2'));
        $this->assertEquals('font/ttf', mime_type('ttf'));
    }

    public function test_mime_type_documents()
    {
        $this->assertEquals('application/pdf', mime_type('pdf'));
        $this->assertEquals('application/zip', mime_type('zip'));
    }

    public function test_mime_type_audio()
    {
        $this->assertEquals('audio/mpeg', mime_type('mp3'));
        $this->assertEquals('audio/wav', mime_type('wav'));
    }

    public function test_mime_type_video()
    {
        $this->assertEquals('video/mp4', mime_type('mp4'));
        $this->assertEquals('video/webm', mime_type('webm'));
    }

    public function test_mime_type_unknown()
    {
        $this->assertEquals('application/octet-stream', mime_type('xyz'));
        $this->assertEquals('application/octet-stream', mime_type('unknown'));
    }

    public function test_mime_type_case_insensitive()
    {
        $this->assertEquals('text/css', mime_type('CSS'));
        $this->assertEquals('text/javascript', mime_type('JS'));
        $this->assertEquals('image/png', mime_type('PNG'));
    }

    public function test_url_path_starts_with()
    {
        $_GET['url'] = 'static/test.css';

        $result = url_path_starts_with('/static');
        $this->assertEquals('test.css', $result);

        $result = url_path_starts_with('static');
        $this->assertEquals('test.css', $result);

        $result = url_path_starts_with('/static/');
        $this->assertEquals('test.css', $result);
    }

    public function test_url_path_starts_with_nested()
    {
        $_GET['url'] = 'assets/css/main.css';

        $result = url_path_starts_with('/assets');
        $this->assertEquals('css/main.css', $result);
    }

    public function test_url_path_starts_with_no_match()
    {
        $_GET['url'] = 'api/users';

        $result = url_path_starts_with('/static');
        $this->assertFalse($result);
    }

    public function test_url_path_starts_with_in_group()
    {
        global $PATH_PREFIX;
        $PATH_PREFIX = 'app';
        $_GET['url'] = 'app/static/test.css';

        $result = url_path_starts_with('/static');
        $this->assertEquals('test.css', $result);
    }

    public function test_static_folder_serves_css()
    {
        $_GET['url'] = 'static/test.css';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        ob_start();
        static_folder($this->staticFolder, '/static');
        $output = ob_get_clean();

        $this->assertStringContainsString('body { color: red; }', $output);
    }

    public function test_static_folder_serves_javascript()
    {
        $_GET['url'] = 'static/test.js';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        ob_start();
        static_folder($this->staticFolder, '/static');
        $output = ob_get_clean();

        $this->assertStringContainsString("console.log('test');", $output);
    }

    public function test_static_folder_serves_json()
    {
        $_GET['url'] = 'static/test.json';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        ob_start();
        static_folder($this->staticFolder, '/static');
        $output = ob_get_clean();

        $this->assertStringContainsString('{"test": true}', $output);
    }

    public function test_static_folder_serves_nested_files()
    {
        $_GET['url'] = 'static/sub/nested.txt';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        ob_start();
        static_folder($this->staticFolder, '/static');
        $output = ob_get_clean();

        $this->assertStringContainsString('Nested file', $output);
    }

    public function test_static_folder_no_match()
    {
        $_GET['url'] = 'api/users';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        ob_start();
        static_folder($this->staticFolder, '/static');
        $output = ob_get_clean();

        // Should not output anything when URL doesn't match
        $this->assertEquals('', $output);
    }

    public function test_static_folder_file_not_found()
    {
        $_GET['url'] = 'static/nonexistent.txt';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        ob_start();
        static_folder($this->staticFolder, '/static');
        $output = ob_get_clean();

        // Should not output anything when file doesn't exist
        $this->assertEquals('', $output);
    }

    public function test_static_folder_prevents_directory_traversal()
    {
        $_GET['url'] = 'static/../_data/SingleActionController.php';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        ob_start();
        static_folder($this->staticFolder, '/static');
        $output = ob_get_clean();

        // Should not serve files outside static folder
        $this->assertEquals('', $output);
    }

    public function test_static_folder_allowed_extensions()
    {
        $_GET['url'] = 'static/test.css';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        // Only allow JS files
        ob_start();
        static_folder($this->staticFolder, '/static', array(
            'allowed_extensions' => array('js')
        ));
        $output = ob_get_clean();

        // CSS should not be served
        $this->assertEquals('', $output);

        // Reset for JS test
        unset($GLOBALS['ROUTER_STOP_CODE']);

        $_GET['url'] = 'static/test.js';
        ob_start();
        static_folder($this->staticFolder, '/static', array(
            'allowed_extensions' => array('js')
        ));
        $output = ob_get_clean();

        // JS should be served
        $this->assertStringContainsString("console.log('test');", $output);
    }

    public function test_static_folder_with_custom_prefix()
    {
        $_GET['url'] = 'assets/test.css';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        ob_start();
        static_folder($this->staticFolder, '/assets');
        $output = ob_get_clean();

        $this->assertStringContainsString('body { color: red; }', $output);
    }

    public function test_static_folder_empty_relative_path()
    {
        $_GET['url'] = 'static';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        ob_start();
        static_folder($this->staticFolder, '/static');
        $output = ob_get_clean();

        // Should not serve when path is empty after prefix
        $this->assertEquals('', $output);
    }

    public function test_static_folder_directory_not_file()
    {
        $_GET['url'] = 'static/sub';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        ob_start();
        static_folder($this->staticFolder, '/static');
        $output = ob_get_clean();

        // Should not serve directories
        $this->assertEquals('', $output);
    }

    // More mime_type tests for coverage
    public function test_mime_type_text()
    {
        $this->assertEquals('text/plain', mime_type('txt'));
        $this->assertEquals('text/csv', mime_type('csv'));
        $this->assertEquals('text/markdown', mime_type('md'));
        $this->assertEquals('text/xml', mime_type('xml'));
    }

    public function test_mime_type_images_extended()
    {
        $this->assertEquals('image/x-icon', mime_type('ico'));
        $this->assertEquals('image/bmp', mime_type('bmp'));
        $this->assertEquals('image/tiff', mime_type('tiff'));
        $this->assertEquals('image/tiff', mime_type('tif'));
    }

    public function test_mime_type_fonts_extended()
    {
        $this->assertEquals('font/otf', mime_type('otf'));
        $this->assertEquals('application/vnd.ms-fontobject', mime_type('eot'));
    }

    public function test_mime_type_documents_extended()
    {
        $this->assertEquals('application/msword', mime_type('doc'));
        $this->assertEquals('application/vnd.openxmlformats-officedocument.wordprocessingml.document', mime_type('docx'));
        $this->assertEquals('application/vnd.ms-excel', mime_type('xls'));
        $this->assertEquals('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', mime_type('xlsx'));
        $this->assertEquals('application/vnd.ms-powerpoint', mime_type('ppt'));
        $this->assertEquals('application/vnd.openxmlformats-officedocument.presentationml.presentation', mime_type('pptx'));
    }

    public function test_mime_type_archives()
    {
        $this->assertEquals('application/gzip', mime_type('gz'));
        $this->assertEquals('application/x-tar', mime_type('tar'));
        $this->assertEquals('application/vnd.rar', mime_type('rar'));
        $this->assertEquals('application/x-7z-compressed', mime_type('7z'));
    }

    public function test_mime_type_audio_extended()
    {
        $this->assertEquals('audio/ogg', mime_type('ogg'));
        $this->assertEquals('audio/flac', mime_type('flac'));
        $this->assertEquals('audio/mp4', mime_type('m4a'));
    }

    public function test_mime_type_video_extended()
    {
        $this->assertEquals('video/x-msvideo', mime_type('avi'));
        $this->assertEquals('video/quicktime', mime_type('mov'));
        $this->assertEquals('video/x-matroska', mime_type('mkv'));
    }

    public function test_mime_type_other()
    {
        $this->assertEquals('application/x-shockwave-flash', mime_type('swf'));
        $this->assertEquals('application/wasm', mime_type('wasm'));
    }

    public function test_url_path_starts_with_exact_prefix()
    {
        $_GET['url'] = 'staticfile.txt';

        // Should not match when prefix doesn't match
        $result = url_path_starts_with('/static');
        $this->assertFalse($result);
    }
}
