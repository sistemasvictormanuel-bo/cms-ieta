<script setup lang="ts">
import { computed, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import {
  ArrowLeft,
  ExternalLink,
  Mail,
  User,
} from '@lucide/vue'
import ContentCard from '@/components/common/ContentCard.vue'
import EditorJsContent from '@/components/common/EditorJsContent.vue'
import EmptyState from '@/components/common/EmptyState.vue'
import SectionHeaderBand from '@/components/layout/SectionHeaderBand.vue'
import { authorName } from '@/api/normalizers'
import { mediaUrl } from '@/api/response'
import { AutoresRepository } from '@/api/repositories.js'
import type { AuthorApiItem } from '@/api/types'
import { useApiResource } from '@/composables/useApiResource'
import { useGsapReveal } from '@/composables/useGsapReveal'

const route = useRoute()
const authorRoot = ref<HTMLElement | null>(null)
const slug = computed(() => String(route.params.slug ?? ''))

const { data: author, loading, error, reload } = useApiResource<AuthorApiItem>(
  slug,
  (id) => AutoresRepository.getBySlug(id),
)

const name = computed(() => (author.value ? authorName(author.value) : 'Perfil de Autor'))
const degree = computed(() => author.value?.grado_academico?.abreviatura || '')
const titleWithDegree = computed(() => {
  if (degree.value) return `${name.value}, ${degree.value}`
  return name.value
})

const subtitle = computed(() => {
  const parts = []
  if (author.value?.tipo_autor?.nombre) parts.push(author.value.tipo_autor.nombre)
  parts.push('Carrera de Estadística · UMSA')
  return parts.join(' · ')
})

const photo = computed(() => mediaUrl(author.value?.persona?.foto_url))

const leadSummary = computed(() => {
  if (author.value?.intereses_cientificos) {
    return author.value.intereses_cientificos
  }
  return `Investigador y autor científico en la Revista Varianza del Instituto de Estadística Teórica y Aplicada (IETA).`
})

const specialtyName = computed(() => {
  if (author.value?.especialidad?.nombre) return author.value.especialidad.nombre
  if (author.value?.especialidades?.length) {
    return author.value.especialidades.map((s) => s.nombre).join(', ')
  }
  return 'Estadística Teórica y Aplicada'
})

useGsapReveal(authorRoot, {
  selector: '.author-reveal',
  stagger: 0.08,
  y: 18,
})
</script>

<template>
  <div ref="authorRoot">
    <SectionHeaderBand
      :title="titleWithDegree"
      :subtitle="author?.especialidad?.nombre || 'Investigador / Autor de Revista Varianza'"
    />

    <main class="container view-body unified-profile">
      <!-- Back Navigation -->
      <nav class="unified-profile__nav author-reveal" aria-label="Navegación de retorno">
        <RouterLink class="unified-profile__back" to="/revista-varianza/autores">
          <ArrowLeft :size="16" aria-hidden="true" />
          <span>Volver a Autores Varianza</span>
        </RouterLink>
      </nav>

      <!-- Loading State -->
      <div v-if="loading" class="unified-profile-skeleton" role="status" aria-label="Cargando autor">
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
          title="No se pudo cargar el perfil del autor"
          description="Por favor, verifica el enlace o inténtalo nuevamente."
          action-label="Reintentar"
          @action="reload"
        />
      </div>

      <!-- Not Found -->
      <EmptyState v-else-if="!author" title="No se encontró el perfil de autor solicitado" />

      <!-- Loaded Profile (Unified Reference Image 2 Style) -->
      <article v-else class="unified-profile__article author-reveal">
        <!-- 1. TOP HERO: 2 COLUMNS (Left: Info / Right: Photo + Metadata) -->
        <header class="profile-hero">
          <!-- Left Column -->
          <div class="profile-hero__intro">
            <h1 class="profile-hero__name">{{ titleWithDegree }}</h1>
            <p class="profile-hero__subtitle">{{ subtitle }}</p>

            <!-- Minimal black accent bar -->
            <div class="profile-hero__accent-bar" aria-hidden="true"></div>

            <!-- Lead Quote / Summary -->
            <p class="profile-hero__lead">
              {{ leadSummary }}
            </p>
          </div>

          <!-- Right Column: Photo + Metadata Box directly below -->
          <aside class="profile-hero__aside" aria-label="Datos institucionales del autor">
            <div class="profile-photo-wrapper">
              <img
                v-if="photo"
                :src="photo"
                :alt="`Fotografía de ${titleWithDegree}`"
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
                <dt class="aside-meta-label">Disciplinas Científicas</dt>
                <dd class="aside-meta-val">
                  <span>{{ specialtyName }}</span>
                </dd>
              </div>

              <div v-if="author.persona?.p_correo" class="aside-meta-item">
                <dt class="aside-meta-label">Correo de Contacto</dt>
                <dd class="aside-meta-val">
                  <a :href="`mailto:${author.persona.p_correo}`" class="aside-meta-link">
                    <Mail :size="14" aria-hidden="true" />
                    <span>{{ author.persona.p_correo }}</span>
                  </a>
                </dd>
              </div>

              <div v-if="author.persona?.p_sitio_web" class="aside-meta-item">
                <dt class="aside-meta-label">Perfil Académico / ORCID</dt>
                <dd class="aside-meta-val">
                  <a
                    :href="author.persona.p_sitio_web"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="aside-meta-link"
                  >
                    <ExternalLink :size="14" aria-hidden="true" />
                    <span>Sitio Web / Repositorio</span>
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
          <!-- Biography / Trajectory -->
          <div class="profile-body__section">
            <h2 class="profile-body__heading">
              Trayectoria e Investigación
            </h2>

            <div v-if="author.biografia" class="profile-bio-content">
              <EditorJsContent :content="author.biografia" />
            </div>
            <p v-else class="profile-body__text">
              Docente e investigador comprometido con el avance de las ciencias estadísticas y metodológicas en Bolivia y la región. Sus contribuciones forman parte del acervo científico de la Revista Varianza y de las líneas de investigación activas en el IETA.
            </p>
          </div>

          <!-- Published Articles in Varianza -->
          <div class="profile-body__section">
            <h2 class="profile-body__heading">
              <span>Artículos Publicados en Revista Varianza</span>
              <span v-if="author.articulos?.length" class="articles-count-tag">
                {{ author.articulos.length }}
              </span>
            </h2>

            <div v-if="author.articulos?.length" class="profile-articles-grid">
              <article
                v-for="article in author.articulos"
                :key="article.id"
                class="article-minimal-card"
              >
                <span class="article-minimal-tag">{{ article.doi || 'Revista Varianza' }}</span>
                <h3 class="article-minimal-title">
                  <RouterLink :to="`/revista-varianza/articulos/${article.id}`">
                    {{ article.titulo }}
                  </RouterLink>
                </h3>
                <p v-if="article.resumen || article.abstracto" class="article-minimal-desc">
                  {{ article.resumen || article.abstracto }}
                </p>
                <div class="article-minimal-footer">
                  <RouterLink
                    class="article-minimal-link"
                    :to="`/revista-varianza/articulos/${article.id}`"
                  >
                    <span>Leer artículo</span>
                    <span aria-hidden="true">→</span>
                  </RouterLink>
                </div>
              </article>
            </div>

            <EmptyState
              v-else
              title="No hay artículos asociados en este momento"
              description="Las contribuciones de este autor aparecerán en próximas ediciones."
            />
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
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.articles-count-tag {
  font-family: inherit;
  font-size: 0.85rem;
  padding: 0.2rem 0.65rem;
  border-radius: 999px;
  background: #f30060;
  color: #ffffff;
  font-weight: 700;
}

.profile-body__text {
  margin: 0;
  font-size: 1.05rem;
  line-height: 1.75;
  color: #374151;
  max-width: 65ch;
}

.profile-articles-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1.5rem;
}

