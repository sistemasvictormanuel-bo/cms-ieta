<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    ediciones: {
        type: Object,
        required: true,
    },
    anios: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({ search: '', anio: null, per_page: 10 }),
    },
    stats: {
        type: Object,
        default: () => ({ total: 0, publicadas: 0, borradores: 0 }),
    },
});

const search = ref(props.filters.search || '');
const selectedAnio = ref(props.filters.anio || '');
const perPage = ref(props.filters.per_page || 10);
let searchTimeout = null;

const confirmingDeletion = ref(false);
const itemToDelete = ref(null);

const applyFilters = () => {
    router.get(
        route('admin.ediciones-varianza.index'),
        {
            search: search.value || undefined,
            anio: selectedAnio.value || undefined,
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

const onAnioChange = () => {
    applyFilters();
};

const clearFilters = () => {
    search.value = '';
    selectedAnio.value = '';
    applyFilters();
};

const openDeleteModal = (edicion) => {
    itemToDelete.value = edicion;
    confirmingDeletion.value = true;
};

const deleteItem = () => {
    if (!itemToDelete.value) return;
    router.delete(route('admin.ediciones-varianza.destroy', itemToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            confirmingDeletion.value = false;
            itemToDelete.value = null;
        },
    });
};
</script>

<template>
    <AppLayout title="Ediciones - Revista Varianza">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 w-full min-w-0">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-1.5 text-[11px] font-semibold text-slate-400 mb-0.5 leading-none">
                        <span>Revista Varianza</span>
                        <span>/</span>
                        <span class="text-slate-700 font-bold">Ediciones</span>
                    </div>
                    <h1 class="font-extrabold text-xl sm:text-2xl text-[#14161C] tracking-tight truncate leading-tight">
                        Ediciones y Números Publicados
                    </h1>
                </div>

                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    <Link :href="route('admin.revista-varianza.edit')">
                        <SecondaryButton class="cursor-pointer gap-1.5">
                            <svg class="size-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>Perfil Revista</span>
                        </SecondaryButton>
                    </Link>
                    <Link :href="route('admin.articulos.create')">
                        <SecondaryButton class="cursor-pointer gap-1.5">
                            <svg class="size-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>Subir Artículo</span>
                        </SecondaryButton>
                    </Link>
                    <Link :href="route('admin.ediciones-varianza.create')">
                        <PrimaryButton class="cursor-pointer gap-1.5">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            <span>Nueva Edición</span>
                        </PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Tarjetas de Estadísticas Principales -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex items-center gap-4">
                    <div class="size-12 rounded-xl bg-blue-50 text-[#35449F] flex items-center justify-center shrink-0">
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Ediciones</p>
                        <p class="text-2xl font-black text-slate-900">{{ stats.total || 0 }}</p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex items-center gap-4">
                    <div class="size-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Ediciones Publicadas</p>
                        <p class="text-2xl font-black text-emerald-700">{{ stats.publicadas || 0 }}</p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex items-center gap-4">
                    <div class="size-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">En Borrador</p>
                        <p class="text-2xl font-black text-amber-700">{{ stats.borradores || 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Barra de Filtros y Búsqueda -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
                <div class="flex-1 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                        <input
                            v-model="search"
                            @input="onSearchInput"
                            type="text"
                            placeholder="Buscar por título, número, volumen, DOI, ISSN..."
                            class="w-full pl-10 pr-4 py-2 bg-slate-50/70 border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-[#35449F]/20 focus:border-[#35449F] transition"
                        />
                    </div>

                    <div class="sm:w-48">
                        <select
                            v-model="selectedAnio"
                            @change="onAnioChange"
                            class="w-full py-2 bg-slate-50/70 border border-slate-200 rounded-xl text-sm text-slate-900 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-[#35449F]/20 focus:border-[#35449F] transition"
                        >
                            <option value="">Todos los años</option>
                            <option v-for="a in anios" :key="a" :value="a">Año {{ a }}</option>
                        </select>
                    </div>

                    <button
                        v-if="search || selectedAnio"
                        @click="clearFilters"
                        class="text-xs text-slate-500 hover:text-slate-800 underline font-semibold cursor-pointer"
                    >
                        Limpiar filtros
                    </button>
                </div>

                <div class="flex items-center gap-2 self-end md:self-auto text-xs text-slate-500 font-semibold">
                    <span>Mostrar:</span>
                    <select
                        v-model="perPage"
                        @change="applyFilters"
                        class="py-1 px-2 bg-slate-50 border border-slate-200 rounded-lg text-xs"
                    >
                        <option :value="10">10</option>
                        <option :value="20">20</option>
                        <option :value="50">50</option>
                    </select>
                </div>
            </div>

            <!-- Listado de Ediciones -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-black uppercase tracking-wider text-slate-500">
                                <th class="py-3.5 px-4 w-12 text-center">#</th>
                                <th class="py-3.5 px-4 w-14 text-center">Portada</th>
                                <th class="py-3.5 px-4">Edición / Título</th>
                                <th class="py-3.5 px-4">Número & Periodo</th>
                                <th class="py-3.5 px-4 text-center">Artículos Vinculados</th>
                                <th class="py-3.5 px-4">PDF / Archivo</th>
                                <th class="py-3.5 px-4 text-center">Estado</th>
                                <th class="py-3.5 px-4 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="(edicion, index) in ediciones.data"
                                :key="edicion.id"
                                class="hover:bg-slate-50/60 transition group"
                            >
                                <!-- Número correlativo -->
                                <td class="py-4 px-4 text-center font-mono text-xs font-bold text-slate-400 whitespace-nowrap">
                                    {{ (ediciones.from ? ediciones.from + index : index + 1) }}
                                </td>

                                <!-- Portada -->
                                <td class="py-4 px-4 text-center">
                                    <div class="size-12 rounded-lg bg-slate-100 overflow-hidden border border-slate-200 shrink-0 mx-auto">
                                        <img
                                            v-if="edicion.portada_url"
                                            :src="edicion.portada_url"
                                            :alt="edicion.ev_titulo"
                                            class="w-full h-full object-cover"
                                        />
                                        <div v-else class="w-full h-full flex items-center justify-center text-slate-400">
                                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                            </svg>
                                        </div>
                                    </div>
                                </td>

                                <!-- Título -->
                                <td class="py-4 px-4 font-bold text-slate-900">
                                    <Link
                                        :href="route('admin.ediciones-varianza.show', edicion.id)"
                                        class="hover:text-[#35449F] transition block"
                                    >
                                        {{ edicion.ev_titulo }}
                                    </Link>
                                    <div class="flex items-center gap-2 mt-1 text-[11px] font-medium text-slate-500">
                                        <span v-if="edicion.ev_doi" class="text-indigo-600 font-mono">
                                            DOI: {{ edicion.ev_doi }}
                                        </span>
                                        <span v-if="edicion.ev_doi && edicion.ev_issn_ver_impresa">•</span>
                                        <span v-if="edicion.ev_issn_ver_impresa">
                                            ISSN: {{ edicion.ev_issn_ver_impresa }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Número & Periodo -->
                                <td class="py-4 px-4 text-xs text-slate-700">
                                    <div class="font-extrabold text-slate-900">
                                        Nº {{ edicion.ev_numero }}
                                        <span v-if="edicion.ev_volumen" class="text-slate-500 font-normal">({{ edicion.ev_volumen }})</span>
                                    </div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">
                                        {{ edicion.ev_mes ? edicion.ev_mes + ' ' : '' }}{{ edicion.ev_anio }}
                                    </div>
                                </td>

                                <!-- Artículos Vinculados -->
                                <td class="py-4 px-4 text-center">
                                    <div class="inline-flex flex-col items-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-[#35449F]">
                                            {{ edicion.articulos_count || 0 }} artículos
                                        </span>
                                        <div class="flex items-center gap-1.5 mt-1 text-[10px] text-slate-500 font-medium">
                                            <span title="Artículos Originales">
                                                🔬 {{ edicion.articulos_originales_count || edicion.ev_num_art_originales || 0 }} Orig.
                                            </span>
                                            <span>•</span>
                                            <span title="Notas Científicas">
                                                📝 {{ edicion.notas_cientificas_count || edicion.ev_notas_cientificas || 0 }} Notas
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- PDF / Archivo -->
                                <td class="py-4 px-4 text-xs">
                                    <a
                                        v-if="edicion.archivo_url"
                                        :href="edicion.archivo_url"
                                        target="_blank"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-red-50 text-red-700 font-bold hover:bg-red-100 transition border border-red-200"
                                    >
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        PDF Completo
                                    </a>
                                    <span v-else class="text-slate-400 text-xs italic">
                                        Sin PDF
                                    </span>
                                </td>

                                <!-- Estado -->
                                <td class="py-4 px-4 text-center">
                                    <span
                                        v-if="edicion.ev_estado"
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"
                                    >
                                        ● Publicado
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200"
                                    >
                                        ○ Borrador
                                    </span>
                                </td>

                                <!-- Acciones -->
                                <td class="py-4 px-4 text-right space-x-1">
                                    <Link
                                        :href="route('admin.ediciones-varianza.show', edicion.id)"
                                        class="p-1.5 text-slate-500 hover:text-blue-600 hover:bg-slate-100 rounded-lg inline-flex items-center transition"
                                        title="Ver detalle y artículos"
                                    >
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </Link>
                                    <Link
                                        :href="route('admin.ediciones-varianza.edit', edicion.id)"
                                        class="p-1.5 text-slate-500 hover:text-[#35449F] hover:bg-slate-100 rounded-lg inline-flex items-center transition"
                                        title="Editar edición"
                                    >
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </Link>
                                    <button
                                        @click="openDeleteModal(edicion)"
                                        class="p-1.5 text-slate-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition cursor-pointer"
                                        title="Eliminar edición"
                                    >
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>

                            <tr v-if="ediciones.data.length === 0">
                                <td colspan="8" class="text-center py-16 text-slate-400">
                                    <svg class="size-12 mx-auto text-slate-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                    <p class="font-bold text-slate-700 text-base">No hay ediciones registradas</p>
                                    <p class="text-xs text-slate-400 mt-1">Crea la primera edición de la Revista Varianza usando el botón "Nueva Edición".</p>
                                    <div class="mt-4">
                                        <Link :href="route('admin.ediciones-varianza.create')">
                                            <PrimaryButton>
                                                + Crear Primera Edición
                                            </PrimaryButton>
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div v-if="ediciones.links && ediciones.links.length > 3" class="p-4 border-t border-slate-100 bg-slate-50/50">
                    <Pagination :links="ediciones.links" />
                </div>
            </div>
        </div>

        <!-- Modal Confirmación Eliminación -->
        <ConfirmationModal :show="confirmingDeletion" @close="confirmingDeletion = false">
            <template #title>
                Eliminar Edición de la Revista
            </template>
            <template #content>
                <p class="text-sm text-slate-600">
                    ¿Estás seguro de que deseas eliminar la edición
                    <strong class="text-slate-900">"{{ itemToDelete?.ev_titulo }}"</strong> (Nº {{ itemToDelete?.ev_numero }})?
                </p>
                <p class="text-xs text-amber-700 font-semibold mt-2 bg-amber-50 p-2.5 rounded-lg border border-amber-200">
                    ℹ️ Los artículos asociados a esta edición permanecerán en la base de datos pero se desvincularán de este número.
                </p>
            </template>
            <template #footer>
                <SecondaryButton @click="confirmingDeletion = false">
                    Cancelar
                </SecondaryButton>
                <DangerButton class="ml-3" @click="deleteItem">
                    Sí, Eliminar Edición
                </DangerButton>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>
