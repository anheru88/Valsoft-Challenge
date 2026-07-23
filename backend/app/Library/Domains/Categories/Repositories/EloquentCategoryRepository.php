<?php

declare(strict_types=1);

namespace App\Library\Domains\Categories\Repositories;

use App\Library\Domains\Categories\Contracts\CategoryRepositoryInterface;
use App\Library\Domains\Categories\DTOs\CategoryData;
use App\Library\Domains\Categories\DTOs\CategoryFilters;
use App\Library\Domains\Categories\Models\Category;
use App\Library\Shared\Infrastructure\EloquentRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * @extends EloquentRepository<Category>
 */
final class EloquentCategoryRepository extends EloquentRepository implements CategoryRepositoryInterface
{
    /**
     * @return LengthAwarePaginator<int, Category>
     */
    public function paginate(CategoryFilters $filters): LengthAwarePaginator
    {
        $query = Category::query()->withCount('books');

        if ($filters->q !== null) {
            $query->where('name', 'like', $filters->q.'%');
        }

        return $this->paginateQuery($this->applySort($query, $filters->sort), $filters->pagination);
    }

    public function findById(int $id): ?Category
    {
        return Category::query()->withCount('books')->find($id);
    }

    public function create(CategoryData $data): Category
    {
        return Category::query()->create($data->toAttributes());
    }

    public function update(Category $category, CategoryData $data): Category
    {
        $category->fill($data->toAttributes());
        $category->save();

        return $category->loadCount('books');
    }

    public function delete(Category $category): void
    {
        $category->delete();
    }

    public function countBooks(int $categoryId): int
    {
        return Category::query()->whereKey($categoryId)->withCount('books')->value('books_count') ?? 0;
    }
}
