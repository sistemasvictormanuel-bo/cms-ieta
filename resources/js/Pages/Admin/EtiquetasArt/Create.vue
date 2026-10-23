<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { slugify } from '@/Utils/slugify';

const form = useForm({
    ea_nombre: '',
    ea_slug: '',
});

const isSlugManuallyEdited = ref(false);

const onNombreInput = () => {
    if (!isSlugManuallyEdited.value) {
        form.ea_slug = slugify(form.ea_nombre);
    }
};

const onSlugInput = () => {
    isSlugManuallyEdited.value = true;
};

const resetSlugToAuto = () => {
    isSlugManuallyEdited.value = false;
    form.ea_slug = slugify(form.ea_nombre);
};

const submit = () => {
    form.post(route('admin.etiquetas-art.store'));
};
</script>

<template>
    <AppLayout title="Nueva Etiqueta de Artículo">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 w-full">
                <div>
                    <h1 class="font-extrabold text-2xl text-[#14161C] tracking-tight">
                        Nueva Etiqueta
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500 font-medium">
                        Crea una etiqueta temática con generación de slug amigable en tiempo real.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Link :href="route('admin.etiquetas-art.index')">
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
                    <!-- Nombre de la Etiqueta -->
                    <div>
                        <InputLabel for="ea_nombre" value="Nombre de la Etiqueta *" />
                        <TextInput
                            id="ea_nombre"
                            v-model="form.ea_nombre"
                            @input="onNombreInput"
                            type="text"
                            class="mt-1 block w-full text-base font-semibold"
                            placeholder="Ej: Inteligencia Artificial, Econometría Aplicada..."
                            required
                            autofocus
                        />
                        <InputError :message="form.errors.ea_nombre" class="mt-1.5" />
                    </div>

                    <!-- Slug Generado en Vivo -->
                    <div>
                        <div class="flex items-center justify-between">
                            <InputLabel for="ea_slug" value="Slug / URL Amigable (Generado en Vivo)" />
                            <button
                                v-if="isSlugManuallyEdited"
                                @click="resetSlugToAuto"
                                type="button"
                                class="text-[11px] font-bold text-[#35449F] hover:underline cursor-pointer"
                            >
                                ⚡ Regenerar automáticamente
                            </button>
                        </div>
                        <div class="mt-1 flex rounded-xl border border-gray-200 focus-within:border-purple-500 focus-within:ring-2 focus-within:ring-purple-200/50 overflow-hidden bg-white transition shadow-2xs">
                            <span class="inline-flex items-center px-3.5 bg-purple-50/80 text-purple-700 text-xs font-mono font-bold border-r border-gray-200 select-none shrink-0">
                                /etiqueta/
                            </span>
                            <input
                                id="ea_slug"
                                v-model="form.ea_slug"
                                @input="onSlugInput"
                                type="text"
                                class="flex-1 block w-full border-0 focus:ring-0 text-sm font-mono text-purple-900 px-3 py-2.5 bg-white"
                                placeholder="inteligencia-artificial"
                                required
                            />
                        </div>
                        <p class="text-[11px] text-gray-500 mt-1 font-medium">
                            Se actualiza automáticamente en vivo mientras escribes el nombre. También puedes editarlo manualmente.
                        </p>
                        <InputError :message="form.errors.ea_slug" class="mt-1.5" />
                    </div>

                    <!-- Preview Badge -->
                    <div class="p-4 rounded-xl bg-gray-50/60 border border-gray-100 flex items-center justify-between">
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Vista Previa:</span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-indigo-50 text-[#35449F] border border-indigo-100">
                            #{{ form.ea_nombre || 'etiqueta-ejemplo' }}
                        </span>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <Link :href="route('admin.etiquetas-art.index')">
                            <SecondaryButton type="button" class="cursor-pointer">
                                Cancelar
                            </SecondaryButton>
                        </Link>
                        <PrimaryButton :disabled="form.processing" class="cursor-pointer">
                            <span v-if="form.processing">Guardando...</span>
                            <span v-else>Crear Etiqueta</span>
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
