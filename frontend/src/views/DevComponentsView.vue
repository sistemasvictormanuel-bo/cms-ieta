<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { gsap } from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'
import {
  ArrowRight,
  ArrowUpRight,
  Bell,
  BookOpen,
  CalendarDays,
  ChartNoAxesCombined,
  Clock3,
  Database,
  Download,
  FileText,
  FlaskConical,
  Globe2,
  GraduationCap,
  Mail,
  MapPin,
  Newspaper,
  Phone,
  Users,
} from '@lucide/vue'
import EmptyState from '@/components/common/EmptyState.vue'
import LoadMoreButton from '@/components/common/LoadMoreButton.vue'
import SearchFilterBar from '@/components/common/SearchFilterBar.vue'
import StatusBadge from '@/components/common/StatusBadge.vue'
import TabNav from '@/components/common/TabNav.vue'

const showcaseRoot = ref<HTMLElement | null>(null)
const activeResearchTab = ref('lineas')
const searchQuery = ref('')
const filterValues = ref({ year: '', type: '' })
const visibleResourceCount = ref(3)
const today = new Date()
let animationContext: gsap.Context | undefined

const researchTabs = [
  { value: 'lineas', label: 'Líneas de Investigación' },
  { value: 'memorias', label: 'Memorias de Investigación' },
]

const featureCards = [
  {
    type: 'Cursos y Talleres',
    title: 'Análisis exploratorio de datos con R Studio',
    description: 'Herramientas prácticas para preparar, explorar y comunicar datos.',
    date: '12 de agosto · 09:00',
    icon: GraduationCap,
    visual: 'course-cover course-cover--blue',
    coverMark: 'DATA\nLAB',
    action: 'Ver curso',
  },
  {
    type: 'Cursos y Talleres',
    title: 'Medición de la pobreza y desigualdad con Stata',
    description: 'Métodos de medición e interpretación para estudios sociales.',
    date: '22 de agosto · 15:00',
    icon: ChartNoAxesCombined,
    visual: 'course-cover course-cover--rose',
    coverMark: 'STAT\nTOOLS',
    action: 'Ver curso',
  },
  {
    type: 'Noticias',
    title: 'El IETA presenta estudio estadístico multidimensional',
    description: 'Un nuevo informe reúne evidencia para apoyar decisiones públicas.',
    date: '18 de septiembre de 2026',
    icon: Newspaper,
    visual: 'news-cover news-cover--campus',
    coverMark: 'IETA\nUMSA',
    action: 'Leer noticia',
  },
  {
    type: 'Noticias',
    title: 'Estudiantes y docentes realizan jornada de divulgación',
    description: 'La comunidad comparte proyectos de investigación y aplicación.',
    date: '4 de septiembre de 2026',
    icon: Users,
    visual: 'news-cover news-cover--people',
    coverMark: 'CIENCIA\nABIERTA',
    action: 'Leer noticia',
  },
]

const lineCards = [
  {
    id: 'survival',
    title: 'Análisis de sobrevivencia',
    description:
      'Modelado del tiempo hasta la ocurrencia de un evento, con tratamiento de datos censurados.',
    icon: ChartNoAxesCombined,
    tone: 'line-card--blue',
  },
  {
    id: 'demography',
    title: 'Demografía',
    description: 'Dinámica poblacional, transición y estructura demográfica.',
    icon: Users,
    tone: 'line-card--rose',
  },
  {
    id: 'optimization',
    title: 'Optimización',
    description: 'Métodos estadísticos para sistemas y toma de decisiones.',
    icon: FlaskConical,
    tone: 'line-card--violet',
  },
  {
    id: 'bayesian',
    title: 'Estadística Bayesiana',
    description: 'Inferencia y modelado probabilístico para datos complejos.',
    icon: ChartNoAxesCombined,
    tone: 'line-card--indigo',
  },
]

const journals = [
  {
    title: 'Revista Varianza',
    subtitle: 'Instituto de Estadística Teórica y Aplicada',
    description: 'Artículos originales de investigación en estadística y aplicaciones.',
    issue: 'Volumen 22 · 2025',
    icon: BookOpen,
    tone: 'journal-card--varianza',
  },
  {
    title: 'International Journal of Statistics and Applications',
    subtitle: 'Publicación internacional',
    description: 'Investigación teórica y aplicada, métodos y aplicaciones estadísticas.',
    issue: 'Acceso al portal editorial',
    icon: Globe2,
    tone: 'journal-card--international',
  },
]

const memories = [
  {
    title: 'Memoria de investigación estadística 2025',
    authors: 'Equipo de investigación IETA',
    summary:
      'Proyectos, actividades y resultados de investigación desarrollados durante la gestión.',
    year: '2025',
    icon: BookOpen,
    coverTone: 'memory-cover--violet',
  },
  {
    title: 'Estudios aplicados y vinculación social',
    authors: 'Docentes y estudiantes',
    summary: 'Una selección de trabajos que vinculan métodos estadísticos y necesidades sociales.',
    year: '2024',
    icon: FileText,
    coverTone: 'memory-cover--blue',
  },
]

