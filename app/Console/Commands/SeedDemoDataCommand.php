<?php

namespace App\Console\Commands;

use App\Services\SyntheticDataService;
use Illuminate\Console\Command;

class SeedDemoDataCommand extends Command
{
    protected $signature = 'uns:seed-demo';
    protected $description = 'Puebla la base de datos con datos 100% ficticios y anonimizados para presentaciones públicas';

    public function handle(SyntheticDataService $service): int
    {
        $this->info("==========================================================");
        $this->info("   GENERANDO DATA FICTICIA Y ANONIMIZADA (MODO DEMO)   ");
        $this->info("==========================================================");
        $this->warn("Eliminando datos sensibles y generando padrón sintético...");

        $startTime = microtime(true);
        $stats = $service->generate();
        $duration = round(microtime(true) - $startTime, 2);

        $this->newLine();
        $this->info("Población ficticia completada con éxito:");
        $this->table(
            ['Entidad Demo', 'Total Creado'],
            [
                ['Asignaturas (Malla Curricular)', $stats['courses']],
                ['Docentes Ficticios', $stats['teachers']],
                ['Grupos de Teoría', $stats['theories']],
                ['Grupos de Práctica', $stats['practices']],
                ['Estudiantes Ficticios', $stats['students']],
                ['Matrículas Sintéticas', $stats['enrollments']],
            ]
        );
        $this->comment("Tiempo de ejecución: {$duration} segundos.");
        $this->info("¡Base de datos lista para proyectar y presentar públicamente!");

        return Command::SUCCESS;
    }
}