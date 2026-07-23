<?php

declare(strict_types=1);

namespace App\Library\Domains\Categories\Actions;

use App\Library\Domains\Categories\Contracts\CategoryRepositoryInterface;
use App\Library\Domains\Categories\DTOs\CategoryData;
use App\Library\Domains\Categories\Models\Category;

final readonly class UpdateCategoryAction
{
    public function __construct(private CategoryRepositoryInterface $categories) {}

    public function __invoke(Category $category, CategoryData $data): Category
    {
        return $this->categories->update($category, $data);
    }
}
