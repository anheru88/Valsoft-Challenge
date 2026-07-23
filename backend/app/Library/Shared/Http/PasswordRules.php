<?php

declare(strict_types=1);

namespace App\Library\Shared\Http;

use Illuminate\Validation\Rules\Password;

/**
 * FR-AUTH-5 in one place: minimum eight characters with at least one letter and
 * one number, confirmed. Registration, admin user creation and password changes
 * all read the same definition, so the policy cannot drift between them.
 */
final class PasswordRules
{
    /**
     * @return list<mixed>
     */
    public static function forNewPassword(bool $confirmed = true): array
    {
        $rules = ['required', 'string', Password::min(8)->letters()->numbers()];

        if ($confirmed) {
            $rules[] = 'confirmed';
        }

        return $rules;
    }
}
