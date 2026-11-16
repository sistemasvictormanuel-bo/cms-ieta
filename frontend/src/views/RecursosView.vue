<script setup lang="ts">
import { computed, nextTick, onMounted, ref, watch } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import {
  ArrowRight,
  BookOpen,
  Calendar,
  Database,
  Download,
  ExternalLink,
  Eye,
  FileSpreadsheet,
  MapPin,
  RotateCcw,
  Search,
  SlidersHorizontal,
  Tag,
  X,
} from '@lucide/vue'
import EmptyState from '@/components/common/EmptyState.vue'
import LoadMoreButton from '@/components/common/LoadMoreButton.vue'
import TabNav from '@/components/common/TabNav.vue'
import SectionHeaderBand from '@/components/layout/SectionHeaderBand.vue'
import { useI18n } from 'vue-i18n'
import { mediaUrl, unwrapCollection } from '@/api/response'
import {
  AreasTematicasRepository,
  BancoDatosRepository,
  RevistasVariasRepository,
} from '@/api/repositories.js'
import type { DataAreaApiItem, DataBankApiItem, RevistasVariasApiItem } from '@/api/types'
import { usePaginatedResource } from '@/composables/usePaginatedResource'
import { useGsapReveal } from '@/composables/useGsapReveal'

const { t } = useI18n()
const route = useRoute()
const router = useRouter()
const resourcesRoot = ref<HTMLElement | null>(null)

