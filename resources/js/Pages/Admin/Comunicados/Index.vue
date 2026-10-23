<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';

const props = defineProps({
    comunicados: {
        type: Object,
        required: true,
    },
    stats: {
        type: Object,
        default: () => ({ total: 0, activos: 0, pausados: 0 }),
    },
    filters: {
        type: Object,
        default: () => ({ search: '', activo: null, tipo: '', per_page: 10 }),
    },
});

const search = ref(props.filters.search || '');
const activo = ref(props.filters.activo ?? '');
const tipo = ref(props.filters.tipo || '');
const perPage = ref(props.filters.per_page || 10);
let searchTimeout = null;

const confirmingDeletion = ref(false);
const itemToDelete = ref(null);

const applyFilters = () => {
    router.get(
        route('admin.comunicados.index'),
        {
            search: search.value || undefined,
            activo: activo.value !== '' ? activo.value : undefined,
            tipo: tipo.value || undefined,
            per_page: perPage.value,
        },
        { preserveState: true, replace: true }
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

const toggleActivo = (item) => {
    router.patch(route('admin.comunicados.toggle-activo', item.id), {}, {
        preserveScroll: true,
    });
};

const openDeleteModal = (item) => {
    itemToDelete.value = item;
    confirmingDeletion.value = true;
};

const deleteItem = () => {
    if (itemToDelete.value) {
        router.delete(route('admin.comunicados.destroy', itemToDelete.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                confirmingDeletion.value = false;
                itemToDelete.value = null;
            },
        });
    }
};

const formatDate = (dateString) => {
    if (!dateString) return 'Indefinido';
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('es-ES', { year: 'numeric', month: 'short', day: 'numeric' });
    } catch {
        return dateString;
    }
};

