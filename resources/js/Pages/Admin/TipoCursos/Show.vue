<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    tipoCurso: {
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
    <AppLayout :title="tipoCurso.tc_nombre || 'Detalle del Tipo de Curso'">
        <template #header>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 w-full">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-500">
                            Tipo de Curso
                        </span>
                    </div>
                    <h1 class="font-extrabold text-2xl text-[#14161C] tracking-tight">
                        {{ tipoCurso.tc_nombre }}
                    </h1>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <Link :href="route('admin.tipo-cursos.index')">
                        <SecondaryButton class="cursor-pointer">
                            ← Volver al listado
                        </SecondaryButton>
                    </Link>
                    <Link :href="route('admin.tipo-cursos.edit', tipoCurso.id)">
                        <PrimaryButton class="cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Editar
                        </PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="max-w-4xl mx-auto space-y-6 pb-12">
            <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xs border border-gray-100 space-y-6">
                <div>
                    <h2 class="text-xs font-extrabold uppercase tracking-wider text-[#71589F] mb-1">
                        Nombre de la Categoría
                    </h2>
                    <p class="text-xl font-black text-[#14161C]">
                        {{ tipoCurso.tc_nombre }}
                    </p>
                </div>

                <div>
                    <h2 class="text-xs font-extrabold uppercase tracking-wider text-[#71589F] mb-1">
                        Descripción
                    </h2>
                    <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">
                        {{ tipoCurso.tc_descripcion || 'No se ha registrado una descripción para este tipo de curso.' }}
                    </p>
                </div>

                <div class="pt-6 border-t border-gray-100 grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs text-gray-500">
                    <div>
                        <span class="font-bold text-[#14161C]">Fecha de Creación:</span>
                        {{ formatDate(tipoCurso.created_at) }}
                    </div>
                    <div>
                        <span class="font-bold text-[#14161C]">Última Actualización:</span>
                        {{ formatDate(tipoCurso.updated_at) }}
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
