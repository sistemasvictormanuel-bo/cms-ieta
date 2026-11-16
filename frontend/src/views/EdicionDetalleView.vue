<script setup lang="ts">
import { computed, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import {
  ArrowLeft,
  BookOpen,
  Calendar,
  Download,
  ExternalLink,
  FileText,
  Hash,
  Layers,
} from '@lucide/vue'
import ContentCard from '@/components/common/ContentCard.vue'
import EditorJsContent from '@/components/common/EditorJsContent.vue'
import EmptyState from '@/components/common/EmptyState.vue'
import SectionHeaderBand from '@/components/layout/SectionHeaderBand.vue'
import { articleCoverUrl, articleDownloadUrl, authorName, formatApiDate } from '@/api/normalizers'
import { mediaUrl } from '@/api/response'
import { EdicionesVarianzaRepository } from '@/api/repositories.js'
import type { VarianzaIssueApiItem } from '@/api/types'
import { useApiResource } from '@/composables/useApiResource'
import { useGsapReveal } from '@/composables/useGsapReveal'

const route = useRoute()
const issueRoot = ref<HTMLElement | null>(null)
const issueIdOrSlug = computed(() => String(route.params.numero ?? ''))

const { data: issue, loading, error, reload } = useApiResource<VarianzaIssueApiItem>(
  issueIdOrSlug,
  (idOrSlug) => EdicionesVarianzaRepository.getBySlug(idOrSlug),
)

const cover = computed(() => mediaUrl(issue.value?.portada_url))
const articles = computed(() => issue.value?.articulos ?? [])
const apiBaseUrl = import.meta.env.VITE_API_URL

useGsapReveal(issueRoot, {
  selector: '.issue-reveal',
  stagger: 0.08,
  y: 18,
})

function articlePath(article: { id: number; slug?: string | null }) {
  return `/revista-varianza/articulos/${article.id}`
}
</script>

<template>
  <div ref="issueRoot" class="issue-detail-page">
    <SectionHeaderBand
      :title="issue?.titulo || 'Edición de Revista Varianza'"
      :subtitle="issue ? [issue.mes, issue.anio].filter(Boolean).join(' · ') : 'Publicación científica indexada del IETA'"
    />

    <main class="container view-body issue-detail">
      <!-- Back Navigation -->
      <nav class="issue-detail__nav issue-reveal" aria-label="Navegación de retorno">
        <RouterLink class="issue-detail__back" to="/revista-varianza/ediciones">
          <ArrowLeft :size="16" aria-hidden="true" />
          <span>Volver a Ediciones de Varianza</span>
        </RouterLink>
      </nav>

      <!-- Loading State -->
      <div v-if="loading" class="issue-skeleton" role="status" aria-label="Cargando edición">
        <div class="skeleton-hero"></div>
        <div class="skeleton-articles"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="issue-detail__state" role="alert">
        <EmptyState
          title="No se pudo cargar la edición"
          description="Comprueba el enlace o inténtalo de nuevo."
          action-label="Reintentar"
          @action="reload"
        />
      </div>

      <!-- Not Found -->
      <EmptyState v-else-if="!issue" title="No se encontró la edición solicitada" />

      <!-- Loaded Content -->
      <article v-else class="issue-article">
        <!-- Hero Section -->
        <section class="issue-hero issue-reveal">
          <div class="issue-hero__media">
            <img
              v-if="cover"
              class="issue-cover-img"
              :src="cover"
              :alt="`Portada de ${issue.titulo}`"
            />
            <div v-else class="issue-cover-placeholder">
              <BookOpen :size="48" aria-hidden="true" />
              <span class="placeholder-vol">VARIANZA</span>
              <span class="placeholder-num">N° {{ issue.numero }}</span>
            </div>
          </div>

          <div class="issue-hero__info">
            <div class="issue-badge-row">
              <span class="issue-period-badge">
                {{ [issue.mes, issue.anio].filter(Boolean).join(' · ') }}
              </span>
              <span v-if="issue.volumen" class="issue-vol-badge">
                Volumen {{ issue.volumen }}
              </span>
            </div>

            <h1 class="issue-title">{{ issue.titulo }}</h1>

            <p v-if="issue.editorial" class="issue-editorial-lead">
              {{ issue.editorial }}
            </p>

            <!-- Technical Metadata Grid -->
            <dl class="issue-specs-grid">
              <div v-if="issue.fecha_publicacion" class="spec-item">
                <dt>Publicación</dt>
                <dd>{{ formatApiDate(issue.fecha_publicacion) }}</dd>
              </div>
              <div v-if="issue.num_paginas" class="spec-item">
                <dt>Páginas</dt>
                <dd>{{ issue.num_paginas }} págs.</dd>
              </div>
              <div v-if="issue.doi" class="spec-item">
                <dt>DOI</dt>
                <dd class="doi-text">{{ issue.doi }}</dd>
              </div>
              <div v-if="issue.issn_impresa" class="spec-item">
                <dt>ISSN Impreso</dt>
                <dd>{{ issue.issn_impresa }}</dd>
              </div>
              <div v-if="issue.issn_electronica" class="spec-item">
                <dt>ISSN Digital</dt>
                <dd>{{ issue.issn_electronica }}</dd>
              </div>
            </dl>

            <div v-if="mediaUrl(issue.archivo_url)" class="issue-actions">
              <a
                class="download-issue-btn"
                :href="mediaUrl(issue.archivo_url)"
                target="_blank"
                rel="noreferrer"
              >
                <Download :size="16" aria-hidden="true" />
                <span>Descargar Edición Completa (PDF)</span>
              </a>
            </div>
          </div>
        </section>

        <!-- Presentation Section -->
        <section v-if="issue.presentacion" class="issue-presentation issue-reveal">
          <div class="presentation-box">
            <h2 class="section-title">Presentación Editorial</h2>
            <EditorJsContent :content="issue.presentacion" :api-base-url="apiBaseUrl" />
          </div>
        </section>

        <!-- Articles Table of Contents -->
        <section class="issue-articles-section issue-reveal" aria-labelledby="issue-articles-heading">
          <header class="articles-header">
            <p class="articles-eyebrow">SUMARIO Y CONTENIDO DEL NÚMERO</p>
            <h2 id="issue-articles-heading" class="articles-title">Artículos de Investigación</h2>
            <p class="articles-lead">
              {{ articles.length }} artículo{{ articles.length === 1 ? '' : 's' }} publicado{{ articles.length === 1 ? '' : 's' }} en este volumen.
            </p>
          </header>

          <div v-if="articles.length" class="articles-grid">
            <div v-for="article in articles" :key="article.id" class="article-card-wrap">
              <ContentCard
                type="edicion"
                :title="article.titulo"
                :eyebrow="article.tipo_articulo?.nombre || article.doi || 'Artículo original'"
                :summary="article.resumen || article.abstracto || undefined"
                :date="formatApiDate(article.fecha_aceptado || article.fecha_recepcion)"
                :author="article.autores?.map(authorName).join(', ')"
                :image="articleCoverUrl(article, issue.portada_url) || undefined"
                :image-alt="`Portada del artículo ${article.titulo}`"
                :to="articlePath(article)"
              />
              <div v-if="articleDownloadUrl(article)" class="article-card-actions">
                <a
                  :href="articleDownloadUrl(article)!"
                  class="btn-article-download"
                  target="_blank"
                  rel="noopener noreferrer"
                  :title="`Descargar o ver en Drive ${article.titulo}`"
                >
                  <Download :size="14" aria-hidden="true" />
                  <span>Descargar / Drive</span>
                  <ExternalLink :size="12" aria-hidden="true" />
                </a>
              </div>
            </div>
          </div>

          <EmptyState
            v-else
            title="Esta edición aún no tiene artículos listados"
            description="Los artículos de este número se encuentran en proceso de carga editorial."
          />
        </section>
      </article>
    </main>
  </div>
</template>

<style scoped>
.issue-detail-page {
  background: var(--color-blanco);
}

.issue-detail {
  min-height: 48vh;
  padding-block: var(--space-4) var(--space-8);
}

.issue-detail__nav {
  margin-bottom: var(--space-4);
}

.issue-detail__back {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  color: var(--color-indigo);
  font-size: var(--text-small-size);
  font-weight: 700;
  text-decoration: none;
  transition: transform 0.15s ease, color 0.15s ease;
}

.issue-detail__back:hover {
  transform: translateX(-4px);
  color: var(--color-rosa-carmin);
}

/* Hero Section */
.issue-hero {
  display: grid;
  grid-template-columns: minmax(13rem, 18rem) minmax(0, 1fr);
  gap: clamp(1.5rem, 4vw, 3rem);
  padding: clamp(1.5rem, 3.5vw, 2.5rem);
  border: 1px solid var(--color-desactivado);
  border-radius: var(--radius-md);
  background: var(--color-superficie);
  box-shadow: 0 4px 20px rgba(20, 22, 28, 0.05);
  margin-bottom: var(--space-6);
  align-items: start;
}

.issue-hero__media {
  overflow: hidden;
  border-radius: var(--radius-md);
  box-shadow: 0 8px 24px -4px rgba(20, 22, 28, 0.18);
  border: 1px solid rgba(20, 22, 28, 0.08);
}

.issue-cover-img {
  width: 100%;
  aspect-ratio: 1 / 1.414;
  object-fit: cover;
  display: block;
}

.issue-cover-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  aspect-ratio: 1 / 1.414;
  background: linear-gradient(135deg, #182038 0%, #2f3b6d 60%, #465593 100%);
  color: #fff;
}

.placeholder-vol {
  font-size: 0.85rem;
  font-weight: 800;
  letter-spacing: 0.08em;
}

.placeholder-num {
  font-size: 1.15rem;
  font-weight: 700;
  color: #ffd4e5;
}

.issue-hero__info {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
}

.issue-badge-row {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.issue-period-badge {
  display: inline-block;
  padding: 0.2rem 0.65rem;
  border-radius: 999px;
  background: rgba(243, 0, 96, 0.1);
  color: var(--color-rosa-carmin);
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.03em;
  text-transform: uppercase;
}

.issue-vol-badge {
  display: inline-block;
  padding: 0.2rem 0.65rem;
  border-radius: 999px;
  background: rgba(53, 68, 159, 0.1);
  color: var(--color-indigo);
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.03em;
  text-transform: uppercase;
}

.issue-title {
  color: var(--color-indigo);
  font-size: clamp(1.85rem, 4vw, 2.5rem);
  font-weight: 700;
  line-height: 1.2;
}

.issue-editorial-lead {
  color: #3b4258;
  font-size: var(--text-body-size);
  line-height: 1.6;
}

/* Specs */
.issue-specs-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(9rem, 1fr));
  gap: 0.75rem;
  margin-block: var(--space-2);
  padding: 1rem;
  border-radius: var(--radius-sm);
  background: var(--color-blanco);
  border: 1px solid var(--color-desactivado);
}

