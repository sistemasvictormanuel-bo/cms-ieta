<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import Modal from '@/Components/Modal.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';

const props = defineProps({
    tipos: {
        type: Array,
        required: true,
    },
});

const search = ref('');
const showCreateModal = ref(false);
const showEditModal = ref(false);
const confirmingDeletion = ref(false);
const itemToEdit = ref(null);
const itemToDelete = ref(null);

const formCreate = useForm({
    tar_nombre: '',
    tar_descripcion: '',
    tar_estado: true,
});

const formEdit = useForm({
    id: null,
    tar_nombre: '',
    tar_descripcion: '',
    tar_estado: true,
});

const filteredTipos = computed(() => {
    if (!search.value.trim()) return props.tipos;
    const term = search.value.toLowerCase().trim();
    return props.tipos.filter(t =>
        t.tar_nombre.toLowerCase().includes(term) ||
        (t.tar_descripcion && t.tar_descripcion.toLowerCase().includes(term))
    );
});

const openCreateModal = () => {
    formCreate.reset();
    formCreate.clearErrors();
    formCreate.tar_estado = true;
    showCreateModal.value = true;
};

const submitCreate = () => {
    formCreate.post(route('admin.tipo-articulos.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showCreateModal.value = false;
            formCreate.reset();
        },
    });
};

const openEditModal = (tipo) => {
    itemToEdit.value = tipo;
    formEdit.clearErrors();
    formEdit.id = tipo.id;
    formEdit.tar_nombre = tipo.tar_nombre;
    formEdit.tar_descripcion = tipo.tar_descripcion || '';
    formEdit.tar_estado = Boolean(tipo.tar_estado);
    showEditModal.value = true;
};

const submitEdit = () => {
    if (!formEdit.id) return;
    formEdit.put(route('admin.tipo-articulos.update', formEdit.id), {
        preserveScroll: true,
        onSuccess: () => {
            showEditModal.value = false;
            formEdit.reset();
        },
    });
};

const openDeleteModal = (tipo) => {
    itemToDelete.value = tipo;
    confirmingDeletion.value = true;
};

const deleteItem = () => {
    if (!itemToDelete.value) return;
    router.delete(route('admin.tipo-articulos.destroy', itemToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            confirmingDeletion.value = false;
            itemToDelete.value = null;
        },
    });
};
</script>

