# Temel PHP-Apache imajı
FROM php:8.2-apache

# Sistem bağımlılıkları
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

# Apache için rewrite modu
RUN a2enmod rewrite

# Composer yükle
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Laravel projesini kopyala
COPY . /var/www/html
WORKDIR /var/www/html

# Apache ayarları (public dizinine yönlendirme)
RUN echo '<Directory /var/www/html/public>\n\
    AllowOverride All\n\
</Directory>\n\
\n\
ServerName localhost' > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel

# İzinler ve composer kurulumu
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache \
    && composer install --no-dev --optimize-autoloader || true

# .env dosyasını dokunma, sadece key ve cache işle
RUN php artisan key:generate || true \
    && php artisan config:cache || true


EXPOSE 80

# Migration'ları konteyner başlatıldıktan sonra çalıştır (RUN yerine entrypoint ile)
ENTRYPOINT ["/var/www/html/docker-entrypoint.sh"]

CMD ["apache2-foreground"]
