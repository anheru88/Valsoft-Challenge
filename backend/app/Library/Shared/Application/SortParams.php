<?php

declare(strict_types=1);

namespace App\Library\Shared\Application;

use Illuminate\Http\Request;

/**
 * FR-LIST-2: sorting is restricted to a per-resource whitelist. The field never
 * reaches a query builder unless it came from that whitelist, which is what
 * keeps `sort` from becoming an injection surface.
 */
final readonly class SortParams
{
    public const DIRECTIONS = ['asc', 'desc'];

    private function __construct(
        public string $field,
        public string $direction,
    ) {}

    /**
     * @param  list<string>  $allowedFields
     */
    public static function make(?string $field, ?string $direction, array $allowedFields, string $defaultField): self
    {
        $field = $field !== null && in_array($field, $allowedFields, true) ? $field : $defaultField;
        $direction = $direction !== null && in_array(strtolower($direction), self::DIRECTIONS, true)
            ? strtolower($direction)
            : 'asc';

        return new self($field, $direction);
    }

    /**
     * @param  list<string>  $allowedFields
     */
    public static function fromRequest(Request $request, array $allowedFields, string $defaultField): self
    {
        return self::make(
            field: $request->string('sort')->toString() ?: null,
            direction: $request->string('direction')->toString() ?: null,
            allowedFields: $allowedFields,
            defaultField: $defaultField,
        );
    }

    public function isDescending(): bool
    {
        return $this->direction === 'desc';
    }
}
