@vite(['resources/css/app.css', 'resources/js/app.js'])

<nav id="sidebar" class="sidebar-wrapper">

    <div class="sidebar-content">

        <div class="sidebar-brand  ">
            <!-- Brand -->
            @php
                $teacherUser = auth()->user();
                $teacherProfile = $teacherUser?->teacher;
                $teacherName = $teacherProfile?->full_name ?? ($teacherUser?->first_name . ' ' . $teacherUser?->last_name);
                $teacherEmail = $teacherUser?->email ?? '';
            @endphp
            <div class="sidebar-teacher-info">
                <div class="sidebar-teacher-avatar">
                    <i class="fa-solid fa-circle-user"></i>
                </div>
                <div class="sidebar-teacher-details">
                    <span class="sidebar-teacher-name">{{ $teacherName }}</span>
                    <span class="sidebar-teacher-email">{{ $teacherEmail }}</span>
                </div>
            </div>

        </div>


        <!-- Navigation Menu -->
        <div class="sidebar-menu">
            <ul>

                <!-- MONITORING SECTION -->
                <li class="sidebar-section-label">
                    <small>General</small>
                </li>
                <li>
                    <a href="{{ route('room-attendance.index') }}">
                        <i class="fas fa-clipboard-check"></i>
                        <span>Room Attendance</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('teacher.student-management') }}">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span>Student Management</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('teacher.grading-system.dashboard') }}">
                        <i class="fas fa-user-graduate"></i>
                        <span>Grading System</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('teacher.grading-system') }}">
                        <i class="fas fa-chart-line"></i>
                        <span>Analytics</span>
                    </a>
                </li>

                {{-- <li>
                    <a href="#">
                        <i class="fas fa-qrcode"></i>
                        <span>QR Generation</span>
                    </a>
                </li> --}}
                {{-- <li>
                    <a href="/reports">
                        <i class="fas fa-chart-bar"></i>
                        <span>Report Generation</span>
                    </a>
                </li> --}}

                <!-- SYSTEM SECTION -->
                <li class="sidebar-section-label">
                    <small>SYSTEM</small>
                </li>
                <li>
                    <a href="#">
                        <i class="fas fa-sliders-h"></i>
                        <span>Settings</span>
                    </a>
                </li>

            </ul>
        </div>

    </div>

    <!-- Logout -->
    <div class="sidebar-footer p-3">
        <a href="#" data-bs-toggle="modal" data-bs-target="#logoutModal"
            class="btn-logout-action w-100 d-flex align-items-center justify-content-center gap-2">
            <i class="fas fa-sign-out-alt"></i>
            <span>Log Out</span>
        </a>
    </div>

</nav>