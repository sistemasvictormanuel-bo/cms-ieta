<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import {
  ArrowLeft,
  CalendarDays,
  Clock,
  Copy,
  Check,
  Share2,
  User,
} from '@lucide/vue'
import EditorJsContent from '@/components/common/EditorJsContent.vue'
import EmptyState from '@/components/common/EmptyState.vue'
import SectionHeaderBand from '@/components/layout/SectionHeaderBand.vue'
import {
  formatApiDate,
  newsContent,
  newsDate,
  newsSummary,
  newsTitle,
} from '@/api/normalizers'
import { mediaUrl, unwrapResource } from '@/api/response'
import { NoticiasRepository } from '@/api/repositories.js'
import type { NewsApiItem } from '@/api/types'
import { useGsapReveal } from '@/composables/useGsapReveal'

const route = useRoute()
const articleRoot = ref<HTMLElement | null>(null)
const article = ref<NewsApiItem | null>(null)
const loading = ref(true)
const error = ref(false)
const copied = ref(false)
const apiBaseUrl = import.meta.env.VITE_API_URL

const newsId = computed(() => String(route.params.slug ?? ''))
const title = computed(() => newsTitle(article.value ?? { id: 0 }) || 'Noticia')
const image = computed(() => mediaUrl(article.value?.portada_url))
const summary = computed(() => (article.value ? newsSummary(article.value) : ''))
const rawContent = computed(() => (article.value ? newsContent(article.value) : null))

// Calculate estimated reading time
const readingTimeMinutes = computed(() => {
  if (!rawContent.value) return 2
  const text = JSON.stringify(rawContent.value)
  const words = text.split(/\s+/).length
  return Math.max(1, Math.ceil(words / 180))
})

useGsapReveal(articleRoot, {
  selector: '.article-reveal',
  stagger: 0.1,
  y: 18,
})

let requestSequence = 0

async function loadNews(id: string) {
  const sequence = ++requestSequence
  article.value = null
  error.value = false
  loading.value = true

  if (!/^\d+$/.test(id)) {
    error.value = true
    loading.value = false
    return
  }

  try {
    const response = await NoticiasRepository.getById(id)
    const result = unwrapResource<NewsApiItem>(response)
    if (sequence === requestSequence) article.value = result
  } catch {
    if (sequence === requestSequence) error.value = true
  } finally {
    if (sequence === requestSequence) loading.value = false
  }
}

async function copyArticleLink() {
  try {
    await navigator.clipboard.writeText(window.location.href)
    copied.value = true
    setTimeout(() => {
      copied.value = false
    }, 2400)
  } catch {
    // Clipboard fallback
  }
}

watch(newsId, loadNews, { immediate: true })
</script>

