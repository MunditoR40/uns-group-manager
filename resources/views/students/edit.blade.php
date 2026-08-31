<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Estudiante</title>
</head>
<body>
    <h2>Editar Estudiante: {{ $student->name }}</h2>

    <form action="{{ route('students.update', $student->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div>
            <label>Nombre Completo:</label><br>
            <input type="text" name="name" value="{{ old('name', $student->name) }}" required>
        </div>
        <br>

        <div>
            <label>Rol:</label><br>
            <select name="role">
                <option value="student" {{ $student->role === 'student' ? 'selected' : '' }}>Estudiante Regular</option>
                <option value="delegate" {{ $student->role === 'delegate' ? 'selected' : '' }}>Delegado</option>
            </select>
        </div>
        <br>

        <div>
            <h3>Cursos Disponibles</h3>
            @foreach($courses as $course)
                <label>
                    <input type="checkbox" name="courses[]" value="{{ $course->id }}">
                    {{ $course->name }}
                </label><br>
            @endforeach
        </div>
        <br>

        <button type="submit">Guardar Cambios</button>
    </form>
</body>
</html>