#!/bin/bash
# Mag-run ng database migrations
echo "Running migrations..."
php artisan migrate --force

# I-clear lahat ng caches (config, route, view) para sumunod sa bago
echo "Clearing caches..."
php artisan optimize:clear

# I-start ang Apache web server
echo "Starting Apache..."
apache2-foreground