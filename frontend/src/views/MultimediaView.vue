<script setup lang="ts">
import { computed, nextTick, onMounted, ref } from 'vue'
import {
  Calendar,
  ChevronLeft,
  ChevronRight,
  Folder,
  Image as ImageIcon,
  MapPin,
  Search,
  Sparkles,
  X,
} from '@lucide/vue'
import EmptyState from '@/components/common/EmptyState.vue'
import LoadMoreButton from '@/components/common/LoadMoreButton.vue'
import SectionHeaderBand from '@/components/layout/SectionHeaderBand.vue'
import { GaleriaRepository } from '@/api/repositories.js'
import { mediaUrl, unwrapCollection, unwrapResource } from '@/api/response'
import type { GaleriaApiItem } from '@/api/types'
import { usePaginatedResource } from '@/composables/usePaginatedResource'
import { useGsapReveal } from '@/composables/useGsapReveal'

const multimediaRoot = ref<HTMLElement | null>(null)
const selectedCategory = ref<string>('all')
const searchQuery = ref('')

const galleryResource = usePaginatedResource<GaleriaApiItem>((page) =>
  GaleriaRepository.list({
    page,
    categoria: selectedCategory.value !== 'all' ? selectedCategory.value : undefined,
  }),
)

const featuredItems = ref<GaleriaApiItem[]>([])
const categoriesList = ref<string[]>([])
const loadingCategories = ref(false)

// Lightbox modal state
const activeItem = ref<GaleriaApiItem | null>(null)
const activeIndex = ref<number>(-1)
const lightboxDialog = ref<HTMLDialogElement | null>(null)

useGsapReveal(multimediaRoot, {
  selector: '.gallery-reveal',
  stagger: 0.08,
  y: 18,
})

const visibleItems = computed(() => {
  const q = searchQuery.value.trim().toLocaleLowerCase()
  return galleryResource.items.value.filter((item) => {
    if (item.activo === false) return false
    const matchCat =
      selectedCategory.value === 'all' ||
      item.categoria?.toLocaleLowerCase() === selectedCategory.value.toLocaleLowerCase()
    const text = `${item.titulo} ${item.descripcion ?? ''} ${item.lugar ?? ''} ${item.categoria ?? ''}`.toLocaleLowerCase()
    const matchSearch = !q || text.includes(q)
    return matchCat && matchSearch
  })
})

const allCategories = computed(() => {
  const fromItems = galleryResource.items.value
    .map((item) => item.categoria)
    .filter((cat): cat is string => Boolean(cat))
  return ['all', ...new Set([...categoriesList.value, ...fromItems])]
})

function setCategory(cat: string) {
  selectedCategory.value = cat
  void galleryResource.refresh()
}

function openLightbox(item: GaleriaApiItem) {
  activeItem.value = item
  activeIndex.value = visibleItems.value.findIndex((i) => i.id === item.id)
  void nextTick(() => {
    lightboxDialog.value?.showModal()
  })
}

function closeLightbox() {
  lightboxDialog.value?.close()
  activeItem.value = null
  activeIndex.value = -1
}

function prevItem() {
  if (visibleItems.value.length === 0) return
  const newIndex = (activeIndex.value - 1 + visibleItems.value.length) % visibleItems.value.length
  activeIndex.value = newIndex
  activeItem.value = visibleItems.value[newIndex] || null
}

function nextItem() {
  if (visibleItems.value.length === 0) return
  const newIndex = (activeIndex.value + 1) % visibleItems.value.length
  activeIndex.value = newIndex
  activeItem.value = visibleItems.value[newIndex] || null
}

async function loadFeaturedAndCategories() {
  loadingCategories.value = true
  try {
    const featRes = await GaleriaRepository.getDestacados()
    const rawFeat = unwrapResource<GaleriaApiItem[]>(featRes) || featRes
    if (Array.isArray(rawFeat)) {
      featuredItems.value = rawFeat
    }
  } catch {
    // Graceful offline fallback
  }

  try {
    const catRes = await GaleriaRepository.getCategorias()
    const rawCats = unwrapResource<any[]>(catRes) || catRes
    if (Array.isArray(rawCats)) {
      categoriesList.value = rawCats
        .map((c) => (typeof c === 'string' ? c : c?.nombre || c?.categoria || null))
        .filter(Boolean)
    }
  } catch {
    // Graceful offline fallback
  } finally {
    loadingCategories.value = false
  }
}

