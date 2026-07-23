<?php

declare(strict_types=1);

namespace App\Library\Domains\Users\Resources;

use App\Library\Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The public JSON shape of a user (API specification 7).
 *
 * `roles` and `permissions` replace the former single `role` string: with
 * capabilities held in data, a client that wants to hide an action it cannot
 * perform needs the effective permissions, not a role name to reason about.
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
            'roles' => $this->whenLoaded(
                'roles',
                fn () => $this->roles->map(fn (Model $role): string => (string) $role->getAttribute('name'))->values()->all(),
            ),
            // Everything the account can do, whether it came from the role or
            // was granted directly.
            'permissions' => $this->when(
                $this->relationLoaded('roles') && $this->relationLoaded('permissions'),
                fn () => $this->getAllPermissions()
                    ->map(fn (Model $permission): string => (string) $permission->getAttribute('name'))
                    ->sort()
                    ->values()
                    ->all(),
            ),
            'is_active' => $this->is_active,
            // Present only when the query counted it, so the resource never
            // triggers a query of its own.
            'active_loans_count' => $this->whenCounted('active_loans'),
            'created_at' => $this->created_at?->toIso8601ZuluString(),
            'updated_at' => $this->updated_at?->toIso8601ZuluString(),
        ];
    }
}
