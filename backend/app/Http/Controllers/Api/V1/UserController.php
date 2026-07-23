<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Library\Domains\Users\Actions\CreateUserAction;
use App\Library\Domains\Users\Actions\DeleteUserAction;
use App\Library\Domains\Users\Actions\UpdateUserAction;
use App\Library\Domains\Users\Contracts\UserRepositoryInterface;
use App\Library\Domains\Users\DTOs\CreateUserData;
use App\Library\Domains\Users\DTOs\UpdateUserData;
use App\Library\Domains\Users\DTOs\UserFilters;
use App\Library\Domains\Users\Models\User;
use App\Library\Domains\Users\Requests\ChangeUserStatusRequest;
use App\Library\Domains\Users\Requests\IndexUserRequest;
use App\Library\Domains\Users\Requests\StoreUserRequest;
use App\Library\Domains\Users\Requests\UpdateUserRequest;
use App\Library\Domains\Users\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

final class UserController
{
    public function index(IndexUserRequest $request, UserRepositoryInterface $users): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', User::class);

        return UserResource::collection($users->paginate(UserFilters::fromRequest($request)));
    }

    public function show(User $user): UserResource
    {
        Gate::authorize('view', $user);

        return new UserResource($user->loadCount(['loans as active_loans_count' => fn ($loans) => $loans->whereNull('returned_at')]));
    }

    public function store(StoreUserRequest $request, CreateUserAction $createUser): JsonResponse
    {
        // The requested role is part of the authorization question: a librarian
        // may create members and nothing else (PRD 8.3).
        Gate::authorize('create', [User::class, $request->role()]);

        $user = $createUser(CreateUserData::fromRequest($request));

        return (new UserResource($user))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED)
            ->header('Location', route('users.show', $user));
    }

    public function update(UpdateUserRequest $request, User $user, UpdateUserAction $updateUser): UserResource
    {
        Gate::authorize('update', $user);

        $data = UpdateUserData::fromRequest($request);

        if ($data->changesRole() || $data->changesStatus()) {
            Gate::authorize('changeRole', User::class);
        }

        return new UserResource($updateUser($user, $data));
    }

    public function changeStatus(ChangeUserStatusRequest $request, User $user, UpdateUserAction $updateUser): UserResource
    {
        Gate::authorize('changeStatus', User::class);

        return new UserResource($updateUser($user, new UpdateUserData(isActive: $request->boolean('is_active'))));
    }

    public function destroy(User $user, DeleteUserAction $deleteUser): Response
    {
        Gate::authorize('delete', User::class);

        $deleteUser($user);

        return response()->noContent();
    }
}
