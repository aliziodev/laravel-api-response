<?php

use Tests\TestCase;

uses(TestCase::class)->in('Unit');

// Custom expectations
expect()->extend('toBeValidResponse', function (string $status, int $code) {
    $content = json_decode($this->value->getContent(), true);

    expect($content)
        ->toHaveKey('status', $status)
        ->toHaveKey('code', $code);

    return $this;
});

expect()->extend('toHaveValidRef', function () {
    $content = json_decode($this->value->getContent(), true);
    expect($content['ref'])->toMatch('/^ERR-\d{8}-REF-[a-f0-9]+$/i');
    return $this;
});

expect()->extend('toHaveDebugInfo', function () {
    $content = json_decode($this->value->getContent(), true);
    expect($content)->toHaveKey('debug');
    expect($content['debug'])
        ->toHaveKey('environment')
        ->toHaveKey('exception')
        ->toHaveKey('error_message');
    return $this;
});

expect()->extend('toBeSuccessResponse', function () {
    return $this->toBeValidResponse('success', 200);
});

expect()->extend('toBeErrorResponse', function () {
    return $this->toBeValidResponse('error', 500);
});

expect()->extend('toBeFailResponse', function () {
    return $this->toBeValidResponse('fail', 400);
});

// Helper functions
function getResponseContent($response) {
    return json_decode($response->getContent(), true);
}
