<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
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
    traducciones: {
        type: Object,
        required: true,
    },
    modulosWithCount: {
        type: Array,
        required: true,
    },
    totalTraducciones: {
        type: Number,
        default: 0,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const search = ref(props.filters.search || '');
const currentModulo = ref(props.filters.modulo || 'todos');
const perPage = ref(props.filters.per_page || 25);

const showCreateModal = ref(false);
const showEditModal = ref(false);
const confirmingDeletion = ref(false);
const itemToDelete = ref(null);

const formCreate = useForm({
    modulo: 'general',
    clave: '',
    texto_es: '',
    texto_en: '',
    descripcion: '',
});

const formEdit = useForm({
    id: null,
    modulo: '',
    clave: '',
    texto_es: '',
    texto_en: '',
    descripcion: '',
});

const copiedKey = ref('');
const copyToClipboard = (text) => {
    navigator.clipboard.writeText(text);
    copiedKey.value = text;
    setTimeout(() => {
        if (copiedKey.value === text) {
            copiedKey.value = '';
        }
    }, 2000);
};

let searchDebounceTimer = null;
const handleSearch = () => {
    clearTimeout(searchDebounceTimer);
    searchDebounceTimer = setTimeout(() => {
        applyFilters();
    }, 350);
};

const filterByModulo = (moduloName) => {
    currentModulo.value = moduloName;
    applyFilters();
};

const applyFilters = () => {
    router.get(
        route('admin.traducciones.index'),
        {
            search: search.value || undefined,
            modulo: currentModulo.value !== 'todos' ? currentModulo.value : undefined,
            per_page: perPage.value !== 25 ? perPage.value : undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
};

const resetFilters = () => {
    search.value = '';
    currentModulo.value = 'todos';
    applyFilters();
};

const openCreateModal = () => {
    formCreate.reset();
    formCreate.modulo = currentModulo.value !== 'todos' ? currentModulo.value : 'general';
    formCreate.clearErrors();
    showCreateModal.value = true;
};

const closeCreateModal = () => {
    showCreateModal.value = false;
    formCreate.reset();
};

const submitCreate = () => {
    formCreate.post(route('admin.traducciones.store'), {
        preserveScroll: true,
        onSuccess: () => {
            closeCreateModal();
        },
    });
};

const openEditModal = (item) => {
    formEdit.id = item.id;
    formEdit.modulo = item.modulo;
    formEdit.clave = item.clave;
    formEdit.texto_es = item.texto_es;
    formEdit.texto_en = item.texto_en;
    formEdit.descripcion = item.descripcion || '';
    formEdit.clearErrors();
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    formEdit.reset();
};

const submitEdit = () => {
    formEdit.put(route('admin.traducciones.update', formEdit.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeEditModal();
        },
    });
};

const confirmDeletion = (item) => {
    itemToDelete.value = item;
    confirmingDeletion.value = true;
};

const executeDeletion = () => {
    if (!itemToDelete.value) return;

    router.delete(route('admin.traducciones.destroy', itemToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            confirmingDeletion.value = false;
            itemToDelete.value = null;
        },
    });
};

const exportarJson = () => {
    window.location.href = route('admin.traducciones.export-json');
};

const formatModuleName = (name) => {
    const map = {
        'navegacion': 'Navegación & Menú',
        'hero': 'Portada & Hero',
        'general': 'General & Acciones',
        'revista': 'Revista Varianza',
        'banco_datos': 'Banco de Datos',
        'footer_contacto': 'Pie & Contacto',
        'investigacion': 'Investigación',
        'cursos': 'Cursos & Talleres',
    };
    return map[name] || name.charAt(0).toUpperCase() + name.slice(1);
};
</script>

<template>
    <div>
        <Head title="Traducciones Multilenguaje (ES/EN) - IETA" />

        <AppLayout>
            <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Encabezado Principal y Acciones -->
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
                    <div>
                        <div class="flex items-center space-x-3">
                            <span class="inline-flex items-center justify-center p-2.5 bg-indigo-50 text-indigo-600 rounded-xl">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                                </svg>
                            </span>
                            <div>
                                <h1 class="text-xl font-bold text-slate-900">
                                    Módulo de Traducciones Multilenguaje (ES / EN)
                                </h1>
                                <p class="text-sm text-slate-500 mt-0.5">
                                    Diccionario centralizado de textos consumidos por el sitio público y la API REST (<code class="text-xs bg-slate-100 text-indigo-600 px-1 py-0.5 rounded font-mono">/api/v1/traducciones?lang=en</code>)
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2.5">
                        <SecondaryButton
                            type="button"
                            @click="exportarJson"
                            class="inline-flex items-center space-x-2 text-xs font-bold"
                            title="Descargar diccionario completo en formato JSON"
                        >
                            <svg class="w-4 h-4 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span>Exportar JSON</span>
                        </SecondaryButton>

                        <PrimaryButton
                            type="button"
                            @click="openCreateModal"
                            class="inline-flex items-center space-x-2 text-xs font-bold"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>Nueva Traducción</span>
                        </PrimaryButton>
                    </div>
                </div>

                <!-- Pestañas de Módulos (Tabs) -->
                <div class="flex items-center space-x-2 overflow-x-auto pb-2 scrollbar-thin">
                    <button
                        type="button"
                        @click="filterByModulo('todos')"
                        :class="[
                            currentModulo === 'todos'
                                ? 'bg-[#35449F] text-white shadow-xs font-bold'
                                : 'bg-white text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-slate-200 font-medium'
                        ]"
                        class="px-4 py-2 rounded-xl text-xs transition cursor-pointer shrink-0 flex items-center space-x-2"
                    >
                        <span>Todos los Módulos</span>
                        <span
                            :class="currentModulo === 'todos' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'"
                            class="text-[10px] px-1.5 py-0.5 rounded-full font-bold"
                        >
                            {{ totalTraducciones }}
                        </span>
                    </button>

                    <button
                        v-for="item in modulosWithCount"
                        :key="item.modulo"
                        type="button"
                        @click="filterByModulo(item.modulo)"
                        :class="[
                            currentModulo === item.modulo
                                ? 'bg-[#35449F] text-white shadow-xs font-bold'
                                : 'bg-white text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-slate-200 font-medium'
                        ]"
                        class="px-3.5 py-2 rounded-xl text-xs transition cursor-pointer shrink-0 flex items-center space-x-2"
                    >
                        <span>{{ formatModuleName(item.modulo) }}</span>
                        <span
                            :class="currentModulo === item.modulo ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600'"
                            class="text-[10px] px-1.5 py-0.5 rounded-full font-bold"
                        >
                            {{ item.count }}
                        </span>
                    </button>
                </div>

                <!-- Barra de Filtros y Búsqueda -->
                <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input
                            v-model="search"
                            @input="handleSearch"
                            type="text"
                            placeholder="Buscar por clave, texto en español, inglés o descripción..."
                            class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#35449F]/20 focus:border-[#35449F] transition"
                        />
                    </div>

                    <div class="flex items-center space-x-2 shrink-0">
                        <select
                            v-model="perPage"
                            @change="applyFilters"
                            class="bg-slate-50 border border-slate-200 text-xs text-slate-700 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#35449F]/20 focus:border-[#35449F]"
                        >
                            <option :value="15">15 por pág.</option>
                            <option :value="25">25 por pág.</option>
                            <option :value="50">50 por pág.</option>
                            <option :value="100">100 por pág.</option>
                        </select>

                        <button
                            v-if="search || currentModulo !== 'todos'"
                            type="button"
                            @click="resetFilters"
                            class="px-3 py-2 text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-xl transition"
                        >
                            Restablecer
                        </button>
                    </div>
                </div>

                <!-- Tabla de Contenido -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-600">
                            <thead class="bg-slate-50/80 text-slate-700 font-bold uppercase tracking-wider text-[11px] border-b border-slate-200">
                                <tr>
                                    <th scope="col" class="py-3 px-4">Módulo / Clave</th>
                                    <th scope="col" class="py-3 px-4">Español (ES)</th>
                                    <th scope="col" class="py-3 px-4">Inglés (EN)</th>
                                    <th scope="col" class="py-3 px-4">Descripción / Contexto</th>
                                    <th scope="col" class="py-3 px-4 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr
                                    v-for="item in traducciones.data"
                                    :key="item.id"
                                    class="hover:bg-slate-50/60 transition group"
                                >
                                    <!-- Módulo y Clave -->
                                    <td class="py-3.5 px-4">
                                        <div class="space-y-1">
                                            <span class="inline-block px-2 py-0.5 text-[10px] font-bold bg-slate-100 text-slate-700 rounded-md">
                                                {{ item.modulo }}
                                            </span>
                                            <div class="flex items-center space-x-1.5">
                                                <code class="font-mono text-[11px] font-bold text-indigo-700 bg-indigo-50/60 px-1.5 py-0.5 rounded">
                                                    {{ item.clave }}
                                                </code>
                                                <button
                                                    type="button"
                                                    @click="copyToClipboard(item.clave)"
                                                    class="text-slate-400 hover:text-slate-600 transition"
                                                    :title="copiedKey === item.clave ? '¡Copiado!' : 'Copiar clave'"
                                                >
                                                    <svg v-if="copiedKey !== item.clave" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                    </svg>
                                                    <span v-else class="text-[10px] text-emerald-600 font-bold">✓</span>
                                                </button>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Español -->
                                    <td class="py-3.5 px-4 font-medium text-slate-900 max-w-xs">
                                        <div class="flex items-start space-x-2">
                                            <span class="text-xs shrink-0 select-none" title="Español">🇧🇴</span>
                                            <span class="line-clamp-2 leading-relaxed">{{ item.texto_es }}</span>
                                        </div>
                                    </td>

                                    <!-- Inglés -->
                                    <td class="py-3.5 px-4 font-medium text-slate-800 max-w-xs">
                                        <div class="flex items-start space-x-2">
                                            <span class="text-xs shrink-0 select-none" title="English">🇺🇸</span>
                                            <span class="line-clamp-2 leading-relaxed">{{ item.texto_en }}</span>
                                        </div>
                                    </td>

                                    <!-- Descripción -->
                                    <td class="py-3.5 px-4 text-slate-500 max-w-xs">
                                        <span class="line-clamp-2 leading-relaxed italic text-[11px]">
                                            {{ item.descripcion || '—' }}
                                        </span>
                                    </td>

                                    <!-- Acciones -->
                                    <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center space-x-1">
                                            <button
                                                type="button"
                                                @click="openEditModal(item)"
                                                class="p-1.5 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition"
                                                title="Editar traducción"
                                            >
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>

                                            <button
                                                type="button"
                                                @click="confirmDeletion(item)"
                                                class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition"
                                                title="Eliminar clave"
                                            >
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-if="!traducciones.data || traducciones.data.length === 0">
                                    <td colspan="5" class="py-12 text-center text-slate-400">
                                        <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                                        </svg>
                                        <p class="font-medium">No se encontraron claves de traducción con los filtros actuales.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div v-if="traducciones.links && traducciones.links.length > 3" class="p-4 border-t border-slate-100">
                        <Pagination :links="traducciones.links" />
                    </div>
                </div>
            </div>
        </AppLayout>

        <!-- Modal de Creación -->
        <Modal :show="showCreateModal" @close="closeCreateModal" max-width="lg">
            <form @submit.prevent="submitCreate" class="p-6">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <h2 class="text-base font-bold text-slate-900">
                        Registrar Nueva Clave de Traducción
                    </h2>
                    <button type="button" @click="closeCreateModal" class="text-slate-400 hover:text-slate-600">
                        ✕
                    </button>
                </div>

                <div class="space-y-4 mt-4 text-xs">
                    <div>
                        <InputLabel for="modulo" value="Módulo o Sección" />
                        <select
                            id="modulo"
                            v-model="formCreate.modulo"
                            class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#35449F]/20 focus:border-[#35449F]"
                        >
                            <option value="navegacion">Navegación & Menú</option>
                            <option value="hero">Portada & Hero</option>
                            <option value="general">General & Acciones</option>
                            <option value="revista">Revista Varianza</option>
                            <option value="banco_datos">Banco de Datos</option>
                            <option value="footer_contacto">Pie & Contacto</option>
                            <option value="investigacion">Investigación</option>
                            <option value="cursos">Cursos & Talleres</option>
                        </select>
                        <InputError :message="formCreate.errors.modulo" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="clave" value="Clave / Identificador (en minúsculas y sin espacios)" />
                        <TextInput
                            id="clave"
                            v-model="formCreate.clave"
                            type="text"
                            placeholder="ej: btn_descargar_articulo"
                            class="mt-1 w-full font-mono text-xs"
                            required
                        />
                        <InputError :message="formCreate.errors.clave" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="texto_es" value="Texto en Español (ES) 🇧🇴" />
                        <textarea
                            id="texto_es"
                            v-model="formCreate.texto_es"
                            rows="2"
                            class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#35449F]/20 focus:border-[#35449F]"
                            placeholder="Texto visible cuando el usuario selecciona Español"
                            required
                        ></textarea>
                        <InputError :message="formCreate.errors.texto_es" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="texto_en" value="Texto en Inglés (EN) 🇺🇸" />
                        <textarea
                            id="texto_en"
                            v-model="formCreate.texto_en"
                            rows="2"
                            class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#35449F]/20 focus:border-[#35449F]"
                            placeholder="Texto visible cuando el usuario activa el switch de Inglés"
                            required
                        ></textarea>
                        <InputError :message="formCreate.errors.texto_en" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="descripcion" value="Descripción / Contexto de uso (Opcional)" />
                        <TextInput
                            id="descripcion"
                            v-model="formCreate.descripcion"
                            type="text"
                            placeholder="ej: Botón visible en la ficha de artículo"
                            class="mt-1 w-full text-xs"
                        />
                        <InputError :message="formCreate.errors.descripcion" class="mt-1" />
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-2 pt-4 border-t border-slate-100">
                    <SecondaryButton type="button" @click="closeCreateModal" class="text-xs">
                        Cancelar
                    </SecondaryButton>
                    <PrimaryButton type="submit" :disabled="formCreate.processing" class="text-xs">
                        Guardar Traducción
                    </PrimaryButton>
                </div>
            </form>
        </Modal>

        <!-- Modal de Edición -->
        <Modal :show="showEditModal" @close="closeEditModal" max-width="lg">
            <form @submit.prevent="submitEdit" class="p-6">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <h2 class="text-base font-bold text-slate-900">
                        Editar Traducción: <span class="font-mono text-indigo-600">{{ formEdit.clave }}</span>
                    </h2>
                    <button type="button" @click="closeEditModal" class="text-slate-400 hover:text-slate-600">
                        ✕
                    </button>
                </div>

                <div class="space-y-4 mt-4 text-xs">
                    <div>
                        <InputLabel for="edit_modulo" value="Módulo o Sección" />
                        <select
                            id="edit_modulo"
                            v-model="formEdit.modulo"
                            class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#35449F]/20 focus:border-[#35449F]"
                        >
                            <option value="navegacion">Navegación & Menú</option>
                            <option value="hero">Portada & Hero</option>
                            <option value="general">General & Acciones</option>
                            <option value="revista">Revista Varianza</option>
                            <option value="banco_datos">Banco de Datos</option>
                            <option value="footer_contacto">Pie & Contacto</option>
                            <option value="investigacion">Investigación</option>
                            <option value="cursos">Cursos & Talleres</option>
                        </select>
                        <InputError :message="formEdit.errors.modulo" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="edit_clave" value="Clave" />
                        <TextInput
                            id="edit_clave"
                            v-model="formEdit.clave"
                            type="text"
                            class="mt-1 w-full font-mono text-xs"
                            required
                        />
                        <InputError :message="formEdit.errors.clave" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="edit_texto_es" value="Texto en Español (ES) 🇧🇴" />
                        <textarea
                            id="edit_texto_es"
                            v-model="formEdit.texto_es"
                            rows="3"
                            class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#35449F]/20 focus:border-[#35449F]"
                            required
                        ></textarea>
                        <InputError :message="formEdit.errors.texto_es" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="edit_texto_en" value="Texto en Inglés (EN) 🇺🇸" />
                        <textarea
                            id="edit_texto_en"
                            v-model="formEdit.texto_en"
                            rows="3"
                            class="mt-1 w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#35449F]/20 focus:border-[#35449F]"
                            required
                        ></textarea>
                        <InputError :message="formEdit.errors.texto_en" class="mt-1" />
                    </div>

                    <div>
                        <InputLabel for="edit_descripcion" value="Descripción / Contexto de uso" />
                        <TextInput
                            id="edit_descripcion"
                            v-model="formEdit.descripcion"
                            type="text"
                            class="mt-1 w-full text-xs"
                        />
                        <InputError :message="formEdit.errors.descripcion" class="mt-1" />
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-2 pt-4 border-t border-slate-100">
                    <SecondaryButton type="button" @click="closeEditModal" class="text-xs">
                        Cancelar
                    </SecondaryButton>
                    <PrimaryButton type="submit" :disabled="formEdit.processing" class="text-xs">
                        Actualizar Cambios
                    </PrimaryButton>
                </div>
            </form>
        </Modal>

        <!-- Modal de Confirmación de Eliminación -->
        <ConfirmationModal :show="confirmingDeletion" @close="confirmingDeletion = false">
            <template #title>
                Eliminar Clave de Traducción
            </template>
            <template #content>
                ¿Estás seguro de que deseas eliminar la traducción de
                <span class="font-mono font-bold text-rose-600">{{ itemToDelete?.clave }}</span>?
                Si el sitio público utiliza esta clave, se mostrará el valor por defecto o un texto vacío.
            </template>
            <template #footer>
                <SecondaryButton @click="confirmingDeletion = false" class="mr-2 text-xs">
                    Cancelar
                </SecondaryButton>
                <DangerButton @click="executeDeletion" class="text-xs">
                    Eliminar Traducción
                </DangerButton>
            </template>
        </ConfirmationModal>
    </div>
</template>

