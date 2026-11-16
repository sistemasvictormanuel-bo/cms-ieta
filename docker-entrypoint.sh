#!/bin/sh
set -e

echo "==> [IETA Backend] Iniciando contenedor en entorno de producción Dokploy..."

cd /var/www/html

# 0. Si APP_KEY vino vacía desde Docker/Dokploy, eliminarla de la memoria para que no anule al .env
if [ -z "$APP_KEY" ]; then
    unset APP_KEY
fi

# 1. Asegurar que exista un archivo .env físico para que Artisan y config:cache puedan operar
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
    else
        touch .env
    fi
fi

# 2. Si APP_KEY no está en memoria y tampoco en el .env, generarla en el archivo .env
if [ -z "$APP_KEY" ] && ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
    echo "==> Generando APP_KEY automáticamente en .env..."
    php artisan key:generate --force --no-interaction
fi

# 3. Asegurar que la APP_KEY de .env quede exportada en el proceso en memoria
if [ -z "$APP_KEY" ]; then
    export APP_KEY=$(grep "^APP_KEY=" .env 2>/dev/null | head -n 1 | cut -d '=' -f2-)
fi

# 4. Limpiar caché previa para asegurar que Laravel reconozca la APP_KEY activa
php artisan config:clear

# 5. Esperar disponibilidad de PostgreSQL si DB_HOST está configurado
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

# 6. Permisos y enlaces de almacenamiento
echo "==> Asegurando estructura y permisos de directorios..."
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# 7. Enlace simbólico de storage público
echo "==> Verificando enlace simbólico de almacenamiento público..."
php artisan storage:link 2>/dev/null || true

# 8. Migraciones automáticas (Dokploy-ready)
echo "==> Ejecutando migraciones de base de datos..."
php artisan migrate --force --no-interaction

# 9. Optimización de caché para producción (con APP_KEY ya validada en memoria y .env)
echo "==> Optimizando rutas, vistas y configuración..."
php artisan config:cache --no-interaction 2>/dev/null || true
php artisan route:cache --no-interaction 2>/dev/null || true
php artisan view:cache --no-interaction 2>/dev/null || true

echo "==> [IETA Backend] Listo. Iniciando servidor web en 0.0.0.0:8000..."
exec "$@"
