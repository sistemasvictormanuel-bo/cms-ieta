<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRoute, useRouter } from 'vue-router'
import {
  Compass,
  Download,
  ExternalLink,
  Eye,
  FileText,
  GraduationCap,
  Search,
  SlidersHorizontal,
  Sparkles,
  Target,
  Users,
} from '@lucide/vue'
import ContentCard from '@/components/common/ContentCard.vue'
import PersonCard from '@/components/common/PersonCard.vue'
import EditorJsContent from '@/components/common/EditorJsContent.vue'
import EmptyState from '@/components/common/EmptyState.vue'
import LoadMoreButton from '@/components/common/LoadMoreButton.vue'
import TabNav from '@/components/common/TabNav.vue'
import SectionHeaderBand from '@/components/layout/SectionHeaderBand.vue'
import { SobreNosotrosRepository, PersonalRepository } from '@/api/repositories.js'
import type { StaffApiItem } from '@/api/types'
import { mediaUrl, unwrapResource } from '@/api/response'
import { usePaginatedResource } from '@/composables/usePaginatedResource'
import { useGsapReveal } from '@/composables/useGsapReveal'

import aboutMissionImg from '@/assets/images/acerca-de/mision.jpeg'
import aboutVisionImg from '@/assets/images/acerca-de/vision.jpeg'
import aboutObjectivesImg from '@/assets/images/acerca-de/objetivos.jpeg'

// Nombres y rutas de archivos configuradas en código para incorporar (NO VISIBLES AL PÚBLICO):
// - Misión: assets/images/acerca-de/mision.jpeg
// - Visión: assets/images/acerca-de/vision.jpeg
// - Objetivos: assets/images/acerca-de/objetivos.jpeg
const missionImagePath = 'assets/images/acerca-de/mision.jpeg'
const visionImagePath = 'assets/images/acerca-de/vision.jpeg'
const objectivesImagePath = 'assets/images/acerca-de/objetivos.jpeg'

const REGULATION_DRIVE_URL = 'https://drive.google.com/file/d/16txUClDbVJTTxl0bQp39WEylU3WaH2gU/view'
const REGULATION_PREVIEW_URL = 'https://drive.google.com/file/d/16txUClDbVJTTxl0bQp39WEylU3WaH2gU/preview'
const REGULATION_DOWNLOAD_URL = 'https://drive.google.com/uc?export=download&id=16txUClDbVJTTxl0bQp39WEylU3WaH2gU'

const defaultMission =
  'El Instituto de Estadística Teórica y Aplicada (IETA) tiene como misión generar, desarrollar y difundir conocimiento científico riguroso en estadística y ciencia de datos, aportando soluciones metodológicas de alto impacto para la investigación interdisciplinaria y el progreso social y económico del país.'
const defaultVision =
  'Consolidarse como el centro de excelencia y referente nacional e internacional en investigación estadística teórica y aplicada, liderando la formación avanzada, la analítica predictiva y la transferencia científica hacia la sociedad y la academia.'

const displayMission = computed(() => content.value?.mision || defaultMission)
const displayVision = computed(() => content.value?.vision || defaultVision)

interface AboutContent {
  id: number
  mision: string
  vision: string
  historia: string
  objetivos: unknown
  contenido: unknown
}

const { t } = useI18n()
const route = useRoute()
const router = useRouter()
const aboutRoot = ref<HTMLElement | null>(null)

const tabs = computed(() => [
  { value: 'quienes-somos', label: t('tabs.whoWeAre') },
  { value: 'mision-vision', label: t('tabs.missionVision') },
  { value: 'objetivos', label: t('tabs.objectives') },
  { value: 'personal', label: t('tabs.staff') },
  { value: 'reglamento', label: t('tabs.regulations') },
])

const activeTab = computed({
  get() {
    const routeTab = route.params.tab
    return typeof routeTab === 'string' && tabs.value.some((tab) => tab.value === routeTab)
      ? routeTab
      : 'quienes-somos'
  },
  set(tab: string) {
    void router.replace({
      name: 'acerca-de',
      params: { tab: tab === 'quienes-somos' ? undefined : tab },
    })
  },
})

const content = ref<AboutContent | null>(null)
const staffResource = usePaginatedResource<StaffApiItem>((page) => PersonalRepository.list({ page }))
const loading = ref(true)
const error = ref(false)
const apiBaseUrl = import.meta.env.VITE_API_URL

const staffSearch = ref('')
const staffRoleFilter = ref('')
const staffSort = ref<'name-asc' | 'name-desc'>('name-asc')

function staffMemberName(member: StaffApiItem): string {
  return (
    member.persona?.nombre_completo ||
    [member.persona?.p_nombre, member.persona?.p_apellido_paterno, member.persona?.p_apellido_materno]
      .filter(Boolean)
      .join(' ') ||
    'Personal IETA'
  )
}

const staffRoles = computed(() => {
  return [...new Set(staffResource.items.value.map((m) => m.cargo?.nombre).filter(Boolean))] as string[]
})

