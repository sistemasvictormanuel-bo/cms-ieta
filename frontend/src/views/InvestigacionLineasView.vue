<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import EmptyState from '@/components/common/EmptyState.vue'
import SectionHeaderBand from '@/components/layout/SectionHeaderBand.vue'
import TabNav from '@/components/common/TabNav.vue'
import { LineasInvestigacionRepository } from '@/api/repositories.js'
import { useGsapReveal } from '@/composables/useGsapReveal'

const router = useRouter()
const { t } = useI18n()
const researchRoot = ref<HTMLElement | null>(null)

const tabs = computed(() => [
  { value: 'lineas', label: t('tabs.lineas') },
  { value: 'memorias', label: t('tabs.memorias') },
])
const activeTab = ref('lineas')

watch(activeTab, (tab) => {
  if (tab === 'memorias') {
    void router.push('/investigacion/memorias')
  }
})

interface ResearchLine {
  id: number
  titulo: string
  slug: string
  descripcion: string
  imagen_url?: string | null
}

interface LinesResponse {
  data: ResearchLine[]
  meta?: {
    current_page?: number
    last_page?: number
  }
}

const lines = ref<ResearchLine[]>([])
const loading = ref(true)
const error = ref(false)
const apiBaseUrl = import.meta.env.VITE_API_URL

useGsapReveal(researchRoot, {
  selector: '.research-reveal',
  stagger: 0.08,
  y: 18,
})

async function loadLines() {
  loading.value = true
  error.value = false

  try {
    const response = (await LineasInvestigacionRepository.list({ per_page: 100 })) as LinesResponse
    lines.value = Array.isArray(response.data) ? response.data : []
  } catch {
    error.value = true
    lines.value = []
  } finally {
    loading.value = false
  }
}

function detailPath(line: ResearchLine): string {
  return `/investigacion/lineas-investigacion/${line.id}`
}

function imageUrl(path?: string | null): string | undefined {
  if (!path) return undefined
  try {
    const url = new URL(path, apiBaseUrl)
    return url.protocol === 'http:' || url.protocol === 'https:' ? url.href : undefined
  } catch {
    return undefined
  }
}

onMounted(() => loadLines())
</script>

<template>
  <div ref="researchRoot">
    <SectionHeaderBand
      :title="t('research.linesTitle')"
      subtitle="Ejes temáticos, métodos de inferencia y producción científica del IETA."
    />

    <main class="container view-body">
      <!-- Tabs Navigation -->
      <div class="tabs-bar research-reveal">
        <TabNav v-model="activeTab" :items="tabs" :label="t('aria.researchSections')" />
      </div>

      <!-- LÍNEAS DE INVESTIGACIÓN (Cargan todas directamente sin botón cargar más) -->
      <section class="lines-panel research-reveal" aria-labelledby="lines-heading">
        <!-- Centered Intro -->
        <header class="research-centered-header">
          <h2 id="lines-heading" class="research-centered-title">Líneas de Investigación</h2>
          <p class="research-centered-lead">
            Estructuramos nuestro trabajo científico alrededor de ejes temáticos estratégicos que responden a los desafíos actuales de la sociedad y la academia. Nuestras líneas de investigación orientan el desarrollo de tesis, proyectos docentes e iniciativas de interacción social, aplicando el rigor de la estadística teórica y computacional a diversas áreas del conocimiento.
          </p>
          <div class="research-resolution-pill">
            De acuerdo a Resolución del Honorable Consejo Facultativo N° 1323/2022.
          </div>
        </header>

        <div v-if="loading && lines.length === 0" class="skeleton-grid-2col" role="status">
          <div v-for="n in 6" :key="n" class="skeleton-line-card-wide"></div>
        </div>

        <div v-else-if="error && lines.length === 0" class="content-state" role="alert">
          <EmptyState
            title="No se pudieron cargar las líneas de investigación"
            description="Por favor, intenta nuevamente."
            action-label="Reintentar"
            @action="loadLines"
          />
        </div>

        <template v-else>
          <!-- 2-Column Grid as in PDF Page 3 -->
          <div v-if="lines.length" class="lines-showcase-grid">
            <RouterLink
              v-for="line in lines"
              :key="line.id"
              :to="detailPath(line)"
              class="line-row-card"
            >
              <!-- Left Visual / Graphic Thumbnail -->
              <div class="line-row-card__visual">
                <img
                  v-if="imageUrl(line.imagen_url)"
                  :src="imageUrl(line.imagen_url)"
                  :alt="line.titulo"
                  class="line-row-card__img"
                  loading="lazy"
                />
                <div v-else class="line-row-card__graphic-mockup">
                  <div class="chart-bars">
                    <span class="bar bar-1"></span>
                    <span class="bar bar-2"></span>
                    <span class="bar bar-3"></span>
                    <span class="bar bar-4"></span>
                  </div>
                  <div class="chart-curve"></div>
                </div>
              </div>

              <!-- Right Copy -->
              <div class="line-row-card__content">
                <h3 class="line-row-card__title">{{ line.titulo }}</h3>
                <p class="line-row-card__desc">
                  {{ line.descripcion || 'Modelado del tiempo transcurrido hasta la ocurrencia de un evento específico.' }}
                </p>
                <div class="line-row-card__footer">
                  <span class="line-row-card__arrow">→</span>
                </div>
              </div>
            </RouterLink>
          </div>

          <EmptyState
            v-else
            title="No hay líneas de investigación registradas"
            description="Las áreas de especialización se publicarán próximamente."
          />
        </template>
      </section>
    </main>
  </div>
