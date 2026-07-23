<?php

declare(strict_types=1);

namespace App\Library\Domains\Books\Resources;

use App\Library\Domains\Authors\Resources\AuthorSummaryResource;
use App\Library\Domains\Books\Models\Book;
use App\Library\Domains\Categories\Resources\CategorySummaryResource;
use App\Library\Domains\Users\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Book
 */
final class BookResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'isbn' => $this->isbn,
            'description' => $this->description,
            'publisher' => $this->publisher,
            'publication_year' => $this->publication_year,
            'cover_url' => $this->cover_url,
            'total_copies' => $this->total_copies,
            'available_copies' => $this->available_copies,
            'is_available' => $this->isAvailable(),
            'authors' => AuthorSummaryResource::collection($this->whenLoaded('authors')),
            'categories' => CategorySummaryResource::collection($this->whenLoaded('categories')),
            // Circulation detail is desk information, not catalogue information
            // (API specification 3).
            'active_loans_count' => $this->when(
                $user instanceof User && $user->isStaff(),
                fn () => $this->active_loans_count,
            ),
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
