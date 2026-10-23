<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    tipo: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    tp_nombre: props.tipo.tp_nombre || '',
    tp_descripcion: props.tipo.tp_descripcion || '',
});

const submit = () => {
    form.put(route('admin.tipo-participaciones.update', props.tipo.id));
};
</script>

<template>
    <AppLayout :title="'Editar: ' + (tipo.tp_nombre || 'Tipo de Participación')">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 w-full">
                <div>
                    <h1 class="font-extrabold text-2xl text-[#14161C] tracking-tight">Editar Tipo de Participación</h1>
                    <p class="text-xs sm:text-sm text-gray-500 font-medium">Modifica los datos de: {{ tipo.tp_nombre }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Link :href="route('admin.tipo-participaciones.index')">
                        <SecondaryButton class="cursor-pointer">← Volver al listado</SecondaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="max-w-3xl mx-auto pb-12">
            <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xs border border-gray-100">
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <InputLabel for="tp_nombre" value="Nombre del Rol / Participación *" class="font-bold text-[#14161C]" />
                        <TextInput
                            id="tp_nombre"
                            v-model="form.tp_nombre"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="Ej: Autor Principal, Primer Coautor..."
                            required
                        />
                        <InputError :message="form.errors.tp_nombre" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="tp_descripcion" value="Descripción" class="font-bold text-[#14161C]" />
                        <textarea
                            id="tp_descripcion"
                            v-model="form.tp_descripcion"
                            rows="4"
                            class="mt-1 block w-full rounded-xl border-gray-300 shadow-xs focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 text-sm leading-relaxed"
                        ></textarea>
                        <InputError :message="form.errors.tp_descripcion" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
                        <Link :href="route('admin.tipo-participaciones.index')">
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
