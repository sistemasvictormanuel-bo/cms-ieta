<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { ChevronDown, Globe } from '@lucide/vue'
import { useI18n } from 'vue-i18n'
import logoIeta from '@/assets/images/logoieta_400.png'
import type { InstitutionConfigApiItem } from '@/api/types'
import { mediaUrl } from '@/api/response'
import apiClient from '@/services/api'

const props = defineProps<{ institutionConfig?: InstitutionConfigApiItem | null }>()
const route = useRoute()
const { locale, t } = useI18n()

const logoUrl = computed(() => mediaUrl(props.institutionConfig?.logo_principal_url) || logoIeta)
const institutionName = computed(
  () =>
    props.institutionConfig?.nombre_institucion || 'Instituto de Estadística Teórica y Aplicada',
)
const institutionShortName = computed(() => props.institutionConfig?.siglas || 'IETA')
const institutionSlogan = computed(
  () => props.institutionConfig?.slogan || 'Carrera de Estadística',
)

// Detect whether we are on Revista Varianza
const isVarianza = computed(() => route.path.startsWith('/revista-varianza'))

// Scroll compression
const isScrolled = ref(false)
const SCROLL_THRESHOLD = 80

function handleScroll() {
  isScrolled.value = window.scrollY > SCROLL_THRESHOLD
}

// Mobile Hamburger
const menuOpen = ref(false)
function toggleMenu() {
  menuOpen.value = !menuOpen.value
}
function closeMenu() {
  menuOpen.value = false
}

// Language menu dropdown toggle
const langOpen = ref(false)
function toggleLang() {
  langOpen.value = !langOpen.value
}
function selectLang(newLocale: string) {
  locale.value = newLocale
  apiClient.defaults.headers.common['Accept-Language'] = newLocale
  try {
    localStorage.setItem('ieta-locale', newLocale)
  } catch {
    // safe fallback
  }
  langOpen.value = false
}

function handleClickOutside(event: MouseEvent) {
  const target = event.target as HTMLElement
  if (!target.closest('.lang-dropdown-wrapper')) {
    langOpen.value = false
  }
}

onMounted(() => {
  window.addEventListener('scroll', handleScroll, { passive: true })
  document.addEventListener('click', handleClickOutside)
})
onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
  document.removeEventListener('click', handleClickOutside)
})

interface NavSubItem {
  label: string
  to: string
}

interface NavItem {
  label: string
  to: string
  children?: NavSubItem[]
}

const openMobileSubs = ref<Record<string, boolean>>({})

function toggleMobileSub(key: string) {
  openMobileSubs.value[key] = !openMobileSubs.value[key]
}

// Main nav items with tab preview dropdowns
const navItems = computed<NavItem[]>(() => [
  {
    label: t('nav.about'),
    to: '/acerca-de',
    children: [
      { label: 'Quiénes Somos', to: '/acerca-de' },
      { label: 'Misión y Visión', to: '/acerca-de/mision-vision' },
      { label: 'Objetivos', to: '/acerca-de/objetivos' },
      { label: 'Personal', to: '/acerca-de/personal' },
      { label: 'Reglamento Interno', to: '/acerca-de/reglamento' },
    ],
  },
  {
    label: t('nav.research'),
    to: '/investigacion/lineas-investigacion',
    children: [
      { label: 'Líneas de Investigación', to: '/investigacion/lineas-investigacion' },
      { label: 'Memorias de Investigación', to: '/investigacion/memorias' },
    ],
  },
  {
    label: t('nav.journal'),
    to: '/revista-varianza',
    children: [
      { label: 'Sobre la Revista', to: '/revista-varianza' },
      { label: 'Comité Editorial', to: '/revista-varianza/comite-editor' },
      { label: 'Autores Varianza', to: '/revista-varianza/autores' },
      { label: 'Todas las Ediciones', to: '/revista-varianza/ediciones' },
      { label: 'Instrucciones para Autores', to: '/revista-varianza/instrucciones-autores' },
    ],
  },
  {
    label: 'Cursos',
    to: '/cursos',
  },
  {
    label: t('nav.services'),
    to: '/servicios',
  },
  {
    label: t('nav.resources'),
    to: '/recursos',
    children: [
      { label: 'Banco de Datos', to: '/recursos/banco-datos' },
      { label: 'Revistas de Estadística', to: '/recursos/revistas-de-estadistica' },
    ],
  },
  {
    label: 'Galería',
    to: '/multimedia',
  },
  {
    label: t('nav.news'),
    to: '/noticias',
  },
])

