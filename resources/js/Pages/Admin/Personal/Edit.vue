<script setup>
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import AccountAccessSection from '@/Components/AccountAccessSection.vue';

const props = defineProps({
    miembro: {
        type: Object,
        required: true,
    },
    cargos: {
        type: Array,
        default: () => [],
    },
    roles: {
        type: Array,
        default: () => ['administrador', 'autor'],
    },
});

const form = useForm({
    // Datos de Persona y Usuario
    p_nombre: props.miembro.persona?.p_nombre || '',
    p_apellido_paterno: props.miembro.persona?.p_apellido_paterno || '',
    p_apellido_materno: props.miembro.persona?.p_apellido_materno || '',
    p_correo: props.miembro.persona?.p_correo || props.miembro.persona?.user?.email || '',
    p_celular: props.miembro.persona?.p_celular || '',
    p_sitio_web: props.miembro.persona?.p_sitio_web || '',
    foto: null,

    // Cuenta de Usuario
    crear_usuario: Boolean(props.miembro.persona?.user_id),
    password: '',
    rol: (props.miembro.persona?.user?.roles && props.miembro.persona?.user?.roles.length > 0)
        ? (props.miembro.persona.user.roles[0].name || props.miembro.persona.user.roles[0])
        : 'administrador',
    user_is_active: props.miembro.persona?.user ? Boolean(props.miembro.persona.user.is_active) : true,

    // Datos de Personal
    cargo_id: props.miembro.cargo_id || '',
    pers_descripcion: props.miembro.pers_descripcion || '',
    pers_fecha_ingreso: props.miembro.pers_fecha_ingreso ? props.miembro.pers_fecha_ingreso.slice(0, 10) : '',
    pers_fecha_retiro: props.miembro.pers_fecha_retiro ? props.miembro.pers_fecha_retiro.slice(0, 10) : '',
    pers_estado: Boolean(props.miembro.pers_estado),
});

const photoInput = ref(null);
const photoPreview = ref(props.miembro?.persona?.foto_url || null);

const selectNewPhoto = () => {
    photoInput.value?.click();
};

const handlePhotoChange = (e) => {
    const file = e.target.files?.[0];
    if (!file) return;
    form.foto = file;
    const reader = new FileReader();
    reader.onload = (event) => {
        photoPreview.value = event.target.result;
    };
    reader.readAsDataURL(file);
};

const removePhoto = () => {
    if (photoInput.value) {
        photoInput.value.value = null;
    }
    form.foto = null;
    photoPreview.value = props.miembro?.persona?.foto_url || null;
};

const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'PUT',
    })).post(route('admin.personal.update', props.miembro.id), {
        preserveScroll: true,
        forceFormData: true,
    });
};
</script>

