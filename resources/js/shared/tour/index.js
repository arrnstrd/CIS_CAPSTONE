/**
 * Product Tour System Entry Point
 * Orchestrates automatic first-time onboarding and manual "Guide" button restarts
 * for both School Admin and Teacher roles.
 */
import { globalTourEngine } from './tour-engine.js';
import { schoolAdminTours } from './tours/school-admin.js';
import { teacherTours } from './tours/teacher.js';

export function initProductTour() {
    const pageContainer = document.querySelector('[data-tour-page]');
    let pageId = pageContainer ? pageContainer.getAttribute('data-tour-page') : null;
    const path = window.location.pathname;

    // Detect School Admin Pages
    if (!pageId && !path.startsWith('/teacher')) {
        if (path.includes('/academic') || path.includes('/sections') || path.includes('/subjects')) {
            pageId = 'academic';
        } else if (path === '/dashboard' || path.endsWith('/dashboard')) {
            pageId = 'dashboard';
        } else if (path.includes('/student-management/grade/') || path.includes('/student-management/section/')) {
            pageId = 'students-roster';
        } else if (path.includes('/student-management')) {
            pageId = 'students';
        } else if (path.includes('/teachers')) {
            pageId = 'teachers';
        } else if (path.includes('/attendance')) {
            pageId = 'attendance';
        } else if (path.includes('/bulk-import') || path.includes('/import')) {
            pageId = 'sf1-import';
        } else if (path.includes('/schedule-configuration')) {
            pageId = 'schedule-config';
        }
    }

    // Detect Teacher Pages
    if (!pageId && path.startsWith('/teacher')) {
        if (path.includes('/teacher/grading-system/grade-sheet')) {
            pageId = 'teacher-gradesheet';
        } else if (path.includes('/teacher/grading-system/dashboard') || path === '/teacher/grading-system') {
            pageId = 'teacher-myclasses';
        } else if (path.includes('/teacher/room-attendance') || path.includes('/teacher/dashboard') || path.includes('/teacher/attendance')) {
            pageId = 'teacher-attendance';
        } else if (path.includes('/teacher/student-management')) {
            pageId = window.location.search.includes('section_id=') ? 'teacher-student-roster' : 'teacher-students';
        } else if (path.includes('/teacher/grading-system/analytics')) {
            pageId = 'teacher-analytics';
        } else if (path.includes('/teacher/grading-system/at-risk')) {
            pageId = 'teacher-at-risk';
        } else if (path.includes('/teacher/grading-system/reports')) {
            pageId = 'teacher-reports';
        } else if (path.includes('/teacher/grading-system/grading-rules') || path.includes('/teacher/grading-system/comp-rules')) {
            pageId = 'teacher-grading-rules';
        }
    }

    // Resolve tour configuration from respective dictionary
    const tourConfig = (pageId && schoolAdminTours[pageId]) 
        ? schoolAdminTours[pageId] 
        : (pageId && teacherTours[pageId] ? teacherTours[pageId] : null);

    // Setup Trigger Buttons
    const saGuideBtn = document.getElementById('saTourGuideTrigger');
    const teacherGuideBtn = document.getElementById('teacherTourGuideTrigger');

    if (!pageId || !tourConfig) {
        if (saGuideBtn) saGuideBtn.classList.add('d-none');
        if (teacherGuideBtn) teacherGuideBtn.classList.add('d-none');
        return;
    }

    if (saGuideBtn) {
        saGuideBtn.classList.remove('d-none');
        saGuideBtn.onclick = (e) => {
            e.preventDefault();
            globalTourEngine.start(pageId, tourConfig, true /* force restart */);
        };
    }

    if (teacherGuideBtn) {
        teacherGuideBtn.classList.remove('d-none');
        teacherGuideBtn.onclick = (e) => {
            e.preventDefault();
            globalTourEngine.start(pageId, tourConfig, true /* force restart */);
        };
    }

    // Auto-Trigger on first visit (after short delay for DOM stabilization)
    setTimeout(() => {
        globalTourEngine.start(pageId, tourConfig, false /* don't force if completed */);
    }, 600);
}

// Auto-initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initProductTour);
} else {
    initProductTour();
}

export { globalTourEngine, schoolAdminTours, teacherTours };
