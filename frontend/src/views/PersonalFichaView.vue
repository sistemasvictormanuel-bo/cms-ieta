<script setup lang="ts">
import { computed, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import {
  ArrowLeft,
  ExternalLink,
  Mail,
  Phone,
  User,
} from '@lucide/vue'
import EmptyState from '@/components/common/EmptyState.vue'
import SectionHeaderBand from '@/components/layout/SectionHeaderBand.vue'
import { mediaUrl } from '@/api/response'
import { PersonalRepository } from '@/api/repositories.js'
import type { StaffApiItem } from '@/api/types'
import { useApiResource } from '@/composables/useApiResource'
import { useGsapReveal } from '@/composables/useGsapReveal'

const route = useRoute()
const profileRoot = ref<HTMLElement | null>(null)
const staffId = computed(() => String(route.params.slug ?? ''))

const { data: member, loading, error, reload } = useApiResource<StaffApiItem>(
  staffId,
  (id) => PersonalRepository.getById(id),
)

const name = computed(() => member.value?.persona?.nombre_completo || [
  member.value?.persona?.p_nombre,
  member.value?.persona?.p_apellido_paterno,
  member.value?.persona?.p_apellido_materno,
].filter(Boolean).join(' ') || 'Personal IETA')

const photo = computed(() => mediaUrl(member.value?.persona?.foto_url))
const roleName = computed(() => member.value?.cargo?.nombre || 'Docente Investigador')

const subtitle = computed(() => {
  const parts = [roleName.value]
  if (member.value?.fecha_ingreso) {
    const year = member.value.fecha_ingreso.slice(0, 4)
    parts.push(`${year}–Presente`)
  } else {
    parts.push('Presente')
  }
  return parts.join(' · ')
})

const leadSummary = computed(() => {
  if (member.value?.descripcion) {
    return member.value.descripcion
  }
  return `Miembro activo del personal institucional y cuerpo docente del Instituto de Estadística Teórica y Aplicada (IETA).`
})

useGsapReveal(profileRoot, {
  selector: '.profile-reveal',
  stagger: 0.08,
  y: 18,
})
</script>

<template>
  <div ref="profileRoot">
    <SectionHeaderBand :title="name" :subtitle="roleName" />

    <main class="container view-body unified-profile">
      <!-- Back Navigation -->
      <nav class="unified-profile__nav profile-reveal" aria-label="Navegación de retorno">
        <RouterLink class="unified-profile__back" to="/acerca-de/personal">
          <ArrowLeft :size="16" aria-hidden="true" />
          <span>Volver al directorio de personal</span>
        </RouterLink>
      </nav>

      <!-- Loading State -->
      <div v-if="loading" class="unified-profile-skeleton" role="status" aria-label="Cargando perfil">
        <div class="skeleton-left">
          <div class="skeleton-line skeleton-line--title"></div>
          <div class="skeleton-line skeleton-line--sub"></div>
          <div class="skeleton-line skeleton-line--bar"></div>
          <div class="skeleton-line skeleton-line--lead"></div>
        </div>
        <div class="skeleton-right">
          <div class="skeleton-photo"></div>
          <div class="skeleton-meta"></div>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="unified-profile__state" role="alert">
        <EmptyState
          title="No se pudo cargar la ficha de personal"
          description="Por favor verifica el identificador o inténtalo nuevamente."
          action-label="Reintentar"
          @action="reload"
        />
      </div>

      <!-- Not Found -->
      <EmptyState v-else-if="!member" title="No se encontró la ficha de personal" />

      <!-- Loaded Profile (Unified Reference Image 2 Style) -->
      <article v-else class="unified-profile__article profile-reveal">
        <!-- 1. TOP HERO: 2 COLUMNS (Left: Info / Right: Photo + Metadata) -->
        <header class="profile-hero">
          <!-- Left Column -->
          <div class="profile-hero__intro">
            <h1 class="profile-hero__name">{{ name }}</h1>
            <p class="profile-hero__subtitle">{{ subtitle }}</p>

            <!-- Minimal black accent bar -->
            <div class="profile-hero__accent-bar" aria-hidden="true"></div>

            <!-- Lead Quote / Summary -->
            <p class="profile-hero__lead">
              {{ leadSummary }}
            </p>
          </div>

          <!-- Right Column: Photo + Metadata Box directly below -->
          <aside class="profile-hero__aside" aria-label="Datos institucionales del personal">
            <div class="profile-photo-wrapper">
              <img
                v-if="photo"
                :src="photo"
                :alt="`Fotografía de ${name}`"
                class="profile-photo-img"
                loading="lazy"
              />
              <div v-else class="profile-photo-fallback" aria-hidden="true">
                <User :size="72" />
              </div>
            </div>

            <!-- Metadata List below photo (Reference Image 2) -->
            <dl class="profile-aside-meta">
              <div class="aside-meta-item">
                <dt class="aside-meta-label">Institución</dt>
                <dd class="aside-meta-val">
                  <span>Universidad Mayor de San Andrés / IETA</span>
                </dd>
              </div>

              <div class="aside-meta-item">
                <dt class="aside-meta-label">Cargo Institucional</dt>
                <dd class="aside-meta-val">
                  <span>{{ roleName }}</span>
                </dd>
              </div>

              <div v-if="member.persona?.p_correo" class="aside-meta-item">
                <dt class="aside-meta-label">Correo Electrónico</dt>
                <dd class="aside-meta-val">
                  <a :href="`mailto:${member.persona.p_correo}`" class="aside-meta-link">
                    <Mail :size="14" aria-hidden="true" />
                    <span>{{ member.persona.p_correo }}</span>
                  </a>
                </dd>
              </div>

              <div v-if="member.persona?.p_celular" class="aside-meta-item">
                <dt class="aside-meta-label">Teléfono / Referencia</dt>
                <dd class="aside-meta-val">
                  <a :href="`tel:${member.persona.p_celular}`" class="aside-meta-link">
                    <Phone :size="14" aria-hidden="true" />
                    <span>{{ member.persona.p_celular }}</span>
                  </a>
                </dd>
              </div>

              <div v-if="member.persona?.p_sitio_web" class="aside-meta-item">
                <dt class="aside-meta-label">Perfil Académico / Web</dt>
                <dd class="aside-meta-val">
                  <a
                    :href="member.persona.p_sitio_web"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="aside-meta-link"
                  >
                    <ExternalLink :size="14" aria-hidden="true" />
                    <span>Sitio Web Personal</span>
                  </a>
                </dd>
              </div>
            </dl>
          </aside>
        </header>

        <!-- Divider Line (Reference Image 2) -->
        <hr class="profile-divider" />

        <!-- 2. LOWER CONTENT BODY -->
        <section class="profile-body">
          <div class="profile-body__section">
            <h2 class="profile-body__heading">
              Trayectoria y Funciones Institucionales
            </h2>

            <p class="profile-body__text">
              Como parte del equipo del IETA, participa activamente en el fortalecimiento académico de la Carrera de Estadística de la UMSA, apoyando las líneas de investigación teórica y aplicada, la dirección de proyectos y la capacitación continua de la comunidad universitaria.
            </p>
          </div>
        </section>
      </article>
    </main>
  </div>
</template>

<style scoped>
.unified-profile {
  min-height: 50vh;
  padding-block: var(--space-4) var(--space-8);
  max-width: 72rem;
  margin-inline: auto;
}

.unified-profile__nav {
  margin-bottom: 2rem;
}

.unified-profile__back {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  color: #35449f;
  font-size: 0.9rem;
  font-weight: 700;
  text-decoration: none;
  transition: transform 0.15s ease, color 0.15s ease;
}

.unified-profile__back:hover {
  transform: translateX(-4px);
  color: #f30060;
}

/* 1. TOP HERO: 2 COLUMNS */
.profile-hero {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 22rem;
  gap: clamp(2rem, 5vw, 4.5rem);
  align-items: start;
}

.profile-hero__intro {
  display: flex;
  flex-direction: column;
}

.profile-hero__name {
  margin: 0;
  font-family: var(--font-main, 'DM Sans', sans-serif);
  font-size: clamp(2.2rem, 4vw, 3rem);
  font-weight: 700;
  line-height: 1.15;
  color: #111827;
  letter-spacing: -0.015em;
}

.profile-hero__subtitle {
  margin: 0.75rem 0 0;
  font-size: 1.05rem;
  font-weight: 600;
  color: #374151;
  line-height: 1.4;
}

.profile-hero__accent-bar {
  width: 36px;
  height: 3.5px;
  background: #111827;
  margin: 1.75rem 0;
  border-radius: 1px;
}

.profile-hero__lead {
  margin: 0;
  font-size: 1.15rem;
  line-height: 1.7;
  color: #1f2937;
  font-weight: 400;
}

/* Right Aside Column (Photo + Meta) */
.profile-hero__aside {
  display: flex;
  flex-direction: column;
}

.profile-photo-wrapper {
  width: 100%;
  aspect-ratio: 1 / 1;
  background: #f1f5f9;
  border-radius: 4px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(15, 23, 42, 0.08);
  border: 1px solid #e2e8f0;
}

.profile-photo-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center top;
  display: block;
}

