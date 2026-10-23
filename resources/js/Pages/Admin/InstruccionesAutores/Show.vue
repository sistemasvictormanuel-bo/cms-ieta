<script setup>
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import EditorRenderer from '@/Components/EditorRenderer.vue';

const props = defineProps({
    instruccion: {
        type: Object,
        required: true,
    },
});

const toggleActivo = () => {
    router.patch(route('admin.instrucciones-autores.toggle-activo', props.instruccion.id), {}, {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :title="`Instrucción: ${instruccion.ia_titulo_seccion}`">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 w-full">
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('admin.instrucciones-autores.index')"
                        class="size-10 rounded-xl bg-white border border-gray-200 flex items-center justify-center text-gray-600 hover:text-gray-900 hover:border-gray-300 transition shadow-2xs"
                        title="Volver"
                    >
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <div>
                        <div class="flex items-center gap-1.5 text-xs font-semibold text-slate-400 mb-0.5">
                            <Link :href="route('admin.instrucciones-autores.index')" class="hover:text-[#35449F]">Instrucciones Autores</Link>
                            <span>/</span>
                            <span class="text-slate-700 font-bold">Vista Previa</span>
                        </div>
                        <h1 class="text-2xl font-black text-slate-900 tracking-tight">
                            {{ instruccion.ia_titulo_seccion }}
                        </h1>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 sm:gap-2.5">
                    <button
                        type="button"
                        @click="toggleActivo"
                        class="px-3.5 py-2 rounded-xl text-xs font-bold border transition cursor-pointer"
                        :class="instruccion.ia_activo
                            ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100'
                            : 'bg-gray-100 text-gray-700 border-gray-300 hover:bg-gray-200'"
                    >
                        {{ instruccion.ia_activo ? '✓ Visible en Portal' : 'Oculto al público' }}
                    </button>
                    <Link :href="route('admin.instrucciones-autores.edit', instruccion.id)">
                        <PrimaryButton class="cursor-pointer gap-2 font-bold text-sm px-4 py-2 shadow-sm">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            <span>Editar Sección</span>
                        </PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="max-w-5xl mx-auto space-y-6 pb-12">
            <!-- Barra informativa de la sección -->
            <div class="bg-white rounded-2xl p-5 border border-gray-200/90 shadow-2xs flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center justify-center px-3 py-1.5 rounded-xl bg-indigo-50 text-[#35449F] font-black text-sm border border-indigo-100">
                        Sección Nº {{ instruccion.ia_orden }}
                    </span>
                    <span
                        :class="instruccion.ia_activo ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-gray-100 text-gray-600 ring-gray-400/20'"
                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold ring-1"
                    >
                        {{ instruccion.ia_activo ? 'Publicado en la Revista' : 'Borrador / Oculto' }}
                    </span>
                </div>

                <div class="text-xs text-gray-500 font-medium flex items-center gap-4">
                    <span>Bloques Editor.js: <strong>{{ instruccion.ia_contenido?.blocks?.length || 0 }}</strong></span>
                    <span>Actualizado: <strong>{{ new Date(instruccion.updated_at).toLocaleDateString() }}</strong></span>
                </div>
            </div>

            <!-- Visualización del Contenido Enriquecido -->
            <div class="bg-white rounded-2xl p-8 sm:p-10 border border-gray-200/90 shadow-2xs">
                <div class="border-b border-gray-100 pb-5 mb-8">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#35449F]">Directriz Revista Varianza</span>
                    <h2 class="text-2xl sm:text-3xl font-black text-gray-900 tracking-tight mt-1">
                        {{ instruccion.ia_titulo_seccion }}
                    </h2>
                </div>

                <div v-if="instruccion.ia_contenido?.blocks?.length" class="prose max-w-none">
                    <EditorRenderer :content="instruccion.ia_contenido" />
                </div>
                <div v-else class="text-center py-12 text-gray-400">
                    <svg class="size-12 mx-auto text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="font-bold text-gray-700">Esta sección no tiene bloques de contenido redactados.</p>
                    <p class="text-xs text-gray-500 mt-1">Edita la sección para agregar viñetas, negritas o directrices técnicas.</p>
                    <div class="mt-4">
                        <Link :href="route('admin.instrucciones-autores.edit', instruccion.id)">
                            <PrimaryButton class="text-xs font-bold cursor-pointer">
                                Redactar Contenido
                            </PrimaryButton>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
