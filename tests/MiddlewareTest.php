<?php
require_once __DIR__ . "/_data/SingleActionMiddleware.php";

use PHPUnit\Framework\TestCase;

class MiddlewareTest extends TestCase
{
    public function tearDown(): void
    {
        if (isset($_GET['test_middleware_1'])) {
            unset($_GET['test_middleware_1']);
        }
        if (isset($_GET['test_middleware_2'])) {
            unset($_GET['test_middleware_2']);
        }
        if (isset($_GET['test_single_action_middleware'])) {
            unset($_GET['test_single_action_middleware']);
        }
    }

    public function test_middleware()
    {
        $actual = middleware(
            function() {
                usleep(100);
                $_GET['test_middleware_1'] = microtime(true);
            },
            function() {
                usleep(100);
                $_GET['test_middleware_2'] = microtime(true);
            }
        );

        call_user_func($actual, array());

        $this->assertArrayHasKey('test_middleware_1', $_GET);
        $this->assertArrayHasKey('test_middleware_2', $_GET);
        $this->assertGreaterThan($_GET['test_middleware_1'], $_GET['test_middleware_2']);
    }

    public function test_single_action_middleware()
    {
        $actual = middleware(
            SingleActionMiddleware::class
        );

        call_user_func($actual, array());

        $this->assertArrayHasKey('test_single_action_middleware', $_GET);
    }
}
