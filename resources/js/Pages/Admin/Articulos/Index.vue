<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import DialogModal from '@/Components/DialogModal.vue';
import InputError from '@/Components/InputError.vue';
import Pagination from '@/Components/Pagination.vue';
import ExcelImportModal from '@/Components/ExcelImportModal.vue';
import IconButton from '@/Components/IconButton.vue';

const props = defineProps({
    articulos: {
        type: [Array, Object],
        default: () => ({ data: [] }),
    },
    filters: {
        type: Object,
        default: () => ({ search: '', tipo_id: null, edicion_id: null, estado_revision: '', per_page: 10 }),
    },
    tipos_articulos: {
        type: Array,
        default: () => [],
    },
    ediciones: {
        type: Array,
        default: () => [],
    },
    puede_revisar: {
        type: Boolean,
        default: false,
    },
    es_autor: {
        type: Boolean,
        default: false,
    },
});

const list = computed(() => {
    if (Array.isArray(props.articulos)) {
        return props.articulos;
    }
    return props.articulos?.data || [];
});

const search = ref(props.filters?.search || '');
const selectedTipo = ref(props.filters?.tipo_id || '');
const selectedEdicion = ref(props.filters?.edicion_id || '');
const selectedRevision = ref(props.filters?.estado_revision || '');
const perPage = ref(props.filters?.per_page || 10);
const showFilters = ref(Boolean(props.filters?.tipo_id || props.filters?.edicion_id));
let searchTimeout = null;

const revisionTabs = [
    { id: '', label: 'Todos los Artículos' },
    { id: 'pendiente', label: 'Pendientes de Revisión' },
    { id: 'aprobado', label: 'Aprobados' },
    { id: 'borrador', label: 'Borradores' },
    { id: 'rechazado', label: 'Rechazados' },
];

const setRevisionTab = (tabId) => {
    selectedRevision.value = tabId;
    applyFilters();
};

const activeExtraFiltersCount = computed(() => {
    let count = 0;
    if (selectedTipo.value) count++;
    if (selectedEdicion.value) count++;
    return count;
});

const hasActiveFilters = computed(() => {
    return Boolean(search.value || selectedTipo.value || selectedEdicion.value || selectedRevision.value);
});

const selectedTipoLabel = computed(() => {
    const found = props.tipos_articulos?.find(t => String(t.id) === String(selectedTipo.value));
    return found ? found.tar_nombre : 'Tipo';
});

const selectedEdicionLabel = computed(() => {
    const found = props.ediciones?.find(e => String(e.id) === String(selectedEdicion.value));
    return found ? `Nº ${found.ev_numero} (${found.ev_anio})` : 'Edición';
});

const clearTipo = () => {
    selectedTipo.value = '';
    applyFilters();
};

const clearEdicion = () => {
    selectedEdicion.value = '';
    applyFilters();
};

const clearRevision = () => {
    selectedRevision.value = '';
    applyFilters();
};

const clearAllFilters = () => {
    search.value = '';
    selectedTipo.value = '';
    selectedEdicion.value = '';
    selectedRevision.value = '';
    applyFilters();
};

const applyFilters = () => {
    router.get(
        route('admin.articulos.index'),
        {
            search: search.value ? search.value : undefined,
            tipo_id: selectedTipo.value ? selectedTipo.value : undefined,
            edicion_id: selectedEdicion.value ? selectedEdicion.value : undefined,
            estado_revision: selectedRevision.value ? selectedRevision.value : undefined,
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

    router.delete(route('admin.articulos.destroy', itemToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            confirmingDeletion.value = false;
            itemToDelete.value = null;
        },
    });
};

// Acciones de Revisión Editorial
const confirmingApproval = ref(false);
const confirmingRejection = ref(false);
const itemToReview = ref(null);
const rejectMotivo = ref('');
const rejectError = ref('');
const processingReview = ref(false);

const confirmApprove = (item) => {
    itemToReview.value = item;
    confirmingApproval.value = true;
};

const approveArticle = () => {
    if (!itemToReview.value) return;
    processingReview.value = true;
    router.post(route('admin.articulos.aprobar', itemToReview.value.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            confirmingApproval.value = false;
            itemToReview.value = null;
        },
        onFinish: () => {
            processingReview.value = false;
        },
    });
};

