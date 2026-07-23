<?php

declare(strict_types=1);

use App\Library\Domains\Authors\Models\Author;
use App\Library\Domains\Books\Models\Book;
use App\Library\Domains\Categories\Models\Category;
use App\Library\Domains\Loans\Models\Loan;
use App\Library\Domains\Users\Models\User;
use Carbon\CarbonImmutable;

it('reports the catalogue and circulation KPIs', function () {
    $librarian = User::factory()->librarian()->create();
    $members = User::factory()->member()->count(3)->create();
    // Deactivated accounts are not part of the membership count.
    User::factory()->member()->inactive()->create();

    $borrowed = Book::factory()->withCopies(5, available: 3)->create();
    Book::factory()->withCopies(2, available: 2)->create();

    // Loans are attached to the members created above, so the counts below
    // describe exactly this fixture.
    Loan::factory()->count(2)->create(['user_id' => $members->first()->id, 'book_id' => $borrowed->id]);
    Loan::factory()->overdue()->create(['user_id' => $members->last()->id, 'book_id' => $borrowed->id]);
    Loan::factory()->returned()->create(['user_id' => $members->last()->id, 'book_id' => $borrowed->id]);

    $this->withBearerToken(token($librarian))->getJson('/api/v1/dashboard')
        ->assertOk()
        ->assertJsonPath('data.total_books', 2)
        ->assertJsonPath('data.total_copies', 7)
        ->assertJsonPath('data.total_members', 3)
        ->assertJsonPath('data.borrowed_now', 3)
        ->assertJsonPath('data.overdue_now', 1)
        ->assertJsonPath('data.returns_this_month', 1)
        ->assertJsonStructure(['data' => [
            'total_books', 'total_copies', 'available_copies', 'borrowed_now', 'overdue_now',
            'total_members', 'books_added_this_month', 'loans_this_month', 'returns_this_month',
        ]]);
});

it('keeps the dashboard and reports away from members', function () {
    $member = User::factory()->member()->create();

    foreach ([
        '/api/v1/dashboard',
        '/api/v1/dashboard/recent-activity',
        '/api/v1/dashboard/popular-authors',
        '/api/v1/dashboard/recent-books',
        '/api/v1/dashboard/books-by-category',
        '/api/v1/dashboard/monthly-stats',
        '/api/v1/reports/overdue',
        '/api/v1/reports/most-borrowed',
    ] as $endpoint) {
        $this->withBearerToken(token($member))->getJson($endpoint)->assertForbidden();
    }
});

it('reserves the reports for administrators', function () {
    $librarian = User::factory()->librarian()->create();
    $admin = User::factory()->admin()->create();

    $this->withBearerToken(token($librarian))->getJson('/api/v1/reports/overdue')->assertForbidden();
    $this->withBearerToken(token($admin))->getJson('/api/v1/reports/overdue')->assertOk();
});

it('lists recent activity newest first', function () {
    $librarian = User::factory()->librarian()->create();
    Loan::factory()->create();
    Loan::factory()->returned()->create();

    $response = $this->withBearerToken(token($librarian))->getJson('/api/v1/dashboard/recent-activity?limit=10')
        ->assertOk()
        ->assertJsonStructure(['data' => [['type', 'occurred_at', 'summary', 'book', 'user']]]);

    $types = array_column($response->json('data'), 'type');
    expect($types)->toContain('loan_created')->toContain('loan_returned');

    $timestamps = array_column($response->json('data'), 'occurred_at');
    $sorted = $timestamps;
    rsort($sorted);
    expect($timestamps)->toBe($sorted);
});

it('ranks authors by how often they were borrowed', function () {
    $librarian = User::factory()->librarian()->create();
    $popular = Author::factory()->create(['name' => 'Popular']);
    $quiet = Author::factory()->create(['name' => 'Quiet']);

    $borrowedBook = Book::factory()->withCopies(9)->create();
    $borrowedBook->authors()->attach($popular);
    Book::factory()->create()->authors()->attach($quiet);

    Loan::factory()->count(3)->create(['book_id' => $borrowedBook->id]);

    $this->withBearerToken(token($librarian))->getJson('/api/v1/dashboard/popular-authors?window=30d&limit=5')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.author.name', 'Popular')
        ->assertJsonPath('data.0.loans_count', 3);
});

