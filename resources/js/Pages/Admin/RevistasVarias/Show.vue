<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';

const props = defineProps({
    revista: {
        type: Object,
        required: true,
    },
});

const confirmingDeletion = ref(false);

const toggleActivo = () => {
    router.patch(route('admin.revistas-varias.toggle-activo', props.revista.id), {}, {
        preserveScroll: true,
    });
};

const deleteItem = () => {
    router.delete(route('admin.revistas-varias.destroy', props.revista.id), {
        onSuccess: () => {
            confirmingDeletion.value = false;
        },
    });
};

const formatDate = (dateString) => {
    if (!dateString) return '—';
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
    } catch {
        return dateString;
    }
};
</script>

<template>
    <AppLayout :title="revista.rev_titulo">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 w-full min-w-0">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-1.5 text-[11px] font-semibold text-slate-400 mb-0.5 leading-none">
                        <Link :href="route('dashboard')" class="hover:text-[#35449F]">Inicio</Link>
                        <span>/</span>
                        <Link :href="route('admin.revistas-varias.index')" class="hover:text-[#35449F]">Revistas de Referencia</Link>
                        <span>/</span>
                        <span class="text-slate-700 font-bold truncate">Detalle</span>
                    </div>
                    <h1 class="font-extrabold text-xl sm:text-2xl text-[#14161C] tracking-tight truncate leading-tight">
                        {{ revista.rev_titulo }}
                    </h1>
                </div>
                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    <Link :href="route('admin.revistas-varias.index')">
                        <SecondaryButton class="gap-1.5">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            <span>Volver</span>
                        </SecondaryButton>
                    </Link>
                    <Link :href="route('admin.revistas-varias.edit', revista.id)">
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

        <div class="max-w-4xl mx-auto space-y-6 pb-12">
            <!-- Ficha Principal -->
            <div class="bg-white rounded-2xl p-6 shadow-xs border border-gray-100 flex flex-col sm:flex-row items-start gap-6">
                <!-- Portada / Logo -->
                <div class="size-32 rounded-2xl bg-slate-100 border border-slate-200 overflow-hidden shrink-0 flex items-center justify-center p-2">
                    <img
                        v-if="revista.portada_url"
                        :src="revista.portada_url"
                        :alt="revista.rev_titulo"
                        class="w-full h-full object-contain"
                    />
                    <div v-else class="text-slate-400 text-center">
                        <svg class="size-10 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <span class="text-[10px] font-semibold mt-1 block">Sin imagen</span>
                    </div>
                </div>

                <!-- Datos Principales -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                        <span
                            class="px-2.5 py-0.5 rounded-full text-xs font-bold"
                            :class="revista.rev_activo ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'"
                        >
                            {{ revista.rev_activo ? 'Visible en Portal' : 'Oculta en Portal' }}
                        </span>
                        <span v-if="revista.rev_issn" class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-slate-100 text-slate-600">
                            ISSN: {{ revista.rev_issn }}
                        </span>
                    </div>

                    <h2 class="text-xl font-extrabold text-slate-900 leading-snug">
                        {{ revista.rev_titulo }}
                    </h2>

                    <p v-if="revista.rev_entidad" class="text-xs font-bold text-[#35449F] mt-1">
                        {{ revista.rev_entidad }}
                    </p>

                    <p v-if="revista.rev_descripcion" class="text-sm text-slate-600 mt-3 leading-relaxed whitespace-pre-line">
                        {{ revista.rev_descripcion }}
                    </p>

                    <!-- Botón Enlace Externo Directo -->
                    <div class="mt-5 pt-4 border-t border-slate-100 flex items-center gap-3">
                        <a
                            :href="revista.rev_url_enlace"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#35449F] text-white rounded-xl text-xs font-bold hover:bg-[#253275] transition shadow-xs"
                        >
                            <span>Visitar Sitio Web Oficial</span>
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                        <button
                            type="button"
                            @click="toggleActivo"
                            class="px-3 py-2 rounded-xl text-xs font-bold transition cursor-pointer"
                            :class="revista.rev_activo ? 'bg-slate-100 text-slate-600 hover:bg-slate-200' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100'"
                        >
                            {{ revista.rev_activo ? 'Ocultar del portal' : 'Publicar en el portal' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Ficha Técnica -->
            <div class="bg-white rounded-2xl p-6 shadow-xs border border-gray-100 space-y-4">
                <h3 class="text-sm font-extrabold text-slate-800">Detalles Técnicos</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 block font-semibold">Enlace Configurado</span>
                        <a
                            :href="revista.rev_url_enlace"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="font-mono text-[#35449F] hover:underline break-all mt-0.5 block"
                        >
                            {{ revista.rev_url_enlace }}
                        </a>
                    </div>
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 block font-semibold">Prioridad / Orden</span>
                        <span class="font-bold text-slate-800 text-sm mt-0.5 block">{{ revista.rev_orden }}</span>
                    </div>
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 block font-semibold">Fecha de Registro</span>
                        <span class="font-bold text-slate-800 mt-0.5 block">{{ formatDate(revista.created_at) }}</span>
                    </div>
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 block font-semibold">Última Actualización</span>
                        <span class="font-bold text-slate-800 mt-0.5 block">{{ formatDate(revista.updated_at) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Confirmación Eliminación -->
        <ConfirmationModal :show="confirmingDeletion" @close="confirmingDeletion = false">
            <template #title>
                Eliminar Revista de Referencia
            </template>
            <template #content>
                ¿Está seguro de que desea eliminar la revista
                <span class="font-bold text-slate-900">"{{ revista.rev_titulo }}"</span>?
                Esta operación no se puede revertir.
            </template>
            <template #footer>
                <SecondaryButton @click="confirmingDeletion = false" class="mr-2">
                    Cancelar
                </SecondaryButton>
                <DangerButton @click="deleteItem">
                    Sí, eliminar revista
                </DangerButton>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>
