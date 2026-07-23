<?php

declare(strict_types=1);

namespace App\Library\Domains\Users\Resources;

use App\Library\Domains\Users\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The public JSON shape of a user (API specification 7). Column renames stop
 * here: they never reach a client.
 *
 * @mixin User
 */
final class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role->value,
            'is_active' => $this->is_active,
            // Present only when the query counted it, so the resource never
            // triggers a query of its own.
            'active_loans_count' => $this->whenCounted('active_loans'),
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
