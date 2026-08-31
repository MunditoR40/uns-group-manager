<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\PracticeGroup;
use App\Models\Teacher;
use App\Models\TheoryGroup;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class RealDataImportService
{
    /**
     * Importa toda la data real desde la carpeta oficial D:/Proyectos/data_real
     */
    public function import(string $baseDir = 'D:/Proyectos/data_real', bool $wipe = true): array
    {
        return DB::transaction(function () use ($baseDir, $wipe) {
            if ($wipe) {
                Enrollment::query()->delete();
                PracticeGroup::query()->delete();
                TheoryGroup::query()->delete();
                Course::query()->delete();
                User::where('role', '!=', 'admin')->delete();
                Teacher::query()->delete();
            }

            $stats = [
                'courses' => 0,
                'teachers' => 0,
                'theories' => 0,
                'practices' => 0,
                'students' => 0,
                'enrollments' => 0,
            ];

            $cycles = ['II CICLO', 'IV CICLO', 'VI CICLO', 'VIII CICLO'];
            $coursesMap = [];

            // 1. Procesar Carga Horaria de cada Ciclo
            foreach ($cycles as $cycle) {
                $dir = "$baseDir/$cycle";
                if (!is_dir($dir)) continue;

                $cargaFiles = glob("$dir/CargaHoraria_*.xlsx");
                foreach ($cargaFiles as $cf) {
                    $reader = IOFactory::createReaderForFile($cf);
                    $spreadsheet = $reader->load($cf);
                    $sheet = $spreadsheet->getActiveSheet();
                    $rows = $sheet->getHighestRow();

                    for ($r = 2; $r <= $rows; $r++) {
                        $dia = trim((string)$sheet->getCell([2, $r])->getValue());
                        $inicio = trim((string)$sheet->getCell([3, $r])->getValue());
                        $fin = trim((string)$sheet->getCell([5, $r])->getValue());
                        $codCurso = trim((string)$sheet->getCell([6, $r])->getValue());
                        $nombreCurso = trim((string)$sheet->getCell([7, $r])->getValue());
                        $grupo = trim((string)$sheet->getCell([8, $r])->getValue());
                        $nombreDocente = trim((string)$sheet->getCell([9, $r])->getValue());
                        $ambiente = trim((string)$sheet->getCell([10, $r])->getValue());

                        if (empty($codCurso) || empty($nombreCurso)) continue;

                        $cleanCourseName = preg_replace('/\s+/', ' ', $nombreCurso);

                        $course = Course::firstOrCreate(
                            ['code_course' => $codCurso],
                            [
                                'name' => $cleanCourseName,
                                'cycle' => $cycle,
                                'semester' => '2026-II',
                            ]
                        );
                        $coursesMap[strtoupper($cleanCourseName)] = $course;

                        // Manejo de Docente
                        $teacherId = null;
                        if (!empty($nombreDocente)) {
                            $cleanTeacherName = preg_replace('/\s+/', ' ', strtoupper($nombreDocente));
                            $teacher = Teacher::firstOrCreate(
                                ['name' => $cleanTeacherName],
                                [
                                    'code' => 'DOC-' . substr(md5($cleanTeacherName), 0, 4),
                                    'email' => strtolower(str_replace(' ', '.', $cleanTeacherName)) . '@uns.edu.pe',
                                    'department' => 'DAISI - Ingeniería de Sistemas e Informática',
                                    'condition' => 'Nombrado Principal',
                                ]
                            );
                            $teacherId = $teacher->id;
                        }

                        // Crear Teoría o Práctica
                        if (str_starts_with($grupo, 'T')) {
                            $theoryNum = substr($grupo, 1);
                            $theoryName = "Teoría $theoryNum";

                            TheoryGroup::updateOrCreate(
                                ['course_id' => $course->id, 'name' => $theoryName],
                                ['teacher_id' => $teacherId]
                            );
                        } elseif (str_starts_with($grupo, 'P')) {
                            $theoryNum = substr($grupo, 1, 1);
                            $theoryName = "Teoría $theoryNum";

                            $theory = TheoryGroup::firstOrCreate(
                                ['course_id' => $course->id, 'name' => $theoryName]
                            );

                            $schedule = (!empty($dia) && !empty($inicio)) ? "$dia $inicio - $fin" : null;

                            PracticeGroup::updateOrCreate(
                                ['theory_group_id' => $theory->id, 'code' => $grupo],
                                [
                                    'base_capacity' => 15,
                                    'schedule' => $schedule,
                                    'environment' => $ambiente ?: null,
                                    'teacher_id' => $teacherId,
                                ]
                            );
                        }
                    }
                }
            }

            // 2. Procesar Matrículas de cada Curso
            foreach ($cycles as $cycle) {
                $dir = "$baseDir/$cycle";
                if (!is_dir($dir)) continue;

                $matFiles = glob("$dir/Matriculados_*.xls*");
                foreach ($matFiles as $mf) {
                    $rawName = trim(str_replace(['Matriculados_2026-02_', '.xlsx', '.xls'], '', basename($mf)));
                    $cleanName = strtoupper(preg_replace('/\s+/', ' ', $rawName));

                    $course = $coursesMap[$cleanName] ?? Course::where('name', $cleanName)->first();
                    if (!$course) {
                        $course = Course::where('name', 'like', "%$cleanName%")->first();
                    }
                    if (!$course) continue;

                    $reader = IOFactory::createReaderForFile($mf);
                    $spreadsheet = $reader->load($mf);
                    $sheet = $spreadsheet->getActiveSheet();
                    $rows = $sheet->getHighestRow();

                    for ($r = 2; $r <= $rows; $r++) {
                        $rawCode = trim((string)$sheet->getCell([2, $r])->getValue());
                        $studentName = trim((string)$sheet->getCell([3, $r])->getValue());
                        $fecha = trim((string)$sheet->getCell([4, $r])->getValue());
                        $hora = trim((string)$sheet->getCell([5, $r])->getValue());
                        $teoriaNum = trim((string)$sheet->getCell([6, $r])->getValue()) ?: '1';
                        $practicaLetra = trim((string)$sheet->getCell([7, $r])->getValue()) ?: 'A';

                        if (empty($rawCode) || empty($studentName)) continue;

                        $cleanCode = sprintf('%010d', (int)$rawCode);
                        $cleanStudentName = preg_replace('/\s+/', ' ', strtoupper($studentName));

                        $user = User::firstOrCreate(
                            ['code' => $cleanCode],
                            [
                                'name' => $cleanStudentName,
                                'email' => "{$cleanCode}@uns.edu.pe",
                                'role' => 'estudiante',
                                'password' => bcrypt('secret'),
                            ]
                        );

                        $theoryName = "Teoría $teoriaNum";
                        $theory = TheoryGroup::firstOrCreate([
                            'course_id' => $course->id,
                            'name' => $theoryName,
                        ]);

                        $practiceCode = "P{$teoriaNum}{$practicaLetra}";
                        $practice = PracticeGroup::firstOrCreate(
                            ['theory_group_id' => $theory->id, 'code' => $practiceCode],
                            ['base_capacity' => 15]
                        );

                        try {
                            $enrolledAt = Carbon::parse("$fecha $hora");
                        } catch (\Throwable $e) {
                            $enrolledAt = now();
                        }

                        Enrollment::firstOrCreate(
                            [
                                'user_id' => $user->id,
                                'course_id' => $course->id,
                            ],
                            [
                                'practice_group_id' => $practice->id,
                                'status' => 'original',
                                'enrolled_at' => $enrolledAt,
                                'has_laptop' => false,
                                'teacher_authorized' => false,
                            ]
                        );
                    }
                }
            }

            if (User::where('role', 'delegado')->count() === 0) {
                User::first()?->update(['role' => 'delegado']);
            }

            $stats['courses'] = Course::count();
            $stats['teachers'] = Teacher::count();
            $stats['theories'] = TheoryGroup::count();
            $stats['practices'] = PracticeGroup::count();
            $stats['students'] = User::count();
            $stats['enrollments'] = Enrollment::count();

            return $stats;
        });
    }
}