// Fallback data when backend is offline or loading
const fallbackDataBanks: DataBankApiItem[] = [
  {
    id: 81,
    tipo_dato: 'Encuestas',
    anio: 2011,
    codigo: 'eh11gna',
    titulo: 'INE - Encuesta a Hogares 2011 - Gasto no alimentario',
    descripcion: 'Microdatos del módulo de gasto no alimentario de la Encuesta a Hogares 2011 del Instituto Nacional de Estadística (INE) de Bolivia.',
    locacion: 'Bolivia',
    url: 'https://drive.google.com/file/d/1Lk7lZcchY-dYYCjqrPo5yV77Yng88WZX/view?usp=sharing',
    bdd_url: 'https://drive.google.com/file/d/1Lk7lZcchY-dYYCjqrPo5yV77Yng88WZX/view?usp=sharing',
    archivo_url: null,
    portada_url: null,
    areas_tematicas: [
      { id: 21, nombre: 'Gasto de Hogares', descripcion: 'Área temática agregada automáticamente.', created_at: '', updated_at: '' },
      { id: 23, nombre: 'Consumo', descripcion: 'Área temática agregada automáticamente.', created_at: '', updated_at: '' },
    ],
    created_at: '2026-10-19T20:18:36+00:00',
    updated_at: '2026-10-19T20:18:36+00:00',
  },
  {
    id: 80,
    tipo_dato: 'Encuestas',
    anio: 2011,
    codigo: 'eh11ga',
    titulo: 'INE - Encuesta a Hogares 2011 - Gasto alimentario',
    descripcion: 'Microdatos del módulo de gasto alimentario de la Encuesta a Hogares 2011 del Instituto Nacional de Estadística (INE) de Bolivia.',
    locacion: 'Bolivia',
    url: 'https://drive.google.com/file/d/1ZBGUaBlHevDeEb7GzlcD2ajlkkokFt3U/view?usp=sharing',
    bdd_url: 'https://drive.google.com/file/d/1ZBGUaBlHevDeEb7GzlcD2ajlkkokFt3U/view?usp=sharing',
    archivo_url: null,
    portada_url: null,
    areas_tematicas: [
      { id: 21, nombre: 'Gasto de Hogares', descripcion: 'Área temática agregada automáticamente.', created_at: '', updated_at: '' },
      { id: 22, nombre: 'Alimentación', descripcion: 'Área temática agregada automáticamente.', created_at: '', updated_at: '' },
      { id: 23, nombre: 'Consumo', descripcion: 'Área temática agregada automáticamente.', created_at: '', updated_at: '' },
    ],
    created_at: '2026-10-19T20:18:36+00:00',
    updated_at: '2026-10-19T20:18:36+00:00',
  },
  {
    id: 79,
    tipo_dato: 'Encuestas',
    anio: 2011,
    codigo: 'eh11e',
    titulo: 'INE - Encuesta a Hogares 2011 - Equipamiento',
    descripcion: 'Microdatos del módulo de equipamiento del hogar de la Encuesta a Hogares 2011 del Instituto Nacional de Estadística (INE) de Bolivia.',
    locacion: 'Bolivia',
    url: 'https://drive.google.com/file/d/11L3xjG8s_sr1zDXHW41sjT3sYZHxGQL0/view?usp=sharing',
    bdd_url: 'https://drive.google.com/file/d/11L3xjG8s_sr1zDXHW41sjT3sYZHxGQL0/view?usp=sharing',
    archivo_url: null,
    portada_url: null,
    areas_tematicas: [
      { id: 3, nombre: 'Vivienda', descripcion: null, created_at: '', updated_at: '' },
      { id: 20, nombre: 'Equipamiento del Hogar', descripcion: 'Área temática agregada automáticamente.', created_at: '', updated_at: '' },
      { id: 11, nombre: 'Condiciones de Vida', descripcion: 'Área temática agregada automáticamente.', created_at: '', updated_at: '' },
    ],
    created_at: '2026-10-19T20:18:36+00:00',
    updated_at: '2026-10-19T20:18:36+00:00',
  },
  {
    id: 78,
    tipo_dato: 'Encuestas',
    anio: 2011,
    codigo: 'eh11v',
    titulo: 'INE - Encuesta a Hogares 2011 - Vivienda',
    descripcion: 'Microdatos del módulo de vivienda de la Encuesta a Hogares 2011 del Instituto Nacional de Estadística (INE) de Bolivia.',
    locacion: 'Bolivia',
    url: 'https://drive.google.com/file/d/1LQDkP0CBvCK_kOthbLq8eT_NFR7y6i6D/view?usp=sharing',
    bdd_url: 'https://drive.google.com/file/d/1LQDkP0CBvCK_kOthbLq8eT_NFR7y6i6D/view?usp=sharing',
    archivo_url: null,
    portada_url: null,
    areas_tematicas: [
      { id: 3, nombre: 'Vivienda', descripcion: null, created_at: '', updated_at: '' },
      { id: 7, nombre: 'Servicios Básicos', descripcion: 'Área temática agregada automáticamente.', created_at: '', updated_at: '' },
      { id: 11, nombre: 'Condiciones de Vida', descripcion: 'Área temática agregada automáticamente.', created_at: '', updated_at: '' },
    ],
    created_at: '2026-10-19T20:18:36+00:00',
    updated_at: '2026-10-19T20:18:36+00:00',
  },
  {
    id: 77,
    tipo_dato: 'Encuestas',
    anio: 2011,
    codigo: 'eh11p',
    titulo: 'INE - Encuesta a Hogares 2011 - Personas',
    descripcion: 'Microdatos del módulo de personas de la Encuesta a Hogares 2011 del Instituto Nacional de Estadística (INE) de Bolivia.',
    locacion: 'Bolivia',
    url: 'https://drive.google.com/file/d/155GuvpnH_4ffv9QuB0ZbWUDZ_Jr--lhu/view?usp=sharing',
    bdd_url: 'https://drive.google.com/file/d/155GuvpnH_4ffv9QuB0ZbWUDZ_Jr--lhu/view?usp=sharing',
    archivo_url: null,
    portada_url: null,
    areas_tematicas: [
      { id: 5, nombre: 'Demografía', descripcion: '', created_at: '', updated_at: '' },
      { id: 9, nombre: 'Empleo', descripcion: '', created_at: '', updated_at: '' },
      { id: 10, nombre: 'Ingresos', descripcion: '', created_at: '', updated_at: '' },
      { id: 18, nombre: 'Educación', descripcion: '', created_at: '', updated_at: '' },
      { id: 19, nombre: 'Salud', descripcion: '', created_at: '', updated_at: '' },
    ],
    created_at: '2026-10-19T20:18:36+00:00',
    updated_at: '2026-10-19T20:18:36+00:00',
  },
  {
    id: 76,
    tipo_dato: 'Encuestas',
    anio: 2012,
    codigo: 'eh12gna',
    titulo: 'INE - Encuesta a Hogares 2012 - Gasto no alimentario',
    descripcion: 'Microdatos del módulo de gasto no alimentario de la Encuesta a Hogares 2012 del Instituto Nacional de Estadística (INE) de Bolivia.',
    locacion: 'Bolivia',
    url: 'https://drive.google.com/file/d/1Qvq_Xf2YORpHV8v60oeDXWiCk8Fn2K3S/view?usp=sharing',
    bdd_url: 'https://drive.google.com/file/d/1Qvq_Xf2YORpHV8v60oeDXWiCk8Fn2K3S/view?usp=sharing',
    archivo_url: null,
    portada_url: null,
    areas_tematicas: [
      { id: 21, nombre: 'Gasto de Hogares', descripcion: '', created_at: '', updated_at: '' },
      { id: 23, nombre: 'Consumo', descripcion: '', created_at: '', updated_at: '' },
    ],
    created_at: '2026-10-19T20:18:36+00:00',
    updated_at: '2026-10-19T20:18:36+00:00',
  },
  {
    id: 75,
    tipo_dato: 'Encuestas',
    anio: 2012,
    codigo: 'eh12ga',
    titulo: 'INE - Encuesta a Hogares 2012 - Gasto alimentario',
    descripcion: 'Microdatos del módulo de gasto alimentario de la Encuesta a Hogares 2012 del Instituto Nacional de Estadística (INE) de Bolivia.',
    locacion: 'Bolivia',
    url: 'https://drive.google.com/file/d/1wZqC4GqxB6VQCmvaF3zYvqk05h4G9ejp/view?usp=sharing',
    bdd_url: 'https://drive.google.com/file/d/1wZqC4GqxB6VQCmvaF3zYvqk05h4G9ejp/view?usp=sharing',
    archivo_url: null,
    portada_url: null,
    areas_tematicas: [
      { id: 21, nombre: 'Gasto de Hogares', descripcion: '', created_at: '', updated_at: '' },
      { id: 22, nombre: 'Alimentación', descripcion: '', created_at: '', updated_at: '' },
      { id: 23, nombre: 'Consumo', descripcion: '', created_at: '', updated_at: '' },
    ],
    created_at: '2026-10-19T20:18:36+00:00',
    updated_at: '2026-10-19T20:18:36+00:00',
  },
  {
    id: 74,
    tipo_dato: 'Encuestas',
    anio: 2012,
    codigo: 'eh12e',
    titulo: 'INE - Encuesta a Hogares 2012 - Equipamiento',
    descripcion: 'Microdatos del módulo de equipamiento del hogar de la Encuesta a Hogares 2012 del Instituto Nacional de Estadística (INE) de Bolivia.',
    locacion: 'Bolivia',
    url: 'https://drive.google.com/file/d/1SmV9MyDye98i_MR1SAaRjyiGCdWRKYcN/view?usp=sharing',
    bdd_url: 'https://drive.google.com/file/d/1SmV9MyDye98i_MR1SAaRjyiGCdWRKYcN/view?usp=sharing',
    archivo_url: null,
    portada_url: null,
    areas_tematicas: [
      { id: 3, nombre: 'Vivienda', descripcion: null, created_at: '', updated_at: '' },
      { id: 20, nombre: 'Equipamiento del Hogar', descripcion: '', created_at: '', updated_at: '' },
      { id: 11, nombre: 'Condiciones de Vida', descripcion: '', created_at: '', updated_at: '' },
    ],
    created_at: '2026-10-19T20:18:36+00:00',
    updated_at: '2026-10-19T20:18:36+00:00',
  },
  {
    id: 73,
    tipo_dato: 'Encuestas',
    anio: 2012,
    codigo: 'eh12v',
    titulo: 'INE - Encuesta a Hogares 2012 - Vivienda',
    descripcion: 'Microdatos del módulo de vivienda de la Encuesta a Hogares 2012 del Instituto Nacional de Estadística (INE) de Bolivia.',
    locacion: 'Bolivia',
    url: 'https://drive.google.com/file/d/1gChpLzxmJ9d6o6a1adfyl89O54PclMIM/view?usp=sharing',
    bdd_url: 'https://drive.google.com/file/d/1gChpLzxmJ9d6o6a1adfyl89O54PclMIM/view?usp=sharing',
    archivo_url: null,
    portada_url: null,
    areas_tematicas: [
      { id: 3, nombre: 'Vivienda', descripcion: null, created_at: '', updated_at: '' },
      { id: 7, nombre: 'Servicios Básicos', descripcion: '', created_at: '', updated_at: '' },
      { id: 11, nombre: 'Condiciones de Vida', descripcion: '', created_at: '', updated_at: '' },
    ],
    created_at: '2026-10-19T20:18:36+00:00',
    updated_at: '2026-10-19T20:18:36+00:00',
  },
  {
    id: 72,
    tipo_dato: 'Encuestas',
    anio: 2012,
    codigo: 'eh12p',
    titulo: 'INE - Encuesta a Hogares 2012 - Personas',
    descripcion: 'Microdatos del módulo de personas de la Encuesta a Hogares 2012 del Instituto Nacional de Estadística (INE) de Bolivia.',
    locacion: 'Bolivia',
    url: 'https://drive.google.com/file/d/154QaEkOeOrjL987pfDzsRtireRr1EKas/view?usp=sharing',
    bdd_url: 'https://drive.google.com/file/d/154QaEkOeOrjL987pfDzsRtireRr1EKas/view?usp=sharing',
    archivo_url: null,
    portada_url: null,
    areas_tematicas: [
      { id: 5, nombre: 'Demografía', descripcion: '', created_at: '', updated_at: '' },
      { id: 9, nombre: 'Empleo', descripcion: '', created_at: '', updated_at: '' },
      { id: 10, nombre: 'Ingresos', descripcion: '', created_at: '', updated_at: '' },
      { id: 18, nombre: 'Educación', descripcion: '', created_at: '', updated_at: '' },
      { id: 19, nombre: 'Salud', descripcion: '', created_at: '', updated_at: '' },
    ],
    created_at: '2026-10-19T20:18:36+00:00',
    updated_at: '2026-10-19T20:18:36+00:00',
  },
  {
    id: 71,
    tipo_dato: 'Encuestas',
    anio: 2013,
    codigo: 'eh13gna',
    titulo: 'INE - Encuesta a Hogares 2013 - Gasto no alimentario',
    descripcion: 'Microdatos del módulo de gasto no alimentario de la Encuesta a Hogares 2013 del Instituto Nacional de Estadística (INE) de Bolivia.',
    locacion: 'Bolivia',
    url: 'https://drive.google.com/file/d/1KiQ8f8YdBJsq-7pt8kDmCuDrkyFmi6xA/view?usp=sharing',
    bdd_url: 'https://drive.google.com/file/d/1KiQ8f8YdBJsq-7pt8kDmCuDrkyFmi6xA/view?usp=sharing',
    archivo_url: null,
    portada_url: null,
    areas_tematicas: [
      { id: 21, nombre: 'Gasto de Hogares', descripcion: '', created_at: '', updated_at: '' },
      { id: 23, nombre: 'Consumo', descripcion: '', created_at: '', updated_at: '' },
    ],
    created_at: '2026-10-19T20:18:36+00:00',
    updated_at: '2026-10-19T20:18:36+00:00',
  },
  {
    id: 70,
    tipo_dato: 'Encuestas',
    anio: 2013,
    codigo: 'eh13ga',
    titulo: 'INE - Encuesta a Hogares 2013 - Gasto alimentario',
    descripcion: 'Microdatos del módulo de gasto alimentario de la Encuesta a Hogares 2013 del Instituto Nacional de Estadística (INE) de Bolivia.',
    locacion: 'Bolivia',
    url: 'https://drive.google.com/file/d/16IvDvSynlRNkbZmIHcZP_ZSloIet5tfy/view?usp=sharing',
    bdd_url: 'https://drive.google.com/file/d/16IvDvSynlRNkbZmIHcZP_ZSloIet5tfy/view?usp=sharing',
    archivo_url: null,
    portada_url: null,
    areas_tematicas: [
      { id: 21, nombre: 'Gasto de Hogares', descripcion: '', created_at: '', updated_at: '' },
      { id: 22, nombre: 'Alimentación', descripcion: '', created_at: '', updated_at: '' },
      { id: 23, nombre: 'Consumo', descripcion: '', created_at: '', updated_at: '' },
    ],
    created_at: '2026-10-19T20:18:36+00:00',
    updated_at: '2026-10-19T20:18:36+00:00',
  },
  {
    id: 69,
    tipo_dato: 'Encuestas',
    anio: 2013,
    codigo: 'eh13e',
    titulo: 'INE - Encuesta a Hogares 2013 - Equipamiento',
    descripcion: 'Microdatos del módulo de equipamiento del hogar de la Encuesta a Hogares 2013 del Instituto Nacional de Estadística (INE) de Bolivia.',
    locacion: 'Bolivia',
    url: 'https://drive.google.com/file/d/17VAyC2fsr5c-pXphdSMFQGBd0cBHSYT8/view?usp=sharing',
    bdd_url: 'https://drive.google.com/file/d/17VAyC2fsr5c-pXphdSMFQGBd0cBHSYT8/view?usp=sharing',
    archivo_url: null,
    portada_url: null,
    areas_tematicas: [
      { id: 3, nombre: 'Vivienda', descripcion: null, created_at: '', updated_at: '' },
      { id: 20, nombre: 'Equipamiento del Hogar', descripcion: '', created_at: '', updated_at: '' },
      { id: 11, nombre: 'Condiciones de Vida', descripcion: '', created_at: '', updated_at: '' },
    ],
    created_at: '2026-10-19T20:18:36+00:00',
    updated_at: '2026-10-19T20:18:36+00:00',
  },
  {
    id: 68,
    tipo_dato: 'Encuestas',
    anio: 2013,
    codigo: 'eh13v',
    titulo: 'INE - Encuesta a Hogares 2013 - Vivienda',
    descripcion: 'Microdatos del módulo de vivienda de la Encuesta a Hogares 2013 del Instituto Nacional de Estadística (INE) de Bolivia.',
    locacion: 'Bolivia',
    url: 'https://drive.google.com/file/d/1bn5vQAQhLM-aQzd-_HDcf7k93Aw5xshv/view?usp=sharing',
    bdd_url: 'https://drive.google.com/file/d/1bn5vQAQhLM-aQzd-_HDcf7k93Aw5xshv/view?usp=sharing',
    archivo_url: null,
    portada_url: null,
    areas_tematicas: [
      { id: 3, nombre: 'Vivienda', descripcion: null, created_at: '', updated_at: '' },
      { id: 7, nombre: 'Servicios Básicos', descripcion: '', created_at: '', updated_at: '' },
      { id: 11, nombre: 'Condiciones de Vida', descripcion: '', created_at: '', updated_at: '' },
    ],
    created_at: '2026-10-19T20:18:36+00:00',
    updated_at: '2026-10-19T20:18:36+00:00',
  },
  {
    id: 67,
    tipo_dato: 'Encuestas',
    anio: 2013,
    codigo: 'eh13p',
    titulo: 'INE - Encuesta a Hogares 2013 - Personas',
    descripcion: 'Microdatos del módulo de personas de la Encuesta a Hogares 2013 del Instituto Nacional de Estadística (INE) de Bolivia.',
    locacion: 'Bolivia',
    url: 'https://drive.google.com/file/d/1HUQWQi38Hv8i-RZGULUrrhwlGq5fS99y/view?usp=sharing',
    bdd_url: 'https://drive.google.com/file/d/1HUQWQi38Hv8i-RZGULUrrhwlGq5fS99y/view?usp=sharing',
    archivo_url: null,
    portada_url: null,
    areas_tematicas: [
      { id: 5, nombre: 'Demografía', descripcion: '', created_at: '', updated_at: '' },
      { id: 9, nombre: 'Empleo', descripcion: '', created_at: '', updated_at: '' },
      { id: 10, nombre: 'Ingresos', descripcion: '', created_at: '', updated_at: '' },
      { id: 18, nombre: 'Educación', descripcion: '', created_at: '', updated_at: '' },
      { id: 19, nombre: 'Salud', descripcion: '', created_at: '', updated_at: '' },
    ],
    created_at: '2026-10-19T20:18:36+00:00',
    updated_at: '2026-10-19T20:18:36+00:00',
  },
  // CENSOS
  {
    id: 66,
    tipo_dato: 'Censos',
    anio: 2012,
    codigo: 'cnpv12',
    titulo: 'INE - Censo Nacional de Población y Vivienda 2012',
    descripcion: 'Base de datos y microdatos del Censo Nacional de Población y Vivienda 2012 llevado a cabo por el Instituto Nacional de Estadística (INE) de Bolivia.',
    locacion: 'Bolivia',
    url: 'https://drive.google.com/file/d/1Lk7lZcchY-dYYCjqrPo5yV77Yng88WZX/view?usp=sharing',
    bdd_url: 'https://drive.google.com/file/d/1Lk7lZcchY-dYYCjqrPo5yV77Yng88WZX/view?usp=sharing',
    archivo_url: null,
    portada_url: null,
    areas_tematicas: [
      { id: 5, nombre: 'Demografía', descripcion: '', created_at: '', updated_at: '' },
      { id: 3, nombre: 'Vivienda', descripcion: null, created_at: '', updated_at: '' },
      { id: 11, nombre: 'Condiciones de Vida', descripcion: '', created_at: '', updated_at: '' },
    ],
    created_at: '2026-10-19T20:18:36+00:00',
    updated_at: '2026-10-19T20:18:36+00:00',
  },
  {
    id: 65,
    tipo_dato: 'Censos',
    anio: 2013,
    codigo: 'ca13',
    titulo: 'INE - Censo Agropecuario 2013',
    descripcion: 'Microdatos y resultados del Censo Agropecuario Nacional 2013 del Estado Plurinacional de Bolivia.',
    locacion: 'Bolivia',
    url: 'https://drive.google.com/file/d/1Lk7lZcchY-dYYCjqrPo5yV77Yng88WZX/view?usp=sharing',
    bdd_url: 'https://drive.google.com/file/d/1Lk7lZcchY-dYYCjqrPo5yV77Yng88WZX/view?usp=sharing',
    archivo_url: null,
    portada_url: null,
    areas_tematicas: [
      { id: 30, nombre: 'Producción Agropecuaria', descripcion: '', created_at: '', updated_at: '' },
      { id: 5, nombre: 'Demografía', descripcion: '', created_at: '', updated_at: '' },
    ],
    created_at: '2026-10-19T20:18:36+00:00',
    updated_at: '2026-10-19T20:18:36+00:00',
  },
  {
    id: 64,
    tipo_dato: 'Censos',
    anio: 2014,
    codigo: 'cee14',
    titulo: 'INE - Censo de Establecimientos Económicos 2014',
    descripcion: 'Directorio y microdatos del Censo de Establecimientos Económicos desarrollado por el INE.',
    locacion: 'Bolivia',
    url: 'https://drive.google.com/file/d/1Lk7lZcchY-dYYCjqrPo5yV77Yng88WZX/view?usp=sharing',
    bdd_url: 'https://drive.google.com/file/d/1Lk7lZcchY-dYYCjqrPo5yV77Yng88WZX/view?usp=sharing',
    archivo_url: null,
    portada_url: null,
    areas_tematicas: [
      { id: 9, nombre: 'Empleo', descripcion: '', created_at: '', updated_at: '' },
      { id: 10, nombre: 'Ingresos', descripcion: '', created_at: '', updated_at: '' },
    ],
    created_at: '2026-10-19T20:18:36+00:00',
    updated_at: '2026-10-19T20:18:36+00:00',
  },
  {
    id: 63,
    tipo_dato: 'Censos',
    anio: 2001,
    codigo: 'cnpv01',
    titulo: 'INE - Censo de Población y Vivienda 2001',
    descripcion: 'Microdatos censales correspondientes al Censo Nacional de Población y Vivienda del año 2001.',
    locacion: 'Bolivia',
    url: 'https://drive.google.com/file/d/1Lk7lZcchY-dYYCjqrPo5yV77Yng88WZX/view?usp=sharing',
    bdd_url: 'https://drive.google.com/file/d/1Lk7lZcchY-dYYCjqrPo5yV77Yng88WZX/view?usp=sharing',
    archivo_url: null,
    portada_url: null,
    areas_tematicas: [
      { id: 5, nombre: 'Demografía', descripcion: '', created_at: '', updated_at: '' },
      { id: 3, nombre: 'Vivienda', descripcion: null, created_at: '', updated_at: '' },
    ],
    created_at: '2026-10-19T20:18:36+00:00',
    updated_at: '2026-10-19T20:18:36+00:00',
  },
  {
    id: 62,
    tipo_dato: 'Censos',
    anio: 2024,
    codigo: 'cnpv24',
    titulo: 'INE - Censo de Población y Vivienda 2024 - Resultados Preliminares',
    descripcion: 'Conteos preliminares y tabulaciones poblacionales del Censo Nacional de Población y Vivienda 2024.',
    locacion: 'Bolivia',
    url: 'https://drive.google.com/file/d/1Lk7lZcchY-dYYCjqrPo5yV77Yng88WZX/view?usp=sharing',
    bdd_url: 'https://drive.google.com/file/d/1Lk7lZcchY-dYYCjqrPo5yV77Yng88WZX/view?usp=sharing',
    archivo_url: null,
    portada_url: null,
    areas_tematicas: [
      { id: 5, nombre: 'Demografía', descripcion: '', created_at: '', updated_at: '' },
      { id: 11, nombre: 'Condiciones de Vida', descripcion: '', created_at: '', updated_at: '' },
    ],
    created_at: '2026-10-19T20:18:36+00:00',
    updated_at: '2026-10-19T20:18:36+00:00',
  },
]

