/**
 * Convierte un texto en un slug amigable para URLs en tiempo real.
 * Soporta tildes, caracteres especiales, acentos y mayúsculas.
 *
 * @param {string} text
 * @returns {string}
 */
export function slugify(text) {
    if (!text) return '';

    return text
        .toString()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '') // Elimina acentos/tildes
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9\s-]/g, '')   // Remueve caracteres que no sean letras o números
        .replace(/[\s_-]+/g, '-')       // Reemplaza espacios y guiones bajos por un guión
        .replace(/^-+|-+$/g, '');       // Remueve guiones al inicio y al final
}

export default slugify;
