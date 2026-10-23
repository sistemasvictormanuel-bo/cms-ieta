<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import EditorRenderer from '@/Components/EditorRenderer.vue';
import { assetUrl } from '@/utils/asset';

const props = defineProps({
    servicio: {
        type: Object,
        required: true,
    },
});

const formatDate = (dateStr) => {
    if (!dateStr) return 'N/A';
    return new Date(dateStr).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};
</script>

<template>
    <AppLayout :title="servicio.sv_titulo || 'Detalle del Servicio'">
        <template #header>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 w-full">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Servicio</span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#35449F]/10 text-[#35449F]">
                            Activo
                        </span>
                    </div>
                    <h1 class="font-extrabold text-2xl text-[#14161C] tracking-tight">
                        {{ servicio.sv_titulo }}
                    </h1>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <Link :href="route('admin.servicios.index')">
                        <SecondaryButton class="cursor-pointer">
                            ← Volver al listado
                        </SecondaryButton>
                    </Link>
                    <Link :href="route('admin.servicios.edit', servicio.id)">
                        <PrimaryButton class="cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Editar Servicio
                        </PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="max-w-5xl mx-auto space-y-6 pb-12">
            <!-- Portada y Resumen -->
            <div class="bg-white rounded-2xl border border-gray-100/90 shadow-[6px_6px_20px_rgba(0,0,0,0.06),-6px_-6px_20px_rgba(255,255,255,0.9)] overflow-hidden">
                <!-- Imagen Principal -->
                <div v-if="servicio.imagen_url" class="relative w-full h-72 sm:h-96 overflow-hidden bg-gray-100">
                    <img
                        :src="assetUrl(servicio.imagen_url)"
                        :alt="servicio.sv_titulo"
                        class="w-full h-full object-cover"
                    />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                    <div class="absolute bottom-6 left-6 right-6 text-white">
                        <h2 class="text-2xl sm:text-3xl font-extrabold drop-shadow-md">
                            {{ servicio.sv_titulo }}
                        </h2>
                    </div>
                </div>

                <!-- Resumen y Metadatos -->
                <div class="p-6 md:p-8 space-y-5">
                    <div v-if="!servicio.imagen_url">
                        <h2 class="text-2xl font-extrabold text-[#14161C]">
                            {{ servicio.sv_titulo }}
                        </h2>
                    </div>

                    <div v-if="servicio.sv_descripcion">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">
                            Descripción Resumida
                        </h3>
                        <p class="text-base text-gray-700 leading-relaxed font-medium">
                            {{ servicio.sv_descripcion }}
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-6 pt-4 border-t border-gray-100 text-xs text-gray-500 font-semibold">
                        <div>
                            Publicado:
                            <span class="text-[#14161C] font-bold">{{ formatDate(servicio.created_at) }}</span>
                        </div>
                        <div>
                            Última actualización:
                            <span class="text-[#14161C] font-bold">{{ formatDate(servicio.updated_at) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contenido Enriquecido Completo -->
            <div class="bg-white rounded-2xl border border-gray-100/90 shadow-[6px_6px_20px_rgba(0,0,0,0.06),-6px_-6px_20px_rgba(255,255,255,0.9)] p-6 md:p-10">
                <div class="flex items-center space-x-3 pb-6 mb-6 border-b border-gray-100">
                    <div class="size-9 rounded-xl bg-[#35449F]/10 text-[#35449F] flex items-center justify-center font-bold">
                        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-extrabold text-[#14161C] tracking-tight">
                            Detalles y Alcance del Servicio
                        </h3>
                        <p class="text-xs text-gray-500">
                            Información completa y especificaciones técnicas redactadas por bloques.
                        </p>
                    </div>
                </div>

                <!-- Renderer de Editor.js -->
                <div class="prose max-w-none">
                    <EditorRenderer :content="servicio.sv_contenido" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
