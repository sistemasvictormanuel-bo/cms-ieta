<script setup lang="ts">
import Breadcrumb from '@/components/layout/Breadcrumb.vue'

defineProps<{
  title: string
  subtitle: string
  breadcrumbLabel?: string
}>()
</script>

<template>
  <section class="varianza-hero">
    <div class="container varianza-hero__inner">
      <Breadcrumb class="varianza-hero__breadcrumb" :append-label="breadcrumbLabel" />
      <div class="varianza-hero__title">
        <h1>{{ title }}</h1>
        <span>{{ subtitle }}</span>
      </div>
    </div>
  </section>
</template>

<style scoped>
.varianza-hero {
  position: relative;
  overflow: hidden;
  background: linear-gradient(90deg, #1b144b 0%, #2b1a6c 50%, #3e2689 100%);
  color: #f5f5f8;
  border-radius: 260px 0 0 0;
  margin: 5.7rem 0 var(--space-2) 0;
  transition: border-radius 0.25s ease, margin 0.25s ease;
}

.varianza-hero::after {
  position: absolute;
  right: -8rem;
  bottom: -14rem;
  width: 30rem;
  aspect-ratio: 1;
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 50%;
  content: '';
  pointer-events: none;
}

.varianza-hero__inner {
  position: relative;
  z-index: 1;
  min-height: 12.5rem;
  padding-top: 1.5rem;
  padding-bottom: clamp(1.75rem, 3.5vw, 2.75rem);
}

.varianza-hero__breadcrumb {
  display:grid;
  justify-items: center;
  color: rgba(255, 255, 255, 0.75);
}

.varianza-hero__title {
  display: grid;
  justify-items: center;
  margin-top: 0.5rem;
  text-align: center;
}

.varianza-hero__title h1 {
  color: #f50088;
  font-size: clamp(2.35rem, 5vw, 3.5rem);
  font-weight: 800;
  line-height: 1.1;
  letter-spacing: -0.02em;
  margin: 0 0 0.35rem;
  animation: heroTextFade 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
}

.varianza-hero__title span {
  color: #ffffff;
  font-size: clamp(0.95rem, 2vw, 1.35rem);
  font-weight: 500;
  line-height: 1.35;
  opacity: 0.95;
  animation: heroTextFade 0.5s 0.08s cubic-bezier(0.16, 1, 0.3, 1) both;
}

@keyframes heroTextFade {
  from {
    opacity: 0;
    transform: translateY(12px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@media (max-width: 1024px) {
  .varianza-hero {
    border-radius: 120px 0 0 0;
    margin: 115px 0 var(--space-2) 0;
  }

  .varianza-hero__inner {
    padding-left: clamp(1.5rem, 5vw, 3.5rem);
  }
}

@media (max-width: 768px) {
  .varianza-hero {
    border-radius: 50px 0 0 0;
    margin: 94px 0 var(--space-2) 0;
  }

  .varianza-hero__inner {
    min-height: auto;
    padding-top: 1.25rem;
    padding-bottom: 1.75rem;
    padding-inline: 1rem;
  }

  .varianza-hero__title h1 {
    font-size: clamp(1.9rem, 7vw, 2.5rem);
  }

  .varianza-hero__title span {
    font-size: clamp(0.9rem, 3.5vw, 1.1rem);
  }
}

@media (max-width: 480px) {
  .varianza-hero {
    border-radius: 32px 0 0 0;
    margin: 80px 0 var(--space-2) 0;
  }

  .varianza-hero__inner {
    padding-inline: 0.75rem;
    padding-top: 1rem;
    padding-bottom: 1.35rem;
  }

  .varianza-hero__title h1 {
    font-size: clamp(1.65rem, 8vw, 2.1rem);
  }

  .varianza-hero__title span {
    font-size: clamp(0.82rem, 3.2vw, 0.95rem);
  }
}
</style>
