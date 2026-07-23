<?php

declare(strict_types=1);

namespace App\Library\Domains\Authors\Actions;

use App\Library\Domains\Authors\Contracts\AuthorRepositoryInterface;
use App\Library\Domains\Authors\DTOs\AuthorData;
use App\Library\Domains\Authors\Models\Author;

final readonly class UpdateAuthorAction
{
    public function __construct(private AuthorRepositoryInterface $authors) {}

    public function __invoke(Author $author, AuthorData $data): Author
    {
        return $this->authors->update($author, $data);
    }
}
