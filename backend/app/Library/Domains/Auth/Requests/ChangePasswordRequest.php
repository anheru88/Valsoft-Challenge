<?php

declare(strict_types=1);

namespace App\Library\Domains\Auth\Requests;

use App\Library\Shared\Http\PasswordRules;
use Illuminate\Foundation\Http\FormRequest;

final class ChangePasswordRequest extends FormRequest
{
    /**
     * The current password is required here but verified in the action: an
     * incorrect one is a domain refusal (CURRENT_PASSWORD_INVALID), not a
     * malformed field.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string'],
            'password' => PasswordRules::forNewPassword(),
        ];
    }
}
