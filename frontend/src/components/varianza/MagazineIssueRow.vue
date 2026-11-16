<script setup lang="ts">
import { ArrowUpRight } from '@lucide/vue'
import { RouterLink } from 'vue-router'

defineProps<{
  number: number | string
  date: string
  title: string
  cover?: string
  coverAlt: string
  to: string
  articleCount?: number
  pages?: number
}>()
</script>

<template>
  <article class="issue-row">
    <RouterLink class="issue-row__cover-link" :to="to" :aria-label="`Abrir ${title}`">
      <img v-if="cover" :src="cover" :alt="coverAlt" loading="lazy" />
      <span v-else class="issue-row__cover-placeholder" aria-hidden="true">{{ number }}</span>
    </RouterLink>
    <div class="issue-row__body">
      <p class="issue-row__date">{{ date }}</p>
      <h3><RouterLink :to="to">{{ title }}</RouterLink></h3>
      <dl class="issue-row__meta">
        <div v-if="articleCount !== undefined">
          <dt>Artículos</dt>
          <dd>{{ articleCount }}</dd>
        </div>
        <div v-if="pages !== undefined">
          <dt>Páginas</dt>
          <dd>{{ pages }}</dd>
        </div>
      </dl>
    </div>
    <RouterLink class="issue-row__open" :to="to" :aria-label="`Consultar ${title}`">
      <ArrowUpRight :size="19" aria-hidden="true" />
    </RouterLink>
  </article>
</template>

<style scoped>
.issue-row {
  position: relative;
  display: grid;
  grid-template-columns: 6rem minmax(0, 1fr) 2.5rem;
  gap: var(--space-2);
  align-items: center;
  min-width: 0;
  padding: var(--space-1);
  border-radius: var(--radius-sm);
  background: #fff;
  box-shadow: 0 2px 8px rgb(20 22 28 / 9%);
}

.issue-row__cover-link {
  display: block;
  overflow: hidden;
  background: #e7e8ee;
}

.issue-row__cover-link img {
  width: 100%;
  aspect-ratio: 0.72;
  object-fit: cover;
}

.issue-row__cover-placeholder {
  display: grid;
  aspect-ratio: 0.72;
  place-items: center;
  background: linear-gradient(155deg, #f50088, #202e71 68%);
  color: #fff;
  font-size: 1.25rem;
  font-weight: 700;
}

.issue-row__body {
  min-width: 0;
}

.issue-row__date,
.issue-row__meta {
  color: #4d5060;
  font-size: 0.75rem;
}

.issue-row h3 {
  margin-block: 0.15rem 0.4rem;
  color: #202e71;
  font-size: 1rem;
  line-height: 1.3;
  overflow-wrap: anywhere;
}

.issue-row h3 a {
  color: inherit;
}

.issue-row__meta {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem 1rem;
}

.issue-row__meta div {
  display: flex;
  gap: 0.25rem;
}

.issue-row__meta dd {
  font-weight: 700;
}

.issue-row__open {
  display: grid;
  width: 2.5rem;
  height: 2.5rem;
  place-items: center;
  color: #f50088;
}

.issue-row__open:focus-visible,
.issue-row__cover-link:focus-visible {
  outline: 2px solid #f50088;
  outline-offset: 2px;
}

@media (max-width: 480px) {
  .issue-row {
    grid-template-columns: 4.5rem minmax(0, 1fr) 2rem;
    gap: var(--space-1);
  }
}
</style>
