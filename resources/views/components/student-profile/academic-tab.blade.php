<div class="col-12">
    <div class="bg-white rounded shadow-sm p-4">
        <h4 class="fw-semibold mb-4">Academic Information</h4>
        
        @if ($enrollments->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>School Year</th>
                            <th>Grade Level</th>
                            <th>Section</th>
                            <th>Adviser</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($enrollments as $enrollment)
                            <tr>
                                <td>{{ $enrollment->school_year }}</td>
                                <td>{{ $enrollment->grade_level }}</td>
                                <td>{{ $enrollment->section?->name ?? 'Not Enrolled' }}</td>
                                <td>{{ $enrollment->section?->adviser?->full_name ?? 'Not Assigned' }}</td>
                                <td>
                                    @if ($enrollment->status === 'active')
                                        <span class="badge-dot dot-success">Active</span>
                                    @else
                                        <span class="badge-dot dot-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($enrollment->status === 'active')
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" 
                                                data-bs-target="#viewEnrollmentModal" 
                                                data-id="{{ $enrollment->id }}"
                                                data-student-name="{{ $student->full_name }}"
                                                data-student-lrn="{{ $student->lrn }}"
                                                data-status="{{ $enrollment->status }}"
                                                data-school-year="{{ $enrollment->school_year }}"
                                                data-section-name="{{ $enrollment->section?->name }}"
                                                data-grade-level="{{ $enrollment->grade_level }}"
                                                data-level="{{ $enrollment->section?->level }}"
                                                data-adviser="{{ $enrollment->section?->adviser?->full_name }}"
                                                data-capacity="{{ $enrollment->section?->capacity }}"
                                                data-created-at="{{ $enrollment->created_at }}">
                                            View Details
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fa-solid fa-graduation-cap fa-3x text-muted mb-3"></i>
                <p class="text-muted mb-0">No active enrollment found.</p>
                <span class="badge-dot dot-secondary-subtle text-secondary border border-secondary-subtle">
                    No active enrollment
                </span>
            </div>
        @endif
    </div>
</div>

<!-- View Enrollment Modal -->
<div class="modal fade" id="viewEnrollmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Enrollment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-6">
                        <p class="mb-2"><strong>Student:</strong></p>
                        <p class="mb-3" id="viewStudentName"></p>
                        
                        <p class="mb-2"><strong>LRN:</strong></p>
                        <p class="mb-3" id="viewStudentLrn"></p>
                        
                        <p class="mb-2"><strong>Status:</strong></p>
                        <p class="mb-3" id="viewEnrollmentStatus"></p>
                    </div>
                    <div class="col-6">
                        <p class="mb-2"><strong>School Year:</strong></p>
                        <p class="mb-3" id="viewSchoolYear"></p>
                        
                        <p class="mb-2"><strong>Section:</strong></p>
                        <p class="mb-3" id="viewSectionName"></p>
                        
                        <p class="mb-2"><strong>Grade Level:</strong></p>
                        <p class="mb-3" id="viewGradeLevel"></p>
                    </div>
                </div>
                
                <hr>
                
                <div class="row">
                    <div class="col-6">
                        <p class="mb-2"><strong>Level:</strong></p>
                        <p class="mb-3" id="viewLevel"></p>
                        
                        <p class="mb-2"><strong>Adviser:</strong></p>
                        <p class="mb-3" id="viewAdviser"></p>
                    </div>
                    <div class="col-6">
                        <p class="mb-2"><strong>Capacity:</strong></p>
                        <p class="mb-3" id="viewCapacity"></p>
                        
                        <p class="mb-2"><strong>Enrolled On:</strong></p>
                        <p class="mb-3" id="viewCreatedAt"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>