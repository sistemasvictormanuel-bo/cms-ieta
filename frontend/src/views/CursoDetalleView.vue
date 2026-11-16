<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRoute, RouterLink } from 'vue-router'
import {
  ArrowLeft,
  Banknote,
  Calendar,
  CalendarDays,
  Clock,
  GraduationCap,
  Hourglass,
  Mail,
  MapPin,
  Phone,
} from '@lucide/vue'
import EditorJsContent from '@/components/common/EditorJsContent.vue'
import EmptyState from '@/components/common/EmptyState.vue'
import StatusBadge from '@/components/common/StatusBadge.vue'
import SectionHeaderBand from '@/components/layout/SectionHeaderBand.vue'
import { courseTitle, courseType, formatApiDate } from '@/api/normalizers'
import { mediaUrl } from '@/api/response'
import { CursosRepository } from '@/api/repositories.js'
import type { CourseApiItem } from '@/api/types'
import { useApiResource } from '@/composables/useApiResource'
import { useGsapReveal } from '@/composables/useGsapReveal'

const route = useRoute()
const courseRoot = ref<HTMLElement | null>(null)
const courseId = computed(() => String(route.params.slug ?? ''))

const { data: course, loading, error, reload } = useApiResource<CourseApiItem>(
  courseId,
  (id) => CursosRepository.getBySlug(id),
)

const apiBaseUrl = import.meta.env.VITE_API_URL
const cover = computed(() => mediaUrl(course.value?.portada_url))
const title = computed(() => (course.value ? courseTitle(course.value) : 'Curso y Taller'))
const content = computed(() => course.value?.contenido)

const modalityName = computed(() => {
  if (!course.value?.modalidad_curso) return null
  return course.value.modalidad_curso.nombre || course.value.modalidad_curso.mc_nombre || null
})

function formatDocenteValue(val: unknown): string {
  if (!val) return ''
  if (typeof val === 'string') return val.trim()
  if (Array.isArray(val)) {
    return val.map((v) => formatDocenteValue(v)).filter(Boolean).join(', ')
  }
  if (typeof val === 'object') {
    const obj = val as Record<string, unknown>
    if (typeof obj.nombre_completo === 'string' && obj.nombre_completo.trim()) {
      return obj.nombre_completo.trim()
    }
    if (obj.persona) {
      const fromPersona = formatDocenteValue(obj.persona)
      if (fromPersona) return fromPersona
    }
    const nameParts = [obj.p_nombre, obj.p_apellido_paterno, obj.p_apellido_materno]
      .filter((p): p is string => typeof p === 'string' && Boolean(p.trim()))
    if (nameParts.length > 0) {
      return nameParts.join(' ')
    }
    if (typeof obj.nombre === 'string' && obj.nombre.trim()) {
      return obj.nombre.trim()
    }
    if (typeof obj.titulo === 'string' && obj.titulo.trim()) {
      return obj.titulo.trim()
    }
  }
  return ''
}

const docenteName = computed(() => {
  return formatDocenteValue(course.value?.docente)
})

useGsapReveal(courseRoot, {
  selector: '.course-reveal',
  stagger: 0.08,
  y: 18,
})
</script>