const filteredStaff = computed(() => {
  const q = staffSearch.value.trim().toLocaleLowerCase()
  const role = staffRoleFilter.value

  let list = staffResource.items.value.filter((member) => {
    if (member.estado === false) return false
    const name = staffMemberName(member).toLocaleLowerCase()
    const desc = (member.descripcion || '').toLocaleLowerCase()
    const matchesQ = !q || name.includes(q) || desc.includes(q)
    const matchesRole = !role || member.cargo?.nombre === role
    return matchesQ && matchesRole
  })

  list = [...list].sort((a, b) => {
    if (staffSort.value === 'name-desc') {
      return staffMemberName(b).localeCompare(staffMemberName(a), 'es')
    }
    return staffMemberName(a).localeCompare(staffMemberName(b), 'es')
  })
  return list
})

interface ExtractedImage {
  url: string
  caption?: string
}

function extractImagesFromEditorJs(contentData: unknown): ExtractedImage[] {
  if (!contentData) return []
  let blocks: any[] = []
  if (typeof contentData === 'string') {
    try {
      const parsed = JSON.parse(contentData)
      blocks = parsed.blocks || []
    } catch {
      return []
    }
  } else if (typeof contentData === 'object' && contentData !== null) {
    blocks = (contentData as any).blocks || []
  }

  const images: ExtractedImage[] = []
  for (const b of blocks) {
    if (b.type === 'image') {
      const rawUrl = b.data?.file?.url || b.data?.url
      if (rawUrl) {
        images.push({
          url: rawUrl,
          caption: b.data?.caption || '',
        })
      }
    }
  }
  return images
}

function extractVideoFromEditorJs(contentData: unknown): string | null {
  if (!contentData) return null
  let blocks: any[] = []
  if (typeof contentData === 'string') {
    try {
      blocks = JSON.parse(contentData).blocks || []
    } catch {
      return null
    }
  } else if (typeof contentData === 'object' && contentData !== null) {
    blocks = (contentData as any).blocks || []
  }

  for (const b of blocks) {
    if (b.type === 'embed' && (b.data?.embed || b.data?.source)) {
      return b.data.embed || b.data.source
    }
    if (b.type === 'video' && (b.data?.url || b.data?.file?.url)) {
      return b.data.url || b.data.file.url
    }
  }
  return null
}

function filterOutMediaFromEditorJs(contentData: unknown): unknown {
  if (!contentData) return null
  let obj: any = null
  if (typeof contentData === 'string') {
    try {
      obj = JSON.parse(contentData)
    } catch {
      return contentData
    }
  } else if (typeof contentData === 'object' && contentData !== null) {
    obj = JSON.parse(JSON.stringify(contentData))
  }
  if (obj && Array.isArray(obj.blocks)) {
    obj.blocks = obj.blocks.filter((b: any) => b.type !== 'image' && b.type !== 'embed' && b.type !== 'video')
    return obj
  }
  return contentData
}

const founderImages = computed(() => extractImagesFromEditorJs(content.value?.contenido))
const institutionVideoUrl = computed(() => extractVideoFromEditorJs(content.value?.contenido))
const remainingContent = computed(() => filterOutMediaFromEditorJs(content.value?.contenido))

const raulImage = computed(() => {
  const match = founderImages.value.find((img) => /raul|marquiegui/i.test(img.caption || ''))
  return match?.url || founderImages.value[0]?.url
})

const zapataImage = computed(() => {
  const match = founderImages.value.find((img) => /zapata|luis/i.test(img.caption || ''))
  return match?.url || (founderImages.value.length > 1 ? founderImages.value[1]?.url : undefined)
})

const ramosImage = computed(() => {
  const match = founderImages.value.find((img) => /ramos|pablo/i.test(img.caption || ''))
  return match?.url || (founderImages.value.length > 2 ? founderImages.value[2]?.url : undefined)
})

useGsapReveal(aboutRoot, {
  selector: '.about-reveal',
  stagger: 0.08,
  y: 18,
})

async function loadAboutContent() {
  loading.value = true
  error.value = false

  try {
    const response = await SobreNosotrosRepository.get()
    content.value = (unwrapResource<AboutContent>(response) || response) as AboutContent
  } catch {
    error.value = true
  } finally {
    loading.value = false
  }
}

async function loadStaff() {
  await staffResource.refresh()
}

onMounted(() => {
  void loadAboutContent()
  void loadStaff()
})
</script>

