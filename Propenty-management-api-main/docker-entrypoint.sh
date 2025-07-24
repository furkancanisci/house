#!/bin/bash

# Önbellekleri temizle
php artisan config:clear
php artisan cache:clear

# Migration çalıştır ama sadece hata olmuyorsa
php artisan migrate --force || true

# Apache'yi başlat
exec "$@"
