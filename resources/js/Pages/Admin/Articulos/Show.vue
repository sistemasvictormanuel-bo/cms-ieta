<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import DialogModal from '@/Components/DialogModal.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    articulo: {
        type: Object,
        required: true,
    },
    participaciones: {
        type: Array,
        default: () => [],
    },
    similares: {
        type: Array,
        default: () => [],
    },
    mas_leidos_autor: {
        type: Array,
        default: () => [],
    },
    puede_revisar: {
        type: Boolean,
        default: false,
    },
    es_autor: {
        type: Boolean,
        default: false,
    },
});

const confirmingDeletion = ref(false);

const deleteItem = () => {
    router.delete(route('admin.articulos.destroy', props.articulo.id));
};

// Acciones de Revisión Editorial
const confirmingApproval = ref(false);
const confirmingRejection = ref(false);
const rejectMotivo = ref('');
const rejectError = ref('');
const processingReview = ref(false);

const approveArticle = () => {
    processingReview.value = true;
    router.post(route('admin.articulos.aprobar', props.articulo.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            confirmingApproval.value = false;
        },
        onFinish: () => {
            processingReview.value = false;
        },
    });
};

const openRejectModal = () => {
    rejectMotivo.value = '';
    rejectError.value = '';
    confirmingRejection.value = true;
};

const rejectArticle = () => {
    if (!rejectMotivo.value.trim()) {
        rejectError.value = 'Debes especificar el motivo u observaciones para el autor.';
        return;
    }
    processingReview.value = true;
    router.post(route('admin.articulos.rechazar', props.articulo.id), {
        motivo: rejectMotivo.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            confirmingRejection.value = false;
            rejectMotivo.value = '';
            rejectError.value = '';
        },
        onError: (err) => {
            if (err.motivo) rejectError.value = err.motivo;
        },
        onFinish: () => {
            processingReview.value = false;
        },
    });
};

const formatDate = (dateString) => {
    if (!dateString) return '—';
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' });
    } catch {
        return dateString;
    }
};

const authorFullName = (autor) => {
    if (autor?.persona?.nombre_completo) {
        return autor.persona.nombre_completo;
    }
    if (autor?.persona) {
        return `${autor.persona.p_nombre} ${autor.persona.p_apellido_paterno || ''} ${autor.persona.p_apellido_materno || ''}`.trim();
    }
    return `Autor #${autor.id}`;
};

const getParticipationName = (autor) => {
    const partId = autor.pivot?.tipo_participaciones_id;
    if (!partId) return 'Participante';
    const found = props.participaciones.find(p => p.id === partId);
    return found ? found.tp_nombre : 'Participante';
};
</script>