// Banco de Datos filters
const query = ref('')
const selectedYear = ref('')
const selectedArea = ref('')
const selectedLocation = ref('')
const activeTypeFilter = ref<string>('all') // 'all' | 'Encuestas' | 'Censos'
const sortBy = ref<string>('recent') // 'recent' | 'oldest' | 'title-asc' | 'title-desc'

// Total counts in database
const totalRecursos = ref<number | null>(79)
const totalEncuestas = ref<number | null>(65)
const totalCensos = ref<number | null>(14)

function isCenso(tipo?: string | null, titulo?: string): boolean {
  const t = (tipo || '').toLowerCase()
  const title = (titulo || '').toLowerCase()
  return t.includes('censo') || title.includes('censo')
}

function isEncuesta(tipo?: string | null, titulo?: string): boolean {
  const t = (tipo || '').toLowerCase()
  const title = (titulo || '').toLowerCase()
  return t.includes('encuesta') || title.includes('encuesta')
}

// Paginated resources with autoFetchAll to ensure all historical datasets (2015-present) are in memory
const banks = usePaginatedResource<DataBankApiItem>(
  (page) => BancoDatosRepository.list({ page, per_page: 100 }),
  { autoFetchAll: true },
)

const journalsResource = usePaginatedResource<RevistasVariasApiItem>(
  (page) => RevistasVariasRepository.list({ page, per_page: 100 }),
  { autoFetchAll: true },
)

