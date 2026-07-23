<?php

declare(strict_types=1);

namespace App\Library\Domains\Auth\DTOs;

use Illuminate\Http\Request;

final readonly class LoginData
{
    public function __construct(
        public string $email,
        public string $password,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            email: strtolower(trim($request->string('email')->toString())),
            password: $request->string('password')->toString(),
        );
    }
}
