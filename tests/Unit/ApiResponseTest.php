<?php

namespace Tests\Unit;

use Tests\TestCase;
use Aliziodev\ApiResponse\Response\ApiResponse;
use Illuminate\Http\Response;

class ApiResponseTest extends TestCase
{
    protected ApiResponse $apiResponse;

    protected function setUp(): void
    {
        parent::setUp();
        $this->apiResponse = new ApiResponse();
    }

    /** @test */
    public function it_can_generate_success_response()
    {
        $data = ['name' => 'John Doe'];
        $response = $this->apiResponse->success(
            data: $data,
            message: 'Success',
            meta: ['page' => 1],
            code: Response::HTTP_OK
        );

        expect($response)
            ->toBeSuccessResponse()
            ->and(getResponseContent($response))
            ->toHaveKey('data', $data)
            ->toHaveKey('meta', ['page' => 1])
            ->toHaveKey('message', 'Success');
    }

    /** @test */
    public function it_can_generate_error_response()
    {
        $errors = ['error' => 'Something went wrong'];
        $response = $this->apiResponse->error(
            message: 'Error occurred',
            errors: $errors,
            code: Response::HTTP_INTERNAL_SERVER_ERROR
        );

        expect($response)
            ->toBeErrorResponse()
            ->toHaveValidRef()
            ->and(getResponseContent($response))
            ->toHaveKey('errors', $errors)
            ->toHaveKey('message', 'Error occurred');
    }

    /** @test */
    public function it_can_generate_fail_response()
    {
        $errors = ['Invalid input'];
        $response = $this->apiResponse->fail(
            message: 'Validation failed',
            errors: $errors,
            code: Response::HTTP_BAD_REQUEST
        );

        expect($response)
            ->toBeFailResponse()
            ->and(getResponseContent($response))
            ->toHaveKey('errors', $errors)
            ->toHaveKey('message', 'Validation failed');
    }

    /** @test */
    public function it_can_generate_created_response()
    {
        $data = ['id' => 1, 'name' => 'New Resource'];
        $response = $this->apiResponse->created(
            data: $data,
            message: 'Resource created',
            meta: ['type' => 'user']
        );

        expect($response)
            ->toBeValidResponse('success', 201)
            ->and(getResponseContent($response))
            ->toHaveKey('data', $data)
            ->toHaveKey('message', 'Resource created')
            ->toHaveKey('meta', ['type' => 'user']);
    }

    /** @test */
    public function it_can_generate_no_content_response()
    {
        $response = $this->apiResponse->noContent('Resource deleted');

        expect($response)
            ->toBeValidResponse('success', 204)
            ->and(getResponseContent($response))
            ->toHaveKey('message', 'Resource deleted');
    }

    /** @test */
    public function it_can_generate_accepted_response()
    {
        $data = ['job_id' => 'abc123'];
        $response = $this->apiResponse->accepted(
            data: $data,
            message: 'Job queued',
            meta: ['queue' => 'default']
        );

        expect($response)
            ->toBeValidResponse('success', 202)
            ->and(getResponseContent($response))
            ->toHaveKey('data', $data)
            ->toHaveKey('message', 'Job queued')
            ->toHaveKey('meta', ['queue' => 'default']);
    }

    /** @test */
    public function it_can_generate_deleted_response()
    {
        $response = $this->apiResponse->deleted('Resource successfully deleted');

        expect($response)
            ->toBeSuccessResponse()
            ->and(getResponseContent($response))
            ->toHaveKey('message', 'Resource successfully deleted');
    }

    /** @test */
    public function it_can_generate_updated_response()
    {
        $data = ['id' => 1, 'name' => 'Updated Resource'];
        $response = $this->apiResponse->updated(
            data: $data,
            message: 'Resource updated',
            meta: ['version' => 2]
        );

        expect($response)
            ->toBeSuccessResponse()
            ->and(getResponseContent($response))
            ->toHaveKey('data', $data)
            ->toHaveKey('message', 'Resource updated')
            ->toHaveKey('meta', ['version' => 2]);
    }

