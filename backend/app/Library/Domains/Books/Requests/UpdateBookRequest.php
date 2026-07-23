<?php

declare(strict_types=1);

namespace App\Library\Domains\Books\Requests;

use App\Library\Domains\Books\Rules\ValidIsbn;
use App\Library\Domains\Books\ValueObjects\Isbn;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * A full replacement (PUT), so the field list matches creation. Note that
 * `available_copies` is not accepted here either: the counter belongs to the
 * loan lifecycle (BR-BOOK-2).
 */
final class UpdateBookRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var string $bookId */
        $bookId = $this->route('book');

        return [
            'title' => ['required', 'string', 'max:255'],
            'isbn' => ['required', 'string', new ValidIsbn, Rule::unique('books', 'isbn')->whereNull('deleted_at')->ignore($bookId)],
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
