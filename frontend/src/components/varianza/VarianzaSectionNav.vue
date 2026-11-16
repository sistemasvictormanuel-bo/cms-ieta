<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted } from 'vue'
import { RouterLink } from 'vue-router'
import { ChevronDown, Check } from '@lucide/vue'

const props = defineProps<{
  active: string
  items: Array<{ value: string; label: string; to: string }>
}>()

const mobileOpen = ref(false)

const currentItem = computed(() => {
  return props.items.find((item) => item.value === props.active) ?? props.items[0]
})

function toggleMobile() {
  mobileOpen.value = !mobileOpen.value
}

function handleClickOutside(e: MouseEvent) {
  const target = e.target as HTMLElement
  if (!target.closest('.varianza-nav-mobile')) {
    mobileOpen.value = false
  }
}

onMounted(() => document.addEventListener('click', handleClickOutside))
onUnmounted(() => document.removeEventListener('click', handleClickOutside))
</script>

<template>
  <nav class="varianza-nav" :class="{ 'varianza-nav--open': mobileOpen }" aria-label="Secciones de la Revista Varianza">
    <!-- Desktop / Tablet Horizontal Bar (>= 769px) -->
    <div class="varianza-nav__inner varianza-nav-desktop">
      <RouterLink
        v-for="item in items"
        :key="item.value"
        :to="item.to"
        class="varianza-nav__link"
        :class="{ 'varianza-nav__link--active': active === item.value }"
        :aria-current="active === item.value ? 'page' : undefined"
      >
        {{ item.label }}
      </RouterLink>
    </div>

    <!-- Mobile Dropdown Selector (< 769px) -->
    <div class="varianza-nav-mobile container">
      <div class="varianza-mobile__container">
        <button
          type="button"
          class="varianza-mobile__toggle"
          :aria-expanded="mobileOpen"
          aria-haspopup="listbox"
          @click="toggleMobile"
        >
          <span class="varianza-mobile__selected">{{ currentItem?.label || 'Sección de la revista' }}</span>
          <ChevronDown
            :size="18"
            class="varianza-mobile__chevron"
            :class="{ 'varianza-mobile__chevron--open': mobileOpen }"
          />
        </button>

        <Transition name="dropdown-slide">
          <div v-if="mobileOpen" class="varianza-mobile__menu" role="listbox">
            <RouterLink
              v-for="item in items"
              :key="item.value"
              :to="item.to"
              class="varianza-mobile__item"
              :class="{ 'varianza-mobile__item--active': active === item.value }"
              @click="mobileOpen = false"
            >
              <span>{{ item.label }}</span>
              <Check v-if="active === item.value" :size="16" class="varianza-mobile__check" />
            </RouterLink>
          </div>
        </Transition>
      </div>
    </div>
  </nav>
</template>

<style scoped>
.varianza-nav {
  position: relative;
  z-index: 40;
  background: var(--color-superficie);
  border-bottom: 1px solid #eaedf6;
}

.varianza-nav--open {
  z-index: 1000;
}

/* Desktop Navigation */
.varianza-nav__inner {
  display: flex;
  width: min(100%, 100rem);
  min-height: 3.5rem;
  align-items: stretch;
  justify-content: center;
  gap: clamp(0.75rem, 3vw, 2.5rem);
  margin-inline: auto;
  padding-inline: var(--space-3);
}

.varianza-nav__link {
  position: relative;
  display: inline-flex;
  flex: 0 0 auto;
  align-items: center;
  color: #1b144b;
  font-size: 0.95rem;
  font-weight: 700;
  text-decoration: none;
  white-space: nowrap;
  padding-block: 0.5rem;
  padding-inline: 0.35rem;
  transition: color 0.18s ease;
}

.varianza-nav__link::after {
  position: absolute;
  right: 0;
  bottom: 0;
  left: 0;
  height: 3px;
  background: #f50088;
  content: '';
  transform: scaleX(0);
  transform-origin: left;
  transition: transform 0.22s ease;
}

.varianza-nav__link:hover,
.varianza-nav__link--active {
  color: #f50088;
}

.varianza-nav__link--active::after {
  transform: scaleX(1);
}

.varianza-nav__link:focus-visible {
  outline: 2px solid #6c81c7;
  outline-offset: -4px;
}

/* Mobile Dropdown */
.varianza-nav-mobile {
  display: none;
  padding-block: 0.65rem;
}

.varianza-mobile__container {
  position: relative;
  width: 100%;
}

.varianza-mobile__toggle {
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

.varianza-mobile__toggle:focus-visible,
.varianza-mobile__toggle:hover {
  border-color: #f50088;
}

.varianza-mobile__chevron {
  color: #64748b;
  transition: transform 0.22s ease;
}

.varianza-mobile__chevron--open {
  transform: rotate(180deg);
  color: #f50088;
}

.varianza-mobile__menu {
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

.varianza-mobile__item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  padding: 0.85rem 1.15rem;
  border-bottom: 1px solid #f1f3f9;
  background: transparent;
  color: #1b144b;
  font-size: 0.92rem;
  font-weight: 600;
  text-decoration: none;
  transition: background-color 0.15s ease, color 0.15s ease;
}

.varianza-mobile__item:last-child {
  border-bottom: 0;
}

.varianza-mobile__item:hover {
  background: #fdf2f7;
  color: #f50088;
}

.varianza-mobile__item--active {
  background: rgba(245, 0, 136, 0.06);
  color: #f50088;
  font-weight: 800;
}

.varianza-mobile__check {
  color: #f50088;
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
  .varianza-nav__inner {
    min-height: 3.15rem;
    gap: clamp(0.4rem, 1.6vw, 1rem);
    padding-inline: var(--space-2);
  }

  .varianza-nav__link {
    font-size: 0.88rem;
    padding-inline: 0.25rem;
  }
}

@media (max-width: 768px) {
  .varianza-nav-desktop {
    display: none;
  }

  .varianza-nav-mobile {
    display: block;
    padding-block: 0.65rem;
  }
}

@media (max-width: 480px) {
  .varianza-nav-mobile {
    padding-block: 0.4rem;
  }

  .varianza-mobile__toggle {
    min-height: 2.85rem;
    padding: 0.65rem 0.9rem;
    font-size: 0.88rem;
    border-radius: 8px;
  }

  .varianza-mobile__selected {
    font-size: 0.88rem;
  }

  .varianza-mobile__menu {
    top: calc(100% + 4px);
    border-radius: 8px;
  }

  .varianza-mobile__item {
    padding: 0.75rem 0.9rem;
    font-size: 0.86rem;
    min-height: 42px;
  }
}

@media (prefers-reduced-motion: reduce) {
  .varianza-nav__link::after,
  .dropdown-slide-enter-active,
  .dropdown-slide-leave-active {
    transition: none;
  }
}
</style>
