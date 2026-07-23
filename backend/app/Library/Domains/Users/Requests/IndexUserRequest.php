<?php

declare(strict_types=1);

namespace App\Library\Domains\Users\Requests;

use App\Library\Domains\Users\Enums\UserRole;
use App\Library\Domains\Users\Enums\UserSortField;
use App\Library\Shared\Application\SortParams;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * FR-LIST-2: an unknown sort field is a 422 rather than a silent fallback, so
 * a client with a typo learns about it.
 */
final class IndexUserRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'q' => ['sometimes', 'string', 'max:255'],
            'role' => ['sometimes', Rule::enum(UserRole::class)],
            'is_active' => ['sometimes', 'boolean'],
            'sort' => ['sometimes', Rule::in(UserSortField::values())],
            'direction' => ['sometimes', Rule::in(SortParams::DIRECTIONS)],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:'.config('library.pagination.max_per_page')],
        ];
    }
}
