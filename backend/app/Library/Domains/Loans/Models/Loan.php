<?php

declare(strict_types=1);

namespace App\Library\Domains\Loans\Models;

use App\Library\Domains\Books\Models\Book;
use App\Library\Domains\Loans\Enums\LoanStatus;
use App\Library\Domains\Users\Models\User;
use Carbon\CarbonImmutable;
use Database\Factories\LoanFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $book_id
 * @property CarbonImmutable $loaned_at
 * @property CarbonImmutable $due_date
 * @property CarbonImmutable|null $returned_at
 */
class Loan extends Model
{
    /** @use HasFactory<LoanFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'book_id',
        'loaned_at',
        'due_date',
        'returned_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'loaned_at' => 'immutable_date',
            'due_date' => 'immutable_date',
            'returned_at' => 'immutable_datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Book, $this>
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * FR-LOAN-3: derived, never stored.
     */
    public function status(CarbonImmutable $now): LoanStatus
    {
        if ($this->returned_at !== null) {
            return LoanStatus::Returned;
        }

        return $this->due_date->isBefore($now->startOfDay())
            ? LoanStatus::Overdue
            : LoanStatus::Active;
    }

    public function daysOverdue(CarbonImmutable $now): int
    {
        if ($this->returned_at !== null || ! $this->due_date->isBefore($now->startOfDay())) {
            return 0;
        }

        return (int) $this->due_date->diffInDays($now->startOfDay());
    }

    public function isReturned(): bool
    {
        return $this->returned_at !== null;
    }

    /**
     * @param  Builder<Loan>  $query
     * @return Builder<Loan>
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNull('returned_at');
    }

    /**
     * @return Factory<Loan>
     */
    protected static function newFactory(): Factory
    {
        return LoanFactory::new();
    }
}