const confirmReject = (item) => {
    itemToReview.value = item;
    rejectMotivo.value = '';
    rejectError.value = '';
    confirmingRejection.value = true;
};

const rejectArticle = () => {
    if (!itemToReview.value) return;
    if (!rejectMotivo.value.trim()) {
        rejectError.value = 'Debes especificar el motivo u observaciones para el autor.';
        return;
    }
    processingReview.value = true;
    router.post(route('admin.articulos.rechazar', itemToReview.value.id), {
        motivo: rejectMotivo.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            confirmingRejection.value = false;
            itemToReview.value = null;
            rejectMotivo.value = '';
            rejectError.value = '';
        },
        onError: (err) => {
            if (err.motivo) rejectError.value = err.motivo;
        },
        onFinish: () => {
            processingReview.value = false;
        },
    });
};

const formatDate = (dateString) => {
    if (!dateString) return '—';
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('es-ES', { year: 'numeric', month: 'short', day: 'numeric' });
    } catch {
        return dateString;
    }
};

const authorName = (autor) => {
    if (autor?.persona?.nombre_completo) return autor.persona.nombre_completo;
    if (autor?.persona) {
        return `${autor.persona.p_nombre} ${autor.persona.p_apellido_paterno || ''}`.trim();
    }
    return 'Autor';
};

const showImportModal = ref(false);

const articulosColumns = [
    { name: 'titulo', label: 'Título', required: true, desc: 'Título oficial del artículo científico', ejemplo: 'Modelos de Regresión Cuantílica en Series Temporales' },
    { name: 'tipo_articulo', label: 'Tipo de Artículo', required: false, desc: 'Nombre del tipo (ej. Artículo Original, Nota Científica)', ejemplo: 'Artículo Original' },
    { name: 'edicion', label: 'Edición', required: false, desc: 'Número o título de la edición de la Revista Varianza', ejemplo: '25' },
    { name: 'pag_inicio', label: 'Pág. Inicio', required: false, desc: 'Número de página inicial en la revista', ejemplo: '10' },
    { name: 'pag_fin', label: 'Pág. Fin', required: false, desc: 'Número de página final en la revista', ejemplo: '25' },
    { name: 'doi', label: 'DOI', required: false, desc: 'Identificador digital normalizado (DOI)', ejemplo: '10.5377/varianza.v25i1.1234' },
    { name: 'orcid', label: 'ORCID', required: false, desc: 'Identificador ORCID del investigador principal', ejemplo: '0000-0002-1825-0097' },
    { name: 'fecha_recepcion', label: 'Fecha Recepción', required: false, desc: 'Fecha de recepción (YYYY-MM-DD)', ejemplo: '2024-03-15' },
    { name: 'fecha_aceptado', label: 'Fecha Aceptado', required: false, desc: 'Fecha de aprobación editorial (YYYY-MM-DD)', ejemplo: '2024-06-20' },
    { name: 'autores', label: 'Autores', required: false, desc: 'Nombres de autores separados por punto y coma (;)', ejemplo: 'Juan Carlos Pérez Condori; María Quispe Ramos' },
    { name: 'etiquetas', label: 'Etiquetas', required: false, desc: 'Palabras clave separadas por comas (,)', ejemplo: 'Series de Tiempo, Regresión Cuantílica, Inflación' },
    { name: 'resumen', label: 'Resumen', required: false, desc: 'Resumen académico en texto plano', ejemplo: 'En este artículo se analiza...' },
    { name: 'abstract', label: 'Abstract', required: false, desc: 'Resumen en idioma inglés', ejemplo: 'In this article inflation is analyzed...' },
    { name: 'referencias', label: 'Referencias', required: false, desc: 'Bibliografía o citas separadas por punto y coma', ejemplo: 'Koenker, R. (2005)...' },
];

