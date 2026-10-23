<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import EditorRenderer from '@/Components/EditorRenderer.vue';
import { assetUrl } from '@/utils/asset';

const props = defineProps({
    noticia: {
        type: Object,
        required: true,
    },
});

const confirmingDeletion = ref(false);

const deleteItem = () => {
    router.delete(route('admin.noticias.destroy', props.noticia.id));
};

const formatDate = (dateStr) => {
    if (!dateStr) return 'N/A';
    const cleanStr = dateStr.includes('T') ? dateStr.split('T')[0] : dateStr;
    const parts = cleanStr.split('-');
    if (parts.length === 3) {
        return `${parts[2]}/${parts[1]}/${parts[0]}`;
    }
    return dateStr;
};

const getPersonaNombre = (persona) => {
    if (!persona) return 'Institucional (IETA - Dirección General)';
    return `${persona.p_nombre} ${persona.p_apellido_paterno || ''} ${persona.p_apellido_materno || ''}`.trim();
};
</script>

<template>
    <AppLayout :title="noticia.not_titulo || 'Detalle de la Noticia'">
        <template #header>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 w-full">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Noticia</span>
                        <span
                            class="px-2.5 py-0.5 rounded-full text-xs font-extrabold"
                            :class="noticia.not_publicado ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600'"
                        >
                            {{ noticia.not_publicado ? 'Publicada' : 'Borrador' }}
                        </span>
                        <span
                            v-if="noticia.not_destacado"
                            class="px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-[#35449F]/10 text-[#35449F] flex items-center gap-1"
                        >
                            <svg class="size-3 fill-current" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                            </svg>
                            Destacada
                        </span>
                    </div>
                    <h1 class="font-black text-2xl text-[#14161C] tracking-tight">
                        Detalle de Noticia
                    </h1>
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    <Link :href="route('admin.noticias.index')">
                        <SecondaryButton class="cursor-pointer">
                            ← Volver al Listado
                        </SecondaryButton>
                    </Link>
                    <Link :href="route('admin.noticias.edit', noticia.id)">
                        <PrimaryButton class="inline-flex items-center gap-1.5 cursor-pointer">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            <span>Editar Noticia</span>
                        </PrimaryButton>
                    </Link>
                    <DangerButton class="cursor-pointer" @click="confirmingDeletion = true">
                        Eliminar
                    </DangerButton>
                </div>
            </div>
        </template>

        <div class="max-w-4xl mx-auto space-y-6 pb-16">
            <!-- Portada Principal -->
            <div v-if="noticia.portada_url" class="rounded-2xl overflow-hidden border border-slate-200 shadow-sm bg-slate-900">
                <img
                    :src="assetUrl(noticia.portada_url)"
                    :alt="noticia.not_titulo"
                    class="w-full max-h-[420px] object-cover"
                />
            </div>

            <!-- Cabecera de la Noticia -->
            <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-100 shadow-xs space-y-5">
                <!-- Meta: Fecha y Enlace Externo -->
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4 text-xs font-bold text-slate-500">
                    <div class="flex items-center gap-2">
                        <svg class="size-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Fecha: {{ formatDate(noticia.not_fecha) }}</span>
                    </div>

                    <a
                        v-if="noticia.not_url"
                        :href="noticia.not_url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1.5 text-[#35449F] hover:underline"
                    >
                        <span>Fuente Oficial / Enlace Externo</span>
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                    </a>
                </div>

                <!-- Título -->
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight leading-snug">
                    {{ noticia.not_titulo }}
                </h1>

                <!-- Resumen / Bajada destacada -->
                <div v-if="noticia.not_descripcion" class="p-4 bg-slate-50 border-l-4 border-[#35449F] rounded-r-xl">
                    <p class="text-[15.5px] font-medium text-slate-700 leading-relaxed italic">
                        {{ noticia.not_descripcion }}
                    </p>
                </div>

                <!-- Ficha del Redactor / Autor -->
                <div class="flex items-center gap-3.5 pt-2">
                    <div class="size-11 rounded-full bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center text-sm font-black text-slate-700 shrink-0 shadow-2xs">
                        <img
                            v-if="noticia.persona?.foto_url"
                            :src="assetUrl(noticia.persona.foto_url)"
                            :alt="getPersonaNombre(noticia.persona)"
                            class="w-full h-full object-cover"
                        />
                        <span v-else>
                            {{ noticia.persona ? noticia.persona.p_nombre?.charAt(0).toUpperCase() : 'I' }}
                        </span>
                    </div>
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-wider text-slate-400">Redactor / Autor</p>
                        <p class="font-extrabold text-slate-900 text-[15px]">
                            {{ getPersonaNombre(noticia.persona) }}
                        </p>
                        <p v-if="noticia.persona?.p_correo" class="text-xs text-slate-500 font-medium">
                            {{ noticia.persona.p_correo }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Cuerpo del Contenido (EditorRenderer) -->
            <div class="bg-white rounded-2xl p-6 sm:p-8 border border-slate-100 shadow-xs space-y-4">
                <h2 class="font-extrabold text-[15px] text-slate-400 uppercase tracking-wider border-b border-slate-100 pb-3">
                    Contenido de la Noticia
                </h2>

                <div v-if="noticia.not_contenido && noticia.not_contenido.blocks && noticia.not_contenido.blocks.length > 0">
                    <EditorRenderer :content="noticia.not_contenido" />
                </div>
                <div v-else class="py-8 text-center text-slate-400 text-sm italic">
                    Esta noticia no contiene bloques de texto detallado.
                </div>
            </div>

            <!-- Metadatos de Auditoría -->
            <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-xs text-xs text-slate-400 flex flex-wrap items-center justify-between gap-3 font-medium">
                <span>Registrado el {{ formatDate(noticia.created_at) }}</span>
                <span>Última modificación: {{ formatDate(noticia.updated_at) }}</span>
            </div>
        </div>

        <!-- Modal de Confirmación de Eliminación -->
        <ConfirmationModal :show="confirmingDeletion" @close="confirmingDeletion = false">
            <template #title>
                <div class="flex items-center gap-2 text-rose-600 font-black">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>Eliminar Noticia</span>
                </div>
            </template>
            <template #content>
                <p class="text-sm text-slate-600">
                    ¿Estás seguro de que deseas eliminar permanentemente esta noticia?
                </p>
                <p class="text-xs text-slate-500 mt-2">
                    Se borrará el registro y su portada de los servidores.
                </p>
            </template>
            <template #footer>
                <SecondaryButton class="cursor-pointer" @click="confirmingDeletion = false">
                    Cancelar
                </SecondaryButton>
                <DangerButton class="ml-3 cursor-pointer" @click="deleteItem">
                    Sí, Eliminar
                </DangerButton>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>
