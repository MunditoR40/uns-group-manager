<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\PracticeGroup;
use App\Models\Teacher;
use App\Models\TheoryGroup;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SyntheticDataService
{
    /**
     * Nombres y apellidos peruanos ficticios para anonimización
     */
    private array $paternalNames = [
        'QUISPE', 'FLORES', 'RODRIGUEZ', 'SANCHEZ', 'GARCIA', 'ROJAS', 'DIAZ', 'TORRES',
        'MENDOZA', 'CASTILLO', 'MORALES', 'CHAVEZ', 'HERRERA', 'PAREDES', 'VASQUEZ', 'GUTIERREZ',
        'RAMIREZ', 'CRUZ', 'REYES', 'MEDINA', 'AGUILAR', 'VEGA', 'ESPINOZA', 'CAMPOS',
        'SILVA', 'FERNANDEZ', 'VALVERDE', 'LOZANO', 'VARGAS', 'DELGADO', 'ALARCON', 'BENITES',
        'CABRERA', 'SALAZAR', 'GUERRERO', 'ZAVALETA', 'SOTO', 'CARRASCO', 'RIOS', 'PALACIOS',
        'ALVA', 'PAZ', 'GUZMAN', 'MERCADO', 'CORDOVA', 'ACUÑA', 'VALENCIA', 'LEON', 'BAZAN', 'CARBAJAL'
    ];

    private array $maternalNames = [
        'MENDOZA', 'CASTRO', 'ROJAS', 'QUISPE', 'FLORES', 'HERRERA', 'VARGAS', 'SANCHEZ',
        'LOPEZ', 'GOMEZ', 'RIVERA', 'DIAZ', 'MORALES', 'TORRES', 'CRUZ', 'REYES',
        'CHAVEZ', 'GARCIA', 'RAMIREZ', 'SILVA', 'ESPINOZA', 'CAMPOS', 'ALVAREZ', 'CABALLERO',
        'AGUILAR', 'PAREDES', 'VASQUEZ', 'SALAZAR', 'GUERRERO', 'RIOS', 'VALVERDE', 'BENITES',
        'CARBAJAL', 'PALACIOS', 'SOTO', 'BAZAN', 'CORDOVA', 'ALVA', 'GUZMAN', 'LEON'
    ];

    private array $firstNames = [
        'LUIS', 'CARLOS', 'JORGE', 'MANUEL', 'DIEGO', 'JUAN', 'KEVIN', 'CRISTIAN',
        'BRYAN', 'DAVID', 'PEDRO', 'ANGEL', 'RENATO', 'MARIO', 'RICARDO', 'FRANCO',
        'HECTOR', 'DANIEL', 'CESAR', 'ALEXANDER', 'MARIA', 'ANA', 'LUCIA', 'ANDREA',
        'VALERIA', 'CAMILA', 'FERNANDA', 'DIANA', 'CLAUDIA', 'PAOLA', 'SOFIA', 'GABRIELA',
        'FIORELLA', 'STEPHANIE', 'CAROLINA', 'ROBERTO', 'FABRICIO', 'ALONSO', 'SEBASTIAN'
    ];

    private array $secondNames = [
        'ENRIQUE', 'ALBERTO', 'EDUARDO', 'ALEJANDRO', 'ALEXIS', 'ANTONIO', 'MIGUEL', 'GABRIEL',
        'MARTIN', 'FERNANDO', 'ELIZABETH', 'CRISTINA', 'ISABEL', 'BEATRIZ', 'PATRICIA', 'CARMEN',
        'ALONSO', 'ANDRES', 'JAVIER', 'FABIAN', 'VALENTINO', 'STEVEN', 'DANIEL', 'ESTEBAN'
    ];

    /**
     * Genera la base de datos completa con información 100% anónima y ficticia
     */
    public function generate(): array
    {
        return DB::transaction(function () {
            // 1. Limpieza de tablas
            if (DB::getDriverName() === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            }

            AuditLog::query()->delete();
            Enrollment::query()->delete();
            PracticeGroup::query()->delete();
            TheoryGroup::query()->delete();
            Course::query()->delete();
            Teacher::query()->delete();
            User::where('role', '!=', 'admin')->delete();

            if (DB::getDriverName() === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }

            // 2. Crear Docentes Ficticios
            $teachersData = [
                ['name' => 'DR. RICARDO MENDOZA ALVA', 'email' => 'rmendoza@uns.edu.pe', 'dep' => 'DAISI', 'cond' => 'Nombrado Principal'],
                ['name' => 'MG. CARLOS SANCHEZ LUNA', 'email' => 'csanchez@uns.edu.pe', 'dep' => 'DAISI', 'cond' => 'Nombrado Principal'],
                ['name' => 'ING. VICTORIA CORDOVA RIOS', 'email' => 'vcordova@uns.edu.pe', 'dep' => 'DAISI', 'cond' => 'Nombrado Asociado'],
                ['name' => 'DR. ROBERTO GIL HERRERA', 'email' => 'rgil@uns.edu.pe', 'dep' => 'DAISI', 'cond' => 'Nombrado Principal'],
                ['name' => 'MG. TEODORO MORENO FLORES', 'email' => 'tmoreno@uns.edu.pe', 'dep' => 'DAISI', 'cond' => 'Nombrado Asociado'],
                ['name' => 'ING. PEDRO MANRIQUE PULIDO', 'email' => 'pmanrique@uns.edu.pe', 'dep' => 'DAISI', 'cond' => 'Nombrado Auxiliar'],
                ['name' => 'DR. HUGO CASELLI VARGAS', 'email' => 'hcaselli@uns.edu.pe', 'dep' => 'DAISI', 'cond' => 'Nombrado Principal'],
                ['name' => 'MG. LUIS RAMIREZ CASTILLO', 'email' => 'lramirez@uns.edu.pe', 'dep' => 'DAISI', 'cond' => 'Nombrado Principal'],
                ['name' => 'ING. MIRKO RONCEROS ALVAREZ', 'email' => 'mronceros@uns.edu.pe', 'dep' => 'DAISI', 'cond' => 'Nombrado Asociado'],
                ['name' => 'ING. CARLOS GUERRA PAREDES', 'email' => 'cguerra@uns.edu.pe', 'dep' => 'DAISI', 'cond' => 'Nombrado Asociado'],
                ['name' => 'DR. CAMILO SUAREZ TORRES', 'email' => 'csuarez@uns.edu.pe', 'dep' => 'DAISI', 'cond' => 'Nombrado Principal'],
                ['name' => 'MG. PENELOPE LEVANO DIAZ', 'email' => 'plevano@uns.edu.pe', 'dep' => 'DAISI', 'cond' => 'Nombrado Asociado'],
                ['name' => 'ING. CARLOS GIL NARVAEZ', 'email' => 'cgil@uns.edu.pe', 'dep' => 'DAISI', 'cond' => 'Nombrado Auxiliar'],
                ['name' => 'ING. WHISTON BORJA CAMPOS', 'email' => 'wborja@uns.edu.pe', 'dep' => 'DAISI', 'cond' => 'Nombrado Asociado'],
                ['name' => 'MG. DAVID MEDINA ROJAS', 'email' => 'dmedina@uns.edu.pe', 'dep' => 'DAISI', 'cond' => 'Nombrado Auxiliar'],
                ['name' => 'ING. JAVIER AVALOS RAMOS', 'email' => 'javalos@uns.edu.pe', 'dep' => 'DAISI', 'cond' => 'Contratado'],
                ['name' => 'DR. CESAR LOZANO VEGA', 'email' => 'clozano@uns.edu.pe', 'dep' => 'DAISI', 'cond' => 'Nombrado Principal'],
                ['name' => 'MG. HECTOR FLORES QUISPE', 'email' => 'hflores@uns.edu.pe', 'dep' => 'DAISI', 'cond' => 'Nombrado Asociado'],
                ['name' => 'ING. DANIEL BAZAN MORALES', 'email' => 'dbazan@uns.edu.pe', 'dep' => 'DAISI', 'cond' => 'Nombrado Auxiliar'],
                ['name' => 'DRA. CARMEN ESPINOZA RIVERA', 'email' => 'cespinoza@uns.edu.pe', 'dep' => 'DAISI', 'cond' => 'Nombrado Principal'],
            ];

            $teachers = [];
            foreach ($teachersData as $idx => $t) {
                $teachers[] = Teacher::create([
                    'name' => $t['name'],
                    'code' => sprintf('DOC-%03d', $idx + 1),
                    'email' => $t['email'],
                    'department' => $t['dep'] . ' - Ingeniería de Sistemas e Informática',
                    'condition' => $t['cond'],
                ]);
            }

            // 3. Crear Asignaturas Ficticias de la Malla Oficial UNS
            $coursesDef = [
                // II CICLO
                ['code' => '1411-0007', 'name' => 'FISICA I', 'cycle' => 'II CICLO', 'groups' => 4, 'target_students' => 64],
                ['code' => '1411-0008', 'name' => 'CALCULO INTEGRAL', 'cycle' => 'II CICLO', 'groups' => 4, 'target_students' => 59],
                ['code' => '1411-0009', 'name' => 'FUNDAMENTOS DE PROGRAMACION', 'cycle' => 'II CICLO', 'groups' => 4, 'target_students' => 62],
                ['code' => '1411-0010', 'name' => 'REALIDAD NACIONAL Y REGIONAL', 'cycle' => 'II CICLO', 'groups' => 3, 'target_students' => 58],
                ['code' => '1411-0011', 'name' => 'MEDIO AMBIENTE Y DESARROLLO SOSTENIBLE', 'cycle' => 'II CICLO', 'groups' => 3, 'target_students' => 60],
                ['code' => '1411-0012', 'name' => 'FUNDAMENTOS DE INGENIERIA', 'cycle' => 'II CICLO', 'groups' => 3, 'target_students' => 56],

                // IV CICLO
                ['code' => '1411-0019', 'name' => 'PROGRAMACION I', 'cycle' => 'IV CICLO', 'groups' => 4, 'target_students' => 59],
                ['code' => '1411-0020', 'name' => 'ARQUITECTURA DE COMPUTADORAS', 'cycle' => 'IV CICLO', 'groups' => 4, 'target_students' => 62],
                ['code' => '1411-0021', 'name' => 'DINAMICA DE SISTEMAS II', 'cycle' => 'IV CICLO', 'groups' => 3, 'target_students' => 48],
                ['code' => '1411-0022', 'name' => 'BASE DE DATOS I', 'cycle' => 'IV CICLO', 'groups' => 3, 'target_students' => 54],
                ['code' => '1411-0023', 'name' => 'INGENIERIA DE REQUISITOS', 'cycle' => 'IV CICLO', 'groups' => 3, 'target_students' => 52],
                ['code' => '1411-0024', 'name' => 'INVESTIGACION DE OPERACIONES I', 'cycle' => 'IV CICLO', 'groups' => 4, 'target_students' => 61],

                // VI CICLO
                ['code' => '1411-0031', 'name' => 'APLICACIONES DISTRIBUIDAS I', 'cycle' => 'VI CICLO', 'groups' => 4, 'target_students' => 74],
                ['code' => '1411-0032', 'name' => 'COMUNICACION DE DATOS', 'cycle' => 'VI CICLO', 'groups' => 4, 'target_students' => 66],
                ['code' => '1411-0033', 'name' => 'BASE DE DATOS II', 'cycle' => 'VI CICLO', 'groups' => 4, 'target_students' => 68],
                ['code' => '1411-0034', 'name' => 'ARQUITECTURA DE SOFTWARE EMPRESARIAL', 'cycle' => 'VI CICLO', 'groups' => 4, 'target_students' => 71],
                ['code' => '1411-0035', 'name' => 'SISTEMAS DE INFORMACION II', 'cycle' => 'VI CICLO', 'groups' => 4, 'target_students' => 65],
                ['code' => '1411-0036', 'name' => 'ADMINISTRACION DE PROCESOS DE NEGOCIO', 'cycle' => 'VI CICLO', 'groups' => 3, 'target_students' => 55],

                // VIII CICLO
                ['code' => '1411-0043', 'name' => 'ARQUITECTURA ORIENTADA A SERVICIOS Y MICROSERVICIOS', 'cycle' => 'VIII CICLO', 'groups' => 3, 'target_students' => 50],
                ['code' => '1411-0044', 'name' => 'REDES DE COMPUTADORAS II', 'cycle' => 'VIII CICLO', 'groups' => 3, 'target_students' => 45],
                ['code' => '1411-0045', 'name' => 'INTELIGENCIA ARTIFICIAL', 'cycle' => 'VIII CICLO', 'groups' => 4, 'target_students' => 64],
                ['code' => '1411-0046', 'name' => 'GESTION DE TECNOLOGIAS DE INFORMACION II', 'cycle' => 'VIII CICLO', 'groups' => 3, 'target_students' => 52],
                ['code' => '1411-0047', 'name' => 'INGENIERIA DE SOFTWARE II', 'cycle' => 'VIII CICLO', 'groups' => 3, 'target_students' => 50],
                ['code' => '1411-0048', 'name' => 'AGENTES INTELIGENTES', 'cycle' => 'VIII CICLO', 'groups' => 2, 'target_students' => 34],
                ['code' => '1411-0049', 'name' => 'GESTION DE LAS RELACIONES CON LOS CLIENTES', 'cycle' => 'VIII CICLO', 'groups' => 2, 'target_students' => 20],
                ['code' => '1411-0050', 'name' => 'PSICOLOGIA ORGANIZACIONAL', 'cycle' => 'VIII CICLO', 'groups' => 1, 'target_students' => 12],
            ];

            $createdCourses = [];
            $coursePractices = []; // [course_id => [PracticeGroup, ...]]

            $days = ['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO'];
            $labs = [
                'LAB SIST AD-01 | PABELLON NUEVO | Piso 3',
                'LAB SIST AD-02 | PABELLON NUEVO | Piso 3',
                'LAB SIST AD-03 | PABELLON NUEVO | Piso 3',
                'LAB SIST REDT | PABELLON NUEVO | Piso 2',
                'A01_Pool | POOL DE AULAS | Piso 1',
                'S01 | PABELLON DE SISTEMAS | Piso 1'
            ];

            foreach ($coursesDef as $cIdx => $cDef) {
                $teacherId = $teachers[$cIdx % count($teachers)]->id;

                $course = Course::create([
                    'code_course' => $cDef['code'],
                    'name' => $cDef['name'],
                    'cycle' => $cDef['cycle'],
                    'semester' => '2026-II',
                ]);
                $createdCourses[] = $course;

                // Crear Teoría 1
                $theory = TheoryGroup::create([
                    'course_id' => $course->id,
                    'name' => 'Teoría 1',
                    'teacher_id' => $teacherId,
                ]);

                // Crear Grupos de Práctica (P1A, P1B, P1C, P1D...)
                $letters = ['A', 'B', 'C', 'D', 'E', 'F'];
                $practices = [];
                for ($g = 0; $g < $cDef['groups']; $g++) {
                    $letra = $letters[$g];
                    $code = "P1{$letra}";
                    $day = $days[($cIdx + $g) % count($days)];
                    $startH = 8 + ($g * 2);
                    $endH = $startH + 2;
                    $sched = sprintf('%s %02d:00:00 - %02d:00:00', $day, $startH, $endH);
                    $env = $labs[($cIdx + $g) % count($labs)];

                    $pGroup = PracticeGroup::create([
                        'theory_group_id' => $theory->id,
                        'code' => $code,
                        'base_capacity' => 15,
                        'schedule' => $sched,
                        'environment' => $env,
                        'teacher_id' => $teachers[($cIdx + $g) % count($teachers)]->id,
                    ]);
                    $practices[] = $pGroup;
                }
                $coursePractices[$course->id] = $practices;
            }

            // 4. Generar 280 Estudiantes Ficticios con Padrón Verosímil
            $students = [];
            $generatedNames = [];
            $promoDistribution = [
                '2026' => 70, // Ciclo II
                '2025' => 70, // Ciclo IV
                '2024' => 75, // Ciclo VI
                '2023' => 60, // Ciclo VIII
                '2022' => 5,  // Repitentes / casos de borde
            ];

            $password = bcrypt('secret123');
            $studentIndex = 1;

            foreach ($promoDistribution as $promo => $count) {
                for ($i = 1; $i <= $count; $i++) {
                    do {
                        $pat = $this->paternalNames[array_rand($this->paternalNames)];
                        $mat = $this->maternalNames[array_rand($this->maternalNames)];
                        $first = $this->firstNames[array_rand($this->firstNames)];
                        $second = $this->secondNames[array_rand($this->secondNames)];
                        $fullName = "$pat $mat $first $second";
                    } while (isset($generatedNames[$fullName]));

                    $generatedNames[$fullName] = true;
                    $code = sprintf('0%s140%03d', $promo, $i);

                    $role = ($studentIndex === 1) ? 'delegado' : 'estudiante';

                    $user = User::create([
                        'name' => $fullName,
                        'code' => $code,
                        'email' => "{$code}@uns.edu.pe",
                        'role' => $role,
                        'password' => $password,
                    ]);

                    $students[] = [
                        'user' => $user,
                        'promo' => $promo,
                    ];
                    $studentIndex++;
                }
            }

            // 5. Generar Matrículas Sintéticas con Cargas Académicas Reales
            $enrollmentsCount = 0;
            $baseTimestamp = Carbon::create(2026, 8, 20, 1, 0, 0);

            // Agrupar estudiantes por promoción
            $studentsByPromo = [];
            foreach ($students as $item) {
                $studentsByPromo[$item['promo']][] = $item['user'];
            }

            // Mapeo Ciclo -> Promoción principal y promociones secundarias (repitentes)
            $cycleToPromos = [
                'II CICLO' => ['main' => '2026', 'others' => ['2025', '2024']],
                'IV CICLO' => ['main' => '2025', 'others' => ['2024', '2023']],
                'VI CICLO' => ['main' => '2024', 'others' => ['2023', '2022']],
                'VIII CICLO' => ['main' => '2023', 'others' => ['2022']],
            ];

            foreach ($createdCourses as $cIdx => $course) {
                $cDef = $coursesDef[$cIdx];
                $targetCount = $cDef['target_students'];
                $practices = $coursePractices[$course->id];
                $promoConfig = $cycleToPromos[$course->cycle];

                // Obtener candidatos principales
                $mainCandidates = $studentsByPromo[$promoConfig['main']] ?? [];
                shuffle($mainCandidates);

                // Candidatos de otras promociones (para el gráfico circular de repitentes)
                $otherCandidates = [];
                foreach ($promoConfig['others'] as $oPromo) {
                    if (isset($studentsByPromo[$oPromo])) {
                        $otherCandidates = array_merge($otherCandidates, $studentsByPromo[$oPromo]);
                    }
                }
                shuffle($otherCandidates);

                // 85% de la promoción regular, 15% de promociones anteriores
                $mainCount = min(count($mainCandidates), (int)round($targetCount * 0.85));
                $otherCount = min(count($otherCandidates), $targetCount - $mainCount);

                $enrolledStudents = array_merge(
                    array_slice($mainCandidates, 0, $mainCount),
                    array_slice($otherCandidates, 0, $otherCount)
                );

                // Asignar en grupos de práctica de manera balanceada y con marcas de tiempo FIFO
                foreach ($enrolledStudents as $eIdx => $student) {
                    $practice = $practices[$eIdx % count($practices)];
                    $enrolledAt = $baseTimestamp->copy()->addMinutes($eIdx * 3 + rand(0, 59))->addSeconds(rand(0, 59));

                    Enrollment::create([
                        'user_id' => $student->id,
                        'course_id' => $course->id,
                        'practice_group_id' => $practice->id,
                        'status' => 'original',
                        'enrolled_at' => $enrolledAt,
                        'has_laptop' => (rand(1, 100) <= 42), // 42% portabilidad laptop
                        'teacher_authorized' => (rand(1, 100) <= 7), // 7% permiso docente
                    ]);

                    $enrollmentsCount++;
                }
            }

            return [
                'courses' => Course::count(),
                'teachers' => Teacher::count(),
                'theories' => TheoryGroup::count(),
                'practices' => PracticeGroup::count(),
                'students' => User::count(),
                'enrollments' => $enrollmentsCount,
            ];
        });
    }
}