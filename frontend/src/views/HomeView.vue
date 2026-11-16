<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import {
  ArrowRight,
  ArrowUpRight,
  BarChart3,
  BookOpen,
  Briefcase,
  ChevronLeft,
  ChevronRight,
  Code2,
  Database,
  ExternalLink,
  FlaskConical,
  GraduationCap,
  Layers,
  School,
} from '@lucide/vue'
import { RouterLink } from 'vue-router'
import ContentCard from '@/components/common/ContentCard.vue'
import EmptyState from '@/components/common/EmptyState.vue'
import HomeHero from '@/components/home/HomeHero.vue'
import HomeSectionHeading from '@/components/home/HomeSectionHeading.vue'
import {
  courseTitle,
  courseType,
  formatApiDate,
  newsDate,
  newsIsFeatured,
  newsIsPublished,
  newsSummary,
  newsTitle,
} from '@/api/normalizers'
import { mediaUrl } from '@/api/response'
import { CursosRepository, NoticiasRepository } from '@/api/repositories.js'
import type { CourseApiItem, NewsApiItem } from '@/api/types'
import { useI18n } from 'vue-i18n'
import { usePaginatedResource } from '@/composables/usePaginatedResource'
import { useGsapReveal } from '@/composables/useGsapReveal'
import { homePresentation } from '@/data/home-presentation'

const { t } = useI18n()
const homeRoot = ref<HTMLElement | null>(null)
const institutionalTrack = ref<HTMLElement | null>(null)
const resourcesTrack = ref<HTMLElement | null>(null)
const courseResource = usePaginatedResource<CourseApiItem>((page) => CursosRepository.list({ page }))
const newsResource = usePaginatedResource<NewsApiItem>((page) => NoticiasRepository.list({ page }))

useGsapReveal(homeRoot, {
  selector: '.home-reveal',
  stagger: 0.08,
  y: 20,
})

const {
  items: courseItems,
  loading: coursesLoading,
  error: coursesError,
  isEmpty: coursesEmpty,
  refresh: refreshCourses,
} = courseResource
const {
  items: newsItems,
  loading: newsLoading,
  error: newsError,
  isEmpty: newsEmpty,
  refresh: refreshNews,
} = newsResource

const featuredCourses = computed(() =>
  courseItems.value.filter((course) => course.activo !== false).slice(0, 3),
)
const recentNews = computed(() =>
  newsItems.value
    .filter(newsIsPublished)
    .sort((first, second) => {
      const featuredDifference = Number(newsIsFeatured(second)) - Number(newsIsFeatured(first))
      if (featuredDifference) return featuredDifference
      return new Date(newsDate(second)).getTime() - new Date(newsDate(first)).getTime()
    })
    .slice(0, 3),
)

// Friendly Quick Access Cards (Directly beneath Hero)
const quickAccessCards = [
  {
    label: 'Líneas de investigación',
    desc: 'Ejes temáticos, métodos probabilísticos y líneas activas del IETA.',
    to: '/investigacion/lineas-investigacion',
    icon: FlaskConical,
    tone: 'purple',
  },
  {
    label: 'Revista Varianza',
    desc: 'Publicación científica indexada con artículos originales y referato.',
    to: '/revista-varianza',
    icon: BookOpen,
    tone: 'purple',
  },
  {
    label: 'Servicios estadísticos',
    desc: 'Consultoría, análisis de datos y asesoría técnica para proyectos.',
    to: '/servicios',
    icon: Briefcase,
    tone: 'purple',
  },
  {
    label: 'Banco de datos',
    desc: 'Microdatos, censos y encuestas disponibles para investigación.',
    to: '/recursos',
    icon: Database,
    tone: 'purple',
  },
]

// Carousel 1: Enlaces de Interés (Solo Logos solicitados)
import logoCarrera from '@/assets/images/enlaces/carrera-estadistica.jpg'
import logoIneBolivia from '@/assets/images/enlaces/ine-bolivia.png'
import logoIneChile from '@/assets/images/enlaces/ine-chile.png'
import logoIneEspana from '@/assets/images/enlaces/ine-espana.webp'
import logoMatUc from '@/assets/images/enlaces/mat-uc.png'

