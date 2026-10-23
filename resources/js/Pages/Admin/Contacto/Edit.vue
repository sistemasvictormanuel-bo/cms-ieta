<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import EditorJs from '@/Components/EditorJs.vue';

const props = defineProps({
    contacto: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    cc_telefono: Array.isArray(props.contacto?.cc_telefono) && props.contacto.cc_telefono.length
        ? [...props.contacto.cc_telefono]
        : [''],
    cc_email: Array.isArray(props.contacto?.cc_email) && props.contacto.cc_email.length
        ? [...props.contacto.cc_email]
        : [''],
    cc_dia_inicio: props.contacto?.cc_dia_inicio || 'Lunes',
    cc_dia_fin: props.contacto?.cc_dia_fin || 'Viernes',
    cc_hora_apertura: props.contacto?.cc_hora_apertura ? props.contacto.cc_hora_apertura.substring(0, 5) : '08:30',
    cc_hora_cierre: props.contacto?.cc_hora_cierre ? props.contacto.cc_hora_cierre.substring(0, 5) : '18:30',
    cc_direccion: props.contacto?.cc_direccion || '',
    cc_contenido: props.contacto?.cc_contenido || { blocks: [] },
});

// Manejo dinámico de teléfonos
const addTelefono = () => {
    form.cc_telefono.push('');
};
const removeTelefono = (index) => {
    if (form.cc_telefono.length > 1) {
        form.cc_telefono.splice(index, 1);
    } else {
        form.cc_telefono[0] = '';
    }
};

// Manejo dinámico de correos
const addEmail = () => {
    form.cc_email.push('');
};
const removeEmail = (index) => {
    if (form.cc_email.length > 1) {
        form.cc_email.splice(index, 1);
    } else {
        form.cc_email[0] = '';
    }
};

const diasSemana = [
    'Lunes',
    'Martes',
    'Miércoles',
    'Jueves',
    'Viernes',
    'Sábado',
    'Domingo',
];

