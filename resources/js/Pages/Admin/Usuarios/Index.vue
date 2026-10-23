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
    usuarios: {
        type: Object,
        required: true,
    },
    roles: {
        type: Array,
        default: () => ['administrador', 'autor'],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const search = ref(props.filters.search || '');
const roleFilter = ref(props.filters.role || '');
const statusFilter = ref(props.filters.status || '');

const showCreateModal = ref(false);
const showEditModal = ref(false);
const confirmingDeletion = ref(false);
const userToEdit = ref(null);
const userToDelete = ref(null);

const formCreate = useForm({
    name: '',
    email: '',
    password: '',
    role: 'autor',
    is_active: true,
});

const formEdit = useForm({
    id: null,
    name: '',
    email: '',
    password: '',
    role: 'autor',
    is_active: true,
});

const applyFilters = () => {
    router.get(
        route('admin.usuarios.index'),
        {
            search: search.value || undefined,
            role: roleFilter.value || undefined,
            status: statusFilter.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
        }
    );
};

const openCreateModal = () => {
    formCreate.reset();
    formCreate.clearErrors();
    formCreate.role = 'autor';
    formCreate.is_active = true;
    showCreateModal.value = true;
};

const submitCreate = () => {
    formCreate.post(route('admin.usuarios.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showCreateModal.value = false;
            formCreate.reset();
        },
    });
};

const openEditModal = (user) => {
    userToEdit.value = user;
    formEdit.clearErrors();
    formEdit.id = user.id;
    formEdit.name = user.name;
    formEdit.email = user.email;
    formEdit.password = '';
    formEdit.role = user.roles && user.roles.length ? user.roles[0] : 'autor';
    formEdit.is_active = !!user.is_active;
    showEditModal.value = true;
};

const submitEdit = () => {
    formEdit.put(route('admin.usuarios.update', formEdit.id), {
        preserveScroll: true,
        onSuccess: () => {
            showEditModal.value = false;
            formEdit.reset();
        },
    });
};

const toggleActivo = (user) => {
    router.patch(route('admin.usuarios.toggle-activo', user.id), {}, {
        preserveScroll: true,
    });
};

const confirmDelete = (user) => {
    userToDelete.value = user;
    confirmingDeletion.value = true;
};

const deleteUser = () => {
    if (!userToDelete.value) return;
    router.delete(route('admin.usuarios.destroy', userToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            confirmingDeletion.value = false;
            userToDelete.value = null;
        },
    });
};
</script>

