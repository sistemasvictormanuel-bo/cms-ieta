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
    roles: {
        type: Array,
        required: true,
    },
    allPermissions: {
        type: Array,
        required: true,
    },
    permissionsByModule: {
        type: Object,
        required: true,
    },
    modules: {
        type: Array,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const search = ref(props.filters.search || '');
const permModalSearch = ref('');
const selectedModuleTab = ref('ALL');

const showCreateModal = ref(false);
const showEditModal = ref(false);
const showPermissionsModal = ref(false);
const confirmingDeletion = ref(false);

const roleToEdit = ref(null);
const roleToDelete = ref(null);
const roleToViewPermissions = ref(null);

const formCreate = useForm({
    name: '',
    description: '',
    permissions: [],
});

const formEdit = useForm({
    id: null,
    name: '',
    description: '',
    permissions: [],
    is_protected: false,
});

const applySearch = () => {
    router.get(
        route('admin.roles.index'),
        {
            search: search.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
        }
    );
};

// Abre modal para crear rol
const openCreateModal = () => {
    formCreate.reset();
    formCreate.clearErrors();
    formCreate.permissions = [];
    permModalSearch.value = '';
    selectedModuleTab.value = 'ALL';
    showCreateModal.value = true;
};

const submitCreate = () => {
    formCreate.post(route('admin.roles.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showCreateModal.value = false;
            formCreate.reset();
        },
    });
};

// Abre modal para editar rol y asignar permisos
const openEditModal = (role) => {
    roleToEdit.value = role;
    formEdit.clearErrors();
    formEdit.id = role.id;
    formEdit.name = role.name;
    formEdit.description = role.description || '';
    formEdit.is_protected = !!role.is_protected;
    formEdit.permissions = [...(role.permission_names || [])];
    permModalSearch.value = '';
    selectedModuleTab.value = 'ALL';
    showEditModal.value = true;
};

const submitEdit = () => {
    if (!roleToEdit.value) return;
    formEdit.put(route('admin.roles.update', roleToEdit.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showEditModal.value = false;
            roleToEdit.value = null;
        },
    });
};

// Abre modal para ver detalle de permisos del rol
const openViewPermissions = (role) => {
    roleToViewPermissions.value = role;
    showPermissionsModal.value = true;
};

// Eliminación de rol
const confirmDelete = (role) => {
    roleToDelete.value = role;
    confirmingDeletion.value = true;
};

const deleteRole = () => {
    if (!roleToDelete.value) return;
    router.delete(route('admin.roles.destroy', roleToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            confirmingDeletion.value = false;
            roleToDelete.value = null;
        },
    });
};

// Helpers para selección de permisos por módulo (para Create)
const toggleSelectAllModuleCreate = (moduleName) => {
    const modulePerms = (props.permissionsByModule[moduleName] || []).map(p => p.name);
    const allSelected = modulePerms.every(p => formCreate.permissions.includes(p));

    if (allSelected) {
        formCreate.permissions = formCreate.permissions.filter(p => !modulePerms.includes(p));
    } else {
        const toAdd = modulePerms.filter(p => !formCreate.permissions.includes(p));
        formCreate.permissions = [...formCreate.permissions, ...toAdd];
    }
};

const isModuleAllSelectedCreate = (moduleName) => {
    const modulePerms = (props.permissionsByModule[moduleName] || []).map(p => p.name);
    if (!modulePerms.length) return false;
    return modulePerms.every(p => formCreate.permissions.includes(p));
};

const moduleSelectedCountCreate = (moduleName) => {
    const modulePerms = (props.permissionsByModule[moduleName] || []).map(p => p.name);
    return modulePerms.filter(p => formCreate.permissions.includes(p)).length;
};

// Helpers para selección de permisos por módulo (para Edit)
const toggleSelectAllModuleEdit = (moduleName) => {
    const modulePerms = (props.permissionsByModule[moduleName] || []).map(p => p.name);
    const allSelected = modulePerms.every(p => formEdit.permissions.includes(p));

    if (allSelected) {
        formEdit.permissions = formEdit.permissions.filter(p => !modulePerms.includes(p));
    } else {
        const toAdd = modulePerms.filter(p => !formEdit.permissions.includes(p));
        formEdit.permissions = [...formEdit.permissions, ...toAdd];
    }
};