const defaultJournals: RevistasVariasApiItem[] = [
  {
    id: 1,
    titulo: 'Revista Varianza — Investigación en Estadística y Datos',
    entidad: 'Instituto de Estadística Teórica y Aplicada · UMSA',
    issn: 'ISSN 2789-3510 / 2789-3529',
    descripcion: 'Órgano oficial del IETA orientado a la publicación de investigaciones originales en estadística matemática, métodos probabilísticos y ciencia de datos.',
    url_enlace: '/revista-varianza',
    portada_url: '',
  },
  {
    id: 2,
    titulo: 'Revista Colombiana de Estadística',
    entidad: 'Universidad Nacional de Colombia',
    issn: 'ISSN 0120-1751',
    descripcion: 'Publicación científica indexada en Scopus y Web of Science con contribuciones originales en teoría estadística, bioestadística y modelos bayesianos.',
    url_enlace: 'https://revistas.unal.edu.co/index.php/estad',
    portada_url: '',
  },
  {
    id: 3,
    titulo: 'Revista de Matemática: Teoría y Aplicaciones',
    entidad: 'CIMPA · Universidad de Costa Rica',
    issn: 'ISSN 1409-2433',
    descripcion: 'Publicación arbitrada e indizada especializada en modelos estocásticos, probabilidad, optimización y estadística computacional.',
    url_enlace: 'https://revistas.ucr.ac.cr/index.php/matematica',
    portada_url: '',
  },
  {
    id: 4,
    titulo: 'Revista Interamericana de Estadística',
    entidad: 'Instituto Interamericano de Estadística (IASI)',
    issn: 'ISSN 0014-1135',
    descripcion: 'Revista de alcance continental dedicada al progreso metodológico de la estadística teórica y aplicada en las Américas.',
    url_enlace: 'https://www.math.uwaterloo.ca/~iosi/',
    portada_url: '',
  },
]

const displayedJournals = computed(() => {
  return journalsResource.items.value.length > 0 ? journalsResource.items.value : defaultJournals
})

