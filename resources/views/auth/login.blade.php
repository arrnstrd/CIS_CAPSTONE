<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In - Concepcion Integrated School</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
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
      display: flex;
      min-height: calc(100vh - 140px);
    }

    .left-column {
      flex: 1;
      min-width: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
      overflow: hidden;
    }

    .illustration {
      max-width: 95%;
      max-height: 85vh;
      width: auto;
      height: auto;
      display: block;
      object-fit: contain;
      margin-left: 20px;
    }

    .right-column {
      flex: 1;
      min-width: 0;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .form-container {
      width: 80%;
      max-width: 400px;
      padding: 10px 20px 20px 20px;
      margin-top: -60px;
    }

    .form-title {
      font-size: 36px;
      font-weight: bold;
      text-align: center;
      color: #1f2937;
      margin-bottom: 8px;
    }

    .form-subtitle {
      text-align: center;
      color: #6b7280;
      font-size: 14px;
      line-height: 1.5;
      padding: 0 20px;
      margin-bottom: 36px;
    }

    .input-group {
      margin-bottom: 22px;
    }

    .input-group label {
      display: block;
      font-size: 14px;
      color: #374151;
      margin-bottom: 6px;
    }

    .input-group input {
      width: 100%;
      padding: 13px 14px;
      border: 1px solid #d1d5db;
      border-radius: 6px;
      font-size: 14px;
    }

    .forgot-password {
      display: block;
      font-size: 13px;
      color: #6b7280;
      text-decoration: none;
      margin-bottom: 10px;
      text-align: left;
    }

    .btn-signin {
      width: 100%;
      padding: 15px;
      background-color: #3b82f6;
      color: white;
      font-size: 16px;
      font-weight: bold;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      letter-spacing: 0.3px;
    }

    .alert {
      padding: 12px 16px;
      border-radius: 6px;
      font-size: 14px;
      margin-bottom: 20px;
    }

    .alert-danger {
      background-color: #fef2f2;
      border: 1px solid #fecaca;
      color: #b91c1c;
    }

    .alert-success {
      background-color: #f0fdf4;
      border: 1px solid #bbf7d0;
      color: #166534;
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

    .demo-select {
      width: 100%;
      padding: 13px 14px;
      border: 1px solid #d1d5db;
      border-radius: 6px;
      font-size: 14px;
      background-color: #fff;
      color: #374151;
      cursor: pointer;
      appearance: auto;
    }

    .demo-hint {
      font-size: 12px;
      color: #9ca3af;
      margin-top: 6px;
    }
  </style>
</head>
<body>

  <!-- HEADER / LOGO -->
  <header class="header">
    <img src="{{ asset('./images/CIS-logo.png') }}" alt="Concepcion Integrated School Logo" class="logo">
    <h2 class="school-name">CONCEPCION INTEGRATED SCHOOL</h2>
  </header>

  <!-- PAGE WRAPPER (2 columns) -->
  <div class="page-wrapper">

    <!-- LEFT COLUMN: Illustration -->
    <div class="left-column">
      <img src="{{ asset('./images/login-vector.png') }}" alt="People working on dashboard illustration" class="illustration">
    </div>

    <!-- RIGHT COLUMN: Form Container -->
    <div class="right-column">
      <div class="form-container">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('login.attempt') }}" method="POST" accept-charset="utf-8">
          @csrf
          <h1 class="form-title">Sign In</h1>
          <p class="form-subtitle">Enter your credentials to access your assigned dashboard.</p>

          <div class="input-group">
            <label for="demo-role">Quick Demo Access</label>
            <select id="demo-role" class="form-control demo-select">
              <option value="" selected disabled>Select a role to auto-fill…</option>

              <!-- <option value="super_admin">Super Admin Account</option>
               -->
              <option value="school_admin">School Admin Account</option>
              <option value="teacher">Teacher Account</option>
              <option value="scanner_operator">Scanner Operator Account</option>
            </select>
            <p class="demo-hint">Picking an account fills in the credentials and signs you in automatically.</p>
          </div>

          <div class="input-group">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required autofocus>
          </div>

          <div class="input-group">
            <label for="password">Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
          </div>

          <a href="{{ route('password.request') }}" class="forgot-password">Forgot Password?</a>

          <button type="submit" class="btn-signin">Sign In</button>
        </form>

      </div>
    </div>

  </div>

  <script>
    const demoAccounts = {
      super_admin: { email: 'superadmin@cis.edu.ph', password: 'ProtectedAdminAccount2026#' },
      school_admin: { email: 'schooladmin@cis.edu.ph', password: 'SchoolAdminPassword2026#' },
      teacher: { email: 'teacher@cis.edu.ph', password: 'TeacherPassword2026#' },
      scanner_operator: { email: 'scanner@cis.edu.ph', password: 'ScannerPassword2026#' }
    };

    const roleSelect = document.getElementById('demo-role');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');

    roleSelect.addEventListener('change', function () {
      const account = demoAccounts[this.value];
      if (!account) return;

      emailInput.value = account.email;
      passwordInput.value = account.password;

      setTimeout(function () {
        roleSelect.closest('form').submit();
      }, 150);
    });
  </script>

</body>
</html>