.spec-item {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
}

.spec-item dt {
  color: var(--color-violeta);
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.spec-item dd {
  color: var(--color-tinta);
  font-size: var(--text-small-size);
  font-weight: 600;
  font-variant-numeric: tabular-nums;
}

.doi-text {
  word-break: break-all;
}

.issue-actions {
  margin-top: var(--space-2);
}

.download-issue-btn {
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
  box-shadow: 0 4px 14px rgba(243, 0, 96, 0.28);
  transition: transform 0.15s ease, background-color 0.15s ease;
}

.download-issue-btn:hover {
  background: #db0056;
  transform: translateY(-2px);
}

/* Presentation Box */
.issue-presentation {
  margin-bottom: var(--space-6);
}

.presentation-box {
  padding: clamp(1.5rem, 3vw, 2.25rem);
  border: 1px solid var(--color-desactivado);
  border-radius: var(--radius-md);
  background: var(--color-blanco);
  box-shadow: 0 4px 16px rgba(20, 22, 28, 0.04);
}

.section-title {
  color: var(--color-indigo);
  font-size: var(--text-h4-size);
  font-weight: 700;
  margin-bottom: var(--space-3);
}

/* Articles Section */
.issue-articles-section {
  margin-top: var(--space-6);
}

.articles-header {
  margin-bottom: var(--space-4);
}

.articles-eyebrow {
  color: var(--color-rosa-carmin);
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.05em;
  margin-bottom: 0.25rem;
}

.articles-title {
  color: var(--color-indigo);
  font-size: clamp(1.6rem, 3.5vw, 2.25rem);
  font-weight: 700;
  line-height: 1.22;
}

.articles-lead {
  color: #555c73;
  font-size: var(--text-large-size);
  margin-top: 0.35rem;
}

.articles-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: clamp(1rem, 2.5vw, 1.75rem);
}

