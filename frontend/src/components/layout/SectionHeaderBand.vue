<script setup lang="ts">
/**
 * SectionHeaderBand.vue — Banda con degradado de marca y soporte de portada.
 * Permite título, subtítulo, breadcrumb y una imagen de portada con overlay
 * degradado que se fusiona con el fondo institucional (según diseño aprobado).
 */
import Breadcrumb from './Breadcrumb.vue'

interface Props {
  title: string
  subtitle?: string
  showBreadcrumb?: boolean
  image?: string
  imageAlt?: string
  centered?: boolean
}

withDefaults(defineProps<Props>(), {
  showBreadcrumb: true,
  centered: false,
})
</script>

<template>
  <section
    class="section-header-band"
    :class="{
      'section-header-band--has-image': Boolean(image),
      'section-header-band--centered': centered,
    }"
  >
    <!-- Background Image with Fade Gradient Overlay -->
    <div v-if="image" class="section-header-band__bg-wrapper" aria-hidden="true">
      <img :src="image" :alt="imageAlt || ''" class="section-header-band__bg-img" />
      <div class="section-header-band__bg-fade"></div>
    </div>

    <div class="container section-header-band__inner">
      <Breadcrumb v-if="showBreadcrumb" class="section-header-band__breadcrumb" />

      <div class="section-header-band__content">
        <h1 class="section-header-band__title">{{ title }}</h1>
        <p v-if="subtitle" class="section-header-band__subtitle">{{ subtitle }}</p>
        <slot name="extra" />
      </div>
    </div>
  </section>
</template>

<style scoped>
.section-header-band {
  position: relative;
  overflow: hidden;
  background: linear-gradient(135deg, #1b144b 0%, #2c1b6e 50%, #3e278b 100%);
  color: var(--color-blanco);
  padding-top: calc(var(--header-height) + var(--space-6));
  padding-bottom: var(--space-6);
  min-height: 12rem;
  display: flex;
  align-items: center;
}

/* Subtle background accent */
.section-header-band::before {
  content: '';
  position: absolute;
  top: -40%;
  right: -10%;
  width: 500px;
  height: 500px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.04);
  pointer-events: none;
}

/* Background image on the right fading to the left */
.section-header-band__bg-wrapper {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  width: 55%;
  max-width: 30rem;
  overflow: hidden;
  pointer-events: none;
  z-index: 1;
}

.section-header-band__bg-img {
  width: 100%;
  height: 100%;
  object-fit: fill;
  object-position: center center;
  opacity: 0.88;
}

.section-header-band__bg-fade {
  position: absolute;
  inset: 0;
  background: linear-gradient(
    to right,
    rgba(44, 27, 110, 1) 0%,
    rgba(44, 27, 110, 0.3) 65%,
    rgba(44, 27, 110, 0.1) 100%
  );
}

.section-header-band__inner {
  position: relative;
  z-index: 2;
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
}

.section-header-band--centered .section-header-band__inner {
  align-items: center;
  text-align: center;
}

.section-header-band__content {
  display: flex;
  flex-direction: column;
  gap: var(--space-1);
  max-width: 68ch;
}

.section-header-band--centered .section-header-band__content {
  align-items: center;
  text-align: center;
}

.section-header-band__breadcrumb {
  margin-bottom: var(--space-1);
}

.section-header-band__title {
  font-size: clamp(2rem, 4.5vw, 3rem);
  font-weight: 700;
  line-height: 1.15;
  color: var(--color-blanco);
  margin: 0;
  animation: bandTextFade 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
}

.section-header-band__subtitle {
  font-size: var(--text-large-size);
  line-height: 1.5;
  color: rgba(255, 255, 255, 0.88);
  margin: 0;
  max-width: 58ch;
  animation: bandTextFade 0.5s 0.08s cubic-bezier(0.16, 1, 0.3, 1) both;
}

@keyframes bandTextFade {
  from {
    opacity: 0;
    transform: translateY(12px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Responsive Breakpoints */
@media (max-width: 1024px) {
  .section-header-band {
    padding-top: calc(5.75rem + var(--space-3));
  }
}

@media (max-width: 768px) {
  .section-header-band {
    padding-top: calc(5.25rem + var(--space-3));
    padding-bottom: var(--space-5);
  }

  .section-header-band__bg-wrapper {
    width: 100%;
    opacity: 0.25;
  }
}

@media (max-width: 480px) {
  .section-header-band {
    padding-top: calc(4.5rem + var(--space-2));
    padding-bottom: var(--space-4);
  }

  .section-header-band__title {
    font-size: clamp(1.65rem, 7vw, 2.1rem);
  }

  .section-header-band__subtitle {
    font-size: 0.95rem;
  }
}
</style>
