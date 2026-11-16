import { createRouter, createWebHistory } from 'vue-router'
import PublicLayout from '@/layouts/PublicLayout.vue'

/**
 * meta.breadcrumb: array de segmentos para el breadcrumb dinámico.
 * Si no se define, Breadcrumb.vue lo construye desde matched[].
 * meta.title: se usa en <title> del documento y como último segmento del breadcrumb.
 */

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  scrollBehavior(_to, _from, savedPosition) {
    if (savedPosition) return savedPosition
    return { top: 0, behavior: 'smooth' }
  },
  routes: [
    {
      path: '/',
      component: PublicLayout,
      children: [
        // ── Inicio ─────────────────────────────────────────────
        {
          path: '',
          name: 'home',
          component: () => import('@/views/HomeView.vue'),
          meta: { title: 'Inicio' },
        },

        // ── Acerca de ───────────────────────────────────────────
        {
          path: 'acerca-de/:tab?',
          name: 'acerca-de',
          component: () => import('@/views/AcercaDeView.vue'),
          meta: {
            title: 'Acerca de',
            breadcrumb: [{ label: 'Inicio', to: '/' }, { label: 'Acerca de' }],
          },
        },
        {
          path: 'acerca-de/personal/:slug',
          name: 'personal-ficha',
          component: () => import('@/views/PersonalFichaView.vue'),
          meta: {
            title: 'Personal',
            breadcrumb: [
              { label: 'Inicio', to: '/' },
              { label: 'Acerca de', to: '/acerca-de/personal' },
              { label: 'Personal' },
            ],
          },
        },

        // ── Investigación ───────────────────────────────────────
        {
          path: 'investigacion/lineas-investigacion',
          name: 'investigacion-lineas-investigacion',
          component: () => import('@/views/InvestigacionLineasView.vue'),
          meta: {
            title: 'Líneas de Investigación',
            breadcrumb: [
              { label: 'Inicio', to: '/' },
              { label: 'Investigación' },
              { label: 'Líneas de Investigación' },
            ],
          },
        },
        {
          path: 'investigacion/lineas-investigacion/:id',
          name: 'investigacion-linea-detalle',
          component: () => import('@/views/InvestigacionLineaDetalleView.vue'),
          meta: {
            title: 'Detalle de Línea',
            breadcrumb: [
              { label: 'Inicio', to: '/' },
              { label: 'Investigación' },
              { label: 'Líneas de Investigación', to: '/investigacion/lineas-investigacion' },
              { label: 'Detalle' },
            ],
          },
        },
        {
          path: 'investigacion/memorias',
          name: 'investigacion-memorias',
          component: () => import('@/views/InvestigacionMemoriasView.vue'),
          meta: {
            title: 'Memorias de Investigación',
            breadcrumb: [
              { label: 'Inicio', to: '/' },
              { label: 'Investigación' },
              { label: 'Memorias de Investigación' },
            ],
          },
        },

        // ── Revista Varianza ────────────────────────────────────
        {
          path: 'revista-varianza/:tab?',
          name: 'revista-varianza',
          component: () => import('@/views/RevistaVarianzaView.vue'),
          meta: {
            title: 'Revista Varianza',
            breadcrumb: [{ label: 'Inicio', to: '/' }, { label: 'Revista Varianza' }],
          },
        },
        {
          path: 'revista-varianza/articulos',
          name: 'revista-varianza-articulos',
          component: () => import('@/views/ArticulosView.vue'),
          meta: {
            title: 'Artículos de Varianza',
            breadcrumb: [
              { label: 'Inicio', to: '/' },
              { label: 'Revista Varianza', to: '/revista-varianza' },
              { label: 'Artículos' },
            ],
          },
        },
        {
          path: 'revista-varianza/autores/:slug',
          name: 'autor-perfil',
          component: () => import('@/views/AutorPerfilView.vue'),
          meta: {
            title: 'Perfil de Autor',
            breadcrumb: [
              { label: 'Inicio', to: '/' },
              { label: 'Revista Varianza', to: '/revista-varianza/autores' },
              { label: 'Autores', to: '/revista-varianza/autores' },
              { label: 'Perfil de Autor' },
            ],
          },
        },
        {
          path: 'revista-varianza/ediciones/:numero',
          name: 'edicion-detalle',
          component: () => import('@/views/EdicionDetalleView.vue'),
          meta: {
            title: 'Edición',
            breadcrumb: [
              { label: 'Inicio', to: '/' },
              { label: 'Revista Varianza', to: '/revista-varianza/ediciones' },
              { label: 'Ediciones', to: '/revista-varianza/ediciones' },
              { label: 'Edición' },
            ],
          },
        },
        {
          path: 'revista-varianza/articulos/:slug',
          name: 'articulo-detalle',
          component: () => import('@/views/ArticuloDetalleView.vue'),
          meta: {
            title: 'Artículo',
            breadcrumb: [
              { label: 'Inicio', to: '/' },
              { label: 'Revista Varianza', to: '/revista-varianza/ediciones' },
              { label: 'Artículo' },
            ],
          },
        },

        // ── Servicios ───────────────────────────────────────────
        {
          path: 'servicios',
          name: 'servicios',
          component: () => import('@/views/ServiciosView.vue'),
          meta: {
            title: 'Servicios',
            breadcrumb: [{ label: 'Inicio', to: '/' }, { label: 'Servicios' }],
          },
        },
        {
          path: 'servicios/:slug',
          name: 'servicio-detalle',
          component: () => import('@/views/ServicioDetalleView.vue'),
          meta: {
            title: 'Servicio',
            breadcrumb: [
              { label: 'Inicio', to: '/' },
              { label: 'Servicios', to: '/servicios' },
              { label: 'Servicio' },
            ],
          },
        },

        // ── Noticias ────────────────────────────────────────────
        {
          path: 'noticias',
          name: 'noticias',
          component: () => import('@/views/NoticiasView.vue'),
          meta: {
            title: 'Noticias',
            breadcrumb: [{ label: 'Inicio', to: '/' }, { label: 'Noticias' }],
          },
        },
        {
          path: 'noticias/:slug',
          name: 'noticia-detalle',
          component: () => import('@/views/NoticiaDetalleView.vue'),
          meta: {
            title: 'Noticia',
            breadcrumb: [
              { label: 'Inicio', to: '/' },
              { label: 'Noticias', to: '/noticias' },
              { label: 'Noticia' },
            ],
          },
        },

        // ── Cursos y Talleres ───────────────────────────────────
        {
          path: 'cursos',
          name: 'cursos',
          component: () => import('@/views/CursosView.vue'),
          meta: {
            title: 'Cursos y Talleres',
            breadcrumb: [{ label: 'Inicio', to: '/' }, { label: 'Cursos y Talleres' }],
          },
        },
        {
          path: 'cursos-talleres/:slug',
          name: 'curso-detalle',
          component: () => import('@/views/CursoDetalleView.vue'),
          meta: {
            title: 'Cursos y Talleres',
            breadcrumb: [
              { label: 'Inicio', to: '/' },
              { label: 'Cursos y Talleres', to: '/cursos' },
              { label: 'Detalle del curso' },
            ],
          },
        },

        // ── Recursos ────────────────────────────────────────────
        {
          path: 'recursos/banco-datos/:id',
          name: 'recurso-banco-datos-detalle',
          component: () => import('@/views/RecursoBancoDatosDetalleView.vue'),
          meta: {
            title: 'Conjunto de datos',
            breadcrumb: [
              { label: 'Inicio', to: '/' },
              { label: 'Recursos', to: '/recursos' },
              { label: 'Banco de Datos', to: '/recursos' },
              { label: 'Conjunto de datos' },
            ],
          },
        },
        {
          path: 'recursos/:tab?',
          name: 'recursos',
          component: () => import('@/views/RecursosView.vue'),
          meta: {
            title: 'Recursos',
            breadcrumb: [{ label: 'Inicio', to: '/' }, { label: 'Recursos' }],
          },
        },
        {
          path: 'revistas-varias',
          name: 'revistas-varias',
          redirect: '/recursos/revistas-de-estadistica',
        },

        // ── Contacto ────────────────────────────────────────────
        {
          path: 'contacto',
          name: 'contacto',
          component: () => import('@/views/ContactoView.vue'),
          meta: {
            title: 'Contacto',
            breadcrumb: [{ label: 'Inicio', to: '/' }, { label: 'Contacto' }],
          },
        },
        {
          path: 'multimedia',
          name: 'multimedia',
          component: () => import('@/views/MultimediaView.vue'),
          meta: {
            title: 'Galería',
            breadcrumb: [{ label: 'Inicio', to: '/' }, { label: 'Galería' }],
          },
        },
        {
          path: 'galeria',
          name: 'galeria',
          redirect: '/multimedia',
        },
        ...(import.meta.env.DEV
          ? [
              {
                path: 'dev-components',
                name: 'dev-components',
                component: () => import('@/views/DevComponentsView.vue'),
                meta: { title: 'Componentes base' },
              },
            ]
          : []),
      ],
    },

    // ── 404 ─────────────────────────────────────────────────────
    {
      path: '/:pathMatch(.*)*',
      name: 'not-found',
      component: () => import('@/views/NotFoundView.vue'),
      meta: { title: 'Página no encontrada' },
    },
  ],
})

// Actualizar el <title> del documento en cada navegación
router.afterEach((to) => {
  const title = to.meta?.title as string | undefined
  document.title = title ? `${title} | IETA` : 'IETA'
})

export default router
