# PHP Compatible Router

[![CI](https://github.com/php-compatible/router/actions/workflows/ci.yml/badge.svg)](https://github.com/php-compatible/router/actions/workflows/ci.yml)
[![codecov](https://codecov.io/gh/php-compatible/router/branch/main/graph/badge.svg)](https://codecov.io/gh/php-compatible/router)
[![PHP Version](https://img.shields.io/badge/php-5.5%20--%208.5-8892BF.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

**Modern routing for legacy PHP applications** — Write clean routes today, upgrade to modern frameworks tomorrow.

## The Problem

You're maintaining a PHP application that's been around for years. Maybe it started on PHP 4 or 5. The codebase works, but:

- Routes are scattered across dozens of files with mixed HTTP verbs
- `.htaccess` rewrites have become impossible to follow
- You want to modernize, but a full framework migration isn't feasible right now
- Your team needs to support multiple PHP versions during the transition

## The Solution

This router meets you where you are. Whether you're on PHP 5.5 or PHP 8.5, you get the same clean, expressive API. No conditional syntax, no version-specific features — just routing that works.

```php
<?php
require_once 'vendor/autoload.php';

router(function() {
    route(method(GET), url_path('/'), function() {
        echo 'Welcome!';
    });

    route(method(GET), url_path('/users/:id'), function() {
        $id = $_GET[':id'];
        echo json_response(HTTP_OK, array('id' => $id));
    });

    route(method(POST), url_path('/users'), function() {
        // Create user
        echo json_response(HTTP_CREATED, array('status' => 'created'));
    });
});
```

## Your Upgrade Journey

This router supports developers at every stage of modernization:

### Stage 1: Legacy Cleanup (PHP 5.5+)
You have working code but messy routing. Drop in this router to consolidate scattered route definitions without changing your PHP version.

```php
// Before: routing logic buried in index.php with dozens of includes
// After: clean, centralized route definitions
router(function() {
    route(method(GET), url_path('/products'), 'show_products');
    route(method(GET), url_path('/products/:id'), 'show_product');
    route(method(POST), url_path('/products'), 'create_product');
});
```

### Stage 2: Adding Structure (PHP 7.0+)
Ready for more organization? Group related routes and add middleware.

```php
router(function() {
    routerGroup('/api', function() {
        middleware(function() {
            if (!is_authenticated()) {
                echo json_response(HTTP_UNAUTHORIZED, array('error' => 'Unauthorized'));
                return false;
            }
            return true;
        });

        route(method(GET), url_path('/users'), 'list_users');
        route(method(POST), url_path('/users'), 'create_user');
    });
});
```

### Stage 3: Modern Patterns (PHP 7.4+)
Adopt PSR-7 style request/response objects while maintaining backward compatibility.

```php
use PhpCompatible\Router\ServerRequest;
use PhpCompatible\Router\JsonResponse;

router(function() {
    route(method(GET), url_path('/api/users/:id'), function() {
        $request = new ServerRequest();
        $id = $request->getRouteParam(':id');

        return JsonResponse::ok(array('id' => $id, 'name' => 'John'));
    });
});
```

### Stage 4: Framework Ready (PHP 8.0+)
When you're ready to migrate to Laravel, Symfony, or another framework, your route definitions are already clean and portable. The patterns you've learned transfer directly.

## Installation

```bash
composer require php-compatible/router
```

## Features

### HTTP Methods
```php
route(method(GET), url_path('/resource'), $handler);
route(method(POST), url_path('/resource'), $handler);
route(method(PUT), url_path('/resource/:id'), $handler);
route(method(DELETE), url_path('/resource/:id'), $handler);
route(method(PATCH), url_path('/resource/:id'), $handler);
```

### URL Parameters
```php
route(method(GET), url_path_params('/users/:id'), function() {
    $id = $_GET[':id'];
});

route(method(GET), url_path_params('/posts/:postId/comments/:commentId'), function() {
    $postId = $_GET[':postId'];
    $commentId = $_GET[':commentId'];
});
```

### Route Groups
```php
routerGroup('/admin', function() {
    route(method(GET), url_path('/dashboard'), 'admin_dashboard');
    route(method(GET), url_path('/users'), 'admin_users');
});
// Matches: /admin/dashboard, /admin/users
```

### Middleware
```php
middleware(function() {
    // Return false to stop route processing
    if (!check_auth()) {
        http_response_code(401);
        return false;
    }
    return true;
});
```

### Response Helpers
```php
// JSON responses
echo json_response(HTTP_OK, array('data' => $data));
echo json_response(HTTP_CREATED, array('id' => $newId));
echo json_response(HTTP_BAD_REQUEST, array('error' => 'Invalid input'));

// HTML responses
echo html_response(HTTP_OK, '<h1>Hello World</h1>');

// Plain text
echo text_response(HTTP_OK, 'Plain text response');
```

### PSR-7 Style Classes
```php
use PhpCompatible\Router\ServerRequest;
use PhpCompatible\Router\JsonResponse;
use PhpCompatible\Router\HtmlResponse;

// Request object
$request = new ServerRequest();
$request->getMethod();           // GET, POST, etc.
$request->getUri();              // /users/123
$request->getQueryParams();      // $_GET
$request->getRouteParam(':id');  // URL parameters

// JSON Response
$response = JsonResponse::ok(array('status' => 'success'));
$response = JsonResponse::created(array('id' => 1));
$response = JsonResponse::badRequest(array('error' => 'Invalid'));
$response = JsonResponse::notFound(array('error' => 'Not found'));

// HTML Response
$response = HtmlResponse::view('/path/to/template.php', array('name' => 'John'));
```

### HTTP Status Constants
All standard HTTP status codes are available as constants:
```php
HTTP_OK                  // 200
HTTP_CREATED             // 201
HTTP_NO_CONTENT          // 204
HTTP_BAD_REQUEST         // 400
HTTP_UNAUTHORIZED        // 401
HTTP_FORBIDDEN           // 403
HTTP_NOT_FOUND           // 404
HTTP_INTERNAL_SERVER_ERROR // 500
// ... and many more
```

### HTTP Exceptions
```php
use PhpCompatible\Router\HttpException;

// Throw exceptions that automatically set status codes
throw HttpException::badRequest('Invalid input');
throw HttpException::unauthorized('Please log in');
throw HttpException::forbidden('Access denied');
throw HttpException::notFound('Resource not found');
throw HttpException::internalServerError('Something went wrong');
```

## Handler Types

The router accepts multiple handler types:

```php
// Closure
route(method(GET), url_path('/'), function() {
    echo 'Hello';
});

// Function name
route(method(GET), url_path('/about'), 'show_about_page');

// Static method
route(method(GET), url_path('/contact'), 'ContactController::show');

// Array callable
route(method(GET), url_path('/help'), array($controller, 'help'));
```

## Apache Configuration

Add to your `.htaccess`:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]
```

## Nginx Configuration

```nginx
location / {
    try_files $uri $uri/ /index.php?url=$uri&$args;
}
```

## Why Not Just Use Laravel/Symfony?

You should! Eventually. But if you're maintaining a legacy application:

- **Framework migrations take time** — This router lets you clean up routing now
- **Team skills vary** — Simple function-based routing has a gentle learning curve
- **Incremental improvement** — Modernize piece by piece instead of big-bang rewrites
- **Version constraints** — Not everyone can run PHP 8.1+ in production yet

This router is a stepping stone, not a destination. Use it to bring order to chaos, then migrate to a full framework when you're ready.

## Requirements

- PHP 5.5 or higher (tested through PHP 8.5)
- Apache with mod_rewrite or Nginx

## Testing

```bash
# Run tests
composer test

# Run tests with coverage
composer test:coverage
```

## License

MIT License. See [LICENSE](LICENSE) for details.