<template>
  <div ref="articleRoot">
    <SectionHeaderBand
      :title="title"
      :subtitle="article ? `${article.persona?.nombre_completo ? 'Autor: ' + article.persona.nombre_completo + '  ·  ' : ''}${formatApiDate(newsDate(article))}` : 'Instituto de Estadística Teórica y Aplicada'"
      :image="image"
      image-alt="Portada de la noticia"
    />

    <main class="container view-body">
      <!-- Back Navigation Bar -->
      <nav class="article-nav article-reveal" aria-label="Navegación de retorno">
        <RouterLink class="back-link" to="/noticias">
          <ArrowLeft :size="16" aria-hidden="true" />
          <span>Volver a Noticias</span>
        </RouterLink>
      </nav>

      <!-- Loading State -->
      <div v-if="loading" class="article-skeleton" role="status" aria-label="Cargando artículo">
        <div class="skeleton-pill"></div>
        <div class="skeleton-title"></div>
        <div class="skeleton-meta"></div>
        <div class="skeleton-cover"></div>
        <div class="skeleton-paragraph"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="content-state" role="alert">
        <EmptyState
          title="No se pudo cargar la noticia"
          description="Comprueba el identificador o vuelve al listado de noticias."
          action-label="Ir a Noticias"
          @action="$router.push('/noticias')"
        />
      </div>

      <!-- Article Content -->
      <article v-else-if="article" class="news-article">
        <!-- Article Header Meta -->
        <header class="article-header article-reveal">
          <div class="article-meta-tags">
            <span class="meta-tag meta-tag--category">Actualidad IETA</span>
            <span class="meta-reading-time">
              <Clock :size="14" aria-hidden="true" />
              <span>{{ readingTimeMinutes }} min de lectura</span>
            </span>
          </div>

          <h1 class="article-title">{{ title }}</h1>

          <div class="article-author-bar">
            <div class="author-avatar" aria-hidden="true">
              <User :size="18" />
            </div>
            <div class="author-details">
              <p class="author-name">
                {{ article.persona?.nombre_completo || 'Redacción IETA' }}
              </p>
              <div class="author-meta">
                <span class="meta-date">
                  <CalendarDays :size="13" aria-hidden="true" />
                  <span>Publicado el {{ formatApiDate(newsDate(article)) }}</span>
                </span>
              </div>
            </div>
          </div>
        </header>

        <!-- Cover Image -->
        <figure v-if="image" class="article-cover-figure article-reveal">
          <img class="article-cover" :src="image" :alt="title" />
          <figcaption class="article-cover-caption">
            Foto: Archivo y Divulgación IETA
          </figcaption>
        </figure>

        <!-- Lead Summary -->
        <div v-if="summary" class="article-lead article-reveal">
          <p>{{ summary }}</p>
        </div>

        <!-- Body Content -->
        <section class="article-content-body article-reveal">
          <EditorJsContent
            v-if="rawContent"
            class="article-content"
            :content="rawContent"
            :api-base-url="apiBaseUrl"
          />
          <EmptyState
            v-else
            title="Esta noticia no tiene contenido publicado"
            description="El contenido completo de esta nota estará disponible próximamente."
          />
        </section>

        <!-- Share and Return Footer -->
        <footer class="article-footer article-reveal">
          <div class="share-box">

            <button
              type="button"
              class="copy-link-btn"
              :class="{ 'copy-link-btn--copied': copied }"
              @click="copyArticleLink"
            >              <span class="share-box__label">
              <Share2 :size="16" aria-hidden="true"/>

            Compartir esta noticia</span></button>
          </div>

          <RouterLink class="return-btn" to="/noticias">
            <ArrowLeft :size="16" aria-hidden="true" />
            <span>Ver más noticias</span>
          </RouterLink>
        </footer>
      </article>

      <EmptyState v-else title="No se encontró la noticia" />
    </main>
  </div>
</template>

<style scoped>
.article-nav {
  margin-bottom: var(--space-3);
}

.back-link {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--color-indigo);
  font-size: var(--text-small-size);
  font-weight: 700;
  text-decoration: none;
  transition: transform 0.15s ease, color 0.15s ease;
}

.back-link:hover {
  transform: translateX(-4px);
  color: var(--color-rosa-carmin);
}

/* Article Container */
.news-article {
  max-width: 74ch;
  margin-inline: auto;
}

/* Header Meta */
.article-header {
  margin-bottom: var(--space-4);
}

.article-meta-tags {
  display: flex;
  align-items: center;
  gap: var(--space-2);
  margin-bottom: var(--space-2);
}

