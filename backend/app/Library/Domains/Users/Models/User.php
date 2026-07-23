<?php

declare(strict_types=1);

namespace App\Library\Domains\Users\Models;

use App\Library\Domains\Loans\Models\Loan;
use App\Library\Domains\Users\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property bool $is_active
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, SoftDeletes;

    /**
     * Roles and permissions are resolved against the `web` guard. Sanctum
     * authenticates the request, but the authorization vocabulary is guard
     * agnostic, and pinning it keeps role lookups from depending on which guard
     * happened to resolve the user.
     */
    protected string $guard_name = 'web';

    /**
     * Writes go through DTOs in the application layer, never through
     * `fill($request->all())`; the list is kept explicit all the same.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Loan, $this>
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * The account's role. The API contract gives a user exactly one, so this
     * reads the first assigned role rather than the whole collection.
     */
    public function role(): ?UserRole
    {
        $name = $this->roles->first()?->getAttribute('name');

        return is_string($name) ? UserRole::tryFrom($name) : null;
    }

    public function isStaff(): bool
    {
        return $this->role()?->isStaff() ?? false;
    }

    public function isAdmin(): bool
    {
        return $this->role()?->isAdmin() ?? false;
    }

    /**
     * @return Factory<User>
     */
    protected static function newFactory(): Factory
    {
        return UserFactory::new();
    }
}
