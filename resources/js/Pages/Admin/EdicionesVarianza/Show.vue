<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import EditorRenderer from '@/Components/EditorRenderer.vue';

const props = defineProps({
    edicion: {
        type: Object,
        required: true,
    },
});

const activeTab = ref('articulos'); // 'articulos' | 'presentacion' | 'ficha'
const selectedTipoFilter = ref('todos');

const filteredArticulos = computed(() => {
    const articulos = props.edicion.articulos || [];
    if (selectedTipoFilter.value === 'todos') {
        return articulos;
    }
    return articulos.filter(art => {
        const tipoNombre = art.tipo_articulo?.tar_nombre?.toLowerCase() || '';
        if (selectedTipoFilter.value === 'original') {
            return tipoNombre.includes('original');
        }
        if (selectedTipoFilter.value === 'nota') {
            return tipoNombre.includes('nota');
        }
        return true;
    });
});

const articulosOriginalesCount = computed(() => {
    return (props.edicion.articulos || []).filter(a =>
        a.tipo_articulo?.tar_nombre?.toLowerCase().includes('original')
    ).length;
});

const notasCientificasCount = computed(() => {
    return (props.edicion.articulos || []).filter(a =>
        a.tipo_articulo?.tar_nombre?.toLowerCase().includes('nota')
    ).length;
});
</script>

