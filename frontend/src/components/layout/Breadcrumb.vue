<script setup lang="ts">

/**
 * Breadcrumb.vue — Dinámico, nunca hardcodeado por vista.
 * Guía §5: "Generado dinámicamente a partir de la ruta activa,
 * nunca hardcodeado en cada vista".
 *
 * Lee meta.breadcrumb de la ruta activa (array de {label, to?}).
 * Si no existe meta.breadcrumb, muestra solo "Inicio".
 */
import { computed } from 'vue'
import { useRoute, RouterLink } from 'vue-router'

const props = defineProps<{ appendLabel?: string }>()

interface BreadcrumbItem {
  label: string
  to?: string
}

const route = useRoute()

const items = computed<BreadcrumbItem[]>(() => {
  const meta = route.meta?.breadcrumb as BreadcrumbItem[] | undefined
  if (meta && meta.length > 0) {
    return props.appendLabel ? [...meta, { label: props.appendLabel }] : meta
  }
  // Fallback: solo Inicio para la home
  return [{ label: 'Inicio' }]
})
</script>

<template>
  <nav class="breadcrumb" aria-label="Ruta de navegación">
    <ol class="breadcrumb__list">
      <li
        v-for="(item, index) in items"
        :key="index"
        class="breadcrumb__item"
      >
        <!-- Separador (no se muestra en el primer ítem) -->
        <span v-if="index > 0" class="breadcrumb__separator" aria-hidden="true">/</span>

        <!-- Ítem con enlace -->
        <RouterLink
          v-if="item.to"
          :to="item.to"
          class="breadcrumb__link"
        >
          {{ item.label }}
        </RouterLink>

        <!-- Ítem actual (sin enlace) -->
        <span
          v-else
          class="breadcrumb__current"
          aria-current="page"
        >
          {{ item.label }}
        </span>
      </li>
    </ol>
  </nav>
</template>

<style scoped>
.breadcrumb {
  /* Heredará color blanco del SectionHeaderBand padre */
}

.breadcrumb__list {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.25rem;
  list-style: none;
  padding: 0;
  margin: 0;
}

.breadcrumb__item {
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.breadcrumb__separator {
  color: rgba(255, 255, 255, 0.5);
  font-size: 0.75rem;
  font-weight: 400;
  user-select: none;
}

.breadcrumb__link {
  color: rgba(255, 255, 255, 0.75);
  text-decoration: none;
  font-size: 0.8125rem;
  font-weight: 400;
  transition: color 0.15s ease;
  border-radius: 2px;
}
.breadcrumb__link:hover {
  color: var(--color-blanco);
}
.breadcrumb__link:focus-visible {
  outline: 2px solid rgba(255, 255, 255, 0.6);
  outline-offset: 2px;
}

.breadcrumb__current {
  color: rgba(255, 255, 255, 0.95);
  font-size: 0.8125rem;
  font-weight: 500;
}
</style>

