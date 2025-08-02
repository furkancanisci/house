#!/bin/bash
set -e

# Apache gets grumpy about PID files pre-existing
rm -f /var/run/apache2/apache2.pid

# Set proper permissions
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# Run database migrations
if [ -f /var/www/html/artisan ]; then
    cd /var/www/html
    php artisan config:clear
    php artisan cache:clear
    php artisan view:clear
    php artisan migrate --force
    php artisan storage:link
    composer dump-autoload
    composer clear-cache
    composer update --no-scripts
    composer install --no-dev --optimize-autoloader
fi

# Start Apache in the foreground
exec apache2-foreground
