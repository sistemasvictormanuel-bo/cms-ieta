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
    personal: {
        type: [Array, Object],
        default: () => ({ data: [] }),
    },
    filters: {
        type: Object,
        default: () => ({ search: '', estado: '', per_page: 10 }),
    },
});

const list = computed(() => {
    if (Array.isArray(props.personal)) {
        return props.personal;
    }
    return props.personal?.data || [];
});

const search = ref(props.filters?.search || '');
const estado = ref(props.filters?.estado ?? '');
const perPage = ref(props.filters?.per_page || 10);
let searchTimeout = null;

const applyFilters = () => {
    router.get(
        route('admin.personal.index'),
        {
            search: search.value ? search.value : undefined,
            estado: estado.value !== '' ? estado.value : undefined,
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

    router.delete(route('admin.personal.destroy', itemToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            confirmingDeletion.value = false;
            itemToDelete.value = null;
        },
    });
};

const personaFullName = (persona) => {
    if (!persona) return 'Sin persona';
    if (persona.nombre_completo) return persona.nombre_completo;
    return `${persona.p_nombre} ${persona.p_apellido_paterno || ''} ${persona.p_apellido_materno || ''}`.trim();
};

const viewMode = ref(
    typeof window !== 'undefined' && localStorage.getItem('admin_view_mode_personal')
        ? localStorage.getItem('admin_view_mode_personal')
        : 'cards'
);

const setViewMode = (mode) => {
    viewMode.value = mode;
    if (typeof window !== 'undefined') {
        localStorage.setItem('admin_view_mode_personal', mode);
    }
};

const imgErrors = ref({});
const onImgError = (id) => {
    imgErrors.value[id] = true;
};

const getInitials = (persona) => {
    if (!persona) return 'P';
    const first = persona.p_nombre ? persona.p_nombre.trim().charAt(0).toUpperCase() : '';
    const last = persona.p_apellido_paterno ? persona.p_apellido_paterno.trim().charAt(0).toUpperCase() : '';
    return (first + last) || first || 'P';
};

const avatarPalettes = [
    { bg: 'bg-[#C69255]', text: 'text-white' },
    { bg: 'bg-[#D9874D]', text: 'text-white' },
    { bg: 'bg-[#8EAA46]', text: 'text-white' },
    { bg: 'bg-[#A373A6]', text: 'text-white' },
    { bg: 'bg-[#3E8ED0]', text: 'text-white' },
    { bg: 'bg-[#E76F51]', text: 'text-white' },
    { bg: 'bg-[#2A9D8F]', text: 'text-white' },
    { bg: 'bg-[#35449F]', text: 'text-white' },
    { bg: 'bg-[#7E57C2]', text: 'text-white' },
    { bg: 'bg-[#D85A7F]', text: 'text-white' },
];

const getAvatarStyle = (name) => {
    if (!name) return avatarPalettes[0];
    let hash = 0;
    for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
    }
    const index = Math.abs(hash) % avatarPalettes.length;
    return avatarPalettes[index];
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
    <AppLayout title="Personal Institucional">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 w-full min-w-0">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-1.5 text-[11px] font-semibold text-slate-400 mb-0.5 leading-none">
                        <Link :href="route('dashboard')" class="hover:text-[#35449F]">Inicio</Link>
                        <span>/</span>
                        <span class="text-slate-700 font-bold">Equipo</span>
                    </div>
                    <h1 class="font-extrabold text-xl sm:text-2xl text-[#14161C] tracking-tight truncate leading-tight">
                        Personal del Instituto
                    </h1>
                </div>
                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    <Link :href="route('admin.personal.create')">
                        <PrimaryButton class="cursor-pointer gap-1.5 whitespace-nowrap">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            <span>Nuevo Personal</span>
                        </PrimaryButton>
                    </Link>
                </div>
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
                        placeholder="Buscar por nombre, cargo, teléfono o descripción..."
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

                <div class="flex items-center gap-3 shrink-0 flex-wrap">
                    <!-- Filtro por Estado -->
                    <div class="flex items-center gap-2">
                        <label for="estado_filter" class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Estado:
                        </label>
                        <select
                            id="estado_filter"
                            v-model="estado"
                            @change="applyFilters"
                            class="text-sm font-semibold rounded-xl border-gray-200 py-2 pl-3 pr-8 bg-gray-50 focus:bg-white focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10"
                        >
                            <option value="">Todos</option>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo / Retirado</option>
                        </select>
                    </div>

                    <!-- Selector de Modo de Vista (Tarjetas / Tabla) -->
                    <div class="flex items-center bg-gray-100 p-1 rounded-xl border border-gray-200/80">
                        <button
                            type="button"
                            @click="setViewMode('cards')"
                            :class="viewMode === 'cards' ? 'bg-white text-[#35449F] shadow-xs font-bold' : 'text-gray-500 hover:text-gray-800 font-medium'"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs transition cursor-pointer"
                            title="Vista en Tarjetas"
                        >
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                            <span class="hidden sm:inline">Tarjetas</span>
                        </button>
                        <button
                            type="button"
                            @click="setViewMode('table')"
                            :class="viewMode === 'table' ? 'bg-white text-[#35449F] shadow-xs font-bold' : 'text-gray-500 hover:text-gray-800 font-medium'"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs transition cursor-pointer"
                            title="Vista en Tabla"
                        >
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            <span class="hidden sm:inline">Tabla</span>
                        </button>
                    </div>

                    <!-- Mostrar por página -->
                    <div class="flex items-center gap-2">
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
            </div>

            <!-- VISTA EN TARJETAS (Estilo Cake) -->
            <div v-if="viewMode === 'cards'" class="space-y-6">
                <div v-if="list.length === 0" class="bg-white rounded-2xl p-12 text-center text-gray-400 border border-gray-100 shadow-xs">
                    <div class="size-16 mx-auto mb-3 rounded-full bg-gray-50 flex items-center justify-center text-gray-300">
                        <svg class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <p class="font-bold text-[#14161C] text-base">No hay personal registrado</p>
                    <p class="text-xs text-gray-500 mt-1">Prueba con otros términos de búsqueda o registra un nuevo miembro del equipo.</p>
                </div>

                <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="(item, index) in list"
                        :key="item.id"
                        class="bg-white rounded-2xl p-6 shadow-xs border border-gray-200/90 hover:shadow-md hover:border-gray-300 transition-all duration-200 flex flex-col justify-between group"
                    >
                        <div>
                            <!-- Cabecera de Tarjeta: ID y Estado -->
                            <div class="flex items-center justify-between gap-2 mb-4">
                                <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded-md text-[11px] font-mono font-bold">
                                    #{{ (props.personal?.from ? props.personal.from + index : index + 1) }}
                                </span>
                                <span
                                    :class="item.pers_estado ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-gray-100 text-gray-600 ring-gray-400/20'"
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold ring-1"
                                >
                                    {{ item.pers_estado ? 'Activo' : 'Inactivo / Retirado' }}
                                </span>
                            </div>

                            <!-- Avatar Circular Prominente + Nombre -->
                            <div class="flex flex-col items-center text-center">
                                <div class="relative mb-3">
                                    <img
                                        v-if="item.persona?.foto_url && !imgErrors[item.id]"
                                        :src="item.persona.foto_url"
                                        :alt="personaFullName(item.persona)"
                                        @error="onImgError(item.id)"
                                        class="w-20 h-20 rounded-full object-cover shadow-xs ring-4 ring-gray-50 border border-gray-200"
                                    />
                                    <div
                                        v-else
                                        :class="[getAvatarStyle(personaFullName(item.persona)).bg, getAvatarStyle(personaFullName(item.persona)).text]"
                                        class="w-20 h-20 rounded-full flex items-center justify-center text-2xl font-extrabold tracking-wider shadow-xs ring-4 ring-gray-50 select-none"
                                    >
                                        {{ getInitials(item.persona) }}
                                    </div>
                                </div>

                                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                                    Personal del Instituto
                                </span>
                                <h3 class="text-base font-extrabold text-[#14161C] tracking-tight leading-snug line-clamp-1 mt-0.5 w-full px-1" :title="personaFullName(item.persona)">
                                    {{ personaFullName(item.persona) }}
                                </h3>

                                <div class="mt-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-[#35449F]/10 text-[#35449F]">
                                        {{ item.cargo?.ci_nombre || 'Sin cargo asignado' }}
                                    </span>
                                </div>
                            </div>

                            <!-- Bloque de datos y contacto -->
                            <div class="border-t border-gray-100 pt-3.5 mt-4 space-y-2 text-xs">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-gray-400 font-medium">Ingreso:</span>
                                    <span class="font-semibold text-gray-700">
                                        {{ formatDate(item.pers_fecha_ingreso) }}
                                    </span>
                                </div>
                                <div v-if="item.pers_fecha_retiro" class="flex items-center justify-between gap-2">
                                    <span class="text-red-400 font-medium">Retiro:</span>
                                    <span class="font-semibold text-red-600">
                                        {{ formatDate(item.pers_fecha_retiro) }}
                                    </span>
                                </div>
                                <div v-if="item.persona?.p_correo" class="flex items-center justify-between gap-2">
                                    <span class="text-gray-400 font-medium">Email:</span>
                                    <span class="text-gray-600 truncate max-w-[170px]" :title="item.persona.p_correo">
                                        {{ item.persona.p_correo }}
                                    </span>
                                </div>
                                <div v-if="item.persona?.p_celular" class="flex items-center justify-between gap-2">
                                    <span class="text-gray-400 font-medium">Teléfono:</span>
                                    <span class="text-gray-600 font-mono">
                                        {{ item.persona.p_celular }}
                                    </span>
                                </div>
                                <div v-if="item.pers_descripcion" class="pt-1 text-[11px] text-gray-500 italic line-clamp-1 border-t border-dashed border-gray-100" :title="item.pers_descripcion">
                                    {{ item.pers_descripcion }}
                                </div>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="border-t border-gray-100 pt-3.5 mt-4 flex items-center justify-between gap-2">
                            <Link
                                :href="route('admin.personal.show', item.id)"
                                class="flex-1 text-center py-2 px-3 text-xs font-bold text-gray-700 bg-white hover:bg-gray-50 border border-gray-200 hover:border-gray-300 rounded-xl transition shadow-2xs"
                            >
                                Ver Perfil
                            </Link>
                            <Link
                                :href="route('admin.personal.edit', item.id)"
                                class="p-2 text-gray-500 hover:text-amber-600 hover:bg-amber-50 rounded-xl transition border border-gray-200 hover:border-amber-200"
                                title="Editar"
                            >
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </Link>
                            <button
                                @click="confirmDelete(item)"
                                type="button"
                                class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-xl transition border border-gray-200 hover:border-red-200 cursor-pointer"
                                title="Dar de baja o eliminar"
                            >
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Paginación en modo Cards -->
                <div v-if="personal?.meta || personal?.links" class="p-4 bg-white rounded-2xl shadow-xs border border-gray-100">
                    <Pagination :meta="personal" />
                </div>
            </div>

            <!-- VISTA EN TABLA TRADICIONAL -->
            <div v-else class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-600 font-extrabold uppercase text-xs tracking-wider border-b border-gray-100">
                            <tr>
                                <th scope="col" class="py-4 px-6 w-16 text-center">#</th>
                                <th scope="col" class="py-4 px-6">Personal</th>
                                <th scope="col" class="py-4 px-6">Cargo Institucional</th>
                                <th scope="col" class="py-4 px-6">Fechas (Ingreso - Retiro)</th>
                                <th scope="col" class="py-4 px-6 text-center">Estado</th>
                                <th scope="col" class="py-4 px-6 text-right w-36">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="list.length === 0">
                                <td colspan="6" class="py-12 text-center text-gray-400">
                                    <p class="font-bold text-[#14161C]">No hay personal registrado</p>
                                </td>
                            </tr>
                            <tr
                                v-for="(item, index) in list"
                                :key="item.id"
                                class="hover:bg-gray-50/70 transition"
                            >
                                <td class="py-4 px-6 font-mono text-xs text-gray-500 text-center font-bold">
                                    {{ (props.personal?.from ? props.personal.from + index : index + 1) }}
                                </td>
                                <td class="py-4 px-6">
                                    <div class="font-bold text-[#14161C]">
                                        {{ personaFullName(item.persona) }}
                                    </div>
                                    <div class="text-xs text-gray-400 mt-0.5 flex items-center gap-1.5">
                                        <span v-if="item.persona?.p_correo">{{ item.persona.p_correo }}</span>
                                        <span v-if="item.persona?.p_celular">• Tel: {{ item.persona.p_celular }}</span>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-[#35449F]/10 text-[#35449F]">
                                        {{ item.cargo?.ci_nombre || 'Sin cargo' }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-xs text-gray-500">
                                    <div><strong>Ingreso:</strong> {{ formatDate(item.pers_fecha_ingreso) }}</div>
                                    <div v-if="item.pers_fecha_retiro" class="text-red-500 mt-0.5">
                                        <strong>Retiro:</strong> {{ formatDate(item.pers_fecha_retiro) }}
                                    </div>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span
                                        :class="item.pers_estado ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-gray-100 text-gray-600 ring-gray-400/20'"
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold ring-1"
                                    >
                                        {{ item.pers_estado ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <Link
                                            :href="route('admin.personal.show', item.id)"
                                            class="p-2 text-gray-400 hover:text-[#35449F] hover:bg-indigo-50 rounded-lg transition"
                                            title="Ver"
                                        >
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.264 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </Link>
                                        <Link
                                            :href="route('admin.personal.edit', item.id)"
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

                <div v-if="personal?.meta || personal?.links" class="p-4 bg-gray-50/50">
                    <Pagination :meta="personal" />
                </div>
            </div>
        </div>

        <ConfirmationModal :show="confirmingDeletion" @close="confirmingDeletion = false">
            <template #title>Dar de Baja a Personal</template>
            <template #content>
                ¿Estás seguro de que deseas eliminar o dar de baja a
                <span class="font-bold text-[#14161C]">"{{ personaFullName(itemToDelete?.persona) }}"</span>?
            </template>
            <template #footer>
                <SecondaryButton @click="confirmingDeletion = false" class="mr-2 cursor-pointer">Cancelar</SecondaryButton>
                <DangerButton @click="deleteItem" class="cursor-pointer">Dar de Baja</DangerButton>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>
