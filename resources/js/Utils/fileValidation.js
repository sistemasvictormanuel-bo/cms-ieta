import { toast } from 'vue3-toastify';

/**
 * Valida el tamaño de un archivo antes de cargarlo al formulario.
 * Si el archivo supera el límite (por defecto 20 MB), muestra una notificación toast
 * amigable sugiriendo optimizar el archivo o usar un enlace externo (Google Drive, Dropbox, etc.).
 *
 * @param {File|null} file - Objeto archivo obtenido del input file
 * @param {number} maxMb - Límite en Megabytes (por defecto 20)
 * @param {string} externalOptionName - Nombre de la opción externa para guiar al usuario
 * @returns {boolean} true si el archivo es válido o nulo; false si excede el tamaño
 */
export function validateFileSize(file, maxMb = 20, externalOptionName = '¿Enlace externo?') {
    if (!file) {
        return true;
    }

    const maxSizeBytes = maxMb * 1024 * 1024;
    if (file.size > maxSizeBytes) {
        const sizeMb = (file.size / (1024 * 1024)).toFixed(1);
        toast.warning(
            `El archivo seleccionado (${sizeMb} MB) supera el tamaño máximo permitido de ${maxMb} MB. Te recomendamos comprimir el documento o utilizar la opción "${externalOptionName}" para compartirlo desde Google Drive u otra nube.`
        );
        return false;
    }

    return true;
}