function datasetDirectDownloadUrl(item: DataBankApiItem): string | undefined {
  const raw = item.archivo_url || item.bdd_url || item.url
  if (!raw) return undefined
  return mediaUrl(raw) || (/^https?:\/\//i.test(raw) ? raw : undefined)
}

const thematicAreas = ref<DataAreaApiItem[]>([])
const areasError = ref(false)

// In-place Modal for Dataset Details and Downloads
const selectedDataset = ref<DataBankApiItem | null>(null)
const datasetDialog = ref<HTMLDialogElement | null>(null)

function openDatasetModal(dataset: DataBankApiItem) {
  selectedDataset.value = dataset
  void nextTick(() => {
    datasetDialog.value?.showModal()
  })
}

function closeDatasetModal() {
  datasetDialog.value?.close()
  selectedDataset.value = null
}

useGsapReveal(resourcesRoot, {
  selector: '.resource-reveal',
  stagger: 0.08,
  y: 18,
})

const tabs = computed(() => [
  { value: 'banco-de-datos', label: t('tabs.database') },
  { value: 'revistas-de-estadistica', label: t('tabs.statsJournals') },
])

const activeTab = computed({
  get: () =>
    route.params.tab === 'revistas-de-estadistica' || route.params.tab === 'revistas-varias'
      ? 'revistas-de-estadistica'
      : 'banco-de-datos',
  set: (value: string) => {
    void router.replace({
      name: 'recursos',
      params: { tab: value === 'banco-de-datos' ? undefined : value },
    })
  },
})

// Dynamic filter options extracted from loaded dataset items and API
const availableAreas = computed(() => {
  const areaSet = new Set<string>()
  for (const item of banks.items.value) {
    if (Array.isArray(item.areas_tematicas)) {
      for (const area of item.areas_tematicas) {
        if (area?.nombre) areaSet.add(area.nombre.trim())
      }
    }
  }
  for (const area of thematicAreas.value) {
    if (area?.nombre) areaSet.add(area.nombre.trim())
  }
  return Array.from(areaSet).sort((a, b) => a.localeCompare(b, 'es'))
})

const yearOptions = computed(() => {
  const years = new Set<number>()
  for (const item of banks.items.value) {
    if (typeof item.anio === 'number' && item.anio > 0) {
      years.add(item.anio)
    }
  }
  return Array.from(years).sort((a, b) => b - a)
})

const locationOptions = computed(() => {
  const locs = new Set<string>()
  for (const item of banks.items.value) {
    if (item.locacion && item.locacion.trim()) {
      locs.add(item.locacion.trim())
    }
  }
  return Array.from(locs).sort((a, b) => a.localeCompare(b, 'es'))
})

function setTypeFilter(type: string) {
  activeTypeFilter.value = type
}

const allCount = computed(() => {
  return banks.items.value.length || totalRecursos.value || 0
})

const encuestasCount = computed(() => {
  const count = banks.items.value.filter((item) => isEncuesta(item.tipo_dato, item.titulo)).length
  return count || totalEncuestas.value || 0
})

const censosCount = computed(() => {
  const count = banks.items.value.filter((item) => isCenso(item.tipo_dato, item.titulo)).length
  return count || totalCensos.value || 0
})

const hasActiveFilters = computed(() => {
  return Boolean(
    query.value.trim() ||
      selectedYear.value ||
      selectedArea.value ||
      selectedLocation.value ||
      activeTypeFilter.value !== 'all',
  )
})

function clearFilters() {
  query.value = ''
  selectedYear.value = ''
  selectedArea.value = ''
  selectedLocation.value = ''
  activeTypeFilter.value = 'all'
  sortBy.value = 'recent'
}

const visibleBanks = computed(() => {
  const search = query.value.trim().toLowerCase()
  const targetYear = selectedYear.value
  const targetArea = selectedArea.value
  const targetLoc = selectedLocation.value
  const typeFilter = activeTypeFilter.value

  const filtered = banks.items.value.filter((item) => {
    // 1. Type Filter
    if (typeFilter === 'Encuestas' && !isEncuesta(item.tipo_dato, item.titulo)) return false
    if (typeFilter === 'Censos' && !isCenso(item.tipo_dato, item.titulo)) return false

    // 2. Year Filter
    if (targetYear && String(item.anio ?? '') !== targetYear) return false

    // 3. Thematic Area Filter
    if (targetArea) {
      const hasArea = item.areas_tematicas?.some(
        (a) => a.nombre?.toLowerCase() === targetArea.toLowerCase(),
      )
      if (!hasArea) return false
    }

    // 4. Location Filter
    if (targetLoc && item.locacion?.toLowerCase() !== targetLoc.toLowerCase()) {
      return false
    }

    // 5. Keyword search across title, code, description, location, and areas
    if (search) {
      const areaNames = item.areas_tematicas?.map((a) => a.nombre).join(' ') ?? ''
      const text = `${item.titulo ?? ''} ${item.codigo ?? ''} ${item.descripcion ?? ''} ${item.locacion ?? ''} ${areaNames}`.toLowerCase()
      if (!text.includes(search)) return false
    }

    return true
  })

  // Sorting
  return filtered.sort((a, b) => {
    if (sortBy.value === 'recent') {
      return (b.anio ?? 0) - (a.anio ?? 0) || (b.id ?? 0) - (a.id ?? 0)
    }
    if (sortBy.value === 'oldest') {
      return (a.anio ?? 0) - (b.anio ?? 0) || (a.id ?? 0) - (b.id ?? 0)
    }
    if (sortBy.value === 'title-asc') {
      return (a.titulo ?? '').localeCompare(b.titulo ?? '', 'es')
    }
    if (sortBy.value === 'title-desc') {
      return (b.titulo ?? '').localeCompare(a.titulo ?? '', 'es')
    }
    return 0
  })
})

async function loadAreas() {
  areasError.value = false
  try {
    thematicAreas.value = unwrapCollection<DataAreaApiItem>(
      await AreasTematicasRepository.list({ page: 1 }),
    ).data
  } catch {
    areasError.value = true
  }
}

async function loadDatabaseTotals() {
  try {
    const allRes = await BancoDatosRepository.list({ page: 1 })
    const allMeta = unwrapCollection<DataBankApiItem>(allRes).meta
    if (typeof allMeta?.total === 'number') {
      totalRecursos.value = allMeta.total
    }
  } catch {
    if (totalRecursos.value === null) totalRecursos.value = 79
  }

  try {
    const encRes = await BancoDatosRepository.list({ tipo_dato: 'Encuestas', page: 1 })
    const encMeta = unwrapCollection<DataBankApiItem>(encRes).meta
    if (typeof encMeta?.total === 'number' && encMeta.total !== totalRecursos.value) {
      totalEncuestas.value = encMeta.total
    }
  } catch {
    // Offline
  }

  try {
    const censoRes = await BancoDatosRepository.list({ tipo_dato: 'Censos', page: 1 })
    const censoMeta = unwrapCollection<DataBankApiItem>(censoRes).meta
    if (typeof censoMeta?.total === 'number' && censoMeta.total !== totalRecursos.value) {
      totalCensos.value = censoMeta.total
    }
  } catch {
    // Offline
  }
}

watch(
  activeTab,
  (tab) => {
    if (tab === 'banco-de-datos' && banks.page.value === 0) void banks.refresh()
    if (tab === 'revistas-de-estadistica' && journalsResource.page.value === 0) {
      void journalsResource.refresh()
    }
  },
  { immediate: true },
)

onMounted(() => {
  void loadAreas()
  void loadDatabaseTotals()
})
</script>

<template>
  <div ref="resourcesRoot">
    <SectionHeaderBand
      :title="t('resources.title')"
      :subtitle="t('resources.subtitle')"
    />

    <main class="container view-body">
      <!-- Subnav Tabs (PDF Pages 8 & 9) -->
      <div class="tabs-bar resource-reveal">
        <TabNav v-model="activeTab" :items="tabs" label="Secciones de recursos" />
      </div>

      <Transition name="tab-panel-fade" mode="out-in">
        <div :key="activeTab" class="resource-tab-panel-wrap">
          <!-- 1. BANCO DE DATOS (PDF Page 8) -->
          <section v-if="activeTab === 'banco-de-datos'" class="resource-panel">
            <!-- Centered Header -->
            <header class="resource-centered-header">
              <h2 class="resource-centered-title">Banco de Datos</h2>
              <p class="resource-centered-lead">
                Repositorio centralizado de microdatos y conjuntos de datos (datasets). Esta herramienta facilita el desarrollo de proyectos de investigación, tesis universitarias y modelos de ciencia de datos con información veraz sobre temáticas académicas, sociales y económicas.
              </p>
            </header>

            <!-- Type filter tabs/pills: [ Todos (79) ] [ Encuestas (65) ] [ Censos (14) ] -->
            <div class="resource-type-tabs-container">
              <div class="resource-type-tabs" role="tablist" aria-label="Filtrar por tipo de conjunto de datos">
                <button
                  type="button"
                  class="resource-type-pill"
                  :class="{ 'resource-type-pill--active': activeTypeFilter === 'all' }"
                  @click="setTypeFilter('all')"
                >
                  <span>Todos</span>
                  <span class="type-pill-count">({{ totalRecursos ?? allCount }})</span>
                </button>
                <button
                  type="button"
                  class="resource-type-pill"
                  :class="{ 'resource-type-pill--active': activeTypeFilter === 'Encuestas' }"
                  @click="setTypeFilter('Encuestas')"
                >
                  <FileSpreadsheet :size="15" aria-hidden="true" />
                  <span>Encuestas</span>
                  <span class="type-pill-count">({{ totalEncuestas ?? encuestasCount }})</span>
                </button>
                <button
                  type="button"
                  class="resource-type-pill"
                  :class="{ 'resource-type-pill--active': activeTypeFilter === 'Censos' }"
                  @click="setTypeFilter('Censos')"
                >
                  <Database :size="15" aria-hidden="true" />
                  <span>Censos</span>
                  <span class="type-pill-count">({{ totalCensos ?? censosCount }})</span>
                </button>
              </div>
            </div>

            <!-- Top Filter Bar (Author Filter Bar Style - Reference Image 1) -->
            <div class="people-filter-bar">
              <div class="people-filter-bar__left">
                <div class="filter-by-label">
                  <SlidersHorizontal :size="16" aria-hidden="true" />
                  <span>Filtrar por</span>
                </div>

                <!-- Dropdown: Área Temática -->
                <div class="filter-select-wrapper">
                  <select v-model="selectedArea" class="filter-select-input" aria-label="Filtrar por área temática">
                    <option value="">Área temática</option>
                    <option v-for="area in availableAreas" :key="area" :value="area">{{ area }}</option>
                  </select>
                </div>

                <!-- Dropdown: Año -->
                <div class="filter-select-wrapper">
                  <select v-model="selectedYear" class="filter-select-input" aria-label="Filtrar por año">
                    <option value="">Año</option>
                    <option v-for="year in yearOptions" :key="year" :value="String(year)">{{ year }}</option>
                  </select>
                </div>

                <!-- Dropdown: Locación / Cobertura (si hay opciones disponibles) -->
                <div v-if="locationOptions.length > 1" class="filter-select-wrapper">
                  <select v-model="selectedLocation" class="filter-select-input" aria-label="Filtrar por locación">
                    <option value="">Cobertura / Locación</option>
                    <option v-for="loc in locationOptions" :key="loc" :value="loc">{{ loc }}</option>
                  </select>
                </div>

                <!-- Search Input -->
                <div class="filter-search-wrapper">
                  <Search :size="15" class="filter-search-icon" aria-hidden="true" />
                  <input
                    v-model="query"
                    type="search"
                    placeholder="Buscar por título, código o palabra clave..."
                    class="filter-search-input"
                    aria-label="Buscar conjunto de datos"
                  />
                </div>

                <!-- Clear filters button -->
                <button
                  v-if="hasActiveFilters"
                  type="button"
                  class="filter-clear-btn"
                  title="Restablecer todos los filtros"
                  @click="clearFilters"
                >
                  <RotateCcw :size="13" aria-hidden="true" />
                  <span>Limpiar</span>
                </button>
              </div>

              <div class="people-filter-bar__right">
                <span class="results-count">{{ visibleBanks.length }} Resultados</span>
                <div class="sort-select-wrapper">
                  <label class="sort-label">Ordenar por:</label>
                  <select v-model="sortBy" class="sort-select-input" aria-label="Ordenar recursos">
                    <option value="recent">Más recientes</option>
                    <option value="oldest">Más antiguos</option>
                    <option value="title-asc">Título (A - Z)</option>
                    <option value="title-desc">Título (Z - A)</option>
                  </select>
                </div>
              </div>
            </div>

            <!-- Loading Skeleton -->
            <div v-if="banks.loading.value && banks.items.value.length === 0" class="skeleton-grid-4col" role="status">
              <div v-for="n in 8" :key="n" class="skeleton-dataset-card"></div>
            </div>

            <!-- Error State -->
            <div v-else-if="banks.error.value && banks.items.value.length === 0" class="content-state" role="alert">
              <EmptyState
                title="No se pudo cargar el banco de datos"
                description="Ocurrió un error al consultar el repositorio."
                action-label="Reintentar"
                @action="banks.refresh"
              />
            </div>

            <!-- 4-Column Dataset Grid -->
            <template v-else>
              <div v-if="visibleBanks.length" class="datasets-grid-4col">
                <article
                  v-for="item in visibleBanks"
                  :key="item.id"
                  class="dataset-card"
                  @click="openDatasetModal(item)"
                >
                  <!-- Optional Cover Support without breaking design -->
                  <div v-if="item.portada_url || item.imagen_url" class="dataset-card__cover">
                    <img :src="mediaUrl(item.portada_url || item.imagen_url)" :alt="item.titulo" loading="lazy" />
                  </div>

                  <div class="dataset-card__body">
                    <!-- Tag & Year Row -->
                    <div class="dataset-card__header-row">
                      <span
                        class="dataset-card__tag"
                        :class="isCenso(item.tipo_dato) ? 'dataset-card__tag--censo' : 'dataset-card__tag--encuesta'"
                      >
                        <component
                          :is="isCenso(item.tipo_dato) ? Database : FileSpreadsheet"
                          :size="14"
                          aria-hidden="true"
                        />
                        <span>{{ item.tipo_dato || 'Encuestas' }}</span>
                      </span>
                      <span class="dataset-card__year">{{ item.anio || '—' }}</span>
                    </div>

                    <!-- Code / ID -->
                    <p class="dataset-card__code">{{ item.codigo || 'cod-' + item.id }}</p>

                    <!-- Title -->
                    <h4 class="dataset-card__title">
                      {{ item.titulo }}
                    </h4>

                    <!-- Category / Location -->
                    <p class="dataset-card__category">
                      {{ item.areas_tematicas?.[0]?.nombre || item.locacion || 'Estadística' }}
                    </p>

                    <!-- Actions Row (Botón Descargar directo + Botón VER modal) -->
                    <div class="dataset-card__footer">
                      <a
                        v-if="datasetDirectDownloadUrl(item)"
                        :href="datasetDirectDownloadUrl(item)"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="dataset-card__download-link"
                        title="Descargar archivo directamente"
                        @click.stop
                      >
                        <Download :size="14" aria-hidden="true" />
                        <span>Descargar</span>
                      </a>
                      <span
                        v-else
                        class="dataset-card__download-link dataset-card__download-link--disabled"
                        title="Descarga en ficha técnica"
                      >
                        <Download :size="14" aria-hidden="true" />
                        <span>Descargar</span>
                      </span>

                      <button
                        type="button"
                        class="dataset-card__view-btn"
                        title="Ver detalles del conjunto de datos"
                        @click.stop="openDatasetModal(item)"
                      >
                        <Eye :size="14" aria-hidden="true" />
                        <span>VER</span>
                      </button>
                    </div>
                  </div>
                </article>
              </div>

              <EmptyState
                v-else-if="!banks.loading.value && !hasActiveFilters"
                title="No hay conjuntos de datos publicados"
                description="Los bancos de datos publicados por el IETA aparecerán en esta sección."
              />
              <EmptyState
                v-else
                title="No se encontraron coincidencias"
                description="Prueba con otros términos de búsqueda o cambia los filtros de área temática, año o tipo."
                action-label="Limpiar filtros"
                @action="clearFilters"
              />

              <!-- Load More Button -->
              <div v-if="banks.hasMore.value" class="pagination-center">
                <LoadMoreButton
                  :loading="banks.loadingMore.value"
                  :has-more="banks.hasMore.value"
                  label="Ver más registros →"
                  @load="banks.loadMore"
                />
              </div>
            </template>
          </section>

          <!-- 2. REVISTAS DE ESTADÍSTICA (Conectado a /api/revistas-varias) -->
          <section v-else class="journal-directory resource-reveal" aria-labelledby="journals-heading">
            <!-- Centered Header -->
            <header class="resource-centered-header">
              <h2 id="journals-heading" class="resource-centered-title">Revistas de Estadística</h2>
              <p class="resource-centered-lead">
                Accede a una red de revistas de estadística y ciencia de datos editadas por destacadas casas de estudio e instituciones académicas. Un recurso abierto diseñado para consultar artículos con referato, novedades teóricas y avances aplicados en diversas disciplinas.
              </p>
            </header>

            <!-- Loading State -->
            <div v-if="journalsResource.loading.value && journalsResource.items.value.length === 0" class="skeleton-journals-list" role="status">
              <div v-for="n in 3" :key="n" class="skeleton-journal-card"></div>
            </div>

            <!-- Error State -->
            <div v-else-if="journalsResource.error.value && journalsResource.items.value.length === 0" class="content-state" role="alert">
              <EmptyState
                title="No se pudieron cargar las revistas de estadística"
                description="Ocurrió un problema de comunicación al consultar el directorio."
                action-label="Reintentar"
                @action="journalsResource.refresh"
              />
            </div>

            <!-- Horizontal Journal Cards List (Con animaciones y hover) -->
            <template v-else>
              <div v-if="displayedJournals.length" class="journals-horizontal-list">
                <article
                  v-for="journal in displayedJournals"
                  :key="journal.id"
                  class="journal-horizontal-card"
                >
                  <!-- Left Cover Thumbnail -->
                  <div class="journal-horizontal-card__cover">
                    <img
                      v-if="journal.portada_url"
                      :src="mediaUrl(journal.portada_url)"
                      :alt="journal.titulo"
                      class="journal-real-cover"
                      loading="lazy"
                    />
                    <div v-else class="journal-cover-frame">
                      <div class="journal-cover-frame__glow"></div>
                      <BookOpen :size="32" class="journal-cover-frame__icon" />
                      <span class="journal-cover-frame__tag">{{ journal.issn ? 'Indexada' : 'Revista' }}</span>
                    </div>
                  </div>

                  <!-- Right Body -->
                  <div class="journal-horizontal-card__body">
                    <div class="journal-meta-row">
                      <span v-if="journal.entidad" class="journal-entity-tag">
                        <span class="entity-badge-dot"></span>
                        <span>{{ journal.entidad }}</span>
                      </span>
                      <span v-if="journal.issn" class="journal-issn-tag">{{ journal.issn }}</span>
                    </div>

                    <h3 class="journal-horizontal-card__title">{{ journal.titulo }}</h3>
                    <p v-if="journal.descripcion" class="journal-horizontal-card__desc">
                      {{ journal.descripcion }}
                    </p>

                    <div class="journal-horizontal-card__action">
                      <a
                        v-if="journal.url_enlace"
                        class="journal-action-btn"
                        :href="journal.url_enlace"
                        :target="journal.url_enlace.startsWith('http') ? '_blank' : '_self'"
                        :rel="journal.url_enlace.startsWith('http') ? 'noopener noreferrer' : ''"
                      >
                        <span>Consultar revista</span>
                        <ExternalLink :size="15" aria-hidden="true" class="journal-btn-arrow" />
                      </a>
                    </div>
                  </div>
                </article>
              </div>

              <EmptyState
                v-else
                title="No hay revistas registradas actualmente"
                description="Las publicaciones científicas de estadística y ciencia de datos se incorporarán próximamente."
              />

              <div class="pagination-center">
                <LoadMoreButton
                  :loading="journalsResource.loadingMore.value"
                  :has-more="journalsResource.hasMore.value"
                  label="Ver más revistas →"
                  @load="journalsResource.loadMore"
                />
              </div>
            </template>
          </section>
        </div>
      </Transition>
    </main>

    <!-- Modal Detalle y Descarga de Banco de Datos (Censos y Encuestas) -->
    <dialog
      ref="datasetDialog"
      class="dataset-modal-dialog"
      @click.self="closeDatasetModal"
      @close="selectedDataset = null"
    >
      <article v-if="selectedDataset" class="dataset-modal-panel">
        <button
          class="dataset-modal-close"
          type="button"
          aria-label="Cerrar modal"
          @click="closeDatasetModal"
        >
          <X :size="20" aria-hidden="true" />
        </button>

        <header class="dataset-modal-header">
          <div class="dataset-modal-tags">
            <span class="dataset-modal-tag">
              <Database :size="13" aria-hidden="true" />
              <span>{{ selectedDataset.tipo_dato || 'Banco de Datos' }}</span>
            </span>
            <span v-if="selectedDataset.anio" class="dataset-modal-year">
              <Calendar :size="13" aria-hidden="true" />
              <span>Año {{ selectedDataset.anio }}</span>
            </span>
          </div>

          <h2 class="dataset-modal-title">{{ selectedDataset.titulo }}</h2>
          <p v-if="selectedDataset.codigo" class="dataset-modal-code">
            Código: <strong>{{ selectedDataset.codigo }}</strong>
          </p>
        </header>

        <div v-if="selectedDataset.portada_url || selectedDataset.imagen_url" class="dataset-modal-media">
          <img
            :src="mediaUrl(selectedDataset.portada_url || selectedDataset.imagen_url)"
            :alt="selectedDataset.titulo"
            class="dataset-modal-img"
          />
        </div>

        <div v-if="selectedDataset.descripcion" class="dataset-modal-desc">
          <p>{{ selectedDataset.descripcion }}</p>
        </div>

        <dl class="dataset-modal-grid">
          <div v-if="selectedDataset.locacion" class="modal-grid-item">
            <dt>Cobertura Geográfica</dt>
            <dd>
              <MapPin :size="14" aria-hidden="true" />
              <span>{{ selectedDataset.locacion }}</span>
            </dd>
          </div>

          <div v-if="selectedDataset.areas_tematicas?.length" class="modal-grid-item">
            <dt>Áreas Temáticas</dt>
            <dd class="modal-chips">
              <span v-for="area in selectedDataset.areas_tematicas" :key="area.id" class="modal-chip">
                <Tag :size="12" aria-hidden="true" />
                <span>{{ area.nombre }}</span>
              </span>
            </dd>
          </div>
        </dl>

        <footer class="dataset-modal-footer">
          <a
            v-if="selectedDataset.archivo_url"
            class="dataset-modal-btn dataset-modal-btn--primary"
            :href="mediaUrl(selectedDataset.archivo_url)"
            target="_blank"
            rel="noopener noreferrer"
          >
            <Download :size="16" aria-hidden="true" />
            <span>Descargar archivo</span>
          </a>

          <a
            v-if="selectedDataset.bdd_url"
            class="dataset-modal-btn dataset-modal-btn--secondary"
            :href="mediaUrl(selectedDataset.bdd_url)"
            target="_blank"
            rel="noopener noreferrer"
          >
            <Database :size="16" aria-hidden="true" />
            <span>Descargar Base de Datos</span>
          </a>

          <a
            v-if="selectedDataset.url"
            class="dataset-modal-btn dataset-modal-btn--outline"
            :href="selectedDataset.url"
            target="_blank"
            rel="noopener noreferrer"
          >
            <ExternalLink :size="16" aria-hidden="true" />
            <span>Descargar / Repositorio</span>
          </a>

          <button
            class="dataset-modal-dismiss"
            type="button"
            @click="closeDatasetModal"
          >
            Cerrar
          </button>
        </footer>
      </article>
    </dialog>
  </div>
</template>

<style scoped>
.view-body {
  min-height: 48vh;
  padding-block: var(--space-4) var(--space-8);
}

.tabs-bar {
  position: relative;
  z-index: 50;
  margin-bottom: var(--space-6);
}

.resource-tab-panel-wrap {
  width: 100%;
}

/* Tab Panel Fade Transition */
.tab-panel-fade-enter-active,
.tab-panel-fade-leave-active {
  transition: opacity 0.22s ease, transform 0.22s ease;
}

.tab-panel-fade-enter-from {
  opacity: 0;
  transform: translateY(8px);
}

.tab-panel-fade-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

/* Centered Header (PDF Pages 8 & 9) */
.resource-centered-header {
  max-width: 860px;
  margin-inline: auto;
  text-align: center;
  margin-bottom: 2.5rem;
}

.resource-centered-title {
  color: #1b144b;
  font-size: clamp(2rem, 3.5vw, 2.6rem);
  font-weight: 700;
  line-height: 1.2;
  margin-bottom: 1rem;
}

.resource-centered-lead {
  color: #4b5268;
  font-size: 1.02rem;
  line-height: 1.75;
}

/* Type Filter Tabs / Pills with count */
.resource-type-tabs-container {
  display: flex;
  margin-bottom: 1.5rem;
}

.resource-type-tabs {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.resource-type-pill {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  padding: 0.5rem 1.15rem;
  border-radius: 999px;
  border: 1px solid #dce0eb;
  background: var(--color-blanco);
  color: #4b5268;
  font-size: 0.88rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.18s ease;
}

.resource-type-pill:hover {
  border-color: #35449f;
  color: #35449f;
}

.resource-type-pill--active {
  background: #35449f;
  border-color: #35449f;
  color: #ffffff;
}

.resource-type-pill--active:hover {
  background: #2b1b6d;
  color: #ffffff;
}

.type-pill-count {
  font-size: 0.8rem;
  opacity: 0.9;
  font-weight: 600;
}

/* People Filter Bar (Author Filter Bar Style - Reference Image 1) */
.people-filter-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 1.25rem;
  padding: 0.85rem 0 1.25rem;
  border-bottom: 2px solid #111827;
  margin-bottom: 2rem;
}

.people-filter-bar__left {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.85rem;
}

.filter-by-label {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  font-size: 0.92rem;
  font-weight: 800;
  color: #111827;
  letter-spacing: -0.01em;
}

.filter-select-wrapper {
  position: relative;
  display: inline-flex;
}

.filter-select-input {
  appearance: none;
  background-color: #ffffff;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23374151' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 0.75rem center;
  padding: 0.5rem 2.2rem 0.5rem 0.85rem;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  font-size: 0.88rem;
  font-weight: 600;
  color: #374151;
  cursor: pointer;
  transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

.filter-select-input:focus {
  outline: 2px solid #35449f;
  outline-offset: 1px;
}

.filter-search-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.filter-search-icon {
  position: absolute;
  left: 0.75rem;
  color: #9ca3af;
  pointer-events: none;
}

.filter-search-input {
  padding: 0.5rem 0.85rem 0.5rem 2.2rem;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  font-size: 0.88rem;
  color: #111827;
  min-width: 15rem;
  transition: border-color 0.15s ease, box-shadow 0.15s ease;
}

.filter-search-input:focus {
  outline: 2px solid #35449f;
  outline-offset: 1px;
}

.filter-clear-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.45rem 0.85rem;
  border: 1px dashed #d1d5db;
  border-radius: 4px;
  background: #f9fafb;
  color: #6b7280;
  font-size: 0.82rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s ease;
}

.filter-clear-btn:hover {
  background: #fee2e2;
  border-color: #ef4444;
  color: #b91c1c;
}

.people-filter-bar__right {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  margin-left: auto;
}

.results-count {
  font-size: 0.88rem;
  font-weight: 600;
  color: #4b5563;
}

.sort-select-wrapper {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.sort-label {
  font-size: 0.85rem;
  font-weight: 700;
  color: #111827;
}

.sort-select-input {
  appearance: none;
  background-color: #ffffff;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23374151' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 0.75rem center;
  padding: 0.45rem 2.2rem 0.45rem 0.85rem;
  border: 1px solid #d1d5db;
  border-radius: 4px;
  font-size: 0.88rem;
  font-weight: 600;
  color: #374151;
  cursor: pointer;
}

.sort-select-input:focus {
  outline: 2px solid #35449f;
  outline-offset: 1px;
}

/* 4-Column Dataset Grid with rich hover */
.datasets-grid-4col {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: clamp(1rem, 2vw, 1.5rem);
  margin-top: 1.5rem;
}

.dataset-card {
  display: flex;
  flex-direction: column;
  border: 1px solid rgba(220, 224, 235, 0.85);
  border-radius: var(--radius-md);
  background: var(--color-blanco);
  overflow: hidden;
  box-shadow: 0 4px 18px rgba(27, 20, 75, 0.04);
  cursor: pointer;
  transition: transform 0.22s ease, box-shadow 0.22s ease, border-color 0.22s ease;
}

.dataset-card:hover {
  transform: translateY(-4px);
  border-color: rgba(243, 0, 96, 0.45);
  box-shadow: 0 12px 30px -4px rgba(27, 20, 75, 0.12);
}

.dataset-card__cover {
  height: 7.5rem;
  overflow: hidden;
  background: #f0f3fa;
}

.dataset-card__cover img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.dataset-card__body {
  padding: 1.15rem 1.25rem;
  display: flex;
  flex-direction: column;
  flex: 1;
}

.dataset-card__header-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.dataset-card__tag {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.8rem;
  font-weight: 700;
  color: #1b144b;
  transition: color 0.18s ease;
}

.dataset-card__tag--censo {
  color: #35449f;
}

.dataset-card__tag--encuesta {
  color: #059669;
}

.dataset-card:hover .dataset-card__tag {
  color: #f30060;
}

.dataset-card__year {
  color: #8c93a8;
  font-size: 0.85rem;
  font-weight: 600;
}

.dataset-card__code {
  color: #8c93a8;
  font-size: 0.82rem;
  font-weight: 600;
  margin: 0 0 0.35rem;
  transition: color 0.18s ease;
}

.dataset-card:hover .dataset-card__code {
  color: #35449f;
}

.dataset-card__title {
  color: #1b144b;
  font-size: 1rem;
  font-weight: 700;
  line-height: 1.35;
  margin: 0 0 0.5rem;
  flex: 1;
  transition: color 0.18s ease;
}

.dataset-card:hover .dataset-card__title {
  color: #f30060;
}

.dataset-card__category {
  color: #636b83;
  font-size: 0.85rem;
  margin: 0 0 1rem;
}

.dataset-card__footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 0.5rem;
  margin-top: auto;
  padding-top: 0.75rem;
  border-top: 1px solid #f0f2f8;
}

.dataset-card__download-link {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  color: #35449f;
  font-size: 0.82rem;
  font-weight: 700;
  text-decoration: none;
  background: #f0f3fa;
  border: 1px solid #e1e6f5;
  padding: 0.35rem 0.65rem;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.18s ease;
}

.dataset-card__download-link:hover {
  background: #f30060;
  border-color: #f30060;
  color: #ffffff;
  transform: translateY(-1px);
}

.dataset-card__download-link--disabled {
  color: #9ca3af;
  background: #f8f9fc;
  border-color: #e5e7eb;
  cursor: not-allowed;
}

.dataset-card__view-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  background: #1b144b;
  color: #ffffff;
  border: 0;
  padding: 0.38rem 0.85rem;
  border-radius: 4px;
  font-size: 0.8rem;
  font-weight: 800;
  letter-spacing: 0.04em;
  cursor: pointer;
  transition: transform 0.18s ease, background-color 0.18s ease, box-shadow 0.18s ease;
}

