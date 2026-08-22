<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Account Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .setup-card {
            max-width: 400px;
            width: 100%;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="setup-card">
        <h2 class="text-center mb-3">Complete Account Setup</h2>
        <p class="text-center text-muted mb-4">Set your password to activate your account</p>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('setup.submit', $token) }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" class="form-control-plaintext fw-bold px-2 py-1 bg-light rounded border" 
                       value="{{ $email ?? '' }}" readonly disabled>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control" 
                       minlength="8" required>
                <small class="form-text text-muted">
                    Minimum 8 characters, must contain uppercase, lowercase, numbers and symbols
                </small>
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Confirm Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" 
                       class="form-control" required>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-primary w-100">Complete Setup</button>
            </div>
        </form>
    </div>
</body>
</html>