function isActive(to: string): boolean {
  if (to === '/') return route.path === '/'
  return route.path.startsWith(to)
}
</script>

<template>
  <header
    class="app-header"
    :class="{
      'app-header--scrolled': isScrolled,
      'app-header--menu-open': menuOpen,
      'app-header--varianza': isVarianza,
      'app-header--gradient': !isVarianza,
    }"
  >
    <div class="app-header__container header-inner">
      <!-- ── Brand / Logo with Curved Background ─────────────── -->
      <RouterLink to="/" class="header-brand" @click="closeMenu" aria-label="IETA — Ir al inicio">
        <span class="header-brand__curve" aria-hidden="true">
          <svg
            viewBox="0 0 120 56"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            preserveAspectRatio="none"
          >
            <path d="M0 56 H100 Q120 56 120 16 V0 H0 Z" fill="white" />
          </svg>
        </span>
        <img class="header-brand__logo" :src="logoUrl" alt="" />
        <span class="header-brand__text">
          <span class="header-brand__full">
            <span class="header-brand__full-name">{{ institutionName }}</span>
            <span class="header-brand__short-name">{{ institutionShortName }}</span>
          </span>
          <span class="header-brand__career">Carrera de Estadística</span>
        </span>
      </RouterLink>

      <!-- ── Right Column: Utilities Top + Navigation Bottom ─── -->
      <div class="header-right-col">
        <!-- Top Utility Row: Ingresar, Contacto, Idioma -->
        <div class="header-utility-row">
          <a href="/login" class="utility-link">{{ t('nav.login') }}</a>
          <RouterLink
            to="/contacto"
            class="utility-link"
            :class="{ 'utility-link--active': route.path === '/contacto' }"
          >
            {{ t('nav.contact') }}
          </RouterLink>

          <!-- Language Selector Pill -->
          <div class="lang-dropdown-wrapper">
            <button
              type="button"
              class="lang-pill"
              :aria-expanded="langOpen"
              aria-label="Seleccionar idioma"
              @click="toggleLang"
            >
              <span>{{ locale.toUpperCase() }}</span>
              <ChevronDown
                :size="13"
                class="lang-pill__chevron"
                :class="{ 'lang-pill__chevron--open': langOpen }"
              />
            </button>
            <div v-if="langOpen" class="lang-dropdown">
              <button
                type="button"
                class="lang-opt"
                :class="{ 'lang-opt--active': locale === 'es' }"
                @click="selectLang('es')"
              >
                Español (ES)
              </button>
              <button
                type="button"
                class="lang-opt"
                :class="{ 'lang-opt--active': locale === 'en' }"
                @click="selectLang('en')"
              >
                English (EN)
              </button>
            </div>
          </div>
        </div>

        <!-- Main Navigation Bar with Tab Dropdowns -->
        <nav class="header-nav" aria-label="Navegación principal">
          <div
            v-for="item in navItems"
            :key="item.to"
            class="header-nav__item"
            :class="{ 'header-nav__item--has-children': Boolean(item.children) }"
          >
            <RouterLink
              :to="item.to"
              class="header-nav__link"
              :class="{ 'header-nav__link--active': isActive(item.to) }"
            >
              <span>{{ item.label }}</span>
              <ChevronDown v-if="item.children" :size="12" class="nav-chevron" aria-hidden="true" />
            </RouterLink>

            <!-- Dropdown Menu -->
            <div v-if="item.children" class="header-nav__dropdown" role="menu">
              <RouterLink
                v-for="sub in item.children"
                :key="sub.to"
                :to="sub.to"
                class="dropdown-sublink"
                role="menuitem"
              >
                <span>{{ sub.label }}</span>
              </RouterLink>
            </div>
          </div>
        </nav>
      </div>

      <!-- ── Hamburger Button (Mobile) ────────────────────────── -->
      <button
        class="header-hamburger"
        :class="{ 'header-hamburger--open': menuOpen }"
        :aria-label="menuOpen ? 'Cerrar menú' : 'Abrir menú'"
        :aria-expanded="menuOpen"
        aria-controls="mobile-nav"
        @click="toggleMenu"
      >
        <span class="header-hamburger__bar" />
        <span class="header-hamburger__bar" />
        <span class="header-hamburger__bar" />
      </button>
    </div>

    <!-- ── Mobile Menu ─────────────────────────────────────────── -->
    <Transition name="mobile-nav">
      <nav
        v-if="menuOpen"
        id="mobile-nav"
        class="header-mobile-nav"
        aria-label="Navegación principal (móvil)"
      >
        <div class="mobile-nav__utility-row">
          <RouterLink to="/contacto" class="mobile-utility-link" @click="closeMenu">
            {{ t('nav.contact') }}
          </RouterLink>
          <button
            type="button"
            class="lang-pill"
            @click="selectLang(locale === 'es' ? 'en' : 'es')"
          >
            <span>{{ locale === 'es' ? 'EN (English)' : 'ES (Español)' }}</span>
          </button>
        </div>

        <div v-for="item in navItems" :key="item.to" class="mobile-nav-group">
          <div class="mobile-nav-main-row">
            <RouterLink
              :to="item.to"
              class="header-mobile-nav__link"
              :class="{ 'header-mobile-nav__link--active': isActive(item.to) }"
              @click="closeMenu"
            >
              {{ item.label }}
            </RouterLink>
            <button
              v-if="item.children"
              type="button"
              class="mobile-subnav-toggle"
              :aria-label="`Ver subsecciones de ${item.label}`"
              @click="toggleMobileSub(item.to)"
            >
              <ChevronDown
                :size="16"
                class="mobile-toggle-chevron"
                :class="{ 'mobile-toggle-chevron--open': openMobileSubs[item.to] }"
              />
            </button>
          </div>

          <!-- Mobile Submenu -->
          <div v-if="item.children && openMobileSubs[item.to]" class="mobile-submenu">
            <RouterLink
              v-for="sub in item.children"
              :key="sub.to"
              :to="sub.to"
              class="mobile-submenu__link"
              @click="closeMenu"
            >
              {{ sub.label }}
            </RouterLink>
          </div>
        </div>

        <a href="/login" class="header-mobile-nav__ingresar" @click="closeMenu">
          {{ t('nav.login') }}
        </a>
      </nav>
    </Transition>
  </header>