const institutionalLinks = [
  { name: 'Carrera de Estadística', logo: logoCarrera, imageCaption: 'assets/images/enlaces/carrera-estadistica.jpg', url: 'https://estadistica.umsa.bo' },
  { name: 'INE Bolivia', logo: logoIneBolivia, imageCaption: 'assets/images/enlaces/ine-bolivia.png', url: 'https://www.ine.gob.bo' },
  { name: 'INE Chile', logo: logoIneChile, imageCaption: 'assets/images/enlaces/ine-chile.png', url: 'https://www.ine.cl' },
  { name: 'INE España', logo: logoIneEspana, imageCaption: 'assets/images/enlaces/ine-espana.webp', url: 'https://www.ine.es' },
  { name: 'Dpto. de Estadística - UC Chile', logo: logoMatUc, imageCaption: 'assets/images/enlaces/mat-uc.png', url: 'https://mat.uc.cl/departamento-de-estadistica.html' },
]

// Carousel 2: Enlaces de Recursos Estadísticos (Solo Logos solicitados)
import logoCranR from '@/assets/images/recursos/cran-r.png'
import logoRstudio from '@/assets/images/recursos/rstudio.webp'
import logoStata from '@/assets/images/recursos/stata.png'
import logoPython from '@/assets/images/recursos/python.png'

const statisticalResources = [
  { name: 'CRAN R Project', logo: logoCranR, imageCaption: 'assets/images/recursos/cran-r.png', url: 'https://cran.r-project.org' },
  { name: 'RStudio Desktop', logo: logoRstudio, imageCaption: 'assets/images/recursos/rstudio.webp', url: 'https://rstudio.com/products/rstudio/download/' },
  { name: 'Stata', logo: logoStata, imageCaption: 'assets/images/recursos/stata.png', url: 'https://www.stata.com' },
  { name: 'Python', logo: logoPython, imageCaption: 'assets/images/recursos/python.png', url: 'https://www.python.org' },
]

function scrollTrack(trackRef: HTMLElement | null, direction: 'left' | 'right') {
  if (!trackRef) return
  const amount = 340
  trackRef.scrollBy({ left: direction === 'left' ? -amount : amount, behavior: 'smooth' })
}

function coursePath(course: CourseApiItem) {
  return `/cursos-talleres/${encodeURIComponent(course.slug || String(course.id))}`
}

onMounted(() => {
  void Promise.all([refreshCourses(), refreshNews()])
})
</script>

