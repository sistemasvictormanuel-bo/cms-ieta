<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    area: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    at_nombre: props.area.at_nombre || '',
    at_descripcion: props.area.at_descripcion || '',
});

const submit = () => {
    form.put(route('admin.area-tematicas.update', props.area.id));
};
</script>

<template>
    <AppLayout title="Editar Área Temática">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 w-full">
                <div>
                    <h1 class="font-extrabold text-2xl text-[#14161C] tracking-tight">
                        Editar Área Temática: {{ area.at_nombre }}
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500 font-medium">
                        Actualiza la información y alcance de esta área temática.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Link :href="route('admin.area-tematicas.show', area.id)">
                        <SecondaryButton class="cursor-pointer">
                            Ver Detalle
                        </SecondaryButton>
                    </Link>
                    <Link :href="route('admin.area-tematicas.index')">
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
                        <InputLabel for="at_nombre" value="Nombre del Área Temática *" />
                        <TextInput
                            id="at_nombre"
                            v-model="form.at_nombre"
                            type="text"
                            class="mt-1 block w-full"
                            required
                        />
                        <InputError :message="form.errors.at_nombre" class="mt-1.5" />
                    </div>

                    <div>
                        <InputLabel for="at_descripcion" value="Descripción / Alcance" />
                        <textarea
                            id="at_descripcion"
                            v-model="form.at_descripcion"
                            rows="4"
                            class="mt-1 block w-full rounded-xl border-gray-200 p-3 text-sm focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 bg-gray-50/50"
                        ></textarea>
                        <InputError :message="form.errors.at_descripcion" class="mt-1.5" />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <Link :href="route('admin.area-tematicas.index')">
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
