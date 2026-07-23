<?php

declare(strict_types=1);

namespace App\Library\Shared\Application;

interface AuditLogger
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function record(string $event, array $context): void;
}
