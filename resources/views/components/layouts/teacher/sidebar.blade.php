      <nav id="sidebar" class="sidebar-wrapper">
        <div class="sidebar-content">
          <!-- Brand -->
          <div class="sidebar-brand d-flex flex-column py-4 px-4">
            <h5 class="text-white mb-0 fw-bold">ADMIN PORTAL</h5>
            <small
              class="text-uppercase fw-semibold"
              style="font-size: 0.6rem; letter-spacing: 1px"
            >
              CONCEPCION INTEGRATED SCHOOL
            </small>
          </div>

          <!-- Navigation Menu -->
          <div class="sidebar-menu">
            <ul>
              <!-- Dashboard -->
              <li class="sidebar">
                <a href="/admin/monitoring/dashboard.html">
                  <i class="fa fa-th-large"></i>
                  <span>Dashboard</span>
                </a>
              </li>

              <!-- Monitoring -->
              <li class="sidebar-dropdown">
                <a href="javascript:void(0)">
                  <i class="fa fa-desktop"></i>
                  <span>Monitoring</span>
                </a>
                <div class="sidebar-submenu">
                  <ul>
                    <li>
                      <a href="/admin/monitoring/in-out.html">
                        <i class="fa fa-exchange-alt me-2"></i>In/Out Monitoring
                      </a>
                    </li>
                    <li>
                      <a
                        href="/admin/monitoring/classroom_attendance/grade-level-overview.html"
                      >
                        <i class="fa fa-user-check me-2"></i>Class Attendance
                      </a>
                    </li>
                    <li>
                      <a href="/admin/monitoring/flagged-scans.html">
                        <i class="fa fa-exclamation-triangle me-2"></i>Flagged
                        Scans
                      </a>
                    </li>
                    <li>
                      <a href="/admin/monitoring/email-monitoring.html">
                        <i class="fa fa-envelope-open-text me-2"></i>Email
                        Monitoring
                      </a>
                    </li>
                  </ul>
                </div>
              </li>

              <!-- Management -->
              <li class="sidebar-dropdown">
                <a href="javascript:void(0)">
                  <i class="fa fa-users-cog"></i>
                  <span>Management</span>
                </a>
                <div class="sidebar-submenu">
                  <ul>
                    <li>
                      <a
                        href="/admin/management/student_management/grade-level-overview.html"
                      >
                        <i class="fa fa-user-graduate me-2"></i>Student
                        Management
                      </a>
                    </li>
                    <li>
                      <a
                        href="/admin/management/teacher_management/teacher_list.html"
                      >
                        <i class="fa fa-chalkboard-teacher me-2"></i>Teacher
                        Management
                      </a>
                    </li>
                    <li>
                      <a href="/admin/management/user_role.html">
                        <i class="fa fa-user-shield me-2"></i>User Role
                        Management
                      </a>
                    </li>
                    <li>
                      <a href="/admin/management/grade-management.html">
                        <i class="fa-solid fa-file-signature me-2"></i>Grade
                        Management
                      </a>
                    </li>
                  </ul>
                </div>
              </li>

              <!-- Others -->
              <li class="sidebar-dropdown">
                <a href="javascript:void(0)">
                  <i class="fa fa-folder-plus"></i>
                  <span>Others</span>
                </a>
                <div class="sidebar-submenu">
                  <ul>
                    <li>
                      <a href="/admin/others/inOut_history.html">
                        <i class="fa fa-history me-2"></i>In/Out History
                      </a>
                    </li>
                    <li>
                      <a href="/admin/others/qr-generation.html">
                        <i class="fa fa-qrcode me-2"></i>QR Generation
                      </a>
                    </li>
                    <li>
                      <a href="/admin/others/report-generation.html">
                        <i class="fa fa-file-contract me-2"></i>Report
                        Generation
                      </a>
                    </li>
                  </ul>
                </div>
              </li>

              <!-- SYSTEM label -->
              <li class="sidebar-section-label">
                <small>SYSTEM</small>
              </li>

              <!-- Settings -->
              <li class="sidebar">
                <a href="/admin/others/settings.html">
                  <i class="fa fa-cog"></i>
                  <span>Settings</span>
                </a>
              </li>
            </ul>
          </div>
          <!-- END: Navigation Menu -->
        </div>

        <!-- Log Out — pinned to bottom -->
        <div class="sidebar-footer p-3">
          <a
            href="#"
            data-bs-toggle="modal"
            data-bs-target="#logoutModal"
            class="btn btn-logout-action w-100 d-flex align-items-center justify-content-center gap-2"
          >
            <i class="fa fa-sign-out-alt"></i>
            <span>Log Out</span>
          </a>
        </div>
      </nav>