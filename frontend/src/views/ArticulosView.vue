<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { ArrowUpRight, Download, ExternalLink } from '@lucide/vue'
import ContentCard from '@/components/common/ContentCard.vue'
import EmptyState from '@/components/common/EmptyState.vue'
import LoadMoreButton from '@/components/common/LoadMoreButton.vue'
import SearchFilterBar from '@/components/common/SearchFilterBar.vue'
import SectionHeaderBand from '@/components/layout/SectionHeaderBand.vue'
import VarianzaSectionNav from '@/components/varianza/VarianzaSectionNav.vue'
import { articleCoverUrl, articleDownloadUrl, authorName, formatApiDate } from '@/api/normalizers'
import { mediaUrl } from '@/api/response'
import {
  EdicionesVarianzaRepository,
  EtiquetasArtRepository,
} from '@/api/repositories.js'
import type { ArticleApiItem, ArticleTagApiItem, VarianzaIssueApiItem } from '@/api/types'
import { unwrapCollection, unwrapResource } from '@/api/response'
import { useApiResource } from '@/composables/useApiResource'
import { usePaginatedResource } from '@/composables/usePaginatedResource'
import { useGsapReveal } from '@/composables/useGsapReveal'

const articlesRoot = ref<HTMLElement | null>(null)
useGsapReveal(articlesRoot, {
  selector: '.articles-reveal',
  stagger: 0.08,
  y: 18,
})

const route = useRoute()
const router = useRouter()
const sectionItems = [
  { value: 'sobre', label: 'Sobre la revista', to: '/revista-varianza' },
  { value: 'comite-editor', label: 'Comité Editor', to: '/revista-varianza/comite-editor' },
  { value: 'autores', label: 'Autores Varianza', to: '/revista-varianza/autores' },
  { value: 'ediciones', label: 'Todas las Ediciones', to: '/revista-varianza/ediciones' },
  { value: 'articulos', label: 'Artículos', to: '/revista-varianza/articulos' },
  { value: 'instrucciones-autores', label: 'Instrucciones para autores', to: '/revista-varianza/instrucciones-autores' },
]
const query = ref('')
const selectedIssue = ref(String(route.query.edicion ?? ''))
const selectedTag = ref('')
const filterValues = ref<Record<string, string>>({ year: '' })
const issuesResource = usePaginatedResource<VarianzaIssueApiItem>(
  (page) => EdicionesVarianzaRepository.list({ page, per_page: 100 }),
  { autoFetchAll: true },
)
const tags = ref<ArticleTagApiItem[]>([])
const latestIssue = ref<VarianzaIssueApiItem | null>(null)
const tagsLoading = ref(true)
const tagsError = ref(false)
const latestIssueLoading = ref(true)
const latestIssueError = ref(false)
const apiBaseUrl = import.meta.env.VITE_API_URL
const currentIssue = computed(() => selectedIssue.value)
const issueResource = useApiResource<VarianzaIssueApiItem>(
  currentIssue,
  (idOrSlug) => EdicionesVarianzaRepository.getBySlug(idOrSlug),
)
const issueOptions = computed(() => {
  const availableIssues = [...issuesResource.items.value]
  if (latestIssue.value && !availableIssues.some((issue) => issue.id === latestIssue.value?.id)) {
    availableIssues.unshift(latestIssue.value)
  }
  return availableIssues
})
const activeIssue = computed(() => issueResource.data.value)
const editionArticles = computed(() => activeIssue.value?.articulos ?? [])
const years = computed(() => [...new Set(editionArticles.value
  .map((article) => article.fecha_aceptado || article.fecha_recepcion)
  .filter((date): date is string => Boolean(date))
  .map((date) => new Date(date).getFullYear())
  .filter(Number.isFinite))]
  .sort((first, second) => second - first)
  .map((year) => ({ value: String(year), label: String(year) })))
const filters = computed(() => [{ key: 'year', label: 'Año', options: years.value }])

const visibleArticles = computed(() => {
  const term = query.value.trim().toLocaleLowerCase()
  return editionArticles.value.filter((article) => {
    const authorText = article.autores?.map(authorName).join(' ') ?? ''
    const tagText = article.etiquetas?.map((tag) => tag.nombre).join(' ') ?? ''
    const searchable = `${article.titulo} ${article.resumen ?? ''} ${article.abstracto ?? ''} ${article.doi ?? ''} ${authorText} ${tagText}`
    const matchesSearch = !term || searchable.toLocaleLowerCase().includes(term)
    const articleYear = new Date(article.fecha_aceptado || article.fecha_recepcion || '').getFullYear()
    const matchesYear = !filterValues.value.year || String(articleYear) === filterValues.value.year
    const matchesTag = !selectedTag.value || article.etiquetas?.some((tag) => String(tag.id) === selectedTag.value || tag.slug === selectedTag.value)
    return matchesSearch && matchesYear && matchesTag
  })
})