.dataset-card__view-btn:hover {
  background: #f30060;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(243, 0, 96, 0.35);
}

/* 2. Horizontal Journal Cards with Animation & Hover */
.journals-horizontal-list {
  display: flex;
  flex-direction: column;
  gap: 1.75rem;
  max-width: 980px;
  margin-inline: auto;
}

.journal-horizontal-card {
  position: relative;
  display: grid;
  grid-template-columns: 12.5rem minmax(0, 1fr);
  gap: 2.25rem;
  padding: 1.85rem;
  border-radius: var(--radius-md);
  border: 1px solid #dce0eb;
  background: var(--color-blanco);
  box-shadow: 0 4px 18px rgba(27, 20, 75, 0.04);
  align-items: center;
  overflow: hidden;
  transition: transform 0.28s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.28s ease, border-color 0.28s ease;
}

.journal-horizontal-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  bottom: 0;
  width: 4px;
  background: linear-gradient(180deg, #35449f 0%, #f30060 100%);
  opacity: 0;
  transition: opacity 0.25s ease;
}

.journal-horizontal-card:hover {
  transform: translateY(-5px);
  border-color: rgba(243, 0, 96, 0.4);
  box-shadow: 0 16px 36px -6px rgba(27, 20, 75, 0.12), 0 0 0 1px rgba(243, 0, 96, 0.15);
}