<template>
  <div ref="courseRoot">
    <SectionHeaderBand
      :title="title"
      subtitle="Formación continua y capacitación especializada del IETA"
    />

    <main class="container view-body course-detail">
      <!-- Back Navigation -->
      <nav class="course-detail__nav course-reveal" aria-label="Navegación de retorno">
        <RouterLink class="course-detail__back" to="/">
          <ArrowLeft :size="16" aria-hidden="true" />
          <span>Volver al inicio</span>
        </RouterLink>
      </nav>

      <!-- Loading State -->
      <div v-if="loading" class="course-skeleton" role="status" aria-label="Cargando curso">
        <div class="skeleton-main">
          <div class="skeleton-banner"></div>
          <div class="skeleton-title"></div>
          <div class="skeleton-text"></div>
        </div>
        <div class="skeleton-aside"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="course-detail__state" role="alert">
        <EmptyState
          title="No se pudo cargar la información del curso"
          description="Revisa el enlace o inténtalo de nuevo en unos momentos."
          action-label="Reintentar"
          @action="reload"
        />
      </div>

      <!-- Not Found -->
      <EmptyState v-else-if="!course" title="No se encontró el curso solicitado" />

      <!-- Loaded Course -->
      <article v-else class="course-detail__layout">
        <!-- Main Column -->
        <div class="course-detail__main">
          <!-- Cover Banner or Fallback -->
          <div class="course-detail__media-wrapper course-reveal">
            <img v-if="cover" class="course-detail__cover" :src="cover" :alt="title" />
            <div v-else class="course-detail__placeholder">
              <GraduationCap :size="56" class="placeholder-icon" aria-hidden="true" />
              <div class="placeholder-tag">IETA · FORMACIÓN CONTINUA</div>
            </div>
          </div>

          <!-- Header / Title -->
          <header class="course-detail__heading course-reveal">
            <div class="course-detail__tags">
              <span class="course-tag course-tag--type">{{ courseType(course) }}</span>
              <span v-if="modalityName" class="course-tag course-tag--modality">
                <MapPin :size="13" aria-hidden="true" />
                <span>{{ modalityName }}</span>
              </span>
            </div>

            <h1 class="course-detail__title">{{ title }}</h1>

            <div class="course-detail__status-row">
              <StatusBadge
                :registration-start="course.fecha_inicio ?? undefined"
                :registration-end="course.fecha_inicio ?? undefined"
                :course-end="course.fecha_fin ?? undefined"
              />
            </div>
          </header>

          <!-- Description Lead -->
          <div v-if="course.descripcion" class="course-detail__lead course-reveal">
            <p>{{ course.descripcion }}</p>
          </div>

          <!-- Syllabus / Rich Body Content -->
          <section class="course-detail__syllabus course-reveal">
            <h2 class="section-title">Contenido y Objetivos del Programa</h2>
            <EditorJsContent
              v-if="content"
              :content="content"
              :api-base-url="apiBaseUrl"
            />
            <EmptyState
              v-else
              title="Programa en preparación"
              description="El temario y cronograma detallado de este curso se publicará en los próximos días."
            />
          </section>
        </div>

        <!-- Sticky Factsheet Sidebar -->
        <aside class="course-detail__sidebar course-reveal" aria-label="Ficha técnica del curso">
          <div class="factsheet-card">
            <div class="factsheet-card__accent"></div>

            <header class="factsheet-card__header">
              <h2 class="factsheet-card__title">Ficha Informativa</h2>
              <StatusBadge
                :registration-start="course.fecha_inicio ?? undefined"
                :registration-end="course.fecha_inicio ?? undefined"
                :course-end="course.fecha_fin ?? undefined"
              />
            </header>

            <dl class="factsheet-list">
              <div v-if="docenteName" class="factsheet-item">
                <dt class="factsheet-item__term">
                  <GraduationCap :size="16" class="term-icon" aria-hidden="true" />
                  <span>Docente</span>
                </dt>
                <dd class="factsheet-item__desc">{{ docenteName }}</dd>
              </div>

              <div v-if="course.fecha_inicio" class="factsheet-item">
                <dt class="factsheet-item__term">
                  <Calendar :size="16" class="term-icon" aria-hidden="true" />
                  <span>Fecha de Inicio</span>
                </dt>
                <dd class="factsheet-item__desc">{{ formatApiDate(course.fecha_inicio) }}</dd>
              </div>

              <div v-if="course.fecha_fin" class="factsheet-item">
                <dt class="factsheet-item__term">
                  <CalendarDays :size="16" class="term-icon" aria-hidden="true" />
                  <span>Finalización</span>
                </dt>
                <dd class="factsheet-item__desc">{{ formatApiDate(course.fecha_fin) }}</dd>
              </div>

              <div v-if="course.hora_inicio" class="factsheet-item">
                <dt class="factsheet-item__term">
                  <Clock :size="16" class="term-icon" aria-hidden="true" />
                  <span>Horario</span>
                </dt>
                <dd class="factsheet-item__desc">
                  {{ course.hora_inicio }}<template v-if="course.hora_fin"> – {{ course.hora_fin }}</template>
                </dd>
              </div>

              <div v-if="course.duracion" class="factsheet-item">
                <dt class="factsheet-item__term">
                  <Hourglass :size="16" class="term-icon" aria-hidden="true" />
                  <span>Carga Horaria</span>
                </dt>
                <dd class="factsheet-item__desc">{{ course.duracion }}</dd>
              </div>

              <div v-if="course.costo" class="factsheet-item factsheet-item--cost">
                <dt class="factsheet-item__term">
                  <Banknote :size="16" class="term-icon" aria-hidden="true" />
                  <span>Inversión</span>
                </dt>
                <dd class="factsheet-item__desc cost-value">{{ course.costo }}</dd>
              </div>
            </dl>

            <div class="factsheet-actions">
              <a
                v-if="course.email_info"
                class="btn-cta btn-cta--primary"
                :href="`mailto:${course.email_info}?subject=Consulta sobre curso: ${encodeURIComponent(title)}`"
              >
                <Mail :size="16" aria-hidden="true" />
                <span>Consultar por correo</span>
              </a>

              <a
                v-if="course.telefono_ref"
                class="btn-cta btn-cta--secondary"
                :href="`tel:${course.telefono_ref.replace(/[^+\d]/g, '')}`"
              >
                <Phone :size="16" aria-hidden="true" />
                <span>{{ course.telefono_ref }}</span>
              </a>
            </div>
          </div>
        </aside>
      </article>
    </main>
  </div>
