<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import Pagination from '@/Components/Pagination.vue';
import ExcelImportModal from '@/Components/ExcelImportModal.vue';

const props = defineProps({
    bancoDatos: {
        type: [Array, Object],
        default: () => ({ data: [] }),
    },
    filters: {
        type: Object,
        default: () => ({ search: '', tipo: '', per_page: 10 }),
    },
});

const list = computed(() => {
    if (Array.isArray(props.bancoDatos)) {
        return props.bancoDatos;
    }
    return props.bancoDatos?.data || [];
});

const search = ref(props.filters?.search || '');
const tipo = ref(props.filters?.tipo || '');
const perPage = ref(props.filters?.per_page || 10);
let searchTimeout = null;

const applyFilters = () => {
    router.get(
        route('admin.banco-datos.index'),
        {
            search: search.value ? search.value : undefined,
            tipo: tipo.value ? tipo.value : undefined,
            per_page: perPage.value !== 10 ? perPage.value : undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

const onSearchInput = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 350);
};

const clearSearch = () => {
    search.value = '';
    applyFilters();
};

const confirmingDeletion = ref(false);
const itemToDelete = ref(null);

const confirmDelete = (item) => {
    itemToDelete.value = item;
    confirmingDeletion.value = true;
};

const deleteItem = () => {
    if (!itemToDelete.value) return;

    router.delete(route('admin.banco-datos.destroy', itemToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            confirmingDeletion.value = false;
            itemToDelete.value = null;
        },
    });
};

const showImportModal = ref(false);

const exportUrl = computed(() => {
    const params = new URLSearchParams();
    if (search.value) params.append('search', search.value);
    if (tipo.value) params.append('tipo', tipo.value);
    const qs = params.toString();
    return route('admin.banco-datos.export') + (qs ? `?${qs}` : '');
});

const bancoDatosColumns = [
    { name: 'tipo_dato', label: 'Tipo de Base', required: true, desc: 'Clasificación: "Censos" o "Encuestas"', ejemplo: 'Censos' },
    { name: 'anio', label: 'Año', required: true, desc: 'Año de realización (1900-2100)', ejemplo: '1992' },
    { name: 'codigo', label: 'Código Único', required: true, desc: 'Código de identificación único del microdato', ejemplo: 'cs92v1' },
    { name: 'titulo', label: 'Título de la Base', required: true, desc: 'Nombre descriptivo oficial de la base', ejemplo: 'INE - Censo Nacional de Población y Vivienda 1992' },
    { name: 'locacion', label: 'Locación / Cobertura', required: false, desc: 'Ámbito geográfico (Bolivia, La Paz, etc.)', ejemplo: 'Bolivia' },
    { name: 'url', label: 'Enlace Externo', required: false, desc: 'URL al repositorio, drive o portal del INE', ejemplo: 'https://ine.gob.bo/censo1992' },
    { name: 'areas_tematicas', label: 'Áreas Temáticas', required: false, desc: 'Temáticas separadas por comas (se crean si no existen)', ejemplo: 'Demografía, Población y Vivienda' },
    { name: 'descripcion', label: 'Descripción', required: false, desc: 'Resumen o notas metodológicas', ejemplo: 'Base de datos oficial con microdatos del Censo.' },
];

const bancoDatosSampleRows = [
    {
        tipo_dato: 'Censos',
        anio: 1992,
        codigo: 'cs92v1',
        titulo: 'INE - Censo Nacional de Población y Vivienda 1992',
        locacion: 'Bolivia',
        url: 'https://ine.gob.bo/censo1992',
        areas_tematicas: 'Demografía, Población y Vivienda',
        descripcion: 'Base de datos oficial con microdatos del Censo 1992.',
    },
    {
        tipo_dato: 'Encuestas',
        anio: 2023,
        codigo: 'eh2023v1',
        titulo: 'INE - Encuesta de Hogares 2023 (EH-2023)',
        locacion: 'Bolivia',
        url: 'https://ine.gob.bo/encuesta-hogares-2023',
        areas_tematicas: 'Pobreza, Empleo, Ingresos',
        descripcion: 'Microdatos para medición de pobreza multidimensional y mercado laboral.',
    },
];
</script>

