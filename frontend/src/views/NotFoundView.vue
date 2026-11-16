<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { ArrowLeft, Compass, Home } from '@lucide/vue'
</script>

<template>
  <main class="not-found">
    <div class="container not-found__inner">
      <div class="not-found__badge">
        <Compass :size="24" aria-hidden="true" />
        <span>Error 404</span>
      </div>

      <h1 class="not-found__title">Página no encontrada</h1>

      <p class="not-found__desc">
        La ruta que intentas acceder no existe, ha sido reubicada o el enlace es incorrecto.
      </p>

      <div class="not-found__actions">
        <RouterLink to="/" class="not-found__btn not-found__btn--primary">
          <Home :size="16" aria-hidden="true" />
          <span>Volver al Inicio</span>
        </RouterLink>

        <RouterLink to="/noticias" class="not-found__btn not-found__btn--secondary">
          <span>Explorar Noticias</span>
        </RouterLink>
      </div>
    </div>
  </main>
</template>

<style scoped>
.not-found {
  min-height: calc(100vh - var(--header-height));
  display: flex;
  align-items: center;
  justify-content: center;
  padding-block: var(--space-8);
  background: var(--color-superficie);
}

.not-found__inner {
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  max-width: 38rem;
  gap: var(--space-3);
}

.not-found__badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.4rem 1rem;
  border-radius: 999px;
  background: rgba(243, 0, 96, 0.1);
  color: var(--color-rosa-carmin);
  font-size: 0.85rem;
  font-weight: 700;
  letter-spacing: 0.05em;
  text-transform: uppercase;
}

.not-found__title {
  color: var(--color-indigo);
  font-size: clamp(2rem, 5vw, 3rem);
  font-weight: 700;
  line-height: 1.15;
}

.not-found__desc {
  color: #555c73;
  font-size: var(--text-large-size);
  line-height: 1.6;
}

.not-found__actions {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  margin-top: var(--space-2);
}

.not-found__btn {
  display: inline-flex;
  min-height: 2.75rem;
  align-items: center;
  gap: 0.5rem;
  padding: 0.65rem 1.5rem;
  border-radius: var(--radius-sm);
  font-size: var(--text-small-size);
  font-weight: 700;
  text-decoration: none;
  transition: transform 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;
}

.not-found__btn--primary {
  background: var(--color-indigo);
  color: var(--color-blanco);
  box-shadow: 0 4px 14px rgba(53, 68, 159, 0.25);
}

.not-found__btn--primary:hover {
  background: #253381;
  transform: translateY(-2px);
}

.not-found__btn--secondary {
  border: 1px solid var(--color-desactivado);
  background: var(--color-blanco);
  color: var(--color-tinta);
}

.not-found__btn--secondary:hover {
  background: var(--color-superficie);
  border-color: #c4c7d5;
}
</style>
