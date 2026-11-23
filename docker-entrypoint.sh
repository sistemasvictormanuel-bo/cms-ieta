#!/bin/sh
set -e

echo "==> [IETA Backend] Iniciando contenedor en entorno de producción Dokploy..."

cd /var/www/html

# 1. Asegurar estructura y permisos de directorios antes de guardar la clave persistente
echo "==> Asegurando estructura y permisos de directorios..."
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs storage/app/public bootstrap/cache
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# 2. Resolver APP_KEY de forma persistente (Dokploy -> Archivo persistente en storage -> Generar nueva)
KEY_FILE="/var/www/html/storage/app/public/.app_key"
if [ -z "$APP_KEY" ]; then
    if [ -f "$KEY_FILE" ] && grep -q "^base64:" "$KEY_FILE" 2>/dev/null; then
        APP_KEY=$(cat "$KEY_FILE")
        echo "==> APP_KEY recuperada desde almacenamiento persistente."
    else
        echo "==> Generando nueva APP_KEY automáticamente..."
        APP_KEY=$(php artisan key:generate --show --no-interaction)
        echo "$APP_KEY" > "$KEY_FILE"
        chmod 600 "$KEY_FILE" 2>/dev/null || true
    fi
fi
export APP_KEY

# 3. Asegurar archivo .env físico con la APP_KEY real para comandos Artisan
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
    else
        touch .env
    fi
fi

if grep -q "^APP_KEY=" .env; then
    sed -i "s|^APP_KEY=.*|APP_KEY=$APP_KEY|" .env
else
    echo "APP_KEY=$APP_KEY" >> .env
fi

# 4. Limpiar caché previa para asegurar que Laravel reconozca la APP_KEY activa
php artisan config:clear

# 5. Esperar disponibilidad de PostgreSQL
DB_HOST_TARGET="${DB_HOST:-db}"
DB_PORT_TARGET="${DB_PORT:-5432}"
echo "==> Esperando conexión a la base de datos en $DB_HOST_TARGET:$DB_PORT_TARGET..."
timeout=60
while ! nc -z "$DB_HOST_TARGET" "$DB_PORT_TARGET" 2>/dev/null; do
    timeout=$((timeout - 1))
    if [ "$timeout" -le 0 ]; then
        echo "==> [ADVERTENCIA] Tiempo de espera agotado conectando a PostgreSQL. Continuando de todos modos..."
        break
    fi
    sleep 1
done
echo "==> Base de datos disponible."

# 6. Sincronizar almacenamiento público y enlace simbólico
echo "==> Asegurando archivos físicos de seeders en almacenamiento público persistente..."
mkdir -p storage/app/public
if [ -d "/var/www/html/database/seeders/data/storage" ]; then
    cp -rn /var/www/html/database/seeders/data/storage/* /var/www/html/storage/app/public/ 2>/dev/null || cp -r /var/www/html/database/seeders/data/storage/* /var/www/html/storage/app/public/ 2>/dev/null || true
fi
chmod -R 775 storage/app/public 2>/dev/null || true

echo "==> Verificando enlace simbólico de almacenamiento público..."
if [ ! -L public/storage ]; then
    rm -rf public/storage
    php artisan storage:link --force 2>/dev/null || true
fi

# 7. Migraciones automáticas
echo "==> Ejecutando migraciones de base de datos..."
php artisan migrate --force --no-interaction

# 8. Sincronización y actualización de Seeders (idempotentes mediante updateOrCreate)
echo "==> Sincronizando y ejecutando Seeders institucionales..."
php artisan db:seed --force --no-interaction

# 9. Optimización de caché para producción
echo "==> Optimizando rutas, vistas y configuración..."
php artisan config:cache --no-interaction 2>/dev/null || true
php artisan route:cache --no-interaction 2>/dev/null || true
php artisan view:cache --no-interaction 2>/dev/null || true

echo "==> [IETA Backend] Listo. Iniciando servidor web en 0.0.0.0:8000..."
exec env APP_KEY="$APP_KEY" "$@"
