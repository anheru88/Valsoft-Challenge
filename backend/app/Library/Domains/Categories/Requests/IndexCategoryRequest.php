<?php

declare(strict_types=1);

namespace App\Library\Domains\Categories\Requests;

use App\Library\Domains\Categories\Enums\CategorySortField;
use App\Library\Shared\Application\SortParams;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class IndexCategoryRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'q' => ['sometimes', 'string', 'max:255'],
            'sort' => ['sometimes', Rule::in(CategorySortField::values())],
            'direction' => ['sometimes', Rule::in(SortParams::DIRECTIONS)],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:'.config('library.pagination.max_per_page')],
        ];
    }
}
