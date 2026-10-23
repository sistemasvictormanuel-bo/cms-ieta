<script setup>
import { ref, onUnmounted } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import EditorJs from '@/Components/EditorJs.vue';

const props = defineProps({
    personas: {
        type: Array,
        default: () => [],
    },
});

const today = new Date().toISOString().split('T')[0];

const form = useForm({
    persona_id: null,
    not_titulo: '',
    not_descripcion: '',
    not_fecha: today,
    not_destacado: false,
    not_publicado: true,
    not_contenido: { blocks: [] },
    not_url: '',
    not_portada: null,
});

const imagePreview = ref(null);
const fileInput = ref(null);

const handleImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        if (imagePreview.value) {
            URL.revokeObjectURL(imagePreview.value);
        }
        form.not_portada = file;
        imagePreview.value = URL.createObjectURL(file);
    }
};

const removeImage = () => {
    if (imagePreview.value) {
        URL.revokeObjectURL(imagePreview.value);
        imagePreview.value = null;
    }
    form.not_portada = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const submit = () => {
    form.post(route('admin.noticias.store'), {
        forceFormData: true,
        preserveScroll: true,
    });
};

onUnmounted(() => {
    if (imagePreview.value) {
        URL.revokeObjectURL(imagePreview.value);
    }
});
</script>

<template>
    <AppLayout title="Nueva Noticia">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 w-full">
                <div>
                    <h1 class="font-black text-2xl text-[#14161C] tracking-tight">
                        Redactar Noticia
                    </h1>
                    <p class="text-[14.5px] text-gray-600 font-medium mt-0.5">
                        Crea un nuevo comunicado, artículo divulgativo o novedad informativa.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Link :href="route('admin.noticias.index')">
                        <SecondaryButton class="cursor-pointer">
                            ← Volver a Noticias
                        </SecondaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="max-w-6xl mx-auto pb-12">
            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Columna Izquierda: Contenido Principal (2 columnas) -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Tarjeta Título y Resumen -->
                        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-xs space-y-5">
                            <h2 class="font-extrabold text-[16.5px] text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                                <svg class="size-5 text-[#35449F]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                <span>Información Principal</span>
                            </h2>

                            <!-- Título -->
                            <div>
                                <InputLabel value="Título de la Noticia *" class="font-bold text-slate-800 text-[14.5px] mb-1.5" />
                                <TextInput
                                    v-model="form.not_titulo"
                                    type="text"
                                    placeholder="Ej. IETA presenta nuevos avances en analítica estadística aplicada 2026"
                                    class="w-full text-[15px] font-medium"
                                    required
                                />
                                <InputError :message="form.errors.not_titulo" class="mt-1.5" />
                            </div>

                            <!-- Resumen / Bajada de Noticia -->
                            <div>
                                <InputLabel value="Descripción Breve / Resumen *" class="font-bold text-slate-800 text-[14.5px] mb-1.5" />
                                <p class="text-xs text-slate-500 mb-2">
                                    Párrafo introductorio visible en listados, tarjetas y redes sociales (máx. 1000 caracteres).
                                </p>
                                <textarea
                                    v-model="form.not_descripcion"
                                    rows="3"
                                    placeholder="Síntesis de la noticia que capte la atención del lector..."
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50/50 p-3 text-[14.5px] font-medium text-slate-800 focus:bg-white focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/20 transition-all"
                                ></textarea>
                                <InputError :message="form.errors.not_descripcion" class="mt-1.5" />
                            </div>
                        </div>

                        <!-- Tarjeta Editor Enriquecido Editor.js -->
                        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-xs space-y-4">
                            <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                                <div>
                                    <h2 class="font-extrabold text-[16.5px] text-slate-900 flex items-center gap-2">
                                        <svg class="size-5 text-[#35449F]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                        </svg>
                                        <span>Cuerpo Detallado de la Noticia (Editor por Bloques)</span>
                                    </h2>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        Inserta párrafos, subtítulos, listas, imágenes subidas directamente y citas.
                                    </p>
                                </div>
                            </div>

                            <EditorJs
                                v-model="form.not_contenido"
                                placeholder="Escribe el desarrollo completo de la noticia, añade encabezados, imágenes o listas..."
                            />
                            <InputError :message="form.errors.not_contenido" class="mt-2" />
                        </div>
                    </div>

                    <!-- Columna Derecha: Configuración y Publicación (1 columna) -->
                    <div class="space-y-6">
                        <!-- Tarjeta Publicación & Estados -->
                        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-xs space-y-5">
                            <h2 class="font-extrabold text-[16px] text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                                <svg class="size-5 text-[#35449F]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>Configuración de Publicación</span>
                            </h2>

                            <!-- Estado: Publicado / Borrador -->
                            <div class="flex items-center justify-between p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                                <div>
                                    <p class="font-extrabold text-[14px] text-slate-800">Estado de Publicación</p>
                                    <p class="text-xs text-slate-500">
                                        {{ form.not_publicado ? 'Visible públicamente en el portal' : 'Guardado como borrador privado' }}
                                    </p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input
                                        v-model="form.not_publicado"
                                        type="checkbox"
                                        class="sr-only peer"
                                    />
                                    <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                                </label>
                            </div>

                            <!-- Estado: Destacado -->
                            <div class="flex items-center justify-between p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                                <div>
                                    <p class="font-extrabold text-[14px] text-slate-800">Noticia Destacada</p>
                                    <p class="text-xs text-slate-500">
                                        Aparecerá en el slider / sección principal
                                    </p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input
                                        v-model="form.not_destacado"
                                        type="checkbox"
                                        class="sr-only peer"
                                    />
                                    <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#35449F]"></div>
                                </label>
                            </div>

                            <!-- Fecha de Publicación -->
                            <div>
                                <InputLabel value="Fecha de la Noticia" class="font-bold text-slate-800 text-[14px] mb-1.5" />
                                <TextInput
                                    v-model="form.not_fecha"
                                    type="date"
                                    class="w-full text-[14.5px] font-medium"
                                />
                                <InputError :message="form.errors.not_fecha" class="mt-1" />
                            </div>

                            <!-- Redactor / Persona -->
                            <div>
                                <InputLabel value="Redactor / Autor Registrado" class="font-bold text-slate-800 text-[14px] mb-1.5" />
                                <select
                                    v-model="form.persona_id"
                                    class="w-full py-2.5 px-3 bg-slate-50 border border-slate-200 rounded-xl text-[14px] font-medium text-slate-800 focus:bg-white focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/20 transition-all"
                                >
                                    <option :value="null">Institucional (IETA - Dirección General)</option>
                                    <option v-for="persona in personas" :key="persona.id" :value="persona.id">
                                        {{ persona.nombre_completo || persona.p_nombre }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.persona_id" class="mt-1" />
                            </div>

                            <!-- Enlace Externo / URL Adicional -->
                            <div>
                                <InputLabel value="Enlace Externo / Fuente Oficial (Opcional)" class="font-bold text-slate-800 text-[14px] mb-1.5" />
                                <TextInput
                                    v-model="form.not_url"
                                    type="url"
                                    placeholder="https://ejemplo.com/comunicado-oficial"
                                    class="w-full text-[14px] font-medium"
                                />
                                <InputError :message="form.errors.not_url" class="mt-1" />
                            </div>
                        </div>

                        <!-- Tarjeta Imagen de Portada -->
                        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-xs space-y-4">
                            <h2 class="font-extrabold text-[16px] text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
                                <svg class="size-5 text-[#35449F]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>Imagen de Portada</span>
                            </h2>

                            <p class="text-xs text-slate-500">
                                Imagen destacada para encabezados y miniaturas (PNG, JPG o WEBP, máx. 10MB).
                            </p>

                            <!-- Vista previa si existe -->
                            <div v-if="imagePreview" class="relative group rounded-xl overflow-hidden border border-slate-200">
                                <img
                                    :src="imagePreview"
                                    alt="Vista previa de portada"
                                    class="w-full h-44 object-cover"
                                />
                                <button
                                    type="button"
                                    class="absolute top-2 right-2 bg-rose-600 text-white rounded-lg p-1.5 shadow-md hover:bg-rose-700 transition-colors cursor-pointer"
                                    title="Quitar imagen"
                                    @click="removeImage"
                                >
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Selector de Archivo -->
                            <div v-else>
                                <label
                                    class="flex flex-col items-center justify-center border-2 border-dashed border-slate-200 rounded-xl p-6 bg-slate-50 hover:bg-slate-100/60 hover:border-[#35449F]/50 transition-all cursor-pointer"
                                >
                                    <svg class="size-9 text-slate-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span class="text-xs font-bold text-[#35449F]">Seleccionar o arrastrar imagen</span>
                                    <span class="text-[11px] text-slate-400 mt-1">Recomendado: 1200x630 píxeles</span>
                                    <input
                                        ref="fileInput"
                                        type="file"
                                        accept="image/png, image/jpeg, image/jpg, image/webp"
                                        class="hidden"
                                        @change="handleImageChange"
                                    />
                                </label>
                            </div>
                            <InputError :message="form.errors.not_portada" class="mt-1" />
                        </div>

                        <!-- Botones de Acción -->
                        <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-xs space-y-3">
                            <PrimaryButton
                                type="submit"
                                :disabled="form.processing"
                                class="w-full justify-center py-3 text-[15px] font-black cursor-pointer shadow-sm hover:shadow-md transition-all"
                            >
                               
                                <span>{{ form.processing ? 'Guardando Noticia...' : 'Guardar y Publicar Noticia' }}</span>
                            </PrimaryButton>

                            <Link :href="route('admin.noticias.index')" class="block">
                                <SecondaryButton class="w-full justify-center py-2.5 text-[14px] cursor-pointer">
                                    Cancelar
                                </SecondaryButton>
                            </Link>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
