<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { RotateCcw, Search, SlidersHorizontal } from '@lucide/vue'
import EmptyState from './EmptyState.vue'

interface FilterOption {
  value: string
  label: string
}

interface FilterDefinition {
  key: string
  label: string
  options: FilterOption[]
}

interface SortOption {
  value: string
  label: string
}

const props = withDefaults(
  defineProps<{
    modelValue?: string
    filters?: FilterDefinition[]
    filterValues?: Record<string, string>
    noResults?: boolean
    resultsCount?: number
    sortOptions?: SortOption[]
    sortValue?: string
    placeholder?: string
    showFilterByLabel?: boolean
  }>(),
  {
    modelValue: '',
    filters: () => [],
    filterValues: () => ({}),
    noResults: false,
    placeholder: '',
    showFilterByLabel: true,
  },
)

const emit = defineEmits<{
  'update:modelValue': [value: string]
  'update:filterValues': [values: Record<string, string>]
  'update:sortValue': [value: string]
  clear: []
}>()

const { t } = useI18n()
const localSearch = ref(props.modelValue)
let searchTimer: ReturnType<typeof setTimeout> | undefined

watch(
  () => props.modelValue,
  (value) => {
    clearTimeout(searchTimer)
    localSearch.value = value
  },
)

function handleSearchInput() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => emit('update:modelValue', localSearch.value), 300)
}

function updateFilter(key: string, value: string) {
  emit('update:filterValues', { ...props.filterValues, [key]: value })
}

const hasActiveFilters = computed(() => {
  if (localSearch.value.trim().length > 0) return true
  return Object.values(props.filterValues).some((val) => Boolean(val))
})

function clearFilters() {
  clearTimeout(searchTimer)
  localSearch.value = ''
  emit('update:modelValue', '')
  emit(
    'update:filterValues',
    Object.fromEntries(props.filters.map((filter) => [filter.key, ''])),
  )
  emit('clear')
}

onBeforeUnmount(() => clearTimeout(searchTimer))
</script>

<template>
  <div class="search-filter-bar">
    <div class="people-filter-bar">
      <!-- Left side: Filter By + Selects + Search Input + Clear Button -->
      <div class="people-filter-bar__left">
        <div v-if="showFilterByLabel" class="filter-by-label">
          <SlidersHorizontal :size="16" aria-hidden="true" />
          <span>Filtrar por</span>
        </div>

        <!-- Dynamic Dropdowns for Each Filter Definition -->
        <div
          v-for="filter in filters"
          :key="filter.key"
          class="filter-select-wrapper"
        >
          <select
            :value="filterValues[filter.key] || ''"
            :aria-label="filter.label"
            class="filter-select-input"
            @change="updateFilter(filter.key, ($event.target as HTMLSelectElement).value)"
          >
            <option value="">{{ filter.label }}</option>
            <option
              v-for="option in filter.options"
              :key="option.value"
              :value="option.value"
            >
              {{ option.label }}
            </option>
          </select>
        </div>

        <!-- Search Input -->
        <div class="filter-search-wrapper">
          <Search :size="15" class="filter-search-icon" aria-hidden="true" />
          <input
            v-model="localSearch"
            type="search"
            :placeholder="placeholder || t('common.search') + '...'"
            class="filter-search-input"
            :aria-label="placeholder || t('common.search')"
            @input="handleSearchInput"
          />
        </div>

        <!-- Reset Button -->
        <button
          v-if="hasActiveFilters"
          type="button"
          class="filter-clear-btn"
          title="Restablecer filtros"
          @click="clearFilters"
        >
          <RotateCcw :size="13" aria-hidden="true" />
          <span>Limpiar</span>
        </button>
      </div>

      <!-- Right side: Results Count + Sort Dropdown -->
      <div v-if="resultsCount !== undefined || (sortOptions && sortOptions.length > 0)" class="people-filter-bar__right">
        <span v-if="resultsCount !== undefined" class="results-count">{{ resultsCount }} Resultados</span>

        <div v-if="sortOptions && sortOptions.length > 0" class="sort-select-wrapper">
          <label class="sort-label">Ordenar:</label>
          <select
            :value="sortValue"
            class="sort-select-input"
            aria-label="Ordenar resultados"
            @change="emit('update:sortValue', ($event.target as HTMLSelectElement).value)"
          >
            <option v-for="opt in sortOptions" :key="opt.value" :value="opt.value">
              {{ opt.label }}
            </option>
          </select>
        </div>
      </div>
    </div>

    <!-- Empty State below bar when no results match -->
    <EmptyState
      v-if="noResults"
      class="search-filter-bar__empty"
      title="No se encontraron coincidencias"
      description="Prueba con otros términos de búsqueda o limpia los filtros activos."
      :action-label="t('common.clearFilters')"
      @action="clearFilters"
    />
  </div>
</template>

<style scoped>
.search-filter-bar {
  display: block;
  width: 100%;
}

/* People Filter Bar (Banco de Datos Unified Style) */
.people-filter-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 1.25rem;
  padding: 0.85rem 0 1.25rem;
  border-bottom: 2px solid #111827;
  margin-bottom: 2rem;
}

.people-filter-bar__left {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.85rem;
}

.filter-by-label {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  font-size: 0.92rem;
  font-weight: 800;
  color: #111827;
  letter-spacing: -0.01em;
}

.filter-select-wrapper {
  position: relative;
  display: inline-flex;
}

.filter-select-input {
  appearance: none;
  background-color: #ffffff;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23374151' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 0.75rem center;
  padding: 0.5rem 2.2rem 0.5rem 0.85rem;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  font-size: 0.88rem;
  font-weight: 600;
  color: #374151;
  cursor: pointer;
  transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

.filter-select-input:focus {
  outline: 2px solid #35449f;
  outline-offset: 1px;
}

.filter-search-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.filter-search-icon {
  position: absolute;
  left: 0.75rem;
  color: #9ca3af;
  pointer-events: none;
}

.filter-search-input {
  padding: 0.5rem 0.85rem 0.5rem 2.2rem;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  font-size: 0.88rem;
  color: #111827;
  min-width: 15rem;
  transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

.filter-search-input:focus {
  outline: 2px solid #35449f;
  outline-offset: 1px;
}

.filter-clear-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.45rem 0.85rem;
  border: 1px dashed #d1d5db;
  border-radius: 4px;
  background: #f9fafb;
  color: #6b7280;
  font-size: 0.82rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s ease;
}

.filter-clear-btn:hover {
  background: #fee2e2;
  border-color: #ef4444;
  color: #b91c1c;
}

.people-filter-bar__right {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  margin-left: auto;
}

.results-count {
  font-size: 0.88rem;
  font-weight: 600;
  color: #4b5563;
}

.sort-select-wrapper {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.sort-label {
  font-size: 0.88rem;
  color: #6b7280;
  white-space: nowrap;
}

.sort-select-input {
  appearance: none;
  background-color: transparent;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23374151' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 0.25rem center;
  padding: 0.25rem 1.6rem 0.25rem 0.25rem;
  border: none;
  font-size: 0.88rem;
  font-weight: 700;
  color: #111827;
  cursor: pointer;
}

.sort-select-input:focus {
  outline: 2px solid #35449f;
  outline-offset: 1px;
}

.search-filter-bar__empty {
  padding-block: var(--space-6);
}

@media (max-width: 768px) {
  .people-filter-bar {
    flex-direction: column;
    align-items: stretch;
  }
  .people-filter-bar__left,
  .people-filter-bar__right {
    width: 100%;
    justify-content: space-between;
  }
  .filter-search-input {
    min-width: 100%;
    width: 100%;
  }
}
</style>
