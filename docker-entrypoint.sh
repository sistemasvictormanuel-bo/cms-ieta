#!/bin/sh
set -e

echo "==> [IETA Backend] Iniciando contenedor en entorno de producción Dokploy..."

# 1. Esperar disponibilidad de PostgreSQL si DB_HOST está configurado
if [ -n "$DB_HOST" ]; then
    echo "==> Esperando conexión a la base de datos en $DB_HOST:${DB_PORT:-5432}..."
    timeout=60
    while ! nc -z "$DB_HOST" "${DB_PORT:-5432}" 2>/dev/null; do
        timeout=$((timeout - 1))
        if [ "$timeout" -le 0 ]; then
            echo "==> [ADVERTENCIA] Tiempo de espera agotado conectando a PostgreSQL. Continuando de todos modos..."
            break
        fi
        sleep 1
    done
    echo "==> Base de datos disponible."
fi

# 2. Permisos y enlaces de almacenamiento
echo "==> Asegurando estructura y permisos de directorios..."
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# 3. Enlace simbólico de storage público
echo "==> Verificando enlace simbólico de almacenamiento público..."
php artisan storage:link 2>/dev/null || true

# 4. Migraciones automáticas (Dokploy-ready)
echo "==> Ejecutando migraciones de base de datos..."
php artisan migrate --force --no-interaction

# 5. Optimización de caché para producción
echo "==> Optimizando rutas, vistas y configuración..."
php artisan config:cache --no-interaction 2>/dev/null || true
php artisan route:cache --no-interaction 2>/dev/null || true
php artisan view:cache --no-interaction 2>/dev/null || true

echo "==> [IETA Backend] Listo. Iniciando servidor web en 0.0.0.0:8000..."
exec "$@"

