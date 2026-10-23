<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';

const props = defineProps({
    imagen: {
        type: Object,
        required: true,
    },
});

const confirmingDeletion = ref(false);
const copied = ref(false);

const copyUrl = () => {
    if (!props.imagen.imagen_url) return;
    const fullUrl = window.location.origin + props.imagen.imagen_url;
    navigator.clipboard.writeText(fullUrl).then(() => {
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2000);
    });
};

const toggleDestacado = () => {
    router.patch(route('admin.galeria.toggle-destacado', props.imagen.id), {}, {
        preserveScroll: true,
    });
};

const toggleActivo = () => {
    router.patch(route('admin.galeria.toggle-activo', props.imagen.id), {}, {
        preserveScroll: true,
    });
};

const deleteItem = () => {
    router.delete(route('admin.galeria.destroy', props.imagen.id), {
        onSuccess: () => {
            confirmingDeletion.value = false;
        },
    });
};

const formatDate = (dateString) => {
    if (!dateString) return '—';
    try {
        const date = new Date(dateString.includes('T') ? dateString : dateString + 'T00:00:00');
        return date.toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
    } catch {
        return dateString;
    }
};

const mediaItem = props.imagen.media?.[0] || null;

const formatFileSize = (bytes) => {
    if (!bytes) return '—';
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return `${(bytes / Math.pow(1024, i)).toFixed(2)} ${sizes[i]}`;
};
</script>