onMounted(() => {
  void galleryResource.refresh()
  void loadFeaturedAndCategories()
})
</script>

<template>
  <div ref="multimediaRoot" class="multimedia-view">
    <SectionHeaderBand
      title="Galería Multimedia"
      subtitle="Memoria visual, actividades académicas e iniciativas de divulgación científica del IETA."
    />

    <main class="container view-body multimedia-main">
      <!-- 1. Featured Highlights Row if available -->
      <section v-if="featuredItems.length" class="gallery-featured gallery-reveal" aria-label="Imágenes destacadas">
        <header class="featured-header">
          <Sparkles :size="18" class="featured-header__icon" aria-hidden="true" />
          <h2 class="featured-header__title">Momentos Destacados</h2>
        </header>

        <div class="featured-grid">
          <article
            v-for="item in featuredItems.slice(0, 3)"
            :key="item.id"
            class="featured-card"
            @click="openLightbox(item)"
          >
            <img
              :src="mediaUrl(item.imagen_url)"
              :alt="item.titulo"
              class="featured-card__img"
              loading="lazy"
            />
            <div class="featured-card__overlay">
              <span v-if="item.categoria" class="featured-card__tag">{{ item.categoria }}</span>
              <h3 class="featured-card__title">{{ item.titulo }}</h3>
              <p v-if="item.lugar" class="featured-card__meta">
                <MapPin :size="13" aria-hidden="true" />
                <span>{{ item.lugar }}</span>
              </p>
            </div>
          </article>
        </div>
      </section>

      <!-- 2. Search & Category Filters Bar -->
      <section class="gallery-controls gallery-reveal" aria-label="Filtros de galería">
        <!-- Categories Pills -->
        <div class="gallery-categories" role="tablist" aria-label="Filtrar por categoría">
          <button
            v-for="cat in allCategories"
            :key="cat"
            type="button"
            class="category-pill"
            :class="{ 'category-pill--active': selectedCategory === cat }"
            @click="setCategory(cat)"
          >
            <Folder v-if="cat !== 'all'" :size="13" aria-hidden="true" />
            <span>{{ cat === 'all' ? 'Todas las fotografías' : cat }}</span>
          </button>
        </div>

        <!-- Search Input -->
        <div class="gallery-search">
          <Search :size="16" class="gallery-search__icon" aria-hidden="true" />
          <input
            v-model="searchQuery"
            type="search"
            placeholder="Buscar por título, lugar o evento..."
            class="gallery-search__input"
          />
        </div>
      </section>

      <!-- 3. Photo Grid -->
      <div v-if="galleryResource.loading.value && galleryResource.items.value.length === 0" class="gallery-skeleton-grid" role="status">
        <div v-for="n in 8" :key="n" class="skeleton-photo-card"></div>
      </div>

      <div v-else-if="galleryResource.error.value && galleryResource.items.value.length === 0" class="content-state" role="alert">
        <EmptyState
          title="No se pudieron cargar las imágenes de la galería"
          description="Ocurrió un problema de comunicación con el repositorio multimedia."
          action-label="Reintentar"
          @action="galleryResource.refresh"
        />
      </div>

      <template v-else>
        <div v-if="visibleItems.length" class="gallery-grid gallery-reveal">
          <article
            v-for="item in visibleItems"
            :key="item.id"
            class="gallery-item-card"
            tabindex="0"
            role="button"
            :aria-label="`Ver imagen: ${item.titulo}`"
            @click="openLightbox(item)"
            @keydown.enter="openLightbox(item)"
          >
            <div class="gallery-item-card__media">
              <img
                v-if="item.imagen_url"
                :src="mediaUrl(item.imagen_url)"
                :alt="item.titulo"
                class="gallery-item-card__img"
                loading="lazy"
              />
              <div v-else class="gallery-item-card__fallback" aria-hidden="true">
                <ImageIcon :size="48" />
              </div>

              <!-- Overlay gradient with meta info -->
              <div class="gallery-item-card__overlay">
                <span v-if="item.categoria" class="gallery-item-tag">{{ item.categoria }}</span>
                <h3 class="gallery-item-title">{{ item.titulo }}</h3>
                <div class="gallery-item-meta">
                  <span v-if="item.fecha_formateada || item.fecha" class="item-meta-entry">
                    <Calendar :size="12" aria-hidden="true" />
                    <span>{{ item.fecha_formateada || item.fecha }}</span>
                  </span>
                  <span v-if="item.lugar" class="item-meta-entry">
                    <MapPin :size="12" aria-hidden="true" />
                    <span>{{ item.lugar }}</span>
                  </span>
                </div>
              </div>
            </div>
          </article>
        </div>

        <EmptyState
          v-else-if="!galleryResource.loading.value && !searchQuery.trim()"
          title="No hay fotografías en esta categoría"
          description="Las nuevas imágenes de actividades y espacios del IETA se publicarán próximamente."
        />
        <EmptyState
          v-else
          title="No se encontraron coincidencias"
          description="Prueba con otros términos de búsqueda o cambia la categoría seleccionada."
        />

        <div class="pagination-center">
          <LoadMoreButton
            :loading="galleryResource.loadingMore.value"
            :has-more="galleryResource.hasMore.value"
            label="Cargar más fotografías →"
            @load="galleryResource.loadMore"
          />
        </div>
      </template>
    </main>

    <!-- In-Place Lightbox Preview Dialog (No dedicated sub-page needed) -->
    <dialog
      ref="lightboxDialog"
      class="lightbox-dialog"
      @click.self="closeLightbox"
      @close="closeLightbox"
    >
      <div v-if="activeItem" class="lightbox-panel">
        <button
          class="lightbox-close-btn"
          type="button"
          aria-label="Cerrar visor de imagen"
          @click="closeLightbox"
        >
          <X :size="22" aria-hidden="true" />
        </button>

        <!-- Previous button -->
        <button
          v-if="visibleItems.length > 1"
          class="lightbox-nav-btn lightbox-nav-btn--prev"
          type="button"
          aria-label="Imagen anterior"
          @click="prevItem"
        >
          <ChevronLeft :size="28" aria-hidden="true" />
        </button>

        <!-- Image Main View -->
        <div class="lightbox-image-container">
          <img
            :src="mediaUrl(activeItem.imagen_url)"
            :alt="activeItem.titulo"
            class="lightbox-full-img"
          />
        </div>

        <!-- Next button -->
        <button
          v-if="visibleItems.length > 1"
          class="lightbox-nav-btn lightbox-nav-btn--next"
          type="button"
          aria-label="Imagen siguiente"
          @click="nextItem"
        >
          <ChevronRight :size="28" aria-hidden="true" />
        </button>

        <!-- Caption / Details bar -->
        <footer class="lightbox-caption-bar">
          <div class="lightbox-caption-left">
            <span v-if="activeItem.categoria" class="lightbox-category-pill">
              {{ activeItem.categoria }}
            </span>
            <h2 class="lightbox-title">{{ activeItem.titulo }}</h2>
            <p v-if="activeItem.descripcion" class="lightbox-desc">
              {{ activeItem.descripcion }}
            </p>
          </div>

          <div class="lightbox-caption-right">
            <span v-if="activeItem.fecha_formateada || activeItem.fecha" class="lightbox-meta-item">
              <Calendar :size="14" aria-hidden="true" />
              <span>{{ activeItem.fecha_formateada || activeItem.fecha }}</span>
            </span>
            <span v-if="activeItem.lugar" class="lightbox-meta-item">
              <MapPin :size="14" aria-hidden="true" />
              <span>{{ activeItem.lugar }}</span>
            </span>
          </div>
        </footer>
      </div>
    </dialog>
  </div>
