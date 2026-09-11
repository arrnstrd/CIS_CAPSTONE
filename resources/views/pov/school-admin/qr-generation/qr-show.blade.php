<!DOCTYPE html>
<html>
<head>
    <title>Student QR</title>
</head>
<body>

<h2>
    {{ $student->first_name }}
    {{ $student->last_name }}
</h2>

<p>
    {{ $student->student_number }}
</p>

<img
    src="{{ asset('storage/' . $student->qrCode->image_path) }}"
    width="220"
>

</body>
</html>