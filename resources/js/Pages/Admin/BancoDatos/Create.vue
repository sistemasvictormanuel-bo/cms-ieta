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
    areas: {
        type: Array,
        default: () => [],
    },
});

const locacionesOpciones = [
    'Bolivia',
    'La Paz',
    'Cochabamba',
    'Santa Cruz',
    'Oruro',
    'Potosí',
    'Chuquisaca',
    'Tarija',
    'Beni',
    'Pando',
    'No corresponde',
    'Otro',
];

const selectedLocacion = ref('Bolivia');
const otroLocacionTexto = ref('');
const tieneEnlaceExterno = ref(false);

const form = useForm({
    bdd_tipo_dato: 'Censos',
    bdd_anio: new Date().getFullYear(),
    bdd_codigo: '',
    bdd_titulo: '',
    bdd_descripcion: '',
    bdd_locacion: 'Bolivia',
    bdd_url: '',
    bdd_archivo: null,
    bdd_portada: null,
    areas: [],
});

const fileName = ref('');
const portadaPreview = ref(null);

const handleArchivoChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.bdd_archivo = file;
        fileName.value = file.name;
    } else {
        form.bdd_archivo = null;
        fileName.value = '';
    }
};

const handlePortadaChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        if (portadaPreview.value) {
            URL.revokeObjectURL(portadaPreview.value);
        }
        form.bdd_portada = file;
        portadaPreview.value = URL.createObjectURL(file);
    } else {
        form.bdd_portada = null;
        portadaPreview.value = null;
    }
};

const submit = () => {
    if (selectedLocacion.value === 'Otro') {
        form.bdd_locacion = otroLocacionTexto.value.trim() || 'Otro';
    } else {
        form.bdd_locacion = selectedLocacion.value;
    }

    if (!tieneEnlaceExterno.value) {
        form.bdd_url = null;
    }

    form.post(route('admin.banco-datos.store'), {
        forceFormData: true,
        preserveScroll: true,
    });
};

onUnmounted(() => {
    if (portadaPreview.value) {
        URL.revokeObjectURL(portadaPreview.value);
    }
});
</script>

