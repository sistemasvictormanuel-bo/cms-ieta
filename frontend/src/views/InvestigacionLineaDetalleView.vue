<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import {
  ArrowLeft,
  Calendar,
  FlaskConical,
  Layers,
  Mail,
  Share2,
} from '@lucide/vue'
import EditorJsContent from '@/components/common/EditorJsContent.vue'
import EmptyState from '@/components/common/EmptyState.vue'
import SectionHeaderBand from '@/components/layout/SectionHeaderBand.vue'
import { LineasInvestigacionRepository } from '@/api/repositories.js'
import { useGsapReveal } from '@/composables/useGsapReveal'

interface ResearchLine {
  id: number
  titulo: string
  slug: string
  descripcion: string
  contenido: unknown
  imagen_url?: string | null
}

interface LineDetailResponse {
  data: ResearchLine
}

const route = useRoute()
const { t } = useI18n()
const lineRoot = ref<HTMLElement | null>(null)
const line = ref<ResearchLine | null>(null)
const allLines = ref<ResearchLine[]>([])
const loading = ref(true)
const error = ref(false)
const apiBaseUrl = import.meta.env.VITE_API_URL
const lineId = computed(() => String(route.params.id ?? ''))
let requestSequence = 0

useGsapReveal(lineRoot, {
  selector: '.line-reveal',
  stagger: 0.08,
  y: 18,
})

const imageUrl = computed(() => {
  if (!line.value?.imagen_url) return undefined
  try {
    const url = new URL(line.value.imagen_url, apiBaseUrl)
    return url.protocol === 'http:' || url.protocol === 'https:' ? url.href : undefined
  } catch {
    return undefined
  }
})

async function loadLine(id: string) {
  const sequence = ++requestSequence
  loading.value = true
  error.value = false
  line.value = null

  if (!/^\d+$/.test(id)) {
    error.value = true
    loading.value = false
    return
  }

  try {
    const response = (await LineasInvestigacionRepository.getById(id)) as LineDetailResponse
    if (sequence === requestSequence) line.value = response.data ?? null
  } catch {
    if (sequence === requestSequence) error.value = true
  } finally {
    if (sequence === requestSequence) loading.value = false
  }
}

async function loadAllLines() {
  try {
    const response = (await LineasInvestigacionRepository.list({ page: 1 })) as { data: ResearchLine[] }
    if (Array.isArray(response.data)) {
      allLines.value = response.data
    }
  } catch {
    // Non-blocking
  }
}

watch(lineId, loadLine, { immediate: true })
loadAllLines()
</script>

<template>
  <div ref="lineRoot">
    <!-- Header Band with Right Image Fading into Purple Gradient (PDF Page 4) -->
    <SectionHeaderBand
      :title="line?.titulo || t('research.lineDetailTitle')"
      :subtitle="line?.descripcion || 'Área de investigación del Instituto de Estadística Teórica y Aplicada'"
      :image="imageUrl"
      image-alt="Gráfico representativo de la línea"
    >
      <template #extra>
        <div class="header-back-slot">
          <RouterLink class="header-back-link" to="/investigacion/lineas-investigacion">
            ‹ {{ t('research.backToLines') }}
          </RouterLink>
        </div>
      </template>
    </SectionHeaderBand>

    <main class="container view-body">
      <!-- Loading State -->
      <div v-if="loading" class="line-skeleton" role="status" aria-live="polite">
        <div class="skeleton-pill"></div>
        <div class="skeleton-title"></div>
        <div class="skeleton-body"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="content-state" role="alert">
        <EmptyState
          title="No se pudo cargar la línea de investigación"
          description="Por favor, comprueba el enlace o inténtalo nuevamente."
          action-label="Reintentar"
          @action="loadLine(lineId)"
        />
      </div>

      <!-- PDF Page 4 Master-Detail Layout -->
      <div v-else-if="line" class="line-master-detail line-reveal">
        <!-- Left Column: Vertical Tabs Menu -->
        <aside class="line-sidebar" aria-label="Menú de líneas de investigación">
          <div class="line-sidebar__list">
            <RouterLink
              v-for="item in (allLines.length ? allLines : [line])"
              :key="item.id"
              :to="`/investigacion/lineas-investigacion/${item.id}`"
              class="line-sidebar__link"
              :class="{ 'line-sidebar__link--active': String(item.id) === String(line.id) }"
            >
              <span class="line-sidebar__name">{{ item.titulo }}</span>
              <span class="line-sidebar__arrow">›</span>
            </RouterLink>
          </div>
        </aside>

        <!-- Right Column: Rich Detail Sections -->
        <article class="line-content-card">
          <!-- Section 1: Descripción y Alcance -->
          <section class="line-detail-block">
            <h2 class="line-detail-block__title">Descripción y Alcance</h2>
            <p class="line-detail-block__text">
              {{ line.descripcion || 'Esta línea aborda el tratamiento adecuado de modelos estadísticos y probabilísticos rigurosos, garantizando metodologías sólidas para la investigación, inferencia y toma de decisiones.' }}
            </p>
          </section>

          <!-- Section 2: Contenido API or Enfoques Metodológicos -->
          <section v-if="line.contenido" class="line-detail-block">
            <EditorJsContent :content="line.contenido" :api-base-url="apiBaseUrl" />
          </section>
          <template v-else>
            <section class="line-detail-block">
              <h2 class="line-detail-block__title">Enfoques Metodológicos y Temáticos</h2>
              <ul class="line-detail-block__list">
                <li>Estimación No Paramétrica: Aplicación e implementación de estimadores robustos de densidad y regresión.</li>
                <li>Modelos de Regresión Paramétricos y Semiparamétricos: Modelación de factores de riesgo o predicción multivariada.</li>
                <li>Inferencia Bayesiana y Computacional: Implementación de algoritmos MCMC y análisis probabilístico riguroso.</li>
                <li>Modelos de Clasificación y Machine Learning: Enfoques estadísticos para la minería de datos y patrones complejos.</li>
              </ul>
            </section>

            <section class="line-detail-block">
              <h2 class="line-detail-block__title">Ámbitos de Aplicación</h2>
              <ol class="line-detail-block__ol">
                <li><strong>Ciencias de la Salud y Biomédicas:</strong> Evaluación de supervivencia, recurrencia y epidemiología.</li>
                <li><strong>Ingeniería e Industria:</strong> Modelado de confiabilidad de componentes, fallas y control de procesos.</li>
                <li><strong>Economía y Finanzas:</strong> Análisis de riesgo, series temporales, predicción macroeconómica y seguros.</li>
                <li><strong>Ciencias Sociales:</strong> Análisis de trayectorias, deserción académica, pobreza multidimensional y demografía.</li>
              </ol>
            </section>
          </template>
        </article>
      </div>


      <EmptyState v-else-if="!loading && !error" :title="t('common.noResults')" />
    </main>
  </div>