</template>

<style scoped>
.multimedia-view {
  min-height: 50vh;
  background: #f8fafc;
}

.multimedia-main {
  padding-block: var(--space-4) var(--space-8);
}

/* 1. Featured Highlights */
.gallery-featured {
  margin-bottom: 2.5rem;
}

.featured-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.featured-header__icon {
  color: #f30060;
}

.featured-header__title {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 700;
  color: #1b144b;
}

.featured-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 1.25rem;
}

.featured-card {
  position: relative;
  aspect-ratio: 16 / 10;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 4px 18px rgba(15, 23, 42, 0.08);
  cursor: pointer;
  background: #0f172a;
}

.featured-card__img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.35s ease;
}

.featured-card:hover .featured-card__img {
  transform: scale(1.05);
}

.featured-card__overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(180deg, rgba(0, 0, 0, 0.1) 0%, rgba(10, 14, 30, 0.88) 100%);
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  padding: 1.25rem;
  color: #ffffff;
}

.featured-card__tag {
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  color: #ff85ae;
  margin-bottom: 0.35rem;
}

.featured-card__title {
  margin: 0 0 0.35rem;
  font-size: 1.05rem;
  font-weight: 700;
  line-height: 1.3;
}

.featured-card__meta {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.8rem;
  color: #cbd5e1;
  margin: 0;
}

