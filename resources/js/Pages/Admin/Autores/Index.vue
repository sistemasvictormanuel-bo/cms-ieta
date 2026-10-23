<script setup>
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import Pagination from '@/Components/Pagination.vue';
import ExcelImportModal from '@/Components/ExcelImportModal.vue';
import IconButton from '@/Components/IconButton.vue';

const props = defineProps({
    autores: {
        type: [Array, Object],
        default: () => ({ data: [] }),
    },
    filters: {
        type: Object,
        default: () => ({ search: '', per_page: 10 }),
    },
});

const list = computed(() => {
    if (Array.isArray(props.autores)) {
        return props.autores;
    }
    return props.autores?.data || [];
});

const search = ref(props.filters?.search || '');
const perPage = ref(props.filters?.per_page || 10);
let searchTimeout = null;

const applyFilters = () => {
    router.get(
        route('admin.autores.index'),
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

    router.delete(route('admin.autores.destroy', itemToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            confirmingDeletion.value = false;
            itemToDelete.value = null;
        },
    });
};

const authorFullName = (item) => {
    if (item.persona?.nombre_completo) {
        return item.persona.nombre_completo;
    }
    if (item.persona) {
        return `${item.persona.p_nombre} ${item.persona.p_apellido_paterno || ''} ${item.persona.p_apellido_materno || ''}`.trim();
    }
    return 'Sin persona asignada';
};

const viewMode = ref(
    typeof window !== 'undefined' && localStorage.getItem('admin_view_mode_autores')
        ? localStorage.getItem('admin_view_mode_autores')
        : 'cards'
);

const setViewMode = (mode) => {
    viewMode.value = mode;
    if (typeof window !== 'undefined') {
        localStorage.setItem('admin_view_mode_autores', mode);
    }
};

const imgErrors = ref({});
const onImgError = (id) => {
    imgErrors.value[id] = true;
};