const resources = [
  {
    type: 'Encuesta',
    year: '2024',
    title: 'Encuesta Sociodemográfica Metropolitana',
    location: 'La Paz · El Alto',
    coverUrl: '',
    coverLabel: 'Encuesta 2024',
    icon: Database,
    tone: 'resource-card--survey',
  },
  {
    type: 'Censo',
    year: '2024',
    title: 'Censo de unidades económicas',
    location: 'Base de datos descargable',
    coverUrl: '',
    coverLabel: 'Censo 2024',
    icon: FileText,
    tone: 'resource-card--census',
  },
  {
    type: 'Encuesta',
    year: '2023',
    title: 'Encuesta de hogares y empleo',
    location: 'Microdatos y documentación',
    coverUrl: '',
    coverLabel: 'Portada opcional',
    icon: Database,
    tone: 'resource-card--plain',
  },
  {
    type: 'Censo',
    year: '2022',
    title: 'Censo de población y vivienda',
    location: 'Tabulados y fichas técnicas',
    coverUrl: '',
    coverLabel: 'Portada opcional',
    icon: FileText,
    tone: 'resource-card--plain',
  },
]

const selectedLineId = ref('survival')
const selectedLine = computed(
  () => lineCards.find((line) => line.id === selectedLineId.value) ?? lineCards[0],
)

const contactChannels = [
  { icon: Phone, label: 'Teléfono', value: '+591 261-2824', href: 'tel:+5912612824' },
  { icon: Mail, label: 'Correo', value: 'ieta@umsa.bo', href: 'mailto:ieta@umsa.bo' },
  { icon: MapPin, label: 'Dirección', value: 'Bloque FCPN, campus Cota Cota', href: '' },
  { icon: Clock3, label: 'Atención', value: 'Martes a sábado · 08:00–16:00', href: '' },
]

const filterOptions = [
  {
    key: 'year',
    label: 'Año',
    options: [
      { value: '2024', label: '2024' },
      { value: '2023', label: '2023' },
    ],
  },
  {
    key: 'type',
    label: 'Tipo',
    options: [
      { value: 'Encuesta', label: 'Encuesta' },
      { value: 'Censo', label: 'Censo' },
    ],
  },
]

const filteredResources = computed(() => {
  const query = searchQuery.value.trim().toLocaleLowerCase()

  return resources.filter((resource) => {
    const matchesQuery =
      !query || `${resource.title} ${resource.location}`.toLocaleLowerCase().includes(query)
    const matchesYear = !filterValues.value.year || resource.year === filterValues.value.year
    const matchesType = !filterValues.value.type || resource.type === filterValues.value.type
    return matchesQuery && matchesYear && matchesType
  })
})

const visibleResources = computed(() =>
  filteredResources.value.slice(0, visibleResourceCount.value),
)

function dateOffset(days: number) {
  const date = new Date(today)
  date.setDate(date.getDate() + days)
  return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
}

function clearFilters() {
  searchQuery.value = ''
  filterValues.value = { year: '', type: '' }
  visibleResourceCount.value = 3
}

function loadMoreResources() {
  visibleResourceCount.value = Math.min(
    visibleResourceCount.value + 1,
    filteredResources.value.length,
  )
}

function previewAnnouncementModal() {
  window.dispatchEvent(new CustomEvent('ieta:open-announcement-preview'))
}

onMounted(() => {
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches || !showcaseRoot.value) return

  gsap.registerPlugin(ScrollTrigger)
  animationContext = gsap.context(() => {
    gsap.from('.showcase-hero__copy', {
      autoAlpha: 0,
      y: 18,
      duration: 0.55,
      ease: 'power2.out',
    })

    gsap.utils.toArray<HTMLElement>('[data-reveal]').forEach((element) => {
      gsap.from(element, {
        autoAlpha: 0,
        y: 20,
        duration: 0.55,
        ease: 'power2.out',
        scrollTrigger: {
          trigger: element,
          start: 'top 88%',
          once: true,
        },
      })
    })
  }, showcaseRoot.value)
})

onBeforeUnmount(() => animationContext?.revert())
</script>

