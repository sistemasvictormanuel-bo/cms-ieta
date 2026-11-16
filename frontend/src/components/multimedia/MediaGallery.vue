<script setup lang="ts">
import { computed, nextTick, ref } from 'vue'
import { Image, Play, X } from '@lucide/vue'
import EmptyState from '@/components/common/EmptyState.vue'

export interface MediaGalleryItem {
  id: string | number
  title: string
  description?: string
  kind: 'photo' | 'video'
  url: string
  thumbnailUrl?: string
  alt?: string
  date?: string
}

const props = defineProps<{
  items: MediaGalleryItem[]
}>()

const activeKind = ref<'all' | 'photo' | 'video'>('all')
const search = ref('')
const selectedItem = ref<MediaGalleryItem | null>(null)
const dialogElement = ref<HTMLDialogElement | null>(null)
const mediaFilters: Array<{ value: 'all' | 'photo' | 'video'; label: string }> = [
  { value: 'all', label: 'Todo' },
  { value: 'photo', label: 'Fotos' },
  { value: 'video', label: 'Videos' },
]

const visibleItems = computed(() => {
  const term = search.value.trim().toLocaleLowerCase()
  return props.items.filter((item) => {
    const matchesKind = activeKind.value === 'all' || item.kind === activeKind.value
    const matchesSearch = !term || `${item.title} ${item.description ?? ''}`.toLocaleLowerCase().includes(term)
    return matchesKind && matchesSearch
  })
})

async function openItem(item: MediaGalleryItem) {
  selectedItem.value = item
  await nextTick()
  if (dialogElement.value && !dialogElement.value.open) dialogElement.value.showModal()
}

function closeItem() {
  dialogElement.value?.close()
  selectedItem.value = null
}
</script>

<template>
  <section class="media-gallery" aria-label="Galería multimedia del IETA">
    <div class="media-gallery__toolbar">
      <label class="media-gallery__search">
        <span>Buscar en multimedia</span>
        <input v-model="search" type="search" placeholder="Buscar fotos y videos" />
      </label>
      <div class="media-gallery__filters" role="group" aria-label="Filtrar por tipo de contenido">
        <button
          v-for="filter in mediaFilters"
          :key="filter.value"
          type="button"
          :aria-pressed="activeKind === filter.value"
          :class="{ 'media-gallery__filter--active': activeKind === filter.value }"
          @click="activeKind = filter.value"
        >
          {{ filter.label }}
        </button>
      </div>
    </div>

    <div v-if="visibleItems.length" class="media-gallery__grid">
      <button
        v-for="item in visibleItems"
        :key="item.id"
        type="button"
        class="media-tile"
        :class="`media-tile--${item.kind}`"
        :aria-label="`${item.kind === 'video' ? 'Reproducir' : 'Ver'}: ${item.title}`"
        @click="openItem(item)"
      >
        <img v-if="item.thumbnailUrl || item.kind === 'photo'" :src="item.thumbnailUrl || item.url" :alt="item.alt || item.title" loading="lazy" />
        <span v-else class="media-tile__placeholder" aria-hidden="true"><Image :size="32" /></span>
        <span v-if="item.kind === 'video'" class="media-tile__play" aria-hidden="true"><Play :size="20" fill="currentColor" /></span>
        <span class="media-tile__caption">
          <span>{{ item.kind === 'video' ? 'Video' : 'Fotografía' }}</span>
          <strong>{{ item.title }}</strong>
        </span>
      </button>
    </div>
    <EmptyState
      v-else
      title="Aún no hay contenido multimedia"
      description="Las fotografías y videos de actividades, espacios e iniciativas del IETA aparecerán aquí cuando estén publicados."
    />

    <dialog ref="dialogElement" class="media-dialog" @close="selectedItem = null">
      <div v-if="selectedItem" class="media-dialog__content">
        <button class="media-dialog__close" type="button" aria-label="Cerrar visor" @click="closeItem">
          <X :size="20" />
        </button>
        <video
          v-if="selectedItem.kind === 'video'"
          :src="selectedItem.url"
          :poster="selectedItem.thumbnailUrl"
          controls
          autoplay
          playsinline
        />
        <img v-else :src="selectedItem.url" :alt="selectedItem.alt || selectedItem.title" />
        <div class="media-dialog__caption">
          <h2>{{ selectedItem.title }}</h2>
          <p v-if="selectedItem.description">{{ selectedItem.description }}</p>
        </div>
      </div>
    </dialog>
  </section>
</template>

<style scoped>
.media-gallery {
  display: grid;
  gap: var(--space-3);
}

