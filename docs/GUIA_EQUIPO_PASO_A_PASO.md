# Guía Paso a Paso para el Equipo de Desarrollo
## Sistema de Gestión y Reasignación de Grupos UNS (Laravel 12 / PHP 8.2)

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
* **Entregables concretos:**
  1. Crear archivo Excel: `Matriculados.xlsx` siguiendo el formato oficial de la UNS:
     * Hoja: `TablaDetalle1`
     * Columnas: `NRO`, `CÓDIGO` (10 dígitos), `APELLIDOS Y NOMBRES`, `FECHA`, `HORA` (escalonada segundo a segundo), `TEORIA` (1), `PRÁCTICA` (A, B, C, D, E), `CONDICIÓN` (1).
     * Total: ~75 alumnos inventados con nombres peruanos realistas.
  2. Crear archivo Excel: `CargaHoraria.xlsx` con la distribución de horarios, docentes y laboratorios.
* **Paso a paso:**
  * Descargar la plantilla o guiarse de las capturas del SIIGAA compartidas en el grupo.
  * Llenar los datos asegurándose de que en el grupo D haya 18-19 alumnos para poner a prueba la cola de excedentes.
  * Entregar los archivos `.xlsx` al Tech Lead para incorporarlos al importador automático del sistema.

---

### 3. Angel Rojas (Tech Lead)
* **Rol:** Tech Lead & Algoritmo de Reasignación
* **Rama:** `feature/reallocation-service`
* **Archivos a crear/modificar:**
  * `app/Services/ReallocationService.php`
  * `tests/Feature/ReallocationServiceTest.php`
* **Paso a paso:**
  1. **Cálculo de Aforo Flexible (`calculateEffectiveCapacity`):**
     * Base: 15 alumnos.
     * Si traen laptop (`has_laptop = true`): amplía aforo hasta 17 (+1 por alumno con laptop).
     * Si tienen autorización docente (`teacher_authorized = true`): amplía aforo hasta 18.
  2. **División de Teorías T1 $\rightarrow$ T2 (`splitTheoryGroups`):**
     * Caso 4 prácticas: P1A y P1B se quedan en T1; P1C $\rightarrow$ P2A y P1D $\rightarrow$ P2B pasan a T2.
     * Caso 5 prácticas: P1A, P1B y P1C se quedan en T1; P1D $\rightarrow$ P2A y P1E $\rightarrow$ P2B pasan a T2.
     * Actualiza la teoría de los alumnos y marca estado `'reasignado'`.
  3. **Manejo de Excedentes y Balanceo FIFO (`getOverflowAndVacancies` y `balanceOverflow`):**
     * Detectar alumnos que superen el aforo efectivo ordenados por `enrolled_at ASC`.
     * Reubicarlos en grupos con vacantes disponibles respetando el orden estricto de matrícula.
  4. **Pruebas Automatizadas:**
     * Crear el test suite en PHPUnit y asegurar que pase con 100% de éxito.

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
