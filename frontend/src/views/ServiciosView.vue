<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { ArrowRight, Briefcase, Search } from '@lucide/vue'
import ContentCard from '@/components/common/ContentCard.vue'
import EmptyState from '@/components/common/EmptyState.vue'
import LoadMoreButton from '@/components/common/LoadMoreButton.vue'
import SearchFilterBar from '@/components/common/SearchFilterBar.vue'
import SectionHeaderBand from '@/components/layout/SectionHeaderBand.vue'
import { useI18n } from 'vue-i18n'
import { mediaUrl } from '@/api/response'
import { ServiciosRepository } from '@/api/repositories.js'
import type { ServiceApiItem } from '@/api/types'
import { usePaginatedResource } from '@/composables/usePaginatedResource'
import { useGsapReveal } from '@/composables/useGsapReveal'

const { t } = useI18n()
const query = ref('')
const servicesRoot = ref<HTMLElement | null>(null)
const resource = usePaginatedResource<ServiceApiItem>((page) => ServiciosRepository.list({ page }))

useGsapReveal(servicesRoot, {
  selector: '.service-reveal',
  stagger: 0.08,
  y: 18,
})

// Fallback services for offline / development when API is not running
const defaultServices: ServiceApiItem[] = [
  {
    id: 1,
    titulo: 'Asesoría y Diseño Muestral',
    descripcion: 'Diseño probabilístico, cálculo de tamaños de muestra, construcción de marcos muestrales y esquemas de ponderación estadística para encuestas e investigaciones complejas.',
  },
  {
    id: 2,
    titulo: 'Modelación Estadística y Ciencia de Datos',
    descripcion: 'Ajuste de modelos econométricos, modelos lineales mixtos, inferencia predictiva y análisis multivariado aplicado mediante R, Python y Stan.',
  },
  {
    id: 3,
    titulo: 'Capacitación y Formación Especializada',
    descripcion: 'Talleres intensivos, seminarios y programas de entrenamiento para instituciones y tesistas en técnicas cuantitativas y software analítico.',
  },
]

const servicesList = computed(() => {
  return resource.items.value.length > 0 ? resource.items.value : defaultServices
})

const filteredServices = computed(() => {
  const search = query.value.trim().toLocaleLowerCase()
  return servicesList.value.filter((service) =>
    !search || `${service.titulo} ${service.descripcion ?? ''}`.toLocaleLowerCase().includes(search),
  )
})

onMounted(() => resource.refresh())
</script>

<template>
  <div ref="servicesRoot">
    <SectionHeaderBand
      :title="t('services.title')"
      :subtitle="t('services.subtitle')"
    />

    <main class="container view-body">
      <!-- Search & Controls -->
      <section class="services-controls service-reveal" aria-label="Búsqueda de servicios">
        <SearchFilterBar
          v-model="query"
          :no-results="query.trim().length > 0 && filteredServices.length === 0"
        />

        <div v-if="!resource.loading.value && servicesList.length > 0" class="results-feedback">
          <p v-if="query.trim()" class="feedback-text">
            <Search :size="15" aria-hidden="true" />
            <span>{{ filteredServices.length }} servicio{{ filteredServices.length === 1 ? '' : 's' }} disponible{{ filteredServices.length === 1 ? '' : 's' }} para «{{ query.trim() }}»</span>
          </p>
          <p v-else class="feedback-text feedback-text--muted">
            Mostrando {{ filteredServices.length }} servicios de asesoría y vinculación
          </p>
        </div>
      </section>

      <!-- Skeleton Loading -->
      <div
        v-if="resource.loading.value && resource.items.value.length === 0 && defaultServices.length === 0"
        class="services-skeleton-grid"
        role="status"
        aria-label="Cargando servicios"
      >
        <div v-for="n in 3" :key="n" class="skeleton-service-card"></div>
      </div>

      <!-- Error State (only if no fallback services) -->
      <div
        v-else-if="resource.error.value && servicesList.length === 0"
        class="content-state"
        role="alert"
      >
        <EmptyState
          title="No se pudieron cargar los servicios"
          description="Ocurrió un problema de comunicación al obtener el catálogo de servicios."
          :action-label="t('common.tryAgain')"
          @action="resource.refresh"
        />
      </div>

      <!-- Loaded Grid -->
      <template v-else>
        <div v-if="filteredServices.length" class="services-grid">
          <div
            v-for="service in filteredServices"
            :key="service.id"
            class="service-item service-reveal"
          >
            <ContentCard
              type="servicio"
              :title="service.titulo"
              :summary="service.descripcion ?? undefined"
              :image="mediaUrl(service.imagen_url)"
              :image-alt="service.titulo"
              :to="`/servicios/${service.id}`"
            />
          </div>
        </div>

        <EmptyState
          v-else-if="!resource.loading.value && !query.trim()"
          title="Aún no hay servicios publicados"
          description="Los servicios disponibles del IETA aparecerán en esta sección."
        />
        <EmptyState
          v-else
          title="No se encontraron coincidencias"
          description="Intenta buscar con otros términos o limpia el campo de búsqueda."
        />

        <div v-if="resource.items.value.length > 0" class="services-pagination service-reveal">
          <LoadMoreButton
            :loading="resource.loadingMore.value"
            :has-more="resource.hasMore.value"
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

.services-controls {
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
  margin-bottom: var(--space-4);
}

.results-feedback {
  padding-inline: 0.25rem;
}

.feedback-text {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  color: #4b5266;
  font-size: var(--text-small-size);
}

.feedback-text--muted {
  color: var(--color-violeta);
  font-weight: 500;
}

/* Services Grid */
.services-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: clamp(1rem, 2.5vw, 1.75rem);
}

.service-item {
  display: flex;
}

.service-item :deep(.content-card) {
  width: 100%;
}

.services-pagination {
  display: flex;
  justify-content: center;
  margin-top: var(--space-6);
}

/* Skeleton */
.services-skeleton-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 1.5rem;
}

.skeleton-service-card {
  height: 18rem;
  border-radius: var(--radius-md);
  background: #edeef5;
}

.content-state {
  display: grid;
  min-height: 18rem;
  align-content: center;
  text-align: center;
}

@media (max-width: 960px) {
  .services-grid,
  .services-skeleton-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 620px) {
  .services-grid,
  .services-skeleton-grid {
    grid-template-columns: 1fr;
  }
}
</style>