<template>
    <AppLayout title="Tipos de Artículos Científicos">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 w-full min-w-0">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-1.5 text-[11px] font-semibold text-slate-400 mb-0.5 leading-none">
                        <Link :href="route('admin.ediciones-varianza.index')" class="hover:text-[#35449F]">Revista Varianza</Link>
                        <span>/</span>
                        <span class="text-slate-700 font-bold">Tipología</span>
                    </div>
                    <h1 class="font-extrabold text-xl sm:text-2xl text-[#14161C] tracking-tight truncate leading-tight">
                        Tipos de Artículos Científicos
                    </h1>
                </div>

                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    <Link :href="route('admin.articulos.index')">
                        <SecondaryButton class="cursor-pointer">
                            ← Artículos
                        </SecondaryButton>
                    </Link>
                    <PrimaryButton @click="openCreateModal" class="cursor-pointer gap-1.5">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span>Nuevo Tipo</span>
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="max-w-6xl mx-auto space-y-6">
            <!-- Barra de búsqueda y contadores -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
                <div class="relative flex-1 max-w-md">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Buscar por nombre o descripción..."
                        class="w-full pl-10 pr-4 py-2 bg-slate-50/70 border border-slate-200 rounded-xl text-sm text-slate-900 placeholder-slate-400 focus:bg-white focus:outline-hidden focus:ring-2 focus:ring-[#35449F]/20 focus:border-[#35449F] transition"
                    />
                </div>

                <div class="flex items-center gap-3 text-xs font-semibold text-slate-600">
                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-blue-50 text-[#35449F] border border-blue-100">
                        Total: {{ tipos.length }} tipos registrados
                    </span>
                </div>
            </div>

            <!-- Tabla de Tipos de Artículos -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-black uppercase tracking-wider text-slate-500">
                                <th class="py-3.5 px-4 w-16 text-center">#</th>
                                <th class="py-3.5 px-4">Tipo de Artículo</th>
                                <th class="py-3.5 px-4">Descripción Metodológica</th>
                                <th class="py-3.5 px-4 text-center">Artículos Asociados</th>
                                <th class="py-3.5 px-4 text-center">Estado</th>
                                <th class="py-3.5 px-4 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="(tipo, index) in filteredTipos"
                                :key="tipo.id"
                                class="hover:bg-slate-50/60 transition group"
                            >
                                <td class="py-4 px-4 text-center font-bold text-slate-500 font-mono text-xs">
                                    {{ index + 1 }}
                                </td>
                                <td class="py-4 px-4 font-bold text-slate-900">
                                    <div class="flex items-center gap-2">
                                        <span class="size-2 rounded-full" :class="tipo.tar_estado ? 'bg-emerald-500' : 'bg-slate-300'"></span>
                                        <span>{{ tipo.tar_nombre }}</span>
                                    </div>
                                </td>
                                <td class="py-4 px-4 text-slate-600 text-xs max-w-md">
                                    {{ tipo.tar_descripcion || 'Sin descripción detallada' }}
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold"
                                        :class="tipo.articulos_count > 0 ? 'bg-blue-50 text-[#35449F]' : 'bg-slate-100 text-slate-500'"
                                    >
                                        {{ tipo.articulos_count || 0 }} artículos
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    <span
                                        v-if="tipo.tar_estado"
                                        class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200"
                                    >
                                        Activo
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200"
                                    >
                                        Inactivo
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-right space-x-1">
                                    <button
                                        @click="openEditModal(tipo)"
                                        class="p-1.5 text-slate-500 hover:text-[#35449F] hover:bg-slate-100 rounded-lg transition cursor-pointer"
                                        title="Editar tipo"
                                    >
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>
                                    <button
                                        @click="openDeleteModal(tipo)"
                                        class="p-1.5 text-slate-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition cursor-pointer"
                                        title="Eliminar tipo"
                                    >
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>

                            <tr v-if="filteredTipos.length === 0">
                                <td colspan="6" class="text-center py-12 text-slate-400">
                                    <svg class="size-10 mx-auto text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="font-semibold text-slate-700">No se encontraron tipos de artículos</p>
                                    <p class="text-xs text-slate-400 mt-1">Registra nuevos tipos de publicaciones científicas arriba.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Crear Tipo de Artículo -->
        <Modal :show="showCreateModal" @close="showCreateModal = false" max-width="md">
            <div class="p-6">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                    <div>
                        <h2 class="text-lg font-extrabold text-slate-900 tracking-tight">Nuevo Tipo de Artículo</h2>
                        <p class="text-xs text-slate-500">Define una categoría aprobada por el comité editorial.</p>
                    </div>
                    <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                        ✕
                    </button>
                </div>

                <form @submit.prevent="submitCreate" class="space-y-4">
                    <div>
                        <InputLabel value="Nombre del Tipo *" class="text-xs font-bold text-slate-700 uppercase" />
                        <TextInput
                            v-model="formCreate.tar_nombre"
                            type="text"
                            class="w-full mt-1"
                            placeholder="Ej: Artículo Original, Nota Científica, Ensayo"
                            required
                        />
                        <InputError :message="formCreate.errors.tar_nombre" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel value="Descripción / Normativa Metodológica" class="text-xs font-bold text-slate-700 uppercase" />
                        <textarea
                            v-model="formCreate.tar_descripcion"
                            rows="3"
                            class="w-full mt-1 border-slate-200 rounded-xl text-sm focus:border-[#35449F] focus:ring-[#35449F]/20"
                            placeholder="Indica las características que debe cumplir este tipo de publicación..."
                        ></textarea>
                        <InputError :message="formCreate.errors.tar_descripcion" class="mt-1" />
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <input
                            v-model="formCreate.tar_estado"
                            type="checkbox"
                            id="create_tar_estado"
                            class="rounded border-slate-300 text-[#35449F] focus:ring-[#35449F]"
                        />
                        <label for="create_tar_estado" class="text-xs font-semibold text-slate-700 cursor-pointer">
                            Activo y disponible para asignación en artículos
                        </label>
                    </div>

                    <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
                        <SecondaryButton @click="showCreateModal = false" :disabled="formCreate.processing">
                            Cancelar
                        </SecondaryButton>
                        <PrimaryButton type="submit" :disabled="formCreate.processing">
                            {{ formCreate.processing ? 'Guardando...' : 'Guardar Tipo' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Modal Editar Tipo de Artículo -->
        <Modal :show="showEditModal" @close="showEditModal = false" max-width="md">
            <div class="p-6">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
                    <div>
                        <h2 class="text-lg font-extrabold text-slate-900 tracking-tight">Editar Tipo de Artículo</h2>
                        <p class="text-xs text-slate-500">Actualiza los datos de esta categoría editorial.</p>
                    </div>
                    <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                        ✕
                    </button>
                </div>

                <form @submit.prevent="submitEdit" class="space-y-4">
                    <div>
                        <InputLabel value="Nombre del Tipo *" class="text-xs font-bold text-slate-700 uppercase" />
                        <TextInput
                            v-model="formEdit.tar_nombre"
                            type="text"
                            class="w-full mt-1"
                            required
                        />
                        <InputError :message="formEdit.errors.tar_nombre" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel value="Descripción / Normativa Metodológica" class="text-xs font-bold text-slate-700 uppercase" />
                        <textarea
                            v-model="formEdit.tar_descripcion"
                            rows="3"
                            class="w-full mt-1 border-slate-200 rounded-xl text-sm focus:border-[#35449F] focus:ring-[#35449F]/20"
                        ></textarea>
                        <InputError :message="formEdit.errors.tar_descripcion" class="mt-1" />
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <input
                            v-model="formEdit.tar_estado"
                            type="checkbox"
                            id="edit_tar_estado"
                            class="rounded border-slate-300 text-[#35449F] focus:ring-[#35449F]"
                        />
                        <label for="edit_tar_estado" class="text-xs font-semibold text-slate-700 cursor-pointer">
                            Activo y disponible para asignación en artículos
                        </label>
                    </div>

                    <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
                        <SecondaryButton @click="showEditModal = false" :disabled="formEdit.processing">
                            Cancelar
                        </SecondaryButton>
                        <PrimaryButton type="submit" :disabled="formEdit.processing">
                            {{ formEdit.processing ? 'Actualizando...' : 'Guardar Cambios' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Modal Confirmación Eliminación -->
        <ConfirmationModal :show="confirmingDeletion" @close="confirmingDeletion = false">
            <template #title>
                Eliminar Tipo de Artículo
            </template>
            <template #content>
                <p class="text-sm text-slate-600">
                    ¿Estás seguro de que deseas eliminar el tipo
                    <strong class="text-slate-900">"{{ itemToDelete?.tar_nombre }}"</strong>?
                </p>
                <p v-if="itemToDelete?.articulos_count > 0" class="text-xs text-amber-600 font-bold mt-2 bg-amber-50 p-2.5 rounded-lg border border-amber-200">
                    ⚠️ Advertencia: Este tipo tiene {{ itemToDelete.articulos_count }} artículo(s) asociado(s). Al eliminarlo se desvinculará de ellos.
                </p>
            </template>
            <template #footer>
                <SecondaryButton @click="confirmingDeletion = false">
                    Cancelar
                </SecondaryButton>
                <DangerButton class="ml-3" @click="deleteItem">
                    Sí, Eliminar
                </DangerButton>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>
