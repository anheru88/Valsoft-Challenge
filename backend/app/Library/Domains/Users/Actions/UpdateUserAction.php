<?php

declare(strict_types=1);

namespace App\Library\Domains\Users\Actions;

use App\Library\Domains\Auth\Contracts\TokenIssuer;
use App\Library\Domains\Users\Contracts\UserRepositoryInterface;
use App\Library\Domains\Users\DTOs\UpdateUserData;
use App\Library\Domains\Users\Enums\UserRole;
use App\Library\Domains\Users\Events\UserRoleChanged;
use App\Library\Domains\Users\Exceptions\LastAdminProtectedException;
use App\Library\Domains\Users\Models\User;
use Illuminate\Contracts\Events\Dispatcher;

final readonly class UpdateUserAction
{
    public function __construct(
        private UserRepositoryInterface $users,
        private TokenIssuer $tokens,
        private Dispatcher $events,
    ) {}

    public function __invoke(User $user, UpdateUserData $data): User
    {
        $previousRole = $user->role();

        $this->guardLastAdmin($user, $data);

        $updated = $this->users->update($user, $data);

        if ($data->changesRole() && $data->role !== $previousRole && $previousRole !== null && $data->role !== null) {
            $this->events->dispatch(new UserRoleChanged($updated->id, $previousRole, $data->role));

            // Privileges just changed, so tokens minted under the old role must
            // not survive.
            $this->tokens->revokeAll($updated);
        }

        // BR-USER-5: deactivation takes effect now, not at token expiry.
        if ($data->isActive === false) {
            $this->tokens->revokeAll($updated);
        }

        return $updated;
    }

    /**
     * BR-USER-3: demoting or deactivating the last active administrator would
     * leave nobody able to manage users.
     */
    private function guardLastAdmin(User $user, UpdateUserData $data): void
    {
        if (! $user->isAdmin() || ! $user->is_active) {
            return;
        }

        $demoting = $data->changesRole() && $data->role !== UserRole::Admin;
        $deactivating = $data->isActive === false;

        if (! $demoting && ! $deactivating) {
            return;
        }

        if ($this->users->countActiveAdmins(excludingUserId: $user->id) > 0) {
            return;
        }

        throw $demoting
            ? LastAdminProtectedException::forRoleChange()
            : LastAdminProtectedException::forDeactivation();
    }
}
