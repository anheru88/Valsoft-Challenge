<?php

declare(strict_types=1);

namespace App\Library\Domains\Books\Requests;

use App\Library\Domains\Books\Enums\BookSortField;
use App\Library\Shared\Application\SortParams;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class IndexBookRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'q' => ['sometimes', 'string', 'max:255'],
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'author_id' => ['sometimes', 'integer', 'exists:authors,id'],
            'available' => ['sometimes', 'boolean'],
            'year_from' => ['sometimes', 'integer'],
            'year_to' => ['sometimes', 'integer', 'gte:year_from'],
            'sort' => ['sometimes', Rule::in(BookSortField::values())],
            'direction' => ['sometimes', Rule::in(SortParams::DIRECTIONS)],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:'.config('library.pagination.max_per_page')],
        ];
    }
}
