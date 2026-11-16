<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { Newspaper } from '@lucide/vue'
import EmptyState from '@/components/common/EmptyState.vue'
import LoadMoreButton from '@/components/common/LoadMoreButton.vue'
import SearchFilterBar from '@/components/common/SearchFilterBar.vue'
import SectionHeaderBand from '@/components/layout/SectionHeaderBand.vue'
import {
  formatApiDate,
  newsDate,
  newsIsFeatured,
  newsIsPublished,
  newsSummary,
  newsTitle,
} from '@/api/normalizers'
import { mediaUrl } from '@/api/response'
import { NoticiasRepository } from '@/api/repositories.js'
import type { NewsApiItem } from '@/api/types'
import { useI18n } from 'vue-i18n'
import { usePaginatedResource } from '@/composables/usePaginatedResource'
import { useGsapReveal } from '@/composables/useGsapReveal'

const { t } = useI18n()
const query = ref('')
const filterValues = ref<Record<string, string>>({ type: '', year: '' })
const sortBy = ref<'recent' | 'oldest' | 'title'>('recent')

const newsRoot = ref<HTMLElement | null>(null)
const resource = usePaginatedResource<NewsApiItem>(
  (page) => NoticiasRepository.list({ page, per_page: 100 }),
  { autoFetchAll: true },
)

// Default news fallback when API is offline
const defaultNews: NewsApiItem[] = [
  {
    id: 1,
    titulo: 'IETA presenta nuevos modelos estadísticos para la estimación de pobreza multidimensional',
    descripcion: 'Investigadores del IETA desarrollaron metodologías de inferencia y modelos lineales mixtos aplicados a encuestas periódicas en Bolivia.',
    contenido: 'El Instituto de Estadística Teórica y Aplicada (IETA) presentó los avances de su programa de investigación cuantitativa.',
    fecha: '2025-10-14',
    portada_url: '',
    destacado: true,
    publicado: true,
    persona: { id: 1, nombre_completo: 'Lic. Esp. Raúl León Delgado' },
  },
  {
    id: 2,
    titulo: 'Convocatoria Abierta: Envío de artículos para Revista Científica Varianza',
    descripcion: 'Se invita a investigadores y docentes del área estadística a postular artículos originales y notas de divulgación científica.',
    contenido: 'La Revista Varianza convoca a la comunidad científica a participar de su siguiente número indexado.',
    fecha: '2025-09-28',
    portada_url: '',
    destacado: false,
    publicado: true,
    persona: { id: 2, nombre_completo: 'Dra. María Elena Quispe' },
  },
  {
    id: 3,
    titulo: 'Taller Internacional de Ciencia de Datos y Machine Learning con Python y R',
    descripcion: 'Programa intensivo de actualización metodológica en aprendizaje automático, inferencia bayesiana y Big Data.',
    contenido: 'El IETA llevó a cabo tres jornadas de capacitación orientadas al análisis predictivo y minería de datos.',
    fecha: '2025-08-15',
    portada_url: '',
    destacado: false,
    publicado: true,
    persona: { id: 3, nombre_completo: 'M.Sc. Carlos Mendoza Flores' },
  },
  {
    id: 4,
    titulo: 'Publicación de nuevos conjuntos de datos en el Banco de Datos Institucional del IETA',
    descripcion: 'Series temporales, indicadores sociodemográficos y microdatos procesados quedan a libre disposición de la comunidad académica.',
    contenido: 'En el marco de la política de ciencia abierta, el instituto habilitó nuevos repositorios con fichas técnicas.',
    fecha: '2025-07-20',
    portada_url: '',
    destacado: false,
    publicado: true,
    persona: { id: 1, nombre_completo: 'Lic. Esp. Raúl León Delgado' },
  },
]

const baseNews = computed(() => {
  return resource.items.value.length > 0 ? resource.items.value : defaultNews
})

const availableYears = computed(() => {
  const years = new Set<string>()
  for (const item of baseNews.value) {
    const d = newsDate(item)
    if (d) {
      const match = d.match(/^(\d{4})/)
      if (match && match[1]) {
        years.add(match[1])
      }
    }
  }
  return Array.from(years).sort((a, b) => b.localeCompare(a))
})

const filters = computed(() => {
  const list = [
    {
      key: 'type',
      label: 'Tipo',
      options: [
        { value: '', label: 'Todas las noticias' },
        { value: 'featured', label: 'Destacadas' },
      ],
    },
  ]
  if (availableYears.value.length > 0) {
    list.push({
      key: 'year',
      label: 'Año',
      options: availableYears.value.map((yr) => ({ value: yr, label: yr })),
    })
  }
  return list
})

