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
    imagenes: {
        type: [Array, Object],
        default: () => ({ data: [] }),
    },
    categorias: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        default: () => ({
            total: 0,
            activas: 0,
            inactivas: 0,
            destacadas: 0,
            categorias_count: 0,
        }),
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            categoria: '',
            destacado: '',
            activo: '',
            per_page: 12,
            view: 'cards',
        }),
    },
});

const list = computed(() => {
    if (Array.isArray(props.imagenes)) {
        return props.imagenes;
    }
    return props.imagenes?.data || [];
});

const search = ref(props.filters?.search || '');
const categoria = ref(props.filters?.categoria || '');
const destacado = ref(props.filters?.destacado ?? '');
const activo = ref(props.filters?.activo ?? '');
const perPage = ref(props.filters?.per_page || 12);
let searchTimeout = null;

const applyFilters = () => {
    router.get(
        route('admin.galeria.index'),
        {
            search: search.value ? search.value : undefined,
            categoria: categoria.value ? categoria.value : undefined,
            destacado: destacado.value !== '' ? destacado.value : undefined,
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

    router.delete(route('admin.galeria.destroy', itemToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            confirmingDeletion.value = false;
            itemToDelete.value = null;
        },
    });
};

const toggleDestacado = (id) => {
    router.patch(route('admin.galeria.toggle-destacado', id), {}, {
        preserveScroll: true,
    });
};

const toggleActivo = (id) => {
    router.patch(route('admin.galeria.toggle-activo', id), {}, {
        preserveScroll: true,
    });
};

// Toggle cards / table view
const viewMode = ref(
    typeof window !== 'undefined' && localStorage.getItem('admin_view_mode_galeria')
        ? localStorage.getItem('admin_view_mode_galeria')
        : (props.filters?.view || 'cards')
);

const setViewMode = (mode) => {
    viewMode.value = mode;
    if (typeof window !== 'undefined') {
        localStorage.setItem('admin_view_mode_galeria', mode);
    }
};

const copiedId = ref(null);
const copyUrl = (item) => {
    if (!item?.imagen_url) return;
    const fullUrl = window.location.origin + item.imagen_url;
    navigator.clipboard.writeText(fullUrl).then(() => {
        copiedId.value = item.id;
        setTimeout(() => {
            if (copiedId.value === item.id) {
                copiedId.value = null;
            }
        }, 2000);
    });
};

const imgErrors = ref({});
const onImgError = (id) => {
    imgErrors.value[id] = true;
};

const formatDate = (dateString) => {
    if (!dateString) return '—';
    try {
        const date = new Date(dateString + 'T00:00:00');
        return date.toLocaleDateString('es-ES', { year: 'numeric', month: 'short', day: 'numeric' });
    } catch {
        return dateString;
    }
};
</script>

<template>
    <AppLayout title="Galería de Imágenes">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 w-full min-w-0">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-1.5 text-[11px] font-semibold text-slate-400 mb-0.5 leading-none">
                        <Link :href="route('dashboard')" class="hover:text-[#35449F]">Inicio</Link>
                        <span>/</span>
                        <span class="text-slate-500">Portal Web</span>
                        <span>/</span>
                        <span class="text-slate-700 font-bold">Galería</span>
                    </div>
                    <h1 class="font-extrabold text-xl sm:text-2xl text-[#14161C] tracking-tight truncate leading-tight">
                        Galería de Imágenes
                    </h1>
                </div>
                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    <Link :href="route('admin.galeria.create')">
                        <PrimaryButton class="cursor-pointer gap-1.5 whitespace-nowrap shadow-sm hover:shadow">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            <span>Nueva Imagen</span>
                        </PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-6 pb-12">
            <!-- Estadísticas Rápidas -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-xs flex items-center gap-3">
                    <div class="size-11 rounded-xl bg-blue-50 text-[#35449F] flex items-center justify-center shrink-0">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Fotografías</div>
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
                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Activas en Web</div>
                        <div class="text-xl font-extrabold text-slate-800">{{ stats.activas }}</div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-xs flex items-center gap-3">
                    <div class="size-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                        <svg class="size-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Destacadas</div>
                        <div class="text-xl font-extrabold text-slate-800">{{ stats.destacadas }}</div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-xs flex items-center gap-3">
                    <div class="size-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Categorías</div>
                        <div class="text-xl font-extrabold text-slate-800">{{ stats.categorias_count }}</div>
                    </div>
                </div>
            </div>

            <!-- Filtros & Barra de Búsqueda -->
            <div class="bg-white rounded-2xl p-4 shadow-xs border border-gray-100 flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4">
                <!-- Input de búsqueda -->
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
                        placeholder="Buscar por título, categoría, lugar o descripción..."
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

                <!-- Selectores de Filtro -->
                <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
                    <!-- Categoría -->
                    <div class="flex items-center gap-1.5">
                        <label for="cat_filter" class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Categoría:
                        </label>
                        <select
                            id="cat_filter"
                            v-model="categoria"
                            @change="applyFilters"
                            class="text-xs font-semibold rounded-xl border-gray-200 py-2 pl-3 pr-8 bg-gray-50 focus:bg-white focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10"
                        >
                            <option value="">Todas</option>
                            <option v-for="cat in categorias" :key="cat" :value="cat">
                                {{ cat }}
                            </option>
                        </select>
                    </div>

                    <!-- Estado Activo -->
                    <div class="flex items-center gap-1.5">
                        <label for="activo_filter" class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Estado:
                        </label>
                        <select
                            id="activo_filter"
                            v-model="activo"
                            @change="applyFilters"
                            class="text-xs font-semibold rounded-xl border-gray-200 py-2 pl-3 pr-8 bg-gray-50 focus:bg-white focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10"
                        >
                            <option value="">Todos</option>
                            <option value="1">Activas</option>
                            <option value="0">Ocultas</option>
                        </select>
                    </div>

                    <!-- Destacado -->
                    <div class="flex items-center gap-1.5">
                        <label for="dest_filter" class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Destacado:
                        </label>
                        <select
                            id="dest_filter"
                            v-model="destacado"
                            @change="applyFilters"
                            class="text-xs font-semibold rounded-xl border-gray-200 py-2 pl-3 pr-8 bg-gray-50 focus:bg-white focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10"
                        >
                            <option value="">Todos</option>
                            <option value="1">Destacadas</option>
                            <option value="0">Normales</option>
                        </select>
                    </div>

                    <!-- Botones de Modo de Vista (Cards / Table) -->
                    <div class="flex items-center bg-gray-100 p-1 rounded-xl border border-gray-200/80">
                        <button
                            type="button"
                            @click="setViewMode('cards')"
                            :class="viewMode === 'cards' ? 'bg-white text-[#35449F] shadow-xs font-bold' : 'text-gray-500 hover:text-gray-800 font-medium'"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs transition cursor-pointer"
                            title="Vista en Tarjetas (Cards)"
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
                            title="Vista en Tabla (Lista)"
                        >
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            <span class="hidden sm:inline">Tabla</span>
                        </button>
                    </div>

                    <!-- Paginación -->
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

            <!-- CONTENIDO: VISTA EN TARJETAS (CARDS) -->
            <div v-if="viewMode === 'cards'">
                <div v-if="list.length > 0" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-5">
                    <div
                        v-for="item in list"
                        :key="item.id"
                        class="bg-white rounded-2xl border border-gray-100 shadow-xs hover:shadow-md transition-all duration-200 overflow-hidden flex flex-col group"
                    >
                        <!-- Contenedor Imagen -->
                        <div class="relative aspect-4/3 bg-slate-100 overflow-hidden shrink-0">
                            <img
                                v-if="item.imagen_url && !imgErrors[item.id]"
                                :src="item.imagen_url"
                                :alt="item.gal_titulo"
                                @error="onImgError(item.id)"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            />
                            <div
                                v-else
                                class="w-full h-full flex flex-col items-center justify-center text-slate-300 bg-slate-50"
                            >
                                <svg class="size-12 stroke-[1.2]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="text-[11px] font-semibold text-slate-400 mt-1">Sin fotografía</span>
                            </div>

                            <!-- Badges Flotantes -->
                            <div class="absolute top-2.5 left-2.5 right-2.5 flex items-center justify-between gap-1 pointer-events-none">
                                <span class="px-2.5 py-1 rounded-lg text-[10.5px] font-bold shadow-xs backdrop-blur-md bg-white/90 text-slate-800 border border-white/60 pointer-events-auto">
                                    {{ item.gal_categoria }}
                                </span>
                                <div class="flex items-center gap-1">
                                    <button
                                        type="button"
                                        @click.stop="toggleDestacado(item.id)"
                                        :title="item.gal_destacado ? 'Quitar de destacadas' : 'Marcar como destacada'"
                                        class="p-1.5 rounded-lg shadow-xs backdrop-blur-md transition cursor-pointer pointer-events-auto"
                                        :class="item.gal_destacado ? 'bg-amber-400 text-white hover:bg-amber-500' : 'bg-white/80 text-slate-400 hover:text-amber-500 hover:bg-white'"
                                    >
                                        <svg class="size-3.5 fill-current" viewBox="0 0 24 24">
                                            <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" />
                                        </svg>
                                    </button>
                                    <button
                                        type="button"
                                        @click.stop="toggleActivo(item.id)"
                                        :title="item.gal_activo ? 'Ocultar en web' : 'Publicar en web'"
                                        class="p-1.5 rounded-lg shadow-xs backdrop-blur-md transition cursor-pointer pointer-events-auto"
                                        :class="item.gal_activo ? 'bg-emerald-500 text-white hover:bg-emerald-600' : 'bg-slate-700/80 text-white/70 hover:bg-slate-800'"
                                    >
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path v-if="item.gal_activo" stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path v-if="item.gal_activo" stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            <path v-else stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Cuerpo de la Tarjeta -->
                        <div class="p-4 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="font-bold text-slate-800 text-[15px] leading-snug line-clamp-2 hover:text-[#35449F] transition">
                                    <Link :href="route('admin.galeria.show', item.id)">
                                        {{ item.gal_titulo }}
                                    </Link>
                                </h3>

                                <p v-if="item.gal_descripcion" class="text-xs text-slate-500 line-clamp-2 mt-1.5 leading-relaxed">
                                    {{ item.gal_descripcion }}
                                </p>

                                <!-- Meta: Fecha y Lugar -->
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-[11.5px] text-slate-400 font-medium mt-3">
                                    <span v-if="item.gal_fecha" class="flex items-center gap-1">
                                        <svg class="size-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        {{ formatDate(item.gal_fecha) }}
                                    </span>
                                    <span v-if="item.gal_lugar" class="flex items-center gap-1">
                                        <svg class="size-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span class="truncate max-w-[120px]">{{ item.gal_lugar }}</span>
                                    </span>
                                </div>
                            </div>

                            <!-- Footer de Acciones -->
                            <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                                <button
                                    v-if="item.imagen_url"
                                    type="button"
                                    @click="copyUrl(item)"
                                    class="text-[11px] font-semibold text-slate-500 hover:text-[#35449F] flex items-center gap-1 transition cursor-pointer"
                                    :title="'Copiar URL pública: ' + item.imagen_url"
                                >
                                    <svg v-if="copiedId !== item.id" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                    </svg>
                                    <svg v-else class="size-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    <span>{{ copiedId === item.id ? '¡Copiado!' : 'Copiar URL' }}</span>
                                </button>
                                <span v-else class="text-[11px] text-slate-300">Sin archivo</span>

                                <div class="flex items-center gap-1">
                                    <Link
                                        :href="route('admin.galeria.show', item.id)"
                                        class="p-1.5 text-slate-400 hover:text-[#35449F] hover:bg-slate-50 rounded-lg transition"
                                        title="Ver detalles"
                                    >
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </Link>
                                    <Link
                                        :href="route('admin.galeria.edit', item.id)"
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

                <!-- Estado Vacío Cards -->
                <div v-else class="bg-white rounded-2xl p-12 text-center border border-gray-100 shadow-xs">
                    <div class="size-16 mx-auto rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center mb-3">
                        <svg class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">No se encontraron imágenes</h3>
                    <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                        No hay fotografías registradas con los filtros actuales. Prueba cambiando la búsqueda o sube una nueva imagen.
                    </p>
                    <div class="mt-4">
                        <Link :href="route('admin.galeria.create')">
                            <PrimaryButton class="text-xs">
                                Subir primera imagen
                            </PrimaryButton>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- CONTENIDO: VISTA EN TABLA (LISTA) -->
            <div v-else class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden">
                <div v-if="list.length > 0" class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-400 font-extrabold border-b border-slate-100">
                            <tr>
                                <th class="py-3.5 px-4 w-12 text-center">#</th>
                                <th class="py-3.5 px-4 w-16">Miniatura</th>
                                <th class="py-3.5 px-4">Título & Descripción</th>
                                <th class="py-3.5 px-4">Categoría</th>
                                <th class="py-3.5 px-4">Fecha & Lugar</th>
                                <th class="py-3.5 px-4 text-center">Destacado</th>
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
                                <!-- Número correlativo -->
                                <td class="py-3 px-4 text-center font-mono text-xs font-bold text-slate-400 whitespace-nowrap">
                                    {{ (props.imagenes?.from ? props.imagenes.from + index : index + 1) }}
                                </td>

                                <!-- Miniatura -->
                                <td class="py-3 px-4">
                                    <div class="size-14 rounded-xl overflow-hidden bg-slate-100 border border-slate-200/80 shrink-0">
                                        <img
                                            v-if="item.imagen_url && !imgErrors[item.id]"
                                            :src="item.imagen_url"
                                            :alt="item.gal_titulo"
                                            @error="onImgError(item.id)"
                                            class="w-full h-full object-cover"
                                        />
                                        <div v-else class="w-full h-full flex items-center justify-center text-slate-300">
                                            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    </div>
                                </td>

                                <!-- Título & Descripción -->
                                <td class="py-3 px-4 max-w-xs">
                                    <Link
                                        :href="route('admin.galeria.show', item.id)"
                                        class="font-bold text-slate-800 text-sm hover:text-[#35449F] transition block truncate"
                                    >
                                        {{ item.gal_titulo }}
                                    </Link>
                                    <p v-if="item.gal_descripcion" class="text-xs text-slate-400 line-clamp-1 mt-0.5">
                                        {{ item.gal_descripcion }}
                                    </p>
                                    <button
                                        v-if="item.imagen_url"
                                        type="button"
                                        @click="copyUrl(item)"
                                        class="text-[10.5px] font-semibold text-slate-400 hover:text-[#35449F] inline-flex items-center gap-1 mt-1 cursor-pointer"
                                    >
                                        <svg v-if="copiedId !== item.id" class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        <svg v-else class="size-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span>{{ copiedId === item.id ? '¡URL Copiada!' : 'Copiar URL' }}</span>
                                    </button>
                                </td>

                                <!-- Categoría -->
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-700">
                                        {{ item.gal_categoria }}
                                    </span>
                                </td>

                                <!-- Fecha & Lugar -->
                                <td class="py-3 px-4 whitespace-nowrap text-xs text-slate-500">
                                    <div class="font-medium text-slate-700">{{ formatDate(item.gal_fecha) }}</div>
                                    <div v-if="item.gal_lugar" class="text-[11px] text-slate-400 truncate max-w-[140px]">
                                        {{ item.gal_lugar }}
                                    </div>
                                </td>

                                <!-- Destacado -->
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <button
                                        type="button"
                                        @click="toggleDestacado(item.id)"
                                        class="p-1.5 rounded-lg transition cursor-pointer"
                                        :class="item.gal_destacado ? 'text-amber-500 bg-amber-50 hover:bg-amber-100' : 'text-slate-300 hover:text-amber-500 hover:bg-slate-50'"
                                        :title="item.gal_destacado ? 'Quitar de destacadas' : 'Marcar como destacada'"
                                    >
                                        <svg class="size-4 fill-current" viewBox="0 0 24 24">
                                            <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z" />
                                        </svg>
                                    </button>
                                </td>

                                <!-- Estado Activo -->
                                <td class="py-3 px-4 text-center whitespace-nowrap">
                                    <button
                                        type="button"
                                        @click="toggleActivo(item.id)"
                                        class="px-2.5 py-1 rounded-full text-xs font-bold transition cursor-pointer"
                                        :class="item.gal_activo ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 hover:bg-slate-200'"
                                    >
                                        {{ item.gal_activo ? 'Activa' : 'Oculta' }}
                                    </button>
                                </td>

                                <!-- Acciones -->
                                <td class="py-3 px-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1">
                                        <Link
                                            :href="route('admin.galeria.show', item.id)"
                                            class="p-1.5 text-slate-400 hover:text-[#35449F] hover:bg-slate-50 rounded-lg transition"
                                            title="Ver detalles"
                                        >
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </Link>
                                        <Link
                                            :href="route('admin.galeria.edit', item.id)"
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

                <!-- Estado Vacío Tabla -->
                <div v-else class="p-12 text-center">
                    <div class="size-16 mx-auto rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center mb-3">
                        <svg class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">No hay registros para mostrar</h3>
                    <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                        Intenta ajustar los criterios de búsqueda o registra una nueva fotografía en el sistema.
                    </p>
                </div>
            </div>

            <!-- Paginación -->
            <div v-if="props.imagenes?.links && list.length > 0" class="flex justify-center pt-2">
                <Pagination :links="props.imagenes.links" />
            </div>
        </div>

        <!-- Modal de Confirmación para Eliminar -->
        <ConfirmationModal :show="confirmingDeletion" @close="confirmingDeletion = false">
            <template #title>
                Eliminar Imagen de la Galería
            </template>
            <template #content>
                ¿Está completamente seguro de eliminar la imagen
                <span class="font-bold text-slate-900">"{{ itemToDelete?.gal_titulo }}"</span>?
                Esta acción borrará permanentemente el registro y el archivo del servidor.
            </template>
            <template #footer>
                <SecondaryButton @click="confirmingDeletion = false" class="mr-2">
                    Cancelar
                </SecondaryButton>
                <DangerButton @click="deleteItem">
                    Sí, eliminar imagen
                </DangerButton>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>
