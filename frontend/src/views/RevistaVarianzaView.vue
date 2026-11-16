<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { useI18n } from 'vue-i18n'
import {
  ArrowRight,
  BookOpen,
  CheckCircle2,
  ChevronLeft,
  ChevronRight,
  Compass,
  Download,
  ExternalLink,
  Eye,
  FileText,
  LayoutGrid,
  List,
  RotateCcw,
  Search,
  SlidersHorizontal,
  Target,
} from '@lucide/vue'
import EmptyState from '@/components/common/EmptyState.vue'
import LoadMoreButton from '@/components/common/LoadMoreButton.vue'
import EditorJsContent from '@/components/common/EditorJsContent.vue'
import EditorialMemberCard from '@/components/varianza/EditorialMemberCard.vue'
import AuthorProfileCard from '@/components/varianza/AuthorProfileCard.vue'
import MagazineIssueRow from '@/components/varianza/MagazineIssueRow.vue'
import VarianzaHero from '@/components/varianza/VarianzaHero.vue'
import VarianzaSectionNav from '@/components/varianza/VarianzaSectionNav.vue'
import { authorName } from '@/api/normalizers'
import { mediaUrl } from '@/api/response'
import {
  AutoresRepository,
  ComiteEditorialRepository,
  EdicionesVarianzaRepository,
  InstruccionesAutoresRepository,
  RevistaVarianzaRepository,
} from '@/api/repositories.js'
import type {
  AuthorApiItem,
  AuthorGuidelineApiItem,
  EditorialMemberApiItem,
  VarianzaIssueApiItem,
  VarianzaProfileApiItem,
} from '@/api/types'
import { unwrapResource } from '@/api/response'
import { usePaginatedResource } from '@/composables/usePaginatedResource'

import varianzaMissionImg from '@/assets/images/revista/mision.jpeg'
import varianzaVisionImg from '@/assets/images/revista/vision.jpeg'
import varianzaScopeImg from '@/assets/images/revista/alcance.jpeg'

const route = useRoute()
const authorQuery = ref('')
const authorFilters = ref<Record<string, string>>({ specialty: '' })
const authorViewMode = ref<'gallery' | 'list'>('gallery')
const issueQuery = ref('')
const selectedIssueYear = ref('')
const issueSort = ref<'desc' | 'asc'>('desc')

const authorsResource = usePaginatedResource<AuthorApiItem>(
  (page) => AutoresRepository.list({ page, per_page: 100 }),
  { autoFetchAll: true },
)
const committeeResource = usePaginatedResource<EditorialMemberApiItem>(
  (page) => ComiteEditorialRepository.list({ page, per_page: 100 }),
  { autoFetchAll: true },
)
const issuesResource = usePaginatedResource<VarianzaIssueApiItem>(
  (page) => EdicionesVarianzaRepository.list({ page, per_page: 100 }),
  { autoFetchAll: true },
)
const guidelinesResource = usePaginatedResource<AuthorGuidelineApiItem>(
  (page) => InstruccionesAutoresRepository.list({ page, per_page: 100 }),
  { autoFetchAll: true },
)

const varianzaProfile = ref<VarianzaProfileApiItem | null>(null)
const latestIssue = ref<VarianzaIssueApiItem | null>(null)
const profileLoading = ref(true)
const profileError = ref(false)
const latestIssueLoading = ref(true)
const latestIssueError = ref(false)

// Dynamic resolution of the latest issue (strictly from API)
const dynamicLatestIssue = computed<VarianzaIssueApiItem | null>(() => {
  if (latestIssue.value && !Array.isArray(latestIssue.value) && latestIssue.value.titulo) {
    return latestIssue.value
  }
  const items = issuesResource.items.value
  if (items.length > 0) {
    const sorted = [...items].sort((a, b) => {
      const yA = Number(a.anio) || 0
      const yB = Number(b.anio) || 0
      if (yB !== yA) return yB - yA
      const nA = Number(a.numero) || 0
      const nB = Number(b.numero) || 0
      if (nB !== nA) return nB - nA
      const dateA = new Date(a.fecha_publicacion || '').getTime() || 0
      const dateB = new Date(b.fecha_publicacion || '').getTime() || 0
      return dateB - dateA
    })
    return sorted[0] || null
  }
  return null
})

const dynamicCover = computed(() => {
  const coverPath = dynamicLatestIssue.value?.portada_url
  return coverPath ? mediaUrl(coverPath) : undefined
})

function clearIssueFilters() {
  issueQuery.value = ''
  selectedIssueYear.value = ''
  issueSort.value = 'desc'
}

const editorialEmail = computed(() => varianzaProfile.value?.correo_contacto || 'estadistica@umsa.bo')
const latestIssueCover = computed(() => dynamicCover.value)
const apiBaseUrl = import.meta.env.VITE_API_URL

const activeGuidelines = computed(() => guidelinesResource.items.value
  .filter((item) => item.activo !== false)
  .sort((first, second) => (first.orden ?? 0) - (second.orden ?? 0)))

function guidelineAnchor(item: { id: number | string }) {
  return `instruccion-${item.id}`
}

const { t } = useI18n()

const sectionItems = computed(() => [
  { value: 'sobre', label: t('tabs.about'), to: '/revista-varianza' },
  { value: 'comite-editor', label: t('tabs.committee'), to: '/revista-varianza/comite-editor' },
  { value: 'autores', label: t('tabs.authors'), to: '/revista-varianza/autores' },
  { value: 'ediciones', label: t('tabs.editions'), to: '/revista-varianza/ediciones' },
  {
    value: 'instrucciones-autores',
    label: t('tabs.instructions'),
    to: '/revista-varianza/instrucciones-autores',
  },
])

const activeSection = computed(() => {
  const tab = String(route.params.tab ?? '')
  if (!tab) return 'sobre'
  if (tab === 'instrucciones') return 'instrucciones-autores'
  return sectionItems.value.find((item) => item.value === tab)?.value ?? 'sobre'
})

const displayedGuidelines = computed(() => {
  return activeGuidelines.value
})

const selectedGuidelineIndex = ref(0)
const guidelineViewMode = ref<'tabs' | 'all'>('tabs')

function getGuidelineTitle(item: AuthorGuidelineApiItem): string {
  return item.titulo_seccion || item.titulo || `Instrucción ${item.orden ?? item.id}`
}

const selectedGuideline = computed(() => {
  if (displayedGuidelines.value.length === 0) return null
  return displayedGuidelines.value[selectedGuidelineIndex.value] ?? displayedGuidelines.value[0] ?? null
})

function selectGuideline(index: number) {
  selectedGuidelineIndex.value = index
  if (guidelineViewMode.value === 'all') {
    const item = displayedGuidelines.value[index]
    if (item) {
      const el = document.getElementById(guidelineAnchor(item))
      if (el) {
        const top = el.getBoundingClientRect().top + window.scrollY - 120
        window.scrollTo({ top, behavior: 'smooth' })
      }
    }
  }
}

function prevGuideline() {
  if (selectedGuidelineIndex.value > 0) {
    selectGuideline(selectedGuidelineIndex.value - 1)
  }
}

