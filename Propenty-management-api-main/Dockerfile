# Laravel için basit ve hızlı Dockerfile
FROM php:8.2-fpm

# Sistem bağımlılıkları
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpq-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring zip

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Uygulama dosyalarını kopyala
WORKDIR /var/www
COPY . .

# Laravel permission fix
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage

# Bağımlılıkları yükle
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Laravel key generate (ilk deploydan sonra çalışacak)
CMD php artisan config:clear && php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8080
