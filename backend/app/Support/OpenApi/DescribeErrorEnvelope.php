<?php

declare(strict_types=1);

namespace App\Support\OpenApi;

use Dedoc\Scramble\Contracts\DocumentTransformer;
use Dedoc\Scramble\OpenApiContext;
use Dedoc\Scramble\Support\Generator\OpenApi;

/**
 * Replaces Scramble's stock error responses with the envelope this API actually
 * returns (FR-ERR-1), so the published contract matches the wire.
 */
final class DescribeErrorEnvelope implements DocumentTransformer
{
    public function handle(OpenApi $document, OpenApiContext $context): void
    {
        foreach ($document->components->responses as $name => $response) {
            // Components are keyed by fully qualified exception name and only
            // shortened on output, so the match is on the class name.
            $replacement = match (class_basename($name)) {
                'ValidationException' => ErrorEnvelope::response(
                    'The input is malformed. `details.errors` lists the offending fields.',
                    ['VALIDATION_FAILED'],
                    ErrorEnvelope::validationDetails(),
                ),
                'AuthenticationException' => ErrorEnvelope::response(
                    'No valid bearer token was supplied.',
                    ['UNAUTHENTICATED'],
                ),
                'AuthorizationException' => ErrorEnvelope::response(
                    'The authenticated role may not perform this action.',
                    ['FORBIDDEN'],
                ),
                'ModelNotFoundException', 'NotFoundHttpException' => ErrorEnvelope::response(
                    'The resource does not exist, or the caller may not know that it does.',
                    ['NOT_FOUND'],
                ),
                default => null,
            };

            if ($replacement !== null) {
                $document->components->responses[$name] = $replacement->setCode($response->code);
            }
        }
    }
}
