<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { RouterLink } from 'vue-router'
import {
  BookOpen,
  CalendarDays,
  GraduationCap,
  Layers,
  Newspaper,
  User,
} from '@lucide/vue'

type ContentType = 'noticia' | 'curso' | 'investigacion' | 'edicion' | 'servicio'

interface Props {
  type: ContentType
  title: string
  eyebrow?: string
  summary?: string
  date?: string
  author?: string
  image?: string
  imageAlt?: string
  imageLoading?: 'eager' | 'lazy'
  headingLevel?: 2 | 3
  to?: string
  href?: string
}

const props = withDefaults(defineProps<Props>(), {
  imageLoading: 'lazy',
  headingLevel: 3,
})
const { t } = useI18n()

const imageFailed = ref(false)
const cardComponent = computed(() => (props.to ? RouterLink : props.href ? 'a' : 'article'))
const cardTarget = computed(() => {
  if (props.to) return { to: props.to }
  if (props.href) return { href: props.href, target: '_blank', rel: 'noopener noreferrer' }
  return {}
})

// Category-based icon and mark for fallback when no image exists
const typeFallbackConfig = computed(() => {
  switch (props.type) {
    case 'curso':
      return {
        icon: GraduationCap,
        mark: 'FORMACIÓN',
        accentClass: 'placeholder--course',
      }
    case 'noticia':
      return {
        icon: Newspaper,
        mark: 'ACTUALIDAD',
        accentClass: 'placeholder--news',
      }
    case 'investigacion':
      return {
        icon: Layers,
        mark: 'CIENCIA',
        accentClass: 'placeholder--research',
      }
    case 'edicion':
      return {
        icon: BookOpen,
        mark: 'VARIANZA',
        accentClass: 'placeholder--journal',
      }
    default:
      return {
        icon: Layers,
        mark: 'IETA',
        accentClass: 'placeholder--default',
      }
  }
})

watch(
  () => props.image,
  () => {
    imageFailed.value = false
  },
)
</script>

<template>
  <component
    :is="cardComponent"
    v-bind="cardTarget"
    class="content-card"
    :class="[`content-card--${type}`, { 'content-card--linked': to || href }]"
  >
    <div class="content-card__media">
      <img
        v-if="image && !imageFailed"
        :src="image"
        :alt="imageAlt || ''"
        :loading="imageLoading"
        @error="imageFailed = true"
      />
      <!-- Rich fallback when no image provided or fails to load -->
      <div
        v-else
        class="content-card__placeholder"
        :class="typeFallbackConfig.accentClass"
        aria-hidden="true"
      >
        <div class="content-card__placeholder-badge">
          <span>IETA</span>
          <span class="dot">·</span>
          <span>{{ typeFallbackConfig.mark }}</span>
        </div>
        <component
          :is="typeFallbackConfig.icon"
          class="content-card__placeholder-icon"
          :size="40"
          :stroke-width="1.5"
        />
      </div>
    </div>

    <div class="content-card__body">
      <p v-if="eyebrow" class="content-card__eyebrow">{{ eyebrow }}</p>

      <component :is="headingLevel === 2 ? 'h2' : 'h3'" class="content-card__title">
        {{ title }}
      </component>

      <p v-if="summary" class="content-card__summary">{{ summary }}</p>

      <dl v-if="date || author" class="content-card__meta">
        <div v-if="date" class="meta-item">
          <CalendarDays :size="14" aria-hidden="true" class="meta-icon" />
          <dt class="sr-only">{{ t('common.date') }}</dt>
          <dd>{{ date }}</dd>
        </div>
        <div v-if="author" class="meta-item">
          <User :size="14" aria-hidden="true" class="meta-icon" />
          <dt class="sr-only">{{ t('common.author') }}</dt>
          <dd>{{ author }}</dd>
        </div>
      </dl>
    </div>
  </component>
</template>

