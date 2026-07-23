<?php

declare(strict_types=1);

namespace App\Library\Domains\Auth\Requests;

use App\Library\Shared\Http\PasswordRules;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Syntactic validation only (FR-VAL-1). Note the absence of `role`: it is not
 * accepted here at all, so public registration cannot mint privileges
 * (FR-AUTH-2).
 */
final class RegisterRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => PasswordRules::forNewPassword(),
        ];
    }
}
