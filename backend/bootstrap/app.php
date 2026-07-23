<?php

use App\Exceptions\ApiExceptionRenderer;
use App\Http\Middleware\AssignTraceId;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

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
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(new ApiExceptionRenderer);
    })->create();
