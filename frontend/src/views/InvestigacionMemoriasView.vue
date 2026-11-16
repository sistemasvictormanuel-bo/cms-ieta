<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { Download, ExternalLink, FileText } from '@lucide/vue'
import EmptyState from '@/components/common/EmptyState.vue'
import LoadMoreButton from '@/components/common/LoadMoreButton.vue'
import SearchFilterBar from '@/components/common/SearchFilterBar.vue'
import SectionHeaderBand from '@/components/layout/SectionHeaderBand.vue'
import TabNav from '@/components/common/TabNav.vue'
import { authorName, formatApiDate, memoryDate, memoryDescription, memoryDocumentUrl, memoryTitle } from '@/api/normalizers'
import { mediaUrl } from '@/api/response'
import { MemoriasInvestigacionRepository } from '@/api/repositories.js'
import type { ResearchMemoryApiItem } from '@/api/types'
import { usePaginatedResource } from '@/composables/usePaginatedResource'
import { useGsapReveal } from '@/composables/useGsapReveal'

const router = useRouter()
const { t } = useI18n()
const memoriesRoot = ref<HTMLElement | null>(null)

const tabs = computed(() => [
  { value: 'lineas', label: t('tabs.lineas') },
  { value: 'memorias', label: t('tabs.memorias') },
])
const activeTab = ref('memorias')

watch(activeTab, (tab) => {
  if (tab === 'lineas') {
    void router.push('/investigacion/lineas-investigacion')
  }
})

const query = ref('')
const filterValues = ref<Record<string, string>>({ year: '' })
const sortBy = ref<'desc' | 'asc'>('desc')

useGsapReveal(memoriesRoot, {
  selector: '.memories-reveal',
  stagger: 0.08,
  y: 18,
})

const resource = usePaginatedResource<ResearchMemoryApiItem>(
  (page) => MemoriasInvestigacionRepository.list({ page, per_page: 100 }),
  { autoFetchAll: true },
)

function memoryYear(memory: ResearchMemoryApiItem) {
  const value = memoryDate(memory)
  const date = new Date(value)
  return Number.isNaN(date.getTime()) ? '' : String(date.getFullYear())
}

const allItems = computed(() => resource.items.value)

const filters = computed(() => [{
  key: 'year',
  label: 'Año',
  options: [...new Set(allItems.value.map(memoryYear).filter(Boolean))]
    .sort((first, second) => second.localeCompare(first))
    .map((year) => ({ value: year, label: year })),
}])

const sortOptions = [
  { value: 'desc', label: 'Más recientes' },
  { value: 'asc', label: 'Más antiguos' },
]

const visibleMemories = computed(() => {
  const search = query.value.trim().toLocaleLowerCase()
  const list = allItems.value.filter((memory) => {
    const author = memory.autor ? authorName(memory.autor) : ''
    const text = `${memoryTitle(memory)} ${memoryDescription(memory)} ${memory.etiqueta ?? memory.mdi_etiqueta ?? ''} ${author}`
    const matchesQuery = !search || text.toLocaleLowerCase().includes(search)
    const matchesYear = !filterValues.value.year || memoryYear(memory) === filterValues.value.year
    return matchesQuery && matchesYear
  })

  return [...list].sort((a, b) => {
    const timeA = new Date(memoryDate(a)).getTime() || 0
    const timeB = new Date(memoryDate(b)).getTime() || 0
    return sortBy.value === 'asc' ? timeA - timeB : timeB - timeA
  })
})

onMounted(() => {
  void resource.refresh()
})
</script>