const sortOptions = [
  { value: 'recent', label: 'Más recientes' },
  { value: 'oldest', label: 'Más antiguos' },
  { value: 'title', label: 'Título (A - Z)' },
]

const filteredNews = computed(() => {
  const search = query.value.trim().toLocaleLowerCase()
  return baseNews.value.filter((item) => {
    if (!newsIsPublished(item)) return false

    if (filterValues.value.type === 'featured' && !newsIsFeatured(item)) {
      return false
    }

    if (filterValues.value.year) {
      const d = newsDate(item) || ''
      if (!d.startsWith(filterValues.value.year)) return false
    }

    if (search) {
      const author = item.persona?.nombre_completo || ''
      const text = `${newsTitle(item)} ${newsSummary(item)} ${author}`.toLocaleLowerCase()
      if (!text.includes(search)) return false
    }

    return true
  })
})

const sortedNews = computed(() => {
  const list = [...filteredNews.value]
  if (sortBy.value === 'title') {
    return list.sort((a, b) => newsTitle(a).localeCompare(newsTitle(b)))
  }
  if (sortBy.value === 'oldest') {
    return list.sort((a, b) => {
      const timeA = new Date(newsDate(a) || 0).getTime()
      const timeB = new Date(newsDate(b) || 0).getTime()
      return timeA - timeB
    })
  }
  // Default: most recent first
  return list.sort((a, b) => {
    const timeA = new Date(newsDate(a) || 0).getTime()
    const timeB = new Date(newsDate(b) || 0).getTime()
    return timeB - timeA
  })
})

const isFilterActive = computed(() => {
  return query.value.trim() !== '' || Boolean(filterValues.value.type) || Boolean(filterValues.value.year) || sortBy.value !== 'recent'
})

const featuredLeadNews = computed(() => {
  if (sortedNews.value.length === 0) return null
  if (!isFilterActive.value) {
    return sortedNews.value.find(newsIsFeatured) || sortedNews.value[0]
  }
  return null
})

const regularNewsList = computed(() => {
  if (isFilterActive.value || !featuredLeadNews.value) {
    return sortedNews.value
  }
  return sortedNews.value.filter((item) => item.id !== featuredLeadNews.value?.id)
})

function resetFilters() {
  query.value = ''
  filterValues.value = { type: '', year: '' }
  sortBy.value = 'recent'
}

useGsapReveal(newsRoot, {
  selector: '.news-reveal',
  stagger: 0.08,
  y: 20,
})

onMounted(() => {
  void resource.refresh()
})
</script>

