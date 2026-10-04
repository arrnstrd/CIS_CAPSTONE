/**
 * Teacher Product Tour Configurations
 * Decoupled step definitions for Teacher POV onboarding.
 */

export const teacherTours = {
    // 1. Interactive Grade Sheet Tour
    'teacher-gradesheet': [
        {
            target: '[data-tour="gradesheet-terms"]',
            title: 'Quarter & Term Tabs',
            content: 'Switch between grading terms (Quarter 1 to Quarter 4) to record or review quarterly grades.',
            placement: 'bottom'
        },
        {
            target: '[data-tour="gradesheet-components"]',
            title: 'Assessment Components',
            content: 'View component weights for Written Works, Performance Tasks, and Exams. Click "+ Add" to create an assessment column.',
            placement: 'bottom'
        },
        {
            target: '[data-tour="gradesheet-grid"]',
            title: 'Interactive Scoring Grid',
            content: 'Enter learner scores directly in each cell. Scores automatically validate against maximum points.',
            placement: 'top'
        },
        {
            target: '[data-tour="gradesheet-computed"]',
            title: 'Automated DepEd Grading',
            content: 'Initial percentages and DepEd transmuted grades calculate in real time as scores are recorded.',
            placement: 'left'
        },
        {
            target: '[data-tour="gradesheet-actions"]',
            title: 'Import & Audit Log',
            content: 'Quickly import grades from DepEd Excel class records or inspect historical score edits via the Assessment Log.',
            placement: 'bottom'
        }
    ],

    // 2. My Classes (Grading Dashboard)
    'teacher-myclasses': [
        {
            target: '[data-tour="myclasses-stats"]',
            title: 'Grading Performance Overview',
            content: 'Check your active class count, total enrolled learners, current term, and flagged at-risk students.',
            placement: 'bottom'
        },
        {
            target: '[data-tour="myclasses-cards"]',
            title: 'Your Teaching Assignments',
            content: 'Select any assigned subject and section card to launch its interactive Grade Sheet.',
            placement: 'bottom'
        }
    ],

    // 3. Room Attendance
    'teacher-attendance': [
        {
            target: '[data-tour="teacher-attendance-sections"]',
            title: 'Classroom Attendance Roster',
            content: 'Select a section to verify daily classroom presence and view student attendance logs.',
            placement: 'bottom'
        }
    ],

    // 4. Student Management (Class Selection)
    'teacher-students': [
        {
            target: '[data-tour="teacher-students-classes"]',
            title: 'Assigned Classes',
            content: 'Browse students enrolled in each of your advisory sections or teaching assignments.',
            placement: 'bottom'
        }
    ],

    // 4b. Student Roster (Class Student List)
    'teacher-student-roster': [
        {
            target: '[data-tour="teacher-student-roster-actions"]',
            title: 'Roster Tools',
            content: 'Download an Excel sheet of this class roster or register a new student directly.',
            placement: 'bottom'
        },
        {
            target: '[data-tour="teacher-student-roster-search"]',
            title: 'Student Search',
            content: 'Quickly find specific students by their LRN, student number, or name.',
            placement: 'bottom'
        },
        {
            target: '[data-tour="teacher-student-roster-table"]',
            title: 'Class Roster List',
            content: 'Browse all enrolled learners in this class, check attendance statuses, and manage records.',
            placement: 'top'
        }
    ],

    // 5. Grading Analytics
    'teacher-analytics': [
        {
            target: '[data-tour="teacher-analytics-overview"]',
            title: 'Academic Performance Insights',
            content: 'Track subject grade distributions, grade level averages, and student performance metrics.',
            placement: 'bottom'
        }
    ],

    // 6. At-Risk Students
    'teacher-at-risk': [
        {
            target: '[data-tour="teacher-at-risk-table"]',
            title: 'Early Academic Intervention',
            content: 'Identify learners with failing risk scores or high absences to plan timely academic support.',
            placement: 'bottom'
        }
    ],

    // 7. Class Reports
    'teacher-reports': [
        {
            target: '[data-tour="teacher-reports-export"]',
            title: 'DepEd Reports Generation',
            content: 'Generate official DepEd electronic class records, quarterly summaries, and export XLSX or PDF files.',
            placement: 'bottom'
        }
    ],

    // 8. Grading Rules
    'teacher-grading-rules': [
        {
            target: '[data-tour="teacher-grading-rules-weights"]',
            title: 'DepEd Grading Formulas',
            content: 'Review the component weight distributions (Written Works, Performance Tasks, Quarterly Assessment) applied to your curriculum.',
            placement: 'bottom'
        }
    ]
};
