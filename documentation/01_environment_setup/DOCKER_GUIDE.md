# Docker Containerization Guide

The repository includes Docker and Docker Compose configurations supporting containerized development and production deployments.

---

## 1. Directory Structure

```text
docker/
├── 8.0/ - 8.5/            # PHP runtime images with supervisor & container scripts
├── nginx/default.conf     # Nginx reverse proxy configuration
├── mariadb/               # MariaDB initialization scripts
├── mysql/                 # MySQL initialization scripts
├── pgsql/                 # PostgreSQL initialization scripts
└── php/                   # Dedicated production PHP build context
docker-compose.yml         # Development multi-container orchestration
Dockerfile.prod            # Multi-stage production container image
```

---

## 2. Docker Compose (Local Development)

The local environment can be spun up using Docker Compose:

```bash
# Start all containers in detached mode
docker compose up -d

# Check running services
docker compose ps
```

### Services Defined:
* **`laravel.test`**: PHP application container running the selected PHP version (defaults to PHP 8.3/8.4/8.5 with standard extensions).
* **`pgsql` / `mariadb`**: Local database instances.
* **`reverb`**: Real-time websocket broadcasting server.

### Running Artisan Inside Docker

```bash
docker compose exec laravel.test php artisan migrate --seed
docker compose exec laravel.test composer test
```

---

## 3. Production Containerization (`Dockerfile.prod`)

The production Dockerfile is configured for enterprise container deployment:
* Multi-stage build copying optimized Composer autoloader files.
* Production PHP-FPM configuration.
* Assets compiled ahead-of-time via Vite.
* Health check endpoint integration against `/up`.