<template>
  <div ref="memoriesRoot">
    <SectionHeaderBand
      title="Memorias de Investigación"
      subtitle="Registro histórico, documentos de investigación y producción científica del IETA."
    />

    <main class="container view-body">
      <!-- Tabs Navigation (Sincronizado con Líneas de Investigación) -->
      <div class="tabs-bar memories-reveal">
        <TabNav v-model="activeTab" :items="tabs" :label="t('aria.researchSections')" />
      </div>

      <!-- Memorias Panel (Showcase PDF Page 5 Design) -->
      <section class="memories-panel memories-reveal" aria-labelledby="memories-heading">
        <!-- Centered Intro -->
        <header class="research-centered-header">
          <h2 id="memories-heading" class="research-centered-title">Memorias de Investigación</h2>
          <p class="research-centered-lead">
            Espacio de consulta académica que reúne el registro histórico y la producción científica del instituto. Explora el catálogo consolidado con las memorias de trabajos de investigación del IETA a partir de 2023, orientadas al desarrollo metodológico y la solución de problemas mediante la ciencia de datos.
          </p>
          <div class="research-resolution-pill">
            Memorias de trabajos de investigación del IETA a partir de 2023
          </div>
        </header>

        <!-- Subheading & Search Filter Bar (Unified Banco de Datos Design) -->
        <div class="memories-subheading-row">
          <h3 class="memories-section-subtitle">Últimas investigaciones</h3>
          <div class="memories-search-bar">
            <SearchFilterBar
              v-model="query"
              v-model:filter-values="filterValues"
              v-model:sort-value="sortBy"
              :filters="filters"
              :sort-options="sortOptions"
              :results-count="visibleMemories.length"
              placeholder="Buscar por título, autor o palabra clave..."
              :no-results="query.trim().length > 0 && visibleMemories.length === 0"
            />
          </div>
        </div>

        <!-- Loading Skeleton -->
        <div v-if="resource.loading.value && resource.items.value.length === 0" class="skeleton-grid" role="status">
          <div v-for="n in 6" :key="n" class="skeleton-memory-card"></div>
        </div>

        <!-- Showcase Grid -->
        <template v-else>
          <div v-if="visibleMemories.length" class="memories-showcase-grid">
            <article
              v-for="memory in visibleMemories"
              :key="memory.id"
              class="memory-showcase-card"
            >
              <!-- Optional Cover Image -->
              <div v-if="memory.portada_url || memory.imagen_url" class="memory-showcase-card__cover">
                <img :src="mediaUrl(memory.portada_url || memory.imagen_url)" :alt="memoryTitle(memory)" loading="lazy" />
              </div>

              <div class="memory-showcase-card__body">
                <div class="memory-showcase-card__meta-top">
                  <span class="memory-tag">
                    <FileText :size="13" />
                    <span>{{ memory.etiqueta || memory.mdi_etiqueta || 'Investigación' }}</span>
                  </span>
                  <span class="memory-year">
                    {{ memoryYear(memory) || '2024' }}
                  </span>
                </div>

                <h4 class="memory-showcase-card__title">
                  {{ memoryTitle(memory) || 'Investigación Estadística del IETA' }}
                </h4>

                <p v-if="memory.descripcion || memory.mdi_descripcion" class="memory-showcase-card__desc">
                  {{ memoryDescription(memory) }}
                </p>

                <p class="memory-showcase-card__author">
                  {{ memory.autor ? authorName(memory.autor) : 'Investigador IETA' }}
                </p>

                <div class="memory-showcase-card__footer">
                  <a
                    v-if="memoryDocumentUrl(memory)"
                    class="memory-action-link"
                    :href="memoryDocumentUrl(memory)"
                    target="_blank"
                    rel="noopener noreferrer"
                  >
                    <Download :size="14" aria-hidden="true" />
                    <span>Ver / Descargar documento</span>
                    <ExternalLink :size="13" aria-hidden="true" />
                  </a>
                  <span v-else class="memory-action-link memory-action-link--disabled">
                    <span>Documento en archivo físico IETA</span>
                  </span>
                </div>
              </div>
            </article>
          </div>

          <EmptyState
            v-else-if="!query.trim()"
            title="No hay memorias publicadas"
            description="Los documentos de investigación disponibles aparecerán aquí."
          />

          <div v-if="resource.hasMore.value" class="pagination-center">
            <LoadMoreButton
              :loading="resource.loadingMore.value"
              :has-more="resource.hasMore.value"
              label="Ver más investigaciones →"
              @load="resource.loadMore"
            />
          </div>
        </template>
      </section>
    </main>
  </div>
</template>

<style scoped>
.view-body {
  min-height: 48vh;
  padding-block: var(--space-4) var(--space-8);
}

