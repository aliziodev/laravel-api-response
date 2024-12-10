# Laravel API Response

[![Latest Version on Packagist](https://img.shields.io/packagist/v/aliziodev/laravel-api-response.svg?style=flat-square)](https://packagist.org/packages/aliziodev/laravel-api-response)
[![Total Downloads](https://img.shields.io/packagist/dt/aliziodev/laravel-api-response.svg?style=flat-square)](https://packagist.org/packages/aliziodev/laravel-api-response)
[![PHP Version](https://img.shields.io/packagist/php-v/aliziodev/laravel-api-response.svg?style=flat-square)](https://packagist.org/packages/aliziodev/laravel-api-response)
[![Laravel Version](https://img.shields.io/badge/Laravel-11.x-red?style=flat-square)](https://packagist.org/packages/aliziodev/laravel-api-response)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/aliziodev/laravel-api-response/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/aliziodev/laravel-api-response/actions?query=workflow%3Arun-tests+branch%3Amain)


A standardized API Response package for Laravel with `Responsable` implementation. This package provides a consistent way to structure your API responses across your Laravel application.

## Features

- Standardized API Response format
- Built-in support for success, error, and fail responses
- Automatic error reference generation
- Sensitive data masking in logs
- Debug information for development
- Laravel's `Responsable` interface implementation
- Type-safe implementation with strict types
- Comprehensive test coverage

## Installation

You can install the package via Composer:

```bash
composer require aliziodev/laravel-api-response
```

## Usage
### Basic Usage

```php
use Aliziodev\ApiResponse\Facades\ApiResponse;

// Success Response
return ApiResponse::success(
    data: ['user' => $user],
    message: 'User retrieved successfully',
    meta: ['total' => 1]
);

// Error Response
return ApiResponse::error(
    message: 'Something went wrong',
    errors: ['database' => 'Connection failed'],
    code: 500
);

// Fail Response
return ApiResponse::fail(
    message: 'Validation failed',
    errors: ['email' => ['Email is required']],
    code: 400
);
```

### Exception Handling
##### For Laravel 11, you need to register the exception handler in `bootstrap/app.php`:

```php
<?php

use Aliziodev\ApiResponse\Exceptions\ApiExceptionHandler;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Add your custom middleware here
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (\Throwable $e, Request $request) {
            if ($request->expectsJson()) {
                return app(ApiExceptionHandler::class)->handle($e, $request);
            }
        });
    })->create();
```
This setup will handle all exceptions for JSON requests and return standardized API responses. The handler will:

- Automatically detect exception types
- Generate reference codes for errors
- Mask sensitive data in logs
- Include debug information in non-production environments
- Return appropriate HTTP status codes
- Format error messages consistently

Common exceptions that are handled:

- Authentication exceptions (401)
- Authorization exceptions (403)
- Validation exceptions (422)
- Not found exceptions (404)
- Database exceptions (500)
- Rate limiting exceptions (429)
- Service unavailable exceptions (503)

### Response Types
#### Success Responses
```php
// Basic Success (200)
ApiResponse::success(
    data: $data,
    message: 'Success',
    meta: ['page' => 1]
);

// Created (201)
ApiResponse::created(
    data: $newResource,
    message: 'Resource created successfully'
);

// Accepted (202)
ApiResponse::accepted(
    data: ['job_id' => 'abc123'],
    message: 'Job queued'
);

// No Content (204)
ApiResponse::noContent(
    message: 'Resource deleted'
);

// Custom Success
ApiResponse::success(
    data: $data,
    message: 'Custom success message',
    meta: ['custom' => 'meta'],
    code: 200
);
```
#### Error Responses
```php
// Server Error (500)
ApiResponse::error(
    message: 'Server error occurred',
    errors: ['server' => 'Internal error']
);

// Service Unavailable (503)
ApiResponse::serviceUnavailable(
    message: 'Service is down',
    errors: ['maintenance' => 'Scheduled maintenance']
);

// Maintenance Mode (503)
ApiResponse::maintenance(
    message: 'System maintenance',
    errors: ['status' => 'Please try again later']
);
```
#### Fail Responses

```php
// Bad Request (400)
ApiResponse::fail(
    message: 'Invalid input',
    errors: ['field' => 'Invalid value']
);

// Unauthorized (401)
ApiResponse::unauthorized(
    message: 'Authentication required',
    errors: ['auth' => 'Please login']
);

// Forbidden (403)
ApiResponse::forbidden(
    message: 'Access denied',
    errors: ['permission' => 'Insufficient permissions']
);

// Not Found (404)
ApiResponse::notFound(
    message: 'Resource not found',
    errors: ['id' => 'Record does not exist']
);

// Validation Error (422)
ApiResponse::validationError(
    errors: ['email' => ['Email is required']],
    message: 'Validation failed'
);

// Too Many Requests (429)
ApiResponse::tooManyRequests(
    message: 'Rate limit exceeded',
    errors: ['limit' => 'Try again later']
);
```
### Response Format
#### Success Response
```json
{
    "status": "success",
    "code": 200,
    "message": "Success message",
    "data": {
        "key": "value"
    },
    "meta": {
        "page": 1,
        "total": 10
    }
}
```   

#### Error Response
```json
{
    "status": "error",
    "code": 500,
    "message": "Error message",
    "ref": "ERR-20240101-REF-abc123",
    "errors": {
        "database": "Connection failed"
    },
    "debug": {
        "environment": "local",
        "exception": "Exception class",
        "error_message": "Detailed error message",
        "file": "/path/to/file.php",
        "line": 123,
        "trace": "Stack trace..."
    }
}
```
#### Fail Response
```json
{
    "status": "fail",
    "code": 400,
    "message": "Fail message",
    "ref": "ERR-20240101-REF-xyz789",
    "errors": {
        "field": ["Validation error message"]
    }
}
```
### Logging
##### The package automatically logs errors and failures with sensitive data masking:
```php
// These keys will be masked in logs
private static array $sensitiveKeys = [
    'password',
    'secret',
    'token',
    'authorization',
    'cookie',
    'api_key',
    'key',
    'private',
    'credential',
];
```
## Testing

```bash
composer test
```

## Static Analysis

```bash
composer analyse
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email aliziodev@gmail.com instead of using the issue tracker.

## Credits

- [Alizio](https://github.com/aliziodev)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
