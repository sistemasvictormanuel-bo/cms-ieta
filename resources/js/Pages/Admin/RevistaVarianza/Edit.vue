<script setup>
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import EditorJs from '@/Components/EditorJs.vue';

const props = defineProps({
    revista: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    rv_titulo: props.revista?.rv_titulo || 'Revista Varianza',
    rv_subtitulo: props.revista?.rv_subtitulo || '',
    rv_mision: props.revista?.rv_mision || '',
    rv_vision: props.revista?.rv_vision || '',
    rv_contenido: props.revista?.rv_contenido || { blocks: [] },
    rv_alcance: props.revista?.rv_alcance || { blocks: [] },
    rv_politicas: props.revista?.rv_politicas || { blocks: [] },
    rv_correo_contacto: props.revista?.rv_correo_contacto || '',
    rv_portada: null,
});

const fileInput = ref(null);
const portadaPreview = ref(props.revista?.portada_url || null);

const handlePortadaChange = (e) => {
    const file = e.target.files?.[0];
    if (!file) return;
    form.rv_portada = file;

    const reader = new FileReader();
    reader.onload = (event) => {
        portadaPreview.value = event.target.result;
    };
    reader.readAsDataURL(file);
};

const triggerFileInput = () => {
    fileInput.value?.click();
};

const removePortada = () => {
    form.rv_portada = null;
    portadaPreview.value = props.revista?.portada_url || null;
    if (fileInput.value) fileInput.value.value = '';
};

const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'PUT',
    })).post(route('admin.revista-varianza.update'), {
        preserveScroll: true,
        forceFormData: true,
    });
};
</script>

