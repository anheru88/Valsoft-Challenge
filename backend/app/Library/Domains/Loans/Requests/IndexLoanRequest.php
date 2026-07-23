<?php

declare(strict_types=1);

namespace App\Library\Domains\Loans\Requests;

use App\Library\Domains\Loans\Enums\LoanSortField;
use App\Library\Domains\Loans\Enums\LoanStatus;
use App\Library\Shared\Application\SortParams;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class IndexLoanRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['sometimes', Rule::enum(LoanStatus::class)],
            'user_id' => ['sometimes', 'integer'],
            'book_id' => ['sometimes', 'integer'],
            'overdue' => ['sometimes', 'boolean'],
            'loaned_from' => ['sometimes', 'date_format:Y-m-d'],
            'loaned_to' => ['sometimes', 'date_format:Y-m-d', 'after_or_equal:loaned_from'],
            'sort' => ['sometimes', Rule::in(LoanSortField::values())],
            'direction' => ['sometimes', Rule::in(SortParams::DIRECTIONS)],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:'.config('library.pagination.max_per_page')],
        ];
    }
}