/* 2. Controls & Filter Bar */
.gallery-controls {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 1.25rem;
  margin-bottom: 2rem;
  padding-bottom: 1.25rem;
  border-bottom: 1px solid #e2e8f0;
}

.gallery-categories {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.65rem;
}

.category-pill {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.45rem 1rem;
  border-radius: 999px;
  border: 1px solid #d1d5db;
  background: #ffffff;
  color: #374151;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.18s ease;
}

.category-pill:hover {
  border-color: #35449f;
  color: #35449f;
}

.category-pill--active {
  background: #35449f;
  border-color: #35449f;
  color: #ffffff;
}

.category-pill--active:hover {
  background: #2b1b6d;
  color: #ffffff;
}

.gallery-search {
  position: relative;
  display: flex;
  align-items: center;
  min-width: 16rem;
}

.gallery-search__icon {
  position: absolute;
  left: 0.75rem;
  color: #94a3b8;
  pointer-events: none;
}

.gallery-search__input {
  width: 100%;
  height: 2.45rem;
  padding: 0 0.85rem 0 2.2rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  background: #ffffff;
  font-size: 0.88rem;
  color: #0f172a;
  outline: none;
  transition: border-color 0.15s ease;
}

.gallery-search__input:focus {
  border-color: #35449f;
}

/* 3. Photo Grid (4 Columns) */
.gallery-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 1.25rem;
}

.gallery-item-card {
  position: relative;
  border-radius: 8px;
  overflow: hidden;
  background: #e2e8f0;
  cursor: pointer;
  box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
  transition: transform 0.22s ease, box-shadow 0.22s ease;
}

.gallery-item-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 28px -4px rgba(15, 23, 42, 0.16);
}

.gallery-item-card:focus-visible {
  outline: 3px solid #35449f;
  outline-offset: 2px;
}

.gallery-item-card__media {
  position: relative;
  width: 100%;
  aspect-ratio: 4 / 3;
}

.gallery-item-card__img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  transition: transform 0.35s ease;
}

.gallery-item-card:hover .gallery-item-card__img {
  transform: scale(1.06);
}

.gallery-item-card__fallback {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #e2e8f0;
  color: #94a3b8;
}

.gallery-item-card__overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(180deg, transparent 40%, rgba(10, 14, 30, 0.92) 100%);
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  padding: 1rem;
  color: #ffffff;
  opacity: 0.92;
  transition: opacity 0.2s ease;
}

.gallery-item-card:hover .gallery-item-card__overlay {
  opacity: 1;
}

.gallery-item-tag {
  align-self: flex-start;
  font-size: 0.68rem;
  font-weight: 700;
  text-transform: uppercase;
  color: #ff85ae;
  letter-spacing: 0.04em;
  margin-bottom: 0.25rem;
}

.gallery-item-title {
  margin: 0 0 0.35rem;
  font-size: 0.95rem;
  font-weight: 700;
  line-height: 1.3;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.gallery-item-meta {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 0.75rem;
  color: #cbd5e1;
}

.item-meta-entry {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
}

/* Skeletons */
.gallery-skeleton-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 1.25rem;
}

