<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import {
  Calendar,
  Clock,
  GraduationCap,
  RotateCcw,
  Search,
  User,
} from '@lucide/vue'
import EmptyState from '@/components/common/EmptyState.vue'
import LoadMoreButton from '@/components/common/LoadMoreButton.vue'
import SectionHeaderBand from '@/components/layout/SectionHeaderBand.vue'
import { CursosRepository } from '@/api/repositories.js'
import { mediaUrl } from '@/api/response'
import type { CourseApiItem } from '@/api/types'
import { usePaginatedResource } from '@/composables/usePaginatedResource'
import { useGsapReveal } from '@/composables/useGsapReveal'

const query = ref('')
const selectedModality = ref<string>('all')
const coursesRoot = ref<HTMLElement | null>(null)

const resource = usePaginatedResource<CourseApiItem>((page) =>
  CursosRepository.list({ page }),
)

function getDocenteName(docente: unknown): string {
  if (!docente) return 'Docente IETA'
  if (typeof docente === 'string') return docente
  if (typeof docente === 'object') {
    const d = docente as Record<string, unknown>
    return (
      (d.nombre_completo as string) ||
      `${(d.nombres as string) || ''} ${(d.apellidos as string) || ''}`.trim() ||
      'Docente IETA'
    )
  }
  return 'Docente IETA'
}

function getModalityName(course: CourseApiItem): string {
  return course.modalidad_curso?.nombre || course.modalidad_curso?.mc_nombre || 'Especializado'
}

const availableModalities = computed(() => {
  const mods = new Set<string>()
  for (const item of resource.items.value) {
    const name = getModalityName(item)
    if (name && name !== 'Especializado') {
      mods.add(name)
    }
  }
  return Array.from(mods)
})

const filteredCourses = computed(() => {
  const search = query.value.trim().toLocaleLowerCase()
  return resource.items.value.filter((course) => {
    if (selectedModality.value !== 'all') {
      const modName = getModalityName(course)
      if (modName !== selectedModality.value) return false
    }

    if (search) {
      const docente = getDocenteName(course.docente).toLocaleLowerCase()
      const title = (course.titulo || '').toLocaleLowerCase()
      const desc = (course.descripcion || '').toLocaleLowerCase()
      if (!title.includes(search) && !desc.includes(search) && !docente.includes(search)) {
        return false
      }
    }

    return true
  })
})

function resetFilters() {
  query.value = ''
  selectedModality.value = 'all'
}

useGsapReveal(coursesRoot, {
  selector: '.course-reveal',
  stagger: 0.08,
  y: 20,
})

onMounted(() => {
  void resource.refresh()
})
</script>