</template>

<style scoped>
.course-detail {
  min-height: 50vh;
  padding-block: var(--space-3) var(--space-8);
}

.course-detail__nav {
  margin-bottom: var(--space-3);
}

.course-detail__back {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  color: var(--color-indigo);
  font-size: var(--text-small-size);
  font-weight: 700;
  text-decoration: none;
  transition: transform 0.15s ease, color 0.15s ease;
}

.course-detail__back:hover {
  transform: translateX(-4px);
  color: var(--color-rosa-carmin);
}

/* Two-column layout */
.course-detail__layout {
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(18rem, 22rem);
  gap: clamp(1.5rem, 4vw, 3rem);
  align-items: start;
}

.course-detail__main {
  min-width: 0;
}

/* Media Cover */
.course-detail__media-wrapper {
  overflow: hidden;
  border-radius: var(--radius-md);
  margin-bottom: var(--space-4);
  box-shadow: 0 4px 18px rgba(20, 22, 28, 0.08);
}

.course-detail__cover {
  width: 100%;
  max-height: 28rem;
  object-fit: cover;
  display: block;
}

.course-detail__placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  height: 16rem;
  background: linear-gradient(135deg, #1d255a 0%, #35449f 60%, #5e47a6 100%);
  color: var(--color-blanco);
}

.placeholder-icon {
  opacity: 0.85;
}

.placeholder-tag {
  padding: 0.25rem 0.75rem;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.18);
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.06em;
}

/* Heading */
.course-detail__heading {
  margin-bottom: var(--space-4);
}

.course-detail__tags {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.65rem;
}