</template>

<style scoped>
/* Base Header */
.app-header {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 200;
  height: 5.75rem;
  transition:
    height 0.25s ease,
    background 0.25s ease,
    box-shadow 0.25s ease;
}

/* Gradient background on standard pages */
.app-header--gradient {
  background: linear-gradient(90deg, #1b144b 0%, #2b1a6c 50%, #3e2689 100%);
  color: var(--color-blanco);
}

/* Revista Varianza Header style (White background with dark blue nav) */
.app-header--varianza {
  background: linear-gradient(90deg, #1b144b 0%, #2b1a6c 50%, #3e2689 100%);
  color: var(--color-blanco);
}

.app-header--scrolled {
  height: 5rem;
  box-shadow: 0 4px 20px rgba(20, 22, 28, 0.18);
}

.app-header__container {
  width: 100%;
  max-width: var(--header-max-width, 1920px);
  margin-inline: auto;
  padding-inline: var(--space-4, 2rem);
  box-sizing: border-box;
}

@media (max-width: 768px) {
  .app-header__container {
    padding-inline: var(--space-2, 1rem);
  }
}

.header-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 100%;
  position: relative;
}

/* Brand */
.header-brand {
  position: relative;
  z-index: 1;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.5rem 1.25rem 0.5rem 0.5rem;
  text-decoration: none;
  flex-shrink: 0;
}

.header-brand__curve {
  position: absolute;
  top: -10px;
  left: -300px;
  right: -25px;
  bottom: -15px;
  z-index: -1;
  pointer-events: none;
}

.app-header--varianza .header-brand__curve {
  display: block;
}

.header-brand__curve svg {
  width: 100%;
  height: 100%;
}

.header-brand__logo {
  width: 3.4rem;
  height: 3.4rem;
  object-fit: contain;
  flex-shrink: 0;
}

.header-brand__text {
  display: flex;
  flex-direction: column;
  gap: 0.1rem;
}

.header-brand__full-name {
  color: #202e71;
  font-size: 0.85rem;
  font-weight: 700;
  line-height: 1.2;
}

.header-brand__short-name {
  display: none;
}

.header-brand__career {
  color: #636b85;
  font-size: 0.75rem;
  font-weight: 500;
}

/* Right Column */
.header-right-col {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.35rem;
}

/* Utility Row (Top) */
.header-utility-row {
  display: flex;
  align-items: center;
  gap: 1.25rem;
  font-size: 0.78rem;
  font-weight: 600;
}

.utility-link {
  color: rgba(255, 255, 255, 0.85);
  text-decoration: none;
  transition: color 0.15s ease;
}


.utility-link:hover,
.utility-link--active {
  color: #fff;
}


/* Language Pill Button */
.lang-dropdown-wrapper {
  position: relative;
}

.lang-pill {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  padding: 0.2rem 0.65rem;
  border-radius: 999px;
  background: var(--color-rosa-carmin);
  color: #fff;
  border: 0;
  font: inherit;
  font-size: 0.72rem;
  font-weight: 800;
  letter-spacing: 0.05em;
  cursor: pointer;
  box-shadow: 0 2px 8px rgba(243, 0, 96, 0.3);
  transition:
    transform 0.15s ease,
    background-color 0.15s ease;
}

.lang-pill:hover {
  transform: translateY(-1px);
  background: #db0056;
}

.lang-pill__chevron {
  transition: transform 0.2s ease;
}

.lang-pill__chevron--open {
  transform: rotate(180deg);
}

.lang-dropdown {
  position: absolute;
  top: calc(100% + 0.4rem);
  right: 0;
  display: flex;
  flex-direction: column;
  background: #fff;
  border: 1px solid #e1e3ea;
  border-radius: var(--radius-sm);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
  min-width: 9rem;
  overflow: hidden;
  z-index: 100;
}

.lang-opt {
  padding: 0.5rem 0.85rem;
  border: 0;
  background: transparent;
  color: #202e71;
  font: inherit;
  font-size: 0.8rem;
  text-align: left;
  cursor: pointer;
  transition: background-color 0.15s ease;
}

.lang-opt:hover,
.lang-opt--active {
  background: #f1f3fa;
  font-weight: 700;
  color: var(--color-rosa-carmin);
}

/* Nav Bar (Bottom) */
.header-nav {
  display: flex;
  align-items: center;
  gap: 1.15rem;
}

.header-nav__item {
  position: relative;
  display: flex;
  align-items: center;
}

.header-nav__link {
  position: relative;
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  font-size: 0.84rem;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.9);
  text-decoration: none;
  padding: 0.35rem 0.45rem;
  border-radius: var(--radius-sm);
  transition: all 0.18s ease;
}

