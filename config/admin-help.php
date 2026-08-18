<?php

/*
|--------------------------------------------------------------------------
| Admin Help & FAQ content
|--------------------------------------------------------------------------
|
| Plain-language, non-technical guidance shown in the reusable "Help" button
| that appears in the admin header banner (components/help-button.blade.php).
|
| Each entry is keyed by the route name (preferred) or the URI path (fallback
| for routes that have no name). Every entry supports:
|
|   'title' => modal heading
|   'intro' => one short paragraph explaining what the page is for
|   'steps' => ordered "how it works" cards: [['title' =>, 'body' =>], ...]
|   'faqs'  => accordion items: [['q' =>, 'a' =>], ...]
|
| Keep the wording simple and friendly — the audience is non-technical staff.
*/

$studentProfile = [
    'title' => 'Student Profile — Help & FAQ',
    'intro' => "View and manage a single student's full record: personal info, guardian info, academics, attendance, and QR code.",
    'steps' => [
        ['title' => 'Personal info', 'body' => "Click Edit on the Personal Information card to update details, then Save."],
        ['title' => 'Guardian info', 'body' => "Click Edit on the Guardian Information card to update parent/guardian contact details."],
        ['title' => 'Tabs', 'body' => "Use the tabs: Academic (enrollment history), Attendance (scans), and QR Code (their QR card)."],
        ['title' => 'Teachers', 'body' => "Teachers can view the profile but cannot edit it — the Edit buttons are hidden for them."],
    ],
    'faqs' => [
        ['q' => "Why don't I see the Edit buttons?", 'a' => 'Only administrators can edit a profile. Teachers can view the profile only.'],
        ['q' => "How do I change a student's section or school year?", 'a' => "That is managed through the student's enrollment. Check the Academic tab or coordinate with an administrator."],
        ['q' => "Where do I find a student's QR code?", 'a' => 'Open the QR Code tab on their profile.'],
        ['q' => "What does 'Not Enrolled' mean?", 'a' => 'The student has no active enrollment for the current school year.'],
    ],
];

