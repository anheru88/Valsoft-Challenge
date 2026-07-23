<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Library\Domains\Authors\Actions\CreateAuthorAction;
use App\Library\Domains\Authors\Actions\DeleteAuthorAction;
use App\Library\Domains\Authors\Actions\UpdateAuthorAction;
use App\Library\Domains\Authors\Contracts\AuthorRepositoryInterface;
use App\Library\Domains\Authors\DTOs\AuthorData;
use App\Library\Domains\Authors\DTOs\AuthorFilters;
use App\Library\Domains\Authors\Models\Author;
use App\Library\Domains\Authors\Requests\IndexAuthorRequest;
use App\Library\Domains\Authors\Requests\StoreAuthorRequest;
use App\Library\Domains\Authors\Resources\AuthorResource;
use App\Support\OpenApi\DomainErrors;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

final class AuthorController
{
    /**
     * List authors.
     */
    public function index(IndexAuthorRequest $request, AuthorRepositoryInterface $authors): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Author::class);

        return AuthorResource::collection($authors->paginate(AuthorFilters::fromRequest($request)));
    }

    /**
     * Show an author.
     */
    public function show(int $author, AuthorRepositoryInterface $authors): AuthorResource
    {
        Gate::authorize('view', Author::class);

        $model = $authors->findById($author);

        abort_if($model === null, Response::HTTP_NOT_FOUND);

        return new AuthorResource($model);
    }

    /**
     * Create an author.
     *
     * Names are deliberately not unique: homonymous authors exist.
     */
    public function store(StoreAuthorRequest $request, CreateAuthorAction $createAuthor): JsonResponse
    {
        Gate::authorize('create', Author::class);

        $created = $createAuthor(AuthorData::fromRequest($request));

        return (new AuthorResource($created))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED)
            ->header('Location', route('authors.show', $created));
    }

    /**
     * Update an author.
     */
    public function update(StoreAuthorRequest $request, Author $author, UpdateAuthorAction $updateAuthor): AuthorResource
    {
        Gate::authorize('update', Author::class);

        return new AuthorResource($updateAuthor($author, AuthorData::fromRequest($request)));
    }

    /**
     * Delete an author.
     */
    #[DomainErrors(['AUTHOR_IN_USE'])]
    public function destroy(Author $author, DeleteAuthorAction $deleteAuthor): Response
    {
        Gate::authorize('delete', Author::class);

        $deleteAuthor($author);

        return response()->noContent();
    }
}
