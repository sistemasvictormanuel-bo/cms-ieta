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
    lineas: {
        type: [Array, Object],
        default: () => ({ data: [] }),
    },
    filters: {
        type: Object,
        default: () => ({ search: '', per_page: 10 }),
    },
});

// Obtener la lista reactivak
const lineasList = computed(() => {
    if (Array.isArray(props.lineas)) {
        return props.lineas;
    }
    return props.lineas?.data || [];
});

// Búsqueda y filtros
const search = ref(props.filters?.search || '');
const perPage = ref(props.filters?.per_page || 10);
let searchTimeout = null;

const applyFilters = () => {
    router.get(
        route('admin.lineas-investigacion.index'),
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

const onPerPageChange = () => {
    applyFilters();
};

// Eliminación de registro
const confirmingDeletion = ref(false);
const itemToDelete = ref(null);

const confirmDelete = (item) => {
    itemToDelete.value = item;
    confirmingDeletion.value = true;
};

const deleteItem = () => {
    if (!itemToDelete.value) return;

    router.delete(route('admin.lineas-investigacion.destroy', itemToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            confirmingDeletion.value = false;
            itemToDelete.value = null;
        },
    });
};
</script>

<template>
    <AppLayout title="Líneas de Investigación">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 w-full">
                <div>
                    <h1 class="font-extrabold text-2xl text-[#14161C] tracking-tight">
                        Líneas de Investigación
                    </h1>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <Link :href="route('admin.lineas-investigacion.create')">
                        <PrimaryButton>
                            Nueva Línea
                        </PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto space-y-4">
            <div class="bg-white rounded-2xl p-6 md:p-8 shadow-[6px_6px_20px_rgba(0,0,0,0.08),-6px_-6px_20px_rgba(255,255,255,0.9)] border border-gray-100/80">
                <!-- Barra de Búsqueda y Filtros -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 mb-6">
                    <!-- Input de Búsqueda -->
                    <div class="relative flex-1 max-w-md">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input
                            v-model="search"
                            @input="onSearchInput"
                            type="text"
                            placeholder="Buscar por título, slug o resumen..."
                            class="w-full pl-10 pr-9 py-2.5 bg-gray-50/70 border border-gray-200 rounded-xl text-sm focus:bg-white focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 transition-all placeholder-gray-400 text-gray-800"
                        />
                        <!-- Botón limpiar búsqueda si hay texto -->
                        <button
                            v-if="search"
                            @click="clearSearch"
                            type="button"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition"
                            title="Limpiar búsqueda"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>

                    <!-- Selector de registros por página -->
                    <div class="flex items-center gap-3">
                        <label for="per_page" class="text-xs font-semibold text-gray-500 whitespace-nowrap">Mostrar:</label>
                        <select
                            id="per_page"
                            v-model="perPage"
                            @change="onPerPageChange"
                            class="py-2 pl-3 pr-8 text-xs font-semibold bg-gray-50/70 border border-gray-200 rounded-xl text-gray-700 focus:bg-white focus:border-[#35449F] focus:ring-0 transition"
                        >
                            <option :value="10">10 por página</option>
                            <option :value="25">25 por página</option>
                            <option :value="50">50 por página</option>
                        </select>
                    </div>
                </div>

                <!-- Tabla de Líneas de Investigación -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#E0E0E0]">
                        <thead class="bg-[#F5F5F8]">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-center text-xs font-extrabold text-[#14161C] uppercase tracking-wider w-16">
                                    #
                                </th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-extrabold text-[#14161C] uppercase tracking-wider">
                                    Portada
                                </th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-extrabold text-[#14161C] uppercase tracking-wider">
                                    Título
                                </th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-extrabold text-[#14161C] uppercase tracking-wider">
                                    Descripción
                                </th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-extrabold text-[#14161C] uppercase tracking-wider">
                                    Slug
                                </th>
                                <th scope="col" class="px-6 py-4 text-right text-xs font-extrabold text-[#14161C] uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-[#E0E0E0]">
                            <tr v-for="(linea, index) in lineasList" :key="linea.id" class="hover:bg-[#F5F5F8]/70 transition">
                                <td class="px-6 py-4 text-center whitespace-nowrap font-mono text-xs font-bold text-gray-500">
                                    {{ (props.lineas?.from ? props.lineas.from + index : index + 1) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="h-14 w-20 rounded-lg overflow-hidden bg-gray-100 flex items-center justify-center border border-[#E0E0E0]">
                                        <img
                                            v-if="linea.portada_url"
                                            :src="assetUrl(linea.portada_url)"
                                            :alt="linea.li_titulo"
                                            class="h-full w-full object-cover"
                                        />
                                        <span v-else class="text-xs font-bold text-gray-400">Sin foto</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-[15px] font-bold text-[#14161C]">
                                        {{ linea.li_titulo }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm font-medium text-gray-600 max-w-sm line-clamp-2">
                                        {{ linea.li_descripcion }}
                                    </p>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-[#35449F]/10 text-[#35449F]">
                                        {{ linea.li_slug }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <!-- Botón Ver (Show) -->
                                    <Link
                                        :href="route('admin.lineas-investigacion.show', linea.id)"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 hover:bg-[#35449F] hover:text-white text-[#35449F] font-bold rounded-lg text-xs transition duration-150"
                                        title="Ver detalle"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        Ver
                                    </Link>

                                    <!-- Botón Editar -->
                                    <Link
                                        :href="route('admin.lineas-investigacion.edit', linea.id)"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 hover:bg-[#35449F] hover:text-white text-[#14161C] font-bold rounded-lg text-xs transition duration-150"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Editar
                                    </Link>

                                    <!-- Botón Eliminar -->
                                    <button
                                        @click="confirmDelete(linea)"
                                        type="button"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-50 hover:bg-[#F30060] hover:text-white text-[#F30060] font-bold rounded-lg text-xs transition duration-150 cursor-pointer"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Eliminar
                                    </button>
                                </td>
                            </tr>

                            <tr v-if="lineasList.length === 0">
                                <td colspan="6" class="px-6 py-16 text-center text-base font-semibold text-gray-500">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <p>No se encontraron líneas de investigación con los criterios indicados.</p>
                                        <button
                                            v-if="search"
                                            @click="clearSearch"
                                            class="text-xs text-[#35449F] font-bold underline hover:text-[#14161C] mt-1"
                                        >
                                            Limpiar búsqueda
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <Pagination :meta="props.lineas" class="mt-4" />
            </div>
        </div>

        <!-- Modal de confirmación para eliminar -->
        <ConfirmationModal :show="confirmingDeletion" @close="confirmingDeletion = false">
            <template #title>
                Eliminar Línea de Investigación
            </template>

            <template #content>
                ¿Estás seguro de que deseas eliminar la línea "{{ itemToDelete?.li_titulo }}"? Esta acción no se puede deshacer.
            </template>

            <template #footer>
                <SecondaryButton @click="confirmingDeletion = false">
                    Cancelar
                </SecondaryButton>

                <DangerButton class="ms-3" @click="deleteItem">
                    Sí, eliminar
                </DangerButton>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>
