#!/bin/sh
set -e

# Create .env if it doesn't exist yet
if [ ! -f /var/www/.env ]; then
    cp /var/www/.env.example /var/www/.env
    echo ".env created from .env.example"
fi

# Generate the Laravel app key
php artisan key:generate --no-interaction --force

# Wait for MySQL to be fully ready before continuing
echo "Waiting for database to be ready..."
until php -r "new PDO('mysql:host=db;dbname=laravel_assessment', 'laravel', 'secret');" 2>/dev/null; do
    echo "Database not ready yet, retrying in 3 seconds..."
    sleep 3
done
echo "Database is ready!"

# Run migrations to create all tables
php artisan migrate --force --no-interaction

# Seed the database with test accounts and sample products
php artisan db:seed --force --no-interaction

# Clear any cached config or views
php artisan config:clear
php artisan cache:clear
php artisan view:clear

echo "Application is ready at http://localhost:8000"

# Hand off to the main process (php-fpm)
exec "$@"