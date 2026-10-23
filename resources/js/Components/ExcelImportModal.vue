<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Importación Masiva desde Excel',
    },
    subtitle: {
        type: String,
        default: 'Carga múltiples registros rápidamente mediante un archivo .xlsx o .csv.',
    },
    plantillaUrl: {
        type: String,
        required: true,
    },
    importUrl: {
        type: String,
        required: true,
    },
    columns: {
        type: Array,
        default: () => [],
    },
    sampleRows: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['close']);

const activeTab = ref('preview'); // 'preview' | 'upload'
const fileInput = ref(null);
const dragOver = ref(false);

const form = useForm({
    archivo_excel: null,
});

const selectedFileName = computed(() => form.archivo_excel?.name || '');
const selectedFileSize = computed(() => {
    if (!form.archivo_excel) return '';
    const bytes = form.archivo_excel.size;
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1048576) return `${(bytes / 1024).toFixed(1)} KB`;
    return `${(bytes / 1048576).toFixed(1)} MB`;
});

const handleFileSelect = (event) => {
    const file = event.target.files?.[0];
    if (file) {
        form.archivo_excel = file;
        activeTab.value = 'upload';
    }
};

const handleDrop = (event) => {
    dragOver.value = false;
    const file = event.dataTransfer?.files?.[0];
    if (file) {
        form.archivo_excel = file;
        activeTab.value = 'upload';
    }
};

const removeFile = () => {
    form.archivo_excel = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const submitImport = () => {
    if (!form.archivo_excel) return;

    form.post(props.importUrl, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            closeModal();
        },
    });
};

