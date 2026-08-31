<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\PracticeGroup;
use App\Models\TheoryGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SplitSimulationTest extends TestCase
{
    use RefreshDatabase;

    public function test_simulation_dry_run_returns_partition_and_student_preview_without_altering_database(): void
    {
        $course = Course::create([
            'code_course' => '1411-0008',
            'name' => 'CALCULO INTEGRAL',
            'cycle' => 'SEGUNDO CICLO',
            'semester' => '2026-I',
        ]);

        $theory = TheoryGroup::create([
            'course_id' => $course->id,
            'name' => 'Teoría 1',
        ]);

        $p1 = PracticeGroup::create(['theory_group_id' => $theory->id, 'code' => 'P1A', 'base_capacity' => 15]);
        $p2 = PracticeGroup::create(['theory_group_id' => $theory->id, 'code' => 'P1B', 'base_capacity' => 15]);
        $p3 = PracticeGroup::create(['theory_group_id' => $theory->id, 'code' => 'P1C', 'base_capacity' => 15]);
        $p4 = PracticeGroup::create(['theory_group_id' => $theory->id, 'code' => 'P1D', 'base_capacity' => 15]);

        $groups = [$p1, $p2, $p3, $p4];

        // Crear 64 estudiantes (16 por grupo)
        for ($i = 1; $i <= 64; $i++) {
            $user = User::create([
                'name' => "ESTUDIANTE {$i}",
                'code' => sprintf("02026140%02d", $i),
                'email' => "alumno{$i}@uns.edu.pe",
                'role' => 'estudiante',
                'password' => bcrypt('secret'),
            ]);

            $group = $groups[($i - 1) % 4];

            Enrollment::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'practice_group_id' => $group->id,
                'status' => 'original',
                'enrolled_at' => now(),
            ]);
        }

        $response = $this->getJson(route('courses.simulate-split', $course));

        $response->assertStatus(200);
        $response->assertJson([
            'can_split' => true,
            'total_enrolled' => 64,
            'total_groups' => 4,
            'stay_count' => 2,
            'migrating_count' => 2,
        ]);

        $data = $response->json();
        $this->assertCount(2, $data['t1_preview']);
        $this->assertCount(2, $data['t2_preview']);
        $this->assertCount(32, $data['migrating_students']); // P1C y P1D = 16 + 16 = 32

        // Verificar que la base de datos no sufrió modificaciones (Dry Run puro)
        $this->assertEquals(1, $course->theoryGroups()->count());
        $this->assertEquals(4, PracticeGroup::where('theory_group_id', $theory->id)->count());
        $this->assertEquals(64, Enrollment::where('status', 'original')->count());
    }

    public function test_simulation_dry_run_reports_when_course_has_under_60_students(): void
    {
        $course = Course::create([
            'code_course' => '1411-0008',
            'name' => 'CALCULO INTEGRAL',
            'cycle' => 'SEGUNDO CICLO',
            'semester' => '2026-I',
        ]);

        $theory = TheoryGroup::create([
            'course_id' => $course->id,
            'name' => 'Teoría 1',
        ]);

        PracticeGroup::create(['theory_group_id' => $theory->id, 'code' => 'P1A']);
        PracticeGroup::create(['theory_group_id' => $theory->id, 'code' => 'P1B']);

        $response = $this->getJson(route('courses.simulate-split', $course));

        $response->assertStatus(200);
        $response->assertJson([
            'can_split' => false,
            'total_enrolled' => 0,
        ]);
        $this->assertStringContainsString('60 alumnos', $response->json('message'));
    }
}