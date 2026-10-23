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
    ta_nombre: props.tipo.ta_nombre || '',
    ta_descripcion: props.tipo.ta_descripcion || '',
    ta_estado: Boolean(props.tipo.ta_estado),
});

const submit = () => {
    form.put(route('admin.tipo-autores.update', props.tipo.id));
};
</script>

<template>
    <AppLayout :title="'Editar: ' + (tipo.ta_nombre || 'Tipo de Autor')">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 w-full">
                <div>
                    <h1 class="font-extrabold text-2xl text-[#14161C] tracking-tight">Editar Tipo de Autor</h1>
                    <p class="text-xs sm:text-sm text-gray-500 font-medium">Modifica los datos de: {{ tipo.ta_nombre }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Link :href="route('admin.tipo-autores.index')">
                        <SecondaryButton class="cursor-pointer">← Volver al listado</SecondaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="max-w-3xl mx-auto pb-12">
            <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xs border border-gray-100">
                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <InputLabel for="ta_nombre" value="Nombre del Tipo de Autor *" class="font-bold text-[#14161C]" />
                        <TextInput
                            id="ta_nombre"
                            v-model="form.ta_nombre"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="Ej: Investigador Titular, Docente Investigador..."
                            required
                        />
                        <InputError :message="form.errors.ta_nombre" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="ta_descripcion" value="Descripción" class="font-bold text-[#14161C]" />
                        <textarea
                            id="ta_descripcion"
                            v-model="form.ta_descripcion"
                            rows="4"
                            class="mt-1 block w-full rounded-xl border-gray-300 shadow-xs focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 text-sm leading-relaxed"
                        ></textarea>
                        <InputError :message="form.errors.ta_descripcion" class="mt-2" />
                    </div>

                    <div>
                        <label class="flex items-center gap-3 cursor-pointer select-none">
                            <input
                                type="checkbox"
                                v-model="form.ta_estado"
                                class="rounded-md border-gray-300 text-[#35449F] focus:ring-[#35449F] size-5"
                            />
                            <span class="text-sm font-bold text-[#14161C]">Activo (disponible para autores)</span>
                        </label>
                        <InputError :message="form.errors.ta_estado" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
                        <Link :href="route('admin.tipo-autores.index')">
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