<template>
  <div ref="aboutRoot">
    <SectionHeaderBand
      title="Acerca de"
      subtitle="Identidad, trayectoria, objetivos y el equipo humano del IETA."
    />

    <div class="container view-body">
      <!-- Tabs Navigation -->
      <div class="tabs-wrapper about-reveal">
        <TabNav v-model="activeTab" :items="tabs" :label="t('aria.aboutSections')" />
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="about-skeleton" role="status" aria-live="polite">
        <div class="skeleton-line skeleton-line--title"></div>
        <div class="skeleton-box"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="content-state" role="alert">
        <EmptyState
          title="No se pudo cargar la información institucional"
          description="Ocurrió un error al consultar los datos de Acerca de."
          action-label="Reintentar"
          @action="loadAboutContent"
        />
      </div>

      <template v-else-if="content">
        <Transition name="tab-panel-fade" mode="out-in">
          <div :key="activeTab" class="about-tab-panel-wrap">
            <!-- 1. QUIÉNES SOMOS -->
            <section v-if="activeTab === 'quienes-somos'" class="about-panel about-panel--full">
              <!-- Top Centered Intro -->
              <div class="about-intro-section">
                <h2 class="about-intro-title">Instituto de Estadística Teórica y Aplicada</h2>
                <p v-if="content.mision" class="about-intro-lead">
                  {{ content.mision }}
                </p>
              </div>

              <!-- Top Video Section: Puesto en la parte superior antes del contenido, sin caption -->
              <div v-if="institutionVideoUrl" class="about-video-section">
                <div class="about-video-wrapper">
                  <iframe
                    :src="institutionVideoUrl"
                    title="Video Institucional IETA"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                    loading="lazy"
                  ></iframe>
                </div>
              </div>

              <!-- Full-Width Dark Historia Banner -->
              <div v-if="content.historia" class="about-history-banner">
                <div class="about-history-banner__inner">
                  <div class="about-history-banner__text">
                    <h2 class="about-history-banner__title">Historia</h2>
                    <div class="about-history-copy">
                      <EditorJsContent :content="content.historia" :api-base-url="apiBaseUrl" />
                    </div>
                  </div>

                  <!-- Right: Founders Cards -->
                  <div class="about-history-banner__founders">
                    <div class="founder-card">
                      <div class="founder-card__avatar">
                        <img
                          v-if="raulImage"
                          :src="mediaUrl(raulImage)"
                          alt="Lic. Raúl Marquiegui Navarro"
                          class="founder-photo"
                        />
                        <span v-else class="founder-initials">RM</span>
                      </div>
                      <span class="founder-card__name">Lic. Raúl Marquiegui Navarro</span>
                      <span class="founder-card__role">Jefe de Carrera (1985)</span>
                    </div>

                    <div class="founder-card">
                      <div class="founder-card__avatar">
                        <img
                          v-if="zapataImage"
                          :src="mediaUrl(zapataImage)"
                          alt="Lic. Luís Zapata Escobar"
                          class="founder-photo"
                        />
                        <span v-else class="founder-initials">LZ</span>
                      </div>
                      <span class="founder-card__name">Lic. Luís Zapata Escobar</span>
                      <span class="founder-card__role">Decano FCPN (1985)</span>
                    </div>

                    <div v-if="ramosImage" class="founder-card">
                      <div class="founder-card__avatar">
                        <img
                          :src="mediaUrl(ramosImage)"
                          alt="Lic. Pablo Ramos Sánchez"
                          class="founder-photo"
                        />
                      </div>
                      <span class="founder-card__name">Lic. Pablo Ramos Sánchez</span>
                      <span class="founder-card__role">Rector UMSA (1985)</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Bottom Section: Contenido Institucional e Investigación (sin duplicar imágenes) -->
              <div v-if="remainingContent" class="about-content-section">
                <EditorJsContent :content="remainingContent" :api-base-url="apiBaseUrl" />
              </div>
            </section>

            <!-- 2. MISIÓN Y VISIÓN -->
            <section v-else-if="activeTab === 'mision-vision'" class="about-panel about-panel--showcase about-reveal">
              <header class="panel-header panel-header--centered">
                <h2 class="panel-title">Misión y Visión Estratégica</h2>

              </header>

              <div class="mission-vision-showcase">
                <!-- Misión Showcase Block -->
                <article class="mv-showcase-card mv-showcase-card--mission">
                  <div class="mv-showcase-visual">
                    <img
                      :src="aboutMissionImg"
                      alt="Misión Institucional IETA"
                      class="mv-showcase-img"
                      loading="lazy"
                    />

                  </div>

                  <div class="mv-showcase-body">
                    <h3 class="mv-showcase-heading">{{ t('about.missionHeading') }}</h3>
                    <p class="mv-showcase-desc">{{ displayMission }}</p>


                  </div>
                </article>

                <!-- Visión Showcase Block -->
                <article class="mv-showcase-card mv-showcase-card--vision mv-showcase-card--reversed">
                  <div class="mv-showcase-visual">
                    <img
                      :src="aboutVisionImg"
                      alt="Visión Institucional IETA"
                      class="mv-showcase-img"
                      loading="lazy"
                    />
                  </div>

                  <div class="mv-showcase-body">
                    <h3 class="mv-showcase-heading">{{ t('about.visionHeading') }}</h3>
                    <p class="mv-showcase-desc">{{ displayVision }}</p>


                  </div>
                </article>
              </div>
            </section>

            <!-- 3. OBJETIVOS -->
            <section v-else-if="activeTab === 'objetivos'" class="about-panel about-panel--showcase about-reveal">
              <header class="panel-header panel-header--centered">
                <h2 class="panel-title">{{ t('tabs.objectives') }}</h2>

              </header>

              <div class="objectives-showcase-container">
                <div class="objectives-visual-col">
                  <div class="objectives-img-frame">
                    <img
                      :src="aboutObjectivesImg"
                      alt="Objetivos Estratégicos IETA"
                      class="objectives-img"
                      loading="lazy"
                    />
                  </div>
                </div>

                <div class="objectives-content-col">
                  <div v-if="content?.objetivos" class="objectives-editor-box">
                    <EditorJsContent :content="content.objetivos" :api-base-url="apiBaseUrl" />
                  </div>
                  <div v-else class="objectives-feature-list">
                    <div class="objective-feature-item">
                      <span class="objective-feature-number">01</span>
                      <div class="objective-feature-text">
                        <h4>Investigación e Innovación</h4>
                        <p>Desarrollar y validar metodologías probabilísticas, computacionales y de inferencia aplicadas a la realidad nacional.</p>
                      </div>
                    </div>
                    <div class="objective-feature-item">
                      <span class="objective-feature-number">02</span>
                      <div class="objective-feature-text">
                        <h4>Publicación y Difusión Científica</h4>
                        <p>Fortalecer la producción de la Revista Varianza y divulgar resultados en congresos y revistas indexadas internacionales.</p>
                      </div>
                    </div>
                    <div class="objective-feature-item">
                      <span class="objective-feature-number">03</span>
                      <div class="objective-feature-text">
                        <h4>Transferencia y Consultoría</h4>
                        <p>Prestar asistencia técnica de alto nivel a organismos públicos, empresas y la comunidad académica en toma de decisiones basada en datos.</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </section>

            <!-- 4. PERSONAL IETA -->
            <section v-else-if="activeTab === 'personal'" class="about-panel about-panel--wide about-reveal">
              <div
                v-if="staffResource.loading.value && staffResource.items.value.length === 0"
                class="staff-skeleton-grid"
                role="status"
              >
                <div v-for="n in 6" :key="n" class="skeleton-staff-card"></div>
              </div>

              <div
                v-else-if="staffResource.error.value && staffResource.items.value.length === 0"
                class="content-state"
                role="alert"
              >
                <EmptyState
                  title="No se pudo cargar el listado de personal"
                  description="Por favor, inténtalo de nuevo."
                  action-label="Reintentar"
                  @action="loadStaff"
                />
              </div>

              <!-- Top Filter Bar (Reference Image 1 Style) -->
              <div class="people-filter-bar">
                <div class="people-filter-bar__left">
                  <div class="filter-by-label">
                    <SlidersHorizontal :size="16" aria-hidden="true" />
                    <span>Filtrar por</span>
                  </div>

                  <div class="filter-select-wrapper">
                    <select v-model="staffRoleFilter" class="filter-select-input" aria-label="Filtrar por cargo">
                      <option value="">Cargo Institucional</option>
                      <option v-for="role in staffRoles" :key="role" :value="role">{{ role }}</option>
                    </select>
                  </div>

                  <div class="filter-search-wrapper">
                    <Search :size="15" class="filter-search-icon" aria-hidden="true" />
                    <input
                      v-model="staffSearch"
                      type="search"
                      placeholder="Buscar por nombre..."
                      class="filter-search-input"
                    />
                  </div>
                </div>

                <div class="people-filter-bar__right">
                  <span class="results-count">{{ filteredStaff.length }} Resultados</span>
                  <div class="sort-select-wrapper">
                    <label class="sort-label">Ordenar:</label>
                    <select v-model="staffSort" class="sort-select-input" aria-label="Ordenar personal">
                      <option value="name-asc">Nombre A–Z</option>
                      <option value="name-desc">Nombre Z–A</option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- 4-Column Grid (Reference Image 1 Style) -->
              <div v-if="filteredStaff.length" class="people-grid-4col">
                <PersonCard
                  v-for="member in filteredStaff"
                  :key="member.id"
                  :name="staffMemberName(member)"
                  :role="member.cargo?.nombre || 'Docente Investigador'"
                  :institution="member.fecha_ingreso ? `${member.fecha_ingreso.slice(0, 4)}–Presente · UMSA` : 'Carrera de Estadística · UMSA'"
                  :photo-url="mediaUrl(member.persona?.foto_url)"
                  :to="`/acerca-de/personal/${member.id}`"
                />
              </div>

              <EmptyState
                v-else-if="!staffResource.loading.value && staffResource.items.value.length === 0"
                title="No hay personal registrado"
                description="El directorio de personal se encuentra en actualización."
              />
              <EmptyState
                v-else
                title="No se encontraron coincidencias"
                description="Prueba con otro término de búsqueda o limpia el filtro de cargo."
              />

              <div class="staff-pagination">
                <LoadMoreButton
                  :loading="staffResource.loadingMore.value"
                  :has-more="staffResource.hasMore.value"
                  @load="staffResource.loadMore"
                />
              </div>
            </section>

            <!-- 5. REGLAMENTO -->
            <section v-else-if="activeTab === 'reglamento'" class="about-panel about-panel--wide about-reveal">
              <header class="panel-header panel-header--centered">
                <h2 class="panel-title">{{ t('tabs.regulations') }}</h2>

              </header>

              <!-- Document Banner & Direct Drive Actions -->
              <div class="regulation-document-banner">
                <div class="regulation-doc-info">
                  <div class="regulation-icon-badge">
                    <FileText :size="28" aria-hidden="true" />
                  </div>
                  <div>
                    <span class="regulation-type-pill">Documento Oficial</span>
                    <h3 class="regulation-title">Reglamento Interno y Estatutos del IETA</h3>
                    <p class="regulation-desc">
                      Aprobado mediante la Resolución N° 082/94 del Honorable Consejo Universitario (HCU) y articulado a los estatutos de la Universidad Mayor de San Andrés (UMSA) y la Facultad de Ciencias Puras y Naturales (FCPN).
                    </p>
                  </div>
                </div>

                <div class="regulation-actions">
                  <a
                    :href="REGULATION_DOWNLOAD_URL"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="btn-regulation-download"
                  >
                    <Download :size="16" aria-hidden="true" />
                    <span>Descargar PDF</span>
                  </a>
                  <a
                    :href="REGULATION_DRIVE_URL"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="btn-regulation-drive"
                  >
                    <ExternalLink :size="16" aria-hidden="true" />
                    <span>Abrir en Google Drive</span>
                  </a>
                </div>
              </div>

              <!-- Interactive Google Drive Embedded PDF Viewer -->
              <div class="regulation-pdf-frame-wrapper">
                <div class="regulation-frame-header">
                </div>
                <iframe
                  :src="REGULATION_PREVIEW_URL"
                  class="regulation-pdf-iframe"
                  title="Visor PDF Reglamento Interno del IETA"
                  allow="autoplay"
                ></iframe>
              </div>
            </section>

        <EmptyState
          v-else
          :title="t('about.sectionUnavailable')"
          :description="t('about.sectionUnavailableDescription')"
        />
          </div>
        </Transition>
      </template>

      <EmptyState v-else :title="t('common.noResults')" />
    </div>
  </div>
