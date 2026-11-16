# Sistema IETA — Instituto de Estadística Teórica y Aplicada
### Carrera de Estadística — Universidad Mayor de San Andrés (UMSA)

Repositorio oficial unificado del sistema integral del **Instituto de Estadística Teórica y Aplicada (IETA)** y la **Revista Científica Varianza**. Este repositorio agrupa en una arquitectura completa el **Portal Web Público** (Vue 3 + Vite + TypeScript) y el **Backend & Panel Administrativo** (Laravel 12 + Inertia.js + PostgreSQL 16), 100% adaptado a los lineamientos de despliegue en el servidor oficial de la Carrera con **Dokploy** y **Traefik**.

---

## 🏛️ Arquitectura del Repositorio

El proyecto utiliza un enfoque de repositorio unificado listo para Dokploy:

```text
ieta/
├── frontend/             # Portal Web Público (Vue 3, TypeScript, Pinia, Vue Router, GSAP, Nginx Alpine)
│   ├── src/              # Código fuente de las vistas y componentes públicos
│   ├── nginx.conf        # Nginx configurado con History Mode y Reverse Proxy (/api, /storage, /admin)
│   ├── Dockerfile        # Imagen multi-stage: Node 22 (Build) + Nginx Alpine (Runner)
│   └── package.json
├── app/                  # Backend Laravel 12 (Modelos, Controladores, Servicios, Reglas de Negocio)
├── database/             # Migraciones PostgreSQL, Seeders y volcados JSON
├── resources/            # Panel Administrativo CMS (Inertia.js + Vue 3 + Tailwind CSS v4)
├── routes/               # Rutas web (/admin) y endpoints de la API REST (/api)
├── docker-compose.yml    # Orquestación de servicios Dokploy: frontend (80) + app (8000) + db (Postgres)
├── Dockerfile            # Imagen multi-stage para Backend/Admin: Node 20 (Vite) + PHP 8.4 Alpine
├── docker-entrypoint.sh  # Script de inicio automatizado (migraciones, storage:link, optimización)
├── .env.example          # Plantilla de variables de entorno de producción
└── README.md
```

---

## 🚀 1. Stack Tecnológico

