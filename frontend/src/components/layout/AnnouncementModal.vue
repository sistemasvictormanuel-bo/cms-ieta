<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { Bell, ExternalLink, X } from '@lucide/vue'
import { ComunicadosRepository } from '@/api/repositories.js'
import { mediaUrl, unwrapResource } from '@/api/response'
import type { AnnouncementApiItem } from '@/api/types'

const props = defineProps<{
  forcePreview?: boolean
}>()

const emit = defineEmits<{
  (e: 'close'): void
}>()

const route = useRoute()
const announcement = ref<AnnouncementApiItem | null>(null)
const dialog = ref<HTMLDialogElement | null>(null)
const flyer = ref<string | undefined>()
const isPreviewMode = ref(false)

const defaultPreviewAnnouncement: AnnouncementApiItem = {
  id: 9999,
  titulo: 'Convocatoria Abierta: Revista Varianza — Volumen 26',
  tipo: 'Comunicado Oficial',
  descripcion:
    'El Instituto de Estadística Teórica y Aplicada (IETA) invita a docentes, investigadores y estudiantes a presentar artículos científicos y de divulgación para la próxima edición indexada.',
  texto_boton: 'Ver bases y requisitos',
  enlace_url: '/revista-varianza/instrucciones-autores',
  activo: true,
  flyer_url: '',
}

function isAnnouncementActive(item: AnnouncementApiItem | null | undefined): boolean {
  if (!item) return false
  const anyItem = item as unknown as Record<string, unknown>
  const activeVal = anyItem.activo ?? anyItem.estado
  if (activeVal === false || activeVal === 0 || activeVal === '0') return false
  if (activeVal === true || activeVal === 1 || activeVal === '1') return true
  return true
}

function resolveFlyerUrl(data: AnnouncementApiItem): string | undefined {
  const anyData = data as unknown as Record<string, unknown>
  const raw =
    data.flyer_url ||
    data.imagen_url ||
    data.portada_url ||
    (typeof anyData.flyer === 'string' ? anyData.flyer : undefined) ||
    (typeof anyData.imagen === 'string' ? anyData.imagen : undefined)
  return raw ? mediaUrl(raw) : undefined
}

function openModal(data: AnnouncementApiItem, isPreview = false) {
  announcement.value = data
  flyer.value = resolveFlyerUrl(data)
  isPreviewMode.value = isPreview
  void nextTick(() => {
    dialog.value?.showModal()
  })
}

function dismiss() {
  if (announcement.value && !isPreviewMode.value) {
    try {
      sessionStorage.setItem('ieta-dismissed-announcement', String(announcement.value.id))
    } catch {
      // Storage can be unavailable in private browsing contexts.
    }
  }
  dialog.value?.close()
  emit('close')
}

// Handler for global preview event from DevComponents or elsewhere
function handlePreviewEvent(event?: Event) {
  const customDetail = (event as CustomEvent<AnnouncementApiItem>)?.detail
  openModal(customDetail || defaultPreviewAnnouncement, true)
}

const isAllowedRoute = computed(() => {
  const p = route.path
  return p === '/' || p === '/noticias' || p.startsWith('/noticias/')
})

async function checkAndShowAnnouncement() {
  // Check URL query parameter ?preview=comunicado or ?preview=anuncio (bypasses route restriction for debugging on any page)
  const urlParams = new URLSearchParams(window.location.search)
  const wantsPreview =
    props.forcePreview ||
    urlParams.get('preview') === 'comunicado' ||
    urlParams.get('preview') === 'anuncio'

  if (wantsPreview) {
    const previewData = { ...defaultPreviewAnnouncement }
    openModal(previewData, true)
    return
  }

  if (!isAllowedRoute.value) return

  try {
    const active = unwrapResource<AnnouncementApiItem>(await ComunicadosRepository.getActive())
    if (!active || !isAnnouncementActive(active)) return
    try {
      if (sessionStorage.getItem('ieta-dismissed-announcement') === String(active.id)) return
    } catch {
      // Show when storage is unavailable
    }
    openModal(active, false)
  } catch {
    announcement.value = null
  }
}

watch(
  () => route.path,
  () => {
    void checkAndShowAnnouncement()
  },
)

onMounted(() => {
  window.addEventListener('ieta:open-announcement-preview', handlePreviewEvent)
  void checkAndShowAnnouncement()
})

onBeforeUnmount(() => {
  window.removeEventListener('ieta:open-announcement-preview', handlePreviewEvent)
})

defineExpose({
  openPreview: (custom?: AnnouncementApiItem) => openModal(custom || defaultPreviewAnnouncement, true),
  close: dismiss,
})
</script>

