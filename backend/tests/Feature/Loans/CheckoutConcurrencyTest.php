<?php

declare(strict_types=1);

use App\Library\Domains\Books\Models\Book;
use App\Library\Domains\Loans\Actions\CheckoutBookAction;
use App\Library\Domains\Loans\DTOs\CheckoutData;
use App\Library\Domains\Loans\Exceptions\NoCopiesAvailableException;
use App\Library\Domains\Users\Models\User;
use Illuminate\Support\Facades\DB;

it('lets exactly one of two check-outs take the last copy', function () {
    $book = Book::factory()->withCopies(1)->create();
    $first = User::factory()->member()->create();
    $second = User::factory()->member()->create();

    $checkout = app(CheckoutBookAction::class);

    $checkout(new CheckoutData($first->id, $book->id));

    expect(fn () => $checkout(new CheckoutData($second->id, $book->id)))
        ->toThrow(NoCopiesAvailableException::class);

    expect($book->fresh()->available_copies)->toBe(0)
        ->and($book->loans()->whereNull('returned_at')->count())->toBe(1);
});

it('leaves nothing behind when a rule rejects the check-out', function () {
    $book = Book::factory()->withCopies(1, available: 0)->create();
    $member = User::factory()->member()->create();

    try {
        app(CheckoutBookAction::class)(new CheckoutData($member->id, $book->id));
    } catch (NoCopiesAvailableException) {
        // Expected: BR-LOAN-7 says the transaction leaves no partial state.
    }

    expect($book->fresh()->available_copies)->toBe(0)
        ->and($book->loans()->count())->toBe(0);
});

it('reads the book row under a pessimistic lock while checking out', function () {
    $book = Book::factory()->withCopies(2)->create();
    $member = User::factory()->member()->create();

    $statements = [];
    DB::listen(function ($query) use (&$statements): void {
        $statements[] = strtolower($query->sql);
    });

    app(CheckoutBookAction::class)(new CheckoutData($member->id, $book->id));

    $lockingReads = array_filter(
        $statements,
        fn (string $sql): bool => str_contains($sql, 'from "books"') && str_contains($sql, 'for update'),
    );

    expect($lockingReads)->not->toBeEmpty();
})->skip(
    fn (): bool => ! in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true),
    'SELECT ... FOR UPDATE is a no-op on SQLite; this guard is verified against MariaDB.',
);
