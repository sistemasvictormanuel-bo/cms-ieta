<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';

const props = defineProps({
    etiquetas: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({ search: '', per_page: 10 }),
    },
    total: {
        type: Number,
        default: 0,
    },
});

const search = ref(props.filters.search || '');
const perPage = ref(props.filters.per_page || 10);
let searchTimeout = null;

const confirmingDeletion = ref(false);
const itemToDelete = ref(null);

const applyFilters = () => {
    router.get(
        route('admin.etiquetas-art.index'),
        {
            search: search.value || undefined,
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

const openDeleteModal = (item) => {
    itemToDelete.value = item;
    confirmingDeletion.value = true;
};

const deleteItem = () => {
    if (itemToDelete.value) {
        router.delete(route('admin.etiquetas-art.destroy', itemToDelete.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                confirmingDeletion.value = false;
                itemToDelete.value = null;
            },
        });
    }
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
</script>

<template>
    <AppLayout title="Etiquetas de Artículos">
        <template #header>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 w-full">
                <div>
                    <h1 class="font-extrabold text-2xl text-[#14161C] tracking-tight">
                        Etiquetas de Artículos Científicos
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500 font-medium">
                        Vocabulario normalizado y temáticas (N:M) para indexación y cálculo de artículos similares.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Link :href="route('admin.articulos.index')">
                        <SecondaryButton class="cursor-pointer">
                            ← Volver a Artículos
                        </SecondaryButton>
                    </Link>
                    <Link :href="route('admin.etiquetas-art.create')">
                        <PrimaryButton class="cursor-pointer">
                            <svg class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Nueva Etiqueta
                        </PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-6 pb-12">
            <!-- Barra de Filtros y Búsqueda -->
            <div class="bg-white rounded-2xl p-4 shadow-xs border border-gray-100 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
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
                        placeholder="Buscar etiquetas por nombre o slug..."
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
                    <span class="text-xs font-semibold text-gray-500">Total: {{ total }}</span>
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
                                <th class="py-3.5 px-6">Nombre de la Etiqueta</th>
                                <th class="py-3.5 px-6">Slug (URL)</th>
                                <th class="py-3.5 px-6 text-center">Artículos Vinculados</th>
                                <th class="py-3.5 px-6">Fecha Registro</th>
                                <th class="py-3.5 px-6 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="etiquetas.data.length === 0">
                                <td colspan="6" class="py-12 text-center text-gray-400">
                                    <p class="font-bold text-[#14161C]">No hay etiquetas registradas</p>
                                    <p class="text-xs text-gray-400 mt-1">Crea nuevas etiquetas temáticas para clasificar artículos.</p>
                                </td>
                            </tr>
                            <tr
                                v-for="(item, index) in etiquetas.data"
                                :key="item.id"
                                class="hover:bg-gray-50/70 transition"
                            >
                                <td class="py-3.5 px-6 font-mono text-xs text-gray-500 text-center font-bold">
                                    {{ (etiquetas.from ? etiquetas.from + index : index + 1) }}
                                </td>

                                <td class="py-3.5 px-6">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-sm font-bold bg-indigo-50 text-[#35449F] border border-indigo-100">
                                        <svg class="size-3.5 text-[#35449F]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                        </svg>
                                        {{ item.ea_nombre }}
                                    </span>
                                </td>

                                <td class="py-3.5 px-6">
                                    <span class="font-mono text-xs text-purple-700 bg-purple-50 px-2 py-0.5 rounded-md border border-purple-100">
                                        /{{ item.ea_slug }}
                                    </span>
                                </td>

                                <td class="py-3.5 px-6 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-700">
                                        {{ item.articulos_count || 0 }} artículos
                                    </span>
                                </td>

                                <td class="py-3.5 px-6 text-xs text-gray-500 font-medium">
                                    {{ formatDate(item.created_at) }}
                                </td>

                                <td class="py-3.5 px-6 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <Link
                                            :href="route('admin.etiquetas-art.edit', item.id)"
                                            class="p-2 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-xl transition cursor-pointer"
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
                <div v-if="etiquetas.links && etiquetas.links.length > 3" class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
                    <p class="text-xs text-gray-500 font-medium">
                        Mostrando del {{ etiquetas.from || 0 }} al {{ etiquetas.to || 0 }} de {{ etiquetas.total }} resultados
                    </p>
                    <div class="flex items-center gap-1">
                        <Link
                            v-for="(link, i) in etiquetas.links"
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
            <template #title>Eliminar Etiqueta</template>
            <template #content>
                ¿Estás seguro de que deseas eliminar permanentemente la etiqueta <strong>"{{ itemToDelete?.ea_nombre }}"</strong>?
                Se desvinculará automáticamente de todos los artículos asociados.
            </template>
            <template #footer>
                <SecondaryButton @click="confirmingDeletion = false" class="mr-2 cursor-pointer">Cancelar</SecondaryButton>
                <DangerButton @click="deleteItem" class="cursor-pointer">Eliminar</DangerButton>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>
