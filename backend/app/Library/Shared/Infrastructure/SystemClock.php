<?php

declare(strict_types=1);

namespace App\Library\Shared\Infrastructure;

use App\Library\Shared\Application\Clock;
use Carbon\CarbonImmutable;

final class SystemClock implements Clock
{
    public function now(): CarbonImmutable
    {
        return CarbonImmutable::now();
    }

    public function today(): CarbonImmutable
    {
        return CarbonImmutable::now()->startOfDay();
    }
}
