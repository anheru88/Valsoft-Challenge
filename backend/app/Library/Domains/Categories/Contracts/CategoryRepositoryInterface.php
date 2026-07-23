<?php

declare(strict_types=1);

namespace App\Library\Domains\Categories\Contracts;

use App\Library\Domains\Categories\DTOs\CategoryData;
use App\Library\Domains\Categories\DTOs\CategoryFilters;
use App\Library\Domains\Categories\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CategoryRepositoryInterface
{
    /**
     * @return LengthAwarePaginator<int, Category>
     */
    public function paginate(CategoryFilters $filters): LengthAwarePaginator;

    public function findById(int $id): ?Category;

    public function create(CategoryData $data): Category;

    public function update(Category $category, CategoryData $data): Category;

    public function delete(Category $category): void;

    /**
     * BR-CAT-1: books referencing this category, which block its removal.
     */
    public function countBooks(int $categoryId): int;
}
