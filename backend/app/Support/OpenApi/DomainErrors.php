<?php

declare(strict_types=1);

namespace App\Support\OpenApi;

use Attribute;

/**
 * Declares the business-rule refusals an endpoint can answer with.
 *
 * These are thrown deep inside actions, so no amount of static inference finds
 * them from the controller. Naming them here keeps the documented codes next to
 * the endpoint that produces them, where they are visible when the rules change.
 */
// Repeatable: an endpoint can refuse for reasons that map to different statuses,
// such as login answering 422 for bad credentials and 403 for a disabled account.
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class DomainErrors
{
    /**
     * @param  list<string>  $codes
     */
    public function __construct(
        public array $codes,
        public int $status = 409,
        public string $description = 'The request is well formed, but a business rule refuses it.',
    ) {}
}
