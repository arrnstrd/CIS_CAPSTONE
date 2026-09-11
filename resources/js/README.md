# Frontend JavaScript Architecture (Point-of-View Isolation)

This directory contains all client-side JavaScript for the application. The architecture strictly organizes scripts following a **Point-of-View (POV) Hierarchy** to prevent script cross-contamination, unbounded bundle bloat, and conflicting event handlers.

---

## 🏛️ Architectural Context & Motivation

### The Problem This Solves
In complex Laravel applications with multiple user roles (`school-admin`, `teacher`, `scanner-operator`, `super-admin`), placing scripts into a single monolithic script or arbitrary root directory causes several critical problems:
1. **Event Listener Collisions:** An event listener on `show.bs.modal` or `form[data-ajax-form]` meant for a Teacher modal might intercept or conflict with a School Admin modal of the same generic name.
2. **Page-Specific Script Execution on Unintended Pages:** Scripts running on pages where their target DOM elements do not exist throw `TypeError: document.getElementById(...) is null` errors in the browser console.
3. **Difficult Maintenance:** When code is monolithic, developers cannot tell which scripts are safe to edit without risking breakage in other user portals.

### The Solution: 3-Tier JS Separation
* **Tier 1 — Global Core & Shell:** Application-wide services required across all authenticated sessions (Laravel Echo, navigation active-state management).
* **Tier 2 — Global Shared Utilities:** Pure helper functions and generic AJAX CRUD orchestrators (`window.ajaxCrud`) with zero role-specific business logic.
* **Tier 3 — POV Page-Specific Modules:** Purpose-built modal controllers, validation scripts, and feature orchestrators scoped to a single role and page.

---

## 📁 Directory Structure

```text
resources/js/
├── app.js                                    # Master JS entry point (compiled by Vite)
├── echo.js                                   # Global Laravel Echo & WebSockets setup
├── layout.js                                 # Global application layout helpers
├── sidebar.js                                # Global sidebar collapse & active route highlighter
│
├── shared/                                   # Universal reusable helper scripts
│   └── ajax-crud.js                          # Core AJAX form & table refresh engine (window.ajaxCrud)
│
└── pov/                                      # Role-specific scripts
    ├── school-admin/                         # School Admin POV Modules
    │   ├── academic/                         # Section CRUD & Subject modal controllers
    │   ├── bulk-import/                      # SF1 interactive drag-drop import engine
    │   ├── qr-generation/                    # Section QR batch download triggers
    │   ├── schedule-configuration/           # Bell schedule modals & time boundary checks
    │   ├── settings/                         # School year toggle modals
    │   ├── students/                         # Student wizard step navigation & section filters
    │   ├── teachers/                         # Teacher profile edit & archive/restore handlers
    │   ├── teaching-assignments/             # Subject-to-teacher assignment modals
    │   └── time-in-time-out-history/         # Date boundary validator for Excel download
    │
    └── scanner-operator/                     # Scanner Operator POV Modules
        └── qr-station/                       # QR Scanner Terminal
            ├── qr-station.js                 # Terminal bootstrap & state entry point
            └── modules/                      # Modular subsystems (audio, API, render, input, core)
```

---

## 🧭 The 3 Tiers of JavaScript: Where Does Your Code Belong?

| Tier | Directory | Purpose | Key Examples |
| :--- | :--- | :--- | :--- |
| **1. Global Core** | `resources/js/` | Realtime broadcasting and shell-level navigation | `echo.js`, `sidebar.js`, `layout.js` |
| **2. Global Shared Utility** | `resources/js/shared/` | Generic reusable utilities without role dependencies | `ajax-crud.js` (`window.ajaxCrud`) |
| **3. POV Page-Specific** | `resources/js/pov/<role>/<page>/` | Specific modals, page handlers, wizards | `student.js`, `teacher.js`, `import.js` |

---

## 🛠️ Step-by-Step Guide: Adding New JavaScript

Whenever creating client-side logic for a page or modal, follow these steps:

### Step 1: Create the File in the Matching POV Directory
Identify the user role and page module:
```bash
# Example: Adding a script for Teacher Class Grading Sheet calculations
mkdir -p resources/js/pov/teacher/my-classes
touch resources/js/pov/teacher/my-classes/grade-sheet.js
```

### Step 2: Implement Defensive DOM Execution
Never assume the target HTML elements are present when the script evaluates. Always use guards or event delegation:
```javascript
// resources/js/pov/teacher/my-classes/grade-sheet.js

document.addEventListener('DOMContentLoaded', () => {
    const table = document.getElementById('teacherGradeSheetTable');
    if (!table) {
        return; // Guard: Exit early if not on the Grade Sheet page
    }

    // Page-specific initialization logic here...
});
```

If interacting with standard AJAX CRUD operations, leverage the global utility:
```javascript
// Submit forms asynchronously with built-in validation & error display:
window.ajaxCrud.submitAjaxForm(formElement, {
    scope: '#grade-sheet-pane',
    onSuccess: (data) => { console.log('Saved', data); }
});
```

### Step 3: Register in `resources/js/app.js`
Open `resources/js/app.js` and import your script under the appropriate POV heading:
```javascript
// 2. POV: School Admin
...
// 3. POV: Teacher
import "./pov/teacher/my-classes/grade-sheet.js"; // <-- ADD HERE

// 4. POV: Scanner Operator
import "./pov/scanner-operator/qr-station/qr-station.js";
```

### Step 4: Validate the Build
Run the Vite compiler to ensure syntax correctness and import resolution:
```bash
# For production build validation:
./vendor/bin/sail npm run build

# For local development with Hot Module Replacement (HMR):
./vendor/bin/sail npm run dev
```

---

## ⚠️ Anti-Patterns & Best Practices

1. **No Dead or Empty Placeholder Files:** Do not create empty `.js` files. If a page is entirely server-rendered Blade without client-side behavior, it does not need a JS file.
2. **Favor Event Delegation for Dynamic Content:** Because tables and tab panels are updated dynamically via AJAX, attach event listeners to `document` or stable parents using `.matches()` or `.closest()`:
   ```javascript
   // Preferred: Works even after AJAX table refresh
   document.addEventListener('click', (event) => {
       const button = event.target.closest('.js-calculate-total');
       if (button) { /* handle calculation */ }
   });
   ```
3. **Specific Modal IDs:** Always guard Bootstrap modal hooks (`show.bs.modal`, `hidden.bs.modal`) with explicit ID checks:
   ```javascript
   document.addEventListener('show.bs.modal', (event) => {
       if (event.target?.id !== 'editStudentModal') return;
       // Handle student edit modal only
   });
   ```
4. **Keep `shared/ajax-crud.js` Generic:** Never add page-specific modal IDs or selectors into `ajax-crud.js`. Page-specific form handling belongs in its respective `resources/js/pov/<role>/<page>/` file.