### Backend & Panel Administrativo
- **Lenguaje:** [PHP 8.4](https://www.php.net/)
- **Framework:** [Laravel 12](https://laravel.com/)
- **Panel Administrativo SPA:** [Vue 3](https://vuejs.org/) con [Inertia.js v2](https://inertiajs.com/)
- **Diseño & Estilos:** [Tailwind CSS v4](https://tailwindcss.com/)
- **Base de Datos:** [PostgreSQL 16](https://www.postgresql.org/)
- **Seguridad & Acceso:** Spatie Laravel Permission (RBAC con roles `administrador` y `autor`)
- **Gestión Multimedia:** Spatie Media Library

### Portal Web Público (`frontend/`)
- **Framework:** [Vue.js 3](https://vuejs.org/) (Composition API, `<script setup>`)
- **Lenguaje:** [TypeScript 5+](https://www.typescriptlang.org/)
- **Enrutamiento:** [Vue Router 4](https://router.vuejs.org/) (HTML5 History Mode)
- **Gestión de Estado:** [Pinia](https://pinia.vuejs.org/)
- **Cliente HTTP:** [Axios](https://axios-http.com/) (conectado a `/api`)
- **Internacionalización:** [Vue I18n 11](https://vue-i18n.intlify.dev/) (Español / Inglés)
- **Animaciones:** [GSAP 3](https://gsap.com/)
- **Servidor Web:** Nginx Alpine optimizado para SPA

---

## 📋 2. Requisitos Previos para Desarrollo Local

- **PHP:** Versión `>= 8.4` con extensiones `pdo_pgsql`, `pgsql`, `gd`, `zip`, `bcmath`, `intl`.
- **Composer:** Versión `>= 2.2`.
- **Node.js:** Versión `>= 20.x` (LTS recomendada) y **NPM** `>= 10.x`.
- **Base de Datos:** PostgreSQL `>= 15` corriendo en puerto `5432`.
- **Docker & Docker Compose:** Para pruebas de empaquetado de producción.

---

## 💻 3. Instrucciones de Instalación y Ejecución Local

### Paso 1: Clonar el Repositorio
```bash
git clone https://github.com/sociest/ieta.git
cd ieta
```

### Paso 2: Configurar el Entorno del Backend
```bash
cp .env.example .env
```
*Edite el archivo `.env` configurando los accesos a su base de datos PostgreSQL local (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).*

### Paso 3: Instalar Dependencias del Backend y Admin
```bash
composer install
php artisan key:generate
php artisan migrate --force
php artisan db:seed
php artisan storage:link
npm install
npm run build
```
*`php artisan db:seed` carga automáticamente roles, permisos, las 24 ediciones de la Revista Varianza, 20 artículos indexados, 149 memorias de investigación, 79 datasets del banco de datos, personal institucional y el diccionario de traducciones bilingües.*

### Paso 4: Instalar Dependencias del Frontend Público
```bash
cd frontend
cp .env.example .env
npm install
cd ..
```

### Paso 5: Iniciar los Servidores Locales
- **Backend & Admin (API + Panel):**
  ```bash
  php artisan serve
  ```
  *(Disponible en `http://127.0.0.1:8000`, Panel Admin en `http://127.0.0.1:8000/admin`)*
- **Frontend Público:**
  ```bash
  cd frontend
  npm run dev
  ```
  *(Disponible en `http://localhost:5173`, consumiendo la API de `http://localhost:8000/api`)*

---

## 🔑 4. Variables de Entorno Principales

Consulte `.env.example` para la lista completa. Las variables críticas son:

| Variable | Descripción | Valor por Defecto / Ejemplo |
| :--- | :--- | :--- |
| `APP_ENV` | Entorno de ejecución | `production` (en servidor) / `local` |
| `APP_KEY` | Llave simétrica de cifrado | Generada con `php artisan key:generate` |
| `APP_URL` | URL canónica del sistema | `https://ieta.estadistica.umsa.bo` |
| `TZ` | Zona horaria oficial de Bolivia | `America/La_Paz` (**Obligatoria**) |
| `DB_CONNECTION` | Motor de base de datos | `pgsql` |
| `DB_HOST` | Host de PostgreSQL | `db` (en Docker) / `127.0.0.1` (local) |
| `DB_PORT` | Puerto de Postgres | `5432` |
| `DB_DATABASE` | Nombre de base de datos | `db_ieta_2026` |
| `DB_USERNAME` | Usuario de base de datos | `postgres` |
| `DB_PASSWORD` | Contraseña de base de datos | *(Configurar clave segura)* |
| `VITE_API_URL` | URL base de la API para el frontend | `/api` |
| `FILESYSTEM_DISK` | Disco de archivos | `public` |
| `MAIL_*` | Credenciales SMTP externas | Configuración de envío de correos |

---

## 🌐 5. Módulo de Traducciones y API Bilingüe (ES / EN)

El sistema provee una API RESTful pública lista para alimentar el switch de idiomas del sitio web:

- **Diccionario completo según idioma:**
  ```http
  GET /api/v1/traducciones?lang=en
  GET /api/v1/traducciones?lang=es
  ```
- **Diccionario por módulo/sección:**
  ```http
  GET /api/v1/traducciones/navegacion?lang=en
  GET /api/v1/traducciones/hero?lang=es
  GET /api/v1/traducciones/revista?lang=en
  GET /api/v1/traducciones/banco_datos?lang=es
  ```
- **Artículos Científicos:** Cada registro dispone de resumen en español (`art_resumen`) y abstracto científico en inglés (`art_abstracto`).

---

## 🐳 6. Despliegue en Dokploy (Servidor Carrera de Estadística)

El repositorio está 100% adaptado a las especificaciones oficiales de **Dokploy** y **Traefik**:

1. **Sin publicación directa de puertos al host:** No se exponen puertos físicos (`ports: - "8080:80"` eliminado). Se utiliza `expose`.
2. **Servicios Orquestados (`docker-compose.yml`):**
   - **`frontend`:** Expone el puerto interno `80` (Nginx Alpine). Sirve el portal público y redirige llamadas de `/api/`, `/storage/`, y `/admin` a la app backend.
   - **`app`:** Expone el puerto interno `8000` (PHP 8.4 Alpine). Procesa la API REST y el panel de administración.
   - **`db`:** PostgreSQL 16 Alpine conectado en la red interna `backend-network`.
3. **Red Externa Traefik:** Conectado a la red `dokploy-network`.
4. **Persistencia de Datos:**
   - **Base de datos:** Volumen nombrado Docker `db_data:/var/lib/postgresql/data` (compatible con la herramienta de backups de Dokploy).
   - **Archivos de usuarios (PDFs, portadas, excels):** Bind mount seguro `../files/storage:/var/www/html/storage/app/public`.
5. **Timezone Obligatorio:** Configurado globalmente como `TZ=America/La_Paz`.
6. **Entrypoint Automatizado:** `docker-entrypoint.sh` ejecuta migraciones (`php artisan migrate --force`), sincronización de secuencias, enlace simbólico de storage y optimización de caché al iniciar.

### Comandos de Consola en Dokploy:
Desde la terminal web de Dokploy, los administradores pueden cargar los catálogos y datos iniciales en cualquier momento:
```bash
php artisan db:seed --force
```

---

## 👥 7. Metodología de Paso a Producción

1. **Revisión de Cumplimiento:** Código limpio, sin secretos ni archivos `.env` reales en Git, dependencias validadas y pruebas aprobadas.
2. **Fork Oficial:** El código se forkea a la organización oficial: `github.com/sociest`.
3. **Coordinación Técnica:** Para coordinar el fork e inyección de contraseñas de producción:
   - **Administración Técnica:** Andres Chirinos — [WhatsApp 67157385](http://wa.me/67157385).
