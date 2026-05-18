<!DOCTYPE html>
<html>
<head>
    <title>Student QR</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 30px;
        }

        .card {
            border: 2px solid #000;
            padding: 20px;
            width: 300px;
            margin: auto;
        }

        .name {
            font-size: 18px;
            font-weight: bold;
        }

        .student-number {
            margin-top: 5px;
            font-size: 14px;
        }

        .qr {
            margin-top: 15px;
        }

        img {
            width: 180px;
            height: 180px;
        }
    </style>
</head>
<body>

<div class="card">

    <div class="name">
        {{ $student->first_name }} {{ $student->last_name }}
    </div>

    <div class="student-number">
        {{ $student->student_number }}
    </div>

    <div class="qr">
        <img src="data:image/png;base64,{{ $qrImage }}">
    </div>

</div>

</body>
</html>