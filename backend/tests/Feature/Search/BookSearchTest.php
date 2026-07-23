<?php

declare(strict_types=1);

use App\Library\Domains\Authors\Models\Author;
use App\Library\Domains\Books\Models\Book;
use App\Library\Domains\Categories\Models\Category;
use App\Library\Domains\Users\Models\User;

it('finds a book by a word in its title', function () {
    $member = User::factory()->member()->create();
    Book::factory()->create(['title' => 'One Hundred Years of Solitude']);
    Book::factory()->create(['title' => 'The Dispossessed']);

    $this->withBearerToken(token($member))->getJson('/api/v1/search/books?q=Solitude')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'One Hundred Years of Solitude')
        ->assertJsonPath('data.0.matched_on', ['title']);
});

it('finds a book by its exact ISBN in either notation', function () {
    $member = User::factory()->member()->create();
    Book::factory()->create(['isbn' => '9780306406157', 'title' => 'The Target']);
    Book::factory()->count(2)->create();

    $this->withBearerToken(token($member))->getJson('/api/v1/search/books?q=9780306406157')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'The Target')
        ->assertJsonPath('data.0.matched_on', ['isbn']);

    // The ISBN-10 of the same book normalises to the stored value.
    $this->withBearerToken(token($member))->getJson('/api/v1/search/books?q=0-306-40615-2')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'The Target');
});

it('finds a book by author and by category name', function () {
    $member = User::factory()->member()->create();
    $author = Author::factory()->create(['name' => 'Ursula Le Guin']);
    $category = Category::factory()->create(['name' => 'Speculative', 'slug' => 'speculative']);
    $book = Book::factory()->create(['title' => 'The Dispossessed']);
    $book->authors()->attach($author);
    $book->categories()->attach($category);
    Book::factory()->create(['title' => 'Something Else']);

    $this->withBearerToken(token($member))->getJson('/api/v1/search/books?q=Ursula')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.matched_on', ['author']);

    $this->withBearerToken(token($member))->getJson('/api/v1/search/books?q=Speculative')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.matched_on', ['category']);
});

it('finds a book by description and publisher', function () {
    $member = User::factory()->member()->create();
    Book::factory()->create([
        'title' => 'Unrelated Title',
        'description' => 'A saga about the Buendia family.',
        'publisher' => 'Harper Collins',
    ]);
    Book::factory()->create(['title' => 'Other', 'description' => 'Nothing here.', 'publisher' => 'Penguin']);

    $this->withBearerToken(token($member))->getJson('/api/v1/search/books?q=Buendia')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.matched_on', ['description']);

    $this->withBearerToken(token($member))->getJson('/api/v1/search/books?q=Harper')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.matched_on', ['publisher']);
});

it('composes the search term with catalogue filters', function () {
    $member = User::factory()->member()->create();
    $category = Category::factory()->create();

    $inCategory = Book::factory()->create(['title' => 'Solitude in Winter']);
    $inCategory->categories()->attach($category);
    Book::factory()->create(['title' => 'Solitude in Summer']);

    $this->withBearerToken(token($member))->getJson("/api/v1/search/books?q=Solitude&category_id={$category->id}")
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.title', 'Solitude in Winter');
});

it('rejects a query too short to be useful', function () {
    $member = User::factory()->member()->create();

    $this->withBearerToken(token($member))->getJson('/api/v1/search/books?q=a')
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'SEARCH_QUERY_TOO_SHORT')
        ->assertJsonPath('error.details.min_length', 2);
});

it('allows filter-only browsing with no query at all', function () {
    $member = User::factory()->member()->create();
    Book::factory()->count(3)->create();

    $this->withBearerToken(token($member))->getJson('/api/v1/search/books?available=1')
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonMissingPath('data.0.matched_on');
});

it('paginates search results with the standard envelope', function () {
    $member = User::factory()->member()->create();
    Book::factory()->count(3)->create(['title' => 'Solitude volume']);

    $this->withBearerToken(token($member))->getJson('/api/v1/search/books?q=Solitude&per_page=2')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('meta.total', 3)
        ->assertJsonPath('meta.last_page', 2);
});
