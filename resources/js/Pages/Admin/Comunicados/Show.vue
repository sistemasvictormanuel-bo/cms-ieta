<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';

const props = defineProps({
    comunicado: {
        type: Object,
        required: true,
    },
});

const confirmingDeletion = ref(false);
const modalPreviewOpen = ref(false);

const deleteItem = () => {
    router.delete(route('admin.comunicados.destroy', props.comunicado.id));
};

const formatDate = (dateString) => {
    if (!dateString) return 'Indefinido';
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' });
    } catch {
        return dateString;
    }
};

const getTipoBadge = (t) => {
    switch (t) {
        case 'curso':
            return { text: 'Curso / Taller', class: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' };
        case 'articulo':
            return { text: 'Artículo Científico', class: 'bg-indigo-50 text-[#35449F] ring-indigo-600/20' };
        case 'convocatoria':
            return { text: 'Convocatoria', class: 'bg-amber-50 text-amber-700 ring-amber-600/20' };
        case 'noticia':
            return { text: 'Noticia Institucional', class: 'bg-sky-50 text-sky-700 ring-sky-600/20' };
        default:
            return { text: 'Aviso General', class: 'bg-gray-100 text-gray-700 ring-gray-600/20' };
    }
};
</script>

<template>
    <AppLayout title="Detalle del Comunicado">
        <template #header>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 w-full">
                <div>
                    <div class="flex items-center gap-2">
                        <Link
                            :href="route('admin.comunicados.index')"
                            class="text-xs font-bold text-gray-400 hover:text-gray-600 transition"
                        >
                            ← Comunicados
                        </Link>
                        <span class="text-xs text-gray-300">/</span>
                        <span class="text-xs font-semibold text-gray-500">Detalle</span>
                    </div>
                    <h1 class="font-extrabold text-2xl text-[#14161C] tracking-tight mt-1">
                        {{ comunicado.com_titulo }}
                    </h1>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button
                        @click="modalPreviewOpen = true"
                        type="button"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-indigo-50 text-[#35449F] hover:bg-indigo-100 transition cursor-pointer"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Simular Popup
                    </button>
                    <Link :href="route('admin.comunicados.edit', comunicado.id)">
                        <PrimaryButton class="cursor-pointer">
                            Editar
                        </PrimaryButton>
                    </Link>
                    <button
                        @click="confirmingDeletion = true"
                        type="button"
                        class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition cursor-pointer"
                        title="Eliminar"
                    >
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </template>

        <div class="max-w-4xl mx-auto space-y-6 pb-12">
            <!-- Ficha Principal -->
            <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xs border border-gray-100">
                <div class="flex items-center gap-2 flex-wrap mb-4">
                    <span
                        :class="comunicado.com_activo ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-gray-100 text-gray-600 ring-gray-400/20'"
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold ring-1"
                    >
                        {{ comunicado.com_activo ? 'Activo / En Rotación' : 'Pausado / Inactivo' }}
                    </span>
                    <span
                        :class="getTipoBadge(comunicado.com_tipo).class"
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold ring-1"
                    >
                        {{ getTipoBadge(comunicado.com_tipo).text }}
                    </span>
                    <span class="text-xs font-semibold text-gray-400">
                        👁 {{ comunicado.com_vistas || 0 }} visualizaciones
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start">
                    <!-- Flyer Grande -->
                    <div class="rounded-2xl overflow-hidden border border-gray-200 bg-gray-50 flex items-center justify-center p-2">
                        <img
                            v-if="comunicado.flyer_url"
                            :src="comunicado.flyer_url"
                            :alt="comunicado.com_titulo"
                            class="max-h-80 w-auto rounded-xl object-contain shadow-xs"
                        />
                        <div v-else class="text-center text-gray-400 py-16 text-xs">
                            Sin afiche asignado
                        </div>
                    </div>

                    <!-- Datos y Enlace -->
                    <div class="md:col-span-2 space-y-4">
                        <div>
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Título</h3>
                            <p class="text-lg font-bold text-gray-800 mt-0.5">{{ comunicado.com_titulo }}</p>
                        </div>

                        <div v-if="comunicado.com_descripcion">
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Descripción</h3>
                            <p class="text-sm text-gray-700 mt-0.5 leading-relaxed whitespace-pre-line">{{ comunicado.com_descripcion }}</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-3 border-t border-gray-100 text-sm">
                            <div>
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Inicio Vigencia</span>
                                <span class="font-semibold text-gray-800">{{ formatDate(comunicado.com_fecha_inicio) }}</span>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Fin Vigencia</span>
                                <span class="font-semibold text-gray-800">{{ formatDate(comunicado.com_fecha_fin) }}</span>
                            </div>
                        </div>

                        <div v-if="comunicado.com_enlace_url" class="p-3.5 bg-indigo-50/60 rounded-xl border border-indigo-100 space-y-1">
                            <span class="text-[11px] font-bold text-[#35449F] uppercase tracking-wider block">Acción del Botón</span>
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-xs font-mono font-medium text-gray-800 truncate">{{ comunicado.com_enlace_url }}</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-[#35449F] text-white shrink-0">
                                    {{ comunicado.com_texto_boton || 'Más información' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Previsualización (Simulación Popup LMS) -->
        <div
            v-if="modalPreviewOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
        >
            <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-2xl overflow-hidden border border-gray-100 animate-in fade-in zoom-in-95 duration-200">
                <!-- Botón cerrar -->
                <button
                    @click="modalPreviewOpen = false"
                    type="button"
                    class="absolute top-4 right-4 z-10 p-2 bg-black/40 hover:bg-black/60 text-white rounded-full transition cursor-pointer"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <!-- Flyer del Popup -->
                <div v-if="comunicado.flyer_url" class="w-full bg-slate-900 flex items-center justify-center max-h-72 overflow-hidden">
                    <img :src="comunicado.flyer_url" :alt="comunicado.com_titulo" class="w-full h-auto object-cover" />
                </div>

                <!-- Contenido Modal -->
                <div class="p-6 md:p-8 space-y-4 text-center">
                    <span
                        :class="getTipoBadge(comunicado.com_tipo).class"
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold ring-1"
                    >
                        {{ getTipoBadge(comunicado.com_tipo).text }}
                    </span>

                    <h2 class="text-xl font-black text-gray-900 leading-snug">
                        {{ comunicado.com_titulo }}
                    </h2>

                    <p v-if="comunicado.com_descripcion" class="text-sm text-gray-600 leading-relaxed">
                        {{ comunicado.com_descripcion }}
                    </p>

                    <div v-if="comunicado.com_enlace_url" class="pt-2">
                        <a
                            :href="comunicado.com_enlace_url"
                            target="_blank"
                            class="inline-flex items-center justify-center w-full py-3 px-6 rounded-xl font-bold text-white bg-[#35449F] hover:bg-[#28357f] shadow-md transition"
                        >
                            {{ comunicado.com_texto_boton || 'Más información' }} →
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Confirmación Eliminación -->
        <ConfirmationModal :show="confirmingDeletion" @close="confirmingDeletion = false">
            <template #title>Eliminar Comunicado</template>
            <template #content>
                ¿Estás seguro de que deseas eliminar permanentemente este comunicado?
            </template>
            <template #footer>
                <SecondaryButton @click="confirmingDeletion = false" class="mr-2 cursor-pointer">Cancelar</SecondaryButton>
                <DangerButton @click="deleteItem" class="cursor-pointer">Eliminar</DangerButton>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>
