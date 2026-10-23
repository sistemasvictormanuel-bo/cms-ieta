<script setup>
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    memoria: {
        type: Object,
        required: true,
    },
    autores: {
        type: Array,
        default: () => [],
    },
});

const etiquetasSugeridas = [
    'Memoria Anual',
    'Investigación Institucional',
    'Documento de Trabajo',
    'Tesis de Grado',
    'Monografía Científica',
    'Informe Técnico',
    'Ponencia / Seminario',
];

const tieneEnlaceExterno = ref(Boolean(props.memoria.mdi_enlace_url && props.memoria.mdi_enlace_url.trim() !== ''));
const fileName = ref('');

const formattedFecha = props.memoria.mdi_fecha
    ? (props.memoria.mdi_fecha.includes('T') ? props.memoria.mdi_fecha.split('T')[0] : props.memoria.mdi_fecha)
    : '';

const form = useForm({
    _method: 'put',
    autor_id: props.memoria.autor_id,
    mdi_titulo: props.memoria.mdi_titulo || '',
    mdi_descripcion: props.memoria.mdi_descripcion || '',
    mdi_etiqueta: props.memoria.mdi_etiqueta || '',
    mdi_fecha: formattedFecha,
    mdi_enlace_url: props.memoria.mdi_enlace_url || '',
    mdi_archivo: null,
});

const selectEtiqueta = (etiqueta) => {
    form.mdi_etiqueta = etiqueta;
};

const handleArchivoChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.mdi_archivo = file;
        fileName.value = file.name;
    } else {
        form.mdi_archivo = null;
        fileName.value = '';
    }
};

