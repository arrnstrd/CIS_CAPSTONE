#!/bin/sh
set -e

cd /var/www

echo "=================================="
echo "Starting CIS Capstone..."
echo "=================================="

# Get host user ID and group ID, default to 1000 if not provided
HOST_UID=${HOST_UID:-1000}
HOST_GID=${HOST_GID:-1000}

# 1. Modify the www-data user to match your host computer's UID/GID
echo "Configuring www-data to match Host UID: $HOST_UID..."
usermod -u $HOST_UID www-data
groupmod -g $HOST_GID www-data

# Wait until project files are mounted
while [ ! -f composer.json ]; do
    echo "Waiting for project files..."
    sleep 1
done

# 2. Fix ownership of application files so www-data (you) can edit them
echo "Fixing file permissions..."
chown -R www-data:www-data /var/www

# Create .env if it doesn't exist
if [ ! -f .env ]; then
    echo "Creating .env from .env.example..."
    # Run as www-data so the file isn't owned by root
    gosu www-data cp .env.example .env
fi

# Install Composer dependencies
if [ ! -f vendor/autoload.php ]; then
    echo "Installing Composer dependencies..."
    gosu www-data composer install --no-interaction --prefer-dist
fi

# Generate APP_KEY only if missing
if grep -q "^APP_KEY=$" .env || ! grep -q "^APP_KEY=base64:" .env; then
    echo "Generating APP_KEY..."
    gosu www-data php artisan key:generate --force
fi

# Create storage link
if [ ! -L public/storage ]; then
    gosu www-data php artisan storage:link || true
fi

echo "=================================="
echo "PHP-FPM Started"
echo "=================================="

# 3. Start PHP-FPM as www-data (dropping root privileges)
exec gosu www-data php-fpm