<template>
  <div ref="newsRoot">
    <SectionHeaderBand
      :title="t('news.title')"
      :subtitle="t('news.subtitle')"
    />

    <main class="container view-body">
      <!-- Loading Skeleton State -->
      <div
        v-if="resource.loading.value && resource.items.value.length === 0"
        class="news-skeleton-grid"
        role="status"
        aria-label="Cargando noticias"
      >
        <div class="skeleton-lead-box"></div>
        <div class="news-skeleton-subgrid">
          <div v-for="n in 6" :key="n" class="skeleton-card">
            <div class="skeleton-media"></div>
            <div class="skeleton-body">
              <div class="skeleton-line skeleton-line--eyebrow"></div>
              <div class="skeleton-line skeleton-line--title"></div>
              <div class="skeleton-line skeleton-line--meta"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Error State -->
      <div
        v-else-if="resource.error.value && resource.items.value.length === 0"
        class="content-state"
        role="alert"
      >
        <EmptyState
          title="No se pudieron cargar las noticias"
          description="Ocurrió un problema de comunicación al obtener los artículos de actualidad."
          :action-label="t('common.tryAgain')"
          @action="resource.refresh"
        />
      </div>

      <!-- Content Results -->
      <template v-else>
        <!-- Top Featured Lead News (Oculto cuando se busca o filtra para mostrar resultados directos) -->
        <article
          v-if="featuredLeadNews && !isFilterActive"
          class="news-lead-banner news-reveal"
          aria-label="Noticia principal destacada"
        >
          <div class="news-lead-banner__copy">
            <span class="news-lead-tag">
              <Newspaper :size="14" aria-hidden="true" />
              <span>{{ newsIsFeatured(featuredLeadNews) ? 'Noticia destacada' : 'Última noticia' }}</span>
            </span>

            <h2 class="news-lead-banner__title">
              {{ newsTitle(featuredLeadNews) }}
            </h2>

            <span class="news-lead-banner__date">
              {{ formatApiDate(newsDate(featuredLeadNews)) }}
            </span>

            <p class="news-lead-banner__excerpt">
              {{ newsSummary(featuredLeadNews) || 'Investigadores del IETA desarrollaron modelos analíticos avanzados para la comprensión de datos multidimensionales y su aplicación al contexto nacional.' }}
            </p>

            <RouterLink
              :to="`/noticias/${featuredLeadNews.id}`"
              class="news-lead-banner__btn"
            >
              <span>Leer noticia completa</span>
              <span class="action-arrow">→</span>
            </RouterLink>
          </div>

          <div class="news-lead-banner__media">
            <img
              v-if="featuredLeadNews.portada_url"
              :src="mediaUrl(featuredLeadNews.portada_url)"
              :alt="newsTitle(featuredLeadNews)"
              class="news-lead-banner__img"
              loading="eager"
            />
            <div v-else class="news-lead-banner__placeholder">
              <Newspaper :size="64" />
            </div>
          </div>
        </article>

        <!-- Search & Filter Bar (Normalizado según Banco de Datos) -->
        <section class="news-filter-section news-reveal" aria-label="Filtros y búsqueda de noticias">
          <SearchFilterBar
            v-model="query"
            v-model:filter-values="filterValues"
            v-model:sort-value="sortBy"
            :filters="filters"
            :sort-options="sortOptions"
            :results-count="sortedNews.length"
            :no-results="sortedNews.length === 0"
            placeholder="Buscar noticias por título, resumen o autor..."
            @clear="resetFilters"
          />
        </section>

        <!-- 3-Column News Grid -->
        <div v-if="regularNewsList.length" class="news-grid-3col">
          <article
            v-for="item in regularNewsList"
            :key="item.id"
            class="news-grid-card news-reveal"
          >
            <RouterLink :to="`/noticias/${item.id}`" class="news-grid-card__link">
              <!-- Top Photo Thumbnail -->
              <div class="news-grid-card__media">
                <img
                  v-if="item.portada_url"
                  :src="mediaUrl(item.portada_url)"
                  :alt="newsTitle(item)"
                  class="news-grid-card__img"
                  loading="lazy"
                />
                <div v-else class="news-grid-card__placeholder">
                  <Newspaper :size="36" />
                </div>
              </div>

              <!-- Body with Tag, Title, Date -->
              <div class="news-grid-card__body">
                <span class="news-grid-card__tag">
                  <Newspaper :size="12" aria-hidden="true" />
                  <span>{{ newsIsFeatured(item) ? 'destacado' : 'noticia' }}</span>
                </span>

                <h4 class="news-grid-card__title">
                  {{ newsTitle(item) }}
                </h4>

                <span class="news-grid-card__date">
                  {{ formatApiDate(newsDate(item)) }}
                </span>
              </div>
            </RouterLink>
          </article>
        </div>

        <!-- Empty Results -->
        <div v-else-if="!featuredLeadNews && !resource.loading.value" class="news-reveal">
          <EmptyState
            v-if="isFilterActive"
            title="No se encontraron noticias coincidentes"
            description="Intenta buscar con otros términos o limpia los filtros activos."
            action-label="Ver todas las noticias"
            @action="resetFilters"
          />
          <EmptyState
            v-else
            title="No hay noticias publicadas"
            description="Las noticias institucionales y de investigación aparecerán aquí."
          />
        </div>

        <!-- Pagination / Load More Button -->
        <div class="news-pagination news-reveal">
          <LoadMoreButton
            :loading="resource.loadingMore.value"
            :has-more="resource.hasMore.value"
            label="Cargar más noticias →"
            @load="resource.loadMore"
          />
        </div>
      </template>
    </main>
  </div>
</template>

<style scoped>
.view-body {
  min-height: 48vh;
  padding-block: var(--space-4) var(--space-8);
}

/* Top Featured Lead News Banner (PDF Page 6) */
.news-lead-banner {
  display: grid;
  grid-template-columns: 1.15fr 0.85fr;
  gap: clamp(1.5rem, 3vw, 2.75rem);
  padding: clamp(1.5rem, 3vw, 2.25rem);
  border: 1px solid rgba(220, 224, 235, 0.85);
  border-radius: var(--radius-md);
  background: var(--color-blanco);
  box-shadow: 0 4px 18px rgba(27, 20, 75, 0.04);
  margin-bottom: 2.75rem;
  align-items: center;
}

.news-lead-banner__copy {
  display: flex;
  flex-direction: column;
}

.news-lead-tag {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.82rem;
  font-weight: 700;
  color: #1b144b;
  margin-bottom: 0.65rem;
}

