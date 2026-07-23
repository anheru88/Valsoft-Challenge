<?php

declare(strict_types=1);

namespace App\Library\Domains\Books\Models;

use App\Library\Domains\Authors\Models\Author;
use App\Library\Domains\Categories\Models\Category;
use App\Library\Domains\Loans\Models\Loan;
use Database\Factories\BookFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $title
 * @property string $isbn
 * @property string|null $description
 * @property string|null $publisher
 * @property int|null $publication_year
 * @property string|null $cover_url
 * @property int $total_copies
 * @property int $available_copies
 * @property-read int|null $active_loans_count aliased withCount, present on repository queries
 */
class Book extends Model
{
    /** @use HasFactory<BookFactory> */
    use HasFactory, SoftDeletes;

    /**
     * `available_copies` is absent by design: it is system-managed and adjusted
     * only by the loan lifecycle inside a transaction (BR-BOOK-2).
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'isbn',
        'description',
        'publisher',
        'publication_year',
        'cover_url',
        'total_copies',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'publication_year' => 'integer',
            'total_copies' => 'integer',
            'available_copies' => 'integer',
        ];
    }

    /**
     * @return BelongsToMany<Author, $this>
     */
    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class);
    }

    /**
     * @return BelongsToMany<Category, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * @return HasMany<Loan, $this>
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function isAvailable(): bool
    {
        return $this->available_copies > 0;
    }

    /**
     * Copies currently in members' hands — the floor for BR-BOOK-3.
     */
    public function loanedCopies(): int
    {
        return $this->total_copies - $this->available_copies;
    }

    /**
     * @return Factory<Book>
     */
    protected static function newFactory(): Factory
    {
        return BookFactory::new();
    }
}
