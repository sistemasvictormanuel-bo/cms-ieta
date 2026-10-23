<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const form = useForm({
    inst_nombre: '',
    inst_pais: '',
    inst_ciudad: '',
});

const submit = () => {
    form.post(route('admin.instituciones.store'));
};
</script>

<template>
    <AppLayout title="Nueva Institución">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 w-full">
                <div>
                    <h1 class="font-extrabold text-xl sm:text-2xl text-[#14161C] tracking-tight">
                        Nueva Institución de Adscripción
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500 font-medium">
                        Registra una universidad o entidad académica vinculada al comité editorial.
                    </p>
                </div>
                <div class="shrink-0">
                    <Link :href="route('admin.instituciones.index')">
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
                        <InputLabel for="inst_nombre" value="Nombre de la Institución *" />
                        <TextInput
                            id="inst_nombre"
                            v-model="form.inst_nombre"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="Ej: Universidad Mayor de San Andrés, CONACYT, INE..."
                            required
                        />
                        <InputError :message="form.errors.inst_nombre" class="mt-1.5" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="inst_pais" value="País *" />
                            <TextInput
                                id="inst_pais"
                                v-model="form.inst_pais"
                                type="text"
                                class="mt-1 block w-full text-sm"
                                placeholder="Ej: Bolivia, Argentina, España..."
                                required
                            />
                            <InputError :message="form.errors.inst_pais" class="mt-1.5" />
                        </div>

                        <div>
                            <InputLabel for="inst_ciudad" value="Ciudad *" />
                            <TextInput
                                id="inst_ciudad"
                                v-model="form.inst_ciudad"
                                type="text"
                                class="mt-1 block w-full text-sm"
                                placeholder="Ej: La Paz, Buenos Aires, Madrid..."
                                required
                            />
                            <InputError :message="form.errors.inst_ciudad" class="mt-1.5" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <Link :href="route('admin.instituciones.index')">
                            <SecondaryButton class="cursor-pointer">Cancelar</SecondaryButton>
                        </Link>
                        <PrimaryButton :disabled="form.processing" class="cursor-pointer">
                            {{ form.processing ? 'Guardando...' : 'Crear Institución' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