<template>
  <main ref="showcaseRoot" class="showcase">
    <section class="showcase-hero">
      <div class="container showcase-hero__inner">
        <div class="showcase-hero__copy">
          <p class="showcase-kicker"><span></span> IETA · SISTEMA PÚBLICO</p>
          <h1>Componentes para investigar, aprender y compartir</h1>
          <p class="showcase-hero__lead">
            Patrones de contenido para cursos, noticias, investigación, publicaciones y recursos
            estadísticos.
          </p>
          <div class="showcase-hero__actions">
            <a class="showcase-hero__link" href="#componentes">
              Explorar componentes <ArrowRight :size="18" aria-hidden="true" />
            </a>
            <button
              type="button"
              class="showcase-hero__btn-secondary"
              @click="previewAnnouncementModal"
            >
              <Bell :size="18" aria-hidden="true" />
              Probar Modal de Notificaciones
            </button>
          </div>
        </div>

      </div>
    </section>

    <section id="componentes" class="showcase-section showcase-section--band" data-reveal>
      <div class="container">
        <div class="section-band-preview">
          <div class="section-band-preview__crumbs">Inicio <span>/</span> Investigación</div>
          <p class="section-band-preview__eyebrow">02 · NAVEGACIÓN DE SECCIÓN</p>
          <h2>Investigación</h2>
          <p>Una banda de alto contraste para orientar al usuario dentro del sitio.</p>
        </div>
        <div class="showcase-tabs">
          <TabNav
            v-model="activeResearchTab"
            :items="researchTabs"
            label="Secciones de investigación"
          />
        </div>
      </div>
    </section>

    <section class="showcase-section" data-reveal>
      <div class="container">
        <header class="showcase-section__heading">
          <p class="showcase-section__index">03 / ACTUALIDAD</p>
          <div>
            <h2>Cursos y noticias</h2>
            <p>Tarjetas editoriales con jerarquía, fecha e identidad visual por categoría.</p>
          </div>
          <a class="text-link" href="#contacto">Ver todas <ArrowUpRight :size="16" /></a>
        </header>

        <div class="feature-grid">
          <article
            v-for="card in featureCards"
            :key="card.title"
            class="feature-card"
            :class="card.type === 'Noticias' ? 'feature-card--news' : 'feature-card--course'"
          >
            <div class="feature-card__visual" :class="card.visual">
              <component
                :is="card.icon"
                class="feature-card__visual-icon"
                :size="35"
                :stroke-width="1.5"
              />
              <span class="feature-card__cover-mark">{{ card.coverMark }}</span>
              <span class="feature-card__cover-caption">INSTITUTO IETA · {{ card.type }}</span>
            </div>
            <div class="feature-card__body">
              <div class="feature-card__meta">
                <span class="category-label"
                  ><component :is="card.icon" :size="14" /> {{ card.type }}</span
                >
                <span><CalendarDays :size="14" /> {{ card.date }}</span>
              </div>
              <h3>{{ card.title }}</h3>
              <p>{{ card.description }}</p>
              <a class="feature-card__action" href="#componentes">
                {{ card.action }} <ArrowRight :size="16" />
              </a>
            </div>
          </article>
        </div>
        <div class="feature-grid">
          <article
            v-for="card in featureCards"
            :key="card.title"
            class="feature-card"
            :class="card.type === 'Noticias' ? 'feature-card--news' : 'feature-card--course'"
          >
            <div class="feature-card__visual" :class="card.visual">
              <component
                :is="card.icon"
                class="feature-card__visual-icon"
                :size="35"
                :stroke-width="1.5"
              />
              <span class="feature-card__cover-mark">{{ card.coverMark }}</span>
              <span class="feature-card__cover-caption">INSTITUTO IETA · {{ card.type }}</span>
            </div>
            <div class="feature-card__body">
              <div class="feature-card__meta">
                <span class="category-label"
                  ><component :is="card.icon" :size="14" /> {{ card.type }}</span
                >
                <span><CalendarDays :size="14" /> {{ card.date }}</span>
              </div>
              <h3>{{ card.title }}</h3>
              <p>{{ card.description }}</p>
              <a class="feature-card__action" href="#componentes">
                {{ card.action }} <ArrowRight :size="16" />
              </a>
            </div>
          </article>
        </div>
      </div>
    </section>

    <section class="showcase-section showcase-section--soft" data-reveal>
      <div class="container">
        <header class="showcase-section__heading">
          <p class="showcase-section__index">04 / INVESTIGACIÓN</p>
          <div>
            <h2>Líneas y publicaciones</h2>
            <p>Una misma estructura escaneable, con iconografía y acentos distintos por recurso.</p>
          </div>
        </header>

        <div v-if="activeResearchTab === 'lineas'" class="research-card-grid">
          <a
            v-for="line in lineCards"
            :key="line.title"
            class="line-card"
            :class="line.tone"
            href="#componentes"
          >
            <span class="line-card__icon"><component :is="line.icon" :size="24" /></span>
            <span class="line-card__copy">
              <strong>{{ line.title }}</strong>
              <span>{{ line.description }}</span>
            </span>
            <ArrowUpRight class="line-card__arrow" :size="18" />
          </a>
        </div>
        <div v-else class="pending-panel">
          <BookOpen :size="25" />
          <div>
            <strong>Memorias de Investigación</strong>
            <p>Componente reservado para la futura integración de memorias.</p>
          </div>
        </div>

        <div class="journal-grid">
          <article v-for="journal in journals" :key="journal.title" class="journal-card">
            <div class="journal-card__cover" :class="journal.tone">
              <BookOpen :size="27" :stroke-width="1.5" />
              <span>{{ journal.title === 'Revista Varianza' ? 'VARIANZA' : 'IJSA' }}</span>
              <small>{{ journal.issue }}</small>
            </div>
            <div class="journal-card__body">
              <p class="journal-card__publisher">
                <component :is="journal.icon" :size="15" /> {{ journal.subtitle }}
              </p>
              <h3>{{ journal.title }}</h3>
              <p>{{ journal.description }}</p>
              <a href="#componentes">Consultar revista <ArrowUpRight :size="15" /></a>
            </div>
          </article>
        </div>

        <div class="line-detail-demo">
          <div class="line-detail-demo__hero">
            <div>
              <p>DETALLE DE LÍNEA · VISTA PREVIA</p>
              <h3>{{ selectedLine?.title }}</h3>
              <span>{{ selectedLine?.description }}</span>
            </div>
            <ChartNoAxesCombined :size="62" :stroke-width="1.2" aria-hidden="true" />
          </div>
          <div class="line-detail-demo__body">
            <div class="line-detail-demo__tabs" role="tablist" aria-label="Líneas relacionadas">
              <button
                v-for="line in lineCards"
                :key="line.id"
                type="button"
                role="tab"
                :aria-selected="selectedLineId === line.id"
                @click="selectedLineId = line.id"
              >
                {{ line.title }}
              </button>
            </div>
            <div class="line-detail-demo__copy">
              <div>
                <h4>Descripción y alcance</h4>
                <p>
                  {{ selectedLine?.description }} La línea articula proyectos, tesis y colaboración
                  académica.
                </p>
                <h4>Enfoques de trabajo</h4>
                <p>
                  Diseño metodológico, análisis reproducible e interpretación de resultados
                  aplicados.
                </p>
              </div>
              <div class="line-detail-demo__chart" aria-label="Gráfico estadístico de ejemplo">
                <ChartNoAxesCombined :size="40" />
                <span>ANÁLISIS<br />ESTADÍSTICO</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="showcase-section" data-reveal>
      <div class="container">
        <header class="showcase-section__heading">
          <p class="showcase-section__index">05 / INVESTIGACIÓN</p>
          <div>
            <h2>Memorias de investigación</h2>
            <p>Tarjetas editoriales con año, responsables y espacio opcional para portada.</p>
          </div>
        </header>
        <div class="memory-grid">
          <article v-for="memory in memories" :key="memory.title" class="memory-card">
            <div class="memory-card__cover" :class="memory.coverTone">
              <component :is="memory.icon" :size="28" />
              <span>MEMORIA<br />{{ memory.year }}</span>
              <small>INSTITUTO IETA</small>
            </div>
            <div class="memory-card__body">
              <p class="memory-card__year"><CalendarDays :size="14" /> {{ memory.year }}</p>
              <h3>{{ memory.title }}</h3>
              <p>{{ memory.summary }}</p>
              <span class="memory-card__authors"><Users :size="14" /> {{ memory.authors }}</span>
            </div>
            <ArrowUpRight class="memory-card__arrow" :size="18" />
          </article>
        </div>
      </div>
    </section>

    <section class="showcase-section" data-reveal>
      <div class="container">
        <header class="showcase-section__heading">
          <p class="showcase-section__index">06 / DATOS ABIERTOS</p>
          <div>
            <h2>Banco de datos</h2>
            <p>Encuestas y censos con espacio para portada, metadatos y acceso a descarga.</p>
          </div>
          <span class="cover-option-label"><FileText :size="15" /> PORTADA OPCIONAL</span>
        </header>

        <SearchFilterBar
          v-model="searchQuery"
          v-model:filter-values="filterValues"
          :filters="filterOptions"
          :no-results="visibleResources.length === 0"
        />

        <div class="resource-grid">
          <article
            v-for="resource in visibleResources"
            :key="resource.title"
            class="resource-card"
            :class="resource.tone"
          >
            <div class="resource-card__cover">
              <img v-if="resource.coverUrl" :src="resource.coverUrl" :alt="resource.coverLabel" />
              <template v-else>
                <component :is="resource.icon" :size="30" :stroke-width="1.5" />
                <span>{{ resource.coverLabel }}<br />{{ resource.year }}</span>
                <small>PORTADA OPCIONAL · IETA</small>
              </template>
            </div>
            <div class="resource-card__body">
              <div class="resource-card__meta">
                <span>{{ resource.type }}</span
                ><span>{{ resource.year }}</span>
              </div>
              <h3>{{ resource.title }}</h3>
              <p>{{ resource.location }}</p>
              <button class="download-link" type="button">Descargar <Download :size="16" /></button>
            </div>
          </article>
        </div>
        <EmptyState
          v-if="visibleResources.length === 0"
          title="No hay recursos para mostrar"
          description="Prueba con otro año o tipo de publicación."
          action-label="Limpiar filtros"
          @action="clearFilters"
        />
        <LoadMoreButton
          :has-more="visibleResourceCount < filteredResources.length"
          @load="loadMoreResources"
        />
      </div>
    </section>

    <section class="showcase-section showcase-section--contact" data-reveal>
      <div class="container contact-preview">
        <div class="contact-preview__heading">
          <p class="showcase-section__index">07 / ATENCIÓN</p>
          <h2>Contacto</h2>
          <p>Información clara, accesible y lista para una acción directa.</p>
        </div>
        <div class="contact-preview__grid">
          <a
            v-for="channel in contactChannels"
            :key="channel.label"
            :href="channel.href || undefined"
            class="contact-channel"
          >
            <span class="contact-channel__icon"><component :is="channel.icon" :size="20" /></span>
            <span
              ><small>{{ channel.label }}</small
              ><strong>{{ channel.value }}</strong></span
            >
            <ArrowUpRight v-if="channel.href" class="contact-channel__arrow" :size="15" />
          </a>
        </div>
      </div>
    </section>

    <section class="showcase-section" data-reveal>
      <div class="container">
        <header class="showcase-section__heading">
          <p class="showcase-section__index">08 / ESTADOS</p>
          <div>
            <h2>Estados y controles</h2>
            <p>Controles reutilizables para filtros, carga y disponibilidad.</p>
          </div>
        </header>
        <div class="control-showcase">
          <StatusBadge
            :today="today"
            :registration-start="dateOffset(-1)"
            :registration-end="dateOffset(12)"
            :course-end="dateOffset(30)"
          />
          <StatusBadge
            :today="today"
            :registration-start="dateOffset(-14)"
            :registration-end="dateOffset(-1)"
            :course-end="dateOffset(12)"
          />
          <StatusBadge :today="today" :course-end="dateOffset(-1)" />
          <div class="empty-example">
            <EmptyState
              title="Sin coincidencias"
              description="Ajusta la búsqueda para explorar otros resultados."
              action-label="Restablecer"
              @action="clearFilters"
            />
          </div>
        </div>
      </div>
    </section>
  </main>
