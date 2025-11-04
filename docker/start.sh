#!/bin/bash
set -e

# Run migrations if needed (uncomment if you want migrations to run on container start)
# cd /var/www/html
# php artisan migrate --force

# Start supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
