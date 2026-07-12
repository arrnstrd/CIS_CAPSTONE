<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">

    <title>Student QR Card</title>

    <style>
        @page {
            margin: 0;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            background: #ffffff;
            margin: 0;
            padding: 40px;
        }

        .card {
            width: 300px;
            margin: 0 auto;
            border: 1px solid #444;
            padding: 18px;
            text-align: center;
        }

        .logo {
            width: 55px;
            margin-bottom: 6px;
        }

        .school {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            line-height: 1.3;
            margin-bottom: 12px;
        }

        .qr {
            margin: 10px 0;
        }

        .qr img {
            width: 180px;
            height: 180px;
        }

        .section {
            font-size: 11px;
            margin-top: 10px;
        }

        .session {
            font-size: 11px;
            margin-bottom: 12px;
        }

        .student-name {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 8px;
        }

        .student-number {
            font-size: 12px;
            margin-top: 8px;
            line-height: 1.5;
        }

        .footer {
            margin-top: 18px;
            font-size: 10px;
           padding-bottom: 5px;
        }
    </style>

</head>

<body>

    @php
        $enrollment = $student->enrollments->first();
    @endphp

    <div class="card">

       <img class="logo" src="{{ public_path('images/CIS-logo.png') }}">

        <div class="school">
            CONCEPCION<br>
            INTEGRATED SCHOOL
        </div>

        <div class="qr">
            <img
                src="data:image/png;base64,{{ $qrImage }}"
                alt="QR Code">
        </div>

        <div class="section">
            {{ $enrollment?->section?->grade_level ?? '-' }}
            -
            {{ $enrollment?->section?->name ?? '-' }}
        </div>

        <div class="session">
            {{ ucfirst(str_replace('_', ' ', $enrollment?->session_type ?? '-')) }}
        </div>

        <div class="student-name">
            {{ strtoupper($student->last_name) }},
            {{ strtoupper($student->first_name) }}
            @if($student->middle_name)
                {{ strtoupper(substr($student->middle_name, 0, 1)) }}.
            @endif
        </div>

        <div class="student-number">
            <strong>Student No.</strong><br>
            {{ $student->student_number }}
        </div>

        <div class="footer">
            Present this QR code for attendance scanning.
        </div>

    </div>

</body>

</html>