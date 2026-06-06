<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(FiliereSeeder::class);

        User::factory()->create([
            'name' => 'Admin HESTIM',
            'email' => 'admin@hestim.ma',
            'role' => UserRole::Admin,
        ]);

        User::factory()->create([
            'name' => 'Étudiant Test',
            'email' => 'etudiant@hestim.ma',
            'role' => UserRole::Student,
        ]);
    }
}
