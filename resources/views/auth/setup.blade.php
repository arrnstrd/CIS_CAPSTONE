<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Account Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .setup-card {
            max-width: 400px;
            width: 100%;
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .setup-logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        .setup-logo h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
        }
    </style>
</head>
<body>
    <div class="setup-card">
        <div class="setup-logo">
            <h1>Complete Account Setup</h1>
            <p class="text-muted">Create your password to access your account</p>
        </div>

        @if (!$isValid || empty($email))
            <div class="alert alert-warning text-center" role="alert">
                <h5 class="alert-heading fw-bold mb-2">Invalid or Expired Link</h5>
                <p class="mb-2 small">
                    This account setup link is invalid, has expired, or has already been replaced by a newer invitation.
                </p>
                <p class="mb-0 small text-muted">
                    If you recently requested a new invitation, please check your inbox for the newest email, or contact your administrator.
                </p>
            </div>
            <a href="{{ route('login') }}" class="btn btn-outline-secondary w-100">Return to Login</a>
        @else
            <form method="POST" action="{{ route('setup.submit', $token) }}">
                @csrf
                
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" value="{{ $email }}" readonly>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required autocomplete="new-password">
                    <div class="form-text">Password must be at least 8 characters long.</div>
                </div>

                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
                </div>

                @if (isset($errors) && $errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <button type="submit" class="btn btn-primary w-100">Complete Setup</button>
            </form>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>