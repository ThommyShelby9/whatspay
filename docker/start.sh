#!/bin/bash
set -e

cd /var/www/html

# Fix permissions à chaque démarrage (critique pour sessions et cache)
echo "Fixing Laravel permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Clear caches pour la production
echo "Clearing caches..."
php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Rebuild caches pour la performance
echo "Rebuilding caches..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Run migrations (décommenter si besoin)
# echo "Running migrations..."
# php artisan migrate --force

echo "Starting services..."
# Start supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
