<script setup>
import { ref, computed } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { slugify } from '@/Utils/slugify';

const props = defineProps({
    autores: {
        type: Array,
        default: () => [],
    },
    participaciones: {
        type: Array,
        default: () => [],
    },
    etiquetas: {
        type: Array,
        default: () => [],
    },
    tipos_articulos: {
        type: Array,
        default: () => [],
    },
    ediciones: {
        type: Array,
        default: () => [],
    },
    autor_actual: {
        type: Object,
        default: null,
    },
    es_autor: {
        type: Boolean,
        default: false,
    },
    puede_revisar: {
        type: Boolean,
        default: false,
    },
});

const page = usePage();

const isAutor = computed(() => {
    if (typeof props.es_autor === 'boolean' && props.es_autor) return true;
    const user = page.props.auth?.user;
    return !!(user?.is_autor && !user?.is_admin);
});

const isAdmin = computed(() => !isAutor.value);

const todayFormatted = computed(() => {
    return new Date().toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' });
});

const form = useForm({
    tipo_articulo_id: props.tipos_articulos?.[0]?.id || '',
    edicion_varianza_id: props.ediciones?.[0]?.id || '',
    art_pag_inicio: '',
    art_pag_fin: '',
    art_titulo: '',
    art_slug: '',
    etiquetas: [],
    art_resumen: '',
    art_abstracto: '',
    art_fecha_recepcion: new Date().toISOString().slice(0, 10),
    art_fecha_aceptado: '',
    art_doi: '',
    art_orcid: '',
    art_num_paginas: '',
    art_referencias: '',
    art_estado: true,
    art_archivo: null,
    autores: [
        { autor_id: '', tipo_participaciones_id: '' },
    ],
});

const isSlugManuallyEdited = ref(false);
const fileName = ref('');

// Tipos de artículos disponibles y creación rápida
const availableTipos = ref([...props.tipos_articulos]);
const showQuickTipoInput = ref(false);
const newTipoNombre = ref('');
const creatingQuickTipo = ref(false);

const handleQuickTipoCreate = async () => {
    if (!newTipoNombre.value.trim()) return;
    creatingQuickTipo.value = true;
    try {
        const response = await axios.post(route('admin.tipo-articulos.store-rapida'), {
            tar_nombre: newTipoNombre.value.trim(),
        });
        const created = response.data;
        availableTipos.value.push(created);
        form.tipo_articulo_id = created.id;
        newTipoNombre.value = '';
        showQuickTipoInput.value = false;
    } catch (error) {
        alert(error.response?.data?.message || 'Error al crear el tipo de artículo.');
    } finally {
        creatingQuickTipo.value = false;
    }
};

const onPaginasRangeChange = () => {
    const inicio = parseInt(form.art_pag_inicio);
    const fin = parseInt(form.art_pag_fin);
    if (!isNaN(inicio) && !isNaN(fin) && fin >= inicio) {
        form.art_num_paginas = (fin - inicio) + 1;
    }
};

// Gestión de etiquetas disponibles y búsqueda
const availableEtiquetas = ref([...props.etiquetas]);
const tagSearch = ref('');
const showQuickTagInput = ref(false);
const newTagName = ref('');
const creatingQuickTag = ref(false);

const filteredEtiquetas = computed(() => {
    if (!tagSearch.value.trim()) return availableEtiquetas.value;
    const term = tagSearch.value.toLowerCase().trim();
    return availableEtiquetas.value.filter(t =>
        t.ea_nombre.toLowerCase().includes(term) || t.ea_slug.toLowerCase().includes(term)
    );
});

const toggleEtiqueta = (id) => {
    const idx = form.etiquetas.indexOf(id);
    if (idx > -1) {
        form.etiquetas.splice(idx, 1);
    } else {
        form.etiquetas.push(id);
    }
};

const removeEtiqueta = (id) => {
    const idx = form.etiquetas.indexOf(id);
    if (idx > -1) {
        form.etiquetas.splice(idx, 1);
    }
};

const getEtiquetaObj = (id) => {
    return availableEtiquetas.value.find(t => t.id === id);
};

