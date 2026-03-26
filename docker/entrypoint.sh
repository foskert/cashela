#!/bin/bash
set -e

# Esperar a que los servicios (MySQL/RabbitMQ) estén listos
echo "⏳ Esperando a que los servicios inicien..."
sleep 5

echo "📦 Instalando dependencias..."
composer install --no-interaction --prefer-dist

echo "🔑 Generando llaves y permisos..."
php artisan key:generate --force
chmod -R 777 storage bootstrap/cache

echo "🗄️ Ejecutando migraciones y seeds..."
# --force es necesario cuando corres migraciones en contenedores
php artisan migrate:fresh --seed --force

echo "📖 Generando documentación Swagger..."
php artisan l5-swagger:generate

echo "✅ Sistema Cashela listo. Iniciando servidor..."

# Esto permite que el CMD del Dockerfile (php artisan serve) se ejecute
exec "$@"
