<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Library\Domains\Authors\Models\Author;
use App\Library\Domains\Books\Models\Book;
use App\Library\Domains\Categories\Models\Category;
use App\Library\Domains\Loans\Models\Loan;
use App\Library\Domains\Users\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * A realistic demo dataset: one account per role, a catalogue with authors and
 * categories, and circulation history including overdue items so dashboards and
 * reports have something to show.
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Alicia Admin',
            'email' => 'admin@librarium.test',
        ]);

        User::factory()->librarian()->create([
            'name' => 'Luis Librarian',
            'email' => 'librarian@librarium.test',
        ]);

        $members = User::factory()->member()->count(8)->create();
        User::factory()->member()->create([
            'name' => 'Marta Member',
            'email' => 'member@librarium.test',
        ]);

        $authors = Author::factory()->count(15)->create();
        $categories = Category::factory()->count(6)->create();

        $books = Book::factory()->count(40)->create();
        foreach ($books as $book) {
            $book->authors()->attach($authors->random(fake()->numberBetween(1, 2))->pluck('id'));
            $book->categories()->attach($categories->random(fake()->numberBetween(1, 2))->pluck('id'));
        }

        // Open loans: each one consumes a copy, mirroring what the check-out action does.
        $books->random(15)->each(function (Book $book) use ($members) {
            Loan::factory()->create([
                'user_id' => $members->random()->id,
                'book_id' => $book->id,
            ]);
            $book->decrement('available_copies');
        });

        $books->random(4)->each(function (Book $book) use ($members) {
            Loan::factory()->overdue()->create([
                'user_id' => $members->random()->id,
                'book_id' => $book->id,
            ]);
            $book->decrement('available_copies');
        });

        // Closed loans leave the counter untouched: borrowed then returned.
        Loan::factory()->returned()->count(25)->create([
            'user_id' => fn () => $members->random()->id,
            'book_id' => fn () => $books->random()->id,
        ]);
    }
}
