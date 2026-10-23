<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';

const props = defineProps({
    area: {
        type: Object,
        required: true,
    },
});

const confirmingDeletion = ref(false);

const deleteItem = () => {
    router.delete(route('admin.area-tematicas.destroy', props.area.id));
};
</script>

<template>
    <AppLayout title="Detalle de Área Temática">
        <template #header>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 w-full">
                <div>
                    <h1 class="font-extrabold text-2xl text-[#14161C] tracking-tight">
                        {{ area.at_nombre }}
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500 font-medium">
                        Detalle del área temática y bases de datos vinculadas.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Link :href="route('admin.area-tematicas.index')">
                        <SecondaryButton class="cursor-pointer">
                            ← Volver al listado
                        </SecondaryButton>
                    </Link>
                    <Link :href="route('admin.area-tematicas.edit', area.id)">
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
                    {{ area.at_nombre }}
                </h2>
                <div class="text-sm text-gray-600 bg-gray-50/60 p-4 rounded-xl border border-gray-100 leading-relaxed">
                    {{ area.at_descripcion || 'Sin descripción registrada para esta área temática.' }}
                </div>
            </div>

            <!-- Bases de Datos Relacionadas -->
            <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xs border border-gray-100">
                <h3 class="text-lg font-extrabold text-[#14161C] mb-4 pb-2 border-b border-gray-100 flex items-center justify-between">
                    <span>Bases de Datos Vinculadas</span>
                    <span class="text-xs font-bold text-gray-400">
                        {{ area.banco_datos?.length || 0 }} registros
                    </span>
                </h3>

                <div v-if="area.banco_datos?.length" class="space-y-3">
                    <div
                        v-for="banco in area.banco_datos"
                        :key="banco.id"
                        class="p-4 rounded-xl border border-gray-100 hover:border-gray-200 transition bg-gray-50/40 flex items-center justify-between gap-4"
                    >
                        <div>
                            <Link :href="route('admin.banco-datos.show', banco.id)" class="font-bold text-sm text-[#14161C] hover:text-[#35449F]">
                                {{ banco.bdd_titulo }}
                            </Link>
                            <div class="text-xs text-gray-400 mt-0.5">
                                Código: {{ banco.bdd_codigo }} • Tipo: {{ banco.bdd_tipo_dato }} • Año: {{ banco.bdd_anio }}
                            </div>
                        </div>
                        <Link :href="route('admin.banco-datos.show', banco.id)" class="text-xs font-bold text-[#35449F] hover:underline">
                            Ver Base →
                        </Link>
                    </div>
                </div>
                <div v-else class="text-sm text-gray-400 italic py-6 text-center">
                    No hay bases de datos asociadas a esta área temática todavía.
                </div>
            </div>
        </div>

        <ConfirmationModal :show="confirmingDeletion" @close="confirmingDeletion = false">
            <template #title>Eliminar Área Temática</template>
            <template #content>
                ¿Estás seguro de que deseas eliminar permanentemente esta área temática?
            </template>
            <template #footer>
                <SecondaryButton @click="confirmingDeletion = false" class="mr-2 cursor-pointer">Cancelar</SecondaryButton>
                <DangerButton @click="deleteItem" class="cursor-pointer">Eliminar</DangerButton>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>
