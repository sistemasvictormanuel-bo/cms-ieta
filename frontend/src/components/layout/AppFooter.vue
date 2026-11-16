<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { Clock3, Mail, MapPin, Phone } from '@lucide/vue'
import { ContactoRepository } from '@/api/repositories.js'
import logoIeta from '@/assets/images/logoieta_400.png'
import type { InstitutionConfigApiItem } from '@/api/types'
import { mediaUrl } from '@/api/response'

const props = withDefaults(defineProps<{
  variant?: 'default' | 'editorial'
  institutionConfig?: InstitutionConfigApiItem | null
}>(), { variant: 'default', institutionConfig: null })
const footerLogo = computed(() => mediaUrl(props.institutionConfig?.logo_principal_url) || logoIeta)
const institutionName = computed(() => props.institutionConfig?.nombre_institucion || 'Instituto de Estadística Teórica y Aplicada')
const institutionCopyright = computed(() => props.institutionConfig?.copyright || `© ${new Date().getFullYear()} IETA — Instituto de Estadística Teórica y Aplicada. Todos los derechos reservados.`)

interface ContactInfo {
  telefonos: string[]
  dia_inicio: string
  dia_fin: string
  hora_apertura: string
  hora_cierre: string
  direccion: string
  emails: string[]
}

const contact = ref<ContactInfo | null>(null)

onMounted(async () => {
  try {
    const response = (await ContactoRepository.get()) as ContactInfo | { data: ContactInfo }
    contact.value = 'data' in response ? response.data : response
  } catch {
    contact.value = null
  }
})

function phoneHref(phone: string) {
  return `tel:${phone.replace(/[^+\d]/g, '')}`
}

function formatTime(value: string) {
  return value.slice(0, 5)
}
</script>

