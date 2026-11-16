import 'vue-router'

export interface BreadcrumbItem {
  label: string
  to?: string
}

// Extiende el tipo RouteMeta de Vue Router con nuestras propiedades personalizadas
declare module 'vue-router' {
  interface RouteMeta {
    /** Título de la vista — usado en <title> y como último segmento del breadcrumb */
    title?: string
    /** Segmentos del breadcrumb — generado estáticamente en router/index.ts */
    breadcrumb?: BreadcrumbItem[]
  }
}

