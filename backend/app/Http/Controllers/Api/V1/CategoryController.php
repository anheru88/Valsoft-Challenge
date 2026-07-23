<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Library\Domains\Categories\Actions\CreateCategoryAction;
use App\Library\Domains\Categories\Actions\DeleteCategoryAction;
use App\Library\Domains\Categories\Actions\UpdateCategoryAction;
use App\Library\Domains\Categories\Contracts\CategoryRepositoryInterface;
use App\Library\Domains\Categories\DTOs\CategoryData;
use App\Library\Domains\Categories\DTOs\CategoryFilters;
use App\Library\Domains\Categories\Models\Category;
use App\Library\Domains\Categories\Requests\IndexCategoryRequest;
use App\Library\Domains\Categories\Requests\StoreCategoryRequest;
use App\Library\Domains\Categories\Resources\CategoryResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

final class CategoryController
{
    public function index(IndexCategoryRequest $request, CategoryRepositoryInterface $categories): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Category::class);

        return CategoryResource::collection($categories->paginate(CategoryFilters::fromRequest($request)));
    }

    public function show(int $category, CategoryRepositoryInterface $categories): CategoryResource
    {
        Gate::authorize('view', Category::class);

        $model = $categories->findById($category);

        abort_if($model === null, Response::HTTP_NOT_FOUND);

        return new CategoryResource($model);
    }

    public function store(StoreCategoryRequest $request, CreateCategoryAction $createCategory): JsonResponse
    {
        Gate::authorize('create', Category::class);

        $created = $createCategory(CategoryData::fromRequest($request));

        return (new CategoryResource($created))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED)
            ->header('Location', route('categories.show', $created));
    }

    public function update(StoreCategoryRequest $request, Category $category, UpdateCategoryAction $updateCategory): CategoryResource
    {
        Gate::authorize('update', Category::class);

        return new CategoryResource($updateCategory($category, CategoryData::fromRequest($request)));
    }

    public function destroy(Category $category, DeleteCategoryAction $deleteCategory): Response
    {
        Gate::authorize('delete', Category::class);

        $deleteCategory($category);

        return response()->noContent();
    }
}
