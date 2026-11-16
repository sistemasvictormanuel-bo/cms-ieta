#!/bin/sh
set -e

echo "==> [IETA Backend] Iniciando contenedor en entorno de producción Dokploy..."

cd /var/www/html

# 1. Asegurar que exista un archivo .env físico para que Artisan y config:cache puedan operar
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
    else
        echo "APP_KEY=" > .env
    fi
fi

# 2. Si APP_KEY no vino de Dokploy o está vacía, generar una nueva y exportarla
if [ -z "$APP_KEY" ]; then
    echo "==> Generando APP_KEY automáticamente..."
    GENERATED_KEY=$(php artisan key:generate --show)
    export APP_KEY="$GENERATED_KEY"

    # Guardarla también en el archivo .env físico
    if grep -q "^APP_KEY=" .env; then
        sed -i "s|^APP_KEY=.*|APP_KEY=$GENERATED_KEY|" .env
    else
        echo "APP_KEY=$GENERATED_KEY" >> .env
    fi
fi

# 3. Limpiar caché previa para asegurar que Laravel lea la APP_KEY exportada en memoria
php artisan config:clear

# 4. Esperar disponibilidad de PostgreSQL si DB_HOST está configurado
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

# 5. Permisos y enlaces de almacenamiento
echo "==> Asegurando estructura y permisos de directorios..."
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# 6. Enlace simbólico de storage público
echo "==> Verificando enlace simbólico de almacenamiento público..."
php artisan storage:link 2>/dev/null || true

# 7. Migraciones automáticas (Dokploy-ready)
echo "==> Ejecutando migraciones de base de datos..."
php artisan migrate --force --no-interaction

# 8. Optimización de caché para producción (con APP_KEY ya validada en memoria y .env)
echo "==> Optimizando rutas, vistas y configuración..."
php artisan config:cache --no-interaction 2>/dev/null || true
php artisan route:cache --no-interaction 2>/dev/null || true
php artisan view:cache --no-interaction 2>/dev/null || true

echo "==> [IETA Backend] Listo. Iniciando servidor web en 0.0.0.0:8000..."
exec "$@"
