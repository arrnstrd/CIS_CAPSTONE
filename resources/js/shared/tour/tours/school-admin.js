/**
 * School Admin Product Tour Configurations
 * Decoupled step definitions for first-time onboarding.
 */

export const schoolAdminTours = {
    // 1. Academic Setup Tour
    academic: [
        {
            target: '[data-tour="academic-tabs"]',
            title: 'Module Navigation',
            content: 'Switch between Subjects, Sections, and Teaching Assignments effortlessly.',
            placement: 'bottom'
        },
        {
            target: '[data-tour="academic-add-btn"]',
            title: 'Record Creation',
            content: 'Add new subjects, sections, or academic assignments to your school catalog.',
            placement: 'bottom'
        },
        {
            target: '[data-tour="academic-column-filters"]',
            title: 'Excel-Style Filtering',
            content: 'Search and filter tabular records directly from column headers, just like an Excel sheet.',
            placement: 'bottom'
        },
        {
            target: '[data-tour="academic-row-actions"]',
            title: 'Inline Actions',
            content: 'Edit details or remove individual records using the dedicated action buttons.',
            placement: 'top'
        }
    ],

    // 2. Dashboard Tour
    dashboard: [
        {
            target: '[data-tour="dashboard-kpis"]',
            title: 'Key Metrics',
            content: 'Monitor daily attendance rates, active students, and faculty headcounts at a glance.',
            placement: 'bottom'
        },
        {
            target: '[data-tour="dashboard-timeline"]',
            title: 'Realtime Scan Trends',
            content: 'Track hourly peak scan activity and tap trends throughout the school day.',
            placement: 'bottom'
        }
    ],

    // 3. Student Management Tour (Grade Grid)
    students: [
        {
            target: '[data-tour="students-grade-grid"]',
            title: 'Grade Directory',
            content: 'Explore students grouped cleanly by Primary, Junior High, and Senior High tiers.',
            placement: 'bottom'
        }
    ],

    // 3b. Student Section Roster Tour (Student List)
    'students-roster': [
        {
            target: '[data-tour="students-roster-advisor"]',
            title: 'Advisor & Faculty Allocation',
            content: 'View or assign the section class adviser and view subject teacher allocations.',
            placement: 'bottom'
        },
        {
            target: '[data-tour="students-roster-actions"]',
            title: 'Roster Tools & Export',
            content: 'Quickly export this section to an XLSX class record or initiate bulk student imports.',
            placement: 'bottom'
        },
        {
            target: '[data-tour="students-roster-add"]',
            title: 'Add Student',
            content: 'Directly enroll and register an individual student into this section.',
            placement: 'bottom'
        },
        {
            target: '[data-tour="students-roster-search"]',
            title: 'Search & Status Filters',
            content: 'Search learners by name or LRN, sort alphabetically, and filter by enrollment status.',
            placement: 'bottom'
        },
        {
            target: '[data-tour="students-roster-table"]',
            title: 'Student Roster Table',
            content: 'Browse all enrolled learners, check active statuses, and open detailed student profiles.',
            placement: 'top'
        }
    ],

    // 4. Teacher Management Tour
    teachers: [
        {
            target: '[data-tour="teachers-filter-bar"]',
            title: 'Search & Filter',
            content: 'Quickly look up faculty members by employee ID, full name, or active status.',
            placement: 'bottom'
        },
        {
            target: '[data-tour="teachers-table"]',
            title: 'Teacher Roster',
            content: 'View instructor workloads, assigned advisory sections, and credentials.',
            placement: 'bottom'
        }
    ],

    // 5. Attendance Tour
    attendance: [
        {
            target: '[data-tour="attendance-grade-levels"]',
            title: 'Attendance by Grade',
            content: 'Select any grade tier to monitor real-time section attendance and daily verification status.',
            placement: 'bottom'
        }
    ],

    // 6. SF1 Bulk Import Tour
    'sf1-import': [
        {
            target: '[data-tour="import-mode-toggle"]',
            title: 'Import Modes',
            content: 'Switch between the active Import Workspace and your past Transaction History logs.',
            placement: 'bottom'
        },
        {
            target: '[data-tour="import-template"]',
            title: 'DepEd SF-1 Template',
            content: 'Download the official Excel template formatted specifically for DepEd School Form 1 learner records.',
            placement: 'bottom'
        },
        {
            target: '[data-tour="import-stepper"]',
            title: '3-Step Import Pipeline',
            content: 'Follow the guided stages: file upload, automated row validation with duplicate detection, and final student enrollment.',
            placement: 'bottom'
        },
        {
            target: '[data-tour="import-dropzone"]',
            title: 'File Upload & Verification',
            content: 'Drag and drop your SF-1 spreadsheet or browse files. The system instantly parses columns and verifies LRN uniqueness.',
            placement: 'bottom'
        }
    ],

    // 7. Schedule Configuration Tour (Time-Window Based Attendance)
    'schedule-config': [
        {
            target: '[data-tour="sched-level-card"]',
            title: 'Grade Level Schedules',
            content: 'Configure attendance scanning rules and session schedules across Elementary, Junior High, and Senior High tiers.',
            placement: 'bottom'
        },
        {
            target: '[data-tour="sched-entry-window"]',
            title: 'Entry Scan Window (Time-In)',
            content: 'Defines the allowed scan-in range (e.g., 6:00 AM – 8:00 AM). QR scans during this window record student arrival at school gates.',
            placement: 'bottom'
        },
        {
            target: '[data-tour="sched-late-threshold"]',
            title: 'Late Threshold Cutoff',
            content: 'The punctuality cutoff time. Scans recorded before this time are marked Present; scans stamped after are automatically flagged as Late.',
            placement: 'bottom'
        },
        {
            target: '[data-tour="sched-exit-window"]',
            title: 'Exit Scan Window (Time-Out)',
            content: 'Defines the permissible scan-out range for dismissal (e.g., 4:00 PM – 6:00 PM). Prevents early departures and logs valid student tap-outs.',
            placement: 'bottom'
        },
        {
            target: '[data-tour="sched-actions"]',
            title: 'Session Management',
            content: 'Add distinct Morning, Afternoon, or Whole Day sessions per grade tier, or click Edit Schedule to modify time windows inline.',
            placement: 'bottom'
        }
    ]
};
