FROM php:8.2-fpm

# Sistem araçları ve PostgreSQL + EXIF desteği
RUN apt-get update && apt-get install -y \
    zip unzip curl libpq-dev git \
    && docker-php-ext-install pdo pdo_pgsql exif

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Uygulama dizini
WORKDIR /var/www
COPY . .

# Laravel bağımlılıkları
RUN composer install --no-dev --optimize-autoloader

# Port aç
EXPOSE 8000

# Laravel'i ayağa kaldır
CMD php artisan key:generate && php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8000
