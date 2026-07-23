<?php

declare(strict_types=1);

namespace App\Library\Domains\Books\ValueObjects;

use InvalidArgumentException;

/**
 * A validated ISBN (FR-VAL-3).
 *
 * Construction is the validation: an Isbn instance cannot hold a value that
 * failed its checksum, so no code downstream has to re-check. Input is
 * normalized (separators stripped) and ISBN-10 is converted to its ISBN-13
 * equivalent, which is what makes uniqueness in BR-BOOK-1 meaningful — the same
 * book entered in either notation collides as it should.
 */
final readonly class Isbn
{
    private function __construct(public string $value) {}

    public static function fromString(string $raw): self
    {
        $isbn = self::tryFrom($raw);

        if ($isbn === null) {
            throw new InvalidArgumentException("The value [{$raw}] is not a valid ISBN.");
        }

        return $isbn;
    }

    public static function tryFrom(string $raw): ?self
    {
        $normalized = self::normalize($raw);

        return match (strlen($normalized)) {
            10 => self::isValidIsbn10($normalized) ? new self(self::toIsbn13($normalized)) : null,
            13 => self::isValidIsbn13($normalized) ? new self($normalized) : null,
            default => null,
        };
    }

    /**
     * Whether a search term is shaped like an ISBN, which routes it to the
     * exact unique-index lookup instead of full-text (RFC 11).
     */
    public static function looksLikeIsbn(string $raw): bool
    {
        $normalized = self::normalize($raw);

        return in_array(strlen($normalized), [10, 13], true)
            && preg_match('/^\d{9}[\dX]$|^\d{13}$/', $normalized) === 1;
    }

    /**
     * The form a lookup should compare against: the ISBN-13 conversion when the
     * input is valid, otherwise the merely normalized string — which matches
     * nothing, exactly as a bad ISBN should.
     */
    public static function canonical(string $raw): string
    {
        $isbn = self::tryFrom($raw);

        return $isbn instanceof self ? $isbn->value : self::normalize($raw);
    }

    public static function normalize(string $raw): string
    {
        return strtoupper(preg_replace('/[\s-]/', '', trim($raw)) ?? '');
    }

    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Weighted 10..1 modulo 11, where the check digit may be X for ten.
     */
    private static function isValidIsbn10(string $isbn): bool
    {
        if (preg_match('/^\d{9}[\dX]$/', $isbn) !== 1) {
            return false;
        }

        $sum = 0;
        foreach (str_split($isbn) as $index => $character) {
            $sum += ($character === 'X' ? 10 : (int) $character) * (10 - $index);
        }

        return $sum % 11 === 0;
    }

    /**
     * Alternating 1/3 weights modulo 10 (the EAN-13 checksum).
     */
    private static function isValidIsbn13(string $isbn): bool
    {
        if (preg_match('/^\d{13}$/', $isbn) !== 1) {
            return false;
        }

        $sum = 0;
        foreach (str_split($isbn) as $index => $digit) {
            $sum += (int) $digit * ($index % 2 === 0 ? 1 : 3);
        }

        return $sum % 10 === 0;
    }

    private static function toIsbn13(string $isbn10): string
    {
        $body = '978'.substr($isbn10, 0, 9);

        $sum = 0;
        foreach (str_split($body) as $index => $digit) {
            $sum += (int) $digit * ($index % 2 === 0 ? 1 : 3);
        }

        return $body.((10 - $sum % 10) % 10);
    }
}
