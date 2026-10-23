<script setup>
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const form = useForm({
    com_titulo: '',
    com_descripcion: '',
    com_enlace_url: '',
    com_texto_boton: 'Más información',
    com_tipo: 'general',
    com_fecha_inicio: new Date().toISOString().slice(0, 10),
    com_fecha_fin: '',
    com_activo: true,
    com_flyer: null,
});

const flyerPreview = ref(null);
const fileName = ref('');

const onTipoChange = () => {
    switch (form.com_tipo) {
        case 'curso':
            form.com_texto_boton = 'Inscribirse al Curso';
            break;
        case 'articulo':
            form.com_texto_boton = 'Leer Publicación';
            break;
        case 'convocatoria':
            form.com_texto_boton = 'Ver Convocatoria';
            break;
        case 'noticia':
            form.com_texto_boton = 'Leer Noticia';
            break;
        default:
            form.com_texto_boton = 'Más información';
            break;
    }
};

const handleFlyerChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.com_flyer = file;
        fileName.value = file.name;
        flyerPreview.value = URL.createObjectURL(file);
    } else {
        form.com_flyer = null;
        fileName.value = '';
        flyerPreview.value = null;
    }
};

const removeFlyer = () => {
    form.com_flyer = null;
    fileName.value = '';
    flyerPreview.value = null;
};

