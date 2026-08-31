<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\PracticeGroup;
use App\Models\Teacher;
use App\Models\TheoryGroup;
use App\Models\User;
use App\Services\TeacherAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TeacherAndCourseCrudTest extends TestCase
{
    use RefreshDatabase;

    protected Teacher $teacher;
    protected Course $course;
    protected TheoryGroup $theory;
    protected PracticeGroup $practice;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacher = Teacher::create([
            'name' => 'ING. BORJA ROSALES WHISTON',
            'code' => 'DOC-103',
            'email' => 'wborja@uns.edu.pe',
            'department' => 'DAISI - Ingeniería de Sistemas e Informática',
            'condition' => 'Nombrado Principal',
        ]);

        $this->course = Course::create([
            'code_course' => '1411-0009',
            'name' => 'FUNDAMENTOS DE PROGRAMACION',
            'cycle' => 'II Ciclo',
            'semester' => '2026-02',
        ]);

        $this->theory = TheoryGroup::create([
            'course_id' => $this->course->id,
            'name' => 'Teoría 1',
            'teacher_id' => $this->teacher->id,
        ]);

        $this->practice = PracticeGroup::create([
            'theory_group_id' => $this->theory->id,
            'code' => 'P1A',
            'base_capacity' => 15,
            'teacher_id' => $this->teacher->id,
        ]);
    }

    #[Test]
    public function regla_uns_bloquea_que_un_docente_tenga_dos_teorias_en_un_mismo_ciclo()
    {
        $service = new TeacherAssignmentService();

        // Segundo curso en el MISMO ciclo (II Ciclo)
        $otroCursoSegundoCiclo = Course::create([
            'code_course' => '1411-0008',
            'name' => 'CALCULO INTEGRAL',
            'cycle' => 'II Ciclo',
            'semester' => '2026-02',
        ]);

        $check = $service->canAssignTheory($this->teacher, $otroCursoSegundoCiclo);
        $this->assertFalse($check['allowed']);
        $this->assertStringContainsString('no puede tener 2 teorías en un solo ciclo', $check['message']);

        // Pero en un ciclo DIFERENTE (IV Ciclo), sí puede tener teoría
        $cursoCuartoCiclo = Course::create([
            'code_course' => '1411-0019',
            'name' => 'PROGRAMACION I',
            'cycle' => 'IV Ciclo',
            'semester' => '2026-02',
        ]);

        $checkDiferente = $service->canAssignTheory($this->teacher, $cursoCuartoCiclo);
        $this->assertTrue($checkDiferente['allowed']);
    }

    #[Test]
    public function docente_puede_tener_multiples_practicas_para_completar_carga_lectiva()
    {
        $service = new TeacherAssignmentService();

        $p1b = PracticeGroup::create([
            'theory_group_id' => $this->theory->id,
            'code' => 'P1B',
            'base_capacity' => 15,
        ]);

        $check = $service->canAssignPractice($this->teacher, $p1b);
        $this->assertTrue($check['allowed']);
    }

    #[Test]
    public function crud_docentes_funciona_correctamente()
    {
        // 1. Listado
        $resIndex = $this->get('/teachers');
        $resIndex->assertStatus(200);
        $resIndex->assertSee('ING. BORJA ROSALES WHISTON');

        // 2. Registro
        $resStore = $this->post('/teachers', [
            'name' => 'DRA. CARMEN VEGA VELASQUEZ',
            'code' => 'DOC-106',
            'email' => 'cvega@uns.edu.pe',
            'department' => 'DAISI - Ingeniería de Sistemas e Informática',
            'condition' => 'Nombrado Principal',
        ]);
        $resStore->assertRedirect();
        $this->assertDatabaseHas('teachers', ['code' => 'DOC-106']);

        // 3. Edición
        $nuevoDocente = Teacher::where('code', 'DOC-106')->first();
        $resUpdate = $this->put('/teachers/' . $nuevoDocente->id, [
            'name' => 'DRA. CARMEN VEGA V.',
            'code' => 'DOC-106',
            'email' => 'cvega_actualizado@uns.edu.pe',
            'department' => 'DAISI - Ingeniería de Sistemas e Informática',
            'condition' => 'Nombrada Principal',
        ]);
        $resUpdate->assertRedirect();
        $this->assertDatabaseHas('teachers', ['email' => 'cvega_actualizado@uns.edu.pe']);

        // 4. Eliminación
        $resDelete = $this->delete('/teachers/' . $nuevoDocente->id);
        $resDelete->assertRedirect();
        $this->assertDatabaseMissing('teachers', ['id' => $nuevoDocente->id]);
    }

    #[Test]
    public function creacion_de_curso_desde_controlador_valida_regla_uns()
    {
        // Intento 1: Asignar a Borja a otro curso de II Ciclo (debe fallar por la regla UNS)
        $resFail = $this->post('/courses', [
            'code_course' => '1411-0099',
            'name' => 'CURSO CONFLICTO',
            'cycle' => 'II Ciclo',
            'semester' => '2026-02',
            'teacher_id' => $this->teacher->id,
            'base_capacity' => 15,
            'practice_groups_count' => 2,
        ]);

        $resFail->assertRedirect();
        $resFail->assertSessionHas('error');
        $this->assertDatabaseMissing('courses', ['code_course' => '1411-0099']);

        // Intento 2: Asignar a un docente libre
        $docenteLibre = Teacher::create([
            'name' => 'LIC. FIS. QUEVEDO HUERTA MARIO',
            'code' => 'DOC-102',
            'email' => 'mquevedo@uns.edu.pe',
            'department' => 'DAEF - Departamento de Física',
            'condition' => 'Nombrado Asociado',
        ]);

        $resSuccess = $this->post('/courses', [
            'code_course' => '1411-0007',
            'name' => 'FISICA I',
            'cycle' => 'II Ciclo',
            'semester' => '2026-02',
            'teacher_id' => $docenteLibre->id,
            'base_capacity' => 15,
            'practice_groups_count' => 3,
        ]);

        $resSuccess->assertRedirect();
        $this->assertDatabaseHas('courses', ['code_course' => '1411-0007']);
        $nuevoCurso = Course::where('code_course', '1411-0007')->first();
        $this->assertEquals(1, $nuevoCurso->theoryGroups()->count());
        $this->assertEquals(3, $nuevoCurso->practiceGroups()->count());
    }

    #[Test]
    public function exportacion_excel_por_curso_y_por_teoria_responde_correctamente()
    {
        // 1. Excel completo del curso
        $resCourseExcel = $this->get('/courses/' . $this->course->id . '/excel');
        $resCourseExcel->assertStatus(200);
        $this->assertTrue(
            str_contains($resCourseExcel->headers->get('content-disposition'), 'padron_completo_') ||
            str_contains($resCourseExcel->headers->get('content-type'), 'spreadsheet') ||
            str_contains($resCourseExcel->headers->get('content-type'), 'octet-stream')
        );

        // 2. Excel exclusivo de una teoría
        $resTheoryExcel = $this->get('/courses/' . $this->course->id . '/excel?theory_group_id=' . $this->theory->id);
        $resTheoryExcel->assertStatus(200);
        $this->assertTrue(
            str_contains($resTheoryExcel->headers->get('content-disposition'), 'teoria') ||
            str_contains($resTheoryExcel->headers->get('content-type'), 'spreadsheet') ||
            str_contains($resTheoryExcel->headers->get('content-type'), 'octet-stream')
        );
    }
}