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
    categoriasSugeridas: {
        type: Array,
        default: () => [
            'Institucional',
            'Eventos y Actividades',
            'Investigación y Estadística',
            'Infraestructura y Ambientes',
            'Académico y Cursos',
            'Convenios y Autoridades',
            'Revista Varianza',
        ],
    },
});

const today = new Date().toISOString().split('T')[0];

const form = useForm({
    gal_titulo: '',
    gal_categoria: props.categoriasSugeridas?.[0] || 'Institucional',
    gal_fecha: today,
    gal_lugar: '',
    gal_descripcion: '',
    gal_orden: 0,
    gal_destacado: false,
    gal_activo: true,
    imagen: null,
});

const imagePreview = ref(null);
const fileInput = ref(null);
const isCustomCategory = ref(false);
const customCategoryInput = ref('');

const selectCategory = (cat) => {
    isCustomCategory.value = false;
    form.gal_categoria = cat;
};

const enableCustomCategory = () => {
    isCustomCategory.value = true;
    form.gal_categoria = customCategoryInput.value || '';
};

const onCustomCategoryChange = () => {
    form.gal_categoria = customCategoryInput.value;
};

const handleImageChange = (e) => {
    const file = e.target.files?.[0];
    if (file) {
        if (imagePreview.value) {
            URL.revokeObjectURL(imagePreview.value);
        }
        form.imagen = file;
        imagePreview.value = URL.createObjectURL(file);
    }
};

