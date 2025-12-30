<?php

use PHPUnit\Framework\TestCase;
use PhpCompatible\Router\Router;

class RouterClassTest extends TestCase
{
    protected function setUp(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['url'] = '';
        $GLOBALS['ROUTER_STOP_CODE'] = true;
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['ROUTER_STOP_CODE']);
        unset($_GET['url']);
    }

    public function test_get_route()
    {
        $called = false;
        $_GET['url'] = 'users';

        Router::run(function() use (&$called) {
            Router::get('/users', function() use (&$called) {
                $called = true;
            });
        });

        $this->assertTrue($called);
    }

    public function test_post_route()
    {
        $called = false;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_GET['url'] = 'users';

        Router::run(function() use (&$called) {
            Router::post('/users', function() use (&$called) {
                $called = true;
            });
        });

        $this->assertTrue($called);
    }

    public function test_put_route()
    {
        $called = false;
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $_GET['url'] = 'users';

        Router::run(function() use (&$called) {
            Router::put('/users', function() use (&$called) {
                $called = true;
            });
        });

        $this->assertTrue($called);
    }

    public function test_delete_route()
    {
        $called = false;
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $_GET['url'] = 'users';

        Router::run(function() use (&$called) {
            Router::delete('/users', function() use (&$called) {
                $called = true;
            });
        });

        $this->assertTrue($called);
    }

    public function test_any_route()
    {
        $called = false;
        $_SERVER['REQUEST_METHOD'] = 'PATCH';
        $_GET['url'] = 'resource';

        Router::run(function() use (&$called) {
            Router::any('/resource', function() use (&$called) {
                $called = true;
            });
        });

        $this->assertTrue($called);
    }

    public function test_match_route()
    {
        $called = false;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_GET['url'] = 'data';

        Router::run(function() use (&$called) {
            Router::match(array(GET, POST), '/data', function() use (&$called) {
                $called = true;
            });
        });

        $this->assertTrue($called);
    }

    public function test_group()
    {
        $called = false;
        $_GET['url'] = 'api/users';

        Router::run(function() use (&$called) {
            Router::group('/api', function() use (&$called) {
                Router::get('/users', function() use (&$called) {
                    $called = true;
                });
            });
        });

        $this->assertTrue($called);
    }

    public function test_nested_groups()
    {
        $called = false;
        $_GET['url'] = 'api/v1/users';

        Router::run(function() use (&$called) {
            Router::group('/api', function() use (&$called) {
                Router::group('/v1', function() use (&$called) {
                    Router::get('/users', function() use (&$called) {
                        $called = true;
                    });
                });
            });
        });

        $this->assertTrue($called);
    }

    public function test_get_with_params()
    {
        $captured_id = null;
        $_GET['url'] = 'users/42';

        Router::run(function() use (&$captured_id) {
            Router::get('/users/:id', function() use (&$captured_id) {
                $captured_id = $_GET[':id'];
            });
        });

        $this->assertEquals('42', $captured_id);
    }

    public function test_put_with_params()
    {
        $captured_id = null;
        $_SERVER['REQUEST_METHOD'] = 'PUT';
        $_GET['url'] = 'users/99';

        Router::run(function() use (&$captured_id) {
            Router::put('/users/:id', function() use (&$captured_id) {
                $captured_id = $_GET[':id'];
            });
        });

        $this->assertEquals('99', $captured_id);
    }

    public function test_delete_with_params()
    {
        $captured_id = null;
        $_SERVER['REQUEST_METHOD'] = 'DELETE';
        $_GET['url'] = 'posts/123';

        Router::run(function() use (&$captured_id) {
            Router::delete('/posts/:id', function() use (&$captured_id) {
                $captured_id = $_GET[':id'];
            });
        });

        $this->assertEquals('123', $captured_id);
    }

    public function test_set_root()
    {
        Router::setRoot('/myapp');
        $this->assertEquals('/myapp/users', Router::rootUrl('/users'));

        // Reset
        Router::setRoot('');
    }

    public function test_patch_route()
    {
        $called = false;
        $_SERVER['REQUEST_METHOD'] = 'PATCH';
        $_GET['url'] = 'users/1';

        Router::run(function() use (&$called) {
            Router::patch('/users/:id', function() use (&$called) {
                $called = true;
            });
        });

        $this->assertTrue($called);
    }

    public function test_head_route()
    {
        $called = false;
        $_SERVER['REQUEST_METHOD'] = 'HEAD';
        $_GET['url'] = 'status';

        Router::run(function() use (&$called) {
            Router::head('/status', function() use (&$called) {
                $called = true;
            });
        });

        $this->assertTrue($called);
    }

    public function test_options_route()
    {
        $called = false;
        $_SERVER['REQUEST_METHOD'] = 'OPTIONS';
        $_GET['url'] = 'api';

        Router::run(function() use (&$called) {
            Router::options('/api', function() use (&$called) {
                $called = true;
            });
        });

        $this->assertTrue($called);
    }

    public function test_post_with_params()
    {
        $captured_id = null;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_GET['url'] = 'users/55';

        Router::run(function() use (&$captured_id) {
            Router::post('/users/:id', function() use (&$captured_id) {
                $captured_id = $_GET[':id'];
            });
        });

        $this->assertEquals('55', $captured_id);
    }

    public function test_root_url_empty()
    {
        Router::setRoot('');
        $this->assertEquals('/', Router::rootUrl());
        $this->assertEquals('/users', Router::rootUrl('/users'));
    }

    public function test_root_url_with_path()
    {
        Router::setRoot('/app');
        $this->assertEquals('/app', Router::rootUrl());
        $this->assertEquals('/app/', Router::rootUrl('/'));
        Router::setRoot('');
    }

    public function test_static_folder()
    {
        // Create temp directory and file
        $tempDir = sys_get_temp_dir() . '/static_test_' . uniqid();
        mkdir($tempDir);
        file_put_contents($tempDir . '/test.txt', 'static content');

        $_GET['url'] = 'assets/test.txt';

        ob_start();
        Router::run(function() use ($tempDir) {
            Router::staticFolder($tempDir, '/assets');
        });
        $output = ob_get_clean();

        $this->assertEquals('static content', $output);

        // Cleanup
        unlink($tempDir . '/test.txt');
        rmdir($tempDir);
    }

    public function test_redirect()
    {
        Router::redirect('/dashboard');
        $headers = xdebug_get_headers();
        $this->assertContains('Location: /dashboard', $headers);
    }
}
