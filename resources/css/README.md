# Frontend CSS Architecture (Point-of-View Isolation)

This directory houses all CSS styling for the application. The codebase enforces a strict **Point-of-View (POV) Based Isolation Architecture** to prevent CSS leakage and cascade collisions between distinct user roles (`school-admin`, `teacher`, `scanner-operator`, and `super-admin`).

---

## 🏛️ Architectural Context & Motivation

### The Problem This Solves
In role-heavy web applications, different user portals often share common terms (such as "sidebar", "banner", "card", "active", "filter-bar"). When styles are declared globally or unstructured:
1. Changes made to improve the **Teacher** layout inadvertently break elements in the **School Admin** or **Scanner Operator** views.
2. Specificity wars emerge (`!important` cascades) to override styles from other modules.
3. Developers hesitate to refactor or restyle a single page out of fear of causing regressions elsewhere.

### The Solution: POV Isolation
We isolate styles into three clear tiers of responsibility:
1. **Global Core:** Strictly foundational, neutral styles applied application-wide (Bootstrap reset, dark mode, basic table styling, badge indicators).
2. **POV Shell & Shared:** Layouts, sidebars, top headers, and reusable UI components that belong strictly to one specific role.
3. **Page-Specific:** Styles scoped to a single feature or sidebar page within a role.

---

## 📁 Directory Structure

```text
resources/css/
├── app.css                                   # Master CSS manifest (compiles all imports via Vite)
├── dark-mode.css                             # Global dark theme variables and overrides
├── status-badges.css                         # Global status indicators and dot badges
├── table.css                                 # Global responsive table baseline
└── pov/                                      # Role-based isolated directories
    ├── school-admin/                         # School Admin POV
    │   ├── layout/                           # Layout shell, sa-head-banner, and sa-sidebar
    │   ├── shared/                           # Shared components (e.g. grade-level.css cards)
    │   ├── academic/                         # Academic setup and tab navigation
    │   ├── bulk-import/                      # SF1 import drag-and-drop workspace
    │   ├── qr-generation/                    # Section QR batch generator interface
    │   └── time-in-time-out-history/         # Attendance history & flagged log modal
    │
    ├── teacher/                              # Teacher POV
    │   ├── layout/                           # Layout shell, teacher-top-nav, and teacher-sidebar
    │   ├── shared/                           # Shared grading system cards and scorecards
    │   ├── attendance/                       # Room attendance tracker and daily filters
    │   └── student-management/               # Teacher class card grid (.sm-classes-*)
    │
    ├── scanner-operator/                     # Scanner Operator POV
    │   ├── layout/                           # Scanner layout shell and scanner-sidebar
    │   ├── qr-station/                       # Live QR terminal interface
    │   └── time-in-time-out-history/         # Terminal log history and flagged modal
    │
    └── super-admin/                          # Super Admin POV
        └── layout/                           # Super admin layout shell and sidebar
```

---

## 🧭 The 3 Tiers of CSS: Where Does Your Code Belong?

| Tier | Directory | Scope | Examples |
| :--- | :--- | :--- | :--- |
| **1. Global Core** | `resources/css/` | All roles across the entire application | `table.css`, `dark-mode.css`, `status-badges.css` |
| **2. POV Shared** | `resources/css/pov/<role>/shared/` or `layout/` | Multiple pages within a single user role | `grade-level.css`, `grading-system.css`, layout/sidebar files |
| **3. Page-Specific**| `resources/css/pov/<role>/<page>/` | A single specific view or sidebar item | `bulk-import.css`, `room-attendance.css`, `qr-generation.css` |

---

## 🛠️ Step-by-Step Guide: Adding New CSS

Follow these steps whenever creating new styles:

### Step 1: Identify the Role and Target Page
Do not place feature CSS directly in the root of `resources/css/`. Determine:
* Which user role uses this UI? (`school-admin`, `teacher`, `scanner-operator`, `super-admin`)
* Is it a single page or shared across multiple pages within that role?

Create a directory matching the feature name if one does not already exist:
```bash
# Example: Adding styles for a new Teacher Analytics page
mkdir -p resources/css/pov/teacher/analytics
touch resources/css/pov/teacher/analytics/analytics.css
```

### Step 2: Enforce Unique Class Prefixes
To guarantee that styles will never collide with other roles or layouts, use role and feature prefixes:
* **School Admin Shell:** `sa-` (e.g. `.sa-head-banner`, `.sa-page-content`, `.sa-sidebar-wrapper`)
* **Teacher Shell:** `teacher-` (e.g. `.teacher-head-banner`, `.teacher-top-nav`, `.teacher-sidebar-wrapper`)
* **Scanner Operator Shell:** `scanner-` (e.g. `.scanner-head-banner`, `.scanner-sidebar-wrapper`)
* **Super Admin Shell:** `super-admin-` (e.g. `.super-admin-head-banner`, `.super-admin-sidebar-wrapper`)
* **Page Features:** Wrap the page in a root container class and scope all sub-elements:
  ```css
  /* Good: Scoped and descriptive */
  .teacher-analytics-page { ... }
  .teacher-analytics-card { ... }
  .teacher-analytics-stat-value { ... }

  /* Bad: Generic and prone to collisions */
  .card { ... }
  .stat { ... }
  .header { ... }
  ```

### Step 3: Register the File in `resources/css/app.css`
Open `resources/css/app.css` and add the `@import` under the corresponding POV section:
```css
/* 4. POV: Teacher */
/* 4.1 Teacher Layout & Shell */
@import './pov/teacher/layout/layout.css';
@import './pov/teacher/layout/sidebar.css';
@import './pov/teacher/layout/top-nav.css';
/* 4.2 Teacher Shared */
@import './pov/teacher/shared/grading-system.css';
/* 4.3 Teacher Page-Specific */
@import './pov/teacher/attendance/room-attendance.css';
@import './pov/teacher/student-management/students.css';
@import './pov/teacher/analytics/analytics.css'; /* <-- ADD HERE */
```

### Step 4: Compile Assets
Compile using Laravel Sail / Vite to ensure there are no broken `@import` paths:
```bash
# For production build validation:
./vendor/bin/sail npm run build

# For local development with Hot Module Replacement (HMR):
./vendor/bin/sail npm run dev
```

---

## ⚠️ Anti-Patterns & Best Practices

1. **No Direct Blade `<link>` or `@import` tags:** All application styles must flow through `resources/css/app.css`, which is compiled by Vite and loaded via `@vite(['resources/css/app.css', 'resources/js/app.js'])`.
2. **Beware of Relative `@import` Paths:** If one CSS file imports another (e.g., `qr-generation.css` importing `qr-station.css`), ensure the relative path traverses correctly (e.g., `@import url('../../scanner-operator/qr-station/qr-station.css');`).
3. **No Dead or Empty Placeholder Files:** Do not create placeholder files until actual CSS rules are needed.
