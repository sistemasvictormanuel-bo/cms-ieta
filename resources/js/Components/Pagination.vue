<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    meta: {
        type: Object,
        default: () => ({}),
    },
    links: {
        type: Array,
        default: () => [],
    },
});

const links = computed(() => {
    if (props.links && props.links.length) {
        return props.links;
    }
    return props.meta?.links || props.meta?.meta?.links || [];
});

const totalRecords = computed(() => {
    return props.meta?.total ?? props.meta?.meta?.total ?? null;
});

const fromRecord = computed(() => {
    return props.meta?.from ?? props.meta?.meta?.from ?? null;
});

const toRecord = computed(() => {
    return props.meta?.to ?? props.meta?.meta?.to ?? null;
});

const hasPagination = computed(() => {
    return links.value.length > 3;
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
    <div v-if="(totalRecords !== null && totalRecords > 0) || hasPagination" class="flex flex-col sm:flex-row items-center justify-between gap-4 py-4 px-2 border-t border-gray-100">
        <!-- Resumen de registros -->
        <div v-if="totalRecords !== null" class="text-xs sm:text-sm text-gray-500 font-medium">
            Mostrando
            <span class="font-bold text-[#14161C]">{{ fromRecord || 0 }}</span>
            a
            <span class="font-bold text-[#14161C]">{{ toRecord || 0 }}</span>
            de
            <span class="font-bold text-[#14161C]">{{ totalRecords }}</span>
            registros
        </div>
        <div v-else></div>

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
