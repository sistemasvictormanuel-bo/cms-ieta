<script setup lang="ts">
withDefaults(
  defineProps<{
    title: string
    id?: string
    tone?: 'light' | 'dark'
  }>(),
  { tone: 'light' },
)
</script>

<template>
  <section :id="id" class="editorial-statement" :class="`editorial-statement--${tone}`">
    <div class="container editorial-statement__inner">
      <div class="editorial-statement__heading">
        <p class="editorial-statement__eyebrow">Revista Varianza</p>
        <h2>{{ title }}</h2>
      </div>
      <div class="editorial-statement__body">
        <slot />
      </div>
    </div>
  </section>
</template>

<style scoped>
.editorial-statement {
  position: relative;
  border-bottom: 1px solid #aab3d0;
  background: #f5f5f8;
  color: #14161c;
}

.editorial-statement__inner {
  display: grid;
  grid-template-columns: minmax(12rem, 0.8fr) minmax(0, 1.2fr);
  gap: clamp(var(--space-3), 7vw, var(--space-8));
  padding-block: clamp(var(--space-4), 6vw, var(--space-8));
}

.editorial-statement__heading h2 {
  color: #202e71;
  font-size: clamp(1.75rem, 3vw, 2.5rem);
  font-weight: 500;
  line-height: 1.15;
}

.editorial-statement__eyebrow {
  margin-bottom: var(--space-1);
  color: #f50088;
  font-size: 0.6875rem;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.editorial-statement__body {
  max-width: 62ch;
  padding-top: 0.25rem;
  font-size: var(--text-body-size);
  line-height: 1.65;
}

.editorial-statement__body :deep(p + p) {
  margin-top: var(--space-2);
}

.editorial-statement__body :deep(a) {
  color: #f50088;
  text-decoration: underline;
  text-underline-offset: 0.18em;
}

.editorial-statement--dark {
  border-color: rgb(255 255 255 / 20%);
  background: #202e71;
  color: #f6f7fb;
}

.editorial-statement--dark .editorial-statement__heading h2 {
  color: #f50088;
}

@media (max-width: 680px) {
  .editorial-statement__inner {
    grid-template-columns: 1fr;
    gap: var(--space-2);
  }
}
</style>
