<?php

declare(strict_types=1);

use App\Library\Domains\Users\Actions\DeleteUserAction;
use App\Library\Domains\Users\Actions\UpdateUserAction;
use App\Library\Domains\Users\DTOs\UpdateUserData;
use App\Library\Domains\Users\Enums\UserRole;
use App\Library\Domains\Users\Exceptions\LastAdminProtectedException;
use App\Library\Domains\Users\Exceptions\UserHasActiveLoansException;
use App\Library\Domains\Users\Models\User;
use Illuminate\Events\Dispatcher;
use Tests\Support\Doubles\FakeLoanRepository;
use Tests\Support\Doubles\InMemoryUserRepository;
use Tests\Support\Doubles\RecordingTokenIssuer;

function makeUser(int $id, UserRole $role, bool $isActive = true): User
{
    $user = new User(['name' => 'User '.$id, 'email' => "user{$id}@librarium.test", 'role' => $role, 'is_active' => $isActive]);
    $user->id = $id;

    return $user;
}

it('refuses to demote the last active administrator', function () {
    $admin = makeUser(1, UserRole::Admin);
    $action = new UpdateUserAction(new InMemoryUserRepository([$admin]), new RecordingTokenIssuer, new Dispatcher);

    expect(fn () => $action($admin, new UpdateUserData(role: UserRole::Member)))
        ->toThrow(LastAdminProtectedException::class);
});

it('refuses to deactivate the last active administrator', function () {
    $admin = makeUser(1, UserRole::Admin);
    $action = new UpdateUserAction(new InMemoryUserRepository([$admin]), new RecordingTokenIssuer, new Dispatcher);

    expect(fn () => $action($admin, new UpdateUserData(isActive: false)))
        ->toThrow(LastAdminProtectedException::class);
});

it('allows demoting an administrator when another active one remains', function () {
    $admin = makeUser(1, UserRole::Admin);
    $other = makeUser(2, UserRole::Admin);
    $action = new UpdateUserAction(new InMemoryUserRepository([$admin, $other]), new RecordingTokenIssuer, new Dispatcher);

    expect($action($admin, new UpdateUserData(role: UserRole::Member))->role)->toBe(UserRole::Member);
});

it('does not count an already inactive administrator as the last one', function () {
    $admin = makeUser(1, UserRole::Admin);
    $inactiveAdmin = makeUser(2, UserRole::Admin, isActive: false);
    $action = new UpdateUserAction(new InMemoryUserRepository([$admin, $inactiveAdmin]), new RecordingTokenIssuer, new Dispatcher);

    expect(fn () => $action($admin, new UpdateUserData(role: UserRole::Member)))
        ->toThrow(LastAdminProtectedException::class);
});

it('cuts every session when a role changes', function () {
    $admin = makeUser(1, UserRole::Admin);
    $member = makeUser(2, UserRole::Member);
    $tokens = new RecordingTokenIssuer;
    $action = new UpdateUserAction(new InMemoryUserRepository([$admin, $member]), $tokens, new Dispatcher);

    $action($member, new UpdateUserData(role: UserRole::Librarian));

    expect($tokens->revokedAllFor)->toBe([2]);
});

it('refuses to delete a member holding books', function () {
    $member = makeUser(2, UserRole::Member);
    $action = new DeleteUserAction(
        new InMemoryUserRepository([$member]),
        new FakeLoanRepository([2 => 3]),
        new RecordingTokenIssuer,
        new Dispatcher,
    );

    expect(fn () => $action($member))->toThrow(UserHasActiveLoansException::class);
});

it('reports how many loans blocked the deletion', function () {
    $member = makeUser(2, UserRole::Member);
    $action = new DeleteUserAction(
        new InMemoryUserRepository([$member]),
        new FakeLoanRepository([2 => 3]),
        new RecordingTokenIssuer,
        new Dispatcher,
    );

    try {
        $action($member);
    } catch (UserHasActiveLoansException $e) {
        expect($e->details())->toBe(['active_loans' => 3])
            ->and($e->errorCode())->toBe('USER_HAS_ACTIVE_LOANS')
            ->and($e->httpStatus())->toBe(409);
    }
});

it('deletes a member with no open loans and cuts their sessions', function () {
    $member = makeUser(2, UserRole::Member);
    $tokens = new RecordingTokenIssuer;
    $repository = new InMemoryUserRepository([$member]);
    $action = new DeleteUserAction($repository, new FakeLoanRepository, $tokens, new Dispatcher);

    $action($member);

    expect($repository->findById(2))->toBeNull()
        ->and($tokens->revokedAllFor)->toBe([2]);
});