const isModuleAllSelectedEdit = (moduleName) => {
    const modulePerms = (props.permissionsByModule[moduleName] || []).map(p => p.name);
    if (!modulePerms.length) return false;
    return modulePerms.every(p => formEdit.permissions.includes(p));
};

const moduleSelectedCountEdit = (moduleName) => {
    const modulePerms = (props.permissionsByModule[moduleName] || []).map(p => p.name);
    return modulePerms.filter(p => formEdit.permissions.includes(p)).length;
};

// Filtrar módulos y permisos según el buscador interno del modal
const filterPermissionsBySearch = (permissionsList) => {
    if (!permModalSearch.value || !permModalSearch.value.trim()) {
        return permissionsList;
    }
    const term = permModalSearch.value.toLowerCase().trim();
    return permissionsList.filter(p =>
        p.name.toLowerCase().includes(term) ||
        (p.description && p.description.toLowerCase().includes(term))
    );
};

// Módulos visibles en el modal según pestaña seleccionada
const visibleModules = computed(() => {
    if (selectedModuleTab.value === 'ALL') {
        return props.modules;
    }
    return props.modules.filter(m => m === selectedModuleTab.value);
});

// Resumen del total de permisos
const totalPermissionsCount = computed(() => props.allPermissions.length);
</script>

