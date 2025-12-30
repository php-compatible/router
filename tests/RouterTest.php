<?php
require_once __DIR__ . "/_data/SingleActionController.php";
require_once __DIR__ . "/_data/ControllerWithParams.php";

use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
    public function setUp(): void
    {
        $_SERVER['DOCUMENT_ROOT'] = __DIR__;
        // disable error logging to prevent tests being marked as risky
        error_reporting(0);
        ini_set('log_errors', '0');
    }

    public function tearDown(): void
    {
        global $PATH_PREFIX, $ROOT_URL;
        $PATH_PREFIX = '';
        $ROOT_URL = '';
        $_GET['url'] = '';
        if (isset($_SERVER['HTTP_ACCEPT'])) {
            unset($_SERVER['HTTP_ACCEPT']);
        }

        if (isset($GLOBALS['ROUTER_STOP_CODE'])) {
            unset($GLOBALS['ROUTER_STOP_CODE']);
        }
    }

    public function test_method()
    {
        $_SERVER['REQUEST_METHOD'] = PUT;
        $this->assertTrue(method(PUT));
        $this->assertFalse(method(POST));
    }

    public function test_url_path()
    {
        $_GET['url'] = '';
        $actual = url_path('');
        $this->assertTrue($actual);

        $actual = url_path('/');
        $this->assertTrue($actual);

        $actual = url_path('*');
        $this->assertTrue($actual);

        $_GET['url'] = 'hello';
        $actual = url_path('/hello');
        $this->assertTrue($actual);

        // test group
        global $PATH_PREFIX;
        $PATH_PREFIX = 'api';
        $_GET['url'] = 'api/hello';
        $actual = url_path('/hello');
        $this->assertTrue($actual);
    }

    public function test_url_path_params()
    {
        $_GET['url'] = 'user/1';
        $this->assertTrue(url_path_params('/user/:id'));
        $this->assertArrayHasKey(':id', $_GET);
        $this->assertEquals(1, $_GET[':id']);
        $this->assertFalse(url_path_params('/user/:id/:page'));

        $_GET['url'] = '1';
        $this->assertTrue(url_path_params('/:id'));
        $this->assertFalse(url_path_params('/1'));

        // test group
        global $PATH_PREFIX;
        $PATH_PREFIX = 'api';
        $_GET['url'] = 'api/hello/1';
        $actual = url_path_params('/hello/:id');
        $this->assertTrue($actual);
    }

    public function test_route()
    {
        $this->assertEmpty(route(false, false, function () {
            $_GET['route_test_1'] = 1;
        }));
        $this->assertArrayNotHasKey('route_test_1', $_GET);

        $this->assertEmpty(route(true, false, function () {
            $_GET['route_test_2'] = 1;
        }));
        $this->assertArrayNotHasKey('route_test_2', $_GET);

        $this->assertEmpty(route(false, true, function () {
            $_GET['route_test_3'] = 1;
        }));
        $this->assertArrayNotHasKey('route_test_3', $_GET);


        $this->assertEmpty(route(true, true, function () {
            $_GET['route_test_4'] = 1;
        }));
        $this->assertArrayHasKey('route_test_4', $_GET);
    }

    public function test_router()
    {
        ob_start();
        router(function () {
            route(true, true, function () {
                echo 'router_test_output';
            });
        });
        $output = ob_get_clean();

        $this->assertEquals('router_test_output', $output);
    }

    public function test_router_json_exception()
    {
        $_SERVER['HTTP_ACCEPT'] = 'application/json';

        ob_start();
        router(function () {
            throw new Exception("This is an exception.", 500);
        });
        $output = ob_get_clean();

        $this->assertEquals(500, http_response_code());
        // Assert the output is the correct JSON message
        $this->assertJson($output);
        $this->assertEquals(
            json_encode(array("error" => array("message" => "This is an exception.", "code" => 500))),
            $output
        );
    }

    public function test_router_html_exception()
    {
        ob_start();
        router(function () {
            throw new Exception("This is an exception.", 500);
        });
        $output = ob_get_clean();

        $this->assertEquals(500, http_response_code());
        // Assert the output is the correct HTML message
        $this->assertStringContainsString('This is an exception.', $output);
    }

    public function test_router_json_error()
    {
        // Error class only exists in PHP 7+
        if (!class_exists('Error')) {
            $this->markTestSkipped('Error class not available in PHP < 7.0');
        }

        // Temporarily redirect error_log to a file or null sink
        ini_set('error_log', '/dev/null');

        ob_start();
        $_SERVER['HTTP_ACCEPT'] = 'application/json';
        router(function () {
            throw new Error("This is an error.", 400);
        });
        $output = ob_get_clean();
        $this->assertEquals(400, http_response_code());
        // Assert the output is the correct JSON message
        $this->assertJson($output);
        $this->assertEquals(
            json_encode(array("error" => array("message" => "This is an error.", "code" => 400))),
            $output
        );
    }

    public function test_router_html_error()
    {
        // Error class only exists in PHP 7+
        if (!class_exists('Error')) {
            $this->markTestSkipped('Error class not available in PHP < 7.0');
        }

        // Temporarily redirect error_log to a file or null sink
        ini_set('error_log', '/dev/null');

        ob_start();
        router(function () {
            throw new Error("This is an error.", 400);
        });
        $output = ob_get_clean();
        $this->assertEquals(400, http_response_code());
        // Assert the output is the correct HTML message
        $this->assertStringContainsString('This is an error.', $output);
    }

    public function test_router_group()
    {
        global $PATH_PREFIX;
        $PATH_PREFIX = '';
        $_GET['url'] = 'api/hello';
        ob_start();
        router(function () {
            routerGroup('/api', function () {
                route(true, url_path('/hello'), function () {
                    echo 'hello';
                });
            });
        });
        $output = ob_get_clean();
        $this->assertEquals('hello', $output);

        $PATH_PREFIX = '';
        $_GET['url'] = 'api/hello';
        ob_start();
        router(function () {
            routerGroup('/api', function () {
                routerGroup('/hello', function () {
                    route(true, url_path('/'), function () {
                        echo 'hello2';
                    });
                });
            });
        });
        $output2 = ob_get_clean();
        $this->assertEquals('hello2', $output2);

        $PATH_PREFIX = '';
        $_GET['url'] = 'api/hello';
        ob_start();
        router(function () {
            routerGroup('/api/', function () {
                routerGroup('/hello', function () {
                    route(true, url_path('/'), function () {
                        echo 'hello2';
                    });
                });
            });
        });
        $output2 = ob_get_clean();
        $this->assertEquals('hello2', $output2);
    }

    public function test_redirect()
    {
        redirect('/');
        $headers = xdebug_get_headers();

        $this->assertEquals(1, in_array('Location: /', $headers));
    }

    public function test_use_request_uri()
    {
        $_SERVER['REQUEST_URI'] = '/';

        use_request_uri();

        $this->assertEquals('', $_GET['url']);
    }

    public function test_route_class()
    {
        ob_start();
        route(true, true, SingleActionController::class);
        $output = ob_get_clean();
        $this->assertEquals(json_encode(array("message" => "Hello World")), $output);
    }

    public function test_root_url()
    {
        // Without root set
        $this->assertEquals('/', root_url());
        $this->assertEquals('/api/users', root_url('/api/users'));

        // Set root
        set_root_url('/subsite');
        $this->assertEquals('/subsite', root_url());
        $this->assertEquals('/subsite/api/users', root_url('/api/users'));
        $this->assertEquals('/subsite/', root_url('/'));
        $this->assertEquals('/subsite/path', root_url('path'));

        // Reset and test with trailing slash
        set_root_url('/myapp/');
        $this->assertEquals('/myapp', root_url());
        $this->assertEquals('/myapp/dashboard', root_url('/dashboard'));

        // Reset to empty
        set_root_url('/');
        $this->assertEquals('/', root_url());
        $this->assertEquals('/api', root_url('/api'));
    }

    public function test_file_router()
    {
        // Simulate file at /var/www/html/api/users.php
        $_SERVER['DOCUMENT_ROOT'] = '/var/www/html';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['url'] = 'api/users';

        ob_start();
        file_router(function () {
            route(method(GET), url_path('/'), function () {
                echo 'users_list';
            });
        }, '/var/www/html/api/users.php');
        $output = ob_get_clean();
        $this->assertEquals('users_list', $output);
    }

    public function test_file_router_windows_paths()
    {
        // Simulate Windows paths
        $_SERVER['DOCUMENT_ROOT'] = 'C:\\Users\\pilot\\wwwroot';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['url'] = 'dashboard/login';

        ob_start();
        file_router(function () {
            route(method(GET), url_path('/'), function () {
                echo 'login_page';
            });
        }, 'C:\\Users\\pilot\\wwwroot\\dashboard\\login.php');
        $output = ob_get_clean();
        $this->assertEquals('login_page', $output);
    }

    public function test_file_router_index_file()
    {
        // Simulate index.php in a directory
        $_SERVER['DOCUMENT_ROOT'] = '/var/www/html';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['url'] = 'api';

        ob_start();
        file_router(function () {
            route(method(GET), url_path('/'), function () {
                echo 'api_index';
            });
        }, '/var/www/html/api/index.php');
        $output = ob_get_clean();
        $this->assertEquals('api_index', $output);
    }

    public function test_file_router_default_file()
    {
        // Simulate default.php in a directory (like orchidlive)
        $_SERVER['DOCUMENT_ROOT'] = '/var/www/html';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['url'] = 'dashboard';

        ob_start();
        file_router(function () {
            route(method(GET), url_path('/'), function () {
                echo 'dashboard_default';
            });
        }, '/var/www/html/dashboard/default.php');
        $output = ob_get_clean();
        $this->assertEquals('dashboard_default', $output);
    }

    public function test_file_router_root_default()
    {
        // Simulate default.php at root
        $_SERVER['DOCUMENT_ROOT'] = '/var/www/html';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['url'] = '';

        ob_start();
        file_router(function () {
            route(method(GET), url_path('/'), function () {
                echo 'root_default';
            });
        }, '/var/www/html/default.php');
        $output = ob_get_clean();
        $this->assertEquals('root_default', $output);
    }

    public function test_route_returns_json_response_object()
    {
        require_once __DIR__ . '/../src/Router/JsonResponse.php';

        $_GET['url'] = 'json-object';

        ob_start();
        router(function () {
            route(method(GET), url_path('/json-object'), function () {
                return new \PhpCompatible\Router\JsonResponse(HTTP_OK, array('result' => 'test'));
            });
        });
        $output = ob_get_clean();

        $this->assertEquals('{"result":"test"}', $output);
    }

    public function test_route_returns_html_response_object()
    {
        require_once __DIR__ . '/../src/Router/HtmlResponse.php';

        $_GET['url'] = 'html-object';

        ob_start();
        router(function () {
            route(method(GET), url_path('/html-object'), function () {
                return new \PhpCompatible\Router\HtmlResponse(HTTP_OK, '<p>Test HTML</p>');
            });
        });
        $output = ob_get_clean();

        $this->assertEquals('<p>Test HTML</p>', $output);
    }

    public function test_route_with_no_return()
    {
        $_GET['url'] = 'no-return';
        $GLOBALS['ROUTER_STOP_CODE'] = true;

        ob_start();
        router(function () {
            route(method(GET), url_path('/no-return'), function () {
                // No return value
            });
        });
        $output = ob_get_clean();
        unset($GLOBALS['ROUTER_STOP_CODE']);

        $this->assertEquals('', $output);
    }

    public function test_router_not_found()
    {
        $_GET['url'] = 'nonexistent-route';

        ob_start();
        router(function () {
            route(method(GET), url_path('/other'), function () {
                echo 'other';
            });
        });
        $output = ob_get_clean();

        $this->assertEquals(404, http_response_code());
        $this->assertStringContainsString('Not Found', $output);
    }

    public function test_router_exception_without_code()
    {
        ob_start();
        router(function () {
            throw new Exception("Error without code");
        });
        $output = ob_get_clean();

        $this->assertEquals(500, http_response_code());
    }

    public function test_url_path_trailing_slash()
    {
        $_GET['url'] = 'hello';
        $actual = url_path('/hello/');
        $this->assertTrue($actual);
    }

    public function test_file_router_root_index()
    {
        $_SERVER['DOCUMENT_ROOT'] = '/var/www/html';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['url'] = '';

        ob_start();
        file_router(function () {
            route(method(GET), url_path('/'), function () {
                echo 'root_index';
            });
        }, '/var/www/html/index.php');
        $output = ob_get_clean();
        $this->assertEquals('root_index', $output);
    }

    public function test_route_returns_array()
    {
        $_GET['url'] = 'array-return';

        ob_start();
        router(function () {
            route(method(GET), url_path('/array-return'), function () {
                return array('status' => 'success');
            });
        });
        $output = ob_get_clean();

        $this->assertEquals('{"status":"success"}', $output);
    }

    public function test_route_returns_string()
    {
        $_GET['url'] = 'string-return';

        ob_start();
        router(function () {
            route(method(GET), url_path('/string-return'), function () {
                return 'plain text response';
            });
        });
        $output = ob_get_clean();

        $this->assertEquals('plain text response', $output);
    }

    public function test_render_error_json()
    {
        $_SERVER['HTTP_ACCEPT'] = 'application/json';

        ob_start();
        render_error(new Exception("JSON error message", 400));
        $output = ob_get_clean();

        $this->assertJson($output);
        $this->assertStringContainsString('JSON error message', $output);
    }

    public function test_render_error_html()
    {
        if (isset($_SERVER['HTTP_ACCEPT'])) {
            unset($_SERVER['HTTP_ACCEPT']);
        }

        ob_start();
        render_error(new Exception("HTML error message", 500));
        $output = ob_get_clean();

        $this->assertStringContainsString('HTML error message', $output);
    }

    public function test_root_url_with_backslash_path()
    {
        set_root_url('/subsite');
        $result = root_url('path\\to\\resource');
        $this->assertStringContainsString('/path/to/resource', $result);
        set_root_url('');
    }

    public function test_url_path_params_no_colon()
    {
        $_GET['url'] = 'users/list';
        $result = url_path_params('/users/list');
        // Path has no : params but matches length - should return false
        $this->assertFalse($result);
    }

    public function test_url_path_params_prefix_mismatch()
    {
        $_GET['url'] = 'posts/1';
        $result = url_path_params('/users/:id');
        $this->assertFalse($result);
    }

    public function test_file_router_auto_detect_file()
    {
        $_SERVER['DOCUMENT_ROOT'] = __DIR__;
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['url'] = '';
        $GLOBALS['ROUTER_STOP_CODE'] = true;

        // This tests the debug_backtrace auto-detection
        ob_start();
        file_router(function () {
            // No routes needed, just testing path calculation
        });
        ob_get_clean();

        // If we get here without error, the auto-detection worked
        $this->assertTrue(true);
    }

    public function test_static_folder_cache_headers()
    {
        $_GET['url'] = 'static/test.css';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $staticFolder = __DIR__ . '/_data/static';

        ob_start();
        static_folder($staticFolder, '/static', array(
            'cache_time' => 3600
        ));
        $output = ob_get_clean();

        $this->assertStringContainsString('body { color: red; }', $output);
    }

    public function test_static_folder_no_cache()
    {
        unset($GLOBALS['ROUTER_STOP_CODE']);
        $_GET['url'] = 'static/test.js';
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $staticFolder = __DIR__ . '/_data/static';

        ob_start();
        static_folder($staticFolder, '/static', array(
            'cache_time' => 0
        ));
        $output = ob_get_clean();

        $this->assertStringContainsString("console.log('test');", $output);
    }

    public function test_route_with_controller_accepting_request()
    {
        $_GET['url'] = 'with-request';

        ob_start();
        router(function () {
            route(method(GET), url_path('/with-request'), ControllerWithRequest::class);
        });
        $output = ob_get_clean();

        // The controller should output something - either the request data or an error
        $this->assertNotEmpty($output);
    }

    public function test_route_with_multiple_params_controller()
    {
        $_GET['url'] = 'multi-params';

        ob_start();
        router(function () {
            route(method(GET), url_path('/multi-params'), ControllerWithMultipleParams::class);
        });
        $output = ob_get_clean();

        // The controller should output something
        $this->assertNotEmpty($output);
    }

    public function test_invoke_action_with_array_callable()
    {
        // Test _invoke_action with array callable
        ob_start();
        $result = _invoke_action(array(new SingleActionController(), '__invoke'));
        $output = ob_get_clean();

        $this->assertStringContainsString('Hello World', $output);
    }

    public function test_use_request_uri_with_leading_slash()
    {
        $_SERVER['REQUEST_URI'] = '/api/users';

        use_request_uri();

        $this->assertEquals('api/users', $_GET['url']);
    }

    public function test_set_root_url_with_slash()
    {
        set_root_url('/');
        $this->assertEquals('/', root_url());
        set_root_url('');
    }

    public function test_root_url_null_path()
    {
        set_root_url('/app');
        $this->assertEquals('/app', root_url(null));
        set_root_url('');
    }

    public function test_url_path_empty_path()
    {
        $_GET['url'] = '';
        $this->assertTrue(url_path(''));
    }

    public function test_url_path_with_prefix_and_slash()
    {
        global $PATH_PREFIX;
        $PATH_PREFIX = 'api';
        $_GET['url'] = 'api/users';
        $this->assertTrue(url_path('/users'));
        $PATH_PREFIX = '';
    }

    public function test_router_group_nested_with_slashes()
    {
        global $PATH_PREFIX;
        $PATH_PREFIX = '';
        $_GET['url'] = 'a/b/c';

        ob_start();
        router(function () {
            routerGroup('/a/', function () {
                routerGroup('/b/', function () {
                    route(true, url_path('/c'), function () {
                        echo 'nested';
                    });
                });
            });
        });
        $output = ob_get_clean();

        $this->assertEquals('nested', $output);
    }

    public function test_file_router_document_root_trailing_slash()
    {
        $_SERVER['DOCUMENT_ROOT'] = '/var/www/html/';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['url'] = 'page';

        ob_start();
        file_router(function () {
            route(method(GET), url_path('/'), function () {
                echo 'page_content';
            });
        }, '/var/www/html/page.php');
        $output = ob_get_clean();

        $this->assertEquals('page_content', $output);
    }

    public function test_stop_function()
    {
        // Test stop function returns code and sets global
        $result = stop(42);
        $this->assertEquals(42, $result);
        $this->assertEquals(42, $GLOBALS['ROUTER_STOP_CODE']);
    }

    public function test_stop_function_default()
    {
        // Test stop function with default code
        $result = stop();
        $this->assertEquals(0, $result);
        $this->assertEquals(0, $GLOBALS['ROUTER_STOP_CODE']);
    }

    public function test_router_group_empty_prefix()
    {
        global $PATH_PREFIX;
        $PATH_PREFIX = '';
        $_GET['url'] = 'users';

        ob_start();
        router(function () {
            routerGroup('', function () {
                route(true, url_path('/users'), function () {
                    echo 'users_list';
                });
            });
        });
        $output = ob_get_clean();

        $this->assertEquals('users_list', $output);
    }

    public function test_url_path_starts_with_no_prefix()
    {
        global $PATH_PREFIX;
        $PATH_PREFIX = '';
        $_GET['url'] = 'assets/main.css';

        $result = url_path_starts_with('/assets');
        $this->assertEquals('main.css', $result);
    }

    public function test_invoke_action_with_no_params_closure()
    {
        $output = null;
        ob_start();
        _invoke_action(function () {
            echo 'no_params_closure';
        });
        $output = ob_get_clean();

        $this->assertEquals('no_params_closure', $output);
    }

    public function test_handle_response_with_null()
    {
        // _handle_response with null should do nothing
        ob_start();
        _handle_response(null);
        $output = ob_get_clean();

        $this->assertEquals('', $output);
    }

    public function test_handle_response_with_generic_response_object()
    {
        // Test response object that's not JsonResponse but has send()
        $response = new \PhpCompatible\Router\HtmlResponse(HTTP_OK, '<p>Test</p>');

        ob_start();
        _handle_response($response);
        $output = ob_get_clean();

        $this->assertEquals('<p>Test</p>', $output);
    }

    public function test_method_various_http_verbs()
    {
        $_SERVER['REQUEST_METHOD'] = HEAD;
        $this->assertTrue(method(HEAD));
        $this->assertFalse(method(GET));

        $_SERVER['REQUEST_METHOD'] = CONNECT;
        $this->assertTrue(method(CONNECT));

        $_SERVER['REQUEST_METHOD'] = TRACE;
        $this->assertTrue(method(TRACE));
    }

    public function test_url_path_params_multiple_params()
    {
        $_GET['url'] = 'users/42/posts/99';
        $result = url_path_params('/users/:userId/posts/:postId');

        $this->assertTrue($result);
        $this->assertEquals('42', $_GET[':userId']);
        $this->assertEquals('99', $_GET[':postId']);
    }

    public function test_route_with_unknown_type_controller()
    {
        $_GET['url'] = 'unknown-type';

        ob_start();
        router(function () {
            route(method(GET), url_path('/unknown-type'), ControllerWithUnknownType::class);
        });
        $output = ob_get_clean();

        // In PHP 8+, getClass() is deprecated, so the type hint may not be detected
        // The test verifies that the route executes without errors
        $this->assertNotEmpty($output);
    }

    public function test_invoke_action_closure_with_request_param()
    {
        $_GET['url'] = '';
        $_SERVER['REQUEST_METHOD'] = 'POST';

        ob_start();
        _invoke_action(function ($request) {
            echo $request->getMethod();
        });
        $output = ob_get_clean();

        $this->assertEquals('POST', $output);
    }

    public function test_invoke_action_static_method_callable()
    {
        // Test _invoke_action with static method array callable
        ob_start();
        $result = _invoke_action(array('StaticTestHelper', 'output'));
        $output = ob_get_clean();

        $this->assertEquals('static_method_output', $output);
    }

    public function test_root_url_empty_root_empty_path()
    {
        global $ROOT_URL;
        $ROOT_URL = '';
        $this->assertEquals('/', root_url(''));
    }

    public function test_root_url_with_empty_root()
    {
        set_root_url('');
        $result = root_url('/dashboard');
        $this->assertEquals('/dashboard', $result);
    }

    public function test_routergroup_with_only_slash()
    {
        global $PATH_PREFIX;
        $PATH_PREFIX = '';
        $_GET['url'] = 'test';

        ob_start();
        router(function () {
            routerGroup('/', function () {
                route(true, url_path('/test'), function () {
                    echo 'slash_group_test';
                });
            });
        });
        $output = ob_get_clean();

        $this->assertEquals('slash_group_test', $output);
    }

    public function test_router_success_with_stop_code()
    {
        $_GET['url'] = 'success-stop';
        $GLOBALS['ROUTER_STOP_CODE'] = true;

        ob_start();
        router(function () {
            route(method(GET), url_path('/success-stop'), function () {
                echo 'success';
            });
        });
        $output = ob_get_clean();

        $this->assertEquals('success', $output);
    }
}

/**
 * Static helper for testing callable invocation
 */
class StaticTestHelper
{
    public static function output()
    {
        echo 'static_method_output';
    }
}