<template>
    <AppLayout title="Gestión de Usuarios y Roles - IETA">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="font-bold text-2xl text-slate-900 dark:text-white leading-tight">
                        Usuarios y Roles (RBAC)
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Control de acceso institucional, cuentas de autor y seguridad 2FA
                    </p>
                </div>
                <div>
                    <PrimaryButton @click="openCreateModal" class="shadow-lg shadow-blue-500/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Nuevo Usuario
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
            <!-- Search & Filters -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-4 sm:p-5 shadow-sm border border-slate-200 dark:border-slate-800 mb-6">
                <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                    <div class="sm:col-span-2">
                        <InputLabel for="search" value="Buscar por nombre o correo" class="sr-only" />
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input
                                id="search"
                                v-model="search"
                                type="text"
                                placeholder="Buscar usuario..."
                                @keyup.enter="applyFilters"
                                class="w-full pl-9 pr-4 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                            />
                        </div>
                    </div>

                    <div>
                        <select
                            v-model="roleFilter"
                            @change="applyFilters"
                            class="w-full py-2 px-3 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="">Todos los roles</option>
                            <option v-for="role in roles" :key="role" :value="role">
                                Rol: {{ role }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <select
                            v-model="statusFilter"
                            @change="applyFilters"
                            class="w-full py-2 px-3 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="">Todos los estados</option>
                            <option value="active">Activos</option>
                            <option value="inactive">Inactivos</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Table of Users -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            <tr>
                                <th class="px-6 py-4">#</th>
                                <th class="px-6 py-4">Usuario</th>
                                <th class="px-6 py-4">Rol</th>
                                <th class="px-6 py-4">2FA (Doble Factor)</th>
                                <th class="px-6 py-4">Vínculo Autor</th>
                                <th class="px-6 py-4">Estado</th>
                                <th class="px-6 py-4 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr
                                v-for="(user, index) in usuarios.data"
                                :key="user.id"
                                class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors"
                            >
                                <td class="px-6 py-4 font-mono text-xs text-slate-400">
                                    {{ ((usuarios.current_page - 1) * usuarios.per_page) + index + 1 }}
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-9 h-9 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center font-bold text-slate-600 dark:text-slate-200 text-sm overflow-hidden shrink-0">
                                            <img
                                                v-if="user.profile_photo_url"
                                                :src="user.profile_photo_url"
                                                :alt="user.name"
                                                class="w-full h-full object-cover"
                                            />
                                            <span v-else>{{ user.name.charAt(0).toUpperCase() }}</span>
                                        </div>
                                        <div>
                                            <div class="font-semibold text-slate-900 dark:text-white">
                                                {{ user.name }}
                                            </div>
                                            <div class="text-xs text-slate-500 dark:text-slate-400">
                                                {{ user.email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <span
                                        v-if="user.roles.includes('administrador')"
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300 border border-purple-200 dark:border-purple-800"
                                    >
                                        🛡️ Administrador
                                    </span>
                                    <span
                                        v-else-if="user.roles.includes('autor')"
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800"
                                    >
                                        ✍️ Autor
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300"
                                    >
                                        {{ user.roles.join(', ') || 'Sin rol' }}
                                    </span>
                                </td>

                                <td class="px-6 py-4">
                                    <span
                                        v-if="user.two_factor_enabled"
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-400"
                                    >
                                        <svg class="w-3 h-3 mr-1 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        Activo
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400"
                                    >
                                        Pendiente
                                    </span>
                                </td>

                                <td class="px-6 py-4">
                                    <span
                                        v-if="user.autor_asociado"
                                        class="text-xs font-medium text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 px-2 py-1 rounded-lg"
                                    >
                                        {{ user.autor_asociado.nombre }}
                                    </span>
                                    <span v-else class="text-xs text-slate-400 italic">
                                        No vinculado
                                    </span>
                                </td>

                                <td class="px-6 py-4">
                                    <button
                                        @click="toggleActivo(user)"
                                        :class="user.is_active ? 'bg-emerald-600' : 'bg-slate-300 dark:bg-slate-700'"
                                        class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out focus:outline-none"
                                        :title="user.is_active ? 'Desactivar cuenta' : 'Activar cuenta'"
                                    >
                                        <span
                                            :class="user.is_active ? 'translate-x-4' : 'translate-x-0.5'"
                                            class="inline-block h-4 w-4 transform rounded-full bg-white transition duration-200 ease-in-out mt-0.5"
                                        />
                                    </button>
                                </td>

                                <td class="px-6 py-4 text-right space-x-2">
                                    <button
                                        @click="openEditModal(user)"
                                        class="text-xs font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 transition-colors"
                                    >
                                        Editar
                                    </button>
                                    <button
                                        @click="confirmDelete(user)"
                                        class="text-xs font-medium text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 transition-colors"
                                    >
                                        Eliminar
                                    </button>
                                </td>
                            </tr>

                            <tr v-if="usuarios.data.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center text-slate-400 text-sm">
                                    No se encontraron usuarios registrados con los criterios seleccionados.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="usuarios.links && usuarios.links.length > 3" class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <div class="text-xs text-slate-500">
                        Mostrando {{ usuarios.from }} a {{ usuarios.to }} de {{ usuarios.total }} usuarios
                    </div>
                    <div class="flex space-x-1">
                        <Link
                            v-for="(link, i) in usuarios.links"
                            :key="i"
                            :href="link.url || '#'"
                            :class="[
                                'px-3 py-1 text-xs rounded-lg transition-colors',
                                link.active ? 'bg-blue-600 text-white font-bold' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800',
                                !link.url ? 'opacity-40 cursor-not-allowed' : ''
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal: Create User -->
        <Modal :show="showCreateModal" @close="showCreateModal = false">
            <div class="p-6">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-1">
                    Dar de Alta Nuevo Usuario
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-6">
                    Las cuentas de usuario y de autor son generadas únicamente por la administración.
                </p>

                <form @submit.prevent="submitCreate" class="space-y-4">
                    <div>
                        <InputLabel for="create_name" value="Nombre Completo" />
                        <TextInput
                            id="create_name"
                            v-model="formCreate.name"
                            type="text"
                            class="mt-1 block w-full"
                            required
                            placeholder="Ej. Dr. Mario Silva"
                        />
                        <InputError :message="formCreate.errors.name" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="create_email" value="Correo Electrónico Institucional / Personal" />
                        <TextInput
                            id="create_email"
                            v-model="formCreate.email"
                            type="email"
                            class="mt-1 block w-full"
                            required
                            placeholder="mario.silva@umsa.bo"
                        />
                        <InputError :message="formCreate.errors.email" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="create_password" value="Contraseña Temporal" />
                        <TextInput
                            id="create_password"
                            v-model="formCreate.password"
                            type="password"
                            class="mt-1 block w-full"
                            required
                            placeholder="Mínimo 8 caracteres"
                        />
                        <InputError :message="formCreate.errors.password" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="create_role" value="Rol en el Sistema" />
                        <select
                            id="create_role"
                            v-model="formCreate.role"
                            class="mt-1 block w-full py-2 px-3 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 capitalize"
                        >
                            <option v-for="r in roles" :key="r" :value="r">
                                {{ r }}
                            </option>
                        </select>
                        <InputError :message="formCreate.errors.role" class="mt-1" />
                    </div>

                    <div class="flex items-center space-x-2 pt-2">
                        <input
                            id="create_active"
                            v-model="formCreate.is_active"
                            type="checkbox"
                            class="rounded border-slate-300 dark:border-slate-700 text-blue-600 focus:ring-blue-500"
                        />
                        <label for="create_active" class="text-xs text-slate-700 dark:text-slate-300">
                            Cuenta activa para inicio de sesión
                        </label>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <SecondaryButton @click="showCreateModal = false">
                            Cancelar
                        </SecondaryButton>
                        <PrimaryButton :disabled="formCreate.processing">
                            Guardar Usuario
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Modal: Edit User -->
        <Modal :show="showEditModal" @close="showEditModal = false">
            <div class="p-6">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-1">
                    Editar Usuario: {{ userToEdit?.name }}
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-6">
                    Actualiza información básica, rol o restablece la contraseña.
                </p>

                <form @submit.prevent="submitEdit" class="space-y-4">
                    <div>
                        <InputLabel for="edit_name" value="Nombre Completo" />
                        <TextInput
                            id="edit_name"
                            v-model="formEdit.name"
                            type="text"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError :message="formEdit.errors.name" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="edit_email" value="Correo Electrónico" />
                        <TextInput
                            id="edit_email"
                            v-model="formEdit.email"
                            type="email"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError :message="formEdit.errors.email" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="edit_password" value="Nueva Contraseña (Dejar en blanco para conservar la actual)" />
                        <TextInput
                            id="edit_password"
                            v-model="formEdit.password"
                            type="password"
                            class="mt-1 block w-full"
                            placeholder="••••••••"
                        />
                        <InputError :message="formEdit.errors.password" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="edit_role" value="Rol en el Sistema" />
                        <select
                            id="edit_role"
                            v-model="formEdit.role"
                            class="mt-1 block w-full py-2 px-3 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:ring-2 focus:ring-blue-500 capitalize"
                        >
                            <option v-for="r in roles" :key="r" :value="r">
                                {{ r }}
                            </option>
                        </select>
                        <InputError :message="formEdit.errors.role" class="mt-1" />
                    </div>

                    <div class="flex items-center space-x-2 pt-2">
                        <input
                            id="edit_active"
                            v-model="formEdit.is_active"
                            type="checkbox"
                            class="rounded border-slate-300 dark:border-slate-700 text-blue-600 focus:ring-blue-500"
                        />
                        <label for="edit_active" class="text-xs text-slate-700 dark:text-slate-300">
                            Cuenta activa para inicio de sesión
                        </label>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <SecondaryButton @click="showEditModal = false">
                            Cancelar
                        </SecondaryButton>
                        <PrimaryButton :disabled="formEdit.processing">
                            Guardar Cambios
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <ConfirmationModal :show="confirmingDeletion" @close="confirmingDeletion = false">
            <template #title>
                Eliminar Cuenta de Usuario
            </template>
            <template #content>
                ¿Estás seguro de que deseas eliminar la cuenta de <strong>{{ userToDelete?.name }}</strong>? Esta acción revocará su acceso al sistema de forma inmediata.
            </template>
            <template #footer>
                <SecondaryButton @click="confirmingDeletion = false">
                    Cancelar
                </SecondaryButton>
                <DangerButton @click="deleteUser" class="ml-3">
                    Eliminar Usuario
                </DangerButton>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>
