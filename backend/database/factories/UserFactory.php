<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Library\Domains\Users\Enums\UserRole;
use App\Library\Domains\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * The role is a pivot row, so it is assigned once the user exists. A later
     * withRole() call registers its own hook and replaces this default, since
     * syncRoles is a replacement rather than an addition.
     */
    public function configure(): static
    {
        return $this->withRole(UserRole::Member);
    }

    public function admin(): static
    {
        return $this->withRole(UserRole::Admin);
    }

    public function librarian(): static
    {
        return $this->withRole(UserRole::Librarian);
    }

    public function member(): static
    {
        return $this->withRole(UserRole::Member);
    }

    public function withRole(UserRole $role): static
    {
        return $this->afterCreating(fn (User $user) => $user->syncRoles([$role->value]));
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => ['email_verified_at' => null]);
    }
}