<template>
  <div ref="coursesRoot" class="courses-view">
    <SectionHeaderBand
      title="Cursos y Talleres"
      subtitle="Programas de capacitación y formación continua en estadística, análisis de datos y metodologías cuantitativas aplicadas."
    />

    <main class="container view-body">
      <!-- Top Filter & Search Controls -->
      <section class="courses-controls-section course-reveal" aria-label="Filtros de cursos">
        <div class="courses-filter-top">
          <div class="modality-pills">
            <button
              type="button"
              class="modality-pill"
              :class="{ 'modality-pill--active': selectedModality === 'all' }"
              @click="selectedModality = 'all'"
            >
              Todos los programas
            </button>
            <button
              v-for="mod in availableModalities"
              :key="mod"
              type="button"
              class="modality-pill"
              :class="{ 'modality-pill--active': selectedModality === mod }"
              @click="selectedModality = mod"
            >
              {{ mod }}
            </button>
          </div>

          <div class="courses-search-box">
            <Search :size="16" class="search-icon" aria-hidden="true" />
            <input
              v-model="query"
              type="search"
              class="search-input"
              placeholder="Buscar curso por título, tema o docente..."
            />
            <button
              v-if="query.trim()"
              type="button"
              class="clear-btn"
              @click="query = ''"
              aria-label="Limpiar búsqueda"
            >
              ✕
            </button>
          </div>
        </div>

        <div class="courses-status-row">
          <span class="count-text">
            <strong>{{ filteredCourses.length }}</strong> curso{{ filteredCourses.length === 1 ? '' : 's' }} disponible{{ filteredCourses.length === 1 ? '' : 's' }}
          </span>
          <button
            v-if="query.trim() || selectedModality !== 'all'"
            type="button"
            class="reset-btn"
            @click="resetFilters"
          >
            <RotateCcw :size="13" aria-hidden="true" />
            Restablecer filtros
          </button>
        </div>
      </section>

      <!-- Skeleton Loading State -->
      <div
        v-if="resource.loading.value && resource.items.value.length === 0"
        class="courses-skeleton-grid"
        role="status"
        aria-label="Cargando cursos"
      >
        <div v-for="n in 6" :key="n" class="skeleton-card">
          <div class="skeleton-media"></div>
          <div class="skeleton-body">
            <div class="skeleton-line skeleton-line--pill"></div>
            <div class="skeleton-line skeleton-line--title"></div>
            <div class="skeleton-line skeleton-line--text"></div>
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
          title="No se pudieron cargar los cursos"
          description="Ocurrió un problema de comunicación al consultar el catálogo de programas."
          action-label="Reintentar"
          @action="resource.refresh"
        />
      </div>

      <!-- Courses Grid -->
      <template v-else>
        <div v-if="filteredCourses.length > 0" class="courses-grid">
          <article
            v-for="course in filteredCourses"
            :key="course.id"
            class="course-card course-reveal"
          >
            <RouterLink
              :to="`/cursos-talleres/${encodeURIComponent(course.slug || String(course.id))}`"
              class="course-card__link"
            >
              <div class="course-card__media">
                <img
                  v-if="course.portada_url"
                  :src="mediaUrl(course.portada_url)"
                  :alt="course.titulo"
                  class="course-card__img"
                  loading="lazy"
                />
                <div v-else class="course-card__placeholder">
                  <GraduationCap :size="40" />
                </div>
                <span class="course-card__badge">
                  {{ getModalityName(course) }}
                </span>
              </div>

              <div class="course-card__body">
                <h3 class="course-card__title">
                  {{ course.titulo }}
                </h3>

                <p v-if="course.descripcion" class="course-card__excerpt">
                  {{ course.descripcion }}
                </p>

                <div class="course-card__meta">
                  <span class="meta-item">
                    <User :size="14" aria-hidden="true" />
                    <span>{{ getDocenteName(course.docente) }}</span>
                  </span>
                  <span v-if="course.fecha_inicio" class="meta-item">
                    <Calendar :size="14" aria-hidden="true" />
                    <span>Inicia: {{ course.fecha_inicio }}</span>
                  </span>
                  <span v-if="course.duracion" class="meta-item">
                    <Clock :size="14" aria-hidden="true" />
                    <span>{{ course.duracion }}</span>
                  </span>
                </div>

                <div class="course-card__action">
                  <span>Ver detalles e inscripción</span>
                  <span class="action-arrow">→</span>
                </div>
              </div>
            </RouterLink>
          </article>
        </div>

        <!-- Empty Filter Results -->
        <div v-else class="empty-wrap course-reveal">
          <EmptyState
            title="No se encontraron cursos coincidentes"
            description="Intenta buscar con otros términos o limpia los filtros activos."
            action-label="Ver todos los cursos"
            @action="resetFilters"
          />
        </div>

        <!-- Pagination -->
        <div class="courses-pagination course-reveal">
          <LoadMoreButton
            :loading="resource.loadingMore.value"
            :has-more="resource.hasMore.value"
            label="Cargar más cursos →"
            @load="resource.loadMore"
          />
        </div>
      </template>
    </main>
  </div>
</template>

<style scoped>
.courses-view {
  min-height: 70vh;
}

.view-body {
  padding-block: clamp(2rem, 4vw, 3.5rem);
}

/* Filter Controls */
.courses-controls-section {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-bottom: 2.25rem;
  padding: 1.25rem 1.5rem;
  border: 1px solid rgba(220, 224, 235, 0.85);
  border-radius: var(--radius-md);
  background: #ffffff;
  box-shadow: 0 4px 18px rgba(27, 20, 75, 0.03);
}

.courses-filter-top {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}

.modality-pills {
  display: inline-flex;
  flex-wrap: wrap;
  gap: 0.4rem;
  background: #f1f4f9;
  padding: 0.3rem;
  border-radius: 999px;
}

.modality-pill {
  border: none;
  background: transparent;
  padding: 0.45rem 1.1rem;
  border-radius: 999px;
  font-size: 0.84rem;
  font-weight: 600;
  color: #555d77;
  cursor: pointer;
  transition: all 0.18s ease;
  font-family: inherit;
}

.modality-pill:hover {
  color: #1b144b;
}

.modality-pill--active {
  background: #1b144b;
  color: #ffffff !important;
  box-shadow: 0 2px 8px rgba(27, 20, 75, 0.2);
}

.courses-search-box {
  position: relative;
  display: flex;
  align-items: center;
  flex: 1;
  min-width: 260px;
  max-width: 420px;
}

.search-icon {
  position: absolute;
  left: 0.85rem;
  color: #8c93a8;
  pointer-events: none;
}

