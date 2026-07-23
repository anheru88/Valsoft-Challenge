<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Library\Domains\Books\Actions\SearchBooksAction;
use App\Library\Domains\Books\DTOs\BookFilters;
use App\Library\Domains\Books\Models\Book;
use App\Library\Domains\Books\Requests\IndexBookRequest;
use App\Library\Domains\Books\Resources\BookResource;
use App\Support\OpenApi\DomainErrors;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

final class SearchController
{
    /**
     * Shares IndexBookRequest with the catalogue listing, which is what makes
     * FR-SRCH-2 true: `q` composes with every book filter and sort.
     */
    #[DomainErrors(['SEARCH_QUERY_TOO_SHORT'], status: 422, description: 'The query is shorter than the minimum, and is not an ISBN.')]
    public function books(IndexBookRequest $request, SearchBooksAction $search): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Book::class);

        return BookResource::collection($search(BookFilters::fromRequest($request)));
    }
}
