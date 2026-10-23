<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';

const props = defineProps({
    cargo: {
        type: Object,
        required: true,
    },
});

const confirmingDeletion = ref(false);

const deleteItem = () => {
    router.delete(route('admin.cargos-institucionales.destroy', props.cargo.id));
};

const personaName = (persona) => {
    if (!persona) return 'Sin persona';
    if (persona.nombre_completo) return persona.nombre_completo;
    return `${persona.p_nombre} ${persona.p_apellido_paterno || ''}`.trim();
};
</script>

<template>
    <AppLayout title="Detalle de Cargo Institucional">
        <template #header>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 w-full">
                <div>
                    <h1 class="font-extrabold text-2xl text-[#14161C] tracking-tight">
                        {{ cargo.ci_nombre }}
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500 font-medium">
                        Detalle del cargo institucional y miembros que lo desempeñan.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Link :href="route('admin.cargos-institucionales.index')">
                        <SecondaryButton class="cursor-pointer">
                            ← Volver al listado
                        </SecondaryButton>
                    </Link>
                    <Link :href="route('admin.cargos-institucionales.edit', cargo.id)">
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

        <div class="space-y-6 pb-12 max-w-4xl mx-auto">
            <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xs border border-gray-100">
                <h2 class="text-xl font-extrabold text-[#14161C] mb-4">
                    {{ cargo.ci_nombre }}
                </h2>
                <div class="text-sm text-gray-600 bg-gray-50/60 p-4 rounded-xl border border-gray-100 leading-relaxed">
                    {{ cargo.ci_descripcion || 'Sin descripción registrada para este cargo.' }}
                </div>
            </div>

            <!-- Personal con este Cargo -->
            <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xs border border-gray-100">
                <h3 class="text-lg font-extrabold text-[#14161C] mb-4 pb-2 border-b border-gray-100 flex items-center justify-between">
                    <span>Personal con este Cargo</span>
                    <span class="text-xs font-bold text-gray-400">
                        {{ cargo.personal?.length || 0 }} registros
                    </span>
                </h3>

                <div v-if="cargo.personal?.length" class="space-y-3">
                    <div
                        v-for="p in cargo.personal"
                        :key="p.id"
                        class="p-4 rounded-xl border border-gray-100 hover:border-gray-200 transition bg-gray-50/40 flex items-center justify-between gap-4"
                    >
                        <div>
                            <Link :href="route('admin.personal.show', p.id)" class="font-bold text-sm text-[#14161C] hover:text-[#35449F]">
                                {{ personaName(p.persona) }}
                            </Link>
                            <div class="text-xs text-gray-400 mt-0.5">
                                {{ p.pers_descripcion || 'Sin descripción' }}
                            </div>
                        </div>
                        <Link :href="route('admin.personal.show', p.id)" class="text-xs font-bold text-[#35449F] hover:underline">
                            Ver Perfil →
                        </Link>
                    </div>
                </div>
                <div v-else class="text-sm text-gray-400 italic py-4 text-center">
                    No hay miembros del personal asignados a este cargo.
                </div>
            </div>

            <!-- Comité Editorial con este Cargo -->
            <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xs border border-gray-100">
                <h3 class="text-lg font-extrabold text-[#14161C] mb-4 pb-2 border-b border-gray-100 flex items-center justify-between">
                    <span>Miembros del Comité Editorial con este Cargo</span>
                    <span class="text-xs font-bold text-gray-400">
                        {{ cargo.comite_editorial?.length || 0 }} registros
                    </span>
                </h3>

                <div v-if="cargo.comite_editorial?.length" class="space-y-3">
                    <div
                        v-for="ce in cargo.comite_editorial"
                        :key="ce.id"
                        class="p-4 rounded-xl border border-gray-100 hover:border-gray-200 transition bg-gray-50/40 flex items-center justify-between gap-4"
                    >
                        <div>
                            <Link :href="route('admin.comite-editorial.show', ce.id)" class="font-bold text-sm text-[#14161C] hover:text-[#35449F]">
                                {{ personaName(ce.persona) }}
                            </Link>
                            <div class="text-xs text-gray-400 mt-0.5">
                                Tipo: {{ ce.ce_tipo }}
                            </div>
                        </div>
                        <Link :href="route('admin.comite-editorial.show', ce.id)" class="text-xs font-bold text-[#35449F] hover:underline">
                            Ver Miembro →
                        </Link>
                    </div>
                </div>
                <div v-else class="text-sm text-gray-400 italic py-4 text-center">
                    No hay miembros del comité editorial con este cargo.
                </div>
            </div>
        </div>

        <ConfirmationModal :show="confirmingDeletion" @close="confirmingDeletion = false">
            <template #title>Eliminar Cargo Institucional</template>
            <template #content>
                ¿Estás seguro de que deseas eliminar permanentemente este cargo institucional?
            </template>
            <template #footer>
                <SecondaryButton @click="confirmingDeletion = false" class="mr-2 cursor-pointer">Cancelar</SecondaryButton>
                <DangerButton @click="deleteItem" class="cursor-pointer">Eliminar</DangerButton>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>