const getTipoBadge = (t) => {
    switch (t) {
        case 'curso':
            return { text: 'Curso / Taller', class: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' };
        case 'articulo':
            return { text: 'Artículo Científico', class: 'bg-indigo-50 text-[#35449F] ring-indigo-600/20' };
        case 'convocatoria':
            return { text: 'Convocatoria', class: 'bg-amber-50 text-amber-700 ring-amber-600/20' };
        case 'noticia':
            return { text: 'Noticia Institucional', class: 'bg-sky-50 text-sky-700 ring-sky-600/20' };
        default:
            return { text: 'Aviso General', class: 'bg-gray-100 text-gray-700 ring-gray-600/20' };
    }
};
</script>

<template>
    <AppLayout title="Comunicados & Novedades Emergentes">
        <template #header>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 w-full">
                <div>
                    <h1 class="font-extrabold text-2xl text-[#14161C] tracking-tight">
                        Comunicados & Popups de Novedades
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500 font-medium">
                        Gestión de avisos emergentes (modales) para la web pública: cursos, artículos, convocatorias y eventos.
                    </p>
                </div>
                <Link :href="route('admin.comunicados.create')">
                    <PrimaryButton class="cursor-pointer">
                        <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Nuevo Comunicado
                    </PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="space-y-6 pb-12">
            <!-- KPIs / Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Creados</span>
                        <p class="text-2xl font-black text-slate-800 mt-1">{{ stats.total }}</p>
                    </div>
                    <div class="p-3 bg-slate-50 text-slate-600 rounded-xl">
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Popups Activos</span>
                        <p class="text-2xl font-black text-emerald-700 mt-1">{{ stats.activos }}</p>
                    </div>
                    <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-amber-500 uppercase tracking-wider">Pausados / Inactivos</span>
                        <p class="text-2xl font-black text-slate-700 mt-1">{{ stats.pausados }}</p>
                    </div>
                    <div class="p-3 bg-amber-50 text-amber-600 rounded-xl">
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Barra de Filtros y Búsqueda -->
            <div class="bg-white rounded-2xl p-4 shadow-xs border border-gray-100 flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-3">
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
                        placeholder="Buscar por título, descripción o enlace..."
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

                <div class="flex items-center gap-2 flex-wrap">
                    <!-- Filtro Tipo -->
                    <select
                        v-model="tipo"
                        @change="applyFilters"
                        class="text-sm font-semibold rounded-xl border-gray-200 py-2 pl-3 pr-8 bg-gray-50 focus:bg-white focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10"
                    >
                        <option value="">Todos los tipos</option>
                        <option value="general">General / Aviso</option>
                        <option value="curso">Curso / Taller</option>
                        <option value="articulo">Artículo Científico</option>
                        <option value="convocatoria">Convocatoria</option>
                        <option value="noticia">Noticia</option>
                    </select>

                    <!-- Filtro Estado -->
                    <select
                        v-model="activo"
                        @change="applyFilters"
                        class="text-sm font-semibold rounded-xl border-gray-200 py-2 pl-3 pr-8 bg-gray-50 focus:bg-white focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10"
                    >
                        <option value="">Todos los estados</option>
                        <option value="true">Activos</option>
                        <option value="false">Pausados</option>
                    </select>

                    <!-- Paginación -->
                    <select
                        v-model="perPage"
                        @change="applyFilters"
                        class="text-sm font-semibold rounded-xl border-gray-200 py-2 pl-3 pr-8 bg-gray-50 focus:bg-white focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10"
                    >
                        <option :value="10">10 por pág.</option>
                        <option :value="25">25 por pág.</option>
                        <option :value="50">50 por pág.</option>
                    </select>
                </div>
            </div>

            <!-- Tabla de Registros -->
            <div class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50/75 text-xs font-extrabold uppercase text-gray-500 tracking-wider">
                                <th class="py-3.5 px-6 w-16 text-center">#</th>
                                <th class="py-3.5 px-6">Flyer</th>
                                <th class="py-3.5 px-6">Título & Novedad</th>
                                <th class="py-3.5 px-6">Tipo</th>
                                <th class="py-3.5 px-6">Vigencia</th>
                                <th class="py-3.5 px-6 text-center">Impresiones</th>
                                <th class="py-3.5 px-6 text-center">Estado</th>
                                <th class="py-3.5 px-6 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="comunicados.data.length === 0">
                                <td colspan="8" class="py-12 text-center text-gray-400">
                                    <p class="font-bold text-[#14161C]">No hay comunicados registrados</p>
                                    <p class="text-xs text-gray-400 mt-1">Crea un nuevo aviso para el modal de novedades del sitio web.</p>
                                </td>
                            </tr>
                            <tr
                                v-for="(item, index) in comunicados.data"
                                :key="item.id"
                                class="hover:bg-gray-50/70 transition"
                            >
                                <td class="py-3.5 px-6 text-center font-mono text-xs font-bold text-gray-500 whitespace-nowrap">
                                    {{ (comunicados.from ? comunicados.from + index : index + 1) }}
                                </td>
                                <!-- Flyer Thumbnail -->
                                <td class="py-3.5 px-6">
                                    <div class="size-14 rounded-xl overflow-hidden bg-gray-100 border border-gray-200/80 shrink-0 flex items-center justify-center">
                                        <img
                                            v-if="item.flyer_url"
                                            :src="item.flyer_url"
                                            :alt="item.com_titulo"
                                            class="size-full object-cover"
                                        />
                                        <svg v-else class="size-6 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                </td>

                                <!-- Título & Enlace -->
                                <td class="py-3.5 px-6 max-w-xs">
                                    <Link
                                        :href="route('admin.comunicados.show', item.id)"
                                        class="font-bold text-[#14161C] hover:text-[#35449F] transition line-clamp-1 block text-[14.5px]"
                                    >
                                        {{ item.com_titulo }}
                                    </Link>
                                    <div v-if="item.com_enlace_url" class="text-xs text-indigo-600 truncate mt-0.5 flex items-center gap-1 font-mono">
                                        <svg class="size-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                        <span>{{ item.com_enlace_url }}</span>
                                    </div>
                                    <span v-else class="text-[11px] text-gray-400 italic">Sin enlace de acción</span>
                                </td>

                                <!-- Tipo Badge -->
                                <td class="py-3.5 px-6">
                                    <span
                                        :class="getTipoBadge(item.com_tipo).class"
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold ring-1"
                                    >
                                        {{ getTipoBadge(item.com_tipo).text }}
                                    </span>
                                </td>

                                <!-- Vigencia -->
                                <td class="py-3.5 px-6 text-xs text-gray-600">
                                    <div class="font-semibold text-gray-800">
                                        Desde: {{ formatDate(item.com_fecha_inicio) }}
                                    </div>
                                    <div class="text-gray-400 mt-0.5">
                                        Hasta: {{ formatDate(item.com_fecha_fin) }}
                                    </div>
                                </td>

                                <!-- Impresiones -->
                                <td class="py-3.5 px-6 text-center font-mono text-xs font-bold text-gray-700">
                                    👁 {{ item.com_vistas || 0 }}
                                </td>

                                <!-- Switch Estado -->
                                <td class="py-3.5 px-6 text-center">
                                    <button
                                        @click="toggleActivo(item)"
                                        type="button"
                                        :class="item.com_activo ? 'bg-[#35449F]' : 'bg-gray-200'"
                                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-hidden"
                                        :title="item.com_activo ? 'Clic para pausar' : 'Clic para activar'"
                                    >
                                        <span
                                            :class="item.com_activo ? 'translate-x-5' : 'translate-x-0'"
                                            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out"
                                        />
                                    </button>
                                </td>

                                <!-- Acciones -->
                                <td class="py-3.5 px-6 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <Link
                                            :href="route('admin.comunicados.show', item.id)"
                                            class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-xl transition cursor-pointer"
                                            title="Ver Detalle"
                                        >
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </Link>
                                        <Link
                                            :href="route('admin.comunicados.edit', item.id)"
                                            class="p-2 text-gray-400 hover:text-[#35449F] hover:bg-slate-100 rounded-xl transition cursor-pointer"
                                            title="Editar"
                                        >
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </Link>
                                        <button
                                            @click="openDeleteModal(item)"
                                            type="button"
                                            class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition cursor-pointer"
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

                <!-- Paginación -->
                <div v-if="comunicados.links && comunicados.links.length > 3" class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
                    <p class="text-xs text-gray-500 font-medium">
                        Mostrando del {{ comunicados.from || 0 }} al {{ comunicados.to || 0 }} de {{ comunicados.total }} resultados
                    </p>
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="(link, i) in comunicados.links"
                            :key="i"
                            :href="link.url || '#'"
                            :class="[
                                link.active ? 'bg-[#35449F] text-white font-bold' : 'text-gray-600 hover:bg-gray-100',
                                !link.url ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer'
                            ]"
                            class="px-3 py-1.5 rounded-lg text-xs transition"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Confirmación Eliminación -->
        <ConfirmationModal :show="confirmingDeletion" @close="confirmingDeletion = false">
            <template #title>Eliminar Comunicado</template>
            <template #content>
                ¿Estás seguro de que deseas eliminar permanentemente el comunicado <strong>{{ itemToDelete?.com_titulo }}</strong>?
            </template>
            <template #footer>
                <SecondaryButton @click="confirmingDeletion = false" class="mr-2 cursor-pointer">Cancelar</SecondaryButton>
                <DangerButton @click="deleteItem" class="cursor-pointer">Eliminar</DangerButton>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>