</template>

<style scoped>
.showcase {
  --showcase-ink: #21184b;
  --showcase-purple: #35227e;
  --showcase-line: #dfdeea;
  color: var(--color-tinta);
  padding-bottom: var(--space-6);
}

.showcase-hero {
  position: relative;
  overflow: hidden;
  padding-block: calc(var(--header-height) + var(--space-8)) var(--space-8);
  background: var(--gradient-marca);
  color: var(--color-blanco);
}

.showcase-hero::after {
  position: absolute;
  right: 12%;
  bottom: -12rem;
  width: 30rem;
  aspect-ratio: 1;
  border: 1px solid rgba(255, 255, 255, 0.18);
  border-radius: 50%;
  content: '';
  pointer-events: none;
}

.showcase-hero__inner {
  position: relative;
  z-index: 1;
  display: grid;
  grid-template-columns: minmax(0, 1fr) 16rem;
  align-items: center;
  gap: var(--space-8);
}

.showcase-hero__copy {
  max-width: 48rem;
}

.showcase-kicker,
.showcase-section__index {
  display: flex;
  align-items: center;
  gap: 0.55rem;
  margin: 0 0 var(--space-2);
  font-size: 0.75rem;
  font-weight: 700;
  line-height: 1.3;
}

.showcase-kicker {
  color: rgba(255, 255, 255, 0.78);
}

