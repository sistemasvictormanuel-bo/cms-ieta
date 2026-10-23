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
    imagen: {
        type: Object,
        required: true,
    },
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

const formattedFecha = props.imagen.gal_fecha
    ? (props.imagen.gal_fecha.includes('T') ? props.imagen.gal_fecha.split('T')[0] : props.imagen.gal_fecha)
    : '';

const isPredefinedCategory = props.categoriasSugeridas.includes(props.imagen.gal_categoria);
const isCustomCategory = ref(!isPredefinedCategory);
const customCategoryInput = ref(!isPredefinedCategory ? props.imagen.gal_categoria : '');

const form = useForm({
    _method: 'put',
    gal_titulo: props.imagen.gal_titulo || '',
    gal_categoria: props.imagen.gal_categoria || 'Institucional',
    gal_fecha: formattedFecha,
    gal_lugar: props.imagen.gal_lugar || '',
    gal_descripcion: props.imagen.gal_descripcion || '',
    gal_orden: props.imagen.gal_orden ?? 0,
    gal_destacado: Boolean(props.imagen.gal_destacado),
    gal_activo: Boolean(props.imagen.gal_activo),
    imagen: null,
});

const newImagePreview = ref(null);
const fileInput = ref(null);

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
        if (newImagePreview.value) {
            URL.revokeObjectURL(newImagePreview.value);
        }
        form.imagen = file;
        newImagePreview.value = URL.createObjectURL(file);
    }
};

const cancelNewImage = () => {
    if (newImagePreview.value) {
        URL.revokeObjectURL(newImagePreview.value);
        newImagePreview.value = null;
    }
    form.imagen = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const submit = () => {
    form.post(route('admin.galeria.update', props.imagen.id), {
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
    <AppLayout :title="`Editar: ${imagen.gal_titulo}`">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 w-full min-w-0">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-1.5 text-[11px] font-semibold text-slate-400 mb-0.5 leading-none">
                        <Link :href="route('dashboard')" class="hover:text-[#35449F]">Inicio</Link>
                        <span>/</span>
                        <Link :href="route('admin.galeria.index')" class="hover:text-[#35449F]">Galería</Link>
                        <span>/</span>
                        <span class="text-slate-700 font-bold truncate">Editar Fotografía</span>
                    </div>
                    <h1 class="font-extrabold text-xl sm:text-2xl text-[#14161C] tracking-tight truncate leading-tight">
                        Modificar Fotografía
                    </h1>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <Link :href="route('admin.galeria.show', imagen.id)">
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
                <!-- Tarjeta 1: Imagen Actual y Reemplazo -->
                <div class="bg-white rounded-2xl p-6 shadow-xs border border-gray-100 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-base font-extrabold text-slate-800">Archivo Fotográfico</h2>
                            <p class="text-xs text-slate-400 mt-0.5">
                                Puede conservar la imagen actual o subir un nuevo archivo para sustituirlo.
                            </p>
                        </div>
                        <span class="text-[11px] font-bold px-2.5 py-1 rounded-md bg-slate-100 text-slate-600">
                            {{ imagen.gal_categoria || 'Fotografía' }}
                        </span>
                    </div>

                    <!-- Vista de la imagen (nueva previsualización o existente) -->
                    <div class="relative rounded-2xl overflow-hidden border border-slate-200 bg-slate-900 aspect-video sm:aspect-21/9 max-h-[380px] flex items-center justify-center group">
                        <img
                            v-if="newImagePreview"
                            :src="newImagePreview"
                            alt="Nueva imagen previsualizada"
                            class="w-full h-full object-contain"
                        />
                        <img
                            v-else-if="imagen.imagen_url"
                            :src="imagen.imagen_url"
                            :alt="imagen.gal_titulo"
                            class="w-full h-full object-contain"
                        />
                        <div v-else class="text-slate-400 text-center p-8">
                            <svg class="size-12 mx-auto stroke-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="text-xs mt-1 block">Sin archivo previo</span>
                        </div>

                        <!-- Acciones sobre la imagen -->
                        <div class="absolute inset-0 bg-slate-900/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
                            <button
                                type="button"
                                @click="fileInput?.click()"
                                class="px-3.5 py-2 rounded-xl bg-white text-slate-800 text-xs font-bold shadow-md hover:bg-slate-100 transition cursor-pointer flex items-center gap-1.5"
                            >
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                                {{ newImagePreview ? 'Cambiar archivo' : 'Sustituir fotografía' }}
                            </button>
                            <button
                                v-if="newImagePreview"
                                type="button"
                                @click="cancelNewImage"
                                class="px-3.5 py-2 rounded-xl bg-slate-800 text-white text-xs font-bold shadow-md hover:bg-slate-700 transition cursor-pointer flex items-center gap-1.5"
                            >
                                Revertir a la original
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
                        Nueva imagen seleccionada para subir al guardar los cambios.
                    </p>

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
                                placeholder="Escribe el nombre de la categoría personalizada..."
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
                            placeholder="Breve reseña o información del evento..."
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
                                <span class="text-[11px] text-slate-400">Mostrar en carruseles y secciones destacadas</span>
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
                        <span>Guardar Cambios</span>
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