<template>
    <AppLayout title="Banco de Datos">
        <template #header>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 w-full">
                <div>
                    <h1 class="font-extrabold text-2xl text-[#14161C] tracking-tight">
                        Banco de Datos Estadísticos
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500 font-medium">
                        Repositorio de censos, encuestas nacionales y microdatos para investigación.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    <!-- Exportar Excel -->
                    <a
                        :href="exportUrl"
                        class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-gray-700 bg-white hover:bg-gray-50 border border-gray-200 rounded-xl shadow-xs transition-colors whitespace-nowrap cursor-pointer"
                        download
                        title="Exportar banco de datos a formato Excel"
                    >
                        <svg class="size-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span class="hidden sm:inline">Exportar Excel</span>
                        <span class="sm:hidden">Excel</span>
                    </a>

                    <!-- Importar Excel / Plantilla -->
                    <button
                        type="button"
                        @click="showImportModal = true"
                        class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-xl shadow-xs transition-colors whitespace-nowrap cursor-pointer"
                        title="Cargar bases de datos masivamente desde Excel o ver la plantilla en vivo"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        <span class="hidden sm:inline">Importar / Plantilla</span>
                        <span class="sm:hidden">Plantilla</span>
                    </button>

                    <!-- Nueva Base de Datos -->
                    <Link :href="route('admin.banco-datos.create')">
                        <PrimaryButton class="cursor-pointer gap-1.5 whitespace-nowrap">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            <span>Nueva Base de Datos</span>
                        </PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-6 pb-12">
            <!-- Filtros y Buscador -->
            <div class="bg-white rounded-2xl p-4 shadow-xs border border-gray-100 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input
                        v-model="search"
                        @input="onSearchInput"
                        type="text"
                        placeholder="Buscar por código, título, locación o área temática..."
                        class="w-full pl-10 pr-10 py-2.5 bg-gray-50/50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 transition"
                    />
                    <button
                        v-if="search"
                        @click="clearSearch"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 cursor-pointer"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Filtro por Tipo -->
                <div class="flex items-center gap-2 shrink-0">
                    <label for="tipo_filter" class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                        Tipo:
                    </label>
                    <select
                        id="tipo_filter"
                        v-model="tipo"
                        @change="applyFilters"
                        class="text-sm font-semibold rounded-xl border-gray-200 py-2 pl-3 pr-8 bg-gray-50 focus:bg-white focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10"
                    >
                        <option value="">Todos los tipos</option>
                        <option value="Censos">Censos</option>
                        <option value="Encuestas">Encuestas</option>
                    </select>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <label for="per_page" class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                        Mostrar:
                    </label>
                    <select
                        id="per_page"
                        v-model="perPage"
                        @change="applyFilters"
                        class="text-sm font-semibold rounded-xl border-gray-200 py-2 pl-3 pr-8 bg-gray-50 focus:bg-white focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10"
                    >
                        <option :value="10">10</option>
                        <option :value="25">25</option>
                        <option :value="50">50</option>
                    </select>
                </div>
            </div>

            <!-- Tabla -->
            <div class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-600 font-extrabold uppercase text-xs tracking-wider border-b border-gray-100">
                            <tr>
                                <th scope="col" class="py-4 px-6 w-16 text-center">#</th>
                                <th scope="col" class="py-4 px-6">Código & Título</th>
                                <th scope="col" class="py-4 px-6 text-center">Tipo / Año</th>
                                <th scope="col" class="py-4 px-6">Locación</th>
                                <th scope="col" class="py-4 px-6">Áreas Temáticas</th>
                                <th scope="col" class="py-4 px-6 text-center">Descarga</th>
                                <th scope="col" class="py-4 px-6 text-right w-36">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="list.length === 0">
                                <td colspan="7" class="py-12 text-center text-gray-400">
                                    <p class="font-bold text-[#14161C]">No hay bases de datos registradas</p>
                                </td>
                            </tr>
                            <tr
                                v-for="(item, index) in list"
                                :key="item.id"
                                class="hover:bg-gray-50/70 transition"
                            >
                                <td class="py-4 px-6 font-mono text-xs text-gray-500 text-center font-bold">
                                    {{ (props.bancos?.from ? props.bancos.from + index : index + 1) }}
                                </td>
                                <td class="py-4 px-6 max-w-sm">
                                    <div class="flex items-center gap-2 mb-0.5">
                                        <span class="font-mono text-xs px-2 py-0.5 rounded bg-gray-100 font-bold text-gray-700">
                                            {{ item.bdd_codigo }}
                                        </span>
                                    </div>
                                    <Link
                                        :href="route('admin.banco-datos.show', item.id)"
                                        class="font-bold text-[#14161C] hover:text-[#35449F] transition line-clamp-1"
                                    >
                                        {{ item.bdd_titulo }}
                                    </Link>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <div class="flex flex-col items-center gap-1">
                                        <span
                                            :class="item.bdd_tipo_dato === 'Censos' ? 'bg-purple-50 text-purple-700' : 'bg-emerald-50 text-emerald-700'"
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold"
                                        >
                                            {{ item.bdd_tipo_dato }}
                                        </span>
                                        <span class="font-mono text-xs text-gray-500 font-bold">
                                            {{ item.bdd_anio }}
                                        </span>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-50 text-gray-700 border border-gray-100">
                                        📍 {{ item.bdd_locacion || 'No especificado' }}
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <div v-if="item.areas_tematicas?.length" class="flex flex-wrap gap-1 max-w-xs">
                                        <span
                                            v-for="area in item.areas_tematicas"
                                            :key="area.id"
                                            class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-indigo-50 text-[#35449F]"
                                        >
                                            {{ area.at_nombre }}
                                        </span>
                                    </div>
                                    <span v-else class="text-xs text-gray-400 italic">Sin áreas asignadas</span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <div class="flex flex-col items-center gap-1">
                                        <a
                                            v-if="item.archivo_url"
                                            :href="item.archivo_url"
                                            target="_blank"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-[#35449F]/10 text-[#35449F] hover:bg-[#35449F]/20 transition"
                                            title="Descargar archivo del servidor"
                                        >
                                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            Archivo
                                        </a>
                                        <a
                                            v-if="item.bdd_url"
                                            :href="item.bdd_url"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-purple-50 text-purple-700 hover:bg-purple-100 transition"
                                            title="Abrir enlace externo en la nube (Drive/Dropbox/OneDrive)"
                                        >
                                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                            </svg>
                                            Enlace
                                        </a>
                                        <span v-if="!item.archivo_url && !item.bdd_url" class="text-xs text-gray-300">—</span>
                                    </div>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <Link
                                            :href="route('admin.banco-datos.show', item.id)"
                                            class="p-2 text-gray-400 hover:text-[#35449F] hover:bg-indigo-50 rounded-lg transition"
                                            title="Ver Base"
                                        >
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.264 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </Link>
                                        <Link
                                            :href="route('admin.banco-datos.edit', item.id)"
                                            class="p-2 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition"
                                            title="Editar"
                                        >
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </Link>
                                        <button
                                            @click="confirmDelete(item)"
                                            type="button"
                                            class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition cursor-pointer"
                                            title="Eliminar"
                                        >
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="bancoDatos?.meta || bancoDatos?.links" class="p-4 bg-gray-50/50">
                    <Pagination :meta="bancoDatos" />
                </div>
            </div>
        </div>

        <ConfirmationModal :show="confirmingDeletion" @close="confirmingDeletion = false">
            <template #title>Eliminar Base de Datos</template>
            <template #content>
                ¿Estás seguro de que deseas eliminar la base de datos
                <span class="font-bold text-[#14161C]">"{{ itemToDelete?.bdd_titulo }}"</span>?
            </template>
            <template #footer>
                <SecondaryButton @click="confirmingDeletion = false" class="mr-2 cursor-pointer">Cancelar</SecondaryButton>
                <DangerButton @click="deleteItem" class="cursor-pointer">Eliminar</DangerButton>
            </template>
        </ConfirmationModal>

        <!-- Modal de Importación Masiva y Plantilla en Vivo -->
        <ExcelImportModal
            :show="showImportModal"
            title="Importación Masiva de Bases de Datos"
            subtitle="Carga y actualiza censos, encuestas y microdatos rápidamente mediante una plantilla Excel (.xlsx o .csv)."
            :plantilla-url="route('admin.banco-datos.plantilla')"
            :import-url="route('admin.banco-datos.import')"
            :columns="bancoDatosColumns"
            :sample-rows="bancoDatosSampleRows"
            @close="showImportModal = false"
        />
    </AppLayout>
</template>