.article-card-wrap {
  display: flex;
}

.article-card-wrap {
  display: flex;
  flex-direction: column;
  height: 100%;
}

.article-card-wrap :deep(.content-card) {
  width: 100%;
  flex: 1;
}

.article-card-actions {
  margin-top: 0.5rem;
}

.btn-article-download {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.45rem;
  width: 100%;
  padding: 0.5rem 0.85rem;
  border-radius: var(--radius-sm);
  background: rgba(243, 0, 96, 0.08);
  color: var(--color-rosa-carmin);
  border: 1px solid rgba(243, 0, 96, 0.25);
  font-size: 0.82rem;
  font-weight: 700;
  text-decoration: none;
  transition: all 0.2s ease;
}

.btn-article-download:hover {
  background: var(--color-rosa-carmin);
  color: #ffffff;
  border-color: var(--color-rosa-carmin);
  box-shadow: 0 4px 12px rgba(243, 0, 96, 0.25);
}

/* Skeleton */
.issue-skeleton {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.skeleton-hero {
  height: 22rem;
  border-radius: var(--radius-md);
  background: #edeef5;
}

.skeleton-articles {
  height: 24rem;
  border-radius: var(--radius-md);
  background: #edeef5;
}

.issue-detail__state {
  display: grid;
  min-height: 20rem;
  align-content: center;
  text-align: center;
}

@media (max-width: 960px) {
  .articles-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 768px) {
  .issue-hero {
    grid-template-columns: 1fr;
  }
  .issue-hero__media {
    max-width: 16rem;
    margin-inline: auto;
  }
  .articles-grid {
    grid-template-columns: 1fr;
  }
}
</style>