</template>

<style scoped>
/* Tab Panel Fade Transition */
.tab-panel-fade-enter-active,
.tab-panel-fade-leave-active {
  transition: opacity 0.22s ease, transform 0.22s ease;
}

.tab-panel-fade-enter-from {
  opacity: 0;
  transform: translateY(8px);
}

.tab-panel-fade-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}

@media (prefers-reduced-motion: reduce) {
  .tab-panel-fade-enter-active,
  .tab-panel-fade-leave-active {
    transition: none;
  }
}

.view-body {
  min-height: 48vh;
  padding-block: var(--space-4) var(--space-8);
}

.tabs-bar {
  position: relative;
  z-index: 50;
  margin-bottom: var(--space-6);
}

/* Centered Header & Resolution Pill (PDF Pages 3 & 5) */
.research-centered-header {
  max-width: 860px;
  margin-inline: auto;
  text-align: center;
  margin-bottom: 2.75rem;
}

.research-centered-title {
  color: #1b144b;
  font-size: clamp(2rem, 3.5vw, 2.6rem);
  font-weight: 700;
  line-height: 1.2;
  margin-bottom: 1rem;
}

.research-centered-lead {
  color: #4b5268;
  font-size: 1.02rem;
  line-height: 1.75;
  margin-bottom: 1.5rem;
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

/* 2-Column Lines Showcase Grid (PDF Page 3) */
.lines-showcase-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: clamp(1.25rem, 2vw, 1.85rem);
}

.line-row-card {
  display: flex;
  align-items: center;
  gap: 1.4rem;
  padding: 1.35rem 1.6rem;
  border: 1px solid rgba(220, 224, 235, 0.85);
  border-radius: var(--radius-md);
  background: var(--color-blanco);
  text-decoration: none;
  box-shadow: 0 4px 18px rgba(27, 20, 75, 0.04);
  transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.22s ease, border-color 0.22s ease;
}

.line-row-card:hover {
  transform: translateY(-4px);
  border-color: rgba(53, 68, 159, 0.35);
  box-shadow: 0 12px 30px -4px rgba(27, 20, 75, 0.09);
}