<template>
    <AppLayout title="Nueva Base de Datos">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 w-full">
                <div>
                    <h1 class="font-black text-2xl text-[#14161C] tracking-tight">
                        Nueva Base de Datos
                    </h1>
                    <p class="text-[14.5px] text-gray-600 font-medium">
                        Registra una base de datos estadística, censo o encuesta con sus archivos o enlaces asociados.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Link :href="route('admin.banco-datos.index')">
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
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Tipo de Dato -->
                        <div>
                            <InputLabel for="bdd_tipo_dato" value="Tipo de Base *" class="text-[15.5px] font-bold text-gray-900" />
                            <select
                                id="bdd_tipo_dato"
                                v-model="form.bdd_tipo_dato"
                                class="mt-1.5 block w-full rounded-xl border-gray-300 py-3 px-3.5 text-[15.5px] font-medium focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 bg-white"
                                required
                            >
                                <option value="Censos">Censos</option>
                                <option value="Encuestas">Encuestas</option>
                            </select>
                            <InputError :message="form.errors.bdd_tipo_dato" class="mt-1.5" />
                        </div>

                        <!-- Año -->
                        <div>
                            <InputLabel for="bdd_anio" value="Año del Censo / Encuesta *" class="text-[15.5px] font-bold text-gray-900" />
                            <TextInput
                                id="bdd_anio"
                                v-model="form.bdd_anio"
                                type="number"
                                min="1900"
                                max="2100"
                                class="mt-1.5 block w-full text-[15.5px]"
                                required
                            />
                            <InputError :message="form.errors.bdd_anio" class="mt-1.5" />
                        </div>

                        <!-- Código -->
                        <div>
                            <InputLabel for="bdd_codigo" value="Código de Identificación *" class="text-[15.5px] font-bold text-gray-900" />
                            <TextInput
                                id="bdd_codigo"
                                v-model="form.bdd_codigo"
                                type="text"
                                class="mt-1.5 block w-full text-[15.5px]"
                                placeholder="Ej: CNPV-2024, EH-2023"
                                required
                            />
                            <InputError :message="form.errors.bdd_codigo" class="mt-1.5" />
                        </div>
                    </div>

                    <!-- Título -->
                    <div>
                        <InputLabel for="bdd_titulo" value="Título de la Base de Datos *" class="text-[15.5px] font-bold text-gray-900" />
                        <TextInput
                            id="bdd_titulo"
                            v-model="form.bdd_titulo"
                            type="text"
                            class="mt-1.5 block w-full text-[15.5px]"
                            placeholder="Ej: Censo Nacional de Población y Vivienda 2024 - Resultados Preliminares"
                            required
                        />
                        <InputError :message="form.errors.bdd_titulo" class="mt-1.5" />
                    </div>

                    <!-- Locación Dinámica (Bolivia / Departamentos / No corresponde / Otro) -->
                    <div class="p-4 rounded-xl bg-gray-50/70 border border-gray-100 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="bdd_locacion_select" value="Locación Geográfica *" class="text-[15.5px] font-bold text-gray-900" />
                                <select
                                    id="bdd_locacion_select"
                                    v-model="selectedLocacion"
                                    class="mt-1.5 block w-full rounded-xl border-gray-300 py-3 px-3.5 text-[15.5px] font-medium focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 bg-white"
                                >
                                    <option v-for="opc in locacionesOpciones" :key="opc" :value="opc">
                                        {{ opc === 'Bolivia' ? '🇧🇴 Bolivia (Nivel Nacional)' : opc }}
                                    </option>
                                </select>
                            </div>

                            <!-- Input visible solo si selecciona 'Otro' -->
                            <div v-if="selectedLocacion === 'Otro'">
                                <InputLabel for="otroLocacionTexto" value="Escribe la Locación Personalizada *" class="text-[15.5px] font-bold text-gray-900" />
                                <TextInput
                                    id="otroLocacionTexto"
                                    v-model="otroLocacionTexto"
                                    type="text"
                                    class="mt-1.5 block w-full text-[15.5px]"
                                    placeholder="Ej: Región Metropolitana de Kanata, Chaco Boliviano..."
                                    required
                                />
                            </div>
                        </div>
                        <InputError :message="form.errors.bdd_locacion" class="mt-1" />
                    </div>

                    <!-- Descripción -->
                    <div>
                        <InputLabel for="bdd_descripcion" value="Descripción / Metodología / Ficha Técnica" class="text-[15.5px] font-bold text-gray-900" />
                        <textarea
                            id="bdd_descripcion"
                            v-model="form.bdd_descripcion"
                            rows="4"
                            class="mt-1.5 block w-full rounded-xl border-gray-300 p-3.5 text-[15.5px] font-medium focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 bg-white"
                            placeholder="Detalles sobre el levantamiento, tamaño muestral o características de los datos..."
                        ></textarea>
                        <InputError :message="form.errors.bdd_descripcion" class="mt-1.5" />
                    </div>

                    <!-- Áreas Temáticas (Asignación) -->
                    <div>
                        <InputLabel value="Áreas Temáticas Asociadas" class="mb-2 text-[15.5px] font-bold text-gray-900" />
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2.5 p-4 rounded-xl bg-gray-50/50 border border-gray-100 max-h-48 overflow-y-auto">
                            <label
                                v-for="area in areas"
                                :key="area.id"
                                class="flex items-center gap-2 text-xs font-semibold text-gray-700 cursor-pointer p-1.5 rounded-lg hover:bg-white transition"
                            >
                                <input
                                    type="checkbox"
                                    :value="area.id"
                                    v-model="form.areas"
                                    class="rounded border-gray-300 text-[#35449F] focus:ring-[#35449F]/20"
                                />
                                <span class="truncate">{{ area.at_nombre }}</span>
                            </label>
                        </div>
                        <InputError :message="form.errors.areas" class="mt-1.5" />
                    </div>

                    <!-- Archivos y Enlaces de Datos -->
                    <div class="space-y-4 pt-2">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Archivo de Datos (ZIP, CSV, SAV, XLSX) -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <InputLabel for="bdd_archivo" value="Archivo de Datos (máx 50MB)" class="text-[15.5px] font-bold text-gray-900" />
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
                                <input
                                    id="bdd_archivo"
                                    type="file"
                                    @change="handleArchivoChange"
                                    class="mt-1 block w-full text-xs text-gray-500 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-[13px] file:font-bold file:bg-[#35449F]/10 file:text-[#35449F] hover:file:bg-[#35449F]/20 cursor-pointer"
                                />
                                <p v-if="fileName" class="text-[13px] text-[#35449F] font-bold mt-1.5">
                                    Archivo seleccionado: {{ fileName }}
                                </p>
                                <p class="text-[12.5px] text-gray-500 font-medium">
                                    Microdatos en ZIP, RAR, CSV, SAV, XLSX (Gestionado con Spatie Media Library).
                                </p>
                                <InputError :message="form.errors.bdd_archivo" class="mt-1.5" />
                            </div>

                            <!-- Imagen de Portada -->
                            <div>
                                <InputLabel for="bdd_portada" value="Imagen de Portada (Opcional - máx 5MB)" class="text-[15.5px] font-bold text-gray-900" />
                                <input
                                    id="bdd_portada"
                                    type="file"
                                    accept="image/jpeg,image/png,image/webp"
                                    @change="handlePortadaChange"
                                    class="mt-1 block w-full text-xs text-gray-500 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-[13px] file:font-bold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 cursor-pointer"
                                />
                                <div v-if="portadaPreview" class="mt-2">
                                    <img :src="portadaPreview" alt="Portada Preview" class="h-20 w-36 object-cover rounded-lg border border-gray-200" />
                                </div>
                                <InputError :message="form.errors.bdd_portada" class="mt-1.5" />
                            </div>
                        </div>

                        <!-- Campo Dinámico: Enlace Externo (Drive, Dropbox, OneDrive, etc.) -->
                        <div v-if="tieneEnlaceExterno" class="p-4 rounded-xl bg-indigo-50/50 border border-indigo-100 space-y-2 transition-all">
                            <div class="flex items-center justify-between">
                                <InputLabel for="bdd_url" value="Enlace Externo a la Nube (Google Drive, OneDrive, etc.)" class="text-[15px] font-bold text-gray-900" />
                                <span class="text-[12px] font-bold text-indigo-700 bg-indigo-100 px-2.5 py-0.5 rounded-full">
                                    Drive • Dropbox • URL Nube
                                </span>
                            </div>
                            <TextInput
                                id="bdd_url"
                                v-model="form.bdd_url"
                                type="url"
                                class="block w-full text-[15.5px]"
                                placeholder="https://drive.google.com/file/d/... o enlace externo compartido"
                            />
                            <p class="text-[12.5px] text-gray-500 font-medium">
                                Opcional. Puedes subir un archivo local, proporcionar un enlace a Google Drive / OneDrive, ambos o ninguno según lo decidas.
                            </p>
                            <InputError :message="form.errors.bdd_url" class="mt-1" />
                        </div>
                    </div>

                    <!-- Acciones -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <Link :href="route('admin.banco-datos.index')">
                            <SecondaryButton class="cursor-pointer text-[15px] font-bold px-5 py-2.5">Cancelar</SecondaryButton>
                        </Link>
                        <PrimaryButton :disabled="form.processing" class="cursor-pointer text-[15.5px] font-bold px-6 py-2.5">
                            {{ form.processing ? 'Guardando...' : 'Crear Base de Datos' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
