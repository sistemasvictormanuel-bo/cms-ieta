<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'
import { ChevronDown, Check } from '@lucide/vue'

interface TabItem {
  value: string
  label: string
}

const props = defineProps<{
  items: TabItem[]
  modelValue: string
  label?: string
}>()

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

const { t } = useI18n()

const mobileOpen = ref(false)

const currentItem = computed(() => {
  return props.items.find((item) => item.value === props.modelValue) ?? props.items[0]
})

function selectTab(value: string) {
  emit('update:modelValue', value)
}

function toggleMobile() {
  mobileOpen.value = !mobileOpen.value
}

function onMobileSelect(value: string) {
  selectTab(value)
  mobileOpen.value = false
}

function moveFocus(event: KeyboardEvent, currentIndex: number) {
  if (props.items.length === 0) return

  const lastIndex = props.items.length - 1
  let nextIndex = currentIndex

  if (event.key === 'ArrowRight') nextIndex = (currentIndex + 1) % props.items.length
  else if (event.key === 'ArrowLeft')
    nextIndex = (currentIndex - 1 + props.items.length) % props.items.length
  else if (event.key === 'Home') nextIndex = 0
  else if (event.key === 'End') nextIndex = lastIndex
  else return

  event.preventDefault()
  const nextTab = props.items[nextIndex]
  if (!nextTab) return

  selectTab(nextTab.value)
  const tabs = (
    event.currentTarget as HTMLElement
  ).parentElement?.querySelectorAll<HTMLButtonElement>('[role="tab"]')
  tabs?.[nextIndex]?.focus()
}

function handleClickOutside(e: MouseEvent) {
  const target = e.target as HTMLElement
  if (!target.closest('.tab-nav-mobile')) {
    mobileOpen.value = false
  }
}

onMounted(() => document.addEventListener('click', handleClickOutside))
onUnmounted(() => document.removeEventListener('click', handleClickOutside))
</script>

<template>
  <div class="tab-nav-wrapper" :class="{ 'tab-nav-wrapper--open': mobileOpen }">
    <!-- Desktop Tab Bar (>= 769px) -->
    <div class="tab-nav-desktop">
      <div
        class="tab-nav"
        role="tablist"
        :aria-label="label || t('aria.tabs')"
      >
        <button
          v-for="(item, index) in items"
          :key="item.value"
          class="tab-nav__tab"
          :class="{ 'tab-nav__tab--active': item.value === modelValue }"
          type="button"
          role="tab"
          :aria-selected="item.value === modelValue"
          :tabindex="item.value === modelValue ? 0 : -1"
          @click="selectTab(item.value)"
          @keydown="moveFocus($event, index)"
        >
          {{ item.label }}
        </button>
      </div>
    </div>

    <!-- Mobile Dropdown Selector (< 769px) -->
    <div class="tab-nav-mobile">
      <div class="tab-nav-mobile__container">
        <button
          type="button"
          class="tab-nav-mobile__toggle"
          :aria-expanded="mobileOpen"
          aria-haspopup="listbox"
          @click="toggleMobile"
        >
          <span class="tab-nav-mobile__selected-label">{{ currentItem?.label || 'Seleccionar sección' }}</span>
          <ChevronDown
            :size="18"
            class="tab-nav-mobile__chevron"
            :class="{ 'tab-nav-mobile__chevron--open': mobileOpen }"
          />
        </button>

        <Transition name="dropdown-slide">
          <div v-if="mobileOpen" class="tab-nav-mobile__menu" role="listbox">
            <button
              v-for="item in items"
              :key="item.value"
              type="button"
              role="option"
              class="tab-nav-mobile__item"
              :class="{ 'tab-nav-mobile__item--active': item.value === modelValue }"
              :aria-selected="item.value === modelValue"
              @click="onMobileSelect(item.value)"
            >
              <span>{{ item.label }}</span>
              <Check v-if="item.value === modelValue" :size="16" class="tab-nav-mobile__check" />
            </button>
          </div>
        </Transition>
      </div>
    </div>
  </div>
</template>

<style scoped>
.tab-nav-wrapper {
  position: relative;
  width: 100%;
  z-index: 50;
}

.tab-nav-wrapper--open {
  z-index: 1000;
}

/* Desktop Horizontal Tab Bar */
.tab-nav-desktop {
  width: 100%;
}

.tab-nav {
  position: relative;
  display: flex;
  justify-content: center;
  gap: clamp(0.75rem, 3vw, 2.5rem);
  width: 100%;
  border-bottom: 1px solid #eaedf6;
}

.tab-nav__tab {
  position: relative;
  display: inline-flex;
  align-items: center;
  min-height: 3.25rem;
  padding-block: 0.5rem;
  padding-inline: 0.5rem;
  border: 0;
  background: transparent;
  color: #1b144b;
  font: inherit;
  font-size: 0.95rem;
  font-weight: 700;
  cursor: pointer;
  white-space: nowrap;
  transition: color 0.18s ease;
}

