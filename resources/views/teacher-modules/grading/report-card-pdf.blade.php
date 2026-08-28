<!DOCTYPE html>
<html>
<head>
    <title>Report Card - {{ $student->last_name }}, {{ $student->first_name }}</title>
    <style>
        @page {
            size: A4;
            margin: 0.5in;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #000;
        }
        
        .sf9-report-card {
            width: 100%;
            max-width: 210mm;
            margin: 0 auto;
            background: white;
            border: 1px solid #000;
            padding: 20px;
        }
        
        /* DepEd Header */
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
        }
        
        .sf9-note-text {
            margin-bottom: 5px;
        }
        
        /* Learning Areas Table */
        .sf9-learning-areas {
            margin-bottom: 20px;
        }
        
        .sf9-table-container {
            border: 1px solid #000;
            overflow: hidden;
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
        }
        
        .sf9-actions button {
            margin: 0 5px;
            padding: 8px 15px;
            border: 1px solid #000;
            background: #f0f0f0;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="sf9-report-card">
        <!-- DepEd Header -->
        <div class="sf9-header">
            <div class="sf9-branding-text">
                <div class="sf9-branding-line">Republic of the Philippines</div>
                <div class="sf9-branding-line">Department of Education</div>
                <div class="sf9-branding-line">Region III</div>
                <div class="sf9-branding-line">SCHOOLS DIVISION OF PAMPANGA</div>
                <div class="sf9-branding-line">San Simon District</div>
                <div class="sf9-branding-line fw-bold">CONCEPCION INTEGRATED SCHOOL</div>
                <div class="sf9-branding-line">San Simon, Pampanga</div>
            </div>
            <div class="sf9-title-section">
                <h1 class="sf9-main-title">LEARNER'S PERFORMANCE REPORT</h1>
                <h2 class="sf9-school-year">School Year {{ $section->school_year->school_year ?? date('Y') }} - {{ date('Y', strtotime('+1 year')) }}</h2>
            </div>
        </div>

        <!-- Student Information Section -->
        <div class="sf9-student-info">
            <div class="sf9-info-row">
                <div class="sf9-info-item">
                    <span class="sf9-info-label">Name:</span>
                    <span>{{ $student->last_name }}, {{ $student->first_name }} {{ $student->middle_name }}</span>
                </div>
                <div class="sf9-info-item">
                    <span class="sf9-info-label">Age:</span>
                    <span>{{ $student->age }}</span>
                </div>
                <div class="sf9-info-item">
                    <span class="sf9-info-label">Sex:</span>
                    <span>{{ $student->sex }}</span>
                </div>
            </div>
            <div class="sf9-info-row">
                <div class="sf9-info-item">
                    <span class="sf9-info-label">LRN:</span>
                    <span>{{ $student->lrn }}</span>
                </div>
                <div class="sf9-info-item">
                    <span class="sf9-info-label">Grade:</span>
                    <span>{{ $section->grade_level }}</span>
                </div>
                <div class="sf9-info-item">
                    <span class="sf9-info-label">Section:</span>
                    <span>{{ $section->name }}</span>
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

        <!-- Action Buttons -->
        <div class="sf9-actions">
            <button onclick="window.print()">Print Report Card</button>
        </div>
    </div>
</body>
</html>