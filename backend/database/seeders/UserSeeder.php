<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Library\Domains\Users\Enums\UserRole;
use App\Library\Domains\Users\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Staff with predictable credentials for review, plus a membership large enough
 * that the user list paginates and the filters have something to filter.
 */
class UserSeeder extends Seeder
{
    public const PASSWORD = 'password';

    /**
     * @var list<string>
     */
    private const MEMBER_NAMES = [
        'Marta Ruiz', 'Iván Costa', 'Lucía Gómez', 'Andrés Vidal', 'Nuria Sanz',
        'Pablo Herrera', 'Elena Márquez', 'Diego Salas', 'Carmen Ortiz', 'Javier Peña',
        'Sofía Ibáñez', 'Marcos Delgado', 'Irene Cabrera', 'Raúl Moya', 'Alba Serrano',
        'Tomás Ferrer', 'Rocío Bravo', 'Hugo Nieto', 'Clara Pardo', 'Sergio Lozano',
        'Beatriz Cano', 'Adrián Gil', 'Natalia Prieto', 'Óscar Rey', 'Silvia Mena',
        'Guillermo Arias', 'Paula Duarte', 'Rubén Castaño', 'Laura Vega', 'Emilio Rosas',
        'Teresa Blanco', 'Nicolás Ramos', 'Marina Soler', 'Álvaro Cuevas', 'Inés Otero',
    ];

    public function run(): void
    {
        $this->staff('Alicia Ferrer', 'admin@librarium.test', UserRole::Admin);
        $this->staff('Luis Prado', 'librarian@librarium.test', UserRole::Librarian);
        $this->staff('Rosa Mendoza', 'librarian2@librarium.test', UserRole::Librarian);

        // The named member is the account a reviewer logs in with to see the
        // borrower side; the rest fill the list.
        $this->member('Marta Ruiz', 'member@librarium.test', active: true);

        foreach (array_slice(self::MEMBER_NAMES, 1) as $index => $name) {
            $this->member(
                $name,
                $this->emailFor($name),
                // A few deactivated accounts, so the status filter is not always
                // an empty result.
                active: $index % 12 !== 0,
            );
        }
    }

    private function staff(string $name, string $email, UserRole $role): User
    {
        $user = User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make(self::PASSWORD),
            'is_active' => true,
        ]);
        $user->syncRoles([$role->value]);

        return $user;
    }

    private function member(string $name, string $email, bool $active): User
    {
        $user = User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make(self::PASSWORD),
            'is_active' => $active,
        ]);
        $user->syncRoles([UserRole::Member->value]);
        $user->created_at = now()->subDays(random_int(10, 800));
        $user->save();

        return $user;
    }

    private function emailFor(string $name): string
    {
        $slug = str_replace(' ', '.', mb_strtolower(
            strtr($name, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n', 'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ñ' => 'N']),
        ));

        return $slug.'@example.com';
    }
}
