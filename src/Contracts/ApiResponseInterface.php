<?php

declare(strict_types=1);

namespace Aliziodev\ApiResponse\Contracts;

use Illuminate\Http\JsonResponse;
use Throwable;

interface ApiResponseInterface
{
    /**
     * Create response based on status code
     *
     * @param mixed $data
     * @param string|null $message
     * @param array<string, mixed> $meta
     * @param int $code
     * @param string|null $ref
     * @param Throwable|null $exception
     */
    public function respond(
        mixed $data = null,
        ?string $message = null,
        array $meta = [],
        int $code = 200,
        ?string $ref = null,
        ?Throwable $exception = null
    ): JsonResponse;

    /**
     * Create a dynamic response based on exception
     *
     * @param Throwable $e
     * @param string|null $message
     * @param array<string, string|array<string>> $errors
     * @param string|null $ref
     */
    public function handleException(
        Throwable $e,
        ?string $message = null,
        array $errors = [],
        ?string $ref = null
    ): JsonResponse;

    /**
     * Create a success response
     *
     * @param mixed $data
     * @param string|null $message
     * @param array<string, mixed> $meta
     * @param int $code
     */
    public function success(
        mixed $data = null,
        ?string $message = null,
        array $meta = [],
        int $code = 200
    ): JsonResponse;

    /**
     * Create an error response
     *
     * @param string|null $message
     * @param array<string, string|array<string>> $errors
     * @param int $code
     * @param string|null $ref
     * @param Throwable|null $exception
     */
    public function error(
        ?string $message = null,
        array $errors = [],
        int $code = 500,
        ?string $ref = null,
        ?Throwable $exception = null
    ): JsonResponse;

    /**
     * Create a fail response
     *
     * @param string|null $message
     * @param array<string, string|array<string>> $errors
     * @param int $code
     * @param string|null $ref
     */
    public function fail(
        ?string $message = null,
        array $errors = [],
        int $code = 400,
        ?string $ref = null
    ): JsonResponse;

    /**
     * Generate a reference code for logging
     */
    public static function refCode(): string;

    /**
     * Create a created response
     *
     * @param mixed $data
     * @param string|null $message
     * @param array<string, mixed> $meta
     */
    public function created(
        mixed $data = null,
        ?string $message = null,
        array $meta = []
    ): JsonResponse;

    /**
     * Create a no content response
     *
     * @param string|null $message
     */
    public function noContent(?string $message = null): JsonResponse;

    /**
     * Create an accepted response
     *
     * @param mixed $data
     * @param string|null $message
     * @param array<string, mixed> $meta
     */
    public function accepted(
        mixed $data = null,
        ?string $message = null,
        array $meta = []
    ): JsonResponse;

    /**
     * Create a deleted response
     *
     * @param string|null $message
     */
    public function deleted(?string $message = null): JsonResponse;

    /**
     * Create an updated response
     *
     * @param mixed $data
     * @param string|null $message
     * @param array<string, mixed> $meta
     */
    public function updated(
        mixed $data = null,
        ?string $message = null,
        array $meta = []
    ): JsonResponse;

    /**
     * Create a forbidden response
     *
     * @param string|null $message
     * @param array<string, string|array<string>> $errors
     */
    public function forbidden(
        ?string $message = null,
        array $errors = []
    ): JsonResponse;

    /**
     * Create an unauthorized response
     *
     * @param string|null $message
     * @param array<string, string|array<string>> $errors
     */
    public function unauthorized(
        ?string $message = null,
        array $errors = []
    ): JsonResponse;

    /**
     * Create a validation error response
     *
     * @param array<string, string|array<string>> $errors
     * @param string|null $message
     */
    public function validationError(
        array $errors,
        ?string $message = null
    ): JsonResponse;

    /**
     * Create a not found response
     *
     * @param string|null $message
     * @param array<string, string|array<string>> $errors
     */
    public function notFound(
        ?string $message = null,
        array $errors = []
    ): JsonResponse;

    /**
     * Create a too many requests response
     *
     * @param string|null $message
     * @param array<string, string|array<string>> $errors
     */
    public function tooManyRequests(
        ?string $message = null,
        array $errors = []
    ): JsonResponse;

    /**
     * Create a service unavailable response
     *
     * @param string|null $message
     * @param array<string, string|array<string>> $errors
     */
    public function serviceUnavailable(
        ?string $message = null,
        array $errors = []
    ): JsonResponse;

    /**
     * Create a maintenance mode response
     *
     * @param string|null $message
     * @param array<string, string|array<string>> $errors
     */
    public function maintenance(
        ?string $message = null,
        array $errors = []
    ): JsonResponse;
}
