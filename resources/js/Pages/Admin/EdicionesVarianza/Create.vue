<script setup>
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import EditorJs from '@/Components/EditorJs.vue';
import { slugify } from '@/Utils/slugify';

const currentYear = new Date().getFullYear();

const form = useForm({
    ev_titulo: '',
    ev_slug: '',
    ev_numero: '',
    ev_volumen: '',
    ev_anio: currentYear,
    ev_mes: 'Julio - Diciembre',
    ev_issn_ver_impresa: '2075-6941',
    ev_issn_ver_electronica: '2415-2684',
    ev_num_art_originales: '',
    ev_notas_cientificas: '',
    ev_num_paginas: '',
    ev_doi: '',
    ev_presentacion: { blocks: [] },
    ev_editorial: 'Instituto de Estadística Teórica y Aplicada (IETA) - UMSA',
    ev_fecha_publicacion: new Date().toISOString().slice(0, 10),
    ev_estado: true,
    ev_portada: null,
    ev_archivo: null,
});

const isSlugManuallyEdited = ref(false);
const portadaInput = ref(null);
const portadaPreview = ref(null);
const archivoName = ref('');

const onTituloInput = () => {
    if (!isSlugManuallyEdited.value) {
        form.ev_slug = slugify(form.ev_titulo);
    }
};

const onSlugInput = () => {
    isSlugManuallyEdited.value = true;
};

const handlePortadaChange = (e) => {
    const file = e.target.files?.[0];
    if (!file) return;
    form.ev_portada = file;

    const reader = new FileReader();
    reader.onload = (event) => {
        portadaPreview.value = event.target.result;
    };
    reader.readAsDataURL(file);
};

const triggerPortadaInput = () => {
    portadaInput.value?.click();
};

const removePortada = () => {
    form.ev_portada = null;
    portadaPreview.value = null;
    if (portadaInput.value) portadaInput.value.value = '';
};

const handleArchivoChange = (e) => {
    const file = e.target.files?.[0];
    if (!file) return;
    form.ev_archivo = file;
    archivoName.value = file.name;
};

const removeArchivo = () => {
    form.ev_archivo = null;
    archivoName.value = '';
};

const submit = () => {
    form.post(route('admin.ediciones-varianza.store'), {
        preserveScroll: true,
        forceFormData: true,
    });
};
</script>

