<?php

declare(strict_types=1);

namespace App\Library\Domains\Users\Actions;

use App\Library\Domains\Auth\Contracts\TokenIssuer;
use App\Library\Domains\Loans\Contracts\LoanRepositoryInterface;
use App\Library\Domains\Users\Contracts\UserRepositoryInterface;
use App\Library\Domains\Users\Events\UserDeleted;
use App\Library\Domains\Users\Exceptions\LastAdminProtectedException;
use App\Library\Domains\Users\Exceptions\UserHasActiveLoansException;
use App\Library\Domains\Users\Models\User;
use Illuminate\Contracts\Events\Dispatcher;

final readonly class DeleteUserAction
{
    public function __construct(
        private UserRepositoryInterface $users,
        private LoanRepositoryInterface $loans,
        private TokenIssuer $tokens,
        private Dispatcher $events,
    ) {}

    public function __invoke(User $user): void
    {
        // BR-USER-1: books in this member's hands must come back first.
        $activeLoans = $this->loans->countActiveForUser($user->id);

        if ($activeLoans > 0) {
            throw UserHasActiveLoansException::withCount($activeLoans);
        }

        // BR-USER-3.
        if ($user->isAdmin() && $user->is_active && $this->users->countActiveAdmins(excludingUserId: $user->id) === 0) {
            throw LastAdminProtectedException::forDeletion();
        }

        $email = $user->email;
        $id = $user->id;

        $this->tokens->revokeAll($user);
        $this->users->delete($user);

        $this->events->dispatch(new UserDeleted($id, $email));
    }
}
