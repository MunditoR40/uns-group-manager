<p align="center">
  <img src="docs/assets/cecomp-logo.png" width="380" alt="CECOMP UNS Logo">
</p>

<h1 align="center">Sistema de Gestión y Reasignación de Grupos UNS</h1>

<p align="center">
  <strong>Producto de Software desarrollado para el curso de PHP + Laravel</strong><br>
  <strong>CECOMP UNS — Centro de Cómputo de la Universidad Nacional del Santa</strong>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12">
  <img src="https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2">
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/PHPUnit-100%25%20Passing-brightgreen?style=for-the-badge&logo=checkmarx&logoColor=white" alt="Tests">
</p>

---

## 🎓 Información Académica

* **Institución:** Universidad Nacional del Santa (UNS) — Chimbote, Perú
* **Centro:** Centro de Cómputo (CECOMP UNS)
* **Curso:** Desarrollo Web con PHP y Laravel
* **Docente a Cargo:** Ing. Borja Whiston
* **Tech Lead del Proyecto:** Angel Rojas

---

## 📌 Problemática y Contexto del Proyecto

Durante el proceso de matrícula oficial en la Universidad Nacional del Santa (UNS), los estudiantes se inscriben inicialmente en una única teoría (**Teoría 1**) y en distintos grupos de práctica (**P1A, P1B, P1C, P1D**, etc.).

Posteriormente, debido a la saturación de alumnos o a la reprogramación de horarios de laboratorio, la universidad suele autorizar la apertura de una **Teoría 2** y reestructurar los grupos de práctica. Esta tarea recae tradicionalmente en el **Delegado del curso**, quien debe reorganizar a los estudiantes de forma transparente, equitativa y con trazabilidad ante las autoridades académicas y docentes.

Este sistema web automatiza la reestructuración inicial y proporciona al delegado herramientas de control fino para gestionar casos particulares, cruces de horarios y la emisión de actas oficiales.

---

## 👥 Equipo de Desarrollo y Funcionalidades Asignadas

El proyecto fue desarrollado de forma colaborativa por un equipo de 8 integrantes, aplicando la metodología GitFlow:

| N° | Integrante | Rol | Rama Git | Funcionalidad y Entregables |
|:---:|:---|:---|:---|:---|
| **1** | **Angel Rojas** | **Tech Lead & Algoritmo** | `feature/reallocation-service` | Diseño de la arquitectura de servicios, implementación del algoritmo `ReallocationService` (regla de $\ge 60$ alumnos, partición truncada $\lfloor N/2 \rfloor$, aforos de laboratorio), suite de pruebas en PHPUnit y coordinación de integración en Git. |
| **2** | **Walter Flores** | **Líder de Base de Datos** | `feature/database-migrations` | Modelado relacional y migraciones de 6 tablas (`users`, `courses`, `theory_groups`, `practice_groups`, `enrollments`, `audit_logs`), llaves foráneas, índices de rendimiento y relaciones Eloquent. |
| **3** | **Loeffer** | **Asistente de Datos** | *Dataset / Seeds* | ✅ Modelado y curación del dataset institucional en `AcademicScheduleSeeder.php` con códigos oficiales UNS de 10 dígitos, 90 estudiantes (promociones 2025 y 2026), 6 cursos oficiales del SIIGAA y 304 matrículas con casos de excedentes y cruces de horario. |
| **4** | **Diego Gutierrez** | **Líder de FrontEnd & UI** | `feature/frontend-ui` | Diseño del layout institucional responsivo en Blade con Tailwind CSS y Alpine.js, modales de confirmación interactivos y toggles visuales para estados. |
| **5** | **Joshua Norabuena** | **Panel del Delegado y Filtros** | `feature/delegate-panel-filters` | Implementación del controlador de matrículas, consultas optimizadas con *Eager Loading*, barra de filtros (búsqueda por código/nombre, grupo, orden FIFO/alfabético) y paginación. |
| **6** | **Kelvin Carrillo** | **Auditoría y Rollback** | `feature/audit-and-rollback` | Módulo de auditoría inmutable (`AuditService` / `AuditController`), tracking de operaciones masivas mediante `batch_id` (UUID) y función de reversión (rollback) de cambios. |
| **7** | **Yampier Salinas** | **Exportación Oficial** | `feature/pdf-excel-exports` | Generación y descarga de reportes oficiales de matrícula en Excel (`maatwebsite/excel`) y actas de asistencia para firma docente en formato PDF (`barryvdh/laravel-dompdf`). |
| **8** | **Jared Rosales** | **Gestión de Cursos y Horarios** | `feature/courses-crud` | Módulo CRUD para asignaturas, semestres académicos, asignación de horarios de laboratorio, docentes responsables y ambientes. |

