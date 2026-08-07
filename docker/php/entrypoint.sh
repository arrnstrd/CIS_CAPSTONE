#!/bin/sh

set -e

cd /var/www

echo "=================================="
echo "Starting CIS Capstone..."
echo "=================================="

# Wait until project files are mounted
while [ ! -f composer.json ]; do
    echo "Waiting for project files..."
    sleep 1
done

# Create .env if it doesn't exist
if [ ! -f .env ]; then
    echo "Creating .env from .env.example..."
    cp .env.example .env
fi

# Install Composer dependencies
if [ ! -f vendor/autoload.php ]; then
    echo "Installing Composer dependencies..."
    composer install --no-interaction --prefer-dist
fi

# Generate APP_KEY only if missing
if grep -q "^APP_KEY=$" .env || ! grep -q "^APP_KEY=base64:" .env; then
    echo "Generating APP_KEY..."
    php artisan key:generate --force
fi

# Create storage link
if [ ! -L public/storage ]; then
    php artisan storage:link || true
fi

echo "=================================="
echo "PHP-FPM Started"
echo "=================================="

exec php-fpm