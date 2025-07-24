FROM php:8.2-apache

# Gerekli paketleri yükle
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpq-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libjpeg-dev \
    libpng-dev \
    libexif-dev \
    && docker-php-ext-install pdo pdo_pgsql zip exif

# Apache rewrite modu
RUN a2enmod rewrite

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Kodları kopyala
COPY . /var/www/html
WORKDIR /var/www/html

# Laravel için izinler
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Composer install
RUN composer install --no-dev --optimize-autoloader \
    && php artisan config:clear \
    && php artisan key:generate || true \
    && php artisan config:cache

EXPOSE 80

CMD ["apache2-foreground"]