<template>
  <main ref="homeRoot" class="home-view">
    <!-- Hero Banner with Branding & Multi-slide Mockup -->
    <HomeHero v-bind="homePresentation.hero" />

    <!-- 1. ACCESOS RÁPIDOS (Directamente debajo del Hero, diseño moderno y dinámico) -->
    <section class="home-view__section home-view__quick-access home-reveal" aria-labelledby="home-quick-access">
      <div class="container">
        <div class="friendly-access-grid">
          <RouterLink
            v-for="item in quickAccessCards"
            :key="item.to"
            :to="item.to"
            class="friendly-card"
            :class="`friendly-card--${item.tone}`"
          >
            <div class="friendly-card__header">
              <div class="friendly-card__icon-box" :class="`friendly-card__icon-box--${item.tone}`">
                <component :is="item.icon" :size="24" aria-hidden="true" />
              </div>

            </div>

            <div class="friendly-card__body">
              <h3 class="friendly-card__title">{{ item.label }}</h3>
              <p class="friendly-card__desc">{{ item.desc }}</p>
            </div>

          </RouterLink>
        </div>
      </div>
    </section>

    <!-- 2. Cursos y Talleres Destacados -->
    <section class="home-view__section home-view__section--courses home-reveal" aria-labelledby="home-courses">
      <div class="container">
        <HomeSectionHeading
          id="home-courses"
          :title="t('home.coursesTitle')"
          :link-label="featuredCourses.length ? t('home.viewAllCourses') : undefined"
          :link-to="featuredCourses[0] ? coursePath(featuredCourses[0]) : undefined"
        />

        <div v-if="featuredCourses.length" class="home-view__course-grid">
          <div v-for="course in featuredCourses" :key="course.id" class="course-card-wrap">
            <ContentCard
              type="curso"
              :title="courseTitle(course)"
              :eyebrow="courseType(course)"
              :summary="course.descripcion ?? undefined"
              :date="formatApiDate(course.fecha_inicio)"
              :image="mediaUrl(course.portada_url)"
              :image-alt="courseTitle(course)"
              :to="coursePath(course)"
            />
          </div>
        </div>

        <div v-if="coursesLoading" class="home-view__skeleton-grid home-view__skeleton-grid--3" role="status">
          <div v-for="n in 3" :key="n" class="skeleton-card skeleton-card--course"></div>
        </div>

        <EmptyState
          v-else-if="coursesError && courseItems.length === 0"
          title="No se pudieron cargar los cursos"
          description="Vuelve a intentarlo en unos momentos."
          :action-label="t('common.tryAgain')"
          @action="refreshCourses"
        />
        <EmptyState
          v-else-if="coursesEmpty"
          title="No hay cursos abiertos actualmente"
          description="Los próximos cursos y talleres del instituto aparecerán en esta sección."
        />
      </div>
    </section>

    <!-- 3. Últimas Noticias y Divulgación -->
    <section class="home-view__section home-view__section--news home-reveal" aria-labelledby="home-news">
      <div class="container">
        <HomeSectionHeading
          id="home-news"
          :title="t('home.newsTitle')"
          :link-label="t('home.viewAllNews')"
          link-to="/noticias"
        />

        <div v-if="recentNews.length" class="home-view__news-grid">
          <div v-for="(article, index) in recentNews" :key="article.id" class="news-card-wrap">
            <ContentCard
              type="noticia"
              :title="newsTitle(article)"
              :eyebrow="newsIsFeatured(article) ? t('news.featuredTag') : t('news.latestTag')"
              :summary="newsSummary(article)"
              :date="formatApiDate(newsDate(article))"
              :image="mediaUrl(article.portada_url)"
              :image-alt="newsTitle(article)"
              :image-loading="index === 0 ? 'eager' : 'lazy'"
              :to="`/noticias/${article.id}`"
            />
          </div>
        </div>

        <div v-if="newsLoading" class="home-view__skeleton-grid home-view__skeleton-grid--3" role="status">
          <div v-for="n in 3" :key="n" class="skeleton-card"></div>
        </div>

        <EmptyState
          v-else-if="newsError && newsItems.length === 0"
          title="No se pudieron cargar las noticias"
          description="Vuelve a intentarlo en unos momentos."
          :action-label="t('common.tryAgain')"
          @action="refreshNews"
        />
        <EmptyState
          v-else-if="newsEmpty"
          title="No hay noticias publicadas"
          description="Las novedades institucionales del IETA aparecerán aquí."
        />
      </div>
    </section>

    <!-- 4. Carrusel 1: Enlaces de Interés (Solo Logos con Caption) -->
    <section class="home-view__section home-view__section--carousel home-reveal" aria-labelledby="home-institutional">
      <div class="container">
        <div class="carousel-section-header">
          <div>
            <h2 id="home-institutional" class="carousel-title">{{ t('home.externalLinks') }}</h2>
          </div>
          <div class="carousel-nav-btns">
            <button
              type="button"
              class="carousel-nav-btn"
              :aria-label="t('aria.prevSlide')"
              @click="scrollTrack(institutionalTrack, 'left')"
            >
              <ChevronLeft :size="18" />
            </button>
            <button
              type="button"
              class="carousel-nav-btn"
              :aria-label="t('aria.nextSlide')"
              @click="scrollTrack(institutionalTrack, 'right')"
            >
              <ChevronRight :size="18" />
            </button>
          </div>
        </div>

        <div ref="institutionalTrack" class="logo-carousel-track">
          <a
            v-for="item in institutionalLinks"
            :key="item.name"
            :href="item.url"
            target="_blank"
            rel="noopener noreferrer"
            class="logo-item-card"
            :title="item.name"
          >
            <div class="logo-item-card__media">
              <img :src="item.logo" :alt="item.name" class="logo-item-img" />
            </div>
          </a>
        </div>
      </div>
    </section>

    <!-- 5. Carrusel 2: Recursos Estadísticos (Solo Logos) -->
    <section class="home-view__section home-view__section--carousel home-view__section--alt-bg home-reveal" aria-labelledby="home-resources">
      <div class="container">
        <div class="carousel-section-header">
          <div>
            <h2 id="home-resources" class="carousel-title">{{ t('home.resourcesTitle') }}</h2>
          </div>
          <div class="carousel-nav-btns">
            <button
              type="button"
              class="carousel-nav-btn"
              :aria-label="t('aria.prevSlide')"
              @click="scrollTrack(resourcesTrack, 'left')"
            >
              <ChevronLeft :size="18" />
            </button>
            <button
              type="button"
              class="carousel-nav-btn"
              :aria-label="t('aria.nextSlide')"
              @click="scrollTrack(resourcesTrack, 'right')"
            >
              <ChevronRight :size="18" />
            </button>
          </div>
        </div>

        <div ref="resourcesTrack" class="logo-carousel-track">
          <a
            v-for="res in statisticalResources"
            :key="res.name"
            :href="res.url"
            target="_blank"
            rel="noopener noreferrer"
            class="logo-item-card logo-item-card--resource"
            :title="res.name"
          >
            <div class="logo-item-card__media">
              <img :src="res.logo" :alt="res.name" class="logo-item-img" />
            </div>
          </a>
        </div>
      </div>
    </section>
  </main>
