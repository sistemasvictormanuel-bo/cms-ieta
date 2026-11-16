export interface PaginationMeta {
  current_page?: number | null
  last_page?: number | null
  per_page?: number | null
  total?: number | null
}

export interface ApiCollection<T> {
  data: T[]
  meta?: PaginationMeta
  links?: {
    next?: string | null
    prev?: string | null
  }
}

export interface ApiResource<T> {
  data: T
}

export interface PersonApiItem {
  id: number
  p_nombre?: string | null
  p_apellido_paterno?: string | null
  p_apellido_materno?: string | null
  nombre_completo?: string | null
  p_celular?: string | null
  p_correo?: string | null
  p_sitio_web?: string | null
  foto_url?: string | null
}

export interface NewsApiItem {
  id: number
  titulo?: string | null
  not_titulo?: string | null
  descripcion?: string | null
  not_descripcion?: string | null
  fecha?: string | null
  not_fecha?: string | null
  destacado?: boolean | null
  not_destacado?: boolean | null
  publicado?: boolean | null
  not_publicado?: boolean | null
  contenido?: unknown
  not_contenido?: unknown
  url?: string | null
  not_url?: string | null
  portada_url?: string | null
  persona?: PersonApiItem | null
}

export interface CourseApiItem {
  id: string | number
  titulo: string
  slug?: string | null
  descripcion?: string | null
  contenido?: unknown
  fecha_inicio?: string | null
  fecha_fin?: string | null
  hora_inicio?: string | null
  hora_fin?: string | null
  duracion?: string | null
  email_info?: string | null
  telefono_ref?: string | null
  costo?: string | null
  portada_url?: string | null
  activo?: boolean | null
  docente?: string | PersonApiItem | Record<string, unknown> | null | unknown
  tipo_curso?: { id: number; tc_nombre: string } | null
  modalidad_curso?: { id: string | number; nombre?: string | null; mc_nombre?: string | null } | null
}

export interface RevistasVariasApiItem {
  id: number
  titulo: string
  descripcion?: string | null
  url_enlace?: string | null
  entidad?: string | null
  issn?: string | null
  activo?: boolean
  orden?: number
  portada_url?: string | null
  created_at?: string | null
  updated_at?: string | null
}

export interface GaleriaApiItem {
  id: number
  titulo: string
  descripcion?: string | null
  categoria?: string | null
  fecha?: string | null
  fecha_formateada?: string | null
  lugar?: string | null
  destacado?: boolean
  activo?: boolean
  orden?: number
  imagen_url?: string | null
  created_at?: string | null
  updated_at?: string | null
}

export interface ServiceApiItem {
  id: string | number
  titulo: string
  slug?: string | null
  descripcion?: string | null
  contenido?: unknown
  imagen_url?: string | null
}

export interface AuthorApiItem {
  id: number
  slug: string
  biografia?: unknown
  intereses_cientificos?: string | null
  estado?: boolean
  persona?: PersonApiItem | null
  tipo_autor?: { nombre?: string | null } | null
  especialidad?: { id?: string | number; nombre?: string | null } | null
  especialidades?: Array<{ id: string | number; nombre: string; descripcion?: string | null }>
  grado_academico?: { nombre?: string | null; abreviatura?: string | null } | null
  articulos?: ArticleApiItem[]
}

export interface ArticleApiItem {
  id: number
  tipo_articulo_id?: number | null
  tipo_articulo?: { id?: number; nombre?: string | null; descripcion?: string | null } | null
  edicion_varianza_id?: number | null
  titulo: string
  slug?: string | null
  resumen?: string | null
  abstracto?: string | null
  fecha_recepcion?: string | null
  fecha_aceptado?: string | null
  orcid?: string | null
  doi?: string | null
  num_paginas?: number | null
  estado?: boolean
  referencias?: string | null
  pag_inicio?: number | null
  pag_fin?: number | null
  rango_paginas?: string | null
  etiquetas?: ArticleTagApiItem[]
  etiquetas_array?: Record<string, unknown> | null
  vistas?: number | null
  archivo_url?: string | null
  portada_url?: string | null
  caratula_url?: string | null
  imagen_url?: string | null
  enlace_drive?: string | null
  drive_url?: string | null
  enlace_url?: string | null
  url?: string | null
  pdf_url?: string | null
  archivo?: string | null
  edicion_id?: string | number | null
  edicion?: {
    id?: string | number
    numero?: string | number | null
    titulo?: string | null
    volumen?: string | null
    anio?: number | null
    slug?: string | null
    portada_url?: string | null
  } | null
  autores?: AuthorApiItem[]
  articulos_similares?: ArticleApiItem[]
  mas_leidos_autor?: ArticleApiItem[]
  created_at?: string | null
  updated_at?: string | null
}

