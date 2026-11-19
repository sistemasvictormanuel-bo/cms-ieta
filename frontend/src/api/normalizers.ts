import type { AuthorApiItem, CourseApiItem, NewsApiItem, ResearchMemoryApiItem } from './types'

export function newsTitle(news: NewsApiItem) {
  return news.titulo?.trim() || news.not_titulo?.trim() || ''
}

export function newsSummary(news: NewsApiItem) {
  return news.descripcion?.trim() || news.not_descripcion?.trim() || ''
}

export function newsDate(news: NewsApiItem) {
  return news.fecha || news.not_fecha || ''
}

export function newsIsPublished(news: NewsApiItem) {
  return (news.publicado ?? news.not_publicado) !== false
}

export function newsIsFeatured(news: NewsApiItem) {
  return (news.destacado ?? news.not_destacado) === true
}

export function newsContent(news: NewsApiItem) {
  return news.contenido ?? news.not_contenido
}

export function courseTitle(course: CourseApiItem) {
  return course.titulo?.trim() || ''
}

export function courseType(course: CourseApiItem) {
  return course.tipo_curso?.tc_nombre || 'Curso'
}

import { mediaUrl } from './response'
import type { ArticleApiItem } from './types'

export function authorName(author: AuthorApiItem) {
  return author.persona?.nombre_completo?.trim() ||
    [author.persona?.p_nombre, author.persona?.p_apellido_paterno, author.persona?.p_apellido_materno]
      .filter(Boolean)
      .join(' ') ||
    'Investigador IETA'
}

export function memoryTitle(memory: ResearchMemoryApiItem) {
  return memory.titulo?.trim() || memory.mdi_titulo?.trim() || ''
}

export function memoryDescription(memory: ResearchMemoryApiItem) {
  return memory.descripcion?.trim() || memory.mdi_descripcion?.trim() || ''
}

export function memoryDate(memory: ResearchMemoryApiItem) {
  return memory.anio || memory.mdi_anio || memory.fecha || memory.mdi_fecha || ''
}

export function memoryDocumentUrl(memory: ResearchMemoryApiItem): string | undefined {
  const raw =
    memory.archivo_url ||
    memory.enlace_url ||
    memory.mdi_enlace_url ||
    memory.mdi_archivo_url ||
    memory.documento_url ||
    memory.pdf_url ||
    memory.url ||
    memory.drive_url ||
    memory.archivo ||
    memory.bdd_url
  if (!raw) return undefined
  return mediaUrl(raw) || (/^https?:\/\//i.test(raw) ? raw : undefined)
}

export function articleCoverUrl(article?: ArticleApiItem | null, fallbackCover?: string | null): string | undefined {
  if (!article) return fallbackCover ? mediaUrl(fallbackCover) : undefined
  const raw =
    article.portada_url ||
    article.caratula_url ||
    article.imagen_url ||
    article.edicion?.portada_url ||
    fallbackCover
  return mediaUrl(raw)
}

export function articleDownloadUrl(article?: ArticleApiItem | null): string | undefined {
  if (!article) return undefined
  const raw =
    article.enlace_drive ||
    article.drive_url ||
    article.archivo_url ||
    article.pdf_url ||
    article.enlace_url ||
    article.url ||
    article.archivo
  if (!raw) return undefined
  return mediaUrl(raw) || (/^https?:\/\//i.test(raw) ? raw : undefined)
}

export function formatApiDate(value?: string | null) {
  if (!value) return ''
  const dateOnly = /^(\d{4})-(\d{2})-(\d{2})$/.exec(value)
  const date = dateOnly
    ? new Date(Number(dateOnly[1]), Number(dateOnly[2]) - 1, Number(dateOnly[3]))
    : new Date(value)
  if (Number.isNaN(date.getTime())) return value
  return new Intl.DateTimeFormat('es-BO', { dateStyle: 'long' }).format(date)
}
