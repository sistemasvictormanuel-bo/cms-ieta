<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';

const props = defineProps({
    banco: {
        type: Object,
        required: true,
    },
});

const confirmingDeletion = ref(false);

const deleteItem = () => {
    router.delete(route('admin.banco-datos.destroy', props.banco.id));
};
</script>

<template>
    <AppLayout title="Detalle de Base de Datos">
        <template #header>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 w-full">
                <div>
                    <h1 class="font-extrabold text-2xl text-[#14161C] tracking-tight">
                        {{ banco.bdd_codigo }} - {{ banco.bdd_titulo }}
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500 font-medium">
                        Ficha técnica y archivos de microdatos del Banco de Datos.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Link :href="route('admin.banco-datos.index')">
                        <SecondaryButton class="cursor-pointer">
                            ← Volver al listado
                        </SecondaryButton>
                    </Link>
                    <a
                        v-if="banco.archivo_url"
                        :href="banco.archivo_url"
                        target="_blank"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-[#35449F]/10 text-[#35449F] hover:bg-[#35449F]/20 transition"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Descargar Microdatos
                    </a>
                    <a
                        v-if="banco.bdd_url"
                        :href="banco.bdd_url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-indigo-50 text-[#35449F] hover:bg-indigo-100 transition border border-indigo-200/60"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                        </svg>
                        Abrir en Nube / Drive
                    </a>
                    <Link :href="route('admin.banco-datos.edit', banco.id)">
                        <PrimaryButton class="cursor-pointer">
                            <svg class="size-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Editar
                        </PrimaryButton>
                    </Link>
                    <button
                        @click="confirmingDeletion = true"
                        type="button"
                        class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition cursor-pointer"
                        title="Eliminar"
                    >
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </template>

        <div class="space-y-6 pb-12 max-w-5xl mx-auto">
            <!-- Portada e Información Principal -->
            <div class="bg-white rounded-2xl overflow-hidden shadow-xs border border-gray-100">
                <div v-if="banco.portada_url" class="h-56 w-full overflow-hidden bg-gray-100">
                    <img :src="banco.portada_url" :alt="banco.bdd_titulo" class="w-full h-full object-cover" />
                </div>

                <div class="p-6 md:p-8">
                    <div class="flex items-center gap-2 flex-wrap mb-3">
                        <span class="font-mono text-xs px-2.5 py-1 rounded-lg bg-gray-100 font-bold text-gray-800">
                            {{ banco.bdd_codigo }}
                        </span>
                        <span
                            :class="banco.bdd_tipo_dato === 'Censos' ? 'bg-purple-50 text-purple-700' : 'bg-emerald-50 text-emerald-700'"
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold"
                        >
                            {{ banco.bdd_tipo_dato }}
                        </span>
                        <span class="font-mono text-xs font-bold px-2.5 py-1 rounded-full bg-blue-50 text-blue-700">
                            Año {{ banco.bdd_anio }}
                        </span>
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800">
                            📍 {{ banco.bdd_locacion || 'Bolivia' }}
                        </span>
                    </div>

                    <h2 class="text-2xl font-black text-[#14161C] leading-snug">
                        {{ banco.bdd_titulo }}
                    </h2>

                    <!-- Áreas Temáticas -->
                    <div class="mt-6 pt-6 border-t border-gray-100">
                        <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">
                            Áreas Temáticas Asignadas
                        </div>
                        <div v-if="banco.areas_tematicas?.length" class="flex flex-wrap gap-2">
                            <span
                                v-for="area in banco.areas_tematicas"
                                :key="area.id"
                                class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold bg-[#35449F]/10 text-[#35449F]"
                            >
                                {{ area.at_nombre }}
                            </span>
                        </div>
                        <span v-else class="text-xs text-gray-400 italic">Sin áreas temáticas vinculadas</span>
                    </div>
                </div>
            </div>

            <!-- Ficha Técnica / Metodología -->
            <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xs border border-gray-100">
                <h3 class="text-lg font-extrabold text-[#14161C] mb-4 pb-2 border-b border-gray-100 flex items-center gap-2">
                    <svg class="size-5 text-[#35449F]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Descripción y Ficha Metodológica
                </h3>
                <div v-if="banco.bdd_descripcion" class="text-sm text-gray-700 leading-relaxed whitespace-pre-line bg-gray-50/50 p-4 rounded-xl border border-gray-100">
                    {{ banco.bdd_descripcion }}
                </div>
                <div v-else class="text-sm text-gray-400 italic py-4 text-center">
                    No se ha redactado una descripción técnica para esta base de datos.
                </div>
            </div>

            <!-- Descarga de Archivos y Enlaces -->
            <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xs border border-gray-100">
                <h3 class="text-lg font-extrabold text-[#14161C] mb-4 pb-2 border-b border-gray-100 flex items-center gap-2">
                    <svg class="size-5 text-[#35449F]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Archivos y Enlaces de Microdatos
                </h3>

                <div class="space-y-3">
                    <!-- Archivo en Servidor -->
                    <div v-if="banco.archivo_url" class="p-4 rounded-xl bg-indigo-50/50 border border-indigo-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div>
                            <div class="font-bold text-sm text-[#14161C] flex items-center gap-2">
                                <span>Paquete de Microdatos en Servidor</span>
                                <span class="text-[11px] font-bold text-[#35449F] bg-indigo-100/70 px-2 py-0.5 rounded-full">Spatie Media</span>
                            </div>
                            <div class="text-xs text-gray-500 mt-0.5">Base en formato para procesamiento estadístico (CSV, SPSS, Excel o compresión ZIP).</div>
                        </div>
                        <a
                            :href="banco.archivo_url"
                            target="_blank"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-[#35449F] text-white hover:bg-[#2c3882] transition shrink-0"
                        >
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Descargar Archivo
                        </a>
                    </div>

                    <!-- Enlace Externo a la Nube (Drive, OneDrive, Dropbox, etc.) -->
                    <div v-if="banco.bdd_url" class="p-4 rounded-xl bg-purple-50/50 border border-purple-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div>
                            <div class="font-bold text-sm text-[#14161C] flex items-center gap-2">
                                <span>Repositorio en la Nube (Google Drive, OneDrive, etc.)</span>
                                <span class="text-[11px] font-bold text-purple-700 bg-purple-100 px-2 py-0.5 rounded-full">Enlace Externo</span>
                            </div>
                            <div class="text-xs text-gray-600 mt-0.5 break-all max-w-xl font-mono">{{ banco.bdd_url }}</div>
                        </div>
                        <a
                            :href="banco.bdd_url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-[#35449F] text-white hover:bg-[#2c3882] transition shrink-0"
                        >
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                            Abrir en Nube
                        </a>
                    </div>

                    <div v-if="!banco.archivo_url && !banco.bdd_url" class="text-sm text-gray-400 italic py-6 text-center">
                        No hay un archivo local ni un enlace externo adjunto a este registro.
                    </div>
                </div>
            </div>
        </div>

        <ConfirmationModal :show="confirmingDeletion" @close="confirmingDeletion = false">
            <template #title>Eliminar Base de Datos</template>
            <template #content>
                ¿Estás seguro de que deseas eliminar permanentemente esta base de datos?
            </template>
            <template #footer>
                <SecondaryButton @click="confirmingDeletion = false" class="mr-2 cursor-pointer">Cancelar</SecondaryButton>
                <DangerButton @click="deleteItem" class="cursor-pointer">Eliminar</DangerButton>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>
