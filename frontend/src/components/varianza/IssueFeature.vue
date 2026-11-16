<script setup lang="ts">
import { ArrowRight } from '@lucide/vue'
import { RouterLink } from 'vue-router'

defineProps<{
  title: string
  issue: string
  cover?: string
  coverAlt: string
  to: string
}>()
</script>

<template>
  <article class="issue-feature">
    <RouterLink class="issue-feature__cover-link" :to="to" :aria-label="`Ver ${title}`">
      <img v-if="cover" class="issue-feature__cover" :src="cover" :alt="coverAlt" loading="lazy" />
      <span v-else class="issue-feature__cover-placeholder" aria-hidden="true">VARIANZA</span>
    </RouterLink>
    <div class="issue-feature__details">
      <p>Última edición</p>
      <h2>{{ title }}</h2>
      <span>{{ issue }}</span>
      <RouterLink class="issue-feature__link" :to="to" aria-label="Consultar última edición">
        <ArrowRight :size="22" aria-hidden="true" />
      </RouterLink>
    </div>
  </article>
</template>

<style scoped>
.issue-feature {
  display: grid;
  grid-template-columns: minmax(10rem, 15rem) minmax(0, 1fr);
  align-items: end;
  gap: var(--space-2);
}

.issue-feature__cover-link {
  display: block;
  overflow: hidden;
  background: #f5f5f8;
}

.issue-feature__cover {
  width: 100%;
  aspect-ratio: 0.76;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.issue-feature__cover-placeholder {
  display: grid;
  aspect-ratio: 0.76;
  place-items: center;
  background: linear-gradient(155deg, #f50088, #202e71 68%);
  color: #fff;
  font-size: 1.3rem;
  font-weight: 700;
}

.issue-feature__cover-link:hover .issue-feature__cover {
  transform: scale(1.025);
}

.issue-feature__details {
  position: relative;
  min-width: 0;
  padding-bottom: 0.2rem;
}

.issue-feature__details > p,
.issue-feature__details > span {
  display: block;
  color: rgb(246 247 251 / 82%);
  font-size: var(--text-small-size);
}

.issue-feature__details h2 {
  margin-top: 0.15rem;
  color: #fff;
  font-size: clamp(1.25rem, 2vw, 1.75rem);
  line-height: 1.2;
  overflow-wrap: anywhere;
}

.issue-feature__link {
  display: inline-flex;
  width: 2.75rem;
  height: 2.75rem;
  align-items: center;
  justify-content: center;
  margin-top: var(--space-1);
  color: #f50088;
}

.issue-feature__link:focus-visible,
.issue-feature__cover-link:focus-visible {
  outline: 2px solid #f50088;
  outline-offset: 3px;
}

@media (max-width: 520px) {
  .issue-feature {
    grid-template-columns: minmax(6rem, 38%) minmax(0, 1fr);
    gap: var(--space-1);
  }
}

@media (prefers-reduced-motion: reduce) {
  .issue-feature__cover {
    transition: none;
  }
}
</style>
