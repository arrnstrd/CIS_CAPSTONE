#!/bin/bash
# Mag-run ng database migrations
echo "Running migrations..."
php artisan migrate --force

# I-start ang Apache web server
echo "Starting Apache..."
apache2-foreground