</template>

<style scoped>
.view-body {
  padding-block: var(--space-4) var(--space-8);
  min-height: 48vh;
}

.tabs-wrapper {
  position: relative;
  z-index: 50;
  margin-bottom: var(--space-4);
}

.about-panel {
  max-width: 78ch;
  margin-inline: auto;
  padding-block: var(--space-2);
}

.about-tab-panel-wrap {
  position: relative;
  z-index: 10;
  width: 100%;
}

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
  transform: translateY(-8px);
}

@media (prefers-reduced-motion: reduce) {
  .tab-panel-fade-enter-active,
  .tab-panel-fade-leave-active {
    transition: none;
  }
}

.about-panel--wide {
  max-width: 100%;
}

.about-panel--full {
  max-width: 100%;
  margin-inline: 0;
  padding-block: 0;
}

/* 1. Quiénes Somos (PDF Page 1) */
.about-intro-section {
  max-width: 860px;
  margin-inline: auto;
  text-align: center;
  padding-top: var(--space-4);
  margin-bottom: 2.5rem;
}

.about-intro-title {
  font-size: clamp(1.8rem, 3.2vw, 2.5rem);
  font-weight: 700;
  color: #1b144b;
  margin-bottom: 1.25rem;
  line-height: 1.2;
}

.about-intro-lead {
  font-size: 1.05rem;
  line-height: 1.75;
  color: #444b62;
  margin-bottom: 1.5rem;
}

