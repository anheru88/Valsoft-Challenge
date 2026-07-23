<?php

declare(strict_types=1);

namespace App\Library\Domains\Authors\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * FR-AUTHOR-1: name uniqueness is deliberately not enforced — homonymous
 * authors exist and rejecting them would be wrong.
 */
final class StoreAuthorRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'birth_year' => ['nullable', 'integer', 'min:0', 'max:'.date('Y')],
        ];
    }
}
