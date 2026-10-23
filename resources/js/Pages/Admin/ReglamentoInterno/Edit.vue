<script setup>
import { ref } from 'vue';
import { useForm, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import EditorJs from '@/Components/EditorJs.vue';

const props = defineProps({
    reglamento: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    reg_titulo: props.reglamento?.reg_titulo || 'Reglamento Interno Institucional',
    reg_descripcion: props.reglamento?.reg_descripcion || '',
    reg_contenido: props.reglamento?.reg_contenido || { blocks: [] },
    archivo_reglamento: null,
});

const fileInput = ref(null);
const selectedFileName = ref('');
const confirmingDeleteFile = ref(false);

const handleFileChange = (e) => {
    const file = e.target.files?.[0];
    if (!file) return;
    form.archivo_reglamento = file;
    selectedFileName.value = file.name;
};

const triggerFileInput = () => {
    fileInput.value?.click();
};

const removeSelectedFile = () => {
    form.archivo_reglamento = null;
    selectedFileName.value = '';
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const deleteExistingPdf = () => {
    router.delete(route('admin.reglamento-interno.eliminar-archivo'), {
        preserveScroll: true,
        onSuccess: () => {
            confirmingDeleteFile.value = false;
        },
    });
};

const submit = () => {
    form.post(route('admin.reglamento-interno.update'), {
        forceFormData: true,
        preserveScroll: true,
    });
};

const formatBytes = (bytes) => {
    if (!bytes) return '—';
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return `${(bytes / Math.pow(1024, i)).toFixed(2)} ${sizes[i]}`;
};
</script>

<template>
    <AppLayout title="Reglamento Interno Institucional">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 w-full min-w-0">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-1.5 text-[11px] font-semibold text-slate-400 mb-0.5 leading-none">
                        <Link :href="route('dashboard')" class="hover:text-[#35449F]">Inicio</Link>
                        <span>/</span>
                        <span class="text-slate-500">Portal Web</span>
                        <span>/</span>
                        <span class="text-slate-700 font-bold">Reglamento Interno</span>
                    </div>
                    <h1 class="font-extrabold text-xl sm:text-2xl text-[#14161C] tracking-tight truncate leading-tight">
                        Reglamento Interno Institucional (IETA)
                    </h1>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a
                        v-if="reglamento.archivo_url"
                        :href="reglamento.archivo_url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1.5 px-3 py-2 bg-emerald-50 text-emerald-700 border border-emerald-200/80 rounded-xl text-xs font-bold hover:bg-emerald-100 transition shadow-xs"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Ver PDF Actual</span>
                    </a>
                </div>
            </div>
        </template>

        <div class="max-w-5xl mx-auto pb-12">
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Tarjeta 1: Información General -->
                <div class="bg-white rounded-2xl p-6 shadow-xs border border-gray-100 space-y-5">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <h2 class="text-base font-extrabold text-slate-800">Datos del Reglamento</h2>
                            <p class="text-xs text-slate-400 mt-0.5">
                                Información y título visible para la comunidad universitaria y visitantes.
                            </p>
                        </div>
                        <span class="text-[11px] font-bold px-2.5 py-1 rounded-md bg-blue-50 text-[#35449F]">
                            Módulo Singleton
                        </span>
                    </div>

                    <!-- Título -->
                    <div>
                        <InputLabel for="reg_titulo" value="Título Institucional *" />
                        <TextInput
                            id="reg_titulo"
                            v-model="form.reg_titulo"
                            type="text"
                            required
                            placeholder="Ej. Reglamento Interno del Instituto de Estadística Teórica y Aplicada"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.reg_titulo" class="mt-1" />
                    </div>

                    <!-- Descripción breve -->
                    <div>
                        <InputLabel for="reg_descripcion" value="Descripción / Preámbulo (Opcional)" />
                        <textarea
                            id="reg_descripcion"
                            v-model="form.reg_descripcion"
                            rows="2"
                            placeholder="Breve reseña sobre la vigencia, aprobación por Honorable Consejo Facultativo o alcance..."
                            class="mt-1 block w-full rounded-xl border-gray-300 shadow-xs focus:border-[#35449F] focus:ring-[#35449F] text-sm"
                        ></textarea>
                        <InputError :message="form.errors.reg_descripcion" class="mt-1" />
                    </div>
                </div>

                <!-- Tarjeta 2: Documento PDF Adjunto -->
                <div class="bg-white rounded-2xl p-6 shadow-xs border border-gray-100 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <h2 class="text-base font-extrabold text-slate-800">Documento Oficial en PDF</h2>
                            <p class="text-xs text-slate-400 mt-0.5">
                                Archivo descargable disponible para los usuarios del portal web institucional (hasta 25MB).
                            </p>
                        </div>
                    </div>

                    <!-- Estado actual del archivo si existe -->
                    <div v-if="reglamento.archivo_url" class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="size-11 rounded-xl bg-red-50 text-red-600 flex items-center justify-center shrink-0 border border-red-200/60">
                                <svg class="size-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 2l5 5h-5V4zm-3 12.5a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-4a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v4zm4-2a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-2a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v2z"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <span class="text-xs font-bold text-slate-800 truncate block">
                                    {{ reglamento.archivo_nombre || 'Reglamento_Interno_IETA.pdf' }}
                                </span>
                                <span class="text-[11px] text-slate-400 block mt-0.5">
                                    Tamaño: {{ formatBytes(reglamento.archivo_size) }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <a
                                :href="reglamento.archivo_url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-slate-700 hover:text-[#35449F] text-xs font-bold shadow-xs transition flex items-center gap-1.5"
                            >
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <span>Descargar</span>
                            </a>
                            <button
                                type="button"
                                @click="confirmingDeleteFile = true"
                                class="px-3 py-1.5 rounded-xl bg-red-50 text-red-600 hover:bg-red-100 text-xs font-bold transition cursor-pointer flex items-center gap-1.5"
                            >
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                <span>Eliminar PDF</span>
                            </button>
                        </div>
                    </div>

                    <!-- Dropzone o selector de nuevo archivo PDF -->
                    <div>
                        <div
                            v-if="!selectedFileName"
                            @click="triggerFileInput"
                            class="border-2 border-dashed border-slate-200 hover:border-[#35449F] rounded-2xl p-6 text-center cursor-pointer transition bg-slate-50/50 hover:bg-blue-50/20 group"
                        >
                            <div class="size-12 mx-auto rounded-xl bg-white border border-slate-200 text-slate-400 group-hover:text-[#35449F] flex items-center justify-center transition shadow-xs">
                                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                            </div>
                            <p class="mt-2 text-xs font-bold text-slate-700 group-hover:text-[#35449F] transition">
                                {{ reglamento.archivo_url ? 'Haga clic para subir un nuevo PDF que sustituya al actual' : 'Haga clic para seleccionar el archivo PDF del reglamento' }}
                            </p>
                            <p class="mt-0.5 text-[11px] text-slate-400">
                                Sólo archivos en formato PDF (Tamaño máximo: 25 MB)
                            </p>
                        </div>

                        <!-- Archivo seleccionado pendiente de guardar -->
                        <div v-else class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2 min-w-0">
                                <svg class="size-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-xs font-bold text-emerald-800 truncate">
                                    Nuevo archivo seleccionado: {{ selectedFileName }}
                                </span>
                            </div>
                            <button
                                type="button"
                                @click="removeSelectedFile"
                                class="text-xs font-bold text-red-600 hover:text-red-800 transition cursor-pointer shrink-0"
                            >
                                Cancelar selección
                            </button>
                        </div>

                        <input
                            ref="fileInput"
                            type="file"
                            accept="application/pdf"
                            @change="handleFileChange"
                            class="hidden"
                        />

                        <InputError :message="form.errors.archivo_reglamento" class="mt-2" />
                    </div>
                </div>

                <!-- Tarjeta 3: Contenido Digital Estructurado (EditorJS) -->
                <div class="bg-white rounded-2xl p-6 shadow-xs border border-gray-100 space-y-4">
                    <div class="border-b border-slate-100 pb-3">
                        <h2 class="text-base font-extrabold text-slate-800">Contenido Digital / Capítulos del Reglamento</h2>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Permite leer los artículos y capítulos del reglamento directamente en el sitio web institucional sin necesidad de descargar el PDF.
                        </p>
                    </div>

                    <div class="min-h-[300px]">
                        <EditorJs
                            v-model="form.reg_contenido"
                            placeholder="Escriba o pegue aquí los capítulos, artículos y disposiciones del reglamento..."
                        />
                    </div>
                    <InputError :message="form.errors.reg_contenido" class="mt-1" />
                </div>

                <!-- Botón de Guardado -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <PrimaryButton
                        type="submit"
                        :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                        :disabled="form.processing"
                        class="gap-1.5"
                    >
                        <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span>Guardar Reglamento Interno</span>
                    </PrimaryButton>
                </div>
            </form>
        </div>

        <!-- Modal para confirmar eliminación del PDF -->
        <ConfirmationModal :show="confirmingDeleteFile" @close="confirmingDeleteFile = false">
            <template #title>
                Eliminar Archivo PDF del Reglamento
            </template>
            <template #content>
                ¿Está seguro de eliminar el archivo PDF adjunto? Esta acción borrará el documento del servidor, pero mantendrá el texto digital configurado.
            </template>
            <template #footer>
                <SecondaryButton @click="confirmingDeleteFile = false" class="mr-2">
                    Cancelar
                </SecondaryButton>
                <DangerButton @click="deleteExistingPdf">
                    Sí, eliminar archivo PDF
                </DangerButton>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>
