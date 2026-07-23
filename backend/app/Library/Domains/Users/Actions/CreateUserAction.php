<?php

declare(strict_types=1);

namespace App\Library\Domains\Users\Actions;

use App\Library\Domains\Users\Contracts\UserRepositoryInterface;
use App\Library\Domains\Users\DTOs\CreateUserData;
use App\Library\Domains\Users\Events\UserCreated;
use App\Library\Domains\Users\Models\User;
use Illuminate\Contracts\Events\Dispatcher;

final readonly class CreateUserAction
{
    public function __construct(
        private UserRepositoryInterface $users,
        private Dispatcher $events,
    ) {}

    public function __invoke(CreateUserData $data): User
    {
        $user = $this->users->create($data);

        $this->events->dispatch(new UserCreated($user->id, $user->email, $user->role));

        return $user;
    }
}