<template>
  <dialog
    ref="dialog"
    class="announcement-dialog"
    :class="{ 'announcement-dialog--flyer': Boolean(flyer) }"
    aria-labelledby="announcement-title"
    @click.self="dismiss"
    @close="announcement = null"
  >
    <article v-if="announcement" class="announcement-dialog__panel" :class="{ 'has-flyer': Boolean(flyer) }">
      <!-- Close Button (always accessible) -->
      <button
        class="announcement-dialog__close"
        type="button"
        aria-label="Cerrar comunicado"
        @click="dismiss"
      >
        <X :size="20" aria-hidden="true" />
      </button>

      <!-- 1. CASO CON IMAGEN/FLYER (Imagen es el foco principal) -->
      <template v-if="flyer">
        <div class="announcement-flyer-focus">
          <div class="announcement-flyer-frame">
            <img class="announcement-flyer-img" :src="flyer" :alt="announcement.titulo" />
          </div>

          <!-- Pie compacto enfocado en acción -->
          <footer class="announcement-flyer-bar">
            <div class="announcement-flyer-info">
              <span v-if="announcement.tipo" class="announcement-flyer-tag">
                {{ announcement.tipo }}
              </span>
              <h2 id="announcement-title" class="announcement-flyer-title">
                {{ announcement.titulo }}
              </h2>
            </div>

            <div class="announcement-flyer-actions">
              <a
                v-if="announcement.enlace_url"
                class="announcement-action-btn"
                :href="announcement.enlace_url"
                :target="announcement.enlace_url.startsWith('http') ? '_blank' : '_self'"
                :rel="announcement.enlace_url.startsWith('http') ? 'noopener noreferrer' : ''"
                @click="dismiss"
              >
                <span>{{ announcement.texto_boton || 'Ver información' }}</span>
                <ExternalLink :size="15" aria-hidden="true" />
              </a>

              <button
                class="announcement-dismiss-btn"
                type="button"
                @click="dismiss"
              >
                Cerrar
              </button>
            </div>
          </footer>
        </div>
      </template>

      <!-- 2. CASO SIN IMAGEN (Fallback tipográfico estructurado) -->
      <template v-else>
        <div class="announcement-dialog__accent"></div>

        <div class="announcement-dialog__banner-fallback">
          <div class="announcement-dialog__icon-badge">
            <Bell :size="26" aria-hidden="true" />
          </div>
          <span class="announcement-dialog__badge">
            {{ isPreviewMode ? 'Vista previa · ' : '' }}{{ announcement.tipo || 'Comunicado institucional' }}
          </span>
        </div>

        <div class="announcement-dialog__body">
          <h2 id="announcement-title" class="announcement-dialog__title">
            {{ announcement.titulo }}
          </h2>

          <p v-if="announcement.descripcion" class="announcement-dialog__desc">
            {{ announcement.descripcion }}
          </p>

          <div class="announcement-dialog__footer">
            <a
              v-if="announcement.enlace_url"
              class="announcement-action-btn"
              :href="announcement.enlace_url"
              :target="announcement.enlace_url.startsWith('http') ? '_blank' : '_self'"
              :rel="announcement.enlace_url.startsWith('http') ? 'noopener noreferrer' : ''"
              @click="dismiss"
            >
              <span>{{ announcement.texto_boton || 'Más información' }}</span>
              <ExternalLink :size="16" aria-hidden="true" />
            </a>

            <button
              class="announcement-dismiss-btn"
              type="button"
              @click="dismiss"
            >
              Entendido
            </button>
          </div>
        </div>
      </template>
    </article>
  </dialog>
</template>

