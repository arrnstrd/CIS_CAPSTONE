<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Expired — Concepcion Integrated School</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background-color: #f8fafc;
            color: #0f172a;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 24px;
        }
        .error-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            max-width: 520px;
            width: 100%;
            padding: 40px;
            text-align: center;
            border: 1px solid #e2e8f0;
        }
        .error-icon-wrapper {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background-color: #fef3c7;
            color: #d97706;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin-bottom: 20px;
        }
        .error-badge {
            display: inline-block;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #d97706;
            background: #fffbeb;
            padding: 4px 12px;
            border-radius: 9999px;
            margin-bottom: 12px;
        }
        .error-title {
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 10px;
        }
        .error-description {
            font-size: 15px;
            color: #64748b;
            line-height: 1.6;
            margin: 0 0 28px;
        }
        .error-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn-primary-action {
            background-color: #1e3a8a;
            color: #ffffff;
            border: none;
            padding: 10px 22px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.2s ease;
        }
        .btn-primary-action:hover {
            background-color: #1e40af;
            color: #ffffff;
        }
        .btn-secondary-action {
            background-color: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }
        .btn-secondary-action:hover {
            background-color: #e2e8f0;
            color: #1e293b;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-icon-wrapper">
            <i class="fa-solid fa-clock-rotate-left"></i>
        </div>
        <div>
            <span class="error-badge">Session Expired</span>
        </div>
        <h1 class="error-title">Your Session Has Expired</h1>
        <p class="error-description">
            For your security, sessions automatically expire after a period of inactivity. Please log in again to continue working.
        </p>
        <div class="error-actions">
            <a href="{{ route('login') }}" class="btn-primary-action">
                <i class="fa-solid fa-right-to-bracket"></i> Log In Again
            </a>
        </div>
    </div>
</body>
</html>
