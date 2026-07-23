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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Controllers stay at the framework edge: build a DTO, call an action, return a
 * resource. No business rule lives here (RFC 4).
 */
final class AuthController
{
    public function register(RegisterRequest $request, RegisterUserAction $register): JsonResponse
    {
        $session = $register(CreateUserData::forRegistration($request));

        return (new AuthenticatedSessionResource($session))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function login(LoginRequest $request, LoginAction $login): AuthenticatedSessionResource
    {
        return new AuthenticatedSessionResource($login(LoginData::fromRequest($request)));
    }

    public function logout(Request $request, TokenIssuer $tokens): Response
    {
        /** @var User $user */
        $user = $request->user();

        $tokens->revokeCurrent($user);

        return response()->noContent();
    }

    public function me(Request $request, UserRepositoryInterface $users): UserResource
    {
        /** @var User $user */
        $user = $request->user();

        // Re-read through the repository so the active loan count of FR-AUTH-6
        // comes from the same counted query the rest of the API uses.
        return new UserResource($users->findById($user->id) ?? $user);
    }

    public function changePassword(ChangePasswordRequest $request, ChangePasswordAction $changePassword): Response
    {
        /** @var User $user */
        $user = $request->user();

        $changePassword($user, ChangePasswordData::fromRequest($request));

        return response()->noContent();
    }
}
