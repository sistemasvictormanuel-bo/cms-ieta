/**
 * Resuelve rutas relativas y absolutas para imágenes y archivos multimedia.
 * 
 * Permite manejar:
 * - Rutas relativas del servidor (ej: '/storage/1/foto.png') -> Se complementan con window.location.origin
 * - URLs absolutas externas (ej: 'https://images.unsplash.com/...') -> Se mantienen intactas
 * - URLs antiguas con localhost (ej: 'http://localhost:8000/storage/...') -> Se adaptan al host/IP actual del cliente
 * - Valores nulos, indefinidos o vacíos -> Retorna un valor por defecto o null sin errores
 * 
 * @param {string|null|undefined} path - Ruta o URL del archivo
 * @param {string|null} fallback - URL alternativa si no existe el recurso
 * @returns {string|null} URL completa resuelta para la etiqueta <img>
 */
export function assetUrl(path, fallback = null) {
    if (!path || typeof path !== 'string') {
        return fallback;
    }

    const cleanPath = path.trim();
    if (!cleanPath) {
        return fallback;
    }

    // 1. Si contiene 'localhost:8000/storage/' de registros guardados anteriormente,
    // reescribir dinámicamente con el origen del cliente actual (IP local, Tailscale, etc.)
    if (typeof window !== 'undefined' && cleanPath.includes('/storage/')) {
        const storageIndex = cleanPath.indexOf('/storage/');
        const relativeStoragePath = cleanPath.substring(storageIndex);
        return `${window.location.origin}${relativeStoragePath}`;
    }

    // 2. Si ya es una URL absoluta externa válida (ej: https://dominio.com/foto.jpg o data:image/...)
    if (/^(https?:|\/\/|data:|blob:)/i.test(cleanPath)) {
        return cleanPath;
    }

    // 3. Si es una ruta relativa que empieza con '/' (ej: '/storage/3/consultoria.png')
    const formattedPath = cleanPath.startsWith('/') ? cleanPath : `/${cleanPath}`;

    // Construir con el origin actual del navegador
    if (typeof window !== 'undefined' && window.location?.origin) {
        return `${window.location.origin}${formattedPath}`;
    }

    return formattedPath;
}

export default assetUrl;
