<?php

declare(strict_types=1);

use App\Library\Domains\Books\Models\Book;
use App\Library\Domains\Loans\Models\Loan;
use App\Library\Domains\Users\Models\User;
use Carbon\CarbonImmutable;

it('checks a book out, decrements the counter and defaults the due date', function () {
    $librarian = User::factory()->librarian()->create();
    $member = User::factory()->member()->create();
    $book = Book::factory()->withCopies(2)->create();

    $this->withBearerToken(token($librarian))->postJson('/api/v1/loans', [
        'user_id' => $member->id,
        'book_id' => $book->id,
    ])->assertCreated()
        ->assertJsonPath('data.status', 'active')
        ->assertJsonPath('data.due_date', CarbonImmutable::today()->addDays(14)->toDateString())
        ->assertJsonPath('data.days_overdue', 0)
        ->assertHeader('Location');

    expect($book->fresh()->available_copies)->toBe(1);
});

it('accepts a due date inside the allowed window and rejects one outside it', function () {
    $librarian = User::factory()->librarian()->create();
    $member = User::factory()->member()->create();
    $book = Book::factory()->create();

    $this->withBearerToken(token($librarian))->postJson('/api/v1/loans', [
        'user_id' => $member->id,
        'book_id' => $book->id,
        'due_date' => CarbonImmutable::today()->addDays(30)->toDateString(),
    ])->assertCreated()->assertJsonPath('data.due_date', CarbonImmutable::today()->addDays(30)->toDateString());

    $this->withBearerToken(token($librarian))->postJson('/api/v1/loans', [
        'user_id' => $member->id,
        'book_id' => Book::factory()->create()->id,
        'due_date' => CarbonImmutable::today()->addDays(90)->toDateString(),
    ])->assertStatus(422)->assertJsonStructure(['error' => ['details' => ['errors' => ['due_date']]]]);

    $this->withBearerToken(token($librarian))->postJson('/api/v1/loans', [
        'user_id' => $member->id,
        'book_id' => Book::factory()->create()->id,
        'due_date' => CarbonImmutable::yesterday()->toDateString(),
    ])->assertStatus(422);
});

it('refuses to lend a book with no copies left', function () {
    $librarian = User::factory()->librarian()->create();
    $member = User::factory()->member()->create();
    $book = Book::factory()->withCopies(1, available: 0)->create();

    $this->withBearerToken(token($librarian))->postJson('/api/v1/loans', [
        'user_id' => $member->id,
        'book_id' => $book->id,
    ])->assertStatus(409)->assertJsonPath('error.code', 'LOAN_NO_COPIES');
});

it('stops a member at the configured active loan limit', function () {
    $librarian = User::factory()->librarian()->create();
    $member = User::factory()->member()->create();
    Loan::factory()->count(5)->create(['user_id' => $member->id]);

    $this->withBearerToken(token($librarian))->postJson('/api/v1/loans', [
        'user_id' => $member->id,
        'book_id' => Book::factory()->create()->id,
    ])->assertStatus(409)
        ->assertJsonPath('error.code', 'LOAN_LIMIT_REACHED')
        ->assertJsonPath('error.details.limit', 5)
        ->assertJsonPath('error.details.active_loans', 5);
});

it('blocks a member who is holding overdue items', function () {
    $librarian = User::factory()->librarian()->create();
    $member = User::factory()->member()->create();
    Loan::factory()->overdue(3)->create(['user_id' => $member->id]);

    $this->withBearerToken(token($librarian))->postJson('/api/v1/loans', [
        'user_id' => $member->id,
        'book_id' => Book::factory()->create()->id,
    ])->assertStatus(409)
        ->assertJsonPath('error.code', 'LOAN_MEMBER_OVERDUE')
        ->assertJsonPath('error.details.overdue_loans', 1);
});

it('refuses a second active loan of the same title', function () {
    $librarian = User::factory()->librarian()->create();
    $member = User::factory()->member()->create();
    $book = Book::factory()->withCopies(4)->create();
    Loan::factory()->create(['user_id' => $member->id, 'book_id' => $book->id]);

    $this->withBearerToken(token($librarian))->postJson('/api/v1/loans', [
        'user_id' => $member->id,
        'book_id' => $book->id,
    ])->assertStatus(409)->assertJsonPath('error.code', 'LOAN_DUPLICATE_TITLE');
});

it('lends only to active member accounts', function () {
    $librarian = User::factory()->librarian()->create();
    $book = Book::factory()->create();

    $this->withBearerToken(token($librarian))->postJson('/api/v1/loans', [
        'user_id' => User::factory()->librarian()->create()->id,
        'book_id' => $book->id,
    ])->assertStatus(409)->assertJsonPath('error.code', 'LOAN_USER_NOT_MEMBER');

    $this->withBearerToken(token($librarian))->postJson('/api/v1/loans', [
        'user_id' => User::factory()->member()->inactive()->create()->id,
        'book_id' => $book->id,
    ])->assertStatus(409)->assertJsonPath('error.code', 'LOAN_USER_INACTIVE');
});