.course-tag {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.2rem 0.65rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.course-tag--type {
  background: rgba(53, 68, 159, 0.1);
  color: var(--color-indigo);
}

.course-tag--modality {
  background: rgba(243, 0, 96, 0.1);
  color: var(--color-rosa-carmin);
}

.course-detail__title {
  color: var(--color-indigo);
  font-size: clamp(1.85rem, 4vw, 2.5rem);
  font-weight: 700;
  line-height: 1.2;
  margin-bottom: 0.75rem;
}

.course-detail__status-row {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

/* Lead text */
.course-detail__lead {
  margin-bottom: var(--space-4);
  padding: var(--space-2) var(--space-3);
  border-left: 4px solid var(--color-rosa-carmin);
  background: var(--color-superficie);
  border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
}

.course-detail__lead p {
  color: #212844;
  font-size: var(--text-large-size);
  line-height: 1.6;
}

/* Syllabus section */
.course-detail__syllabus {
  margin-top: var(--space-6);
  padding-top: var(--space-4);
  border-top: 1px solid var(--color-desactivado);
}

.section-title {
  color: var(--color-indigo);
  font-size: var(--text-h4-size);
  font-weight: 700;
  margin-bottom: var(--space-3);
}

/* Factsheet Sidebar */
.course-detail__sidebar {
  position: sticky;
  top: calc(var(--header-height) + 1.5rem);
}

.factsheet-card {
  overflow: hidden;
  border: 1px solid var(--color-desactivado);
  border-radius: var(--radius-md);
  background: var(--color-blanco);
  box-shadow: 0 4px 18px rgba(20, 22, 28, 0.06);
}

.factsheet-card__accent {
  height: 4px;
  background: linear-gradient(90deg, var(--color-rosa-carmin) 0%, var(--color-indigo) 100%);
}

.factsheet-card__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
  padding: 1.25rem 1.25rem 0.75rem;
  border-bottom: 1px solid var(--color-superficie);
}

.factsheet-card__title {
  color: var(--color-indigo);
  font-size: var(--text-h6-size);
  font-weight: 700;
}

.factsheet-list {
  display: flex;
  flex-direction: column;
  padding: 0.75rem 1.25rem;
}

.factsheet-item {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
  padding-block: 0.6rem;
  border-bottom: 1px solid #f1f2f7;
}

.factsheet-item:last-child {
  border-bottom: none;
}

.factsheet-item__term {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  color: var(--color-violeta);
  font-size: var(--text-small-size);
  font-weight: 700;
}

.term-icon {
  opacity: 0.75;
}

.factsheet-item__desc {
  color: var(--color-tinta);
  font-size: var(--text-body-size);
  font-weight: 500;
}

.cost-value {
  color: var(--color-rosa-carmin);
  font-size: 1.15rem;
  font-weight: 700;
}

.factsheet-actions {
  display: flex;
  flex-direction: column;
  gap: 0.65rem;
  padding: 1rem 1.25rem 1.25rem;
  background: var(--color-superficie);
  border-top: 1px solid var(--color-desactivado);
}

.btn-cta {
  display: inline-flex;
  min-height: 2.75rem;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: var(--radius-sm);
  font-size: var(--text-small-size);
  font-weight: 700;
  text-decoration: none;
  transition: transform 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;
}

.btn-cta--primary {
  background: var(--color-rosa-carmin);
  color: var(--color-blanco);
  box-shadow: 0 4px 12px rgba(243, 0, 96, 0.25);
}

.btn-cta--primary:hover {
  transform: translateY(-2px);
  background: #db0056;
  box-shadow: 0 6px 16px rgba(243, 0, 96, 0.35);
}

.btn-cta--secondary {
  border: 1px solid var(--color-desactivado);
  background: var(--color-blanco);
  color: var(--color-indigo);
}

.btn-cta--secondary:hover {
  background: #f1f3fa;
  border-color: #c7cbdb;
}

/* Skeleton Loading */
.course-skeleton {
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(18rem, 22rem);
  gap: 2rem;
}

.skeleton-banner {
  width: 100%;
  height: 20rem;
  border-radius: var(--radius-md);
  background: #edeef5;
}

.skeleton-title {
  width: 80%;
  height: 2.5rem;
  margin-top: 1.5rem;
  border-radius: var(--radius-sm);
  background: #edeef5;
}

.skeleton-text {
  width: 100%;
  height: 10rem;
  margin-top: 1rem;
  border-radius: var(--radius-sm);
  background: #edeef5;
}

.skeleton-aside {
  height: 22rem;
  border-radius: var(--radius-md);
  background: #edeef5;
}

.course-detail__state {
  display: grid;
  min-height: 20rem;
  align-content: center;
  text-align: center;
}

@media (max-width: 880px) {
  .course-detail__layout {
    grid-template-columns: 1fr;
  }
  .course-detail__sidebar {
    position: static;
  }
}
</style>
