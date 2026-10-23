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
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    permissions: {
        type: Object,
        required: true,
    },
    modules: {
        type: Array,
        required: true,
    },
    modulesWithCount: {
        type: Array,
        required: true,
    },
    roles: {
        type: Array,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    total: {
        type: Number,
        default: 0,
    },
});

const search = ref(props.filters.search || '');
const moduleFilter = ref(props.filters.modulo || '');
const roleFilter = ref(props.filters.role || '');
const perPage = ref(props.filters.per_page || 20);

const showCreateModal = ref(false);
const showEditModal = ref(false);
const confirmingDeletion = ref(false);
const permissionToDelete = ref(null);
const permissionToEdit = ref(null);

const isNewModuleMode = ref(false);
const customModuleName = ref('');

const formCreate = useForm({
    name: '',
    modulo: '',
    description: '',
    roles: ['administrador'],
});

const formEdit = useForm({
    id: null,
    name: '',
    modulo: '',
    description: '',
    roles: [],
    is_protected: false,
});

const copiedPermission = ref('');
const copyToClipboard = (text) => {
    navigator.clipboard.writeText(text);
    copiedPermission.value = text;
    setTimeout(() => {
        if (copiedPermission.value === text) {
            copiedPermission.value = '';
        }
    }, 2000);
};

const applyFilters = () => {
    router.get(
        route('admin.permisos.index'),
        {
            search: search.value || undefined,
            modulo: moduleFilter.value || undefined,
            role: roleFilter.value || undefined,
            per_page: perPage.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
        }
    );
};

const filterByModuleChip = (mod) => {
    if (moduleFilter.value === mod) {
        moduleFilter.value = '';
    } else {
        moduleFilter.value = mod;
    }
    applyFilters();
};

const openCreateModal = () => {
    formCreate.reset();
    formCreate.clearErrors();
    isNewModuleMode.value = false;
    customModuleName.value = '';
    formCreate.modulo = props.modules[0] || 'General';
    formCreate.roles = ['administrador'];
    showCreateModal.value = true;
};

const submitCreate = () => {
    if (isNewModuleMode.value && customModuleName.value.trim()) {
        formCreate.modulo = customModuleName.value.trim();
    }

    formCreate.post(route('admin.permisos.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showCreateModal.value = false;
            formCreate.reset();
        },
    });
};

const openEditModal = (permission) => {
    permissionToEdit.value = permission;
    formEdit.clearErrors();
    formEdit.id = permission.id;
    formEdit.name = permission.name;
    formEdit.modulo = permission.modulo || 'General';
    formEdit.description = permission.description || '';
    formEdit.roles = [...(permission.roles || [])];
    formEdit.is_protected = !!permission.is_protected;
    showEditModal.value = true;
};

const submitEdit = () => {
    if (!permissionToEdit.value) return;

    formEdit.put(route('admin.permisos.update', permissionToEdit.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showEditModal.value = false;
            permissionToEdit.value = null;
        },
    });
};

const confirmDelete = (permission) => {
    permissionToDelete.value = permission;
    confirmingDeletion.value = true;
};

const deletePermission = () => {
    if (!permissionToDelete.value) return;
    router.delete(route('admin.permisos.destroy', permissionToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            confirmingDeletion.value = false;
            permissionToDelete.value = null;
        },
    });
};

// Sugerencia de slug técnico cuando el usuario escribe en nuevo módulo
const suggestSlug = () => {
    if (formCreate.name) return;
    const base = isNewModuleMode.value && customModuleName.value ? customModuleName.value : formCreate.modulo;
    if (base) {
        const slug = base
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9]/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
        formCreate.name = `${slug}.gestionar`;
    }
};

// Generador de colores consistentes por módulo
const getModuleBadgeColor = (moduleName) => {
    const colors = [
        'bg-blue-50 text-blue-700 border-blue-200',
        'bg-emerald-50 text-emerald-700 border-emerald-200',
        'bg-purple-50 text-purple-700 border-purple-200',
        'bg-amber-50 text-amber-700 border-amber-200',
        'bg-indigo-50 text-indigo-700 border-indigo-200',
        'bg-rose-50 text-rose-700 border-rose-200',
        'bg-cyan-50 text-cyan-700 border-cyan-200',
        'bg-teal-50 text-teal-700 border-teal-200',
    ];
    let hash = 0;
    for (let i = 0; i < (moduleName || '').length; i++) {
        hash = moduleName.charCodeAt(i) + ((hash << 5) - hash);
    }
    const index = Math.abs(hash) % colors.length;
    return colors[index];
};
</script>

