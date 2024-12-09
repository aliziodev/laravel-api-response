<?php

declare(strict_types=1);

namespace Aliziodev\ApiResponse\Response;

use Aliziodev\ApiResponse\Support\HttpResponse;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;

use Throwable;

class ApiErrorResponse implements Responsable
{
    /**
     * @param string|null $message
     * @param array<string, string|array<string>> $errors
     * @param int $code
     * @param string $ref
     * @param Throwable|null $exception
     */
    public function __construct(
        private ?string $message,
        private array $errors,
        private int $code,
        private string $ref,
        private ?Throwable $exception = null
    ) {}

    /**
     * Create an HTTP response that represents the object.
     */
    public function toResponse($request): JsonResponse
    {
        $response = [
            'status' => HttpResponse::getType($this->code),
            'code' => $this->code,
            'message' => $this->message ?? 'Server Error',
            'ref' => $this->ref,
            'errors' => $this->errors,
        ];

        if (app()->environment('local', 'testing', 'staging') && $this->exception) {
            $response['debug'] = [
                'environment' => app()->environment(),
                'exception' => get_class($this->exception),
                'error_message' => $this->exception->getMessage(),
                'file' => $this->exception->getFile(),
                'line' => $this->exception->getLine(),
                'trace' => $this->exception->getTraceAsString(),
            ];
        }

        return new JsonResponse($response, $this->code);
    }
}
