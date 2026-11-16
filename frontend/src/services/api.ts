import axios, { type AxiosError } from 'axios'

/**
 * Instancia centralizada de Axios para el proyecto IETA.
 * Guía: GUIA_IMPLEMENTACION_FRONTEND_IETA.md §7
 *
 * - baseURL desde variable de entorno VITE_API_URL
 * - withCredentials: true para Sanctum SPA (cookies)
 * - Interceptor de errores para manejo centralizado
 */
const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  withCredentials: true, // Sanctum SPA auth via cookies
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
  timeout: 10000,
})

// Idioma por defecto para la API
const initialLocale = (typeof localStorage !== 'undefined' && localStorage.getItem('ieta-locale')) || 'es'
apiClient.defaults.headers.common['Accept-Language'] = initialLocale

// Interceptor de petición: asegura el header Accept-Language en cada llamada a la API
apiClient.interceptors.request.use((config) => {
  const currentLocale = (typeof localStorage !== 'undefined' && localStorage.getItem('ieta-locale')) || 'es'
  config.headers['Accept-Language'] = currentLocale
  return config
})

// Interceptor de respuesta: manejo centralizado de errores
apiClient.interceptors.response.use(
  (response) => response,
  (error: AxiosError) => {
    if (error.response) {
      const status = error.response.status
      console.error(`[API] Error ${status}:`, error.response.data)
    } else if (error.request) {
      console.error('[API] Sin respuesta del servidor. Verifica la conexión.')
    } else {
      console.error('[API] Error de configuración:', error.message)
    }
    return Promise.reject(error)
  },
)

export const apiPool = apiClient

export default apiClient
