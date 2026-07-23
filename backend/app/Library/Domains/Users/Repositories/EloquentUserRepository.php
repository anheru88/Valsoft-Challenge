<?php

declare(strict_types=1);

namespace App\Library\Domains\Users\Repositories;

use App\Library\Domains\Users\Contracts\UserRepositoryInterface;
use App\Library\Domains\Users\DTOs\CreateUserData;
use App\Library\Domains\Users\DTOs\UpdateUserData;
use App\Library\Domains\Users\DTOs\UserFilters;
use App\Library\Domains\Users\Enums\UserRole;
use App\Library\Domains\Users\Models\User;
use App\Library\Shared\Infrastructure\EloquentRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends EloquentRepository<User>
 */
final class EloquentUserRepository extends EloquentRepository implements UserRepositoryInterface
{
    /**
     * @return LengthAwarePaginator<int, User>
     */
    public function paginate(UserFilters $filters): LengthAwarePaginator
    {
        $query = User::query()
            // withCount is a subquery: the list never loads loan collections
            // to display a number (RFC 10).
            ->withCount(['loans as active_loans_count' => fn (Builder $loans) => $loans->whereNull('returned_at')]);

        if ($filters->q !== null) {
            $query->where(function (Builder $scoped) use ($filters): void {
                $scoped->where('name', 'like', $filters->q.'%')
                    ->orWhere('email', 'like', $filters->q.'%');
            });
        }

        if ($filters->role !== null) {
            $query->where('role', $filters->role);
        }

        if ($filters->isActive !== null) {
            $query->where('is_active', $filters->isActive);
        }

        return $this->paginateQuery($this->applySort($query, $filters->sort), $filters->pagination);
    }

    public function findById(int $id): ?User
    {
        return User::query()
            ->withCount(['loans as active_loans_count' => fn (Builder $loans) => $loans->whereNull('returned_at')])
            ->find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::query()->where('email', $email)->first();
    }

    public function create(CreateUserData $data): User
    {
        return User::query()->create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => $data->password,
            'role' => $data->role,
            'is_active' => true,
        ]);
    }

    public function update(User $user, UpdateUserData $data): User
    {
        $user->fill($data->toAttributes());
        $user->save();

        return $user;
    }

    public function delete(User $user): void
    {
        $user->delete();
    }

    public function countActiveAdmins(?int $excludingUserId = null): int
    {
        return User::query()
            ->where('role', UserRole::Admin)
            ->where('is_active', true)
            ->when($excludingUserId !== null, fn (Builder $query) => $query->whereKeyNot($excludingUserId))
            ->count();
    }
}
