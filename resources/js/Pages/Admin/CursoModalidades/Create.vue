<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const form = useForm({
    mc_nombre: '',
    mc_descripcion: '',
});

const submit = () => {
    form.post(route('admin.curso-modalidades.store'));
};
</script>

<template>
    <AppLayout title="Nueva Modalidad de Curso">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 w-full">
                <div>
                    <h1 class="font-extrabold text-2xl text-[#14161C] tracking-tight">
                        Nueva Modalidad de Curso
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500 font-medium">
                        Registra una nueva modalidad de impartición académica (Ej: Presencial, Virtual Sincrónico, Semipresencial).
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Link :href="route('admin.curso-modalidades.index')">
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
                    <!-- Nombre de la modalidad -->
                    <div>
                        <InputLabel for="mc_nombre" value="Nombre de la Modalidad *" class="font-bold text-[#14161C]" />
                        <TextInput
                            id="mc_nombre"
                            v-model="form.mc_nombre"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="Ej: Presencial en Aulas IETA, Virtual por Zoom, Híbrido..."
                            required
                        />
                        <InputError :message="form.errors.mc_nombre" class="mt-2" />
                    </div>

                    <!-- Descripción -->
                    <div>
                        <InputLabel for="mc_descripcion" value="Descripción / Condiciones" class="font-bold text-[#14161C]" />
                        <textarea
                            id="mc_descripcion"
                            v-model="form.mc_descripcion"
                            rows="4"
                            class="mt-1 block w-full rounded-xl border-gray-300 shadow-xs focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 text-sm leading-relaxed"
                            placeholder="Aclaraciones de asistencia, plataforma requerida o sedes de clases..."
                        ></textarea>
                        <InputError :message="form.errors.mc_descripcion" class="mt-2" />
                    </div>

                    <!-- Botones de Acción -->
                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
                        <Link :href="route('admin.curso-modalidades.index')">
                            <SecondaryButton class="cursor-pointer">Cancelar</SecondaryButton>
                        </Link>
                        <PrimaryButton :disabled="form.processing" class="cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ form.processing ? 'Guardando...' : 'Crear Modalidad' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