<template>
  <footer class="app-footer" :class="`app-footer--${variant}`" aria-labelledby="footer-heading">
    <h2 id="footer-heading" class="visually-hidden">Pie de página</h2>

    <div class="footer-body">
      <div class="container footer-body__inner">
        <!-- ── Brand e info institucional ──────────────────────── -->
        <div class="footer-brand">
          <RouterLink to="/" class="footer-brand__logo" aria-label="IETA — Ir al inicio">
            <img :src="footerLogo" :alt="institutionName" class="footer-brand__img" />
          </RouterLink>
          <div class="footer-brand__identity">
            <h3 class="footer-brand__name">{{ institutionName }}</h3>
            <p class="footer-brand__org">Universidad Mayor de San Andrés · Carrera de Estadística</p>
          </div>

          <address v-if="contact || institutionConfig" class="footer-contact">
            <a
              v-if="institutionConfig?.email_principal"
              :href="`mailto:${institutionConfig.email_principal}`"
              class="footer-contact__item"
            >
              <Mail class="footer-contact__icon" aria-hidden="true" />
              <span>{{ institutionConfig.email_principal }}</span>
            </a>
            <a
              v-else
              v-for="email in contact?.emails || []"
              :key="email"
              :href="`mailto:${email}`"
              class="footer-contact__item"
            >
              <Mail class="footer-contact__icon" aria-hidden="true" />
              <span>{{ email }}</span>
            </a>
            <a
              v-if="institutionConfig?.telefono_principal"
              :href="phoneHref(institutionConfig.telefono_principal)"
              class="footer-contact__item"
            >
              <Phone class="footer-contact__icon" aria-hidden="true" />
              <span>{{ institutionConfig.telefono_principal }}</span>
            </a>
            <a
              v-else
              v-for="phone in contact?.telefonos || []"
              :key="phone"
              :href="phoneHref(phone)"
              class="footer-contact__item"
            >
              <Phone class="footer-contact__icon" aria-hidden="true" />
              <span>{{ phone }}</span>
            </a>
            <p v-if="institutionConfig?.direccion_principal || contact?.direccion" class="footer-contact__item">
              <MapPin class="footer-contact__icon" aria-hidden="true" />
              <span>{{ institutionConfig?.direccion_principal || contact?.direccion }}</span>
            </p>
            <p v-if="contact?.dia_inicio && contact?.dia_fin" class="footer-contact__item">
              <Clock3 class="footer-contact__icon" aria-hidden="true" />
              <span>
                {{ contact.dia_inicio }} a {{ contact.dia_fin }},
                {{ formatTime(contact.hora_apertura) }}–{{ formatTime(contact.hora_cierre) }}
              </span>
            </p>
          </address>
          <address v-else class="footer-contact">
            <RouterLink to="/contacto" class="footer-contact__item">
              <MapPin class="footer-contact__icon" aria-hidden="true" />
              <span>Ver datos de contacto</span>
            </RouterLink>
          </address>
        </div>

        <!-- ── Columnas de navegación ───────────────────────────── -->
        <nav class="footer-nav" aria-label="Navegación del pie de página">
          <div class="footer-nav__col">
            <h3 class="footer-nav__title">Institución</h3>
            <ul class="footer-nav__list">
              <li><RouterLink to="/acerca-de">¿Quiénes somos?</RouterLink></li>
              <li><RouterLink to="/acerca-de/mision-vision">Misión y Visión</RouterLink></li>
              <li><RouterLink to="/acerca-de/personal">Personal IETA</RouterLink></li>
              <li><RouterLink to="/acerca-de/reglamento">Reglamento Interno</RouterLink></li>
              <li><RouterLink to="/contacto">Contacto</RouterLink></li>
            </ul>
          </div>

          <div class="footer-nav__col">
            <h3 class="footer-nav__title">Investigación</h3>
            <ul class="footer-nav__list">
              <li>
                <RouterLink to="/investigacion/lineas-investigacion"
                  >Líneas de Investigación</RouterLink
                >
              </li>
              <li><RouterLink to="/investigacion/memorias">Memorias</RouterLink></li>
              <li><RouterLink to="/revista-varianza">Revista Varianza</RouterLink></li>
              <li><RouterLink to="/revista-varianza/ediciones">Todas las Ediciones</RouterLink></li>
            </ul>
          </div>

          <div class="footer-nav__col">
            <h3 class="footer-nav__title">Servicios y Recursos</h3>
            <ul class="footer-nav__list">
              <li><RouterLink to="/servicios">Servicios</RouterLink></li>
              <li><RouterLink to="/noticias">Noticias</RouterLink></li>
              <li><RouterLink to="/recursos">Banco de Datos</RouterLink></li>
              <li>
                <RouterLink to="/recursos/revistas-de-estadistica"
                  >Revistas de Estadística</RouterLink
                >
              </li>
            </ul>
          </div>
        </nav>
      </div>
    </div>

    <!-- ── Barra inferior ─────────────────────────────────────── -->
    <div class="footer-bottom">
      <div class="container footer-bottom__inner">
        <p class="footer-bottom__copy">
          {{ institutionCopyright }}
        </p>
        <!-- Redes sociales — íconos funcionales con aria-label — Guía §9 -->
        <div class="footer-social" role="list" aria-label="Redes sociales">
          <a
            v-if="!institutionConfig || institutionConfig.redes_sociales?.facebook"
            :href="institutionConfig?.redes_sociales?.facebook || 'https://facebook.com'"
            target="_blank"
            rel="noopener noreferrer"
            class="footer-social__link"
            role="listitem"
            aria-label="IETA en Facebook (abre en nueva pestaña)"
          >
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
              <path
                d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"
              />
            </svg>
          </a>
          <a
            v-if="!institutionConfig || institutionConfig.redes_sociales?.twitter"
            :href="institutionConfig?.redes_sociales?.twitter || 'https://twitter.com'"
            target="_blank"
            rel="noopener noreferrer"
            class="footer-social__link"
            role="listitem"
            aria-label="IETA en X / Twitter (abre en nueva pestaña)"
          >
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
              <path
                d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"
              />
            </svg>
          </a>
          <a
            v-if="!institutionConfig || institutionConfig.redes_sociales?.youtube"
            :href="institutionConfig?.redes_sociales?.youtube || 'https://youtube.com'"
            target="_blank"
            rel="noopener noreferrer"
            class="footer-social__link"
            role="listitem"
            aria-label="IETA en YouTube (abre en nueva pestaña)"
          >
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
              <path
                d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"
              />
            </svg>
          </a>
          <a
            v-if="institutionConfig?.redes_sociales?.linkedin"
            :href="institutionConfig.redes_sociales.linkedin"
            target="_blank"
            rel="noopener noreferrer"
            class="footer-social__link footer-social__text-link"
            role="listitem"
            aria-label="IETA en LinkedIn (abre en nueva pestaña)"
          >in</a>
          <a
            v-if="institutionConfig?.redes_sociales?.instagram"
            :href="institutionConfig.redes_sociales.instagram"
            target="_blank"
            rel="noopener noreferrer"
            class="footer-social__link footer-social__text-link"
            role="listitem"
            aria-label="IETA en Instagram (abre en nueva pestaña)"
          >ig</a>
        </div>
      </div>
    </div>
  </footer>
