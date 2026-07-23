<?php

declare(strict_types=1);

namespace Tests\Support\Doubles;

use App\Library\Domains\Users\Contracts\UserRepositoryInterface;
use App\Library\Domains\Users\DTOs\CreateUserData;
use App\Library\Domains\Users\DTOs\UpdateUserData;
use App\Library\Domains\Users\DTOs\UserFilters;
use App\Library\Domains\Users\Enums\UserRole;
use App\Library\Domains\Users\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use RuntimeException;
use Spatie\Permission\Models\Role;

/**
 * The payoff of the repository contract (RFC 6): rule tests run against this
 * in memory, with no database and no migrations, in milliseconds.
 */
final class InMemoryUserRepository implements UserRepositoryInterface
{
    /**
     * @param  list<User>  $users
     */
    public function __construct(private array $users = []) {}

    public function paginate(UserFilters $filters): LengthAwarePaginator
    {
        throw new RuntimeException('Listing is covered by feature tests.');
    }

    public function findById(int $id): ?User
    {
        foreach ($this->users as $user) {
            if ($user->id === $id) {
                return $user;
            }
        }

        return null;
    }

    public function findByEmail(string $email): ?User
    {
        foreach ($this->users as $user) {
            if ($user->email === $email) {
                return $user;
            }
        }

        return null;
    }

    public function create(CreateUserData $data): User
    {
        $user = new User([
            'name' => $data->name,
            'email' => $data->email,
            'password' => $data->password,
            'is_active' => true,
        ]);
        $user->id = count($this->users) + 1;
        // The role is a relation, populated here so the in-memory user answers
        // role() the way a persisted one does.
        $user->setRelation('roles', collect([new Role(['name' => $data->role->value, 'guard_name' => 'web'])]));

        $this->users[] = $user;

        return $user;
    }

    public function update(User $user, UpdateUserData $data): User
    {
        $user->fill($data->toAttributes());

        if ($data->role !== null) {
            $user->setRelation('roles', collect([new Role(['name' => $data->role->value, 'guard_name' => 'web'])]));
        }

        return $user;
    }

    public function delete(User $user): void
    {
        $this->users = array_values(array_filter(
            $this->users,
            fn (User $candidate): bool => $candidate->id !== $user->id,
        ));
    }

    public function countActiveAdmins(?int $excludingUserId = null): int
    {
        return count(array_filter(
            $this->users,
            fn (User $user): bool => $user->role() === UserRole::Admin
                && $user->is_active
                && $user->id !== $excludingUserId,
        ));
    }
}
