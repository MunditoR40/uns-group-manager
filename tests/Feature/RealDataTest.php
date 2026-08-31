<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Teacher;
use App\Models\User;
use App\Services\ReallocationService;
use App\Services\RealDataImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RealDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_real_data_import_service_loads_official_siigaa_dataset(): void
    {
        $realDataPath = 'D:/Proyectos/data_real';
        if (!is_dir($realDataPath)) {
            $this->markTestSkipped("Directorio $realDataPath no encontrado.");
        }

        $service = new RealDataImportService();
        $stats = $service->import($realDataPath, true);

        // Verificaciones cuantitativas del padrón oficial UNS
        $this->assertEquals(26, $stats['courses'], 'Deben existir 26 asignaturas reales.');
        $this->assertGreaterThanOrEqual(20, $stats['teachers'], 'Deben existir al menos 20 docentes reales.');
        $this->assertGreaterThanOrEqual(250, $stats['students'], 'Deben existir más de 250 estudiantes únicos.');
        $this->assertGreaterThanOrEqual(1400, $stats['enrollments'], 'Deben existir más de 1400 matrículas registradas.');

        // Verificaciones cualitativas de cursos y docentes emblemáticos
        $calculo = Course::where('code_course', '1411-0008')->first();
        $this->assertNotNull($calculo);
        $this->assertEquals('CALCULO INTEGRAL', $calculo->name);
        $this->assertEquals('II CICLO', $calculo->cycle);

        $agentes = Course::where('code_course', '1411-0048')->first();
        $this->assertNotNull($agentes);
        $this->assertEquals('AGENTES INTELIGENTES', $agentes->name);
        $this->assertEquals('VIII CICLO', $agentes->cycle);

        $ingBorja = Teacher::where('name', 'like', '%BORJA REYNA WHISTON%')->first();
        $this->assertNotNull($ingBorja, 'El Ing. Whiston Borja debe figurar en la plana docente.');

        // Comprobación de rol de delegado
        $this->assertGreaterThan(0, User::where('role', 'delegado')->count());
    }

    public function test_real_course_simulation_on_high_enrollment_course(): void
    {
        $realDataPath = 'D:/Proyectos/data_real';
        if (!is_dir($realDataPath)) {
            $this->markTestSkipped("Directorio $realDataPath no encontrado.");
        }

        $service = new RealDataImportService();
        $service->import($realDataPath, true);

        // Cursos con alta población estudiantil (ej: APLICACIONES DISTRIBUIDAS I con 75 alumnos)
        $course = Course::where('name', 'like', '%APLICACIONES DISTRIBUIDAS%')->first();
        $this->assertNotNull($course);
        $this->assertGreaterThanOrEqual(60, $course->enrollments()->count());

        $reallocationService = app(ReallocationService::class);
        $simulation = $reallocationService->simulateSplit($course);

        $this->assertTrue($simulation['can_split'], 'El curso con más de 60 alumnos debe ser elegible para división.');
        $this->assertGreaterThan(0, count($simulation['t1_preview']));
        $this->assertGreaterThan(0, count($simulation['t2_preview']));
        $this->assertNotEmpty($simulation['migrating_students'], 'Debe generar el padrón nominal de estudiantes a transferir.');

        // Verificar que el endpoint de simulación responde HTTP 200 con la misma data
        $response = $this->getJson(route('courses.simulate-split', $course));
        $response->assertStatus(200);
        $response->assertJson(['can_split' => true]);
    }

    public function test_real_dashboard_renders_with_complete_university_metrics(): void
    {
        $realDataPath = 'D:/Proyectos/data_real';
        if (!is_dir($realDataPath)) {
            $this->markTestSkipped("Directorio $realDataPath no encontrado.");
        }

        $service = new RealDataImportService();
        $service->import($realDataPath, true);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertViewHas('totalStudents', 277);
        $response->assertViewHas('totalEnrollments', 1427);
        $response->assertSee('CALCULO INTEGRAL');
        $response->assertSee('AGENTES INTELIGENTES');
    }
}