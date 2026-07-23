<?php

declare(strict_types=1);

namespace App\Library\Domains\Books\Repositories;

use App\Library\Domains\Books\Contracts\BookRepositoryInterface;
use App\Library\Domains\Books\DTOs\BookData;
use App\Library\Domains\Books\DTOs\BookFilters;
use App\Library\Domains\Books\Models\Book;
use App\Library\Shared\Infrastructure\EloquentRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends EloquentRepository<Book>
 */
final class EloquentBookRepository extends EloquentRepository implements BookRepositoryInterface
{
    /**
     * @return LengthAwarePaginator<int, Book>
     */
    public function paginate(BookFilters $filters): LengthAwarePaginator
    {
        return $this->paginateQuery(
            $this->applySort($this->filtered($filters), $filters->sort),
            $filters->pagination,
        );
    }

    public function findById(int $id): ?Book
    {
        return $this->withListRelations(Book::query())->find($id);
    }

    public function create(BookData $data): Book
    {
        $book = new Book($data->toAttributes());
        // The counter starts equal to the stock: no copy is out yet.
        $book->available_copies = $data->totalCopies;
        $book->save();

        $book->authors()->sync($data->authorIds);
        $book->categories()->sync($data->categoryIds);

        return $this->findById($book->id) ?? $book;
    }

    public function update(Book $book, BookData $data): Book
    {
        $previousTotal = $book->total_copies;

        $book->fill($data->toAttributes());

        // The stock changed, so the counter moves by the same delta: copies on
        // loan are unaffected by an inventory correction (BR-BOOK-2/3).
        $book->available_copies += $data->totalCopies - $previousTotal;
        $book->save();

        $book->authors()->sync($data->authorIds);
        $book->categories()->sync($data->categoryIds);

        return $this->findById($book->id) ?? $book;
    }

    public function delete(Book $book): void
    {
        $book->delete();
    }

    /**
     * @param  Builder<Book>  $query
     * @return Builder<Book>
     */
    private function withListRelations(Builder $query): Builder
    {
        // Declared here rather than left to the resource, so a list of books is
        // three queries whatever its length (RFC 10).
        return $query
            ->with(['authors:id,name', 'categories:id,name,slug'])
            ->withCount(['loans as active_loans_count' => fn (Builder $loans) => $loans->whereNull('returned_at')]);
    }

    /**
     * @return Builder<Book>
     */
    private function filtered(BookFilters $filters): Builder
    {
        $query = $this->withListRelations(Book::query());

        if ($filters->hasSearchTerm()) {
            $term = $filters->q;
            $query->where(fn (Builder $scoped) => $scoped
                ->where('title', 'like', $term.'%')
                ->orWhere('isbn', 'like', $term.'%')
                ->orWhereHas('authors', fn (Builder $authors) => $authors->where('name', 'like', $term.'%')));
        }

        if ($filters->categoryId !== null) {
            $query->whereHas('categories', fn (Builder $categories) => $categories->whereKey($filters->categoryId));
        }

        if ($filters->authorId !== null) {
            $query->whereHas('authors', fn (Builder $authors) => $authors->whereKey($filters->authorId));
        }

        if ($filters->available !== null) {
            $filters->available
                ? $query->where('available_copies', '>', 0)
                : $query->where('available_copies', '=', 0);
        }

        if ($filters->yearFrom !== null) {
            $query->where('publication_year', '>=', $filters->yearFrom);
        }

        if ($filters->yearTo !== null) {
            $query->where('publication_year', '<=', $filters->yearTo);
        }

        return $query;
    }
}