<style scoped>
.content-card {
  display: flex;
  min-width: 0;
  height: 100%;
  flex-direction: column;
  overflow: hidden;
  border: 1px solid var(--color-desactivado);
  border-radius: var(--radius-md);
  background: var(--color-blanco);
  color: var(--color-tinta);
  text-decoration: none;
  box-shadow: 0 2px 6px rgba(20, 22, 28, 0.03);
  transition:
    transform 0.24s cubic-bezier(0.16, 1, 0.3, 1),
    box-shadow 0.24s cubic-bezier(0.16, 1, 0.3, 1),
    border-color 0.2s ease;
}

.content-card--linked:hover {
  transform: translateY(-5px);
  border-color: #fbcfe8;
  box-shadow: 0 16px 32px -8px rgba(43, 27, 109, 0.16), 0 0 0 1.5px rgba(243, 0, 96, 0.28);
}

.content-card--linked:focus-visible {
  outline: 3px solid var(--color-rosa-carmin);
  outline-offset: 3px;
}

.content-card__media {
  position: relative;
  aspect-ratio: 16 / 9;
  overflow: hidden;
  background: var(--color-superficie);
}

.content-card__media img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.35s ease;
}

.content-card--linked:hover .content-card__media img {
  transform: scale(1.04);
}

/* Rich Placeholders */
.content-card__placeholder {
  position: relative;
  display: flex;
  width: 100%;
  height: 100%;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  overflow: hidden;
  user-select: none;
}

.content-card__placeholder::before {
  content: '';
  position: absolute;
  inset: 0;
  background-image: radial-gradient(rgba(255, 255, 255, 0.15) 1px, transparent 1px);
  background-size: 14px 14px;
  opacity: 0.7;
}

.placeholder--course {
  background: linear-gradient(135deg, #233177 0%, #35449f 60%, #6c81c7 100%);
  color: #fff;
}

.placeholder--news {
  background: linear-gradient(135deg, #1b2046 0%, #35449f 50%, #f30060 120%);
  color: #fff;
}

.placeholder--research {
  background: linear-gradient(135deg, #2a1a5e 0%, #583f8b 60%, #8967b8 100%);
  color: #fff;
}

.placeholder--journal {
  background: linear-gradient(135deg, #182038 0%, #2f3b6d 60%, #465593 100%);
  color: #fff;
}

.placeholder--default {
  background: linear-gradient(135deg, #2b3252 0%, #424e77 100%);
  color: #fff;
}

.content-card__placeholder-badge {
  position: relative;
  z-index: 1;
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  padding: 0.2rem 0.65rem;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.18);
  backdrop-filter: blur(4px);
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.06em;
  text-transform: uppercase;
}

.content-card__placeholder-badge .dot {
  opacity: 0.6;
}

.content-card__placeholder-icon {
  position: relative;
  z-index: 1;
  opacity: 0.85;
  transition: transform 0.24s ease;
}

.content-card--linked:hover .content-card__placeholder-icon {
  transform: scale(1.1);
}

.content-card__body {
  display: flex;
  flex: 1;
  flex-direction: column;
  align-items: flex-start;
  gap: var(--space-1);
  padding: clamp(1rem, 2vw, 1.25rem);
}

.content-card__eyebrow {
  color: var(--color-rosa-carmin);
  font-size: var(--text-small-size);
  font-weight: 700;
  letter-spacing: 0.02em;
}

.content-card__title {
  color: var(--color-tinta);
  font-size: var(--text-h5-size);
  font-weight: var(--text-h5-weight);
  line-height: 1.28;
  overflow-wrap: anywhere;
  transition: color 0.15s ease;
}

.content-card--linked:hover .content-card__title {
  color: var(--color-rosa-carmin);
}

.content-card__summary {
  color: #4a5061;
  font-size: var(--text-body-size);
  line-height: 1.5;
  display: -webkit-box;
  overflow: hidden;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 3;
}

.content-card__meta {
  display: flex;
  flex-wrap: wrap;
  gap: 0.85rem;
  margin-top: auto;
  padding-top: var(--space-2);
  color: var(--color-violeta);
  font-size: var(--text-small-size);
}

.meta-item {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
}

.meta-icon {
  opacity: 0.75;
  flex-shrink: 0;
}

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}

@media (prefers-reduced-motion: reduce) {
  .content-card,
  .content-card__media img,
  .content-card__placeholder-icon,
  .content-card__title {
    transition: none;
  }
}
</style>
