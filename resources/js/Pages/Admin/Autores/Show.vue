<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import EditorRenderer from '@/Components/EditorRenderer.vue';

const props = defineProps({
    autor: {
        type: Object,
        required: true,
    },
});

const confirmingDeletion = ref(false);

const deleteItem = () => {
    router.delete(route('admin.autores.destroy', props.autor.id));
};

const personaName = (persona) => {
    if (!persona) return 'Sin persona asignada';
    if (persona.nombre_completo) return persona.nombre_completo;
    return `${persona.p_nombre} ${persona.p_apellido_paterno || ''} ${persona.p_apellido_materno || ''}`.trim();
};

const fotoError = ref(false);

const getInitials = (persona) => {
    if (!persona) return 'A';
    const first = persona.p_nombre ? persona.p_nombre.trim().charAt(0).toUpperCase() : '';
    const last = persona.p_apellido_paterno ? persona.p_apellido_paterno.trim().charAt(0).toUpperCase() : '';
    return (first + last) || first || 'A';
};
</script>

<template>
    <AppLayout title="Detalle de Autor">
        <template #header>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 w-full">
                <div>
                    <h1 class="font-extrabold text-2xl text-[#14161C] tracking-tight">
                        {{ personaName(autor.persona) }}
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500 font-medium">
                        Perfil académico, biografía institucional y artículos científicos asociados.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route('admin.autores.index')">
                        <SecondaryButton class="cursor-pointer">
                            ← Volver al listado
                        </SecondaryButton>
                    </Link>
                    <Link :href="route('admin.autores.edit', autor.id)">
                        <PrimaryButton class="cursor-pointer">
                            <svg class="size-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
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

        <div class="space-y-6 pb-12 max-w-6xl mx-auto">
            <!-- Tarjeta de Perfil Principal -->
            <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xs border border-gray-100">
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6 pb-6 border-b border-gray-100">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 md:w-20 md:h-20 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-[#35449F] font-extrabold text-2xl overflow-hidden shadow-xs shrink-0">
                            <img
                                v-if="autor.persona?.foto_url && !fotoError"
                                :src="autor.persona.foto_url"
                                :alt="personaName(autor.persona)"
                                @error="fotoError = true"
                                class="w-full h-full object-cover"
                            />
                            <span v-else>
                                {{ getInitials(autor.persona) }}
                            </span>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h2 class="text-xl font-extrabold text-[#14161C]">
                                    {{ personaName(autor.persona) }}
                                </h2>
                                <span
                                    :class="autor.at_estado ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-gray-100 text-gray-600 ring-gray-400/20'"
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold ring-1"
                                >
                                    {{ autor.at_estado ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-400 font-mono mt-0.5">
                                slug: @{{ autor.at_slug }}
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold bg-[#35449F]/10 text-[#35449F]">
                            {{ autor.tipo_autor?.ta_nombre || 'Tipo General' }}
                        </span>
                        <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold bg-amber-50 text-amber-800">
                            {{ autor.grado_academico?.ga_nombre || 'Grado no asignado' }}
                        </span>
                        <template v-if="autor.especialidades && autor.especialidades.length > 0">
                            <span
                                v-for="esp in autor.especialidades"
                                :key="esp.id"
                                class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold bg-purple-50 text-purple-800"
                            >
                                {{ esp.ep_nombre }}
                            </span>
                        </template>
                        <span v-else class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold bg-purple-50 text-purple-800">
                            {{ autor.especialidad?.ep_nombre || 'Sin especialidad' }}
                        </span>
                    </div>
                </div>

                <!-- Datos de contacto & Credenciales -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 py-6 border-b border-gray-100 text-sm">
                    <div>
                        <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Correo Electrónico</div>
                        <div class="font-semibold text-gray-800 mt-1 truncate">
                            {{ autor.persona?.p_correo || 'No registrado' }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Teléfono / Celular</div>
                        <div class="font-semibold text-gray-800 mt-1">
                            {{ autor.persona?.p_celular || 'No registrado' }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Documento / CI</div>
                        <div class="font-semibold text-gray-800 mt-1">
                            {{ autor.persona?.p_ci || 'No registrado' }}
                        </div>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Sitio Web / Perfil</div>
                        <div class="font-semibold text-gray-800 mt-1 truncate">
                            <a
                                v-if="autor.persona?.p_sitio_web"
                                :href="autor.persona.p_sitio_web"
                                target="_blank"
                                class="text-[#35449F] hover:underline"
                            >
                                {{ autor.persona.p_sitio_web }}
                            </a>
                            <span v-else class="text-gray-400">No registrado</span>
                        </div>
                    </div>
                </div>

                <!-- Intereses Científicos -->
                <div v-if="autor.at_intereses_cientificos" class="pt-6">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">
                        Líneas e Intereses de Investigación
                    </h3>
                    <p class="text-sm font-medium text-gray-700 bg-gray-50/60 p-4 rounded-xl border border-gray-100 leading-relaxed">
                        {{ autor.at_intereses_cientificos }}
                    </p>
                </div>
            </div>

            <!-- Biografía (EditorRenderer) -->
            <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xs border border-gray-100">
                <h3 class="text-lg font-extrabold text-[#14161C] mb-4 pb-2 border-b border-gray-100 flex items-center gap-2">
                    <svg class="size-5 text-[#35449F]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    Biografía y Trayectoria Académica
                </h3>
                <div v-if="autor.at_biografia?.blocks?.length" class="prose max-w-none">
                    <EditorRenderer :content="autor.at_biografia" />
                </div>
                <div v-else class="text-sm text-gray-400 italic py-6 text-center">
                    No se ha redactado una biografía para este autor aún.
                </div>
            </div>

            <!-- Artículos Asociados -->
            <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xs border border-gray-100">
                <h3 class="text-lg font-extrabold text-[#14161C] mb-4 pb-2 border-b border-gray-100 flex items-center justify-between">
                    <span class="flex items-center gap-2">
                        <svg class="size-5 text-[#35449F]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Artículos y Publicaciones Científicas
                    </span>
                    <span class="text-xs font-bold text-gray-400">
                        {{ autor.articulos?.length || 0 }} artículos vinculados
                    </span>
                </h3>

                <div v-if="autor.articulos?.length" class="space-y-3">
                    <div
                        v-for="art in autor.articulos"
                        :key="art.id"
                        class="p-4 rounded-xl border border-gray-100 hover:border-gray-200 hover:bg-gray-50/50 transition flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                    >
                        <div class="space-y-1">
                            <Link
                                :href="route('admin.articulos.show', art.id)"
                                class="font-bold text-sm text-[#14161C] hover:text-[#35449F] transition"
                            >
                                {{ art.art_titulo }}
                            </Link>
                            <div class="flex items-center gap-3 text-xs text-gray-400">
                                <span v-if="art.art_fecha_recepcion">
                                    Recibido: {{ art.art_fecha_recepcion }}
                                </span>
                                <span v-if="art.art_doi" class="font-mono">
                                    DOI: {{ art.art_doi }}
                                </span>
                            </div>
                        </div>

                        <div class="shrink-0 flex items-center gap-2">
                            <Link
                                :href="route('admin.articulos.show', art.id)"
                                class="text-xs font-bold text-[#35449F] hover:underline"
                            >
                                Ver Artículo →
                            </Link>
                        </div>
                    </div>
                </div>
                <div v-else class="text-sm text-gray-400 italic py-6 text-center">
                    Este autor no tiene artículos científicos asociados todavía.
                </div>
            </div>
        </div>

        <ConfirmationModal :show="confirmingDeletion" @close="confirmingDeletion = false">
            <template #title>Eliminar Autor</template>
            <template #content>
                ¿Estás seguro de que deseas eliminar permanentemente a este autor?
            </template>
            <template #footer>
                <SecondaryButton @click="confirmingDeletion = false" class="mr-2 cursor-pointer">Cancelar</SecondaryButton>
                <DangerButton @click="deleteItem" class="cursor-pointer">Eliminar</DangerButton>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>
