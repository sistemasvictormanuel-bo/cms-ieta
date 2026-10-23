<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    grado: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    ga_nombre: props.grado.ga_nombre || '',
    ga_abreviatura: props.grado.ga_abreviatura || '',
    ga_descripcion: props.grado.ga_descripcion || '',
});

const submit = () => {
    form.put(route('admin.grado-academicos.update', props.grado.id));
};
</script>

<template>
    <AppLayout :title="'Editar: ' + (grado.ga_nombre || 'Grado Académico')">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 w-full">
                <div>
                    <h1 class="font-extrabold text-xl sm:text-2xl text-[#14161C] tracking-tight">Editar Grado Académico</h1>
                    <p class="text-xs sm:text-sm text-gray-500 font-medium">Modifica los datos de: {{ grado.ga_nombre }}</p>
                </div>
                <div class="shrink-0">
                    <Link :href="route('admin.grado-academicos.index')">
                        <SecondaryButton class="cursor-pointer">← Volver al listado</SecondaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="max-w-3xl mx-auto pb-12">
            <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xs border border-gray-100">
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <InputLabel for="ga_nombre" value="Nombre del Grado Académico *" class="font-bold text-[#14161C]" />
                        <TextInput
                            id="ga_nombre"
                            v-model="form.ga_nombre"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="Ej: Doctor en Estadística, Magíster en Ciencias, Licenciado..."
                            required
                        />
                        <InputError :message="form.errors.ga_nombre" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="ga_abreviatura" value="Abreviatura Oficial *" class="font-bold text-[#14161C]" />
                        <TextInput
                            id="ga_abreviatura"
                            v-model="form.ga_abreviatura"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="Ej: Ph.D., M.Sc., Lic."
                            required
                        />
                        <InputError :message="form.errors.ga_abreviatura" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="ga_descripcion" value="Descripción (Opcional)" class="font-bold text-[#14161C]" />
                        <textarea
                            id="ga_descripcion"
                            v-model="form.ga_descripcion"
                            rows="4"
                            class="mt-1 block w-full rounded-xl border-gray-300 shadow-xs focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 text-sm leading-relaxed"
                        ></textarea>
                        <InputError :message="form.errors.ga_descripcion" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
                        <Link :href="route('admin.grado-academicos.index')">
                            <SecondaryButton class="cursor-pointer">Cancelar</SecondaryButton>
                        </Link>
                        <PrimaryButton :disabled="form.processing" class="cursor-pointer">
                            {{ form.processing ? 'Guardando...' : 'Guardar Cambios' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