.article-minimal-card {
  display: flex;
  flex-direction: column;
  gap: 0.65rem;
  padding: 1.5rem;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  background: #ffffff;
  box-shadow: 0 2px 10px rgba(15, 23, 42, 0.03);
  transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}

.article-minimal-card:hover {
  transform: translateY(-3px);
  border-color: #35449f;
  box-shadow: 0 8px 24px -4px rgba(15, 23, 42, 0.08);
}

.article-minimal-tag {
  font-size: 0.75rem;
  font-weight: 700;
  color: #f30060;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.article-minimal-title {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 700;
  line-height: 1.35;
}

.article-minimal-title a {
  color: #111827;
  text-decoration: none;
  transition: color 0.15s ease;
}

.article-minimal-title a:hover {
  color: #35449f;
}

.article-minimal-desc {
  margin: 0;
  font-size: 0.9rem;
  color: #4b5563;
  line-height: 1.55;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.article-minimal-footer {
  margin-top: auto;
  padding-top: 0.75rem;
}

.article-minimal-link {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  color: #35449f;
  font-size: 0.88rem;
  font-weight: 700;
  text-decoration: none;
  transition: gap 0.15s ease, color 0.15s ease;
}

.article-minimal-link:hover {
  gap: 0.6rem;
  color: #f30060;
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

  .profile-articles-grid {
    grid-template-columns: 1fr;
  }

  .unified-profile-skeleton {
    grid-template-columns: 1fr;
  }
}
</style>
