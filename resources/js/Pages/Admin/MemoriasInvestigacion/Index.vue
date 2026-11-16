<script setup>
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import Pagination from '@/Components/Pagination.vue';
import ExcelImportModal from '@/Components/ExcelImportModal.vue';

const props = defineProps({
    memorias: {
        type: [Array, Object],
        default: () => ({ data: [] }),
    },
    autores: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({ search: '', autor_id: null, etiqueta: '', per_page: 10 }),
    },
});

const page = usePage();

const list = computed(() => {
    if (Array.isArray(props.memorias)) {
        return props.memorias;
    }
    return props.memorias?.data || [];
});

const search = ref(props.filters?.search || '');
const autorId = ref(props.filters?.autor_id || '');
const etiqueta = ref(props.filters?.etiqueta || '');
const perPage = ref(props.filters?.per_page || 10);
let searchTimeout = null;

const applyFilters = () => {
    router.get(
        route('admin.memorias-investigacion.index'),
        {
            search: search.value ? search.value : undefined,
            autor_id: autorId.value ? autorId.value : undefined,
            etiqueta: etiqueta.value ? etiqueta.value : undefined,
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

    router.delete(route('admin.memorias-investigacion.destroy', itemToDelete.value.id), {
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
    if (autorId.value) params.append('autor_id', autorId.value);
    if (etiqueta.value) params.append('etiqueta', etiqueta.value);
    const qs = params.toString();
    return route('admin.memorias-investigacion.export') + (qs ? `?${qs}` : '');
});

const memoriasColumns = [
    { name: 'titulo', label: 'Título', required: true, desc: 'Nombre del proyecto o memoria de investigación', ejemplo: 'Análisis estadístico del crecimiento poblacional estudiantil en la UMSA' },
    { name: 'autor', label: 'Investigador / Docente', required: true, desc: 'Nombre del docente o autor responsable', ejemplo: 'M.Sc. Nicolás Chávez Quisbert' },
    { name: 'gestion', label: 'Gestión / Año', required: true, desc: 'Año académico de la investigación (ej: 2015, 2024)', ejemplo: '2015' },
    { name: 'etiqueta', label: 'Tipo / Etiqueta', required: false, desc: 'Clasificación ("Proyecto de Investigación", "Proyecto de Interacción Social", "Memoria Anual")', ejemplo: 'Proyecto de Investigación' },
    { name: 'descripcion', label: 'Descripción / Resumen', required: false, desc: 'Abstract, resolución HCF o notas de archivo físico', ejemplo: 'Aprobado con Resolución del HCF Nº 780/2015. Documento en físico.' },
    { name: 'enlace_url', label: 'Enlace Nube (Drive)', required: false, desc: 'Enlace externo a Google Drive o repositorio digital', ejemplo: 'https://drive.google.com/file/d/...' },
];

const sampleRows = [
    {
        titulo: 'Análisis estadístico del crecimiento poblacional estudiantil en la Universidad Mayor de San Andrés',
        autor: 'M.Sc. Nicolás Chávez Quisbert',
        gestion: 2015,
        etiqueta: 'Proyecto de Investigación',
        descripcion: 'Aprobado con Resolución del HCF Nº 780/2015. Documento en físico existente en los archivos de la Carrera.',
        enlace_url: 'https://drive.google.com/file/d/1k0-XxsSjMAefO9nJYz6JispbUhxPDWNN/view',
    },
    {
        titulo: 'Curso Computacional modelación y Simulación de Series de Tiempo Univariadas',
        autor: 'M.Sc. Nicolás Chávez Quisbert',
        gestion: 2015,
        etiqueta: 'Proyecto de Interacción Social',
        descripcion: 'Resolución del HCF Nº 780/2015. Proyecto de Interacción Social entregado como informe físico.',
        enlace_url: '',
    },
    {
        titulo: 'Medición de la Disparidad Salarial en las Regiones del Departamento de La Paz',
        autor: 'Lic. Melisa Abalos Choque',
        gestion: 2015,
        etiqueta: 'Proyecto de Investigación',
        descripcion: 'Aprobado con Resolución del HCF Nº 780/2015. Se encuentra el proyecto en físico.',
        enlace_url: '',
    },
    {
        titulo: 'Aplicación de las Series de Tiempo Univariadas para la Modelación de Datos del COVID en Bolivia',
        autor: 'M.Sc. Nicolás Chávez Quisbert',
        gestion: 2023,
        etiqueta: 'Proyecto de Investigación',
        descripcion: 'Modelación estocástica y proyecciones departamentales.',
        enlace_url: 'https://drive.google.com/file/d/1FxCXo_p6F1_RNahq4XRtmhg0retgldmB/view',
    },
];
</script>

<template>
    <AppLayout title="Memorias de Investigación">
        <template #header>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 w-full">
                <div>
                    <h1 class="font-black text-2xl text-[#14161C] tracking-tight">
                        Memorias de Investigación
                    </h1>
                    <p class="text-[14.5px] text-gray-600 font-medium">
                        Repositorio de proyectos de investigación, memorias anuales y producción científica docente.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    <!-- Exportar Excel -->
                    <a
                        :href="exportUrl"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2.5 text-xs font-bold text-gray-700 bg-white hover:bg-gray-50 border border-gray-200 rounded-xl shadow-xs transition-colors whitespace-nowrap cursor-pointer"
                        download
                        title="Exportar registros a formato Excel"
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
                        class="inline-flex items-center gap-1.5 px-3.5 py-2.5 text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-xl shadow-xs transition-colors whitespace-nowrap cursor-pointer"
                        title="Cargar memorias masivamente desde Excel o descargar la plantilla"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        <span class="hidden sm:inline">Importar / Plantilla</span>
                        <span class="sm:hidden">Plantilla</span>
                    </button>

                    <Link :href="route('admin.memorias-investigacion.create')">
                        <PrimaryButton class="cursor-pointer text-[15px] font-bold px-4 py-2.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Nueva Memoria
                        </PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-6 pb-12">
            <!-- Alerta de observaciones de importación si existen -->
            <div
                v-if="page.props.flash?.import_errors?.length"
                class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-amber-900 space-y-2 shadow-xs"
            >
                <div class="flex items-center gap-2 font-bold text-sm">
                    <svg class="size-5 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>Observaciones registradas durante la importación desde Excel:</span>
                </div>
                <ul class="list-disc list-inside text-xs space-y-1 text-amber-800 font-mono">
                    <li v-for="(err, eIdx) in page.props.flash.import_errors" :key="eIdx">
                        {{ err }}
                    </li>
                </ul>
            </div>
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
                        placeholder="Buscar por título, autor, etiqueta o descripción..."
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

                <!-- Filtro por Etiqueta -->
                <div class="flex items-center gap-2 shrink-0">
                    <label for="etiqueta_filter" class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                        Categoría:
                    </label>
                    <select
                        id="etiqueta_filter"
                        v-model="etiqueta"
                        @change="applyFilters"
                        class="rounded-xl border-gray-200 py-2 px-3 text-xs font-semibold focus:border-[#35449F] focus:ring-[#35449F]/20 bg-gray-50/50 cursor-pointer"
                    >
                        <option value="">Todas</option>
                        <option value="Proyecto de Investigación">Proyecto de Investigación</option>
                        <option value="Proyecto de Interacción Social">Proyecto de Interacción Social</option>
                        <option value="Memoria Anual">Memoria Anual</option>
                    </select>
                </div>

                <!-- Filtro por Autor -->
                <div class="flex items-center gap-2 shrink-0">
                    <label for="autor_filter" class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                        Autor:
                    </label>
                    <select
                        id="autor_filter"
                        v-model="autorId"
                        @change="applyFilters"
                        class="rounded-xl border-gray-200 py-2 px-3 text-xs font-semibold focus:border-[#35449F] focus:ring-[#35449F]/20 bg-gray-50/50 cursor-pointer max-w-[200px] truncate"
                    >
                        <option value="">Todos los autores</option>
                        <option v-for="autor in autores" :key="autor.id" :value="autor.id">
                            {{ autor.persona ? `${autor.persona.p_nombre} ${autor.persona.p_apellido_paterno || ''}`.trim() : `Autor #${autor.id}` }}
                        </option>
                    </select>
                </div>

                <!-- Selector de registros por página -->
                <div class="flex items-center gap-2 shrink-0">
                    <label for="per_page_filter" class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                        Mostrar:
                    </label>
                    <select
                        id="per_page_filter"
                        v-model="perPage"
                        @change="applyFilters"
                        class="rounded-xl border-gray-200 py-2 px-3 text-xs font-semibold focus:border-[#35449F] focus:ring-[#35449F]/20 bg-gray-50/50 cursor-pointer"
                    >
                        <option :value="10">10</option>
                        <option :value="25">25</option>
                        <option :value="50">50</option>
                        <option :value="100">100</option>
                        <option :value="200">Todos (200)</option>
                    </select>
                </div>
            </div>

            <!-- Tabla de Memorias -->
            <div class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50/50 text-[11px] font-black text-gray-400 uppercase tracking-wider">
                                <th class="py-3.5 px-6 text-center w-16">#</th>
                                <th class="py-3.5 px-6">Título de la Investigación</th>
                                <th class="py-3.5 px-6">Autor Responsable</th>
                                <th class="py-3.5 px-6 text-center">Etiqueta</th>
                                <th class="py-3.5 px-6 text-center">Gestión</th>
                                <th class="py-3.5 px-6 text-center">Archivos / Enlaces</th>
                                <th class="py-3.5 px-6 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            <tr
                                v-for="(item, index) in list"
                                :key="item.id"
                                class="hover:bg-gray-50/60 transition group"
                            >
                                <td class="py-4 px-6 text-center font-mono text-xs font-bold text-gray-500 whitespace-nowrap">
                                    {{ (props.memorias?.from ? props.memorias.from + index : index + 1) }}
                                </td>
                                <td class="py-4 px-6 max-w-sm">
                                    <Link
                                        :href="route('admin.memorias-investigacion.show', item.id)"
                                        class="font-bold text-[#14161C] hover:text-[#35449F] transition line-clamp-2 leading-snug"
                                    >
                                        {{ item.mdi_titulo }}
                                    </Link>
                                    <p v-if="item.mdi_descripcion" class="text-xs text-gray-400 line-clamp-1 mt-0.5">
                                        {{ item.mdi_descripcion }}
                                    </p>
                                </td>

                                <td class="py-4 px-6">
                                    <div v-if="item.autor" class="flex items-center gap-2.5">
                                        <div class="size-8 rounded-full bg-[#35449F]/10 text-[#35449F] font-black flex items-center justify-center text-xs shrink-0">
                                            {{ item.autor.persona?.p_nombre?.charAt(0) || 'A' }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-[#14161C] text-xs">
                                                {{ item.autor.persona?.nombre_completo || (item.autor.persona ? `${item.autor.persona.p_nombre} ${item.autor.persona.p_apellido_paterno || ''}`.trim() : `Autor #${item.autor.id}`) }}
                                            </p>
                                            <p v-if="item.autor.persona?.p_correo" class="text-[11px] text-gray-400">
                                                {{ item.autor.persona.p_correo }}
                                            </p>
                                        </div>
                                    </div>
                                    <span v-else class="text-xs text-gray-400 italic">Sin autor</span>
                                </td>

                                <td class="py-4 px-6 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-[#35449F]/10 text-[#35449F]">
                                        {{ item.mdi_etiqueta }}
                                    </span>
                                </td>

                                <td class="py-4 px-6 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md font-mono text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                        {{ item.mdi_anio || item.mdi_fecha || '—' }}
                                    </span>
                                </td>

                                <td class="py-4 px-6 text-center">
                                    <div class="flex flex-col items-center gap-1">
                                        <a
                                            v-if="item.archivo_url"
                                            :href="item.archivo_url"
                                            target="_blank"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-[#35449F]/10 text-[#35449F] hover:bg-[#35449F]/20 transition"
                                            title="Descargar documento local"
                                        >
                                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            Archivo
                                        </a>

                                        <a
                                            v-if="item.mdi_enlace_url"
                                            :href="item.mdi_enlace_url"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-purple-50 text-purple-700 hover:bg-purple-100 transition"
                                            title="Abrir enlace externo (Drive / Repositorio)"
                                        >
                                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                            </svg>
                                            Enlace Nube
                                        </a>

                                        <span v-if="!item.archivo_url && !item.mdi_enlace_url" class="text-xs text-gray-300">—</span>
                                    </div>
                                </td>

                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <Link
                                            :href="route('admin.memorias-investigacion.show', item.id)"
                                            class="p-2 text-gray-400 hover:text-[#35449F] hover:bg-indigo-50 rounded-lg transition"
                                            title="Ver Detalle"
                                        >
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.264 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </Link>
                                        <Link
                                            :href="route('admin.memorias-investigacion.edit', item.id)"
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

                            <tr v-if="!list.length">
                                <td colspan="7" class="py-12 text-center text-gray-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <svg class="size-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                        <span class="text-sm font-medium">No se encontraron memorias de investigación registradas.</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="memorias?.meta || memorias?.links || memorias?.total" class="p-4 bg-gray-50/50">
                    <Pagination :meta="memorias" />
                </div>
            </div>
        </div>

        <ConfirmationModal :show="confirmingDeletion" @close="confirmingDeletion = false">
            <template #title>Eliminar Memoria de Investigación</template>
            <template #content>
                ¿Estás seguro de que deseas eliminar permanentemente esta memoria de investigación? Esta acción no se puede deshacer.
            </template>
            <template #footer>
                <SecondaryButton @click="confirmingDeletion = false" class="mr-2 cursor-pointer">Cancelar</SecondaryButton>
                <DangerButton @click="deleteItem" class="cursor-pointer">Eliminar</DangerButton>
            </template>
        </ConfirmationModal>

        <!-- Modal de Importación Masiva desde Excel -->
        <ExcelImportModal
            :show="showImportModal"
            title="Importación Masiva de Memorias y Proyectos"
            subtitle="Carga y actualiza investigaciones docentes y memorias masivamente desde un archivo Excel (.xlsx o .csv)."
            :plantilla-url="route('admin.memorias-investigacion.plantilla')"
            :import-url="route('admin.memorias-investigacion.import')"
            :columns="memoriasColumns"
            :sample-rows="sampleRows"
            @close="showImportModal = false"
        />
    </AppLayout>
</template>
