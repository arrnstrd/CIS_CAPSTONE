# Concepcion Integrated School (CIS) Management System

An enterprise school management platform featuring self-service QR gate attendance kiosks, classroom teacher verification, official DepEd SF1 bulk student imports, academic setup, and grading analytics.

---

## 📚 System Documentation Hub (`documentation/`)


```
documentation/
├── 01_environment_setup/      # Prerequisites (PHP, Node, PostgreSQL), .env definitions, Docker deployment
├── 02_codebase_context/       # Request lifecycle, architectural layers, directory responsibilities, core libraries
├── 03_auth_demo_access/       # Role permissions matrix, seed test accounts, token invitation onboarding
├── 04_business_logic/         # QR gate state machine, classroom verification, DepEd SF1 bulk import, grading
├── 05_point_of_views/         # Role breakdown mapped to UI sidebars: Super Admin, School Admin, Teacher, Scanner Operator
├── 06_session_logs/           # Chronological architectural refactor records and system modification history
├── 07_technical_reference/    # Database schema rules, routing architecture, service contracts, coding standards
└── README.md                  # Central index and source of truth declaration for all internal documentation
```

### Documentation Directory Guide

| Section | Description | Key Reference |
|---|---|---|
| [**`01_environment_setup/`**](documentation/01_environment_setup/SETUP.md) | Local development setup, Docker containerization, and environment variables. | [`SETUP.md`](documentation/01_environment_setup/SETUP.md), [`DOCKER_GUIDE.md`](documentation/01_environment_setup/DOCKER_GUIDE.md) |
| [**`02_codebase_context/`**](documentation/02_codebase_context/SYSTEM_OVERVIEW.md) | Request-to-response lifecycles, service layer patterns, and package dependencies. | [`SYSTEM_OVERVIEW.md`](documentation/02_codebase_context/SYSTEM_OVERVIEW.md), [`DIRECTORY_RESPONSIBILITIES.md`](documentation/02_codebase_context/DIRECTORY_RESPONSIBILITIES.md) |
| [**`03_auth_demo_access/`**](documentation/03_auth_demo_access/ROLE_PERMISSIONS_MATRIX.md) | Access control tiers, guard middleware (`EnsureUserHasRole`), and default seed credentials. | [`ROLE_PERMISSIONS_MATRIX.md`](documentation/03_auth_demo_access/ROLE_PERMISSIONS_MATRIX.md), [`SEED_ACCOUNTS.md`](documentation/03_auth_demo_access/SEED_ACCOUNTS.md) |
| [**`04_business_logic/`**](documentation/04_business_logic/QR_STATION_STATE_MACHINE.md) | Deep dives into the core domain engines and business rules. | [`QR_STATION_STATE_MACHINE.md`](documentation/04_business_logic/QR_STATION_STATE_MACHINE.md), [`STUDENT_MANAGEMENT_IMPORT.md`](documentation/04_business_logic/STUDENT_MANAGEMENT_IMPORT.md) |
| [**`05_point_of_views/`**](documentation/05_point_of_views/SCHOOL_ADMIN_POV.md) | Functional matrix and view routes tailored per actor persona. | [`SUPER_ADMIN_POV.md`](documentation/05_point_of_views/SUPER_ADMIN_POV.md), [`SCHOOL_ADMIN_POV.md`](documentation/05_point_of_views/SCHOOL_ADMIN_POV.md), [`TEACHER_POV.md`](documentation/05_point_of_views/TEACHER_POV.md) |
| [**`06_session_logs/`**](documentation/06_session_logs/) | Historical records of code migrations, feature additions, and architectural decisions. | [`SESSION_TYPE_TASK_CONTEXT.md`](documentation/06_session_logs/SESSION_TYPE_TASK_CONTEXT.md) |
| [**`07_technical_reference/`**](documentation/07_technical_reference/) | Coding standards, routing architecture, and database conventions. | [`TECHNICAL_REFERENCE.md`](documentation/07_technical_reference/TECHNICAL_REFERENCE.md) |

---

## ⚡ Key Platform Capabilities

1. **Self-Service QR Gate Station Kiosk**
   - High-throughput scanning with real-time feedback audio, gate pass generation, and cooldown deduplication.
   - WebSocket broadcast (`laravel/reverb`) to operator monitoring feeds.
2. **Classroom Attendance & Verification**
   - Teachers verify daily room attendance for their assigned sections.
   - Dual-status tracking (Campus Gate In/Out vs. In-Room Present/Absent/Late).
3. **DepEd School Form 1 (SF1) Bulk Import Engine**
   - Ingests official DepEd SF1 Excel spreadsheets via `phpoffice/phpspreadsheet`.
   - Comprehensive multi-phase validation (header verification, duplicate LRN detection, automatic section assignment/creation).
4. **Trimester Grading Engine & At-Risk Analytics**
   - DepEd Order 8, s. 2015 compliant computation with Written Work, Performance Tasks, and Term Assessments.
   - Early warning indicators flagging students at academic risk.
5. **Role-Based Point of Views (POVs)**
   - Strict segmentation across 4 roles: **Super Admin**, **School Admin**, **Teacher**, and **Scanner Operator**.

---

## 🛠️ Technology Stack

- **Backend:** PHP 8.3+, Laravel 13.x
- **Database:** PostgreSQL 16+ (default), compatible with MySQL/SQLite
- **Frontend:** Blade, Vanilla CSS design tokens, Bootstrap 5, FontAwesome, Vite
- **Real-Time WebSockets:** Laravel Reverb
- **Excel & PDF:** `phpoffice/phpspreadsheet`, `barryvdh/laravel-dompdf`
- **QR Code Generation:** `simplesoftwareio/simple-qrcode`

---

## 🚀 Quick Start Guide

### 1. Prerequisites

Ensure your system has the required tools installed:
- PHP >= 8.3 with `pdo_pgsql`, `mbstring`, `gd` (or `imagick`), `zip`, `xml`
- Composer 2.x
- Node.js >= 20.x & npm
- PostgreSQL >= 14.x

### 2. Installation & Configuration

```bash
# Clone the repository and enter directory
git clone <repo-url>
cd CIS_CAPSTONE

# Install PHP and Node dependencies
composer install
npm install

# Setup environment configuration
cp .env.example .env
php artisan key:generate
```

Configure your `.env` database connection:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=database_name
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

### 3. Run Migrations & Seeders

```bash
php artisan migrate --seed
```

*(See [`SEED_ACCOUNTS.md`](documentation/03_auth_demo_access/SEED_ACCOUNTS.md) for default user accounts and passwords across each role).*

### 4. Run Development Environment

```bash
# Option A: Run all dev services simultaneously (HTTP, Vite, Queue, Reverb)
composer dev

# Option B: Run standard Artisan and Vite servers
php artisan serve
npm run dev
```

---

## 🐳 Docker Deployment

For containerized deployment, a production Dockerfile and Compose setup are provided:

```bash
# Build and run containers
docker compose up -d --build

# Run startup migrations and cache optimization
./start-prod.sh
```

Refer to the [**Docker Deployment Guide**](documentation/01_environment_setup/DOCKER_GUIDE.md) for production container configuration.

---

## 📄 License

This software is developed for Concepcion Integrated School (CIS). All rights reserved.
