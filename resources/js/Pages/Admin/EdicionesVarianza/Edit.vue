<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import EditorJs from '@/Components/EditorJs.vue';
import { slugify } from '@/Utils/slugify';
import { validateFileSize } from '@/Utils/fileValidation';

const props = defineProps({
    edicion: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    ev_titulo: props.edicion.ev_titulo || '',
    ev_slug: props.edicion.ev_slug || '',
    ev_numero: props.edicion.ev_numero || '',
    ev_volumen: props.edicion.ev_volumen || '',
    ev_anio: props.edicion.ev_anio || new Date().getFullYear(),
    ev_mes: props.edicion.ev_mes || '',
    ev_issn_ver_impresa: props.edicion.ev_issn_ver_impresa || '',
    ev_issn_ver_electronica: props.edicion.ev_issn_ver_electronica || '',
    ev_num_art_originales: props.edicion.ev_num_art_originales ?? '',
    ev_notas_cientificas: props.edicion.ev_notas_cientificas ?? '',
    ev_num_paginas: props.edicion.ev_num_paginas ?? '',
    ev_doi: props.edicion.ev_doi || '',
    ev_presentacion: props.edicion.ev_presentacion || { blocks: [] },
    ev_editorial: props.edicion.ev_editorial || '',
    ev_fecha_publicacion: props.edicion.ev_fecha_publicacion ? props.edicion.ev_fecha_publicacion.slice(0, 10) : '',
    ev_estado: Boolean(props.edicion.ev_estado),
    ev_enlace_url: props.edicion.ev_enlace_url || '',
    extraer_portada_de_pdf: false,
    ev_portada: null,
    ev_archivo: null,
});

const tieneEnlaceExterno = ref(Boolean(props.edicion.ev_enlace_url));
const isSlugManuallyEdited = ref(true);
const portadaInput = ref(null);
const portadaPreview = ref(props.edicion.portada_url || null);
const archivoName = ref('');

const googleDriveId = computed(() => {
    if (!tieneEnlaceExterno.value || !form.ev_enlace_url) return null;
    const match = form.ev_enlace_url.match(/(?:file\/d\/|open\?id=|id=|\/d\/)([a-zA-Z0-9_-]{20,})/);
    return match ? match[1] : null;
});

const googleDrivePreviewUrl = computed(() => {
    if (!googleDriveId.value) return null;
    return `https://drive.google.com/thumbnail?id=${googleDriveId.value}&sz=w600`;
});

watch(googleDriveId, (newId) => {
    if (newId && !form.ev_portada && !props.edicion.portada_url) {
        form.extraer_portada_de_pdf = true;
    }
});

const onTituloInput = () => {
    // If slug was empty or user wants to update it
    if (!form.ev_slug) {
        form.ev_slug = slugify(form.ev_titulo);
    }
};

const handlePortadaChange = (e) => {
    const file = e.target.files?.[0];
    if (!file) return;
    form.ev_portada = file;
    form.extraer_portada_de_pdf = false;

    const reader = new FileReader();
    reader.onload = (event) => {
        portadaPreview.value = event.target.result;
    };
    reader.readAsDataURL(file);
};

const onToggleExtraerPortada = () => {
    if (form.extraer_portada_de_pdf) {
        form.ev_portada = null;
        if (portadaInput.value) portadaInput.value.value = '';
    }
};

const triggerPortadaInput = () => {
    portadaInput.value?.click();
};

const removePortada = () => {
    form.ev_portada = null;
    form.extraer_portada_de_pdf = false;
    portadaPreview.value = props.edicion.portada_url || null;
    if (portadaInput.value) portadaInput.value.value = '';
};

const handleArchivoChange = (e) => {
    const file = e.target.files?.[0];
    if (!file) return;

    if (!validateFileSize(file, 20, '¿Enlace externo?')) {
        tieneEnlaceExterno.value = true;
        e.target.value = '';
        form.ev_archivo = null;
        archivoName.value = '';
        return;
    }

    form.ev_archivo = file;
    archivoName.value = file.name;
    if (!props.edicion.portada_url && !form.ev_portada) {
        form.extraer_portada_de_pdf = true;
    }
};

const removeArchivo = () => {
    form.ev_archivo = null;
    archivoName.value = '';
};