.skeleton-photo-card {
  aspect-ratio: 4 / 3;
  border-radius: 8px;
  background: #edeef5;
}

.content-state {
  display: grid;
  min-height: 20rem;
  align-content: center;
  text-align: center;
}

.pagination-center {
  display: flex;
  justify-content: center;
  margin-top: var(--space-6);
}

/* Lightbox Dialog */
.lightbox-dialog {
  position: fixed;
  inset: 0;
  margin: auto;
  width: min(60rem, calc(100vw - 2rem));
  max-width: calc(100vw - 2rem);
  max-height: min(92vh, 56rem);
  padding: 0;
  border: 0;
  border-radius: 12px;
  background: #0b0f19;
  color: #ffffff;
  box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.6);
  overflow: hidden;
}

.lightbox-dialog::backdrop {
  background: rgba(8, 11, 20, 0.88);
  backdrop-filter: blur(8px);
}

.lightbox-panel {
  position: relative;
  display: flex;
  flex-direction: column;
  max-height: min(92vh, 56rem);
}

.lightbox-close-btn {
  position: absolute;
  top: 1rem;
  right: 1rem;
  z-index: 20;
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 50%;
  background: rgba(15, 23, 42, 0.75);
  backdrop-filter: blur(4px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: #ffffff;
  display: grid;
  place-items: center;
  cursor: pointer;
  transition: transform 0.15s ease, background-color 0.15s ease;
}

.lightbox-close-btn:hover {
  transform: rotate(90deg);
  background: #f30060;
  border-color: #f30060;
}

.lightbox-nav-btn {
  position: absolute;
  top: 45%;
  transform: translateY(-50%);
  z-index: 15;
  width: 3rem;
  height: 3rem;
  border-radius: 50%;
  background: rgba(15, 23, 42, 0.6);
  border: 1px solid rgba(255, 255, 255, 0.15);
  color: #ffffff;
  display: grid;
  place-items: center;
  cursor: pointer;
  transition: background-color 0.15s ease, transform 0.15s ease;
}

.lightbox-nav-btn:hover {
  background: rgba(243, 0, 96, 0.85);
  transform: translateY(-50%) scale(1.08);
}

.lightbox-nav-btn--prev {
  left: 1rem;
}

.lightbox-nav-btn--next {
  right: 1rem;
}

.lightbox-image-container {
  display: flex;
  align-items: center;
  justify-content: center;
  background: #000000;
  max-height: min(68vh, 42rem);
  overflow: hidden;
}

.lightbox-full-img {
  max-width: 100%;
  max-height: min(68vh, 42rem);
  object-fit: contain;
  display: block;
}

.lightbox-caption-bar {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1.5rem;
  padding: 1.25rem 1.75rem;
  background: #111827;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.lightbox-caption-left {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  flex: 1;
}

.lightbox-category-pill {
  align-self: flex-start;
  padding: 0.2rem 0.6rem;
  border-radius: 999px;
  background: rgba(243, 0, 96, 0.25);
  color: #ff85ae;
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
}

.lightbox-title {
  margin: 0;
  font-size: 1.2rem;
  font-weight: 700;
  color: #ffffff;
}

.lightbox-desc {
  margin: 0;
  font-size: 0.9rem;
  color: #94a3b8;
  line-height: 1.5;
}

.lightbox-caption-right {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  flex-shrink: 0;
}

.lightbox-meta-item {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.82rem;
  color: #cbd5e1;
}

/* Responsive */
@media (max-width: 1024px) {
  .gallery-grid,
  .gallery-skeleton-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}

@media (max-width: 768px) {
  .featured-grid {
    grid-template-columns: 1fr;
  }
  .gallery-grid,
  .gallery-skeleton-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
  .gallery-controls {
    flex-direction: column;
    align-items: stretch;
  }
  .gallery-search {
    width: 100%;
  }
  .lightbox-caption-bar {
    flex-direction: column;
    gap: 0.75rem;
  }
}

@media (max-width: 480px) {
  .gallery-grid,
  .gallery-skeleton-grid {
    grid-template-columns: 1fr;
  }
}
</style>
