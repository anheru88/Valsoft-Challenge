<?php

declare(strict_types=1);

namespace App\Library\Domains\Authors\DTOs;

use Illuminate\Http\Request;

final readonly class AuthorData
{
    public function __construct(
        public string $name,
        public ?string $bio,
        public ?int $birthYear,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: trim($request->string('name')->toString()),
            bio: $request->filled('bio') ? $request->string('bio')->toString() : null,
            birthYear: $request->filled('birth_year') ? $request->integer('birth_year') : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'name' => $this->name,
            'bio' => $this->bio,
            'birth_year' => $this->birthYear,
        ];
    }
}