function nextGuideline() {
  if (selectedGuidelineIndex.value < displayedGuidelines.value.length - 1) {
    selectGuideline(selectedGuidelineIndex.value + 1)
  }
}


const availableIssueYears = computed(() => {
  const years = new Set<string>()
  for (const item of issuesResource.items.value) {
    if (item.anio) years.add(String(item.anio))
  }
  return [...years].sort((a, b) => b.localeCompare(a))
})

const filteredIssues = computed(() => {
  const q = issueQuery.value.trim().toLowerCase()
  const y = selectedIssueYear.value.trim()
  const baseItems = issuesResource.items.value
  const list = baseItems.filter((issue) => {
    const matchesQ = !q || issue.titulo.toLowerCase().includes(q) || (issue.numero && String(issue.numero).includes(q))
    const matchesY = !y || String(issue.anio ?? '') === y
    return matchesQ && matchesY
  })
  return [...list].sort((a, b) => {
    const yA = Number(a.anio) || 0
    const yB = Number(b.anio) || 0
    if (yA !== yB) return issueSort.value === 'asc' ? yA - yB : yB - yA
    const nA = Number(a.numero) || 0
    const nB = Number(b.numero) || 0
    return issueSort.value === 'asc' ? nA - nB : nB - nA
  })
})

const selectedAuthorProgram = ref('')
const selectedAuthorSpecialty = ref('')
const authorSort = ref<'name-asc' | 'name-desc'>('name-asc')

const authorPrograms = computed(() => {
  return [...new Set(authorsResource.items.value.map((a) => a.tipo_autor?.nombre).filter(Boolean))] as string[]
})

const authorSpecialties = computed(() => {
  return [...new Set(authorsResource.items.value.map((a) => a.especialidad?.nombre).filter(Boolean))] as string[]
})

const displayedAuthors = computed(() => {
  const query = authorQuery.value.trim().toLocaleLowerCase()
  const prog = selectedAuthorProgram.value
  const spec = selectedAuthorSpecialty.value

  let list = authorsResource.items.value.filter((author) => {
    const matchesQuery = !query || `${authorName(author)} ${author.intereses_cientificos ?? ''}`.toLocaleLowerCase().includes(query)
    const matchesProgram = !prog || author.tipo_autor?.nombre === prog
    const matchesSpecialty = !spec || author.especialidad?.nombre === spec
    return matchesQuery && matchesProgram && matchesSpecialty
  })

  list = [...list].sort((a, b) => {
    if (authorSort.value === 'name-desc') {
      return authorName(b).localeCompare(authorName(a), 'es')
    }
    return authorName(a).localeCompare(authorName(b), 'es')
  })
  return list
})

function authorPath(author: AuthorApiItem) {
  return `/revista-varianza/autores/${encodeURIComponent(author.slug || String(author.id))}`
}

function clearAuthorFilters() {
  authorQuery.value = ''
  selectedAuthorProgram.value = ''
  selectedAuthorSpecialty.value = ''
  authorSort.value = 'name-asc'
}

const committeeGroups = computed(() => {
  const groups = new Map<string, EditorialMemberApiItem[]>()
  for (const member of committeeResource.items.value) {
    const groupName = member.tipo?.trim() || 'Comité Editorial'
    groups.set(groupName, [...(groups.get(groupName) ?? []), member])
  }
  return [...groups.entries()].map(([title, members]) => ({ title, members }))
})

function memberName(member: EditorialMemberApiItem) {
  return member.persona?.nombre_completo?.trim() ||
    [member.persona?.p_nombre, member.persona?.p_apellido_paterno, member.persona?.p_apellido_materno]
      .filter(Boolean)
      .join(' ') ||
    'Integrante del comité'
}

function memberInstitution(member: EditorialMemberApiItem) {
  return [member.institucion?.nombre, member.institucion?.ciudad, member.institucion?.pais]
    .filter(Boolean)
    .join(' · ')
}

watch(activeSection, (section) => {
  if (section === 'autores' && authorsResource.page.value === 0) void authorsResource.refresh()
  if (section === 'comite-editor' && committeeResource.page.value === 0) {
    void committeeResource.refresh()
  }
  if (section === 'ediciones' && issuesResource.page.value === 0) void issuesResource.refresh()
  if (section === 'instrucciones-autores' && guidelinesResource.page.value === 0) void guidelinesResource.refresh()
}, { immediate: true })

