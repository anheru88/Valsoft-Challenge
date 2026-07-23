<?php

declare(strict_types=1);

namespace App\Library\Domains\Loans\DTOs;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

final readonly class CheckoutData
{
    public function __construct(
        public int $userId,
        public int $bookId,
        public ?CarbonImmutable $requestedDueDate = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            userId: $request->integer('user_id'),
            bookId: $request->integer('book_id'),
            requestedDueDate: $request->filled('due_date')
                ? CarbonImmutable::parse($request->string('due_date')->toString())->startOfDay()
                : null,
        );
    }
}
