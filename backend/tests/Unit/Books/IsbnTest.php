<?php

declare(strict_types=1);

use App\Library\Domains\Books\ValueObjects\Isbn;

it('accepts valid ISBN-13 values in any notation', function (string $raw) {
    expect(Isbn::fromString($raw)->value)->toBe('9780060883287');
})->with([
    'plain' => ['9780060883287'],
    'hyphenated' => ['978-0-06-088328-7'],
    'spaced' => ['978 0 06 088328 7'],
    'padded' => ['  9780060883287  '],
]);

it('converts a valid ISBN-10 to its ISBN-13 equivalent so duplicates collide', function () {
    // 0-306-40615-2 is the canonical ISBN-10 example; its ISBN-13 is 9780306406157.
    expect(Isbn::fromString('0-306-40615-2')->value)->toBe('9780306406157');
});

it('accepts the X check digit of ISBN-10', function () {
    expect(Isbn::tryFrom('043942089X'))->not->toBeNull();
});

it('rejects values whose checksum does not hold', function (string $raw) {
    expect(Isbn::tryFrom($raw))->toBeNull();
})->with([
    'wrong ISBN-13 check digit' => ['9780060883288'],
    'wrong ISBN-10 check digit' => ['0306406153'],
    'too short' => ['123456789'],
    'too long' => ['97800608832870'],
    'not numeric' => ['not-an-isbn!!'],
    'empty' => [''],
]);

it('throws when constructed from an invalid value', function () {
    expect(fn () => Isbn::fromString('9780060883288'))->toThrow(InvalidArgumentException::class);
});

it('recognises ISBN-shaped search terms to route them to the exact lookup', function () {
    expect(Isbn::looksLikeIsbn('978-0-06-088328-7'))->toBeTrue()
        ->and(Isbn::looksLikeIsbn('043942089X'))->toBeTrue()
        ->and(Isbn::looksLikeIsbn('solitude'))->toBeFalse()
        ->and(Isbn::looksLikeIsbn('12345'))->toBeFalse();
});
