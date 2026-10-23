<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    instrucciones: {
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
const confirmingDeletion = ref(false);
const itemToDelete = ref(null);

let searchTimeout = null;
watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(
            route('admin.instrucciones-autores.index'),
            { search: value },
            { preserveState: true, replace: true }
        );
    }, 350);
});

const openDeleteModal = (item) => {
    itemToDelete.value = item;
    confirmingDeletion.value = true;
};

const deleteItem = () => {
    if (!itemToDelete.value) return;
    router.delete(route('admin.instrucciones-autores.destroy', itemToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            confirmingDeletion.value = false;
            itemToDelete.value = null;
        },
    });
};

const toggleActivo = (item) => {
    router.patch(route('admin.instrucciones-autores.toggle-activo', item.id), {}, {
        preserveScroll: true,
    });
};

const getSnippet = (contenido) => {
    if (!contenido || !contenido.blocks || !contenido.blocks.length) {
        return 'Sin contenido redactado';
    }
    const firstText = contenido.blocks.find(b => b.data && (b.data.text || b.data.items));
    if (!firstText) return `${contenido.blocks.length} bloque(s) de contenido`;

    if (firstText.data.text) {
        const clean = firstText.data.text.replace(/<[^>]*>?/gm, '');
        return clean.length > 90 ? clean.substring(0, 90) + '...' : clean;
    }

    if (Array.isArray(firstText.data.items) && firstText.data.items.length) {
        return `Lista con ${firstText.data.items.length} elemento(s)`;
    }

    return `${contenido.blocks.length} bloque(s)`;
};
</script>

