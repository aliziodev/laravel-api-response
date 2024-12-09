<?php

use Aliziodev\ApiResponse\Exceptions\ApiExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use PDOException;
use Exception;

beforeEach(function () {
    $this->handler = new ApiExceptionHandler();
    $this->request = Request::create('/test', 'GET');
});

it('handles authentication exception', function () {
    $exception = new AuthenticationException('Unauthenticated');
    $response = $this->handler->handle($exception, $this->request);

    expect($response)
        ->toBeValidResponse('fail', 401)
        ->and(getResponseContent($response))
        ->toHaveKey('message', 'Unauthenticated');
});

it('handles authorization exception', function () {
    $exception = new AuthorizationException('Unauthorized action');
    $response = $this->handler->handle($exception, $this->request);

    expect($response)
        ->toBeValidResponse('fail', 403)
        ->and(getResponseContent($response))
        ->toHaveKey('message', 'Unauthorized action');
});

it('handles access denied exception', function () {
    $exception = new AccessDeniedHttpException('Access denied');
    $response = $this->handler->handle($exception, $this->request);

    expect($response)
        ->toBeValidResponse('fail', 403)
        ->and(getResponseContent($response))
        ->toHaveKey('message', 'Unauthorized action');
});

it('handles database query exception', function () {
    $sql = 'select * from users';
    $bindings = [];
    $previous = new Exception('SQLSTATE[42S02]: Base table or view not found');
    
    $exception = new QueryException(
        'mysql',
        $sql,
        $bindings,
        $previous
    );

    $response = $this->handler->handle($exception, $this->request);

    expect($response)
        ->toBeValidResponse('error', 500)
        ->toHaveValidRef();
});

it('handles PDO exception', function () {
    $exception = new PDOException('Database connection failed');
    $response = $this->handler->handle($exception, $this->request);

    expect($response)
        ->toBeValidResponse('error', 500)
        ->toHaveValidRef();
});

it('handles model not found exception', function () {
    $exception = new ModelNotFoundException('Resource not found');
    $response = $this->handler->handle($exception, $this->request);

    expect($response)
        ->toBeValidResponse('fail', 404)
        ->and(getResponseContent($response))
        ->toHaveKey('message', 'Resource not found');
});

it('handles not found exception', function () {
    $exception = new NotFoundHttpException('Resource not found');
    $response = $this->handler->handle($exception, $this->request);

    expect($response)
        ->toBeValidResponse('fail', 404)
        ->and(getResponseContent($response))
        ->toHaveKey('message');
});

it('handles method not allowed exception', function () {
    $exception = new MethodNotAllowedHttpException(['GET'], 'Method not allowed');
    $response = $this->handler->handle($exception, $this->request);

    expect($response)
        ->toBeValidResponse('fail', 405)
        ->and(getResponseContent($response))
        ->toHaveKey('message', 'Method Not Allowed');
});

it('handles validation exception', function () {
    $validator = validator([], ['email' => 'required']);
    $validator->fails();
    $exception = new ValidationException($validator);

    $response = $this->handler->handle($exception, $this->request);

    expect($response)
        ->toBeValidResponse('fail', 422)
        ->and(getResponseContent($response))
        ->toHaveKey('errors');
});

it('handles too many requests exception', function () {
    $exception = new ThrottleRequestsException('Too Many Attempts');
    $response = $this->handler->handle($exception, $this->request);

    expect($response)
        ->toBeValidResponse('fail', 429)
        ->and(getResponseContent($response))
        ->toHaveKey('message', 'Too Many Attempts');
});

it('handles post too large exception', function () {
    $exception = new PostTooLargeException('File too large');
    $response = $this->handler->handle($exception, $this->request);

    expect($response)
        ->toBeValidResponse('fail', 413)
        ->and(getResponseContent($response))
        ->toHaveKey('message', 'File Too Large');
});

it('handles file not found exception', function () {
    $exception = new FileNotFoundException('File not found');
    $response = $this->handler->handle($exception, $this->request);

    expect($response)
        ->toBeValidResponse('fail', 404)
        ->and(getResponseContent($response))
        ->toHaveKey('message', 'File Not Found');
});

it('handles service unavailable exception', function () {
    $exception = new ServiceUnavailableHttpException(60, 'Service unavailable');
    $response = $this->handler->handle($exception, $this->request);

    expect($response)
        ->toBeValidResponse('error', 503)
        ->and(getResponseContent($response))
        ->toHaveKey('message', 'Service Unavailable');
});

it('includes debug information in development environment', function () {
    app()['env'] = 'local';
    $exception = new Exception('Test Exception');
    $response = $this->handler->handle($exception, $this->request);

    expect($response)->toHaveDebugInfo();
});

it('excludes debug information in production environment', function () {
    app()['env'] = 'production';
    $exception = new Exception('Test Exception');
    $response = $this->handler->handle($exception, $this->request);

    expect(getResponseContent($response))->not->toHaveKey('debug');
});

it('handles generic http exception', function () {
    $exception = new HttpException(
        Response::HTTP_BAD_REQUEST,
        'Bad Request'
    );
    $response = $this->handler->handle($exception, $this->request);

    expect($response)
        ->toBeValidResponse('fail', 400)
        ->and(getResponseContent($response))
        ->toHaveKey('message', 'Bad Request');
});

it('handles custom http exception', function () {
    $exception = new HttpException(
        Response::HTTP_PAYMENT_REQUIRED,
        'Payment Required'
    );
    $response = $this->handler->handle($exception, $this->request);

    expect($response)
        ->toBeValidResponse('fail', 402)
        ->and(getResponseContent($response))
        ->toHaveKey('message', 'Payment Required');
});
