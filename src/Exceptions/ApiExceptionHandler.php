<?php

declare(strict_types=1);

namespace Aliziodev\ApiResponse\Exceptions;

use Aliziodev\ApiResponse\Response\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

class ApiExceptionHandler
{
    /**
     * Handle the exception and return the appropriate response.
     *
     * @param Throwable $e
     * @param Request $request
     * @return mixed
     */
    public function handle(Throwable $e, Request $request): mixed
    {
        $ref = app(ApiResponse::class)->refCode();

        return match (true) {
            // Authentication & Authorization Exceptions
            $e instanceof \Illuminate\Auth\AuthenticationException =>
                app(ApiResponse::class)->unauthorized(
                    message: 'Unauthenticated',
                    errors: ['authentication' => $e->getMessage()]
                ),

            $e instanceof \Illuminate\Auth\Access\AuthorizationException,
            $e instanceof \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException =>
                app(ApiResponse::class)->forbidden(
                    message: 'Unauthorized action',
                    errors: ['authorization' => $e->getMessage()]
                ),

            // Validation & Form Exceptions
            $e instanceof \Illuminate\Validation\ValidationException =>
                app(ApiResponse::class)->validationError(
                    errors: $e->errors(),
                    message: 'The given data was invalid'
                ),

            $e instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException =>
                app(ApiResponse::class)->tooManyRequests(
                    message: 'Too Many Attempts',
                    errors: ['throttle' => $e->getMessage()]
                ),

            // Database & Model Exceptions
            $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException =>
                app(ApiResponse::class)->notFound(
                    message: 'Resource not found',
                    errors: ['model' => 'The requested resource was not found.']
                ),

            $e instanceof \Illuminate\Database\QueryException =>
                app(ApiResponse::class)->error(
                    ref: $ref,
                    message: 'Database Error',
                    errors: ['database' => $this->getDatabaseErrorMessage($e)],
                    code: Response::HTTP_INTERNAL_SERVER_ERROR,
                    exception: $e
                ),

            $e instanceof \PDOException =>
                app(ApiResponse::class)->error(
                    ref: $ref,
                    message: 'Database Connection Error',
                    errors: ['database' => $this->getErrorMessage($e)],
                    code: Response::HTTP_INTERNAL_SERVER_ERROR,
                    exception: $e
                ),

            // HTTP Exceptions
            $e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException =>
                app(ApiResponse::class)->notFound(
                    message: 'Not Found',
                    errors: ['http' => $e->getMessage()]
                ),

            $e instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException =>
                app(ApiResponse::class)->fail(
                    ref: $ref,
                    message: 'Method Not Allowed',
                    errors: ['method' => $e->getMessage()],
                    code: Response::HTTP_METHOD_NOT_ALLOWED
                ),

            // File & Upload Exceptions
            $e instanceof \Illuminate\Http\Exceptions\PostTooLargeException =>
                app(ApiResponse::class)->fail(
                    ref: $ref,
                    message: 'File Too Large',
                    errors: ['upload' => 'The uploaded file exceeds the maximum allowed size.'],
                    code: Response::HTTP_REQUEST_ENTITY_TOO_LARGE
                ),

            $e instanceof \Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException =>
                app(ApiResponse::class)->notFound(
                    message: 'File Not Found',
                    errors: ['file' => 'The requested file was not found.']
                ),

            // Service & Maintenance Exceptions
            $e instanceof \Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException =>
                app(ApiResponse::class)->serviceUnavailable(
                    message: 'Service Unavailable',
                    errors: ['service' => $e->getMessage()]
                ),

            // Generic HTTP Exceptions
            $e instanceof \Symfony\Component\HttpKernel\Exception\HttpException =>
                app(ApiResponse::class)->respond(
                    message: $e->getMessage(),
                    code: $e->getStatusCode()
                ),

            // Default case for unhandled exceptions
            default => app(ApiResponse::class)->error(
                ref: $ref,
                message: 'Server Error',
                errors: ['server' => $this->getErrorMessage($e)],
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
                exception: $e
            ),
        };
    }

    /**
     * Get database specific error message
     * 
     * @param \Illuminate\Database\QueryException $e
     * @return string
     */
    protected function getDatabaseErrorMessage(\Illuminate\Database\QueryException $e): string
    {
        return match (true) {
            str_contains($e->getMessage(), 'Duplicate entry') => 'Duplicate entry found.',
            str_contains($e->getMessage(), 'Foreign key constraint') => 'Related record not found.',
            str_contains($e->getMessage(), 'Data too long') => 'Data exceeds maximum length.',
            str_contains($e->getMessage(), 'Column not found') => 'Invalid database column.',
            str_contains($e->getMessage(), 'Table') && str_contains($e->getMessage(), 'doesn\'t exist') => 'Database table not found.',
            str_contains($e->getMessage(), 'Connection refused') => 'Database connection failed.',
            default => $this->getErrorMessage($e)
        };
    }

    /**
     * Get appropriate error message based on environment
     * 
     * @param Throwable $e
     * @return string
     */
    protected function getErrorMessage(Throwable $e): string
    {
        return app()->environment('production')
            ? 'An unexpected error occurred.'
            : ($e->getMessage() ?: 'An unexpected error occurred.');
    }
}

