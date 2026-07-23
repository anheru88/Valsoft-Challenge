<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * The demo dataset: a small library with a year of history behind it.
 *
 * Order matters — circulation needs a catalogue to lend and members to lend to.
 * Roles and permissions are not seeded here: they are part of the schema, so a
 * migrated database can already authorize (see the roles migration).
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CatalogSeeder::class,
            CirculationSeeder::class,
        ]);
    }
}
