#!/bin/bash
set -e

echo "=== Starting Production Deployment Initializer ==="

# DIAGNOSTIC: PHP version and extensions
echo "=== DIAGNOSTIC: PHP Environment ==="
php -v
echo "PHP Extensions:"
php -m | grep -E "(pdo|pgsql|mbstring|gd|zip|bcmath|imagick)" || echo "Warning: Some extensions may be missing"

# DIAGNOSTIC: Laravel application info
echo "=== DIAGNOSTIC: Laravel Application ==="
php artisan --version || echo "Warning: Could not get Laravel version"

# DIAGNOSTIC: Laravel environment (safe targeted values only)
echo "=== DIAGNOSTIC: Laravel Environment ==="
if [ -f .env ]; then
    echo "APP_ENV=$(grep '^APP_ENV=' .env | cut -d '=' -f2)"
    echo "APP_DEBUG=$(grep '^APP_DEBUG=' .env | cut -d '=' -f2)"
    APP_URL=$(grep '^APP_URL=' .env | cut -d '=' -f2)
    if [ -n "$APP_URL" ]; then
        # Only show host, not full URL with potential secrets
        echo "APP_URL_HOST=$(echo $APP_URL | sed -E 's|https?://([^/]+).*|\1|')"
    fi
else
    echo "Warning: .env file not found"
fi

# DIAGNOSTIC: Laravel bootstrap test
echo "=== DIAGNOSTIC: Laravel Bootstrap ==="
php -r "require __DIR__.'/vendor/autoload.php'; \$app = require_once __DIR__.'/bootstrap/app.php'; echo 'Laravel bootstrap: SUCCESS';" 2>&1 || echo "Warning: Laravel bootstrap failed"

# DIAGNOSTIC: Directory structure
echo "=== DIAGNOSTIC: Directory Structure ==="
test -d /var/www/html/public && echo "public directory: EXISTS" || echo "public directory: MISSING"
test -d /var/www/html/storage && echo "storage directory: EXISTS" || echo "storage directory: MISSING"
test -d /var/www/html/bootstrap/cache && echo "bootstrap/cache directory: EXISTS" || echo "bootstrap/cache directory: MISSING"

# DIAGNOSTIC: Vite build assets
echo "=== DIAGNOSTIC: Vite Build Assets ==="
test -d /var/www/html/public/build && echo "public/build directory: EXISTS" || echo "public/build directory: MISSING"
test -f /var/www/html/public/build/manifest.json && echo "public/build/manifest.json: EXISTS" || echo "public/build/manifest.json: MISSING"

# DIAGNOSTIC: Apache configuration
echo "=== DIAGNOSTIC: Apache Configuration ==="
apache2 -v
a2query -m rewrite && echo "mod_rewrite: ENABLED" || echo "mod_rewrite: DISABLED"

# 1. Ensure storage directories exist and symlink is created
echo "Ensuring storage directories and symlink..."
mkdir -p storage/framework/sessions \
         storage/framework/views \
         storage/framework/cache \
         storage/logs \
         storage/app/public/qr-codes \
         storage/app/private/imports \
         storage/app/templates/teacher\(pov\)

# Sync/restore template files if missing from storage
if [ -d "resources/templates" ]; then
    echo "Syncing template assets to storage/app/templates..."
    cp -rn resources/templates/* storage/app/templates/ 2>/dev/null || cp -r resources/templates/* storage/app/templates/ 2>/dev/null || true
fi

php artisan storage:link --force || true

# 3. Run database migrations
echo "Running database migrations..."
php artisan migrate --force

# 4. Clear caches (DIAGNOSTIC: temporarily disabling config/route/view caching)
echo "Clearing Laravel caches..."
php artisan optimize:clear

# TEMPORARILY DISABLED FOR DIAGNOSTICS:
# php artisan config:cache
# php artisan route:cache
# php artisan view:cache

# 5. Reset ownership and permissions so www-data (Apache) can write logs, sessions, and views
echo "Setting runtime permissions for www-data..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# 6. Configure Apache for Render port
if [ -n "$PORT" ]; then
    echo "Configuring Apache to listen on Render port: $PORT..."
    sed -i "s/80/$PORT/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf
fi

# 7. Startup complete - Launch Apache directly in foreground
echo "Startup complete. Launching Apache..."
exec apache2-foreground