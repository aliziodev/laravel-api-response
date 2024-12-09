<?php

declare(strict_types=1);

namespace Aliziodev\ApiResponse\Logging;

use Illuminate\Support\Facades\Log;
use Throwable;

class ApiLogger
{
    /**
     * List of sensitive keys that should be masked in logs
     *
     * @var array<string>
     */
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

    /**
     * Log error response
     *
     * @param string $ref
     * @param string|null $message
     * @param array<string, mixed> $errors
     * @param int $code
     * @param Throwable|null $exception
     * @return void
     */
    public function error(
        string $ref,
        ?string $message,
        array $errors,
        int $code,
        ?Throwable $exception = null
    ): void {
        $context = [
            'ref' => $ref,
            'message' => $message,
            'errors' => $this->maskSensitiveData($errors),
            'code' => $code,
        ];

        if ($exception) {
            $context['exception'] = [
                'class' => get_class($exception),
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ];
        }

        Log::error('API Error Response', $context);
    }

    /**
     * Log fail response
     *
     * @param string $ref
     * @param string|null $message
     * @param array<string, mixed> $errors
     * @param int $code
     * @return void
     */
    public function fail(
        string $ref,
        ?string $message,
        array $errors,
        int $code
    ): void {
        Log::warning('API Fail Response', [
            'ref' => $ref,
            'message' => $message,
            'errors' => $this->maskSensitiveData($errors),
            'code' => $code,
        ]);
    }

    /**
     * Mask sensitive data in array
     *
     * @param array<string|int, mixed> $data
     * @return array<string|int, mixed>
     */
    private function maskSensitiveData(array $data): array
    {
        $masked = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $masked[$key] = $this->maskSensitiveData($value);
                continue;
            }

            $masked[$key] = $this->isSensitiveKey((string) $key, self::$sensitiveKeys)
                ? '********'
                : $value;
        }

        return $masked;
    }

    /**
     * Check if key contains sensitive information
     *
     * @param string $key
     * @param array<string> $sensitiveKeys
     * @return bool
     */
    private static function isSensitiveKey(string $key, array $sensitiveKeys): bool
    {
        $lowercaseKey = strtolower($key);
        foreach ($sensitiveKeys as $sensitiveKey) {
            if (str_contains($lowercaseKey, $sensitiveKey)) {
                return true;
            }
        }
        return false;
    }
}
