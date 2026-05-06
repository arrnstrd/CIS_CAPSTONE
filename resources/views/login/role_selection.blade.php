<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CIS | Choose Portal</title>
    <link rel="icon" type="image/png" href="/assets/images/school-logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        /* Minimal Custom CSS to supplement Bootstrap */
        body {
            background: linear-gradient(135deg, #1a1d2e 0%, #252a41 100%);
            background-attachment: fixed;
        }
        
        .ls-2 { 
            letter-spacing: 2px; 
        }

        /* Hover Elevation Effect */
        .hover-card {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s ease;
        }
        
        .hover-card:hover {
            transform: translateY(-10px);
        }
        
        /* Icon hover transitions */
        .icon-wrapper {
            transition: all 0.3s ease;
        }
        
        .admin-card:hover .icon-wrapper {
            background-color: #0d6efd !important; /* Bootstrap Primary Blue */
            color: #fff !important;
        }
        
        .teacher-card:hover .icon-wrapper {
            background-color: #198754 !important; /* Bootstrap Success Green */
            color: #fff !important;
        }
    </style>
</head>
<body> 
    
    <div class="container vh-100 d-flex flex-column justify-content-center align-items-center">
        
        <!-- Header -->
        <div class="text-center mb-5">
            <img src="/assets/images/cis-logo-w-bg.png" alt="CIS Logo" class="mb-3 rounded-circle shadow-sm" style="width: 120px;">
            <h2 class="text-white fw-bold mb-0">CONCEPCION INTEGRATED SCHOOL</h2>
            <p class="text-white-50 text-uppercase small ls-2 mt-1">Centralized Management System</p>
        </div>

        <div class="row g-4 justify-content-center w-100" style="max-width: 900px;">
            
            <!-- Admin Role Card -->
            <div class="col-md-5">
                <a href="/login-portal/admin-login.html" class="text-decoration-none h-100 d-block admin-card hover-card">
                    <!-- Added rounded-4 and standard shadow -->
                    <div class="card border-0 h-100 p-4 text-center rounded-4 shadow">
                        
                        <!-- Using bg-primary and bg-opacity-10 for the icon wrapper -->
                        <div class="icon-wrapper rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4 bg-primary bg-opacity-10 text-primary" style="width: 80px; height: 80px; font-size: 2rem;">
                            <i class="fa-solid fa-user-shield"></i>
                        </div>
                        
                        <h3 class="fw-bold text-dark">Administrator</h3>
                        <p class="text-muted small mb-4">Manage school data, student records, and system settings.</p>
                        
                        <div class="mt-auto">
                            <!-- Standard Bootstrap Primary Button -->
                            <span class="btn btn-primary w-100 fw-bold py-2 rounded-3">Enter Admin Portal</span>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Teacher Role Card -->
            <div class="col-md-5">
                <a href="/login-portal/teacher-login.html" class="text-decoration-none h-100 d-block teacher-card hover-card">
                    <div class="card border-0 h-100 p-4 text-center rounded-4 shadow">
                        
                        <!-- Using bg-success and bg-opacity-10 for the teacher icon wrapper -->
                        <div class="icon-wrapper rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4 bg-success bg-opacity-10 text-success" style="width: 80px; height: 80px; font-size: 2rem;">
                            <i class="fa-solid fa-chalkboard-teacher"></i>
                        </div>
                        
                        <h3 class="fw-bold text-dark">Teacher</h3>
                        <p class="text-muted small mb-4">Track classroom attendance, view logs, and manage sections.</p>
                        
                        <div class="mt-auto">
                            <!-- Standard Bootstrap Outline Success Button -->
                            <span class="btn btn-outline-success w-100 fw-bold border-2 py-2 rounded-3">Enter Teacher Portal</span>
                        </div>
                    </div>
                </a>
            </div>

        </div>

        <div class="mt-5 text-white-50 small">
            &copy; 2026 Concepcion Integrated School Portal
        </div>
        
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>