const closeModal = () => {
    form.reset();
    form.clearErrors();
    activeTab.value = 'preview';
    emit('close');
};
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
        <!-- Backdrop -->
        <div 
            class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity duration-300"
            @click="closeModal"
        />

        <!-- Contenedor del Modal Centrado -->
        <div class="flex min-h-full items-center justify-center p-3 sm:p-6 text-center">
            <div 
                class="w-full max-w-4xl transform overflow-hidden rounded-2xl bg-white text-left align-middle shadow-2xl transition-all border border-gray-100 flex flex-col max-h-[90vh]"
                @click.stop
            >
                <!-- Cabecera del Modal -->
                <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-gray-50/80 to-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shadow-xs shrink-0">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900 tracking-tight">
                                {{ title }}
                            </h2>
                            <p class="text-xs text-gray-500 font-medium">
                                {{ subtitle }}
                            </p>
                        </div>
                    </div>

                    <button 
                        type="button" 
                        class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors cursor-pointer"
                        @click="closeModal"
                    >
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Selector de Pestañas (Tabs) -->
                <div class="px-6 pt-3 border-b border-gray-100 bg-gray-50/40 flex items-center justify-between gap-4">
                    <div class="flex gap-2">
                        <button
                            type="button"
                            class="px-4 py-2.5 text-xs sm:text-sm font-semibold rounded-t-lg transition-colors border-b-2 flex items-center gap-2 cursor-pointer"
                            :class="activeTab === 'preview' 
                                ? 'border-indigo-600 text-indigo-700 bg-white font-bold' 
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100/60'"
                            @click="activeTab = 'preview'"
                        >
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <span>Estructura y Vista Previa en Vivo</span>
                        </button>
                        <button
                            type="button"
                            class="px-4 py-2.5 text-xs sm:text-sm font-semibold rounded-t-lg transition-colors border-b-2 flex items-center gap-2 cursor-pointer"
                            :class="activeTab === 'upload' 
                                ? 'border-indigo-600 text-indigo-700 bg-white font-bold' 
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100/60'"
                            @click="activeTab = 'upload'"
                        >
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            <span>Subir Archivo Excel</span>
                            <span v-if="form.archivo_excel" class="size-2 rounded-full bg-emerald-500 animate-pulse" />
                        </button>
                    </div>

                    <!-- Botón de Descarga Directa siempre visible -->
                    <a
                        :href="plantillaUrl"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-lg transition-colors shrink-0 mb-1"
                        download
                    >
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        <span>Descargar Plantilla .xlsx</span>
                    </a>
                </div>

                <!-- Cuerpo del Modal -->
                <div class="p-6 overflow-y-auto space-y-6 flex-1 text-xs sm:text-sm">
                    <!-- ================= TAB 1: VISTA PREVIA DE PLANTILLA ================= -->
                    <div v-if="activeTab === 'preview'" class="space-y-6">
                        <!-- Tarjeta de Instrucción con Botón de Descarga -->
                        <div class="rounded-xl p-4 bg-gradient-to-r from-emerald-50/70 to-teal-50/50 border border-emerald-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-600 text-white uppercase tracking-wider">
                                        Paso 1
                                    </span>
                                    <h3 class="font-bold text-gray-900 text-sm sm:text-base">
                                        Descarga la plantilla con ejemplos reales
                                    </h3>
                                </div>
                                <p class="text-xs text-gray-600 leading-relaxed">
                                    El archivo viene con cabeceras predefinidas y filas de ejemplo. No modifiques los nombres de la fila 1 (cabeceras).
                                </p>
                            </div>
                            <a
                                :href="plantillaUrl"
                                class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 rounded-xl shadow-xs transition-colors shrink-0 whitespace-nowrap cursor-pointer"
                                download
                            >
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                <span>Descargar Plantilla .xlsx</span>
                            </a>
                        </div>

                        <!-- Vista Previa de la Tabla en Vivo (Spreadsheet Mockup) -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <h4 class="font-bold text-gray-800 text-xs sm:text-sm flex items-center gap-1.5">
                                    <span class="size-2 rounded-full bg-emerald-500"></span>
                                    Vista Previa en Vivo de la Hoja de Cálculo (Primeras Filas)
                                </h4>
                                <span class="text-[11px] text-gray-400 font-mono">Formato Excel .xlsx</span>
                            </div>

                            <div class="border border-gray-200 rounded-xl overflow-hidden shadow-xs">
                                <div class="overflow-x-auto max-h-64">
                                    <table class="min-w-full text-left text-xs divide-y divide-gray-200">
                                        <thead class="bg-[#1E3A8A] text-white sticky top-0 font-mono">
                                            <tr>
                                                <th class="px-3 py-2 text-center text-blue-200 border-r border-blue-900/50 w-10">#</th>
                                                <th 
                                                    v-for="col in columns" 
                                                    :key="col.name" 
                                                    class="px-3 py-2 whitespace-nowrap border-r border-blue-900/50 text-xs font-semibold"
                                                >
                                                    {{ col.name }}
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100 bg-white font-sans text-gray-700">
                                            <tr 
                                                v-for="(row, rIdx) in sampleRows" 
                                                :key="rIdx" 
                                                class="hover:bg-gray-50/80 transition-colors"
                                            >
                                                <td class="px-3 py-2 text-center text-gray-400 bg-gray-50/60 font-mono text-[11px] border-r border-gray-200 font-bold">
                                                    {{ rIdx + 2 }}
                                                </td>
                                                <td 
                                                    v-for="col in columns" 
                                                    :key="col.name" 
                                                    class="px-3 py-2 whitespace-nowrap border-r border-gray-100 max-w-[220px] truncate text-xs"
                                                    :title="String(row[col.name] ?? '')"
                                                >
                                                    <span v-if="row[col.name]" class="text-gray-900">
                                                        {{ row[col.name] }}
                                                    </span>
                                                    <span v-else class="text-gray-300 italic">
                                                        (opcional)
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Especificación Detallada de Columnas -->
                        <div class="space-y-2">
                            <h4 class="font-bold text-gray-800 text-xs sm:text-sm">
                                Guía de Columnas y Reglas
                            </h4>
                            <div class="border border-gray-200 rounded-xl overflow-hidden divide-y divide-gray-100 bg-white">
                                <div 
                                    v-for="col in columns" 
                                    :key="col.name" 
                                    class="p-3 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 hover:bg-gray-50/50 transition-colors"
                                >
                                    <div class="space-y-0.5">
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono font-bold text-gray-900 text-xs bg-gray-100 px-1.5 py-0.5 rounded border border-gray-200">
                                                {{ col.name }}
                                            </span>
                                            <span 
                                                class="text-[10px] font-bold px-1.5 py-0.5 rounded-full"
                                                :class="col.required ? 'bg-rose-100 text-rose-700' : 'bg-gray-100 text-gray-600'"
                                            >
                                                {{ col.required ? 'Obligatorio' : 'Opcional' }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500">
                                            {{ col.desc }}
                                        </p>
                                    </div>
                                    <div v-if="col.ejemplo" class="text-[11px] text-gray-600 bg-gray-50 px-2 py-1 rounded border border-gray-200 max-w-xs truncate font-mono">
                                        Ej: {{ col.ejemplo }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ================= TAB 2: SUBIR ARCHIVO ================= -->
                    <div v-if="activeTab === 'upload'" class="space-y-6">
                        <!-- Zona Dropzone -->
                        <div
                            class="border-2 border-dashed rounded-2xl p-8 text-center transition-all cursor-pointer relative"
                            :class="[
                                dragOver ? 'border-indigo-500 bg-indigo-50/40 ring-4 ring-indigo-50' : 'border-gray-200 hover:border-indigo-400 bg-gray-50/30 hover:bg-indigo-50/10',
                                form.archivo_excel ? 'border-emerald-500 bg-emerald-50/30' : ''
                            ]"
                            @dragover.prevent="dragOver = true"
                            @dragleave.prevent="dragOver = false"
                            @drop.prevent="handleDrop"
                            @click="$refs.fileInput.click()"
                        >
                            <input
                                ref="fileInput"
                                type="file"
                                accept=".xlsx, .xls, .csv"
                                class="hidden"
                                @change="handleFileSelect"
                            />

                            <!-- Estado Sin Archivo -->
                            <div v-if="!form.archivo_excel" class="space-y-3">
                                <div class="w-14 h-14 mx-auto rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center shadow-xs">
                                    <svg class="size-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-gray-800">
                                        Arrastra y suelta tu archivo Excel aquí, o <span class="text-indigo-600 underline">haz clic para examinar</span>
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Formatos admitidos: <strong>.xlsx, .xls, .csv</strong> (Máximo 10 MB)
                                    </p>
                                </div>
                            </div>

                            <!-- Estado Con Archivo Seleccionado -->
                            <div v-else class="space-y-3" @click.stop>
                                <div class="w-14 h-14 mx-auto rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shadow-xs">
                                    <svg class="size-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-gray-900">
                                        {{ selectedFileName }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Tamaño: {{ selectedFileSize }}
                                    </p>
                                </div>
                                <div class="pt-2 flex items-center justify-center gap-2">
                                    <button
                                        type="button"
                                        class="px-3 py-1 text-xs font-semibold text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 rounded-lg transition-colors cursor-pointer"
                                        @click="removeFile"
                                    >
                                        Cambiar archivo
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Errores de Validación del Formulario -->
                        <div v-if="form.errors.archivo_excel" class="p-4 bg-rose-50 border border-rose-200 rounded-xl flex items-start gap-3 text-rose-800">
                            <svg class="size-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <div class="text-xs font-medium">
                                {{ form.errors.archivo_excel }}
                            </div>
                        </div>

                        <!-- Estado de Carga / Spinner -->
                        <div v-if="form.processing" class="p-4 bg-indigo-50 border border-indigo-200 rounded-xl flex items-center gap-3 text-indigo-900">
                            <svg class="animate-spin size-5 text-indigo-600" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span class="text-xs font-bold">Procesando registros en base de datos... Por favor espera un momento.</span>
                        </div>
                    </div>
                </div>

                <!-- Pie del Modal -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between gap-3">
                    <div class="text-xs text-gray-500">
                        <span v-if="activeTab === 'preview'">
                            ¿Tienes dudas? Descarga la plantilla para ver el formato exacto.
                        </span>
                        <span v-else>
                            Los registros se guardarán en una sola transacción segura.
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <SecondaryButton type="button" @click="closeModal" class="cursor-pointer">
                            Cancelar
                        </SecondaryButton>

                        <PrimaryButton
                            v-if="activeTab === 'preview'"
                            type="button"
                            @click="activeTab = 'upload'"
                            class="cursor-pointer"
                        >
                            <span>Continuar a Subida</span>
                            <svg class="size-4 ml-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </PrimaryButton>

                        <PrimaryButton
                            v-else
                            type="button"
                            :disabled="!form.archivo_excel || form.processing"
                            @click="submitImport"
                            class="cursor-pointer !bg-emerald-600 hover:!bg-emerald-700 active:!bg-emerald-800"
                        >
                            <svg v-if="!form.processing" class="size-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            <svg v-else class="animate-spin size-4 mr-1.5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <span>{{ form.processing ? 'Importando...' : 'Procesar e Importar' }}</span>
                        </PrimaryButton>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
