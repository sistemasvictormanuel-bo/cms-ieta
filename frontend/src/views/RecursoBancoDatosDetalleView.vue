<script setup lang="ts">
import { computed, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import {
  ArrowLeft,
  Calendar,
  Database,
  Download,
  ExternalLink,
  FileSpreadsheet,
  MapPin,
  Tag,
} from '@lucide/vue'
import EmptyState from '@/components/common/EmptyState.vue'
import SectionHeaderBand from '@/components/layout/SectionHeaderBand.vue'
import { mediaUrl } from '@/api/response'
import { BancoDatosRepository } from '@/api/repositories.js'
import type { DataBankApiItem } from '@/api/types'
import { useApiResource } from '@/composables/useApiResource'
import { useGsapReveal } from '@/composables/useGsapReveal'

const route = useRoute()
const datasetRoot = ref<HTMLElement | null>(null)
const resourceId = computed(() => String(route.params.id ?? ''))

const { data: dataset, loading, error, reload } = useApiResource<DataBankApiItem>(
  resourceId,
  (id) => BancoDatosRepository.getById(id),
)

const title = computed(() => dataset.value?.titulo || 'Conjunto de datos')
const cover = computed(() => mediaUrl(dataset.value?.portada_url))

useGsapReveal(datasetRoot, {
  selector: '.dataset-reveal',
  stagger: 0.08,
  y: 18,
})
</script>

<template>
  <div ref="datasetRoot">
    <SectionHeaderBand
      :title="title"
      subtitle="Banco de Datos y Repositorio Estadístico del IETA"
    />

    <main class="container view-body dataset-detail">
      <!-- Back Navigation -->
      <nav class="dataset-nav dataset-reveal" aria-label="Navegación de retorno">
        <RouterLink class="dataset-back" to="/recursos">
          <ArrowLeft :size="16" aria-hidden="true" />
          <span>Volver a Recursos y Banco de Datos</span>
        </RouterLink>
      </nav>

      <!-- Loading State -->
      <div v-if="loading" class="dataset-skeleton" role="status" aria-label="Cargando dataset">
        <div class="skeleton-pill"></div>
        <div class="skeleton-title"></div>
        <div class="skeleton-banner"></div>
        <div class="skeleton-meta"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="dataset-state" role="alert">
        <EmptyState
          title="No se pudo cargar el conjunto de datos"
          description="Verifica el identificador o vuelve al listado de recursos."
          action-label="Reintentar"
          @action="reload"
        />
      </div>

      <!-- Not Found -->
      <EmptyState v-else-if="!dataset" title="No se encontró el conjunto de datos solicitado" />

      <!-- Loaded Content -->
      <article v-else class="dataset-card dataset-reveal">
        <header class="dataset-header">
          <div class="tag-row">
            <span class="type-pill">
              <Database :size="13" aria-hidden="true" />
              <span>{{ dataset.tipo_dato || 'Banco de Datos' }}</span>
            </span>
            <span v-if="dataset.anio" class="year-pill">
              <Calendar :size="13" aria-hidden="true" />
              <span>Año {{ dataset.anio }}</span>
            </span>
          </div>

          <h1 class="dataset-title">{{ title }}</h1>
        </header>

        <!-- Cover Image or Branded Placeholder -->
        <figure class="dataset-media">
          <img v-if="cover" class="dataset-cover" :src="cover" :alt="title" loading="lazy" />
          <div v-else class="dataset-placeholder-banner">
            <FileSpreadsheet :size="52" class="placeholder-icon" aria-hidden="true" />
            <span class="placeholder-tag">IETA · REPOSITORIO DE DATOS</span>
          </div>
        </figure>

        <!-- Description -->
        <div v-if="dataset.descripcion" class="dataset-description">
          <p>{{ dataset.descripcion }}</p>
        </div>

        <!-- Metadata Grid -->
        <div class="dataset-meta-box">
          <dl class="meta-grid">
            <div v-if="dataset.codigo" class="meta-item">
              <dt>Código de Registro</dt>
              <dd class="code-value">{{ dataset.codigo }}</dd>
            </div>

            <div v-if="dataset.locacion" class="meta-item">
              <dt>Cobertura Geográfica</dt>
              <dd>
                <MapPin :size="14" aria-hidden="true" class="meta-icon" />
                <span>{{ dataset.locacion }}</span>
              </dd>
            </div>

            <div v-if="dataset.areas_tematicas?.length" class="meta-item meta-item--wide">
              <dt>Áreas Temáticas</dt>
              <dd class="chips-row">
                <span v-for="area in dataset.areas_tematicas" :key="area.id" class="meta-area-chip">
                  <Tag :size="12" aria-hidden="true" />
                  <span>{{ area.nombre }}</span>
                </span>
              </dd>
            </div>
          </dl>
        </div>

        <!-- Download and Access Links Bar -->
        <div
          v-if="dataset.bdd_url || dataset.archivo_url || dataset.url"
          class="dataset-actions-bar"
          aria-label="Acceso a archivos y descarga"
        >
          <a
            v-if="mediaUrl(dataset.bdd_url)"
            class="action-btn action-btn--primary"
            :href="mediaUrl(dataset.bdd_url)"
            target="_blank"
            rel="noopener noreferrer"
          >
            <Database :size="16" aria-hidden="true" />
            <span>Abrir Base de Datos</span>
          </a>

          <a
            v-if="mediaUrl(dataset.archivo_url)"
            class="action-btn action-btn--download"
            :href="mediaUrl(dataset.archivo_url)"
            target="_blank"
            rel="noopener noreferrer"
          >
            <Download :size="16" aria-hidden="true" />
            <span>Descargar Archivo</span>
          </a>

          <a
            v-if="mediaUrl(dataset.url)"
            class="action-btn action-btn--external"
            :href="mediaUrl(dataset.url)"
            target="_blank"
            rel="noopener noreferrer"
          >
            <ExternalLink :size="16" aria-hidden="true" />
            <span>Recurso Externo</span>
          </a>
        </div>

        <EmptyState
          v-else-if="!dataset.descripcion"
          title="Este conjunto aún no tiene recursos descargables asociados"
        />
      </article>
    </main>
  </div>
</template>

<style scoped>
.dataset-detail {
  min-height: 48vh;
  padding-block: var(--space-4) var(--space-8);
}

.dataset-nav {
  margin-bottom: var(--space-3);
}

.dataset-back {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  color: var(--color-indigo);
  font-size: var(--text-small-size);
  font-weight: 700;
  text-decoration: none;
  transition: transform 0.15s ease, color 0.15s ease;
}

.dataset-back:hover {
  transform: translateX(-4px);
  color: var(--color-rosa-carmin);
}

.dataset-card {
  max-width: 78ch;
  margin-inline: auto;
}

.dataset-header {
  margin-bottom: var(--space-3);
}

.tag-row {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
}

.type-pill,
.year-pill {
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

.type-pill {
  background: rgba(53, 68, 159, 0.1);
  color: var(--color-indigo);
}

.year-pill {
  background: rgba(113, 88, 159, 0.1);
  color: var(--color-violeta);
  font-variant-numeric: tabular-nums;
}

.dataset-title {
  color: var(--color-indigo);
  font-size: clamp(1.85rem, 4vw, 2.5rem);
  font-weight: 700;
  line-height: 1.22;
}

/* Media */
.dataset-media {
  overflow: hidden;
  border-radius: var(--radius-md);
  margin-block: var(--space-4);
  box-shadow: 0 4px 18px rgba(20, 22, 28, 0.07);
}

.dataset-cover {
  width: 100%;
  max-height: 28rem;
  object-fit: cover;
  display: block;
}

.dataset-placeholder-banner {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  height: 14rem;
  background: linear-gradient(135deg, #182245 0%, #293875 60%, #465593 100%);
  color: #fff;
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
  letter-spacing: 0.05em;
}

/* Description */
.dataset-description {
  margin-block: var(--space-4);
  font-size: var(--text-large-size);
  line-height: 1.65;
  color: #2b324b;
}

/* Meta Box */
.dataset-meta-box {
  margin-block: var(--space-4);
  padding: clamp(1.25rem, 3vw, 1.75rem);
  border-left: 4px solid var(--color-rosa-carmin);
  background: var(--color-superficie);
  border-radius: 0 var(--radius-md) var(--radius-md) 0;
}

.meta-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: var(--space-3);
}

.meta-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.meta-item--wide {
  grid-column: 1 / -1;
}

.meta-item dt {
  color: var(--color-violeta);
  font-size: var(--text-small-size);
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.meta-item dd {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  color: var(--color-tinta);
  font-size: var(--text-body-size);
  font-weight: 500;
}

.code-value {
  font-family: monospace;
  font-weight: 700;
  color: var(--color-indigo);
}

.meta-icon {
  opacity: 0.7;
}

.chips-row {
  display: flex;
  flex-wrap: wrap;
  gap: 0.4rem;
}

.meta-area-chip {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  padding: 0.2rem 0.6rem;
  border-radius: 999px;
  background: var(--color-blanco);
  border: 1px solid var(--color-desactivado);
  color: #434b63;
  font-size: 0.75rem;
  font-weight: 600;
}

/* Actions Bar */
.dataset-actions-bar {
  display: flex;
  flex-wrap: wrap;
  gap: 0.85rem;
  margin-top: var(--space-6);
  padding-top: var(--space-4);
  border-top: 1px solid var(--color-desactivado);
}

.action-btn {
  display: inline-flex;
  min-height: 2.75rem;
  align-items: center;
  gap: 0.5rem;
  padding: 0.55rem 1.25rem;
  border-radius: var(--radius-sm);
  font-size: var(--text-small-size);
  font-weight: 700;
  text-decoration: none;
  transition: transform 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;
}

.action-btn--primary {
  background: var(--color-indigo);
  color: var(--color-blanco);
}

.action-btn--primary:hover {
  background: #253381;
  transform: translateY(-2px);
}

.action-btn--download {
  background: var(--color-rosa-carmin);
  color: var(--color-blanco);
  box-shadow: 0 4px 12px rgba(243, 0, 96, 0.25);
}

.action-btn--download:hover {
  background: #db0056;
  transform: translateY(-2px);
}

.action-btn--external {
  border: 1px solid var(--color-desactivado);
  background: var(--color-blanco);
  color: var(--color-indigo);
}

.action-btn--external:hover {
  background: #f1f3fa;
}

/* Skeleton */
.dataset-skeleton {
  max-width: 78ch;
  margin-inline: auto;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  padding: 2rem 0;
}

.skeleton-pill {
  width: 9rem;
  height: 1.5rem;
  border-radius: 999px;
  background: #edeef5;
}

.skeleton-title {
  width: 85%;
  height: 2.8rem;
  border-radius: var(--radius-sm);
  background: #edeef5;
}

.skeleton-banner {
  width: 100%;
  height: 16rem;
  border-radius: var(--radius-md);
  background: #edeef5;
}

.skeleton-meta {
  width: 100%;
  height: 8rem;
  border-radius: var(--radius-md);
  background: #edeef5;
}

.dataset-state {
  display: grid;
  min-height: 20rem;
  align-content: center;
  text-align: center;
}

@media (max-width: 600px) {
  .meta-grid {
    grid-template-columns: 1fr;
  }
  .dataset-actions-bar > * {
    width: 100%;
    justify-content: center;
  }
}
</style>