const submit = () => {
    if (!tieneEnlaceExterno.value) {
        form.mdi_enlace_url = null;
    }

    form.post(route('admin.memorias-investigacion.update', props.memoria.id), {
        forceFormData: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout title="Editar Memoria de Investigación">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 w-full">
                <div>
                    <h1 class="font-black text-2xl text-[#14161C] tracking-tight">
                        Editar Memoria de Investigación
                    </h1>
                    <p class="text-[14.5px] text-gray-600 font-medium">
                        Actualiza la información técnica, archivos o enlaces de la investigación.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Link :href="route('admin.memorias-investigacion.show', memoria.id)">
                        <SecondaryButton class="cursor-pointer text-[15px] font-bold px-4 py-2.5">
                            Ver Detalle
                        </SecondaryButton>
                    </Link>
                    <Link :href="route('admin.memorias-investigacion.index')">
                        <SecondaryButton class="cursor-pointer text-[15px] font-bold px-4 py-2.5">
                            ← Volver al listado
                        </SecondaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="max-w-4xl mx-auto pb-12">
            <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xs border border-gray-100">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Autor y Fecha -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Autor / Investigador -->
                        <div>
                            <InputLabel for="autor_id" value="Autor / Investigador Responsable *" class="text-[15.5px] font-bold text-gray-900" />
                            <select
                                id="autor_id"
                                v-model="form.autor_id"
                                class="mt-1.5 block w-full rounded-xl border-gray-300 py-3 px-3.5 text-[15.5px] font-medium focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 bg-white"
                                required
                            >
                                <option value="" disabled>Selecciona un autor...</option>
                                <option v-for="autor in autores" :key="autor.id" :value="autor.id">
                                    {{ autor.persona ? `${autor.persona.p_nombre} ${autor.persona.p_apellido_paterno || ''} ${autor.persona.p_apellido_materno || ''}`.trim() : `Autor #${autor.id}` }}
                                    {{ autor.tipo_autor ? `(${autor.tipo_autor.ta_nombre})` : '' }}
                                </option>
                            </select>
                            <InputError :message="form.errors.autor_id" class="mt-1.5" />
                        </div>

                        <!-- Fecha de Presentación / Registro -->
                        <div>
                            <InputLabel for="mdi_fecha" value="Fecha de la Memoria / Investigación *" class="text-[15.5px] font-bold text-gray-900" />
                            <TextInput
                                id="mdi_fecha"
                                v-model="form.mdi_fecha"
                                type="date"
                                class="mt-1.5 block w-full text-[15.5px]"
                                required
                            />
                            <InputError :message="form.errors.mdi_fecha" class="mt-1.5" />
                        </div>
                    </div>

                    <!-- Título -->
                    <div>
                        <InputLabel for="mdi_titulo" value="Título de la Memoria de Investigación *" class="text-[15.5px] font-bold text-gray-900" />
                        <TextInput
                            id="mdi_titulo"
                            v-model="form.mdi_titulo"
                            type="text"
                            class="mt-1.5 block w-full text-[15.5px]"
                            required
                        />
                        <InputError :message="form.errors.mdi_titulo" class="mt-1.5" />
                    </div>

                    <!-- Etiqueta / Categoría Dinámica -->
                    <div class="p-4 rounded-xl bg-gray-50/70 border border-gray-100 space-y-3">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <InputLabel for="mdi_etiqueta" value="Etiqueta / Tipo de Memoria *" class="text-[15.5px] font-bold text-gray-900" />
                            <span class="text-[12px] font-bold text-gray-500 bg-gray-100 px-2.5 py-0.5 rounded-full">
                                Clasificación en Portal
                            </span>
                        </div>

                        <TextInput
                            id="mdi_etiqueta"
                            v-model="form.mdi_etiqueta"
                            type="text"
                            class="block w-full text-[15.5px] bg-white"
                            placeholder="Escribe o selecciona una etiqueta rápida..."
                            required
                        />

                        <!-- Sugerencias de Etiquetas Rápidas -->
                        <div>
                            <p class="text-[12.5px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">
                                Sugerencias rápidas:
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <button
                                    v-for="sug in etiquetasSugeridas"
                                    :key="sug"
                                    type="button"
                                    @click="selectEtiqueta(sug)"
                                    :class="form.mdi_etiqueta === sug ? 'bg-[#35449F] text-white border-[#35449F]' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-100'"
                                    class="px-2.5 py-1 rounded-lg text-xs font-bold border transition cursor-pointer"
                                >
                                    {{ sug }}
                                </button>
                            </div>
                        </div>
                        <InputError :message="form.errors.mdi_etiqueta" class="mt-1" />
                    </div>

                    <!-- Descripción / Resumen -->
                    <div>
                        <InputLabel for="mdi_descripcion" value="Descripción / Resumen de la Investigación" class="text-[15.5px] font-bold text-gray-900" />
                        <textarea
                            id="mdi_descripcion"
                            v-model="form.mdi_descripcion"
                            rows="4"
                            class="mt-1.5 block w-full rounded-xl border-gray-300 p-3.5 text-[15.5px] font-medium focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 bg-white"
                            placeholder="Breve abstract, conclusiones principales o metodología de la memoria..."
                        ></textarea>
                        <InputError :message="form.errors.mdi_descripcion" class="mt-1.5" />
                    </div>

                    <!-- Archivos y Enlaces de la Investigación -->
                    <div class="space-y-4 pt-2">
                        <!-- Subida de Archivo Local / Archivo Actual -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <InputLabel for="mdi_archivo" value="Documento de Investigación (PDF, DOCX - máx. 50MB)" class="text-[15.5px] font-bold text-gray-900" />
                                <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                                    <input
                                        type="checkbox"
                                        v-model="tieneEnlaceExterno"
                                        class="rounded border-gray-300 text-[#35449F] focus:ring-[#35449F]/20 size-4"
                                    />
                                    <span class="text-[13.5px] font-bold text-[#35449F] hover:underline">
                                        ¿Enlace externo?
                                    </span>
                                </label>
                            </div>

                            <div v-if="memoria.archivo_url" class="p-3 bg-indigo-50/60 border border-indigo-100 rounded-xl text-xs font-bold text-[#35449F] flex items-center justify-between">
                                <span class="flex items-center gap-1.5">
                                    <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Archivo disponible en servidor (Spatie Media)
                                </span>
                                <a :href="memoria.archivo_url" target="_blank" class="underline text-[13px] hover:text-[#28357a]">Descargar actual</a>
                            </div>

                            <input
                                id="mdi_archivo"
                                type="file"
                                accept=".pdf,.doc,.docx,.zip,.rar"
                                @change="handleArchivoChange"
                                class="mt-1 block w-full text-xs text-gray-500 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-[13px] file:font-bold file:bg-[#35449F]/10 file:text-[#35449F] hover:file:bg-[#35449F]/20 cursor-pointer"
                            />
                            <p v-if="fileName" class="text-[13px] text-[#35449F] font-bold mt-1.5">
                                Nuevo archivo seleccionado: {{ fileName }}
                            </p>
                            <p class="text-[12.5px] text-gray-500 font-medium">
                                Subir un nuevo archivo reemplazará el documento anterior.
                            </p>
                            <InputError :message="form.errors.mdi_archivo" class="mt-1.5" />
                        </div>

                        <!-- Campo Dinámico: Enlace Externo (Google Drive, OneDrive, Repositorio, etc.) -->
                        <div v-if="tieneEnlaceExterno" class="p-4 rounded-xl bg-indigo-50/50 border border-indigo-100 space-y-2 transition-all">
                            <div class="flex items-center justify-between">
                                <InputLabel for="mdi_enlace_url" value="Enlace Externo a la Investigación (Google Drive, Dropbox, etc.)" class="text-[15px] font-bold text-gray-900" />
                                <div class="flex items-center gap-2">
                                    <a
                                        v-if="form.mdi_enlace_url"
                                        :href="form.mdi_enlace_url"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="text-[12.5px] font-bold text-[#35449F] hover:underline flex items-center gap-1"
                                    >
                                        <span>Probar enlace</span>
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                        </svg>
                                    </a>
                                    <span class="text-[12px] font-bold text-indigo-700 bg-indigo-100 px-2.5 py-0.5 rounded-full">
                                        Drive • OneDrive • Repositorio
                                    </span>
                                </div>
                            </div>
                            <TextInput
                                id="mdi_enlace_url"
                                v-model="form.mdi_enlace_url"
                                type="url"
                                class="block w-full text-[15.5px]"
                                placeholder="https://drive.google.com/file/d/... o enlace al repositorio"
                            />
                            <p class="text-[12.5px] text-gray-500 font-medium">
                                Opcional. Puedes mantener o actualizar este enlace externo hacia la nube.
                            </p>
                            <InputError :message="form.errors.mdi_enlace_url" class="mt-1" />
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <Link :href="route('admin.memorias-investigacion.index')">
                            <SecondaryButton class="cursor-pointer text-[15px] font-bold px-5 py-2.5">Cancelar</SecondaryButton>
                        </Link>
                        <PrimaryButton :disabled="form.processing" class="cursor-pointer text-[15.5px] font-bold px-6 py-2.5">
                            {{ form.processing ? 'Guardando...' : 'Guardar Cambios' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
