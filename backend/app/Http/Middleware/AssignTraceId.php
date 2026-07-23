<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * RFC 8: one identifier ties a user's report to its logs and to the error the
 * client saw. An inbound X-Request-Id is honoured so a trace that started at
 * the SPA or a gateway is not broken here.
 */
final class AssignTraceId
{
    public const HEADER = 'X-Request-Id';

    public const CONTEXT_KEY = 'trace_id';

    public function handle(Request $request, Closure $next): Response
    {
        $traceId = $request->header(self::HEADER);

        if (! is_string($traceId) || trim($traceId) === '' || strlen($traceId) > 128) {
            $traceId = (string) Str::uuid();
        }

        // Available to the exception renderer, to every log line in this
        // request, and to the client for support conversations.
        $request->attributes->set(self::CONTEXT_KEY, $traceId);
        Context::add(self::CONTEXT_KEY, $traceId);

        $response = $next($request);
        $response->headers->set(self::HEADER, $traceId);

        return $response;
    }
}
