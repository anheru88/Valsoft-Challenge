<?php

declare(strict_types=1);

namespace App\Library\Shared\Application;

use Carbon\CarbonImmutable;

/**
 * Time as an injected dependency, so rules that depend on "now" (due dates,
 * overdue detection) are testable without freezing global state and the domain
 * layer never reaches for a Laravel facade (RFC 5).
 */
interface Clock
{
    public function now(): CarbonImmutable;

    public function today(): CarbonImmutable;
}