<template>
    <AppLayout title="Gestión de Permisos - IETA">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center space-x-2.5">
                        <span class="inline-flex items-center justify-center size-9 rounded-xl bg-[#35449F]/10 text-[#35449F]">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                        </span>
                        <h2 class="font-extrabold text-2xl text-slate-900 tracking-tight">
                            Permisos del Sistema
                        </h2>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1 pl-11">
                        Catálogo de permisos técnicos categorizados por módulo (módulos actuales y nuevos módulos futuros)
                    </p>
                </div>
                <div class="flex items-center space-x-3">
                    <Link
                        :href="route('admin.roles.index')"
                        class="inline-flex items-center px-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 transition shadow-xs"
                    >
                        <svg class="w-4 h-4 mr-2 text-[#35449F]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        Ver Matriz de Roles
                    </Link>
                    <PrimaryButton @click="openCreateModal" class="shadow-md shadow-[#35449F]/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Nuevo Permiso
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-6">
            <!-- Chips de conteo por Módulo (Filtro rápido) -->
            <div class="bg-white rounded-2xl p-4 shadow-xs border border-slate-200">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">
                        Módulos Disponibles (Haz clic para filtrar)
                    </span>
                    <button
                        v-if="moduleFilter"
                        type="button"
                        @click="filterByModuleChip('')"
                        class="text-xs font-semibold text-[#35449F] hover:underline"
                    >
                        Limpiar filtro de módulo
                    </button>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button
                        type="button"
                        @click="filterByModuleChip('')"
                        :class="!moduleFilter ? 'bg-[#35449F] text-white font-bold shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-semibold transition cursor-pointer"
                    >
                        Todos los Módulos
                        <span class="ml-1.5 px-1.5 py-0.5 rounded-full text-[10px]" :class="!moduleFilter ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'">
                            {{ total }}
                        </span>
                    </button>

                    <button
                        v-for="item in modulesWithCount"
                        :key="item.modulo"
                        type="button"
                        @click="filterByModuleChip(item.modulo)"
                        :class="moduleFilter === item.modulo ? 'bg-[#35449F] text-white font-bold shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-semibold transition cursor-pointer"
                    >
                        {{ item.modulo }}
                        <span class="ml-1.5 px-1.5 py-0.5 rounded-full text-[10px]" :class="moduleFilter === item.modulo ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700'">
                            {{ item.count }}
                        </span>
                    </button>
                </div>
            </div>

            <!-- Filtros y Búsqueda -->
            <div class="bg-white rounded-2xl p-4 sm:p-5 shadow-xs border border-slate-200">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <!-- Buscador -->
                    <div class="lg:col-span-2">
                        <InputLabel for="search" value="Buscar permiso" class="sr-only" />
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input
                                id="search"
                                v-model="search"
                                type="text"
                                placeholder="Buscar por identificador técnico o descripción..."
                                @keyup.enter="applyFilters"
                                class="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-slate-300 bg-slate-50/70 text-slate-900 focus:bg-white focus:ring-2 focus:ring-[#35449F] focus:border-transparent transition"
                            />
                        </div>
                    </div>

                    <!-- Filtro por Rol -->
                    <div>
                        <select
                            v-model="roleFilter"
                            @change="applyFilters"
                            class="w-full py-2 px-3 text-sm rounded-xl border border-slate-300 bg-slate-50/70 text-slate-900 focus:bg-white focus:ring-2 focus:ring-[#35449F] focus:border-transparent cursor-pointer"
                        >
                            <option value="">Asignado a cualquier rol</option>
                            <option v-for="r in roles" :key="r" :value="r">
                                Rol: {{ r }}
                            </option>
                        </select>
                    </div>

                    <!-- Registros por página -->
                    <div>
                        <select
                            v-model="perPage"
                            @change="applyFilters"
                            class="w-full py-2 px-3 text-sm rounded-xl border border-slate-300 bg-slate-50/70 text-slate-900 focus:bg-white focus:ring-2 focus:ring-[#35449F] focus:border-transparent cursor-pointer"
                        >
                            <option :value="10">Mostrar 10 registros</option>
                            <option :value="20">Mostrar 20 registros</option>
                            <option :value="50">Mostrar 50 registros</option>
                            <option :value="100">Mostrar 100 registros</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Tabla de Permisos -->
            <div class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50/80 border-b border-slate-200 text-xs font-bold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-6 py-4">#</th>
                                <th class="px-6 py-4">Identificador Técnico</th>
                                <th class="px-6 py-4">Módulo</th>
                                <th class="px-6 py-4">Descripción del Permiso</th>
                                <th class="px-6 py-4">Roles con este Permiso</th>
                                <th class="px-6 py-4 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="(perm, idx) in permissions.data"
                                :key="perm.id"
                                class="hover:bg-slate-50/80 transition-colors duration-150"
                            >
                                <td class="px-6 py-4 text-xs text-slate-400 font-mono">
                                    {{ permissions.from ? permissions.from + idx : idx + 1 }}
                                </td>

                                <!-- Identificador Técnico con botón de copia -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center space-x-2">
                                        <code class="px-2 py-1 rounded bg-slate-100 font-mono text-xs font-bold text-slate-900 border border-slate-200">
                                            {{ perm.name }}
                                        </code>
                                        <button
                                            type="button"
                                            @click="copyToClipboard(perm.name)"
                                            class="text-slate-400 hover:text-[#35449F] transition"
                                            title="Copiar identificador técnico"
                                        >
                                            <svg v-if="copiedPermission === perm.name" class="size-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                            <svg v-else class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                        </button>
                                        <span
                                            v-if="perm.is_protected"
                                            class="text-[10px] font-bold text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200"
                                            title="Permiso esencial del sistema"
                                        >
                                            Base
                                        </span>
                                    </div>
                                </td>

                                <!-- Módulo con badge colorido -->
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border"
                                        :class="getModuleBadgeColor(perm.modulo)"
                                    >
                                        {{ perm.modulo }}
                                    </span>
                                </td>

                                <!-- Descripción -->
                                <td class="px-6 py-4 text-slate-600 text-xs sm:text-sm">
                                    {{ perm.description || 'Sin descripción detallada' }}
                                </td>

                                <!-- Roles asignados -->
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1 max-w-xs">
                                        <span
                                            v-for="r in perm.roles"
                                            :key="r"
                                            class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-indigo-50 text-[#35449F] border border-indigo-100 capitalize"
                                        >
                                            {{ r }}
                                        </span>
                                        <span v-if="!perm.roles.length" class="text-xs text-slate-400 italic">
                                            Sin asignar
                                        </span>
                                    </div>
                                </td>

                                <!-- Acciones -->
                                <td class="px-6 py-4 text-right space-x-1 whitespace-nowrap">
                                    <button
                                        type="button"
                                        @click="openEditModal(perm)"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-[#35449F] hover:bg-slate-100 transition cursor-pointer"
                                        title="Editar permiso"
                                    >
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>

                                    <button
                                        v-if="!perm.is_protected"
                                        type="button"
                                        @click="confirmDelete(perm)"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer"
                                        title="Eliminar permiso"
                                    >
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>

                            <tr v-if="!permissions.data.length">
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400 text-sm">
                                    No se encontraron permisos que coincidan con la búsqueda o filtros aplicados.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="px-6 bg-slate-50/50">
                    <Pagination :meta="permissions" />
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL: REGISTRAR NUEVO PERMISO             -->
        <!-- ========================================== -->
        <Modal :show="showCreateModal" @close="showCreateModal = false" max-width="2xl">
            <div class="p-6">
                <!-- Header del Modal -->
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">
                            Registrar Nuevo Permiso
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Crea un permiso para un módulo existente o registra uno para futuros módulos del sistema
                        </p>
                    </div>
                    <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 transition">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitCreate" class="mt-4 space-y-4">
                    <!-- Selector de Módulo (Actual vs Nuevo Módulo Futuro) -->
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/90 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-700 uppercase tracking-wider">
                                Módulo o Categoría *
                            </span>
                            <div class="flex items-center space-x-2 text-xs">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input
                                        type="checkbox"
                                        v-model="isNewModuleMode"
                                        @change="suggestSlug"
                                        class="rounded border-slate-300 text-[#35449F] focus:ring-[#35449F] size-3.5 mr-1.5"
                                    />
                                    <span class="font-semibold text-[#35449F]">¿Crear un módulo nuevo futuro?</span>
                                </label>
                            </div>
                        </div>

                        <!-- Selector de Módulo Existente -->
                        <div v-if="!isNewModuleMode">
                            <select
                                v-model="formCreate.modulo"
                                @change="suggestSlug"
                                class="w-full py-2 px-3 text-sm rounded-xl border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-[#35449F]"
                                required
                            >
                                <option v-for="m in modules" :key="m" :value="m">
                                    {{ m }}
                                </option>
                            </select>
                            <p class="text-[11px] text-slate-500 mt-1">
                                Selecciona el módulo al cual pertenece la funcionalidad.
                            </p>
                        </div>

                        <!-- Entrada de Nuevo Módulo Futuro -->
                        <div v-else>
                            <TextInput
                                v-model="customModuleName"
                                @input="suggestSlug"
                                type="text"
                                class="w-full"
                                placeholder="Ej: Biblioteca Digital, Finanzas, Eventos y Congresos..."
                                required
                            />
                            <p class="text-[11px] text-indigo-700 font-medium mt-1">
                                Ingresa el nombre del nuevo módulo. El permiso y futuros permisos quedarán automáticamente agrupados bajo este nuevo módulo.
                            </p>
                        </div>
                        <InputError :message="formCreate.errors.modulo" class="mt-1" />
                    </div>

                    <!-- Identificador Técnico (Slug) -->
                    <div>
                        <InputLabel for="create_perm_name" value="Identificador Técnico del Permiso *" />
                        <TextInput
                            id="create_perm_name"
                            v-model="formCreate.name"
                            type="text"
                            class="mt-1 block w-full font-mono text-sm"
                            placeholder="Ej: articulos.publicar, cursos.aprobar, biblioteca.descargar"
                            required
                        />
                        <p class="text-[11px] text-slate-500 mt-1">
                            Usa formato slug con puntos (ej: <code>modulo.accion</code>). Solo letras, números, puntos y guiones.
                        </p>
                        <InputError :message="formCreate.errors.name" class="mt-1" />
                    </div>

                    <!-- Descripción Legible -->
                    <div>
                        <InputLabel for="create_perm_desc" value="Descripción en Lenguaje Natural" />
                        <TextInput
                            id="create_perm_desc"
                            v-model="formCreate.description"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="Ej: Permite publicar y dar el visto bueno a los artículos en el portal público"
                        />
                        <InputError :message="formCreate.errors.description" class="mt-1" />
                    </div>

                    <!-- Asignación Inmediata a Roles -->
                    <div class="pt-2">
                        <InputLabel value="Asignar Inmediatamente a los Siguientes Roles:" class="mb-1.5" />
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            <label
                                v-for="r in roles"
                                :key="r"
                                class="flex items-center p-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer select-none text-xs"
                            >
                                <input
                                    type="checkbox"
                                    :value="r"
                                    v-model="formCreate.roles"
                                    class="rounded border-slate-300 text-[#35449F] focus:ring-[#35449F] size-3.5 mr-2"
                                />
                                <span class="capitalize font-semibold text-slate-800">{{ r }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                        <SecondaryButton @click="showCreateModal = false" :disabled="formCreate.processing">
                            Cancelar
                        </SecondaryButton>
                        <PrimaryButton :disabled="formCreate.processing" :class="{ 'opacity-25': formCreate.processing }">
                            Registrar Permiso
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- ========================================== -->
        <!-- MODAL: EDITAR PERMISO                      -->
        <!-- ========================================== -->
        <Modal :show="showEditModal" @close="showEditModal = false" max-width="2xl">
            <div class="p-6">
                <!-- Header del Modal -->
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-bold text-slate-900">
                                Editar Permiso: <code class="text-[#35449F] font-mono">{{ formEdit.name }}</code>
                            </h3>
                            <span
                                v-if="formEdit.is_protected"
                                class="text-[10px] font-bold text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200"
                            >
                                Permiso Base
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Modifica la categoría de módulo, descripción o roles asociados
                        </p>
                    </div>
                    <button @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 transition">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitEdit" class="mt-4 space-y-4">
                    <!-- Identificador Técnico -->
                    <div>
                        <InputLabel for="edit_perm_name" value="Identificador Técnico *" />
                        <TextInput
                            id="edit_perm_name"
                            v-model="formEdit.name"
                            type="text"
                            class="mt-1 block w-full font-mono text-sm"
                            :disabled="formEdit.is_protected"
                            :class="formEdit.is_protected ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : ''"
                            required
                        />
                        <p v-if="formEdit.is_protected" class="text-[11px] text-amber-600 mt-1">
                            El identificador de los permisos base del sistema no puede modificarse.
                        </p>
                        <InputError :message="formEdit.errors.name" class="mt-1" />
                    </div>

                    <!-- Módulo -->
                    <div>
                        <InputLabel for="edit_perm_modulo" value="Módulo o Categoría *" />
                        <TextInput
                            id="edit_perm_modulo"
                            v-model="formEdit.modulo"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="Ej: Artículos, Cursos, Biblioteca..."
                            required
                        />
                        <p class="text-[11px] text-slate-500 mt-1">
                            Puedes reasignarlo a cualquier módulo existente o escribir uno nuevo.
                        </p>
                        <InputError :message="formEdit.errors.modulo" class="mt-1" />
                    </div>

                    <!-- Descripción Legible -->
                    <div>
                        <InputLabel for="edit_perm_desc" value="Descripción en Lenguaje Natural" />
                        <TextInput
                            id="edit_perm_desc"
                            v-model="formEdit.description"
                            type="text"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="formEdit.errors.description" class="mt-1" />
                    </div>

                    <!-- Roles con este permiso -->
                    <div class="pt-2">
                        <InputLabel value="Roles Asignados:" class="mb-1.5" />
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            <label
                                v-for="r in roles"
                                :key="r"
                                class="flex items-center p-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer select-none text-xs"
                            >
                                <input
                                    type="checkbox"
                                    :value="r"
                                    v-model="formEdit.roles"
                                    class="rounded border-slate-300 text-[#35449F] focus:ring-[#35449F] size-3.5 mr-2"
                                />
                                <span class="capitalize font-semibold text-slate-800">{{ r }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                        <SecondaryButton @click="showEditModal = false" :disabled="formEdit.processing">
                            Cancelar
                        </SecondaryButton>
                        <PrimaryButton :disabled="formEdit.processing" :class="{ 'opacity-25': formEdit.processing }">
                            Actualizar Permiso
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- ========================================== -->
        <!-- MODAL: CONFIRMAR ELIMINACIÓN DE PERMISO    -->
        <!-- ========================================== -->
        <ConfirmationModal :show="confirmingDeletion" @close="confirmingDeletion = false">
            <template #title>
                Eliminar Permiso: <code class="text-rose-600 font-mono">{{ permissionToDelete?.name }}</code>
            </template>

            <template #content>
                <p class="text-sm text-slate-600">
                    ¿Estás seguro de que deseas eliminar este permiso del sistema? Se revocará de todos los roles a los que esté asignado actualmente.
                </p>
                <div v-if="permissionToDelete?.roles?.length" class="mt-3 p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-800">
                    <strong>Aviso:</strong> Este permiso está actualmente asignado a los roles:
                    <strong>{{ permissionToDelete.roles.join(', ') }}</strong>.
                </div>
            </template>

            <template #footer>
                <SecondaryButton @click="confirmingDeletion = false">
                    Cancelar
                </SecondaryButton>

                <DangerButton class="ml-3" @click="deletePermission">
                    Eliminar Permiso
                </DangerButton>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>
