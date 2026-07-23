<?php

declare(strict_types=1);

namespace App\Library\Domains\Categories\Actions;

use App\Library\Domains\Categories\Contracts\CategoryRepositoryInterface;
use App\Library\Domains\Categories\Exceptions\CategoryInUseException;
use App\Library\Domains\Categories\Models\Category;

final readonly class DeleteCategoryAction
{
    public function __construct(private CategoryRepositoryInterface $categories) {}

    public function __invoke(Category $category): void
    {
        $booksCount = $this->categories->countBooks($category->id);

        if ($booksCount > 0) {
            throw CategoryInUseException::withBooks($booksCount);
        }

        $this->categories->delete($category);
    }
}
