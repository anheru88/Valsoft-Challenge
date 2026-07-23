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
use App\Support\OpenApi\DomainErrors;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

final class BookController
{
    /**
     * List the catalogue.
     */
    public function index(IndexBookRequest $request, BookRepositoryInterface $books): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Book::class);

        return BookResource::collection($books->paginate(BookFilters::fromRequest($request)));
    }

    /**
     * Show a book.
     *
     * Carries an ETag; a conditional request with If-None-Match answers 304.
     */
    public function show(int $book, BookRepositoryInterface $books): BookResource
    {
        Gate::authorize('view', Book::class);

        // Resolved through the repository rather than route binding, so the
        // detail payload carries the same eager loads the list does.
        $model = $books->findById($book);

        abort_if($model === null, Response::HTTP_NOT_FOUND);

        return new BookResource($model);
    }

    /**
     * Add a book to the catalogue.
     *
     * The ISBN is normalised to its ISBN-13 form before uniqueness is checked.
     */
    public function store(StoreBookRequest $request, CreateBookAction $createBook): JsonResponse
    {
        Gate::authorize('create', Book::class);

        $created = $createBook(BookData::fromRequest($request));

        return (new BookResource($created))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED)
            ->header('Location', route('books.show', $created));
    }

    /**
     * Replace a book.
     *
     * `available_copies` is system-managed and not accepted here; changing
     * `total_copies` moves the counter by the same delta.
     */
    #[DomainErrors(['BOOK_COPIES_BELOW_LOANED'], status: 422, description: 'The stock cannot be cut below the copies currently on loan.')]
    public function update(UpdateBookRequest $request, Book $book, UpdateBookAction $updateBook): BookResource
    {
        Gate::authorize('update', Book::class);

        return new BookResource($updateBook($book, BookData::fromRequest($request)));
    }

    /**
     * Delete a book.
     *
     * Soft delete, so closed loans keep a row to point at.
     */
    #[DomainErrors(['BOOK_HAS_ACTIVE_LOANS'])]
    public function destroy(Book $book, DeleteBookAction $deleteBook): Response
    {
        Gate::authorize('delete', Book::class);

        $deleteBook($book);

        return response()->noContent();
    }
}
