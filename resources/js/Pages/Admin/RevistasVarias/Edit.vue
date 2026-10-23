<script setup>
import { ref, onUnmounted } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    revista: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    _method: 'put',
    rev_titulo: props.revista?.rev_titulo || '',
    rev_descripcion: props.revista?.rev_descripcion || '',
    rev_url_enlace: props.revista?.rev_url_enlace || '',
    rev_entidad: props.revista?.rev_entidad || '',
    rev_issn: props.revista?.rev_issn || '',
    rev_orden: props.revista?.rev_orden ?? 0,
    rev_activo: Boolean(props.revista?.rev_activo),
    portada: null,
});

const newImagePreview = ref(null);
const fileInput = ref(null);

const handleImageChange = (e) => {
    const file = e.target.files?.[0];
    if (file) {
        if (newImagePreview.value) {
            URL.revokeObjectURL(newImagePreview.value);
        }
        form.portada = file;
        newImagePreview.value = URL.createObjectURL(file);
    }
};

const cancelNewImage = () => {
    if (newImagePreview.value) {
        URL.revokeObjectURL(newImagePreview.value);
        newImagePreview.value = null;
    }
    form.portada = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const submit = () => {
    form.post(route('admin.revistas-varias.update', props.revista.id), {
        forceFormData: true,
        preserveScroll: true,
    });
};

onUnmounted(() => {
    if (newImagePreview.value) {
        URL.revokeObjectURL(newImagePreview.value);
    }
});
</script>

<template>
    <AppLayout :title="`Editar: ${revista.rev_titulo}`">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 w-full min-w-0">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-1.5 text-[11px] font-semibold text-slate-400 mb-0.5 leading-none">
                        <Link :href="route('dashboard')" class="hover:text-[#35449F]">Inicio</Link>
                        <span>/</span>
                        <Link :href="route('admin.revistas-varias.index')" class="hover:text-[#35449F]">Revistas de Referencia</Link>
                        <span>/</span>
                        <span class="text-slate-700 font-bold truncate">Editar Revista</span>
                    </div>
                    <h1 class="font-extrabold text-xl sm:text-2xl text-[#14161C] tracking-tight truncate leading-tight">
                        Modificar Revista de Referencia
                    </h1>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <Link :href="route('admin.revistas-varias.show', revista.id)">
                        <SecondaryButton class="gap-1.5">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <span>Ver Detalle</span>
                        </SecondaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="max-w-4xl mx-auto pb-12">
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Tarjeta 1: Información Principal -->
                <div class="bg-white rounded-2xl p-6 shadow-xs border border-gray-100 space-y-5">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <h2 class="text-base font-extrabold text-slate-800">Datos Principales</h2>
                            <p class="text-xs text-slate-400 mt-0.5">
                                Información bibliográfica y enlace hacia la revista de referencia.
                            </p>
                        </div>
                    </div>

                    <!-- Título -->
                    <div>
                        <InputLabel for="rev_titulo" value="Título de la Revista *" />
                        <TextInput
                            id="rev_titulo"
                            v-model="form.rev_titulo"
                            type="text"
                            required
                            placeholder="Ej. Revista Varianza o Revista Colombiana de Estadística"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.rev_titulo" class="mt-1" />
                    </div>

                    <!-- URL / Enlace Web -->
                    <div>
                        <InputLabel for="rev_url_enlace" value="Dirección Web / Enlace (URL) *" />
                        <TextInput
                            id="rev_url_enlace"
                            v-model="form.rev_url_enlace"
                            type="url"
                            required
                            placeholder="https://revistas.unal.edu.co/index.php/estad"
                            class="mt-1 block w-full"
                        />
                        <p class="text-[11px] text-slate-400 mt-1">Debe incluir el protocolo https:// o http://</p>
                        <InputError :message="form.errors.rev_url_enlace" class="mt-1" />
                    </div>

                    <!-- Fila: Entidad e ISSN -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="rev_entidad" value="Entidad / Institución Editora" />
                            <TextInput
                                id="rev_entidad"
                                v-model="form.rev_entidad"
                                type="text"
                                placeholder="Ej. Universidad Nacional de Colombia / IETA - UMSA"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="form.errors.rev_entidad" class="mt-1" />
                        </div>

                        <div>
                            <InputLabel for="rev_issn" value="Código ISSN (Opcional)" />
                            <TextInput
                                id="rev_issn"
                                v-model="form.rev_issn"
                                type="text"
                                placeholder="Ej. 0120-1751 o 2076-6416"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="form.errors.rev_issn" class="mt-1" />
                        </div>
                    </div>

                    <!-- Descripción -->
                    <div>
                        <InputLabel for="rev_descripcion" value="Descripción / Enfoque Temático (Opcional)" />
                        <textarea
                            id="rev_descripcion"
                            v-model="form.rev_descripcion"
                            rows="3"
                            placeholder="Breve reseña sobre el contenido de la revista..."
                            class="mt-1 block w-full rounded-xl border-gray-300 shadow-xs focus:border-[#35449F] focus:ring-[#35449F] text-sm"
                        ></textarea>
                        <InputError :message="form.errors.rev_descripcion" class="mt-1" />
                    </div>
                </div>

                <!-- Tarjeta 2: Portada o Logotipo -->
                <div class="bg-white rounded-2xl p-6 shadow-xs border border-gray-100 space-y-4">
                    <div class="border-b border-slate-100 pb-3">
                        <h2 class="text-base font-extrabold text-slate-800">Logotipo o Portada de la Revista</h2>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Puede conservar la imagen actual o subir un nuevo archivo para sustituirlo.
                        </p>
                    </div>

                    <div class="relative rounded-2xl overflow-hidden border border-slate-200 bg-slate-100 h-48 flex items-center justify-center p-3 group">
                        <img
                            v-if="newImagePreview"
                            :src="newImagePreview"
                            alt="Nueva imagen"
                            class="max-h-full max-w-full object-contain"
                        />
                        <img
                            v-else-if="revista.portada_url"
                            :src="revista.portada_url"
                            :alt="revista.rev_titulo"
                            class="max-h-full max-w-full object-contain"
                        />
                        <div v-else class="text-center text-slate-400">
                            <svg class="size-8 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <span class="text-xs mt-1 block">Sin imagen asociada</span>
                        </div>

                        <div class="absolute inset-0 bg-slate-900/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                            <button
                                type="button"
                                @click="fileInput?.click()"
                                class="px-3 py-1.5 rounded-xl bg-white text-slate-800 text-xs font-bold shadow-md hover:bg-slate-100 transition cursor-pointer"
                            >
                                {{ newImagePreview || revista.portada_url ? 'Cambiar imagen' : 'Subir imagen' }}
                            </button>
                            <button
                                v-if="newImagePreview"
                                type="button"
                                @click="cancelNewImage"
                                class="px-3 py-1.5 rounded-xl bg-slate-800 text-white text-xs font-bold shadow-md hover:bg-slate-700 transition cursor-pointer"
                            >
                                Revertir
                            </button>
                        </div>
                    </div>

                    <input
                        ref="fileInput"
                        type="file"
                        accept="image/jpeg,image/png,image/webp,image/avif,image/jpg"
                        @change="handleImageChange"
                        class="hidden"
                    />

                    <p v-if="newImagePreview" class="text-xs font-semibold text-emerald-600 flex items-center gap-1">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        Nueva imagen seleccionada para subir al guardar.
                    </p>

                    <InputError :message="form.errors.portada" class="mt-2" />
                </div>

                <!-- Tarjeta 3: Configuración de Publicación -->
                <div class="bg-white rounded-2xl p-6 shadow-xs border border-gray-100 space-y-4">
                    <div class="border-b border-slate-100 pb-3">
                        <h2 class="text-base font-extrabold text-slate-800">Orden y Visibilidad</h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <InputLabel for="rev_orden" value="Orden de Visualización" />
                            <TextInput
                                id="rev_orden"
                                v-model.number="form.rev_orden"
                                type="number"
                                min="0"
                                class="mt-1 block w-full"
                            />
                            <p class="text-[11px] text-slate-400 mt-1">Prioridad ascendente (0 = primera).</p>
                            <InputError :message="form.errors.rev_orden" class="mt-1" />
                        </div>

                        <div class="flex items-center gap-3 pt-6">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input
                                    type="checkbox"
                                    v-model="form.rev_activo"
                                    class="sr-only peer"
                                />
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                            </label>
                            <div>
                                <span class="text-sm font-bold text-slate-800 block">Visible en Portal Web</span>
                                <span class="text-[11px] text-slate-400">Mostrar a los visitantes</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <Link :href="route('admin.revistas-varias.index')">
                        <SecondaryButton type="button">
                            Cancelar
                        </SecondaryButton>
                    </Link>
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
                        <span>Guardar Cambios</span>
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
