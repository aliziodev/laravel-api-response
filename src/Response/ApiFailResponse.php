<?php

declare(strict_types=1);

namespace Aliziodev\ApiResponse\Response;

use Aliziodev\ApiResponse\Support\HttpResponse;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;

class ApiFailResponse implements Responsable
{
    /**
     * @param string|null $message
     * @param array<string, string|array<string>> $errors
     * @param int $code
     * @param string|null $ref
     */
    public function __construct(
        private ?string $message = null,
        private array $errors = [],
        private int $code = 400,
        private ?string $ref = null
    ) {}

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toResponse($request): JsonResponse
    {
        $response = [
            'status' => HttpResponse::getType($this->code),
            'message' => $this->message ?? HttpResponse::getMessage($this->code),
            'code' => $this->code,
        ];

        if (!empty($this->errors)) {
            $response['env'] = app()->environment();
            $response['errors'] = $this->errors;
        }

        if ($this->ref) {
            $response['ref'] = $this->ref;
        }

        return response()->json($response, $this->code);
    }
}