</template>

<style scoped>
.home-view {
  overflow: clip;
}

.home-view__section {
  padding-block: clamp(var(--space-6), 6vw, var(--space-6));
}

.home-view__quick-access {
  background: var(--color-blanco);
}

.home-view__section--courses {
  background: var(--color-superficie);
}

.home-view__section--news {
  background: var(--color-blanco);
}

.home-view__section--carousel {
  background: var(--color-blanco);
}

.home-view__section--alt-bg {
  background: #f7f8fc;
}

/* Accesos Rápidos - Friendly Cards */
.home-view__access {
  padding-block: clamp(2rem, 4vw, 3rem);
  background: linear-gradient(180deg, #ffffff 0%, #f7f8fd 100%);
  border-bottom: 1px solid #eaedf6;
}

.friendly-access-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 1.35rem;
  margin-top: 1.25rem;
}

.friendly-card {
  position: relative;
  display: flex;
  flex-direction: column;
  padding: 1.65rem 1.45rem;
  background: linear-gradient(175deg, #ffffff 0%, #fafbff 65%, #f1f4fc 100%);
  border-radius: 16px;
  border: 1px solid rgba(220, 226, 240, 0.95);
  box-shadow: 0 4px 16px -2px rgba(27, 20, 75, 0.04);
  text-decoration: none;
  transition: transform 0.26s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.26s ease, border-color 0.26s ease;
  overflow: hidden;
}

.friendly-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: transparent;
  transition: background 0.24s ease;
}

.friendly-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 18px 38px -6px rgba(27, 20, 75, 0.12);
}