async function loadEditorialProfile() {
  profileLoading.value = true
  profileError.value = false
  try {
    varianzaProfile.value = unwrapResource<VarianzaProfileApiItem>(await RevistaVarianzaRepository.get())
  } catch {
    profileError.value = true
  } finally {
    profileLoading.value = false
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

onMounted(() => {
  void loadEditorialProfile()
  void loadLatestIssue()
})
</script>

<template>
  <main class="varianza-view">
    <VarianzaHero
      :title="varianzaProfile?.titulo || 'Varianza'"
      :subtitle="'Revista Científica del IETA'"
      :breadcrumb-label="sectionItems.find((item) => item.value === activeSection)?.label"
    />
    <VarianzaSectionNav :active="activeSection" :items="sectionItems" />

    <Transition name="tab-panel-fade" mode="out-in">
      <div :key="activeSection" class="varianza-tab-panel-wrap">
        <!-- 1. SOBRE LA REVISTA (PDF Page 15) -->
        <template v-if="activeSection === 'sobre'">
          <section class="varianza-about">
        <div class="container varianza-about__inner">
          <div class="varianza-about__copy">
            <h2 class="varianza-pink-heading">{{ varianzaProfile?.titulo || 'Sobre la revista' }}</h2>
            <div class="varianza-about__text">
              <p v-if="profileLoading" class="varianza-resource-state" role="status">Cargando perfil editorial…</p>
              <p v-else-if="profileError" class="varianza-resource-state" role="alert">No se pudo cargar el perfil editorial.</p>
              <EditorJsContent v-else-if="varianzaProfile?.subtitulo" :content="varianzaProfile.subtitulo" :api-base-url="apiBaseUrl" />
            </div>
            <dl class="varianza-about__issn">
              <div>
                <dt>ISSN versión impresa:</dt>
                <dd>{{ varianzaProfile?.issn_impreso || '2789-3510' }}</dd>
              </div>
              <div>
                <dt>ISSN versión en línea:</dt>
                <dd>{{ varianzaProfile?.issn_electronico || '2789-3529' }}</dd>
              </div>
              <div v-if="varianzaProfile?.periodicidad">
                <dt>Periodicidad:</dt>
                <dd>{{ varianzaProfile.periodicidad }}</dd>
              </div>
            </dl>

          </div>

          <!-- Right side: Latest Issue card preview (PDF Page 15) -->
          <div class="varianza-about__featured">
            <div v-if="latestIssueLoading" class="varianza-resource-state" role="status">Cargando última edición…</div>
            <div v-else-if="!dynamicLatestIssue" class="varianza-resource-state" role="status">
              No hay ediciones publicadas todavía.
            </div>
            <div v-else class="varianza-latest-card">
              <RouterLink
                :to="`/revista-varianza/ediciones/${encodeURIComponent(dynamicLatestIssue.slug || String(dynamicLatestIssue.id))}`"
                class="varianza-latest-card__cover-link"
              >
                <img
                  v-if="latestIssueCover"
                  :src="latestIssueCover"
                  :alt="`Portada de ${dynamicLatestIssue.titulo}`"
                  class="varianza-latest-card__img"
                />
                <div v-else class="varianza-latest-card__placeholder">
                  <span>VARIANZA</span>
                  <span>N° {{ dynamicLatestIssue.numero || '' }}</span>
                </div>
              </RouterLink>
              <div class="varianza-latest-card__meta">
                <span class="varianza-latest-card__label">Última Edición</span>
                <RouterLink
                  :to="`/revista-varianza/ediciones/${encodeURIComponent(dynamicLatestIssue.slug || String(dynamicLatestIssue.id))}`"
                  class="varianza-latest-card__title-link"
                >
                  <span>{{ dynamicLatestIssue.titulo }}</span>
                  <ArrowRight :size="16" aria-hidden="true" />
                </RouterLink>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Showcase Sections: Misión, Visión, Alcance with frontend images -->
      <section class="varianza-showcase-section">
        <div class="container varianza-showcase-container">
          <!-- Misión Showcase Card -->
          <article class="varianza-showcase-card">
            <div class="varianza-showcase-visual">
              <img
                :src="varianzaMissionImg"
                alt="Misión Editorial Revista Varianza"
                class="varianza-showcase-img"
                loading="lazy"
              />
            </div>
            <div class="varianza-showcase-body">
              <h3 class="varianza-showcase-heading">Misión</h3>
              <p class="varianza-showcase-desc">
                {{ varianzaProfile?.mision || 'Contenido en proceso de actualización editorial.' }}
              </p>
            </div>
          </article>

          <!-- Visión Showcase Card (Reversed) -->
          <article class="varianza-showcase-card varianza-showcase-card--reversed">
            <div class="varianza-showcase-visual">
              <img
                :src="varianzaVisionImg"
                alt="Visión Editorial Revista Varianza"
                class="varianza-showcase-img"
                loading="lazy"
              />
            </div>
            <div class="varianza-showcase-body">
              <h3 class="varianza-showcase-heading">Visión</h3>
              <p class="varianza-showcase-desc">
                {{ varianzaProfile?.vision || 'Contenido en proceso de actualización editorial.' }}
              </p>
            </div>
          </article>

          <!-- Alcance Showcase Card -->
          <article class="varianza-showcase-card">
            <div class="varianza-showcase-visual">
              <img
                :src="varianzaScopeImg"
                alt="Alcance y Temática Revista Varianza"
                class="varianza-showcase-img"
                loading="lazy"
              />
            </div>
            <div class="varianza-showcase-body">
              <h3 class="varianza-showcase-heading">Alcance</h3>
              <div class="varianza-showcase-desc">
                <EditorJsContent v-if="varianzaProfile?.alcance" :content="varianzaProfile.alcance" :api-base-url="apiBaseUrl" />
                <p v-else>Contenido en proceso de actualización editorial.</p>
              </div>
            </div>
          </article>
        </div>
      </section>

    </template>

    <!-- 2. COMITÉ EDITOR (PDF Page 10) -->
    <template v-else-if="activeSection === 'comite-editor'">
      <section class="varianza-committee-section">
        <div class="container">
          <div class="varianza-committee-header">
            <h2 class="varianza-committee-title">Comité Editor</h2>
            <p class="varianza-committee-desc">
              El equipo académico responsable de la gestión editorial, la revisión por pares y la excelencia científica de la Revista Varianza del IETA.
            </p>
          </div>
          <div class="varianza-pink-bar"></div>

          <p v-if="committeeResource.loading.value && committeeResource.items.value.length === 0" class="varianza-resource-state" role="status">Cargando comité editorial…</p>
          <div v-else-if="committeeResource.error.value && committeeResource.items.value.length === 0" class="varianza-resource-state" role="alert">
            <p>No se pudo cargar el comité editorial.</p>
            <button type="button" class="retry-button" @click="committeeResource.refresh">Reintentar</button>
          </div>

          <div v-else class="varianza-committee-content">
            <template v-if="committeeGroups.length">
              <div
                v-for="(group, idx) in committeeGroups"
                :key="group.title"
                class="varianza-committee-group"
              >
                <h3 class="varianza-group-title">{{ group.title }}</h3>
                <div class="varianza-group-grid" :class="{ 'varianza-group-grid--single': group.title.toLowerCase().includes('editor') && !group.title.toLowerCase().includes('comit') }">
                  <EditorialMemberCard
                    v-for="member in group.members"
                    :key="member.id"
                    :name="memberName(member)"
                    :role="member.cargo?.nombre || member.tipo || 'Comité Editorial'"
                    :institution="memberInstitution(member)"
                    :email="member.persona?.p_correo || undefined"
                  />
                </div>
                <hr v-if="idx < committeeGroups.length - 1" class="varianza-divider" />
              </div>
            </template>
          </div>
        </div>
      </section>
    </template>

    <!-- 3. AUTORES VARIANZA -->
    <template v-else-if="activeSection === 'autores'">
      <section class="varianza-listing">
        <div class="container">
          <header class="varianza-authors-intro">
            <div>
              <p>Comunidad científica</p>
              <h2>Autores Varianza</h2>
            </div>
            <p>
              Espacio dedicado a investigadores, docentes y profesionales que contribuyen al
              desarrollo de la ciencia de datos y la estadística aplicada.
            </p>
          </header>

          <!-- Top Filter Bar (Reference Image 1 Style) -->
          <div class="people-filter-bar">
            <div class="people-filter-bar__left">
              <div class="filter-by-label">
                <SlidersHorizontal :size="16" aria-hidden="true" />
                <span>Filtrar por</span>
              </div>

              <div class="filter-select-wrapper">
                <select v-model="selectedAuthorProgram" class="filter-select-input" aria-label="Filtrar por tipo de autor">
                  <option value="">Tipo de autor</option>
                  <option v-for="prog in authorPrograms" :key="prog" :value="prog">{{ prog }}</option>
                </select>
              </div>

              <div class="filter-select-wrapper">
                <select v-model="selectedAuthorSpecialty" class="filter-select-input" aria-label="Filtrar por disciplina científica">
                  <option value="">Disciplina científica</option>
                  <option v-for="spec in authorSpecialties" :key="spec" :value="spec">{{ spec }}</option>
                </select>
              </div>

              <div class="filter-search-wrapper">
                <Search :size="15" class="filter-search-icon" aria-hidden="true" />
                <input
                  v-model="authorQuery"
                  type="search"
                  placeholder="Buscar autor..."
                  class="filter-search-input"
                />
              </div>
            </div>

            <div class="people-filter-bar__right">
              <span class="results-count">{{ displayedAuthors.length }} Resultados</span>
              <div class="sort-select-wrapper">
                <label class="sort-label">Ordenar por:</label>
                <select v-model="authorSort" class="sort-select-input" aria-label="Ordenar autores">
                  <option value="name-asc">Nombre (A - Z)</option>
                  <option value="name-desc">Nombre (Z - A)</option>
                </select>
              </div>
            </div>
          </div>

          <p v-if="authorsResource.loading.value && authorsResource.items.value.length === 0" class="varianza-resource-state" role="status">Cargando autores…</p>
          <div v-else-if="authorsResource.error.value && authorsResource.items.value.length === 0" class="varianza-resource-state" role="alert">
            <p>No se pudo cargar el directorio de autores.</p>
            <button type="button" class="retry-button" @click="authorsResource.refresh">Reintentar</button>
          </div>
          <div v-else-if="displayedAuthors.length" :class="authorViewMode === 'gallery' ? 'varianza-authors-grid' : 'varianza-authors-list'">
            <AuthorProfileCard
              v-for="author in displayedAuthors"
              :key="author.id"
              :name="authorName(author)"
              :degree="author.grado_academico?.abreviatura || undefined"
              :role="author.tipo_autor?.nombre || 'Investigador Varianza'"
              :specialty="author.especialidad?.nombre || undefined"
              :institution="'Carrera de Estadística · UMSA'"
              :image="mediaUrl(author.persona?.foto_url)"
              :to="authorPath(author)"
              :compact="authorViewMode === 'list'"
            />
          </div>
          <EmptyState
            v-else-if="!authorsResource.loading.value && authorsResource.items.value.length === 0"
            title="No hay autores publicados"
            description="El directorio de autores aparecerá aquí cuando tenga registros."
          />
          <EmptyState
            v-else-if="displayedAuthors.length === 0 && !authorQuery.trim()"
            title="No hay autores para mostrar"
            description="Prueba con otra especialidad o limpia los filtros."
            action-label="Limpiar filtros"
            @action="clearAuthorFilters"
          />
          <LoadMoreButton
            :loading="authorsResource.loadingMore.value"
            :has-more="authorsResource.hasMore.value"
            @load="authorsResource.loadMore"
          />
        </div>
      </section>
    </template>

    <!-- 4. TODAS LAS EDICIONES (PDF Page 11) -->
    <template v-else-if="activeSection === 'ediciones'">
      <section class="varianza-editions-section">
        <div class="container">
          <!-- Featured Issue Card (PDF Page 11) - Only rendered when an issue exists from the API -->
          <div v-if="dynamicLatestIssue" class="varianza-current-issue-card">
            <div class="varianza-current-issue-card__media">
              <img
                v-if="latestIssueCover"
                :src="latestIssueCover"
                :alt="`Portada de ${dynamicLatestIssue.titulo}`"
                class="current-issue-cover"
              />
              <div v-else class="current-issue-cover-placeholder">
                <span class="cover-placeholder__vol">VARIANZA</span>
                <span class="cover-placeholder__num">N° {{ dynamicLatestIssue.numero }}</span>
              </div>
            </div>
            <div class="varianza-current-issue-card__info">
              <span class="current-issue-date">{{ [dynamicLatestIssue.mes, dynamicLatestIssue.anio].filter(Boolean).join(', ') || 'Última Edición' }}</span>
              <h2 class="current-issue-title">{{ dynamicLatestIssue.titulo }}</h2>
              <dl class="current-issue-specs">
                <div v-if="dynamicLatestIssue.articulos_originales_count ?? dynamicLatestIssue.num_art_originales_declarado">
                  <dt>Nro. Artículos Originales:</dt>
                  <dd>{{ dynamicLatestIssue.articulos_originales_count ?? dynamicLatestIssue.num_art_originales_declarado }}</dd>
                </div>
                <div v-if="dynamicLatestIssue.num_paginas">
                  <dt>Nro. Páginas:</dt>
                  <dd>{{ dynamicLatestIssue.num_paginas }} págs.</dd>
                </div>
                <div v-if="dynamicLatestIssue.issn_impresa">
                  <dt>ISSN versión impresa:</dt>
                  <dd>{{ dynamicLatestIssue.issn_impresa }}</dd>
                </div>
                <div v-if="dynamicLatestIssue.issn_electronica">
                  <dt>ISSN versión en línea:</dt>
                  <dd>{{ dynamicLatestIssue.issn_electronica }}</dd>
                </div>
              </dl>
              <div class="current-issue-actions">
                <RouterLink
                  :to="`/revista-varianza/ediciones/${encodeURIComponent(dynamicLatestIssue.slug || String(dynamicLatestIssue.id))}`"
                  class="btn-pink-pill"
                >
                  <span>Ver Revista</span>
                  <ArrowRight :size="16" aria-hidden="true" />
                </RouterLink>

                <a
                  v-if="dynamicLatestIssue.archivo_url"
                  :href="mediaUrl(dynamicLatestIssue.archivo_url)"
                  class="btn-pink-outline"
                  target="_blank"
                  rel="noreferrer"
                >
                  <span>Descargar</span>
                  <Download :size="16" aria-hidden="true" />
                </a>
              </div>
            </div>
          </div>

          <!-- Section heading and unified filter bar (Banco de Datos style) -->
          <header class="varianza-all-editions-header">
            <h2 class="all-editions-title">Todas las ediciones</h2>
          </header>

          <div class="people-filter-bar varianza-editions-filter-bar">
            <div class="people-filter-bar__left">
              <div class="filter-label">
                <SlidersHorizontal :size="18" aria-hidden="true" />
                <span>Filtrar por</span>
              </div>

              <div class="filter-select-wrapper">
                <select v-model="selectedIssueYear" class="filter-select-input" aria-label="Filtrar por año">
                  <option value="">Todos los años</option>
                  <option v-for="y in availableIssueYears" :key="y" :value="y">{{ y }}</option>
                </select>
              </div>

              <div class="filter-search-wrapper">
                <Search :size="18" class="search-icon" aria-hidden="true" />
                <input
                  v-model="issueQuery"
                  type="search"
                  placeholder="Buscar edición por título, número o tema..."
                  class="filter-search-input"
                />
              </div>

              <button
                v-if="issueQuery || selectedIssueYear"
                type="button"
                class="filter-clear-btn"
                @click="clearIssueFilters"
              >
                <RotateCcw :size="14" aria-hidden="true" />
                <span>Limpiar</span>
              </button>
            </div>

            <div class="people-filter-bar__right">
              <span class="results-count">{{ filteredIssues.length }} Resultados</span>
              <div class="sort-select-wrapper">
                <label class="sort-label">Ordenar por:</label>
                <select v-model="issueSort" class="sort-select-input" aria-label="Ordenar ediciones">
                  <option value="desc">Más recientes primero</option>
                  <option value="asc">Más antiguas primero</option>
                </select>
              </div>
            </div>
          </div>

          <div v-if="filteredIssues.length" class="varianza-issues__list">
            <MagazineIssueRow
              v-for="issue in filteredIssues"
              :key="issue.id"
              :number="issue.numero"
              :date="[issue.mes, issue.anio].filter(Boolean).join(', ')"
              :title="issue.titulo"
              :cover="mediaUrl(issue.portada_url)"
              :cover-alt="`Portada de ${issue.titulo}`"
              :to="`/revista-varianza/ediciones/${encodeURIComponent(issue.slug || String(issue.id))}`"
              :article-count="Number(issue.articulos_count ?? issue.articulos_originales_count ?? issue.num_art_originales_declarado ?? 2)"
              :pages="issue.num_paginas ?? 51"
            />
          </div>

          <EmptyState
            v-else
            title="No se encontraron ediciones"
            description="Prueba con otros términos de búsqueda o selecciona otro año."
          />

          <div class="varianza-load-more-wrap">
            <button type="button" class="btn-load-more-magazines" @click="issuesResource.loadMore">
              <span>Cargar más revistas</span>
              <ArrowRight :size="16" aria-hidden="true" />
            </button>
          </div>
        </div>
      </section>
    </template>

    <!-- 5. INSTRUCCIONES PARA AUTORES -->
    <template v-else-if="activeSection === 'instrucciones-autores'">
      <section class="varianza-guidelines-section">
        <div class="container">
          <div class="guidelines-header-row">
            <div>
              <h2 class="varianza-guidelines-title">Instrucciones para autores</h2>
              <div class="varianza-pink-bar"></div>
            </div>
            <!-- View Mode Switcher -->
            <div class="guideline-mode-toggle">
              <button
                type="button"
                class="mode-btn"
                :class="{ 'mode-btn--active': guidelineViewMode === 'tabs' }"
                @click="guidelineViewMode = 'tabs'"
              >
                Vista interactiva por secciones
              </button>
              <button
                type="button"
                class="mode-btn"
                :class="{ 'mode-btn--active': guidelineViewMode === 'all' }"
                @click="guidelineViewMode = 'all'"
              >
                Ver documento continuo
              </button>
            </div>
          </div>

          <!-- Loading & Error States -->
          <p v-if="guidelinesResource.loading.value && guidelinesResource.items.value.length === 0" class="varianza-resource-state" role="status">
            Cargando normas e instrucciones…
          </p>
          <div v-else-if="guidelinesResource.error.value && guidelinesResource.items.value.length === 0" class="varianza-resource-state" role="alert">
            <p>No se pudieron cargar las instrucciones editoriales.</p>
            <button type="button" class="retry-button" @click="guidelinesResource.refresh">Reintentar</button>
          </div>

          <div v-else-if="displayedGuidelines.length" class="varianza-guidelines__layout">
            <!-- Left Navigation Sidebar (Visual interactive buttons) -->
            <nav class="varianza-guidelines__index" aria-label="Índice de instrucciones">
              <div class="index-header">
                <FileText :size="18" class="index-header-icon" aria-hidden="true" />
                <h3 class="index-title">Índice temático</h3>
              </div>
              <ul class="index-list">
                <li v-for="(item, idx) in displayedGuidelines" :key="item.id">
                  <button
                    type="button"
                    class="index-nav-btn"
                    :class="{ 'index-nav-btn--active': selectedGuidelineIndex === idx }"
                    @click="selectGuideline(idx)"
                  >
                    <span class="index-nav-num">{{ String(idx + 1).padStart(2, '0') }}</span>
                    <span class="index-nav-text">{{ getGuidelineTitle(item) }}</span>
                    <ChevronRight :size="15" class="index-nav-arrow" aria-hidden="true" />
                  </button>
                </li>
              </ul>
            </nav>

            <!-- Right Content Area -->
            <div class="varianza-guidelines__content">
              <!-- Mode: Tabs (Interactive focused view without scroll jumps) -->
              <div v-if="guidelineViewMode === 'tabs' && selectedGuideline" class="guideline-card-wrap">
                <article class="guideline-hero-card">
                  <header class="guideline-card-header">
                    <span class="guideline-badge">
                      <BookOpen :size="13" aria-hidden="true" />
                      Sección {{ String(selectedGuidelineIndex + 1).padStart(2, '0') }} de {{ String(displayedGuidelines.length).padStart(2, '0') }}
                    </span>
                    <h3 class="guideline-card-title">{{ getGuidelineTitle(selectedGuideline) }}</h3>
                    <div class="guideline-accent-rule"></div>
                  </header>

                  <div class="guideline-card-body">
                    <EditorJsContent
                      v-if="typeof selectedGuideline.contenido === 'object' && selectedGuideline.contenido !== null"
                      :content="selectedGuideline.contenido"
                      :api-base-url="apiBaseUrl"
                    />
                    <div
                      v-else
                      class="guideline-raw-content"
                      v-html="selectedGuideline.contenido"
                    ></div>
                  </div>

                  <!-- Step Actions Footer -->
                  <footer class="guideline-card-footer">
                    <button
                      type="button"
                      class="guideline-nav-action"
                      :disabled="selectedGuidelineIndex === 0"
                      @click="prevGuideline"
                    >
                      <ChevronLeft :size="16" aria-hidden="true" />
                      <span>Instrucción anterior</span>
                    </button>

                    <button
                      type="button"
                      class="guideline-nav-action guideline-nav-action--next"
                      :disabled="selectedGuidelineIndex >= displayedGuidelines.length - 1"
                      @click="nextGuideline"
                    >
                      <span>Siguiente instrucción</span>
                      <ChevronRight :size="16" aria-hidden="true" />
                    </button>
                  </footer>
                </article>
              </div>

              <!-- Mode: Continuous All Document -->
              <div v-else class="guideline-all-sections">
                <section
                  v-for="(item, idx) in displayedGuidelines"
                  :id="guidelineAnchor(item)"
                  :key="item.id"
                  class="guideline-entry-card"
                >
                  <header class="guideline-card-header">
                    <span class="guideline-badge">
                      <BookOpen :size="13" aria-hidden="true" />
                      Sección {{ String(idx + 1).padStart(2, '0') }}
                    </span>
                    <h3 class="guideline-card-title">{{ getGuidelineTitle(item) }}</h3>
                    <div class="guideline-accent-rule"></div>
                  </header>

                  <div class="guideline-card-body">
                    <EditorJsContent
                      v-if="typeof item.contenido === 'object' && item.contenido !== null"
                      :content="item.contenido"
                      :api-base-url="apiBaseUrl"
                    />
                    <div
                      v-else
                      class="guideline-raw-content"
                      v-html="item.contenido"
                    ></div>
                  </div>
                </section>
              </div>

              
            </div>
          </div>
        </div>
      </section>
    </template>
  </div>
</Transition>
</main>
</template>

<style scoped>
.varianza-view {
  --varianza-navy: #1b144b;
  --varianza-pink: #f50088;
  overflow: clip;
  background: var(--color-superficie);
}

.varianza-tab-panel-wrap {
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

/* Common Dividers & Bars */
.varianza-divider {
  border: 0;
  height: 1px;
  background: #eaedf6;
  margin: clamp(2rem, 4vw, 3rem) 0;
}

.varianza-pink-bar {
  height: 3px;
  background: var(--varianza-pink);
  margin-block: 1rem 2.25rem;
  border-radius: 2px;
}

.varianza-pink-heading {
  color: var(--varianza-pink);
  font-size: clamp(1.85rem, 3.5vw, 2.5rem);
  font-weight: 800;
  letter-spacing: -0.01em;
  margin: 0 0 1rem;
}

.varianza-statement-block {
  padding-block: 0.5rem;
}

.varianza-statement-title {
  color: var(--varianza-navy);
  font-size: clamp(1.4rem, 2.5vw, 1.85rem);
  font-weight: 800;
  margin: 0 0 1rem;
}

.varianza-statement-body {
  color: #334155;
  font-size: 1rem;
  line-height: 1.7;
  max-width: 84ch;
}

.varianza-statement-body p + p {
  margin-top: 1rem;
}

/* 1. SOBRE LA REVISTA */
.varianza-about {
  padding-top: clamp(1.5rem, 3vw, 2.5rem);
}

.varianza-about__inner {
  display: grid;
  grid-template-columns: minmax(0, 1.35fr) minmax(18rem, 0.85fr);
  align-items: center;
  gap: clamp(2rem, 5vw, 4rem);
}

.varianza-about__copy {
  max-width: 44rem;
}

.varianza-about__text {
  color: #334155;
  font-size: 1.02rem;
  line-height: 1.7;
}

.varianza-about__issn {
  display: grid;
  gap: 0.4rem;
  margin-block: 1.5rem;
  font-size: 0.95rem;
  color: #1e293b;
}

.varianza-about__issn div {
  display: flex;
  gap: 0.5rem;
}

.varianza-about__issn dt {
  font-weight: 700;
  color: #1b144b;
}

.varianza-about__issn dd {
  margin: 0;
  color: #475569;
}

.varianza-external-link {
  display: inline-block;
  color: var(--varianza-pink);
  font-weight: 700;
  text-decoration: underline;
  text-underline-offset: 0.25em;
  font-size: 0.95rem;
  transition: opacity 0.2s ease;
}

.varianza-external-link:hover {
  opacity: 0.85;
}

/* Latest Card Preview (Right side) */
.varianza-latest-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  background: var(--color-superficie);
  padding: 1.5rem;
}

.varianza-latest-card__cover-link {
  display: block;
  width: min(100%, 15rem);
  box-shadow: 0 12px 32px rgba(27, 20, 75, 0.16);
  border-radius: 6px;
  overflow: hidden;
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.varianza-latest-card__cover-link:hover {
  transform: translateY(-4px);
  box-shadow: 0 16px 36px rgba(27, 20, 75, 0.22);
}

.varianza-latest-card__img {
  width: 100%;
  aspect-ratio: 0.72;
  object-fit: cover;
  display: block;
}

.varianza-latest-card__placeholder {
  display: grid;
  place-content: center;
  aspect-ratio: 0.72;
  background: linear-gradient(145deg, #18113c, #2b1a6c);
  color: #ffffff;
  font-weight: 800;
  font-size: 1.25rem;
  gap: 0.35rem;
}

.varianza-latest-card__meta {
  margin-top: 1rem;
}

.varianza-latest-card__label {
  display: block;
  font-size: 0.82rem;
  color: #64748b;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.varianza-latest-card__title-link {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  color: var(--varianza-pink);
  font-size: 1.2rem;
  font-weight: 800;
  text-decoration: none;
  margin-top: 0.2rem;
  transition: gap 0.2s ease;
}

.varianza-latest-card__title-link:hover {
  gap: 0.6rem;
}

/* 2. COMITÉ EDITOR (PDF Page 10) */
.varianza-committee-section {
  padding-block: clamp(1.5rem, 3vw, 2.5rem);
}

.varianza-committee-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 2rem;
}

.varianza-committee-title {
  color: var(--varianza-pink);
  font-size: clamp(1.85rem, 3.5vw, 2.5rem);
  font-weight: 800;
  margin: 0;
}

.varianza-committee-desc {
  color: #475569;
  font-size: 0.95rem;
  line-height: 1.5;
  max-width: 44ch;
  margin: 0;
  text-align: right;
}

.varianza-group-title {
  color: var(--varianza-navy);
  font-size: 1.35rem;
  font-weight: 800;
  margin: 0 0 1.25rem;
}

.varianza-group-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1.25rem;
}

.varianza-group-grid--single {
  grid-template-columns: minmax(0, 32rem);
}

/* 3. AUTORES */
.varianza-listing {
  padding-block: clamp(1.5rem, 3vw, 2.5rem);
}

.varianza-authors-intro {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 2rem;
  margin-bottom: 1.75rem;
}

.varianza-authors-intro p:first-child {
  font-size: 0.75rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: var(--varianza-pink);
  margin: 0 0 0.25rem;
}

.varianza-authors-intro h2 {
  font-size: clamp(1.85rem, 3.5vw, 2.5rem);
  font-weight: 800;
  color: var(--varianza-pink);
  margin: 0;
}

.varianza-authors-intro > p {
  color: #475569;
  font-size: 0.95rem;
  line-height: 1.5;
  max-width: 44ch;
  margin: 0;
  text-align: right;
}

.author-filters-row {
  display: flex;
  gap: 1rem;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 2rem;
}

.author-search-input {
  flex-grow: 1;
  max-width: 36rem;
  height: 2.85rem;
  padding: 0 1.15rem;
  border-radius: 8px;
  border: 1px solid #d4d8e5;
  background: #ffffff;
  font-size: 0.92rem;
  outline: none;
  transition: border-color 0.2s ease;
}

.author-search-input:focus {
  border-color: var(--varianza-pink);
}

.author-view-switch {
  display: inline-flex;
  border: 1px solid #d4d8e5;
  border-radius: 8px;
  padding: 3px;
  background: #ffffff;
}

.author-view-switch button {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  height: 2.25rem;
  padding: 0 0.85rem;
  border: 0;
  border-radius: 6px;
  background: transparent;
  color: var(--varianza-navy);
  font-weight: 700;
  font-size: 0.82rem;
  cursor: pointer;
}

.author-view-switch button[aria-pressed='true'] {
  background: var(--varianza-navy);
  color: #ffffff;
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

.varianza-authors-grid {
  display: grid;
  grid-template-columns: repeat(5, minmax(0, 1fr));
  gap: 1.25rem;
}

.varianza-authors-list {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1rem;
}

/* 4. TODAS LAS EDICIONES (PDF Page 11) */
.varianza-editions-section {
  padding-block: clamp(1.5rem, 3vw, 2.5rem);
}

.varianza-current-issue-card {
  display: grid;
  grid-template-columns: 14rem minmax(0, 1fr);
  gap: 2.5rem;
  align-items: center;
  background: linear-gradient(135deg, #1b144b 0%, #291a66 100%);
  color: #ffffff;
  border-radius: 12px;
  padding: clamp(1.75rem, 3.5vw, 2.5rem);
  box-shadow: 0 12px 36px rgba(27, 20, 75, 0.18);
  margin-bottom: 3.5rem;
}

.varianza-current-issue-card__media {
  width: 100%;
}

.current-issue-cover {
  width: 100%;
  aspect-ratio: 0.72;
  object-fit: cover;
  border-radius: 6px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
}

.current-issue-cover-placeholder {
  display: grid;
  place-content: center;
  aspect-ratio: 0.72;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.18);
  border-radius: 6px;
  text-align: center;
  gap: 0.35rem;
}

.cover-placeholder__vol {
  font-size: 1.3rem;
  font-weight: 800;
  letter-spacing: 0.05em;
  color: #ffffff;
}

.cover-placeholder__num {
  font-size: 1rem;
  color: rgba(255, 255, 255, 0.8);
}

.current-issue-date {
  font-size: 0.92rem;
  color: #f6d1e3;
  font-weight: 600;
  letter-spacing: 0.03em;
}

.current-issue-title {
  color: #ffffff;
  font-size: clamp(1.75rem, 3vw, 2.35rem);
  font-weight: 800;
  margin: 0.25rem 0 1.25rem;
}

.current-issue-specs {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.6rem 2rem;
  font-size: 0.88rem;
  color: #e2e8f0;
  margin-bottom: 1.75rem;
}

.current-issue-specs div {
  display: flex;
  gap: 0.4rem;
}

.current-issue-specs dt {
  font-weight: 700;
  color: #ffffff;
}

.current-issue-specs dd {
  margin: 0;
  color: rgba(255, 255, 255, 0.85);
}

.current-issue-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  align-items: center;
}

.btn-pink-pill {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  height: 2.75rem;
  padding: 0 1.5rem;
  border-radius: 999px;
  background: var(--varianza-pink);
  color: #ffffff;
  font-weight: 800;
  font-size: 0.9rem;
  text-decoration: none;
  transition: transform 0.2s ease, background 0.2s ease;
}

.btn-pink-pill:hover {
  background: #db007a;
  transform: translateY(-2px);
}

.btn-pink-outline {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  height: 2.75rem;
  padding: 0 1.5rem;
  border-radius: 999px;
  border: 1px solid rgba(255, 255, 255, 0.4);
  background: rgba(255, 255, 255, 0.08);
  color: #ffffff;
  font-weight: 700;
  font-size: 0.9rem;
  text-decoration: none;
  cursor: pointer;
  transition: border-color 0.2s ease, background 0.2s ease;
}

.btn-pink-outline:hover {
  border-color: #ffffff;
  background: rgba(255, 255, 255, 0.16);
}

/* All Editions Header & Filter */
.varianza-all-editions-header {
  margin-bottom: 2rem;
}

.all-editions-title {
  color: var(--varianza-navy);
  font-size: clamp(1.6rem, 2.8vw, 2.1rem);
  font-weight: 800;
  margin: 0 0 1.25rem;
}

.all-editions-filters {
  display: flex;
  gap: 1.5rem;
}

.filter-field {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  flex-grow: 1;
}

.filter-field--year {
  flex-grow: 0;
  width: 12rem;
}

.filter-field__label {
  font-size: 0.8rem;
  font-weight: 700;
  color: var(--varianza-navy);
}

.filter-field__input,
.filter-field__select {
  height: 2.65rem;
  border: 1px solid #d4d8e5;
  border-radius: 6px;
  padding: 0 0.85rem;
  font-size: 0.9rem;
  outline: none;
}

.filter-field__input:focus,
.filter-field__select:focus {
  border-color: var(--varianza-pink);
}

.varianza-issues__list {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1.25rem;
}

.varianza-load-more-wrap {
  display: flex;
  justify-content: center;
  margin-top: 2.5rem;
}

.btn-load-more-magazines {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  height: 2.65rem;
  padding: 0 1.5rem;
  border-radius: 999px;
  border: 1px solid var(--varianza-pink);
  background: transparent;
  color: var(--varianza-pink);
  font-weight: 800;
  font-size: 0.88rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-load-more-magazines:hover {
  background: var(--varianza-pink);
  color: #ffffff;
}

/* 5. INSTRUCCIONES PARA AUTORES */
.varianza-guidelines-section {
  padding-block: clamp(1.5rem, 3vw, 2.5rem);
}

.guidelines-header-row {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 1.25rem;
  margin-bottom: 2rem;
}

.varianza-guidelines-title {
  color: var(--varianza-pink);
  font-size: clamp(1.85rem, 3.5vw, 2.5rem);
  font-weight: 800;
  margin: 0;
}

.guideline-mode-toggle {
  display: inline-flex;
  background: #edf2f7;
  padding: 0.3rem;
  border-radius: 999px;
  gap: 0.35rem;
}

.mode-btn {
  border: none;
  background: transparent;
  padding: 0.45rem 1.1rem;
  border-radius: 999px;
  font-size: 0.84rem;
  font-weight: 600;
  color: #4b5563;
  cursor: pointer;
  transition: all 0.2s ease;
}

.mode-btn:hover {
  color: #111827;
}

.mode-btn--active {
  background: #ffffff;
  color: #1b144b !important;
  font-weight: 700;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
}

.varianza-guidelines__layout {
  display: grid;
  grid-template-columns: minmax(16rem, 20rem) minmax(0, 1fr);
  gap: clamp(1.75rem, 3.5vw, 3rem);
  align-items: start;
}

.varianza-guidelines__index {
  position: sticky;
  top: calc(var(--header-height) + 1.5rem);
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 1.25rem;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
}

.index-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid #f1f5f9;
}

.index-header-icon {
  color: var(--varianza-pink);
}

.index-title {
  color: var(--varianza-navy);
  font-size: 1.1rem;
  font-weight: 800;
  margin: 0;
}

.index-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.index-nav-btn {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.65rem 0.85rem;
  border: 1px solid transparent;
  border-radius: 8px;
  background: transparent;
  text-align: left;
  cursor: pointer;
  transition: all 0.18s ease;
  font-family: inherit;
}

.index-nav-num {
  font-size: 0.8rem;
  font-weight: 800;
  color: #94a3b8;
  padding: 0.15rem 0.4rem;
  border-radius: 4px;
  background: #f1f5f9;
  transition: all 0.18s ease;
}

.index-nav-text {
  flex: 1;
  font-size: 0.88rem;
  color: #334155;
  font-weight: 600;
  line-height: 1.35;
  transition: color 0.18s ease;
}

.index-nav-arrow {
  color: #cbd5e1;
  transition: transform 0.18s ease, color 0.18s ease;
}

.index-nav-btn:hover {
  background: #fdf2f8;
  border-color: #fbcfe8;
}

.index-nav-btn:hover .index-nav-text {
  color: var(--varianza-pink);
}

.index-nav-btn--active {
  background: #1b144b !important;
  border-color: #1b144b !important;
}

.index-nav-btn--active .index-nav-num {
  background: rgba(255, 255, 255, 0.2);
  color: #ffffff;
}

.index-nav-btn--active .index-nav-text {
  color: #ffffff !important;
  font-weight: 700;
}

.index-nav-btn--active .index-nav-arrow {
  color: #f43f5e;
  transform: translateX(3px);
}

.varianza-guidelines__content {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.guideline-hero-card,
.guideline-entry-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: clamp(1.5rem, 3vw, 2.5rem);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
}

.guideline-all-sections {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.guideline-entry-card {
  scroll-margin-top: calc(var(--header-height) + 2rem);
}

.guideline-card-header {
  margin-bottom: 1.5rem;
}

.guideline-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  background: #fff1f2;
  color: #e11d48;
  font-size: 0.8rem;
  font-weight: 700;
  padding: 0.25rem 0.75rem;
  border-radius: 999px;
  margin-bottom: 0.75rem;
}

.guideline-card-title {
  color: #111827;
  font-size: clamp(1.4rem, 2.4vw, 1.9rem);
  font-weight: 800;
  margin: 0 0 0.85rem;
  line-height: 1.25;
}

.guideline-accent-rule {
  width: 48px;
  height: 3.5px;
  background: var(--varianza-pink);
  border-radius: 2px;
}

.guideline-card-body {
  color: #334155;
  font-size: 0.98rem;
  line-height: 1.75;
}

.guideline-card-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  margin-top: 2.25rem;
  padding-top: 1.5rem;
  border-top: 1px solid #f1f5f9;
}

.guideline-nav-action {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.6rem 1.25rem;
  border-radius: 8px;
  border: 1.5px solid #d1d5db;
  background: #ffffff;
  color: #374151;
  font-size: 0.86rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.18s ease;
  font-family: inherit;
}

.guideline-nav-action:hover:not(:disabled) {
  border-color: #1b144b;
  color: #1b144b;
  background: #f8fafc;
}

.guideline-nav-action--next {
  border-color: #1b144b;
  background: #1b144b;
  color: #ffffff;
}

.guideline-nav-action--next:hover:not(:disabled) {
  background: #f30060;
  border-color: #f30060;
  color: #ffffff;
}

.guideline-nav-action:disabled {
  opacity: 0.35;
  cursor: not-allowed;
}

.guideline-raw-content {
  color: #334155;
  font-size: 0.96rem;
  line-height: 1.7;
}

.guideline-raw-content :deep(p) {
  margin: 0 0 1rem;
}

.guideline-raw-content :deep(h4) {
  color: var(--varianza-navy);
  font-size: 1.05rem;
  font-weight: 800;
  margin: 1.25rem 0 0.35rem;
}

.guideline-contact-box {
  padding: 1.5rem;
  background: #f8f9fd;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
}

.guideline-contact-box p {
  margin: 0 0 0.85rem;
  font-weight: 700;
  color: var(--varianza-navy);
}

.varianza-guidelines__external {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  color: var(--varianza-pink);
  font-weight: 800;
  font-size: 0.92rem;
  text-decoration: underline;
  text-underline-offset: 0.2em;
}

.varianza-resource-state {
  display: grid;
  place-content: center;
  min-height: 8rem;
  color: #64748b;
  font-size: 0.95rem;
}

.retry-button {
  margin-top: 0.75rem;
  padding: 0.5rem 1.25rem;
  border-radius: 6px;
  border: 1px solid var(--varianza-navy);
  background: var(--varianza-navy);
  color: #ffffff;
  font-weight: 700;
  cursor: pointer;
}

/* Responsive */
@media (max-width: 1280px) {
  .varianza-authors-grid {
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }
}

@media (max-width: 1024px) {
  .varianza-authors-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }

  .varianza-issues__list {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .varianza-about__inner {
    grid-template-columns: 1fr;
  }

  .varianza-committee-header,
  .varianza-authors-intro {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }

  .varianza-committee-desc,
  .varianza-authors-intro > p {
    text-align: left;
  }

  .varianza-group-grid {
    grid-template-columns: 1fr;
  }

  .varianza-current-issue-card {
    grid-template-columns: 1fr;
    text-align: center;
  }

  .varianza-current-issue-card__media {
    max-width: 14rem;
    margin-inline: auto;
  }

  .current-issue-specs {
    grid-template-columns: 1fr;
  }

  .current-issue-actions {
    justify-content: center;
  }

  .all-editions-filters {
    flex-direction: column;
  }

  .filter-field--year {
    width: 100%;
  }

  .varianza-guidelines__layout {
    grid-template-columns: 1fr;
  }

  .varianza-guidelines__index {
    position: static;
  }

  .author-filters-row {
    flex-direction: column;
    align-items: stretch;
  }
}

@media (max-width: 640px) {
  .varianza-authors-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .varianza-authors-list {
    grid-template-columns: 1fr;
  }
}

/* Showcase Sections: Misión, Visión, Alcance (Compact, smaller cards) */
.varianza-showcase-section {
  padding: 1.5rem 0 2.5rem;
  background: #f8fafc;
}

.varianza-showcase-container {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  max-width: 58rem;
  margin-inline: auto;
}

.varianza-showcase-card {
  display: grid;
  grid-template-columns: 12.5rem minmax(0, 1fr);
  gap: 1.5rem;
  background: var(--color-blanco);
  border-radius: var(--radius-sm);
  border: 1px solid rgba(220, 224, 235, 0.85);
  box-shadow: 0 3px 14px rgba(27, 20, 75, 0.04);
  overflow: hidden;
  align-items: center;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.varianza-showcase-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 22px -4px rgba(27, 20, 75, 0.08);
}

.varianza-showcase-card--reversed {
  grid-template-columns: minmax(0, 1fr) 12.5rem;
}

.varianza-showcase-card--reversed .varianza-showcase-visual {
  order: 2;
}

.varianza-showcase-card--reversed .varianza-showcase-body {
  order: 1;
}

.varianza-showcase-visual {
  position: relative;
  height: 100%;
  min-height: 8.5rem;
  max-height: 10.5rem;
  overflow: hidden;
  background: #f0f3fa;
  display: flex;
  align-items: center;
  justify-content: center;
}

.varianza-showcase-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.varianza-showcase-card:hover .varianza-showcase-img {
  transform: scale(1.03);
}

.varianza-showcase-visual__tag {
  position: absolute;
  top: 0.65rem;
  left: 0.65rem;
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  background: rgba(27, 20, 75, 0.88);
  backdrop-filter: blur(6px);
  color: #ffffff;
  padding: 0.2rem 0.55rem;
  border-radius: 999px;
  font-size: 0.68rem;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  z-index: 2;
}

.varianza-showcase-visual__tag--pink {
  background: rgba(243, 0, 96, 0.92);
}

.varianza-showcase-caption {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(15, 23, 42, 0.88);
  backdrop-filter: blur(4px);
  padding: 0.25rem 0.5rem;
  text-align: center;
  z-index: 2;
}

.varianza-showcase-caption .caption-label {
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
  font-size: 0.68rem;
  color: #f1f5f9;
  font-weight: 600;
  letter-spacing: -0.01em;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  display: block;
}

.varianza-showcase-body {
  padding: 1.25rem 1.5rem 1.25rem 0.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.varianza-showcase-card--reversed .varianza-showcase-body {
  padding: 1.25rem 0.25rem 1.25rem 1.5rem;
}

.varianza-showcase-sub {
  font-size: 0.72rem;
  font-weight: 800;
  color: var(--color-indigo);
  letter-spacing: 0.05em;
  text-transform: uppercase;
}

.varianza-showcase-sub--pink {
  color: var(--color-rosa-carmin);
}

.varianza-showcase-heading {
  margin: 0;
  font-size: 1.2rem;
  font-weight: 700;
  color: #1b144b;
  line-height: 1.3;
}

.varianza-showcase-desc {
  margin: 0;
  color: #4b5268;
  font-size: 0.92rem;
  line-height: 1.6;
}

.varianza-editions-filter-bar {
  margin-bottom: 2rem;
}

@media (max-width: 768px) {
  .varianza-showcase-card,
  .varianza-showcase-card--reversed {
    grid-template-columns: 1fr;
  }
  .varianza-showcase-card--reversed .varianza-showcase-visual {
    order: 0;
  }
  .varianza-showcase-card--reversed .varianza-showcase-body {
    order: 0;
    padding: 1rem;
  }
  .varianza-showcase-body {
    padding: 1rem;
  }
}
</style>