it('keeps members from checking books out themselves', function () {
    $member = User::factory()->member()->create();

    $this->withBearerToken(token($member))->postJson('/api/v1/loans', [
        'user_id' => $member->id,
        'book_id' => Book::factory()->create()->id,
    ])->assertForbidden();
});

it('checks a book back in and returns the copy to the shelf', function () {
    $librarian = User::factory()->librarian()->create();
    $book = Book::factory()->withCopies(3, available: 2)->create();
    $loan = Loan::factory()->create(['book_id' => $book->id]);

    $this->withBearerToken(token($librarian))->postJson("/api/v1/loans/{$loan->id}/return")
        ->assertOk()
        ->assertJsonPath('data.status', 'returned')
        ->assertJsonPath('data.days_overdue', 0);

    expect($book->fresh()->available_copies)->toBe(3)
        ->and($loan->fresh()->returned_at)->not->toBeNull();
});

it('refuses a second check-in of the same loan', function () {
    $librarian = User::factory()->librarian()->create();
    $book = Book::factory()->withCopies(3, available: 2)->create();
    $loan = Loan::factory()->create(['book_id' => $book->id]);

    $this->withBearerToken(token($librarian))->postJson("/api/v1/loans/{$loan->id}/return")->assertOk();

    $this->withBearerToken(token($librarian))->postJson("/api/v1/loans/{$loan->id}/return")
        ->assertStatus(409)
        ->assertJsonPath('error.code', 'LOAN_ALREADY_RETURNED');

    // The counter moved once, not twice.
    expect($book->fresh()->available_copies)->toBe(3);
});

it('derives overdue status and the days elapsed', function () {
    $librarian = User::factory()->librarian()->create();
    $loan = Loan::factory()->overdue(4)->create();

    $this->withBearerToken(token($librarian))->getJson("/api/v1/loans/{$loan->id}")
        ->assertOk()
        ->assertJsonPath('data.status', 'overdue')
        ->assertJsonPath('data.days_overdue', 4);
});

it('filters the loan list by status, overdue flag and book', function () {
    $librarian = User::factory()->librarian()->create();
    $book = Book::factory()->withCopies(9)->create();
    Loan::factory()->create(['book_id' => $book->id]);
    Loan::factory()->overdue()->create(['book_id' => $book->id]);
    Loan::factory()->returned()->create();

    $this->withBearerToken(token($librarian))->getJson('/api/v1/loans?status=active')
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.status', 'active');

    $this->withBearerToken(token($librarian))->getJson('/api/v1/loans?status=overdue')
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.status', 'overdue');

    $this->withBearerToken(token($librarian))->getJson('/api/v1/loans?status=returned')
        ->assertOk()->assertJsonCount(1, 'data');

    $this->withBearerToken(token($librarian))->getJson('/api/v1/loans?overdue=1')
        ->assertOk()->assertJsonCount(1, 'data');

    $this->withBearerToken(token($librarian))->getJson("/api/v1/loans?book_id={$book->id}")
        ->assertOk()->assertJsonCount(2, 'data');
});

it('scopes a member to their own loans whatever they ask for', function () {
    $member = User::factory()->member()->create();
    $other = User::factory()->member()->create();
    Loan::factory()->create(['user_id' => $member->id]);
    Loan::factory()->count(2)->create(['user_id' => $other->id]);

    $this->withBearerToken(token($member))->getJson("/api/v1/loans?user_id={$other->id}")
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.user.id', $member->id);
});

it('answers 404 rather than 403 for somebody else\'s loan', function () {
    $member = User::factory()->member()->create();
    $foreignLoan = Loan::factory()->create();

    $this->withBearerToken(token($member))->getJson("/api/v1/loans/{$foreignLoan->id}")
        ->assertNotFound()
        ->assertJsonPath('error.code', 'NOT_FOUND');
});

it('hides borrower contact details from members', function () {
    $member = User::factory()->member()->create();
    $loan = Loan::factory()->create(['user_id' => $member->id]);

    $memberView = $this->withBearerToken(token($member))->getJson("/api/v1/loans/{$loan->id}")->assertOk();
    $staffView = $this->withBearerToken(token(User::factory()->librarian()->create()))
        ->getJson("/api/v1/loans/{$loan->id}")->assertOk();

    $memberView->assertJsonMissingPath('data.user.email');
    $staffView->assertJsonPath('data.user.email', $member->email);
});

it('serves a member loan history to the desk', function () {
    $librarian = User::factory()->librarian()->create();
    $member = User::factory()->member()->create();
    Loan::factory()->count(2)->create(['user_id' => $member->id]);
    Loan::factory()->create();

    $this->withBearerToken(token($librarian))->getJson("/api/v1/users/{$member->id}/loans")
        ->assertOk()
        ->assertJsonCount(2, 'data');

    $this->withBearerToken(token(User::factory()->member()->create()))
        ->getJson("/api/v1/users/{$member->id}/loans")
        ->assertForbidden();
});
