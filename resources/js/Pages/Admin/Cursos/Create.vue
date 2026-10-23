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
    tipos: {
        type: Array,
        default: () => [],
    },
    modalidades: {
        type: Array,
        default: () => [],
    },
    docentes: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    ct_titulo: '',
    tipo_curso_id: '',
    modalidad_curso_id: '',
    docente_id: '',
    ct_fecha_inicio: '',
    ct_fecha_fin: '',
    ct_hora_inicio: '',
    ct_hora_fin: '',
    ct_duracion: '',
    ct_costo: 0,
    ct_email_info: '',
    ct_telefono_ref: '',
    ct_descripcion: '',
    ct_contenido: { blocks: [] },
    ct_activo: true,
    portada_curso: null,
});

const imagePreview = ref(null);

const handleImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        if (imagePreview.value) {
            URL.revokeObjectURL(imagePreview.value);
        }
        form.portada_curso = file;
        imagePreview.value = URL.createObjectURL(file);
    }
};

const getDocenteLabel = (doc) => {
    if (doc.nombre_completo) return doc.nombre_completo;
    return `${doc.p_nombre} ${doc.p_apellido_paterno || ''} ${doc.p_apellido_materno || ''}`.trim();
};

const submit = () => {
    form.post(route('admin.cursos.store'), {
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
    <AppLayout title="Crear Nuevo Curso">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 w-full">
                <div>
                    <h1 class="font-extrabold text-2xl text-[#14161C] tracking-tight">
                        Nuevo Curso
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500 font-medium">
                        Publica un nuevo diplomado, taller o curso de capacitación del IETA.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Link :href="route('admin.cursos.index')">
                        <SecondaryButton class="cursor-pointer">
                            ← Volver al catálogo
                        </SecondaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="max-w-5xl mx-auto pb-16">
            <form @submit.prevent="submit" class="space-y-8">
                <!-- SECCIÓN 1: Identificación y Clasificación -->
                <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xs border border-gray-100 space-y-6">
                    <div class="border-b border-gray-100 pb-4">
                        <h2 class="text-lg font-extrabold text-[#14161C]">Información Principal</h2>
                        <p class="text-xs text-gray-500">Datos descriptivos y categorización académica.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Título del Curso (Full width) -->
                        <div class="md:col-span-2">
                            <InputLabel for="ct_titulo" value="Título del Curso *" class="font-bold text-[#14161C]" />
                            <TextInput
                                id="ct_titulo"
                                v-model="form.ct_titulo"
                                type="text"
                                class="mt-1 block w-full"
                                placeholder="Ej: Diplomado en Ciencia de Datos y Machine Learning con Python & R"
                                required
                            />
                            <InputError :message="form.errors.ct_titulo" class="mt-2" />
                        </div>

                        <!-- Tipo de Curso (Select Dinámico) -->
                        <div>
                            <InputLabel for="tipo_curso_id" value="Tipo de Curso *" class="font-bold text-[#14161C]" />
                            <select
                                id="tipo_curso_id"
                                v-model="form.tipo_curso_id"
                                required
                                class="mt-1 block w-full rounded-xl border-gray-300 shadow-xs focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 text-sm py-2.5"
                            >
                                <option value="" disabled>Seleccione un tipo de curso</option>
                                <option
                                    v-for="tipo in tipos"
                                    :key="tipo.id"
                                    :value="tipo.id"
                                >
                                    {{ tipo.tc_nombre }}
                                </option>
                            </select>
                            <InputError :message="form.errors.tipo_curso_id" class="mt-2" />
                        </div>

                        <!-- Modalidad (Select Dinámico) -->
                        <div>
                            <InputLabel for="modalidad_curso_id" value="Modalidad *" class="font-bold text-[#14161C]" />
                            <select
                                id="modalidad_curso_id"
                                v-model="form.modalidad_curso_id"
                                required
                                class="mt-1 block w-full rounded-xl border-gray-300 shadow-xs focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 text-sm py-2.5"
                            >
                                <option value="" disabled>Seleccione una modalidad</option>
                                <option
                                    v-for="mod in modalidades"
                                    :key="mod.id"
                                    :value="mod.id"
                                >
                                    {{ mod.mc_nombre }}
                                </option>
                            </select>
                            <InputError :message="form.errors.modalidad_curso_id" class="mt-2" />
                        </div>

                        <!-- Docente Asignado (Select Dinámico) -->
                        <div>
                            <InputLabel for="docente_id" value="Docente / Instructor (Opcional)" class="font-bold text-[#14161C]" />
                            <select
                                id="docente_id"
                                v-model="form.docente_id"
                                class="mt-1 block w-full rounded-xl border-gray-300 shadow-xs focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 text-sm py-2.5"
                            >
                                <option value="">Sin docente asignado por ahora</option>
                                <option
                                    v-for="doc in docentes"
                                    :key="doc.id"
                                    :value="doc.id"
                                >
                                    {{ getDocenteLabel(doc) }}
                                </option>
                            </select>
                            <InputError :message="form.errors.docente_id" class="mt-2" />
                        </div>

                        <!-- Estado Activo / Visible -->
                        <div class="flex items-center pt-6">
                            <label class="flex items-center gap-3 cursor-pointer select-none">
                                <input
                                    type="checkbox"
                                    v-model="form.ct_activo"
                                    class="rounded-md border-gray-300 text-[#35449F] focus:ring-[#35449F] size-5"
                                />
                                <span class="text-sm font-bold text-[#14161C]">Publicar inmediatamente (Activo)</span>
                            </label>
                            <InputError :message="form.errors.ct_activo" class="mt-2" />
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 2: Cronograma, Horarios e Inversión -->
                <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xs border border-gray-100 space-y-6">
                    <div class="border-b border-gray-100 pb-4">
                        <h2 class="text-lg font-extrabold text-[#14161C]">Fechas, Horarios y Costo</h2>
                        <p class="text-xs text-gray-500">Cronograma de impartición y tarifas.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                        <!-- Fecha Inicio -->
                        <div>
                            <InputLabel for="ct_fecha_inicio" value="Fecha de Inicio *" class="font-bold text-[#14161C]" />
                            <TextInput
                                id="ct_fecha_inicio"
                                v-model="form.ct_fecha_inicio"
                                type="date"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.ct_fecha_inicio" class="mt-2" />
                        </div>

                        <!-- Fecha Fin -->
                        <div>
                            <InputLabel for="ct_fecha_fin" value="Fecha de Finalización *" class="font-bold text-[#14161C]" />
                            <TextInput
                                id="ct_fecha_fin"
                                v-model="form.ct_fecha_fin"
                                type="date"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.ct_fecha_fin" class="mt-2" />
                        </div>

                        <!-- Duración -->
                        <div>
                            <InputLabel for="ct_duracion" value="Carga Horaria / Duración" class="font-bold text-[#14161C]" />
                            <TextInput
                                id="ct_duracion"
                                v-model="form.ct_duracion"
                                type="text"
                                class="mt-1 block w-full"
                                placeholder="Ej: 120 Horas Académicas"
                            />
                            <InputError :message="form.errors.ct_duracion" class="mt-2" />
                        </div>

                        <!-- Hora Inicio -->
                        <div>
                            <InputLabel for="ct_hora_inicio" value="Hora Inicio (Opcional)" class="font-bold text-[#14161C]" />
                            <TextInput
                                id="ct_hora_inicio"
                                v-model="form.ct_hora_inicio"
                                type="time"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="form.errors.ct_hora_inicio" class="mt-2" />
                        </div>

                        <!-- Hora Fin -->
                        <div>
                            <InputLabel for="ct_hora_fin" value="Hora Fin (Opcional)" class="font-bold text-[#14161C]" />
                            <TextInput
                                id="ct_hora_fin"
                                v-model="form.ct_hora_fin"
                                type="time"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="form.errors.ct_hora_fin" class="mt-2" />
                        </div>

                        <!-- Costo (Bs) -->
                        <div>
                            <InputLabel for="ct_costo" value="Costo de Inscripción (Bs)" class="font-bold text-[#14161C]" />
                            <TextInput
                                id="ct_costo"
                                v-model="form.ct_costo"
                                type="number"
                                step="0.01"
                                min="0"
                                class="mt-1 block w-full"
                                placeholder="0 para gratuito"
                            />
                            <p class="text-[11px] text-gray-400 mt-1">Ingresa 0 para indicar que es de acceso libre o gratuito.</p>
                            <InputError :message="form.errors.ct_costo" class="mt-2" />
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 3: Canales de Contacto y Referencias -->
                <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xs border border-gray-100 space-y-6">
                    <div class="border-b border-gray-100 pb-4">
                        <h2 class="text-lg font-extrabold text-[#14161C]">Información de Contacto</h2>
                        <p class="text-xs text-gray-500">Canales para que los postulantes soliciten información adicional.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Email de Información -->
                        <div>
                            <InputLabel for="ct_email_info" value="Correo Electrónico de Consultas" class="font-bold text-[#14161C]" />
                            <TextInput
                                id="ct_email_info"
                                v-model="form.ct_email_info"
                                type="email"
                                class="mt-1 block w-full"
                                placeholder="Ej: postgrados@ieta.edu.bo"
                            />
                            <InputError :message="form.errors.ct_email_info" class="mt-2" />
                        </div>

                        <!-- Teléfono / WhatsApp de Referencia -->
                        <div>
                            <InputLabel for="ct_telefono_ref" value="Teléfono / WhatsApp de Contacto" class="font-bold text-[#14161C]" />
                            <TextInput
                                id="ct_telefono_ref"
                                v-model="form.ct_telefono_ref"
                                type="text"
                                class="mt-1 block w-full"
                                placeholder="Ej: +591 71234567 / 2-2441234"
                            />
                            <InputError :message="form.errors.ct_telefono_ref" class="mt-2" />
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 4: Portada y Resumen -->
                <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xs border border-gray-100 space-y-6">
                    <div class="border-b border-gray-100 pb-4">
                        <h2 class="text-lg font-extrabold text-[#14161C]">Portada y Resumen</h2>
                        <p class="text-xs text-gray-500">Material visual y sinopsis para las tarjetas y catálogo.</p>
                    </div>

                    <!-- Input Portada (Spatie Media Library) -->
                    <div>
                        <InputLabel for="portada_curso" value="Imagen de Portada" class="font-bold text-[#14161C]" />
                        <input
                            id="portada_curso"
                            type="file"
                            accept="image/png, image/jpeg, image/webp"
                            @change="handleImageChange"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#35449F]/10 file:text-[#35449F] hover:file:bg-[#35449F]/20 cursor-pointer"
                        />
                        <p class="text-[11px] text-gray-400 mt-1">Formatos admitidos: JPG, PNG, WEBP (Hasta 2MB). Relación recomendada 16:9.</p>
                        <InputError :message="form.errors.portada_curso" class="mt-2" />

                        <!-- Vista Previa -->
                        <div v-if="imagePreview" class="mt-3">
                            <p class="text-xs text-gray-500 mb-1 font-semibold">Vista previa de la portada:</p>
                            <img
                                :src="imagePreview"
                                alt="Vista previa"
                                class="h-44 w-72 object-cover rounded-xl border border-gray-200 shadow-xs"
                            />
                        </div>
                    </div>

                    <!-- Resumen / Breve Descripción -->
                    <div>
                        <InputLabel for="ct_descripcion" value="Resumen Breve del Curso" class="font-bold text-[#14161C]" />
                        <textarea
                            id="ct_descripcion"
                            v-model="form.ct_descripcion"
                            rows="3"
                            class="mt-1 block w-full rounded-xl border-gray-300 shadow-xs focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 text-sm leading-relaxed"
                            placeholder="Breve párrafo introductorio para el catálogo y redes sociales..."
                        ></textarea>
                        <InputError :message="form.errors.ct_descripcion" class="mt-2" />
                    </div>
                </div>

                <!-- SECCIÓN 5: Contenido Enriquecido (Editor.js) -->
                <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xs border border-gray-100 space-y-4">
                    <div class="border-b border-gray-100 pb-4">
                        <h2 class="text-lg font-extrabold text-[#14161C]">Contenido Detallado del Curso</h2>
                        <p class="text-xs text-gray-500">
                            Estructura el programa analítico, requisitos, módulos, bibliografía, certificaciones y metodología.
                        </p>
                    </div>

                    <EditorJs
                        v-model="form.ct_contenido"
                        placeholder="Escribe aquí el contenido completo del curso: módulos, requisitos, bibliografía o cronograma detallado..."
                    />
                    <InputError :message="form.errors.ct_contenido" class="mt-2" />
                </div>

                <!-- Botones de Acción Finales -->
                <div class="flex items-center justify-end space-x-3 pt-4">
                    <Link :href="route('admin.cursos.index')">
                        <SecondaryButton class="cursor-pointer">Cancelar</SecondaryButton>
                    </Link>
                    <PrimaryButton :disabled="form.processing" class="cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ form.processing ? 'Guardando curso...' : 'Publicar Curso' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
