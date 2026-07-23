<?php

declare(strict_types=1);

use App\Library\Domains\Authors\Models\Author;
use App\Library\Domains\Books\Models\Book;
use App\Library\Domains\Categories\Models\Category;
use App\Library\Domains\Loans\Models\Loan;
use App\Library\Domains\Users\Models\User;

function bookPayload(array $overrides = []): array
{
    return array_merge([
        'title' => 'One Hundred Years of Solitude',
        'isbn' => '978-0-06-088328-7',
        'description' => 'A multi-generational story.',
        'publisher' => 'Harper',
        'publication_year' => 1967,
        'total_copies' => 3,
        'author_ids' => [Author::factory()->create()->id],
        'category_ids' => [Category::factory()->create()->id],
    ], $overrides);
}

it('lets anybody browse the catalogue', function (string $role) {
    $actor = User::factory()->{$role}()->create();
    Book::factory()->count(3)->create();

    $this->withBearerToken(token($actor))->getJson('/api/v1/books')
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [['id', 'title', 'isbn', 'total_copies', 'available_copies', 'is_available', 'authors', 'categories']],
            'meta' => ['current_page', 'per_page', 'total', 'last_page'],
        ]);
})->with(['admin', 'librarian', 'member']);

it('shows circulation counts to staff only', function () {
    $book = Book::factory()->create();

    $staffResponse = $this->withBearerToken(token(User::factory()->librarian()->create()))
        ->getJson("/api/v1/books/{$book->id}");
    $memberResponse = $this->withBearerToken(token(User::factory()->member()->create()))
        ->getJson("/api/v1/books/{$book->id}");

    $staffResponse->assertOk()->assertJsonStructure(['data' => ['active_loans_count']]);
    $memberResponse->assertOk()->assertJsonMissingPath('data.active_loans_count');
});

it('filters, sorts and paginates the catalogue', function () {
    $member = User::factory()->member()->create();
    $category = Category::factory()->create();
    $author = Author::factory()->create();

    $matching = Book::factory()->create(['title' => 'Alpha', 'publication_year' => 1990]);
    $matching->categories()->attach($category);
    $matching->authors()->attach($author);

    Book::factory()->unavailable()->create(['title' => 'Zulu', 'publication_year' => 2010]);

    $this->withBearerToken(token($member))->getJson("/api/v1/books?category_id={$category->id}")
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.title', 'Alpha');

    $this->withBearerToken(token($member))->getJson("/api/v1/books?author_id={$author->id}")
        ->assertOk()->assertJsonCount(1, 'data');

    $this->withBearerToken(token($member))->getJson('/api/v1/books?available=1')
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.title', 'Alpha');

    $this->withBearerToken(token($member))->getJson('/api/v1/books?year_from=2000')
        ->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.title', 'Zulu');

    $this->withBearerToken(token($member))->getJson('/api/v1/books?sort=title&direction=desc')
        ->assertOk()->assertJsonPath('data.0.title', 'Zulu');

    $this->withBearerToken(token($member))->getJson('/api/v1/books?per_page=1')
        ->assertOk()->assertJsonPath('meta.per_page', 1)->assertJsonPath('meta.last_page', 2);
});

it('lets staff create a book and normalises its ISBN', function () {
    $librarian = User::factory()->librarian()->create();

    $this->withBearerToken(token($librarian))->postJson('/api/v1/books', bookPayload())
        ->assertCreated()
        ->assertJsonPath('data.isbn', '9780060883287')
        ->assertJsonPath('data.available_copies', 3)
        ->assertHeader('Location');
});

it('keeps members out of catalogue writes', function () {
    $member = User::factory()->member()->create();
    $book = Book::factory()->create();

    $this->withBearerToken(token($member))->postJson('/api/v1/books', bookPayload())->assertForbidden();
    $this->withBearerToken(token($member))->putJson("/api/v1/books/{$book->id}", bookPayload())->assertForbidden();
    $this->withBearerToken(token($member))->deleteJson("/api/v1/books/{$book->id}")->assertForbidden();
});

it('rejects an invalid ISBN checksum', function () {
    $librarian = User::factory()->librarian()->create();

    $this->withBearerToken(token($librarian))->postJson('/api/v1/books', bookPayload(['isbn' => '9780060883288']))
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'VALIDATION_FAILED')
        ->assertJsonStructure(['error' => ['details' => ['errors' => ['isbn']]]]);
});

