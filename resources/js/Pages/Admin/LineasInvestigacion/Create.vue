<script setup>
import { ref, onUnmounted } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
//import { route } from 'ziggy-js';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import EditorJs from '@/Components/EditorJs.vue';

const form = useForm({
    li_titulo: '',
    li_descripcion: '',
    li_contenido: { blocks: [] },
    li_imagen: null,
});

const imagePreview = ref(null);

const handleImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
       //limpar
        if (imagePreview.value) {
            URL.revokeObjectURL(imagePreview.value);
        }
        form.li_imagen = file;
        imagePreview.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    form.post(route('admin.lineas-investigacion.store'), {
        forceFormData: true,
    });
};

onUnmounted(() => {
    if (imagePreview.value) {
        URL.revokeObjectURL(imagePreview.value);
    }
});
</script>

<template>
    <AppLayout title="Nueva Línea de Investigación">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 w-full">
                <div>
                    <h1 class="font-extrabold text-2xl text-[#14161C] tracking-tight">
                        Nueva Línea de Investigación
                    </h1>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Link :href="route('admin.lineas-investigacion.index')">
                        <SecondaryButton>
                            ← Volver al listado
                        </SecondaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="max-w-5xl mx-auto ">
            <div class="bg-white rounded-2xl p-6 md:p-8 shadow-[6px_6px_20px_rgba(0,0,0,0.08),-6px_-6px_20px_rgba(255,255,255,0.9)] border border-gray-100/80">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Título -->
                    <div>
                        <InputLabel for="li_titulo" value="Título de la Línea *" />
                        <TextInput
                            id="li_titulo"
                            v-model="form.li_titulo"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="Ej: Inteligencia Artificial Aplicada a la Educación"
                            required
                        />
                        <InputError :message="form.errors.li_titulo" class="mt-2" />
                    </div>

                    <!-- Descripción breve -->
                    <div>
                        <InputLabel for="li_descripcion" value="Descripción breve / Resumen *" />
                        <textarea
                            id="li_descripcion"
                            v-model="form.li_descripcion"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                            placeholder="Breve introducción o resumen que se verá en las tarjetas..."
                            required
                        ></textarea>
                        <InputError :message="form.errors.li_descripcion" class="mt-2" />
                    </div>

                    <!-- Imagen de portada (Spatie Media) -->
                    <div>
                        <InputLabel for="li_imagen" value="Imagen de Portada (Opcional)" />
                        <input
                            id="li_imagen"
                            type="file"
                            accept="image/png, image/jpeg, image/webp"
                            @change="handleImageChange"
                            class="mt-1 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-900/40 dark:file:text-indigo-300"
                        />
                        <InputError :message="form.errors.li_imagen" class="mt-2" />

                        <!-- Preview de la imagen -->
                        <div v-if="imagePreview" class="mt-3">
                            <p class="text-xs text-gray-500 mb-1">Vista previa:</p>
                            <img
                                :src="imagePreview"
                                alt="Preview"
                                class="h-32 w-52 object-cover rounded-lg border border-gray-200 dark:border-gray-700"
                            />
                        </div>
                    </div>

                    <!-- Contenido Enriquecido (Editor.js) -->
                    <div>
                        <InputLabel value="Contenido Detallado (Bloques, Imágenes, Videos) *" class="mb-1" />
                        <EditorJs
                            v-model="form.li_contenido"
                            placeholder="Escribe el artículo, agrega encabezados, pega videos de YouTube o fotos..."
                        />
                        <InputError :message="form.errors.li_contenido" class="mt-2" />
                    </div>

                    <!-- Botones de Acción -->
                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <Link :href="route('admin.lineas-investigacion.index')">
                            <SecondaryButton>Cancelar</SecondaryButton>
                        </Link>
                        <PrimaryButton :disabled="form.processing">
                            {{ form.processing ? 'Guardando...' : 'Crear Línea de Investigación' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
