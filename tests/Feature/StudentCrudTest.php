<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\PracticeGroup;
use App\Models\TheoryGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_students_index_renders_with_paginated_list_and_stats(): void
    {
        $st1 = User::create([
            'name' => 'JUAN PEREZ SILVA',
            'code' => '0202614001',
            'email' => 'jperez@uns.edu.pe',
            'role' => 'estudiante',
            'password' => bcrypt('secret'),
        ]);

        $st2 = User::create([
            'name' => 'MARIA LOPEZ DIAZ',
            'code' => '0202614002',
            'email' => 'mlopez@uns.edu.pe',
            'role' => 'delegado',
            'password' => bcrypt('secret'),
        ]);

        $response = $this->get(route('students.index'));

        $response->assertStatus(200);
        $response->assertSee('Padrón de Estudiantes');
        $response->assertSee('JUAN PEREZ SILVA');
        $response->assertSee('MARIA LOPEZ DIAZ');
        $response->assertSee('Delegado');
    }

    public function test_students_search_by_name_or_code(): void
    {
        User::create([
            'name' => 'CARLOS ALVAREZ',
            'code' => '0202614010',
            'email' => 'calvarez@uns.edu.pe',
            'role' => 'estudiante',
            'password' => bcrypt('secret'),
        ]);

        User::create([
            'name' => 'PEDRO BENITEZ',
            'code' => '0202614020',
            'email' => 'pbenitez@uns.edu.pe',
            'role' => 'estudiante',
            'password' => bcrypt('secret'),
        ]);

        $response = $this->get(route('students.index', ['search' => 'ALVAREZ']));
        $response->assertStatus(200);
        $response->assertSee('CARLOS ALVAREZ');
        $response->assertDontSee('PEDRO BENITEZ');
    }

    public function test_student_edit_screen_loads_student_information(): void
    {
        $student = User::create([
            'name' => 'ANA GOMEZ',
            'code' => '0202614030',
            'email' => 'agomez@uns.edu.pe',
            'role' => 'estudiante',
            'password' => bcrypt('secret'),
        ]);

        $response = $this->get(route('students.edit', $student->id));

        $response->assertStatus(200);
        $response->assertSee('Editar Datos del Estudiante');
        $response->assertSee('ANA GOMEZ');
        $response->assertSee('0202614030');
    }

    public function test_student_update_persists_changes_successfully(): void
    {
        $student = User::create([
            'name' => 'LUCIA MENDOZA',
            'code' => '0202614040',
            'email' => 'lmendoza@uns.edu.pe',
            'role' => 'estudiante',
            'password' => bcrypt('secret'),
        ]);

        $response = $this->put(route('students.update', $student->id), [
            'name' => 'LUCIA MENDOZA VARGAS',
            'code' => '0202614040',
            'email' => 'lucia.mendoza@uns.edu.pe',
            'role' => 'delegado',
        ]);

        $response->assertRedirect(route('students.index'));
        $response->assertSessionHas('success');

        $student->refresh();
        $this->assertEquals('LUCIA MENDOZA VARGAS', $student->name);
        $this->assertEquals('lucia.mendoza@uns.edu.pe', $student->email);
        $this->assertEquals('delegado', $student->role);
    }

    public function test_toggle_delegate_status(): void
    {
        $student = User::create([
            'name' => 'MIGUEL ZAVALETA',
            'code' => '0202614050',
            'email' => 'mzavaleta@uns.edu.pe',
            'role' => 'estudiante',
            'password' => bcrypt('secret'),
        ]);

        $this->post(route('students.toggle-delegate', $student->id));
        $student->refresh();
        $this->assertEquals('delegado', $student->role);

        $this->post(route('students.toggle-delegate', $student->id));
        $student->refresh();
        $this->assertEquals('estudiante', $student->role);
    }
}