const articulosSampleRows = [
    {
        titulo: 'Modelos de Regresión Cuantílica en Series Temporales de Inflación',
        tipo_articulo: 'Artículo Original',
        edicion: '25',
        pag_inicio: 10,
        pag_fin: 25,
        doi: '10.5377/varianza.v25i1.1234',
        orcid: '0000-0002-1825-0097',
        fecha_recepcion: '2024-03-15',
        fecha_aceptado: '2024-06-20',
        autores: 'Juan Carlos Pérez Condori; María Quispe Ramos',
        etiquetas: 'Series de Tiempo, Regresión Cuantílica, Inflación',
        resumen: 'En este artículo se analiza el comportamiento de la inflación mediante modelos autorregresivos cuantílicos...',
        abstract: 'In this article, inflation behavior is analyzed through quantile autoregressive models...',
        referencias: 'Koenker, R. (2005). Quantile Regression. Cambridge University Press.',
    },
    {
        titulo: 'Estimación de Áreas Pequeñas para Indicadores de Pobreza Multidimensional',
        tipo_articulo: 'Nota Científica',
        edicion: '25',
        pag_inicio: 26,
        pag_fin: 38,
        doi: '10.5377/varianza.v25i1.1235',
        orcid: '0000-0003-4567-8901',
        fecha_recepcion: '2024-04-02',
        fecha_aceptado: '2024-07-10',
        autores: 'Carlos Alberto Mamani',
        etiquetas: 'Áreas Pequeñas, Encuesta de Hogares, Pobreza',
        resumen: 'Se presentan estimadores empíricos de Bayes para desagregaciones municipales basadas en encuestas complejas.',
        abstract: 'Empirical Bayes estimators for municipal disaggregations based on complex surveys are presented.',
        referencias: 'Rao, J. N. K., & Molina, I. (2015). Small Area Estimation. John Wiley & Sons.',
    },
];

const exportUrl = computed(() => {
    const params = new URLSearchParams();
    if (search.value) params.append('search', search.value);
    if (selectedTipo.value) params.append('tipo_id', selectedTipo.value);
    if (selectedEdicion.value) params.append('edicion_id', selectedEdicion.value);
    if (selectedRevision.value) params.append('estado_revision', selectedRevision.value);
    const qs = params.toString();
    return route('admin.articulos.export') + (qs ? `?${qs}` : '');
});
</script>

