<script setup lang="ts">
import { computed, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import {
  ArrowLeft,
  BookOpen,
  Calendar,
  Download,
  ExternalLink,
  Eye,
  FileText,
  Hash,
  Link,
  Tag,
  User,
} from '@lucide/vue'
import EmptyState from '@/components/common/EmptyState.vue'
import SectionHeaderBand from '@/components/layout/SectionHeaderBand.vue'
import { formatApiDate, authorName, articleCoverUrl, articleDownloadUrl } from '@/api/normalizers'
import { mediaUrl } from '@/api/response'
import { ArticulosRepository } from '@/api/repositories.js'
import type { ArticleApiItem } from '@/api/types'
import { useApiResource } from '@/composables/useApiResource'
import { useGsapReveal } from '@/composables/useGsapReveal'

const route = useRoute()
const articleRoot = ref<HTMLElement | null>(null)
const articleId = computed(() => String(route.params.slug ?? ''))

const { data: article, loading, error, reload } = useApiResource<ArticleApiItem>(
  articleId,
  (id) => ArticulosRepository.getById(id),
)

const title = computed(() => article.value?.titulo || 'Artículo Científico')
const cover = computed(() => articleCoverUrl(article.value))
const downloadUrl = computed(() => articleDownloadUrl(article.value))

useGsapReveal(articleRoot, {
  selector: '.article-reveal',
  stagger: 0.08,
  y: 18,
})
</script>

<template>
  <div ref="articleRoot">
    <SectionHeaderBand
      :title="title"
      :subtitle="article?.edicion?.titulo || 'Artículo publicado en Revista Varianza'"
    />

    <main class="container view-body article-detail">
      <!-- Back Navigation -->
      <nav class="article-detail__nav article-reveal" aria-label="Navegación de retorno">
        <RouterLink class="article-detail__back" to="/revista-varianza/ediciones">
          <ArrowLeft :size="16" aria-hidden="true" />
          <span>Volver a Ediciones de Varianza</span>
        </RouterLink>
      </nav>

      <!-- Loading State -->
      <div v-if="loading" class="article-skeleton" role="status" aria-label="Cargando artículo">
        <div class="skeleton-pill"></div>
        <div class="skeleton-title"></div>
        <div class="skeleton-authors"></div>
        <div class="skeleton-box"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="article-detail__state" role="alert">
        <EmptyState
          title="No se pudo cargar el artículo"
          description="Por favor, verifica el identificador o inténtalo nuevamente."
          action-label="Reintentar"
          @action="reload"
        />
      </div>

      <!-- Not Found -->
      <EmptyState v-else-if="!article" title="No se encontró el artículo solicitado" />

      <!-- Loaded Article -->
      <article v-else class="article-content-wrapper">
        <header class="article-header article-reveal">
          <div class="article-header__layout" :class="{ 'article-header__layout--with-cover': !!cover }">
            <div v-if="cover" class="article-cover-box">
              <img
                :src="cover"
                :alt="`Carátula de ${title}`"
                class="article-cover-img"
              />
            </div>
            <div class="article-header__main">
              <div class="article-pills">
                <span v-if="article.tipo_articulo?.nombre" class="type-pill">
                  {{ article.tipo_articulo.nombre }}
                </span>
                <span v-if="article.edicion" class="issue-pill">
                  <RouterLink :to="`/revista-varianza/ediciones/${encodeURIComponent(article.edicion.slug || String(article.edicion.id))}`">
                    <BookOpen :size="13" aria-hidden="true" />
                    <span>{{ article.edicion.titulo || `Edición N° ${article.edicion.numero}` }}</span>
                  </RouterLink>
                </span>
              </div>

              <h1 class="article-title">{{ title }}</h1>

              <!-- Authors Bar -->
              <div v-if="article.autores?.length" class="authors-bar">
                <User :size="16" class="author-icon" aria-hidden="true" />
                <span class="authors-label">Autores:</span>
                <div class="authors-list">
                  <RouterLink
                    v-for="author in article.autores"
                    :key="author.id"
                    class="author-link"
                    :to="`/revista-varianza/autores/${encodeURIComponent(author.slug || String(author.id))}`"
                  >
                    {{ authorName(author) }}
                  </RouterLink>
                </div>
              </div>

              <!-- Metadata Tags -->
              <div class="article-meta-tags">
                <span v-if="article.fecha_aceptado" class="meta-tag">
                  <Calendar :size="13" aria-hidden="true" />
                  <span>Publicado: {{ formatApiDate(article.fecha_aceptado) }}</span>
                </span>
                <span v-if="article.rango_paginas || article.pag_inicio" class="meta-tag">
                  <FileText :size="13" aria-hidden="true" />
                  <span>Págs. {{ article.rango_paginas || [article.pag_inicio, article.pag_fin].filter(Boolean).join('–') }}</span>
                </span>
                <span v-if="article.doi" class="meta-tag meta-tag--doi">
                  <span>DOI: {{ article.doi }}</span>
                </span>
                <span v-if="article.vistas !== undefined" class="meta-tag">
                  <Eye :size="13" aria-hidden="true" />
                  <span>{{ article.vistas }} lecturas</span>
                </span>
              </div>
            </div>
          </div>
        </header>

        <!-- Abstract / Resumen -->
        <section v-if="article.resumen || article.abstracto" class="abstract-section article-reveal">
          <h2 class="section-title">Resumen / Abstract</h2>
          <div class="abstract-card">
            <p>{{ article.resumen || article.abstracto }}</p>
          </div>
        </section>

        <!-- Keywords / Tags -->
        <div v-if="article.etiquetas?.length" class="keywords-bar article-reveal">
          <span class="keywords-title">
            <Tag :size="14" aria-hidden="true" />
            <span>Palabras clave:</span>
          </span>
          <span v-for="tag in article.etiquetas" :key="tag.id" class="keyword-chip">
            {{ tag.nombre }}
          </span>
        </div>

        <!-- Download Full Paper Action -->
        <div v-if="downloadUrl" class="download-action-box article-reveal">
          <div class="download-info">
            <h3>Artículo Completo (PDF / Google Drive)</h3>
            <p>Descarga o consulta el documento completo con diagramación, tablas y fórmulas de la Revista Varianza.</p>
          </div>
          <a
            class="download-btn"
            :href="downloadUrl"
            target="_blank"
            rel="noopener noreferrer"
          >
            <Download :size="16" aria-hidden="true" />
            <span>Descargar / Ver en Google Drive</span>
            <ExternalLink :size="14" aria-hidden="true" />
          </a>
        </div>

        <!-- References -->
        <section v-if="article.referencias" class="references-section article-reveal">
          <h2 class="section-title">Referencias Bibliográficas</h2>
          <div class="references-card">
            <p class="references-text">{{ article.referencias }}</p>
          </div>
        </section>

        <!-- Similar Articles -->
        <section v-if="article.articulos_similares?.length" class="related-section article-reveal">
          <h2 class="section-title">Artículos Relacionados</h2>
          <ul class="related-list">
            <li v-for="related in article.articulos_similares" :key="related.id" class="related-item">
              <RouterLink :to="`/revista-varianza/articulos/${related.id}`">
                {{ related.titulo }}
              </RouterLink>
            </li>
          </ul>
        </section>

        <!-- Empty State -->
        <EmptyState
          v-if="!article.resumen && !article.abstracto && !article.referencias && !downloadUrl"
          title="El artículo aún no tiene contenido cargado"
        />
      </article>
    </main>
  </div>
</template>

<style scoped>
.article-detail {
  min-height: 48vh;
  padding-block: var(--space-4) var(--space-8);
}

.article-detail__nav {
  margin-bottom: var(--space-4);
}

.article-detail__back {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  color: var(--color-indigo);
  font-size: var(--text-small-size);
  font-weight: 700;
  text-decoration: none;
  transition: transform 0.15s ease, color 0.15s ease;
}

.article-detail__back:hover {
  transform: translateX(-4px);
  color: var(--color-rosa-carmin);
}

.article-content-wrapper {
  max-width: 76ch;
  margin-inline: auto;
}

/* Header */
.article-header {
  margin-bottom: var(--space-4);
  padding-bottom: var(--space-4);
  border-bottom: 1px solid var(--color-desactivado);
}

.article-header__layout--with-cover {
  display: grid;
  grid-template-columns: 14rem minmax(0, 1fr);
  gap: 1.75rem;
  align-items: start;
}

.article-cover-box {
  border-radius: var(--radius-sm);
  overflow: hidden;
  box-shadow: 0 8px 24px -4px rgba(27, 20, 75, 0.16);
  border: 1px solid rgba(220, 224, 235, 0.9);
  background: #f8fafc;
}

.article-cover-img {
  width: 100%;
  height: auto;
  display: block;
  object-fit: cover;
}

@media (max-width: 640px) {
  .article-header__layout--with-cover {
    grid-template-columns: 1fr;
  }
  .article-cover-box {
    max-width: 13rem;
    margin-bottom: 1rem;
  }
}

.article-pills {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.65rem;
}

.type-pill {
  display: inline-block;
  padding: 0.2rem 0.65rem;
  border-radius: 999px;
  background: rgba(243, 0, 96, 0.1);
  color: var(--color-rosa-carmin);
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.issue-pill a {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.2rem 0.65rem;
  border-radius: 999px;
  background: rgba(53, 68, 159, 0.1);
  color: var(--color-indigo);
  font-size: 0.75rem;
  font-weight: 700;
  text-decoration: none;
}

.article-title {
  color: var(--color-indigo);
  font-size: clamp(1.85rem, 4.2vw, 2.65rem);
  font-weight: 700;
  line-height: 1.22;
  margin-bottom: var(--space-3);
}

/* Authors */
.authors-bar {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.45rem;
  margin-bottom: var(--space-2);
}

.author-icon {
  color: var(--color-rosa-carmin);
}

.authors-label {
  color: #61687d;
  font-size: var(--text-small-size);
  font-weight: 600;
}

.authors-list {
  display: flex;
  flex-wrap: wrap;
  gap: 0.4rem;
}

.author-link {
  color: var(--color-indigo);
  font-size: var(--text-body-size);
  font-weight: 700;
  text-decoration: underline;
  text-decoration-color: rgba(53, 68, 159, 0.3);
  text-underline-offset: 3px;
  transition: color 0.15s ease, text-decoration-color 0.15s ease;
}

.author-link:hover {
  color: var(--color-rosa-carmin);
  text-decoration-color: var(--color-rosa-carmin);
}

.author-link:not(:last-child)::after {
  content: ',';
  color: #61687d;
}

/* Meta Tags */
.article-meta-tags {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.65rem;
  margin-top: 0.75rem;
}

.meta-tag {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  color: #61687d;
  font-size: var(--text-small-size);
}

.meta-tag--doi {
  font-family: monospace;
  font-size: 0.8rem;
  background: var(--color-superficie);
  padding: 0.15rem 0.5rem;
  border-radius: var(--radius-sm);
  color: var(--color-indigo);
}

/* Abstract */
.abstract-section {
  margin-block: var(--space-4);
}

.section-title {
  color: var(--color-indigo);
  font-size: var(--text-h5-size);
  font-weight: 700;
  margin-bottom: var(--space-2);
}

.abstract-card {
  padding: clamp(1.25rem, 3vw, 1.75rem);
  border-left: 4px solid var(--color-rosa-carmin);
  background: var(--color-superficie);
  border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
}

.abstract-card p {
  color: #252b42;
  font-size: 1.05rem;
  line-height: 1.7;
}

/* Keywords */
.keywords-bar {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.45rem;
  margin-block: var(--space-3);
}

.keywords-title {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  color: var(--color-violeta);
  font-size: var(--text-small-size);
  font-weight: 700;
}

.keyword-chip {
  padding: 0.2rem 0.6rem;
  border-radius: 999px;
  background: var(--color-superficie);
  border: 1px solid var(--color-desactivado);
  color: #424961;
  font-size: 0.78rem;
  font-weight: 600;
}

/* Download Action Box */
.download-action-box {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: var(--space-3);
  margin-block: var(--space-6);
  padding: clamp(1.25rem, 3vw, 2rem);
  border-radius: var(--radius-md);
  background: linear-gradient(135deg, #182042 0%, #293875 100%);
  color: #fff;
  box-shadow: 0 6px 22px rgba(24, 32, 66, 0.18);
}

.download-info h3 {
  color: #fff;
  font-size: var(--text-h6-size);
  font-weight: 700;
  margin-bottom: 0.25rem;
}

.download-info p {
  color: #ccd2e8;
  font-size: var(--text-small-size);
}

.download-btn {
  display: inline-flex;
  min-height: 2.75rem;
  align-items: center;
  gap: 0.5rem;
  padding: 0.55rem 1.35rem;
  border-radius: var(--radius-sm);
  background: var(--color-rosa-carmin);
  color: var(--color-blanco);
  font-size: var(--text-small-size);
  font-weight: 700;
  text-decoration: none;
  box-shadow: 0 4px 14px rgba(243, 0, 96, 0.35);
  transition: transform 0.15s ease, background-color 0.15s ease;
}

.download-btn:hover {
  background: #db0056;
  transform: translateY(-2px);
}

/* References */
.references-section {
  margin-block: var(--space-6);
  padding-top: var(--space-4);
  border-top: 1px solid var(--color-desactivado);
}

.references-card {
  padding: 1.25rem;
  border-radius: var(--radius-sm);
  background: var(--color-superficie);
  font-size: var(--text-small-size);
  line-height: 1.7;
  color: #495066;
  white-space: pre-line;
}

/* Related */
.related-section {
  margin-top: var(--space-6);
  padding-top: var(--space-4);
  border-top: 1px solid var(--color-desactivado);
}

.related-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  padding-left: 1.25rem;
}

.related-item a {
  color: var(--color-indigo);
  font-weight: 600;
  line-height: 1.45;
  text-decoration: none;
  transition: color 0.15s ease;
}

.related-item a:hover {
  color: var(--color-rosa-carmin);
}

/* Skeleton */
.article-skeleton {
  max-width: 76ch;
  margin-inline: auto;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  padding: 2rem 0;
}

.skeleton-pill {
  width: 8rem;
  height: 1.4rem;
  border-radius: 999px;
  background: #edeef5;
}

.skeleton-title {
  width: 88%;
  height: 3rem;
  border-radius: var(--radius-sm);
  background: #edeef5;
}

.skeleton-authors {
  width: 60%;
  height: 1.8rem;
  border-radius: var(--radius-sm);
  background: #edeef5;
}

.skeleton-box {
  width: 100%;
  height: 12rem;
  border-radius: var(--radius-md);
  background: #edeef5;
}

.article-detail__state {
  display: grid;
  min-height: 20rem;
  align-content: center;
  text-align: center;
}
</style>
