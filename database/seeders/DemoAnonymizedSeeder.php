<?php

namespace Database\Seeders;

use App\Services\SyntheticDataService;
use Illuminate\Database\Seeder;

class DemoAnonymizedSeeder extends Seeder
{
    /**
     * Puebla la base de datos con datos académicos 100% ficticios y anonimizados
     * para presentaciones públicas, demos y sustentación académica.
     */
    public function run(SyntheticDataService $service): void
    {
        $service->generate();
    }
}