.media-gallery__toolbar {
  display: flex;
  align-items: end;
  justify-content: space-between;
  gap: var(--space-2);
}

.media-gallery__search {
  display: grid;
  flex: 1 1 20rem;
  gap: 0.35rem;
  max-width: 34rem;
  color: #202e71;
  font-size: var(--text-small-size);
  font-weight: 700;
}

.media-gallery__search input {
  width: 100%;
  min-height: 2.75rem;
  padding-inline: 0.75rem;
  border: 1px solid #aab3d0;
  border-radius: var(--radius-sm);
  background: #fff;
  color: #14161c;
  font: inherit;
}

.media-gallery__filters {
  display: inline-flex;
  gap: 2px;
  padding: 3px;
  border: 1px solid #c9cddd;
  border-radius: var(--radius-sm);
  background: #fff;
}

.media-gallery__filters button {
  min-height: 2.25rem;
  padding-inline: 0.75rem;
  border: 0;
  border-radius: 3px;
  background: transparent;
  color: #202e71;
  font: inherit;
  font-size: 0.8125rem;
  cursor: pointer;
}

.media-gallery__filter--active {
  background: #202e71 !important;
  color: #fff !important;
}

.media-gallery__search input:focus-visible,
.media-gallery__filters button:focus-visible {
  outline: 3px solid #f50088;
  outline-offset: 2px;
}

.media-gallery__grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: var(--space-2);
}

.media-tile {
  position: relative;
  display: block;
  min-width: 0;
  aspect-ratio: 1.36;
  overflow: hidden;
  border: 0;
  background: #202e71;
  color: #fff;
  text-align: left;
  cursor: pointer;
}

.media-tile img,
.media-tile__placeholder {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.media-tile__placeholder {
  display: grid;
  place-items: center;
  color: rgb(255 255 255 / 75%);
  background: linear-gradient(135deg, #202e71, #3e2987);
}

.media-tile::after {
  position: absolute;
  inset: 35% 0 0;
  background: linear-gradient(transparent, rgb(10 15 42 / 86%));
  content: '';
}

.media-tile__play {
  position: absolute;
  z-index: 1;
  top: 50%;
  left: 50%;
  display: grid;
  width: 3rem;
  aspect-ratio: 1;
  place-items: center;
  border: 1px solid rgb(255 255 255 / 80%);
  border-radius: 50%;
  background: rgb(32 46 113 / 78%);
  transform: translate(-50%, -50%);
}

.media-tile__caption {
  position: absolute;
  z-index: 1;
  right: var(--space-1);
  bottom: var(--space-1);
  left: var(--space-1);
  display: grid;
  gap: 0.2rem;
}

.media-tile__caption span {
  color: #ffc4e0;
  font-size: 0.6875rem;
  font-weight: 700;
  text-transform: uppercase;
}

.media-tile__caption strong {
  overflow-wrap: anywhere;
  font-size: 0.9375rem;
  line-height: 1.25;
}

.media-tile:focus-visible {
  outline: 3px solid #f50088;
  outline-offset: 3px;
}

.media-dialog {
  position: fixed;
  inset: 0;
  margin: auto;
  width: min(90vw, 70rem);
  max-width: 90vw;
  max-height: 90vh;
  padding: 0;
  border: 0;
  border-radius: 12px;
  background: #111;
  color: #fff;
  overflow: hidden;
}

.media-dialog::backdrop {
  background: rgb(8 12 32 / 82%);
}

.media-dialog__content {
  position: relative;
  display: grid;
}

.media-dialog__content > img,
.media-dialog__content > video {
  width: 100%;
  max-height: 76vh;
  object-fit: contain;
  background: #080808;
}

.media-dialog__close {
  position: absolute;
  z-index: 2;
  top: 0.75rem;
  right: 0.75rem;
  display: grid;
  width: 2.75rem;
  height: 2.75rem;
  place-items: center;
  border: 0;
  border-radius: 50%;
  background: rgb(0 0 0 / 70%);
  color: #fff;
  cursor: pointer;
}

.media-dialog__caption {
  padding: var(--space-2);
}

.media-dialog__caption h2 {
  font-size: 1.125rem;
}

.media-dialog__caption p {
  margin-top: 0.25rem;
  color: rgb(255 255 255 / 75%);
  font-size: var(--text-small-size);
}

@media (max-width: 800px) {
  .media-gallery__grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 560px) {
  .media-gallery__toolbar {
    align-items: stretch;
    flex-direction: column;
  }

  .media-gallery__filters {
    align-self: flex-start;
  }

  .media-gallery__grid {
    grid-template-columns: 1fr;
  }
}
</style>
