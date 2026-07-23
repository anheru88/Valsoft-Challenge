<?php

declare(strict_types=1);

namespace Tests\Support\Doubles;

use App\Library\Domains\Auth\Contracts\TokenIssuer;
use App\Library\Domains\Users\Models\User;

final class RecordingTokenIssuer implements TokenIssuer
{
    /** @var list<int|null> */
    public array $revokedAllFor = [];

    public function issue(User $user, string $name = 'api'): string
    {
        return 'fake-token';
    }

    public function revokeCurrent(User $user): void {}

    public function revokeOthers(User $user): void {}

    public function revokeAll(User $user): void
    {
        $this->revokedAllFor[] = $user->id;
    }
}
