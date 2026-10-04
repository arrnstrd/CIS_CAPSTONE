<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password - Concepcion Integrated School</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
    }

    body {
      background-color: #f9fafb;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    .header {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 24px 40px;
      text-align: left;
    }

    img.logo {
      width: 60px;
      height: auto;
    }

    .school-name {
      font-size: 20px;
      font-weight: bold;
      color: #1e3a8a;
      margin: 0;
    }

    .page-wrapper {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 40px 20px;
    }

    .card-container {
      width: 100%;
      max-width: 440px;
      background: #ffffff;
      border: 1px solid #e5e7eb;
      border-radius: 10px;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
      padding: 36px 32px;
    }

    .form-title {
      font-size: 26px;
      font-weight: bold;
      text-align: center;
      color: #1f2937;
      margin-bottom: 10px;
    }

    .form-subtitle {
      text-align: center;
      color: #6b7280;
      font-size: 14px;
      line-height: 1.5;
      margin-bottom: 28px;
    }

    .input-group {
      margin-bottom: 22px;
    }

    .input-group label {
      display: block;
      font-size: 14px;
      font-weight: 500;
      color: #374151;
      margin-bottom: 6px;
    }

    .input-group input {
      width: 100%;
      padding: 13px 14px;
      border: 1px solid #d1d5db;
      border-radius: 6px;
      font-size: 14px;
      transition: border-color 0.15s ease-in-out;
    }

    .input-group input:focus {
      outline: none;
      border-color: #3b82f6;
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
    }

    .btn-submit {
      width: 100%;
      padding: 14px;
      background-color: #3b82f6;
      color: white;
      font-size: 15px;
      font-weight: bold;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      letter-spacing: 0.3px;
      transition: background-color 0.15s ease-in-out;
    }

    .btn-submit:hover {
      background-color: #2563eb;
    }

    .btn-secondary {
      display: inline-block;
      width: 100%;
      padding: 13px;
      background-color: #f3f4f6;
      color: #374151;
      font-size: 14px;
      font-weight: 600;
      text-align: center;
      text-decoration: none;
      border-radius: 6px;
      margin-top: 12px;
      transition: background-color 0.15s ease-in-out;
    }

    .btn-secondary:hover {
      background-color: #e5e7eb;
    }

    .btn-resend {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 100%;
      padding: 13px;
      background-color: #eff6ff;
      color: #1d4ed8;
      border: 1px solid #bfdbfe;
      font-size: 14px;
      font-weight: 600;
      border-radius: 6px;
      cursor: pointer;
      margin-bottom: 12px;
      transition: all 0.15s ease-in-out;
    }

    .btn-resend:hover {
      background-color: #dbeafe;
      border-color: #93c5fd;
    }

    .back-to-login {
      display: block;
      text-align: center;
      margin-top: 24px;
      font-size: 14px;
      color: #4b5563;
      text-decoration: none;
      transition: color 0.15s ease-in-out;
    }

    .back-to-login:hover {
      color: #1e3a8a;
      text-decoration: underline;
    }

    .alert {
      padding: 14px 16px;
      border-radius: 6px;
      font-size: 14px;
      line-height: 1.5;
      margin-bottom: 22px;
    }

    .alert-success {
      background-color: #f0fdf4;
      border: 1px solid #bbf7d0;
      color: #166534;
    }

    .alert-danger {
      background-color: #fef2f2;
      border: 1px solid #fecaca;
      color: #b91c1c;
    }

    .alert-danger ul {
      list-style: none;
      padding-left: 0;
      margin: 0;
    }

    .alert-danger li {
      margin-top: 4px;
    }

    .alert-danger li:first-child {
      margin-top: 0;
    }

    .status-icon-box {
      width: 52px;
      height: 52px;
      border-radius: 50%;
      background-color: #eff6ff;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 16px auto;
      color: #2563eb;
    }

    .status-icon-box svg {
      width: 28px;
      height: 28px;
    }
  </style>
</head>
<body>

  <!-- HEADER / LOGO -->
  <header class="header">
    <img src="{{ asset('./images/CIS-logo.png') }}" alt="Concepcion Integrated School Logo" class="logo">
    <h2 class="school-name">CONCEPCION INTEGRATED SCHOOL</h2>
  </header>

  <!-- MAIN PAGE WRAPPER -->
  <div class="page-wrapper">
    <div class="card-container">

      @if (session('status'))
        <!-- STATE 2: GENERIC CONFIRMATION -->
        <div class="status-icon-box">
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
          </svg>
        </div>

        <h1 class="form-title">Check Your Email</h1>

        <div class="alert alert-success">
          {{ session('status') }}
        </div>

        <p class="form-subtitle" style="margin-bottom: 20px;">
          The reset link will remain valid for 60 minutes. If you don't see the email within a few moments, please check your spam or junk folder.
        </p>

        @if (session('submitted_email'))
          <form action="{{ route('password.email') }}" method="POST">
            @csrf
            <input type="hidden" name="email" value="{{ session('submitted_email') }}">
            <button type="submit" class="btn-resend">
              <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width: 16px; height: 16px; margin-right: 6px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              Resend Reset Link
            </button>
          </form>
        @endif

        <a href="{{ route('login') }}" class="btn-secondary">Return to Sign In</a>

        <a href="{{ route('password.request') }}" class="back-to-login">Use a different email address</a>
      @else
        <!-- STATE 1: ENTER EMAIL -->
        <h1 class="form-title">Forgot Password?</h1>
        <p class="form-subtitle">Enter your account's verified email address and we'll send you a link to reset your password.</p>

        @if ($errors->any())
          <div class="alert alert-danger">
            <ul>
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form action="{{ route('password.email') }}" method="POST" accept-charset="utf-8">
          @csrf

          <div class="input-group">
            <label for="email">Email Address</label>
            <input
              type="email"
              id="email"
              name="email"
              value="{{ old('email') }}"
              required
              autofocus
              placeholder="name@cis.edu.ph"
            >
          </div>

          <button type="submit" class="btn-submit">Send Reset Link</button>

          <a href="{{ route('login') }}" class="back-to-login">← Back to Sign In</a>
        </form>
      @endif

    </div>
  </div>

</body>
</html>
