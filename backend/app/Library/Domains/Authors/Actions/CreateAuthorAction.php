<?php

declare(strict_types=1);

namespace App\Library\Domains\Authors\Actions;

use App\Library\Domains\Authors\Contracts\AuthorRepositoryInterface;
use App\Library\Domains\Authors\DTOs\AuthorData;
use App\Library\Domains\Authors\Models\Author;

final readonly class CreateAuthorAction
{
    public function __construct(private AuthorRepositoryInterface $authors) {}

    public function __invoke(AuthorData $data): Author
    {
        return $this->authors->create($data);
    }
}