const getInitials = (persona) => {
    if (!persona) return 'A';
    const first = persona.p_nombre ? persona.p_nombre.trim().charAt(0).toUpperCase() : '';
    const last = persona.p_apellido_paterno ? persona.p_apellido_paterno.trim().charAt(0).toUpperCase() : '';
    return (first + last) || first || 'A';
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

const showImportModal = ref(false);

const autoresColumns = [
    { name: 'nombres', label: 'Nombres', required: true, desc: 'Nombres de pila del autor o investigador', ejemplo: 'Carlos Alberto' },
    { name: 'apellido_paterno', label: 'Apellido Paterno', required: true, desc: 'Primer apellido', ejemplo: 'Condori' },
    { name: 'apellido_materno', label: 'Apellido Materno', required: false, desc: 'Segundo apellido', ejemplo: 'Flores' },
    { name: 'correo', label: 'Correo Electrónico', required: false, desc: 'Email único institucional o personal', ejemplo: 'carlos.condori@umsa.bo' },
    { name: 'celular', label: 'Celular / Teléfono', required: false, desc: 'Número telefónico de contacto', ejemplo: '77712345' },
    { name: 'grado_academico', label: 'Grado Académico', required: false, desc: 'Nivel académico (Doctorado, Maestría, Licenciatura)', ejemplo: 'Doctorado' },
    { name: 'tipo_autor', label: 'Tipo de Autor', required: false, desc: 'Rol o categoría (Docente Investigador, Estudiante)', ejemplo: 'Docente Investigador' },
    { name: 'especialidad', label: 'Especialidad', required: false, desc: 'Área estadística o científica principal', ejemplo: 'Bioestadística' },
    { name: 'intereses_cientificos', label: 'Intereses Científicos', required: false, desc: 'Líneas de investigación o palabras clave', ejemplo: 'Epidemiología Matemática, Modelos Bayesianos' },
    { name: 'sitio_web', label: 'Sitio Web / Perfil', required: false, desc: 'Enlace a ORCID, Google Scholar o perfil profesional', ejemplo: 'https://orcid.org/0000-0001-2345-6789' },
    { name: 'biografia', label: 'Biografía', required: false, desc: 'Perfil biográfico o reseña académica del autor (opcional, se transforma a texto enriquecido)', ejemplo: 'Docente e investigador en Bioestadística con más de 10 años de experiencia académica.' },
];

const autoresSampleRows = [
    {
        nombres: 'Carlos Alberto',
        apellido_paterno: 'Condori',
        apellido_materno: 'Flores',
        correo: 'carlos.condori@umsa.bo',
        celular: '77712345',
        grado_academico: 'Doctorado',
        tipo_autor: 'Docente Investigador',
        especialidad: 'Bioestadística',
        intereses_cientificos: 'Bioestadística, Epidemiología Matemática, Modelos Bayesianos',
        sitio_web: 'https://orcid.org/0000-0001-2345-6789',
        biografia: 'Docente e investigador en el área de Bioestadística con más de 10 años de experiencia académica.',
    },
    {
        nombres: 'Elena Valeria',
        apellido_paterno: 'Rios',
        apellido_materno: 'Mendoza',
        correo: 'elena.rios@umsa.bo',
        celular: '72098765',
        grado_academico: 'Maestría',
        tipo_autor: 'Estudiante Investigador',
        especialidad: 'Ciencia de Datos',
        intereses_cientificos: 'Machine Learning, Procesamiento del Lenguaje Natural',
        sitio_web: 'https://linkedin.com/in/elena-rios',
        biografia: 'Investigadora especializada en modelos predictivos y análisis de datos estadísticos.',
    },
];

const exportUrl = computed(() => {
    const params = new URLSearchParams();
    if (search.value) params.append('search', search.value);
    const qs = params.toString();
    return route('admin.autores.export') + (qs ? `?${qs}` : '');
});
</script>

<template>
    <AppLayout title="Autores e Investigadores">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 w-full min-w-0">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-1.5 text-[11px] font-semibold text-slate-400 mb-0.5 leading-none">
                        <Link :href="route('dashboard')" class="hover:text-[#35449F]">Inicio</Link>
                        <span>/</span>
                        <span class="text-slate-700 font-bold">Investigadores</span>
                    </div>
                    <h1 class="font-extrabold text-xl sm:text-2xl text-[#14161C] tracking-tight truncate leading-tight">
                        Autores e Investigadores
                    </h1>
                </div>
                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    <!-- Exportar Excel -->
                    <a
                        :href="exportUrl"
                        class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-gray-700 bg-white hover:bg-gray-50 border border-gray-200 rounded-xl shadow-xs transition-colors whitespace-nowrap cursor-pointer"
                        download
                        title="Exportar directorio de autores a formato Excel"
                    >
                        <svg class="size-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span class="hidden sm:inline">Exportar Excel</span>
                        <span class="sm:hidden">Excel</span>
                    </a>

                    <!-- Importar Excel / Plantilla -->
                    <button
                        type="button"
                        @click="showImportModal = true"
                        class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-xl shadow-xs transition-colors whitespace-nowrap cursor-pointer"
                        title="Cargar investigadores masivos desde Excel o ver la plantilla en vivo"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        <span class="hidden sm:inline">Importar / Plantilla</span>
                        <span class="sm:hidden">Plantilla</span>
                    </button>

                    <!-- Nuevo Autor -->
                    <Link :href="route('admin.autores.create')">
                        <PrimaryButton class="cursor-pointer gap-1.5 whitespace-nowrap">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            <span>Nuevo Autor</span>
                        </PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="space-y-4 pb-12">
            <!-- Buscador y Filtros -->
            <div class="bg-white rounded-2xl p-3.5 sm:p-4 shadow-2xs border border-slate-200/90 space-y-3">
                <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input
                            v-model="search"
                            @input="onSearchInput"
                            type="text"
                            placeholder="Buscar por nombre, correo, slug o intereses científicos..."
                            class="w-full pl-10 pr-10 py-2.5 bg-slate-50/70 border border-slate-200 rounded-xl text-sm placeholder:text-slate-400 focus:bg-white focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 transition"
                        />
                        <button
                            v-if="search"
                            @click="clearSearch"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer"
                            title="Limpiar búsqueda"
                        >
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="flex items-center gap-2.5 shrink-0 flex-wrap justify-end">
                        <!-- Selector de Modo de Vista (Tarjetas / Tabla) -->
                        <div class="flex items-center bg-slate-100/90 p-1 rounded-xl border border-slate-200/80">
                            <button
                                type="button"
                                @click="setViewMode('cards')"
                                :class="viewMode === 'cards' ? 'bg-white text-[#35449F] shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
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
                                :class="viewMode === 'table' ? 'bg-white text-[#35449F] shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs transition cursor-pointer"
                                title="Vista en Tabla"
                            >
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                                <span class="hidden sm:inline">Tabla</span>
                            </button>
                        </div>

                        <!-- Selector de registros por página -->
                        <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200/90 rounded-xl px-2.5 py-1.5">
                            <label for="per_page" class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                                Ver:
                            </label>
                            <select
                                id="per_page"
                                v-model="perPage"
                                @change="applyFilters"
                                class="text-xs font-bold text-slate-800 bg-transparent border-0 p-0 pr-4 focus:ring-0 cursor-pointer"
                            >
                                <option :value="10">10</option>
                                <option :value="25">25</option>
                                <option :value="50">50</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Active Filter Chip si hay búsqueda -->
                <div v-if="search" class="pt-2 border-t border-slate-100 flex items-center gap-2 flex-wrap text-xs">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Filtro activo:</span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-indigo-50 text-[#35449F] font-semibold border border-indigo-100">
                        <span>Búsqueda: "{{ search }}"</span>
                        <button type="button" @click="clearSearch" class="hover:text-rose-600 cursor-pointer" title="Quitar búsqueda">
                            <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </span>
                    <button
                        type="button"
                        @click="clearSearch"
                        class="text-xs font-bold text-rose-600 hover:text-rose-800 transition underline underline-offset-2 ml-1 cursor-pointer"
                    >
                        Limpiar búsqueda
                    </button>
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
                    <p class="font-bold text-[#14161C] text-base">No hay autores registrados</p>
                    <p class="text-xs text-gray-500 mt-1">Prueba con otros términos de búsqueda o registra un nuevo autor.</p>
                </div>

                <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="(item, index) in list"
                        :key="item.id"
                        class="bg-white rounded-2xl p-6 shadow-xs border border-gray-200/90 hover:shadow-md hover:border-gray-300 transition-all duration-200 flex flex-col justify-between group"
                    >
                        <div>
                            <!-- Cabecera de Tarjeta: ID/Categoría y Estado -->
                            <div class="flex items-center justify-between gap-2 mb-4">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded-md text-[11px] font-mono font-bold">
                                        #{{ (props.autores?.from ? props.autores.from + index : index + 1) }}
                                    </span>
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-bold bg-indigo-50 text-[#35449F]">
                                        {{ item.tipo_autor?.ta_nombre || 'General' }}
                                    </span>
                                    <span
                                        v-if="item.persona?.user_id"
                                        class="px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60"
                                        title="Cuenta de acceso vinculada al sistema"
                                    >
                                        Con cuenta
                                    </span>
                                    <span
                                        v-else
                                        class="px-1.5 py-0.5 rounded-md text-[10px] font-medium bg-slate-100 text-slate-500"
                                        title="Sin cuenta de acceso (solo ficha)"
                                    >
                                        Sin cuenta
                                    </span>
                                </div>
                                <span
                                    :class="item.at_estado ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-gray-100 text-gray-600 ring-gray-400/20'"
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold ring-1 shrink-0"
                                >
                                    {{ item.at_estado ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>

                            <!-- Avatar Circular Prominente + Nombre -->
                            <div class="flex flex-col items-center text-center">
                                <div class="relative mb-3">
                                    <img
                                        v-if="item.persona?.foto_url && !imgErrors[item.id]"
                                        :src="item.persona.foto_url"
                                        :alt="authorFullName(item)"
                                        @error="onImgError(item.id)"
                                        class="w-20 h-20 rounded-full object-cover shadow-xs ring-4 ring-gray-50 border border-gray-200"
                                    />
                                    <div
                                        v-else
                                        :class="[getAvatarStyle(authorFullName(item)).bg, getAvatarStyle(authorFullName(item)).text]"
                                        class="w-20 h-20 rounded-full flex items-center justify-center text-2xl font-extrabold tracking-wider shadow-xs ring-4 ring-gray-50 select-none"
                                    >
                                        {{ getInitials(item.persona) }}
                                    </div>
                                </div>

                                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                                    Investigador / Autor
                                </span>
                                <h3 class="text-base font-extrabold text-[#14161C] tracking-tight leading-snug line-clamp-1 mt-0.5 w-full px-1" :title="authorFullName(item)">
                                    {{ authorFullName(item) }}
                                </h3>
                                <p class="text-xs text-gray-400 font-mono mt-0.5">
                                    @{{ item.at_slug }}
                                </p>
                            </div>

                            <!-- Bloque de datos académicos -->
                            <div class="border-t border-gray-100 pt-3.5 mt-4 space-y-2 text-xs">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-gray-400 font-medium">Grado:</span>
                                    <span class="font-bold text-[#35449F] bg-indigo-50/70 px-2 py-0.5 rounded text-[11px]">
                                        {{ item.grado_academico?.ga_abreviatura || item.grado_academico?.ga_nombre || 'S/G' }}
                                    </span>
                                </div>
                                <div class="flex items-start justify-between gap-2">
                                    <span class="text-gray-400 font-medium shrink-0 pt-0.5">Especialidad:</span>
                                    <div v-if="item.especialidades && item.especialidades.length > 0" class="flex flex-wrap justify-end gap-1 max-w-[190px]">
                                        <span
                                            v-for="esp in item.especialidades"
                                            :key="esp.id"
                                            class="font-semibold text-gray-700 bg-gray-50 border border-gray-200/80 px-1.5 py-0.5 rounded text-[11px]"
                                            :title="esp.ep_nombre"
                                        >
                                            {{ esp.ep_nombre }}
                                        </span>
                                    </div>
                                    <span v-else class="font-semibold text-gray-700 truncate max-w-[160px]" :title="item.especialidad?.ep_nombre">
                                        {{ item.especialidad?.ep_nombre || 'Sin especialidad' }}
                                    </span>
                                </div>
                                <div v-if="item.persona?.p_correo" class="flex items-center justify-between gap-2">
                                    <span class="text-gray-400 font-medium">Contacto:</span>
                                    <span class="text-gray-600 truncate max-w-[170px]" :title="item.persona.p_correo">
                                        {{ item.persona.p_correo }}
                                    </span>
                                </div>
                                <div v-if="item.at_intereses_cientificos" class="pt-1 text-[11px] text-gray-500 italic line-clamp-1 border-t border-dashed border-gray-100" :title="item.at_intereses_cientificos">
                                    "{{ item.at_intereses_cientificos }}"
                                </div>
                            </div>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="border-t border-slate-100 pt-3.5 mt-4 flex items-center justify-between gap-2">
                            <Link
                                :href="route('admin.autores.show', item.id)"
                                class="flex-1 text-center py-2 px-3 text-xs font-bold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 hover:border-slate-300 rounded-xl transition shadow-2xs"
                            >
                                Ver Perfil
                            </Link>
                            <IconButton
                                as="Link"
                                :href="route('admin.autores.edit', item.id)"
                                variant="warning"
                                size="sm"
                                title="Editar"
                            >
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </IconButton>
                            <IconButton
                                @click="confirmDelete(item)"
                                variant="danger"
                                size="sm"
                                title="Eliminar"
                            >
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </IconButton>
                        </div>
                    </div>
                </div>

                <!-- Paginación en modo Cards -->
                <div v-if="autores?.meta || autores?.links" class="p-4 bg-white rounded-2xl shadow-xs border border-gray-100">
                    <Pagination :meta="autores" />
                </div>
            </div>

            <!-- VISTA EN TABLA TRADICIONAL -->
            <div v-else class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-gray-600 font-extrabold uppercase text-xs tracking-wider border-b border-gray-100">
                            <tr>
                                <th scope="col" class="py-4 px-6 w-16 text-center">#</th>
                                <th scope="col" class="py-4 px-6">Autor / Investigador</th>
                                <th scope="col" class="py-4 px-6">Grado & Especialidad</th>
                                <th scope="col" class="py-4 px-6">Tipo</th>
                                <th scope="col" class="py-4 px-6 text-center">Estado</th>
                                <th scope="col" class="py-4 px-6 text-right w-36">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="list.length === 0">
                                <td colspan="6" class="py-12 text-center text-gray-400">
                                    <p class="font-bold text-[#14161C]">No hay autores registrados</p>
                                </td>
                            </tr>
                            <tr
                                v-for="(item, index) in list"
                                :key="item.id"
                                class="hover:bg-gray-50/70 transition"
                            >
                                <td class="py-4 px-6 font-mono text-xs text-gray-500 text-center font-bold">
                                    {{ (props.autores?.from ? props.autores.from + index : index + 1) }}
                                </td>
                                <td class="py-4 px-6">
                                    <div class="font-bold text-[#14161C]">
                                        {{ authorFullName(item) }}
                                    </div>
                                    <div class="text-xs text-gray-400 font-mono flex items-center gap-1.5 mt-0.5">
                                        <span>@{{ item.at_slug }}</span>
                                        <span v-if="item.persona?.p_correo" class="text-gray-300">•</span>
                                        <span v-if="item.persona?.p_correo" class="text-gray-500">{{ item.persona.p_correo }}</span>
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="font-semibold text-gray-800 text-xs flex flex-wrap items-center gap-1">
                                        <span class="px-1.5 py-0.5 bg-indigo-50 text-[#35449F] rounded font-bold">
                                            {{ item.grado_academico?.ga_abreviatura || item.grado_academico?.ga_nombre || 'S/G' }}
                                        </span>
                                        <template v-if="item.especialidades && item.especialidades.length > 0">
                                            <span
                                                v-for="esp in item.especialidades"
                                                :key="esp.id"
                                                class="px-1.5 py-0.5 bg-gray-100 text-gray-700 rounded text-[11px]"
                                            >
                                                {{ esp.ep_nombre }}
                                            </span>
                                        </template>
                                        <span v-else class="text-gray-600 truncate max-w-xs">
                                            {{ item.especialidad?.ep_nombre || 'Sin especialidad' }}
                                        </span>
                                    </div>
                                    <div v-if="item.at_intereses_cientificos" class="text-xs text-gray-400 truncate max-w-xs mt-0.5">
                                        {{ item.at_intereses_cientificos }}
                                    </div>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex flex-col items-start gap-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-gray-100 text-gray-700">
                                            {{ item.tipo_autor?.ta_nombre || 'General' }}
                                        </span>
                                        <span
                                            v-if="item.persona?.user_id"
                                            class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60"
                                            title="Cuenta vinculada"
                                        >
                                            Con cuenta
                                        </span>
                                        <span
                                            v-else
                                            class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-500"
                                            title="Solo ficha (sin cuenta)"
                                        >
                                            Sin cuenta
                                        </span>
                                    </div>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span
                                        :class="item.at_estado ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-gray-100 text-gray-600 ring-gray-400/20'"
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold ring-1"
                                    >
                                        {{ item.at_estado ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <IconButton
                                            as="Link"
                                            :href="route('admin.autores.show', item.id)"
                                            variant="primary"
                                            size="sm"
                                            title="Ver Perfil"
                                        >
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.264 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </IconButton>

                                        <IconButton
                                            as="Link"
                                            :href="route('admin.autores.edit', item.id)"
                                            variant="warning"
                                            size="sm"
                                            title="Editar"
                                        >
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </IconButton>

                                        <IconButton
                                            @click="confirmDelete(item)"
                                            variant="danger"
                                            size="sm"
                                            title="Eliminar"
                                        >
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </IconButton>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="autores?.meta || autores?.links" class="p-4 bg-gray-50/50">
                    <Pagination :meta="autores" />
                </div>
            </div>
        </div>

        <ConfirmationModal :show="confirmingDeletion" @close="confirmingDeletion = false">
            <template #title>Eliminar Autor</template>
            <template #content>
                ¿Estás seguro de que deseas eliminar al autor
                <span class="font-bold text-[#14161C]">"{{ authorFullName(itemToDelete || {}) }}"</span>?
            </template>
            <template #footer>
                <SecondaryButton @click="confirmingDeletion = false" class="mr-2 cursor-pointer">Cancelar</SecondaryButton>
                <DangerButton @click="deleteItem" class="cursor-pointer">Eliminar</DangerButton>
            </template>
        </ConfirmationModal>

        <!-- Modal de Importación Masiva y Plantilla en Vivo -->
        <ExcelImportModal
            :show="showImportModal"
            title="Importación Masiva de Autores e Investigadores"
            subtitle="Carga y actualiza investigadores mediante una planilla Excel. Los autores se registrarán como fichas y podrás habilitarles cuenta de acceso al sistema individualmente cuando lo desees."
            :plantilla-url="route('admin.autores.plantilla')"
            :import-url="route('admin.autores.import')"
            :columns="autoresColumns"
            :sample-rows="autoresSampleRows"
            @close="showImportModal = false"
        />
    </AppLayout>
</template>