.showcase-kicker span {
  width: 1.4rem;
  height: 2px;
  background: var(--color-rosa-carmin);
}

.showcase-hero h1 {
  max-width: 12ch;
  margin: 0;
  font-size: clamp(2.5rem, 5vw, 4rem);
  font-weight: 700;
  line-height: 1.04;
}

.showcase-hero__lead {
  max-width: 55ch;
  margin: var(--space-3) 0 0;
  color: rgba(255, 255, 255, 0.82);
  font-size: var(--text-medium-size);
  line-height: var(--text-medium-lh);
}

.showcase-hero__link,
.text-link,
.feature-card__action,
.journal-card__body a {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  font-weight: 700;
}

.showcase-hero__actions {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: var(--space-2);
  margin-top: var(--space-3);
}

.showcase-hero__link {
  color: var(--color-blanco);
}

.showcase-hero__btn-secondary {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.55rem 1rem;
  border: 1px solid rgba(255, 255, 255, 0.35);
  border-radius: var(--radius-sm);
  background: rgba(255, 255, 255, 0.12);
  backdrop-filter: blur(4px);
  color: var(--color-blanco);
  font: inherit;
  font-size: var(--text-small-size);
  font-weight: 700;
  cursor: pointer;
  transition: background-color 0.18s ease, border-color 0.18s ease, transform 0.18s ease;
}

.showcase-hero__btn-secondary:hover {
  background: rgba(255, 255, 255, 0.22);
  border-color: rgba(255, 255, 255, 0.6);
  transform: translateY(-2px);
}

.showcase-hero__seal {
  position: relative;
  display: grid;
  width: 13rem;
  aspect-ratio: 1;
  align-content: center;
  justify-items: center;
  gap: var(--space-2);
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  color: var(--color-blanco);
  text-align: center;
}

.showcase-hero__seal-ring {
  position: absolute;
  inset: 0.6rem;
  border: 1px dashed rgba(255, 255, 255, 0.24);
  border-radius: 50%;
}

.showcase-hero__seal span:last-child {
  font-size: 0.625rem;
  font-weight: 700;
  line-height: 1.4;
}

.showcase-section {
  padding-block: var(--space-8);
}

.showcase-section--soft {
  background: var(--color-superficie);
}

.showcase-section--band {
  padding-block: var(--space-6) 0;
}

.section-band-preview {
  padding: var(--space-4) var(--space-6);
  border-radius: var(--radius-sm);
  background: var(--gradient-marca);
  color: var(--color-blanco);
}

.section-band-preview__crumbs {
  margin-bottom: var(--space-3);
  color: rgba(255, 255, 255, 0.75);
  font-size: var(--text-small-size);
}

.section-band-preview__crumbs span {
  margin-inline: 0.4rem;
  opacity: 0.65;
}

.section-band-preview__eyebrow {
  margin: 0 0 var(--space-1);
  color: rgba(255, 255, 255, 0.72);
  font-size: 0.6875rem;
  font-weight: 700;
}

.section-band-preview h2 {
  margin: 0;
  font-size: var(--text-h3-size);
  line-height: var(--text-h3-lh);
}

.section-band-preview > p:last-child {
  margin: var(--space-1) 0 0;
  color: rgba(255, 255, 255, 0.85);
}

.showcase-tabs {
  padding: var(--space-2) var(--space-3) 0;
  border-bottom: 1px solid var(--showcase-line);
}

.showcase-section__heading {
  display: grid;
  grid-template-columns: 11rem minmax(0, 1fr) auto;
  align-items: end;
  gap: var(--space-3);
  margin-bottom: var(--space-4);
}

.showcase-section__index {
  align-self: start;
  margin: 0;
  color: var(--color-violeta);
}

.showcase-section__heading h2,
.contact-preview__heading h2 {
  margin: 0;
  color: var(--showcase-ink);
  font-size: var(--text-h4-size);
  line-height: var(--text-h4-lh);
}

.showcase-section__heading p:not(.showcase-section__index),
.contact-preview__heading > p:last-child {
  max-width: 58ch;
  margin: var(--space-1) 0 0;
  color: #53505f;
}