.journal-horizontal-card:hover::before {
  opacity: 1;
}

.journal-horizontal-card__cover {
  position: relative;
  width: 100%;
  aspect-ratio: 3 / 4.2;
  height: auto;
  min-height: 16.5rem;
  border-radius: var(--radius-sm);
  overflow: hidden;
  background: #f1f4fa;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.07);
  display: flex;
  align-items: center;
  justify-content: center;
}

.journal-real-cover {
  width: 100%;
  height: 100%;
  object-fit: contain;
  background: #ffffff;
  transition: transform 0.4s ease;
}

.journal-horizontal-card:hover .journal-real-cover {
  transform: scale(1.06);
}

.journal-cover-frame {
  position: relative;
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.85rem;
  background: linear-gradient(135deg, #1b144b 0%, #35449f 100%);
  color: #ffffff;
  padding: 1rem;
  text-align: center;
  overflow: hidden;
  transition: transform 0.35s ease;
}

.journal-cover-frame__glow {
  position: absolute;
  top: -20%;
  left: -20%;
  width: 140%;
  height: 140%;
  background: radial-gradient(circle at 50% 30%, rgba(243, 0, 96, 0.25), transparent 70%);
  pointer-events: none;
}

.journal-horizontal-card:hover .journal-cover-frame {
  transform: scale(1.03);
}

.journal-cover-frame__tag {
  font-size: 0.72rem;
  font-weight: 800;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  background: rgba(243, 0, 96, 0.85);
  color: #ffffff;
  padding: 0.25rem 0.65rem;
  border-radius: 999px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.journal-horizontal-card__body {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.journal-meta-row {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  flex-wrap: wrap;
}

.journal-entity-tag {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.8rem;
  font-weight: 700;
  color: #35449f;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

.entity-badge-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: #f30060;
}

.journal-issn-tag {
  font-size: 0.8rem;
  color: #636b83;
}

.journal-horizontal-card__title {
  color: #1b144b;
  font-size: 1.25rem;
  font-weight: 700;
  line-height: 1.3;
  margin: 0;
  transition: color 0.18s ease;
}

.journal-horizontal-card:hover .journal-horizontal-card__title {
  color: #35449f;
}

.journal-horizontal-card__desc {
  color: #4b5268;
  font-size: 0.95rem;
  line-height: 1.6;
  margin: 0;
}

.journal-horizontal-card__action {
  margin-top: 0.5rem;
}

.journal-action-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.55rem 1.25rem;
  border-radius: var(--radius-sm);
  background: #f30060;
  color: #ffffff;
  font-size: 0.88rem;
  font-weight: 700;
  text-decoration: none;
  box-shadow: 0 4px 12px rgba(243, 0, 96, 0.25);
  transition: transform 0.2s ease, background-color 0.2s ease, box-shadow 0.2s ease;
}

.journal-action-btn:hover {
  transform: translateY(-2px);
  background: #db0056;
  box-shadow: 0 6px 18px rgba(243, 0, 96, 0.4);
}

.journal-btn-arrow {
  transition: transform 0.2s ease;
}

.journal-action-btn:hover .journal-btn-arrow {
  transform: translate(2px, -2px);
}

/* Skeletons */
.skeleton-grid-4col {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 1.5rem;
}

.skeleton-dataset-card {
  height: 14rem;
  border-radius: var(--radius-md);
  background: #edeef5;
}

.skeleton-journals-list {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  max-width: 980px;
  margin-inline: auto;
}

.skeleton-journal-card {
  height: 12rem;
  border-radius: var(--radius-md);
  background: #edeef5;
}

.pagination-center {
  display: flex;
  justify-content: center;
  margin-top: var(--space-6);
}

/* Dataset Modal Dialog (Centered in the middle of screen) */
.dataset-modal-dialog {
  position: fixed;
  inset: 0;
  margin: auto;
  width: min(44rem, calc(100vw - 2rem));
  max-width: calc(100vw - 2rem);
  max-height: min(92vh, 52rem);
  padding: 0;
  border: 0;
  border-radius: var(--radius-lg, 16px);
  background: #ffffff;
  box-shadow: 0 25px 60px -15px rgba(27, 20, 75, 0.4);
  overflow: hidden;
  outline: none;
}

.dataset-modal-dialog[open] {
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

.dataset-modal-dialog::backdrop {
  background: rgba(15, 20, 48, 0.72);
  backdrop-filter: blur(4px);
}

.dataset-modal-panel {
  position: relative;
  display: flex;
  flex-direction: column;
  padding: clamp(1.5rem, 4vw, 2.25rem);
  gap: 1.25rem;
  max-height: min(92vh, 52rem);
  overflow-y: auto;
}

.dataset-modal-close {
  position: absolute;
  top: 1rem;
  right: 1rem;
  display: grid;
  width: 2.25rem;
  height: 2.25rem;
  place-items: center;
  border: 1px solid #dce0eb;
  border-radius: 50%;
  background: #f8f9fa;
  color: #111827;
  cursor: pointer;
  transition: transform 0.18s ease, background-color 0.18s ease;
}

.dataset-modal-close:hover {
  transform: rotate(90deg);
  background: #f30060;
  color: #ffffff;
  border-color: #f30060;
}

.dataset-modal-tags {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  margin-bottom: 0.5rem;
}

.dataset-modal-tag {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.25rem 0.65rem;
  border-radius: 999px;
  background: rgba(53, 68, 159, 0.1);
  color: #35449f;
  font-size: 0.78rem;
  font-weight: 700;
  text-transform: uppercase;
}

.dataset-modal-year {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.85rem;
  font-weight: 600;
  color: #636b83;
}

.dataset-modal-title {
  margin: 0 0 0.4rem;
  color: #1b144b;
  font-size: 1.5rem;
  font-weight: 700;
  line-height: 1.25;
}

.dataset-modal-code {
  margin: 0;
  font-size: 0.9rem;
  color: #636b83;
}

.dataset-modal-media {
  max-height: 16rem;
  overflow: hidden;
  border-radius: 8px;
  background: #f0f3fa;
}

.dataset-modal-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.dataset-modal-desc {
  font-size: 0.98rem;
  line-height: 1.65;
  color: #3b4258;
}

.dataset-modal-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 1rem;
  padding: 1.25rem;
  background: #f8f9fc;
  border-radius: 8px;
  border: 1px solid #e5eaf5;
}

.modal-grid-item dt {
  font-size: 0.78rem;
  font-weight: 700;
  color: #636b83;
  text-transform: uppercase;
  margin-bottom: 0.25rem;
}

.modal-grid-item dd {
  margin: 0;
  font-size: 0.95rem;
  font-weight: 600;
  color: #1b144b;
  display: flex;
  align-items: center;
  gap: 0.35rem;
}

.modal-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 0.4rem;
}

.modal-chip {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  padding: 0.2rem 0.5rem;
  background: #ffffff;
  border: 1px solid #dce0eb;
  border-radius: 4px;
  font-size: 0.78rem;
  font-weight: 600;
  color: #35449f;
}

.dataset-modal-footer {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  justify-content: flex-end;
  gap: 0.75rem;
  margin-top: 0.5rem;
  padding-top: 1rem;
  border-top: 1px solid #e5e7eb;
}

.dataset-modal-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  padding: 0.6rem 1.25rem;
  border-radius: 8px;
  font-size: 0.88rem;
  font-weight: 700;
  text-decoration: none;
  transition: transform 0.15s ease, background-color 0.15s ease;
}