.meta-tag {
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

.meta-reading-time {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  color: #636b83;
  font-size: var(--text-small-size);
}

.article-title {
  color: var(--color-indigo);
  font-size: clamp(1.85rem, 4.5vw, 2.75rem);
  font-weight: 700;
  line-height: 1.2;
  margin-bottom: var(--space-3);
}

.article-author-bar {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  padding-block: var(--space-2);
  border-block: 1px solid var(--color-desactivado);
}

.author-avatar {
  display: grid;
  width: 2.75rem;
  height: 2.75rem;
  place-items: center;
  border-radius: 50%;
  background: #edeef7;
  color: var(--color-indigo);
  flex-shrink: 0;
}

.author-details {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
}

.author-name {
  color: var(--color-tinta);
  font-size: var(--text-body-size);
  font-weight: 700;
}

.author-meta {
  display: flex;
  align-items: center;
  gap: var(--space-2);
  color: #636b83;
  font-size: var(--text-small-size);
}

.meta-date {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
}

/* Cover Image */
.article-cover-figure {
  margin-block: var(--space-4);
}

.article-cover {
  width: 100%;
  max-height: 32rem;
  border-radius: var(--radius-md);
  object-fit: cover;
  box-shadow: 0 4px 18px rgba(20, 22, 28, 0.08);
}

.article-cover-caption {
  margin-top: 0.5rem;
  color: #71788d;
  font-size: 0.8rem;
  text-align: right;
  font-style: italic;
}

/* Lead Summary */
.article-lead {
  margin-block: var(--space-4);
  padding: var(--space-2) var(--space-3);
  border-left: 4px solid var(--color-rosa-carmin);
  background: var(--color-superficie);
  border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
}

.article-lead p {
  color: #1f2544;
  font-size: var(--text-large-size);
  line-height: var(--text-large-lh);
  font-weight: 500;
}

/* Content Body */
.article-content-body {
  margin-block: var(--space-4) var(--space-6);
  line-height: 1.75;
}

.article-content {
  font-size: 1.0625rem;
}

.article-content :deep(p) {
  margin-bottom: 1.25rem;
}

.article-content :deep(h2),
.article-content :deep(h3) {
  color: var(--color-indigo);
  margin-top: 2rem;
  margin-bottom: 0.75rem;
}

/* Article Footer */
.article-footer {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: var(--space-3);
  margin-top: var(--space-6);
  padding-top: var(--space-4);
  border-top: 2px solid var(--color-desactivado);
}

.share-box {
  display: flex;
  align-items: center;
  gap: var(--space-2);
}

.share-box__label {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  color: #555c73;
  font-size: var(--text-small-size);
  font-weight: 600;
}

.copy-link-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.45rem 0.9rem;
  border: 1px solid var(--color-desactivado);
  border-radius: var(--radius-sm);
  background: var(--color-blanco);
  color: var(--color-tinta);
  font: inherit;
  font-size: var(--text-small-size);
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s ease;
}

.copy-link-btn:hover {
  background: var(--color-superficie);
  border-color: #bcc0d1;
}

.copy-link-btn--copied {
  background: #e8f9ed;
  border-color: #6ed18f;
  color: #167a36;
}

.return-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  padding: 0.5rem 1.15rem;
  border-radius: var(--radius-sm);
  background: var(--color-indigo);
  color: var(--color-blanco);
  font-size: var(--text-small-size);
  font-weight: 700;
  text-decoration: none;
  transition: background-color 0.15s ease, transform 0.15s ease;
}

.return-btn:hover {
  background: #253381;
  transform: translateX(-2px);
}

/* Skeleton State */
.article-skeleton {
  max-width: 74ch;
  margin-inline: auto;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  padding-block: var(--space-4);
}

.skeleton-pill {
  width: 8rem;
  height: 1.5rem;
  border-radius: 999px;
  background: #edeef5;
}

.skeleton-title {
  width: 95%;
  height: 3rem;
  border-radius: var(--radius-sm);
  background: #edeef5;
}

.skeleton-meta {
  width: 50%;
  height: 2rem;
  border-radius: var(--radius-sm);
  background: #edeef5;
}

.skeleton-cover {
  width: 100%;
  height: 20rem;
  border-radius: var(--radius-md);
  background: #edeef5;
}

.skeleton-paragraph {
  width: 100%;
  height: 8rem;
  border-radius: var(--radius-sm);
  background: #edeef5;
}

.content-state {
  display: grid;
  min-height: 20rem;
  align-content: center;
  text-align: center;
}

@media (max-width: 600px) {
  .article-footer {
    flex-direction: column;
    align-items: stretch;
  }
  .share-box {
    justify-content: space-between;
  }
  .return-btn {
    justify-content: center;
  }
}
</style>