/* Video en la parte superior antes del contenido, sin caption */
.about-video-section {
  max-width: 920px;
  margin-inline: auto;
  margin-bottom: 3.5rem;
}

.about-video-wrapper {
  position: relative;
  width: 100%;
  aspect-ratio: 16 / 9;
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 16px 36px -8px rgba(27, 20, 75, 0.16);
  background: #0f172a;
}

.about-video-wrapper iframe {
  width: 100%;
  height: 100%;
  border: 0;
}

/* Gráficos para Misión, Visión y Objetivos */
.mv-graphic-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
  min-height: 16rem;
  border-radius: 12px;
  position: relative;
  overflow: hidden;
}

.mv-graphic-container--indigo {
  background: linear-gradient(135deg, rgba(53, 68, 159, 0.14) 0%, rgba(43, 27, 109, 0.08) 100%);
  color: var(--color-indigo);
  border: 1px solid rgba(53, 68, 159, 0.15);
}

.mv-graphic-container--pink {
  background: linear-gradient(135deg, rgba(243, 0, 96, 0.12) 0%, rgba(113, 88, 159, 0.08) 100%);
  color: #f30060;
  border: 1px solid rgba(243, 0, 96, 0.15);
}

.mv-graphic-icon {
  margin-bottom: 1rem;
  transition: transform 0.3s ease;
}

.mv-showcase-card:hover .mv-graphic-icon {
  transform: scale(1.12);
}

.objectives-graphic-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 20rem;
  background: linear-gradient(135deg, rgba(53, 68, 159, 0.12) 0%, rgba(243, 0, 96, 0.06) 100%);
  border: 1px solid rgba(53, 68, 159, 0.15);
  border-radius: 14px;
}

.objectives-graphic-icon {
  color: var(--color-indigo);
  margin-bottom: 1.25rem;
  transition: transform 0.3s ease;
}

.objectives-img-frame:hover .objectives-graphic-icon {
  transform: rotate(25deg) scale(1.08);
}

/* Full Width Dark Historia Banner */
.about-history-banner {
  background: #2b1b6d;
  color: #fff;
  padding-block: clamp(3rem, 5vw, 4.5rem);
  padding-inline: clamp(1.5rem, 4vw, 3.5rem);
  margin-block: 3rem;
  border-radius: 16px;
}

.about-history-banner__inner {
  display: grid;
  grid-template-columns: 1.25fr 0.75fr;
  gap: clamp(2rem, 4vw, 4.5rem);
  align-items: center;
}

.about-history-banner__title {
  font-size: clamp(2rem, 3.5vw, 2.75rem);
  font-weight: 700;
  color: #fff;
  margin-bottom: 1.25rem;
}

.about-history-copy {
  color: rgba(255, 255, 255, 0.94);
  font-size: 1rem;
  line-height: 1.8;
  display: flex;
  flex-direction: column;
  gap: 1.1rem;
}

.about-history-copy :deep(p) {
  color: rgba(255, 255, 255, 0.94) !important;
  font-size: 1rem;
  line-height: 1.8;
  margin-bottom: 1rem;
}

.about-history-banner__founders {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  align-items: center;
}

.founder-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.35rem;
  text-align: center;
  width: 100%;
  max-width: 280px;
}

