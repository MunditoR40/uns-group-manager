<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <title>
        Padrón {{ $practiceGroup->code }}
    </title>

    <style>
        @page {
            margin: 25px 32px 30px 32px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1f2937;
        }

        /* =========================
           CABECERA
        ========================== */

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .header-table td {
            border: none;
            vertical-align: middle;
        }

        .logo-cell {
            width: 90px;
            text-align: center;
        }

        .logo {
            width: 70px;
            height: auto;
        }

        .title-cell {
            text-align: center;
        }

        .universidad {
            font-size: 17px;
            font-weight: bold;
            color: #7a0f1d;
            margin-bottom: 3px;
        }

        .escuela {
            font-size: 10px;
            color: #4b5563;
            margin-bottom: 7px;
        }

        .documento {
            font-size: 13px;
            font-weight: bold;
            color: #7a0f1d;
            text-transform: uppercase;
        }

        .linea-header {
            border-top: 3px solid #7a0f1d;
            margin-bottom: 15px;
        }

        /* =========================
           INFORMACIÓN DEL CURSO
        ========================== */

        .info-box {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            background-color: #f8fafc;
        }

        .info-box td {
            border: 1px solid #cbd5e1;
            padding: 6px 7px;
        }

        .info-label {
            font-weight: bold;
            color: #7a0f1d;
            width: 95px;
        }

        /* =========================
           TABLA DE ESTUDIANTES
        ========================== */

        .students {
            width: 100%;
            border-collapse: collapse;
        }

        .students th {
            background-color: #7a0f1d;
            color: #ffffff;
            border: 1px solid #7a0f1d;
            padding: 6px 4px;
            font-size: 9px;
            text-align: center;
        }

        .students td {
            border: 1px solid #cbd5e1;
            padding: 5px 4px;
            font-size: 9px;
        }

        .students tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        .numero {
            width: 27px;
            text-align: center;
        }

        .codigo {
            width: 82px;
            text-align: center;
        }

        .nombre {
            width: auto;
        }

        .small-column {
            width: 55px;
            text-align: center;
        }

        .estado {
            width: 65px;
            text-align: center;
        }

        /* =========================
           RESUMEN
        ========================== */

        .summary {
            width: 100%;
            margin-top: 13px;
            border-collapse: collapse;
        }

        .summary td {
            border: none;
            padding: 3px 4px;
        }

        .summary-label {
            font-weight: bold;
            color: #7a0f1d;
        }

        .summary-value {
            font-weight: bold;
        }

        /* =========================
           FIRMAS
        ========================== */

        .signatures {
            width: 100%;
            border-collapse: collapse;
            margin-top: 55px;
        }

        .signatures td {
            width: 50%;
            border: none;
            text-align: center;
            vertical-align: bottom;
        }

        .signature-line {
            width: 190px;
            margin: 0 auto 5px auto;
            border-top: 1px solid #111827;
        }

        .signature-title {
            font-weight: bold;
            color: #7a0f1d;
        }

        /* =========================
           FOOTER
        ========================== */

        .footer {
            position: fixed;
            bottom: -13px;
            left: 0;
            right: 0;
            font-size: 7px;
            color: #64748b;
            border-top: 1px solid #e5bcbc;
            padding-top: 4px;
        }

        .footer-left {
            float: left;
        }

        .footer-right {
            float: right;
        }

    </style>

</head>

