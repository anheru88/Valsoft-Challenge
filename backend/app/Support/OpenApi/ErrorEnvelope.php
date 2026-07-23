<?php

declare(strict_types=1);

namespace App\Support\OpenApi;

use Dedoc\Scramble\Support\Generator\Response;
use Dedoc\Scramble\Support\Generator\Schema;
use Dedoc\Scramble\Support\Generator\Types\ArrayType;
use Dedoc\Scramble\Support\Generator\Types\ObjectType;
use Dedoc\Scramble\Support\Generator\Types\StringType;

/**
 * The error envelope of FR-ERR-1, expressed as an OpenAPI schema.
 *
 * Scramble infers success responses from the resources, but it cannot know that
 * this API replaces Laravel's default `{message, errors}` error shape with a
 * single envelope. Describing it here keeps the published contract honest.
 */
final class ErrorEnvelope
{
    /**
     * @param  list<string>  $codes  the registry entries this response can carry
     */
    public static function response(string $description, array $codes = [], ?ObjectType $details = null): Response
    {
        return Response::make(0)
            ->setDescription($description)
            ->setContent('application/json', Schema::fromType(
                (new ObjectType)
                    ->addProperty('error', self::errorObject($codes, $details))
                    ->setRequired(['error'])
            ));
    }

    /**
     * @param  list<string>  $codes
     */
    private static function errorObject(array $codes, ?ObjectType $details): ObjectType
    {
        $code = (new StringType)->setDescription(
            $codes === []
                ? 'Stable machine-readable code from the registry.'
                : 'Stable machine-readable code. One of: '.implode(', ', $codes).'.'
        );

        if ($codes !== []) {
            $code->enum($codes);
        }

        return (new ObjectType)
            ->addProperty('code', $code)
            ->addProperty('message', (new StringType)->setDescription('Human-readable message, safe to display.'))
            ->addProperty('details', ($details ?? new ObjectType)->setDescription('Machine-readable context, when the error carries any.'))
            ->addProperty('trace_id', (new StringType)->setDescription('Correlates this response with the server logs; echoed as X-Request-Id.'))
            ->setRequired(['code', 'message', 'trace_id']);
    }

    /**
     * The per-field errors a 422 carries (FR-VAL-2).
     */
    public static function validationDetails(): ObjectType
    {
        return (new ObjectType)->addProperty(
            'errors',
            (new ObjectType)
                ->additionalProperties((new ArrayType)->setItems(new StringType))
                ->setDescription('Messages keyed by the field that failed validation.')
        );
    }
}
