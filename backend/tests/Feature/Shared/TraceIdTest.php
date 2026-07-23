<?php

declare(strict_types=1);

use App\Http\Middleware\AssignTraceId;

it('answers unauthenticated API calls with the error envelope', function () {
    $response = $this->getJson('/api/v1/auth/me');

    $response->assertUnauthorized()
        ->assertJsonPath('error.code', 'UNAUTHENTICATED')
        ->assertJsonStructure(['error' => ['code', 'message', 'trace_id']]);

    expect($response->json('error.trace_id'))->not->toBeEmpty();
});

it('returns the trace id as a response header', function () {
    $response = $this->getJson('/api/v1/auth/me');

    expect($response->headers->get(AssignTraceId::HEADER))
        ->toBe($response->json('error.trace_id'));
});

it('honours an inbound X-Request-Id so an existing trace is not broken', function () {
    $response = $this->getJson('/api/v1/auth/me', [AssignTraceId::HEADER => 'spa-abc-123']);

    $response->assertJsonPath('error.trace_id', 'spa-abc-123');
    expect($response->headers->get(AssignTraceId::HEADER))->toBe('spa-abc-123');
});
