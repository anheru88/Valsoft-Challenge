<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Library\Domains\Books\Actions\CreateBookAction;
use App\Library\Domains\Books\Actions\DeleteBookAction;
use App\Library\Domains\Books\Actions\UpdateBookAction;
use App\Library\Domains\Books\Contracts\BookRepositoryInterface;
use App\Library\Domains\Books\DTOs\BookData;
use App\Library\Domains\Books\DTOs\BookFilters;
use App\Library\Domains\Books\Models\Book;
use App\Library\Domains\Books\Requests\IndexBookRequest;
use App\Library\Domains\Books\Requests\StoreBookRequest;
use App\Library\Domains\Books\Requests\UpdateBookRequest;
use App\Library\Domains\Books\Resources\BookResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

final class BookController
{
    public function index(IndexBookRequest $request, BookRepositoryInterface $books): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Book::class);

        return BookResource::collection($books->paginate(BookFilters::fromRequest($request)));
    }

    public function show(int $book, BookRepositoryInterface $books): BookResource
    {
        Gate::authorize('view', Book::class);

        // Resolved through the repository rather than route binding, so the
        // detail payload carries the same eager loads the list does.
        $model = $books->findById($book);

        abort_if($model === null, Response::HTTP_NOT_FOUND);

        return new BookResource($model);
    }

    public function store(StoreBookRequest $request, CreateBookAction $createBook): JsonResponse
    {
        Gate::authorize('create', Book::class);

        $created = $createBook(BookData::fromRequest($request));

        return (new BookResource($created))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED)
            ->header('Location', route('books.show', $created));
    }

    public function update(UpdateBookRequest $request, Book $book, UpdateBookAction $updateBook): BookResource
    {
        Gate::authorize('update', Book::class);

        return new BookResource($updateBook($book, BookData::fromRequest($request)));
    }

    public function destroy(Book $book, DeleteBookAction $deleteBook): Response
    {
        Gate::authorize('delete', Book::class);

        $deleteBook($book);

        return response()->noContent();
    }
}