.search-input {
  width: 100%;
  padding: 0.55rem 2.25rem 0.55rem 2.4rem;
  border: 1px solid #d9e0ec;
  border-radius: var(--radius-sm);
  font-size: 0.86rem;
  background: #ffffff;
  color: #1b144b;
  outline: none;
  font-family: inherit;
  transition: border-color 0.15s ease;
}

.search-input:focus {
  border-color: #1b144b;
}

.clear-btn {
  position: absolute;
  right: 0.75rem;
  border: none;
  background: transparent;
  color: #8c93a8;
  font-size: 0.85rem;
  cursor: pointer;
}

.courses-status-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding-top: 0.75rem;
  border-top: 1px solid #eef2f7;
}

.count-text {
  font-size: 0.84rem;
  color: #64748b;
}

.reset-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  border: none;
  background: transparent;
  color: #f30060;
  font-size: 0.82rem;
  font-weight: 600;
  cursor: pointer;
  font-family: inherit;
}

.reset-btn:hover {
  text-decoration: underline;
}

/* 3-Column Courses Grid */
.courses-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: clamp(1.25rem, 2vw, 1.85rem);
}

.course-card {
  border: 1px solid rgba(220, 224, 235, 0.85);
  border-radius: var(--radius-md);
  overflow: hidden;
  background: #ffffff;
  box-shadow: 0 4px 18px rgba(27, 20, 75, 0.04);
  transition: transform 0.25s cubic-bezier(0.2, 0, 0.2, 1), box-shadow 0.25s ease, border-color 0.25s ease;
}

.course-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 16px 32px -8px rgba(43, 27, 109, 0.14), 0 0 0 1.5px rgba(243, 0, 96, 0.25);
  border-color: #fbcfe8;
}

.course-card__link {
  display: flex;
  flex-direction: column;
  height: 100%;
  text-decoration: none;
  color: inherit;
}

.course-card__media {
  position: relative;
  height: 12.5rem;
  overflow: hidden;
  background: #f0f3fa;
}

.course-card__img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.4s cubic-bezier(0.2, 0, 0.2, 1);
}

.course-card:hover .course-card__img {
  transform: scale(1.05);
}

.course-card__placeholder {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #a8b0c5;
}

.course-card__badge {
  position: absolute;
  top: 0.85rem;
  right: 0.85rem;
  background: rgba(27, 20, 75, 0.88);
  backdrop-filter: blur(4px);
  color: #ffffff;
  font-size: 0.74rem;
  font-weight: 700;
  padding: 0.25rem 0.65rem;
  border-radius: 999px;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.course-card__body {
  padding: 1.4rem;
  display: flex;
  flex-direction: column;
  flex: 1;
  gap: 0.65rem;
}

.course-card__title {
  color: #1b144b;
  font-size: 1.15rem;
  font-weight: 700;
  line-height: 1.35;
  margin: 0;
  transition: color 0.18s ease;
}

.course-card:hover .course-card__title {
  color: #f30060;
}

.course-card__excerpt {
  color: #555d77;
  font-size: 0.88rem;
  line-height: 1.55;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  margin: 0;
}

.course-card__meta {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
  margin-top: auto;
  padding-top: 0.85rem;
  border-top: 1px solid #f1f4f9;
}

.meta-item {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  font-size: 0.82rem;
  color: #64748b;
  font-weight: 500;
}

.course-card__action {
  display: inline-flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 0.5rem;
  font-size: 0.86rem;
  font-weight: 700;
  color: #1b144b;
  transition: color 0.18s ease;
}

.course-card:hover .course-card__action {
  color: #f30060;
}

.action-arrow {
  transition: transform 0.2s ease;
}

.course-card:hover .action-arrow {
  transform: translateX(4px);
}

/* Skeleton Loading */
.courses-skeleton-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 1.5rem;
}

.skeleton-card {
  border: 1px solid var(--color-desactivado);
  border-radius: var(--radius-md);
  overflow: hidden;
  background: #ffffff;
}

.skeleton-media {
  height: 12.5rem;
  background: #edeef5;
}

.skeleton-body {
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.skeleton-line {
  border-radius: var(--radius-sm);
  background: #edeef5;
}

.skeleton-line--pill {
  width: 35%;
  height: 0.8rem;
}

.skeleton-line--title {
  width: 85%;
  height: 1.4rem;
}

.skeleton-line--text {
  width: 60%;
  height: 0.8rem;
}

.courses-pagination {
  display: flex;
  justify-content: center;
  margin-top: clamp(2rem, 4vw, 3rem);
}

/* Responsive */
@media (max-width: 960px) {
  .courses-grid,
  .courses-skeleton-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 640px) {
  .courses-grid,
  .courses-skeleton-grid {
    grid-template-columns: 1fr;
  }
  .modality-pills {
    width: 100%;
  }
  .courses-search-box {
    max-width: 100%;
  }
}
</style>
