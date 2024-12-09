<?php

declare(strict_types=1);

namespace Aliziodev\ApiResponse\Response;

use Aliziodev\ApiResponse\Contracts\ApiResponseInterface;
use Aliziodev\ApiResponse\Logging\ApiLogger;
use Aliziodev\ApiResponse\Support\HttpResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class ApiResponse implements ApiResponseInterface
{
    /**
     * Generate a reference code for logging.
     * 
     * @return string
     */
    public static function refCode(): string
    {
        return strtoupper('ERR-' . date('Ymd') . '-' . uniqid('REF-'));
    }

    /**
     * Create response based on status code
     * 
     * @param mixed $data
     * @param string|null $message
     * @param array<string, mixed> $meta
     * @param int $code
     * @param string|null $ref
     * @param \Throwable|null $exception
     */
    public function respond(
        mixed $data = null,
        ?string $message = null,
        array $meta = [],
        int $code = Response::HTTP_OK,
        ?string $ref = null,
        ?Throwable $exception = null
    ): JsonResponse {
        if (HttpResponse::isSuccess($code)) {
            return $this->success($data, $message, $meta, $code);
        }

        if (HttpResponse::isRedirect($code)) {
            return $this->fail(
                message: $message ?? HttpResponse::getMessage($code),
                errors: [],
                code: $code,
                ref: $ref
            );
        }

        if (HttpResponse::isClientError($code)) {
            return $this->fail(
                message: $message ?? HttpResponse::getMessage($code),
                errors: is_array($data) ? $data : [],
                code: $code,
                ref: $ref
            );
        }

        if (HttpResponse::isServerError($code)) {
            return $this->error(
                message: $message ?? HttpResponse::getMessage($code),
                errors: is_array($data) ? $data : [],
                code: $code,
                ref: self::refCode(),
                exception: $exception
            );
        }

        // Default to error response
        return $this->error(
            message: $message ?? 'Unknown Error',
            errors: is_array($data) ? $data : [],
            code: $code,
            ref: self::refCode(),
            exception: $exception
        );
    }

    /**
     * Create a dynamic response based on exception
     * 
     * @param \Throwable $e
     * @param string|null $message
     * @param array<string, mixed> $errors
     * @param string|null $ref
     */
    public function handleException(
        Throwable $e,
        ?string $message = null,
        array $errors = [],
        ?string $ref = null
    ): JsonResponse {
        $code = $e instanceof HttpException ? $e->getStatusCode() : Response::HTTP_INTERNAL_SERVER_ERROR;

        if (HttpResponse::isClientError($code)) {
            return $this->fail(
                message: $message ?? $e->getMessage(),
                errors: $errors,
                code: $code,
                ref: $ref
            );
        }

        return $this->error(
            message: $message ?? $e->getMessage(),
            errors: $errors,
            code: $code,
            ref: self::refCode(),
            exception: $e
        );
    }

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
        int $code = Response::HTTP_OK
    ): JsonResponse {
        return (new ApiSuccessResponse($data, $message, $meta, $code))
            ->toResponse(request());
    }

    /**
     * Create an error response
     * 
     * @param string|null $message
     * @param array<string, string|array<string>> $errors
     * @param int $code
     * @param string|null $ref
     * @param \Throwable|null $exception
     */
    public function error(
        ?string $message = null,
        array $errors = [],
        int $code = Response::HTTP_INTERNAL_SERVER_ERROR,
        ?string $ref = null,
        ?Throwable $exception = null
    ): JsonResponse {
        $ref = $ref ?? self::refCode();

        // Log the error response
        app(ApiLogger::class)->error($ref, $message, $errors, $code, $exception);

        return (new ApiErrorResponse($message, $errors, $code, $ref, $exception))
            ->toResponse(request());
    }

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
        int $code = Response::HTTP_BAD_REQUEST,
        ?string $ref = null
    ): JsonResponse {
        $ref = $ref ?? self::refCode();
        return (new ApiFailResponse($message, $errors, $code, $ref))
            ->toResponse(request());
    }

    /**
     * Create a created response
     * 
     * @param mixed $data
     * @param string|null $message
     * @param array<string, mixed> $meta
     * @param int $code
     */
    public function created(
        mixed $data = null,
        ?string $message = null,
        array $meta = [],
        int $code = Response::HTTP_CREATED
    ): JsonResponse {
        return $this->success(
            data: $data,
            message: $message ?? HttpResponse::getMessage(Response::HTTP_CREATED),
            meta: $meta,
            code: Response::HTTP_CREATED
        );
    }

    /**
     * Create a no content response
     * 
     * @param string|null $message
     */
    public function noContent(
        ?string $message = null
    ): JsonResponse {
        return $this->success(
            message: $message ?? HttpResponse::getMessage(Response::HTTP_NO_CONTENT),
            code: Response::HTTP_NO_CONTENT
        );
    }

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
    ): JsonResponse {
        return $this->success(
            data: $data,
            message: $message ?? HttpResponse::getMessage(Response::HTTP_ACCEPTED),
            meta: $meta,
            code: Response::HTTP_ACCEPTED
        );
    }

    /**
     * Create a deleted response
     * 
     * @param string|null $message
     */
    public function deleted(
        ?string $message = null
    ): JsonResponse {
        return $this->success(
            message: $message ?? 'Resource deleted successfully',
            code: Response::HTTP_OK
        );
    }

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
    ): JsonResponse {
        return $this->success(
            data: $data,
            message: $message ?? 'Resource updated successfully',
            meta: $meta,
            code: Response::HTTP_OK
        );
    }

    /**
     * Create a forbidden response
     * 
     * @param string|null $message
     * @param array<string, string|array<string>> $errors
     */
    public function forbidden(
        ?string $message = null,
        array $errors = []
    ): JsonResponse {
        return $this->fail(
            message: $message ?? HttpResponse::getMessage(Response::HTTP_FORBIDDEN),
            errors: $errors,
            code: Response::HTTP_FORBIDDEN
        );
    }

    /**
     * Create an unauthorized response
     * 
     * @param string|null $message
     * @param array<string, string|array<string>> $errors
     */
    public function unauthorized(
        ?string $message = null,
        array $errors = []
    ): JsonResponse {
        return $this->fail(
            message: $message ?? HttpResponse::getMessage(Response::HTTP_UNAUTHORIZED),
            errors: $errors,
            code: Response::HTTP_UNAUTHORIZED
        );
    }

    /**
     * Create a validation error response
     * 
     * @param array<string, string|array<string>> $errors
     * @param string|null $message
     */
    public function validationError(
        array $errors,
        ?string $message = null
    ): JsonResponse {
        return $this->fail(
            message: $message ?? HttpResponse::getMessage(Response::HTTP_UNPROCESSABLE_ENTITY),
            errors: $errors,
            code: Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }

    /**
     * Create a not found response
     * 
     * @param string|null $message
     * @param array<string, string|array<string>> $errors
     */
    public function notFound(
        ?string $message = null,
        array $errors = []
    ): JsonResponse {
        return $this->fail(
            message: $message ?? HttpResponse::getMessage(Response::HTTP_NOT_FOUND),
            errors: $errors,
            code: Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Create a too many requests response
     * 
     * @param string|null $message
     * @param array<string, string|array<string>> $errors
     */
    public function tooManyRequests(
        ?string $message = null,
        array $errors = []
    ): JsonResponse {
        return $this->fail(
            message: $message ?? HttpResponse::getMessage(Response::HTTP_TOO_MANY_REQUESTS),
            errors: $errors,
            code: Response::HTTP_TOO_MANY_REQUESTS
        );
    }

    /**
     * Create a service unavailable response
     * 
     * @param string|null $message
     * @param array<string, string|array<string>> $errors
     */
    public function serviceUnavailable(
        ?string $message = null,
        array $errors = []
    ): JsonResponse {
        return $this->error(
            ref: self::refCode(),
            message: $message ?? HttpResponse::getMessage(Response::HTTP_SERVICE_UNAVAILABLE),
            errors: $errors,
            code: Response::HTTP_SERVICE_UNAVAILABLE
        );
    }

    /**
     * Create a maintenance mode response
     * 
     * @param string|null $message
     * @param array<string, string|array<string>> $errors
     */
    public function maintenance(
        ?string $message = null,
        array $errors = []
    ): JsonResponse {
        return $this->error(
            ref: self::refCode(),
            message: $message ?? 'System is under maintenance',
            errors: $errors,
            code: Response::HTTP_SERVICE_UNAVAILABLE
        );
    }
}
