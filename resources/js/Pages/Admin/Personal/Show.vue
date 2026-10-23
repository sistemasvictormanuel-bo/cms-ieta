<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';

const props = defineProps({
    miembro: {
        type: Object,
        required: true,
    },
});

const confirmingDeletion = ref(false);

const deleteItem = () => {
    router.delete(route('admin.personal.destroy', props.miembro.id));
};

const personaName = (persona) => {
    if (!persona) return 'Sin persona asignada';
    if (persona.nombre_completo) return persona.nombre_completo;
    return `${persona.p_nombre} ${persona.p_apellido_paterno || ''} ${persona.p_apellido_materno || ''}`.trim();
};

const formatDate = (dateString) => {
    if (!dateString) return 'No registrado';
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' });
    } catch {
        return dateString;
    }
};

const fotoError = ref(false);

const getInitials = (persona) => {
    if (!persona) return 'P';
    const first = persona.p_nombre ? persona.p_nombre.trim().charAt(0).toUpperCase() : '';
    const last = persona.p_apellido_paterno ? persona.p_apellido_paterno.trim().charAt(0).toUpperCase() : '';
    return (first + last) || first || 'P';
};
</script>

<template>
    <AppLayout title="Detalle de Personal">
        <template #header>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 w-full">
                <div>
                    <h1 class="font-extrabold text-2xl text-[#14161C] tracking-tight">
                        {{ personaName(miembro.persona) }}
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500 font-medium">
                        Ficha del personal, cargo institucional y detalles laborales.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Link :href="route('admin.personal.index')">
                        <SecondaryButton class="cursor-pointer">
                            ← Volver al listado
                        </SecondaryButton>
                    </Link>
                    <Link :href="route('admin.personal.edit', miembro.id)">
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
                        title="Dar de baja / Eliminar"
                    >
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </template>

        <div class="space-y-6 pb-12 max-w-4xl mx-auto">
            <!-- Ficha Principal -->
            <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xs border border-gray-100">
                <div class="flex items-center justify-between pb-6 border-b border-gray-100 flex-wrap gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 md:w-20 md:h-20 rounded-2xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-[#35449F] font-extrabold text-2xl overflow-hidden shadow-xs shrink-0">
                            <img
                                v-if="miembro.persona?.foto_url && !fotoError"
                                :src="miembro.persona.foto_url"
                                :alt="personaName(miembro.persona)"
                                @error="fotoError = true"
                                class="w-full h-full object-cover"
                            />
                            <span v-else>
                                {{ getInitials(miembro.persona) }}
                            </span>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-xl font-extrabold text-[#14161C]">
                                    {{ personaName(miembro.persona) }}
                                </h2>
                                <span
                                    :class="miembro.pers_estado ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-gray-100 text-gray-600 ring-gray-400/20'"
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold ring-1"
                                >
                                    {{ miembro.pers_estado ? 'Activo' : 'Inactivo / Retirado' }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-0.5 font-semibold text-[#35449F]">
                                {{ miembro.cargo?.ci_nombre || 'Sin cargo asignado' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Datos de la Persona y Contacto -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 py-6 border-b border-gray-100 text-sm">
                    <div>
                        <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Correo Electrónico</div>
                        <div class="font-semibold text-gray-800 mt-1">
                            {{ miembro.persona?.p_correo || 'No registrado' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Teléfono / Celular</div>
                        <div class="font-semibold text-gray-800 mt-1">
                            {{ miembro.persona?.p_celular || 'No registrado' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Documento de Identidad / CI</div>
                        <div class="font-semibold text-gray-800 mt-1">
                            {{ miembro.persona?.p_ci || 'No registrado' }}
                        </div>
                    </div>
                </div>

                <!-- Período Laboral / Fechas -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 py-6 border-b border-gray-100 text-sm">
                    <div>
                        <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Fecha de Ingreso</div>
                        <div class="font-bold text-emerald-700 mt-1">
                            {{ formatDate(miembro.pers_fecha_ingreso) }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">Fecha de Retiro / Conclusión</div>
                        <div class="font-bold text-gray-700 mt-1">
                            {{ formatDate(miembro.pers_fecha_retiro) }}
                        </div>
                    </div>
                </div>

                <!-- Descripción / Observaciones -->
                <div v-if="miembro.pers_descripcion" class="pt-6">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">
                        Descripción de Funciones / Observaciones
                    </h3>
                    <div class="text-sm text-gray-700 leading-relaxed bg-gray-50/60 p-4 rounded-xl border border-gray-100">
                        {{ miembro.pers_descripcion }}
                    </div>
                </div>
            </div>
        </div>

        <ConfirmationModal :show="confirmingDeletion" @close="confirmingDeletion = false">
            <template #title>Dar de Baja a Personal</template>
            <template #content>
                ¿Estás seguro de que deseas eliminar permanentemente a este miembro del personal?
            </template>
            <template #footer>
                <SecondaryButton @click="confirmingDeletion = false" class="mr-2 cursor-pointer">Cancelar</SecondaryButton>
                <DangerButton @click="deleteItem" class="cursor-pointer">Eliminar</DangerButton>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>
