<?php

namespace App\Console\Commands;

use App\Services\RealDataImportService;
use Illuminate\Console\Command;

class ImportRealDataCommand extends Command
{
    protected $signature = 'uns:import-real-data {--path=D:/Proyectos/data_real} {--no-wipe : No vaciar datos existentes}';
    protected $description = 'Importa la carga horaria y matriculados reales desde archivos Excel oficiales del SIIGAA UNS';

    public function handle(RealDataImportService $service): int
    {
        $path = $this->option('path');
        $wipe = !$this->option('no-wipe');

        $this->info("Iniciando importación desde: $path");
        $this->warn($wipe ? "Modo: Limpieza y carga completa de datos reales" : "Modo: Carga incremental");

        $startTime = microtime(true);
        $stats = $service->import($path, $wipe);
        $duration = round(microtime(true) - $startTime, 2);

        $this->newLine();
        $this->info("==================================================");
        $this->info("   IMPORTACIÓN OFICIAL UNS FINALIZADA CON ÉXITO   ");
        $this->info("==================================================");
        $this->table(
            ['Entidad / Métrica', 'Total Registros Reales'],
            [
                ['Asignaturas (Cursos)', $stats['courses']],
                ['Docentes de la Plana', $stats['teachers']],
                ['Grupos de Teoría', $stats['theories']],
                ['Grupos de Práctica', $stats['practices']],
                ['Estudiantes Únicos', $stats['students']],
                ['Matrículas Totales', $stats['enrollments']],
            ]
        );
        $this->comment("Tiempo de procesamiento: {$duration} segundos.");

        return Command::SUCCESS;
    }
}