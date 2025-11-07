#!/bin/bash

# Activar modo mantenimiento
php artisan down

# Actualizar código desde git
git pull origin main

# Instalar dependencias
composer install --optimize-autoloader --no-dev
npm ci
npm run build

# Limpiar y regenerar caché
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Ejecutar migraciones
php artisan migrate --force

# Establecer permisos
chmod -R 755 storage bootstrap/cache

# Desactivar modo mantenimiento
php artisan up

echo "¡Despliegue completado!"