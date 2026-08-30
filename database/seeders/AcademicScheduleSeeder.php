<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\PracticeGroup;
use App\Models\TheoryGroup;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder de Carga Académica y Estudiantes UNS
 * Cursos y Horarios oficiales del SIIGAA (Semestre 2026-02)
 * Ciclo II (Promoción 2026) y Ciclo IV (Promoción 2025)
 */
class AcademicScheduleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Limpiar datos existentes manteniendo integridad
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Enrollment::truncate();
        PracticeGroup::truncate();
        TheoryGroup::truncate();
        Course::truncate();
        User::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $defaultPassword = Hash::make('password123');

        // =========================================================================
        // 2. GENERACIÓN ALFABÉTICA DE ESTUDIANTES PROMOCIÓN 2025 (Ciclo IV - 45 alumnos)
        // Lista estrictamente ordenada por apellidos para asignar el código institucional
        // Formato UNS: 0 + 2025 + 140 + orden_alfabetico (01 a 45)
        // =========================================================================
        $namesList2025 = [
            'AGUILAR BAZAN CARLOS DANIEL',
            'ALVA CASTILLO DIEGO ARMANDO',
            'ARANDA FIGUEROA LUIS ENRIQUE',
            'BAZAN MORALES JORGE LUIS',
            'CABALLERO DIAZ CRISTIAN ALEXIS',
            'CALDERON LOZANO BRYAN EDUARDO',
            'CARBAJAL MENDOZA DAVID DANIEL',
            'CARLIN JIMENEZ ROBERTO CARLOS',
            'CARRILLO CRUZ PEDRO CESAR',
            'CASTILLO RAMIREZ MANUEL ALEJANDRO',
            'CHAVEZ GUTIERREZ KEVIN ALEXANDER',
            'CORDOVA MORENO ANGEL GABRIEL',
            'CRUZ ZAVALETA RENATO ALONSO',
            'CUEVA PAREDES MARIO ANDRES',
            'DIAZ TELLO RICARDO FABRICIO',
            'ESPINOZA OLIVARES FRANCO SEBASTIAN',
            'FIGUEROA VARGAS JUAN CARLOS',
            'FLORES QUISPE HECTOR MARTIN',
            'GALLARDO HERRERA RAFAEL EDUARDO',
            'GARCIA SALINAS JOSE MIGUEL',
            'GOMEZ SILVA FERNANDO JAVIER',
            'GUTIERREZ VALVERDE MARCO ANTONIO',
            'HERRERA PEREDA ALONSO ESTEBAN',
            'IBANEZ SANCHEZ JULIO CESAR',
            'JIMENEZ VEGA GABRIEL OMAR',
            'LOZANO VERA EDUARDO DANIEL',
            'MEDINA VILLANUEVA CLAUDIO CESAR',
            'MENDOZA ARANDA FELIX ANDRES',
            'MIRANDA CABALLERO VICTOR HUGO',
            'MORALES CALDERON JAIME LUIS',
            'MORENO CHAVEZ ENRIQUE MANUEL',
            'NORABUENA DIAZ JOSUE DANIEL',
            'OLIVARES FLORES CESAR AUGUSTO',
            'ORTIZ GARCIA SERGIO PABAO',
            'PALACIOS HERRERA DANIEL EDUARDO',
            'PAREDES LOZANO PABLO EMILIO',
            'PEREDA MEDINA CARLOS ENRIQUE',
            // El estudiante en orden 38 es el Delegado General de la Promoción 2025
            'PEREZ ROJAS JUAN CARLOS',
            'PINEDO MIRANDA GUSTAVO ADOLFO',
            'QUISPE MORALES ELVIS CRISTIAN',
            'RAMIREZ OLIVARES WALTER MARTIN',
            'RODRIGUEZ PALACIOS JESUS ALBERTO',
            'SALINAS QUISPE GUILLERMO CESAR',
            'TELLO VEGA DARIO MARCELO',
            'ZAVALETA BAZAN SEBASTIAN OMAR',
        ];

        // Ordenar alfabéticamente de forma estricta
        sort($namesList2025);

        $students2025 = [];
        foreach ($namesList2025 as $index => $fullName) {
            $order = $index + 1; // 1 a 45
            $code = sprintf('02025140%02d', $order);

            // Delegado General de Ciclo IV: Juan Carlos Perez Rojas (según su posición alfabética real)
            $isDelegate = str_contains($fullName, 'PEREZ ROJAS JUAN CARLOS');
            $role = $isDelegate ? 'delegado' : 'estudiante';

            $students2025[$order] = User::create([
                'name' => $fullName . ($isDelegate ? ' (DELEGADO IV CICLO)' : ''),
                'code' => $code,
                'email' => "{$code}@uns.edu.pe",
                'password' => $defaultPassword,
                'role' => $role,
            ]);
        }

        // =========================================================================
        // 3. GENERACIÓN ALFABÉTICA DE ESTUDIANTES PROMOCIÓN 2026 (Ciclo II - 45 alumnos)
        // Formato UNS: 0 + 2026 + 140 + orden_alfabetico (01 a 45)
        // =========================================================================
        $namesList2026 = [
            'AGUILAR CORDOVA KEVIN BRYAN',
            'ALVA SANCHEZ CESAR JUNIOR',
            'ARANDA VALVERDE EDSON JAVIER',
            'BAZAN PAREDES MARTIN ALONSO',
            'CABALLERO CUEVA GIANCARLO ENRIQUE',
            'CALDERON FIGUEROA JORGE OMAR',
            'CARBAJAL ZAVALETA BRUNO MANUEL',
            'CARLIN HERRERA LEONARDO DANIEL',
            'CARRILLO VARGAS RODRIGO ANDRES',
            'CASTILLO GOMEZ AXEL JOSUE',
            'CHAVEZ LOZANO MARCO AURELIO',
            'CORDOVA TELLO SERGIO DANIEL',
            'CRUZ MEDINA ALEXIS RAFAEL',
            'CUEVA PINEDO LUIS GABRIEL',
            'DIAZ MENDOZA CARLOS ALBERTO',
            'ESPINOZA GARCIA SEBASTIAN OMAR',
            'FIGUEROA SILVA CHRISTIAN FABRICIO',
            'FLORES GUTIERREZ PIERO ALESSANDRO',
            'GALLARDO PEREDA JEAN PIERRE',
            'GARCIA VEGA CRISTOPHER DAVID',
            'GOMEZ MORALES FELIPE ANDRES',
            'GUTIERREZ OLIVARES EDUARDO LUIS',
            'HERRERA BAZAN GABRIEL ALEXANDER',
            'IBANEZ CHAVEZ DARIO FRANCISCO',
            'JIMENEZ FLORES JONATHAN DAVID',
            'LOZANO HERRERA VICTOR ENRIQUE',
            'MEDINA MORALES RAUL ESTEBAN',
            'MENDOZA SANCHEZ ANDRE FABRICIO',
            'MIRANDA VARGAS LUIS FERNANDO',
            'MORALES VEGA CESAR AUGUSTO',
            'MORENO ZAVALETA DIEGO ARMANDO',
            'NORABUENA PAREDES CARLOS ALONSO',
            'OLIVARES QUISPE JORGE LUIS',
            'ORTIZ RAMIREZ MARIO CESAR',
            'PALACIOS SALINAS GUILLERMO ENRIQUE',
            'PAREDES TELLO SERGIO ALBERTO',
            'PEREDA ALVA DANIEL ALEXANDER',
            'PEREZ CASTILLO BRYAN ANTHONY',
            'PINEDO DIAZ JOSUE ESTEBAN',
            'QUISPE GARCIA KEVIN OMAR',
            'RAMIREZ GUTIERREZ LUIS MIGUEL',
            'RODRIGUEZ LOZANO CARLOS DANIEL',
            // El estudiante en la posición alfabética 'Rojas' es el Delegado General de la Promoción 2026
            'ROJAS LEON ANGEL EDMUNDO',
            'ROSALES RAMIREZ RACHEL JARED',
            'ZAVALETA MORENO CESAR AUGUSTO',
        ];

        sort($namesList2026);

        $students2026 = [];
        foreach ($namesList2026 as $index => $fullName) {
            $order = $index + 1; // 1 a 45
            $code = sprintf('02026140%02d', $order);

            // Delegado General de Ciclo II: Angel Edmundo Rojas Leon (según su orden alfabético real)
            $isDelegate = str_contains($fullName, 'ROJAS LEON ANGEL EDMUNDO');
            $role = $isDelegate ? 'delegado' : 'estudiante';

            $students2026[$order] = User::create([
                'name' => $fullName . ($isDelegate ? ' (DELEGADO II CICLO)' : ''),
                'code' => $code,
                'email' => "{$code}@uns.edu.pe",
                'password' => $defaultPassword,
                'role' => $role,
            ]);
        }

        // =========================================================================
        // 4. CURSOS OFICIALES DEL SEGUNDO CICLO (Semestre 2026-02)
        // Tomados de la captura oficial del SIIGAA UNS
        // =========================================================================

        // --- CURSO 1: CALCULO INTEGRAL (1411-0008) ---
        // Este curso tiene alumnos regulares de 2026 + alumnos jalados de 2025 = 63 alumnos (> 60)
        $cursoCalculo = Course::create([
            'code_course' => '1411-0008',
            'name' => 'CALCULO INTEGRAL',
            'semester' => '2026-02',
        ]);
        $t1Calculo = TheoryGroup::create(['course_id' => $cursoCalculo->id, 'name' => 'Teoría 1']);
        
        $p1aCalculo = PracticeGroup::create([
            'theory_group_id' => $t1Calculo->id,
            'code' => 'P1A',
            'base_capacity' => 15,
            'schedule' => 'Jueves 09:00-11:00 | AULA A-02'
        ]);
        $p1bCalculo = PracticeGroup::create([
            'theory_group_id' => $t1Calculo->id,
            'code' => 'P1B',
            'base_capacity' => 15,
            'schedule' => 'Jueves 11:00-13:00 | AULA A-02'
        ]);
        $p1cCalculo = PracticeGroup::create([
            'theory_group_id' => $t1Calculo->id,
            'code' => 'P1C',
            'base_capacity' => 15,
            'schedule' => 'Viernes 09:00-11:00 | AULA A-03'
        ]);
        $p1dCalculo = PracticeGroup::create([
            'theory_group_id' => $t1Calculo->id,
            'code' => 'P1D',
            'base_capacity' => 15,
            'schedule' => 'Viernes 11:00-13:00 | AULA A-03'
        ]);

        // Matricular con excedentes iniciales:
        // P1A: 18 alumnos (3 excedentes)
        // P1B: 19 alumnos (4 excedentes)
        // P1C: 14 alumnos
        // P1D: 12 alumnos (alumnos jalados de base 2025)
        // Total = 63 alumnos matriculados
        $baseEnrollTime = Carbon::create(2026, 8, 24, 0, 1, 10);
        $studentCounter = 0;

        for ($i = 1; $i <= 18; $i++) {
            $studentCounter++;
            Enrollment::create([
                'user_id' => $students2026[$i]->id,
                'course_id' => $cursoCalculo->id,
                'practice_group_id' => $p1aCalculo->id,
                'has_laptop' => false,
                'teacher_authorized' => false,
                'enrolled_at' => (clone $baseEnrollTime)->addSeconds($studentCounter * 3),
                'status' => 'original',
            ]);
        }

        for ($i = 19; $i <= 37; $i++) {
            $studentCounter++;
            Enrollment::create([
                'user_id' => $students2026[$i]->id,
                'course_id' => $cursoCalculo->id,
                'practice_group_id' => $p1bCalculo->id,
                'has_laptop' => false,
                'teacher_authorized' => false,
                'enrolled_at' => (clone $baseEnrollTime)->addSeconds($studentCounter * 3),
                'status' => 'original',
            ]);
        }

        for ($i = 38; $i <= 45; $i++) {
            $studentCounter++;
            Enrollment::create([
                'user_id' => $students2026[$i]->id,
                'course_id' => $cursoCalculo->id,
                'practice_group_id' => $p1cCalculo->id,
                'has_laptop' => false,
                'teacher_authorized' => false,
                'enrolled_at' => (clone $baseEnrollTime)->addSeconds($studentCounter * 3),
                'status' => 'original',
            ]);
        }
        // Alumnos jalados de 2025 que van a P1C
        for ($i = 28; $i <= 33; $i++) {
            $studentCounter++;
            Enrollment::create([
                'user_id' => $students2025[$i]->id,
                'course_id' => $cursoCalculo->id,
                'practice_group_id' => $p1cCalculo->id,
                'has_laptop' => false,
                'teacher_authorized' => false,
                'enrolled_at' => (clone $baseEnrollTime)->addSeconds($studentCounter * 3),
                'status' => 'original',
            ]);
        }

        // Alumnos jalados de 2025 que van a P1D
        for ($i = 34; $i <= 45; $i++) {
            $studentCounter++;
            Enrollment::create([
                'user_id' => $students2025[$i]->id,
                'course_id' => $cursoCalculo->id,
                'practice_group_id' => $p1dCalculo->id,
                'has_laptop' => false,
                'teacher_authorized' => false,
                'enrolled_at' => (clone $baseEnrollTime)->addSeconds($studentCounter * 3),
                'status' => 'original',
            ]);
        }

        // --- CURSO 2: FISICA I (1411-0007) ---
        $cursoFisica = Course::create([
            'code_course' => '1411-0007',
            'name' => 'FISICA I',
            'semester' => '2026-02',
        ]);
        $t1Fisica = TheoryGroup::create(['course_id' => $cursoFisica->id, 'name' => 'Teoría 1']);
        $p1aFisica = PracticeGroup::create(['theory_group_id' => $t1Fisica->id, 'code' => 'P1A', 'base_capacity' => 15, 'schedule' => 'Viernes 07:00-09:00 | LAB FISICA']);
        $p1bFisica = PracticeGroup::create(['theory_group_id' => $t1Fisica->id, 'code' => 'P1B', 'base_capacity' => 15, 'schedule' => 'Viernes 09:00-11:00 | LAB FISICA']);
        $p1cFisica = PracticeGroup::create(['theory_group_id' => $t1Fisica->id, 'code' => 'P1C', 'base_capacity' => 15, 'schedule' => 'Viernes 11:00-13:00 | LAB FISICA']);

        foreach ($students2026 as $idx => $st) {
            $pg = ($idx <= 15) ? $p1aFisica : (($idx <= 30) ? $p1bFisica : $p1cFisica);
            Enrollment::create([
                'user_id' => $st->id,
                'course_id' => $cursoFisica->id,
                'practice_group_id' => $pg->id,
                'enrolled_at' => (clone $baseEnrollTime)->addMinutes($idx),
                'status' => 'original',
            ]);
        }

        // --- CURSO 3: FUNDAMENTOS DE PROGRAMACION (1411-0009) ---
        $cursoFundProg = Course::create([
            'code_course' => '1411-0009',
            'name' => 'FUNDAMENTOS DE PROGRAMACION',
            'semester' => '2026-02',
        ]);
        $t1FundProg = TheoryGroup::create(['course_id' => $cursoFundProg->id, 'name' => 'Teoría 1']);
        $p1aFund = PracticeGroup::create(['theory_group_id' => $t1FundProg->id, 'code' => 'P1A', 'base_capacity' => 15, 'schedule' => 'Miércoles 07:00-09:00 | LAB SIST 01']);
        $p1bFund = PracticeGroup::create(['theory_group_id' => $t1FundProg->id, 'code' => 'P1B', 'base_capacity' => 15, 'schedule' => 'Miércoles 09:00-11:00 | LAB SIST 01']);
        $p1cFund = PracticeGroup::create(['theory_group_id' => $t1FundProg->id, 'code' => 'P1C', 'base_capacity' => 15, 'schedule' => 'Jueves 07:00-09:00 | LAB SIST 01']);
        $p1dFund = PracticeGroup::create(['theory_group_id' => $t1FundProg->id, 'code' => 'P1D', 'base_capacity' => 15, 'schedule' => 'Jueves 09:00-11:00 | LAB SIST 01']);

        for ($i = 1; $i <= 45; $i++) {
            $pg = ($i <= 16) ? $p1aFund : (($i <= 32) ? $p1bFund : $p1cFund);
            Enrollment::create([
                'user_id' => $students2026[$i]->id,
                'course_id' => $cursoFundProg->id,
                'practice_group_id' => $pg->id,
                'enrolled_at' => (clone $baseEnrollTime)->addMinutes($i),
                'status' => 'original',
            ]);
        }
        for ($i = 30; $i <= 45; $i++) {
            Enrollment::create([
                'user_id' => $students2025[$i]->id,
                'course_id' => $cursoFundProg->id,
                'practice_group_id' => $p1dFund->id,
                'enrolled_at' => (clone $baseEnrollTime)->addMinutes(50 + $i),
                'status' => 'original',
            ]);
        }

        // =========================================================================
        // 5. CURSOS OFICIALES DEL CUARTO CICLO (Semestre 2026-02)
        // Tomados de la captura oficial del SIIGAA UNS
        // =========================================================================

        // --- CURSO 4: PROGRAMACION I (1411-0019) ---
        $cursoProg1 = Course::create([
            'code_course' => '1411-0019',
            'name' => 'PROGRAMACION I',
            'semester' => '2026-02',
        ]);
        $t1Prog1 = TheoryGroup::create(['course_id' => $cursoProg1->id, 'name' => 'Teoría 1']);
        $p1aProg1 = PracticeGroup::create(['theory_group_id' => $t1Prog1->id, 'code' => 'P1A', 'base_capacity' => 15, 'schedule' => 'Lunes 07:00-11:00 | LAB SIST AD-02']);
        $p1bProg1 = PracticeGroup::create(['theory_group_id' => $t1Prog1->id, 'code' => 'P1B', 'base_capacity' => 15, 'schedule' => 'Viernes 09:00-11:00 | LAB SIST AD-02']);
        $p1cProg1 = PracticeGroup::create(['theory_group_id' => $t1Prog1->id, 'code' => 'P1C', 'base_capacity' => 15, 'schedule' => 'Martes 07:00-11:00 | LAB SIST AD-02']);

        for ($i = 1; $i <= 45; $i++) {
            $pg = ($i <= 18) ? $p1aProg1 : (($i <= 33) ? $p1bProg1 : $p1cProg1);
            Enrollment::create([
                'user_id' => $students2025[$i]->id,
                'course_id' => $cursoProg1->id,
                'practice_group_id' => $pg->id,
                'enrolled_at' => (clone $baseEnrollTime)->addMinutes($i),
                'status' => 'original',
            ]);
        }

        // --- CURSO 5: BASE DE DATOS I (1411-0022) ---
        $cursoBd1 = Course::create([
            'code_course' => '1411-0022',
            'name' => 'BASE DE DATOS I',
            'semester' => '2026-02',
        ]);
        $t1Bd1 = TheoryGroup::create(['course_id' => $cursoBd1->id, 'name' => 'Teoría 1']);
        $p1aBd1 = PracticeGroup::create(['theory_group_id' => $t1Bd1->id, 'code' => 'P1A', 'base_capacity' => 15, 'schedule' => 'Miércoles 07:00-09:00 | LAB SIST AD-03']);
        $p1bBd1 = PracticeGroup::create(['theory_group_id' => $t1Bd1->id, 'code' => 'P1B', 'base_capacity' => 15, 'schedule' => 'Miércoles 09:00-11:00 | LAB SIST AD-03']);
        $p1cBd1 = PracticeGroup::create(['theory_group_id' => $t1Bd1->id, 'code' => 'P1C', 'base_capacity' => 15, 'schedule' => 'Jueves 11:00-13:00 | LAB SIST AD-03']);

        for ($i = 1; $i <= 45; $i++) {
            $pg = ($i <= 17) ? $p1aBd1 : (($i <= 32) ? $p1bBd1 : $p1cBd1);
            Enrollment::create([
                'user_id' => $students2025[$i]->id,
                'course_id' => $cursoBd1->id,
                'practice_group_id' => $pg->id,
                'enrolled_at' => (clone $baseEnrollTime)->addMinutes($i),
                'status' => 'original',
            ]);
        }

        // --- CURSO 6: INGENIERIA DE REQUISITOS (1411-0023) ---
        $cursoReq = Course::create([
            'code_course' => '1411-0023',
            'name' => 'INGENIERIA DE REQUISITOS',
            'semester' => '2026-02',
        ]);
        $t1Req = TheoryGroup::create(['course_id' => $cursoReq->id, 'name' => 'Teoría 1']);
        $p1aReq = PracticeGroup::create(['theory_group_id' => $t1Req->id, 'code' => 'P1A', 'base_capacity' => 15, 'schedule' => 'Martes 11:00-13:00 | AULA 201']);
        $p1bReq = PracticeGroup::create(['theory_group_id' => $t1Req->id, 'code' => 'P1B', 'base_capacity' => 15, 'schedule' => 'Jueves 07:00-09:00 | AULA 201']);
        $p1cReq = PracticeGroup::create(['theory_group_id' => $t1Req->id, 'code' => 'P1C', 'base_capacity' => 15, 'schedule' => 'Jueves 09:00-11:00 | AULA 201']);

        for ($i = 1; $i <= 45; $i++) {
            $pg = ($i <= 15) ? $p1aReq : (($i <= 30) ? $p1bReq : $p1cReq);
            Enrollment::create([
                'user_id' => $students2025[$i]->id,
                'course_id' => $cursoReq->id,
                'practice_group_id' => $pg->id,
                'enrolled_at' => (clone $baseEnrollTime)->addMinutes($i),
                'status' => 'original',
            ]);
        }
    }
}