const handleQuickTagCreate = async () => {
    if (!newTagName.value.trim()) return;
    creatingQuickTag.value = true;
    try {
        const response = await axios.post(route('admin.etiquetas-art.store-rapida'), {
            ea_nombre: newTagName.value.trim(),
            ea_slug: slugify(newTagName.value.trim()),
        });
        const created = response.data;
        availableEtiquetas.value.push(created);
        form.etiquetas.push(created.id);
        newTagName.value = '';
        showQuickTagInput.value = false;
    } catch (error) {
        alert(error.response?.data?.message || 'Error al crear la etiqueta rápida.');
    } finally {
        creatingQuickTag.value = false;
    }
};

// Generación de Slug en Vivo
const onTituloInput = () => {
    if (!isSlugManuallyEdited.value) {
        form.art_slug = slugify(form.art_titulo);
    }
};

const onSlugInput = () => {
    isSlugManuallyEdited.value = true;
};

const resetSlugToAuto = () => {
    isSlugManuallyEdited.value = false;
    form.art_slug = slugify(form.art_titulo);
};

const handleFileChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.art_archivo = file;
        fileName.value = file.name;
    } else {
        form.art_archivo = null;
        fileName.value = '';
    }
};

const addAuthorRow = () => {
    form.autores.push({ autor_id: '', tipo_participaciones_id: '' });
};

const removeAuthorRow = (index) => {
    if (form.autores.length > 1) {
        form.autores.splice(index, 1);
    } else {
        form.autores[0] = { autor_id: '', tipo_participaciones_id: '' };
    }
};

const authorFullName = (autor) => {
    if (autor?.persona?.nombre_completo) {
        const grado = autor.grado_academico?.ga_abreviatura ? `[${autor.grado_academico.ga_abreviatura}] ` : '';
        return `${grado}${autor.persona.nombre_completo}`;
    }
    return `Autor #${autor.id}`;
};