.nav-chevron {
  opacity: 0.65;
  transition: transform 0.2s ease, opacity 0.2s ease;
}

.header-nav__item:hover .nav-chevron {
  transform: rotate(180deg);
  opacity: 1;
}

.header-nav__link:hover {
  color: #ffffff;
  background: rgba(255, 255, 255, 0.1);
}

.header-nav__link--active {
  color: #ffffff;
}

.app-header--varianza .header-nav__link--active {
  color: var(--color-rosa-carmin);
}

.header-nav__link--active::after {
  content: '';
  position: absolute;
  bottom: 0px;
  left: 0.45rem;
  right: 0.45rem;
  height: 2px;
  background: var(--color-rosa-carmin);
  border-radius: 999px;
}

/* Dropdown Flyout */
.header-nav__dropdown {
  position: absolute;
  top: calc(100% + 0.25rem);
  left: 0;
  min-width: 14rem;
  padding: 0.5rem 0;
  background: #ffffff;
  border-radius: 10px;
  box-shadow: 0 10px 30px -4px rgba(27, 20, 75, 0.25), 0 0 0 1px rgba(0, 0, 0, 0.06);
  opacity: 0;
  visibility: hidden;
  transform: translateY(8px);
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  z-index: 120;
  display: flex;
  flex-direction: column;
}

.header-nav__item:hover .header-nav__dropdown,
.header-nav__item:focus-within .header-nav__dropdown {
  opacity: 1;
  visibility: visible;
  transform: translateY(2px);
}

.dropdown-sublink {
  display: flex;
  align-items: center;
  padding: 0.6rem 1.15rem;
  font-size: 0.83rem;
  font-weight: 600;
  color: #1b144b;
  text-decoration: none;
  transition: all 0.15s ease;
  white-space: nowrap;
}

