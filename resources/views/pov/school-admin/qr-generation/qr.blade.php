<!DOCTYPE html>
<html>
<head>
    <title>Student QR</title>
    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            width: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f6fb;
        }

        .page-shell {
            margin: 0 1rem;
            padding: 1rem 0 1.5rem;
        }

        .card {
            border: 1px solid #d7dce8;
            padding: 28px;
            width: 100%;
            max-width: 520px;
            margin: 0 auto;
            text-align: center;
            border-radius: 18px;
            background: #ffffff;
            box-shadow: 0 10px 30px rgba(29, 41, 62, 0.08);
        }

        .name {
            font-size: 20px;
            font-weight: 700;
            color: #172033;
        }

        .student-number {
            margin-top: 5px;
            font-size: 14px;
            color: #6b7280;
        }

        .qr {
            margin-top: 22px;
        }

        img {
            width: 220px;
            height: 220px;
            max-width: 100%;
        }
    </style>
</head>
<body>

<div class="page-shell mx-3">
    <div class="card">

        <div class="name">
            {{ $student->first_name }} {{ $student->last_name }}
        </div>

        <div class="student-number">
            {{ $student->student_number }}
        </div>

        <div class="qr">
            <img src="data:image/png;base64,{{ $qrImage }}" alt="Student QR Code">
        </div>

    </div>
</div>


</body>
</html>