export interface ArticleTagApiItem {
  id: number
  nombre: string
  slug?: string | null
  articulos_count?: number
}

export interface VarianzaIssueApiItem {
  id: number
  titulo: string
  numero: string
  volumen?: string | null
  anio: number
  mes?: string | null
  slug: string
  issn_impresa?: string | null
  issn_electronica?: string | null
  num_art_originales_declarado?: number | null
  notas_cientificas_declarado?: number | null
  num_paginas?: number | null
  doi?: string | null
  presentacion?: unknown
  editorial?: string | null
  descripcion?: string | null
  fecha_publicacion?: string | null
  estado?: boolean
  portada_url?: string | null
  archivo_url?: string | null
  articulos_count?: number | string | null
  articulos_originales_count?: number | string | null
  notas_cientificas_count?: number | string | null
  articulos?: ArticleApiItem[]
}

export interface VarianzaProfileApiItem {
  id: number
  titulo: string
  subtitulo?: string | null
  mision?: string | null
  vision?: string | null
  contenido?: unknown
  alcance?: unknown
  politicas?: unknown
  issn_impreso?: string | null
  issn_electronico?: string | null
  periodicidad?: string | null
  correo_contacto?: string | null
  portada_url?: string | null
}

export interface AuthorGuidelineApiItem {
  id: number
  titulo_seccion?: string
  titulo?: string
  contenido?: unknown
  orden?: number
  activo?: boolean
}

export interface AnnouncementApiItem {
  id: number
  titulo: string
  descripcion?: string | null
  enlace_url?: string | null
  texto_boton?: string | null
  tipo?: string | null
  fecha_inicio?: string | null
  fecha_fin?: string | null
  activo?: boolean | number | string
  estado?: boolean | number | string
  vistas?: number
  flyer_url?: string | null
  flyer?: string | null
  imagen_url?: string | null
  portada_url?: string | null
  created_at?: string | null
  updated_at?: string | null
}

export interface InstitutionConfigApiItem {
  id: number
  nombre_institucion?: string | null
  siglas?: string | null
  slogan?: string | null
  descripcion_corta?: string | null
  email_principal?: string | null
  telefono_principal?: string | null
  direccion_principal?: string | null
  redes_sociales?: {
    facebook?: string | null
    twitter?: string | null
    linkedin?: string | null
    youtube?: string | null
    instagram?: string | null
  } | null
  copyright?: string | null
  logo_principal_url?: string | null
  logo_blanco_url?: string | null
  favicon_url?: string | null
}

export interface DataAreaApiItem {
  id: string | number
  nombre: string
  descripcion?: string | null
  created_at?: string | null
  updated_at?: string | null
}

export interface DataBankApiItem {
  id: number
  tipo_dato?: string | null
  anio?: number | null
  codigo?: string | null
  titulo: string
  descripcion?: string | null
  locacion?: string | null
  url?: string | null
  bdd_url?: string | null
  archivo_url?: string | null
  portada_url?: string | null
  imagen_url?: string | null
  areas_tematicas?: DataAreaApiItem[]
  created_at?: string | null
  updated_at?: string | null
}

export interface ResearchMemoryApiItem {
  id: number
  titulo?: string | null
  mdi_titulo?: string | null
  descripcion?: string | null
  mdi_descripcion?: string | null
  etiqueta?: string | null
  mdi_etiqueta?: string | null
  fecha?: string | null
  mdi_fecha?: string | null
  enlace_url?: string | null
  mdi_enlace_url?: string | null
  archivo_url?: string | null
  mdi_archivo_url?: string | null
  documento_url?: string | null
  url?: string | null
  pdf_url?: string | null
  drive_url?: string | null
  archivo?: string | null
  bdd_url?: string | null
  portada_url?: string | null
  imagen_url?: string | null
  autor?: AuthorApiItem | null
  created_at?: string | null
  updated_at?: string | null
}

export interface EditorialMemberApiItem {
  id: number
  tipo?: string | null
  persona?: PersonApiItem | null
  cargo?: { nombre?: string | null } | null
  institucion?: { nombre?: string | null; pais?: string | null; ciudad?: string | null } | null
}

export interface StaffApiItem {
  id: number
  descripcion?: string | null
  fecha_ingreso?: string | null
  fecha_retiro?: string | null
  estado?: boolean
  persona?: PersonApiItem | null
  cargo?: { nombre?: string | null } | null
}
