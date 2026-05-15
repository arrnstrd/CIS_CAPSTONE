<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CIS QR Scanner</title>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: #0f172a;
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 24px;
        }

        .header h1 {
            font-size: 1.4rem;
            font-weight: 700;
            color: #38bdf8;
            letter-spacing: 0.5px;
        }

        .header p {
            font-size: 0.85rem;
            color: #94a3b8;
            margin-top: 4px;
        }

        #reader {
            width: 100%;
            max-width: 360px;
            border-radius: 16px;
            overflow: hidden;
            border: 2px solid #1e3a5f;
        }

        #result-box {
            margin-top: 20px;
            width: 100%;
            max-width: 360px;
            border-radius: 12px;
            padding: 16px;
            font-size: 0.9rem;
            display: none;
            animation: fadeIn 0.3s ease;
        }

        #result-box.success {
            background: #052e16;
            border: 1px solid #16a34a;
            color: #4ade80;
        }

        #result-box.error {
            background: #2d0a0a;
            border: 1px solid #dc2626;
            color: #f87171;
        }

        #result-box.late {
            background: #2d1f00;
            border: 1px solid #d97706;
            color: #fbbf24;
        }

        .result-name {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .result-meta {
            font-size: 0.8rem;
            opacity: 0.8;
        }

        .badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 99px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 8px;
        }

        .badge-late {
            background: #d97706;
            color: #fff;
        }

        .badge-ontime {
            background: #16a34a;
            color: #fff;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>CIS Gate Scan Attendance</h1>
        <p>Concepcion Integrated School</p>
    </div>

    <div id="reader"></div>

    <div id="result-box">
        <div class="result-name" id="result-name"></div>
        <div class="result-meta" id="result-meta"></div>
        <span class="badge" id="result-badge"></span>
    </div>

    <script>
        const BASE_URL = "{{ config('app.url') }}";
        let isProcessing = false;

        function showResult(type, name, meta, badge, badgeClass) {
            const box = document.getElementById('result-box');
            box.className = `result-box ${type}`;
            box.style.display = 'block';
            document.getElementById('result-name').textContent = name;
            document.getElementById('result-meta').textContent = meta;
            const b = document.getElementById('result-badge');
            b.textContent = badge;
            b.className = `badge ${badgeClass}`;

            // auto hide after 4 seconds
            setTimeout(() => {
                box.style.display = 'none';
                isProcessing = false;
            }, 4000);
        }

        function onScanSuccess(code) {
            if (isProcessing) return;
            isProcessing = true;

            fetch(`/api/scan`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ code })
            })
            .then(res => res.json())
            .then(data => {
                if (data.message === 'Scan successful') {
                    const isLate = data.late;
                    showResult(
                        isLate ? 'late' : 'success',
                        data.student.name,
                        `${data.student.section} • ${data.attendance_log.scan_time}`,
                        isLate ? 'LATE' : 'ON TIME',
                        isLate ? 'badge-late' : 'badge-ontime'
                    );
                } else {
                    showResult('error', 'Scan Failed', data.message, 'ERROR', '');
                }
            })
            .catch(() => {
                showResult('error', 'Connection Error', 'Could not reach server', 'ERROR', '');
                isProcessing = false;
            });
        }

        const html5QrCode = new Html5Qrcode("reader");
        html5QrCode.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 250, height: 250 } },
            onScanSuccess
        );
    </script>
</body>
</html>