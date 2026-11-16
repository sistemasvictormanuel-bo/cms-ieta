<script setup lang="ts">
import { useI18n } from 'vue-i18n'

withDefaults(
  defineProps<{
    title?: string
    description?: string
    actionLabel?: string
  }>(),
  {
    title: undefined,
    description: undefined,
    actionLabel: undefined,
  },
)

const emit = defineEmits<{
  action: []
}>()

const { t } = useI18n()
</script>

<template>
  <section class="empty-state" aria-live="polite">
    <span class="empty-state__icon" aria-hidden="true"></span>
    <h2 class="empty-state__title">{{ title || t('common.noResults') }}</h2>
    <p v-if="description" class="empty-state__description">{{ description }}</p>
    <button v-if="actionLabel" class="empty-state__action" type="button" @click="emit('action')">
      {{ actionLabel }}
    </button>
  </section>
</template>

<style scoped>
.empty-state {
  display: grid;
  justify-items: center;
  gap: var(--space-2);
  padding: var(--space-8) var(--space-3);
  text-align: center;
}

.empty-state__icon {
  position: relative;
  width: 2rem;
  height: 2rem;
  border: 2px solid var(--color-resaltador);
  border-radius: 50%;
}

.empty-state__icon::after {
  position: absolute;
  right: -0.6rem;
  bottom: -0.25rem;
  width: 0.9rem;
  height: 2px;
  background: var(--color-resaltador);
  content: '';
  transform: rotate(45deg);
  transform-origin: left center;
}

.empty-state__title {
  font-size: var(--text-h5-size);
  font-weight: var(--text-h5-weight);
}

.empty-state__description {
  max-width: 48ch;
  color: var(--color-violeta);
}

.empty-state__action {
  min-height: 2.75rem;
  margin-top: var(--space-1);
  padding: var(--space-1) var(--space-2);
  border: 1px solid var(--color-indigo);
  border-radius: var(--radius-sm);
  background: transparent;
  color: var(--color-indigo);
  font: inherit;
  font-weight: 600;
  cursor: pointer;
}

.empty-state__action:hover {
  background: var(--color-superficie);
}

.empty-state__action:focus-visible {
  outline: 3px solid var(--color-resaltador);
  outline-offset: 2px;
}
</style>