.line-row-card__visual {
  width: 8rem;
  height: 8rem;
  border-radius: var(--radius-sm);
  border: 1px solid #dce0eb;
  overflow: hidden;
  background: #f8f9fc;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.line-row-card__img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* Graphic Mockup inside Visual Box if no image */
.line-row-card__graphic-mockup {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  padding: 0.75rem;
  position: relative;
  background: linear-gradient(180deg, #f0f3fa 0%, #e5eaf5 100%);
}

.chart-bars {
  display: flex;
  align-items: flex-end;
  gap: 0.4rem;
  height: 60%;
}

.chart-bars .bar {
  flex: 1;
  background: linear-gradient(180deg, #35449f 0%, #71589f 100%);
  border-radius: 2px 2px 0 0;
}

.chart-bars .bar-1 { height: 40%; }
.chart-bars .bar-2 { height: 75%; background: linear-gradient(180deg, #f30060 0%, #71589f 100%); }
.chart-bars .bar-3 { height: 95%; }
.chart-bars .bar-4 { height: 60%; }

.chart-curve {
  position: absolute;
  top: 25%;
  left: 10%;
  right: 10%;
  height: 2px;
  background: #f30060;
  transform: rotate(-12deg);
}

.line-row-card__content {
  display: flex;
  flex: 1;
  min-width: 0;
  flex-direction: column;
  gap: 0.35rem;
}

.line-row-card__title {
  color: #1b144b;
  font-size: 1.12rem;
  font-weight: 700;
  line-height: 1.25;
}

.line-row-card__desc {
  color: #555c74;
  font-size: 0.84rem;
  line-height: 1.5;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-row-card__footer {
  margin-top: 0.5rem;
  display: flex;
  justify-content: flex-end;
}

.line-row-card__arrow {
  color: #35449f;
  font-size: 1.35rem;
  font-weight: 700;
  transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), color 0.2s ease;
}

.line-row-card:hover .line-row-card__arrow {
  transform: translateX(6px);
  color: var(--color-rosa-carmin);
}

/* 3-Column Memories Showcase Grid (PDF Page 5) */
.memories-subheading-row {
  margin-bottom: 2rem;
}

.memories-section-subtitle {
  color: #1b144b;
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 1rem;
}

.memories-search-bar {
  margin-bottom: 1.5rem;
}

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
  transition: transform 0.22s ease, box-shadow 0.22s ease;
}

.memory-showcase-card:hover {
  transform: translateY(-4px);
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
  font-size: 1.05rem;
  font-weight: 700;
  line-height: 1.35;
  margin-top: 0.25rem;
}

.memory-showcase-card__author {
  color: #636b83;
  font-size: 0.88rem;
  margin-top: 0.25rem;
}

.memory-showcase-card__footer {
  margin-top: auto;
  padding-top: 1rem;
}

.memory-action-link {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  color: #1b144b;
  font-size: 0.92rem;
  font-weight: 700;
  text-decoration: none;
  transition: color 0.15s ease, gap 0.15s ease;
}

.memory-action-link:hover {
  color: var(--color-rosa-carmin);
  gap: 0.65rem;
}

.action-arrow {
  font-size: 1.15rem;
  transition: transform 0.15s ease;
}

.memory-action-link:hover .action-arrow {
  transform: translateX(3px);
}

.pagination-center {
  display: flex;
  justify-content: center;
  margin-top: var(--space-6);
}

/* Skeletons */
.skeleton-grid-2col {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1.5rem;
}

.skeleton-line-card-wide {
  height: 8.5rem;
  border-radius: var(--radius-md);
  background: #edeef5;
}

.skeleton-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 1.5rem;
}

.skeleton-memory-card {
  height: 15rem;
  border-radius: var(--radius-md);
  background: #edeef5;
}

.content-state {
  display: grid;
  min-height: 20rem;
  align-content: center;
  text-align: center;
}

/* Responsive */
@media (max-width: 960px) {
  .lines-showcase-grid,
  .skeleton-grid-2col {
    grid-template-columns: 1fr;
  }

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

  .line-row-card {
    flex-direction: column;
    align-items: stretch;
    padding: 1.15rem;
    gap: 1rem;
    overflow: hidden;
  }

  .line-row-card__visual {
    width: 100%;
    height: 10rem;
    border-radius: var(--radius-sm);
  }

  .line-row-card__content {
    width: 100%;
  }

  .line-row-card__title {
    font-size: 1.05rem;
  }

  .line-row-card__desc {
    font-size: 0.82rem;
    -webkit-line-clamp: 4;
  }
}
</style>
