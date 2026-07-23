<?php

declare(strict_types=1);

namespace App\Library\Domains\Auth\Actions;

use App\Library\Domains\Auth\Contracts\TokenIssuer;
use App\Library\Domains\Auth\DTOs\ChangePasswordData;
use App\Library\Domains\Auth\Exceptions\CurrentPasswordInvalidException;
use App\Library\Domains\Users\Contracts\UserRepositoryInterface;
use App\Library\Domains\Users\DTOs\UpdateUserData;
use App\Library\Domains\Users\Models\User;
use Illuminate\Contracts\Hashing\Hasher;

final readonly class ChangePasswordAction
{
    public function __construct(
        private UserRepositoryInterface $users,
        private TokenIssuer $tokens,
        private Hasher $hasher,
    ) {}

    public function __invoke(User $user, ChangePasswordData $data): void
    {
        if (! $this->hasher->check($data->currentPassword, $user->password)) {
            throw new CurrentPasswordInvalidException;
        }

        $this->users->update($user, new UpdateUserData(password: $data->newPassword));

        // FR-AUTH-7: the session doing the change survives, every other one dies.
        $this->tokens->revokeOthers($user);
    }
}
