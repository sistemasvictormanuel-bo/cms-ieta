<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const form = useForm({
    ep_nombre: '',
    ep_descripcion: '',
    ep_estado: true,
});

const submit = () => {
    form.post(route('admin.especialidades.store'));
};
</script>

<template>
    <AppLayout title="Nueva Especialidad">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 w-full">
                <div>
                    <h1 class="font-extrabold text-2xl text-[#14161C] tracking-tight">Nueva Especialidad</h1>
                    <p class="text-xs sm:text-sm text-gray-500 font-medium">Registra un área de especialidad científica.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Link :href="route('admin.especialidades.index')">
                        <SecondaryButton class="cursor-pointer">← Volver al listado</SecondaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="max-w-3xl mx-auto pb-12">
            <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xs border border-gray-100">
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <InputLabel for="ep_nombre" value="Nombre de Especialidad *" class="font-bold text-[#14161C]" />
                        <TextInput
                            id="ep_nombre"
                            v-model="form.ep_nombre"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="Ej: Bioestadística, Econometría, Minería de Datos..."
                            required
                        />
                        <InputError :message="form.errors.ep_nombre" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="ep_descripcion" value="Descripción (Opcional)" class="font-bold text-[#14161C]" />
                        <textarea
                            id="ep_descripcion"
                            v-model="form.ep_descripcion"
                            rows="4"
                            class="mt-1 block w-full rounded-xl border-gray-300 shadow-xs focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 text-sm leading-relaxed"
                            placeholder="Breve descripción del alcance temático de la especialidad..."
                        ></textarea>
                        <InputError :message="form.errors.ep_descripcion" class="mt-2" />
                    </div>

                    <div>
                        <label class="flex items-center gap-3 cursor-pointer select-none">
                            <input
                                type="checkbox"
                                v-model="form.ep_estado"
                                class="rounded-md border-gray-300 text-[#35449F] focus:ring-[#35449F] size-5"
                            />
                            <span class="text-sm font-bold text-[#14161C]">Activo (disponible para autores)</span>
                        </label>
                        <InputError :message="form.errors.ep_estado" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
                        <Link :href="route('admin.especialidades.index')">
                            <SecondaryButton class="cursor-pointer">Cancelar</SecondaryButton>
                        </Link>
                        <PrimaryButton :disabled="form.processing" class="cursor-pointer">
                            {{ form.processing ? 'Guardando...' : 'Crear Especialidad' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
