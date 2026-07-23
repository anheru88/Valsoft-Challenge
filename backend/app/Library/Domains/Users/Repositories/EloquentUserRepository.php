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
        $query = $this->withAuthorization(User::query())
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
            // Spatie's scope resolves the pivot; the role name never reaches
            // the query as raw input.
            $query->role($filters->role->value);
        }

        if ($filters->isActive !== null) {
            $query->where('is_active', $filters->isActive);
        }

        return $this->paginateQuery($this->applySort($query, $filters->sort), $filters->pagination);
    }

    public function findById(int $id): ?User
    {
        return $this->withAuthorization(User::query())
            ->withCount(['loans as active_loans_count' => fn (Builder $loans) => $loans->whereNull('returned_at')])
            ->find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->withAuthorization(User::query())->where('email', $email)->first();
    }

    public function create(CreateUserData $data): User
    {
        $user = User::query()->create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => $data->password,
            'is_active' => true,
        ]);

        $user->syncRoles([$data->role->value]);

        return $user->load(['roles.permissions', 'permissions']);
    }

    public function update(User $user, UpdateUserData $data): User
    {
        $user->fill($data->toAttributes());
        $user->save();

        if ($data->role !== null) {
            // A user carries exactly one role in this product, so assigning is
            // a replacement.
            $user->syncRoles([$data->role->value]);
        }

        return $user->load(['roles.permissions', 'permissions']);
    }

    public function delete(User $user): void
    {
        $user->delete();
    }

    public function countActiveAdmins(?int $excludingUserId = null): int
    {
        return User::query()
            ->role(UserRole::Admin->value)
            ->where('is_active', true)
            ->when($excludingUserId !== null, fn (Builder $query) => $query->whereKeyNot($excludingUserId))
            ->count();
    }

    /**
     * Roles and their permissions are part of every user payload, so they are
     * eager loaded rather than resolved per row (RFC 10).
     *
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    private function withAuthorization(Builder $query): Builder
    {
        return $query->with(['roles.permissions', 'permissions']);
    }
}
