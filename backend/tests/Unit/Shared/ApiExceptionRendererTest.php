<?php

declare(strict_types=1);

use App\Exceptions\ApiExceptionRenderer;
use App\Http\Middleware\AssignTraceId;
use App\Library\Shared\Domain\DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

/**
 * Stand-in for a real rule violation, so the renderer is tested against the
 * contract rather than against one domain's implementation.
 */
final class LoanLimitReachedStub extends DomainException
{
    public function errorCode(): string
    {
        return 'LOAN_LIMIT_REACHED';
    }

    /**
     * @return array<string, mixed>
     */
    public function details(): array
    {
        return ['limit' => 5, 'active_loans' => 5];
    }
}

function renderApiException(Throwable $e, string $traceId = 'trace-123'): array
{
    $request = Request::create('/api/v1/loans', 'POST');
    $request->attributes->set(AssignTraceId::CONTEXT_KEY, $traceId);

    $response = (new ApiExceptionRenderer)($e, $request);

    return [$response->getStatusCode(), $response->getData(true), $response];
}

it('maps a domain rule violation to 409 with its stable code and details', function () {
    [$status, $body] = renderApiException(new LoanLimitReachedStub('This member already has 5 active loans.'));

    expect($status)->toBe(409)
        ->and($body['error']['code'])->toBe('LOAN_LIMIT_REACHED')
        ->and($body['error']['message'])->toBe('This member already has 5 active loans.')
        ->and($body['error']['details'])->toBe(['limit' => 5, 'active_loans' => 5])
        ->and($body['error']['trace_id'])->toBe('trace-123');
});

it('maps validation failures to 422 with per-field errors', function () {
    $exception = ValidationException::withMessages([
        'isbn' => ['The ISBN checksum is invalid.'],
        'author_ids' => ['At least one author is required.'],
    ]);

    [$status, $body] = renderApiException($exception);

    expect($status)->toBe(422)
        ->and($body['error']['code'])->toBe('VALIDATION_FAILED')
        ->and($body['error']['details']['errors'])->toBe([
            'isbn' => ['The ISBN checksum is invalid.'],
            'author_ids' => ['At least one author is required.'],
        ]);
});

it('maps the framework exceptions to their registry codes', function (Throwable $e, int $expectedStatus, string $expectedCode) {
    [$status, $body] = renderApiException($e);

    expect($status)->toBe($expectedStatus)
        ->and($body['error']['code'])->toBe($expectedCode);
})->with([
    'unauthenticated' => [new AuthenticationException, 401, 'UNAUTHENTICATED'],
    'forbidden' => [new AuthorizationException, 403, 'FORBIDDEN'],
    'missing model' => [new ModelNotFoundException, 404, 'NOT_FOUND'],
    'missing route' => [new NotFoundHttpException, 404, 'NOT_FOUND'],
    'throttled' => [new TooManyRequestsHttpException, 429, 'RATE_LIMITED'],
]);

it('hides internals behind a generic 500', function () {
    config()->set('app.debug', false);

    [$status, $body] = renderApiException(new RuntimeException('SQLSTATE[42S02]: table missing'));

    expect($status)->toBe(500)
        ->and($body['error']['code'])->toBe('INTERNAL_ERROR')
        ->and($body['error']['message'])->toBe('An unexpected error occurred.')
        ->and($body['error'])->not->toHaveKey('details');
});

it('keeps protocol headers such as Retry-After', function () {
    [, , $response] = renderApiException(new TooManyRequestsHttpException(30));

    expect($response->headers->get('Retry-After'))->toBe('30');
});

it('ignores non-API requests so the web surface keeps its own error pages', function () {
    $response = (new ApiExceptionRenderer)(new NotFoundHttpException, Request::create('/'));

    expect($response)->toBeNull();
});
