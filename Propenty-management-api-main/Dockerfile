# Laravel için PHP + Apache ortamı
FROM php:8.2-apache

# Sistemi güncelle ve gerekli paketleri kur
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpq-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo pdo_pgsql zip

# Composer yükle
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Apache ayarları
COPY . /var/www/html
WORKDIR /var/www/html

RUN chown -R www-data:www-data /var/www/html \
    && a2enmod rewrite

# Laravel bağımlılıklarını yükle
RUN composer install --no-dev --optimize-autoloader \
    && cp .env.example .env || true \
    && php artisan key:generate \
    && php artisan config:cache

# Port
EXPOSE 80

CMD ["apache2-foreground"]
