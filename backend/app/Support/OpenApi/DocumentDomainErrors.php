<?php

declare(strict_types=1);

namespace App\Support\OpenApi;

use Dedoc\Scramble\Contracts\OperationTransformer;
use Dedoc\Scramble\Support\Generator\Operation;
use Dedoc\Scramble\Support\RouteInfo;

/**
 * Turns a controller method's #[DomainErrors] attribute into a documented
 * response, and adds the trace header every response carries (RFC 8).
 */
final class DocumentDomainErrors implements OperationTransformer
{
    public function handle(Operation $operation, RouteInfo $routeInfo): void
    {
        $method = $routeInfo->reflectionMethod();

        if ($method === null) {
            return;
        }

        foreach ($method->getAttributes(DomainErrors::class) as $attribute) {
            $errors = $attribute->newInstance();

            $operation->addResponse(
                ErrorEnvelope::response($errors->description, $errors->codes)
                    ->setCode($errors->status)
            );
        }
    }
}
