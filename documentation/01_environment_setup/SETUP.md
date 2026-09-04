# Environment Setup Guide

This guide details the local development setup, runtime prerequisites, and execution commands for the **CIS Capstone** application.

---

## 1. System Requirements

The following runtimes and tools must be installed on your local host:

| Tool | Verified Version | Minimum Requirement | Notes |
|---|---|---|---|
| **PHP** | `8.5.10` (CLI) | `^8.3` | Must have extensions: `pdo_pgsql`, `pdo_mysql`, `mbstring`, `gd` or `imagick` (for QR codes), `zip`, `xml` |
| **Composer** | `2.x` | `^2.2` | Dependency management for PHP packages |
| **Node.js** | `v26.8.1` | `>= 20.x` | JavaScript runtime for Vite and frontend bundling |
| **NPM** | `12.0.2` | `>= 10.x` | Package manager for frontend packages |
| **PostgreSQL** | `16.x` | `>= 14.x` | Default database engine (`pgsql`). SQLite or MySQL can also be used for testing. |

---

## 2. Step-by-Step Local Setup

### Step 1: Clone and Install Dependencies

```bash
# Install PHP Composer dependencies
composer install

# Install Node frontend dependencies
npm install --ignore-scripts
```

### Step 2: Environment File Configuration

Copy `.env.example` to create your local `.env`:

```bash
cp .env.example .env
php artisan key:generate
```

Review and adjust the database credentials in `.env`:

```env
APP_NAME="Concepcion Integrated School"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=cis_capstone
DB_USERNAME=postgres
DB_PASSWORD=your_local_password
```

*(Refer to [`ENVIRONMENT_VARIABLES.md`](./ENVIRONMENT_VARIABLES.md) for full parameter definitions).*

### Step 3: Run Database Migrations & Seeders

```bash
# Run database schema migrations
php artisan migrate

# Seed initial roles, admin, teacher, and assessment categories
php artisan db:seed
```

> [!NOTE]
> To reset and re-seed the local database cleanly, run:
> ```bash
> php artisan migrate:fresh --seed
> ```

---

## 3. Running the Development Environment

The project includes an integrated `composer dev` script configured via `npx concurrently` to execute all necessary runtime processes in parallel:

```bash
composer run dev
```

This single command boots 4 simultaneous services:
1. **HTTP Server (`server`):** `php artisan serve` (Listening on `http://127.0.0.1:8000`)
2. **Background Queue (`queue`):** `php artisan queue:listen --tries=1 --timeout=0` (Processes email dispatch & bulk imports)
3. **Log Monitor (`logs`):** `php artisan pail --timeout=0` (Real-time tailing of application log entries)
4. **Asset Bundler (`vite`):** `npm run dev` (Vite HMR on `http://localhost:5173`)

### Real-Time WebSocket Server (Laravel Reverb)

For live QR scan updates and table broadcasting:

```bash
php artisan reverb:start
```

---

## 4. Running Automated Tests

```bash
# Run unit & feature tests with cleared config
composer test

# Target specific test suites
php artisan test tests/Feature/PrivilegedAccessControlTest.php
php artisan test tests/Unit/GradingServiceCalculationTest.php
```
