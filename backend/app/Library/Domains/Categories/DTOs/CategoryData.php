<?php

declare(strict_types=1);

namespace App\Library\Domains\Categories\DTOs;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

final readonly class CategoryData
{
    public function __construct(
        public string $name,
        public ?string $description,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            name: trim($request->string('name')->toString()),
            description: $request->filled('description') ? $request->string('description')->toString() : null,
        );
    }

    /**
     * FR-CAT-1: the slug is derived, never accepted from the client.
     */
    public function slug(): string
    {
        return Str::slug($this->name);
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug(),
            'description' => $this->description,
        ];
    }
}
