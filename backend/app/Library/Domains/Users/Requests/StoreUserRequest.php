<?php

declare(strict_types=1);

namespace App\Library\Domains\Users\Requests;

use App\Library\Domains\Users\Enums\UserRole;
use App\Library\Shared\Http\PasswordRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreUserRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => PasswordRules::forNewPassword(confirmed: false),
            'role' => ['required', Rule::enum(UserRole::class)],
        ];
    }

    public function role(): UserRole
    {
        return UserRole::from($this->string('role')->toString());
    }
}