const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'PUT',
        ev_enlace_url: tieneEnlaceExterno.value ? (data.ev_enlace_url?.trim() || null) : null,
    })).post(route('admin.ediciones-varianza.update', props.edicion.id), {
        preserveScroll: true,
        forceFormData: true,
    });
};
</script>

<template>
    <AppLayout :title="`Editar Edición - ${edicion.ev_titulo}`">
        <template #header>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 w-full">
                <!-- Barra superior inspirada en diseño de referencia -->
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('admin.ediciones-varianza.show', edicion.id)"
                        class="size-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-600 hover:text-slate-900 hover:border-slate-300 transition shadow-2xs"
                        title="Volver a la edición"
                    >
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <div>
                        <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-0.5">
                            <span>Revista Varianza</span>
                            <span>/</span>
                            <Link :href="route('admin.ediciones-varianza.index')" class="hover:text-[#35449F]">Ediciones</Link>
                            <span>/</span>
                            <span class="text-slate-800 font-bold">Editar #{{ edicion.ev_numero }}</span>
                        </div>
                        <h1 class="font-extrabold text-2xl text-[#14161C] tracking-tight">
                            Editar Edición: {{ edicion.ev_titulo }}
                        </h1>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 sm:gap-3 shrink-0">
                    <span v-if="form.isDirty" class="text-xs font-semibold text-amber-700 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-200">
                        ● Cambios sin guardar
                    </span>
                    <Link :href="route('admin.ediciones-varianza.show', edicion.id)">
                        <SecondaryButton class="cursor-pointer">
                            Ver Detalle
                        </SecondaryButton>
                    </Link>
                    <PrimaryButton @click="submit" :disabled="form.processing" class="cursor-pointer">
                        <svg class="size-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ form.processing ? 'Actualizando...' : 'Guardar Cambios' }}
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto pb-16">
            <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <!-- Columna Izquierda Principal (8 columnas / ~65%) -->
                <div class="lg:col-span-8 space-y-6">
                    <!-- Tarjeta 1: Título y Slug -->
                    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-5">
                        <div>
                            <InputLabel value="Título de la Edición *" class="text-xs font-bold text-slate-700 uppercase" />
                            <TextInput
                                v-model="form.ev_titulo"
                                @input="onTituloInput"
                                type="text"
                                class="w-full mt-1 font-semibold text-base"
                                required
                            />
                            <InputError :message="form.errors.ev_titulo" class="mt-1" />
                        </div>

                        <div>
                            <div class="flex items-center justify-between">
                                <InputLabel value="Slug Permanente (URL amigable) *" class="text-xs font-bold text-slate-700 uppercase" />
                                <span class="text-[11px] text-slate-400">Identificador único</span>
                            </div>
                            <div class="relative mt-1">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-sm font-mono">
                                    /ediciones/
                                </span>
                                <TextInput
                                    v-model="form.ev_slug"
                                    type="text"
                                    class="w-full pl-28 font-mono text-sm"
                                    required
                                />
                            </div>
                            <InputError :message="form.errors.ev_slug" class="mt-1" />
                        </div>
                    </div>

                    <!-- Tarjeta 2: Presentación Editorial (Editor.js) -->
                    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                        <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                            <div>
                                <h2 class="text-base font-extrabold text-[#14161C]">Presentación / Carta Editorial</h2>
                                <p class="text-xs text-slate-500">Prólogo, mensaje del director del comité o resumen de la edición redactado en Editor.js.</p>
                            </div>
                            <span class="px-2 py-1 text-[10px] font-bold uppercase rounded bg-slate-100 text-slate-600">
                                Editor.js
                            </span>
                        </div>

                        <div class="pt-2">
                            <EditorJs
                                v-model="form.ev_presentacion"
                                placeholder="Escribe la editorial, introducción a los temas destacados de este número..."
                            />
                            <InputError :message="form.errors.ev_presentacion" class="mt-1" />
                        </div>
                    </div>

                    <!-- Tarjeta 3: Media y Archivos (Portada + PDF) -->
                    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-6">
                        <div class="border-b border-slate-100 pb-3">
                            <h2 class="text-base font-extrabold text-[#14161C]">Archivos de la Edición</h2>
                            <p class="text-xs text-slate-500">Actualiza la portada o el archivo PDF compilatorio.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Portada de la Edición -->
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <InputLabel value="Portada de la Edición (Imagen)" class="text-xs font-bold text-slate-700 uppercase" />
                                    <span v-if="form.extraer_portada_de_pdf" class="inline-flex items-center gap-1 text-[11px] font-bold text-[#35449F] bg-indigo-50 px-2 py-0.5 rounded-full border border-indigo-100">
                                        ✨ Extracción 1ª Pág. PDF
                                    </span>
                                </div>

                                <!-- Opción para extraer portada del PDF automáticamente -->
                                <label
                                    class="flex items-start gap-2.5 p-3 rounded-xl border transition cursor-pointer select-none"
                                    :class="form.extraer_portada_de_pdf ? 'bg-indigo-50/70 border-indigo-200' : 'bg-slate-50/60 border-slate-200/80 hover:bg-slate-100/60'"
                                >
                                    <input
                                        type="checkbox"
                                        v-model="form.extraer_portada_de_pdf"
                                        @change="onToggleExtraerPortada"
                                        class="mt-0.5 rounded border-gray-300 text-[#35449F] focus:ring-[#35449F]/20 size-4 cursor-pointer"
                                    />
                                    <div class="text-xs">
                                        <span class="font-bold text-slate-800">Usar 1ª página del PDF como portada</span>
                                        <p class="text-[11px] text-slate-500 mt-0.5">
                                            Extrae y actualiza la carátula automáticamente desde la primera página del PDF al guardar.
                                        </p>
                                    </div>
                                </label>

                                <input
                                    ref="portadaInput"
                                    type="file"
                                    accept="image/jpeg,image/png,image/webp"
                                    @change="handlePortadaChange"
                                    class="hidden"
                                />

                                <!-- Estado 1: Portada detectada desde Google Drive -->
                                <div
                                    v-if="googleDrivePreviewUrl && !form.ev_portada && form.extraer_portada_de_pdf"
                                    class="group relative aspect-3/4 rounded-xl border-2 border-indigo-200 overflow-hidden bg-slate-900 shadow-xs flex flex-col justify-end"
                                >
                                    <img
                                        :src="googleDrivePreviewUrl"
                                        alt="Vista previa portada Drive"
                                        class="absolute inset-0 w-full h-full object-cover"
                                    />
                                    <div class="relative z-10 p-3 bg-gradient-to-t from-slate-950/90 via-slate-900/60 to-transparent text-center space-y-1">
                                        <span class="inline-block px-2.5 py-1 text-[11px] font-bold text-white bg-indigo-600 rounded-lg shadow-xs">
                                            ✨ Portada detectada desde Drive
                                        </span>
                                        <p class="text-[10px] text-slate-200 font-medium">Se guardará automáticamente en alta resolución.</p>
                                        <button
                                            type="button"
                                            @click.stop="triggerPortadaInput"
                                            class="text-[11px] text-indigo-300 hover:text-white font-bold underline block mx-auto pt-1 cursor-pointer"
                                        >
                                            O cambiar por una imagen manual
                                        </button>
                                    </div>
                                </div>

                                <!-- Estado 2: Extracción Automática Activada desde PDF -->
                                <div
                                    v-else-if="form.extraer_portada_de_pdf"
                                    class="group relative aspect-3/4 rounded-xl border-2 border-dashed border-indigo-300 bg-indigo-50/30 flex flex-col items-center justify-center p-4 text-center space-y-2 transition"
                                >
                                    <div class="size-14 rounded-2xl bg-indigo-100 text-[#35449F] flex items-center justify-center shadow-xs">
                                        <svg class="size-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-[#35449F]">Extracción de portada activa</p>
                                        <p class="text-[11px] text-slate-500 mt-1 max-w-[200px]">
                                            El sistema generará la portada a partir de la primera página del documento o enlace al guardar cambios.
                                        </p>
                                    </div>
                                    <button
                                        type="button"
                                        @click.stop="triggerPortadaInput"
                                        class="text-[11px] text-[#35449F] hover:underline font-bold mt-2 cursor-pointer"
                                    >
                                        O subir imagen manual
                                    </button>
                                </div>

                                <!-- Estado 3: Subida Manual o Existente -->
                                <div
                                    v-else
                                    @click="triggerPortadaInput"
                                    class="group relative aspect-3/4 rounded-xl border-2 border-dashed border-slate-200 hover:border-[#35449F] bg-slate-50/50 flex flex-col items-center justify-center p-4 cursor-pointer transition overflow-hidden"
                                >
                                    <img
                                        v-if="portadaPreview"
                                        :src="portadaPreview"
                                        alt="Vista previa portada"
                                        class="absolute inset-0 w-full h-full object-cover transition group-hover:opacity-90"
                                    />

                                    <div
                                        v-if="portadaPreview"
                                        class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition"
                                    >
                                        <span class="px-3 py-1.5 text-xs font-bold text-white bg-slate-900/80 rounded-lg">
                                            Cambiar Imagen
                                        </span>
                                    </div>

                                    <div v-else class="text-center space-y-2">
                                        <div class="size-12 rounded-xl bg-blue-50 text-[#35449F] flex items-center justify-center mx-auto">
                                            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-slate-700">Subir portada</p>
                                            <p class="text-[11px] text-slate-400">PNG, JPG o WEBP (máx. 5MB)</p>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="form.ev_portada && !form.extraer_portada_de_pdf" class="flex items-center justify-between text-xs">
                                    <span class="text-emerald-700 font-semibold truncate max-w-[180px]">
                                        ✓ Nueva: {{ form.ev_portada.name }}
                                    </span>
                                    <button type="button" @click="removePortada" class="text-red-600 hover:underline cursor-pointer">
                                        Deshacer
                                    </button>
                                </div>
                                <InputError :message="form.errors.ev_portada" />
                            </div>

                            <!-- Archivo PDF Completo -->
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <InputLabel value="Revista Completa en PDF" class="text-xs font-bold text-slate-700 uppercase" />
                                    <label class="inline-flex items-center gap-1.5 cursor-pointer select-none">
                                        <input
                                            type="checkbox"
                                            v-model="tieneEnlaceExterno"
                                            class="rounded border-gray-300 text-[#35449F] focus:ring-[#35449F]/20 size-4 cursor-pointer"
                                        />
                                        <span class="text-xs font-bold text-[#35449F] hover:underline">
                                            ¿Enlace externo?
                                        </span>
                                    </label>
                                </div>

                                <label class="group relative aspect-3/4 rounded-xl border-2 border-dashed border-slate-200 hover:border-red-500 bg-slate-50/50 flex flex-col items-center justify-center p-4 cursor-pointer transition">
                                    <input
                                        type="file"
                                        accept="application/pdf"
                                        @change="handleArchivoChange"
                                        class="hidden"
                                    />

                                    <div v-if="archivoName" class="text-center space-y-2 p-2">
                                        <div class="size-14 rounded-xl bg-red-100 text-red-600 flex items-center justify-center mx-auto">
                                            <svg class="size-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <p class="text-xs font-bold text-slate-800 break-all">{{ archivoName }}</p>
                                        <p class="text-[11px] text-emerald-600 font-semibold">✓ Nuevo PDF seleccionado</p>
                                        <button
                                            type="button"
                                            @click.stop.prevent="removeArchivo"
                                            class="text-xs text-red-600 hover:underline font-semibold"
                                        >
                                            Cambiar archivo
                                        </button>
                                    </div>

                                    <div v-else-if="edicion.archivo_url" class="text-center space-y-2 p-2">
                                        <div class="size-14 rounded-xl bg-red-50 text-red-600 flex items-center justify-center mx-auto">
                                            <svg class="size-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <p class="text-xs font-bold text-slate-800">PDF actual cargado</p>
                                        <a
                                            :href="edicion.archivo_url"
                                            target="_blank"
                                            @click.stop
                                            class="inline-block text-[11px] text-[#35449F] hover:underline font-semibold"
                                        >
                                            Ver PDF existente ↗
                                        </a>
                                        <p class="text-[11px] text-slate-400">Haz clic para reemplazar por otro PDF</p>
                                    </div>

                                    <div v-else class="text-center space-y-2">
                                        <div class="size-12 rounded-xl bg-red-50 text-red-600 flex items-center justify-center mx-auto">
                                            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-slate-700">Subir PDF completo</p>
                                            <p class="text-[11px] text-slate-400">Documento PDF (máx. 20MB)</p>
                                        </div>
                                    </div>
                                </label>

                                <InputError :message="form.errors.ev_archivo" />
                            </div>
                        </div>

                        <!-- Campo Dinámico: Enlace Externo (Drive, Dropbox, OneDrive, etc.) -->
                        <div v-if="tieneEnlaceExterno" class="mt-4 p-4 rounded-xl bg-indigo-50/50 border border-indigo-100 space-y-2 transition-all">
                            <div class="flex items-center justify-between">
                                <InputLabel for="ev_enlace_url" value="Enlace Externo al PDF de la Revista (Google Drive, Dropbox, OneDrive, etc.)" class="text-xs font-bold text-gray-900" />
                                <span class="text-[11px] font-bold text-indigo-700 bg-indigo-100 px-2.5 py-0.5 rounded-full">
                                    Drive • Dropbox • URL Nube
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <TextInput
                                    id="ev_enlace_url"
                                    v-model="form.ev_enlace_url"
                                    type="url"
                                    class="block w-full text-sm"
                                    placeholder="https://drive.google.com/file/d/... o enlace compartido del PDF de la revista"
                                />
                                <a
                                    v-if="form.ev_enlace_url"
                                    :href="form.ev_enlace_url"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="shrink-0 px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5 shadow-2xs"
                                    title="Abrir y verificar enlace externo"
                                >
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                    <span>Probar enlace</span>
                                </a>
                            </div>
                            <p class="text-[11px] text-gray-500 font-medium">
                                Opcional. Puedes subir el archivo PDF local, proporcionar un enlace externo a la nube, ambos o ninguno según lo decidas.
                            </p>
                            <InputError :message="form.errors.ev_enlace_url" class="mt-1" />
                        </div>
                    </div>

                    <!-- Tarjeta 4: Editorial / Casa Editora -->
                    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                        <div>
                            <InputLabel value="Editorial / Entidad Editora" class="text-xs font-bold text-slate-700 uppercase" />
                            <TextInput
                                v-model="form.ev_editorial"
                                type="text"
                                class="w-full mt-1 text-sm"
                            />
                            <InputError :message="form.errors.ev_editorial" class="mt-1" />
                        </div>
                    </div>
                </div>

                <!-- Columna Derecha (Sidebar / 4 columnas / ~35%) -->
                <div class="lg:col-span-4 space-y-6 lg:sticky lg:top-6">
                    <!-- Tarjeta Estado de Publicación -->
                    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                        <div class="border-b border-slate-100 pb-3">
                            <h3 class="text-sm font-extrabold text-[#14161C]">Estado de Publicación</h3>
                            <p class="text-xs text-slate-500">Disponibilidad pública de la edición.</p>
                        </div>

                        <div>
                            <InputLabel value="Visibilidad" class="text-xs font-bold text-slate-700 uppercase" />
                            <select
                                v-model="form.ev_estado"
                                class="w-full mt-1 border-slate-200 rounded-xl text-sm font-semibold focus:border-[#35449F] focus:ring-[#35449F]/20 bg-white"
                            >
                                <option :value="true">🟢 Publicado (Visible en el sitio web)</option>
                                <option :value="false">🟡 Borrador (Solo administradores)</option>
                            </select>
                            <InputError :message="form.errors.ev_estado" class="mt-1" />
                        </div>

                        <div>
                            <InputLabel value="Fecha Oficial de Publicación" class="text-xs font-bold text-slate-700 uppercase" />
                            <TextInput
                                v-model="form.ev_fecha_publicacion"
                                type="date"
                                class="w-full mt-1 text-sm font-medium"
                            />
                            <p class="text-[11px] text-slate-400 mt-1">Opcional. Si solo se conoce mes/semestre y año, déjalo vacío.</p>
                            <InputError :message="form.errors.ev_fecha_publicacion" class="mt-1" />
                        </div>
                    </div>

                    <!-- Tarjeta Numeración y Periodo -->
                    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                        <div class="border-b border-slate-100 pb-3">
                            <h3 class="text-sm font-extrabold text-[#14161C]">Numeración & Periodo</h3>
                            <p class="text-xs text-slate-500">Datos bibliográficos del número.</p>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <InputLabel value="Número *" class="text-xs font-bold text-slate-700 uppercase" />
                                <TextInput
                                    v-model="form.ev_numero"
                                    type="text"
                                    class="w-full mt-1 font-bold text-center text-sm"
                                    placeholder="25"
                                    required
                                />
                                <InputError :message="form.errors.ev_numero" class="mt-1" />
                            </div>

                            <div>
                                <InputLabel value="Volumen" class="text-xs font-bold text-slate-700 uppercase" />
                                <TextInput
                                    v-model="form.ev_volumen"
                                    type="text"
                                    class="w-full mt-1 text-center text-sm"
                                    placeholder="Vol. 14"
                                />
                                <InputError :message="form.errors.ev_volumen" class="mt-1" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <InputLabel value="Año *" class="text-xs font-bold text-slate-700 uppercase" />
                                <TextInput
                                    v-model="form.ev_anio"
                                    type="number"
                                    min="1980"
                                    max="2050"
                                    class="w-full mt-1 font-bold text-center text-sm"
                                    required
                                />
                                <InputError :message="form.errors.ev_anio" class="mt-1" />
                            </div>

                            <div>
                                <InputLabel value="Mes / Semestre" class="text-xs font-bold text-slate-700 uppercase" />
                                <TextInput
                                    v-model="form.ev_mes"
                                    type="text"
                                    class="w-full mt-1 text-sm font-medium"
                                    placeholder="Ej: Julio - Diciembre o Octubre"
                                />
                                <InputError :message="form.errors.ev_mes" class="mt-1" />
                            </div>
                        </div>
                    </div>

                    <!-- Tarjeta Identificadores Internacionales -->
                    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                        <div class="border-b border-slate-100 pb-3">
                            <h3 class="text-sm font-extrabold text-[#14161C]">Identificadores</h3>
                            <p class="text-xs text-slate-500">ISSN y DOI para citas científicas.</p>
                        </div>

                        <div>
                            <InputLabel value="ISSN Impreso" class="text-xs font-bold text-slate-700 uppercase" />
                            <TextInput
                                v-model="form.ev_issn_ver_impresa"
                                type="text"
                                class="w-full mt-1 font-mono text-sm"
                                placeholder="Ej: 2075-6941"
                            />
                            <InputError :message="form.errors.ev_issn_ver_impresa" class="mt-1" />
                        </div>

                        <div>
                            <InputLabel value="ISSN Electrónico" class="text-xs font-bold text-slate-700 uppercase" />
                            <TextInput
                                v-model="form.ev_issn_ver_electronica"
                                type="text"
                                class="w-full mt-1 font-mono text-sm"
                                placeholder="Ej: 2415-2684"
                            />
                            <InputError :message="form.errors.ev_issn_ver_electronica" class="mt-1" />
                        </div>

                        <div>
                            <InputLabel value="DOI de la Edición" class="text-xs font-bold text-slate-700 uppercase" />
                            <TextInput
                                v-model="form.ev_doi"
                                type="text"
                                class="w-full mt-1 font-mono text-sm"
                                placeholder="Ej: 10.53287/varianza.v14i25"
                            />
                            <InputError :message="form.errors.ev_doi" class="mt-1" />
                        </div>
                    </div>

                    <!-- Tarjeta Métricas y Páginas -->
                    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                        <div class="border-b border-slate-100 pb-3">
                            <h3 class="text-sm font-extrabold text-[#14161C]">Métricas Declaradas</h3>
                            <p class="text-xs text-slate-500">Páginas y artículos declarados.</p>
                        </div>

                        <div>
                            <InputLabel value="Número Total de Páginas" class="text-xs font-bold text-slate-700 uppercase" />
                            <TextInput
                                v-model="form.ev_num_paginas"
                                type="number"
                                min="1"
                                class="w-full mt-1 text-sm font-medium"
                                placeholder="Ej: 120"
                            />
                            <InputError :message="form.errors.ev_num_paginas" class="mt-1" />
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <InputLabel value="Art. Originales" class="text-[11px] font-bold text-slate-700 uppercase" />
                                <TextInput
                                    v-model="form.ev_num_art_originales"
                                    type="number"
                                    min="0"
                                    class="w-full mt-1 text-center text-sm font-medium"
                                    placeholder="Ej: 8"
                                />
                                <InputError :message="form.errors.ev_num_art_originales" class="mt-1" />
                            </div>

                            <div>
                                <InputLabel value="Notas Científicas" class="text-[11px] font-bold text-slate-700 uppercase" />
                                <TextInput
                                    v-model="form.ev_notas_cientificas"
                                    type="number"
                                    min="0"
                                    class="w-full mt-1 text-center text-sm font-medium"
                                    placeholder="Ej: 3"
                                />
                                <InputError :message="form.errors.ev_notas_cientificas" class="mt-1" />
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