.news-lead-banner__title {
  color: #1b144b;
  font-size: clamp(1.55rem, 2.7vw, 2.2rem);
  font-weight: 700;
  line-height: 1.25;
  margin-bottom: 0.5rem;
}

.news-lead-banner__date {
  color: #71788d;
  font-size: 0.88rem;
  font-weight: 600;
  margin-bottom: 1rem;
}

.news-lead-banner__excerpt {
  color: #444b62;
  font-size: 0.98rem;
  line-height: 1.65;
  margin-bottom: 1.5rem;
}

.news-lead-banner__btn {
  display: inline-flex;
  align-items: center;
  align-self: flex-start;
  gap: 0.5rem;
  padding: 0.55rem 1.4rem;
  border: 1.5px solid #1b144b;
  border-radius: 999px;
  background: transparent;
  color: #1b144b;
  font-weight: 700;
  font-size: 0.88rem;
  text-decoration: none;
  transition: all 0.15s ease;
}

.news-lead-banner__btn:hover {
  background: #1b144b;
  color: #fff;
}

.news-lead-banner__media {
  height: 100%;
  min-height: 17rem;
  border-radius: var(--radius-md);
  overflow: hidden;
  background: #f0f3fa;
}

.news-lead-banner__img {
  width: 100%;
  height: 100%;
  max-height: 17rem;
  object-fit: cover;
  display: block;
}

.news-lead-banner__placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #a8b0c5;
}

/* Filter Controls Section */
.news-filter-section {
  margin-bottom: 2.25rem;
}

/* 3-Column News Grid (PDF Page 6) */
.news-grid-3col {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: clamp(1.25rem, 2vw, 1.85rem);
}

.news-grid-card {
  border: 1px solid rgba(220, 224, 235, 0.85);
  border-radius: var(--radius-md);
  overflow: hidden;
  background: var(--color-blanco);
  box-shadow: 0 4px 18px rgba(27, 20, 75, 0.04);
  transition: transform 0.22s ease, box-shadow 0.22s ease;
}

.news-grid-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 28px -4px rgba(27, 20, 75, 0.09);
}

.news-grid-card__link {
  display: flex;
  flex-direction: column;
  height: 100%;
  text-decoration: none;
}

.news-grid-card__media {
  height: 12rem;
  overflow: hidden;
  background: #f0f3fa;
}

.news-grid-card__img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.news-grid-card__placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #a8b0c5;
}

.news-grid-card__body {
  padding: 1.25rem 1.4rem;
  display: flex;
  flex-direction: column;
  flex: 1;
  gap: 0.5rem;
}

.news-grid-card__tag {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.78rem;
  font-weight: 700;
  color: #1b144b;
}

.news-grid-card__title {
  color: #1b144b;
  font-size: 1.05rem;
  font-weight: 700;
  line-height: 1.35;
}

.news-grid-card__date {
  color: #8c93a8;
  font-size: 0.82rem;
  margin-top: auto;
  padding-top: 0.5rem;
}

.news-pagination {
  display: flex;
  justify-content: center;
  margin-top: var(--space-6);
}

/* Skeleton Loading */
.skeleton-lead-box {
  height: 18rem;
  border-radius: var(--radius-md);
  background: #edeef5;
  margin-bottom: 2rem;
}

.news-skeleton-subgrid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 1.5rem;
}

.skeleton-card {
  overflow: hidden;
  border: 1px solid var(--color-desactivado);
  border-radius: var(--radius-md);
  background: var(--color-blanco);
}

.skeleton-media {
  height: 11rem;
  background: #edeef5;
}

.skeleton-body {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  padding: 1.25rem;
}

.skeleton-line {
  border-radius: var(--radius-sm);
  background: #edeef5;
}

.skeleton-line--eyebrow {
  width: 30%;
  height: 0.8rem;
}

.skeleton-line--title {
  width: 90%;
  height: 1.4rem;
}

.skeleton-line--meta {
  width: 45%;
  height: 0.75rem;
}

.content-state {
  display: grid;
  min-height: 18rem;
  align-content: center;
  justify-items: center;
  padding: var(--space-6) 0;
}

/* Responsive */
@media (max-width: 960px) {
  .news-lead-banner {
    grid-template-columns: 1fr;
  }

  .news-lead-banner__media {
    min-height: 14rem;
  }

  .news-grid-3col,
  .news-skeleton-subgrid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 640px) {
  .news-grid-3col,
  .news-skeleton-subgrid {
    grid-template-columns: 1fr;
  }
}
</style>
