<?php

declare(strict_types=1);

namespace App\Library\Domains\Users\Contracts;

use App\Library\Domains\Users\DTOs\CreateUserData;
use App\Library\Domains\Users\DTOs\UpdateUserData;
use App\Library\Domains\Users\DTOs\UserFilters;
use App\Library\Domains\Users\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * The dependency-inversion seam for the Users domain: actions depend on this,
 * the container supplies Eloquent, and rule tests supply a fake (RFC 6).
 */
interface UserRepositoryInterface
{
    /**
     * @return LengthAwarePaginator<int, User>
     */
    public function paginate(UserFilters $filters): LengthAwarePaginator;

    public function findById(int $id): ?User;

    public function findByEmail(string $email): ?User;

    public function create(CreateUserData $data): User;

    public function update(User $user, UpdateUserData $data): User;

    /**
     * Soft delete — historical loans keep pointing at a real row (FR-USER-4).
     */
    public function delete(User $user): void;

    /**
     * BR-USER-3: how many active administrators remain if the given user is
     * discounted. The exclusion is what makes "the last one" answerable.
     */
    public function countActiveAdmins(?int $excludingUserId = null): int;
}
