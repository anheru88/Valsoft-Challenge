<?php

declare(strict_types=1);

namespace App\Library\Domains\Loans\Resources;

use App\Library\Domains\Books\Models\Book;
use App\Library\Domains\Loans\Models\Loan;
use App\Library\Domains\Users\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;

/**
 * @mixin Loan
 */
final class LoanResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $loan = $this->resource;
        $now = CarbonImmutable::now();

        $actor = $request->user();
        $isStaff = $actor instanceof User && $actor->isStaff();

        $borrower = $loan->relationLoaded('user') ? $loan->user : null;
        $book = $loan->relationLoaded('book') ? $loan->book : null;

        return [
            'id' => $loan->id,
            // Derived on read, so it is never stale (FR-LOAN-3).
            'status' => $loan->status($now)->value,
            'loaned_at' => $loan->loaned_at->toDateString(),
            'due_date' => $loan->due_date->toDateString(),
            'returned_at' => $loan->returned_at?->toIso8601ZuluString(),
            'days_overdue' => $loan->daysOverdue($now),
            'user' => $borrower instanceof User
                ? [
                    'id' => $borrower->id,
                    'name' => $borrower->name,
                    // Contact details are desk information (API specification 6).
                    ...$isStaff ? ['email' => $borrower->email] : [],
                ]
                : new MissingValue,
            'book' => $book instanceof Book
                ? [
                    'id' => $book->id,
                    'title' => $book->title,
                    'isbn' => $book->isbn,
                ]
                : new MissingValue,
            'created_at' => $loan->created_at?->toIso8601ZuluString(),
            'updated_at' => $loan->updated_at?->toIso8601ZuluString(),
        ];
    }
}
