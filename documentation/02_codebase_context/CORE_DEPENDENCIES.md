# Core Dependencies Reference

This document catalogs third-party dependencies utilized in `CIS_CAPSTONE`, explaining their integration points and technical rationale.

---

## 1. Backend Dependencies (`composer.json`)

### `laravel/framework` (`^13.0`)
* **Purpose:** Core Web Application Framework.
* **Usage:** Provides MVC architecture, routing, Eloquent ORM, authentication, service container, and blade templating.

### `laravel/reverb` (`^1.0`)
* **Purpose:** Native WebSocket Server for Laravel.
* **Usage:** Manages real-time bi-directional WebSocket connections for instantaneous attendance dashboard updates when students scan at the QR station.

### `simplesoftwareio/simple-qrcode` (`^4.2`)
* **Purpose:** QR Code Generator.
* **Usage:** Generates 2D QR codes encoding unique student numbers. Encapsulated in `app/Libraries/QRCode/SimpleQrCodeAdapter.php` to allow bulk badge printing and digital ID rendering.

### `barryvdh/laravel-dompdf` (`^3.1`)
* **Purpose:** HTML to PDF Conversion.
* **Usage:** Compiles Blade views into official downloadable PDF documents: DepEd SF-9 (Report Cards), DepEd SF-1 (School Registers), Student QR Badges, and Security Audit Logs. Encapsulated in `app/Libraries/PDF/DomPdfWrapper.php`.

### `phpoffice/phpspreadsheet` (`^5.9`)
* **Purpose:** Excel and CSV parsing and generation.
* **Usage:** Handles bulk student roster uploads, column-header extraction, row validation, and downloading audit reports in `.xlsx` format. Encapsulated in `app/Libraries/Spreadsheet/ExcelSpreadsheetService.php`.

### `laravel/sanctum` (`^4.0`)
* **Purpose:** API Token Authentication.
* **Usage:** Secures stateful and token-based API endpoints (`/api/classroom/*`, `/api/user`).

---

## 2. Frontend Dependencies (`package.json`)

### `vite` (`^8.0.0`) & `laravel-vite-plugin` (`^3.0.0`)
* **Purpose:** Next-generation frontend bundler and asset compiler.
* **Usage:** Compiles CSS and JavaScript assets located in `resources/` with Hot Module Replacement (HMR) during development.

### `laravel-echo` (`^2.3.4`) & `pusher-js` (`^8.5.0`)
* **Purpose:** Client-side WebSocket integration.
* **Usage:** Configured in `resources/js/echo.js` to listen for Reverb broadcast events on attendance and audit channels.

### `bootstrap` (`^5.3.8`)
* **Purpose:** UI Component Library.
* **Usage:** Powers responsive grid systems, modal dialogs, navigation bars, and dropdown menus across all administrative and teacher layouts.

> [!IMPORTANT]
> **Bootstrap 5 is the only CSS framework in use. Tailwind CSS is NOT installed and must NOT be used.**
> Bootstrap 5 spacing utilities use integer steps only: `p-0` through `p-5`, `m-0` through `m-5`, `gap-0` through `gap-5`.
> Fractional classes such as `p-2.5`, `gap-2.5`, `py-0.5`, `mt-1.5` are **Tailwind CSS syntax** and will silently produce zero styling in this project.
> All custom spacing beyond Bootstrap's scale must be written as inline `style=""` attributes or in the appropriate `resources/css/pov/<role>/<page>/<page>.css` file.

### `@fortawesome/fontawesome-free` (`^7.2.0`)
* **Purpose:** Iconography.
* **Usage:** Unified iconography throughout the application sidebars, status badges, action buttons, and dashboard telemetry widgets.

