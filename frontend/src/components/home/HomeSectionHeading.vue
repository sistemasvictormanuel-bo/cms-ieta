<script setup lang="ts">
import { RouterLink } from 'vue-router'

defineProps<{
  id?: string
  eyebrow?: string
  title: string
  linkLabel?: string
  linkTo?: string
}>()
</script>

<template>
  <header :id="id" class="home-section-heading">
    <div class="home-section-heading__copy">
      <p v-if="eyebrow" class="home-section-heading__eyebrow">{{ eyebrow }}</p>
      <h2>{{ title }}</h2>
    </div>
    <RouterLink v-if="linkLabel && linkTo" class="home-section-heading__link" :to="linkTo">
      {{ linkLabel }} <span aria-hidden="true">↗</span>
    </RouterLink>
  </header>
</template>

<style scoped>
.home-section-heading {
  display: flex;
  align-items: end;
  justify-content: space-between;
  gap: var(--space-3);
  margin-bottom: var(--space-3);
}

.home-section-heading__eyebrow {
  margin-bottom: var(--space-1);
  color: var(--color-violeta);
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.home-section-heading h2 {
  color: #202e71;
  font-size: var(--text-h4-size);
  line-height: var(--text-h4-lh);
}

.home-section-heading__link {
  display: inline-flex;
  flex: 0 0 auto;
  align-items: center;
  gap: 0.5rem;
  padding-bottom: 0.25rem;
  color: #3e2987;
  font-weight: 700;
}

.home-section-heading__link span {
  color: var(--color-rosa-carmin);
  font-size: 1.25rem;
}

.home-section-heading__link:focus-visible {
  outline: 2px solid var(--color-resaltador);
  outline-offset: 4px;
}

@media (max-width: 520px) {
  .home-section-heading {
    align-items: start;
  }

  .home-section-heading h2 {
    font-size: 1.7rem;
  }

  .home-section-heading__link {
    font-size: 0.8125rem;
  }
}
</style>
