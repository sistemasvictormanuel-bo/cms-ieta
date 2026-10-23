<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import EditorJs from '@/Components/EditorJs.vue';

const props = defineProps({
    instruccion: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    ia_titulo_seccion: props.instruccion.ia_titulo_seccion || '',
    ia_contenido: props.instruccion.ia_contenido || { blocks: [] },
    ia_orden: props.instruccion.ia_orden ?? 1,
    ia_activo: Boolean(props.instruccion.ia_activo),
});

const submit = () => {
    form.put(route('admin.instrucciones-autores.update', props.instruccion.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout title="Editar Instrucción para Autores">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 w-full">
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('admin.instrucciones-autores.index')"
                        class="size-10 rounded-xl bg-white border border-gray-200 flex items-center justify-center text-gray-600 hover:text-gray-900 hover:border-gray-300 transition shadow-2xs shrink-0"
                        title="Volver"
                    >
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <div>
                        <h1 class="font-black text-xl sm:text-2xl text-slate-900 tracking-tight">
                            Editar Sección: {{ instruccion.ia_titulo_seccion }}
                        </h1>
                        <p class="text-[13.5px] sm:text-[15px] text-slate-600 font-medium mt-0.5">
                            Modifica el contenido enriquecido o el orden de la directriz
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 sm:gap-2.5">
                    <Link :href="route('admin.instrucciones-autores.show', instruccion.id)">
                        <SecondaryButton class="cursor-pointer text-[14px] sm:text-[15px] font-bold px-4 py-2 sm:py-2.5">Ver Vista Previa</SecondaryButton>
                    </Link>
                    <PrimaryButton :disabled="form.processing" @click="submit" class="cursor-pointer text-[14px] sm:text-[15.5px] font-bold px-4 sm:px-5 py-2 sm:py-2.5">
                        {{ form.processing ? 'Guardando...' : 'Actualizar Sección' }}
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto pb-12">
            <form @submit.prevent="submit">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <!-- COLUMNA PRINCIPAL (8 COLUMNAS) -->
                    <div class="lg:col-span-8 space-y-6">
                        <!-- CARD 1: TÍTULO DE LA SECCIÓN -->
                        <div class="bg-white rounded-2xl p-6 border border-gray-200/90 shadow-2xs">
                            <div class="border-b border-gray-100 pb-3 mb-5">
                                <h2 class="text-[17px] font-black text-gray-900 tracking-tight">
                                    Título e Identificación de la Sección
                                </h2>
                                <p class="text-[14px] text-gray-600 font-medium mt-0.5">
                                    Nombre visible en el índice de directrices para investigadores
                                </p>
                            </div>

                            <div>
                                <InputLabel for="ia_titulo_seccion" value="Título de la Sección *" class="text-[15.5px] font-bold text-gray-900" />
                                <TextInput
                                    id="ia_titulo_seccion"
                                    v-model="form.ia_titulo_seccion"
                                    type="text"
                                    class="mt-1.5 block w-full text-[16px] font-medium"
                                    placeholder="Ej: 1. Formato y Extensión de los Manuscritos"
                                    required
                                />
                                <InputError :message="form.errors.ia_titulo_seccion" class="mt-1" />
                            </div>
                        </div>

                        <!-- CARD 2: EDITOR ENRIQUECIDO (EDITOR.JS) -->
                        <div class="bg-white rounded-2xl p-6 border border-gray-200/90 shadow-2xs">
                            <div class="border-b border-gray-100 pb-3 mb-5">
                                <h2 class="text-[17px] font-black text-gray-900 tracking-tight">
                                    Contenido y Normas de la Sección
                                </h2>
                                <p class="text-[14px] text-gray-600 font-medium mt-0.5">
                                    Redacta con listas ordenadas, negritas, subtítulos o enlaces explicativos
                                </p>
                            </div>

                            <div>
                                <InputLabel value="Cuerpo de la Instrucción (Editor Enriquecido)" class="text-[15.5px] font-bold text-gray-900 mb-2" />
                                <div class="border border-gray-200 rounded-xl p-4 bg-gray-50/30">
                                    <EditorJs
                                        v-model="form.ia_contenido"
                                        placeholder="Escribe las pautas, requisitos técnicos, formato de márgenes, citas bibliográficas..."
                                    />
                                </div>
                                <InputError :message="form.errors.ia_contenido" class="mt-1" />
                            </div>
                        </div>
                    </div>

                    <!-- COLUMNA LATERAL (4 COLUMNAS) -->
                    <div class="lg:col-span-4 space-y-6">
                        <!-- CARD: ESTADO Y ORDEN -->
                        <div class="bg-white rounded-2xl p-6 border border-gray-200/90 shadow-2xs">
                            <h2 class="text-[17px] font-black text-gray-900 tracking-tight border-b border-gray-100 pb-3 mb-4">
                                Configuración de Publicación
                            </h2>

                            <div class="space-y-5">
                                <!-- Estado Activo -->
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-[15px] font-bold text-gray-900">
                                            {{ form.ia_activo ? 'Visible en el Portal' : 'Sección Oculta' }}
                                        </p>
                                        <p class="text-[13px] text-gray-600 font-medium mt-0.5">
                                            {{ form.ia_activo ? 'Los autores podrán leer esta sección.' : 'Sección temporalmente oculta al público.' }}
                                        </p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input
                                            type="checkbox"
                                            v-model="form.ia_activo"
                                            class="sr-only peer"
                                        />
                                        <div class="w-12 h-6.5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[3px] after:left-[3px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#35449F]"></div>
                                    </label>
                                </div>
                                <InputError :message="form.errors.ia_activo" class="mt-1" />

                                <hr class="border-gray-100" />

                                <!-- Orden Numérico -->
                                <div>
                                    <InputLabel for="ia_orden" value="Orden de Visualización *" class="text-[15.5px] font-bold text-gray-900" />
                                    <TextInput
                                        id="ia_orden"
                                        v-model.number="form.ia_orden"
                                        type="number"
                                        min="1"
                                        class="mt-1.5 block w-full text-[15.5px] font-medium"
                                        required
                                    />
                                    <p class="text-xs text-gray-500 font-medium mt-1">
                                        Define la posición jerárquica en la que aparecerá esta sección en la web (1 = primera).
                                    </p>
                                    <InputError :message="form.errors.ia_orden" class="mt-1" />
                                </div>
                            </div>
                        </div>

                        <!-- METADATOS DEL REGISTRO -->
                        <div class="bg-white rounded-2xl p-6 border border-gray-200/90 shadow-2xs">
                            <h2 class="text-[17px] font-black text-gray-900 tracking-tight border-b border-gray-100 pb-3 mb-3">
                                Información del Registro
                            </h2>
                            <dl class="text-xs space-y-2 text-gray-600 font-medium">
                                <div class="flex justify-between">
                                    <dt class="text-gray-400">Bloques Editor.js:</dt>
                                    <dd class="font-bold text-gray-800">{{ form.ia_contenido?.blocks?.length || 0 }} bloque(s)</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-400">Última actualización:</dt>
                                    <dd class="text-gray-700">{{ new Date(instruccion.updated_at).toLocaleDateString() }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