.text-link {
  color: var(--color-violeta);
  white-space: nowrap;
}

.feature-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: var(--space-2);
}

.feature-card {
  display: flex;
  min-width: 0;
  flex-direction: column;
  overflow: hidden;
  border: 1px solid var(--showcase-line);
  border-radius: var(--radius-md);
  background: var(--color-blanco);
}

.feature-card__visual {
  position: relative;
  display: flex;
  min-height: 12rem;
  flex-direction: column;
  justify-content: space-between;
  overflow: hidden;
  padding: var(--space-2);
  color: var(--color-blanco);
}

.feature-card__visual::before,
.feature-card__visual::after {
  position: absolute;
  content: '';
  pointer-events: none;
}

.feature-card__visual::before {
  inset: auto -2rem -4rem auto;
  width: 12rem;
  aspect-ratio: 1;
  border: 1px solid rgba(255, 255, 255, 0.42);
  border-radius: 50%;
}

.feature-card__visual::after {
  right: 1.2rem;
  bottom: 1.5rem;
  width: 5rem;
  height: 5rem;
  border: 1px solid rgba(255, 255, 255, 0.32);
  transform: rotate(35deg);
}

.course-cover--blue {
  background: linear-gradient(145deg, #1a275f, #355aa4 55%, #78a4c4);
}

.course-cover--rose {
  background: linear-gradient(145deg, #832b62, #d65c87 58%, #edb18f);
}

.news-cover--campus {
  background: linear-gradient(145deg, #253765, #5d7592 55%, #94b0af);
}

.news-cover--people {
  background: linear-gradient(145deg, #30431f, #839644 55%, #d0a944);
}

.feature-card__visual-icon {
  position: relative;
  z-index: 1;
}

.feature-card__cover-mark {
  position: relative;
  z-index: 1;
  white-space: pre-line;
  font-size: 1.8rem;
  font-weight: 700;
  line-height: 0.98;
}

.feature-card__cover-caption {
  position: relative;
  z-index: 1;
  font-size: 0.625rem;
  font-weight: 700;
  letter-spacing: 0.04em;
}

.feature-card__body {
  display: flex;
  flex: 1;
  flex-direction: column;
  padding: var(--space-2);
}

.feature-card__meta,
.category-label {
  display: flex;
  align-items: center;
  gap: 0.35rem;
}

.feature-card__meta {
  flex-wrap: wrap;
  justify-content: space-between;
  gap: var(--space-1);
  color: #646172;
  font-size: 0.6875rem;
}

.category-label {
  color: var(--color-violeta);
  font-weight: 700;
}

.feature-card h3 {
  margin: var(--space-2) 0 var(--space-1);
  color: var(--showcase-ink);
  font-size: var(--text-h6-size);
  line-height: 1.25;
}

.feature-card__body > p {
  margin: 0;
  color: #54515f;
  font-size: var(--text-small-size);
}

.feature-card__action {
  margin-top: auto;
  padding-top: var(--space-2);
  color: var(--color-rosa-carmin);
  font-size: var(--text-small-size);
}

.research-card-grid,
.journal-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: var(--space-2);
}

.line-card {
  display: grid;
  grid-template-columns: 3.5rem minmax(0, 1fr) auto;
  align-items: center;
  gap: var(--space-2);
  min-height: 6rem;
  padding: var(--space-2);
  border: 1px solid var(--showcase-line);
  border-radius: var(--radius-sm);
  background: var(--color-blanco);
  color: var(--color-tinta);
  text-decoration: none;
  transition:
    border-color 0.18s ease,
    transform 0.18s ease;
}

.line-card:hover {
  transform: translateY(-2px);
  border-color: var(--color-resaltador);
}

.line-card__icon {
  display: grid;
  width: 3.5rem;
  aspect-ratio: 1;
  place-items: center;
  color: var(--color-blanco);
}

.line-card--blue .line-card__icon {
  background: #31528e;
}
.line-card--rose .line-card__icon {
  background: #b74170;
}
.line-card--violet .line-card__icon {
  background: #6e5399;
}
.line-card--indigo .line-card__icon {
  background: #35449f;
}

.line-card__copy {
  display: grid;
  gap: 0.25rem;
}

.line-card__copy strong {
  color: var(--showcase-ink);
  font-size: var(--text-body-size);
}

.line-card__copy > span {
  color: #565363;
  font-size: var(--text-small-size);
}

.line-card__arrow {
  color: var(--color-violeta);
}

.pending-panel {
  display: flex;
  min-height: 8rem;
  align-items: center;
  gap: var(--space-2);
  padding: var(--space-3);
  border-left: 4px solid var(--color-rosa-carmin);
  background: var(--color-blanco);
  color: var(--showcase-ink);
}

.pending-panel p {
  margin: 0.25rem 0 0;
  color: #565363;
}

.journal-grid {
  margin-top: var(--space-4);
}

.journal-card {
  display: grid;
  grid-template-columns: 6.5rem minmax(0, 1fr);
  min-height: 10rem;
  overflow: hidden;
  border: 1px solid var(--showcase-line);
  border-radius: var(--radius-sm);
  background: var(--color-blanco);
}

.journal-card__cover {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: var(--space-1);
  color: var(--color-blanco);
}

.journal-card--varianza {
  background: linear-gradient(155deg, #f2c368, #553884 58%, #22165e);
}
.journal-card--international {
  background: linear-gradient(155deg, #e55b37, #873573 55%, #243469);
}

.journal-card__cover > span {
  font-size: 0.9rem;
  font-weight: 700;
  line-height: 1;
  overflow-wrap: anywhere;
}

.journal-card__cover small {
  font-size: 0.5625rem;
}

.journal-card__body {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  padding: var(--space-2);
}

.journal-card__publisher {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  margin: 0;
  color: var(--color-violeta);
  font-size: 0.6875rem;
  font-weight: 700;
}

.journal-card h3 {
  margin: var(--space-1) 0;
  font-size: var(--text-body-size);
  line-height: 1.3;
}

.journal-card__body > p:not(.journal-card__publisher) {
  margin: 0;
  color: #565363;
  font-size: var(--text-small-size);
}

.journal-card__body a {
  margin-top: auto;
  padding-top: var(--space-1);
  color: var(--color-rosa-carmin);
  font-size: var(--text-small-size);
}

.line-detail-demo {
  margin-top: var(--space-6);
  overflow: hidden;
  border: 1px solid var(--showcase-line);
  border-radius: var(--radius-sm);
  background: var(--color-blanco);
}

.line-detail-demo__hero {
  display: flex;
  min-height: 13rem;
  align-items: center;
  justify-content: space-between;
  gap: var(--space-4);
  padding: var(--space-4);
  background: var(--gradient-marca);
  color: var(--color-blanco);
}

.line-detail-demo__hero > div {
  max-width: 45rem;
}

.line-detail-demo__hero p {
  margin: 0 0 var(--space-1);
  color: rgba(255, 255, 255, 0.72);
  font-size: 0.6875rem;
  font-weight: 700;
}

.line-detail-demo__hero h3 {
  margin: 0 0 var(--space-1);
  font-size: var(--text-h4-size);
  line-height: var(--text-h4-lh);
}

.line-detail-demo__hero span {
  display: block;
  max-width: 58ch;
  color: rgba(255, 255, 255, 0.84);
}

.line-detail-demo__body {
  display: grid;
  grid-template-columns: 15rem minmax(0, 1fr);
  min-height: 17rem;
}

.line-detail-demo__tabs {
  display: flex;
  flex-direction: column;
  align-items: stretch;
  padding: var(--space-1);
  border-right: 1px solid var(--showcase-line);
  background: #faf9fc;
}

.line-detail-demo__tabs button {
  display: flex;
  min-height: 2.75rem;
  align-items: center;
  gap: var(--space-1);
  padding: var(--space-1);
  border: 0;
  border-bottom: 1px solid var(--showcase-line);
  background: transparent;
  color: var(--showcase-ink);
  font: inherit;
  font-size: var(--text-small-size);
  text-align: left;
  cursor: pointer;
}

.line-detail-demo__tabs button[aria-selected='true'] {
  border-left: 3px solid var(--color-rosa-carmin);
  background: var(--color-blanco);
  font-weight: 700;
}

.line-detail-demo__tabs button:focus-visible {
  outline: 2px solid var(--color-resaltador);
  outline-offset: -2px;
}

.line-detail-demo__copy {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 11rem;
  gap: var(--space-3);
  padding: var(--space-3);
}

.line-detail-demo__copy h4 {
  margin: 0 0 var(--space-1);
  color: var(--showcase-ink);
  font-size: var(--text-body-size);
}

.line-detail-demo__copy p {
  margin: 0 0 var(--space-2);
  color: #565363;
  font-size: var(--text-small-size);
  line-height: 1.6;
}

.line-detail-demo__chart {
  display: grid;
  min-height: 10rem;
  align-content: center;
  justify-items: center;
  gap: var(--space-2);
  background: #f1eff8;
  color: var(--color-violeta);
  text-align: center;
}

.line-detail-demo__chart span {
  font-size: 0.625rem;
  font-weight: 700;
}

.memory-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: var(--space-2);
}

.memory-card {
  position: relative;
  display: grid;
  grid-template-columns: 7rem minmax(0, 1fr);
  min-height: 11rem;
  overflow: hidden;
  border: 1px solid var(--showcase-line);
  border-radius: var(--radius-sm);
  background: var(--color-blanco);
}

.memory-card__cover {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: var(--space-1);
  color: var(--color-blanco);
}

.memory-cover--violet {
  background: linear-gradient(145deg, #342879, #8c5b9e);
}
.memory-cover--blue {
  background: linear-gradient(145deg, #203d70, #5f82a7);
}

.memory-card__cover span {
  font-size: 1.125rem;
  font-weight: 700;
  line-height: 1;
}

.memory-card__cover small {
  font-size: 0.5625rem;
  font-weight: 700;
}

.memory-card__body {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  padding: var(--space-2);
}

.memory-card__year,
.memory-card__authors {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  margin: 0;
  color: var(--color-violeta);
  font-size: 0.6875rem;
  font-weight: 700;
}

.memory-card h3 {
  margin: var(--space-1) 0;
  color: var(--showcase-ink);
  font-size: var(--text-body-size);
  line-height: 1.3;
}

.memory-card__body > p:not(.memory-card__year) {
  margin: 0 0 var(--space-1);
  color: #565363;
  font-size: var(--text-small-size);
}

.memory-card__authors {
  margin-top: auto;
}

.memory-card__arrow {
  position: absolute;
  top: var(--space-1);
  right: var(--space-1);
  color: var(--color-violeta);
}

.cover-option-label {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.4rem 0.65rem;
  border: 1px solid var(--color-violeta);
  border-radius: 99px;
  color: var(--color-violeta);
  font-size: 0.625rem;
  font-weight: 700;
  white-space: nowrap;
}

.resource-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: var(--space-2);
  margin-top: var(--space-3);
}

.resource-card {
  overflow: hidden;
  border: 1px solid var(--showcase-line);
  border-radius: var(--radius-sm);
  background: var(--color-blanco);
}

.resource-card__cover {
  position: relative;
  display: flex;
  min-height: 8rem;
  flex-direction: column;
  align-items: flex-start;
  justify-content: space-between;
  padding: var(--space-2);
  color: var(--color-blanco);
}

.resource-card__cover img {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.resource-card--survey .resource-card__cover {
  background: linear-gradient(135deg, #344d83, #6076a1);
}
.resource-card--census .resource-card__cover {
  background: linear-gradient(135deg, #733678, #bd557d);
}
.resource-card--plain .resource-card__cover {
  background: var(--color-superficie);
  color: var(--color-violeta);
}

.resource-card__cover span {
  font-size: 1.25rem;
  font-weight: 700;
  line-height: 1.05;
}

.resource-card__cover small {
  font-size: 0.5625rem;
  font-weight: 700;
}

.resource-card__body {
  padding: var(--space-2);
}

.resource-card__meta {
  display: flex;
  justify-content: space-between;
  color: var(--color-violeta);
  font-size: 0.6875rem;
  font-weight: 700;
}

.resource-card h3 {
  margin: var(--space-1) 0;
  color: var(--showcase-ink);
  font-size: var(--text-body-size);
  line-height: 1.3;
}

.resource-card__body > p {
  margin: 0;
  color: #565363;
  font-size: var(--text-small-size);
}

.download-link {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: var(--space-2);
  padding: 0;
  border: 0;
  background: transparent;
  color: var(--color-violeta);
  font: inherit;
  font-size: var(--text-small-size);
  font-weight: 700;
  cursor: pointer;
}

.showcase-section--contact {
  background: var(--showcase-purple);
  color: var(--color-blanco);
}

.contact-preview {
  display: grid;
  grid-template-columns: 0.8fr 1.2fr;
  gap: var(--space-6);
  align-items: center;
}

.contact-preview__heading h2,
.contact-preview__heading > p:last-child {
  color: var(--color-blanco);
}

.contact-preview__heading > p:last-child {
  opacity: 0.78;
}

.contact-preview__heading .showcase-section__index {
  color: #f2cbe1;
}

.contact-preview__grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 0.5rem;
}

.contact-channel {
  display: grid;
  grid-template-columns: 2.5rem minmax(0, 1fr) auto;
  align-items: center;
  gap: var(--space-1);
  min-width: 0;
  padding: var(--space-1);
  border: 1px solid rgba(255, 255, 255, 0.18);
  color: var(--color-blanco);
  text-decoration: none;
}

.contact-channel__icon {
  display: grid;
  width: 2.5rem;
  aspect-ratio: 1;
  place-items: center;
  background: rgba(255, 255, 255, 0.1);
}

.contact-channel > span:nth-child(2) {
  display: grid;
  min-width: 0;
  gap: 0.15rem;
}

.contact-channel small {
  color: rgba(255, 255, 255, 0.65);
}

.contact-channel strong {
  overflow-wrap: anywhere;
  font-size: var(--text-small-size);
}

.contact-channel__arrow {
  color: #f5bfd9;
}

.control-showcase {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-start;
  gap: var(--space-2);
}

.empty-example {
  flex: 1 1 100%;
  border: 1px solid var(--showcase-line);
}

@media (max-width: 900px) {
  .feature-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .showcase-section__heading {
    grid-template-columns: 8rem minmax(0, 1fr);
  }

  .showcase-section__heading > :last-child {
    grid-column: 2;
  }
}

@media (max-width: 680px) {
  .showcase-hero__inner,
  .contact-preview {
    grid-template-columns: 1fr;
  }

  .showcase-hero__seal {
    display: none;
  }

  .showcase-hero h1 {
    font-size: var(--text-h3-size);
  }

  .showcase-section__heading {
    grid-template-columns: 1fr;
    align-items: start;
    gap: var(--space-1);
  }

  .showcase-section__heading > :last-child {
    grid-column: auto;
  }

  .feature-grid,
  .research-card-grid,
  .journal-grid,
  .memory-grid,
  .resource-grid,
  .contact-preview__grid {
    grid-template-columns: 1fr;
  }

  .section-band-preview {
    padding: var(--space-3);
  }

  .section-band-preview h2 {
    font-size: var(--text-h4-size);
  }

  .cover-option-label {
    justify-self: start;
  }

  .line-detail-demo__body {
    grid-template-columns: 1fr;
  }

  .line-detail-demo__tabs {
    flex-direction: row;
    overflow-x: auto;
    border-right: 0;
    border-bottom: 1px solid var(--showcase-line);
  }

  .line-detail-demo__tabs button {
    flex: 0 0 auto;
    border-bottom: 0;
  }

  .line-detail-demo__copy {
    grid-template-columns: 1fr;
  }

  .line-detail-demo__chart {
    min-height: 7rem;
  }

  .memory-card {
    grid-template-columns: 6rem minmax(0, 1fr);
  }
}

@media (prefers-reduced-motion: reduce) {
  .line-card {
    transition: none;
  }
}
</style>