<style scoped>
.announcement-dialog {
  position: fixed;
  inset: 0;
  margin: auto;
  width: min(38rem, calc(100vw - 2rem));
  max-width: calc(100vw - 2rem);
  max-height: min(92vh, 56rem);
  padding: 0;
  overflow: hidden;
  border: 0;
  border-radius: var(--radius-lg, 16px);
  background: transparent;
  color: var(--color-tinta, #1e293b);
  box-shadow: 0 25px 60px -15px rgba(10, 15, 35, 0.55);
  outline: none;
}

.announcement-dialog--flyer {
  width: min(44rem, calc(100vw - 2rem));
  background: #0f1424;
}

.announcement-dialog::backdrop {
  background: rgba(10, 14, 32, 0.78);
  backdrop-filter: blur(6px);
}

.announcement-dialog[open] {
  animation: modal-enter 0.28s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes modal-enter {
  from {
    opacity: 0;
    transform: scale(0.96) translateY(12px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.announcement-dialog__panel {
  position: relative;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  border-radius: var(--radius-lg, 16px);
  background: #ffffff;
}

.announcement-dialog__panel.has-flyer {
  background: #0f1424;
  color: #ffffff;
}

.announcement-dialog__close {
  position: absolute;
  z-index: 20;
  top: 0.85rem;
  right: 0.85rem;
  display: grid;
  width: 2.5rem;
  height: 2.5rem;
  place-items: center;
  border: 1px solid rgba(255, 255, 255, 0.25);
  border-radius: 50%;
  background: rgba(15, 20, 36, 0.75);
  backdrop-filter: blur(8px);
  color: #ffffff;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
  transition: transform 0.18s ease, background-color 0.18s ease;
}

.announcement-dialog__close:hover {
  transform: scale(1.08) rotate(90deg);
  background: #f30060;
  border-color: #f30060;
}

/* 1. Flyer Focus Layout */
.announcement-flyer-focus {
  display: flex;
  flex-direction: column;
  max-height: min(92vh, 56rem);
  background: #0f1424;
}

.announcement-flyer-frame {
  position: relative;
  max-height: min(72vh, 44rem);
  overflow: auto;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #080b15;
}

.announcement-flyer-img {
  display: block;
  width: 100%;
  height: auto;
  max-height: min(72vh, 44rem);
  object-fit: contain;
}

.announcement-flyer-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1.25rem;
  padding: 1rem 1.4rem;
  background: #14192d;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.announcement-flyer-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  min-width: 0;
  flex: 1;
}

.announcement-flyer-tag {
  display: inline-block;
  font-size: 0.7rem;
  font-weight: 800;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: #ff528f;
}

.announcement-flyer-title {
  margin: 0;
  font-size: 1rem;
  font-weight: 700;
  line-height: 1.3;
  color: #ffffff;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.announcement-flyer-actions {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  flex-shrink: 0;
}

/* Botones compartidos */
.announcement-action-btn {
  display: inline-flex;
  min-height: 2.5rem;
  align-items: center;
  gap: 0.45rem;
  padding: 0.5rem 1.25rem;
  border-radius: 8px;
  background: #f30060;
  color: #ffffff;
  font-size: 0.85rem;
  font-weight: 700;
  text-decoration: none;
  box-shadow: 0 4px 14px rgba(243, 0, 96, 0.4);
  transition: transform 0.15s ease, background-color 0.15s ease;
}

.announcement-action-btn:hover {
  transform: translateY(-2px);
  background: #db0056;
}

.announcement-dismiss-btn {
  display: inline-flex;
  min-height: 2.5rem;
  align-items: center;
  padding: 0.5rem 1rem;
  border: 1px solid rgba(255, 255, 255, 0.18);
  border-radius: 8px;
  background: transparent;
  color: #cbd5e1;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.15s ease, color 0.15s ease;
}

.announcement-dismiss-btn:hover {
  background: rgba(255, 255, 255, 0.1);
  color: #ffffff;
}

/* 2. Fallback Layout */
.announcement-dialog__accent {
  height: 5px;
  background: linear-gradient(90deg, #35449f 0%, #f30060 50%, #9024f0 100%);
}

.announcement-dialog__banner-fallback {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  padding: 2.5rem 1.5rem 1.5rem;
  background: linear-gradient(135deg, #1d255a 0%, #35449f 60%, #4c3b9b 100%);
  color: #ffffff;
  text-align: center;
}

.announcement-dialog__icon-badge {
  display: grid;
  width: 3.5rem;
  height: 3.5rem;
  place-items: center;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.15);
  box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.25);
  color: #ff9ec4;
}

.announcement-dialog__badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 999px;
  background: rgba(243, 0, 96, 0.25);
  border: 1px solid rgba(243, 0, 96, 0.45);
  color: #ffd4e5;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.05em;
  text-transform: uppercase;
}

.announcement-dialog__body {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  padding: 1.75rem;
}

.announcement-dialog__title {
  color: #1b144b;
  font-size: 1.45rem;
  font-weight: 700;
  line-height: 1.25;
}

.announcement-dialog__desc {
  color: #475569;
  font-size: 0.95rem;
  line-height: 1.6;
}

.announcement-dialog__footer {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: flex-end;
  gap: 0.75rem;
  margin-top: 0.75rem;
  padding-top: 1rem;
  border-top: 1px solid #e2e8f0;
}

.announcement-dialog__footer .announcement-dismiss-btn {
  border-color: #cbd5e1;
  color: #475569;
}

.announcement-dialog__footer .announcement-dismiss-btn:hover {
  background: #f1f5f9;
  color: #0f172a;
}

@media (max-width: 600px) {
  .announcement-flyer-bar {
    flex-direction: column;
    align-items: stretch;
    gap: 0.75rem;
  }
  .announcement-flyer-title {
    white-space: normal;
  }
  .announcement-flyer-actions {
    width: 100%;
    justify-content: stretch;
  }
  .announcement-flyer-actions > * {
    flex: 1;
    justify-content: center;
  }
}
</style>
