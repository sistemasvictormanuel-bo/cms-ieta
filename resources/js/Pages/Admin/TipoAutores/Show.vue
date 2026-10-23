<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    tipo: {
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
    <AppLayout :title="tipo.ta_nombre || 'Detalle del Tipo de Autor'">
        <template #header>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 w-full">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-500">Tipo de Autor</span>
                        <span
                            :class="tipo.ta_estado ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-gray-100 text-gray-600 ring-gray-400/20'"
                            class="px-2.5 py-0.5 rounded-full text-xs font-bold ring-1"
                        >
                            {{ tipo.ta_estado ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>
                    <h1 class="font-extrabold text-2xl text-[#14161C] tracking-tight">{{ tipo.ta_nombre }}</h1>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <Link :href="route('admin.tipo-autores.index')">
                        <SecondaryButton class="cursor-pointer">← Volver al listado</SecondaryButton>
                    </Link>
                    <Link :href="route('admin.tipo-autores.edit', tipo.id)">
                        <PrimaryButton class="cursor-pointer">Editar</PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="max-w-4xl mx-auto space-y-6 pb-12">
            <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xs border border-gray-100 space-y-6">
                <div>
                    <h2 class="text-xs font-extrabold uppercase tracking-wider text-[#71589F] mb-1">Denominación del Rol</h2>
                    <p class="text-xl font-black text-[#14161C]">{{ tipo.ta_nombre }}</p>
                </div>

                <div>
                    <h2 class="text-xs font-extrabold uppercase tracking-wider text-[#71589F] mb-1">Descripción</h2>
                    <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ tipo.ta_descripcion || 'Sin descripción' }}</p>
                </div>

                <div class="pt-6 border-t border-gray-100 grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs text-gray-500">
                    <div>
                        <span class="font-bold text-[#14161C]">Fecha de Creación:</span> {{ formatDate(tipo.created_at) }}
                    </div>
                    <div>
                        <span class="font-bold text-[#14161C]">Última Actualización:</span> {{ formatDate(tipo.updated_at) }}
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