<template>
    <AppLayout title="Perfil CMS - Revista Varianza">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 w-full min-w-0">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-1.5 text-[11px] font-semibold text-slate-400 mb-0.5 leading-none">
                        <Link :href="route('admin.ediciones-varianza.index')" class="hover:text-[#35449F]">Revista Varianza</Link>
                        <span>/</span>
                        <span class="text-slate-700 font-bold">Perfil Institucional</span>
                    </div>
                    <h1 class="font-extrabold text-xl sm:text-2xl text-[#14161C] tracking-tight truncate leading-tight">
                        Configuración de la Revista Varianza
                    </h1>
                </div>

                <div class="flex flex-wrap items-center gap-2 sm:gap-2.5 shrink-0">
                    <span v-if="form.isDirty" class="text-xs font-semibold text-amber-700 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-200 whitespace-nowrap">
                        ● Sin guardar
                    </span>
                    <Link :href="route('admin.ediciones-varianza.index')">
                        <SecondaryButton class="cursor-pointer">
                            Ver Ediciones
                        </SecondaryButton>
                    </Link>
                    <PrimaryButton @click="submit" :disabled="form.processing" class="cursor-pointer gap-1.5">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>{{ form.processing ? 'Guardando...' : 'Guardar Información' }}</span>
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto pb-16">
            <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                <!-- Columna Izquierda: Contenido Principal (8 columnas) -->
                <div class="lg:col-span-8 space-y-6">
                    <!-- Tarjeta 1: Títulos y Datos Principales -->
                    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-5">
                        <div class="border-b border-slate-100 pb-3">
                            <h2 class="text-base font-extrabold text-[#14161C]">Identificación de la Revista</h2>
                            <p class="text-xs text-slate-500">Nombre institucional y subtítulo oficial de la publicación científica.</p>
                        </div>

                        <div>
                            <InputLabel value="Título de la Revista *" class="text-xs font-bold text-slate-700 uppercase" />
                            <TextInput
                                v-model="form.rv_titulo"
                                type="text"
                                class="w-full mt-1 font-semibold"
                                placeholder="Ej: Revista Varianza"
                                required
                            />
                            <InputError :message="form.errors.rv_titulo" class="mt-1" />
                        </div>

                        <div>
                            <InputLabel value="Subtítulo / Lema de la Revista" class="text-xs font-bold text-slate-700 uppercase" />
                            <TextInput
                                v-model="form.rv_subtitulo"
                                type="text"
                                class="w-full mt-1"
                                placeholder="Ej: Publicación Oficial de Investigación Estadística e Informática - IETA / UMSA"
                            />
                            <InputError :message="form.errors.rv_subtitulo" class="mt-1" />
                        </div>
                    </div>

                    <!-- Tarjeta 2: Misión y Visión -->
                    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-5">
                        <div class="border-b border-slate-100 pb-3">
                            <h2 class="text-base font-extrabold text-[#14161C]">Misión y Visión Editorial</h2>
                            <p class="text-xs text-slate-500">Fundamento y proyección de la revista en el ecosistema científico.</p>
                        </div>

                        <div>
                            <InputLabel value="Misión de la Revista" class="text-xs font-bold text-slate-700 uppercase" />
                            <textarea
                                v-model="form.rv_mision"
                                rows="3"
                                class="w-full mt-1 border-slate-200 rounded-xl text-sm focus:border-[#35449F] focus:ring-[#35449F]/20"
                                placeholder="Describa el propósito y la misión científica de la Revista Varianza..."
                            ></textarea>
                            <InputError :message="form.errors.rv_mision" class="mt-1" />
                        </div>

                        <div>
                            <InputLabel value="Visión de la Revista" class="text-xs font-bold text-slate-700 uppercase" />
                            <textarea
                                v-model="form.rv_vision"
                                rows="3"
                                class="w-full mt-1 border-slate-200 rounded-xl text-sm focus:border-[#35449F] focus:ring-[#35449F]/20"
                                placeholder="Describa las metas de indexación y reconocimiento nacional/internacional..."
                            ></textarea>
                            <InputError :message="form.errors.rv_vision" class="mt-1" />
                        </div>
                    </div>

                    <!-- Tarjeta 3: Alcance y Cobertura Temática (Editor.js) -->
                    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                        <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                            <div>
                                <h2 class="text-base font-extrabold text-[#14161C]">Alcance y Cobertura Temática (Scope)</h2>
                                <p class="text-xs text-slate-500">Líneas disciplinares, metodologías aceptadas y público objetivo (Editor.js).</p>
                            </div>
                            <span class="px-2 py-1 text-[10px] font-bold uppercase rounded bg-slate-100 text-slate-600">
                                Editor.js
                            </span>
                        </div>

                        <div class="pt-2">
                            <EditorJs
                                v-model="form.rv_alcance"
                                placeholder="Escribe el alcance temático, ramas de la estadística aplicada, etc..."
                            />
                            <InputError :message="form.errors.rv_alcance" class="mt-1" />
                        </div>
                    </div>

                    <!-- Tarjeta 4: Políticas de Sección y Ética (Editor.js) -->
                    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                        <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                            <div>
                                <h2 class="text-base font-extrabold text-[#14161C]">Políticas de Sección & Ética Editorial</h2>
                                <p class="text-xs text-slate-500">Proceso de arbitraje doble ciego, código de ética COPE y directrices para autores.</p>
                            </div>
                            <span class="px-2 py-1 text-[10px] font-bold uppercase rounded bg-slate-100 text-slate-600">
                                Editor.js
                            </span>
                        </div>

                        <div class="pt-2">
                            <EditorJs
                                v-model="form.rv_politicas"
                                placeholder="Políticas de acceso abierto, arbitraje por pares ciegos, derechos de autor..."
                            />
                            <InputError :message="form.errors.rv_politicas" class="mt-1" />
                        </div>
                    </div>

                    <!-- Tarjeta 5: Contenido General Institucional (Editor.js) -->
                    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                        <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                            <div>
                                <h2 class="text-base font-extrabold text-[#14161C]">Contenido Institucional General</h2>
                                <p class="text-xs text-slate-500">Historia de la revista, patrocinadores y presentación institucional completa.</p>
                            </div>
                            <span class="px-2 py-1 text-[10px] font-bold uppercase rounded bg-slate-100 text-slate-600">
                                Editor.js
                            </span>
                        </div>

                        <div class="pt-2">
                            <EditorJs
                                v-model="form.rv_contenido"
                                placeholder="Detalles adicionales sobre la Revista Varianza..."
                            />
                            <InputError :message="form.errors.rv_contenido" class="mt-1" />
                        </div>
                    </div>
                </div>

                <!-- Columna Derecha: Sidebar (4 columnas) -->
                <div class="lg:col-span-4 space-y-6 lg:sticky lg:top-6">
                    <!-- Tarjeta Portada / Emblema -->
                    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                        <div class="border-b border-slate-100 pb-3">
                            <h3 class="text-sm font-extrabold text-[#14161C]">Portada Oficial de la Revista</h3>
                            <p class="text-xs text-slate-500">Imagen institucional o isotipo de la Revista Varianza.</p>
                        </div>

                        <div class="space-y-3">
                            <input
                                ref="fileInput"
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                @change="handlePortadaChange"
                                class="hidden"
                            />

                            <div
                                @click="triggerFileInput"
                                class="group relative aspect-3/4 rounded-xl border-2 border-dashed border-slate-200 hover:border-[#35449F] bg-slate-50/50 flex flex-col items-center justify-center p-4 cursor-pointer transition overflow-hidden"
                            >
                                <img
                                    v-if="portadaPreview"
                                    :src="portadaPreview"
                                    alt="Vista previa portada"
                                    class="absolute inset-0 w-full h-full object-cover transition group-hover:opacity-90"
                                />

                                <div
                                    v-if="portadaPreview"
                                    class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition"
                                >
                                    <span class="px-3 py-1.5 text-xs font-bold text-white bg-slate-900/80 rounded-lg">
                                        Cambiar Portada
                                    </span>
                                </div>

                                <div v-else class="text-center space-y-2">
                                    <div class="size-12 rounded-xl bg-blue-50 text-[#35449F] flex items-center justify-center mx-auto">
                                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-slate-700">Subir portada oficial</p>
                                        <p class="text-[11px] text-slate-400">PNG, JPG o WEBP (máx. 5MB)</p>
                                    </div>
                                </div>
                            </div>

                            <div v-if="form.rv_portada" class="flex items-center justify-between pt-1">
                                <span class="text-xs text-emerald-700 font-semibold truncate max-w-[200px]">
                                    ✓ {{ form.rv_portada.name }}
                                </span>
                                <button
                                    type="button"
                                    @click="removePortada"
                                    class="text-xs text-red-600 hover:underline cursor-pointer"
                                >
                                    Deshacer
                                </button>
                            </div>
                            <InputError :message="form.errors.rv_portada" />
                        </div>
                    </div>


                    <!-- Tarjeta Contacto Editorial -->
                    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                        <div class="border-b border-slate-100 pb-3">
                            <h3 class="text-sm font-extrabold text-[#14161C]">Contacto del Comité Editorial</h3>
                            <p class="text-xs text-slate-500">Canal para recepción de correspondencia y autores.</p>
                        </div>

                        <div>
                            <InputLabel value="Correo Electrónico de Contacto" class="text-xs font-bold text-slate-700 uppercase" />
                            <TextInput
                                v-model="form.rv_correo_contacto"
                                type="email"
                                class="w-full mt-1 text-sm"
                                placeholder="revistavarianza@umsa.bo"
                            />
                            <InputError :message="form.errors.rv_correo_contacto" class="mt-1" />
                        </div>
                    </div>

                    <!-- Tarjeta Enlaces y Navegación Rápida -->
                    <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200/80 space-y-3 text-xs text-slate-600">
                        <p class="font-bold text-slate-800">Módulos Relacionados:</p>
                        <div class="space-y-2">
                            <Link
                                :href="route('admin.ediciones-varianza.index')"
                                class="flex items-center justify-between p-2.5 rounded-xl bg-white border border-slate-200 hover:border-[#35449F] font-semibold text-slate-700 hover:text-[#35449F] transition"
                            >
                                <span>Ver Todas las Ediciones</span>
                                <span>→</span>
                            </Link>
                            <Link
                                :href="route('admin.tipo-articulos.index')"
                                class="flex items-center justify-between p-2.5 rounded-xl bg-white border border-slate-200 hover:border-[#35449F] font-semibold text-slate-700 hover:text-[#35449F] transition"
                            >
                                <span>Configurar Tipos de Artículos</span>
                                <span>→</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
