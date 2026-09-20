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

    // 3. Student Management Tour
    students: [
        {
            target: '[data-tour="students-grade-grid"]',
            title: 'Grade Directory',
            content: 'Explore students grouped cleanly by Primary, Junior High, and Senior High tiers.',
            placement: 'bottom'
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
    ]
};