const removeImage = () => {
    if (imagePreview.value) {
        URL.revokeObjectURL(imagePreview.value);
        imagePreview.value = null;
    }
    form.imagen = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const submit = () => {
    form.post(route('admin.galeria.store'), {
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
    <AppLayout title="Subir Nueva Imagen - Galería">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 w-full min-w-0">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-1.5 text-[11px] font-semibold text-slate-400 mb-0.5 leading-none">
                        <Link :href="route('dashboard')" class="hover:text-[#35449F]">Inicio</Link>
                        <span>/</span>
                        <Link :href="route('admin.galeria.index')" class="hover:text-[#35449F]">Galería</Link>
                        <span>/</span>
                        <span class="text-slate-700 font-bold">Nueva Imagen</span>
                    </div>
                    <h1 class="font-extrabold text-xl sm:text-2xl text-[#14161C] tracking-tight truncate leading-tight">
                        Subir Fotografía a la Galería
                    </h1>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <Link :href="route('admin.galeria.index')">
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
                <!-- Tarjeta 1: Archivo Fotográfico -->
                <div class="bg-white rounded-2xl p-6 shadow-xs border border-gray-100 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-extrabold text-slate-800">Archivo de Imagen</h2>
                            <p class="text-xs text-slate-400 mt-0.5">
                                Seleccione la fotografía que se exhibirá en el sitio web institucional (JPG, PNG, WEBP, AVIF hasta 15MB).
                            </p>
                        </div>
                        <span class="text-[11px] font-bold px-2.5 py-1 rounded-md bg-amber-50 text-amber-700 border border-amber-200/60">
                            Requerido
                        </span>
                    </div>

                    <div
                        v-if="!imagePreview"
                        @click="fileInput?.click()"
                        class="border-2 border-dashed border-slate-200 hover:border-[#35449F] rounded-2xl p-8 text-center cursor-pointer transition bg-slate-50/50 hover:bg-blue-50/20 group"
                    >
                        <div class="size-14 mx-auto rounded-2xl bg-white border border-slate-200/80 text-slate-400 group-hover:text-[#35449F] group-hover:border-[#35449F]/30 flex items-center justify-center transition shadow-xs">
                            <svg class="size-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <p class="mt-3 text-sm font-bold text-slate-700 group-hover:text-[#35449F] transition">
                            Haga clic aquí para seleccionar o arrastre su imagen
                        </p>
                        <p class="mt-1 text-xs text-slate-400">
                            Formatos soportados: WEBP, JPG, PNG, AVIF (Resolución recomendada: 1200x800px o superior)
                        </p>
                    </div>

                    <!-- Previsualización -->
                    <div v-else class="relative rounded-2xl overflow-hidden border border-slate-200 bg-slate-900 group aspect-video sm:aspect-21/9 max-h-[380px] flex items-center justify-center">
                        <img
                            :src="imagePreview"
                            alt="Previsualización"
                            class="w-full h-full object-contain"
                        />
                        <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
                            <button
                                type="button"
                                @click="fileInput?.click()"
                                class="px-3.5 py-2 rounded-xl bg-white text-slate-800 text-xs font-bold shadow-md hover:bg-slate-100 transition cursor-pointer flex items-center gap-1.5"
                            >
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                                Cambiar imagen
                            </button>
                            <button
                                type="button"
                                @click="removeImage"
                                class="px-3.5 py-2 rounded-xl bg-red-600 text-white text-xs font-bold shadow-md hover:bg-red-700 transition cursor-pointer flex items-center gap-1.5"
                            >
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
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

                    <InputError :message="form.errors.imagen || form.errors.gal_imagen" class="mt-2" />
                </div>

                <!-- Tarjeta 2: Metadatos y Clasificación -->
                <div class="bg-white rounded-2xl p-6 shadow-xs border border-gray-100 space-y-5">
                    <h2 class="text-base font-extrabold text-slate-800">Información de la Fotografía</h2>

                    <!-- Título -->
                    <div>
                        <InputLabel for="gal_titulo" value="Título de la Fotografía *" />
                        <TextInput
                            id="gal_titulo"
                            v-model="form.gal_titulo"
                            type="text"
                            required
                            placeholder="Ej. Taller Internacional de Métodos Bayesianos 2026"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.gal_titulo" class="mt-1" />
                    </div>

                    <!-- Categoría -->
                    <div>
                        <InputLabel value="Categoría de la Imagen *" />
                        <div class="mt-2 flex flex-wrap gap-2">
                            <button
                                v-for="cat in props.categoriasSugeridas"
                                :key="cat"
                                type="button"
                                @click="selectCategory(cat)"
                                :class="!isCustomCategory && form.gal_categoria === cat ? 'bg-[#35449F] text-white shadow-xs font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 font-semibold'"
                                class="px-3 py-1.5 rounded-xl text-xs transition cursor-pointer"
                            >
                                {{ cat }}
                            </button>
                            <button
                                type="button"
                                @click="enableCustomCategory"
                                :class="isCustomCategory ? 'bg-[#35449F] text-white shadow-xs font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 font-semibold'"
                                class="px-3 py-1.5 rounded-xl text-xs transition cursor-pointer flex items-center gap-1"
                            >
                                <span>+ Otra categoría</span>
                            </button>
                        </div>

                        <!-- Input para categoría personalizada -->
                        <div v-if="isCustomCategory" class="mt-2">
                            <TextInput
                                v-model="customCategoryInput"
                                @input="onCustomCategoryChange"
                                type="text"
                                placeholder="Escribe el nombre de la nueva categoría..."
                                class="w-full text-xs"
                            />
                        </div>
                        <InputError :message="form.errors.gal_categoria" class="mt-1" />
                    </div>

                    <!-- Fila: Fecha y Lugar -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="gal_fecha" value="Fecha del Acontecimiento" />
                            <TextInput
                                id="gal_fecha"
                                v-model="form.gal_fecha"
                                type="date"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="form.errors.gal_fecha" class="mt-1" />
                        </div>

                        <div>
                            <InputLabel for="gal_lugar" value="Lugar / Ubicación" />
                            <TextInput
                                id="gal_lugar"
                                v-model="form.gal_lugar"
                                type="text"
                                placeholder="Ej. Auditorio Central UMSA, La Paz"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="form.errors.gal_lugar" class="mt-1" />
                        </div>
                    </div>

                    <!-- Descripción Contextual -->
                    <div>
                        <InputLabel for="gal_descripcion" value="Descripción / Contexto (Opcional)" />
                        <textarea
                            id="gal_descripcion"
                            v-model="form.gal_descripcion"
                            rows="3"
                            placeholder="Breve reseña o información del evento capturado en la fotografía para el pie de foto..."
                            class="mt-1 block w-full rounded-xl border-gray-300 shadow-xs focus:border-[#35449F] focus:ring-[#35449F] text-sm"
                        ></textarea>
                        <InputError :message="form.errors.gal_descripcion" class="mt-1" />
                    </div>
                </div>

                <!-- Tarjeta 3: Visibilidad y Configuración -->
                <div class="bg-white rounded-2xl p-6 shadow-xs border border-gray-100 space-y-4">
                    <h2 class="text-base font-extrabold text-slate-800">Publicación & Orden</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        <!-- Orden -->
                        <div>
                            <InputLabel for="gal_orden" value="Orden de Visualización" />
                            <TextInput
                                id="gal_orden"
                                v-model.number="form.gal_orden"
                                type="number"
                                min="0"
                                class="mt-1 block w-full"
                            />
                            <p class="text-[11px] text-slate-400 mt-1">Los números menores aparecen primero.</p>
                            <InputError :message="form.errors.gal_orden" class="mt-1" />
                        </div>

                        <!-- Switch Destacado -->
                        <div class="flex items-center gap-3 pt-6">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input
                                    type="checkbox"
                                    v-model="form.gal_destacado"
                                    class="sr-only peer"
                                />
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"></div>
                            </label>
                            <div>
                                <span class="text-sm font-bold text-slate-800 block">Fotografía Destacada</span>
                                <span class="text-[11px] text-slate-400">Mostrar en carruseles y secciones principales</span>
                            </div>
                        </div>

                        <!-- Switch Activo -->
                        <div class="flex items-center gap-3 pt-6">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input
                                    type="checkbox"
                                    v-model="form.gal_activo"
                                    class="sr-only peer"
                                />
                                <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                            </label>
                            <div>
                                <span class="text-sm font-bold text-slate-800 block">Visible en Portal Web</span>
                                <span class="text-[11px] text-slate-400">Pública para visitantes</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="flex items-center justify-end gap-3 pt-2">
                    <Link :href="route('admin.galeria.index')">
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
                        <span>Guardar Fotografía</span>
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