.founder-card__avatar {
  width: 100%;
  height: 9rem;
  border-radius: var(--radius-sm);
  border: 2px solid rgba(255, 255, 255, 0.25);
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 10px 24px rgba(0, 0, 0, 0.3);
  margin-bottom: 0.35rem;
}

.founder-initials {
  color: rgba(255, 255, 255, 0.4);
  font-size: 2.75rem;
  font-weight: 800;
  letter-spacing: 0.05em;
}

.founder-card__name {
  color: #fff;
  font-size: 0.92rem;
  font-weight: 700;
}

.founder-card__role {
  color: rgba(255, 255, 255, 0.72);
  font-size: 0.75rem;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

/* Institutional Content Section (EditorJS) */
.about-content-section {
  max-width: 860px;
  margin-inline: auto;
  padding-block: var(--space-4) var(--space-8);
}

/* Mission & Vision Showcase */
.about-panel--showcase {
  max-width: 1040px;
  margin-inline: auto;
}

.mission-vision-showcase {
  display: flex;
  flex-direction: column;
  gap: 2.5rem;
  margin-top: var(--space-4);
}

.mv-showcase-card {
  display: grid;
  grid-template-columns: 24rem minmax(0, 1fr);
  gap: 2.25rem;
  background: var(--color-blanco);
  border-radius: var(--radius-md);
  border: 1px solid rgba(220, 224, 235, 0.85);
  box-shadow: 0 6px 24px rgba(27, 20, 75, 0.05);
  overflow: hidden;
  align-items: center;
  transition: transform 0.24s ease, box-shadow 0.24s ease;
}

.mv-showcase-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 14px 34px -6px rgba(27, 20, 75, 0.1);
}

.mv-showcase-card--reversed {
  grid-template-columns: minmax(0, 1fr) 24rem;
}

.mv-showcase-card--reversed .mv-showcase-visual {
  order: 2;
}

.mv-showcase-card--reversed .mv-showcase-body {
  order: 1;
}

.mv-showcase-visual {
  position: relative;
  height: 100%;
  min-height: 19rem;
  overflow: hidden;
  background: #f0f3fa;
}

.mv-showcase-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.4s ease;
}

.mv-showcase-card:hover .mv-showcase-img {
  transform: scale(1.04);
}

.mv-showcase-visual__tag {
  position: absolute;
  top: 1rem;
  left: 1rem;
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  background: rgba(27, 20, 75, 0.85);
  backdrop-filter: blur(8px);
  color: #ffffff;
  padding: 0.35rem 0.75rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.mv-showcase-visual__tag--pink {
  background: rgba(243, 0, 96, 0.9);
}

.mv-showcase-body {
  padding: 2.25rem 2.25rem 2.25rem 0.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.mv-showcase-card--reversed .mv-showcase-body {
  padding: 2.25rem 0.5rem 2.25rem 2.25rem;
}

.mv-showcase-sub {
  font-size: 0.78rem;
  font-weight: 800;
  color: var(--color-indigo);
  letter-spacing: 0.05em;
  text-transform: uppercase;
}

.mv-showcase-sub--pink {
  color: var(--color-rosa-carmin);
}

.mv-showcase-heading {
  margin: 0;
  font-size: 1.65rem;
  font-weight: 700;
  color: #1b144b;
  line-height: 1.25;
}

.mv-showcase-desc {
  margin: 0;
  color: #4b5268;
  font-size: 1.02rem;
  line-height: 1.7;
}

.mv-showcase-pillars {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-top: 0.5rem;
}

.mv-pillar-item {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  font-size: 0.92rem;
  font-weight: 600;
  color: #2b3144;
}

.pillar-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  flex-shrink: 0;
}

.pillar-dot--indigo {
  background: var(--color-indigo);
}

.pillar-dot--pink {
  background: var(--color-rosa-carmin);
}

/* Objectives Showcase */
.objectives-showcase-container {
  display: grid;
  grid-template-columns: 22rem minmax(0, 1fr);
  gap: 2.25rem;
  background: var(--color-blanco);
  border-radius: var(--radius-md);
  border: 1px solid rgba(220, 224, 235, 0.85);
  box-shadow: 0 6px 24px rgba(27, 20, 75, 0.05);
  padding: 1.75rem;
  align-items: stretch;
}

.objectives-visual-col {
  display: flex;
}

.objectives-img-frame {
  position: relative;
  width: 100%;
  min-height: 18rem;
  border-radius: var(--radius-sm);
  overflow: hidden;
  background: #f0f3fa;
}

.objectives-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.4s ease;
}

.objectives-img-frame:hover .objectives-img {
  transform: scale(1.04);
}

.objectives-badge-pill {
  position: absolute;
  bottom: 1rem;
  left: 1rem;
  right: 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: rgba(27, 20, 75, 0.9);
  backdrop-filter: blur(8px);
  color: #ffffff;
  padding: 0.55rem 0.85rem;
  border-radius: 8px;
  font-size: 0.82rem;
  font-weight: 600;
}