.friendly-card--purple:hover {
  border-color: rgba(43, 27, 109, 0.35);
}
.friendly-card--purple:hover::before {
  background: linear-gradient(90deg, #2b1b6d, #6366f1);
}

.friendly-card--pink:hover {
  border-color: rgba(243, 0, 96, 0.35);
}
.friendly-card--pink:hover::before {
  background: linear-gradient(90deg, #f30060, #fb7185);
}

.friendly-card--blue:hover {
  border-color: rgba(53, 68, 159, 0.35);
}
.friendly-card--blue:hover::before {
  background: linear-gradient(90deg, #35449f, #38bdf8);
}

.friendly-card--emerald:hover {
  border-color: rgba(13, 148, 136, 0.35);
}
.friendly-card--emerald:hover::before {
  background: linear-gradient(90deg, #0d9488, #34d399);
}

.friendly-card__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.15rem;
}

.friendly-card__icon-box {
  display: grid;
  place-items: center;
  width: 3.1rem;
  height: 3.1rem;
  border-radius: 12px;
  transition: transform 0.22s ease, box-shadow 0.22s ease;
}

.friendly-card:hover .friendly-card__icon-box {
  transform: scale(1.08);
}

.friendly-card__icon-box--purple {
  background: rgba(43, 27, 109, 0.08);
  color: #2b1b6d;
  box-shadow: 0 4px 12px rgba(43, 27, 109, 0.08);
}

.friendly-card__icon-box--pink {
  background: rgba(243, 0, 96, 0.08);
  color: #f30060;
  box-shadow: 0 4px 12px rgba(243, 0, 96, 0.08);
}

.friendly-card__icon-box--blue {
  background: rgba(53, 68, 159, 0.08);
  color: #35449f;
  box-shadow: 0 4px 12px rgba(53, 68, 159, 0.08);
}

.friendly-card__icon-box--emerald {
  background: rgba(13, 148, 136, 0.08);
  color: #0d9488;
  box-shadow: 0 4px 12px rgba(13, 148, 136, 0.08);
}

.friendly-card__arrow-pill {
  display: grid;
  place-items: center;
  width: 2rem;
  height: 2rem;
  border-radius: 50%;
  background: #edf1fa;
  color: #64748b;
  transition: transform 0.22s ease, background-color 0.22s ease, color 0.22s ease;
}

.friendly-card:hover .friendly-card__arrow-pill {
  transform: translate(3px, -3px);
  background: var(--color-indigo);
  color: #ffffff;
}

.friendly-card--pink:hover .friendly-card__arrow-pill {
  background: #f30060;
}

.friendly-card--purple:hover .friendly-card__arrow-pill {
  background: #2b1b6d;
}

.friendly-card--emerald:hover .friendly-card__arrow-pill {
  background: #0d9488;
}

.friendly-card__body {
  display: flex;
  flex-direction: column;
  flex-grow: 1;
}

.friendly-card__title {
  font-size: 1.08rem;
  font-weight: 800;
  color: #1b144b;
  margin: 0 0 0.45rem;
  line-height: 1.3;
}

.friendly-card__desc {
  font-size: 0.86rem;
  color: #555e77;
  line-height: 1.5;
  margin: 0 0 0;
  flex-grow: 1;
}

.friendly-card__footer {
  display: flex;
  align-items: center;
  padding-top: 0.75rem;
  border-top: 1px solid rgba(220, 226, 240, 0.6);
}

.friendly-card__action {
  font-size: 0.84rem;
  font-weight: 700;
  color: var(--color-indigo);
  letter-spacing: 0.01em;
  transition: color 0.18s ease;
}

.friendly-card:hover .friendly-card__action {
  color: #f30060;
}

/* Grids for Courses & News */
.home-view__course-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: clamp(1rem, 2.5vw, 1.75rem);
  margin-top: var(--space-3);
}

.course-card-wrap,
.news-card-wrap {
  display: flex;
}

.course-card-wrap :deep(.content-card),
.news-card-wrap :deep(.content-card) {
  width: 100%;
}

.home-view__news-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: clamp(1rem, 2.5vw, 1.75rem);
  margin-top: var(--space-3);
}

/* Carousel Section Styles */
.carousel-section-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 1.5rem;
  gap: 1rem;
}

.carousel-eyebrow {
  font-size: 0.72rem;
  font-weight: 800;
  letter-spacing: 0.08em;
  color: #f30060;
  margin: 0 0 0.25rem;
}

.carousel-title {
  font-size: clamp(1.35rem, 2.5vw, 1.85rem);
  font-weight: 800;
  color: #1b144b;
  margin: 0;
  line-height: 1.25;
}

.carousel-nav-btns {
  display: flex;
  gap: 0.5rem;
  flex-shrink: 0;
}

.carousel-nav-btn {
  display: grid;
  place-items: center;
  width: 2.35rem;
  height: 2.35rem;
  border-radius: 50%;
  border: 1px solid #d4d8e5;
  background: #ffffff;
  color: #1b144b;
  cursor: pointer;
  transition: all 0.2s ease;
}

.carousel-nav-btn:hover {
  border-color: #f30060;
  color: #f30060;
  box-shadow: 0 4px 12px rgba(243, 0, 96, 0.15);
  transform: translateY(-1px);
}

.logo-carousel-track {
  display: flex;
  gap: 1.25rem;
  overflow-x: auto;
  scroll-behavior: smooth;
  scroll-snap-type: x mandatory;
  scrollbar-width: none;
  -ms-overflow-style: none;
  padding-block: 0.5rem 1rem;
}

.logo-carousel-track::-webkit-scrollbar {
  display: none;
}

.logo-item-card {
  flex: 0 0 210px;
  scroll-snap-align: start;
  background: #ffffff;
  border: 1px solid #e4e7f2;
  border-radius: 12px;
  padding: 1.25rem 1rem 0.85rem;
  text-decoration: none;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: space-between;
  gap: 0.85rem;
  transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
  box-shadow: 0 4px 14px rgba(30, 20, 80, 0.04);
}

