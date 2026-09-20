/**
 * Product Tour System Entry Point
 * Orchestrates automatic first-time onboarding and manual "Guide" button restarts.
 */
import { globalTourEngine } from './tour-engine.js';
import { schoolAdminTours } from './tours/school-admin.js';

export function initSchoolAdminTour() {
    // 1. Detect current page ID from container or current URL path
    const pageContainer = document.querySelector('[data-tour-page]');
    let pageId = pageContainer ? pageContainer.getAttribute('data-tour-page') : null;

    if (!pageId) {
        const path = window.location.pathname;
        if (path.includes('/academic') || path.includes('/sections') || path.includes('/subjects')) {
            pageId = 'academic';
        } else if (path === '/dashboard' || path.endsWith('/dashboard')) {
            pageId = 'dashboard';
        } else if (path.includes('/student-management')) {
            pageId = 'students';
        } else if (path.includes('/teachers')) {
            pageId = 'teachers';
        } else if (path.includes('/attendance')) {
            pageId = 'attendance';
        }
    }

    if (!pageId || !schoolAdminTours[pageId]) {
        // Current page has no tour configured; hide guide button if present
        const guideBtn = document.getElementById('saTourGuideTrigger');
        if (guideBtn) {
            guideBtn.classList.add('d-none');
        }
        return;
    }

    const currentTourConfig = schoolAdminTours[pageId];

    // 2. Setup Manual Trigger Button ("Guide" in top-nav)
    const guideBtn = document.getElementById('saTourGuideTrigger');
    if (guideBtn) {
        guideBtn.classList.remove('d-none');
        guideBtn.onclick = (e) => {
            e.preventDefault();
            globalTourEngine.start(pageId, currentTourConfig, true /* force restart */);
        };
    }

    // 3. Auto-Trigger for first-time visits (delayed slightly for initial page render)
    setTimeout(() => {
        globalTourEngine.start(pageId, currentTourConfig, false /* don't force if already completed */);
    }, 600);
}

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSchoolAdminTour);
} else {
    initSchoolAdminTour();
}

export { globalTourEngine, schoolAdminTours };
