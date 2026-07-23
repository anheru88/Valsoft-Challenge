<?php

declare(strict_types=1);

use App\Library\Domains\Authors\Models\Author;
use App\Library\Domains\Books\Models\Book;
use App\Library\Domains\Categories\Models\Category;
use App\Library\Domains\Users\Models\User;

it('lists authors with their book counts', function () {
    $member = User::factory()->member()->create();
    $author = Author::factory()->create(['name' => 'Gabriel García Márquez']);
    Book::factory()->create()->authors()->attach($author);
    Author::factory()->create(['name' => 'Zadie Smith']);

    $this->withBearerToken(token($member))->getJson('/api/v1/authors')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.books_count', 1)
        ->assertJsonStructure(['data' => [['id', 'name', 'bio', 'birth_year', 'books_count']], 'meta', 'links']);
});

it('searches authors by name prefix', function () {
    $member = User::factory()->member()->create();
    Author::factory()->create(['name' => 'Gabriel García Márquez']);
    Author::factory()->create(['name' => 'Zadie Smith']);

    $this->withBearerToken(token($member))->getJson('/api/v1/authors?q=Gab')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Gabriel García Márquez');
});

it('lets staff manage authors and keeps members out', function () {
    $librarian = User::factory()->librarian()->create();
    $member = User::factory()->member()->create();

    $created = $this->withBearerToken(token($librarian))->postJson('/api/v1/authors', [
        'name' => 'Ursula K. Le Guin',
        'bio' => 'Author of the Earthsea cycle.',
        'birth_year' => 1929,
    ])->assertCreated()->assertHeader('Location')->json('data.id');

    $this->withBearerToken(token($librarian))->putJson("/api/v1/authors/{$created}", ['name' => 'Ursula K. Le Guin (updated)'])
        ->assertOk()
        ->assertJsonPath('data.name', 'Ursula K. Le Guin (updated)');

    $this->withBearerToken(token($member))->postJson('/api/v1/authors', ['name' => 'Nope'])->assertForbidden();
    $this->withBearerToken(token($member))->deleteJson("/api/v1/authors/{$created}")->assertForbidden();
});

it('refuses to delete an author attached to books', function () {
    $librarian = User::factory()->librarian()->create();
    $author = Author::factory()->create();
    Book::factory()->create()->authors()->attach($author);

    $this->withBearerToken(token($librarian))->deleteJson("/api/v1/authors/{$author->id}")
        ->assertStatus(409)
        ->assertJsonPath('error.code', 'AUTHOR_IN_USE')
        ->assertJsonPath('error.details.books_count', 1);

    $this->assertDatabaseHas('authors', ['id' => $author->id]);
});

it('deletes an unreferenced author', function () {
    $librarian = User::factory()->librarian()->create();
    $author = Author::factory()->create();

    $this->withBearerToken(token($librarian))->deleteJson("/api/v1/authors/{$author->id}")->assertNoContent();

    $this->assertDatabaseMissing('authors', ['id' => $author->id]);
});

it('generates a slug when a category is created', function () {
    $librarian = User::factory()->librarian()->create();

    $this->withBearerToken(token($librarian))->postJson('/api/v1/categories', [
        'name' => 'Literary Fiction',
        'description' => 'Character-driven narratives.',
    ])->assertCreated()
        ->assertJsonPath('data.slug', 'literary-fiction')
        ->assertJsonPath('data.name', 'Literary Fiction');
});

it('rejects a duplicate category name', function () {
    $librarian = User::factory()->librarian()->create();
    Category::factory()->create(['name' => 'History', 'slug' => 'history']);

    $this->withBearerToken(token($librarian))->postJson('/api/v1/categories', ['name' => 'History'])
        ->assertStatus(422)
        ->assertJsonStructure(['error' => ['details' => ['errors' => ['name']]]]);
});

it('refuses to delete a category attached to books', function () {
    $librarian = User::factory()->librarian()->create();
    $category = Category::factory()->create();
    Book::factory()->create()->categories()->attach($category);

    $this->withBearerToken(token($librarian))->deleteJson("/api/v1/categories/{$category->id}")
        ->assertStatus(409)
        ->assertJsonPath('error.code', 'CATEGORY_IN_USE')
        ->assertJsonPath('error.details.books_count', 1);
});

it('sorts categories by how many books they hold', function () {
    $member = User::factory()->member()->create();
    $popular = Category::factory()->create(['name' => 'Popular']);
    $empty = Category::factory()->create(['name' => 'Empty']);
    Book::factory()->count(2)->create()->each(fn (Book $book) => $book->categories()->attach($popular));

    $this->withBearerToken(token($member))->getJson('/api/v1/categories?sort=books_count&direction=desc')
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Popular')
        ->assertJsonPath('data.0.books_count', 2)
        ->assertJsonPath('data.1.name', 'Empty');

    expect($empty->books()->count())->toBe(0);
});
