<?php

declare(strict_types=1);

namespace App\Library\Domains\Books\Requests;

use App\Library\Domains\Books\Rules\ValidIsbn;
use App\Library\Domains\Books\ValueObjects\Isbn;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * FR-BOOK-1. Uniqueness is checked against the normalized ISBN, so the same
 * book submitted as ISBN-10 and ISBN-13 collides as BR-BOOK-1 intends.
 */
final class StoreBookRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'isbn' => ['required', 'string', new ValidIsbn, Rule::unique('books', 'isbn')->whereNull('deleted_at')],
            'description' => ['nullable', 'string', 'max:5000'],
            'publisher' => ['nullable', 'string', 'max:255'],
            'publication_year' => ['nullable', 'integer', 'min:'.config('library.catalog.min_publication_year'), 'max:'.date('Y')],
            'cover_url' => ['nullable', 'url', 'max:255'],
            'total_copies' => ['required', 'integer', 'min:1', 'max:9999'],
            'author_ids' => ['required', 'array', 'min:1'],
            'author_ids.*' => ['integer', 'exists:authors,id'],
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
        ];
    }

    /**
     * The unique rule has to see the same value the database stores.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('isbn') && is_string($this->input('isbn'))) {
            $isbn = Isbn::tryFrom($this->string('isbn')->toString());

            if ($isbn !== null) {
                $this->merge(['isbn' => $isbn->value]);
            }
        }
    }
}
