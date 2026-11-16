<script setup lang="ts">
import { useI18n } from 'vue-i18n'

withDefaults(
  defineProps<{
    loading?: boolean
    hasMore?: boolean
  }>(),
  {
    loading: false,
    hasMore: true,
  },
)

const emit = defineEmits<{
  load: []
}>()

const { t } = useI18n()
</script>

<template>
  <div v-if="hasMore" class="load-more">
    <button
      class="load-more__button"
      type="button"
      :disabled="loading"
      :aria-busy="loading"
      @click="emit('load')"
    >
      <span v-if="loading" class="load-more__spinner" aria-hidden="true"></span>
      {{ loading ? t('common.loading') : t('common.loadMore') }}
    </button>
  </div>
</template>

<style scoped>
.load-more {
  display: flex;
  justify-content: center;
  padding-block: var(--space-4);
}

.load-more__button {
  display: inline-flex;
  min-height: 2.75rem;
  align-items: center;
  justify-content: center;
  gap: var(--space-1);
  padding: var(--space-1) var(--space-3);
  border: 1px solid var(--color-indigo);
  border-radius: var(--radius-sm);
  background: var(--color-indigo);
  color: var(--color-blanco);
  font: inherit;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.15s ease;
}

.load-more__button:hover:not(:disabled) {
  background: var(--color-purpura);
}

.load-more__button:focus-visible {
  outline: 3px solid var(--color-resaltador);
  outline-offset: 2px;
}

.load-more__button:disabled {
  cursor: wait;
  opacity: 0.75;
}

.load-more__spinner {
  width: 1rem;
  height: 1rem;
  border: 2px solid currentColor;
  border-right-color: transparent;
  border-radius: 50%;
  animation: load-more-spin 0.7s linear infinite;
}

@keyframes load-more-spin {
  to {
    transform: rotate(360deg);
  }
}

@media (prefers-reduced-motion: reduce) {
  .load-more__button {
    transition: none;
  }

  .load-more__spinner {
    animation: none;
  }
}
</style>
