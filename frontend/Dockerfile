# ==============================================================================
# Dockerfile - ieta-frontend
# Arquitectura Multi-Stage: Node 22 (Build) + Nginx Alpine (Producción)
# Optimizado para Dokploy & Traefik (Carrera de Estadística - UMSA)
# ==============================================================================

# --- Etapa 1: Compilación de la aplicación Vue 3 ---
FROM node:22-alpine AS builder

WORKDIR /app

# Inyección de variable de entorno requerida por Vite en build time
ARG VITE_API_URL
ENV VITE_API_URL=${VITE_API_URL}

# Copia de manifiestos de dependencias
COPY package.json package-lock.json ./

# Instalación limpia y determinista de dependencias
RUN npm ci

# Copia de todo el código fuente
COPY . .

# Compilación y verificación de tipos (TypeScript + Vite)
RUN npm run build

# --- Etapa 2: Servidor Web Estático Ultraligero ---
FROM nginx:alpine AS runner

# Metadatos del contenedor
LABEL maintainer="Carrera de Estadística - UMSA"
LABEL project="IETA Frontend"

# Configuración personalizada de Nginx para SPA (HTML5 History Mode)
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Copia de los archivos estáticos generados en la etapa de build
COPY --from=builder /app/dist /usr/share/nginx/html

# Exposición del puerto interno 80 (consumido por Traefik en Dokploy)
EXPOSE 80

# Comando de inicio del servidor Nginx en primer plano
CMD ["nginx", "-g", "daemon off;"]