</template>

<style scoped>
/* ── Visually hidden (accesibilidad) ─────────────────────────── */
.visually-hidden {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}

/* ── Footer base ─────────────────────────────────────────────── */
.app-footer {
  position: relative;
  background: var(--color-tinta, #14161c);
  color: var(--color-blanco);
  margin-top: auto;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}


/* ── Body del footer ─────────────────────────────────────────── */
.footer-body {
  padding-block: clamp(2.5rem, 5vw, 4rem) clamp(2rem, 4vw, 3rem);
}

.footer-body__inner {
  display: grid;
  grid-template-columns: minmax(280px, 340px) 1fr;
  gap: clamp(2rem, 5vw, 4.5rem);
  align-items: start;
}

/* ── Brand ───────────────────────────────────────────────────── */
.footer-brand {
  display: flex;
  flex-direction: column;
}

.footer-brand__logo {
  display: inline-flex;
  align-items: center;
  margin-bottom: 0.85rem;
  text-decoration: none;
}

.footer-brand__img {
  width: clamp(3.5rem, 6vw, 4.75rem);
  height: clamp(3.5rem, 6vw, 4.75rem);
  object-fit: contain;
  filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.25));
  transition: transform 0.25s ease;
}

.footer-brand__logo:hover .footer-brand__img {
  transform: scale(1.05);
}

.footer-brand__identity {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.footer-brand__name {
  font-size: clamp(0.95rem, 1.8vw, 1.15rem);
  font-weight: 700;
  color: #ffffff;
  line-height: 1.35;
  margin: 0;
  letter-spacing: -0.01em;
}

.footer-brand__org {
  font-size: clamp(0.78rem, 1.4vw, 0.85rem);
  color: #9aa8cc;
  margin: 0;
  font-weight: 500;
  line-height: 1.4;
}

/* ── Contacto ────────────────────────────────────────────────── */
.footer-contact {
  display: flex;
  flex-direction: column;
  gap: 0.65rem;
  margin-top: 1.35rem;
  font-style: normal;
}

.footer-contact__item {
  display: inline-flex;
  align-items: flex-start;
  gap: 0.65rem;
  color: #b0bfdf;
  text-decoration: none;
  font-size: clamp(0.82rem, 1.4vw, 0.88rem);
  line-height: 1.45;
  transition: color 0.18s ease, transform 0.18s ease;
  word-break: break-word;
}

.footer-contact__item:hover {
  color: #ffffff;
  transform: translateX(3px);
}

.footer-contact__icon {
  width: 17px;
  height: 17px;
  flex-shrink: 0;
  margin-top: 2px;
  color: #f30060;
}

/* ── Nav columnas ────────────────────────────────────────────── */
.footer-nav {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: clamp(1.5rem, 3.5vw, 2.75rem);
}

.footer-nav__col {
  display: flex;
  flex-direction: column;
}

.footer-nav__title {
  position: relative;
  font-size: clamp(0.78rem, 1.3vw, 0.85rem);
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #ffffff;
  margin: 0 0 1.15rem 0;
  padding-bottom: 0.5rem;
}

.footer-nav__title::after {
  content: '';
  position: absolute;
  left: 0;
  bottom: 0;
  width: 22px;
  height: 2px;
  background: #f30060;
  border-radius: 2px;
}

.footer-nav__list {
  display: flex;
  flex-direction: column;
  gap: 0.65rem;
  list-style: none;
  padding: 0;
  margin: 0;
}

.footer-nav__list a {
  display: inline-flex;
  align-items: center;
  color: #a4b2d5;
  text-decoration: none;
  font-size: clamp(0.84rem, 1.4vw, 0.9rem);
  line-height: 1.45;
  transition: color 0.18s ease, transform 0.18s ease;
}

.footer-nav__list a:hover {
  color: #ffffff;
  transform: translateX(4px);
}

.footer-nav__list a:focus-visible {
  outline: 2px solid #f30060;
  outline-offset: 3px;
  border-radius: 4px;
}

/* ── Bottom bar ──────────────────────────────────────────────── */
.footer-bottom {
  padding-block: clamp(1.1rem, 2.5vw, 1.4rem);
  background: rgba(0, 0, 0, 0.22);
}

.footer-bottom__inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1.25rem;
  flex-wrap: wrap;
}

