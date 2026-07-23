<?php

declare(strict_types=1);

namespace App\Library\Domains\Authors\Resources;

use App\Library\Domains\Authors\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The embedded shape inside a book: id and name only, so the catalogue payload
 * does not carry biographies it will not render (API specification 3).
 *
 * @mixin Author
 */
final class AuthorSummaryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
