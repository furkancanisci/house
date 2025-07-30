#!/bin/bash

set -e  # Script hata alırsa durur, güvenli olur


echo " Migrationlar çalıştırılıyor..."
if ! php artisan migrate --force; then
  echo "❌ Migration sırasında hata oluştu ama devam ediliyor."
fi

echo "Seeder çalıştırılıyor..."
php artisan db:seed --force

echo "Apache (veya başka bir process) başlatılıyor..."
exec "$@"
