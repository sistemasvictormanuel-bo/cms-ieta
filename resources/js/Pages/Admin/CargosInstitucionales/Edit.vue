<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    cargo: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    ci_nombre: props.cargo.ci_nombre || '',
    ci_descripcion: props.cargo.ci_descripcion || '',
});

const submit = () => {
    form.put(route('admin.cargos-institucionales.update', props.cargo.id));
};
</script>

<template>
    <AppLayout title="Editar Cargo Institucional">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 w-full">
                <div class="min-w-0 flex-1">
                    <h1 class="font-extrabold text-xl sm:text-2xl text-[#14161C] tracking-tight truncate">
                        Editar Cargo: {{ cargo.ci_nombre }}
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500 font-medium">
                        Actualiza la denominación y descripción del cargo institucional.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    <Link :href="route('admin.cargos-institucionales.show', cargo.id)">
                        <SecondaryButton class="cursor-pointer">
                            Ver Detalle
                        </SecondaryButton>
                    </Link>
                    <Link :href="route('admin.cargos-institucionales.index')">
                        <SecondaryButton class="cursor-pointer">
                            ← Volver al listado
                        </SecondaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="max-w-2xl mx-auto pb-12">
            <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xs border border-gray-100">
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <InputLabel for="ci_nombre" value="Nombre del Cargo *" />
                        <TextInput
                            id="ci_nombre"
                            v-model="form.ci_nombre"
                            type="text"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError :message="form.errors.ci_nombre" class="mt-1.5" />
                    </div>

                    <div>
                        <InputLabel for="ci_descripcion" value="Descripción / Funciones del Cargo" />
                        <textarea
                            id="ci_descripcion"
                            v-model="form.ci_descripcion"
                            rows="4"
                            class="mt-1 block w-full rounded-xl border-gray-200 p-3 text-sm focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 bg-gray-50/50"
                        ></textarea>
                        <InputError :message="form.errors.ci_descripcion" class="mt-1.5" />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <Link :href="route('admin.cargos-institucionales.index')">
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