</template>

<style scoped>
.view-body {
  min-height: 48vh;
  padding-block: var(--space-4) var(--space-8);
}

.header-back-slot {
  margin-bottom: 0.75rem;
}

.header-back-link {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  color: rgba(255, 255, 255, 0.88);
  font-size: 0.88rem;
  font-weight: 600;
  text-decoration: none;
  transition: color 0.15s ease, transform 0.15s ease;
}

.header-back-link:hover {
  color: #fff;
  transform: translateX(-3px);
}

/* Master-Detail Grid (PDF Page 4) */
.line-master-detail {
  display: grid;
  grid-template-columns: 290px 1fr;
  gap: clamp(1.5rem, 3vw, 2.75rem);
  align-items: flex-start;
  margin-block: var(--space-4);
}

/* Sidebar Vertical Tabs Menu */
.line-sidebar {
  position: sticky;
  top: calc(var(--header-height) + 1.5rem);
  background: var(--color-blanco);
  border: 1px solid rgba(220, 224, 235, 0.85);
  border-radius: var(--radius-md);
  box-shadow: 0 4px 18px rgba(27, 20, 75, 0.04);
  overflow: hidden;
}

.line-sidebar__list {
  display: flex;
  flex-direction: column;
}

.line-sidebar__link {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.85rem 1.15rem;
  color: #2b3044;
  text-decoration: none;
  font-size: 0.92rem;
  font-weight: 600;
  border-bottom: 1px solid #f0f3fa;
  border-left: 3px solid transparent;
  transition: all 0.15s ease;
}

.line-sidebar__link:last-child {
  border-bottom: none;
}

.line-sidebar__link:hover {
  background: #f8f9fe;
  color: #1b144b;
}

.line-sidebar__link--active {
  background: #f5f4fd;
  color: #2c1b6e;
  font-weight: 700;
  border-left: 3px solid #2c1b6e;
}

.line-sidebar__arrow {
  color: #8c93a8;
  font-size: 1.15rem;
  line-height: 1;
}

.line-sidebar__link--active .line-sidebar__arrow {
  color: #2c1b6e;
  transform: translateX(2px);
}

/* Right Content Card */
.line-content-card {
  display: flex;
  flex-direction: column;
  gap: 2rem;
  background: var(--color-blanco);
  border: 1px solid rgba(220, 224, 235, 0.85);
  border-radius: var(--radius-md);
  padding: clamp(1.5rem, 3.5vw, 2.5rem);
  box-shadow: 0 4px 18px rgba(27, 20, 75, 0.04);
}

.line-detail-block {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.line-detail-block__title {
  color: #1b144b;
  font-size: 1.35rem;
  font-weight: 700;
}

.line-detail-block__text {
  color: #3b4258;
  font-size: 1rem;
  line-height: 1.75;
}

.line-detail-block__list,
.line-detail-block__ol {
  padding-left: 1.35rem;
  display: flex;
  flex-direction: column;
  gap: 0.65rem;
  color: #3b4258;
  line-height: 1.65;
  font-size: 0.98rem;
}

.line-detail-block__list li::marker {
  color: var(--color-rosa-carmin);
}

.line-detail__footer {
  margin-top: var(--space-6);
  padding-top: var(--space-4);
}





.contact-cta-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  padding: 0.65rem 1.35rem;
  border-radius: var(--radius-sm);
  background: var(--color-rosa-carmin);
  color: var(--color-blanco);
  font-size: 0.88rem;
  font-weight: 700;
  text-decoration: none;
  box-shadow: 0 4px 14px rgba(243, 0, 96, 0.25);
  transition: transform 0.15s ease, background-color 0.15s ease;
}

.contact-cta-btn:hover {
  background: #db0056;
  transform: translateY(-2px);
}

/* Skeleton */
.line-skeleton {
  max-width: 100%;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  padding: 2rem 0;
}

.skeleton-pill {
  width: 8rem;
  height: 2rem;
  border-radius: 999px;
  background: #edeef5;
}

.skeleton-title {
  width: 50%;
  height: 2.5rem;
  border-radius: var(--radius-sm);
  background: #edeef5;
}

.skeleton-body {
  height: 22rem;
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
  .line-master-detail {
    grid-template-columns: 1fr;
  }

  .line-sidebar {
    position: static;
  }
}
</style>
