<?php

declare(strict_types=1);

namespace App\Library\Domains\Users\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ChangeUserStatusRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'is_active' => ['required', 'boolean'],
        ];
    }
}
