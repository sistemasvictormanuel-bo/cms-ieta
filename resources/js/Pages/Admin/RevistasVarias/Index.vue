<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    revistas: {
        type: [Array, Object],
        default: () => ({ data: [] }),
    },
    stats: {
        type: Object,
        default: () => ({
            total: 0,
            activas: 0,
            inactivas: 0,
        }),
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            activo: '',
            per_page: 12,
            view: 'cards',
        }),
    },
});

const list = computed(() => {
    if (Array.isArray(props.revistas)) {
        return props.revistas;
    }
    return props.revistas?.data || [];
});

const search = ref(props.filters?.search || '');
const activo = ref(props.filters?.activo ?? '');
const perPage = ref(props.filters?.per_page || 12);
let searchTimeout = null;

const applyFilters = () => {
    router.get(
        route('admin.revistas-varias.index'),
        {
            search: search.value ? search.value : undefined,
            activo: activo.value !== '' ? activo.value : undefined,
            per_page: perPage.value !== 12 ? perPage.value : undefined,
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

    router.delete(route('admin.revistas-varias.destroy', itemToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            confirmingDeletion.value = false;
            itemToDelete.value = null;
        },
    });
};

const toggleActivo = (id) => {
    router.patch(route('admin.revistas-varias.toggle-activo', id), {}, {
        preserveScroll: true,
    });
};

const viewMode = ref(
    typeof window !== 'undefined' && localStorage.getItem('admin_view_mode_revistas_varias')
        ? localStorage.getItem('admin_view_mode_revistas_varias')
        : (props.filters?.view || 'cards')
);

const setViewMode = (mode) => {
    viewMode.value = mode;
    if (typeof window !== 'undefined') {
        localStorage.setItem('admin_view_mode_revistas_varias', mode);
    }
};

const imgErrors = ref({});
const onImgError = (id) => {
    imgErrors.value[id] = true;
};
</script>

<template>
    <AppLayout title="Revistas de Referencia">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 w-full min-w-0">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-1.5 text-[11px] font-semibold text-slate-400 mb-0.5 leading-none">
                        <Link :href="route('dashboard')" class="hover:text-[#35449F]">Inicio</Link>
                        <span>/</span>
                        <span class="text-slate-500">Revista Varianza</span>
                        <span>/</span>
                        <span class="text-slate-700 font-bold">Revistas de Referencia</span>
                    </div>
                    <h1 class="font-extrabold text-xl sm:text-2xl text-[#14161C] tracking-tight truncate leading-tight">
                        Revistas de Referencia Científica
                    </h1>
                </div>
                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    <Link :href="route('admin.revistas-varias.create')">
                        <PrimaryButton class="cursor-pointer gap-1.5 whitespace-nowrap shadow-sm hover:shadow">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            <span>Nueva Revista</span>
                        </PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-6 pb-12">
            <!-- KPIs -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-xs flex items-center gap-3">
                    <div class="size-11 rounded-xl bg-blue-50 text-[#35449F] flex items-center justify-center shrink-0">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Revistas</div>
                        <div class="text-xl font-extrabold text-slate-800">{{ stats.total }}</div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-xs flex items-center gap-3">
                    <div class="size-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Activas en Portal</div>
                        <div class="text-xl font-extrabold text-slate-800">{{ stats.activas }}</div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-xs flex items-center gap-3">
                    <div class="size-11 rounded-xl bg-slate-50 text-slate-500 flex items-center justify-center shrink-0">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Ocultas</div>
                        <div class="text-xl font-extrabold text-slate-800">{{ stats.inactivas }}</div>
                    </div>
                </div>
            </div>

            <!-- Filtros & Barra de Búsqueda -->
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
                        placeholder="Buscar por título, entidad, ISSN o descripción..."
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

                <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
                    <!-- Estado -->
                    <div class="flex items-center gap-1.5">
                        <label for="activo_filter_rev" class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Estado:
                        </label>
                        <select
                            id="activo_filter_rev"
                            v-model="activo"
                            @change="applyFilters"
                            class="text-xs font-semibold rounded-xl border-gray-200 py-2 pl-3 pr-8 bg-gray-50 focus:bg-white focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10"
                        >
                            <option value="">Todas</option>
                            <option value="1">Activas</option>
                            <option value="0">Ocultas</option>
                        </select>
                    </div>

                    <!-- Botones de Modo de Vista (Cards / Table) -->
                    <div class="flex items-center bg-gray-100 p-1 rounded-xl border border-gray-200/80">
                        <button
                            type="button"
                            @click="setViewMode('cards')"
                            :class="viewMode === 'cards' ? 'bg-white text-[#35449F] shadow-xs font-bold' : 'text-gray-500 hover:text-gray-800 font-medium'"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs transition cursor-pointer"
                            title="Vista en Tarjetas"
                        >
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                            <span class="hidden sm:inline">Tarjetas</span>
                        </button>
                        <button
                            type="button"
                            @click="setViewMode('table')"
                            :class="viewMode === 'table' ? 'bg-white text-[#35449F] shadow-xs font-bold' : 'text-gray-500 hover:text-gray-800 font-medium'"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs transition cursor-pointer"
                            title="Vista en Tabla"
                        >
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            <span class="hidden sm:inline">Tabla</span>
                        </button>
                    </div>

                    <!-- Mostrar por página -->
                    <div class="flex items-center gap-1.5">
                        <select
                            v-model="perPage"
                            @change="applyFilters"
                            class="text-xs font-semibold rounded-xl border-gray-200 py-2 pl-2.5 pr-7 bg-gray-50 focus:bg-white focus:border-[#35449F]"
                        >
                            <option :value="12">12 p/pág</option>
                            <option :value="24">24 p/pág</option>
                            <option :value="48">48 p/pág</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- CONTENIDO: VISTA EN TARJETAS -->
            <div v-if="viewMode === 'cards'">
                <div v-if="list.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                    <div
                        v-for="item in list"
                        :key="item.id"
                        class="bg-white rounded-2xl border border-gray-100 shadow-xs hover:shadow-md transition-all duration-200 overflow-hidden flex flex-col group"
                    >
                        <!-- Cabecera / Portada si existe -->
                        <div class="relative h-44 bg-gradient-to-br from-slate-100 to-slate-200/60 flex items-center justify-center p-3 overflow-hidden">
                            <img
                                v-if="item.portada_url && !imgErrors[item.id]"
                                :src="item.portada_url"
                                :alt="item.rev_titulo"
                                @error="onImgError(item.id)"
                                class="max-h-full max-w-full object-contain group-hover:scale-105 transition-transform duration-300"
                            />
                            <div v-else class="text-center p-4 text-slate-400">
                                <div class="size-12 mx-auto rounded-2xl bg-white/80 border border-slate-200/60 flex items-center justify-center text-[#35449F] shadow-xs mb-2">
                                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </div>
                                <span class="text-[11px] font-semibold text-slate-500">Revista Científica</span>
                            </div>

                            <!-- Badge Estado Flotante -->
                            <button
                                type="button"
                                @click.stop="toggleActivo(item.id)"
                                class="absolute top-2.5 right-2.5 px-2.5 py-1 rounded-lg text-[10.5px] font-bold shadow-xs backdrop-blur-md transition cursor-pointer"
                                :class="item.rev_activo ? 'bg-emerald-500 text-white hover:bg-emerald-600' : 'bg-slate-700 text-white/80 hover:bg-slate-800'"
                                :title="item.rev_activo ? 'Ocultar del portal' : 'Publicar en el portal'"
                            >
                                {{ item.rev_activo ? 'Activa' : 'Oculta' }}
                            </button>
                        </div>

                        <!-- Contenido -->
                        <div class="p-4 flex-1 flex flex-col justify-between">
                            <div>
                                <span v-if="item.rev_entidad" class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block mb-1">
                                    {{ item.rev_entidad }}
                                </span>

                                <h3 class="font-bold text-slate-800 text-[15px] leading-snug line-clamp-2 hover:text-[#35449F] transition">
                                    <Link :href="route('admin.revistas-varias.show', item.id)">
                                        {{ item.rev_titulo }}
                                    </Link>
                                </h3>

                                <p v-if="item.rev_descripcion" class="text-xs text-slate-500 line-clamp-2 mt-1.5 leading-relaxed">
                                    {{ item.rev_descripcion }}
                                </p>

                                <div v-if="item.rev_issn" class="mt-3 text-[11px] font-mono text-slate-400">
                                    ISSN: {{ item.rev_issn }}
                                </div>
                            </div>

                            <!-- Footer de Acciones -->
                            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                                <a
                                    :href="item.rev_url_enlace"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-xs font-bold text-[#35449F] hover:text-[#253275] inline-flex items-center gap-1 transition"
                                >
                                    <span>Ir al sitio</span>
                                    <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </a>

                                <div class="flex items-center gap-1">
                                    <Link
                                        :href="route('admin.revistas-varias.show', item.id)"
                                        class="p-1.5 text-slate-400 hover:text-[#35449F] hover:bg-slate-50 rounded-lg transition"
                                        title="Ver detalles"
                                    >
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </Link>
                                    <Link
                                        :href="route('admin.revistas-varias.edit', item.id)"
                                        class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-slate-50 rounded-lg transition"
                                        title="Editar"
                                    >
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </Link>
                                    <button
                                        type="button"
                                        @click="confirmDelete(item)"
                                        class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition cursor-pointer"
                                        title="Eliminar"
                                    >
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Estado Vacío -->
                <div v-else class="bg-white rounded-2xl p-12 text-center border border-gray-100 shadow-xs">
                    <div class="size-16 mx-auto rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center mb-3">
                        <svg class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">No se encontraron revistas registradas</h3>
                    <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                        Registre las revistas de referencia asociadas o indizadas para mostrarlas en el portal web institucional.
                    </p>
                    <div class="mt-4">
                        <Link :href="route('admin.revistas-varias.create')">
                            <PrimaryButton class="text-xs">
                                Registrar primera revista
                            </PrimaryButton>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- CONTENIDO: VISTA EN TABLA -->
            <div v-else class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden">
                <div v-if="list.length > 0" class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-400 font-extrabold border-b border-slate-100">
                            <tr>
                                <th class="py-3.5 px-4 w-12 text-center">#</th>
                                <th class="py-3.5 px-4 w-16">Portada</th>
                                <th class="py-3.5 px-4">Revista & Descripción</th>
                                <th class="py-3.5 px-4">Entidad / Institución</th>
                                <th class="py-3.5 px-4">Enlace Web</th>
                                <th class="py-3.5 px-4 text-center">Estado</th>
                                <th class="py-3.5 px-4 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="(item, index) in list"
                                :key="item.id"
                                class="hover:bg-slate-50/60 transition group"
                            >
                                <td class="py-3 px-4 text-center font-mono text-xs font-bold text-slate-400 whitespace-nowrap">
                                    {{ (props.revistas?.from ? props.revistas.from + index : index + 1) }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="size-12 rounded-xl overflow-hidden bg-slate-100 border border-slate-200/80 shrink-0 flex items-center justify-center p-1">
                                        <img
                                            v-if="item.portada_url && !imgErrors[item.id]"
                                            :src="item.portada_url"
                                            :alt="item.rev_titulo"
                                            @error="onImgError(item.id)"
                                            class="w-full h-full object-contain"
                                        />
                                        <div v-else class="text-slate-400">
                                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                            </svg>
                                        </div>
                                    </div>
                                </td>

                                <td class="py-3 px-4 max-w-xs">
                                    <Link
                                        :href="route('admin.revistas-varias.show', item.id)"
                                        class="font-bold text-slate-800 text-sm hover:text-[#35449F] transition block truncate"
                                    >
                                        {{ item.rev_titulo }}
                                    </Link>
                                    <p v-if="item.rev_descripcion" class="text-xs text-slate-400 line-clamp-1 mt-0.5">
                                        {{ item.rev_descripcion }}
                                    </p>
                                    <span v-if="item.rev_issn" class="text-[11px] font-mono text-slate-400 block mt-0.5">
                                        ISSN: {{ item.rev_issn }}
                                    </span>
                                </td>

                                <td class="py-3 px-4 text-xs font-semibold text-slate-700 whitespace-nowrap">
                                    {{ item.rev_entidad || '—' }}
                                </td>

                                <td class="py-3 px-4 text-xs">
                                    <a
                                        :href="item.rev_url_enlace"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="text-[#35449F] font-semibold hover:underline inline-flex items-center gap-1 max-w-[200px] truncate"
                                    >
                                        <span class="truncate">{{ item.rev_url_enlace }}</span>
                                        <svg class="size-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                    </a>
                                </td>

                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <button
                                        type="button"
                                        @click="toggleActivo(item.id)"
                                        class="px-2.5 py-1 rounded-full text-xs font-bold transition cursor-pointer"
                                        :class="item.rev_activo ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 hover:bg-slate-200'"
                                    >
                                        {{ item.rev_activo ? 'Activa' : 'Oculta' }}
                                    </button>
                                </td>

                                <td class="py-3 px-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1">
                                        <Link
                                            :href="route('admin.revistas-varias.show', item.id)"
                                            class="p-1.5 text-slate-400 hover:text-[#35449F] hover:bg-slate-50 rounded-lg transition"
                                            title="Ver detalles"
                                        >
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </Link>
                                        <Link
                                            :href="route('admin.revistas-varias.edit', item.id)"
                                            class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-slate-50 rounded-lg transition"
                                            title="Editar"
                                        >
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </Link>
                                        <button
                                            type="button"
                                            @click="confirmDelete(item)"
                                            class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition cursor-pointer"
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

                <div v-else class="p-12 text-center">
                    <h3 class="text-base font-bold text-slate-800">No hay registros</h3>
                    <p class="text-xs text-slate-400 mt-1">Ajuste la búsqueda para ver otros resultados.</p>
                </div>
            </div>

            <!-- Paginación -->
            <div v-if="props.revistas?.links && list.length > 0" class="flex justify-center pt-2">
                <Pagination :links="props.revistas.links" />
            </div>
        </div>

        <!-- Modal Confirmación Eliminación -->
        <ConfirmationModal :show="confirmingDeletion" @close="confirmingDeletion = false">
            <template #title>
                Eliminar Revista de Referencia
            </template>
            <template #content>
                ¿Está seguro de que desea eliminar la revista
                <span class="font-bold text-slate-900">"{{ itemToDelete?.rev_titulo }}"</span>?
                Esta acción no se puede deshacer.
            </template>
            <template #footer>
                <SecondaryButton @click="confirmingDeletion = false" class="mr-2">
                    Cancelar
                </SecondaryButton>
                <DangerButton @click="deleteItem">
                    Sí, eliminar revista
                </DangerButton>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>
