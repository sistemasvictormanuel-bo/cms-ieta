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
    instituciones: {
        type: [Array, Object],
        default: () => ({ data: [] }),
    },
    filters: {
        type: Object,
        default: () => ({ search: '', per_page: 10 }),
    },
});

const list = computed(() => {
    if (Array.isArray(props.instituciones)) {
        return props.instituciones;
    }
    return props.instituciones?.data || [];
});

const search = ref(props.filters?.search || '');
const perPage = ref(props.filters?.per_page || 10);
let searchTimeout = null;

const applyFilters = () => {
    router.get(
        route('admin.instituciones.index'),
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

    router.delete(route('admin.instituciones.destroy', itemToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            confirmingDeletion.value = false;
            itemToDelete.value = null;
        },
    });
};
</script>

<template>
    <AppLayout title="Instituciones de Adscripción">
        <template #header>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 w-full">
                <div>
                    <h1 class="font-extrabold text-2xl text-[#14161C] tracking-tight">
                        Instituciones de Adscripción
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500 font-medium">
                        Universidades, centros de investigación y organismos de los miembros del comité editorial.
                    </p>
                </div>
                <Link :href="route('admin.instituciones.create')">
                    <PrimaryButton class="cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Nueva Institución
                    </PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="space-y-6 pb-12">
            <!-- Buscador y Filtros -->
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
                        placeholder="Buscar por institución, país o ciudad..."
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
                                <th scope="col" class="py-4 px-6">Institución</th>
                                <th scope="col" class="py-4 px-6">País</th>
                                <th scope="col" class="py-4 px-6">Ciudad</th>
                                <th scope="col" class="py-4 px-6 text-center">Miembros</th>
                                <th scope="col" class="py-4 px-6 text-right w-36">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="list.length === 0">
                                <td colspan="6" class="py-12 text-center text-gray-400">
                                    <p class="font-bold text-[#14161C]">No hay instituciones registradas</p>
                                </td>
                            </tr>
                            <tr
                                v-for="(item, index) in list"
                                :key="item.id"
                                class="hover:bg-gray-50/70 transition"
                            >
                                <td class="py-4 px-6 font-mono text-xs text-gray-500 text-center font-bold">
                                    {{ (props.instituciones?.from ? props.instituciones.from + index : index + 1) }}
                                </td>
                                <td class="py-4 px-6 font-bold text-[#14161C]">
                                    {{ item.inst_nombre }}
                                </td>
                                <td class="py-4 px-6 text-gray-600">
                                    {{ item.inst_pais }}
                                </td>
                                <td class="py-4 px-6 text-gray-500">
                                    {{ item.inst_ciudad }}
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-[#35449F]">
                                        {{ item.comite_editorial_count ?? 0 }} editores
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <Link
                                            :href="route('admin.instituciones.show', item.id)"
                                            class="p-2 text-gray-400 hover:text-[#35449F] hover:bg-indigo-50 rounded-lg transition"
                                            title="Ver"
                                        >
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.264 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </Link>
                                        <Link
                                            :href="route('admin.instituciones.edit', item.id)"
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

                <div v-if="instituciones?.meta || instituciones?.links" class="p-4 bg-gray-50/50">
                    <Pagination :meta="instituciones" />
                </div>
            </div>
        </div>

        <ConfirmationModal :show="confirmingDeletion" @close="confirmingDeletion = false">
            <template #title>Eliminar Institución</template>
            <template #content>
                ¿Estás seguro de que deseas eliminar la institución
                <span class="font-bold text-[#14161C]">"{{ itemToDelete?.inst_nombre }}"</span>?
            </template>
            <template #footer>
                <SecondaryButton @click="confirmingDeletion = false" class="mr-2 cursor-pointer">Cancelar</SecondaryButton>
                <DangerButton @click="deleteItem" class="cursor-pointer">Eliminar</DangerButton>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>
