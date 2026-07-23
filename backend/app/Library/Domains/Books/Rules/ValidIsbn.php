<?php

declare(strict_types=1);

namespace App\Library\Domains\Books\Rules;

use App\Library\Domains\Books\ValueObjects\Isbn;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Surfaces the value object's rule as a per-field 422, which is the shape the
 * API specification promises for a bad ISBN.
 */
final class ValidIsbn implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || Isbn::tryFrom($value) === null) {
            $fail('The :attribute must be a valid ISBN-10 or ISBN-13 with a correct checksum.');
        }
    }
}
