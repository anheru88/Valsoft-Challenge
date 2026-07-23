<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Library\Domains\Auth\Actions\ChangePasswordAction;
use App\Library\Domains\Auth\Actions\LoginAction;
use App\Library\Domains\Auth\Actions\RegisterUserAction;
use App\Library\Domains\Auth\Contracts\TokenIssuer;
use App\Library\Domains\Auth\DTOs\ChangePasswordData;
use App\Library\Domains\Auth\DTOs\LoginData;
use App\Library\Domains\Auth\Requests\ChangePasswordRequest;
use App\Library\Domains\Auth\Requests\LoginRequest;
use App\Library\Domains\Auth\Requests\RegisterRequest;
use App\Library\Domains\Auth\Resources\AuthenticatedSessionResource;
use App\Library\Domains\Users\Contracts\UserRepositoryInterface;
use App\Library\Domains\Users\DTOs\CreateUserData;
use App\Library\Domains\Users\Models\User;
use App\Library\Domains\Users\Resources\UserResource;
use App\Support\OpenApi\DomainErrors;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Controllers stay at the framework edge: build a DTO, call an action, return a
 * resource. No business rule lives here (RFC 4).
 */
final class AuthController
{
    /**
     * Register a member account.
     *
     * Public. The role is decided by the server: registration never mints
     * privileges (FR-AUTH-2). Rate limited to 5 requests a minute.
     */
    public function register(RegisterRequest $request, RegisterUserAction $register): JsonResponse
    {
        $session = $register(CreateUserData::forRegistration($request));

        return (new AuthenticatedSessionResource($session))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Log in.
     *
     * Public. An unknown email and a wrong password answer identically, so the
     * endpoint cannot be used to enumerate accounts. Rate limited to 5 requests
     * a minute per email and IP.
     */
    #[DomainErrors(['INVALID_CREDENTIALS'], status: 422, description: 'The credentials do not match any account.')]
    #[DomainErrors(['USER_INACTIVE'], status: 403, description: 'The account exists but has been deactivated.')]
    public function login(LoginRequest $request, LoginAction $login): AuthenticatedSessionResource
    {
        return new AuthenticatedSessionResource($login(LoginData::fromRequest($request)));
    }

    /**
     * Log out.
     *
     * Revokes the token that authenticated this request, and only that one.
     */
    public function logout(Request $request, TokenIssuer $tokens): Response
    {
        /** @var User $user */
        $user = $request->user();

        $tokens->revokeCurrent($user);

        return response()->noContent();
    }

    /**
     * Show the authenticated account.
     */
    public function me(Request $request, UserRepositoryInterface $users): UserResource
    {
        /** @var User $user */
        $user = $request->user();

        // Re-read through the repository so the active loan count of FR-AUTH-6
        // comes from the same counted query the rest of the API uses.
        return new UserResource($users->findById($user->id) ?? $user);
    }

    /**
     * Change the password.
     *
     * Requires the current password and revokes every other session.
     */
    #[DomainErrors(['CURRENT_PASSWORD_INVALID'], status: 422, description: 'The supplied current password is wrong.')]
    public function changePassword(ChangePasswordRequest $request, ChangePasswordAction $changePassword): Response
    {
        /** @var User $user */
        $user = $request->user();

        $changePassword($user, ChangePasswordData::fromRequest($request));

        return response()->noContent();
    }
}
