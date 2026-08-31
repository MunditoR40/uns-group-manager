<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Teacher;
use App\Models\User;
use App\Services\ReallocationService;
use App\Services\SyntheticDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_synthetic_data_generation_creates_complete_anonymized_environment(): void
    {
        $service = new SyntheticDataService();
        $stats = $service->generate();

        $this->assertEquals(26, $stats['courses']);
        $this->assertEquals(20, $stats['teachers']);
        $this->assertEquals(280, $stats['students']);
        $this->assertGreaterThanOrEqual(1300, $stats['enrollments']);

        // Verificar que no queden nombres en blanco o corruptos
        $sampleStudent = User::where('role', 'estudiante')->first();
        $this->assertNotNull($sampleStudent);
        $this->assertNotEmpty($sampleStudent->name);
        $this->assertMatchesRegularExpression('/^0202\d{7}$/', $sampleStudent->code);
        $this->assertStringEndsWith('@uns.edu.pe', $sampleStudent->email);

        // Verificar que exista al menos un delegado
        $this->assertGreaterThanOrEqual(1, User::where('role', 'delegado')->count());
    }

    public function test_demo_data_supports_theory_split_simulation_and_reallocation(): void
    {
        $service = new SyntheticDataService();
        $service->generate();

        $course = Course::where('name', 'APLICACIONES DISTRIBUIDAS I')->first();
        $this->assertNotNull($course);
        $this->assertGreaterThanOrEqual(60, $course->enrollments()->count());

        $reallocationService = app(ReallocationService::class);
        $sim = $reallocationService->simulateSplit($course);

        $this->assertTrue($sim['can_split']);
        $this->assertCount(2, $sim['t1_preview']);
        $this->assertCount(2, $sim['t2_preview']);
        $this->assertNotEmpty($sim['migrating_students']);
    }

    public function test_all_major_views_and_exports_render_with_demo_data(): void
    {
        $service = new SyntheticDataService();
        $service->generate();

        // 1. Dashboard
        $respDashboard = $this->get(route('dashboard'));
        $respDashboard->assertStatus(200);
        $respDashboard->assertSee('Dashboard y Métricas');

        // 2. Catálogo de Cursos
        $respCourses = $this->get(route('courses.index'));
        $respCourses->assertStatus(200);
        $respCourses->assertSee('Asignaturas Registradas');

        // 3. Padrón de Estudiantes
        $respStudents = $this->get(route('students.index'));
        $respStudents->assertStatus(200);
        $respStudents->assertSee('Padrón de Estudiantes');

        // 4. Exportación Excel General
        $respExcel = $this->get(route('exports.enrollments.excel'));
        $respExcel->assertStatus(200);
    }
}