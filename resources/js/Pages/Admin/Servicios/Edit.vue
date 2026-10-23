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
    servicio: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    sv_titulo: props.servicio.sv_titulo || '',
    sv_descripcion: props.servicio.sv_descripcion || '',
    sv_contenido: props.servicio.sv_contenido || { blocks: [] },
    sv_imagen: null,
});

const imagePreview = ref(props.servicio.imagen_url || null);
let objectUrlCreated = false;

const handleImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        if (objectUrlCreated && imagePreview.value) {
            URL.revokeObjectURL(imagePreview.value);
        }
        form.sv_imagen = file;
        imagePreview.value = URL.createObjectURL(file);
        objectUrlCreated = true;
    }
};

const submit = () => {
    // Al subir archivos en actualización con Inertia, usamos POST con _method: 'PUT'
    form.transform((data) => ({
        ...data,
        _method: 'put',
    })).post(route('admin.servicios.update', props.servicio.id), {
        forceFormData: true,
    });
};

onUnmounted(() => {
    if (objectUrlCreated && imagePreview.value) {
        URL.revokeObjectURL(imagePreview.value);
    }
});
</script>

<template>
    <AppLayout :title="'Editar: ' + (servicio.sv_titulo || 'Servicio')">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 w-full">
                <div>
                    <h1 class="font-extrabold text-2xl text-[#14161C] tracking-tight">
                        Editar Servicio
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500 font-medium">
                        Actualiza la información, imágenes o contenido detallado del servicio seleccionado.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Link :href="route('admin.servicios.index')">
                        <SecondaryButton class="cursor-pointer">
                            ← Volver al listado
                        </SecondaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="max-w-5xl mx-auto pb-12">
            <div class="bg-white rounded-2xl p-6 md:p-8 shadow-[6px_6px_20px_rgba(0,0,0,0.08),-6px_-6px_20px_rgba(255,255,255,0.9)] border border-gray-100/80">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Título -->
                    <div>
                        <InputLabel for="sv_titulo" value="Título del Servicio *" class="font-bold text-[#14161C]" />
                        <TextInput
                            id="sv_titulo"
                            v-model="form.sv_titulo"
                            type="text"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError :message="form.errors.sv_titulo" class="mt-2" />
                    </div>

                    <!-- Descripción breve -->
                    <div>
                        <InputLabel for="sv_descripcion" value="Descripción breve / Resumen" class="font-bold text-[#14161C]" />
                        <textarea
                            id="sv_descripcion"
                            v-model="form.sv_descripcion"
                            rows="3"
                            class="mt-1 block w-full rounded-xl border-gray-300 shadow-xs focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 text-sm leading-relaxed"
                            placeholder="Breve introducción o resumen que se mostrará en los listados..."
                        ></textarea>
                        <InputError :message="form.errors.sv_descripcion" class="mt-2" />
                    </div>

                    <!-- Imagen del servicio (Spatie Media) -->
                    <div>
                        <InputLabel for="sv_imagen" value="Cambiar Imagen Ilustrativa (Opcional)" class="font-bold text-[#14161C]" />
                        <input
                            id="sv_imagen"
                            type="file"
                            accept="image/png, image/jpeg, image/webp"
                            @change="handleImageChange"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#35449F]/10 file:text-[#35449F] hover:file:bg-[#35449F]/20 cursor-pointer"
                        />
                        <InputError :message="form.errors.sv_imagen" class="mt-2" />

                        <!-- Vista previa de la imagen actual o nueva -->
                        <div v-if="imagePreview" class="mt-3">
                            <p class="text-xs text-gray-500 mb-1 font-semibold">Imagen actual / seleccionada:</p>
                            <img
                                :src="imagePreview"
                                alt="Imagen de Servicio"
                                class="h-36 w-60 object-cover rounded-xl border border-gray-200 shadow-xs"
                            />
                        </div>
                    </div>

                    <!-- Contenido Enriquecido (Editor.js) -->
                    <div>
                        <InputLabel value="Contenido Detallado y Alcance (Editor por Bloques) *" class="font-bold text-[#14161C] mb-2" />
                        <p class="text-xs text-gray-500 mb-3">
                            Edita el contenido estructurado, listas de verificación, fotos, videos y especificaciones del servicio.
                        </p>
                        <EditorJs
                            v-model="form.sv_contenido"
                            placeholder="Escribe aquí los detalles del servicio, listas de requisitos, procedimientos, tablas o videos..."
                        />
                        <InputError :message="form.errors.sv_contenido" class="mt-2" />
                    </div>

                    <!-- Botones de Acción -->
                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
                        <Link :href="route('admin.servicios.index')">
                            <SecondaryButton class="cursor-pointer">Cancelar</SecondaryButton>
                        </Link>
                        <PrimaryButton :disabled="form.processing" class="cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ form.processing ? 'Actualizando...' : 'Actualizar Cambios' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
