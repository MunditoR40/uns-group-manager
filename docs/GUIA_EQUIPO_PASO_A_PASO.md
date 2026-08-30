# Guía Paso a Paso para el Equipo de Desarrollo
## Sistema de Gestión y Reasignación de Grupos UNS (Laravel 12 / PHP 8.2)
### Curso de PHP + Laravel — CECOMP UNS (Centro de Cómputo de la Universidad Nacional del Santa)
**Docente a cargo:** Ing. Borja Whiston | **Tech Lead:** Angel Rojas

---

## 📌 Flujo de Trabajo en Git para Todos los Integrantes

1. **Antes de empezar a programar:**
   Asegúrate de estar en la rama `develop` actualizada:
   ```bash
   git checkout develop
   git pull origin develop
   ```

2. **Crear tu rama de trabajo personal:**
   Crea tu rama con el prefijo `feature/` según la tabla abajo:
   ```bash
   git checkout -b feature/nombre-de-tu-rama
   ```

3. **Al terminar tus cambios:**
   ```bash
   git add .
   git commit -m "feat: descripción clara de lo que hiciste"
   git push origin feature/nombre-de-tu-rama
   ```

4. **Avisar al Tech Lead (Angel Rojas)** para revisar y fusionar (merge) tu rama a `develop`.

---

## ⚠️ ADVERTENCIA CRÍTICA: Sincronización de Base de Datos para el Equipo

> ### 🛑 ¿Por qué no ves los cursos y alumnos en tu MySQL Workbench automáticamente al hacer git pull?
> * Git **NO sincroniza los archivos de la base de datos MySQL** de tu computadora porque cada integrante tiene su propio servidor local (`localhost`). Git solo sincroniza el código fuente.
> * Los datos oficiales fueron codificados en el **Seeder de Laravel** (`database/seeders/AcademicScheduleSeeder.php`).
> 
> ### 📋 Comando OBLIGATORIO que debe ejecutar cada integrante en su PC:
> Cada vez que descargues cambios con `git pull origin develop`, ejecuta en tu terminal:
> ```bash
> php artisan migrate:fresh --seed
> ```
> **¿Qué hace este comando en tu computadora?**
> 1. Borra y recrea las 6 tablas limpias en tu base de datos MySQL local (`uns_groups_db`).
> 2. Carga automáticamente:
>    * **90 estudiantes reales** con códigos institucionales UNS ordenados alfabéticamente (Promociones 2025 y 2026).
>    * **6 cursos oficiales del SIIGAA** (Cálculo Integral, Física I, Base de Datos I, Programación I, etc.) con sus docentes y horarios.
>    * **304 matrículas oficiales**, con excedentes en prácticas y cruces de horario reales para probar sus pantallas y filtros.

---

## 👥 Asignación Detallada por Integrante

---

### 1. Walter Flores
* **Rol:** Líder de Base de Datos y Modelado
* **Rama:** `feature/database-migrations`
* **Estado:** ✅ **COMPLETADO AL 100%**
* **Entregables realizados:**
  * Migraciones para 6 tablas: `users`, `courses`, `theory_groups`, `practice_groups`, `enrollments`, `audit_logs`.
  * Modelos Eloquent con relaciones: `User`, `Course`, `TheoryGroup`, `PracticeGroup`, `Enrollment`, `AuditLog`.

---

