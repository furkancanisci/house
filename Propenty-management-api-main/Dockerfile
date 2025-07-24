FROM php:8.2-fpm

# Sistem paketleri
RUN apt-get update && apt-get install -y \
    git unzip curl libpq-dev zip \
    && docker-php-ext-install pdo pdo_pgsql

# Composer yükle
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Uygulama dosyaları
WORKDIR /var/www
COPY . .

# Laravel bağımlılıkları
RUN composer install --no-dev --optimize-autoloader

# .env ayarları vs.
EXPOSE 8000

# Laravel’i çalıştır
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8000
