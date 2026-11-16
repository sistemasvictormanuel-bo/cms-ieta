<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import { User } from '@lucide/vue'

const props = defineProps<{
  name: string
  role?: string | null
  institution?: string | null
  photoUrl?: string | null
  to?: string | null
  externalUrl?: string | null
  tag?: string | null
}>()

const isRouterLink = computed(() => Boolean(props.to))
const isExternalLink = computed(() => Boolean(props.externalUrl && !props.to))
</script>

<template>
  <component
    :is="isRouterLink ? RouterLink : isExternalLink ? 'a' : 'article'"
    :to="isRouterLink ? to : undefined"
    :href="isExternalLink ? externalUrl : undefined"
    :target="isExternalLink ? '_blank' : undefined"
    :rel="isExternalLink ? 'noopener noreferrer' : undefined"
    class="person-card"
  >
    <!-- Portrait Photo Container (Image 1 style) -->
    <div class="person-card__media">
      <img
        v-if="photoUrl"
        :src="photoUrl"
        :alt="name"
        class="person-card__img"
        loading="lazy"
      />
      <div v-else class="person-card__fallback" aria-hidden="true">
        <User :size="54" class="fallback-icon" />
      </div>

      <span v-if="tag" class="person-card__tag">{{ tag }}</span>
    </div>

    <!-- Metadata Content (Name, Role, Institution/Period) -->
    <div class="person-card__body">
      <h3 class="person-card__name">{{ name }}</h3>
      <p v-if="role" class="person-card__role">{{ role }}</p>
      <p v-if="institution" class="person-card__institution">{{ institution }}</p>
    </div>
  </component>
</template>

<style scoped>
.person-card {
  display: flex;
  flex-direction: column;
  background: #ffffff;
  border-radius: 4px;
  overflow: hidden;
  text-decoration: none;
  color: inherit;
  transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.22s ease;
  position: relative;
}

.person-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 16px 32px -8px rgba(43, 27, 109, 0.16), 0 0 0 1.5px rgba(243, 0, 96, 0.35);
}

.person-card:focus-visible {
  outline: 2px solid #35449f;
  outline-offset: 3px;
}

.person-card__media {
  position: relative;
  width: 100%;
  aspect-ratio: 1 / 1;
  background: #eef2f6;
  overflow: hidden;
}

.person-card__img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center top;
  display: block;
  transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
}

.person-card:hover .person-card__img {
  transform: scale(1.03);
}

.person-card__fallback {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
  color: #64748b;
}

.person-card__tag {
  position: absolute;
  top: 0.75rem;
  left: 0.75rem;
  padding: 0.2rem 0.55rem;
  border-radius: 3px;
  background: rgba(15, 20, 48, 0.85);
  backdrop-filter: blur(4px);
  color: #ffffff;
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.person-card__body {
  padding: 0.9rem 0.35rem 0.75rem 0.15rem;
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
  flex: 1;
}

.person-card__name {
  margin: 0;
  font-family: inherit;
  font-size: 1.05rem;
  font-weight: 700;
  line-height: 1.3;
  color: #111827;
  transition: color 0.15s ease;
}

.person-card:hover .person-card__name {
  color: #f30060;
}

.person-card__role {
  margin: 0;
  font-size: 0.88rem;
  font-weight: 600;
  line-height: 1.35;
  color: #4b5563;
}

.person-card__institution {
  margin: 0.1rem 0 0;
  font-size: 0.82rem;
  font-weight: 400;
  line-height: 1.4;
  color: #6b7280;
}
</style>