function articlePath(article: ArticleApiItem) {
  return `/revista-varianza/articulos/${article.id}`
}

async function loadTags() {
  tagsLoading.value = true
  tagsError.value = false
  try {
    tags.value = unwrapCollection<ArticleTagApiItem>(await EtiquetasArtRepository.list({ page: 1 })).data
  } catch {
    tagsError.value = true
  } finally {
    tagsLoading.value = false
  }
}

async function loadLatestIssue() {
  latestIssueLoading.value = true
  latestIssueError.value = false
  try {
    latestIssue.value = unwrapResource<VarianzaIssueApiItem>(await EdicionesVarianzaRepository.getLatest())
  } catch {
    latestIssueError.value = true
  } finally {
    latestIssueLoading.value = false
  }
}

watch(selectedIssue, (issue) => {
  if (issue) void router.replace({ query: { ...route.query, edicion: issue } })
  else void router.replace({ query: { ...route.query, edicion: undefined } })
})

watch(() => issuesResource.items.value, (availableIssues) => {
  const issueByNumber = availableIssues.find((issue) => issue.numero === selectedIssue.value)
  if (issueByNumber) selectedIssue.value = issueByNumber.slug || String(issueByNumber.id)
})

onMounted(async () => {
  await Promise.all([issuesResource.refresh(), loadTags(), loadLatestIssue()])
  if (!selectedIssue.value) {
    const firstAvailable = latestIssue.value ?? issuesResource.items.value[0]
    selectedIssue.value = firstAvailable?.slug || (firstAvailable ? String(firstAvailable.id) : '')
  }
})
</script>

<template>
  <div ref="articlesRoot" class="articles-page">
    <SectionHeaderBand
      title="Artículos de Varianza"
      subtitle="Explora las publicaciones científicas vinculadas a cada edición de la revista."
    />
    <VarianzaSectionNav active="articulos" :items="sectionItems" />

    <main class="container view-body articles-page__main">
      <RouterLink class="articles-page__back articles-reveal" to="/revista-varianza/ediciones">Volver a Revista Varianza</RouterLink>

      <section v-if="activeIssue" class="articles-edition articles-reveal" aria-labelledby="articles-edition-title">
        <img v-if="mediaUrl(activeIssue.portada_url)" :src="mediaUrl(activeIssue.portada_url)" :alt="`Portada de ${activeIssue.titulo}`" />
        <span v-else class="articles-edition__cover-placeholder" aria-hidden="true">VARIANZA<br />N° {{ activeIssue.numero }}</span>
        <div>
          <p>Edición seleccionada</p>
          <h2 id="articles-edition-title">{{ activeIssue.titulo }}</h2>
          <span>{{ [activeIssue.mes, activeIssue.anio].filter(Boolean).join(' · ') }}</span>
          <RouterLink :to="`/revista-varianza/ediciones/${encodeURIComponent(activeIssue.slug || String(activeIssue.id))}`">Ver ficha de edición <ArrowUpRight :size="16" /></RouterLink>
        </div>
        <label class="articles-edition__select">
          <span>Filtrar por edición</span>
          <select v-model="selectedIssue" aria-label="Filtrar artículos por edición">
            <option v-for="issue in issueOptions" :key="issue.id" :value="issue.slug || String(issue.id)">
              N° {{ issue.numero }} · {{ issue.anio }}
            </option>
          </select>
        </label>
      </section>
      <div v-else-if="issuesResource.loading.value || latestIssueLoading" class="articles-page__state" role="status">
        Cargando ediciones de Varianza…
      </div>
      <EmptyState v-else-if="issuesResource.error.value && !issueOptions.length" title="No se pudieron cargar las ediciones" action-label="Reintentar" @action="issuesResource.refresh" />

      <header class="articles-page__heading">
        <div>
          <p>Catálogo científico</p>
          <h2>Artículos publicados</h2>
        </div>
        <span>{{ editionArticles.length }} artículos en esta edición</span>
      </header>

      <SearchFilterBar
        v-model="query"
        v-model:filter-values="filterValues"
        :filters="filters"
        :no-results="query.trim().length > 0 && visibleArticles.length === 0"
      />
      <label class="articles-tag-filter">
        <span>Etiqueta temática</span>
        <select v-model="selectedTag" aria-label="Filtrar por etiqueta temática">
          <option value="">Todas las etiquetas</option>
          <option v-for="tag in tags" :key="tag.id" :value="String(tag.id)">{{ tag.nombre }}</option>
        </select>
      </label>
      <p v-if="tagsLoading" class="articles-tag-filter__hint" role="status">Cargando etiquetas…</p>
      <p v-else-if="tagsError" class="articles-tag-filter__hint" role="status">No se pudieron cargar las etiquetas temáticas.</p>

      <div v-if="issueResource.loading.value" class="articles-page__state" role="status">
        Cargando artículos…
      </div>
      <div v-else-if="issueResource.error.value" class="articles-page__state" role="alert">
        <EmptyState
          title="No se pudieron cargar los artículos"
          description="No fue posible abrir la edición seleccionada."
          action-label="Reintentar"
          @action="issueResource.reload"
        />
      </div>
      <template v-else>
        <div v-if="visibleArticles.length" class="articles-grid">
          <div v-for="article in visibleArticles" :key="article.id" class="article-card-wrap">
            <ContentCard
              type="edicion"
              :title="article.titulo"
              :eyebrow="article.doi || 'Investigación original'"
              :summary="article.resumen || article.abstracto || undefined"
              :date="formatApiDate(article.fecha_aceptado || article.fecha_recepcion)"
              :author="article.autores?.map(authorName).join(', ')"
              :image="articleCoverUrl(article, activeIssue?.portada_url)"
              :image-alt="article.titulo"
              :to="articlePath(article)"
            />
            <div v-if="articleDownloadUrl(article)" class="article-card-actions">
              <a
                :href="articleDownloadUrl(article)!"
                class="btn-article-download"
                target="_blank"
                rel="noopener noreferrer"
              >
                <Download :size="14" aria-hidden="true" />
                <span>Descargar / Drive</span>
                <ExternalLink :size="12" aria-hidden="true" />
              </a>
            </div>
          </div>
        </div>
        <EmptyState
          v-else-if="!issueResource.loading.value && !query.trim() && !filterValues.year && !selectedTag"
          title="No hay artículos para esta edición"
          description="Esta edición no tiene artículos publicados en el sistema editorial."
        />
      </template>
    </main>
  </div>
