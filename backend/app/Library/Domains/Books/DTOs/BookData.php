<?php

declare(strict_types=1);

namespace App\Library\Domains\Books\DTOs;

use App\Library\Domains\Books\ValueObjects\Isbn;
use Illuminate\Http\Request;

/**
 * The writable shape of a book. `available_copies` is absent on purpose: it is
 * system-managed and the API refuses to take it from a client (BR-BOOK-2).
 */
final readonly class BookData
{
    /**
     * @param  list<int>  $authorIds
     * @param  list<int>  $categoryIds
     */
    public function __construct(
        public string $title,
        public Isbn $isbn,
        public ?string $description,
        public ?string $publisher,
        public ?int $publicationYear,
        public ?string $coverUrl,
        public int $totalCopies,
        public array $authorIds,
        public array $categoryIds,
    ) {}

    public static function fromRequest(Request $request): self
    {
        /** @var list<int> $authorIds */
        $authorIds = array_map(intval(...), $request->array('author_ids'));
        /** @var list<int> $categoryIds */
        $categoryIds = array_map(intval(...), $request->array('category_ids'));

        return new self(
            title: trim($request->string('title')->toString()),
            isbn: Isbn::fromString($request->string('isbn')->toString()),
            description: $request->filled('description') ? $request->string('description')->toString() : null,
            publisher: $request->filled('publisher') ? $request->string('publisher')->toString() : null,
            publicationYear: $request->filled('publication_year') ? $request->integer('publication_year') : null,
            coverUrl: $request->filled('cover_url') ? $request->string('cover_url')->toString() : null,
            totalCopies: $request->integer('total_copies'),
            authorIds: array_values(array_unique($authorIds)),
            categoryIds: array_values(array_unique($categoryIds)),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'title' => $this->title,
            'isbn' => $this->isbn->value,
            'description' => $this->description,
            'publisher' => $this->publisher,
            'publication_year' => $this->publicationYear,
            'cover_url' => $this->coverUrl,
            'total_copies' => $this->totalCopies,
        ];
    }
}
