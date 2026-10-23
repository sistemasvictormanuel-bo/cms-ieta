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

const props = defineProps({
    etiqueta: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    ea_nombre: props.etiqueta.ea_nombre || '',
    ea_slug: props.etiqueta.ea_slug || '',
});

const autoSlug = ref(false);

const onNombreInput = () => {
    if (autoSlug.value) {
        form.ea_slug = slugify(form.ea_nombre);
    }
};

const regenerateSlug = () => {
    form.ea_slug = slugify(form.ea_nombre);
};

const submit = () => {
    form.put(route('admin.etiquetas-art.update', props.etiqueta.id));
};
</script>

<template>
    <AppLayout title="Editar Etiqueta de Artículo">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 w-full">
                <div>
                    <h1 class="font-extrabold text-2xl text-[#14161C] tracking-tight">
                        Editar Etiqueta: {{ etiqueta.ea_nombre }}
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500 font-medium">
                        Modifica los datos de la etiqueta y su identificador permanente de URL.
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
                            placeholder="Ej: Inteligencia Artificial..."
                            required
                        />
                        <InputError :message="form.errors.ea_nombre" class="mt-1.5" />
                    </div>

                    <!-- Slug Generado en Vivo / Editable -->
                    <div>
                        <div class="flex items-center justify-between">
                            <InputLabel for="ea_slug" value="Slug / URL Amigable" />
                            <div class="flex items-center gap-3">
                                <label class="inline-flex items-center text-xs text-gray-500 font-medium cursor-pointer">
                                    <input
                                        type="checkbox"
                                        v-model="autoSlug"
                                        class="rounded text-[#35449F] focus:ring-[#35449F] mr-1.5"
                                    />
                                    Sincronizar en vivo
                                </label>
                                <button
                                    @click="regenerateSlug"
                                    type="button"
                                    class="text-[11px] font-bold text-[#35449F] hover:underline cursor-pointer"
                                >
                                    ⚡ Regenerar
                                </button>
                            </div>
                        </div>
                        <div class="mt-1 flex rounded-xl border border-gray-200 focus-within:border-purple-500 focus-within:ring-2 focus-within:ring-purple-200/50 overflow-hidden bg-white transition shadow-2xs">
                            <span class="inline-flex items-center px-3.5 bg-purple-50/80 text-purple-700 text-xs font-mono font-bold border-r border-gray-200 select-none shrink-0">
                                /etiqueta/
                            </span>
                            <input
                                id="ea_slug"
                                v-model="form.ea_slug"
                                type="text"
                                class="flex-1 block w-full border-0 focus:ring-0 text-sm font-mono text-purple-900 px-3 py-2.5 bg-white"
                                required
                            />
                        </div>
                        <InputError :message="form.errors.ea_slug" class="mt-1.5" />
                    </div>

                    <!-- Estadísticas de la Etiqueta -->
                    <div class="p-4 rounded-xl bg-gray-50/60 border border-gray-100 flex items-center justify-between">
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Artículos Vinculados:</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-[#35449F] border border-indigo-100">
                            {{ etiqueta.articulos_count || 0 }} artículos asociados
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
                            <span v-else>Guardar Cambios</span>
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
