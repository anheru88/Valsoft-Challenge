<?php

declare(strict_types=1);

namespace App\Library\Domains\Loans\ValueObjects;

use Carbon\CarbonImmutable;
use InvalidArgumentException;

/**
 * BR-LOAN-5: the loan window. A DueDate cannot hold a date in the past or
 * further out than the configured maximum, so the rule is enforced by the type
 * rather than re-checked at each call site.
 */
final readonly class DueDate
{
    private function __construct(public CarbonImmutable $value) {}

    public static function fromLoanDate(CarbonImmutable $loanedAt, ?CarbonImmutable $requested = null): self
    {
        if ($requested === null) {
            return new self($loanedAt->addDays(self::defaultPeriodDays())->startOfDay());
        }

        $due = $requested->startOfDay();
        $days = (int) $loanedAt->startOfDay()->diffInDays($due, absolute: false);

        if ($days < 1) {
            throw new InvalidArgumentException('The due date must be at least one day after the loan date.');
        }

        if ($days > self::maxPeriodDays()) {
            throw new InvalidArgumentException('The due date exceeds the maximum loan period of '.self::maxPeriodDays().' days.');
        }

        return new self($due);
    }

    public static function isWithinAllowedWindow(CarbonImmutable $loanedAt, CarbonImmutable $requested): bool
    {
        $days = (int) $loanedAt->startOfDay()->diffInDays($requested->startOfDay(), absolute: false);

        return $days >= 1 && $days <= self::maxPeriodDays();
    }

    public static function defaultPeriodDays(): int
    {
        return (int) config('library.loans.default_period_days');
    }

    public static function maxPeriodDays(): int
    {
        return (int) config('library.loans.max_period_days');
    }
}