.dataset-modal-btn--primary {
  background: #f30060;
  color: #ffffff;
  box-shadow: 0 4px 14px rgba(243, 0, 96, 0.35);
}

.dataset-modal-btn--primary:hover {
  transform: translateY(-2px);
  background: #db0056;
}

.dataset-modal-btn--secondary {
  background: #35449f;
  color: #ffffff;
}

.dataset-modal-btn--secondary:hover {
  transform: translateY(-2px);
  background: #2b1b6d;
}

.dataset-modal-btn--outline {
  border: 1px solid #35449f;
  color: #35449f;
}

.dataset-modal-btn--outline:hover {
  background: rgba(53, 68, 159, 0.08);
}

.dataset-modal-dismiss {
  padding: 0.6rem 1.15rem;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  background: transparent;
  color: #4b5563;
  font-size: 0.88rem;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.15s ease;
}

.dataset-modal-dismiss:hover {
  background: #f3f4f6;
  color: #111827;
}

/* Responsive */
@media (max-width: 1024px) {
  .datasets-grid-4col,
  .skeleton-grid-4col {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}

@media (max-width: 900px) {
  .people-filter-bar {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
  }
  .people-filter-bar__left {
    width: 100%;
  }
  .people-filter-bar__right {
    width: 100%;
    justify-content: space-between;
    margin-left: 0;
  }
  .filter-search-wrapper {
    flex: 1;
    min-width: 12rem;
  }
  .filter-search-input {
    width: 100%;
    min-width: 0;
  }
}

@media (max-width: 768px) {
  .datasets-grid-4col,
  .skeleton-grid-4col {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
  .journal-horizontal-card {
    grid-template-columns: 1fr;
  }
  .journal-horizontal-card__cover {
    width: min(15rem, 100%);
    margin-inline: auto;
    height: auto;
    min-height: 16rem;
    aspect-ratio: 3 / 4.2;
  }
}

@media (max-width: 480px) {
  .datasets-grid-4col,
  .skeleton-grid-4col {
    grid-template-columns: 1fr;
  }
  .dataset-modal-grid {
    grid-template-columns: 1fr;
  }
  .dataset-modal-footer {
    flex-direction: column-reverse;
  }
  .dataset-modal-footer > * {
    width: 100%;
    justify-content: center;
  }
}
</style>
