<x-layouts.teacher>
    <x-slot name="pageName">Report Card</x-slot>
    <x-slot name="subtitle">{{ $student->last_name }}, {{ $student->first_name }}</x-slot>

    <div class="gd-layout">
        <div class="gd-content">
            <!-- SF-9 Template Structure -->
            <div class="sf9-report-card">
                <div class="sf9-two-column">
                    <!-- LEFT COLUMN -->
                    <div class="sf9-left-column">
                        <!-- DepEd Header -->
                        <div class="sf9-header">
                            <div class="sf9-deped-branding">
                                <div class="sf9-branding-text">
                                    <div class="sf9-branding-line">Republic of the Philippines</div>
                                    <div class="sf9-branding-line">Department of Education</div>
                                    <div class="sf9-branding-line">Region III</div>
                                    <div class="sf9-branding-line">SCHOOLS DIVISION OF PAMPANGA</div>
                                    <div class="sf9-branding-line">San Simon District</div>
                                    <div class="sf9-branding-line fw-bold">CONCEPCION INTEGRATED SCHOOL</div>
                                    <div class="sf9-branding-line">San Simon, Pampanga</div>
                                </div>
                                <div class="sf9-logos">
                                    <div class="sf9-logo-placeholder sf9-deped-logo">
                                        <!-- DepEd Logo -->
                                    </div>
                                    <div class="sf9-logo-placeholder sf9-school-logo">
                                        <img src="{{ asset('images/CIS-logo.png') }}" alt="CIS Logo" class="sf9-logo-img">
                                    </div>
                                </div>
                            </div>
                            <div class="sf9-title-section">
                                <h1 class="sf9-main-title fw-bold">LEARNER'S PERFORMANCE REPORT</h1>
                                <h2 class="sf9-school-year">School Year {{ $section->school_year->school_year ?? date('Y') }} - {{ date('Y', strtotime('+1 year')) }}</h2>
                            </div>
                        </div>

                        <!-- Student Information Section -->
                        <div class="sf9-student-info">
                            <div class="sf9-student-info-content">
                                <div class="sf9-info-row">
                                    <div class="sf9-info-item">
                                        <span class="sf9-info-label">Name:</span>
                                        <span class="sf9-info-value">{{ $student->last_name }}, {{ $student->first_name }} {{ $student->middle_name }}</span>
                                    </div>
                                    <div class="sf9-info-item">
                                        <span class="sf9-info-label">Age:</span>
                                        <span class="sf9-info-value">{{ $student->age }}</span>
                                    </div>
                                    <div class="sf9-info-item">
                                        <span class="sf9-info-label">Sex:</span>
                                        <span class="sf9-info-value">{{ $student->sex }}</span>
                                    </div>
                                </div>
                                <div class="sf9-info-row">
                                    <div class="sf9-info-item">
                                        <span class="sf9-info-label">LRN:</span>
                                        <span class="sf9-info-value">{{ $student->lrn }}</span>
                                    </div>
                                    <div class="sf9-info-item">
                                        <span class="sf9-info-label">Grade:</span>
                                        <span class="sf9-info-value">{{ $section->grade_level }}</span>
                                    </div>
                                    <div class="sf9-info-item">
                                        <span class="sf9-info-label">Section:</span>
                                        <span class="sf9-info-value">{{ $section->name }}</span>
                                    </div>
                                </div>
                                <div class="sf9-info-row">
                                    <div class="sf9-info-item sf9-track-item">
                                        <span class="sf9-info-label">Track (SHS only):</span>
                                        <span class="sf9-info-value">{{ $section->track ?? '' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Dear Parents Note -->
                        <div class="sf9-parents-note">
                            <p class="sf9-note-text">Dear Parents,</p>
                            <p class="sf9-note-text">This Performance Report shows the ability and progress your child has made in the different learning areas as well as his/her core values. The school welcomes you should you desire to know more about your child's progress.</p>
                        </div>

                        <!-- Learning Areas Table -->
                        <div class="sf9-learning-areas">
                            <div class="sf9-table-header">
                                <h3 class="sf9-table-title fw-bold">LEARNING PROGRESS AND ACHIEVEMENT</h3>
                            </div>
                            <div class="sf9-table-container">
                                <table class="sf9-learning-table">
                                    <thead>
                                        <tr>
                                            <th class="sf9-subject-header">LEARNING AREAS</th>
                                            <th class="sf9-term-header" colspan="3">TERM</th>
                                            <th class="sf9-final-header">FINAL GRADE</th>
                                            <th class="sf9-remarks-header">REMARKS</th>
                                        </tr>
                                        <tr>
                                            <th></th>
                                            <th class="sf9-term-col">1</th>
                                            <th class="sf9-term-col">2</th>
                                            <th class="sf9-term-col">3</th>
                                            <th></th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Filipino -->
                                        <tr>
                                            <td class="sf9-subject-cell">Filipino</td>
                                            @foreach ($gradingPeriods as $period)
                                                <td class="sf9-grade-cell">{{ $grades['Filipino']["term{$period->sequence}"] ?? '—' }}</td>
                                            @endforeach
                                            <td class="sf9-final-cell">{{ $grades['Filipino']['final'] ?? '—' }}</td>
                                            <td class="sf9-remarks-cell">{{ $grades['Filipino']['final'] ? App\Services\Grading\PerformanceDescriptorResolver::resolve($grades['Filipino']['final'])['remarks'] : '—' }}</td>
                                        </tr>
                                        <!-- English -->
                                        <tr>
                                            <td class="sf9-subject-cell">English</td>
                                            @foreach ($gradingPeriods as $period)
                                                <td class="sf9-grade-cell">{{ $grades['English']["term{$period->sequence}"] ?? '—' }}</td>
                                            @endforeach
                                            <td class="sf9-final-cell">{{ $grades['English']['final'] ?? '—' }}</td>
                                            <td class="sf9-remarks-cell">{{ $grades['English']['final'] ? App\Services\Grading\PerformanceDescriptorResolver::resolve($grades['English']['final'])['remarks'] : '—' }}</td>
                                        </tr>
                                        <!-- Mathematics -->
                                        <tr>
                                            <td class="sf9-subject-cell">Mathematics</td>
                                            @foreach ($gradingPeriods as $period)
                                                <td class="sf9-grade-cell">{{ $grades['Mathematics']["term{$period->sequence}"] ?? '—' }}</td>
                                            @endforeach
                                            <td class="sf9-final-cell">{{ $grades['Mathematics']['final'] ?? '—' }}</td>
                                            <td class="sf9-remarks-cell">{{ $grades['Mathematics']['final'] ? App\Services\Grading\PerformanceDescriptorResolver::resolve($grades['Mathematics']['final'])['remarks'] : '—' }}</td>
                                        </tr>
                                        <!-- Science -->
                                        <tr>
                                            <td class="sf9-subject-cell">Science</td>
                                            @foreach ($gradingPeriods as $period)
                                                <td class="sf9-grade-cell">{{ $grades['Science']["term{$period->sequence}"] ?? '—' }}</td>
                                            @endforeach
                                            <td class="sf9-final-cell">{{ $grades['Science']['final'] ?? '—' }}</td>
                                            <td class="sf9-remarks-cell">{{ $grades['Science']['final'] ? App\Services\Grading\PerformanceDescriptorResolver::resolve($grades['Science']['final'])['remarks'] : '—' }}</td>
                                        </tr>
                                        <!-- Araling Panlipunan -->
                                        <tr>
                                            <td class="sf9-subject-cell">Araling Panlipunan (AP)</td>
                                            @foreach ($gradingPeriods as $period)
                                                <td class="sf9-grade-cell">{{ $grades['Araling Panlipunan']["term{$period->sequence}"] ?? '—' }}</td>
                                            @endforeach
                                            <td class="sf9-final-cell">{{ $grades['Araling Panlipunan']['final'] ?? '—' }}</td>
                                            <td class="sf9-remarks-cell">{{ $grades['Araling Panlipunan']['final'] ? App\Services\Grading\PerformanceDescriptorResolver::resolve($grades['Araling Panlipunan']['final'])['remarks'] : '—' }}</td>
                                        </tr>
                                        <!-- GMRC / Values Education -->
                                        <tr>
                                            <td class="sf9-subject-cell">GMRC / Values Education</td>
                                            @foreach ($gradingPeriods as $period)
                                                <td class="sf9-grade-cell">{{ $grades['GMRC / Values Education']["term{$period->sequence}"] ?? '—' }}</td>
                                            @endforeach
                                            <td class="sf9-final-cell">{{ $grades['GMRC / Values Education']['final'] ?? '—' }}</td>
                                            <td class="sf9-remarks-cell">{{ $grades['GMRC / Values Education']['final'] ? App\Services\Grading\PerformanceDescriptorResolver::resolve($grades['GMRC / Values Education']['final'])['remarks'] : '—' }}</td>
                                        </tr>
                                        <!-- EPP / TLE -->
                                        <tr>
                                            <td class="sf9-subject-cell">EPP / TLE</td>
                                            @foreach ($gradingPeriods as $period)
                                                <td class="sf9-grade-cell">{{ $grades['EPP / TLE']["term{$period->sequence}"] ?? '—' }}</td>
                                            @endforeach
                                            <td class="sf9-final-cell">{{ $grades['EPP / TLE']['final'] ?? '—' }}</td>
                                            <td class="sf9-remarks-cell">{{ $grades['EPP / TLE']['final'] ? App\Services\Grading\PerformanceDescriptorResolver::resolve($grades['EPP / TLE']['final'])['remarks'] : '—' }}</td>
                                        </tr>
                                        <!-- MAPEH -->
                                        <tr>
                                            <td class="sf9-subject-cell fw-bold">MAPEH</td>
                                            @foreach ($gradingPeriods as $period)
                                                <td class="sf9-grade-cell">{{ $grades['MAPEH']["term{$period->sequence}"] ?? '—' }}</td>
                                            @endforeach
                                            <td class="sf9-final-cell">{{ $grades['MAPEH']['final'] ?? '—' }}</td>
                                            <td class="sf9-remarks-cell">{{ $grades['MAPEH']['final'] ? App\Services\Grading\PerformanceDescriptorResolver::resolve($grades['MAPEH']['final'])['remarks'] : '—' }}</td>
                                        </tr>
                                        <!-- MAPEH sub-rows -->
                                        <tr class="sf9-sub-row">
                                            <td class="sf9-subject-cell italic">Music and Arts</td>
                                            @foreach ($gradingPeriods as $period)
                                                <td class="sf9-grade-cell">—</td>
                                            @endforeach
                                            <td class="sf9-final-cell">—</td>
                                            <td class="sf9-remarks-cell">—</td>
                                        </tr>
                                        <tr class="sf9-sub-row">
                                            <td class="sf9-subject-cell italic">Physical Education and Health</td>
                                            @foreach ($gradingPeriods as $period)
                                                <td class="sf9-grade-cell">—</td>
                                            @endforeach
                                            <td class="sf9-final-cell">—</td>
                                            <td class="sf9-remarks-cell">—</td>
                                        </tr>
                                        <!-- General Average -->
                                        <tr class="sf9-average-row">
                                            <td class="sf9-average-cell" colspan="4">General Average</td>
                                            <td class="sf9-average-final-cell">{{ $generalAverage ?? '—' }}</td>
                                            <td class="sf9-average-remarks-cell">{{ $generalAverage ? App\Services\Grading\PerformanceDescriptorResolver::resolve($generalAverage)['remarks'] : '—' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Performance Descriptors Table -->
                        <div class="sf9-performance-descriptors">
                            <div class="sf9-table-header">
                                <h3 class="sf9-table-title fw-bold">PERFORMANCE DESCRIPTORS</h3>
                            </div>
                            <div class="sf9-table-container">
                                <table class="sf9-descriptors-table">
                                    <thead>
                                        <tr>
                                            <th class="sf9-descriptor-scale">Grading Scale</th>
                                            <th class="sf9-descriptor-description">Description</th>
                                            <th class="sf9-descriptor-remarks">Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="sf9-descriptor-cell">90-100</td>
                                            <td class="sf9-descriptor-cell">Advancing</td>
                                            <td class="sf9-descriptor-cell">Passed</td>
                                        </tr>
                                        <tr>
                                            <td class="sf9-descriptor-cell">88-89</td>
                                            <td class="sf9-descriptor-cell">Benchmarking</td>
                                            <td class="sf9-descriptor-cell">Passed</td>
                                        </tr>
                                        <tr>
                                            <td class="sf9-descriptor-cell">80-87</td>
                                            <td class="sf9-descriptor-cell">Progressing</td>
                                            <td class="sf9-descriptor-cell">Passed</td>
                                        </tr>
                                        <tr>
                                            <td class="sf9-descriptor-cell">75-79</td>
                                            <td class="sf9-descriptor-cell">Connecting</td>
                                            <td class="sf9-descriptor-cell">Passed</td>
                                        </tr>
                                        <tr>
                                            <td class="sf9-descriptor-cell">65-74</td>
                                            <td class="sf9-descriptor-cell">Developing</td>
                                            <td class="sf9-descriptor-cell">Failed</td>
                                        </tr>
                                        <tr>
                                            <td class="sf9-descriptor-cell">0-64</td>
                                            <td class="sf9-descriptor-cell">Emerging</td>
                                            <td class="sf9-descriptor-cell">Failed</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN -->
                    <div class="sf9-right-column">
                        <!-- Right column content will be added in future iterations -->
                    </div>
                </div>







                <!-- Action Buttons -->
                <div class="sf9-actions">
                    <div class="text-center">
                        <a href="{{ route('teacher.grading-system.reports') }}" 
                           class="btn btn-outline-primary me-2">
                            <i class="fa-solid fa-arrow-left me-1"></i> Back to Reports
                        </a>
                        <a href="{{ route('teacher.grading-system.report-card.pdf', $enrollmentId) }}" 
                           class="btn btn-success me-2" target="_blank">
                            <i class="fa-solid fa-file-pdf me-1"></i> Download PDF
                        </a>
                        <button type="button" class="btn btn-primary" onclick="window.print()">
                            <i class="fa-solid fa-print me-1"></i> Print Report Card
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.teacher>

@push('styles')
<style>
/* SF-9 Report Card Layout */
.sf9-report-card {
    width: 100%;
    max-width: 210mm;
    margin: 0 auto;
    background: white;
    font-family: Arial, sans-serif;
    font-size: 11px;
    line-height: 1.4;
    color: #000;
}

/* Two-column layout */
.sf9-two-column {
    display: flex;
    gap: 20px;
    min-height: 297mm; /* A4 height */
}

.sf9-left-column {
    flex: 2;
    padding: 20px;
    border-right: 1px solid #000;
}

.sf9-right-column {
    flex: 1;
    padding: 20px;
    background: #f9f9f9;
    border-left: 1px solid #000;
}

/* Header styling */
.sf9-header {
    text-align: center;
    margin-bottom: 20px;
    border-bottom: 2px solid #000;
    padding-bottom: 15px;
}

.sf9-branding-text {
    margin-bottom: 15px;
}

.sf9-branding-line {
    margin-bottom: 3px;
    font-size: 10px;
}

.sf9-branding-line.fw-bold {
    font-weight: bold;
}

.sf9-logos {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 10px;
}

.sf9-logo-placeholder {
    width: 60px;
    height: 60px;
    border: 1px solid #000;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
}

.sf9-logo-img {
    width: 50px;
    height: 50px;
    object-fit: contain;
}

.sf9-title-section {
    margin-top: 15px;
}

.sf9-main-title {
    font-size: 16px;
    font-weight: bold;
    margin-bottom: 5px;
}

.sf9-school-year {
    font-size: 12px;
}

/* Student Information */
.sf9-student-info {
    margin-bottom: 20px;
    border: 1px solid #000;
    padding: 10px;
}

.sf9-info-row {
    display: flex;
    margin-bottom: 8px;
}

.sf9-info-item {
    flex: 1;
    margin-right: 10px;
}

.sf9-info-label {
    font-weight: bold;
    margin-right: 5px;
}

/* Dear Parents Note */
.sf9-parents-note {
    margin-bottom: 20px;
    font-size: 10px;
    border: 1px solid #000;
    padding: 10px;
}

.sf9-note-text {
    margin-bottom: 5px;
}

/* Tables */
.sf9-table-container {
    border: 1px solid #000;
    overflow: hidden;
    margin-bottom: 20px;
}

.sf9-learning-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 10px;
}

.sf9-learning-table th {
    border: 1px solid #000;
    padding: 5px;
    text-align: center;
    background: #f0f0f0;
    font-weight: bold;
}

.sf9-learning-table td {
    border: 1px solid #000;
    padding: 4px;
    text-align: center;
}

.sf9-subject-cell {
    text-align: left;
    font-weight: normal;
}

.sf9-subject-cell.fw-bold {
    font-weight: bold;
}

.sf9-subject-cell.italic {
    font-style: italic;
}

.sf9-average-row td {
    background: #f0f0f0;
    font-weight: bold;
}

.sf9-average-cell {
    text-align: left !important;
}

/* Performance Descriptors */
.sf9-performance-descriptors {
    margin-bottom: 20px;
}

.sf9-descriptors-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 9px;
}

.sf9-descriptors-table th {
    border: 1px solid #000;
    padding: 3px;
    text-align: center;
    background: #f0f0f0;
    font-weight: bold;
}

.sf9-descriptors-table td {
    border: 1px solid #000;
    padding: 3px;
    text-align: center;
}

/* Action Buttons */
.sf9-actions {
    text-align: center;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #000;
}

.sf9-actions button {
    margin: 0 5px;
    padding: 8px 15px;
    border: 1px solid #000;
    background: #f0f0f0;
    cursor: pointer;
}

/* Right column placeholder */
.sf9-right-placeholder {
    height: 100%;
    border: 1px dashed #ccc;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #666;
    font-size: 12px;
}

.report-card-table {
    font-size: 14px;
}

.report-card-table th {
    background-color: #f8f9fa;
    font-weight: 600;
    border: 1px solid #dee2e6;
}

.report-card-table td {
    border: 1px solid #dee2e6;
    vertical-align: middle;
    text-align: center;
}

.report-card-summary {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.summary-item label {
    font-weight: 600;
}

.report-card-footer {
    border-top: 1px solid #dee2e6;
    padding-top: 20px;
}

.teacher-section, .parent-section {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 20px;
    background: #f8f9fa;
}

.signature-line {
    margin-bottom: 15px;
}

.teacher-info, .parent-info {
    font-size: 14px;
}

@media print {
    .report-card-container {
        border: none;
        padding: 0;
        max-width: none;
    }
    
    .btn {
        display: none;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Add any JavaScript for report card functionality
document.addEventListener('DOMContentLoaded', function() {
    // Print functionality
    window.print = function() {
        window.focus();
        window.print();
    };
});
</script>
@endpush