<template>
    <AppLayout title="Nueva Edición - Revista Varianza">
        <template #header>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 w-full">
                <!-- Barra superior inspirada en diseño de referencia -->
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('admin.ediciones-varianza.index')"
                        class="size-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-600 hover:text-slate-900 hover:border-slate-300 transition shadow-2xs"
                        title="Volver al listado"
                    >
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <div>
                        <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-0.5">
                            <span>Revista Varianza</span>
                            <span>/</span>
                            <span>Ediciones</span>
                            <span>/</span>
                            <span class="text-slate-800 font-bold">Crear</span>
                        </div>
                        <h1 class="font-extrabold text-2xl text-[#14161C] tracking-tight">
                            Nueva Edición de la Revista
                        </h1>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 sm:gap-3 shrink-0">
                    <Link :href="route('admin.ediciones-varianza.index')">
                        <SecondaryButton class="cursor-pointer">
                            Descartar
                        </SecondaryButton>
                    </Link>
                    <PrimaryButton @click="submit" :disabled="form.processing" class="cursor-pointer">
                        <svg class="size-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ form.processing ? 'Guardando...' : 'Guardar Edición' }}
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
                                placeholder="Ej: Revista Varianza - Edición Nº 25 (Año 2024)"
                                required
                            />
                            <InputError :message="form.errors.ev_titulo" class="mt-1" />
                        </div>

                        <div>
                            <div class="flex items-center justify-between">
                                <InputLabel value="Slug Permanente (URL amigable) *" class="text-xs font-bold text-slate-700 uppercase" />
                                <span v-if="!isSlugManuallyEdited" class="text-[11px] text-slate-400">
                                    Generado automáticamente
                                </span>
                            </div>
                            <div class="relative mt-1">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 text-xs font-mono">
                                    /ediciones/
                                </span>
                                <TextInput
                                    v-model="form.ev_slug"
                                    @input="onSlugInput"
                                    type="text"
                                    class="w-full pl-24 font-mono text-xs"
                                    placeholder="revista-varianza-edicion-25"
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
                            <p class="text-xs text-slate-500">Sube la portada oficial y el archivo PDF compilatorio completo.</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Portada de la Edición -->
                            <div class="space-y-3">
                                <InputLabel value="Portada de la Edición (Imagen)" class="text-xs font-bold text-slate-700 uppercase" />
                                <input
                                    ref="portadaInput"
                                    type="file"
                                    accept="image/jpeg,image/png,image/webp"
                                    @change="handlePortadaChange"
                                    class="hidden"
                                />

                                <div
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

                                <div v-if="form.ev_portada" class="flex items-center justify-between text-xs">
                                    <span class="text-emerald-700 font-semibold truncate max-w-[180px]">
                                        ✓ {{ form.ev_portada.name }}
                                    </span>
                                    <button type="button" @click="removePortada" class="text-red-600 hover:underline cursor-pointer">
                                        Quitar
                                    </button>
                                </div>
                                <InputError :message="form.errors.ev_portada" />
                            </div>

                            <!-- Archivo PDF Completo -->
                            <div class="space-y-3">
                                <InputLabel value="Revista Completa en PDF" class="text-xs font-bold text-slate-700 uppercase" />

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
                                        <p class="text-[11px] text-emerald-600 font-semibold">✓ Archivo PDF seleccionado</p>
                                        <button
                                            type="button"
                                            @click.stop.prevent="removeArchivo"
                                            class="text-xs text-red-600 hover:underline font-semibold"
                                        >
                                            Cambiar archivo
                                        </button>
                                    </div>

                                    <div v-else class="text-center space-y-2">
                                        <div class="size-12 rounded-xl bg-red-50 text-red-600 flex items-center justify-center mx-auto">
                                            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-slate-700">Subir PDF completo</p>
                                            <p class="text-[11px] text-slate-400">Documento PDF (máx. 50MB)</p>
                                        </div>
                                    </div>
                                </label>

                                <InputError :message="form.errors.ev_archivo" />
                            </div>
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
                                placeholder="Ej: Instituto de Estadística Teórica y Aplicada (IETA) - UMSA"
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
                                class="w-full mt-1 text-sm"
                            />
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
                                    class="w-full mt-1 font-bold text-center"
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
                                    class="w-full mt-1 text-center"
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
                                    class="w-full mt-1 font-bold text-center"
                                    required
                                />
                                <InputError :message="form.errors.ev_anio" class="mt-1" />
                            </div>

                            <div>
                                <InputLabel value="Mes / Semestre" class="text-xs font-bold text-slate-700 uppercase" />
                                <TextInput
                                    v-model="form.ev_mes"
                                    type="text"
                                    class="w-full mt-1 text-xs"
                                    placeholder="Julio - Dic"
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
                                class="w-full mt-1 font-mono text-xs"
                                placeholder="2075-6941"
                            />
                            <InputError :message="form.errors.ev_issn_ver_impresa" class="mt-1" />
                        </div>

                        <div>
                            <InputLabel value="ISSN Electrónico" class="text-xs font-bold text-slate-700 uppercase" />
                            <TextInput
                                v-model="form.ev_issn_ver_electronica"
                                type="text"
                                class="w-full mt-1 font-mono text-xs"
                                placeholder="2415-2684"
                            />
                            <InputError :message="form.errors.ev_issn_ver_electronica" class="mt-1" />
                        </div>

                        <div>
                            <InputLabel value="DOI de la Edición" class="text-xs font-bold text-slate-700 uppercase" />
                            <TextInput
                                v-model="form.ev_doi"
                                type="text"
                                class="w-full mt-1 font-mono text-xs"
                                placeholder="10.53287/varianza.v14i25"
                            />
                            <InputError :message="form.errors.ev_doi" class="mt-1" />
                        </div>
                    </div>

                    <!-- Tarjeta Métricas y Páginas -->
                    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                        <div class="border-b border-slate-100 pb-3">
                            <h3 class="text-sm font-extrabold text-[#14161C]">Métricas de la Edición</h3>
                            <p class="text-xs text-slate-500">Páginas y artículos declarados.</p>
                        </div>

                        <div>
                            <InputLabel value="Número Total de Páginas" class="text-xs font-bold text-slate-700 uppercase" />
                            <TextInput
                                v-model="form.ev_num_paginas"
                                type="number"
                                min="1"
                                class="w-full mt-1"
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
                                    class="w-full mt-1 text-center"
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
                                    class="w-full mt-1 text-center"
                                    placeholder="Ej: 3"
                                />
                                <InputError :message="form.errors.ev_notas_cientificas" class="mt-1" />
                            </div>
                        </div>

                        <div class="p-3 rounded-xl bg-blue-50/70 border border-blue-100 text-[11px] text-[#35449F]">
                            💡 <strong>Tip:</strong> Una vez creada la edición, podrás subir artículos científicos asignándolos a este número y el sistema contabilizará automáticamente las estadísticas.
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