<template>
    <AppLayout :title="`Edición Nº ${edicion.ev_numero} - Revista Varianza`">
        <template #header>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 w-full">
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('admin.ediciones-varianza.index')"
                        class="size-10 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-slate-600 hover:text-slate-900 hover:border-slate-300 transition shadow-2xs"
                        title="Volver a ediciones"
                    >
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <div>
                        <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-0.5">
                            <Link :href="route('admin.ediciones-varianza.index')" class="hover:text-[#35449F]">Ediciones Varianza</Link>
                            <span>/</span>
                            <span class="text-slate-800 font-bold">Nº {{ edicion.ev_numero }} ({{ edicion.ev_anio }})</span>
                        </div>
                        <h1 class="font-extrabold text-2xl text-[#14161C] tracking-tight">
                            {{ edicion.ev_titulo }}
                        </h1>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    <a
                        v-if="edicion.archivo_url"
                        :href="edicion.archivo_url"
                        target="_blank"
                        class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-xl transition shadow-xs"
                    >
                        <svg class="size-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Descargar PDF
                    </a>
                    <Link :href="route('admin.articulos.create')">
                        <SecondaryButton class="cursor-pointer">
                            + Añadir Artículo
                        </SecondaryButton>
                    </Link>
                    <Link :href="route('admin.ediciones-varianza.edit', edicion.id)">
                        <PrimaryButton class="cursor-pointer">
                            <svg class="size-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                            Editar Edición
                        </PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto space-y-8 pb-16">
            <!-- Hero Card: Resumen de la Edición -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/80 shadow-xs relative overflow-hidden">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-start">
                    <!-- Portada de la Edición -->
                    <div class="md:col-span-4 lg:col-span-3">
                        <div class="aspect-3/4 rounded-2xl bg-slate-100 overflow-hidden border border-slate-200/80 shadow-md relative group">
                            <img
                                v-if="edicion.portada_url"
                                :src="edicion.portada_url"
                                :alt="edicion.ev_titulo"
                                class="w-full h-full object-cover"
                            />
                            <div v-else class="w-full h-full flex flex-col items-center justify-center p-6 text-center text-slate-400">
                                <svg class="size-16 mb-2 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                <span class="text-xs font-semibold">Sin imagen de portada</span>
                            </div>

                            <span
                                class="absolute top-3 right-3 px-2.5 py-1 rounded-full text-[11px] font-bold shadow-xs"
                                :class="edicion.ev_estado ? 'bg-emerald-500 text-white' : 'bg-amber-500 text-white'"
                            >
                                {{ edicion.ev_estado ? 'Publicado' : 'Borrador' }}
                            </span>
                        </div>
                    </div>

                    <!-- Datos y Metadatos del Número -->
                    <div class="md:col-span-8 lg:col-span-9 space-y-5">
                        <div class="space-y-2">
                            <div class="flex items-center gap-2 flex-wrap text-xs">
                                <span class="px-2.5 py-1 rounded-lg bg-blue-50 text-[#35449F] font-black uppercase tracking-wider">
                                    Número {{ edicion.ev_numero }}
                                </span>
                                <span v-if="edicion.ev_volumen" class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-bold">
                                    {{ edicion.ev_volumen }}
                                </span>
                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-bold">
                                    Año {{ edicion.ev_anio }} {{ edicion.ev_mes ? `(${edicion.ev_mes})` : '' }}
                                </span>
                                <span v-if="edicion.ev_fecha_publicacion" class="text-slate-400 font-medium">
                                    Publicado el: {{ edicion.ev_fecha_publicacion }}
                                </span>
                            </div>

                            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight leading-tight">
                                {{ edicion.ev_titulo }}
                            </h2>

                            <p class="text-xs sm:text-sm text-slate-500 font-medium">
                                {{ edicion.ev_editorial || 'Instituto de Estadística Teórica y Aplicada (IETA) - UMSA' }}
                            </p>
                        </div>

                        <!-- Fila de Identificadores DOI / ISSN -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 p-4 bg-slate-50 rounded-2xl border border-slate-100 text-xs">
                            <div>
                                <span class="text-[10px] uppercase font-extrabold text-slate-400 block tracking-wider">ISSN Impreso</span>
                                <span class="font-mono font-bold text-slate-800">{{ edicion.ev_issn_ver_impresa || 'No asignado' }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] uppercase font-extrabold text-slate-400 block tracking-wider">ISSN Electrónico</span>
                                <span class="font-mono font-bold text-slate-800">{{ edicion.ev_issn_ver_electronica || 'No asignado' }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] uppercase font-extrabold text-slate-400 block tracking-wider">DOI</span>
                                <span class="font-mono font-bold text-indigo-700 truncate block">{{ edicion.ev_doi || 'No asignado' }}</span>
                            </div>
                        </div>

                        <!-- Métricas Claves -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2">
                            <div class="p-3 bg-white rounded-xl border border-slate-200/80 text-center">
                                <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Artículos Totales</span>
                                <span class="text-xl font-black text-slate-900">{{ edicion.articulos?.length || 0 }}</span>
                            </div>
                            <div class="p-3 bg-white rounded-xl border border-slate-200/80 text-center">
                                <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Art. Originales</span>
                                <span class="text-xl font-black text-[#35449F]">{{ articulosOriginalesCount }}</span>
                            </div>
                            <div class="p-3 bg-white rounded-xl border border-slate-200/80 text-center">
                                <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Notas Científicas</span>
                                <span class="text-xl font-black text-amber-600">{{ notasCientificasCount }}</span>
                            </div>
                            <div class="p-3 bg-white rounded-xl border border-slate-200/80 text-center">
                                <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Páginas Declaradas</span>
                                <span class="text-xl font-black text-slate-700">{{ edicion.ev_num_paginas || '—' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navegación por Pestañas -->
            <div class="border-b border-slate-200 flex items-center gap-6">
                <button
                    @click="activeTab = 'articulos'"
                    class="pb-3 text-sm font-extrabold transition cursor-pointer border-b-2"
                    :class="activeTab === 'articulos' ? 'border-[#35449F] text-[#35449F]' : 'border-transparent text-slate-500 hover:text-slate-800'"
                >
                    Artículos Publicados ({{ edicion.articulos?.length || 0 }})
                </button>
                <button
                    @click="activeTab = 'presentacion'"
                    class="pb-3 text-sm font-extrabold transition cursor-pointer border-b-2"
                    :class="activeTab === 'presentacion' ? 'border-[#35449F] text-[#35449F]' : 'border-transparent text-slate-500 hover:text-slate-800'"
                >
                    Presentación Editorial
                </button>
                <button
                    @click="activeTab = 'ficha'"
                    class="pb-3 text-sm font-extrabold transition cursor-pointer border-b-2"
                    :class="activeTab === 'ficha' ? 'border-[#35449F] text-[#35449F]' : 'border-transparent text-slate-500 hover:text-slate-800'"
                >
                    Ficha Técnica & Citas
                </button>
            </div>

            <!-- Contenido de Pestañas -->
            <!-- Pestaña 1: Artículos de la Edición -->
            <div v-if="activeTab === 'articulos'" class="space-y-6">
                <!-- Filtros rápidos por tipo -->
                <div class="flex items-center justify-between gap-4 flex-wrap">
                    <div class="flex items-center gap-2">
                        <button
                            @click="selectedTipoFilter = 'todos'"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer"
                            :class="selectedTipoFilter === 'todos' ? 'bg-[#35449F] text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'"
                        >
                            Todos ({{ edicion.articulos?.length || 0 }})
                        </button>
                        <button
                            @click="selectedTipoFilter = 'original'"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer"
                            :class="selectedTipoFilter === 'original' ? 'bg-[#35449F] text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'"
                        >
                            Artículos Originales ({{ articulosOriginalesCount }})
                        </button>
                        <button
                            @click="selectedTipoFilter = 'nota'"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer"
                            :class="selectedTipoFilter === 'nota' ? 'bg-[#35449F] text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'"
                        >
                            Notas Científicas ({{ notasCientificasCount }})
                        </button>
                    </div>

                    <Link :href="route('admin.articulos.create')">
                        <PrimaryButton class="text-xs">
                            + Subir Artículo a esta Edición
                        </PrimaryButton>
                    </Link>
                </div>

                <!-- Tabla de Artículos -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-sm">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-black uppercase tracking-wider text-slate-500">
                                    <th class="py-3.5 px-4 w-12 text-center">#</th>
                                    <th class="py-3.5 px-4">Artículo Científico</th>
                                    <th class="py-3.5 px-4">Tipo</th>
                                    <th class="py-3.5 px-4">Autores</th>
                                    <th class="py-3.5 px-4 text-center">Páginas</th>
                                    <th class="py-3.5 px-4 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr
                                    v-for="(art, idx) in filteredArticulos"
                                    :key="art.id"
                                    class="hover:bg-slate-50/60 transition group"
                                >
                                    <td class="py-4 px-4 text-center font-bold text-slate-400">
                                        {{ idx + 1 }}
                                    </td>
                                    <td class="py-4 px-4 max-w-md">
                                        <Link
                                            :href="route('admin.articulos.show', art.id)"
                                            class="font-extrabold text-slate-900 hover:text-[#35449F] transition block line-clamp-2"
                                        >
                                            {{ art.art_titulo }}
                                        </Link>
                                        <div class="flex items-center gap-2 mt-1 text-[11px] text-slate-500">
                                            <span v-if="art.art_doi" class="font-mono text-indigo-600">
                                                DOI: {{ art.art_doi }}
                                            </span>
                                            <span v-if="art.etiquetas && art.etiquetas.length" class="text-slate-400">
                                                • {{ art.etiquetas.map(e => e.ea_nombre).join(', ') }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold"
                                            :class="art.tipo_articulo?.tar_nombre?.toLowerCase().includes('original') ? 'bg-blue-50 text-[#35449F] border border-blue-100' : 'bg-purple-50 text-purple-700 border border-purple-100'"
                                        >
                                            {{ art.tipo_articulo?.tar_nombre || 'Sin tipo' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4 text-xs text-slate-600">
                                        <div v-if="art.autores && art.autores.length" class="space-y-0.5">
                                            <div
                                                v-for="autor in art.autores"
                                                :key="autor.id"
                                                class="font-medium text-slate-800"
                                            >
                                                {{ autor.persona?.p_nombre }} {{ autor.persona?.p_apellido_paterno }}
                                            </div>
                                        </div>
                                        <span v-else class="text-slate-400 italic">Sin autores</span>
                                    </td>
                                    <td class="py-4 px-4 text-center font-mono text-xs font-semibold text-slate-700">
                                        <span v-if="art.art_pag_inicio && art.art_pag_fin">
                                            pp. {{ art.art_pag_inicio }}-{{ art.art_pag_fin }}
                                        </span>
                                        <span v-else-if="art.art_num_paginas">
                                            {{ art.art_num_paginas }} págs.
                                        </span>
                                        <span v-else class="text-slate-300">—</span>
                                    </td>
                                    <td class="py-4 px-4 text-right space-x-1">
                                        <Link
                                            :href="route('admin.articulos.show', art.id)"
                                            class="p-1.5 text-slate-500 hover:text-blue-600 hover:bg-slate-100 rounded-lg inline-flex items-center transition"
                                            title="Ver artículo"
                                        >
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </Link>
                                        <Link
                                            :href="route('admin.articulos.edit', art.id)"
                                            class="p-1.5 text-slate-500 hover:text-[#35449F] hover:bg-slate-100 rounded-lg inline-flex items-center transition"
                                            title="Editar artículo"
                                        >
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </Link>
                                    </td>
                                </tr>

                                <tr v-if="filteredArticulos.length === 0">
                                    <td colspan="6" class="text-center py-12 text-slate-400">
                                        <svg class="size-10 mx-auto text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <p class="font-bold text-slate-700">No hay artículos con este filtro en la edición</p>
                                        <p class="text-xs text-slate-400 mt-1">Registra artículos seleccionando esta edición en el formulario.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Pestaña 2: Presentación Editorial -->
            <div v-else-if="activeTab === 'presentacion'" class="bg-white rounded-2xl p-8 border border-slate-200/80 shadow-xs space-y-6">
                <div class="border-b border-slate-100 pb-4">
                    <h3 class="text-xl font-extrabold text-slate-900">Presentación de la Edición Nº {{ edicion.ev_numero }}</h3>
                    <p class="text-xs text-slate-500">Carta editorial del comité y directrices científicas de este número.</p>
                </div>

                <div v-if="edicion.ev_presentacion && edicion.ev_presentacion.blocks && edicion.ev_presentacion.blocks.length" class="prose max-w-none">
                    <EditorRenderer :content="edicion.ev_presentacion" />
                </div>
                <div v-else class="text-center py-12 text-slate-400">
                    <p class="italic text-sm">No se ha redactado una presentación editorial para esta edición.</p>
                    <Link :href="route('admin.ediciones-varianza.edit', edicion.id)" class="text-xs text-[#35449F] hover:underline font-bold mt-2 inline-block">
                        Redactar presentación ahora ↗
                    </Link>
                </div>
            </div>

            <!-- Pestaña 3: Ficha Técnica & Citas -->
            <div v-else-if="activeTab === 'ficha'" class="bg-white rounded-2xl p-8 border border-slate-200/80 shadow-xs space-y-6">
                <div class="border-b border-slate-100 pb-4">
                    <h3 class="text-xl font-extrabold text-slate-900">Ficha Bibliográfica & Datos Técnicos</h3>
                    <p class="text-xs text-slate-500">Información para indexadores, bibliotecas y repositorios OAI-PMH.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                    <div class="space-y-4">
                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block">Título Oficial</span>
                            <span class="font-bold text-slate-900 text-base">{{ edicion.ev_titulo }}</span>
                        </div>
                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block">Editorial</span>
                            <span class="font-bold text-slate-900">{{ edicion.ev_editorial || 'IETA / UMSA' }}</span>
                        </div>
                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block">Identificador Digital DOI</span>
                            <span class="font-mono font-bold text-indigo-700">{{ edicion.ev_doi || 'No registrado' }}</span>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block">ISSN Impreso / Electrónico</span>
                            <span class="font-mono font-bold text-slate-900">
                                Impreso: {{ edicion.ev_issn_ver_impresa || '—' }} | Electrónico: {{ edicion.ev_issn_ver_electronica || '—' }}
                            </span>
                        </div>
                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block">Fecha de Publicación</span>
                            <span class="font-bold text-slate-900">{{ edicion.ev_fecha_publicacion || 'No definida' }}</span>
                        </div>
                        <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 block">Formato Digital</span>
                            <span class="font-bold text-slate-900">
                                {{ edicion.archivo_url ? 'Archivo PDF Completo disponible' : 'Sin archivo adjunto' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