</template>

<style scoped>
.articles-page {
  background: #f5f5f8;
}

.articles-page__main {
  min-height: 45vh;
  padding-block: var(--space-3) var(--space-8);
}

.articles-page__back {
  display: inline-flex;
  min-height: 2.75rem;
  align-items: center;
  margin-bottom: var(--space-2);
  color: #202e71;
  font-weight: 700;
}

.articles-edition {
  display: grid;
  grid-template-columns: 6rem minmax(0, 1fr) minmax(12rem, 0.55fr);
  align-items: center;
  gap: var(--space-2);
  margin-bottom: var(--space-4);
  padding: var(--space-2);
  background: #202e71;
  color: #fff;
}

.articles-edition > img {
  width: 100%;
  aspect-ratio: 0.74;
  object-fit: cover;
}

.articles-edition > div > p,
.articles-page__heading > div > p {
  color: #f6c7df;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
}

.articles-edition h2 {
  margin-block: 0.2rem;
  color: #fff;
  font-size: 1.4rem;
}

.articles-edition > div > span {
  display: block;
  color: rgb(255 255 255 / 78%);
  font-size: var(--text-small-size);
}

.articles-edition > div > a {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  margin-top: 0.5rem;
  color: #ff8fca;
  font-size: 0.8125rem;
  font-weight: 700;
}

.articles-edition__select {
  display: grid;
  gap: 0.35rem;
  color: #fff;
  font-size: 0.8125rem;
  font-weight: 700;
}

.articles-edition__select select {
  width: 100%;
  min-height: 2.75rem;
  padding-inline: 0.7rem;
  border: 1px solid rgb(255 255 255 / 55%);
  border-radius: var(--radius-sm);
  background: #fff;
  color: #202e71;
  font: inherit;
}

.articles-page__heading {
  display: flex;
  align-items: end;
  justify-content: space-between;
  gap: var(--space-2);
  margin-bottom: var(--space-2);
}

.articles-page__heading h2 {
  color: #202e71;
  font-size: var(--text-h4-size);
}

.articles-page__heading > div > p {
  color: #f50088;
}

.articles-page__heading > span {
  color: #545767;
  font-size: var(--text-small-size);
}

.articles-tag-filter {
  display: grid;
  width: min(100%, 22rem);
  gap: 0.35rem;
  margin-top: var(--space-2);
  color: #202e71;
  font-size: var(--text-small-size);
  font-weight: 700;
}

.articles-tag-filter select {
  width: 100%;
  min-height: 2.75rem;
  padding-inline: 0.7rem;
  border: 1px solid #aab3d0;
  border-radius: var(--radius-sm);
  background: #fff;
  color: #202e71;
  font: inherit;
}

.articles-tag-filter__hint {
  margin-top: 0.35rem;
  color: #5c6070;
  font-size: var(--text-small-size);
}

.articles-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: var(--space-2);
  margin-top: var(--space-3);
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

.articles-page__state {
  display: grid;
  min-height: 16rem;
  align-content: center;
  text-align: center;
}

@media (max-width: 800px) {
  .articles-edition {
    grid-template-columns: 5rem minmax(0, 1fr);
  }

  .articles-edition__select {
    grid-column: 1 / -1;
  }

  .articles-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 560px) {
  .articles-grid {
    grid-template-columns: 1fr;
  }

  .articles-page__heading {
    align-items: flex-start;
    flex-direction: column;
  }
}
</style>