.profile-photo-fallback {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
  color: #64748b;
}

.profile-aside-meta {
  margin: 1.5rem 0 0;
  display: flex;
  flex-direction: column;
}

.aside-meta-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  padding-block: 0.85rem;
  border-top: 1px solid #e5e7eb;
}

.aside-meta-label {
  font-size: 0.78rem;
  font-weight: 700;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.aside-meta-val {
  margin: 0;
  font-size: 0.95rem;
  font-weight: 600;
  color: #111827;
  line-height: 1.4;
}

.aside-meta-link {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  color: #35449f;
  text-decoration: underline;
  text-underline-offset: 3px;
  transition: color 0.15s ease;
}

.aside-meta-link:hover {
  color: #f30060;
}

/* Divider Line */
.profile-divider {
  border: 0;
  border-top: 1px solid #e5e7eb;
  margin: 3.5rem 0;
}

/* 2. LOWER CONTENT BODY */
.profile-body {
  display: flex;
  flex-direction: column;
  gap: 3.5rem;
}

.profile-body__section {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.profile-body__heading {
  margin: 0;
  font-family: var(--font-main, 'DM Sans', sans-serif);
  font-size: clamp(1.4rem, 2.5vw, 1.85rem);
  font-weight: 700;
  color: #111827;
  line-height: 1.3;
}

.profile-body__text {
  margin: 0;
  font-size: 1.05rem;
  line-height: 1.75;
  color: #374151;
  max-width: 65ch;
}

/* Skeleton */
.unified-profile-skeleton {
  display: grid;
  grid-template-columns: 1fr 22rem;
  gap: 3rem;
  padding-block: 2rem;
}

.skeleton-line {
  height: 1rem;
  background: #edeef5;
  border-radius: 4px;
  margin-bottom: 1rem;
}

.skeleton-line--title { height: 2.5rem; width: 75%; }
.skeleton-line--sub { height: 1.25rem; width: 45%; }
.skeleton-line--bar { height: 4px; width: 40px; }
.skeleton-line--lead { height: 4.5rem; width: 90%; }
.skeleton-photo { width: 100%; aspect-ratio: 1; background: #edeef5; border-radius: 4px; }
.skeleton-meta { height: 6rem; background: #edeef5; margin-top: 1rem; border-radius: 4px; }

/* Responsive */
@media (max-width: 900px) {
  .profile-hero {
    grid-template-columns: 1fr;
    gap: 2.5rem;
  }

  .profile-hero__aside {
    max-width: 22rem;
  }

  .unified-profile-skeleton {
    grid-template-columns: 1fr;
  }
}
</style>