    /** @test */
    public function it_can_generate_forbidden_response()
    {
        $response = $this->apiResponse->forbidden(
            message: 'Access denied',
            errors: ['Insufficient permissions']
        );

        expect($response)
            ->toBeValidResponse('fail', 403)
            ->and(getResponseContent($response))
            ->toHaveKey('message', 'Access denied')
            ->toHaveKey('errors', ['Insufficient permissions']);
    }

    /** @test */
    public function it_can_generate_unauthorized_response()
    {
        $response = $this->apiResponse->unauthorized(
            message: 'Authentication required',
            errors: ['Invalid token']
        );

        expect($response)
            ->toBeValidResponse('fail', 401)
            ->and(getResponseContent($response))
            ->toHaveKey('message', 'Authentication required')
            ->toHaveKey('errors', ['Invalid token']);
    }

    /** @test */
    public function it_can_generate_validation_error_response()
    {
        $errors = ['email' => ['Email is required']];
        $response = $this->apiResponse->validationError(
            errors: $errors,
            message: 'Validation failed'
        );

        expect($response)
            ->toBeValidResponse('fail', 422)
            ->and(getResponseContent($response))
            ->toHaveKey('message', 'Validation failed')
            ->toHaveKey('errors', $errors);
    }

    /** @test */
    public function it_can_generate_not_found_response()
    {
        $response = $this->apiResponse->notFound(
            message: 'Resource not found',
            errors: ['Record does not exist']
        );

        expect($response)
            ->toBeValidResponse('fail', 404)
            ->and(getResponseContent($response))
            ->toHaveKey('message', 'Resource not found')
            ->toHaveKey('errors', ['Record does not exist']);
    }

    /** @test */
    public function it_can_generate_too_many_requests_response()
    {
        $response = $this->apiResponse->tooManyRequests(
            message: 'Rate limit exceeded',
            errors: ['Try again later']
        );

        expect($response)
            ->toBeValidResponse('fail', 429)
            ->and(getResponseContent($response))
            ->toHaveKey('message', 'Rate limit exceeded')
            ->toHaveKey('errors', ['Try again later']);
    }

    /** @test */
    public function it_can_generate_service_unavailable_response()
    {
        $errors = ['service' => 'Maintenance in progress'];
        $response = $this->apiResponse->serviceUnavailable(
            message: 'Service is down',
            errors: $errors
        );

        expect($response)
            ->toBeValidResponse('error', 503)
            ->and(getResponseContent($response))
            ->toHaveKey('message', 'Service is down')
            ->toHaveKey('errors', $errors);
    }

    /** @test */
    public function it_can_generate_maintenance_response()
    {
        $errors = ['maintenance' => 'Please try again later'];
        $response = $this->apiResponse->maintenance(
            message: 'System maintenance',
            errors: $errors
        );

        expect($response)
            ->toBeValidResponse('error', 503)
            ->and(getResponseContent($response))
            ->toHaveKey('message', 'System maintenance')
            ->toHaveKey('errors', $errors);
    }

    /** @test */
    public function it_can_generate_ref_code()
    {
        $refCode = ApiResponse::refCode();
        expect($refCode)->toMatch('/^ERR-\d{8}-REF-[a-f0-9]+$/i');
    }

    /** @test */
    public function it_can_handle_dynamic_response_based_on_status_code()
    {
        $response = $this->apiResponse->respond(
            data: ['test' => true],
            message: 'Test message',
            meta: ['page' => 1],
            code: Response::HTTP_OK
        );

        expect($response)
            ->toBeSuccessResponse()
            ->and(getResponseContent($response))
            ->toHaveKey('data', ['test' => true])
            ->toHaveKey('message', 'Test message')
            ->toHaveKey('meta', ['page' => 1]);
    }
}
