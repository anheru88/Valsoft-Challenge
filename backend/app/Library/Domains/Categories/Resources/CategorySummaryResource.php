<?php

declare(strict_types=1);

namespace App\Library\Domains\Categories\Resources;

use App\Library\Domains\Categories\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The embedded shape inside a book (API specification 3).
 *
 * @mixin Category
 */
final class CategorySummaryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
        ];
    }
}