const submit = () => {
    form.put(route('admin.contacto.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout title="Contacto - Información y Canales de Atención">
        <template #header>
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 w-full">
                <div>
                    <h1 class="font-extrabold text-2xl text-[#14161C] tracking-tight">
                        Contacto y Atención Institucional
                    </h1>
                    <p class="text-xs sm:text-sm text-gray-500 font-medium">
                        Administra y actualiza los canales de atención, teléfonos, correos, horarios y ubicación del IETA.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <span v-if="form.isDirty" class="text-xs font-semibold text-amber-600 bg-amber-50 px-3 py-1.5 rounded-lg border border-amber-200">
                        ● Cambios sin guardar
                    </span>
                    <PrimaryButton @click="submit" :disabled="form.processing" class="cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ form.processing ? 'Guardando...' : 'Guardar Información' }}
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="max-w-5xl mx-auto pb-12">
            <form @submit.prevent="submit" class="space-y-8">
                <!-- ========================================== -->
                <!-- SECCIÓN 1: CANALES DIRECTOS DE COMUNICACIÓN -->
                <!-- ========================================== -->
                <div class="bg-white border border-[#E0E0E0] shadow-xs rounded-2xl p-6 md:p-8 space-y-6">
                    <div class="flex items-center space-x-3 pb-4 border-b border-gray-100">
                        <div class="size-9 rounded-xl bg-[#35449F]/10 text-[#35449F] flex items-center justify-center font-bold">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-extrabold text-[#14161C] tracking-tight">
                                Canales Directos de Comunicación
                            </h2>
                            <p class="text-xs text-gray-500">
                                Números de teléfono o WhatsApp y correos electrónicos oficiales del instituto.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Sub-sección: Teléfonos -->
                        <div class="bg-gray-50/70 p-5 rounded-2xl border border-gray-100 space-y-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <InputLabel value="Teléfonos de Contacto *" class="font-bold text-[#14161C]" />
                                    <p class="text-xs text-gray-500">Añade uno o más números oficiales.</p>
                                </div>
                                <button
                                    type="button"
                                    @click="addTelefono"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold text-[#35449F] bg-[#35449F]/10 hover:bg-[#35449F] hover:text-white rounded-lg transition cursor-pointer"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Agregar
                                </button>
                            </div>

                            <div class="space-y-2.5">
                                <div
                                    v-for="(tel, idx) in form.cc_telefono"
                                    :key="idx"
                                    class="flex items-center gap-2"
                                >
                                    <div class="relative flex-1">
                                        <TextInput
                                            v-model="form.cc_telefono[idx]"
                                            type="text"
                                            class="w-full text-sm"
                                            placeholder="Ej: +591 2 2441234 o +591 71234567"
                                            required
                                        />
                                    </div>
                                    <button
                                        type="button"
                                        @click="removeTelefono(idx)"
                                        class="p-2.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition cursor-pointer"
                                        title="Quitar teléfono"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <InputError :message="form.errors.cc_telefono" class="mt-1" />
                        </div>

                        <!-- Sub-sección: Correos -->
                        <div class="bg-gray-50/70 p-5 rounded-2xl border border-gray-100 space-y-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <InputLabel value="Correos Electrónicos *" class="font-bold text-[#14161C]" />
                                    <p class="text-xs text-gray-500">Añade las casillas institucionales.</p>
                                </div>
                                <button
                                    type="button"
                                    @click="addEmail"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold text-[#35449F] bg-[#35449F]/10 hover:bg-[#35449F] hover:text-white rounded-lg transition cursor-pointer"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Agregar
                                </button>
                            </div>

                            <div class="space-y-2.5">
                                <div
                                    v-for="(email, idx) in form.cc_email"
                                    :key="idx"
                                    class="flex items-center gap-2"
                                >
                                    <div class="relative flex-1">
                                        <TextInput
                                            v-model="form.cc_email[idx]"
                                            type="email"
                                            class="w-full text-sm"
                                            placeholder="Ej: contacto@ieta.edu.bo"
                                            required
                                        />
                                    </div>
                                    <button
                                        type="button"
                                        @click="removeEmail(idx)"
                                        class="p-2.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-xl transition cursor-pointer"
                                        title="Quitar correo"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <InputError :message="form.errors.cc_email" class="mt-1" />
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- SECCIÓN 2: DÍAS Y HORARIOS DE ATENCIÓN     -->
                <!-- ========================================== -->
                <div class="bg-white border border-[#E0E0E0] shadow-xs rounded-2xl p-6 md:p-8 space-y-6">
                    <div class="flex items-center space-x-3 pb-4 border-b border-gray-100">
                        <div class="size-9 rounded-xl bg-[#35449F]/10 text-[#35449F] flex items-center justify-center font-bold">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-extrabold text-[#14161C] tracking-tight">
                                Horario y Días de Atención al Público
                            </h2>
                            <p class="text-xs text-gray-500">
                                Establece las jornadas y horas hábiles de recepción presencial o telefónica.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                        <!-- Día inicio -->
                        <div>
                            <InputLabel for="cc_dia_inicio" value="Día Inicial *" class="font-bold text-[#14161C]" />
                            <select
                                id="cc_dia_inicio"
                                v-model="form.cc_dia_inicio"
                                class="mt-1 block w-full rounded-xl border-gray-300 shadow-xs focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 text-sm font-semibold"
                                required
                            >
                                <option v-for="dia in diasSemana" :key="dia" :value="dia">{{ dia }}</option>
                            </select>
                            <InputError :message="form.errors.cc_dia_inicio" class="mt-1" />
                        </div>

                        <!-- Día fin -->
                        <div>
                            <InputLabel for="cc_dia_fin" value="Día Final *" class="font-bold text-[#14161C]" />
                            <select
                                id="cc_dia_fin"
                                v-model="form.cc_dia_fin"
                                class="mt-1 block w-full rounded-xl border-gray-300 shadow-xs focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 text-sm font-semibold"
                                required
                            >
                                <option v-for="dia in diasSemana" :key="dia" :value="dia">{{ dia }}</option>
                            </select>
                            <InputError :message="form.errors.cc_dia_fin" class="mt-1" />
                        </div>

                        <!-- Hora apertura -->
                        <div>
                            <InputLabel for="cc_hora_apertura" value="Hora Apertura *" class="font-bold text-[#14161C]" />
                            <TextInput
                                id="cc_hora_apertura"
                                v-model="form.cc_hora_apertura"
                                type="time"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.cc_hora_apertura" class="mt-1" />
                        </div>

                        <!-- Hora cierre -->
                        <div>
                            <InputLabel for="cc_hora_cierre" value="Hora Cierre *" class="font-bold text-[#14161C]" />
                            <TextInput
                                id="cc_hora_cierre"
                                v-model="form.cc_hora_cierre"
                                type="time"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.cc_hora_cierre" class="mt-1" />
                        </div>
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- SECCIÓN 3: UBICACIÓN Y DIRECCIÓN FÍSICA    -->
                <!-- ========================================== -->
                <div class="bg-white border border-[#E0E0E0] shadow-xs rounded-2xl p-6 md:p-8 space-y-6">
                    <div class="flex items-center space-x-3 pb-4 border-b border-gray-100">
                        <div class="size-9 rounded-xl bg-[#35449F]/10 text-[#35449F] flex items-center justify-center font-bold">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-extrabold text-[#14161C] tracking-tight">
                                Ubicación y Dirección Física
                            </h2>
                            <p class="text-xs text-gray-500">
                                Dirección de la sede central o campus para visitas y trámites presenciales.
                            </p>
                        </div>
                    </div>

                    <div>
                        <InputLabel for="cc_direccion" value="Dirección Institucional" class="font-bold text-[#14161C]" />
                        <textarea
                            id="cc_direccion"
                            v-model="form.cc_direccion"
                            rows="3"
                            placeholder="Ej: Av. Villazón N° 1995, Edificio Central IETA, Piso 3, La Paz - Bolivia"
                            class="mt-1 block w-full rounded-xl border-gray-300 shadow-xs focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 text-sm leading-relaxed"
                        ></textarea>
                        <InputError :message="form.errors.cc_direccion" class="mt-2" />
                    </div>
                </div>

                <!-- ========================================== -->
                <!-- SECCIÓN 4: INFORMACIÓN ADICIONAL (EDITOR.JS)-->
                <!-- ========================================== -->
                <div class="bg-white border border-[#E0E0E0] shadow-xs rounded-2xl p-6 md:p-8 space-y-6">
                    <div class="flex items-center space-x-3 pb-4 border-b border-gray-100">
                        <div class="size-9 rounded-xl bg-[#35449F]/10 text-[#35449F] flex items-center justify-center font-bold">
                            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-extrabold text-[#14161C] tracking-tight">
                                Contenido Enriquecido y Protocolos
                            </h2>
                            <p class="text-xs text-gray-500">
                                Información detallada, referencias de llegada, enlaces a mapas o indicaciones especiales redactadas mediante Editor.js.
                            </p>
                        </div>
                    </div>

                    <div>
                        <EditorJs
                            v-model="form.cc_contenido"
                            placeholder="Redacta referencias adicionales de llegada, requisitos para trámites, enlaces a mapas..."
                        />
                        <InputError :message="form.errors.cc_contenido" class="mt-2" />
                    </div>
                </div>

                <!-- Botón Flotante Inferior -->
                <div class="flex items-center justify-end space-x-3 pt-4">
                    <PrimaryButton :disabled="form.processing" class="cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ form.processing ? 'Guardando...' : 'Guardar Información de Contacto' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
