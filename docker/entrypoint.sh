#!/bin/sh
set -e

# 1. Create .env if it doesn't exist
if [ ! -f /var/www/.env ]; then
    cp /var/www/.env.example /var/www/.env
    echo ".env created from .env.example"
fi

# 2. CLEAR CONFIG ONLY
# We do this first so Artisan knows to connect to 'db' instead of '127.0.0.1'
php artisan config:clear

# 3. Generate the Laravel app key
php artisan key:generate --no-interaction --force

# 4. Wait for MySQL
echo "Waiting for database to be ready..."
until php -r "
try {
    \$p = new PDO('mysql:host=db;dbname=twphc', 'root', '');
    exit(0);
} catch (PDOException \$e) {
    exit(1);
}" 2>/dev/null; do
    echo "Database not ready yet, retrying in 3 seconds..."
    sleep 3
done
echo "Database is ready!"

# 5. RUN MIGRATIONS FIRST
# This creates the 'cache' table so the next commands don't fail
echo "Running migrations..."
php artisan migrate --force --no-interaction

# 6. RUN SEEDERS
echo "Running seeders..."
php artisan db:seed --force --no-interaction

# 7. NOW IT IS SAFE TO CLEAR THE REST
# The tables now exist, so these won't throw 1146 errors
php artisan cache:clear
php artisan view:clear
php artisan route:clear

echo "Application is ready at http://localhost:8000"

# 8. Hand off to the main process
exec "$@"