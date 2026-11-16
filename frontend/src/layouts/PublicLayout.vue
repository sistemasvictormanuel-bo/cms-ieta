<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { RouterView } from 'vue-router'
import { useRoute } from 'vue-router'
import AppHeader from '@/components/layout/AppHeader.vue'
import AppFooter from '@/components/layout/AppFooter.vue'
import AnnouncementModal from '@/components/layout/AnnouncementModal.vue'
import { ConfiguracionRepository } from '@/api/repositories.js'
import { unwrapResource } from '@/api/response'
import type { InstitutionConfigApiItem } from '@/api/types'

const route = useRoute()
const institutionConfig = ref<InstitutionConfigApiItem | null>(null)
const footerVariant = 'default'
// Key for top-level view transitions:
// Keep same key across tab parameter changes within the same view (e.g. acerca-de/:tab?, revista-varianza/:tab?, recursos/:tab?)
// so switching tabs does NOT unmount or fade the entire page!
const viewKey = computed(() => {
  if (route.params.slug) return `${String(route.name)}-${String(route.params.slug)}`
  if (route.params.id) return `${String(route.name)}-${String(route.params.id)}`
  if (route.params.numero) return `${String(route.name)}-${String(route.params.numero)}`
  return String(route.name || route.matched[1]?.path || route.path)
})

onMounted(async () => {
  try {
    institutionConfig.value = unwrapResource<InstitutionConfigApiItem>(await ConfiguracionRepository.get())
  } catch {
    institutionConfig.value = null
  }
})
</script>

<template>
  <div class="public-layout">
    <AppHeader :institution-config="institutionConfig" />

    <main class="main-content">
      <!-- Transición fade entre páginas principales (sin desmontar en cambios de sub-tabs) -->
      <RouterView v-slot="{ Component }">
        <Transition name="page-fade" mode="out-in">
          <component :is="Component" :key="viewKey" />
        </Transition>
      </RouterView>
    </main>

    <AppFooter :variant="footerVariant" :institution-config="institutionConfig" />
    <AnnouncementModal />
  </div>
</template>

<style scoped>
.public-layout {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.main-content {
  flex: 1;
  /* El header es fixed, las vistas internas con SectionHeaderBand
     ya contemplan el padding-top internamente.
     Para la home y otras vistas sin banda, se añade padding base. */
}

/* Transición fade entre páginas — Guía §6.7: 0.2-0.3s */
.page-fade-enter-active,
.page-fade-leave-active {
  transition: opacity 0.2s ease;
}
.page-fade-enter-from,
.page-fade-leave-to {
  opacity: 0;
}

/* Respetar prefers-reduced-motion */
@media (prefers-reduced-motion: reduce) {
  .page-fade-enter-active,
  .page-fade-leave-active {
    transition: none;
  }
}
</style>