return [

    'admin.dashboard' => [
        'title' => 'Dashboard — Help & FAQ',
        'intro' => "The Dashboard gives you a quick snapshot of today's attendance: how many students scanned in and out, and the busiest times. Tap any card to jump straight to that module.",
        'steps' => [
            ['title' => 'Stat cards', 'body' => "The cards at the top show today's totals. Click a card to open that module."],
            ['title' => 'Attendance by level', 'body' => 'The chart splits scans into Elementary, High School, and Senior High.'],
            ['title' => 'Weekly trend', 'body' => 'Attendance by week shows the last 6 weeks so you can spot busy or slow days.'],
            ['title' => 'Chart style', 'body' => 'Use the Bars / Line / Table toggle to change how the same data is shown.'],
            ['title' => 'Latest scans', 'body' => 'The list shows recent IN/OUT records, including remarks like Late arrival.'],
            ['title' => 'Peak', 'body' => 'Peak window/week is the time of day or week with the most scans.'],
        ],
        'faqs' => [
            ['q' => 'Why do the card numbers and the charts not match?', 'a' => 'The cards show today only, while some charts cover the last 6 weeks.'],
            ['q' => 'What does IN / OUT mean?', 'a' => 'IN means the student scanned to enter (Time In). OUT means they scanned to leave (Time Out).'],
            ['q' => "What is a 'Peak window'?", 'a' => 'It is simply the time of day when the most students scanned — the busiest period.'],
            ['q' => 'How do I change what the chart shows?', 'a' => 'Use the Bars / Line / Table toggle above the chart.'],
        ],
    ],

    // QR Code Generation — route: qr.index (path: qr-generation)
    'qr.index' => [
        'title' => 'QR Code Generation — Help & FAQ',
        'intro' => 'Generate printable PDF ID cards with QR codes for every student in a section — one download per section.',
        'steps' => [
            ['title' => 'Find a section', 'body' => 'Use the search box or grade filter to narrow the list of sections.'],
            ['title' => 'Pick a section', 'body' => 'Each row is one section. Sections with no students cannot be downloaded.'],
            ['title' => 'Download QR', 'body' => "Click Download QR to get a PDF of that section's student ID cards."],
            ['title' => 'Print', 'body' => "Open the PDF and print the cards. Each card shows the student's name and QR code."],
        ],
        'faqs' => [
            ['q' => 'Why is the Download QR button grayed out?', 'a' => 'That section has no students yet. Add students to the section first.'],
            ['q' => 'What file do I get?', 'a' => 'A PDF file named like QR-Grade-Section.pdf with one card per student.'],
            ['q' => "A student's QR code is missing or broken.", 'a' => "The card is generated from the student's stored QR code. Have the student's QR re-generated or contact support."],
        ],
    ],

    // Schedule Configuration — path only (GET route has no name)
    'schedule-configuration' => [
        'title' => 'Schedule Configuration — Help & FAQ',
        'intro' => 'A schedule configuration defines when students may enter and exit campus. Each configuration is tied to an Education Level and a Session Type.',
        'steps' => [
            ['title' => 'Department', 'body' => 'Elementary, High School (HS), or Senior High School (SHS).'],
            ['title' => 'Session', 'body' => 'Morning, Afternoon, or Whole-day session.'],
            ['title' => 'IN Window', 'body' => 'The allowed time range for entry scanning.'],
            ['title' => 'Late Threshold', 'body' => 'Scans after this time are marked as Late.'],
            ['title' => 'OUT Window', 'body' => 'The allowed time range for exit scanning.'],
            ['title' => 'Actions', 'body' => 'Use the ellipsis to Edit or Delete a schedule.'],
        ],
        'faqs' => [
            ['q' => 'What is a schedule configuration?', 'a' => 'It defines the allowed entry (IN) and exit (OUT) scanning windows for a specific Education Level and Session Type, plus the Late Threshold used to flag late arrivals.'],
            ['q' => 'What happens if a student scans outside the IN Window?', 'a' => 'A scan before the Entry Start Time or after the Entry End Time is considered outside the allowed window. Widen the schedule if needed.'],
            ['q' => 'How does the Late Threshold work?', 'a' => 'Any entry scan after the Late Threshold time is marked as Late. It should sit between Entry Start and Entry End.'],
            ['q' => 'Can I have different sessions for the same level?', 'a' => 'Yes. For example, Elementary can have both a Morning and an Afternoon session. Add one schedule per level-session pair.'],
            ['q' => "Why can't I delete a schedule?", 'a' => 'Deleting a schedule is permanent. If active attendance records are tied to it, coordinate with your administrator first.'],
        ],
    ],

    // Settings — path only (no route name)
    'settings' => [
        'title' => 'Settings — Help & FAQ',
        'intro' => "Manage your school's basic setup here, mainly the list of school years and which one is currently active.",
        'steps' => [
            ['title' => 'School years', 'body' => 'Add a new school year (for example 2026-2027) when a new school year starts.'],
            ['title' => 'Active year', 'body' => 'Set the current school year as Active. The system uses the active year for all records.'],
            ['title' => 'Edit / Archive', 'body' => 'Use the ellipsis to edit a year, or Archive it to hide it. Restore brings it back.'],
            ['title' => 'One active', 'body' => 'Only one school year can be active at a time.'],
        ],
        'faqs' => [
            ['q' => "What does 'Set as active school year' do?", 'a' => 'It tells the system which school year is current, so all records are tagged to the right year.'],
            ['q' => "What's the difference between Archive and Delete?", 'a' => 'Archive just hides the year and can be undone (Restore). Delete removes it permanently.'],
            ['q' => 'Where do I change the school name and logo?', 'a' => 'Those fields are placeholders on this page for now. Contact your developer to change them.'],
        ],
    ],

    // Bulk Import Students — route: bulk-import
    'bulk-import' => [
        'title' => 'Bulk Import Students — Help & FAQ',
        'intro' => 'Import many students at once from the official DepEd SF-1 Excel file. The system checks every row and tells you which ones are ready and which need fixing.',
        'steps' => [
            ['title' => 'Download the template', 'body' => 'Click Template to get the exact Excel format the system expects (.xlsx).'],
            ['title' => 'Fill it in', 'body' => 'Enter students using the template columns (LRN, Learner Name, Sex, Grade, Section, etc.).'],
            ['title' => 'Upload', 'body' => 'Click Bulk Import, choose the file (max 10 MB), then Upload & Validate.'],
            ['title' => 'Check issues', 'body' => 'Rows with Errors are skipped. Warnings import fine but are worth reviewing.'],
            ['title' => 'Import', 'body' => 'Review the issues, then proceed. Watch the progress bar — it shows Created vs Failed.'],
            ['title' => 'Follow up', 'body' => 'Use the History tab for past imports and the Issues tab to acknowledge or download errors.'],
        ],
        'faqs' => [
            ['q' => 'What is an SF-1 file and where do I get it?', 'a' => 'It is the DepEd School Register spreadsheet (Excel) that lists students. Download the template from this page and fill it in.'],
            ['q' => 'Why did my upload fail validation?', 'a' => 'Usually a wrong file format, an LRN that already exists, or a missing required field. The issue list shows exactly which row and why.'],
            ['q' => 'What is the difference between an Error and a Warning?', 'a' => 'An Error blocks that row from importing. A Warning means the row will import but has something worth checking, like no guardian.'],
            ['q' => "What does 'Acknowledge All' do?", 'a' => 'It marks all issues as reviewed. It does NOT change or delete any data.'],
        ],
    ],

    // Section Management — route: sections.index
    'sections.index' => [
        'title' => 'Section Management — Help & FAQ',
        'intro' => 'Create and manage class sections — name, level, grade, adviser, capacity, and session type.',
        'steps' => [
            ['title' => 'Add a section', 'body' => 'Click + Add Section and enter the section name, level, grade, session type, adviser, and capacity.'],
            ['title' => 'Match grade to level', 'body' => 'The grade must fit the level: Elementary is grades 1–6, High School 7–10, Senior High 11–12.'],
            ['title' => 'Capacity', 'body' => 'Set the maximum number of students the section can hold.'],
            ['title' => 'Manage', 'body' => 'Use the row’s ellipsis to Edit, Archive (hide), or Restore a section.'],
        ],
        'faqs' => [
            ['q' => 'What’s the difference between Archive and Delete?', 'a' => 'Archive sets the section to Inactive (hidden) and can be undone. Delete removes it permanently.'],
            ['q' => 'How many students can I put in a section?', 'a' => 'As many as the Capacity you set. The table shows students / capacity.'],
            ['q' => 'Why can’t I save a grade that doesn’t match the level?', 'a' => 'The grade must be valid for the chosen level (for example, Grade 8 cannot be Elementary).'],
        ],
    ],

    // Student Management (grade grid) — route: student-management.index
    'student-management.index' => [
        'title' => 'Student Management — Help & FAQ',
        'intro' => 'This is the landing page for student records. Students are organized by grade — click a grade to open its list.',
        'steps' => [
            ['title' => 'Pick a level', 'body' => 'Levels are grouped as Primary, Junior High, and Senior High.'],
            ['title' => 'Click a grade', 'body' => 'Each card shows the grade and how many students it has. Click it to see the students.'],
            ['title' => 'Manage students', 'body' => 'On the next page you can add, edit, view, or delete students in that grade.'],
        ],
        'faqs' => [
            ['q' => 'Where do I actually add or edit a student?', 'a' => 'Open the grade first, then use the buttons on the student list page.'],
            ['q' => 'How do I get to a specific grade’s students?', 'a' => 'Click the grade card here.'],
        ],
    ],

    // Students · Grade N (per-grade list) — route: student-management.grade
    'student-management.grade' => [
        'title' => 'Grade Students — Help & FAQ',
        'intro' => 'This is the student list for one grade. Search, filter, add, view, edit, or remove students here.',
        'steps' => [
            ['title' => 'Add a student', 'body' => 'Click + Add Student to add a new student to this grade.'],
            ['title' => 'Search', 'body' => 'Search by name, LRN, or student number.'],
            ['title' => 'Filter', 'body' => 'Filter by school year, section, or status to narrow the list.'],
            ['title' => 'View a profile', 'body' => 'Click View (eye) to open the student’s full profile in a new tab.'],
            ['title' => 'Edit / Delete', 'body' => 'Use the row’s ellipsis to Edit or Delete a student.'],
        ],
        'faqs' => [
            ['q' => 'How do I search for a student by LRN?', 'a' => 'Type the LRN into the search box and press Enter.'],
            ['q' => 'What’s the difference between View and Edit?', 'a' => 'View opens the full profile page. Edit changes the row’s basic info in place.'],
            ['q' => 'Why is a student missing when I filter by section?', 'a' => 'They may be enrolled in a different section, a different school year, or have an Inactive status. Clear the filters to see everyone.'],
        ],
    ],

    // Student Profile — route: student.profile and nameless path /student-profile
    'student.profile' => $studentProfile,
    'student-profile' => $studentProfile,

    // Teacher Management — route: teachers.index
    'teachers.index' => [
        'title' => 'Teacher Management — Help & FAQ',
        'intro' => 'Manage teacher accounts — their contact info and whether their account is active.',
        'steps' => [
            ['title' => 'Add a teacher', 'body' => 'Click + Add Teacher and enter first name, last name, and email.'],
            ['title' => 'Default password', 'body' => 'New teachers get a default password (Password123) and should change it on first login.'],
            ['title' => 'Search / filter', 'body' => 'Use search or the status filter to find teachers.'],
            ['title' => 'Edit / Archive', 'body' => 'Use the row’s ellipsis to Edit or Archive. Archiving also deactivates their login.'],
        ],
        'faqs' => [
            ['q' => 'What password does a new teacher use to log in?', 'a' => 'Password123. They should change it the first time they log in.'],
            ['q' => 'What’s the difference between Archive and Delete?', 'a' => 'Archive deactivates the teacher and their login, and can be undone. Delete is permanent.'],
            ['q' => 'Why can’t I edit a teacher after archiving?', 'a' => 'Restore the teacher first, then edit.'],
        ],
    ],

    // User and Role Management — path only (stub page)
    'users' => [
        'title' => 'User and Role Management — Help',
        'intro' => 'This page is under construction. User account and role management is not available here yet.',
        'steps' => [],
        'faqs' => [
            ['q' => 'Why is this page empty?', 'a' => 'The feature is still being built. Teachers are managed on the Teacher Management page.'],
        ],
    ],

    // Grades — path only (under construction)
    'grades' => [
        'title' => 'Grades — Help',
        'intro' => 'Grade recording is under construction and not available yet.',
        'steps' => [],
        'faqs' => [
            ['q' => 'When will grade management be available?', 'a' => 'The grading system is still in development.'],
        ],
    ],

    // Academic Setup (tabs container) — route: academic.index
    'academic.index' => [
        'title' => 'Academic Setup — Help & FAQ',
        'intro' => 'Set up your school’s academic structure here — class sections and subjects — all in one place using tabs.',
        'steps' => [
            ['title' => 'Section tab', 'body' => 'Manage sections (name, grade, adviser, capacity) under the Section tab.'],
            ['title' => 'Subject tab', 'body' => 'Manage subjects (code, name, level) under the Subject tab.'],
        ],
        'faqs' => [
            ['q' => 'Where do I add a section vs. a subject?', 'a' => 'Use the Section tab for sections and the Subject tab for subjects.'],
        ],
    ],

    // Subject Records — route: subjects.index
    'subjects.index' => [
        'title' => 'Subject Records — Help & FAQ',
        'intro' => 'Manage the school’s subject list — the subjects teachers will be assigned to teach.',
        'steps' => [
            ['title' => 'Add a subject', 'body' => 'Click + Add Subject and enter a Code (short name like MATH7), Subject Name, and Level.'],
            ['title' => 'Search / filter', 'body' => 'Search by code, name, or level to find a subject.'],
            ['title' => 'Edit / Delete', 'body' => 'Use the row’s ellipsis to Edit or Delete a subject.'],
        ],
        'faqs' => [
            ['q' => 'What’s the difference between Code and Subject Name?', 'a' => 'Code is the short identifier (e.g., MATH7). Name is the full subject name (e.g., Mathematics).'],
            ['q' => 'Can the same subject exist at multiple levels?', 'a' => 'Yes, add it once per level if needed.'],
            ['q' => 'Is Delete permanent?', 'a' => 'Yes, deleting a subject is permanent.'],
        ],
    ],

    // Teaching Assignments — route: teaching-assignments.index
    'teaching-assignments.index' => [
        'title' => 'Teaching Assignments — Help & FAQ',
        'intro' => 'Assign teachers to subjects, sections, and school years so the system knows who teaches what and where.',
        'steps' => [
            ['title' => 'Add an assignment', 'body' => 'Click Add New and choose Teacher, Subject, Section, School Year, and Status.'],
            ['title' => 'Session type', 'body' => 'The session type comes from the section automatically.'],
            ['title' => 'Status', 'body' => 'Active means the assignment is in effect. Inactive turns it off.'],
            ['title' => 'Edit / Delete', 'body' => 'Use the pencil to edit and the trash to delete an assignment.'],
        ],
        'faqs' => [
            ['q' => 'What is a teaching assignment?', 'a' => 'It links one teacher to one subject in one section for a school year.'],
            ['q' => 'Why is a section missing from the dropdown?', 'a' => 'Only active teachers and active sections are offered. Check the section’s status.'],
            ['q' => 'What does the assignment status do?', 'a' => 'Inactive assignments do not count — the teacher is not shown as teaching that section.'],
        ],
    ],

    // Class Attendance — route: attendance (under construction)
    'attendance' => [
        'title' => 'Class Attendance — Help',
        'intro' => 'Class attendance monitoring is under construction and not available yet.',
        'steps' => [],
        'faqs' => [
            ['q' => 'When will class attendance be available?', 'a' => 'This module is still in development.'],
        ],
    ],

    // Email Monitoring — route: emails.index
    'emails.index' => [
        'title' => 'Email Monitoring — Help & FAQ',
        'intro' => 'Monitor the gate-scan emails sent to parents and guardians — who got them, when, and whether they were sent.',
        'steps' => [
            ['title' => 'Overview', 'body' => 'The cards show Total, Sent, Pending, and Failed emails for the chosen period.'],
            ['title' => 'Filter', 'body' => 'Use Search, Status, Scan Type (IN/OUT), and the date pills (Today, This Week, This Month, Custom).'],
            ['title' => 'Resend', 'body' => 'Click Retry on a failed email, or Resend All to retry every failed email.'],
            ['title' => 'Reset', 'body' => 'Click Reset to clear all filters.'],
        ],
        'faqs' => [
            ['q' => 'Who receives these emails?', 'a' => 'The student’s parent or guardian email address, sent when the student scans in or out.'],
            ['q' => 'What does “pending” mean?', 'a' => 'The email is queued but has not been sent yet.'],
            ['q' => 'Why are some emails failing?', 'a' => 'Usually a wrong or unreachable email address. Fix the guardian’s email, then retry.'],
            ['q' => 'How do I resend an email that failed?', 'a' => 'Use the row’s Retry button, or Resend All to retry all failed emails at once.'],
        ],
    ],

    // QR Station — route: qr-station.index
    'qr-station.index' => [
        'title' => 'QR Station — Help & FAQ',
        'intro' => 'This is the live gate scanning screen. Scan students’ QR codes to log their Time In and Time Out.',
        'steps' => [
            ['title' => 'USB scanner', 'body' => 'Plug in a USB QR scanner. It works like a keyboard — just point and scan.'],
            ['title' => 'Manual entry', 'body' => 'If the scanner is not working, paste or type the QR code into the Manual QR String box and click Send.'],
            ['title' => 'Live queue', 'body' => 'The right side updates in real time with today’s scans, totals, and late counts.'],
        ],
        'faqs' => [
            ['q' => 'How do I use the USB scanner?', 'a' => 'Plug it in and scan the student’s QR card — the code is entered automatically, like typing.'],
            ['q' => 'The scanner is not working. How do I enter a scan manually?', 'a' => 'Paste or type the QR code into the Manual QR String box and click Send.'],
            ['q' => 'What does “Late” count in the overview?', 'a' => 'Scans that happened after the schedule’s late threshold.'],
            ['q' => 'How do I check a scan that just happened?', 'a' => 'Watch the live queue on the right — it updates as each scan comes in.'],
        ],
    ],

    // Time In / Time Out History — route: time-in-time-out-history.index
    'time-in-time-out-history.index' => [
        'title' => 'Time In / Time Out History — Help & FAQ',
        'intro' => 'Search and review all gate scan records — who scanned in or out, when, and any flags.',
        'steps' => [
            ['title' => 'Search / filter', 'body' => 'Search by student name or number; filter by Scan Type (IN/OUT), Session Type, Remarks, and date.'],
            ['title' => 'Remarks', 'body' => 'Rows with a remark (like Late arrival or Checkout issue) have an eye button to see details.'],
            ['title' => 'Download Excel', 'body' => 'Click Download Excel, pick a date range (max 31 days), and export the current results.'],
        ],
        'faqs' => [
            ['q' => 'What does “Checkout issue” mean?', 'a' => 'A flag on an OUT scan that needs review — for example, an invalid checkout time.'],
            ['q' => 'How do I export these records to Excel?', 'a' => 'Click Download Excel, choose a date range, and it exports the filtered records.'],
            ['q' => 'Why do some rows not have an action button?', 'a' => 'Only rows with remarks or flags have an eye button; clean rows show a dash.'],
            ['q' => 'How far back can I view or download scans?', 'a' => 'Each Excel export is limited to 31 days. Use the date filters to browse older periods.'],
        ],
    ],

    // Report Generation — path only (placeholder / not built)
    'report-generation' => [
        'title' => 'Report Generation — Help',
        'intro' => 'Report generation is not built yet. This page is a placeholder.',
        'steps' => [],
        'faqs' => [
            ['q' => 'Why is this page blank?', 'a' => 'Report generation is still in development.'],
        ],
    ],

];