.logo-item-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 24px rgba(30, 20, 80, 0.09);
  border-color: #f30060;
}

.logo-item-card__media {
  width: 100%;
  height: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0.5rem;
  border-radius: 8px;
}

.logo-item-img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  transition: transform 0.25s ease;
}

.logo-item-card:hover .logo-item-img {
  transform: scale(1.06);
}

.logo-item-card__caption {
  width: 100%;
  text-align: center;
  padding-top: 0.25rem;
  border-top: 1px dashed #e2e8f0;
}

.logo-item-card__caption .caption-path {
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
  font-size: 0.65rem;
  color: #64748b;
  font-weight: 600;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  display: block;
}

.logo-link-card {
  flex: 0 0 250px;
  scroll-snap-align: start;
  background: #ffffff;
  border: 1px solid #e4e7f2;
  border-radius: 12px;
  padding: 1.25rem;
  text-decoration: none;
  color: inherit;
  display: flex;
  flex-direction: column;
  transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
  position: relative;
}

.logo-link-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 24px rgba(30, 20, 80, 0.08);
  border-color: #f30060;
}

.logo-link-card__badge {
  align-self: flex-start;
  font-size: 0.68rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  padding: 0.2rem 0.55rem;
  border-radius: 999px;
  background: rgba(43, 27, 109, 0.08);
  color: #2b1b6d;
  margin-bottom: 0.75rem;
}

.logo-link-card__badge--tool {
  background: rgba(243, 0, 96, 0.08);
  color: #f30060;
}

.logo-link-card__name {
  font-size: 1.05rem;
  font-weight: 800;
  color: #1b144b;
  margin: 0 0 0.25rem;
}

.logo-link-card__label {
  font-size: 0.82rem;
  color: #64748b;
  line-height: 1.4;
  margin: 0 0 1rem;
  flex-grow: 1;
}

.logo-link-card__external {
  font-size: 0.78rem;
  font-weight: 700;
  color: #35449f;
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  transition: gap 0.2s ease;
}

.logo-link-card:hover .logo-link-card__external {
  gap: 0.55rem;
  color: #f30060;
}

/* Skeletons */
.home-view__skeleton-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1.5rem;
  margin-top: 1.5rem;
}

.home-view__skeleton-grid--3 {
  grid-template-columns: repeat(3, minmax(0, 1fr));
}

.skeleton-card {
  height: 18rem;
  border-radius: var(--radius-md);
  background: #edeef5;
}

.skeleton-card--course {
  height: 20rem;
}

/* Responsive */
@media (max-width: 1024px) {
  .friendly-access-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 900px) {
  .home-view__course-grid,
  .home-view__news-grid,
  .home-view__skeleton-grid--3 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 640px) {
  .friendly-access-grid,
  .home-view__course-grid,
  .home-view__news-grid,
  .home-view__skeleton-grid,
  .home-view__skeleton-grid--3 {
    grid-template-columns: 1fr;
  }

  .logo-item-card {
  flex: 0 0 210px;
  scroll-snap-align: start;
  background: #ffffff;
  border: 1px solid #e4e7f2;
  border-radius: 12px;
  padding: 1.25rem 1rem 0.85rem;
  text-decoration: none;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: space-between;
  gap: 0.85rem;
  transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
  box-shadow: 0 4px 14px rgba(30, 20, 80, 0.04);
}

.logo-item-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 24px rgba(30, 20, 80, 0.09);
  border-color: #f30060;
}

.logo-item-card__media {
  width: 100%;
  height: 80px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0.5rem;
  background: #f8fafc;
  border-radius: 8px;
}

.logo-item-img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  transition: transform 0.25s ease;
}

.logo-item-card:hover .logo-item-img {
  transform: scale(1.06);
}

.logo-item-card__caption {
  width: 100%;
  text-align: center;
  padding-top: 0.25rem;
  border-top: 1px dashed #e2e8f0;
}

.logo-item-card__caption .caption-path {
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
  font-size: 0.65rem;
  color: #64748b;
  font-weight: 600;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  display: block;
}

.logo-link-card {
    flex: 0 0 210px;
  }
}
</style>
