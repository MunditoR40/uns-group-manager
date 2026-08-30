<?php

namespace Tests\Feature;

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
 * Pruebas Unitarias y de Integración para el ReallocationService
 * Desarrolladas por: Angel Rojas (Tech Lead)
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
     * Prueba Regla B: Cálculo dinámico de aforos flexibles (Base 15, Laptop 17, Docente 18)
     */
    public function test_calculate_effective_capacity_flexible_rules()
    {
        $course = Course::create(['code_course' => 'IF-301', 'name' => 'Sistemas II', 'semester' => '2026-II']);
        $theory = TheoryGroup::create(['course_id' => $course->id, 'name' => 'Teoría 1']);
        $group = PracticeGroup::create(['theory_group_id' => $theory->id, 'code' => 'P1A', 'base_capacity' => 15]);

        // 1. Sin laptops ni autorización -> Capacidad base = 15
        $this->assertEquals(15, $this->service->calculateEffectiveCapacity($group));
        $this->assertEquals(15, $group->effective_capacity);

        // 2. Con 1 laptop -> Capacidad = 16
        $s1 = User::create(['name' => 'Alumno 1', 'email' => 'a1@uns.edu.pe', 'password' => '123']);
        Enrollment::create([
            'user_id' => $s1->id,
            'course_id' => $course->id,
            'practice_group_id' => $group->id,
            'has_laptop' => true,
            'teacher_authorized' => false,
            'enrolled_at' => now(),
            'status' => 'original',
        ]);
        $group->load('enrollments');
        $this->assertEquals(16, $this->service->calculateEffectiveCapacity($group));

        // 3. Con 2 laptops -> Capacidad = 17 (Tope máximo por laptop)
        $s2 = User::create(['name' => 'Alumno 2', 'email' => 'a2@uns.edu.pe', 'password' => '123']);
        Enrollment::create([
            'user_id' => $s2->id,
            'course_id' => $course->id,
            'practice_group_id' => $group->id,
            'has_laptop' => true,
            'teacher_authorized' => false,
            'enrolled_at' => now(),
            'status' => 'original',
        ]);
        $group->load('enrollments');
        $this->assertEquals(17, $this->service->calculateEffectiveCapacity($group));

        // 4. Con 3 laptops -> Capacidad se mantiene en 17
        $s3 = User::create(['name' => 'Alumno 3', 'email' => 'a3@uns.edu.pe', 'password' => '123']);
        Enrollment::create([
            'user_id' => $s3->id,
            'course_id' => $course->id,
            'practice_group_id' => $group->id,
            'has_laptop' => true,
            'teacher_authorized' => false,
            'enrolled_at' => now(),
            'status' => 'original',
        ]);
        $group->load('enrollments');
        $this->assertEquals(17, $this->service->calculateEffectiveCapacity($group));

        // 5. Con autorización docente -> Capacidad se amplía a 18 (Tope absoluto)
        $s4 = User::create(['name' => 'Alumno 4', 'email' => 'a4@uns.edu.pe', 'password' => '123']);
        Enrollment::create([
            'user_id' => $s4->id,
            'course_id' => $course->id,
            'practice_group_id' => $group->id,
            'has_laptop' => false,
            'teacher_authorized' => true,
            'enrolled_at' => now(),
            'status' => 'original',
        ]);
        $group->load('enrollments');
        $this->assertEquals(18, $this->service->calculateEffectiveCapacity($group));
    }

    /**
     * Prueba Regla A: Caso 4 prácticas iniciales (P1A, P1B se quedan en T1; P1C->P2A, P1D->P2B en T2)
     */
    public function test_split_theory_groups_4_practices_case()
    {
        $course = Course::create(['code_course' => 'IF-301', 'name' => 'Sistemas II', 'semester' => '2026-II']);
        $t1 = TheoryGroup::create(['course_id' => $course->id, 'name' => 'Teoría 1']);

        $gA = PracticeGroup::create(['theory_group_id' => $t1->id, 'code' => 'P1A']);
        $gB = PracticeGroup::create(['theory_group_id' => $t1->id, 'code' => 'P1B']);
        $gC = PracticeGroup::create(['theory_group_id' => $t1->id, 'code' => 'P1C']);
        $gD = PracticeGroup::create(['theory_group_id' => $t1->id, 'code' => 'P1D']);

        $s = User::create(['name' => 'Estudiante C', 'email' => 'c@uns.edu.pe', 'password' => '123']);
        $enr = Enrollment::create([
            'user_id' => $s->id,
            'course_id' => $course->id,
            'practice_group_id' => $gC->id,
            'enrolled_at' => now(),
            'status' => 'original',
        ]);

        $res = $this->service->splitTheoryGroups($course, $this->executor);

        $this->assertTrue($res['success']);
        $this->assertNotNull($res['batch_id']);

        $gA->refresh(); $gB->refresh(); $gC->refresh(); $gD->refresh(); $enr->refresh();

        $t2 = TheoryGroup::where('course_id', $course->id)->where('name', 'Teoría 2')->first();
        $this->assertNotNull($t2);

        // P1A y P1B se quedan en T1
        $this->assertEquals($t1->id, $gA->theory_group_id);
        $this->assertEquals('P1A', $gA->code);
        $this->assertEquals($t1->id, $gB->theory_group_id);
        $this->assertEquals('P1B', $gB->code);

        // P1C -> P2A y P1D -> P2B migran a T2
        $this->assertEquals($t2->id, $gC->theory_group_id);
        $this->assertEquals('P2A', $gC->code);
        $this->assertEquals($t2->id, $gD->theory_group_id);
        $this->assertEquals('P2B', $gD->code);

        // El estudiante se actualizó a 'reasignado'
        $this->assertEquals('reasignado', $enr->status);
        $this->assertEquals('P2A', $enr->practiceGroup->code);
    }

    /**
     * Prueba Regla A: Caso 5 prácticas iniciales (P1A, P1B, P1C se quedan en T1; P1D->P2A, P1E->P2B en T2)
     */
    public function test_split_theory_groups_5_practices_case()
    {
        $course = Course::create(['code_course' => 'IF-301', 'name' => 'Sistemas II', 'semester' => '2026-II']);
        $t1 = TheoryGroup::create(['course_id' => $course->id, 'name' => 'Teoría 1']);

        $gA = PracticeGroup::create(['theory_group_id' => $t1->id, 'code' => 'P1A']);
        $gB = PracticeGroup::create(['theory_group_id' => $t1->id, 'code' => 'P1B']);
        $gC = PracticeGroup::create(['theory_group_id' => $t1->id, 'code' => 'P1C']);
        $gD = PracticeGroup::create(['theory_group_id' => $t1->id, 'code' => 'P1D']);
        $gE = PracticeGroup::create(['theory_group_id' => $t1->id, 'code' => 'P1E']);

        $res = $this->service->splitTheoryGroups($course, $this->executor);

        $this->assertTrue($res['success']);

        $gA->refresh(); $gB->refresh(); $gC->refresh(); $gD->refresh(); $gE->refresh();

        $t2 = TheoryGroup::where('course_id', $course->id)->where('name', 'Teoría 2')->first();

        // Primeras 3 se quedan en T1
        $this->assertEquals($t1->id, $gA->theory_group_id);
        $this->assertEquals($t1->id, $gB->theory_group_id);
        $this->assertEquals($t1->id, $gC->theory_group_id);

        // Últimas 2 pasan a T2 como P2A y P2B
        $this->assertEquals($t2->id, $gD->theory_group_id);
        $this->assertEquals('P2A', $gD->code);
        $this->assertEquals($t2->id, $gE->theory_group_id);
        $this->assertEquals('P2B', $gE->code);
    }

    /**
     * Prueba Regla C: Detección de cola de excedentes FIFO y balanceo hacia grupos con vacantes
     */
    public function test_fifo_overflow_queue_and_balancing()
    {
        $course = Course::create(['code_course' => 'IF-301', 'name' => 'Sistemas II', 'semester' => '2026-II']);
        $theory = TheoryGroup::create(['course_id' => $course->id, 'name' => 'Teoría 1']);

        $groupFull = PracticeGroup::create(['theory_group_id' => $theory->id, 'code' => 'P1A', 'base_capacity' => 15]);
        $groupVacant = PracticeGroup::create(['theory_group_id' => $theory->id, 'code' => 'P1B', 'base_capacity' => 15]);

        $baseTime = Carbon::create(2026, 8, 24, 0, 1, 0);

        // 17 alumnos en P1A (15 aforo base, 2 excedentes)
        for ($i = 0; $i < 17; $i++) {
            $u = User::create(['name' => "Estudiante A{$i}", 'email' => "a{$i}@uns.edu.pe", 'password' => '123']);
            Enrollment::create([
                'user_id' => $u->id,
                'course_id' => $course->id,
                'practice_group_id' => $groupFull->id,
                'has_laptop' => false,
                'teacher_authorized' => false,
                'enrolled_at' => (clone $baseTime)->addMinutes($i * 2),
                'status' => 'original',
            ]);
        }

        // 13 alumnos en P1B (2 vacantes disponibles)
        for ($i = 0; $i < 13; $i++) {
            $u = User::create(['name' => "Estudiante B{$i}", 'email' => "b{$i}@uns.edu.pe", 'password' => '123']);
            Enrollment::create([
                'user_id' => $u->id,
                'course_id' => $course->id,
                'practice_group_id' => $groupVacant->id,
                'has_laptop' => false,
                'teacher_authorized' => false,
                'enrolled_at' => (clone $baseTime)->addMinutes(100 + $i * 2),
                'status' => 'original',
            ]);
        }

        // 1. Verificar cola de excedentes detectada
        $data = $this->service->getOverflowAndVacancies($course);
        $this->assertCount(2, $data['overflow']);
        $this->assertEquals(2, $data['vacancies'][$groupVacant->id]['available_slots']);

        // 2. Ejecutar balanceo FIFO
        $balanceRes = $this->service->balanceOverflow($course, $this->executor);
        $this->assertTrue($balanceRes['success']);
        $this->assertEquals(2, $balanceRes['reallocated_count']);

        // 3. Ambos grupos deben quedar exactamente con 15 alumnos
        $this->assertEquals(15, $groupFull->enrollments()->count());
        $this->assertEquals(15, $groupVacant->enrollments()->count());
    }
}
