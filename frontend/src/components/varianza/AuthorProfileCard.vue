<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import { User } from '@lucide/vue'

const props = defineProps<{
  name: string
  image?: string
  specialty?: string
  role?: string
  institution?: string
  degree?: string
  to: string
  compact?: boolean
}>()

const displayName = computed(() => {
  if (props.degree) return `${props.name}, ${props.degree}`
  return props.name
})
</script>

<template>
  <RouterLink class="author-card" :class="{ 'author-card--compact': compact }" :to="to">
    <div class="author-card__portrait">
      <img v-if="image" :src="image" :alt="`Retrato de ${displayName}`" loading="lazy" />
      <div v-else class="author-card__avatar-fallback" aria-hidden="true">
        <User :size="48" />
      </div>
    </div>
    <div class="author-card__copy">
      <h3 class="author-card__name">{{ displayName }}</h3>
      <p v-if="role || specialty" class="author-card__role">{{ role || specialty }}</p>
      <p v-if="institution || (role && specialty)" class="author-card__institution">
        {{ institution || specialty }}
      </p>
    </div>
  </RouterLink>
</template>

<style scoped>
.author-card {
  display: flex;
  flex-direction: column;
  min-width: 0;
  overflow: hidden;
  background: #ffffff;
  color: #111827;
  text-decoration: none;
  transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.22s ease;
  border-radius: 4px;
}

.author-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 16px 32px -8px rgba(43, 27, 109, 0.16), 0 0 0 1.5px rgba(243, 0, 96, 0.35);
}

.author-card:focus-visible {
  outline: 2px solid #35449f;
  outline-offset: 3px;
}

.author-card__portrait {
  position: relative;
  width: 100%;
  aspect-ratio: 1 / 1;
  overflow: hidden;
  background: #eef2f6;
  display: flex;
  align-items: center;
  justify-content: center;
}

.author-card__portrait img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center top;
  transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
}

.author-card:hover .author-card__portrait img {
  transform: scale(1.03);
}

.author-card__avatar-fallback {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
  color: #64748b;
}

.author-card__copy {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
  padding: 0.9rem 0.35rem 0.75rem 0.15rem;
}

.author-card__name {
  margin: 0;
  font-size: 1.05rem;
  font-weight: 700;
  line-height: 1.3;
  color: #111827;
  transition: color 0.15s ease;
}

.author-card:hover .author-card__name {
  color: #f30060;
}

.author-card__role {
  margin: 0;
  font-size: 0.88rem;
  font-weight: 600;
  line-height: 1.35;
  color: #4b5563;
}

.author-card__institution {
  margin: 0.1rem 0 0;
  font-size: 0.82rem;
  font-weight: 400;
  line-height: 1.4;
  color: #6b7280;
}

.author-card--compact {
  flex-direction: row;
  align-items: center;
  gap: 1rem;
  padding: 0.5rem;
  background: #ffffff;
  border: 1px solid #e5e7eb;
}

.author-card--compact .author-card__portrait {
  width: 4.5rem;
  height: 4.5rem;
  aspect-ratio: 1;
  border-radius: 4px;
  flex-shrink: 0;
}

.author-card--compact .author-card__copy {
  padding: 0;
}
</style>