it('lists the most recently added books', function () {
    $librarian = User::factory()->librarian()->create();
    Book::factory()->create(['title' => 'Older', 'created_at' => CarbonImmutable::now()->subWeek()]);
    Book::factory()->create(['title' => 'Newer']);

    $this->withBearerToken(token($librarian))->getJson('/api/v1/dashboard/recent-books?limit=1')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Newer');
});

it('counts books per category including empty ones', function () {
    $librarian = User::factory()->librarian()->create();
    $withBooks = Category::factory()->create(['name' => 'Busy']);
    Category::factory()->create(['name' => 'Empty']);
    Book::factory()->count(2)->create()->each(fn (Book $book) => $book->categories()->attach($withBooks));

    $response = $this->withBearerToken(token($librarian))->getJson('/api/v1/dashboard/books-by-category')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.category.name', 'Busy')
        ->assertJsonPath('data.0.books_count', 2);

    expect(collect($response->json('data'))->firstWhere('category.name', 'Empty')['books_count'])->toBe(0);
});

it('returns a chart-ready monthly series', function () {
    $librarian = User::factory()->librarian()->create();

    Loan::factory()->create(['loaned_at' => CarbonImmutable::today()]);
    Loan::factory()->create([
        'loaned_at' => CarbonImmutable::today()->subMonths(2),
        'due_date' => CarbonImmutable::today()->subMonths(2)->addDays(14),
        'returned_at' => CarbonImmutable::today()->subMonths(2)->addDays(3),
    ]);

    $response = $this->withBearerToken(token($librarian))->getJson('/api/v1/dashboard/monthly-stats?months=6')
        ->assertOk()
        ->assertJsonStructure(['data' => ['labels', 'loans', 'returns']]);

    $data = $response->json('data');

    expect($data['labels'])->toHaveCount(6)
        ->and($data['loans'])->toHaveCount(6)
        ->and($data['returns'])->toHaveCount(6)
        ->and(end($data['labels']))->toBe(CarbonImmutable::today()->format('Y-m'))
        ->and(array_sum($data['loans']))->toBe(2)
        ->and(array_sum($data['returns']))->toBe(1);
});

it('reports overdue loans most overdue first, with borrower contact details', function () {
    $admin = User::factory()->admin()->create();
    Loan::factory()->overdue(2)->create();
    $worst = Loan::factory()->overdue(20)->create();
    Loan::factory()->create();

    $this->withBearerToken(token($admin))->getJson('/api/v1/reports/overdue')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.id', $worst->id)
        ->assertJsonPath('data.0.days_overdue', 20)
        ->assertJsonPath('data.0.status', 'overdue')
        ->assertJsonStructure(['data' => [['user' => ['id', 'name', 'email']]]]);
});

it('ranks the most borrowed books within a date range', function () {
    $admin = User::factory()->admin()->create();
    $hit = Book::factory()->withCopies(9)->create(['title' => 'Hit']);
    $miss = Book::factory()->create(['title' => 'Miss']);

    Loan::factory()->count(3)->create(['book_id' => $hit->id, 'loaned_at' => CarbonImmutable::today()]);
    Loan::factory()->create(['book_id' => $miss->id, 'loaned_at' => CarbonImmutable::today()->subYear()]);

    $this->withBearerToken(token($admin))->getJson(sprintf(
        '/api/v1/reports/most-borrowed?from=%s&to=%s&limit=10',
        CarbonImmutable::today()->subMonth()->toDateString(),
        CarbonImmutable::today()->toDateString(),
    ))->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.book.title', 'Hit')
        ->assertJsonPath('data.0.loans_count', 3);
});
