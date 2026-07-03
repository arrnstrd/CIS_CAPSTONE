@vite(['resources/css/app.css', 'resources/css/login.css', 'resources/js/app.js'])


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>CIS – Sign In</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>

</head>
<body>

<div class="page-wrapper">

  <!-- Top bar -->
  <div class="top-bar">
    <div class="logo-placeholder">LOGO</div>
    <span class="school-name">Concepcion Integrated School</span>
  </div>

  <!-- Main -->
  <div class="main-content">

    <!-- Illustration -->
    <div class="illustration-col">
      <div class="illustration-placeholder">
        <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect x="4" y="10" width="56" height="40" rx="4" stroke="#7fa8cc" stroke-width="3"/>
          <circle cx="20" cy="27" r="6" stroke="#7fa8cc" stroke-width="2.5"/>
          <path d="M4 42 L17 29 L29 39 L40 26 L60 42" stroke="#7fa8cc" stroke-width="2.5" stroke-linejoin="round"/>
        </svg>
       <img src="/images/login-vector.png"> 
      </div>
    </div>

    <!-- Form -->
    <div class="form-col">
      <h1>Sign In</h1>
      <p class="subtitle">Admin Access</p>

      <div class="mb-3">
        <label class="form-label" for="emailOrId">Email or User ID</label>
        <input type="text" class="form-control" id="emailOrId" />
      </div>

      <div class="mb-0">
        <label class="form-label" for="password">Password</label>
        <input type="password" class="form-control" id="password" />
      </div>

      <a href="#" class="forgot-link">Forgot Password?</a>

      <button class="btn-signin">Sign In</button>

      <div class="divider">OR</div>

      <p class="not-admin">Not an administrator?</p>
      <a href="#" class="switch-link">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M7 16V4m0 0L3 8m4-4l4 4"/>
          <path d="M17 8v12m0 0l4-4m-4 4l-4-4"/>
        </svg>
        Switch to Teacher Portal
      </a>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
