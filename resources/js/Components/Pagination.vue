<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    meta: {
        type: Object,
        required: true,
    },
});

const links = computed(() => {
    return props.meta?.links || [];
});

const hasPagination = computed(() => {
    return (props.meta?.total || 0) > 0 && links.value.length > 3;
});

const cleanLabel = (label) => {
    if (!label) return '';
    return label
        .replace('&laquo;', '‹')
        .replace('&raquo;', '›')
        .replace('Previous', 'Anterior')
        .replace('Next', 'Siguiente');
};
</script>

<template>
    <div v-if="meta && meta.total > 0" class="flex flex-col sm:flex-row items-center justify-between gap-4 py-4 px-2 border-t border-gray-100">
        <!-- Resumen de registros -->
        <div class="text-xs sm:text-sm text-gray-500 font-medium">
            Mostrando
            <span class="font-bold text-[#14161C]">{{ meta.from || 0 }}</span>
            a
            <span class="font-bold text-[#14161C]">{{ meta.to || 0 }}</span>
            de
            <span class="font-bold text-[#14161C]">{{ meta.total || 0 }}</span>
            registros
        </div>

        <!-- Botones de Paginación -->
        <nav v-if="hasPagination" class="inline-flex -space-x-px rounded-xl shadow-xs overflow-hidden" aria-label="Paginación">
            <template v-for="(link, index) in links" :key="index">
                <!-- Enlace activo o navegable -->
                <Link
                    v-if="link.url"
                    :href="link.url"
                    preserve-scroll
                    preserve-state
                    class="relative inline-flex items-center px-3.5 py-2 text-xs sm:text-sm font-semibold transition-colors duration-150 border border-gray-200"
                    :class="[
                        link.active
                            ? 'z-10 bg-[#35449F] border-[#35449F] text-white shadow-xs'
                            : 'bg-white text-gray-700 hover:bg-[#F5F5F8] hover:text-[#35449F]'
                    ]"
                >
                    <span v-html="cleanLabel(link.label)"></span>
                </Link>

                <!-- Enlace deshabilitado (ej. Anterior en pág 1 o elipses '...') -->
                <span
                    v-else
                    class="relative inline-flex items-center px-3.5 py-2 text-xs sm:text-sm font-semibold text-gray-400 bg-gray-50 border border-gray-200 cursor-not-allowed select-none"
                >
                    <span v-html="cleanLabel(link.label)"></span>
                </span>
            </template>
        </nav>
    </div>
</template>
