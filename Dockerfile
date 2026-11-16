# ==============================================================
# FASE 1: Construcción de Assets Frontend con Node 20
# ==============================================================
FROM node:20-alpine AS node-builder
WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY . .
RUN npm run build

# ==============================================================
# FASE 2: Instalación de Dependencias PHP con Composer
# ==============================================================
FROM composer:2 AS composer-builder
WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader \
    --ignore-platform-req=ext-gd \
    --ignore-platform-req=ext-zip \
    --ignore-platform-req=ext-intl \
    --ignore-platform-req=ext-bcmath \
    --ignore-platform-req=ext-pdo_pgsql \
    --ignore-platform-req=ext-pgsql \
    --ignore-platform-req=ext-exif \
    --ignore-platform-req=ext-pcntl

# ==============================================================
# FASE 3: Imagen Final de Producción (PHP 8.4 Alpine)
# ==============================================================
FROM php:8.4-cli-alpine

# Variables de entorno por defecto
ENV TZ=America/La_Paz \
    COMPOSER_ALLOW_SUPERUSER=1

# Instalar dependencias del sistema y librerías para extensiones PHP
RUN apk add --no-cache \
    tzdata \
    netcat-openbsd \
    postgresql-client \
    postgresql-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    icu-dev \
    oniguruma-dev \
    bash \
    && cp /usr/share/zoneinfo/${TZ} /etc/localtime \
    && echo "${TZ}" > /etc/timezone \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_pgsql \
        pgsql \
        gd \
        zip \
        bcmath \
        intl \
        opcache \
        exif \
        pcntl \
    && rm -rf /var/cache/apk/*

WORKDIR /var/www/html

# Copiar código del proyecto
COPY . .

# Copiar dependencias de Composer desde stage builder
COPY --from=composer-builder /app/vendor ./vendor

# Copiar assets compilados de Vite desde stage node-builder
COPY --from=node-builder /app/public/build ./public/build

# Permisos para storage y bootstrap/cache
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Configurar script de inicio
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Dokploy escucha internamente en 8000 (sin mapeo directo de host)
EXPOSE 8000

ENTRYPOINT ["docker-entrypoint.sh"]

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]

