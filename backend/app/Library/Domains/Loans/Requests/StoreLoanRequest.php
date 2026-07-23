<?php

declare(strict_types=1);

namespace App\Library\Domains\Loans\Requests;

use App\Library\Domains\Loans\ValueObjects\DueDate;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Syntax only. Whether the member may borrow — limit, overdue items, duplicate
 * title, availability — is business state and answered with 409 by the action.
 */
final class StoreLoanRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'due_date' => [
                'sometimes',
                'date_format:Y-m-d',
                'after:today',
                'before_or_equal:'.now()->addDays(DueDate::maxPeriodDays())->toDateString(),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'due_date.after' => 'The due date must be at least one day from today.',
            'due_date.before_or_equal' => 'The due date cannot exceed '.DueDate::maxPeriodDays().' days from today.',
        ];
    }
}