const submit = () => {
    const autoresFiltrados = form.autores.filter(a => a.autor_id);
    form.transform((data) => ({
        ...data,
        autores: autoresFiltrados,
    })).post(route('admin.articulos.store'), {
        forceFormData: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout title="Nuevo Artículo Científico">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 w-full">
                <div>
                    <h1 class="font-extrabold text-xl sm:text-2xl text-[#14161C] tracking-tight">
                        Nuevo Artículo Científico
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500 font-medium">
                        Crea una publicación científica con etiquetas normalizadas (N:M), slug automático en vivo y autores.
                    </p>
                </div>
                <div class="shrink-0">
                    <Link :href="route('admin.articulos.index')">
                        <SecondaryButton class="cursor-pointer">
                            ← Volver al listado
                        </SecondaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="max-w-5xl mx-auto pb-12">
            <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xs border border-gray-100">
                <form @submit.prevent="submit" class="space-y-8">
                    <!-- Sección 1: Información Básica del Artículo -->
                    <div class="space-y-6">
                        <h2 class="text-base font-extrabold text-[#14161C] pb-2 border-b border-gray-100 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-[#35449F]/10 text-[#35449F] flex items-center justify-center text-xs font-bold">1</span>
                            Información Principal & Metadatos
                        </h2>

                        <!-- Sección: Revista Varianza & Clasificación Científica -->
                        <div class="p-5 rounded-2xl bg-blue-50/50 border border-blue-100 space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="size-2 rounded-full bg-[#35449F]"></span>
                                    <h3 class="text-sm font-extrabold text-[#14161C]">Revista Varianza & Clasificación Científica</h3>
                                </div>
                                <span class="text-[10px] uppercase font-bold text-[#35449F] bg-blue-100 px-2 py-0.5 rounded">
                                    Jerarquía Editorial
                                </span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Edición de la Revista -->
                                <div>
                                    <InputLabel for="edicion_varianza_id" value="Edición de Revista Varianza" class="text-xs font-bold text-slate-700" />
                                    <select
                                        id="edicion_varianza_id"
                                        v-model="form.edicion_varianza_id"
                                        class="mt-1 block w-full text-sm border-gray-200 rounded-xl focus:border-[#35449F] focus:ring-[#35449F]/20 bg-white"
                                    >
                                        <option value="">-- Sin asignar a una edición --</option>
                                        <option
                                            v-for="ed in ediciones"
                                            :key="ed.id"
                                            :value="ed.id"
                                        >
                                            Nº {{ ed.ev_numero }} ({{ ed.ev_anio }}) - {{ ed.ev_titulo }}
                                        </option>
                                    </select>
                                    <InputError :message="form.errors.edicion_varianza_id" class="mt-1" />
                                </div>

                                <!-- Tipo de Artículo con + Crear Rápido -->
                                <div>
                                    <div class="flex items-center justify-between">
                                        <InputLabel for="tipo_articulo_id" value="Tipo de Artículo Científico *" class="text-xs font-bold text-slate-700" />
                                        <button
                                            type="button"
                                            @click="showQuickTipoInput = !showQuickTipoInput"
                                            class="text-xs font-bold text-[#35449F] hover:underline cursor-pointer"
                                        >
                                            + Rápido
                                        </button>
                                    </div>
                                    <select
                                        id="tipo_articulo_id"
                                        v-model="form.tipo_articulo_id"
                                        class="mt-1 block w-full text-sm border-gray-200 rounded-xl focus:border-[#35449F] focus:ring-[#35449F]/20 bg-white font-semibold"
                                        required
                                    >
                                        <option value="">-- Seleccionar tipo aprobado --</option>
                                        <option
                                            v-for="tipo in availableTipos"
                                            :key="tipo.id"
                                            :value="tipo.id"
                                        >
                                            {{ tipo.tar_nombre }}
                                        </option>
                                    </select>
                                    <InputError :message="form.errors.tipo_articulo_id" class="mt-1" />

                                    <!-- Input de Creación Rápida de Tipo de Artículo -->
                                    <div v-if="showQuickTipoInput" class="mt-2 p-2.5 bg-white rounded-xl border border-blue-200 flex items-center gap-2 shadow-xs">
                                        <input
                                            v-model="newTipoNombre"
                                            type="text"
                                            placeholder="Ej: Artículo de Revisión..."
                                            class="flex-1 text-xs px-2.5 py-1.5 border border-slate-200 rounded-lg focus:border-[#35449F]"
                                            @keydown.enter.prevent="handleQuickTipoCreate"
                                        />
                                        <button
                                            @click="handleQuickTipoCreate"
                                            :disabled="creatingQuickTipo || !newTipoNombre.trim()"
                                            type="button"
                                            class="px-2.5 py-1.5 bg-[#35449F] text-white text-xs font-bold rounded-lg hover:bg-blue-900 transition disabled:opacity-50 cursor-pointer"
                                        >
                                            {{ creatingQuickTipo ? '...' : 'Crear' }}
                                        </button>
                                        <button
                                            @click="showQuickTipoInput = false"
                                            type="button"
                                            class="text-xs text-slate-400 hover:text-slate-600 px-1 cursor-pointer"
                                        >
                                            ✕
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Rango de Páginas en la Edición -->
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 pt-2 border-t border-blue-100/60">
                                <div>
                                    <InputLabel for="art_pag_inicio" value="Pág. Inicio" class="text-[11px] font-bold text-slate-600 uppercase" />
                                    <TextInput
                                        id="art_pag_inicio"
                                        v-model="form.art_pag_inicio"
                                        @input="onPaginasRangeChange"
                                        type="number"
                                        min="1"
                                        class="mt-1 block w-full text-xs text-center font-bold"
                                        placeholder="Ej: 15"
                                    />
                                    <InputError :message="form.errors.art_pag_inicio" class="mt-1" />
                                </div>
                                <div>
                                    <InputLabel for="art_pag_fin" value="Pág. Fin" class="text-[11px] font-bold text-slate-600 uppercase" />
                                    <TextInput
                                        id="art_pag_fin"
                                        v-model="form.art_pag_fin"
                                        @input="onPaginasRangeChange"
                                        type="number"
                                        min="1"
                                        class="mt-1 block w-full text-xs text-center font-bold"
                                        placeholder="Ej: 28"
                                    />
                                    <InputError :message="form.errors.art_pag_fin" class="mt-1" />
                                </div>
                                <div class="col-span-2 sm:col-span-1 flex items-center pt-5 text-xs text-slate-500">
                                    <span v-if="form.art_pag_inicio && form.art_pag_fin" class="font-semibold text-emerald-700 bg-emerald-50 px-2 py-1 rounded-lg border border-emerald-200">
                                        ✓ pp. {{ form.art_pag_inicio }} - {{ form.art_pag_fin }}
                                    </span>
                                    <span v-else class="text-[11px] italic text-slate-400">
                                        Rango dentro de la revista
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Título -->
                        <div>
                            <InputLabel for="art_titulo" value="Título del Artículo *" />
                            <TextInput
                                id="art_titulo"
                                v-model="form.art_titulo"
                                @input="onTituloInput"
                                type="text"
                                class="mt-1 block w-full text-base font-semibold"
                                placeholder="Ej: Análisis del impacto de la inteligencia artificial en la automatización de procesos agrícolas"
                                required
                                autofocus
                            />
                            <InputError :message="form.errors.art_titulo" class="mt-1.5" />
                        </div>

                        <!-- Slug en Vivo -->
                        <div>
                            <div class="flex items-center justify-between">
                                <InputLabel for="art_slug" value="Slug / URL Amigable (Generado en Vivo)" />
                                <button
                                    v-if="isSlugManuallyEdited"
                                    @click="resetSlugToAuto"
                                    type="button"
                                    class="text-[11px] font-bold text-[#35449F] hover:underline cursor-pointer"
                                >
                                    ⚡ Regenerar automáticamente desde título
                                </button>
                            </div>
                            <div class="mt-1 flex rounded-xl border border-gray-200 focus-within:border-purple-500 focus-within:ring-2 focus-within:ring-purple-200/50 overflow-hidden bg-white transition shadow-2xs">
                                <span class="inline-flex items-center px-3.5 bg-purple-50/80 text-purple-700 text-xs font-mono font-bold border-r border-gray-200 select-none shrink-0">
                                    /articulos/
                                </span>
                                <input
                                    id="art_slug"
                                    v-model="form.art_slug"
                                    @input="onSlugInput"
                                    type="text"
                                    class="flex-1 block w-full border-0 focus:ring-0 text-sm font-mono text-purple-900 px-3 py-2.5 bg-white"
                                    placeholder="analisis-impacto-ia-procesos-agricolas"
                                />
                            </div>
                            <p class="text-[11px] text-gray-500 mt-1 font-medium">
                                Se genera automáticamente en vivo mientras escribes el título. Puedes ajustarlo manualmente si lo deseas.
                            </p>
                            <InputError :message="form.errors.art_slug" class="mt-1.5" />
                        </div>

                        <!-- Selector Normalizado de Etiquetas N:M -->
                        <div class="p-5 rounded-2xl bg-gray-50/70 border border-gray-100 space-y-4">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                                <div>
                                    <InputLabel value="Etiquetas Temáticas (N:M) *" class="text-sm font-bold text-gray-800" />
                                    <p class="text-xs text-gray-500 font-medium">
                                        Selecciona las etiquetas que categorizan este artículo. Fundamental para el cálculo de artículos similares.
                                    </p>
                                </div>
                                <button
                                    @click="showQuickTagInput = !showQuickTagInput"
                                    type="button"
                                    class="text-xs font-bold text-[#35449F] hover:underline flex items-center gap-1 cursor-pointer"
                                >
                                    <span class="text-base font-black leading-none">+</span>
                                    <span>Nueva Etiqueta Rápida</span>
                                </button>
                            </div>

                            <!-- Input para creación rápida de etiqueta -->
                            <div v-if="showQuickTagInput" class="p-3 bg-white rounded-xl border border-indigo-100 flex items-center gap-2 shadow-xs">
                                <input
                                    v-model="newTagName"
                                    type="text"
                                    placeholder="Nombre de la nueva etiqueta (ej: Bioestadística)..."
                                    class="flex-1 text-xs px-3 py-2 border border-gray-200 rounded-lg focus:border-[#35449F] focus:ring-1 focus:ring-[#35449F]"
                                    @keydown.enter.prevent="handleQuickTagCreate"
                                />
                                <button
                                    @click="handleQuickTagCreate"
                                    :disabled="creatingQuickTag || !newTagName.trim()"
                                    type="button"
                                    class="px-3 py-2 bg-[#35449F] text-white text-xs font-bold rounded-lg hover:bg-indigo-900 transition disabled:opacity-50 cursor-pointer"
                                >
                                    {{ creatingQuickTag ? 'Guardando...' : 'Crear y Asignar' }}
                                </button>
                                <button
                                    @click="showQuickTagInput = false"
                                    type="button"
                                    class="text-xs text-gray-400 hover:text-gray-600 px-2 cursor-pointer"
                                >
                                    Cancelar
                                </button>
                            </div>

                            <!-- Etiquetas seleccionadas activas -->
                            <div v-if="form.etiquetas.length > 0" class="flex flex-wrap gap-1.5 pt-1">
                                <span class="text-xs font-bold text-gray-400 self-center mr-1">Seleccionadas:</span>
                                <span
                                    v-for="tagId in form.etiquetas"
                                    :key="tagId"
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-[#35449F] text-white shadow-xs"
                                >
                                    #{{ getEtiquetaObj(tagId)?.ea_nombre || `Tag #${tagId}` }}
                                    <button
                                        @click="removeEtiqueta(tagId)"
                                        type="button"
                                        class="hover:text-red-200 cursor-pointer font-black text-sm leading-none"
                                        title="Quitar etiqueta"
                                    >
                                        ×
                                    </button>
                                </span>
                            </div>
                            <div v-else class="text-xs text-amber-700 bg-amber-50/80 px-3 py-2 rounded-xl border border-amber-200/50 flex items-center gap-2">
                                <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <span>No has seleccionado ninguna etiqueta. Selecciona al menos una del catálogo a continuación.</span>
                            </div>

                            <!-- Buscador y catálogo de etiquetas para seleccionar -->
                            <div class="space-y-2 pt-2 border-t border-gray-200/60">
                                <input
                                    v-model="tagSearch"
                                    type="text"
                                    placeholder="Filtrar catálogo de etiquetas..."
                                    class="w-full text-xs px-3 py-1.5 border border-gray-200 rounded-lg bg-white focus:border-[#35449F]"
                                />
                                <div class="flex flex-wrap gap-1.5 max-h-36 overflow-y-auto p-1">
                                    <button
                                        v-for="t in filteredEtiquetas"
                                        :key="t.id"
                                        @click="toggleEtiqueta(t.id)"
                                        type="button"
                                        :class="[
                                            form.etiquetas.includes(t.id)
                                                ? 'bg-indigo-100 text-[#35449F] font-bold border-[#35449F]'
                                                : 'bg-white text-gray-700 hover:bg-gray-100 border-gray-200 font-medium'
                                        ]"
                                        class="px-2.5 py-1 rounded-lg text-xs border transition cursor-pointer flex items-center gap-1"
                                    >
                                        <span>#{{ t.ea_nombre }}</span>
                                        <span v-if="form.etiquetas.includes(t.id)" class="text-[#35449F] font-bold">✓</span>
                                    </button>
                                </div>
                            </div>
                            <InputError :message="form.errors.etiquetas" class="mt-1.5" />
                        </div>

                        <!-- Fechas e Identificadores -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                            <!-- Fecha de Recepción -->
                            <div>
                                <InputLabel for="art_fecha_recepcion" value="Fecha Recepción *" />
                                <div v-if="isAdmin">
                                    <TextInput
                                        id="art_fecha_recepcion"
                                        v-model="form.art_fecha_recepcion"
                                        type="date"
                                        class="mt-1 block w-full text-sm"
                                        required
                                    />
                                    <InputError :message="form.errors.art_fecha_recepcion" class="mt-1.5" />
                                </div>
                                <div v-else class="mt-1">
                                    <div class="flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-800 text-sm font-semibold">
                                        <svg class="size-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                        </svg>
                                        <span>{{ todayFormatted }}</span>
                                    </div>
                                    <p class="text-[11px] text-slate-400 mt-1">Asignada automáticamente con la fecha de envío.</p>
                                </div>
                            </div>

                            <!-- Fecha de Aceptación (Exclusiva para Administradores / Revisores) -->
                            <div v-if="isAdmin">
                                <InputLabel for="art_fecha_aceptado" value="Fecha Aceptación" />
                                <TextInput
                                    id="art_fecha_aceptado"
                                    v-model="form.art_fecha_aceptado"
                                    type="date"
                                    class="mt-1 block w-full text-sm"
                                />
                                <InputError :message="form.errors.art_fecha_aceptado" class="mt-1.5" />
                            </div>

                            <div>
                                <InputLabel for="art_doi" value="Identificador DOI" />
                                <TextInput
                                    id="art_doi"
                                    v-model="form.art_doi"
                                    type="text"
                                    class="mt-1 block w-full text-sm"
                                    placeholder="10.1234/journal.2026.01"
                                />
                                <InputError :message="form.errors.art_doi" class="mt-1.5" />
                            </div>

                            <div>
                                <InputLabel for="art_orcid" value="Identificador ORCID" />
                                <TextInput
                                    id="art_orcid"
                                    v-model="form.art_orcid"
                                    type="text"
                                    class="mt-1 block w-full text-sm"
                                    placeholder="0000-0002-1825-0097"
                                />
                                <InputError :message="form.errors.art_orcid" class="mt-1.5" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="art_num_paginas" value="Número de Páginas" />
                                <TextInput
                                    id="art_num_paginas"
                                    v-model="form.art_num_paginas"
                                    type="number"
                                    min="1"
                                    class="mt-1 block w-full text-sm"
                                    placeholder="Ej: 14"
                                />
                                <InputError :message="form.errors.art_num_paginas" class="mt-1.5" />
                            </div>

                            <div class="flex items-center pt-6">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input
                                        type="checkbox"
                                        v-model="form.art_estado"
                                        class="sr-only peer"
                                    />
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:width-5 after:transition-all peer-checked:bg-[#35449F]"></div>
                                    <span class="ml-3 text-sm font-semibold text-gray-700">
                                        Artículo Publicado / Visible
                                    </span>
                                </label>
                                <InputError :message="form.errors.art_estado" class="mt-1.5" />
                            </div>
                        </div>

                        <!-- Resumen y Abstract -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <InputLabel for="art_resumen" value="Resumen (Español)" />
                                <textarea
                                    id="art_resumen"
                                    v-model="form.art_resumen"
                                    rows="5"
                                    class="mt-1 block w-full rounded-xl border-gray-200 p-3 text-sm focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 bg-gray-50/50"
                                    placeholder="Resumen del artículo en español..."
                                />
                                <InputError :message="form.errors.art_resumen" class="mt-1.5" />
                            </div>

                            <div>
                                <InputLabel for="art_abstracto" value="Abstract (Inglés)" />
                                <textarea
                                    id="art_abstracto"
                                    v-model="form.art_abstracto"
                                    rows="5"
                                    class="mt-1 block w-full rounded-xl border-gray-200 p-3 text-sm focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 bg-gray-50/50"
                                    placeholder="Abstract in English..."
                                />
                                <InputError :message="form.errors.art_abstracto" class="mt-1.5" />
                            </div>
                        </div>

                        <!-- Referencias Bibliográficas -->
                        <div>
                            <InputLabel for="art_referencias" value="Referencias Bibliográficas" />
                            <textarea
                                id="art_referencias"
                                v-model="form.art_referencias"
                                rows="4"
                                class="mt-1 block w-full rounded-xl border-gray-200 p-3 text-sm focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 bg-gray-50/50 font-mono text-xs"
                                placeholder="1. Smith, J. (2025). AI and Agriculture...&#10;2. González, M. (2024). Modelos econométricos aplicados..."
                            />
                            <InputError :message="form.errors.art_referencias" class="mt-1.5" />
                        </div>
                    </div>

                    <!-- Sección 2: Autores Participantes -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                            <h2 class="text-base font-extrabold text-[#14161C] flex items-center gap-2">
                                <span class="w-6 h-6 rounded-lg bg-[#35449F]/10 text-[#35449F] flex items-center justify-center text-xs font-bold">2</span>
                                Autores y Tipos de Participación
                            </h2>
                            <button
                                type="button"
                                @click="addAuthorRow"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-bold bg-[#35449F]/10 text-[#35449F] hover:bg-[#35449F]/20 transition cursor-pointer"
                            >
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Agregar Autor
                            </button>
                        </div>

                        <div class="space-y-3">
                            <div
                                v-for="(authorRow, index) in form.autores"
                                :key="index"
                                class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 p-4 rounded-xl bg-gray-50/50 border border-gray-100"
                            >
                                <div class="flex-1 w-full">
                                    <InputLabel :value="`Autor #${index + 1} *`" class="text-xs text-gray-500 mb-1" />
                                    <select
                                        v-model="authorRow.autor_id"
                                        class="w-full text-sm font-semibold rounded-xl border-gray-200 py-2 px-3 bg-white focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10"
                                    >
                                        <option value="">-- Seleccionar Autor --</option>
                                        <option
                                            v-for="autor in autores"
                                            :key="autor.id"
                                            :value="autor.id"
                                        >
                                            {{ authorFullName(autor) }}
                                        </option>
                                    </select>
                                </div>

                                <div class="w-full sm:w-64">
                                    <InputLabel value="Rol / Participación" class="text-xs text-gray-500 mb-1" />
                                    <select
                                        v-model="authorRow.tipo_participaciones_id"
                                        class="w-full text-sm font-semibold rounded-xl border-gray-200 py-2 px-3 bg-white focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10"
                                    >
                                        <option value="">-- Tipo de Rol --</option>
                                        <option
                                            v-for="rol in participaciones"
                                            :key="rol.id"
                                            :value="rol.id"
                                        >
                                            {{ rol.tp_nombre }}
                                        </option>
                                    </select>
                                </div>

                                <div class="self-end sm:self-center pt-2 sm:pt-5">
                                    <button
                                        type="button"
                                        @click="removeAuthorRow(index)"
                                        class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition cursor-pointer"
                                        title="Quitar autor"
                                    >
                                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sección 3: Archivo Adjunto (PDF / Word) -->
                    <div class="space-y-4">
                        <h2 class="text-base font-extrabold text-[#14161C] pb-2 border-b border-gray-100 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-[#35449F]/10 text-[#35449F] flex items-center justify-center text-xs font-bold">3</span>
                            Documento Adjunto (PDF / Word)
                        </h2>

                        <div class="border-2 border-dashed border-gray-200 hover:border-[#35449F] rounded-2xl p-6 text-center transition bg-gray-50/30">
                            <input
                                id="art_archivo"
                                type="file"
                                accept=".pdf,.doc,.docx"
                                class="hidden"
                                @change="handleFileChange"
                            />
                            <label for="art_archivo" class="cursor-pointer flex flex-col items-center">
                                <div class="p-3 bg-indigo-50 text-[#35449F] rounded-2xl mb-3">
                                    <svg class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                </div>
                                <span class="text-sm font-bold text-gray-700">
                                    Haz clic para seleccionar el archivo del artículo
                                </span>
                                <span class="text-xs text-gray-400 mt-1">
                                    Formatos permitidos: PDF, DOC o DOCX (Máx. 15 MB)
                                </span>
                                <div v-if="fileName" class="mt-3 inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-[#35449F]">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    {{ fileName }}
                                </div>
                            </label>
                        </div>
                        <InputError :message="form.errors.art_archivo" class="mt-1.5" />
                    </div>

                    <!-- Botones de Acción -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <Link :href="route('admin.articulos.index')">
                            <SecondaryButton type="button" class="cursor-pointer">
                                Cancelar
                            </SecondaryButton>
                        </Link>
                        <PrimaryButton :disabled="form.processing" class="cursor-pointer">
                            <span v-if="form.processing">Guardando artículo...</span>
                            <span v-else>Guardar Artículo</span>
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
