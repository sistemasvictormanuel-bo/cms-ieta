# Instituto de Estadística Teórica y Aplicada (IETA) - Frontend

Portal web oficial y plataforma de difusión científica del **Instituto de Estadística Teórica y Aplicada (IETA)**, perteneciente a la **Carrera de Estadística** de la **Universidad Mayor de San Andrés (UMSA)**.

---

## 1. Descripción del Proyecto

El sistema proporciona la interfaz pública y académica del IETA, resolviendo la necesidad de centralizar y visibilizar la producción científica, publicaciones periódicas y servicios analíticos de la institución:

* **Líneas y Memorias de Investigación:** Catálogo de proyectos, metodologías y documentos de investigación estadística (con filtrado por año y búsqueda en tiempo real).
* **Revista Científica Varianza:** Publicación indexada con gestión de ediciones, artículos, perfiles de autores y comités editoriales.
* **Recursos y Banco de Datos:** Difusión de censos, encuestas estadísticas y accesos directos a ecosistemas de software analítico (R, RStudio, Stata, Python).
* **Servicios y Cursos:** Portafolio de consultoría estadística, interacción social y capacitación especializada.
* **Noticias y Multimedia:** Comunicados oficiales, convocatorias y galería de actividades institucionales.
---

## 2. Stack Tecnológico

* **Framework Principal:** [Vue.js 3](https://vuejs.org/) (Composition API, `<script setup>`)
* **Lenguaje:** [TypeScript 5+](https://www.typescriptlang.org/) (Tipado estricto con `vue-tsc`)
* **Herramienta de Construcción:** [Vite 8](https://vite.dev/)
* **Enrutamiento:** [Vue Router 4](https://router.vuejs.org/) (HTML5 History Mode)
* **Gestión de Estado:** [Pinia](https://pinia.vuejs.org/)
* **Cliente HTTP:** [Axios](https://axios-http.com/) (Instancia centralizada con soporte para Laravel Sanctum)
* **Internacionalización:** [Vue I18n 11](https://vue-i18n.intlify.dev/)
* **Animaciones e Interacción:** [GSAP 3](https://gsap.com/)
* **Iconografía:** [Lucide Vue](https://lucide.dev/)
* **Servidor Web en Producción:** [Nginx Alpine](https://hub.docker.com/_/nginx) en contenedor multi-stage

---

## 3. Requisitos Previos

Para ejecutar y desarrollar el proyecto en entorno local se requiere:

* **Node.js:** Versión `^22.18.0` o `>=24.12.0` (recomendado v22 LTS o v24).
* **npm:** Versión `10+` (incluido con Node.js).
* **Docker & Docker Compose:** *(Opcional)* Para pruebas de empaquetado y despliegue local.

---

## 4. Instrucciones de Instalación y Ejecución Local

### Paso 1: Clonar el repositorio
```bash
git clone https://github.com/jpacoa/ieta-frontend.git
cd ieta-frontend
```

### Paso 2: Configurar las variables de entorno
Copia la plantilla de variables de entorno:
```bash
cp .env.example .env
```
Edita `.env` para apuntar a la URL de tu API backend:
```env
VITE_API_URL=http://localhost:8000/api
```

### Paso 3: Instalar dependencias
```bash
npm install
```

### Paso 4: Iniciar el servidor de desarrollo
```bash
npm run dev
```
La aplicación estará disponible en `http://localhost:5173`.

### Comandos de Calidad y Compilación
* **Verificación de tipos TypeScript:** `npm run type-check`
* **Compilación de producción:** `npm run build`
* **Linter de código:** `npm run lint`

---

## 5. Variables de Entorno

El proyecto se configura mediante variables de entorno prefijadas con `VITE_`:

| Variable | Tipo | Requerida | Valor por Defecto | Descripción |
| :--- | :---: | :---: | :--- | :--- |
| `VITE_API_URL` | String | Sí | `http://localhost:8000/api` | URL base de la API REST del CMS backend. |

> **Nota importante sobre Vite:** Las variables `VITE_*` son inyectadas en tiempo de compilación (`build time`). En Dokploy, la variable se inyecta como argumento de compilación (`ARG VITE_API_URL`) dentro del `Dockerfile`.

---

## 6. Despliegue en Servidor Oficial (Dokploy / Traefik)

El repositorio está 100% adaptado a los estándares de **Dokploy** de la Carrera de Estadística:

* **Proxy Traefik:** El servicio expone únicamente el puerto interno `80` (`expose: - "80"`) sin mapeo directo de puertos en el host.
* **Red Externa:** Conectado a la red `dokploy-network`.
* **Zona Horaria:** Configurado con `TZ=America/La_Paz`.
* **SPA Fallback:** Incluye configuración de Nginx con directiva `try_files $uri $uri/ /index.html;` para soportar navegación directa y recargas en rutas anidadas.

### Despliegue local con Docker Compose (simulación):
```bash
# Crear la red externa si no existe localmente
docker network create dokploy-network || true

# Construir y levantar el contenedor
docker compose up -d --build
```

---

## 7. Licencia y Créditos

* **Desarrollado para:** Instituto de Estadística Teórica y Aplicada (IETA)
* **Institución:** Carrera de Estadística, Facultad de Ciencias Puras y Naturales, UMSA
* **Año:** 2026