### 2. Loeffer
* **Rol:** Asistente de Datos y Dataset Oficial
* **Estado:** ✅ **COMPLETADO AL 100%**
* **Entregables realizados:**
  * Estructuración del dataset de estudiantes con el formato oficial de código UNS:
    `0` + `año de ingreso (2025/2026)` + `código de escuela (140)` + `orden alfabético (01 a 45)`.
  * Generación y curación de 90 alumnos ficticios con nombres peruanos realistas y correos `@uns.edu.pe`.
  * Implementación del Seeder oficial de la aplicación: [`AcademicScheduleSeeder.php`](file:///d:/Proyectos/uns-group-manager/database/seeders/AcademicScheduleSeeder.php).
  * Carga de los 6 cursos oficiales del SIIGAA UNS (Ciclo II y Ciclo IV) con docentes, ambientes y 304 matrículas con casos de excedentes y cruces de horario por repitencia.

---

### 3. Angel Rojas (Tech Lead)
* **Rol:** Tech Lead & Algoritmo de Reasignación
* **Rama:** `feature/reallocation-service`
* **Estado:** ✅ **COMPLETADO AL 100%**
* **Archivos implementados:**
  * `app/Services/ReallocationService.php`
  * `tests/Feature/ReallocationServiceTest.php`
* **Lógica implementada:**
  1. **Condición de activación ($\ge 60$ alumnos matriculados):**
     * Si hay menos de 60 alumnos, el curso se mantiene con solo Teoría 1.
     * Con 60 o más alumnos, se habilita y crea la Teoría 2.
  2. **Partición generalizada truncada de grupos ($\lfloor N / 2 \rfloor$):**
     * Teoría 1 conserva $(N - \lfloor N / 2 \rfloor)$ prácticas: `P1A, P1B, P1C...`.
     * Teoría 2 recibe $\lfloor N / 2 \rfloor$ prácticas reiniciando abecedario: `P2A, P2B...`.
     * Los estudiantes de los grupos migrantes se actualizan a Teoría 2 con estado `'reasignado'` y registro inmutable en `audit_logs` con `batch_id`.
  3. **Herramientas de apoyo para la gestión manual del Delegado:**
     * `moveStudentManually(...)`: Reasignación individual de alumnos particulares (cruces de horario, peticiones directas).
     * `toggleLaptop(...)`: Control manual de alumnos con laptop.
     * `toggleTeacherAuth(...)`: Control manual de autorizaciones docentes.
  4. **Pruebas Automatizadas:**
     * Suite en PHPUnit con 5 tests y 36 aserciones pasando al 100% (`php artisan test`).

---

### 4. Diego Gutierrez
* **Rol:** Líder de FrontEnd y Responsive UI
* **Rama:** `feature/frontend-ui`
* **Archivos a crear/modificar:**
  * `resources/views/layouts/app.blade.php`
  * Modales y estilos interactivos (Tailwind CSS CDN + Alpine.js)
* **Paso a paso:**
  1. Diseñar el layout base con navbar institucional de la UNS (azul marino `#1e3a8a`, logo y títulos).
  2. Incluir el contenedor de mensajes flash para alertas de éxito (`session('success')`) y error.
  3. Maquetar los modales de confirmación:
     * Modal para confirmar la **División T1 $\rightarrow$ T2** con explicación de las reglas.
     * Modal para confirmar el **Balanceo FIFO** con conteo de excedentes.
     * Modal para **Reasignar manualmente** a un estudiante a otro grupo de práctica.
  4. Diseñar botones estilo *Toggle / Switch* para marcar si el alumno tiene laptop o autorización docente.

---

### 5. Joshua Norabuena
* **Rol:** Panel del Delegado y Filtros
* **Rama:** `feature/delegate-panel-filters`
* **Archivos a crear/modificar:**
  * `app/Http/Controllers/CourseController.php` (método `show`)
  * `resources/views/courses/show.blade.php` (tabla interactiva y barra de filtros)
* **Paso a paso:**
  1. Construir la consulta Eloquent con *Eager Loading* (`with(['user', 'practiceGroup.theoryGroup'])`) para evitar consultas lentas (N+1).
  2. Implementar los filtros mediante `Request $request`:
     * Filtro por búsqueda: `$query->whereHas('user', ...)` por nombre o código universitario.
     * Filtro por grupo de práctica y teoría.
     * Ordenamiento: FIFO por defecto (`orderBy('enrolled_at', 'asc')`), alfabético (`name asc/desc`) o por código.
  3. Agregar paginación limpia (`->paginate(25)`).
  4. Mostrar en la cabecera las tarjetas de cada grupo de práctica con su barra de progreso de aforo actual vs aforo efectivo.

---

### 6. Kelvin Carrillo
* **Rol:** Módulo de Auditoría y Rollback
* **Rama:** `feature/audit-and-rollback`
* **Archivos a crear/modificar:**
  * `app/Services/AuditService.php`
  * `app/Http/Controllers/AuditController.php`
  * `resources/views/audit/index.blade.php`
  * `tests/Feature/AuditAndRollbackTest.php`
* **Paso a paso:**
  1. En `AuditService.php`, implementar el método `logAction(...)` que reciba el snapshot de `previous_state` y `new_state` en arrays PHP y lo guarde como JSON en `audit_logs`.
  2. Implementar `rollbackBatch(string $batchId, User $executor)`:
     * Buscar todos los logs con ese `batch_id` dentro de una transacción `DB::transaction(...)`.
     * Restaurar los campos del alumno al estado que tenían en `previous_state`.
     * Marcar el log con `is_reverted = true`.
  3. Implementar `rollbackSingle(AuditLog $auditLog, User $executor)` para revertir una acción puntual.
  4. Crear la vista `audit/index.blade.php` para que el delegado vea los lotes recientes y pueda hacer clic en "Revertir Lote".

---

### 7. Yampier Salinas
* **Rol:** Módulo de Exportación Oficial
* **Rama:** `feature/pdf-excel-exports`
* **Archivos a crear/modificar:**
  * `app/Exports/EnrollmentsExport.php`
  * `app/Http/Controllers/ExportController.php`
  * `resources/views/exports/attendance_pdf.blade.php`
* **Paso a paso:**
  1. Instalar paquetes: `composer require barryvdh/laravel-dompdf maatwebsite/excel`.
  2. Crear `EnrollmentsExport.php` implementando `FromCollection`, `WithHeadings`, `ShouldAutoSize` para exportar a Excel `.xlsx` la lista de inscritos con sus flags y hora de matrícula.
  3. Diseñar `attendance_pdf.blade.php` con formato de Acta Oficial lista para imprimir:
     * Membrete de la UNS y Escuela de Informática.
     * Asignatura, semestre, grupo de práctica y aforo.
     * Columnas: N°, Código, Apellidos y Nombres, Laptop, Docente, Estado, y casillero de Firma.
     * Espacio inferior para firma del Docente y del Delegado.
  4. Conectar las rutas de descarga en `ExportController`.

---

### 8. Jared Rosales
* **Rol:** Gestión de Cursos y Horarios
* **Rama:** `feature/courses-crud`
* **Archivos a crear/modificar:**
  * `app/Http/Controllers/CourseController.php` (métodos `index`, `create`, `store`)
  * `resources/views/courses/index.blade.php`
* **Paso a paso:**
  1. Crear la vista `courses/index.blade.php` que muestre los cursos disponibles como tarjetas interactivas.
  2. Mostrar en cada tarjeta: código del curso (ej: IF-301), nombre de la asignatura, semestre y cantidad de matriculados.
  3. Botón directo "Gestionar y Reasignar" que enlace al panel de control del curso (`courses.show`).
  4. Asegurar que las rutas apunten a `/courses` como pantalla inicial del sistema.

---

## 🚀 Comandos Rápidos de Verificación

* Para levantar el servidor local:
  ```bash
  php artisan serve
  ```
* Para correr las pruebas unitarias:
  ```bash
  php artisan test
  ```
* Para limpiar caché si algo no recarga:
  ```bash
  php artisan config:clear
  php artisan route:clear
  php artisan view:clear
  ```
