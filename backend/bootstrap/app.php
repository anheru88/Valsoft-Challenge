<?php

use App\Exceptions\ApiExceptionRenderer;
use App\Http\Middleware\AssignTraceId;
use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        // URI versioning (ADR-8): breaking changes get /api/v2, never a mutation
        // of this surface.
        apiPrefix: 'api/v1',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // First in the stack, so even a request rejected by a later middleware
        // still carries a trace id (RFC 8).
        $middleware->prependToGroup('api', AssignTraceId::class);

        // 60 requests/minute per authenticated user (PRD 9, Security).
        $middleware->throttleApi();

        $middleware->alias(['active' => EnsureUserIsActive::class]);

        // This application has no login page to redirect to. Returning null lets
        // the auth middleware throw, so an unauthenticated API call answers 401
        // with the error envelope whatever Accept header it carried — instead of
        // failing on a route that does not exist.
        $middleware->redirectGuestsTo(fn (Request $request): ?string => null);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(new ApiExceptionRenderer);
    })->create();
