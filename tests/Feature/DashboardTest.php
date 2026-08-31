<?php

namespace Tests\Feature;

use App\Http\Controllers\DashboardController;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\PracticeGroup;
use App\Models\Teacher;
use App\Models\TheoryGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_extract_promo_helper_works_correctly(): void
    {
        $this->assertEquals('Promo 2026', DashboardController::extractPromo('0202614001'));
        $this->assertEquals('Promo 2025', DashboardController::extractPromo('0202514050'));
        $this->assertEquals('Sin Código', DashboardController::extractPromo(null));
        $this->assertEquals('Otras Promociones', DashboardController::extractPromo('XYZ99'));
    }

    public function test_dashboard_page_loads_successfully_with_data(): void
    {
        $teacher = Teacher::create([
            'name' => 'ING. BORJA ROSALES WHISTON',
            'email' => 'wborja@uns.edu.pe',
            'department' => 'DAISI',
            'condition' => 'Nombrado Principal'
        ]);

        $course = Course::create([
            'code_course' => '1411-0008',
            'name' => 'CALCULO INTEGRAL',
            'cycle' => 'SEGUNDO CICLO',
            'semester' => '2026-I'
        ]);

        $theory = TheoryGroup::create([
            'course_id' => $course->id,
            'name' => 'Teoría 1',
            'teacher_id' => $teacher->id
        ]);

        $practice = PracticeGroup::create([
            'theory_group_id' => $theory->id,
            'code' => 'P1A',
            'base_capacity' => 15,
            'teacher_id' => $teacher->id
        ]);

        $student2026 = User::create([
            'name' => 'JUAN PEREZ',
            'code' => '0202614001',
            'email' => 'juan@uns.edu.pe',
            'role' => 'estudiante',
            'password' => bcrypt('secret')
        ]);

        $student2025 = User::create([
            'name' => 'CARLOS REPITENTE',
            'code' => '0202514099',
            'email' => 'carlos@uns.edu.pe',
            'role' => 'estudiante',
            'password' => bcrypt('secret')
        ]);

        Enrollment::create([
            'user_id' => $student2026->id,
            'course_id' => $course->id,
            'practice_group_id' => $practice->id,
            'has_laptop' => true,
            'status' => 'original',
            'enrolled_at' => now()
        ]);

        Enrollment::create([
            'user_id' => $student2025->id,
            'course_id' => $course->id,
            'practice_group_id' => $practice->id,
            'has_laptop' => false,
            'status' => 'original',
            'enrolled_at' => now()
        ]);

        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
        $response->assertViewHas('totalStudents', 2);
        $response->assertViewHas('totalEnrollments', 2);
        $response->assertViewHas('totalLaptops', 1);
        $response->assertSee('Promo 2025');
        $response->assertSee('Promo 2026');
    }

    public function test_dashboard_can_filter_by_specific_course(): void
    {
        $course1 = Course::create([
            'code_course' => '1411-0008',
            'name' => 'CALCULO INTEGRAL',
            'cycle' => 'SEGUNDO CICLO',
            'semester' => '2026-I'
        ]);

        $course2 = Course::create([
            'code_course' => '1411-0015',
            'name' => 'BASE DE DATOS I',
            'cycle' => 'CUARTO CICLO',
            'semester' => '2026-I'
        ]);

        $response = $this->get(route('dashboard', ['course_id' => $course1->id]));
        $response->assertStatus(200);
        $response->assertViewHas('selectedCourse');
        $this->assertEquals($course1->id, $response->viewData('selectedCourse')->id);
    }

    public function test_course_show_displays_promo_distribution(): void
    {
        $course = Course::create([
            'code_course' => '1411-0008',
            'name' => 'CALCULO INTEGRAL',
            'cycle' => 'SEGUNDO CICLO',
            'semester' => '2026-I'
        ]);

        $theory = TheoryGroup::create([
            'course_id' => $course->id,
            'name' => 'Teoría 1'
        ]);

        $practice = PracticeGroup::create([
            'theory_group_id' => $theory->id,
            'code' => 'P1A',
            'base_capacity' => 15
        ]);

        $student = User::create([
            'name' => 'ALEX PROMO 2026',
            'code' => '0202614010',
            'email' => 'alex@uns.edu.pe',
            'role' => 'estudiante',
            'password' => bcrypt('secret')
        ]);

        Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'practice_group_id' => $practice->id,
            'status' => 'original',
            'enrolled_at' => now()
        ]);

        $response = $this->get(route('courses.show', $course));
        $response->assertStatus(200);
        $response->assertViewHas('coursePromoLabels');
        $response->assertViewHas('coursePromoData');
        $response->assertSee('Distribución por Promoción de Ingreso');
        $response->assertSee('Promo 2026');
    }
}