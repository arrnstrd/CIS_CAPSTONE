<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 12mm;
        }

        body {
            font-family: DejaVu Sans;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-spacing: 10px;
        }

        td {
            width: 50%;
            vertical-align: top;
        }

        .card {
            border: 1px solid #444;
            border-radius: 8px;
            padding: 16px 16px 20px;
            text-align: center;
            height: 420px;
            display: flex;
            flex-direction: column;
        }

        .logo {
            width: 55px;
            margin-bottom: 6px;
        }

        .school {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .qr {
            width: 170px;
            height: 170px;
            margin: 10px auto;
        }

        .section {
            font-size: 11px;
            margin-top: 6px;
        }

        .session {
            font-size: 11px;
            margin-bottom: 12px;
        }

        .student-name {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 10px;
        }

        .student-number {
            font-size: 12px;
            margin-top: 6px;
        }

        .footer {
            margin-top: auto;
            padding-top: 15px;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>

<body>
    <table>
        @foreach($students->chunk(2) as $row)
            <tr>
                @foreach($row as $student)
                    @php 
                        $enrollment = $student->enrollments->first(); 
                        $section = $enrollment?->section;
                    @endphp
                    <td>
                        <div class="card">
                            @if(file_exists(public_path('images/CIS-logo.png')))
                                <img class="logo" src="{{ public_path('images/CIS-logo.png') }}">
                            @endif

                            <div class="school">
                                CONCEPCION INTEGRATED SCHOOL
                            </div>
                            @if(!empty($student->qrImage))
                                <img class="qr" src="data:image/png;base64,{{ $student->qrImage }}">
                            @endif
                            <div class="section">
                                Grade {{ $section->grade_level ?? '-' }} - {{ $section->name ?? 'Unassigned' }}
                            </div>
                            <div class="session">
                                {{ ucfirst(str_replace('_', ' ', $section->session_type ?? 'Default Session')) }}
                            </div>
                            <div class="student-name">{{ $student->last_name }}, {{ $student->first_name }}</div>
                            <div class="student-number">Student No.<br>{{ $student->student_number }}</div>
                            <div class="footer">Present this QR code for attendance scanning.</div>
                        </div>
                    </td>
                @endforeach
                @if($row->count() == 1)
                    <td></td>
                @endif
            </tr>
        @endforeach
    </table>
</body>

</html>
