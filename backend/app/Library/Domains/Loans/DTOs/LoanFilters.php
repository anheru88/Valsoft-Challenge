<?php

declare(strict_types=1);

namespace App\Library\Domains\Loans\DTOs;

use App\Library\Domains\Loans\Enums\LoanSortField;
use App\Library\Domains\Loans\Enums\LoanStatus;
use App\Library\Shared\Application\PaginationParams;
use App\Library\Shared\Application\SortParams;
use Illuminate\Http\Request;

final readonly class LoanFilters
{
    public function __construct(
        public ?LoanStatus $status,
        public ?int $userId,
        public ?int $bookId,
        public ?bool $overdue,
        public ?string $loanedFrom,
        public ?string $loanedTo,
        public PaginationParams $pagination,
        public SortParams $sort,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $status = $request->string('status')->toString();

        return new self(
            status: $status !== '' ? LoanStatus::from($status) : null,
            userId: $request->filled('user_id') ? $request->integer('user_id') : null,
            bookId: $request->filled('book_id') ? $request->integer('book_id') : null,
            overdue: $request->has('overdue') ? $request->boolean('overdue') : null,
            loanedFrom: $request->filled('loaned_from') ? $request->string('loaned_from')->toString() : null,
            loanedTo: $request->filled('loaned_to') ? $request->string('loaned_to')->toString() : null,
            pagination: PaginationParams::fromRequest($request),
            sort: SortParams::fromRequest($request, LoanSortField::values(), LoanSortField::LoanedAt->value),
        );
    }

    /**
     * FR-LOAN-5: a member sees their own loans whatever they ask for, so the
     * scoping is applied by the controller and cannot be filtered away.
     */
    public function scopedToUser(int $userId): self
    {
        return new self(
            status: $this->status,
            userId: $userId,
            bookId: $this->bookId,
            overdue: $this->overdue,
            loanedFrom: $this->loanedFrom,
            loanedTo: $this->loanedTo,
            pagination: $this->pagination,
            sort: $this->sort,
        );
    }
}
