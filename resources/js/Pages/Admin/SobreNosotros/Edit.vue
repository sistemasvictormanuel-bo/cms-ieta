<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import EditorJs from '@/Components/EditorJs.vue';

const props = defineProps({
    sobreNosotros: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    sn_mision: props.sobreNosotros?.sn_mision || '',
    sn_vision: props.sobreNosotros?.sn_vision || '',
    sn_historia: props.sobreNosotros?.sn_historia || '',
    sn_objetivos: props.sobreNosotros?.sn_objetivos || { blocks: [] },
    sn_contenido: props.sobreNosotros?.sn_contenido || { blocks: [] },
});

const submit = () => {
    form.put(route('admin.sobre-nosotros.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout title="Sobre Nosotros - Información Institucional">
        <template #header>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 w-full">
                <div>

                    <h1 class="font-extrabold text-2xl text-[#14161C] tracking-tight">
                        Sobre Nosotros
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500 font-medium">
                        Administra y actualiza la información global e institucional del IETA.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <span v-if="form.isDirty" class="text-xs font-semibold text-amber-600 bg-amber-50 px-3 py-1.5 rounded-lg border border-amber-200">
                        ● Cambios sin guardar
                    </span>
                    <PrimaryButton @click="submit" :disabled="form.processing" class="cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ form.processing ? 'Guardando...' : 'Guardar Información' }}
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="max-w-5xl mx-auto pb-12">
            <form @submit.prevent="submit" class="space-y-8">
                <!-- ========================================== -->
                <!-- SECCIÓN 1: MISIÓN Y VISIÓN                -->
                <!-- ========================================== -->
                <div class="bg-white border border-[#E0E0E0] shadow-xs rounded-2xl p-6 md:p-8 space-y-6">
                    <div class="flex items-center space-x-3 pb-4 border-b border-gray-100">
                        <div class="size-9 rounded-xl bg-[#35449F]/10 text-[#35449F] flex items-center justify-center font-bold">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-extrabold text-[#14161C] tracking-tight">
                                Identidad y Propósito Institucional
                            </h2>
                            <p class="text-xs text-gray-500">
                                Misión y visión que guían la excelencia del instituto.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Misión -->
                        <div>
                            <InputLabel for="sn_mision" value="Misión Institucional *" class="font-bold text-[#14161C]" />
                            <textarea
                                id="sn_mision"
                                v-model="form.sn_mision"
                                rows="5"
                                placeholder="Describe el propósito fundamental y la razón de ser del IETA..."
                                class="mt-1 block w-full rounded-xl border-gray-300 shadow-xs focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 text-sm leading-relaxed"
                                required
                            ></textarea>
                            <InputError :message="form.errors.sn_mision" class="mt-2" />
                        </div>

                        <!-- Visión -->
                        <div>
                            <InputLabel for="sn_vision" value="Visión Institucional *" class="font-bold text-[#14161C]" />
                            <textarea
                                id="sn_vision"
                                v-model="form.sn_vision"
                                rows="5"
                                placeholder="Describe hacia dónde se proyecta el IETA en el futuro a mediano y largo plazo..."
                                class="mt-1 block w-full rounded-xl border-gray-300 shadow-xs focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 text-sm leading-relaxed"
                                required
                            ></textarea>
                            <InputError :message="form.errors.sn_vision" class="mt-2" />
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- SECCIÓN 2: HISTORIA INSTITUCIONAL          -->
                <!-- ========================================== -->
                <div class="bg-white border border-[#E0E0E0] shadow-xs rounded-2xl p-6 md:p-8 space-y-6">
                    <div class="flex items-center space-x-3 pb-4 border-b border-gray-100">
                        <div class="size-9 rounded-xl bg-[#F50088]/10 text-[#F50088] flex items-center justify-center font-bold">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-extrabold text-[#14161C] tracking-tight">
                                Historia y Antecedentes
                            </h2>
                            <p class="text-xs text-gray-500">
                                Trayectoria, fundación y evolución histórica del IETA.
                            </p>
                        </div>
                    </div>

                    <div>
                        <InputLabel for="sn_historia" value="Historia del IETA *" class="font-bold text-[#14161C]" />
                        <textarea
                            id="sn_historia"
                            v-model="form.sn_historia"
                            rows="6"
                            placeholder="Escribe la reseña histórica, fundación, logros y evolución del Instituto..."
                            class="mt-1 block w-full rounded-xl border-gray-300 shadow-xs focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 text-sm leading-relaxed"
                            required
                        ></textarea>
                        <InputError :message="form.errors.sn_historia" class="mt-2" />
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- SECCIÓN 3: OBJETIVOS ESTRATÉGICOS          -->
                <!-- ========================================== -->
                <div class="bg-white border border-[#E0E0E0] shadow-xs rounded-2xl p-6 md:p-8 space-y-6">
                    <div class="flex items-center space-x-3 pb-4 border-b border-gray-100">
                        <div class="size-9 rounded-xl bg-[#71589F]/10 text-[#71589F] flex items-center justify-center font-bold">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-extrabold text-[#14161C] tracking-tight">
                                Objetivos Institucionales
                            </h2>
                            <p class="text-xs text-gray-500">
                                Metas y objetivos institucionales, académicos y de investigación.
                            </p>
                        </div>
                    </div>

                    <div>
                        <InputLabel value="Objetivos Estratégicos e Institucionales (Editor por Bloques)" class="font-bold text-[#14161C] mb-2" />
                        <EditorJs
                            v-model="form.sn_objetivos"
                            placeholder="Redacta los objetivos institucionales, añade listas ordenadas, viñetas, secciones temáticas..."
                        />
                        <InputError :message="form.errors.sn_objetivos" class="mt-2" />
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- SECCIÓN 4: CONTENIDO DETALLADO (EDITOR.JS) -->
                <!-- ========================================== -->
                <div class="bg-white border border-[#E0E0E0] shadow-xs rounded-2xl p-6 md:p-8 space-y-6">
                    <div class="flex items-center space-x-3 pb-4 border-b border-gray-100">
                        <div class="size-9 rounded-xl bg-gradient-to-tr from-[#35449F] to-[#F50088] text-white flex items-center justify-center font-bold">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-extrabold text-[#14161C] tracking-tight">
                                Contenido Detallado y Memoria Institucional
                            </h2>
                            <p class="text-xs text-gray-500">
                                Editor enriquecido por bloques: añade organigramas, imágenes, videos, encabezados y listas estructuradas.
                            </p>
                        </div>
                    </div>

                    <div>
                        <InputLabel value="Contenido Enriquecido (Editor por Bloques)" class="font-bold text-[#14161C] mb-2" />
                        <EditorJs
                            v-model="form.sn_contenido"
                            placeholder="Comienza a redactar el contenido institucional, agregar bloques, fotos, videos..."
                        />
                        <InputError :message="form.errors.sn_contenido" class="mt-2" />
                    </div>
                </div>

                <!-- Barra Inferior de Acción -->
                <div class="flex items-center justify-between pt-4 bg-white border border-[#E0E0E0] shadow-xs rounded-2xl p-5">
                    <div class="text-xs text-gray-500 font-medium">
                        Última modificación:
                        <span class="font-bold text-[#14161C]">
                            {{ sobreNosotros.updated_at ? new Date(sobreNosotros.updated_at).toLocaleString('es-ES') : 'Sin modificaciones previas' }}
                        </span>
                    </div>

                    <PrimaryButton :disabled="form.processing" class="cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ form.processing ? 'Guardando cambios...' : 'Guardar Información Institucional' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