---

## ⚙️ Reglas de Negocio y Lógica del Sistema

1. **Condición de Apertura de Teoría 2:**
   * La división se habilita únicamente cuando la cantidad total de estudiantes matriculados en el curso es **mayor o igual a 60** ($N \ge 60$). Con menos de 60 alumnos, el curso se mantiene con una sola teoría.
2. **Partición Truncada de Prácticas:**
   * Sea $N$ la cantidad de grupos de práctica iniciales de Teoría 1:
     $$\text{Grupos para Teoría 2} = \lfloor N / 2 \rfloor$$
     $$\text{Grupos que quedan en Teoría 1} = N - \lfloor N / 2 \rfloor$$
   * **Ejemplo 4 prácticas:** 2 quedan en Teoría 1 (`P1A, P1B`) y 2 migran a Teoría 2 (`P2A, P2B`).
   * **Ejemplo 5 prácticas:** 3 quedan en Teoría 1 (`P1A, P1B, P1C`) y 2 migran a Teoría 2 (`P2A, P2B`).
   * El contador del abecedario se reinicia en `A` para Teoría 2.
3. **Capacidad y Aforo de Laboratorios:**
   * Cada laboratorio de práctica tiene una capacidad base de **15 alumnos**.
   * A cada teoría le corresponde a lo sumo $(\text{N° Prácticas}) \times 15$ alumnos.
4. **Control Manual del Delegado (Casos Especiales):**
   * El sistema automatiza la división estructural, dejando al delegado la potestad de reasignar a estudiantes individuales mediante el panel web para atender cruces de horarios, compromisos laborales o acuerdos mutuos.
   * Gestión manual de flags: alumno con laptop (`has_laptop`) y autorización escrita del docente (`teacher_authorized`).
5. **Trazabilidad e Inmutabilidad:**
   * Cada movimiento (automático o manual) genera un registro inmutable en `audit_logs` con el estado previo (`previous_state`) y el estado nuevo (`new_state`) en formato JSON.

---

## 🛠️ Stack Tecnológico

* **Lenguaje:** PHP 8.2+
* **Framework:** Laravel 12.x
* **Base de Datos:** MySQL / MariaDB (y SQLite en memoria para tests)
* **Frontend:** Laravel Blade, Tailwind CSS, Alpine.js
* **Testing:** PHPUnit 11.x
* **Exportaciones:** DomPDF y Laravel Excel

---

## 🚀 Instalación y Despliegue Local

### 1. Clonar el repositorio:
```bash
git clone https://github.com/MunditoR40/uns-group-manager.git
cd uns-group-manager
```

### 2. Instalar dependencias de PHP:
```bash
composer install
```

### 3. Configurar el archivo de entorno:
```bash
cp .env.example .env
php artisan key:generate
```

Asegúrate de configurar tus credenciales de base de datos en `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=uns_groups_db
DB_USERNAME=root
DB_PASSWORD=
SESSION_DRIVER=file
```

### 4. Ejecutar las migraciones:
```bash
php artisan migrate
```

### 5. Ejecutar la suite de pruebas unitarias:
```bash
php artisan test
```

### 6. Iniciar el servidor local:
```bash
php artisan serve
```
El sistema estará disponible en `http://127.0.0.1:8000`.

---

## 📄 Licencia

Proyecto con fines académicos desarrollado para la comunidad universitaria de la **Universidad Nacional del Santa (UNS)**.
