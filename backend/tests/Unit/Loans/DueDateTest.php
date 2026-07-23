<?php

declare(strict_types=1);

use App\Library\Domains\Loans\ValueObjects\DueDate;
use Carbon\CarbonImmutable;

it('defaults to the configured loan period', function () {
    $loanedAt = CarbonImmutable::parse('2026-07-22');

    expect(DueDate::fromLoanDate($loanedAt)->value->toDateString())->toBe('2026-08-05');
});

it('accepts a requested date inside the window', function (int $days) {
    $loanedAt = CarbonImmutable::parse('2026-07-22');
    $requested = $loanedAt->addDays($days);

    expect(DueDate::fromLoanDate($loanedAt, $requested)->value->toDateString())
        ->toBe($requested->toDateString());
})->with([
    'minimum' => [1],
    'default' => [14],
    'maximum' => [60],
]);

it('refuses a due date on or before the loan date', function (int $days) {
    $loanedAt = CarbonImmutable::parse('2026-07-22');

    expect(fn () => DueDate::fromLoanDate($loanedAt, $loanedAt->addDays($days)))
        ->toThrow(InvalidArgumentException::class);
})->with([
    'same day' => [0],
    'yesterday' => [-1],
]);

it('refuses a due date beyond the maximum period', function () {
    $loanedAt = CarbonImmutable::parse('2026-07-22');

    expect(fn () => DueDate::fromLoanDate($loanedAt, $loanedAt->addDays(61)))
        ->toThrow(InvalidArgumentException::class);
});

it('reports whether a date falls inside the window', function () {
    $loanedAt = CarbonImmutable::parse('2026-07-22');

    expect(DueDate::isWithinAllowedWindow($loanedAt, $loanedAt->addDays(30)))->toBeTrue()
        ->and(DueDate::isWithinAllowedWindow($loanedAt, $loanedAt))->toBeFalse()
        ->and(DueDate::isWithinAllowedWindow($loanedAt, $loanedAt->addDays(61)))->toBeFalse();
});
