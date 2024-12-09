<?php

declare(strict_types=1);

namespace Aliziodev\ApiResponse\Facades;

use Illuminate\Support\Facades\Facade;

class ApiResponse extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'api-response';
    }
}