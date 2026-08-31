<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\PracticeGroup;
use App\Models\TheoryGroup;
use App\Models\User;
use App\Services\ReallocationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pruebas de Reorganización y División de Grupos UNS
 * Desarrollado por: Angel Rojas (Tech Lead)
 */
class ReallocationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ReallocationService $service;
    protected User $executor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ReallocationService::class);
        $this->executor = User::create([
            'name' => 'Angel Rojas (Delegado)',
            'code' => '0202114001',
            'email' => 'arojas@uns.edu.pe',
            'password' => bcrypt('secret'),
            'role' => 'delegado',
        ]);
    }

    /**
     * Helper para matricular N estudiantes
     */
    protected function enrollStudents(Course $course, PracticeGroup $group, int $count, int $startId = 1): void
    {
        $baseTime = Carbon::create(2026, 8, 24, 0, 1, 0);

        for ($i = 0; $i < $count; $i++) {
            $num = $startId + $i;
            $u = User::create([
                'name' => "Estudiante {$num}",
                'code' => sprintf('2024140%03d', $num),
                'email' => "estudiante_{$num}@uns.edu.pe",
                'password' => bcrypt('123'),
                'role' => 'estudiante',
            ]);

            Enrollment::create([
                'user_id' => $u->id,
                'course_id' => $course->id,
                'practice_group_id' => $group->id,
                'has_laptop' => false,
                'teacher_authorized' => false,
                'enrolled_at' => (clone $baseTime)->addMinutes($num),
                'status' => 'original',
            ]);
        }
    }

    /**
     * Prueba 1: Si hay menos de 60 matriculados, la división es rechazada
     */
    public function test_split_theory_groups_fails_if_under_60_students()
    {
        $course = Course::create(['code_course' => 'IF-301', 'name' => 'Sistemas II', 'semester' => '2026-II']);
        $t1 = TheoryGroup::create(['course_id' => $course->id, 'name' => 'Teoría 1']);
        $gA = PracticeGroup::create(['theory_group_id' => $t1->id, 'code' => 'P1A']);
        $gB = PracticeGroup::create(['theory_group_id' => $t1->id, 'code' => 'P1B']);

        // Matricular 59 alumnos (< 60)
        $this->enrollStudents($course, $gA, 30, 1);
        $this->enrollStudents($course, $gB, 29, 31);

        $res = $this->service->splitTheoryGroups($course, $this->executor);

        $this->assertFalse($res['success']);
        $this->assertStringContainsString('60 o más alumnos', $res['message']);
    }

    /**
     * Prueba 2: Con 60 o más alumnos y 4 prácticas, se divide en 2 y 2 (P1A, P1B en T1; P2A, P2B en T2)
     */
    public function test_split_theory_groups_4_practices()
    {
        $course = Course::create(['code_course' => 'IF-301', 'name' => 'Sistemas II', 'semester' => '2026-II']);
        $t1 = TheoryGroup::create(['course_id' => $course->id, 'name' => 'Teoría 1']);

        $gA = PracticeGroup::create(['theory_group_id' => $t1->id, 'code' => 'P1A']);
        $gB = PracticeGroup::create(['theory_group_id' => $t1->id, 'code' => 'P1B']);
        $gC = PracticeGroup::create(['theory_group_id' => $t1->id, 'code' => 'P1C']);
        $gD = PracticeGroup::create(['theory_group_id' => $t1->id, 'code' => 'P1D']);

        // Matricular 64 alumnos (ejemplo real dado por el usuario)
        $this->enrollStudents($course, $gA, 16, 1);
        $this->enrollStudents($course, $gB, 16, 17);
        $this->enrollStudents($course, $gC, 16, 33);
        $this->enrollStudents($course, $gD, 16, 49);

        $res = $this->service->splitTheoryGroups($course, $this->executor);

        $this->assertTrue($res['success']);
        $this->assertEquals(2, $res['migrated_groups_count']);
        $this->assertEquals(2, $res['staying_groups_count']);
        $this->assertEquals(32, $res['migrated_students']);
        $this->assertNotNull($res['batch_id']);

        $gA->refresh(); $gB->refresh(); $gC->refresh(); $gD->refresh();

        $t2 = TheoryGroup::where('course_id', $course->id)->where('name', 'Teoría 2')->first();
        $this->assertNotNull($t2);

        // P1A y P1B se mantienen en Teoría 1
        $this->assertEquals($t1->id, $gA->theory_group_id);
        $this->assertEquals('P1A', $gA->code);
        $this->assertEquals($t1->id, $gB->theory_group_id);
        $this->assertEquals('P1B', $gB->code);

        // P1C y P1D migran a Teoría 2 reiniciando contador a P2A y P2B
        $this->assertEquals($t2->id, $gC->theory_group_id);
        $this->assertEquals('P2A', $gC->code);
        $this->assertEquals($t2->id, $gD->theory_group_id);
        $this->assertEquals('P2B', $gD->code);

        // Todos los alumnos de P2A y P2B fueron marcados como 'reasignado'
        $this->assertEquals(32, Enrollment::where('status', 'reasignado')->count());
    }

    /**
     * Prueba 3: Con 70 alumnos y 5 prácticas, 3 quedan en T1 (P1A, P1B, P1C) y 2 migran a T2 (P2A, P2B)
     */
    public function test_split_theory_groups_5_practices()
    {
        $course = Course::create(['code_course' => 'IF-301', 'name' => 'Sistemas II', 'semester' => '2026-II']);
        $t1 = TheoryGroup::create(['course_id' => $course->id, 'name' => 'Teoría 1']);

        $gA = PracticeGroup::create(['theory_group_id' => $t1->id, 'code' => 'P1A']);
        $gB = PracticeGroup::create(['theory_group_id' => $t1->id, 'code' => 'P1B']);
        $gC = PracticeGroup::create(['theory_group_id' => $t1->id, 'code' => 'P1C']);
        $gD = PracticeGroup::create(['theory_group_id' => $t1->id, 'code' => 'P1D']);
        $gE = PracticeGroup::create(['theory_group_id' => $t1->id, 'code' => 'P1E']);

        // Matricular 70 alumnos (ejemplo real dado por el usuario)
        $this->enrollStudents($course, $gA, 14, 1);
        $this->enrollStudents($course, $gB, 14, 15);
        $this->enrollStudents($course, $gC, 14, 29);
        $this->enrollStudents($course, $gD, 14, 43);
        $this->enrollStudents($course, $gE, 14, 57);

        $res = $this->service->splitTheoryGroups($course, $this->executor);

        $this->assertTrue($res['success']);
        $this->assertEquals(3, $res['staying_groups_count']);
        $this->assertEquals(2, $res['migrated_groups_count']);

        $gA->refresh(); $gB->refresh(); $gC->refresh(); $gD->refresh(); $gE->refresh();

        $t2 = TheoryGroup::where('course_id', $course->id)->where('name', 'Teoría 2')->first();

        // 3 quedan en Teoría 1: P1A, P1B, P1C
        $this->assertEquals($t1->id, $gA->theory_group_id);
        $this->assertEquals('P1A', $gA->code);
        $this->assertEquals($t1->id, $gB->theory_group_id);
        $this->assertEquals('P1B', $gB->code);
        $this->assertEquals($t1->id, $gC->theory_group_id);
        $this->assertEquals('P1C', $gC->code);

        // 2 pasan a Teoría 2 reiniciando contador: P2A y P2B
        $this->assertEquals($t2->id, $gD->theory_group_id);
        $this->assertEquals('P2A', $gD->code);
        $this->assertEquals($t2->id, $gE->theory_group_id);
        $this->assertEquals('P2B', $gE->code);
    }

    /**
     * Prueba 4: El delegado puede mover manualmente alumnos particulares entre prácticas
     */
    public function test_delegate_can_move_student_manually()
    {
        $course = Course::create(['code_course' => 'IF-301', 'name' => 'Sistemas II', 'semester' => '2026-II']);
        $t1 = TheoryGroup::create(['course_id' => $course->id, 'name' => 'Teoría 1']);
        $gA = PracticeGroup::create(['theory_group_id' => $t1->id, 'code' => 'P1A']);
        $gB = PracticeGroup::create(['theory_group_id' => $t1->id, 'code' => 'P1B']);

        $student = User::create(['name' => 'Juan Perez', 'email' => 'juan@uns.edu.pe', 'password' => '123']);
        $enrollment = Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'practice_group_id' => $gA->id,
            'has_laptop' => false,
            'teacher_authorized' => false,
            'enrolled_at' => now(),
            'status' => 'original',
        ]);

        // Delegado reasigna manualmente a Juan Perez a P1B por petición directa
        $this->service->moveStudentManually($enrollment, $gB, $this->executor);

        $enrollment->refresh();
        $this->assertEquals($gB->id, $enrollment->practice_group_id);
        $this->assertEquals('reasignado', $enrollment->status);

        // Se registró en auditoría
        $log = AuditLog::where('enrollment_id', $enrollment->id)->first();
        $this->assertNotNull($log);
        $this->assertEquals('manual_move', $log->action_type);
    }

    /**
     * Prueba 5: El delegado puede cambiar los toggles de laptop y autorización docente
     */
    public function test_delegate_can_toggle_laptop_and_teacher_auth()
    {
        $course = Course::create(['code_course' => 'IF-301', 'name' => 'Sistemas II', 'semester' => '2026-II']);
        $t1 = TheoryGroup::create(['course_id' => $course->id, 'name' => 'Teoría 1']);
        $gA = PracticeGroup::create(['theory_group_id' => $t1->id, 'code' => 'P1A']);

        $student = User::create(['name' => 'Maria Silva', 'email' => 'maria@uns.edu.pe', 'password' => '123']);
        $enrollment = Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'practice_group_id' => $gA->id,
            'has_laptop' => false,
            'teacher_authorized' => false,
            'enrolled_at' => now(),
            'status' => 'original',
        ]);

        // Toggle laptop
        $this->service->toggleLaptop($enrollment, true, $this->executor);
        $enrollment->refresh();
        $this->assertTrue($enrollment->has_laptop);

        // Toggle autorización docente
        $this->service->toggleTeacherAuth($enrollment, true, $this->executor);
        $enrollment->refresh();
        $this->assertTrue($enrollment->teacher_authorized);
    }
}
