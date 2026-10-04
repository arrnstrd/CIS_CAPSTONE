---
name: interactive-product-tour
description: Architectural specification and guidelines for the Interactive Product Tour System across the CIS portal. Governs the decoupled tour engine, SVG cutout masking, viewport collision positioning, and page configuration rules.
---

# Interactive Product Tour System Specification

Framework: **RISE UP**

## 1. Role
Senior Frontend Architect and UX Engineer. Core competency: scalable, non-intrusive onboarding flows, DOM element positioning, and decoupled component architecture that separates business logic from UI overlays.

---

## 2. Input (System Context)
* **Target Audience:** First-time users in School Admin and Teacher roles (extensible to Scanner Operators and Super Admins).
* **Core Mechanism:** Global rendering layer that dims the background, dynamically highlights a target DOM element with an SVG cutout mask, and positions a contextual popover beside it.
* **School Admin Scope:** Dashboard, Academic Setup, Student Management & Rosters, Teacher Management, Attendance, SF1 Bulk Import, Schedule Configuration (Time-Window Based).
* **Teacher Scope:** Grade Sheet (Interactive Matrix), My Classes (Grading Dashboard), Room Attendance, Student Management, Analytics, At-Risk Students, Reports, Grading Rules.

### Example Flow A (Academic Setup):
1. **Highlight Tabs (`[data-tour="academic-tabs"]`)** &rarr; Explain module navigation between Subjects, Sections, and Teaching Assignments.
2. **Highlight "Add" Button (`[data-tour="academic-add-btn"]`)** &rarr; Explain record creation.
3. **Highlight Column Filters (`[data-tour="academic-column-filters"]`)** &rarr; Explain Excel-style filtering.
4. **Highlight Table Actions (`[data-tour="academic-row-actions"]`)** &rarr; Explain inline edit/delete.
5. **Finish State** &rarr; Closes overlay and records completion in `localStorage`.

### Example Flow B (Teacher Interactive Grade Sheet):
1. **Quarter / Term Tabs (`[data-tour="gradesheet-terms"]`)** &rarr; Switch between DepEd quarters (Q1–Q4).
2. **Component Weights & Add Column (`[data-tour="gradesheet-components"]`)** &rarr; Review WW, PT, and Exam weights; insert new assessment columns.
3. **Interactive Scoring Grid (`[data-tour="gradesheet-grid"]`)** &rarr; Record individual learner scores with input validation.
4. **Automated Final Grades (`[data-tour="gradesheet-computed"]`)** &rarr; Explain live Initial Grade and DepEd Transmuted grade calculations.
5. **Actions (`[data-tour="gradesheet-actions"]`)** &rarr; Shortcut for importing DepEd Excel class records or opening the Assessment Log audit trail.

---

## 3. Steps (Implementation & Architecture)

### Step 1: Core Tour Engine Architecture
* Centralized singleton engine (`resources/js/shared/tour/tour-engine.js`).
* **State Tracking:** Tracks `isActive`, `currentStepIndex`, and `completedTours` via `localStorage` keys `cis_tour_completed_${pageId}`.
* **DOM Interaction:** Dynamically targets DOM elements using `data-tour="<key>"` selectors.
* **Visual Masking:** Injects fixed full-screen SVG overlay with `<mask id="cisTourSvgMask">` containing `<rect id="cisTourCutout" rx="8" ry="8">` to punch a transparent hole over the target element. An animated highlight box (`.cis-tour-highlight-box`) surrounds the active target.

### Step 2: Tooltip & Popover UI
The popover (`.cis-tour-popover`) contains:
* Header with step counter badge (e.g. `Step 2 of 5`) and direct Close button.
* Brief, scannable title and 1–3 short sentences of explanatory text.
* Footer navigation: Back, Next (or Finish on final step), and global Skip Tour.

### Step 3: Page-Level Configuration
Pages do NOT contain tour rendering logic. Steps are defined as plain declarative objects in configuration modules:
* `resources/js/shared/tour/tours/school-admin.js`
* `resources/js/shared/tour/tours/teacher.js`

### Step 4: Display Logic & Lifecycle
* **Auto-Trigger:** On page mount, check `hasCompletedTour(pageId)`. If `false`, automatically launch the tour.
* **Completion Persistence:** Clicking Skip or Finish permanently marks that page's tour as completed in `localStorage`.
* **Manual Trigger:** An always-available `"Guide"` button (`#saTourGuideTrigger` or `#teacherTourGuideTrigger`) in the top navigation bar restarts the tour on demand (`force = true`).
* **Element Absence Handling:** If a target element is not in DOM or not visible, gracefully skip to the next step or cleanly abort without throwing exceptions.
* **Auto-Scrolling:** Target elements are smoothly scrolled into view (`scrollIntoView({ behavior: 'smooth', block: 'nearest' })`) prior to bounding rect calculation.

---

## 4. Expectations (Acceptance Criteria)
* **Decoupled Architecture:** Tour engine is a reusable global layer. Pages only declare `data-tour` selectors.
* **Brevity & Focus:** 1–3 short sentences per step. 2–5 steps per page.
* **Responsive Positioning:** Automatically detects viewport boundaries and flips placement (bottom &harr; top, right &harr; left) to prevent clipping.
* **Non-Blocking Interaction:** Skipping or finishing immediately unmounts the overlay and restores full page interactivity.

---

## 5. Users
* **Immediate Targets:** School Administrators and Teachers.
* **Future Targets:** Scanner Operators and Super Admins can be supported simply by adding configuration arrays in separate role files without touching the core engine.

---

## 6. Parameters & Guardrails
* **No External Documents:** The tour points directly to live UI elements in context.
* **Zero UI Disruption:** Never redesign existing page layouts or alter core CSS to fit the tour. Only attach `data-tour="..."` attributes.
* **Z-Index Safety:** Overlay mounts directly to `document.body` with `z-index: 100000+` to safely float above sticky headers, modals, and dropdowns.