<template>
    <AppLayout title="Editar Personal">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 w-full">
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('admin.personal.index')"
                        class="size-10 rounded-xl bg-white border border-gray-200 flex items-center justify-center text-gray-600 hover:text-gray-900 hover:border-gray-300 transition shadow-2xs shrink-0"
                        title="Volver"
                    >
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <div>
                        <h1 class="font-black text-xl sm:text-2xl text-slate-900 tracking-tight">
                            Editar Personal: {{ miembro.persona?.nombre_completo || `#${miembro.id}` }}
                        </h1>
                        <p class="text-[13.5px] sm:text-[15px] text-slate-600 font-medium mt-0.5">
                            Actualiza la información laboral, credenciales y fotografía institucional
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 sm:gap-2.5">
                    <Link :href="route('admin.personal.show', miembro.id)">
                        <SecondaryButton class="cursor-pointer text-[14px] sm:text-[15px] font-bold px-4 sm:px-5 py-2 sm:py-2.5">Ver Detalle</SecondaryButton>
                    </Link>
                    <PrimaryButton :disabled="form.processing" @click="submit" class="cursor-pointer text-[14px] sm:text-[15.5px] font-bold px-4 sm:px-5 py-2 sm:py-2.5">
                        {{ form.processing ? 'Guardando...' : 'Actualizar Personal' }}
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="max-w-7xl mx-auto pb-12">
            <form @submit.prevent="submit">
                <!-- GRID INSPIRADO EN SHOPIFY -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <!-- COLUMNA PRINCIPAL (8 COLUMNAS) -->
                    <div class="lg:col-span-8 space-y-6">
                        <!-- CARD 1: DATOS PERSONALES Y USUARIO -->
                        <div class="bg-white rounded-2xl p-6 border border-gray-200/90 shadow-2xs">
                            <div class="border-b border-gray-100 pb-3 mb-5">
                                <h2 class="text-[17px] font-black text-gray-900 tracking-tight">
                                    Información Personal & Credenciales
                                </h2>
                                <p class="text-[14px] text-gray-600 font-medium mt-0.5">
                                    Datos de identificación oficial y cuenta de acceso
                                </p>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                                <div>
                                    <InputLabel for="p_nombre" value="Nombres *" class="text-[15.5px] font-bold text-gray-900" />
                                    <TextInput
                                        id="p_nombre"
                                        v-model="form.p_nombre"
                                        type="text"
                                        class="mt-1.5 block w-full text-[15.5px] font-medium"
                                        required
                                    />
                                    <InputError :message="form.errors.p_nombre" class="mt-1" />
                                </div>

                                <div>
                                    <InputLabel for="p_apellido_paterno" value="Apellido Paterno *" class="text-[15.5px] font-bold text-gray-900" />
                                    <TextInput
                                        id="p_apellido_paterno"
                                        v-model="form.p_apellido_paterno"
                                        type="text"
                                        class="mt-1.5 block w-full text-[15.5px] font-medium"
                                        required
                                    />
                                    <InputError :message="form.errors.p_apellido_paterno" class="mt-1" />
                                </div>

                                <div>
                                    <InputLabel for="p_apellido_materno" value="Apellido Materno" class="text-[15.5px] font-bold text-gray-900" />
                                    <TextInput
                                        id="p_apellido_materno"
                                        v-model="form.p_apellido_materno"
                                        type="text"
                                        class="mt-1.5 block w-full text-[15.5px] font-medium"
                                    />
                                    <InputError :message="form.errors.p_apellido_materno" class="mt-1" />
                                </div>

                                <div>
                                    <InputLabel for="p_correo" value="Correo Electrónico *" class="text-[15.5px] font-bold text-gray-900" />
                                    <TextInput
                                        id="p_correo"
                                        v-model="form.p_correo"
                                        type="email"
                                        class="mt-1.5 block w-full text-[15.5px] font-medium"
                                        required
                                    />
                                    <InputError :message="form.errors.p_correo" class="mt-1" />
                                </div>

                                <div>
                                    <InputLabel for="p_celular" value="Teléfono / Celular (Opcional)" class="text-[15.5px] font-bold text-gray-900" />
                                    <TextInput
                                        id="p_celular"
                                        v-model="form.p_celular"
                                        type="text"
                                        class="mt-1.5 block w-full text-[15.5px] font-medium"
                                        placeholder="+591 70000000"
                                    />
                                    <InputError :message="form.errors.p_celular" class="mt-1" />
                                </div>

                                <div class="sm:col-span-2">
                                    <InputLabel for="p_sitio_web" value="Sitio Web / Perfil Profesional (Opcional)" class="text-[15.5px] font-bold text-gray-900" />
                                    <TextInput
                                        id="p_sitio_web"
                                        v-model="form.p_sitio_web"
                                        type="url"
                                        class="mt-1.5 block w-full text-[15.5px] font-medium"
                                        placeholder="https://linkedin.com/in/... o sitio web personal"
                                    />
                                    <p class="text-[13.5px] text-gray-600 font-medium mt-1">
                                        Opcional. Algunos miembros disponen de portafolio o perfil en LinkedIn; otros no.
                                    </p>
                                    <InputError :message="form.errors.p_sitio_web" class="mt-1" />
                                </div>
                            </div>
                        </div>

                        <!-- CARD: CUENTA DE USUARIO Y ACCESO AL SISTEMA -->
                        <AccountAccessSection
                            :form="form"
                            :roles="roles"
                            default-role="administrador"
                            :user="miembro.persona?.user"
                            entity-label="Personal"
                        />

                        <!-- CARD 2: FOTO DE PERFIL (SPATIE MEDIA LIBRARY) -->
                        <div class="bg-white rounded-2xl p-6 border border-gray-200/90 shadow-2xs">
                            <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                                <div>
                                    <h2 class="text-[17px] font-black text-gray-900 tracking-tight flex items-center gap-2">
                                        <span>Foto de Perfil</span>
                                        <span class="text-[12.5px] font-bold text-gray-600 bg-gray-100 px-3 py-0.5 rounded-full">
                                            Opcional • Spatie Media
                                        </span>
                                    </h2>
                                    <p class="text-[14px] text-gray-600 font-medium mt-0.5">
                                        Fotografía institucional gestionada en la colección foto_perfil
                                    </p>
                                </div>
                                <button
                                    v-if="photoPreview"
                                    type="button"
                                    @click="selectNewPhoto"
                                    class="text-[13px] font-bold text-[#35449F] hover:underline cursor-pointer"
                                >
                                    Reemplazar foto
                                </button>
                            </div>

                            <input
                                ref="photoInput"
                                type="file"
                                accept="image/png,image/jpeg,image/webp"
                                class="hidden"
                                @change="handlePhotoChange"
                            />

                            <!-- Con Preview -->
                            <div v-if="photoPreview" class="flex items-center gap-5 p-4 rounded-xl bg-gray-50/70 border border-gray-200">
                                <img
                                    :src="photoPreview"
                                    alt="Foto Perfil"
                                    class="size-20 rounded-full object-cover shadow-xs border-2 border-white ring-2 ring-[#35449F]/20"
                                />
                                <div>
                                    <p class="text-[15px] font-bold text-gray-900">
                                        {{ form.foto ? 'Nueva fotografía seleccionada' : 'Fotografía institucional actual' }}
                                    </p>
                                    <p class="text-[13px] text-gray-600 font-medium mt-0.5">
                                        {{ form.foto ? 'Se actualizará al guardar.' : 'Almacenada con Laravel Spatie Media Library.' }}
                                    </p>
                                    <div class="mt-3 flex items-center gap-2">
                                        <button
                                            type="button"
                                            @click="selectNewPhoto"
                                            class="px-3.5 py-1.5 text-[13px] font-bold rounded-lg bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition cursor-pointer"
                                        >
                                            Cambiar imagen
                                        </button>
                                        <button
                                            v-if="form.foto"
                                            type="button"
                                            @click="removePhoto"
                                            class="px-3.5 py-1.5 text-[13px] font-bold rounded-lg text-red-600 hover:bg-red-50 transition cursor-pointer"
                                        >
                                            Revertir
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Dropzone vacía -->
                            <div
                                v-else
                                @click="selectNewPhoto"
                                class="border-2 border-dashed border-gray-200 hover:border-[#35449F] rounded-xl p-7 text-center cursor-pointer transition bg-gray-50/40 hover:bg-indigo-50/20"
                            >
                                <div class="size-11 rounded-full bg-indigo-50 text-[#35449F] flex items-center justify-center mx-auto mb-2.5">
                                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <p class="text-[15px] font-bold text-gray-900">Subir foto institucional</p>
                                <p class="text-[13.5px] text-gray-600 font-medium mt-0.5">JPG, PNG o WEBP</p>
                            </div>
                            <InputError :message="form.errors.foto" class="mt-2" />
                        </div>

                        <!-- CARD 3: FUNCIONES Y RESPONSABILIDADES -->
                        <div class="bg-white rounded-2xl p-6 border border-gray-200/90 shadow-2xs">
                            <div class="border-b border-gray-100 pb-3 mb-5">
                                <h2 class="text-[17px] font-black text-gray-900 tracking-tight">
                                    Funciones & Responsabilidades
                                </h2>
                                <p class="text-[14px] text-gray-600 font-medium mt-0.5">
                                    Detalle del rol y tareas asignadas
                                </p>
                            </div>

                            <div>
                                <InputLabel for="pers_descripcion" value="Descripción / Responsabilidades del Puesto" class="text-[15.5px] font-bold text-gray-900" />
                                <textarea
                                    id="pers_descripcion"
                                    v-model="form.pers_descripcion"
                                    rows="4"
                                    class="mt-1.5 block w-full rounded-xl border border-gray-300 p-3.5 text-[15.5px] font-medium text-slate-900 focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 bg-white shadow-2xs"
                                    placeholder="Describe las funciones principales desempeñadas en el instituto..."
                                ></textarea>
                                <InputError :message="form.errors.pers_descripcion" class="mt-1" />
                            </div>
                        </div>
                    </div>

                    <!-- COLUMNA LATERAL (4 COLUMNAS) -->
                    <div class="lg:col-span-4 space-y-6">
                        <!-- CARD 1: ESTADO DEL PERSONAL -->
                        <div class="bg-white rounded-2xl p-6 border border-gray-200/90 shadow-2xs">
                            <h2 class="text-[17px] font-black text-gray-900 tracking-tight border-b border-gray-100 pb-3 mb-4">
                                Estado del Personal
                            </h2>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-[15px] font-bold text-gray-900">
                                        {{ form.pers_estado ? 'Personal Activo' : 'Personal Inactivo' }}
                                    </p>
                                    <p class="text-[13px] text-gray-600 font-medium mt-0.5">
                                        {{ form.pers_estado ? 'Actualmente en funciones dentro del instituto.' : 'En baja o desvinculado.' }}
                                    </p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input
                                        type="checkbox"
                                        v-model="form.pers_estado"
                                        class="sr-only peer"
                                    />
                                    <div class="w-12 h-6.5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[3px] after:left-[3px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#35449F]"></div>
                                </label>
                            </div>
                        </div>

                        <!-- CARD 2: CARGO INSTITUCIONAL -->
                        <div class="bg-white rounded-2xl p-6 border border-gray-200/90 shadow-2xs">
                            <h2 class="text-[17px] font-black text-gray-900 tracking-tight border-b border-gray-100 pb-3 mb-4">
                                Cargo Institucional
                            </h2>

                            <div>
                                <InputLabel for="cargo_id" value="Cargo Designado *" class="text-[15.5px] font-bold text-gray-900" />
                                <select
                                    id="cargo_id"
                                    v-model="form.cargo_id"
                                    class="mt-1.5 block w-full rounded-xl border border-gray-300 py-3 px-3.5 text-[15.5px] font-medium text-slate-900 focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 bg-white shadow-2xs"
                                    required
                                >
                                    <option value="" disabled>Selecciona el cargo institucional...</option>
                                    <option v-for="c in cargos" :key="c.id" :value="c.id">{{ c.ci_nombre }}</option>
                                </select>
                                <InputError :message="form.errors.cargo_id" class="mt-1" />
                            </div>
                        </div>

                        <!-- CARD 3: PERÍODO INSTITUCIONAL (MOVIDO AQUÍ EN VERTICAL ANTES DE GUÍA/FICHA) -->
                        <div class="bg-white rounded-2xl p-6 border border-gray-200/90 shadow-2xs">
                            <h2 class="text-[17px] font-black text-gray-900 tracking-tight border-b border-gray-100 pb-3 mb-4">
                                Período Institucional
                            </h2>

                            <div class="space-y-4">
                                <div>
                                    <InputLabel for="pers_fecha_ingreso" value="Fecha de Ingreso (Opcional)" class="text-[15.5px] font-bold text-gray-900" />
                                    <TextInput
                                        id="pers_fecha_ingreso"
                                        v-model="form.pers_fecha_ingreso"
                                        type="date"
                                        class="mt-1.5 block w-full text-[15.5px] font-medium"
                                    />
                                    <InputError :message="form.errors.pers_fecha_ingreso" class="mt-1" />
                                </div>

                                <div>
                                    <InputLabel for="pers_fecha_retiro" value="Fecha de Retiro (Opcional)" class="text-[15.5px] font-bold text-gray-900" />
                                    <TextInput
                                        id="pers_fecha_retiro"
                                        v-model="form.pers_fecha_retiro"
                                        type="date"
                                        class="mt-1.5 block w-full text-[15.5px] font-medium"
                                    />
                                    <InputError :message="form.errors.pers_fecha_retiro" class="mt-1" />
                                </div>
                            </div>
                        </div>

                        <!-- CARD 4: RESUMEN / FICHA -->
                        <div class="bg-white rounded-2xl p-6 border border-gray-200/90 shadow-2xs">
                            <h2 class="text-[17px] font-black text-gray-900 tracking-tight border-b border-gray-100 pb-3 mb-3">
                                Ficha de Colaborador
                            </h2>
                            <div class="text-[13.5px] text-gray-700 font-medium space-y-2">
                                <p><span class="font-bold text-gray-900">Usuario Vinculado:</span> {{ miembro.persona?.user?.email || 'N/A' }}</p>
                                <p><span class="font-bold text-gray-900">Cargo Actual:</span> {{ miembro.cargo?.ci_nombre || 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BARRA INFERIOR DE ACCIONES -->
                <div class="mt-8 flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                    <Link :href="route('admin.personal.index')">
                        <SecondaryButton class="cursor-pointer text-[15px] font-bold px-5 py-2.5">Cancelar</SecondaryButton>
                    </Link>
                    <PrimaryButton :disabled="form.processing" class="cursor-pointer text-[15.5px] font-bold px-6 py-2.5">
                        {{ form.processing ? 'Guardando...' : 'Guardar Cambios' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
