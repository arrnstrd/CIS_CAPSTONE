#!/bin/bash
set -e

echo "=== Starting Production Deployment Initializer ==="

# 1. Render Port configuration
# Render binds dynamically to the port in $PORT (defaults to 10000 or custom)
if [ -n "$PORT" ]; then
    echo "Configuring Apache to listen on Render port: $PORT..."
    sed -i "s/80/$PORT/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf
fi

# 2. Ensure storage directories exist and symlink is created
echo "Ensuring storage directories and symlink..."
mkdir -p storage/framework/sessions \
         storage/framework/views \
         storage/framework/cache \
         storage/logs \
         storage/app/public

php artisan storage:link --force || true

# 3. Run database migrations
echo "Running database migrations..."
php artisan migrate --force

# 4. Clear and rebuild production caches
echo "Warming production caches..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Reset ownership and permissions so www-data (Apache) can write logs, sessions, and views
echo "Setting runtime permissions for www-data..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# 6. Start Apache web server
echo "Starting Apache web server..."
exec apache2-foreground