.objectives-content-col {
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.objectives-feature-list {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.objective-feature-item {
  display: flex;
  gap: 1.25rem;
  padding: 1rem;
  border-radius: 8px;
  background: #f8f9fc;
  border: 1px solid #e7ecf5;
  transition: transform 0.18s ease;
}

.objective-feature-item:hover {
  transform: translateX(4px);
}

.objective-feature-number {
  font-size: 1.35rem;
  font-weight: 800;
  color: var(--color-rosa-carmin);
  line-height: 1;
}

.objective-feature-text h4 {
  margin: 0 0 0.25rem;
  font-size: 1.05rem;
  font-weight: 700;
  color: #1b144b;
}

.objective-feature-text p {
  margin: 0;
  font-size: 0.9rem;
  color: #555c73;
  line-height: 1.55;
}

/* Regulation Document & Google Drive PDF Viewer */
.regulation-document-banner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1.5rem;
  flex-wrap: wrap;
  padding: 1.5rem 1.75rem;
  background: #ffffff;
  border-radius: var(--radius-md);
  border: 1px solid rgba(220, 224, 235, 0.85);
  box-shadow: 0 4px 18px rgba(27, 20, 75, 0.04);
  margin-bottom: 1.5rem;
}

.regulation-doc-info {
  display: flex;
  gap: 1.25rem;
  align-items: flex-start;
  max-width: 48rem;
}

.regulation-icon-badge {
  display: grid;
  width: 3.5rem;
  height: 3.5rem;
  place-items: center;
  border-radius: var(--radius-sm);
  background: rgba(53, 68, 159, 0.1);
  color: var(--color-indigo);
  flex-shrink: 0;
}

.regulation-type-pill {
  display: inline-block;
  font-size: 0.72rem;
  font-weight: 800;
  color: var(--color-rosa-carmin);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 0.25rem;
}

.regulation-title {
  margin: 0 0 0.35rem;
  font-size: 1.25rem;
  font-weight: 700;
  color: #1b144b;
}

.regulation-desc {
  margin: 0;
  font-size: 0.92rem;
  color: #4b5268;
  line-height: 1.6;
}

.regulation-actions {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.btn-regulation-download {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  padding: 0.65rem 1.25rem;
  border-radius: var(--radius-sm);
  background: var(--color-rosa-carmin);
  color: #ffffff;
  font-size: 0.88rem;
  font-weight: 700;
  text-decoration: none;
  box-shadow: 0 4px 14px rgba(243, 0, 96, 0.3);
  transition: transform 0.18s ease, background-color 0.18s ease;
}

.btn-regulation-download:hover {
  transform: translateY(-2px);
  background: #db0056;
}

.btn-regulation-drive {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  padding: 0.65rem 1.25rem;
  border-radius: var(--radius-sm);
  background: #f0f3fa;
  border: 1px solid #dce0eb;
  color: var(--color-indigo);
  font-size: 0.88rem;
  font-weight: 700;
  text-decoration: none;
  transition: transform 0.18s ease, background-color 0.18s ease;
}

.btn-regulation-drive:hover {
  transform: translateY(-2px);
  background: #e4e9f5;
}

.regulation-pdf-frame-wrapper {
  background: #ffffff;
  border-radius: var(--radius-md);
  border: 1px solid rgba(220, 224, 235, 0.85);
  box-shadow: 0 8px 30px rgba(27, 20, 75, 0.07);
  overflow: hidden;
}

.regulation-frame-header {
  display: flex;
  align-items: center;
  gap: 0.45rem;
  padding: 0.75rem 1.25rem;
  background: #1b144b;
  color: #ffffff;
}

.frame-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
}

.frame-dot--red { background: #ff5f56; }
.frame-dot--yellow { background: #ffbd2e; }
.frame-dot--green { background: #27c93f; }

.frame-title {
  margin-left: 0.5rem;
  font-size: 0.82rem;
  font-weight: 600;
  color: #d1d5eb;
}

.regulation-pdf-iframe {
  width: 100%;
  height: min(72vh, 48rem);
  min-height: 32rem;
  border: 0;
  display: block;
  background: #525659;
}

/* Panel Header */
.panel-header {
  margin-bottom: var(--space-4);
  text-align: left;
}

.panel-eyebrow {
  display: inline-block;
  color: var(--color-rosa-carmin);
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.05em;
  margin-bottom: 0.35rem;
}

.panel-title {
  color: #1b144b;
  font-size: clamp(1.6rem, 3.5vw, 2.25rem);
  font-weight: 700;
  line-height: 1.22;
}

.panel-lead {
  color: #555c73;
  font-size: var(--text-large-size);
  margin-top: 0.4rem;
}

/* About Cards */
.about-card {
  padding: clamp(1.25rem, 3vw, 2rem);
  border: 1px solid var(--color-desactivado);
  border-radius: var(--radius-md);
  background: var(--color-blanco);
  box-shadow: 0 4px 16px rgba(20, 22, 28, 0.04);
  margin-bottom: var(--space-4);
}

.history-card,
.regulation-card {
  display: flex;
  gap: var(--space-3);
  align-items: flex-start;
}

.card-icon-badge {
  display: grid;
  width: 3rem;
  height: 3rem;
  place-items: center;
  border-radius: var(--radius-sm);
  background: rgba(53, 68, 159, 0.08);
  color: var(--color-indigo);
  flex-shrink: 0;
}

.card-body {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  line-height: 1.7;
}

.card-subtitle {
  color: var(--color-indigo);
  font-size: var(--text-h5-size);
  font-weight: 700;
}

/* Mission & Vision Grid */
.mission-vision-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: clamp(1rem, 3vw, 2rem);
  margin-top: var(--space-2);
}

.mv-card {
  position: relative;
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
  padding: clamp(1.5rem, 3vw, 2.25rem);
  border: 1px solid var(--color-desactivado);
  border-radius: var(--radius-md);
  background: var(--color-blanco);
  box-shadow: 0 4px 18px rgba(20, 22, 28, 0.05);
  overflow: hidden;
  transition: transform 0.22s ease, box-shadow 0.22s ease;
}

.mv-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 24px -4px rgba(20, 22, 28, 0.09);
}

.mv-card__accent {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
}

.mv-card--mission .mv-card__accent {
  background: var(--color-indigo);
}

.mv-card--vision .mv-card__accent {
  background: var(--color-rosa-carmin);
}

.mv-card__icon-box {
  display: grid;
  width: 3.5rem;
  height: 3.5rem;
  place-items: center;
  border-radius: 50%;
  background: var(--color-superficie);
  color: var(--color-indigo);
}

.mv-card--vision .mv-card__icon-box {
  color: var(--color-rosa-carmin);
  background: rgba(243, 0, 96, 0.08);
}

.mv-card__title {
  color: var(--color-indigo);
  font-size: var(--text-h5-size);
  font-weight: 700;
}

.mv-card__text {
  color: #3b4258;
  font-size: var(--text-body-size);
  line-height: 1.7;
}

.founder-photo {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center top;
  border-radius: var(--radius-sm);
  display: block;
}

/* People Filter Bar (Reference Image 1) */
.people-filter-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 1.25rem;
  padding: 0.85rem 0 1.25rem;
  border-bottom: 2px solid #111827;
  margin-bottom: 2rem;
}

.people-filter-bar__left {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.filter-by-label {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  font-size: 0.92rem;
  font-weight: 800;
  color: #111827;
  letter-spacing: -0.01em;
}

.filter-select-input {
  appearance: none;
  background-color: #ffffff;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23374151' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 0.75rem center;
  padding: 0.5rem 2.2rem 0.5rem 0.85rem;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  font-size: 0.88rem;
  font-weight: 600;
  color: #374151;
  cursor: pointer;
}

.filter-select-input:focus {
  outline: 2px solid #35449f;
  outline-offset: 1px;
}

.filter-search-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.filter-search-icon {
  position: absolute;
  left: 0.75rem;
  color: #9ca3af;
  pointer-events: none;
}

.filter-search-input {
  padding: 0.5rem 0.85rem 0.5rem 2.2rem;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  font-size: 0.88rem;
  color: #111827;
  min-width: 14rem;
}

.filter-search-input:focus {
  outline: 2px solid #35449f;
  outline-offset: 1px;
}

.people-filter-bar__right {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  margin-left: auto;
}

.results-count {
  font-size: 0.88rem;
  font-weight: 600;
  color: #4b5563;
}

.sort-select-wrapper {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.sort-label {
  font-size: 0.85rem;
  font-weight: 700;
  color: #111827;
}

.sort-select-input {
  appearance: none;
  background-color: #ffffff;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23374151' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 0.65rem center;
  padding: 0.45rem 2rem 0.45rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  font-size: 0.85rem;
  font-weight: 600;
  color: #111827;
  cursor: pointer;
}

/* 4-Column Grid */
.people-grid-4col {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: clamp(1.25rem, 2.5vw, 2rem);
  margin-top: 1.5rem;
}

/* Staff Pagination */
.staff-pagination {
  display: flex;
  justify-content: center;
  margin-top: var(--space-6);
}

/* Skeletons */
.about-skeleton {
  max-width: 78ch;
  margin-inline: auto;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  padding: 2rem 0;
}

.skeleton-box {
  height: 18rem;
  border-radius: var(--radius-md);
  background: #edeef5;
}

.staff-skeleton-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 1.5rem;
  margin-top: 1.5rem;
}

.skeleton-staff-card {
  height: 16rem;
  border-radius: var(--radius-md);
  background: #edeef5;
}

.content-state {
  display: grid;
  min-height: 20rem;
  align-content: center;
  text-align: center;
}

@media (max-width: 960px) {
  .people-grid-4col,
  .staff-grid,
  .staff-skeleton-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 768px) {
  .about-history-banner__inner {
    grid-template-columns: 1fr;
    text-align: left;
  }

  .about-history-banner__founders {
    flex-direction: row;
    justify-content: center;
    flex-wrap: wrap;
    gap: 1.5rem;
  }
}

@media (max-width: 860px) {
  .mv-showcase-card,
  .mv-showcase-card--reversed {
    grid-template-columns: 1fr;
  }
  .mv-showcase-card--reversed .mv-showcase-visual {
    order: 0;
  }
  .mv-showcase-card--reversed .mv-showcase-body {
    order: 0;
    padding: 1.5rem;
  }
  .mv-showcase-body {
    padding: 1.5rem;
  }
  .objectives-showcase-container {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 640px) {
  .people-filter-bar {
    flex-direction: column;
    align-items: stretch;
  }
  .people-filter-bar__left,
  .people-filter-bar__right {
    width: 100%;
    justify-content: space-between;
  }
  .filter-search-input {
    min-width: 100%;
  }
  .mission-vision-grid {
    grid-template-columns: 1fr;
  }
  .people-grid-4col,
  .staff-grid,
  .staff-skeleton-grid {
    grid-template-columns: 1fr;
  }
  .history-card,
  .regulation-card {
    flex-direction: column;
  }
}
</style>
