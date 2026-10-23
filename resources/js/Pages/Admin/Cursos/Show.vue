<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import EditorRenderer from '@/Components/EditorRenderer.vue';
import { assetUrl } from '@/utils/asset';

const props = defineProps({
    curso: {
        type: Object,
        required: true,
    },
});

const formatDate = (dateStr) => {
    if (!dateStr) return 'No especificado';
    return new Date(dateStr).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const formatCurrency = (val) => {
    if (val === null || val === undefined || Number(val) === 0) return 'Gratuito';
    return `Bs. ${Number(val).toFixed(2)}`;
};

const getDocenteName = (docente) => {
    if (!docente) return 'Sin docente asignado';
    if (docente.nombre_completo) return docente.nombre_completo;
    return `${docente.p_nombre} ${docente.p_apellido_paterno || ''} ${docente.p_apellido_materno || ''}`.trim();
};
</script>

<template>
    <AppLayout :title="curso.ct_titulo || 'Detalle del Curso'">
        <template #header>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 w-full">
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-1.5">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#35449F]/10 text-[#35449F]">
                            {{ curso.tipo_curso?.tc_nombre || 'Curso General' }}
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                            {{ curso.modalidad_curso?.mc_nombre || 'Modalidad General' }}
                        </span>
                        <span
                            :class="[
                                curso.ct_activo
                                    ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20'
                                    : 'bg-gray-100 text-gray-600 ring-1 ring-gray-400/20'
                            ]"
                            class="px-2.5 py-0.5 rounded-full text-xs font-bold"
                        >
                            {{ curso.ct_activo ? 'Publicado (Activo)' : 'Borrador (Inactivo)' }}
                        </span>
                    </div>
                    <h1 class="font-extrabold text-2xl text-[#14161C] tracking-tight">
                        {{ curso.ct_titulo }}
                    </h1>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <Link :href="route('admin.cursos.index')">
                        <SecondaryButton class="cursor-pointer">
                            ← Volver al catálogo
                        </SecondaryButton>
                    </Link>
                    <Link :href="route('admin.cursos.edit', curso.id)">
                        <PrimaryButton class="cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Editar Curso
                        </PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="max-w-5xl mx-auto space-y-8 pb-16">
            <!-- Portada y Tarjetas Resumen de Datos Clave -->
            <div class="bg-white rounded-2xl shadow-xs border border-gray-100 overflow-hidden">
                <div v-if="curso.portada_url" class="w-full h-72 md:h-96 relative bg-gray-900">
                    <img
                        :src="assetUrl(curso.portada_url)"
                        :alt="curso.ct_titulo"
                        class="w-full h-full object-cover"
                    />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>
                    <div class="absolute bottom-6 left-6 right-6 text-white">
                        <p class="text-xs font-extrabold uppercase tracking-wider text-pink-400 mb-1">
                            {{ curso.tipo_curso?.tc_nombre }} • {{ curso.modalidad_curso?.mc_nombre }}
                        </p>
                        <h2 class="text-xl md:text-2xl font-black text-white">
                            {{ curso.ct_titulo }}
                        </h2>
                    </div>
                </div>

                <!-- Parrilla de Información Rápida -->
                <div class="p-6 md:p-8 grid grid-cols-2 md:grid-cols-4 gap-6 bg-gray-50/50 border-b border-gray-100">
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Docente</p>
                        <p class="text-sm font-extrabold text-[#14161C]">
                            {{ getDocenteName(curso.docente) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Inversión / Costo</p>
                        <p class="text-sm font-extrabold text-[#35449F]">
                            {{ formatCurrency(curso.ct_costo) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Fecha de Inicio</p>
                        <p class="text-sm font-extrabold text-[#14161C]">
                            {{ formatDate(curso.ct_fecha_inicio) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Fecha de Fin</p>
                        <p class="text-sm font-extrabold text-[#14161C]">
                            {{ formatDate(curso.ct_fecha_fin) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Horario</p>
                        <p class="text-sm font-bold text-gray-700">
                            {{ curso.ct_hora_inicio ? `${curso.ct_hora_inicio} a ${curso.ct_hora_fin || ''}` : 'No definido' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Duración</p>
                        <p class="text-sm font-bold text-gray-700">
                            {{ curso.ct_duracion || 'No definida' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Email de Contacto</p>
                        <p class="text-sm font-bold text-gray-700 truncate">
                            {{ curso.ct_email_info || 'No especificado' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Teléfono / WhatsApp</p>
                        <p class="text-sm font-bold text-gray-700">
                            {{ curso.ct_telefono_ref || 'No especificado' }}
                        </p>
                    </div>
                </div>

                <!-- Resumen Breve -->
                <div v-if="curso.ct_descripcion" class="p-6 md:p-8">
                    <h3 class="text-xs font-extrabold uppercase tracking-wider text-[#71589F] mb-2">
                        Resumen del Curso
                    </h3>
                    <p class="text-sm text-gray-700 leading-relaxed">
                        {{ curso.ct_descripcion }}
                    </p>
                </div>
            </div>

            <!-- Contenido Enriquecido del Curso (EditorRenderer) -->
            <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xs border border-gray-100 space-y-6">
                <div class="border-b border-gray-100 pb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-black text-[#14161C]">
                            Contenido y Programa Analítico
                        </h2>
                        <p class="text-xs text-gray-500">
                            Renderizado con bloques enriquecidos estructurados por Editor.js.
                        </p>
                    </div>
                    <span class="text-xs font-bold text-gray-400 font-mono">
                        Slug: /{{ curso.ct_slug }}
                    </span>
                </div>

                <div class="prose max-w-none text-[#14161C]">
                    <EditorRenderer :content="curso.ct_contenido" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