.dropdown-sublink:hover {
  background: #fdf2f8;
  color: #f30060;
  padding-left: 1.35rem;
}

/* Hamburger */
.header-hamburger {
  display: none;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  gap: 5px;
  width: 2.75rem;
  height: 2.75rem;
  padding: 0;
  border: 0;
  background: transparent;
  cursor: pointer;
  z-index: 10;
}

.header-hamburger__bar {
  display: block;
  width: 24px;
  height: 2.5px;
  background: #ffffff;
  border-radius: 2px;
  transition:
    transform 0.22s ease,
    opacity 0.22s ease;
}

.header-hamburger--open .header-hamburger__bar:nth-child(1) {
  transform: translateY(7.5px) rotate(45deg);
}
.header-hamburger--open .header-hamburger__bar:nth-child(2) {
  opacity: 0;
}
.header-hamburger--open .header-hamburger__bar:nth-child(3) {
  transform: translateY(-7.5px) rotate(-45deg);
}

/* Mobile Nav Drawer */
.header-mobile-nav {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: #1b144b;
  border-bottom: 3px solid var(--color-rosa-carmin);
  padding: 1.25rem 1.5rem 1.75rem;
  display: flex;
  flex-direction: column;
  gap: 0.65rem;
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.45);
  max-height: calc(100vh - 4.75rem);
  overflow-y: auto;
  z-index: 199;
}

.mobile-nav__utility-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-bottom: 0.75rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.12);
  margin-bottom: 0.5rem;
}

.mobile-utility-link {
  color: #fff;
  font-weight: 700;
  text-decoration: none;
}

.header-mobile-nav__link {
  color: rgba(255, 255, 255, 0.9);
  text-decoration: none;
  font-size: 1.05rem;
  font-weight: 600;
  padding: 0.45rem 0.25rem;
  transition: color 0.15s ease, padding-left 0.15s ease;
  flex: 1;
}

.header-mobile-nav__link:hover,
.header-mobile-nav__link--active {
  color: var(--color-rosa-carmin);
  padding-left: 0.5rem;
}

.mobile-nav-group {
  display: flex;
  flex-direction: column;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
  padding-bottom: 0.25rem;
}

.mobile-nav-main-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.mobile-subnav-toggle {
  border: none;
  background: transparent;
  color: #ffffff;
  padding: 0.5rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.mobile-toggle-chevron {
  transition: transform 0.2s ease;
}

.mobile-toggle-chevron--open {
  transform: rotate(180deg);
  color: var(--color-rosa-carmin);
}

.mobile-submenu {
  display: flex;
  flex-direction: column;
  padding-left: 1rem;
  padding-block: 0.35rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: var(--radius-sm);
  margin-top: 0.25rem;
  gap: 0.25rem;
}

.mobile-submenu__link {
  color: rgba(255, 255, 255, 0.75);
  font-size: 0.92rem;
  padding: 0.4rem 0.5rem;
  text-decoration: none;
  transition: color 0.15s ease;
}

.mobile-submenu__link:hover {
  color: var(--color-rosa-carmin);
}

.header-mobile-nav__ingresar {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 2.75rem;
  margin-top: 0.75rem;
  border-radius: var(--radius-sm);
  background: var(--color-rosa-carmin);
  color: #fff;
  font-weight: 700;
  text-decoration: none;
}

/* Responsive Breakpoints */
@media (max-width: 1024px) {
  .app-header {
    height: 5.75rem;
  }

  .app-header--scrolled {
    height: 5.25rem;
  }

  /* In mobile: curve only covers the logo, hide the text */
  .header-brand__text {
    display: none !important;
  }

  .header-brand {
    padding: 0.35rem 0.5rem 0.35rem 0.25rem;
    gap: 0;
  }

  .header-brand__logo {
    width: 3.5rem;
    height: 3.5rem;
  }


  .header-right-col {
    display: none;
  }

  .header-hamburger {
    display: flex;
    margin-left: auto;
  }
}

@media (max-width: 480px) {
  .app-header {
    height: 4.5rem;
  }

  .app-header--scrolled {
    height: 4rem;
  }

  .header-brand__logo {
    width: 2.5rem;
    height: 2.5rem;
  }


}
</style>
