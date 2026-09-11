<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password - Concepcion Integrated School</title>
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
      margin-bottom: 20px;
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

    .input-hint {
      font-size: 12px;
      color: #6b7280;
      margin-top: 5px;
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
      margin-top: 8px;
      transition: background-color 0.15s ease-in-out;
    }

    .btn-submit:hover {
      background-color: #2563eb;
    }

    .btn-action {
      display: inline-block;
      width: 100%;
      padding: 13px;
      background-color: #3b82f6;
      color: white;
      font-size: 14px;
      font-weight: 600;
      text-align: center;
      text-decoration: none;
      border-radius: 6px;
      transition: background-color 0.15s ease-in-out;
    }

    .btn-action:hover {
      background-color: #2563eb;
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
      background-color: #fee2e2;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 16px auto;
      color: #dc2626;
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

      @if (!$isValid)
        <!-- INVALID OR EXPIRED TOKEN STATE -->
        <div class="status-icon-box">
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
          </svg>
        </div>

        <h1 class="form-title">Link Invalid or Expired</h1>

        <div class="alert alert-danger" style="text-align: center;">
          This password reset link is invalid, has expired (tokens are valid for 60 minutes), or has already been used.
        </div>

        <p class="form-subtitle" style="margin-bottom: 24px;">
          For your security, password reset links can only be used once. Please request a new link to proceed.
        </p>

        <a href="{{ route('password.request') }}" class="btn-action">Request New Reset Link</a>

        <a href="{{ route('login') }}" class="back-to-login">← Return to Sign In</a>
      @else
        <!-- STATE 3: SET NEW PASSWORD FORM -->
        <h1 class="form-title">Set New Password</h1>
        <p class="form-subtitle">Choose a strong, unique password to secure your account.</p>

        @if ($errors->any())
          <div class="alert alert-danger">
            <ul>
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form action="{{ route('password.update') }}" method="POST" accept-charset="utf-8">
          @csrf

          <input type="hidden" name="token" value="{{ $token }}">

          <div class="input-group">
            <label for="email">Email Address</label>
            <input
              type="email"
              id="email"
              name="email"
              value="{{ old('email', $email) }}"
              required
              readonly
              style="background-color: #f3f4f6; cursor: not-allowed;"
            >
          </div>

          <div class="input-group">
            <label for="password">New Password</label>
            <input
              type="password"
              id="password"
              name="password"
              required
              autofocus
              autocomplete="new-password"
              placeholder="••••••••"
            >
            <p class="input-hint">Must be at least 8 characters long.</p>
          </div>

          <div class="input-group">
            <label for="password_confirmation">Confirm New Password</label>
            <input
              type="password"
              id="password_confirmation"
              name="password_confirmation"
              required
              autocomplete="new-password"
              placeholder="••••••••"
            >
          </div>

          <button type="submit" class="btn-submit">Reset Password</button>

          <a href="{{ route('login') }}" class="back-to-login">← Cancel and Return to Sign In</a>
        </form>
      @endif

    </div>
  </div>

</body>
</html>
