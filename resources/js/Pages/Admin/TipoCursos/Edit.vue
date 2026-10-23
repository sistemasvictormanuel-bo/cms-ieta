<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    tipoCurso: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    tc_nombre: props.tipoCurso.tc_nombre || '',
    tc_descripcion: props.tipoCurso.tc_descripcion || '',
});

const submit = () => {
    form.put(route('admin.tipo-cursos.update', props.tipoCurso.id));
};
</script>

<template>
    <AppLayout :title="'Editar: ' + (tipoCurso.tc_nombre || 'Tipo de Curso')">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 w-full">
                <div>
                    <h1 class="font-extrabold text-2xl text-[#14161C] tracking-tight">
                        Editar Tipo de Curso
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500 font-medium">
                        Actualiza la información del tipo de curso: {{ tipoCurso.tc_nombre }}
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Link :href="route('admin.tipo-cursos.index')">
                        <SecondaryButton class="cursor-pointer">
                            ← Volver al listado
                        </SecondaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="max-w-3xl mx-auto pb-12">
            <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xs border border-gray-100">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Nombre del tipo de curso -->
                    <div>
                        <InputLabel for="tc_nombre" value="Nombre de la Categoría / Tipo *" class="font-bold text-[#14161C]" />
                        <TextInput
                            id="tc_nombre"
                            v-model="form.tc_nombre"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="Ej: Diplomado, Taller Especializado, Certificación..."
                            required
                        />
                        <InputError :message="form.errors.tc_nombre" class="mt-2" />
                    </div>

                    <!-- Descripción -->
                    <div>
                        <InputLabel for="tc_descripcion" value="Descripción / Propósito" class="font-bold text-[#14161C]" />
                        <textarea
                            id="tc_descripcion"
                            v-model="form.tc_descripcion"
                            rows="4"
                            class="mt-1 block w-full rounded-xl border-gray-300 shadow-xs focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 text-sm leading-relaxed"
                            placeholder="Detalles sobre este tipo de programa, su enfoque u orientación..."
                        ></textarea>
                        <InputError :message="form.errors.tc_descripcion" class="mt-2" />
                    </div>

                    <!-- Botones de Acción -->
                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
                        <Link :href="route('admin.tipo-cursos.index')">
                            <SecondaryButton class="cursor-pointer">Cancelar</SecondaryButton>
                        </Link>
                        <PrimaryButton :disabled="form.processing" class="cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ form.processing ? 'Guardando cambios...' : 'Guardar Cambios' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
