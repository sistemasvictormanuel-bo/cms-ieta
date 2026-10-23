<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import EditorRenderer from '@/Components/EditorRenderer.vue';
import { assetUrl } from '@/utils/asset';

const props = defineProps({
    linea: {
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
    <AppLayout :title="linea.li_titulo || 'Detalle de Línea de Investigación'">
        <template #header>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 w-full">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Línea de Investigación</span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#35449F]/10 text-[#35449F]">
                            {{ linea.li_slug }}
                        </span>
                    </div>
                    <h1 class="font-extrabold text-2xl text-[#14161C] tracking-tight">
                        {{ linea.li_titulo }}
                    </h1>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <Link :href="route('admin.lineas-investigacion.index')">
                        <SecondaryButton>
                            ← Volver al listado
                        </SecondaryButton>
                    </Link>
                    <Link :href="route('admin.lineas-investigacion.edit', linea.id)">
                        <PrimaryButton>
                            Editar Línea
                        </PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="max-w-5xl mx-auto space-y-6">
            <!-- Portada y Resumen -->
            <div class="bg-white rounded-2xl border border-gray-100/90 shadow-[6px_6px_20px_rgba(0,0,0,0.06),-6px_-6px_20px_rgba(255,255,255,0.9)] overflow-hidden">
                <!-- Imagen de Portada -->
                <div v-if="linea.portada_url" class="relative w-full h-72 sm:h-96 overflow-hidden bg-gray-100">
                    <img
                        :src="assetUrl(linea.portada_url)"
                        :alt="linea.li_titulo"
                        class="w-full h-full object-cover"
                    />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                    <div class="absolute bottom-6 left-6 right-6 text-white">
                        <h2 class="text-2xl sm:text-3xl font-extrabold drop-shadow-md">
                            {{ linea.li_titulo }}
                        </h2>
                    </div>
                </div>

                <div class="p-6 md:p-8 space-y-6">
                    <!-- Resumen / Descripción Breve -->
                    <div class="bg-[#F5F5F8] border-l-4 border-[#35449F] rounded-r-xl p-4 sm:p-5">
                        <h3 class="text-xs font-extrabold uppercase tracking-wider text-[#35449F] mb-1.5">
                            Descripción / Resumen
                        </h3>
                        <p class="text-[15px] text-gray-700 leading-relaxed font-medium">
                            {{ linea.li_descripcion }}
                        </p>
                    </div>

                    <!-- Metadatos de la línea -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 pb-4 border-b border-gray-100 text-xs text-gray-500">
                        <div>
                            <span class="font-bold text-gray-700 block">Slug permanente:</span>
                            {{ linea.li_slug }}
                        </div>
                        <div>
                            <span class="font-bold text-gray-700 block">Última actualización:</span>
                            {{ formatDate(linea.updated_at) }}
                        </div>
                    </div>

                    <!-- Contenido Detallado (Editor.js) -->
                    <div>
                        <h3 class="text-xs font-extrabold uppercase tracking-wider text-gray-400 mb-4">
                            Contenido Detallado
                        </h3>
                        <div class="prose max-w-none">
                            <EditorRenderer :content="linea.li_contenido" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