<template>
    <AppLayout title="Instrucciones para Autores">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 w-full">
                <div>
                    <div class="flex items-center gap-1.5 text-xs font-semibold text-slate-400 mb-1">
                        <Link :href="route('dashboard')" class="hover:text-[#35449F]">Inicio</Link>
                        <span>/</span>
                        <span>Revista Varianza</span>
                        <span>/</span>
                        <span class="text-slate-700 font-bold">Instrucciones Autores</span>
                    </div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                        <span>Directrices e Instrucciones para Autores</span>
                        <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-blue-50 text-[#35449F] border border-blue-200">
                            {{ total }} secciones
                        </span>
                    </h1>
                    <p class="text-[14px] text-slate-600 font-medium mt-0.5">
                        Estructura, normas de publicación y directrices técnicas visibles en el portal web de la Revista Varianza.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Link :href="route('admin.instrucciones-autores.create')">
                        <PrimaryButton class="cursor-pointer gap-2 font-bold text-sm px-4 py-2.5 shadow-sm">
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            <span>Nueva Sección</span>
                        </PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-6 pb-12">
            <!-- Barra de Filtros y Búsqueda -->
            <div class="bg-white rounded-2xl p-4 shadow-2xs border border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="relative w-full sm:w-96">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Buscar por título de sección..."
                        class="w-full pl-10 pr-4 py-2 text-sm bg-gray-50/50 border border-gray-200 rounded-xl focus:bg-white focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 font-medium transition"
                    />
                </div>

                <div class="text-xs font-semibold text-gray-500">
                    Secciones ordenadas por secuencia web (<code class="bg-gray-100 px-1 py-0.5 rounded text-gray-700">ia_orden</code>)
                </div>
            </div>

            <!-- Tabla de Secciones -->
            <div class="bg-white rounded-2xl shadow-2xs border border-gray-200/80 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50/80 text-gray-600 font-extrabold uppercase text-xs tracking-wider border-b border-gray-100">
                            <tr>
                                <th scope="col" class="py-4 px-6 w-20 text-center">Orden</th>
                                <th scope="col" class="py-4 px-6">Título de la Sección</th>
                                <th scope="col" class="py-4 px-6">Extracto de Contenido</th>
                                <th scope="col" class="py-4 px-6 text-center w-36">Estado Web</th>
                                <th scope="col" class="py-4 px-6 text-right w-40">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="!instrucciones.data || instrucciones.data.length === 0">
                                <td colspan="5" class="py-12 text-center text-gray-400">
                                    <div class="size-14 mx-auto mb-3 rounded-full bg-gray-50 flex items-center justify-center text-gray-300">
                                        <svg class="size-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <p class="font-bold text-[#14161C] text-base">No hay instrucciones registradas</p>
                                    <p class="text-xs text-gray-500 mt-1">Crea la primera sección o directriz de envío para autores.</p>
                                    <div class="mt-4">
                                        <Link :href="route('admin.instrucciones-autores.create')">
                                            <PrimaryButton class="text-xs font-bold cursor-pointer">
                                                Registrar Sección
                                            </PrimaryButton>
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                            <tr
                                v-for="item in instrucciones.data"
                                :key="item.id"
                                class="hover:bg-gray-50/60 transition group"
                            >
                                <td class="py-4 px-6 text-center">
                                    <span class="inline-flex items-center justify-center size-8 rounded-lg bg-indigo-50 text-[#35449F] font-black text-xs border border-indigo-100 shadow-2xs">
                                        #{{ item.ia_orden }}
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="font-bold text-gray-900 text-sm">
                                        {{ item.ia_titulo_seccion }}
                                    </div>
                                    <div class="text-[11px] text-gray-400 font-mono mt-0.5">
                                        {{ item.ia_contenido?.blocks?.length || 0 }} bloque(s) de contenido
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <p class="text-xs text-gray-600 line-clamp-2 max-w-md font-medium leading-relaxed">
                                        {{ getSnippet(item.ia_contenido) }}
                                    </p>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <button
                                        type="button"
                                        @click="toggleActivo(item)"
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold transition cursor-pointer"
                                        :class="item.ia_activo
                                            ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 ring-1 ring-emerald-600/20'
                                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200 ring-1 ring-gray-400/20'"
                                        :title="item.ia_activo ? 'Clic para ocultar de la web' : 'Clic para mostrar en la web'"
                                    >
                                        <span class="size-1.5 rounded-full" :class="item.ia_activo ? 'bg-emerald-500' : 'bg-gray-400'"></span>
                                        <span>{{ item.ia_activo ? 'Visible' : 'Oculto' }}</span>
                                    </button>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <Link
                                            :href="route('admin.instrucciones-autores.show', item.id)"
                                            class="p-2 text-slate-500 hover:text-[#35449F] hover:bg-indigo-50 rounded-lg transition"
                                            title="Ver sección completa"
                                        >
                                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </Link>
                                        <Link
                                            :href="route('admin.instrucciones-autores.edit', item.id)"
                                            class="p-2 text-slate-500 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition"
                                            title="Editar sección"
                                        >
                                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </Link>
                                        <button
                                            type="button"
                                            @click="openDeleteModal(item)"
                                            class="p-2 text-slate-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition cursor-pointer"
                                            title="Eliminar sección"
                                        >
                                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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
                <div v-if="instrucciones.meta || instrucciones.links" class="p-4 border-t border-gray-100">
                    <Pagination :meta="instrucciones" />
                </div>
            </div>
        </div>

        <!-- Modal Confirmación Eliminación -->
        <ConfirmationModal :show="confirmingDeletion" @close="confirmingDeletion = false">
            <template #title>
                Eliminar Instrucción para Autores
            </template>
            <template #content>
                ¿Estás seguro de que deseas eliminar la sección <strong>"{{ itemToDelete?.ia_titulo_seccion }}"</strong>? Esta acción ocultará la sección del portal de la revista.
            </template>
            <template #footer>
                <SecondaryButton @click="confirmingDeletion = false" class="cursor-pointer">
                    Cancelar
                </SecondaryButton>
                <DangerButton @click="deleteItem" class="ml-3 cursor-pointer">
                    Eliminar
                </DangerButton>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>