.tabs-bar {
  position: relative;
  z-index: 50;
  margin-bottom: var(--space-6);
}

.memories-panel {
  max-width: 1200px;
  margin-inline: auto;
}

/* Centered Header (PDF Page 5) */
.research-centered-header {
  text-align: center;
  max-width: 820px;
  margin-inline: auto;
  margin-bottom: var(--space-6);
}

.research-centered-title {
  color: #1b144b;
  font-size: clamp(1.8rem, 3.5vw, 2.35rem);
  font-weight: 700;
  line-height: 1.25;
  margin-bottom: 0.75rem;
}

.research-centered-lead {
  color: #4b5268;
  font-size: 1rem;
  line-height: 1.65;
  margin-bottom: 1.25rem;
}

.research-resolution-pill {
  display: inline-block;
  padding: 0.55rem 1.6rem;
  border: 1.5px solid #71589f;
  border-radius: 999px;
  color: #2c1b6e;
  font-size: 0.85rem;
  font-weight: 700;
  background: rgba(113, 88, 159, 0.04);
}

/* Subheading Row */
.memories-subheading-row {
  margin-bottom: 1.5rem;
}

.memories-section-subtitle {
  color: #1b144b;
  font-size: 1.35rem;
  font-weight: 700;
  margin: 0 0 1rem;
}

.memories-search-bar {
  margin-bottom: 1rem;
}

/* Showcase Grid */
.memories-showcase-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: clamp(1.25rem, 2vw, 1.75rem);
}

.memory-showcase-card {
  display: flex;
  flex-direction: column;
  border: 1px solid rgba(220, 224, 235, 0.85);
  border-radius: var(--radius-md);
  background: var(--color-blanco);
  overflow: hidden;
  box-shadow: 0 4px 18px rgba(27, 20, 75, 0.04);
  transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
}

.memory-showcase-card:hover {
  transform: translateY(-4px);
  border-color: rgba(53, 68, 159, 0.35);
  box-shadow: 0 12px 28px -4px rgba(27, 20, 75, 0.08);
}

.memory-showcase-card__cover {
  height: 9rem;
  overflow: hidden;
  background: #f0f3fa;
}

.memory-showcase-card__cover img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.memory-showcase-card__body {
  padding: 1.35rem 1.5rem;
  display: flex;
  flex-direction: column;
  flex: 1;
  gap: 0.5rem;
}

.memory-showcase-card__meta-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.82rem;
  color: #636b83;
}

.memory-tag {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  font-weight: 700;
  color: #1b144b;
}

.memory-year {
  color: #8c93a8;
  font-weight: 600;
}

.memory-showcase-card__title {
  color: #1b144b;
  font-size: 1.08rem;
  font-weight: 700;
  line-height: 1.35;
  margin: 0;
}

.memory-showcase-card__desc {
  margin: 0;
  color: #555c73;
  font-size: 0.88rem;
  line-height: 1.5;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.memory-showcase-card__author {
  color: var(--color-rosa-carmin);
  font-size: 0.85rem;
  font-weight: 600;
  margin: 0;
}

.memory-showcase-card__footer {
  margin-top: auto;
  padding-top: 1rem;
  border-top: 1px solid #edf0f7;
}

.memory-action-link {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  color: #35449f;
  font-size: 0.88rem;
  font-weight: 700;
  text-decoration: none;
  transition: color 0.15s ease, transform 0.15s ease;
}

.memory-action-link:hover {
  color: var(--color-rosa-carmin);
  transform: translateX(2px);
}

.memory-action-link--disabled {
  color: #9ca3af;
  font-size: 0.82rem;
  font-weight: 500;
}

.pagination-center {
  display: flex;
  justify-content: center;
  margin-top: var(--space-6);
}

/* Skeleton Loading */
.skeleton-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 1.5rem;
}

.skeleton-memory-card {
  height: 16rem;
  border-radius: var(--radius-md);
  background: #edeef5;
}

@media (max-width: 960px) {
  .memories-showcase-grid,
  .skeleton-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 640px) {
  .memories-showcase-grid,
  .skeleton-grid {
    grid-template-columns: 1fr;
  }
}
</style>
