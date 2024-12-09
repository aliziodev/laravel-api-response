<?php

declare(strict_types=1);

namespace Aliziodev\ApiResponse\Response;

use Aliziodev\ApiResponse\Support\HttpResponse;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;

class ApiSuccessResponse implements Responsable
{
    /**
     * @param mixed $data
     * @param string|null $message
     * @param array<string, mixed> $meta
     * @param int $code
     */
    public function __construct(
        private mixed $data = null,
        private ?string $message = null,
        private array $meta = [],
        private int $code = 200
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

        if (!is_null($this->data)) {
            $response['data'] = $this->data;
        }

        if (!empty($this->meta)) {
            $response['meta'] = $this->meta;
        }

        return response()->json($response, $this->code);
    }
}
