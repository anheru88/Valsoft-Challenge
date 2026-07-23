<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Http\Middleware\AssignTraceId;
use App\Library\Shared\Domain\DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * The single place exceptions become the error envelope of FR-ERR-1:
 * {"error": {"code", "message", "details", "trace_id"}}.
 *
 * Every code produced here is in the registry of API specification 10, and
 * nothing about the internals (stack traces, SQL) reaches the client outside
 * local debugging (FR-ERR-3).
 */
final class ApiExceptionRenderer
{
    public function __invoke(Throwable $e, Request $request): ?JsonResponse
    {
        if (! $request->is('api/*') && ! $request->expectsJson()) {
            return null;
        }

        [$status, $code, $message, $details] = $this->map($e);

        $payload = ['code' => $code, 'message' => $message];

        if ($details !== []) {
            $payload['details'] = $details;
        }

        $payload['trace_id'] = $this->traceId($request);

        $response = new JsonResponse(['error' => $payload], $status);

        // Preserve protocol headers the framework set, e.g. Retry-After on 429.
        if ($e instanceof HttpExceptionInterface) {
            $response->headers->add($e->getHeaders());
        }

        return $response;
    }

    /**
     * @return array{int, string, string, array<string, mixed>}
     */
    private function map(Throwable $e): array
    {
        return match (true) {
            // 422 means "your input is malformed"; 409 means "your input is fine,
            // the world disagrees" (RFC 6).
            $e instanceof ValidationException => [
                Response::HTTP_UNPROCESSABLE_ENTITY,
                'VALIDATION_FAILED',
                'The given data was invalid.',
                ['errors' => $e->errors()],
            ],

            $e instanceof DomainException => [
                $e->httpStatus(),
                $e->errorCode(),
                $e->getMessage(),
                $e->details(),
            ],

            $e instanceof AuthenticationException => [
                Response::HTTP_UNAUTHORIZED,
                'UNAUTHENTICATED',
                'Authentication is required to access this resource.',
                [],
            ],

            $e instanceof AuthorizationException,
            $e instanceof AccessDeniedHttpException => [
                Response::HTTP_FORBIDDEN,
                'FORBIDDEN',
                'You are not allowed to perform this action.',
                [],
            ],

            // A missing record and a record you may not know about are answered
            // identically, so 404 never leaks existence (API specification 6).
            $e instanceof ModelNotFoundException,
            $e instanceof NotFoundHttpException => [
                Response::HTTP_NOT_FOUND,
                'NOT_FOUND',
                'The requested resource was not found.',
                [],
            ],

            $e instanceof HttpExceptionInterface => [
                $e->getStatusCode(),
                $this->codeForStatus($e->getStatusCode()),
                $this->messageForHttpException($e),
                [],
            ],

            default => [
                Response::HTTP_INTERNAL_SERVER_ERROR,
                'INTERNAL_ERROR',
                'An unexpected error occurred.',
                config('app.debug') ? ['exception' => $e::class, 'message' => $e->getMessage()] : [],
            ],
        };
    }

    private function codeForStatus(int $status): string
    {
        return match ($status) {
            Response::HTTP_METHOD_NOT_ALLOWED => 'METHOD_NOT_ALLOWED',
            Response::HTTP_TOO_MANY_REQUESTS => 'RATE_LIMITED',
            Response::HTTP_SERVICE_UNAVAILABLE => 'SERVICE_UNAVAILABLE',
            default => 'HTTP_ERROR',
        };
    }

    private function messageForHttpException(HttpExceptionInterface $e): string
    {
        return match ($e->getStatusCode()) {
            Response::HTTP_METHOD_NOT_ALLOWED => 'This method is not supported for this route.',
            Response::HTTP_TOO_MANY_REQUESTS => 'Too many requests. Please retry later.',
            default => 'The request could not be processed.',
        };
    }

    private function traceId(Request $request): string
    {
        $traceId = $request->attributes->get(AssignTraceId::CONTEXT_KEY);

        return is_string($traceId) ? $traceId : '';
    }
}
