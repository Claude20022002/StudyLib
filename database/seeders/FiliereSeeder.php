<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Filiere;
use Illuminate\Database\Seeder;

class FiliereSeeder extends Seeder
{
    public function run(): void
    {
        $filieres = [
            ['name' => 'Génie Informatique', 'code' => 'GI'],
            ['name' => 'Génie Civil', 'code' => 'GC'],
            ['name' => 'Génie Industriel', 'code' => 'GIND'],
            ['name' => 'Management', 'code' => 'MGT'],
        ];

        foreach ($filieres as $filiere) {
            Filiere::query()->firstOrCreate(
                ['code' => $filiere['code']],
                ['name' => $filiere['name']],
            );
        }
    }
}
