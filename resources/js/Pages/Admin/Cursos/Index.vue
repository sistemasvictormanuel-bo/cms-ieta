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
    cursos: {
        type: [Array, Object],
        default: () => ({ data: [] }),
    },
    filters: {
        type: Object,
        default: () => ({ search: '', per_page: 10 }),
    },
});

const list = computed(() => {
    if (Array.isArray(props.cursos)) {
        return props.cursos;
    }
    return props.cursos?.data || [];
});

const search = ref(props.filters?.search || '');
const perPage = ref(props.filters?.per_page || 10);
let searchTimeout = null;

const applyFilters = () => {
    router.get(
        route('admin.cursos.index'),
        {
            search: search.value ? search.value : undefined,
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

    router.delete(route('admin.cursos.destroy', itemToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            confirmingDeletion.value = false;
            itemToDelete.value = null;
        },
    });
};

const formatCurrency = (val) => {
    if (val === null || val === undefined || Number(val) === 0) return 'Gratuito';
    return `Bs. ${Number(val).toFixed(2)}`;
};

const formatDate = (val) => {
    if (!val) return 'Por definir';
    return new Date(val).toLocaleDateString('es-ES', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
};

const getDocenteName = (docente) => {
    if (!docente) return 'Sin docente asignado';
    if (docente.nombre_completo) return docente.nombre_completo;
    return `${docente.p_nombre} ${docente.p_apellido_paterno || ''}`.trim();
};
</script>

<template>
    <AppLayout title="Gestión de Cursos">
        <template #header>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 w-full">
                <div>
                    <h1 class="font-extrabold text-2xl text-[#14161C] tracking-tight">
                        Catálogo de Cursos
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500 font-medium">
                        Administración integral de diplomados, talleres, seminarios y programas de capacitación.
                    </p>
                </div>
                <Link :href="route('admin.cursos.create')">
                    <PrimaryButton class="cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Nuevo Curso
                    </PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="space-y-6 pb-12">
            <!-- Barra de Búsqueda y Filtros -->
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
                        placeholder="Buscar por título, código o slug..."
                        class="w-full pl-10 pr-10 py-2.5 bg-gray-50/50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 transition"
                    />
                    <button
                        v-if="search"
                        @click="clearSearch"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 cursor-pointer"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
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

            <!-- Tabla de Cursos -->
            <div class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-600 font-extrabold uppercase text-xs tracking-wider border-b border-gray-100">
                            <tr>
                                <th scope="col" class="py-4 px-6 w-16 text-center">#</th>
                                <th scope="col" class="py-4 px-6">Curso / Portada</th>
                                <th scope="col" class="py-4 px-6">Tipo / Modalidad</th>
                                <th scope="col" class="py-4 px-6">Docente</th>
                                <th scope="col" class="py-4 px-6">Periodo & Costo</th>
                                <th scope="col" class="py-4 px-6 text-center">Estado</th>
                                <th scope="col" class="py-4 px-6 text-right w-36">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="list.length === 0">
                                <td colspan="7" class="py-12 text-center text-gray-400">
                                    <div class="max-w-xs mx-auto space-y-2">
                                        <svg class="size-10 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l9-5-9-5-9 5 9 5z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                        </svg>
                                        <p class="font-bold text-[#14161C]">No hay cursos registrados</p>
                                        <p class="text-xs text-gray-500">Crea el primer curso usando el botón superior.</p>
                                    </div>
                                </td>
                            </tr>
                            <tr
                                v-for="(item, index) in list"
                                :key="item.id"
                                class="hover:bg-gray-50/70 transition"
                            >
                                <td class="py-4 px-6 text-center font-mono text-xs font-bold text-gray-500 whitespace-nowrap">
                                    {{ (props.cursos?.from ? props.cursos.from + index : index + 1) }}
                                </td>
                                <!-- Portada & Título -->
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3.5">
                                        <img
                                            v-if="item.portada_url"
                                            :src="assetUrl(item.portada_url)"
                                            :alt="item.ct_titulo"
                                            class="w-14 h-11 object-cover rounded-xl border border-gray-100 shrink-0 shadow-2xs"
                                        />
                                        <div
                                            v-else
                                            class="w-14 h-11 rounded-xl bg-gradient-to-tr from-[#35449F]/10 to-[#F50088]/10 text-[#35449F] flex items-center justify-center shrink-0 border border-gray-100"
                                        >
                                            <svg class="size-6 opacity-60" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-extrabold text-[#14161C] text-sm line-clamp-1 hover:text-[#35449F] transition">
                                                {{ item.ct_titulo }}
                                            </div>
                                            <div class="text-xs text-gray-400 font-mono">
                                                {{ item.ct_slug }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Tipo & Modalidad -->
                                <td class="py-4 px-6">
                                    <div class="flex flex-col gap-1 items-start">
                                        <span class="px-2 py-0.5 rounded-md text-xs font-bold bg-[#35449F]/10 text-[#35449F]">
                                            {{ item.tipo_curso?.tc_nombre || 'General' }}
                                        </span>
                                        <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-gray-100 text-gray-600">
                                            {{ item.modalidad_curso?.mc_nombre || 'No definida' }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Docente -->
                                <td class="py-4 px-6 text-gray-700">
                                    <div class="flex items-center gap-1.5 text-xs font-semibold">
                                        <svg class="size-3.5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <span class="truncate max-w-[150px]">
                                            {{ getDocenteName(item.docente) }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Periodo & Costo -->
                                <td class="py-4 px-6">
                                    <div class="text-xs text-gray-600 space-y-0.5">
                                        <div>
                                            {{ formatDate(item.ct_fecha_inicio) }}
                                        </div>
                                        <div class="font-bold text-[#14161C]">
                                            {{ formatCurrency(item.ct_costo) }}
                                        </div>
                                    </div>
                                </td>

                                <!-- Estado -->
                                <td class="py-4 px-6 text-center">
                                    <span
                                        :class="[
                                            item.ct_activo
                                                ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20'
                                                : 'bg-gray-100 text-gray-600 ring-1 ring-gray-400/20'
                                        ]"
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold"
                                    >
                                        <span :class="item.ct_activo ? 'bg-emerald-500' : 'bg-gray-400'" class="size-1.5 rounded-full mr-1.5"></span>
                                        {{ item.ct_activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>

                                <!-- Acciones -->
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <Link
                                            :href="route('admin.cursos.show', item.id)"
                                            class="p-2 text-gray-400 hover:text-[#35449F] hover:bg-indigo-50 rounded-lg transition"
                                            title="Ver detalle"
                                        >
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.264 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </Link>

                                        <Link
                                            :href="route('admin.cursos.edit', item.id)"
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

                <!-- Paginación -->
                <div v-if="cursos?.meta || cursos?.links" class="p-4 bg-gray-50/50">
                    <Pagination :meta="cursos" />
                </div>
            </div>
        </div>

        <!-- Modal de Confirmación de Eliminación -->
        <ConfirmationModal :show="confirmingDeletion" @close="confirmingDeletion = false">
            <template #title>
                Eliminar Curso
            </template>
            <template #content>
                ¿Estás seguro de que deseas eliminar el curso
                <span class="font-bold text-[#14161C]">"{{ itemToDelete?.ct_titulo }}"</span>?
                Se eliminarán también los archivos multimedia y contenidos asociados. Esta acción no se puede deshacer.
            </template>
            <template #footer>
                <SecondaryButton @click="confirmingDeletion = false" class="mr-2 cursor-pointer">
                    Cancelar
                </SecondaryButton>
                <DangerButton @click="deleteItem" class="cursor-pointer">
                    Eliminar Curso
                </DangerButton>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>
