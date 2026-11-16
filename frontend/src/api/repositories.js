import { apiPool } from '../services/api'

/**
 * Crea un repositorio de solo lectura para un recurso del CMS.
 * El repositorio devuelve el payload del servidor y conserva los errores HTTP
 * rechazados para que los composables o las vistas decidan cómo mostrarlos.
 *
 * @param {string} resourcePath Ruta relativa a VITE_API_URL.
 */
export function createReadRepository(resourcePath) {
  const path = resourcePath.replace(/\/$/, '')

  return Object.freeze({
    /** @returns {Promise<unknown>} */
    get() {
      return apiPool.get(path, { withCredentials: false }).then((response) => response.data)
    },

    /** @param {string | number} id */
    getById(id) {
      return apiPool
        .get(`${path}/${encodeURIComponent(String(id))}`, { withCredentials: false })
        .then((response) => response.data)
    },

    /** @param {Record<string, string | number | boolean>} [params] */
    list(params = {}) {
      return apiPool.get(path, { params, withCredentials: false }).then((response) => response.data)
    },

    /** @param {string} slug */
    getBySlug(slug) {
      const safeSlug = encodeURIComponent(slug)
      return apiPool
        .get(`${path}/${safeSlug}`, { withCredentials: false })
        .then((response) => response.data)
    },
  })
}

export const NoticiasRepository = createReadRepository('/noticias')
export const SobreNosotrosRepository = createReadRepository('/sobre-nosotros')
export const LineasInvestigacionRepository = createReadRepository('/lineas-investigacion')
export const ContactoRepository = createReadRepository('/contacto')
export const CursosRepository = createReadRepository('/cursos')
export const ServiciosRepository = createReadRepository('/servicios')
export const AutoresRepository = createReadRepository('/autores')
export const ArticulosRepository = createReadRepository('/articulos')
export const PersonalRepository = createReadRepository('/personal')
export const BancoDatosRepository = createReadRepository('/banco-datos')
export const AreasTematicasRepository = createReadRepository('/area-tematicas')
export const MemoriasInvestigacionRepository = createReadRepository('/memorias-investigacion')
export const ComiteEditorialRepository = createReadRepository('/comite-editorial')
export const CargosInstitucionalesRepository = createReadRepository('/cargos-institucionales')
export const EspecialidadesRepository = createReadRepository('/especialidades')
export const GradosAcademicosRepository = createReadRepository('/grado-academicos')
export const InstitucionesRepository = createReadRepository('/instituciones')
export const CursoModalidadesRepository = createReadRepository('/curso-modalidades')
export const TipoAutoresRepository = createReadRepository('/tipo-autores')
export const TipoCursosRepository = createReadRepository('/tipo-cursos')
export const TipoParticipacionesRepository = createReadRepository('/tipo-participaciones')
export const RevistaVarianzaRepository = createReadRepository('/revista-varianza')
export const EtiquetasArtRepository = createReadRepository('/etiquetas-art')
export const InstruccionesAutoresRepository = createReadRepository('/instrucciones-autores')
export const EdicionesVarianzaRepository = Object.freeze({
  ...createReadRepository('/ediciones-varianza'),
  async getLatest() {
    try {
      const response = await apiPool.get('/ediciones-varianza/', { withCredentials: false })
      const payload = response.data
      if (!payload) return null
      let items = []
      if (Array.isArray(payload)) {
        items = payload
      } else if (Array.isArray(payload.data)) {
        items = payload.data
      } else if (payload.data && typeof payload.data === 'object') {
        return payload.data
      } else if (typeof payload === 'object') {
        return payload
      }

      if (items.length === 0) return null

      const sorted = [...items].sort((a, b) => {
        const yA = Number(a.anio) || 0
        const yB = Number(b.anio) || 0
        if (yB !== yA) return yB - yA
        const nA = Number(a.numero) || 0
        const nB = Number(b.numero) || 0
        if (nB !== nA) return nB - nA
        const dateA = new Date(a.fecha_publicacion || a.created_at || '').getTime() || 0
        const dateB = new Date(b.fecha_publicacion || b.created_at || '').getTime() || 0
        return dateB - dateA
      })
      return sorted[0] || null
    } catch {
      return null
    }
  },
})
export const ComunicadosRepository = Object.freeze({
  ...createReadRepository('/comunicados'),
  async getActive() {
    try {
      const response = await apiPool.get('/comunicados/activo', { withCredentials: false })
      const payload = response.data
      if (payload) {
        if (Array.isArray(payload)) return payload[0] || null
        if (Array.isArray(payload.data)) return payload.data[0] || null
        return payload.data || payload
      }
    } catch {
      try {
        const listRes = await apiPool.get('/comunicados', { withCredentials: false })
        const listPayload = listRes.data
        const items = Array.isArray(listPayload) ? listPayload : Array.isArray(listPayload?.data) ? listPayload.data : []
        const activeItem = items.find((item) => item.activo === true || item.activo === 1 || item.estado === true || item.estado === 1)
        return activeItem || null
      } catch {
        return null
      }
    }
    return null
  },
})
export const ConfiguracionRepository = createReadRepository('/configuracion')
export const RevistasVariasRepository = createReadRepository('/revistas-varias')
export const GaleriaRepository = Object.freeze({
  ...createReadRepository('/galeria'),
  getDestacados() {
    return apiPool.get('/galeria/destacados', { withCredentials: false }).then((response) => response.data)
  },
  getCategorias() {
    return apiPool.get('/galeria/categorias', { withCredentials: false }).then((response) => response.data)
  },
})