const submit = () => {
    form.post(route('admin.comunicados.store'), {
        forceFormData: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout title="Nuevo Comunicado / Novedad">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 w-full">
                <div>
                    <h1 class="font-extrabold text-2xl text-[#14161C] tracking-tight">
                        Nuevo Comunicado / Novedad Emergente
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500 font-medium">
                        Configura un aviso emergente en modal o banner para promocionar cursos, artículos o avisos institucionales.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Link :href="route('admin.comunicados.index')">
                        <SecondaryButton class="cursor-pointer">
                            ← Volver al listado
                        </SecondaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="max-w-4xl mx-auto pb-12">
            <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xs border border-gray-100">
                <form @submit.prevent="submit" class="space-y-8">
                    <!-- Sección 1: Información General -->
                    <div class="space-y-6">
                        <h2 class="text-base font-extrabold text-[#14161C] pb-2 border-b border-gray-100 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-[#35449F]/10 text-[#35449F] flex items-center justify-center text-xs font-bold">1</span>
                            Contenido del Comunicado
                        </h2>

                        <!-- Título y Tipo -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-2">
                                <InputLabel for="com_titulo" value="Título de la Novedad *" />
                                <TextInput
                                    id="com_titulo"
                                    v-model="form.com_titulo"
                                    type="text"
                                    class="mt-1 block w-full"
                                    placeholder="Ej: Inscripciones Abiertas: Curso Taller de Series de Tiempo 2026"
                                    required
                                />
                                <InputError :message="form.errors.com_titulo" class="mt-1.5" />
                            </div>

                            <div>
                                <InputLabel for="com_tipo" value="Tipo de Novedad *" />
                                <select
                                    id="com_tipo"
                                    v-model="form.com_tipo"
                                    @change="onTipoChange"
                                    class="mt-1 block w-full rounded-xl border-gray-200 py-2.5 px-3 text-sm focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 bg-white"
                                >
                                    <option value="general">Aviso General</option>
                                    <option value="curso">Curso o Taller</option>
                                    <option value="articulo">Artículo Científico</option>
                                    <option value="convocatoria">Convocatoria</option>
                                    <option value="noticia">Noticia Institucional</option>
                                </select>
                                <InputError :message="form.errors.com_tipo" class="mt-1.5" />
                            </div>
                        </div>

                        <!-- Descripción Corta -->
                        <div>
                            <InputLabel for="com_descripcion" value="Descripción / Mensaje Explicativo (Opcional)" />
                            <textarea
                                id="com_descripcion"
                                v-model="form.com_descripcion"
                                rows="3"
                                class="mt-1 block w-full rounded-xl border-gray-200 p-3 text-sm focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 bg-gray-50/50"
                                placeholder="Breve texto que acompañará al afiche en la ventana emergente..."
                            ></textarea>
                            <InputError :message="form.errors.com_descripcion" class="mt-1.5" />
                        </div>

                        <!-- Enlace y Texto de Botón -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="com_enlace_url" value="Enlace o Ruta de Destino (Opcional)" />
                                <TextInput
                                    id="com_enlace_url"
                                    v-model="form.com_enlace_url"
                                    type="text"
                                    class="mt-1 block w-full text-sm font-mono"
                                    placeholder="Ej: /cursos/series-tiempo o https://forms.gle/..."
                                />
                                <p class="text-[11px] text-gray-400 mt-1">Ruta interna o enlace externo hacia donde redirigirá el botón.</p>
                                <InputError :message="form.errors.com_enlace_url" class="mt-1.5" />
                            </div>

                            <div>
                                <InputLabel for="com_texto_boton" value="Texto del Botón de Acción" />
                                <TextInput
                                    id="com_texto_boton"
                                    v-model="form.com_texto_boton"
                                    type="text"
                                    class="mt-1 block w-full text-sm"
                                    placeholder="Ej: Inscribirme, Leer más, Ver convocatoria"
                                />
                                <InputError :message="form.errors.com_texto_boton" class="mt-1.5" />
                            </div>
                        </div>
                    </div>

                    <!-- Sección 2: Flyer / Imagen -->
                    <div class="space-y-4">
                        <h2 class="text-base font-extrabold text-[#14161C] pb-2 border-b border-gray-100 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-[#35449F]/10 text-[#35449F] flex items-center justify-center text-xs font-bold">2</span>
                            Afiche / Flyer Publicitario
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                            <div class="md:col-span-2">
                                <label
                                    class="flex flex-col items-center justify-center border-2 border-dashed border-gray-200 hover:border-[#35449F] rounded-2xl p-6 cursor-pointer bg-gray-50/50 hover:bg-indigo-50/20 transition group"
                                >
                                    <svg class="size-10 text-gray-400 group-hover:text-[#35449F] transition mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span class="text-sm font-bold text-gray-700 group-hover:text-[#35449F]">
                                        Selecciona una imagen o arrástrala aquí
                                    </span>
                                    <span class="text-xs text-gray-400 mt-1">
                                        Formatos recomendados: PNG, JPG, WebP (Máx. 10 MB)
                                    </span>
                                    <input
                                        type="file"
                                        accept="image/*"
                                        @change="handleFlyerChange"
                                        class="hidden"
                                    />
                                </label>
                                <InputError :message="form.errors.com_flyer" class="mt-1.5" />
                            </div>

                            <!-- Preview -->
                            <div class="flex flex-col items-center justify-center p-4 bg-gray-50 rounded-2xl border border-gray-100 min-h-[160px]">
                                <div v-if="flyerPreview" class="relative group">
                                    <img
                                        :src="flyerPreview"
                                        alt="Preview"
                                        class="max-h-48 rounded-xl object-contain shadow-xs"
                                    />
                                    <button
                                        @click="removeFlyer"
                                        type="button"
                                        class="absolute top-2 right-2 p-1 bg-red-600 text-white rounded-full opacity-80 hover:opacity-100 shadow cursor-pointer"
                                        title="Quitar"
                                    >
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                <div v-else class="text-center text-gray-400 text-xs">
                                    <svg class="size-8 mx-auto text-gray-300 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    Previsualización del afiche
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sección 3: Vigencia y Publicación -->
                    <div class="space-y-4">
                        <h2 class="text-base font-extrabold text-[#14161C] pb-2 border-b border-gray-100 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-[#35449F]/10 text-[#35449F] flex items-center justify-center text-xs font-bold">3</span>
                            Período de Vigencia & Estado
                        </h2>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-center">
                            <div>
                                <InputLabel for="com_fecha_inicio" value="Fecha de Inicio *" />
                                <TextInput
                                    id="com_fecha_inicio"
                                    v-model="form.com_fecha_inicio"
                                    type="date"
                                    class="mt-1 block w-full text-sm"
                                    required
                                />
                                <InputError :message="form.errors.com_fecha_inicio" class="mt-1.5" />
                            </div>

                            <div>
                                <InputLabel for="com_fecha_fin" value="Fecha de Fin (Opcional)" />
                                <TextInput
                                    id="com_fecha_fin"
                                    v-model="form.com_fecha_fin"
                                    type="date"
                                    class="mt-1 block w-full text-sm"
                                />
                                <p class="text-[11px] text-gray-400 mt-1">Si está vacío, permanecerá activo indefinidamente.</p>
                                <InputError :message="form.errors.com_fecha_fin" class="mt-1.5" />
                            </div>

                            <div class="pt-4 sm:pt-0 flex items-center">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input
                                        type="checkbox"
                                        v-model="form.com_activo"
                                        class="sr-only peer"
                                    />
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-hidden rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#35449F]"></div>
                                    <span class="ml-3 text-sm font-semibold text-gray-700">
                                        Activar Novedad Inmediatamente
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100">
                        <Link :href="route('admin.comunicados.index')">
                            <SecondaryButton type="button" class="cursor-pointer">Cancelar</SecondaryButton>
                        </Link>
                        <PrimaryButton type="submit" :disabled="form.processing" class="cursor-pointer">
                            Guardar Comunicado
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
