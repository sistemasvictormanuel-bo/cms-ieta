<script setup>
import { ref, computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import Pagination from '@/Components/Pagination.vue';
import { assetUrl } from '@/utils/asset';

const props = defineProps({
    noticias: {
        type: [Array, Object],
        default: () => ({ data: [] }),
    },
    personas: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        default: () => ({ total: 0, publicadas: 0, borradores: 0, destacadas: 0 }),
    },
    filters: {
        type: Object,
        default: () => ({ search: '', publicado: null, destacado: null, persona_id: null, per_page: 10 }),
    },
});

const list = computed(() => {
    if (Array.isArray(props.noticias)) {
        return props.noticias;
    }
    return props.noticias?.data || [];
});

const search = ref(props.filters?.search || '');
const publicado = ref(props.filters?.publicado ?? '');
const destacado = ref(props.filters?.destacado ?? '');
const personaId = ref(props.filters?.persona_id || '');
const perPage = ref(props.filters?.per_page || 10);
let searchTimeout = null;

const applyFilters = () => {
    router.get(
        route('admin.noticias.index'),
        {
            search: search.value ? search.value : undefined,
            publicado: publicado.value !== '' ? publicado.value : undefined,
            destacado: destacado.value !== '' ? destacado.value : undefined,
            persona_id: personaId.value ? personaId.value : undefined,
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

const resetFilters = () => {
    search.value = '';
    publicado.value = '';
    destacado.value = '';
    personaId.value = '';
    perPage.value = 10;
    applyFilters();
};

const togglingId = ref(null);

const togglePublicado = (item) => {
    togglingId.value = item.id;
    router.patch(route('admin.noticias.toggle-publicado', item.id), {}, {
        preserveScroll: true,
        onFinish: () => {
            togglingId.value = null;
        },
    });
};

const toggleDestacado = (item) => {
    togglingId.value = item.id;
    router.patch(route('admin.noticias.toggle-destacado', item.id), {}, {
        preserveScroll: true,
        onFinish: () => {
            togglingId.value = null;
        },
    });
};

// Modal de confirmación de eliminación
const confirmingDeletion = ref(false);
const itemToDelete = ref(null);

const confirmDelete = (item) => {
    itemToDelete.value = item;
    confirmingDeletion.value = true;
};

const deleteItem = () => {
    if (!itemToDelete.value) return;

    router.delete(route('admin.noticias.destroy', itemToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            confirmingDeletion.value = false;
            itemToDelete.value = null;
        },
    });
};

const formatDate = (dateStr) => {
    if (!dateStr) return 'Sin fecha';
    const cleanStr = dateStr.includes('T') ? dateStr.split('T')[0] : dateStr;
    const parts = cleanStr.split('-');
    if (parts.length === 3) {
        return `${parts[2]}/${parts[1]}/${parts[0]}`;
    }
    return dateStr;
};

const getPersonaNombre = (persona) => {
    if (!persona) return 'Institucional (IETA)';
    return `${persona.p_nombre} ${persona.p_apellido_paterno || ''}`.trim();
};
</script>

<template>
    <AppLayout title="Gestión de Noticias">
        <template #header>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 w-full">
                <div>
                    <h1 class="font-black text-2xl text-[#14161C] tracking-tight">
                        Noticias & Comunicados
                    </h1>
                    <p class="text-[14.5px] text-gray-600 font-medium mt-0.5">
                        Administración editorial, publicaciones y novedades informativas del IETA.
                    </p>
                </div>
                <Link :href="route('admin.noticias.create')">
                    <PrimaryButton class="inline-flex items-center gap-2 cursor-pointer shadow-sm hover:shadow-md transition-all">
                      
                        <span>+ Nueva Noticia</span>
                    </PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Tarjetas de Estadísticas KPI -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-xs flex items-center justify-between">
                    <div>
                        <p class="text-[12.5px] font-bold text-slate-500 uppercase tracking-wider">Total Noticias</p>
                        <p class="text-2xl font-black text-slate-900 mt-1">{{ stats.total }}</p>
                    </div>
                    <div class="size-11 rounded-xl bg-slate-100 flex items-center justify-center text-slate-600">
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                        </svg>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-xs flex items-center justify-between">
                    <div>
                        <p class="text-[12.5px] font-bold text-emerald-600 uppercase tracking-wider">Publicadas</p>
                        <p class="text-2xl font-black text-emerald-700 mt-1">{{ stats.publicadas }}</p>
                    </div>
                    <div class="size-11 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-xs flex items-center justify-between">
                    <div>
                        <p class="text-[12.5px] font-bold text-amber-600 uppercase tracking-wider">Borradores</p>
                        <p class="text-2xl font-black text-amber-700 mt-1">{{ stats.borradores }}</p>
                    </div>
                    <div class="size-11 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600">
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-xs flex items-center justify-between">
                    <div>
                        <p class="text-[12.5px] font-bold text-[#35449F] uppercase tracking-wider">Destacadas</p>
                        <p class="text-2xl font-black text-[#35449F] mt-1">{{ stats.destacadas }}</p>
                    </div>
                    <div class="size-11 rounded-xl bg-[#35449F]/10 flex items-center justify-center text-[#35449F]">
                        <svg class="size-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Barra de Filtros y Búsqueda -->
            <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-xs">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                    <!-- Búsqueda por texto -->
                    <div class="lg:col-span-2 relative">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Buscar por título, contenido o autor..."
                            class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-[14.5px] font-medium text-slate-800 placeholder-slate-400 focus:bg-white focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/20 transition-all"
                            @input="onSearchInput"
                        />
                        <svg class="size-5 text-slate-400 absolute left-3 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    <!-- Filtro por Estado (Publicado/Borrador) -->
                    <div>
                        <select
                            v-model="publicado"
                            class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-[14.5px] font-medium text-slate-700 focus:bg-white focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/20 transition-all"
                            @change="applyFilters"
                        >
                            <option value="">Estado: Todos</option>
                            <option value="true">Solo Publicados</option>
                            <option value="false">Solo Borradores</option>
                        </select>
                    </div>

                    <!-- Filtro por Destacado -->
                    <div>
                        <select
                            v-model="destacado"
                            class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-[14.5px] font-medium text-slate-700 focus:bg-white focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/20 transition-all"
                            @change="applyFilters"
                        >
                            <option value="">Destacado: Todos</option>
                            <option value="true">Solo Destacadas</option>
                            <option value="false">No Destacadas</option>
                        </select>
                    </div>

                    <!-- Filtro por Redactor / Persona -->
                    <div>
                        <select
                            v-model="personaId"
                            class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-[14.5px] font-medium text-slate-700 focus:bg-white focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/20 transition-all"
                            @change="applyFilters"
                        >
                            <option value="">Redactor: Todos</option>
                            <option v-for="persona in personas" :key="persona.id" :value="persona.id">
                                {{ persona.nombre_completo || persona.p_nombre }}
                            </option>
                        </select>
                    </div>
                </div>

                <div v-if="search || publicado !== '' || destacado !== '' || personaId" class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <span>Filtros activos aplicados</span>
                    <button
                        type="button"
                        class="text-[#35449F] hover:text-[#25327a] font-bold cursor-pointer transition-colors"
                        @click="resetFilters"
                    >
                        Limpiar todos los filtros
                    </button>
                </div>
            </div>

            <!-- Tabla de Noticias -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-100 text-[12.5px] font-black uppercase tracking-wider text-slate-500">
                                <th class="py-3.5 px-4 w-12 text-center">#</th>
                                <th class="py-3.5 px-5">Portada</th>
                                <th class="py-3.5 px-5">Título & Resumen</th>
                                <th class="py-3.5 px-5">Redactor / Autor</th>
                                <th class="py-3.5 px-5">Fecha</th>
                                <th class="py-3.5 px-4 text-center">Destacado</th>
                                <th class="py-3.5 px-4 text-center">Estado</th>
                                <th class="py-3.5 px-5 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-[14px]">
                            <tr
                                v-for="(item, index) in list"
                                :key="item.id"
                                class="hover:bg-slate-50/70 transition-colors group"
                            >
                                <!-- Número correlativo -->
                                <td class="py-3.5 px-4 text-center font-mono text-xs font-bold text-slate-400 whitespace-nowrap">
                                    {{ (props.noticias?.from ? props.noticias.from + index : index + 1) }}
                                </td>

                                <!-- Portada -->
                                <td class="py-3.5 px-5 w-24">
                                    <div class="size-16 rounded-xl overflow-hidden bg-slate-100 border border-slate-200 flex items-center justify-center shrink-0 shadow-2xs">
                                        <img
                                            v-if="item.portada_url"
                                            :src="assetUrl(item.portada_url)"
                                            :alt="item.not_titulo"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"
                                        />
                                        <svg v-else class="size-7 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                </td>

                                <!-- Título y Resumen -->
                                <td class="py-3.5 px-5 min-w-[280px] max-w-[420px]">
                                    <div class="space-y-1">
                                        <Link
                                            :href="route('admin.noticias.show', item.id)"
                                            class="font-extrabold text-[15px] text-slate-900 hover:text-[#35449F] transition-colors line-clamp-1 block"
                                        >
                                            {{ item.not_titulo }}
                                        </Link>
                                        <p v-if="item.not_descripcion" class="text-xs text-slate-500 line-clamp-2 leading-relaxed">
                                            {{ item.not_descripcion }}
                                        </p>
                                        <div v-if="item.not_url" class="flex items-center gap-1.5 text-[11.5px] text-indigo-600 font-semibold pt-0.5">
                                            <svg class="size-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                            </svg>
                                            <a :href="item.not_url" target="_blank" rel="noopener noreferrer" class="truncate hover:underline max-w-[240px]">
                                                {{ item.not_url }}
                                            </a>
                                        </div>
                                    </div>
                                </td>

                                <!-- Redactor / Persona -->
                                <td class="py-3.5 px-5 whitespace-nowrap">
                                    <div class="flex items-center gap-2.5">
                                        <div class="size-8 rounded-full bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center text-xs font-black text-slate-600 shrink-0">
                                            <img
                                                v-if="item.persona?.foto_url"
                                                :src="assetUrl(item.persona.foto_url)"
                                                :alt="getPersonaNombre(item.persona)"
                                                class="w-full h-full object-cover"
                                            />
                                            <span v-else>
                                                {{ item.persona ? item.persona.p_nombre?.charAt(0).toUpperCase() : 'I' }}
                                            </span>
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-800 text-[13.5px]">
                                                {{ getPersonaNombre(item.persona) }}
                                            </p>
                                            <p v-if="item.persona?.p_correo" class="text-[11px] text-slate-400">
                                                {{ item.persona.p_correo }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Fecha -->
                                <td class="py-3.5 px-5 whitespace-nowrap text-slate-600 font-semibold text-[13px]">
                                    {{ formatDate(item.not_fecha) }}
                                </td>

                                <!-- Destacado (Toggle interactivo) -->
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <button
                                        type="button"
                                        :disabled="togglingId === item.id"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-extrabold cursor-pointer transition-all disabled:opacity-50"
                                        :class="item.not_destacado 
                                            ? 'bg-amber-100/90 text-amber-800 border border-amber-200 hover:bg-amber-200' 
                                            : 'bg-slate-100 text-slate-500 border border-slate-200 hover:bg-slate-200'"
                                        title="Clic para cambiar estado de destacado"
                                        @click="toggleDestacado(item)"
                                    >
                                        <svg class="size-3.5" :fill="item.not_destacado ? 'currentColor' : 'none'" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                        </svg>
                                        <span>{{ item.not_destacado ? 'Destacada' : 'Normal' }}</span>
                                    </button>
                                </td>

                                <!-- Publicado / Borrador (Toggle interactivo) -->
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <button
                                        type="button"
                                        :disabled="togglingId === item.id"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-extrabold cursor-pointer transition-all disabled:opacity-50"
                                        :class="item.not_publicado 
                                            ? 'bg-emerald-100 text-emerald-800 border border-emerald-200 hover:bg-emerald-200' 
                                            : 'bg-slate-100 text-slate-600 border border-slate-200 hover:bg-slate-200'"
                                        title="Clic para alternar publicación"
                                        @click="togglePublicado(item)"
                                    >
                                        <span class="size-1.5 rounded-full" :class="item.not_publicado ? 'bg-emerald-600' : 'bg-slate-400'"></span>
                                        <span>{{ item.not_publicado ? 'Publicada' : 'Borrador' }}</span>
                                    </button>
                                </td>

                                <!-- Acciones -->
                                <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Ver -->
                                        <Link
                                            :href="route('admin.noticias.show', item.id)"
                                            class="size-8.5 rounded-lg flex items-center justify-center text-slate-500 hover:text-[#35449F] hover:bg-slate-100 transition-colors"
                                            title="Ver noticia completa"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </Link>

                                        <!-- Editar -->
                                        <Link
                                            :href="route('admin.noticias.edit', item.id)"
                                            class="size-8.5 rounded-lg flex items-center justify-center text-slate-500 hover:text-emerald-700 hover:bg-emerald-50 transition-colors"
                                            title="Editar noticia"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </Link>

                                        <!-- Eliminar -->
                                        <button
                                            type="button"
                                            class="size-8.5 rounded-lg flex items-center justify-center text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer"
                                            title="Eliminar noticia"
                                            @click="confirmDelete(item)"
                                        >
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Estado Vacío -->
                            <tr v-if="list.length === 0">
                                <td colspan="8" class="py-12 px-5 text-center">
                                    <div class="max-w-sm mx-auto space-y-3">
                                        <div class="size-14 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 mx-auto">
                                            <svg class="size-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                            </svg>
                                        </div>
                                        <h3 class="font-extrabold text-base text-slate-800">No se encontraron noticias</h3>
                                        <p class="text-xs text-slate-500">
                                            {{ search || publicado !== '' || destacado !== '' || personaId 
                                                ? 'No hay resultados que coincidan con los criterios de búsqueda aplicados.' 
                                                : 'Aún no se han redactado noticias en la plataforma.' }}
                                        </p>
                                        <div class="pt-2">
                                            <Link :href="route('admin.noticias.create')">
                                                <PrimaryButton class="cursor-pointer text-xs">
                                                    Redactar Primera Noticia
                                                </PrimaryButton>
                                            </Link>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div v-if="noticias?.links && list.length > 0" class="p-4 border-t border-slate-100 bg-slate-50/50">
                    <Pagination :links="noticias.links" />
                </div>
            </div>
        </div>

        <!-- Modal de Confirmación de Eliminación -->
        <ConfirmationModal :show="confirmingDeletion" @close="confirmingDeletion = false">
            <template #title>
                <div class="flex items-center gap-2 text-rose-600 font-black">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>Eliminar Noticia</span>
                </div>
            </template>
            <template #content>
                <p class="text-sm text-slate-600">
                    ¿Estás seguro de que deseas eliminar permanentemente la noticia
                    <strong class="text-slate-900 font-bold">"{{ itemToDelete?.not_titulo }}"</strong>?
                </p>
                <p class="text-xs text-slate-500 mt-2">
                    Esta acción suprimirá su portada asociada y no se podrá deshacer.
                </p>
            </template>
            <template #footer>
                <SecondaryButton class="cursor-pointer" @click="confirmingDeletion = false">
                    Cancelar
                </SecondaryButton>
                <DangerButton class="ml-3 cursor-pointer" @click="deleteItem">
                    Sí, Eliminar Noticia
                </DangerButton>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>