it('treats the same book in ISBN-10 and ISBN-13 notation as a duplicate', function () {
    $librarian = User::factory()->librarian()->create();

    $this->withBearerToken(token($librarian))->postJson('/api/v1/books', bookPayload(['isbn' => '0-306-40615-2']))
        ->assertCreated();

    $this->withBearerToken(token($librarian))->postJson('/api/v1/books', bookPayload(['isbn' => '9780306406157']))
        ->assertStatus(422)
        ->assertJsonStructure(['error' => ['details' => ['errors' => ['isbn']]]]);
});

it('demands at least one author and one category', function () {
    $librarian = User::factory()->librarian()->create();

    $this->withBearerToken(token($librarian))
        ->postJson('/api/v1/books', bookPayload(['author_ids' => [], 'category_ids' => []]))
        ->assertStatus(422)
        ->assertJsonStructure(['error' => ['details' => ['errors' => ['author_ids', 'category_ids']]]]);
});

it('ignores an attempt to set available_copies directly', function () {
    $librarian = User::factory()->librarian()->create();

    $this->withBearerToken(token($librarian))
        ->postJson('/api/v1/books', bookPayload(['total_copies' => 2, 'available_copies' => 99]))
        ->assertCreated()
        ->assertJsonPath('data.available_copies', 2);
});

it('moves the counter by the same delta when the stock is corrected', function () {
    $librarian = User::factory()->librarian()->create();
    $book = Book::factory()->withCopies(5)->create();
    Loan::factory()->count(2)->create(['book_id' => $book->id]);
    // Direct assignment: the counter is not mass assignable (BR-BOOK-2), so
    // this mirrors what the loan lifecycle does to it.
    $book->available_copies = 3;
    $book->save();

    $this->withBearerToken(token($librarian))
        ->putJson("/api/v1/books/{$book->id}", bookPayload(['isbn' => $book->isbn, 'total_copies' => 6]))
        ->assertOk()
        ->assertJsonPath('data.total_copies', 6)
        ->assertJsonPath('data.available_copies', 4);
});

it('refuses to cut the stock below the copies on loan', function () {
    $librarian = User::factory()->librarian()->create();
    $book = Book::factory()->withCopies(5)->create();
    Loan::factory()->count(3)->create(['book_id' => $book->id]);
    $book->available_copies = 2;
    $book->save();

    $this->withBearerToken(token($librarian))
        ->putJson("/api/v1/books/{$book->id}", bookPayload(['isbn' => $book->isbn, 'total_copies' => 2]))
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'BOOK_COPIES_BELOW_LOANED')
        ->assertJsonPath('error.details.loaned_copies', 3);
});

it('refuses to delete a book with open loans', function () {
    $librarian = User::factory()->librarian()->create();
    $book = Book::factory()->create();
    Loan::factory()->create(['book_id' => $book->id]);

    $this->withBearerToken(token($librarian))->deleteJson("/api/v1/books/{$book->id}")
        ->assertStatus(409)
        ->assertJsonPath('error.code', 'BOOK_HAS_ACTIVE_LOANS')
        ->assertJsonPath('error.details.active_loans', 1);
});

it('soft deletes a book whose loans are all closed', function () {
    $librarian = User::factory()->librarian()->create();
    $book = Book::factory()->create();
    Loan::factory()->returned()->create(['book_id' => $book->id]);

    $this->withBearerToken(token($librarian))->deleteJson("/api/v1/books/{$book->id}")->assertNoContent();

    $this->assertSoftDeleted('books', ['id' => $book->id]);
    $this->assertDatabaseHas('loans', ['book_id' => $book->id]);

    $this->withBearerToken(token($librarian))->getJson("/api/v1/books/{$book->id}")->assertNotFound();
});

it('answers 304 when the client already holds the current book', function () {
    $member = User::factory()->member()->create();
    $book = Book::factory()->create();

    $first = $this->withBearerToken(token($member))->getJson("/api/v1/books/{$book->id}")->assertOk();
    $etag = $first->headers->get('ETag');

    expect($etag)->not->toBeNull();

    $this->withBearerToken(token($member))
        ->getJson("/api/v1/books/{$book->id}", ['If-None-Match' => $etag])
        ->assertStatus(304);
});