<template>
    <AppLayout :title="imagen.gal_titulo">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 w-full min-w-0">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-1.5 text-[11px] font-semibold text-slate-400 mb-0.5 leading-none">
                        <Link :href="route('dashboard')" class="hover:text-[#35449F]">Inicio</Link>
                        <span>/</span>
                        <Link :href="route('admin.galeria.index')" class="hover:text-[#35449F]">Galería</Link>
                        <span>/</span>
                        <span class="text-slate-700 font-bold truncate">Detalle</span>
                    </div>
                    <h1 class="font-extrabold text-xl sm:text-2xl text-[#14161C] tracking-tight truncate leading-tight">
                        {{ imagen.gal_titulo }}
                    </h1>
                </div>
                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    <Link :href="route('admin.galeria.index')">
                        <SecondaryButton class="gap-1.5">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            <span>Volver</span>
                        </SecondaryButton>
                    </Link>
                    <Link :href="route('admin.galeria.edit', imagen.id)">
                        <PrimaryButton class="gap-1.5">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            <span>Editar</span>
                        </PrimaryButton>
                    </Link>
                    <DangerButton @click="confirmingDeletion = true" class="gap-1.5 cursor-pointer">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <span>Eliminar</span>
                    </DangerButton>
                </div>
            </div>
        </template>

        <div class="max-w-5xl mx-auto space-y-6 pb-12">
            <!-- Visor Principal de la Imagen -->
            <div class="bg-slate-900 rounded-3xl overflow-hidden shadow-md border border-slate-800">
                <div class="relative min-h-[300px] max-h-[580px] flex items-center justify-center p-4 bg-slate-950/60">
                    <img
                        v-if="imagen.imagen_url"
                        :src="imagen.imagen_url"
                        :alt="imagen.gal_titulo"
                        class="max-h-[520px] w-auto max-w-full object-contain rounded-xl shadow-lg"
                    />
                    <div v-else class="text-slate-400 text-center py-16">
                        <svg class="size-16 mx-auto stroke-1 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="text-sm font-semibold mt-2">Esta entrada no tiene un archivo gráfico asociado.</p>
                    </div>

                    <!-- Botón para ver en pestaña completa -->
                    <a
                        v-if="imagen.imagen_url"
                        :href="imagen.imagen_url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="absolute bottom-4 right-4 px-3.5 py-2 rounded-xl bg-black/70 hover:bg-black/90 text-white text-xs font-bold backdrop-blur-md transition flex items-center gap-1.5 shadow-md"
                    >
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        <span>Abrir tamaño completo</span>
                    </a>
                </div>

                <!-- Barra de Herramientas de la Imagen -->
                <div class="px-6 py-4 bg-slate-900 border-t border-slate-800 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 text-xs text-slate-300">
                    <div class="flex items-center gap-3">
                        <span class="px-2.5 py-1 rounded-lg font-bold bg-white/10 text-white">
                            {{ imagen.gal_categoria }}
                        </span>
                        <span v-if="imagen.gal_lugar" class="flex items-center gap-1 text-slate-400">
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            </svg>
                            {{ imagen.gal_lugar }}
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <button
                            v-if="imagen.imagen_url"
                            type="button"
                            @click="copyUrl"
                            class="px-3 py-1.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-semibold transition cursor-pointer flex items-center gap-1.5"
                        >
                            <svg v-if="!copied" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                            </svg>
                            <svg v-else class="size-3.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>{{ copied ? '¡URL Copiada al Portapapeles!' : 'Copiar URL Pública' }}</span>
                        </button>

                        <button
                            type="button"
                            @click="toggleDestacado"
                            class="px-3 py-1.5 rounded-xl font-semibold transition cursor-pointer flex items-center gap-1.5"
                            :class="imagen.gal_destacado ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : 'bg-white/10 text-slate-300 hover:text-amber-300'"
                        >
                            <svg class="size-3.5 fill-current" viewBox="0 0 24 24">
                                <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" />
                            </svg>
                            <span>{{ imagen.gal_destacado ? 'Destacada' : 'Destacar' }}</span>
                        </button>

                        <button
                            type="button"
                            @click="toggleActivo"
                            class="px-3 py-1.5 rounded-xl font-semibold transition cursor-pointer flex items-center gap-1.5"
                            :class="imagen.gal_activo ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-red-500/20 text-red-300 border border-red-500/30'"
                        >
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <span>{{ imagen.gal_activo ? 'Visible en Portal' : 'Oculta en Portal' }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Ficha Técnica y Metadatos -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Columna Izquierda / Central: Descripción y Datos -->
                <div class="lg:col-span-2 bg-white rounded-2xl p-6 shadow-xs border border-gray-100 space-y-5">
                    <div>
                        <h2 class="text-lg font-black text-slate-800">Descripción & Contexto</h2>
                        <p v-if="imagen.gal_descripcion" class="mt-2 text-sm text-slate-600 leading-relaxed whitespace-pre-line">
                            {{ imagen.gal_descripcion }}
                        </p>
                        <p v-else class="mt-2 text-xs text-slate-400 italic">
                            Sin descripción o pie de foto registrado para esta imagen.
                        </p>
                    </div>

                    <div class="pt-4 border-t border-slate-100">
                        <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-400 mb-3">
                            Metadatos del Evento
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                                <span class="text-slate-400 block font-semibold">Fecha del Acontecimiento</span>
                                <span class="font-bold text-slate-800 text-sm mt-0.5 block">
                                    {{ formatDate(imagen.gal_fecha) }}
                                </span>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                                <span class="text-slate-400 block font-semibold">Ubicación / Lugar</span>
                                <span class="font-bold text-slate-800 text-sm mt-0.5 block">
                                    {{ imagen.gal_lugar || 'No especificada' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Enlace público directo -->
                    <div v-if="imagen.imagen_url" class="pt-4 border-t border-slate-100">
                        <label class="text-xs font-bold text-slate-500 block mb-1.5">
                            Ruta relativa / Enlace para incrustar en el sitio web:
                        </label>
                        <div class="flex items-center gap-2">
                            <input
                                type="text"
                                readonly
                                :value="imagen.imagen_url"
                                class="flex-1 text-xs font-mono bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-600 select-all"
                            />
                            <button
                                type="button"
                                @click="copyUrl"
                                class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold transition cursor-pointer"
                            >
                                {{ copied ? 'Copiado' : 'Copiar' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Columna Derecha: Configuración y Archivo -->
                <div class="bg-white rounded-2xl p-6 shadow-xs border border-gray-100 space-y-4">
                    <h2 class="text-base font-extrabold text-slate-800">Detalles Técnicos</h2>

                    <div class="space-y-3 text-xs">
                        <div class="flex items-center justify-between py-2 border-b border-slate-100">
                            <span class="text-slate-400 font-semibold">Estado de Visualización</span>
                            <span
                                class="px-2.5 py-0.5 rounded-full font-bold"
                                :class="imagen.gal_activo ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'"
                            >
                                {{ imagen.gal_activo ? 'Activa' : 'Oculta' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between py-2 border-b border-slate-100">
                            <span class="text-slate-400 font-semibold">Destacada</span>
                            <span
                                class="px-2.5 py-0.5 rounded-full font-bold"
                                :class="imagen.gal_destacado ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-500'"
                            >
                                {{ imagen.gal_destacado ? 'Sí (Carrusel)' : 'No' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between py-2 border-b border-slate-100">
                            <span class="text-slate-400 font-semibold">Prioridad / Orden</span>
                            <span class="font-bold text-slate-800 font-mono">{{ imagen.gal_orden }}</span>
                        </div>

                        <div v-if="mediaItem" class="flex items-center justify-between py-2 border-b border-slate-100">
                            <span class="text-slate-400 font-semibold">Peso del Archivo</span>
                            <span class="font-bold text-slate-800 font-mono">{{ formatFileSize(mediaItem.size) }}</span>
                        </div>

                        <div v-if="mediaItem" class="flex items-center justify-between py-2 border-b border-slate-100">
                            <span class="text-slate-400 font-semibold">Tipo MIME</span>
                            <span class="font-bold text-slate-800 font-mono">{{ mediaItem.mime_type }}</span>
                        </div>

                        <div class="flex items-center justify-between py-2 border-b border-slate-100">
                            <span class="text-slate-400 font-semibold">Registrado en</span>
                            <span class="font-semibold text-slate-700">{{ formatDate(imagen.created_at) }}</span>
                        </div>

                        <div class="flex items-center justify-between py-2">
                            <span class="text-slate-400 font-semibold">Última Modificación</span>
                            <span class="font-semibold text-slate-700">{{ formatDate(imagen.updated_at) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Confirmación para Eliminar -->
        <ConfirmationModal :show="confirmingDeletion" @close="confirmingDeletion = false">
            <template #title>
                Eliminar Imagen de la Galería
            </template>
            <template #content>
                ¿Está seguro de que desea eliminar la fotografía
                <span class="font-bold text-slate-900">"{{ imagen.gal_titulo }}"</span>?
                Esta operación es irreversible.
            </template>
            <template #footer>
                <SecondaryButton @click="confirmingDeletion = false" class="mr-2">
                    Cancelar
                </SecondaryButton>
                <DangerButton @click="deleteItem">
                    Sí, eliminar imagen
                </DangerButton>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>
