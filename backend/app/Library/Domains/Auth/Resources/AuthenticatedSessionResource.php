<?php

declare(strict_types=1);

namespace App\Library\Domains\Auth\Resources;

use App\Library\Domains\Auth\DTOs\AuthenticatedSession;
use App\Library\Domains\Users\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin AuthenticatedSession
 */
final class AuthenticatedSessionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'token' => $this->token,
            'token_type' => $this->tokenType,
            'user' => new UserResource($this->user),
        ];
    }
}