.footer-bottom__copy {
  margin: 0;
  font-size: clamp(0.75rem, 1.2vw, 0.84rem);
  color: #8391b4;
  line-height: 1.5;
}

/* ── Redes sociales ──────────────────────────────────────────── */
.footer-social {
  display: flex;
  align-items: center;
  gap: 0.65rem;
}

.footer-social__link {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #b0bfdf;
  text-decoration: none;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.footer-social__link:hover {
  background: #f30060;
  border-color: #f30060;
  color: #ffffff;
  transform: translateY(-2px);
  box-shadow: 0 4px 14px rgba(243, 0, 96, 0.35);
}

.footer-social__link:focus-visible {
  outline: 2px solid #f30060;
  outline-offset: 3px;
}

.footer-social__link svg {
  width: 17px;
  height: 17px;
}

.footer-social__text-link {
  font-size: 0.75rem;
  font-weight: 800;
}

/* ── Variante Editorial ──────────────────────────────────────── */
.app-footer--editorial {
  background: #fff;
  color: #14161c;
  border-top: 1px solid #eaedf6;
}

.app-footer--editorial::before {
  display: none;
}

.app-footer--editorial .footer-body {
  padding-block: var(--space-2) 0;
}

.app-footer--editorial .footer-body__inner {
  grid-template-columns: 1fr;
  justify-items: center;
  gap: var(--space-1);
  text-align: center;
}

.app-footer--editorial .footer-brand {
  align-items: center;
  gap: 0.25rem;
}

.app-footer--editorial .footer-brand__logo {
  width: 4rem;
  height: 4rem;
  margin: 0;
}

.app-footer--editorial .footer-brand__img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.app-footer--editorial .footer-brand__name {
  color: #14161c;
  font-size: 0.95rem;
  font-weight: 700;
}

.app-footer--editorial .footer-brand__org,
.app-footer--editorial .footer-nav {
  display: none;
}

.app-footer--editorial .footer-contact {
  align-items: center;
  gap: 0.1rem;
  margin-top: 0.25rem;
}

.app-footer--editorial .footer-contact__item {
  justify-content: center;
  color: #333;
  font-size: 0.75rem;
  line-height: 1.35;
}

.app-footer--editorial .footer-contact__icon {
  display: none;
}

.app-footer--editorial .footer-bottom {
  padding-block: var(--space-1) var(--space-2);
  border-top: 0;
  background: #fff;
}

.app-footer--editorial .footer-bottom__inner {
  flex-direction: column;
  justify-content: center;
  gap: 0.3rem;
  text-align: center;
}

.app-footer--editorial .footer-social {
  order: -1;
  gap: 0.5rem;
}

.app-footer--editorial .footer-social__link {
  width: 2rem;
  height: 2rem;
  background: transparent;
  color: #49328d;
}

.app-footer--editorial .footer-social__link:hover {
  background: #f4e8ef;
  color: #f50088;
}

.app-footer--editorial .footer-bottom__copy {
  color: #555;
  font-size: 0.6875rem;
}

/* ── Breakpoints Responsive ──────────────────────────────────── */
@media (max-width: 1024px) {
  .footer-body__inner {
    grid-template-columns: 1fr;
    gap: 2.75rem;
  }
  .footer-brand {
    max-width: 44rem;
  }
  .footer-nav {
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 2rem;
  }
}

@media (max-width: 768px) {
  .footer-body {
    padding-block: 2.25rem 1.75rem;
  }
  .footer-body__inner {
    gap: 2.25rem;
  }
  .footer-nav {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1.75rem 1.5rem;
  }
  .footer-bottom__inner {
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 1rem;
  }
  .footer-social {
    order: -1;
  }
}

@media (max-width: 480px) {
  .footer-brand__img {
    width: 3.25rem;
    height: 3.25rem;
  }
  .footer-brand__name {
    font-size: 0.95rem;
  }
  .footer-nav {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }
  .footer-nav__title {
    margin-bottom: 0.75rem;
  }
  .footer-contact {
    gap: 0.6rem;
  }
  .footer-contact__item {
    font-size: 0.82rem;
  }
  .footer-bottom__copy {
    font-size: 0.75rem;
  }
}
</style>