<body>

    {{-- ======================================================
         CABECERA
    ======================================================= --}}

    <table class="header-table">

        <tr>

            <td class="logo-cell">

                @php
                    $logoPath = public_path('images/logo-uns.png');
                @endphp

                @if (file_exists($logoPath))

                    <img
                        class="logo"
                        src="data:image/png;base64,{{ base64_encode(file_get_contents($logoPath)) }}"
                        alt="Logo UNS"
                    >

                @endif

            </td>


            <td class="title-cell">

                <div class="universidad">
                    UNIVERSIDAD NACIONAL DEL SANTA
                </div>

                <div class="escuela">
                    Sistema de Gestión y Reasignación de Grupos
                </div>

                <div class="documento">
                    Padrón Oficial de Estudiantes
                </div>

            </td>


            <td style="width: 90px;">
            </td>

        </tr>

    </table>

    <div class="linea-header"></div>


    {{-- ======================================================
         INFORMACIÓN ACADÉMICA
    ======================================================= --}}

    <table class="info-box">

        <tr>

            <td class="info-label">
                Curso
            </td>

            <td>
                {{ $practiceGroup->theoryGroup->course->name }}
            </td>


            <td class="info-label">
                Código
            </td>

            <td>
                {{ $practiceGroup->theoryGroup->course->code_course }}
            </td>

        </tr>


        <tr>

            <td class="info-label">
                Semestre
            </td>

            <td>
                {{ $practiceGroup->theoryGroup->course->semester }}
            </td>


            <td class="info-label">
                Teoría
            </td>

            <td>
                {{ $practiceGroup->theoryGroup->name }}
            </td>

        </tr>


        <tr>

            <td class="info-label">
                Práctica
            </td>

            <td>
                {{ $practiceGroup->code }}
            </td>


            <td class="info-label">
                Horario
            </td>

            <td>
                {{ $practiceGroup->schedule }}
            </td>

        </tr>

    </table>


    {{-- ======================================================
         ESTUDIANTES
    ======================================================= --}}

    <table class="students">

        <thead>

            <tr>

                <th class="numero">
                    N°
                </th>

                <th class="codigo">
                    Código UNS
                </th>

                <th class="nombre">
                    Apellidos y nombres
                </th>

                <th class="small-column">
                    Laptop
                </th>

                <th class="small-column">
                    Autorizado
                </th>

                <th class="estado">
                    Estado
                </th>

            </tr>

        </thead>


        <tbody>

            @forelse ($enrollments as $index => $enrollment)

                <tr>

                    <td class="numero">
                        {{ $index + 1 }}
                    </td>

                    <td class="codigo">
                        {{ $enrollment->user->code }}
                    </td>

                    <td class="nombre">
                        {{ $enrollment->user->name }}
                    </td>

                    <td class="small-column">
                        {{ $enrollment->has_laptop ? 'Sí' : 'No' }}
                    </td>

                    <td class="small-column">
                        {{ $enrollment->teacher_authorized ? 'Sí' : 'No' }}
                    </td>

                    <td class="estado">
                        {{ ucfirst($enrollment->status) }}
                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="6" style="text-align: center; padding: 15px;">
                        No existen estudiantes asignados a este grupo.
                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>


    {{-- ======================================================
         RESUMEN
    ======================================================= --}}

    <table class="summary">

        <tr>

            <td>
                <span class="summary-label">
                    Capacidad base:
                </span>

                <span class="summary-value">
                    {{ $practiceGroup->base_capacity }}
                </span>
            </td>

            <td style="text-align: right;">
                <span class="summary-label">
                    Total matriculados:
                </span>

                <span class="summary-value">
                    {{ $enrollments->count() }}
                </span>
            </td>

        </tr>

    </table>



    {{-- ======================================================
         FIRMAS
    ======================================================= --}}

    <table class="signatures">

        <tr>

            <td>

                <div class="signature-line"></div>

                <div class="signature-title">
                    Docente responsable
                </div>

            </td>


            <td>

                <div class="signature-line"></div>

                <div class="signature-title">
                    Delegado
                </div>

            </td>

        </tr>

    </table>


    {{-- ======================================================
         PIE DE PÁGINA
    ======================================================= --}}

    <div class="footer">

        <span class="footer-left">
            Sistema de Gestión y Reasignación de Grupos UNS
        </span>

        <span class="footer-right">
            Generado: {{ now()->format('d/m/Y H:i') }}
        </span>

    </div>

</body>

</html>