<script setup lang="ts">
import { computed, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import {
  ArrowLeft,
  Briefcase,
  Layers,
  Mail,
  Phone,
} from '@lucide/vue'
import EditorJsContent from '@/components/common/EditorJsContent.vue'
import EmptyState from '@/components/common/EmptyState.vue'
import SectionHeaderBand from '@/components/layout/SectionHeaderBand.vue'
import { mediaUrl } from '@/api/response'
import { ServiciosRepository } from '@/api/repositories.js'
import type { ServiceApiItem } from '@/api/types'
import { useApiResource } from '@/composables/useApiResource'
import { useGsapReveal } from '@/composables/useGsapReveal'

const route = useRoute()
const serviceRoot = ref<HTMLElement | null>(null)
const serviceId = computed(() => String(route.params.slug ?? ''))
const allServices = ref<ServiceApiItem[]>([])

const { data: service, loading, error, reload } = useApiResource<ServiceApiItem>(
  serviceId,
  (id) => (/^\d+$/.test(id) ? ServiciosRepository.getById(id) : ServiciosRepository.getBySlug(id)),
)

const image = computed(() => mediaUrl(service.value?.imagen_url))
const apiBaseUrl = import.meta.env.VITE_API_URL

useGsapReveal(serviceRoot, {
  selector: '.service-reveal',
  stagger: 0.08,
  y: 18,
})

async function loadAllServices() {
  try {
    const response = (await ServiciosRepository.list({ page: 1 })) as { data: ServiceApiItem[] }
    if (Array.isArray(response.data)) {
      allServices.value = response.data
    }
  } catch {
    // Non-blocking
  }
}

loadAllServices()
</script>

<template>
  <div ref="serviceRoot">
    <SectionHeaderBand
      :title="service?.titulo || 'Servicio Institucional'"
      :subtitle="service?.descripcion || 'Asesoría y vinculación del Instituto de Estadística Teórica y Aplicada'"
      :image="image"
      image-alt="Imagen del servicio"
    >
      <template #extra>
        <div class="header-back-slot">
          <RouterLink class="header-back-link" to="/servicios">
            ‹ Volver a Servicios
          </RouterLink>
        </div>
      </template>
    </SectionHeaderBand>

    <main class="container view-body service-detail">
      <!-- Loading State -->
      <div v-if="loading" class="service-skeleton" role="status" aria-label="Cargando servicio">
        <div class="skeleton-pill"></div>
        <div class="skeleton-title"></div>
        <div class="skeleton-body"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="service-detail__state" role="alert">
        <EmptyState
          title="No se pudo cargar el servicio"
          description="Comprueba el identificador o vuelve al listado de servicios."
          action-label="Reintentar"
          @action="reload"
        />
      </div>

      <!-- Master-Detail Layout (Left Tabs + Right Card) -->
      <div v-else-if="service" class="service-master-detail service-reveal">
        <!-- Left Sidebar: Vertical Tabs Menu -->
        <aside class="service-sidebar" aria-label="Lista de servicios">
          <div class="service-sidebar__list">
            <RouterLink
              v-for="item in (allServices.length ? allServices : [service])"
              :key="item.id"
              :to="`/servicios/${item.slug || item.id}`"
              class="service-sidebar__link"
              :class="{ 'service-sidebar__link--active': String(item.id) === String(service.id) }"
            >
              <span class="service-sidebar__name">{{ item.titulo }}</span>
              <span class="service-sidebar__arrow">›</span>
            </RouterLink>
          </div>
        </aside>

        <!-- Right Column: Content Card with Description & Cover Inside -->
        <article class="service-content-card">
          <!-- Cover Media inside Card -->
          <div v-if="image" class="service-card-cover">
            <img :src="image" :alt="service.titulo" class="service-card-cover__img" loading="lazy" />
          </div>

          <div class="service-card-inner">
            <header class="service-card-header">
              <span class="service-pill">
                <Briefcase :size="13" aria-hidden="true" />
                <span>Servicio Institucional</span>
              </span>
              <h1 class="service-card-title">{{ service.titulo }}</h1>
            </header>

            <!-- Description inside Card -->
            <div v-if="service.descripcion" class="service-card-desc">
              <h2 class="service-card-subtitle">Descripción del Servicio</h2>
              <p>{{ service.descripcion }}</p>
            </div>

            <!-- Rich Body Content -->
            <section v-if="service.contenido" class="service-card-body">
              <EditorJsContent :content="service.contenido" :api-base-url="apiBaseUrl" />
            </section>

            
          </div>
        </article>
      </div>

      <EmptyState v-else title="No se encontró el servicio solicitado" />
    </main>
  </div>
</template>

<style scoped>
.service-detail {
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

/* Master-Detail Grid */
.service-master-detail {
  display: grid;
  grid-template-columns: 290px 1fr;
  gap: clamp(1.5rem, 3vw, 2.75rem);
  align-items: flex-start;
  margin-block: var(--space-4);
}

/* Sidebar Vertical Tabs Menu */
.service-sidebar {
  position: sticky;
  top: calc(var(--header-height) + 1.5rem);
  background: var(--color-blanco);
  border: 1px solid rgba(220, 224, 235, 0.85);
  border-radius: var(--radius-md);
  box-shadow: 0 4px 18px rgba(27, 20, 75, 0.04);
  overflow: hidden;
}

.service-sidebar__list {
  display: flex;
  flex-direction: column;
}

.service-sidebar__link {
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

.service-sidebar__link:last-child {
  border-bottom: none;
}

.service-sidebar__link:hover {
  background: #f8f9fe;
  color: #1b144b;
}

.service-sidebar__link--active {
  background: #f5f4fd;
  color: #2c1b6e;
  font-weight: 700;
  border-left: 3px solid #2c1b6e;
}

.service-sidebar__arrow {
  color: #8c93a8;
  font-size: 1.15rem;
  line-height: 1;
}

.service-sidebar__link--active .service-sidebar__arrow {
  color: #2c1b6e;
  transform: translateX(2px);
}

/* Right Content Card with Cover inside */
.service-content-card {
  display: flex;
  flex-direction: column;
  background: var(--color-blanco);
  border: 1px solid rgba(220, 224, 235, 0.85);
  border-radius: var(--radius-md);
  overflow: hidden;
  box-shadow: 0 4px 18px rgba(27, 20, 75, 0.04);
}

.service-card-cover {
  width: 100%;
  max-height: 20rem;
  overflow: hidden;
  background: #f0f3fa;
}

.service-card-cover__img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.service-card-inner {
  padding: clamp(1.5rem, 3.5vw, 2.5rem);
  display: flex;
  flex-direction: column;
  gap: 1.75rem;
}

.service-pill {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.2rem 0.65rem;
  border-radius: 999px;
  background: rgba(53, 68, 159, 0.1);
  color: var(--color-indigo);
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  margin-bottom: 0.5rem;
}

.service-card-title {
  color: #1b144b;
  font-size: clamp(1.85rem, 3.5vw, 2.35rem);
  font-weight: 700;
  line-height: 1.25;
}

.service-card-desc {
  padding: 1.25rem 1.5rem;
  border-left: 4px solid var(--color-rosa-carmin);
  background: #f8f9fe;
  border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
}

.service-card-subtitle {
  color: #1b144b;
  font-size: 1.15rem;
  font-weight: 700;
  margin-bottom: 0.4rem;
}

.service-card-desc p {
  color: #3b4258;
  font-size: 1.02rem;
  line-height: 1.65;
}

.service-card-body {
  line-height: 1.75;
}

.cta-box {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: space-between;
  gap: var(--space-3);
  padding: clamp(1.25rem, 3vw, 2rem);
  border-radius: var(--radius-md);
  background: #f8f9fe;
  border: 1px solid rgba(220, 224, 235, 0.85);
}

.cta-copy h3 {
  color: #1b144b;
  font-size: 1.15rem;
  font-weight: 700;
  margin-bottom: 0.3rem;
}

.cta-copy p {
  color: #555c73;
  font-size: 0.92rem;
  line-height: 1.5;
}

.cta-actions {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.75rem;
}

.btn-primary {
  display: inline-flex;
  min-height: 2.75rem;
  align-items: center;
  gap: 0.45rem;
  padding: 0.55rem 1.25rem;
  border-radius: var(--radius-sm);
  background: var(--color-rosa-carmin);
  color: var(--color-blanco);
  font-size: var(--text-small-size);
  font-weight: 700;
  text-decoration: none;
  box-shadow: 0 4px 12px rgba(243, 0, 96, 0.25);
  transition: transform 0.15s ease, background-color 0.15s ease;
}

.btn-primary:hover {
  background: #db0056;
  transform: translateY(-2px);
}

.btn-secondary {
  display: inline-flex;
  min-height: 2.75rem;
  align-items: center;
  gap: 0.45rem;
  padding: 0.55rem 1.15rem;
  border: 1px solid rgba(220, 224, 235, 0.9);
  border-radius: var(--radius-sm);
  background: var(--color-blanco);
  color: var(--color-indigo);
  font-size: var(--text-small-size);
  font-weight: 700;
  text-decoration: none;
  transition: background-color 0.15s ease;
}

.btn-secondary:hover {
  background: #f1f3fa;
}

/* Skeleton */
.service-skeleton {
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

.service-detail__state {
  display: grid;
  min-height: 20rem;
  align-content: center;
  text-align: center;
}

/* Responsive */
@media (max-width: 960px) {
  .service-master-detail {
    grid-template-columns: 1fr;
  }

  .service-sidebar {
    position: static;
  }
}
</style>