/* Bottom active line matching Varianza */
.tab-nav__tab::after {
  content: '';
  position: absolute;
  right: 0;
  bottom: -1px;
  left: 0;
  height: 3px;
  background: var(--color-rosa-carmin);
  transform: scaleX(0);
  transform-origin: left;
  transition: transform 0.22s ease;
}

.tab-nav__tab:hover,
.tab-nav__tab--active,
.tab-nav__tab[aria-selected='true'] {
  color: var(--color-rosa-carmin);
}

.tab-nav__tab--active::after,
.tab-nav__tab[aria-selected='true']::after {
  transform: scaleX(1);
}

.tab-nav__tab:focus-visible {
  outline: 2px solid var(--color-rosa-carmin);
  outline-offset: -3px;
}

/* Mobile Dropdown Section */
.tab-nav-mobile {
  position: relative;
  z-index: 60;
  display: none;
  width: 100%;
  padding-block: 0.5rem;
}

.tab-nav-mobile__container {
  position: relative;
  width: 100%;
  z-index: 70;
}

.tab-nav-mobile__toggle {
  display: flex;
  width: 100%;
  align-items: center;
  justify-content: space-between;
  min-height: 3.1rem;
  padding: 0.75rem 1.15rem;
  border-radius: 10px;
  border: 1px solid #d4d8e5;
  background: #ffffff;
  color: #1b144b;
  font: inherit;
  font-size: 0.95rem;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 2px 8px rgba(27, 20, 75, 0.05);
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.tab-nav-mobile__toggle:focus-visible,
.tab-nav-mobile__toggle:hover {
  border-color: var(--color-rosa-carmin);
}

.tab-nav-mobile__chevron {
  color: #64748b;
  transition: transform 0.22s ease;
}

.tab-nav-mobile__chevron--open {
  transform: rotate(180deg);
  color: var(--color-rosa-carmin);
}

.tab-nav-mobile__menu {
  position: absolute;
  top: calc(100% + 6px);
  left: 0;
  right: 0;
  z-index: 1000;
  display: flex;
  flex-direction: column;
  background: #ffffff;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 16px 36px rgba(27, 20, 75, 0.22);
  overflow: hidden;
}

.tab-nav-mobile__item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  padding: 0.85rem 1.15rem;
  border: 0;
  border-bottom: 1px solid #f1f3f9;
  background: transparent;
  color: #1b144b;
  font: inherit;
  font-size: 0.92rem;
  font-weight: 600;
  text-align: left;
  cursor: pointer;
  transition: background-color 0.15s ease, color 0.15s ease;
}

.tab-nav-mobile__item:last-child {
  border-bottom: 0;
}

.tab-nav-mobile__item:hover {
  background: #f8f9fe;
  color: var(--color-rosa-carmin);
}

.tab-nav-mobile__item--active {
  background: rgba(243, 0, 96, 0.06);
  color: var(--color-rosa-carmin);
  font-weight: 800;
}

.tab-nav-mobile__check {
  color: var(--color-rosa-carmin);
}

/* Dropdown Animation */
.dropdown-slide-enter-active,
.dropdown-slide-leave-active {
  transition: opacity 0.18s ease, transform 0.18s ease;
}

.dropdown-slide-enter-from,
.dropdown-slide-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}

/* Responsive Breakpoints */
@media (max-width: 1024px) {
  .tab-nav {
    gap: clamp(0.5rem, 1.8vw, 1.25rem);
  }

  .tab-nav__tab {
    min-height: 3rem;
    font-size: 0.9rem;
    padding-inline: 0.35rem;
  }
}

@media (max-width: 768px) {
  .tab-nav-desktop {
    display: none;
  }

  .tab-nav-mobile {
    display: block;
  }
}

@media (max-width: 480px) {
  .tab-nav-mobile {
    padding-block: 0.25rem;
  }

  .tab-nav-mobile__toggle {
    min-height: 2.85rem;
    padding: 0.65rem 0.9rem;
    font-size: 0.88rem;
    border-radius: 8px;
  }

  .tab-nav-mobile__selected-label {
    font-size: 0.88rem;
  }

  .tab-nav-mobile__menu {
    top: calc(100% + 4px);
    border-radius: 8px;
  }

  .tab-nav-mobile__item {
    padding: 0.75rem 0.9rem;
    font-size: 0.86rem;
    min-height: 42px;
  }
}

@media (prefers-reduced-motion: reduce) {
  .tab-nav__tab::after,
  .dropdown-slide-enter-active,
  .dropdown-slide-leave-active {
    transition: none;
  }
}
</style>
