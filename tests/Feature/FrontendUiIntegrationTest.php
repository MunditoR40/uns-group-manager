<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\PracticeGroup;
use App\Models\TheoryGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FrontendUiIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected Course $course;
    protected TheoryGroup $theoryGroup;
    protected PracticeGroup $group1;
    protected PracticeGroup $group2;
    protected User $student;
    protected Enrollment $enrollment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->course = Course::create([
            'code_course' => 'IF-301',
            'name' => 'Base de Datos I',
            'semester' => '2026-II',
        ]);

        $this->theoryGroup = TheoryGroup::create([
            'course_id' => $this->course->id,
            'name' => 'Teoría 1',
        ]);

        $this->group1 = PracticeGroup::create([
            'theory_group_id' => $this->theoryGroup->id,
            'code' => 'P1A',
            'base_capacity' => 15,
            'schedule' => 'Lunes 08:00-10:00',
        ]);

        $this->group2 = PracticeGroup::create([
            'theory_group_id' => $this->theoryGroup->id,
            'code' => 'P1B',
            'base_capacity' => 15,
            'schedule' => 'Lunes 10:00-12:00',
        ]);

        $this->student = User::create([
            'name' => 'Ana Estudiante',
            'code' => '0202514002',
            'email' => 'ana@uns.edu.pe',
            'password' => bcrypt('secret'),
            'role' => 'estudiante',
        ]);

        $this->enrollment = Enrollment::create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'practice_group_id' => $this->group1->id,
            'status' => 'original',
            'enrolled_at' => now(),
            'has_laptop' => false,
            'teacher_authorized' => false,
        ]);
    }

    #[Test]
    public function catalogo_de_cursos_responde_correctamente()
    {
        $response = $this->get('/courses');
        $response->assertStatus(200);
        $response->assertSee('Base de Datos I');
        $response->assertSee('IF-301');
    }

    #[Test]
    public function panel_del_curso_responde_y_muestra_aforos_y_estudiante()
    {
        $response = $this->get('/courses/' . $this->course->id);
        $response->assertStatus(200);
        $response->assertSee('Ana Estudiante');
        $response->assertSee('P1A');
        $response->assertSee('IF-301');
    }

    #[Test]
    public function toggle_laptop_vua_ajax_actualiza_y_registra_auditoria()
    {
        $response = $this->patchJson('/enrollments/' . $this->enrollment->id . '/toggle', [
            'field' => 'has_laptop',
            'value' => true,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->enrollment->refresh();
        $this->assertTrue($this->enrollment->has_laptop);

        $this->assertDatabaseHas('audit_logs', [
            'enrollment_id' => $this->enrollment->id,
            'action_type' => 'laptop_toggle',
            'is_reverted' => false,
        ]);
    }

    #[Test]
    public function reasignacion_manual_mueve_alumno_a_nuevo_grupo_y_audita()
    {
        $response = $this->post('/enrollments/' . $this->enrollment->id . '/move-group', [
            'new_practice_group_id' => $this->group2->id,
        ]);

        $response->assertRedirect();

        $this->enrollment->refresh();
        $this->assertEquals($this->group2->id, $this->enrollment->practice_group_id);
        $this->assertEquals('reasignado', $this->enrollment->status);

        $this->assertDatabaseHas('audit_logs', [
            'enrollment_id' => $this->enrollment->id,
            'action_type' => 'manual_move',
            'is_reverted' => false,
        ]);
    }
}