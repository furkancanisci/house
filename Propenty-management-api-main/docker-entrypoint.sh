#!/bin/bash

set -e  # Script hata alırsa durur, güvenli olur

php artisan config:clear
php artisan cache:clear


echo " Migrationlar çalıştırılıyor..."
if ! php artisan migrate --force; then
  echo "❌ Migration sırasında hata oluştu ama devam ediliyor."
fi


echo "Apache (veya başka bir process) başlatılıyor..."
exec "$@"
