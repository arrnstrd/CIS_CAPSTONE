<x-layouts.super-admin>
    <x-slot name="title">User Management</x-slot>
    <x-slot name="pageName">User Management</x-slot>
    <x-slot name="subtitle">Manage system user accounts, assigned roles, account statuses, and issue setup invitations.</x-slot>

    @php
        $isSuperAdmin = auth()->check() && auth()->user()->isSuperAdmin();
    @endphp

    <style>
        /* Premium Custom Styling */
        .user-card-panel {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 15px -1px rgba(0, 0, 0, 0.05), 0 2px 6px -1px rgba(0, 0, 0, 0.03);
            overflow: hidden;
        }

        .table-name-avatar {
            width: 2.3rem;
            height: 2.3rem;
            border-radius: 50%;
            background: #2438b9;
            color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.8rem;
            letter-spacing: 0.03em;
            flex-shrink: 0;
        }

        .status-dot-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.32rem 0.7rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            border: 1px solid transparent;
        }
        .status-dot-pill .dot {
            width: 0.45rem;
            height: 0.45rem;
            border-radius: 50%;
        }
        .status-active {
            background-color: #ecfdf5;
            color: #065f46;
            border-color: #a7f3d0;
        }
        .status-active .dot {
            background-color: #10b981;
            box-shadow: 0 0 6px rgba(16, 185, 129, 0.5);
        }
        .status-pending {
            background-color: #fffbeb;
            color: #92400e;
            border-color: #fde68a;
        }
        .status-pending .dot {
            background-color: #f59e0b;
            box-shadow: 0 0 6px rgba(245, 158, 11, 0.5);
        }
        .status-inactive {
            background-color: #f8fafc;
            color: #475569;
            border-color: #e2e8f0;
        }
        .status-inactive .dot {
            background-color: #94a3b8;
        }

        .role-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.3rem 0.65rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            border: 1px solid transparent;
        }
        .role-super_admin { background-color: #fef3c7; color: #92400e; border-color: #fde68a; }
        .role-admin { background-color: #f0f3ff; color: #2438b9; border-color: #c7d2fe; }
        .role-teacher { background-color: #f0f9ff; color: #0369a1; border-color: #bae6fd; }
        .role-scanner { background-color: #fff7ed; color: #c2410c; border-color: #ffedd5; }

        /* Custom Checkbox Styling */
        .custom-check-input {
            width: 1.15rem;
            height: 1.15rem;
            cursor: pointer;
            border-radius: 4px;
            border: 1.5px solid #cbd5e1;
        }
        .custom-check-input:checked {
            background-color: #2438b9;
            border-color: #2438b9;
        }

        /* Bulk Action Bar */
        .bulk-actions-bar {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #ffffff;
            border-radius: 10px;
            padding: 0.75rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.25);
            margin-bottom: 1.25rem;
        }

        .details-avatar-lg {
            width: 4.5rem;
            height: 4.5rem;
            border-radius: 50%;
            background: #2438b9;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.6rem;
            box-shadow: 0 4px 10px rgba(36, 56, 185, 0.3);
        }

        .action-dropdown-toggle {
            padding: 0.35rem 0.65rem;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        tr.selected-row {
            background-color: #f1f5f9 !important;
        }
    </style>

    <div class="main-content mx-3">
        @if(session('status'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
                <i class="fas fa-check-circle me-2"></i> {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Header Actions & Title Bar -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h4 class="fw-bold text-dark mb-0"><i class="fas fa-users-cog text-primary me-2"></i>User Directory</h4>
            </div>

            @if($isSuperAdmin)
                <button type="button" class="btn btn-dark px-3.5 py-2.5 rounded-3 fw-medium d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#createUserModal">
                    <i class="fas fa-user-plus"></i>
                    <span>Create New User</span>
                </button>
            @else
                <button type="button" class="btn btn-secondary px-3.5 py-2.5 rounded-3 fw-medium d-flex align-items-center gap-2 shadow-sm" disabled title="Only the Super Admin can create new user accounts.">
                    <i class="fas fa-lock"></i>
                    <span>Create New User (Super Admin Only)</span>
                </button>
            @endif
        </div>
                
                <!-- Search & Filters Toolbar -->
                <div class="bg-white rounded-3 p-3 border shadow-sm mb-3">
                    <form method="GET" action="{{ route('users.index') }}" id="filterForm">
                        <div class="row g-2 align-items-center">
                            <div class="col-lg-6 col-md-12">
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-search text-muted"></i>
                                    </span>
                                    <input type="search" name="search" class="form-control border-start-0 ps-0" 
                                           placeholder="Search users by name, email, or employee ID..." 
                                           value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <select name="role" class="form-select" onchange="document.getElementById('filterForm').submit()">
                                    <option value="">All Roles</option>
                                    <option value="super_admin" {{ request('role') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>School Admin</option>
                                    <option value="teacher" {{ request('role') === 'teacher' ? 'selected' : '' }}>Teacher</option>
                                    <option value="scanner_operator" {{ request('role') === 'scanner_operator' ? 'selected' : '' }}>Scanner Operator</option>
                                </select>
                            </div>
                            <div class="col-lg-3 col-md-6 d-flex gap-2">
                                <select name="status" class="form-select" onchange="document.getElementById('filterForm').submit()">
                                    <option value="">All Account Statuses</option>
                                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @if(request('search') || request('role') || request('status'))
                                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary" title="Clear Filters">
                                        <i class="fas fa-times"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Contextual Bulk Actions Bar -->
                @if($isSuperAdmin)
                    <div id="bulkActionBar" class="bulk-actions-bar d-none">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary rounded-pill px-2.5 py-1 fs-6" id="selectedCount">0</span>
                            <span class="fw-semibold fs-7">user(s) selected for administrative action</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" id="bulkDeactivateBtn" class="btn btn-sm btn-warning fw-semibold d-none" onclick="openBulkConfirmModal('deactivate')">
                                <i class="fas fa-pause me-1.5"></i> Deactivate Selected
                            </button>
                            <button type="button" id="bulkReactivateBtn" class="btn btn-sm btn-success fw-semibold d-none" onclick="openBulkConfirmModal('reactivate')">
                                <i class="fas fa-play me-1.5"></i> Reactivate Selected
                            </button>
                            <button type="button" id="bulkResendBtn" class="btn btn-sm btn-info text-white fw-semibold d-none" onclick="executeBulkResend()">
                                <i class="fas fa-paper-plane me-1.5"></i> Resend Invitations
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-light ms-2" onclick="clearSelection()">
                                <i class="fas fa-times me-1"></i> Deselect
                            </button>
                        </div>
                    </div>
                @endif

                <!-- Table Panel -->
                @if($users->isEmpty())
                    <div class="bg-white rounded-3 p-5 text-center border shadow-sm">
                        <i class="fas fa-users-slash fa-3x text-muted mb-3 opacity-50"></i>
                        <h5 class="fw-semibold text-dark mb-1">No Matching User Records</h5>
                        <p class="text-muted small mb-3">No registered users matched the current search or filter criteria.</p>
                        <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary">Reset Filters</a>
                    </div>
                @else
                    <div class="table-responsive">
                        <x-ui.table>
                            <thead class="text-uppercase small">
                                <tr>
                                    <th style="width: 4%" class="text-center">
                                        <input type="checkbox" id="selectAllUsers" class="form-check-input custom-check-input" {{ !$isSuperAdmin ? 'disabled' : '' }}>
                                    </th>
                                    <th style="width: 32%">User Identity</th>
                                    <th style="width: 24%">Email</th>
                                    <th style="width: 14%">Role</th>
                                    <th style="width: 13%">Status</th>
                                    <th style="width: 13%" class="text-end pe-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                    @php
                                        $firstName = trim($user->first_name);
                                        $lastName = trim($user->last_name);
                                        $fullName = $firstName . ' ' . $lastName;
                                        $initials = mb_strtoupper(mb_substr($firstName, 0, 1) . mb_substr($lastName, 0, 1));
                                        $isSelf = auth()->check() && auth()->id() === $user->id;
                                        $isProtected = $user->isSuperAdmin();
                                        $cannotModifyStatus = !$isSuperAdmin || $isSelf || $isProtected;
                                        $canEditProfile = $isSuperAdmin && (!$isProtected || $isSelf);
                                    @endphp
                                    <tr id="user-row-{{ $user->id }}">
                                        <td class="text-center">
                                            <input type="checkbox" class="form-check-input custom-check-input user-select-checkbox" 
                                                   value="{{ $user->id }}" 
                                                   data-name="{{ $fullName }}"
                                                   data-status="{{ $user->status }}"
                                                   {{ $cannotModifyStatus ? 'disabled' : '' }}>
                                        </td>
                                        <td class="table-name-cell">
                                            <div class="table-name-wrap">
                                                <div class="table-name-avatar" aria-hidden="true">
                                                    {{ $initials ?: 'U' }}
                                                </div>
                                                <div class="table-name-copy">
                                                    <div class="table-name-main fw-bold text-dark">
                                                        {{ $fullName }}
                                                        @if($isProtected)
                                                            <span class="badge bg-warning text-dark ms-1" style="font-size: 0.65rem;" title="Super Administrator Account">Super Admin</span>
                                                        @endif
                                                    </div>
                                                    <div class="table-name-sub text-muted fs-7">
                                                        {{ $user->employee_id ?? ('ID: ' . $user->id) }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-secondary small">
                                            <i class="far fa-envelope me-1.5 text-muted"></i> {{ $user->email }}
                                        </td>
                                        <td>
                                            @if($user->isSuperAdmin())
                                                <span class="role-pill role-super_admin"><i class="fas fa-crown me-1"></i> Super Admin</span>
                                            @elseif($user->role === 'admin')
                                                <span class="role-pill role-admin"><i class="fas fa-user-shield me-1"></i> School Admin</span>
                                            @elseif($user->isTeacher())
                                                <span class="role-pill role-teacher"><i class="fas fa-chalkboard-teacher me-1"></i> Teacher</span>
                                            @elseif($user->isScannerOperator())
                                                <span class="role-pill role-scanner"><i class="fas fa-qrcode me-1"></i> Scanner Operator</span>
                                            @else
                                                <span class="role-pill bg-secondary text-white">{{ ucfirst($user->role) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->status === 'active')
                                                <span class="status-dot-pill status-active"><span class="dot"></span> Active</span>
                                            @elseif($user->status === 'pending')
                                                <span class="status-dot-pill status-pending"><span class="dot"></span> Pending</span>
                                            @else
                                                <span class="status-dot-pill status-inactive"><span class="dot"></span> Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-3">
                                            <!-- State-based Action Dropdown -->
                                            <div class="dropdown d-inline-block position-static">
                                                <button class="btn btn-sm btn-outline-secondary action-dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <span>Actions</span>
                                                    <i class="fas fa-chevron-down ms-1 fs-7"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                                    <li>
                                                        <a class="dropdown-item py-2" href="#" 
                                                           onclick="openViewModal('{{ addslashes($fullName) }}', '{{ addslashes($initials) }}', '{{ addslashes($user->email) }}', '{{ addslashes($user->employee_id ?? ('ID: ' . $user->id)) }}', '{{ ucfirst($user->role) }}', '{{ ucfirst($user->status) }}', '{{ $user->created_at->format('M d, Y H:i') }}')">
                                                            <i class="fas fa-id-card text-muted me-2"></i> View Account Details
                                                        </a>
                                                    </li>

                                                    <!-- Edit Profile Details Option -->
                                                    @if($canEditProfile)
                                                        <li>
                                                            <a class="dropdown-item py-2" href="#" 
                                                               onclick="openEditModal('{{ $user->id }}', '{{ addslashes($firstName) }}', '{{ addslashes($lastName) }}', '{{ addslashes($user->email) }}', '{{ $user->role }}', {{ $isProtected ? 'true' : 'false' }})">
                                                                <i class="fas fa-user-edit text-muted me-2"></i> Edit Account Details
                                                            </a>
                                                        </li>
                                                    @else
                                                        <li>
                                                            <span class="dropdown-item text-muted disabled py-2">
                                                                <i class="fas fa-lock me-2"></i> {{ $isProtected ? 'Protected Account' : 'Requires Super Admin' }}
                                                            </span>
                                                        </li>
                                                    @endif

                                                    @if(!$isSuperAdmin)
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <span class="dropdown-item text-muted disabled py-2">
                                                                <i class="fas fa-shield-alt me-2 text-secondary"></i> Requires Super Admin
                                                            </span>
                                                        </li>
                                                    @else
                                                        @if($user->status === 'pending')
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li>
                                                                <form method="POST" action="{{ route('api.users.resend-invitation', $user->id) }}" data-ajax-form="resend">
                                                                    @csrf
                                                                    <button type="submit" class="dropdown-item py-2 text-primary">
                                                                        <i class="fas fa-paper-plane me-2"></i> Resend Setup Invitation
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        @elseif($user->status === 'active')
                                                            @if($isProtected)
                                                                <li><hr class="dropdown-divider"></li>
                                                                <li>
                                                                    <span class="dropdown-item text-muted disabled py-2">
                                                                        <i class="fas fa-shield-alt me-2 text-warning"></i> Protected Account
                                                                    </span>
                                                                </li>
                                                            @elseif($isSelf)
                                                                <li><hr class="dropdown-divider"></li>
                                                                <li>
                                                                    <span class="dropdown-item text-muted disabled py-2">
                                                                        <i class="fas fa-lock me-2"></i> Cannot Deactivate Self
                                                                    </span>
                                                                </li>
                                                            @else
                                                                <li><hr class="dropdown-divider"></li>
                                                                <li>
                                                                    <a class="dropdown-item py-2 text-warning" href="#" 
                                                                       onclick="openSingleConfirmModal('deactivate', '{{ $user->id }}', '{{ addslashes($fullName) }}')">
                                                                        <i class="fas fa-pause me-2"></i> Deactivate Account
                                                                    </a>
                                                                </li>
                                                            @endif
                                                        @elseif($user->status === 'inactive')
                                                            @if($isProtected)
                                                                <li><hr class="dropdown-divider"></li>
                                                                <li>
                                                                    <span class="dropdown-item text-muted disabled py-2">
                                                                        <i class="fas fa-shield-alt me-2 text-warning"></i> Protected Account
                                                                    </span>
                                                                </li>
                                                            @elseif(!$isSelf)
                                                                <li><hr class="dropdown-divider"></li>
                                                                <li>
                                                                    <a class="dropdown-item py-2 text-success" href="#" 
                                                                       onclick="openSingleConfirmModal('reactivate', '{{ $user->id }}', '{{ addslashes($fullName) }}')">
                                                                        <i class="fas fa-play me-2"></i> Reactivate Account
                                                                    </a>
                                                                </li>
                                                            @endif
                                                        @endif
                                                    @endif
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </x-ui.table>
                    </div>
                @endif
            </div>
    </div>

    <!-- Create User Modal (Preserved untouched) -->
    <x-modal id="createUserModal" modalTitle="Create User" size="modal-md">
        <form id="createUserForm" action="{{ url('/api/users') }}" method="POST" data-ajax-form="user">
            @csrf

            <div data-ajax-errors></div>

            <div class="mb-3">
                <label class="form-label">First Name</label>
                <input type="text" name="first_name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Last Name</label>
                <input type="text" name="last_name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-select" required>
                    <option value="super_admin">Super Admin</option>
                    <option value="admin">School Admin</option>
                    <option value="teacher">Teacher</option>
                    <option value="scanner_operator">Scanner Operator</option>
                </select>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-dark" data-loading-text="Creating...">Create User</button>
            </div>
        </form>
    </x-modal>

    <!-- Edit User Modal -->
    <x-modal id="editUserModal" modalTitle="Edit User Account" size="modal-md">
        <form id="editUserForm" method="POST" data-ajax-form="editUser">
            @csrf
            @method('PUT')

            <div data-ajax-errors></div>

            <div class="mb-3">
                <label class="form-label">First Name</label>
                <input type="text" name="first_name" id="edit_first_name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Last Name</label>
                <input type="text" name="last_name" id="edit_last_name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" id="edit_email" class="form-control" required>
            </div>

            <div class="mb-3" id="editRoleContainer">
                <label class="form-label">Role</label>
                <select name="role" id="edit_role" class="form-select" required>
                    <option value="super_admin">Super Admin</option>
                    <option value="admin">School Admin</option>
                    <option value="teacher">Teacher</option>
                    <option value="scanner_operator">Scanner Operator</option>
                </select>
            </div>

            <div id="protectedNotice" class="alert alert-info py-2 fs-7 mb-3 d-none">
                <i class="fas fa-shield-alt me-1 text-primary"></i> <strong>Protected Administrator</strong>: Administrative privileges and active account status are permanently protected.
            </div>

            <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-dark" data-loading-text="Saving Changes...">Save Changes</button>
            </div>
        </form>
    </x-modal>

    <!-- Read-Only View User Details Modal -->
    <x-modal id="viewUserModal" modalTitle="User Account Details" size="modal-md">
        <div class="text-center py-4 border-bottom">
            <div class="details-avatar-lg mx-auto mb-3" id="viewAvatar">
                U
            </div>
            <h4 class="fw-bold mb-1 text-dark" id="viewFullName">User Name</h4>
            <p class="text-muted small mb-2" id="viewEmployeeId">EMP-0000</p>
            <div class="d-flex justify-content-center gap-2">
                <span class="badge bg-primary" id="viewRole">Role</span>
                <span class="badge bg-success" id="viewStatus">Status</span>
            </div>
        </div>
        <div class="py-3">
            <div class="row g-3">
                <div class="col-6">
                    <label class="form-label text-muted fs-7 mb-1">Email Address</label>
                    <p class="fw-semibold text-dark mb-0 fs-7" id="viewEmail">email@example.com</p>
                </div>
                <div class="col-6">
                    <label class="form-label text-muted fs-7 mb-1">Account Created</label>
                    <p class="fw-semibold text-dark mb-0 fs-7" id="viewCreated">Aug 22, 2026</p>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-end pt-2 border-top">
            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
        </div>
    </x-modal>

    <!-- Sensitive Action Confirmation Modal (Requires Admin Password) -->
    <x-modal id="confirmActionModal" modalTitle="Confirm Sensitive Administrative Action" size="modal-md">
        <form id="confirmActionForm" method="POST" data-ajax-form="confirmAction">
            @csrf

            <div data-ajax-errors></div>

            <div class="alert alert-warning d-flex align-items-center gap-2 mb-3">
                <i class="fas fa-exclamation-triangle fs-4 flex-shrink-0"></i>
                <div id="confirmActionNotice" class="small">
                    Are you sure you want to perform this sensitive action?
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Target Account(s)</label>
                <div class="p-2.5 bg-light rounded border text-dark fw-medium fs-7" id="confirmTargetList">
                    None selected
                </div>
            </div>

            <div class="mb-3">
                <label for="admin_current_password" class="form-label fw-semibold">Admin Password Confirmation</label>
                <input type="password" name="current_password" id="admin_current_password" class="form-control" required placeholder="Enter your current password to authorize action">
                <small class="form-text text-muted">Your current logged-in password is required for security verification.</small>
            </div>

            <div id="bulkUserInputsContainer"></div>

            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" id="confirmSubmitBtn" class="btn btn-danger" data-loading-text="Processing...">Confirm Action</button>
            </div>
        </form>
    </x-modal>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectAllCheck = document.getElementById('selectAllUsers');
            const rowCheckboxes = document.querySelectorAll('.user-select-checkbox');
            const bulkBar = document.getElementById('bulkActionBar');
            const selectedCountSpan = document.getElementById('selectedCount');
            const bulkDeactivateBtn = document.getElementById('bulkDeactivateBtn');
            const bulkReactivateBtn = document.getElementById('bulkReactivateBtn');
            const bulkResendBtn = document.getElementById('bulkResendBtn');

            function updateBulkBar() {
                if (!bulkBar) return;
                const checked = Array.from(rowCheckboxes).filter(cb => cb.checked);
                const count = checked.length;

                rowCheckboxes.forEach(cb => {
                    const row = document.getElementById(`user-row-${cb.value}`);
                    if (row) {
                        row.classList.toggle('selected-row', cb.checked);
                    }
                });

                if (count > 0) {
                    bulkBar.classList.remove('d-none');
                    if (selectedCountSpan) selectedCountSpan.textContent = count;

                    const hasActive = checked.some(cb => cb.dataset.status === 'active');
                    const hasInactive = checked.some(cb => cb.dataset.status === 'inactive');
                    const hasPending = checked.some(cb => cb.dataset.status === 'pending');

                    if (bulkDeactivateBtn) bulkDeactivateBtn.classList.toggle('d-none', !hasActive);
                    if (bulkReactivateBtn) bulkReactivateBtn.classList.toggle('d-none', !hasInactive);
                    if (bulkResendBtn) bulkResendBtn.classList.toggle('d-none', !hasPending);
                } else {
                    bulkBar.classList.add('d-none');
                }

                if (selectAllCheck) {
                    selectAllCheck.checked = rowCheckboxes.length > 0 && checked.length === rowCheckboxes.length;
                }
            }

            if (selectAllCheck) {
                selectAllCheck.addEventListener('change', function () {
                    rowCheckboxes.forEach(cb => {
                        if (!cb.disabled) {
                            cb.checked = selectAllCheck.checked;
                        }
                    });
                    updateBulkBar();
                });
            }

            rowCheckboxes.forEach(cb => {
                cb.addEventListener('change', updateBulkBar);
            });

            window.clearSelection = function () {
                rowCheckboxes.forEach(cb => cb.checked = false);
                if (selectAllCheck) selectAllCheck.checked = false;
                updateBulkBar();
            };

            window.openViewModal = function (name, initials, email, empId, role, status, created) {
                document.getElementById('viewAvatar').textContent = initials || 'U';
                document.getElementById('viewFullName').textContent = name;
                document.getElementById('viewEmployeeId').textContent = empId;
                document.getElementById('viewEmail').textContent = email;
                document.getElementById('viewRole').textContent = role;
                document.getElementById('viewStatus').textContent = status;
                document.getElementById('viewCreated').textContent = created;

                const modal = new bootstrap.Modal(document.getElementById('viewUserModal'));
                modal.show();
            };

            window.openEditModal = function (userId, firstName, lastName, email, role, isProtected) {
                const form = document.getElementById('editUserForm');
                form.action = `/api/users/${userId}`;
                document.getElementById('edit_first_name').value = firstName;
                document.getElementById('edit_last_name').value = lastName;
                document.getElementById('edit_email').value = email;

                const roleSelect = document.getElementById('edit_role');
                const protectedNotice = document.getElementById('protectedNotice');

                if (isProtected) {
                    roleSelect.value = 'admin';
                    roleSelect.disabled = true;
                    protectedNotice.classList.remove('d-none');
                } else {
                    roleSelect.value = role;
                    roleSelect.disabled = false;
                    protectedNotice.classList.add('d-none');
                }

                const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
                modal.show();
            };

            window.openSingleConfirmModal = function (action, userId, userName) {
                const form = document.getElementById('confirmActionForm');
                const notice = document.getElementById('confirmActionNotice');
                const targetList = document.getElementById('confirmTargetList');
                const container = document.getElementById('bulkUserInputsContainer');
                const submitBtn = document.getElementById('confirmSubmitBtn');

                container.innerHTML = '';
                document.getElementById('admin_current_password').value = '';

                if (action === 'deactivate') {
                    form.action = `/api/users/${userId}/deactivate`;
                    notice.textContent = `Are you sure you want to deactivate the user account "${userName}"? Deactivated users will be prevented from logging into the system.`;
                    submitBtn.className = 'btn btn-warning';
                    submitBtn.textContent = 'Deactivate User';
                } else {
                    form.action = `/api/users/${userId}/reactivate`;
                    notice.textContent = `Are you sure you want to reactivate the user account "${userName}"? Reactivated users will regain access to their account.`;
                    submitBtn.className = 'btn btn-success';
                    submitBtn.textContent = 'Reactivate User';
                }

                targetList.textContent = userName;
                const modal = new bootstrap.Modal(document.getElementById('confirmActionModal'));
                modal.show();
            };

            window.openBulkConfirmModal = function (action) {
                const checked = Array.from(rowCheckboxes).filter(cb => cb.checked);
                const form = document.getElementById('confirmActionForm');
                const notice = document.getElementById('confirmActionNotice');
                const targetList = document.getElementById('confirmTargetList');
                const container = document.getElementById('bulkUserInputsContainer');
                const submitBtn = document.getElementById('confirmSubmitBtn');

                container.innerHTML = '';
                document.getElementById('admin_current_password').value = '';

                let targetUserNames = [];
                let eligibleChecked = [];

                if (action === 'deactivate') {
                    eligibleChecked = checked.filter(cb => cb.dataset.status === 'active');
                    form.action = '/api/users/bulk-deactivate';
                    notice.textContent = `Are you sure you want to deactivate ${eligibleChecked.length} selected active user(s)?`;
                    submitBtn.className = 'btn btn-warning';
                    submitBtn.textContent = 'Deactivate Selected';
                } else {
                    eligibleChecked = checked.filter(cb => cb.dataset.status === 'inactive');
                    form.action = '/api/users/bulk-reactivate';
                    notice.textContent = `Are you sure you want to reactivate ${eligibleChecked.length} selected inactive user(s)?`;
                    submitBtn.className = 'btn btn-success';
                    submitBtn.textContent = 'Reactivate Selected';
                }

                eligibleChecked.forEach(cb => {
                    targetUserNames.push(cb.dataset.name);
                    const hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'user_ids[]';
                    hidden.value = cb.value;
                    container.appendChild(hidden);
                });

                targetList.textContent = targetUserNames.join(', ');
                const modal = new bootstrap.Modal(document.getElementById('confirmActionModal'));
                modal.show();
            };

            window.executeBulkResend = function () {
                const checked = Array.from(rowCheckboxes).filter(cb => cb.checked && cb.dataset.status === 'pending');
                if (checked.length === 0) return;

                if (!confirm(`Resend invitation setup email to ${checked.length} pending user(s)?`)) return;

                const formData = new FormData();
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                checked.forEach(cb => formData.append('user_ids[]', cb.value));

                fetch('/api/users/bulk-resend-invitation', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: formData,
                }).then(res => res.json()).then(data => {
                    alert(data.message || 'Invitations resent.');
                    clearSelection();
                    if (window.ajaxCrud) window.ajaxCrud.refreshTables();
                }).catch(err => {
                    alert('Failed to resend invitations.');
                });
            };
        });
    </script>
</x-layouts.super-admin>