FROM php:8.2-fpm

# Sistem bağımlılıkları ve PHP uzantıları
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpq-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring zip exif

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .

# Laravel izinleri
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage

# Laravel bağımlılıkları
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Başlatma komutu
CMD php artisan config:clear && php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8080
