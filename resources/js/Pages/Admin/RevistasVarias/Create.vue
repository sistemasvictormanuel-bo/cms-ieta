<script setup>
import { ref, onUnmounted } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const form = useForm({
    rev_titulo: '',
    rev_descripcion: '',
    rev_url_enlace: '',
    rev_entidad: '',
    rev_issn: '',
    rev_orden: 0,
    rev_activo: true,
    portada: null,
});

const imagePreview = ref(null);
const fileInput = ref(null);

const handleImageChange = (e) => {
    const file = e.target.files?.[0];
    if (file) {
        if (imagePreview.value) {
            URL.revokeObjectURL(imagePreview.value);
        }
        form.portada = file;
        imagePreview.value = URL.createObjectURL(file);
    }
};

const removeImage = () => {
    if (imagePreview.value) {
        URL.revokeObjectURL(imagePreview.value);
        imagePreview.value = null;
    }
    form.portada = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const submit = () => {
    form.post(route('admin.revistas-varias.store'), {
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
    <AppLayout title="Nueva Revista de Referencia">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 w-full min-w-0">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-1.5 text-[11px] font-semibold text-slate-400 mb-0.5 leading-none">
                        <Link :href="route('dashboard')" class="hover:text-[#35449F]">Inicio</Link>
                        <span>/</span>
                        <Link :href="route('admin.revistas-varias.index')" class="hover:text-[#35449F]">Revistas de Referencia</Link>
                        <span>/</span>
                        <span class="text-slate-700 font-bold">Nueva Revista</span>
                    </div>
                    <h1 class="font-extrabold text-xl sm:text-2xl text-[#14161C] tracking-tight truncate leading-tight">
                        Registrar Revista de Referencia
                    </h1>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <Link :href="route('admin.revistas-varias.index')">
                        <SecondaryButton class="gap-1.5">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            <span>Volver</span>
                        </SecondaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="max-w-4xl mx-auto pb-12">
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Tarjeta 1: Información Principal -->
                <div class="bg-white rounded-2xl p-6 shadow-xs border border-gray-100 space-y-5">
                    <div class="border-b border-slate-100 pb-3">
                        <h2 class="text-base font-extrabold text-slate-800">Datos Principales</h2>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Información bibliográfica y de enlace hacia la revista de referencia o indizada.
                        </p>
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
                            placeholder="Breve reseña sobre el contenido de la revista, área de indexación o importancia metodológica..."
                            class="mt-1 block w-full rounded-xl border-gray-300 shadow-xs focus:border-[#35449F] focus:ring-[#35449F] text-sm"
                        ></textarea>
                        <InputError :message="form.errors.rev_descripcion" class="mt-1" />
                    </div>
                </div>

                <!-- Tarjeta 2: Portada o Logo Opcional -->
                <div class="bg-white rounded-2xl p-6 shadow-xs border border-gray-100 space-y-4">
                    <div class="border-b border-slate-100 pb-3">
                        <h2 class="text-base font-extrabold text-slate-800">Logotipo o Tapa de la Revista (Opcional)</h2>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Imagen ilustrativa o logo de la revista para la tarjeta en el portal web (JPG, PNG, WEBP).
                        </p>
                    </div>

                    <div
                        v-if="!imagePreview"
                        @click="fileInput?.click()"
                        class="border-2 border-dashed border-slate-200 hover:border-[#35449F] rounded-2xl p-6 text-center cursor-pointer transition bg-slate-50/50 hover:bg-blue-50/20 group"
                    >
                        <div class="size-12 mx-auto rounded-xl bg-white border border-slate-200 text-slate-400 group-hover:text-[#35449F] flex items-center justify-center transition shadow-xs">
                            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <p class="mt-2 text-xs font-bold text-slate-700 group-hover:text-[#35449F] transition">
                            Haga clic para subir una imagen de portada o logo
                        </p>
                        <p class="mt-0.5 text-[11px] text-slate-400">
                            Formatos: WEBP, PNG, JPG (Hasta 10 MB)
                        </p>
                    </div>

                    <div v-else class="relative rounded-2xl overflow-hidden border border-slate-200 bg-slate-100 h-48 flex items-center justify-center p-3 group">
                        <img
                            :src="imagePreview"
                            alt="Previsualización"
                            class="max-h-full max-w-full object-contain"
                        />
                        <div class="absolute inset-0 bg-slate-900/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                            <button
                                type="button"
                                @click="fileInput?.click()"
                                class="px-3 py-1.5 rounded-xl bg-white text-slate-800 text-xs font-bold shadow-md hover:bg-slate-100 transition cursor-pointer"
                            >
                                Cambiar
                            </button>
                            <button
                                type="button"
                                @click="removeImage"
                                class="px-3 py-1.5 rounded-xl bg-red-600 text-white text-xs font-bold shadow-md hover:bg-red-700 transition cursor-pointer"
                            >
                                Quitar
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
                        <span>Guardar Revista</span>
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