<template>
    <AppLayout title="Detalle de Artículo Científico">
        <template #header>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 w-full">
                <div>
                    <h1 class="font-extrabold text-2xl text-[#14161C] tracking-tight">
                        Detalle del Artículo
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500 font-medium">
                        Consulta los metadatos completos, autores, estado editorial y archivo adjunto.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Link :href="route('admin.articulos.index')">
                        <SecondaryButton class="cursor-pointer">
                            ← Volver al listado
                        </SecondaryButton>
                    </Link>

                    <!-- Botones de dictamen para Revisor / Administrador -->
                    <button
                        v-if="puede_revisar && articulo.estado_revision !== 'aprobado'"
                        @click="confirmingApproval = true"
                        type="button"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition cursor-pointer"
                        title="Aprobar y publicar este artículo"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        Aprobar y Publicar
                    </button>

                    <button
                        v-if="puede_revisar && articulo.estado_revision !== 'rechazado'"
                        @click="openRejectModal"
                        type="button"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 transition cursor-pointer"
                        title="Rechazar con observaciones editoriales"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Rechazar con Obs.
                    </button>

                    <a
                        v-if="articulo.archivo_url"
                        :href="articulo.archivo_url"
                        target="_blank"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold bg-red-50 text-red-700 hover:bg-red-100 transition"
                    >
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Descargar PDF
                    </a>
                    <Link :href="route('admin.articulos.edit', articulo.id)">
                        <PrimaryButton class="cursor-pointer">
                            <svg class="size-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Editar
                        </PrimaryButton>
                    </Link>
                    <button
                        v-if="!es_autor || articulo.estado_revision === 'borrador'"
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

        <div class="space-y-6 pb-12 max-w-6xl mx-auto">
            <!-- Encabezado del Artículo -->
            <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xs border border-gray-100">
                <div class="flex items-center gap-2 flex-wrap mb-3">
                    <!-- Estado de Revisión -->
                    <span
                        v-if="articulo.estado_revision === 'aprobado'"
                        class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20"
                    >
                        <span class="size-1.5 rounded-full bg-emerald-500"></span>
                        Aprobado (Publicado)
                    </span>
                    <span
                        v-else-if="articulo.estado_revision === 'pendiente'"
                        class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 ring-1 ring-amber-600/20"
                    >
                        <span class="size-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                        Pendiente de Revisión
                    </span>
                    <span
                        v-else-if="articulo.estado_revision === 'rechazado'"
                        class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 ring-1 ring-rose-600/20"
                    >
                        <span class="size-1.5 rounded-full bg-rose-500"></span>
                        Rechazado con Observaciones
                    </span>
                    <span
                        v-else
                        class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700 ring-1 ring-gray-400/20"
                    >
                        Borrador
                    </span>

                    <span v-if="articulo.tipo_articulo" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-[#35449F] border border-blue-200">
                        🔬 {{ articulo.tipo_articulo.tar_nombre }}
                    </span>
                    <Link
                        v-if="articulo.edicion"
                        :href="route('admin.ediciones-varianza.show', articulo.edicion.id)"
                        class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200 hover:bg-amber-100 transition"
                    >
                        📖 Revista Varianza Nº {{ articulo.edicion.ev_numero }} ({{ articulo.edicion.ev_anio }})
                        <span v-if="articulo.art_pag_inicio && articulo.art_pag_fin" class="text-slate-500 font-mono">
                            [pp. {{ articulo.art_pag_inicio }}-{{ articulo.art_pag_fin }}]
                        </span>
                    </Link>
                    <span v-if="articulo.art_doi" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-blue-50 text-blue-700">
                        DOI: {{ articulo.art_doi }}
                    </span>
                    <span v-if="articulo.art_orcid" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-emerald-50 text-emerald-700">
                        ORCID: {{ articulo.art_orcid }}
                    </span>
                    <span v-if="articulo.art_slug" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-mono font-medium bg-purple-50 text-purple-700 ring-1 ring-purple-600/20">
                        slug: /{{ articulo.art_slug }}
                    </span>
                    <span v-if="articulo.art_num_paginas" class="text-xs font-semibold text-gray-400">
                        {{ articulo.art_num_paginas }} páginas
                    </span>
                    <span class="text-xs font-semibold text-gray-400">
                        👁 {{ articulo.art_vistas || 0 }} lecturas
                    </span>
                </div>

                <h2 class="text-2xl font-black text-[#14161C] leading-snug tracking-tight">
                    {{ articulo.art_titulo }}
                </h2>

                <!-- Etiquetas / Palabras Clave -->
                <div v-if="articulo.etiquetas && articulo.etiquetas.length" class="flex flex-wrap gap-1.5 mt-3">
                    <span
                        v-for="tag in articulo.etiquetas"
                        :key="tag.id"
                        class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-semibold bg-indigo-50 text-[#35449F] border border-indigo-100"
                    >
                        #{{ tag.ea_nombre }}
                    </span>
                </div>
                <div v-else-if="articulo.etiquetas_list && articulo.etiquetas_list.length" class="flex flex-wrap gap-1.5 mt-3">
                    <span
                        v-for="(tag, tIdx) in articulo.etiquetas_list"
                        :key="tIdx"
                        class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-semibold bg-indigo-50 text-[#35449F] border border-indigo-100"
                    >
                        #{{ tag }}
                    </span>
                </div>

                <!-- Fechas de Recepción y Aceptación -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 mt-6 pt-6 border-t border-gray-100 text-sm">
                    <div>
                        <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Fecha de Recepción</div>
                        <div class="font-semibold text-gray-800 mt-1">
                            {{ formatDate(articulo.art_fecha_recepcion) }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Fecha de Aceptación</div>
                        <div class="font-semibold mt-1">
                            <span v-if="articulo.art_fecha_aceptado" class="text-gray-800">
                                {{ formatDate(articulo.art_fecha_aceptado) }}
                            </span>
                            <span v-else class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-700 bg-amber-50 px-2.5 py-0.5 rounded-md border border-amber-200/80">
                                <span class="size-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                Pendiente de aprobación
                            </span>
                        </div>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Documento Adjunto</div>
                        <div class="mt-1">
                            <a
                                v-if="articulo.archivo_url"
                                :href="articulo.archivo_url"
                                target="_blank"
                                class="text-xs font-bold text-[#35449F] hover:underline inline-flex items-center gap-1"
                            >
                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                                Abrir Documento Completo
                            </a>
                            <span v-else class="text-xs text-gray-400 italic">Sin archivo adjunto</span>
                        </div>
                    </div>
                </div>

                <!-- Dictamen Editorial / Observaciones -->
                <div v-if="articulo.estado_revision === 'rechazado' && articulo.revision_motivo" class="mt-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 flex items-start gap-3">
                    <div class="p-2 bg-rose-100 text-rose-700 rounded-lg shrink-0">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="flex-1 text-xs">
                        <h4 class="font-bold text-rose-950 text-sm">Observaciones del Revisor Editorial</h4>
                        <p class="mt-1 leading-relaxed text-rose-800 whitespace-pre-line">{{ articulo.revision_motivo }}</p>
                        <div v-if="articulo.revisado_por" class="mt-2 text-[11px] text-rose-700 font-semibold">
                            Revisado por: {{ articulo.revisado_por.name }} • {{ formatDate(articulo.revisado_at) }}
                        </div>
                    </div>
                </div>

                <div v-else-if="articulo.estado_revision === 'aprobado' && articulo.revisado_por" class="mt-6 p-3.5 rounded-xl bg-emerald-50/70 border border-emerald-200/80 text-emerald-900 flex items-center gap-3">
                    <div class="p-1.5 bg-emerald-100 text-emerald-700 rounded-lg shrink-0">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div class="text-xs">
                        <span class="font-bold">Dictamen Favorable:</span> Aprobado y publicado por <span class="font-semibold">{{ articulo.revisado_por.name }}</span> el {{ formatDate(articulo.revisado_at) }}.
                    </div>
                </div>
            </div>

            <!-- Autores Participantes -->
            <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xs border border-gray-100">
                <h3 class="text-lg font-extrabold text-[#14161C] mb-4 pb-2 border-b border-gray-100 flex items-center justify-between">
                    <span class="flex items-center gap-2">
                        <svg class="size-5 text-[#35449F]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Autores del Artículo
                    </span>
                    <span class="text-xs font-bold text-gray-400">
                        {{ articulo.autores?.length || 0 }} autores registrados
                    </span>
                </h3>

                <div v-if="articulo.autores?.length" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div
                        v-for="autor in articulo.autores"
                        :key="autor.id"
                        class="p-4 rounded-xl border border-gray-100 hover:border-gray-200 transition bg-gray-50/40 flex items-start justify-between gap-3"
                    >
                        <div class="space-y-1">
                            <Link
                                :href="route('admin.autores.show', autor.id)"
                                class="font-bold text-sm text-[#14161C] hover:text-[#35449F] transition"
                            >
                                {{ authorFullName(autor) }}
                            </Link>
                            <div class="text-xs text-gray-500 flex items-center gap-2 flex-wrap">
                                <span v-if="autor.grado_academico" class="font-semibold text-gray-700">
                                    {{ autor.grado_academico.ga_nombre }}
                                </span>
                                <span v-if="autor.especialidades && autor.especialidades.length" class="text-gray-400">
                                    • {{ autor.especialidades.map(e => e.ep_nombre).join(', ') }}
                                </span>
                                <span v-else-if="autor.especialidad" class="text-gray-400">
                                    • {{ autor.especialidad.ep_nombre }}
                                </span>
                            </div>
                        </div>

                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-[#35449F]/10 text-[#35449F] shrink-0">
                            {{ getParticipationName(autor) }}
                        </span>
                    </div>
                </div>
                <div v-else class="text-sm text-gray-400 italic py-6 text-center">
                    No se han vinculado autores a este artículo aún.
                </div>
            </div>

            <!-- Resumen y Abstract -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Resumen en Español -->
                <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xs border border-gray-100 flex flex-col">
                    <h3 class="text-base font-extrabold text-[#14161C] mb-3 pb-2 border-b border-gray-100">
                        Resumen
                    </h3>
                    <div class="text-sm text-gray-700 leading-relaxed whitespace-pre-line flex-1">
                        {{ articulo.art_resumen || 'Sin resumen registrado.' }}
                    </div>
                </div>

                <!-- Abstract en Inglés -->
                <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xs border border-gray-100 flex flex-col">
                    <h3 class="text-base font-extrabold text-[#14161C] mb-3 pb-2 border-b border-gray-100">
                        Abstract
                    </h3>
                    <div class="text-sm text-gray-700 leading-relaxed whitespace-pre-line flex-1">
                        {{ articulo.art_abstracto || 'Sin abstracto registrado.' }}
                    </div>
                </div>
            </div>

            <!-- Referencias Bibliográficas -->
            <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xs border border-gray-100">
                <h3 class="text-base font-extrabold text-[#14161C] mb-3 pb-2 border-b border-gray-100 flex items-center gap-2">
                    <svg class="size-5 text-[#35449F]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    Referencias Bibliográficas
                </h3>
                <div v-if="articulo.art_referencias" class="text-xs font-mono text-gray-700 bg-gray-50/70 p-4 rounded-xl border border-gray-100 whitespace-pre-line leading-relaxed">
                    {{ articulo.art_referencias }}
                </div>
                <div v-else class="text-sm text-gray-400 italic py-4 text-center">
                    No se han registrado referencias bibliográficas para este artículo.
                </div>
            </div>

            <!-- Algoritmos de Recomendación: Artículos Similares y Más Leídos Mismo Autor -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Artículos Similares -->
                <div class="bg-white rounded-2xl p-6 shadow-xs border border-gray-100 flex flex-col">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                        <div class="flex items-center gap-2">
                            <span class="p-1.5 bg-indigo-50 text-[#35449F] rounded-lg">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                            </span>
                            <div>
                                <h3 class="text-sm font-extrabold text-[#14161C]">Artículos Similares</h3>
                                <p class="text-[11px] text-gray-400">Por etiquetas coincidentes & vistas</p>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-gray-400">{{ similares.length }} artículos</span>
                    </div>

                    <div v-if="similares.length" class="space-y-3 flex-1">
                        <div
                            v-for="sim in similares"
                            :key="sim.id"
                            class="p-3 rounded-xl border border-gray-100 hover:border-indigo-100 hover:bg-indigo-50/20 transition flex flex-col gap-1.5"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <Link
                                    :href="route('admin.articulos.show', sim.id)"
                                    class="text-xs font-bold text-gray-900 hover:text-[#35449F] transition line-clamp-2"
                                >
                                    {{ sim.art_titulo }}
                                </Link>
                                <span class="text-[11px] font-bold text-gray-500 whitespace-nowrap bg-gray-50 px-2 py-0.5 rounded-md">
                                    👁 {{ sim.art_vistas || 0 }}
                                </span>
                            </div>
                            <div v-if="sim.etiquetas && sim.etiquetas.length" class="text-[10px] text-gray-400 line-clamp-1 font-medium">
                                Tags: {{ sim.etiquetas.map(t => t.ea_nombre).join(', ') }}
                            </div>
                            <div v-else-if="sim.art_etiquetas" class="text-[10px] text-gray-400 line-clamp-1 font-medium">
                                Tags: {{ sim.art_etiquetas }}
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-xs text-gray-400 italic py-6 text-center">
                        No hay suficientes artículos similares registrados aún.
                    </div>
                </div>

                <!-- Más Leídos del Mismo Autor -->
                <div class="bg-white rounded-2xl p-6 shadow-xs border border-gray-100 flex flex-col">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                        <div class="flex items-center gap-2">
                            <span class="p-1.5 bg-amber-50 text-amber-600 rounded-lg">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </span>
                            <div>
                                <h3 class="text-sm font-extrabold text-[#14161C]">Más Leídos del Mismo Autor</h3>
                                <p class="text-[11px] text-gray-400">Coautores ordenados por vistas</p>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-gray-400">{{ mas_leidos_autor.length }} artículos</span>
                    </div>

                    <div v-if="mas_leidos_autor.length" class="space-y-3 flex-1">
                        <div
                            v-for="mla in mas_leidos_autor"
                            :key="mla.id"
                            class="p-3 rounded-xl border border-gray-100 hover:border-amber-100 hover:bg-amber-50/20 transition flex flex-col gap-1.5"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <Link
                                    :href="route('admin.articulos.show', mla.id)"
                                    class="text-xs font-bold text-gray-900 hover:text-amber-700 transition line-clamp-2"
                                >
                                    {{ mla.art_titulo }}
                                </Link>
                                <span class="text-[11px] font-bold text-amber-700 whitespace-nowrap bg-amber-50 px-2 py-0.5 rounded-md">
                                    👁 {{ mla.art_vistas || 0 }}
                                </span>
                            </div>
                            <div v-if="mla.autores && mla.autores.length" class="text-[10px] text-gray-400 line-clamp-1">
                                Autores: {{ mla.autores.map(a => a.persona ? a.persona.nombre_completo : 'Autor #' + a.id).join(', ') }}
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-xs text-gray-400 italic py-6 text-center">
                        Los autores no tienen otros artículos registrados aún.
                    </div>
                </div>
            </div>

            <!-- Historial de Revisiones & Auditoría Editorial -->
            <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xs border border-gray-100">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-6">
                    <div class="flex items-center gap-2">
                        <span class="p-1.5 bg-indigo-50 text-[#35449F] rounded-lg">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                        <div>
                            <h3 class="text-base font-extrabold text-[#14161C]">Historial de Revisiones & Auditoría</h3>
                            <p class="text-[11px] text-gray-400">Trazabilidad de dictámenes editoriales y motivos de rechazo/aprobación</p>
                        </div>
                    </div>
                    <span class="text-xs font-bold text-gray-400">
                        {{ articulo.historial_revisiones?.length || 0 }} eventos
                    </span>
                </div>

                <div v-if="articulo.historial_revisiones && articulo.historial_revisiones.length > 0" class="relative pl-6 space-y-6 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-gray-200">
                    <div
                        v-for="(hist, hIdx) in articulo.historial_revisiones"
                        :key="hist.id || hIdx"
                        class="relative flex flex-col gap-1 text-xs"
                    >
                        <!-- Timeline dot -->
                        <span
                            class="absolute -left-6 top-1 size-4 rounded-full border-2 border-white flex items-center justify-center ring-2"
                            :class="{
                                'bg-emerald-500 ring-emerald-200': hist.nuevo_estado === 'aprobado',
                                'bg-rose-500 ring-rose-200': hist.nuevo_estado === 'rechazado',
                                'bg-amber-500 ring-amber-200': hist.nuevo_estado === 'pendiente',
                                'bg-gray-400 ring-gray-200': !['aprobado', 'rechazado', 'pendiente'].includes(hist.nuevo_estado)
                            }"
                        ></span>

                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-bold text-gray-900 capitalize">{{ hist.accion }}</span>
                            <span
                                v-if="hist.nuevo_estado"
                                class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                :class="{
                                    'bg-emerald-50 text-emerald-700': hist.nuevo_estado === 'aprobado',
                                    'bg-rose-50 text-rose-700': hist.nuevo_estado === 'rechazado',
                                    'bg-amber-50 text-amber-700': hist.nuevo_estado === 'pendiente',
                                    'bg-gray-100 text-gray-700': hist.nuevo_estado === 'borrador'
                                }"
                            >
                                {{ hist.estado_anterior ? `${hist.estado_anterior} → ` : '' }}{{ hist.nuevo_estado }}
                            </span>
                            <span class="text-gray-400 text-[11px]">• {{ formatDate(hist.created_at) }}</span>
                            <span v-if="hist.user" class="text-gray-500 font-medium text-[11px]">
                                por {{ hist.user.name }} ({{ hist.user.email }})
                            </span>
                        </div>

                        <div v-if="hist.motivo" class="mt-1 p-3 rounded-xl bg-gray-50 border border-gray-100 text-gray-700 text-xs leading-relaxed whitespace-pre-line">
                            <strong class="text-gray-800">Observaciones:</strong> {{ hist.motivo }}
                        </div>
                    </div>
                </div>
                <div v-else class="text-xs text-gray-400 italic py-4 text-center">
                    No hay registros de historial de revisión para este artículo.
                </div>
            </div>
        </div>

        <ConfirmationModal :show="confirmingDeletion" @close="confirmingDeletion = false">
            <template #title>Eliminar Artículo</template>
            <template #content>
                ¿Estás seguro de que deseas eliminar permanentemente este artículo científico?
            </template>
            <template #footer>
                <SecondaryButton @click="confirmingDeletion = false" class="mr-2 cursor-pointer">Cancelar</SecondaryButton>
                <DangerButton @click="deleteItem" class="cursor-pointer">Eliminar</DangerButton>
            </template>
        </ConfirmationModal>

        <!-- Modal de Aprobación de Artículo -->
        <ConfirmationModal :show="confirmingApproval" @close="confirmingApproval = false">
            <template #title>Aprobar y Publicar Artículo</template>
            <template #content>
                ¿Confirmas la aprobación del artículo
                <span class="font-bold text-[#14161C]">"{{ articulo.art_titulo }}"</span>?
                El artículo cambiará su estado a <strong class="text-emerald-700">Aprobado</strong> y quedará visible públicamente en la Revista Varianza.
            </template>
            <template #footer>
                <SecondaryButton @click="confirmingApproval = false" class="mr-2 cursor-pointer">
                    Cancelar
                </SecondaryButton>
                <PrimaryButton :disabled="processingReview" @click="approveArticle" class="cursor-pointer bg-emerald-600 hover:bg-emerald-700">
                    Aprobar y Publicar
                </PrimaryButton>
            </template>
        </ConfirmationModal>

        <!-- Modal de Rechazo con Observaciones -->
        <DialogModal :show="confirmingRejection" @close="confirmingRejection = false">
            <template #title>Rechazar Artículo con Observaciones</template>
            <template #content>
                <p class="text-xs text-gray-600 mb-3">
                    Indica las observaciones editoriales para el autor de
                    <span class="font-bold text-[#14161C]">"{{ articulo.art_titulo }}"</span>. El autor podrá ver estos comentarios y enviar una versión corregida.
                </p>
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">
                        Motivo / Observaciones Editorial <span class="text-rose-500">*</span>
                    </label>
                    <textarea
                        v-model="rejectMotivo"
                        rows="4"
                        placeholder="Describe los motivos del rechazo o correcciones solicitadas al autor..."
                        class="w-full text-sm rounded-xl border-gray-200 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/10 p-3 bg-gray-50/50 focus:bg-white"
                    ></textarea>
                    <InputError :message="rejectError" class="mt-1" />
                </div>
            </template>
            <template #footer>
                <SecondaryButton @click="confirmingRejection = false" class="mr-2 cursor-pointer">
                    Cancelar
                </SecondaryButton>
                <DangerButton :disabled="processingReview" @click="rejectArticle" class="cursor-pointer">
                    Rechazar con Observaciones
                </DangerButton>
            </template>
        </DialogModal>
    </AppLayout>
</template>
