<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <title>
        Padrón {{ $practiceGroup->code }}
    </title>

    <style>
        @page {
            margin: 28px 32px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #111;
        }

        .header {
            text-align: center;
            margin-bottom: 18px;
        }

        .universidad {
            font-size: 16px;
            font-weight: bold;
            margin: 0;
        }

        .documento {
            font-size: 13px;
            font-weight: bold;
            margin-top: 7px;
        }

        .periodo {
            font-size: 10px;
            margin-top: 4px;
        }

        .datos {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        .datos td {
            padding: 3px 5px;
            border: none;
        }

        .datos .label {
            font-weight: bold;
            width: 110px;
        }

        .lista {
            width: 100%;
            border-collapse: collapse;
        }

        .lista th,
        .lista td {
            border: 1px solid #555;
            padding: 5px 4px;
        }

        .lista th {
            background-color: #e7e7e7;
            text-align: center;
            font-weight: bold;
        }

        .numero {
            width: 28px;
            text-align: center;
        }

        .codigo {
            width: 85px;
            text-align: center;
        }

        .centrado {
            text-align: center;
        }

        .resumen {
            margin-top: 12px;
            width: 100%;
        }

        .resumen td {
            padding: 2px 0;
        }

        .resumen .label {
            font-weight: bold;
            width: 150px;
        }

        .fecha {
            text-align: right;
            margin-top: 15px;
            font-size: 9px;
        }

        .firmas {
            margin-top: 60px;
            width: 100%;
        }

        .firma {
            width: 45%;
            display: inline-block;
            text-align: center;
        }

        .firma-derecha {
            float: right;
        }

        .linea {
            margin-bottom: 5px;
        }

        .nota {
            margin-top: 16px;
            font-size: 8px;
            color: #555;
        }
    </style>
</head>

<body>

    <div class="header">

        <p class="universidad">
            UNIVERSIDAD NACIONAL DEL SANTA
        </p>

        <div class="documento">
            PADRÓN OFICIAL DE ESTUDIANTES
        </div>

        <div class="periodo">
            Semestre académico:
            {{ $practiceGroup->theoryGroup->course->semester }}
        </div>

    </div>


    <table class="datos">

        <tr>
            <td class="label">
                Curso:
            </td>

            <td>
                {{ $practiceGroup->theoryGroup->course->name }}
            </td>

            <td class="label">
                Código:
            </td>

            <td>
                {{ $practiceGroup->theoryGroup->course->code_course }}
            </td>
        </tr>


        <tr>
            <td class="label">
                Teoría:
            </td>

            <td>
                {{ $practiceGroup->theoryGroup->name }}
            </td>

            <td class="label">
                Práctica:
            </td>

            <td>
                {{ $practiceGroup->code }}
            </td>
        </tr>


        <tr>
            <td class="label">
                Horario:
            </td>

            <td colspan="3">
                {{ $practiceGroup->schedule }}
            </td>
        </tr>

    </table>


    <table class="lista">

        <thead>

            <tr>
                <th class="numero">
                    N°
                </th>

                <th class="codigo">
                    Código UNS
                </th>

                <th>
                    Apellidos y nombres
                </th>

                <th>
                    Laptop
                </th>

                <th>
                    Autorizado
                </th>

                <th>
                    Estado
                </th>

            </tr>

        </thead>


        <tbody>

            @foreach ($enrollments as $index => $enrollment)

                <tr>

                    <td class="numero">
                        {{ $index + 1 }}
                    </td>

                    <td class="codigo">
                        {{ $enrollment->user->code }}
                    </td>

                    <td>
                        {{ $enrollment->user->name }}
                    </td>

                    <td class="centrado">
                        {{ $enrollment->has_laptop ? 'Sí' : 'No' }}
                    </td>

                    <td class="centrado">
                        {{ $enrollment->teacher_authorized ? 'Sí' : 'No' }}
                    </td>

                    <td class="centrado">
                        {{ ucfirst($enrollment->status) }}
                    </td>

                </tr>

            @endforeach

        </tbody>

    </table>


    <table class="resumen">

        <tr>
            <td class="label">
                Capacidad base:
            </td>

            <td>
                {{ $practiceGroup->base_capacity }}
                estudiantes
            </td>
        </tr>

        <tr>
            <td class="label">
                Total matriculados:
            </td>

            <td>
                {{ $enrollments->count() }}
                estudiantes
            </td>
        </tr>

    </table>


    <div class="fecha">

        Documento generado:
        {{ now()->format('d/m/Y H:i') }}

    </div>


    <div class="firmas">

        <div class="firma">

            <div class="linea">
                ______________________________
            </div>

            Docente responsable

        </div>


        <div class="firma firma-derecha">

            <div class="linea">
                ______________________________
            </div>

            Delegado

        </div>

    </div>


    <div class="nota">

        Documento generado por el Sistema de Gestión y Reasignación
        de Grupos de la Universidad Nacional del Santa.

    </div>

</body>

</html>