<template>
    <AppLayout title="Gestión de Roles - IETA">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center space-x-2.5">
                        <span class="inline-flex items-center justify-center size-9 rounded-xl bg-[#35449F]/10 text-[#35449F]">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </span>
                        <h2 class="font-extrabold text-2xl text-slate-900 tracking-tight">
                            Roles y Control de Acceso
                        </h2>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1 pl-11">
                        Crea perfiles institucionales y asigna permisos específicos por módulo a cada rol
                    </p>
                </div>
                <div class="flex items-center space-x-3">
                    <Link
                        :href="route('admin.permisos.index')"
                        class="inline-flex items-center px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 transition shadow-xs"
                    >
                        <svg class="w-4 h-4 mr-2 text-[#35449F]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                        Ver Módulo de Permisos
                    </Link>
                    <PrimaryButton @click="openCreateModal" class="shadow-md shadow-[#35449F]/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Nuevo Rol
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">
            <!-- Barra de búsqueda e información -->
            <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-xs border border-slate-200 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="relative flex-1 max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Buscar por nombre o descripción de rol..."
                        @keyup.enter="applySearch"
                        class="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-slate-300 bg-slate-50/70 text-slate-900 focus:bg-white focus:ring-2 focus:ring-[#35449F] focus:border-transparent transition"
                    />
                </div>

                <div class="flex items-center space-x-4 text-xs font-medium text-slate-500">
                    <span class="flex items-center gap-1.5">
                        <span class="size-2.5 rounded-full bg-emerald-500"></span>
                        <strong>{{ roles.length }}</strong> Roles registrados
                    </span>
                    <span class="hidden sm:inline text-slate-300">|</span>
                    <span class="flex items-center gap-1.5">
                        <span class="size-2.5 rounded-full bg-[#35449F]"></span>
                        <strong>{{ totalPermissionsCount }}</strong> Permisos globales en {{ modules.length }} módulos
                    </span>
                </div>
            </div>

            <!-- Grid de Roles -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="role in roles"
                    :key="role.id"
                    class="bg-white rounded-2xl border border-slate-200/90 shadow-xs hover:shadow-md transition-all duration-200 flex flex-col overflow-hidden group"
                >
                    <!-- Header Card -->
                    <div class="p-5 border-b border-slate-100 bg-gradient-to-br from-slate-50/70 via-white to-white flex items-start justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-extrabold text-lg text-slate-900 capitalize tracking-tight">
                                    {{ role.name }}
                                </h3>
                                <span
                                    v-if="role.is_protected"
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200/60 uppercase tracking-wider"
                                >
                                    Base del Sistema
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-1 line-clamp-2 min-h-[32px]">
                                {{ role.description || 'Sin descripción adicional para este rol.' }}
                            </p>
                        </div>
                        <div class="size-10 rounded-xl bg-[#35449F]/5 text-[#35449F] flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                    </div>

                    <!-- Estadísticas del Rol -->
                    <div class="px-5 py-4 bg-slate-50/40 border-b border-slate-100 flex items-center justify-between text-xs">
                        <div class="flex items-center space-x-2 text-slate-600 font-semibold">
                            <svg class="size-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span>{{ role.users_count }} usuario(s)</span>
                        </div>

                        <div class="flex items-center space-x-1.5 font-bold">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs" :class="role.permissions_count > 0 ? 'bg-indigo-50 text-[#35449F]' : 'bg-slate-100 text-slate-500'">
                                <svg class="size-3.5 mr-1 text-[#35449F]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                </svg>
                                {{ role.permissions_count }} / {{ totalPermissionsCount }} permisos
                            </span>
                        </div>
                    </div>

                    <!-- Módulos incluidos (Tags) -->
                    <div class="p-5 flex-1 space-y-3">
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                            Permisos asignados
                        </div>

                        <div v-if="role.permissions.length" class="flex flex-wrap gap-1.5 max-h-36 overflow-y-auto pr-1">
                            <span
                                v-for="perm in role.permissions.slice(0, 8)"
                                :key="perm.id"
                                class="inline-flex items-center px-2 py-1 rounded-md text-[11px] font-medium bg-slate-100 text-slate-700 border border-slate-200/60"
                                :title="perm.description || perm.name"
                            >
                                <span class="text-[10px] text-slate-400 mr-1 font-mono">{{ perm.modulo }}:</span>
                                {{ perm.name }}
                            </span>
                            <span
                                v-if="role.permissions.length > 8"
                                @click="openViewPermissions(role)"
                                class="inline-flex items-center px-2 py-1 rounded-md text-[11px] font-bold bg-[#35449F]/10 text-[#35449F] hover:bg-[#35449F]/20 cursor-pointer transition"
                            >
                                +{{ role.permissions.length - 8 }} más...
                            </span>
                        </div>
                        <div v-else class="text-xs text-slate-400 italic py-2">
                            No tiene permisos asignados todavía.
                        </div>
                    </div>

                    <!-- Footer / Acciones -->
                    <div class="p-4 bg-slate-50/80 border-t border-slate-100 flex items-center justify-between gap-2">
                        <button
                            type="button"
                            @click="openViewPermissions(role)"
                            class="text-xs font-semibold text-slate-600 hover:text-slate-900 transition flex items-center gap-1 cursor-pointer"
                        >
                            <svg class="size-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Detalle
                        </button>

                        <div class="flex items-center space-x-2">
                            <button
                                type="button"
                                @click="openEditModal(role)"
                                class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-[#35449F] text-white hover:bg-[#2b3780] shadow-xs transition cursor-pointer"
                            >
                                <svg class="size-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Configurar Permisos
                            </button>

                            <button
                                v-if="!role.is_protected"
                                type="button"
                                @click="confirmDelete(role)"
                                class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer"
                                title="Eliminar Rol"
                            >
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL: CREAR NUEVO ROL                     -->
        <!-- ========================================== -->
        <Modal :show="showCreateModal" @close="showCreateModal = false" max-width="4xl">
            <div class="p-6 flex flex-col max-h-[85vh]">
                <!-- Header del Modal -->
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 shrink-0">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">
                            Crear Nuevo Rol
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Define el nombre del rol institucional y selecciona los permisos asignados por módulo
                        </p>
                    </div>
                    <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 transition">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Formulario -->
                <form @submit.prevent="submitCreate" class="flex flex-col flex-1 overflow-hidden mt-4">
                    <div class="space-y-4 shrink-0 pb-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="create_name" value="Nombre del Rol *" />
                                <TextInput
                                    id="create_name"
                                    v-model="formCreate.name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    placeholder="Ej: Editor, Revisor Científico, Coordinador"
                                    required
                                />
                                <InputError :message="formCreate.errors.name" class="mt-1" />
                            </div>

                            <div>
                                <InputLabel for="create_description" value="Descripción / Propósito" />
                                <TextInput
                                    id="create_description"
                                    v-model="formCreate.description"
                                    type="text"
                                    class="mt-1 block w-full"
                                    placeholder="Breve explicación de las atribuciones de este rol"
                                />
                                <InputError :message="formCreate.errors.description" class="mt-1" />
                            </div>
                        </div>

                        <!-- Barra de búsqueda de permisos y selector de módulos -->
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pt-2">
                            <div class="text-xs font-bold uppercase tracking-wider text-slate-600 flex items-center gap-2">
                                <span>Matriz de Permisos</span>
                                <span class="bg-[#35449F]/10 text-[#35449F] px-2 py-0.5 rounded-full text-[11px] font-extrabold">
                                    {{ formCreate.permissions.length }} de {{ totalPermissionsCount }} seleccionados
                                </span>
                            </div>

                            <div class="relative w-full sm:w-64">
                                <input
                                    v-model="permModalSearch"
                                    type="text"
                                    placeholder="Filtrar permisos..."
                                    class="w-full pl-8 pr-3 py-1.5 text-xs rounded-xl border border-slate-300 bg-slate-50 focus:bg-white focus:ring-1 focus:ring-[#35449F]"
                                />
                                <svg class="size-3.5 absolute left-2.5 top-2.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Filtro de Tabs de Módulos -->
                        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs">
                            <button
                                type="button"
                                @click="selectedModuleTab = 'ALL'"
                                :class="selectedModuleTab === 'ALL' ? 'bg-[#35449F] text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                class="px-3 py-1 rounded-lg shrink-0 transition"
                            >
                                Todos ({{ modules.length }})
                            </button>
                            <button
                                v-for="mod in modules"
                                :key="mod"
                                type="button"
                                @click="selectedModuleTab = mod"
                                :class="selectedModuleTab === mod ? 'bg-[#35449F] text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                class="px-3 py-1 rounded-lg shrink-0 transition"
                            >
                                {{ mod }}
                            </button>
                        </div>
                    </div>

                    <!-- Lista de permisos scrolleable agrupada por módulo -->
                    <div class="flex-1 overflow-y-auto space-y-4 pr-1 border-t border-slate-100 pt-3">
                        <div
                            v-for="modName in visibleModules"
                            :key="modName"
                            class="bg-slate-50/60 rounded-xl p-3.5 border border-slate-200/80"
                        >
                            <!-- Header de cada Módulo -->
                            <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-200/60">
                                <div class="flex items-center space-x-2">
                                    <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wide">
                                        {{ modName }}
                                    </h4>
                                    <span class="text-[11px] font-semibold text-slate-500 bg-white px-1.5 py-0.5 rounded border border-slate-200">
                                        {{ moduleSelectedCountCreate(modName) }} / {{ (permissionsByModule[modName] || []).length }}
                                    </span>
                                </div>
                                <button
                                    type="button"
                                    @click="toggleSelectAllModuleCreate(modName)"
                                    class="text-[11px] font-semibold text-[#35449F] hover:underline cursor-pointer"
                                >
                                    {{ isModuleAllSelectedCreate(modName) ? 'Desmarcar todos' : 'Marcar todos' }}
                                </button>
                            </div>

                            <!-- Permisos dentro de este módulo -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <label
                                    v-for="perm in filterPermissionsBySearch(permissionsByModule[modName] || [])"
                                    :key="perm.id"
                                    class="flex items-start p-2 rounded-lg border transition cursor-pointer select-none"
                                    :class="formCreate.permissions.includes(perm.name) ? 'bg-indigo-50/50 border-[#35449F]/40' : 'bg-white border-slate-200/80 hover:bg-slate-50'"
                                >
                                    <input
                                        type="checkbox"
                                        :value="perm.name"
                                        v-model="formCreate.permissions"
                                        class="rounded border-slate-300 text-[#35449F] focus:ring-[#35449F] size-4 mt-0.5 mr-2.5 shrink-0"
                                    />
                                    <div class="min-w-0">
                                        <p class="text-xs font-mono font-bold text-slate-800 truncate leading-tight">
                                            {{ perm.name }}
                                        </p>
                                        <p class="text-[11px] text-slate-500 line-clamp-2 mt-0.5">
                                            {{ perm.description || 'Sin descripción' }}
                                        </p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Footer del Modal -->
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3 shrink-0">
                        <SecondaryButton @click="showCreateModal = false" :disabled="formCreate.processing">
                            Cancelar
                        </SecondaryButton>
                        <PrimaryButton :disabled="formCreate.processing" :class="{ 'opacity-25': formCreate.processing }">
                            Guardar Rol
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- ========================================== -->
        <!-- MODAL: EDITAR ROL Y ASIGNAR PERMISOS       -->
        <!-- ========================================== -->
        <Modal :show="showEditModal" @close="showEditModal = false" max-width="4xl">
            <div class="p-6 flex flex-col max-h-[85vh]">
                <!-- Header del Modal -->
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 shrink-0">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-bold text-slate-900">
                                Configurar Rol: <span class="capitalize text-[#35449F]">{{ formEdit.name }}</span>
                            </h3>
                            <span
                                v-if="formEdit.is_protected"
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 uppercase"
                            >
                                Rol Base Protegido
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Modifica la descripción del rol y administra los permisos asignados
                        </p>
                    </div>
                    <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 transition">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Formulario -->
                <form @submit.prevent="submitEdit" class="flex flex-col flex-1 overflow-hidden mt-4">
                    <div class="space-y-4 shrink-0 pb-3">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="edit_name" value="Nombre del Rol *" />
                                <TextInput
                                    id="edit_name"
                                    v-model="formEdit.name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    :disabled="formEdit.is_protected"
                                    :class="formEdit.is_protected ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : ''"
                                    required
                                />
                                <p v-if="formEdit.is_protected" class="text-[11px] text-amber-600 mt-1">
                                    El nombre de este rol está protegido por el sistema y no puede cambiarse.
                                </p>
                                <InputError :message="formEdit.errors.name" class="mt-1" />
                            </div>

                            <div>
                                <InputLabel for="edit_description" value="Descripción / Propósito" />
                                <TextInput
                                    id="edit_description"
                                    v-model="formEdit.description"
                                    type="text"
                                    class="mt-1 block w-full"
                                    placeholder="Breve explicación de las atribuciones de este rol"
                                />
                                <InputError :message="formEdit.errors.description" class="mt-1" />
                            </div>
                        </div>

                        <!-- Barra de búsqueda de permisos y selector de módulos -->
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pt-2">
                            <div class="text-xs font-bold uppercase tracking-wider text-slate-600 flex items-center gap-2">
                                <span>Permisos Asignados</span>
                                <span class="bg-[#35449F]/10 text-[#35449F] px-2 py-0.5 rounded-full text-[11px] font-extrabold">
                                    {{ formEdit.permissions.length }} de {{ totalPermissionsCount }} seleccionados
                                </span>
                            </div>

                            <div class="relative w-full sm:w-64">
                                <input
                                    v-model="permModalSearch"
                                    type="text"
                                    placeholder="Filtrar permisos..."
                                    class="w-full pl-8 pr-3 py-1.5 text-xs rounded-xl border border-slate-300 bg-slate-50 focus:bg-white focus:ring-1 focus:ring-[#35449F]"
                                />
                                <svg class="size-3.5 absolute left-2.5 top-2.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Filtro de Tabs de Módulos -->
                        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs">
                            <button
                                type="button"
                                @click="selectedModuleTab = 'ALL'"
                                :class="selectedModuleTab === 'ALL' ? 'bg-[#35449F] text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                class="px-3 py-1 rounded-lg shrink-0 transition"
                            >
                                Todos ({{ modules.length }})
                            </button>
                            <button
                                v-for="mod in modules"
                                :key="mod"
                                type="button"
                                @click="selectedModuleTab = mod"
                                :class="selectedModuleTab === mod ? 'bg-[#35449F] text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                class="px-3 py-1 rounded-lg shrink-0 transition"
                            >
                                {{ mod }}
                            </button>
                        </div>
                    </div>

                    <!-- Lista de permisos scrolleable agrupada por módulo -->
                    <div class="flex-1 overflow-y-auto space-y-4 pr-1 border-t border-slate-100 pt-3">
                        <div
                            v-for="modName in visibleModules"
                            :key="modName"
                            class="bg-slate-50/60 rounded-xl p-3.5 border border-slate-200/80"
                        >
                            <!-- Header de cada Módulo -->
                            <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-200/60">
                                <div class="flex items-center space-x-2">
                                    <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wide">
                                        {{ modName }}
                                    </h4>
                                    <span class="text-[11px] font-semibold text-slate-500 bg-white px-1.5 py-0.5 rounded border border-slate-200">
                                        {{ moduleSelectedCountEdit(modName) }} / {{ (permissionsByModule[modName] || []).length }}
                                    </span>
                                </div>
                                <button
                                    type="button"
                                    @click="toggleSelectAllModuleEdit(modName)"
                                    class="text-[11px] font-semibold text-[#35449F] hover:underline cursor-pointer"
                                >
                                    {{ isModuleAllSelectedEdit(modName) ? 'Desmarcar todos' : 'Marcar todos' }}
                                </button>
                            </div>

                            <!-- Permisos dentro de este módulo -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <label
                                    v-for="perm in filterPermissionsBySearch(permissionsByModule[modName] || [])"
                                    :key="perm.id"
                                    class="flex items-start p-2 rounded-lg border transition cursor-pointer select-none"
                                    :class="formEdit.permissions.includes(perm.name) ? 'bg-indigo-50/50 border-[#35449F]/40' : 'bg-white border-slate-200/80 hover:bg-slate-50'"
                                >
                                    <input
                                        type="checkbox"
                                        :value="perm.name"
                                        v-model="formEdit.permissions"
                                        class="rounded border-slate-300 text-[#35449F] focus:ring-[#35449F] size-4 mt-0.5 mr-2.5 shrink-0"
                                    />
                                    <div class="min-w-0">
                                        <p class="text-xs font-mono font-bold text-slate-800 truncate leading-tight">
                                            {{ perm.name }}
                                        </p>
                                        <p class="text-[11px] text-slate-500 line-clamp-2 mt-0.5">
                                            {{ perm.description || 'Sin descripción' }}
                                        </p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Footer del Modal -->
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3 shrink-0">
                        <SecondaryButton @click="showEditModal = false" :disabled="formEdit.processing">
                            Cancelar
                        </SecondaryButton>
                        <PrimaryButton :disabled="formEdit.processing" :class="{ 'opacity-25': formEdit.processing }">
                            Actualizar Rol
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- ========================================== -->
        <!-- MODAL: DETALLE COMPLETO DE PERMISOS        -->
        <!-- ========================================== -->
        <Modal :show="showPermissionsModal" @close="showPermissionsModal = false" max-width="3xl">
            <div class="p-6">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                            Permisos asignados al Rol:
                            <span class="text-[#35449F] capitalize">{{ roleToViewPermissions?.name }}</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Total: {{ roleToViewPermissions?.permissions?.length || 0 }} permisos otorgados
                        </p>
                    </div>
                    <button @click="showPermissionsModal = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="max-h-[60vh] overflow-y-auto mt-4 space-y-4 pr-1">
                    <div v-if="!roleToViewPermissions?.permissions?.length" class="text-center py-8 text-slate-400 text-sm">
                        Este rol no tiene ningún permiso asignado actualmente.
                    </div>
                    <div
                        v-for="(perms, moduleName) in permissionsByModule"
                        :key="moduleName"
                    >
                        <div v-if="perms.some(p => roleToViewPermissions?.permission_names?.includes(p.name))" class="bg-slate-50 rounded-xl p-3 border border-slate-200">
                            <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wide mb-2">
                                {{ moduleName }}
                            </h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <div
                                    v-for="perm in perms.filter(p => roleToViewPermissions?.permission_names?.includes(p.name))"
                                    :key="perm.id"
                                    class="p-2 rounded-lg bg-white border border-slate-200/80"
                                >
                                    <p class="text-xs font-mono font-bold text-[#35449F]">
                                        {{ perm.name }}
                                    </p>
                                    <p class="text-[11px] text-slate-500 mt-0.5">
                                        {{ perm.description || 'Sin descripción' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-5 pt-3 border-t border-slate-100 flex justify-end">
                    <SecondaryButton @click="showPermissionsModal = false">
                        Cerrar
                    </SecondaryButton>
                </div>
            </div>
        </Modal>

        <!-- ========================================== -->
        <!-- MODAL: CONFIRMACIÓN DE ELIMINACIÓN         -->
        <!-- ========================================== -->
        <ConfirmationModal :show="confirmingDeletion" @close="confirmingDeletion = false">
            <template #title>
                Eliminar Rol: <span class="capitalize text-rose-600">{{ roleToDelete?.name }}</span>
            </template>

            <template #content>
                <p class="text-sm text-slate-600">
                    ¿Estás seguro de que deseas eliminar este rol? Esta acción revocará todos sus permisos.
                </p>
                <div v-if="roleToDelete?.users_count > 0" class="mt-3 p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-800">
                    <strong>Advertencia:</strong> Este rol tiene <strong>{{ roleToDelete.users_count }} usuario(s) asignado(s)</strong>.
                    Debes reasignar a esos usuarios a otro rol antes de poder eliminarlo.
                </div>
            </template>

            <template #footer>
                <SecondaryButton @click="confirmingDeletion = false">
                    Cancelar
                </SecondaryButton>

                <DangerButton
                    class="ml-3"
                    @click="deleteRole"
                    :disabled="roleToDelete?.users_count > 0"
                    :class="{ 'opacity-50 cursor-not-allowed': roleToDelete?.users_count > 0 }"
                >
                    Eliminar Rol
                </DangerButton>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>
