<?php

declare(strict_types=1);

namespace App\Library\Domains\Authors\Repositories;

use App\Library\Domains\Authors\Contracts\AuthorRepositoryInterface;
use App\Library\Domains\Authors\DTOs\AuthorData;
use App\Library\Domains\Authors\DTOs\AuthorFilters;
use App\Library\Domains\Authors\Models\Author;
use App\Library\Shared\Infrastructure\EloquentRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * @extends EloquentRepository<Author>
 */
final class EloquentAuthorRepository extends EloquentRepository implements AuthorRepositoryInterface
{
    /**
     * @return LengthAwarePaginator<int, Author>
     */
    public function paginate(AuthorFilters $filters): LengthAwarePaginator
    {
        $query = Author::query()->withCount('books');

        if ($filters->q !== null) {
            // Prefix match, which the name index can serve (RFC 9.2).
            $query->where('name', 'like', $filters->q.'%');
        }

        return $this->paginateQuery($this->applySort($query, $filters->sort), $filters->pagination);
    }

    public function findById(int $id): ?Author
    {
        return Author::query()->withCount('books')->find($id);
    }

    public function create(AuthorData $data): Author
    {
        return Author::query()->create($data->toAttributes());
    }

    public function update(Author $author, AuthorData $data): Author
    {
        $author->fill($data->toAttributes());
        $author->save();

        return $author->loadCount('books');
    }

    public function delete(Author $author): void
    {
        $author->delete();
    }

    public function countBooks(int $authorId): int
    {
        return Author::query()->whereKey($authorId)->withCount('books')->value('books_count') ?? 0;
    }
}