<template>
    <AppLayout title="Artículos Científicos">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 w-full min-w-0">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-1.5 text-[11px] font-semibold text-slate-400 mb-0.5 leading-none">
                        <Link :href="route('admin.ediciones-varianza.index')" class="hover:text-[#35449F]">Revista Varianza</Link>
                        <span>/</span>
                        <span class="text-slate-700 font-bold">Artículos</span>
                    </div>
                    <h1 class="font-extrabold text-xl sm:text-2xl text-[#14161C] tracking-tight truncate leading-tight">
                        Artículos Científicos
                    </h1>
                </div>
                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    <!-- Exportar Excel -->
                    <a
                        :href="exportUrl"
                        class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-gray-700 bg-white hover:bg-gray-50 border border-gray-200 rounded-xl shadow-xs transition-colors whitespace-nowrap cursor-pointer"
                        download
                        title="Exportar artículos filtrados a formato Excel"
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
                        title="Cargar registros masivos desde Excel o ver la plantilla en vivo"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        <span class="hidden sm:inline">Importar / Plantilla</span>
                        <span class="sm:hidden">Plantilla</span>
                    </button>

                    <!-- Nuevo Artículo -->
                    <Link :href="route('admin.articulos.create')">
                        <PrimaryButton class="cursor-pointer gap-1.5 whitespace-nowrap">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            <span>Nuevo Artículo</span>
                        </PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-4 pb-12">
            <!-- Pestañas de Estado Editorial (Estilo OJS / Linear) -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none border-b border-slate-200/80">
                <button
                    v-for="tab in revisionTabs"
                    :key="tab.id"
                    type="button"
                    @click="setRevisionTab(tab.id)"
                    :class="[
                        selectedRevision === tab.id
                            ? 'border-[#35449F] text-[#35449F] font-bold bg-white shadow-2xs'
                            : 'border-transparent text-slate-500 hover:text-slate-800 hover:bg-slate-100/60 font-medium'
                    ]"
                    class="inline-flex items-center gap-2 px-3.5 py-2 border rounded-xl text-xs sm:text-sm transition-all duration-150 cursor-pointer whitespace-nowrap"
                >
                    <span
                        v-if="tab.id === 'pendiente'"
                        class="size-2 rounded-full bg-amber-500 animate-pulse"
                    ></span>
                    <span>{{ tab.label }}</span>
                </button>
            </div>

            <!-- Barra de Búsqueda y Botón de Filtros Desplegable (Progressive Disclosure) -->
            <div class="bg-white rounded-2xl p-3.5 sm:p-4 shadow-2xs border border-slate-200/90 space-y-3">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                    <!-- Buscador Limpio Principal -->
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input
                            v-model="search"
                            @input="onSearchInput"
                            type="text"
                            placeholder="Buscar por título, etiquetas, DOI, ORCID o autor..."
                            class="w-full pl-10 pr-10 py-2.5 bg-slate-50/70 border border-slate-200 rounded-xl text-sm placeholder:text-slate-400 focus:bg-white focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 transition"
                        />
                        <button
                            v-if="search"
                            @click="clearSearch"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer"
                            title="Limpiar búsqueda"
                        >
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Controles Secundarios: Botón Filtros + Selector de Páginas -->
                    <div class="flex items-center gap-2.5 shrink-0 justify-end">
                        <!-- Botón Toggle de Filtros Avanzados -->
                        <button
                            type="button"
                            @click="showFilters = !showFilters"
                            :class="[
                                showFilters || activeExtraFiltersCount > 0
                                    ? 'bg-indigo-50/80 border-indigo-200 text-[#35449F] font-bold'
                                    : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50 font-medium'
                            ]"
                            class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-semibold rounded-xl border shadow-2xs transition cursor-pointer"
                            title="Filtros avanzados"
                        >
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            <span>Filtros</span>
                            <span
                                v-if="activeExtraFiltersCount > 0"
                                class="size-5 rounded-full bg-[#35449F] text-white text-[11px] font-black flex items-center justify-center"
                            >
                                {{ activeExtraFiltersCount }}
                            </span>
                        </button>

                        <!-- Selector de Registros por Página -->
                        <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200/90 rounded-xl px-2.5 py-1.5">
                            <label for="per_page" class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                Ver:
                            </label>
                            <select
                                id="per_page"
                                v-model="perPage"
                                @change="applyFilters"
                                class="text-xs font-bold text-slate-800 bg-transparent border-0 p-0 pr-4 focus:ring-0 cursor-pointer"
                            >
                                <option :value="10">10</option>
                                <option :value="25">25</option>
                                <option :value="50">50</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Panel Desplegable de Filtros Específicos (Progressive Disclosure) -->
                <div
                    v-show="showFilters"
                    class="pt-3 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3"
                >
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">
                            Tipo de Artículo
                        </label>
                        <select
                            v-model="selectedTipo"
                            @change="applyFilters"
                            class="w-full text-xs font-semibold rounded-xl border-slate-200 py-2 pl-3 pr-8 bg-slate-50/70 focus:bg-white focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10"
                        >
                            <option value="">Todos los Tipos</option>
                            <option v-for="t in tipos_articulos" :key="t.id" :value="t.id">
                                {{ t.tar_nombre }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">
                            Edición Varianza
                        </label>
                        <select
                            v-model="selectedEdicion"
                            @change="applyFilters"
                            class="w-full text-xs font-semibold rounded-xl border-slate-200 py-2 pl-3 pr-8 bg-slate-50/70 focus:bg-white focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10"
                        >
                            <option value="">Todas las Ediciones</option>
                            <option v-for="ed in ediciones" :key="ed.id" :value="ed.id">
                                Nº {{ ed.ev_numero }} ({{ ed.ev_anio }})
                            </option>
                        </select>
                    </div>

                    <div class="flex items-end">
                        <button
                            v-if="selectedTipo || selectedEdicion"
                            type="button"
                            @click="clearTipo(); clearEdicion();"
                            class="text-xs font-semibold text-slate-500 hover:text-rose-600 transition py-2 cursor-pointer"
                        >
                            Limpiar estos filtros
                        </button>
                    </div>
                </div>

                <!-- Active Filter Chips (Pastillas de filtros activos estilo Zenodo / InvenioRDM) -->
                <div v-if="hasActiveFilters" class="pt-2 border-t border-slate-100 flex items-center gap-2 flex-wrap text-xs">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Filtros activos:</span>

                    <span
                        v-if="search"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50 text-[#35449F] font-semibold border border-indigo-100"
                    >
                        <span>Búsqueda: "{{ search }}"</span>
                        <button type="button" @click="clearSearch" class="hover:text-rose-600 cursor-pointer" title="Quitar">
                            <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </span>

                    <span
                        v-if="selectedRevision"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50 text-[#35449F] font-semibold border border-indigo-100 capitalize"
                    >
                        <span>Estado: {{ selectedRevision }}</span>
                        <button type="button" @click="clearRevision" class="hover:text-rose-600 cursor-pointer" title="Quitar">
                            <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </span>

                    <span
                        v-if="selectedTipo"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50 text-[#35449F] font-semibold border border-indigo-100"
                    >
                        <span>Tipo: {{ selectedTipoLabel }}</span>
                        <button type="button" @click="clearTipo" class="hover:text-rose-600 cursor-pointer" title="Quitar">
                            <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </span>

                    <span
                        v-if="selectedEdicion"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50 text-[#35449F] font-semibold border border-indigo-100"
                    >
                        <span>Edición: {{ selectedEdicionLabel }}</span>
                        <button type="button" @click="clearEdicion" class="hover:text-rose-600 cursor-pointer" title="Quitar">
                            <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </span>

                    <button
                        type="button"
                        @click="clearAllFilters"
                        class="text-xs font-bold text-rose-600 hover:text-rose-800 transition underline underline-offset-2 ml-1 cursor-pointer"
                    >
                        Limpiar todos
                    </button>
                </div>
            </div>

            <!-- Tabla de Artículos -->
            <div class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-600 font-extrabold uppercase text-xs tracking-wider border-b border-gray-100">
                            <tr>
                                <th scope="col" class="py-4 px-6 w-16 text-center">#</th>
                                <th scope="col" class="py-4 px-6">Título & Identificadores</th>
                                <th scope="col" class="py-4 px-6">Edición & Tipo</th>
                                <th scope="col" class="py-4 px-6">Autores</th>
                                <th scope="col" class="py-4 px-6">Recepción / Aceptación</th>
                                <th scope="col" class="py-4 px-6 text-center">PDF</th>
                                <th scope="col" class="py-4 px-6 text-center">Revisión</th>
                                <th scope="col" class="py-4 px-6 text-right w-36">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="list.length === 0">
                                <td colspan="8" class="py-12 text-center text-gray-400">
                                    <p class="font-bold text-[#14161C]">No hay artículos registrados</p>
                                </td>
                            </tr>
                            <tr
                                v-for="(item, index) in list"
                                :key="item.id"
                                class="hover:bg-gray-50/70 transition"
                            >
                                <td class="py-4 px-6 font-mono text-xs text-gray-500 text-center font-bold">
                                    {{ (props.articulos?.from ? props.articulos.from + index : index + 1) }}
                                </td>
                                <td class="py-4 px-6 max-w-sm">
                                    <Link
                                        :href="route('admin.articulos.show', item.id)"
                                        class="font-bold text-[#14161C] hover:text-[#35449F] transition line-clamp-2"
                                    >
                                        {{ item.art_titulo }}
                                    </Link>
                                    <div class="flex items-center gap-2 text-xs text-gray-400 mt-1 flex-wrap">
                                        <span v-if="item.art_doi" class="font-mono bg-blue-50 text-blue-700 px-1.5 py-0.5 rounded">
                                            DOI: {{ item.art_doi }}
                                        </span>
                                        <span v-if="item.art_orcid" class="font-mono bg-emerald-50 text-emerald-700 px-1.5 py-0.5 rounded">
                                            ORCID: {{ item.art_orcid }}
                                        </span>
                                        <span v-if="item.art_slug" class="font-mono bg-purple-50 text-purple-700 px-1.5 py-0.5 rounded">
                                            /{{ item.art_slug }}
                                        </span>
                                        <span v-if="item.art_num_paginas" class="text-gray-400">
                                            {{ item.art_num_paginas }} págs.
                                        </span>
                                    </div>
                                    <div v-if="item.etiquetas && item.etiquetas.length" class="flex items-center gap-1 mt-1.5 flex-wrap">
                                        <span
                                            v-for="tag in item.etiquetas"
                                            :key="tag.id"
                                            class="text-[10px] font-bold text-indigo-700 bg-indigo-50 px-1.5 py-0.5 rounded border border-indigo-100/60"
                                        >
                                            #{{ tag.ea_nombre }}
                                        </span>
                                    </div>
                                </td>
                                <!-- Edición & Tipo de Artículo -->
                                <td class="py-4 px-6 text-xs space-y-1">
                                    <div v-if="item.tipo_articulo" class="inline-block">
                                        <span
                                            class="px-2 py-0.5 rounded text-[11px] font-bold"
                                            :class="item.tipo_articulo.tar_nombre.includes('Original') ? 'bg-blue-50 text-[#35449F] border border-blue-100' : 'bg-purple-50 text-purple-700 border border-purple-100'"
                                        >
                                            {{ item.tipo_articulo.tar_nombre }}
                                        </span>
                                    </div>
                                    <div v-else class="text-slate-400 italic text-[11px]">
                                        Sin tipo asignado
                                    </div>

                                    <div v-if="item.edicion" class="text-[11px] text-slate-600 font-semibold flex items-center gap-1">
                                        <span>📖 Edición Nº {{ item.edicion.ev_numero }} ({{ item.edicion.ev_anio }})</span>
                                        <span v-if="item.art_pag_inicio && item.art_pag_fin" class="text-slate-400 font-mono">
                                            [pp. {{ item.art_pag_inicio }}-{{ item.art_pag_fin }}]
                                        </span>
                                    </div>
                                    <div v-else class="text-slate-400 text-[11px] italic">
                                        Independiente
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <div v-if="item.autores && item.autores.length > 0" class="flex flex-wrap gap-1">
                                        <span
                                            v-for="autor in item.autores"
                                            :key="autor.id"
                                            class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-700"
                                        >
                                            {{ authorName(autor) }}
                                        </span>
                                    </div>
                                    <span v-else class="text-xs text-gray-400 italic">Sin autores</span>
                                </td>
                                <td class="py-4 px-6 text-xs text-gray-500">
                                    <div><strong class="text-gray-700">Rec:</strong> {{ formatDate(item.art_fecha_recepcion) }}</div>
                                    <div v-if="item.art_fecha_aceptado" class="mt-0.5">
                                        <strong class="text-gray-700">Acep:</strong> {{ formatDate(item.art_fecha_aceptado) }}
                                    </div>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <a
                                        v-if="item.archivo_url"
                                        :href="item.archivo_url"
                                        target="_blank"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-red-50 text-red-700 hover:bg-red-100 transition"
                                        title="Descargar documento"
                                    >
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        PDF
                                    </a>
                                    <span v-else class="text-xs text-gray-300">—</span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <div class="flex flex-col items-center gap-1">
                                        <span
                                            v-if="item.estado_revision === 'aprobado'"
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20"
                                        >
                                            <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                            Aprobado
                                        </span>
                                        <span
                                            v-else-if="item.estado_revision === 'pendiente'"
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 ring-1 ring-amber-600/20"
                                        >
                                            <span class="size-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            Pendiente
                                        </span>
                                        <span
                                            v-else-if="item.estado_revision === 'rechazado'"
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 ring-1 ring-rose-600/20"
                                            :title="item.revision_motivo || 'Rechazado con observaciones'"
                                        >
                                            <span class="size-1.5 rounded-full bg-rose-500"></span>
                                            Rechazado
                                        </span>
                                        <span
                                            v-else
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-600 ring-1 ring-gray-400/20"
                                        >
                                            Borrador
                                        </span>

                                        <span v-if="item.art_estado && item.estado_revision === 'aprobado'" class="text-[10px] text-emerald-600 font-semibold">
                                            ● Público
                                        </span>
                                        <span v-else class="text-[10px] text-slate-400 font-medium">
                                            ○ No visible
                                        </span>
                                    </div>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Acciones editoriales rápidas para administrador / revisor -->
                                        <IconButton
                                            v-if="puede_revisar && item.estado_revision === 'pendiente'"
                                            @click="confirmApprove(item)"
                                            variant="success"
                                            size="sm"
                                            title="Aprobar y publicar artículo"
                                        >
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </IconButton>

                                        <IconButton
                                            v-if="puede_revisar && item.estado_revision === 'pendiente'"
                                            @click="confirmReject(item)"
                                            variant="danger"
                                            size="sm"
                                            title="Rechazar con observaciones"
                                        >
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </IconButton>

                                        <IconButton
                                            as="Link"
                                            :href="route('admin.articulos.show', item.id)"
                                            variant="primary"
                                            size="sm"
                                            title="Ver Artículo"
                                        >
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.264 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </IconButton>

                                        <IconButton
                                            as="Link"
                                            :href="route('admin.articulos.edit', item.id)"
                                            variant="warning"
                                            size="sm"
                                            title="Editar"
                                        >
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </IconButton>

                                        <IconButton
                                            v-if="!es_autor || item.estado_revision === 'borrador'"
                                            @click="confirmDelete(item)"
                                            variant="danger"
                                            size="sm"
                                            title="Eliminar"
                                        >
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </IconButton>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="articulos?.meta || articulos?.links" class="p-4 bg-gray-50/50">
                    <Pagination :meta="articulos" />
                </div>
            </div>
        </div>

        <ConfirmationModal :show="confirmingDeletion" @close="confirmingDeletion = false">
            <template #title>Eliminar Artículo</template>
            <template #content>
                ¿Estás seguro de que deseas eliminar el artículo
                <span class="font-bold text-[#14161C]">"{{ itemToDelete?.art_titulo }}"</span>? Esta acción no se puede deshacer.
            </template>
            <template #footer>
                <SecondaryButton @click="confirmingDeletion = false" class="mr-2 cursor-pointer">Cancelar</SecondaryButton>
                <DangerButton @click="deleteItem" class="cursor-pointer">Eliminar</DangerButton>
            </template>
        </ConfirmationModal>

        <!-- Modal de Aprobación de Artículo -->
        <ConfirmationModal :show="confirmingApproval" @close="confirmingApproval = false">
            <template #title>Aprobar y Publicar Artículo</template>
            <template #content>
                ¿Confirmas la aprobación del artículo
                <span class="font-bold text-[#14161C]">"{{ itemToReview?.art_titulo }}"</span>?
                El artículo cambiará su estado a <strong class="text-emerald-700">Aprobado</strong> y quedará visible públicamente en la Revista Varianza.
            </template>
            <template #footer>
                <SecondaryButton @click="confirmingApproval = false" class="mr-2 cursor-pointer">
                    Cancelar
                </SecondaryButton>
                <PrimaryButton :disabled="processingReview" @click="approveArticle" class="cursor-pointer bg-emerald-600 hover:bg-emerald-700">
                    Aprobar y Publicar
                </PrimaryButton>
            </template>
        </ConfirmationModal>

        <!-- Modal de Rechazo con Observaciones -->
        <DialogModal :show="confirmingRejection" @close="confirmingRejection = false">
            <template #title>Rechazar Artículo con Observaciones</template>
            <template #content>
                <p class="text-xs text-gray-600 mb-3">
                    Indica las observaciones editoriales para el autor del artículo
                    <span class="font-bold text-[#14161C]">"{{ itemToReview?.art_titulo }}"</span>. El autor podrá ver estos comentarios y enviar una versión corregida.
                </p>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                        Motivo / Observaciones Editorial <span class="text-rose-500">*</span>
                    </label>
                    <textarea
                        v-model="rejectMotivo"
                        rows="4"
                        placeholder="Describe los motivos del rechazo, correcciones metodológicas o cambios solicitados al autor..."
                        class="w-full text-sm rounded-xl border-gray-200 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/10 p-3 bg-gray-50/50 focus:bg-white"
                    ></textarea>
                    <InputError :message="rejectError" class="mt-1" />
                </div>
            </template>
            <template #footer>
                <SecondaryButton @click="confirmingRejection = false" class="mr-2 cursor-pointer">
                    Cancelar
                </SecondaryButton>
                <DangerButton :disabled="processingReview" @click="rejectArticle" class="cursor-pointer">
                    Rechazar con Observaciones
                </DangerButton>
            </template>
        </DialogModal>

        <!-- Modal de Importación Masiva y Plantilla en Vivo -->
        <ExcelImportModal
            :show="showImportModal"
            title="Importación Masiva de Artículos Científicos"
            subtitle="Digitaliza y registra decenas de artículos simultáneamente mediante una hoja de cálculo Excel."
            :plantilla-url="route('admin.articulos.plantilla')"
            :import-url="route('admin.articulos.import')"
            :columns="articulosColumns"
            :sample-rows="articulosSampleRows"
            @close="showImportModal = false"
        />
    </AppLayout>
</template>
