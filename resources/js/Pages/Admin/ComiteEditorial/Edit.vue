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
    instituciones: {
        type: Array,
        default: () => [],
    },
    roles: {
        type: Array,
        default: () => ['autor', 'administrador'],
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
        : 'autor',
    user_is_active: props.miembro.persona?.user ? Boolean(props.miembro.persona.user.is_active) : true,

    // Datos de Comité Editorial
    cargo_id: props.miembro.cargo_id || '',
    institucion_id: props.miembro.institucion_id || '',
    ce_tipo: props.miembro.ce_tipo || 'Nacional',
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
    })).post(route('admin.comite-editorial.update', props.miembro.id), {
        preserveScroll: true,
        forceFormData: true,
    });
};
</script>

<template>
    <AppLayout title="Editar Miembro del Comité Editorial">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 w-full">
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('admin.comite-editorial.index')"
                        class="size-10 rounded-xl bg-white border border-gray-200 flex items-center justify-center text-gray-600 hover:text-gray-900 hover:border-gray-300 transition shadow-2xs shrink-0"
                        title="Volver"
                    >
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <div>
                        <h1 class="font-black text-xl sm:text-2xl text-slate-900 tracking-tight">
                            Editar Miembro: {{ miembro.persona?.nombre_completo || `#${miembro.id}` }}
                        </h1>
                        <p class="text-[13.5px] sm:text-[15px] text-slate-600 font-medium mt-0.5">
                            Actualiza la información personal, fotografía y filiación institucional en el comité
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 sm:gap-2.5">
                    <Link :href="route('admin.comite-editorial.show', miembro.id)">
                        <SecondaryButton class="cursor-pointer text-[14px] sm:text-[15px] font-bold px-4 sm:px-5 py-2 sm:py-2.5">Ver Perfil</SecondaryButton>
                    </Link>
                    <PrimaryButton :disabled="form.processing" @click="submit" class="cursor-pointer text-[14px] sm:text-[15.5px] font-bold px-4 sm:px-5 py-2 sm:py-2.5">
                        {{ form.processing ? 'Guardando...' : 'Actualizar Miembro' }}
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
                                    Identificación oficial del integrante del comité
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
                                    <InputLabel for="p_sitio_web" value="Sitio Web / Perfil Académico (Opcional)" class="text-[15.5px] font-bold text-gray-900" />
                                    <TextInput
                                        id="p_sitio_web"
                                        v-model="form.p_sitio_web"
                                        type="url"
                                        class="mt-1.5 block w-full text-[15.5px] font-medium"
                                        placeholder="https://orcid.org/0000-0002-... o sitio institucional"
                                    />
                                    <p class="text-[13.5px] text-gray-600 font-medium mt-1">
                                        Opcional. Algunos miembros del comité disponen de sitio o perfil web universitario; otros no.
                                    </p>
                                    <InputError :message="form.errors.p_sitio_web" class="mt-1" />
                                </div>
                            </div>
                        </div>

                        <!-- CARD: CUENTA DE USUARIO Y ACCESO AL SISTEMA -->
                        <AccountAccessSection
                            :form="form"
                            :roles="roles"
                            default-role="autor"
                            :user="miembro.persona?.user"
                            entity-label="Comité Editorial"
                        />

                        <!-- CARD 2: FOTO DE PERFIL (SPATIE MEDIA LIBRARY) -->
                        <div class="bg-white rounded-2xl p-6 border border-gray-200/90 shadow-2xs">
                            <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                                <div>
                                    <h2 class="text-[17px] font-black text-gray-900 tracking-tight flex items-center gap-2">
                                        <span>Foto de Perfil</span>
                                        <span class="text-[12.5px] font-bold text-gray-600 bg-gray-100 px-3 py-1 rounded-full">
                                            Opcional • Spatie Media
                                        </span>
                                    </h2>
                                    <p class="text-[13.5px] text-gray-500 font-medium mt-0.5">
                                        Fotografía oficial del revisor en la colección foto_perfil
                                    </p>
                                </div>
                                <button
                                    v-if="photoPreview"
                                    type="button"
                                    @click="selectNewPhoto"
                                    class="text-[13.5px] font-bold text-[#35449F] hover:underline cursor-pointer"
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
                                    <p class="text-[15.5px] font-bold text-gray-900">
                                        {{ form.foto ? 'Nueva fotografía seleccionada' : 'Fotografía actual del miembro' }}
                                    </p>
                                    <p class="text-[13px] text-gray-500 font-medium mt-0.5">
                                        {{ form.foto ? 'Se actualizará al guardar los cambios.' : 'Almacenada con Laravel Spatie Media Library.' }}
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
                                <p class="text-[15.5px] font-bold text-gray-800">Subir foto de perfil</p>
                                <p class="text-[13px] text-gray-500 font-medium mt-0.5">JPG, PNG o WEBP</p>
                            </div>
                            <InputError :message="form.errors.foto" class="mt-2" />
                        </div>
                    </div>

                    <!-- COLUMNA LATERAL (4 COLUMNAS) -->
                    <div class="lg:col-span-4 space-y-6">
                        <!-- CARD TIPO DE COMITÉ -->
                        <div class="bg-white rounded-2xl p-6 border border-gray-200/90 shadow-2xs">
                            <h2 class="text-[17px] font-black text-gray-900 tracking-tight border-b border-gray-100 pb-3 mb-4">
                                Tipo de Comité Editorial
                            </h2>

                            <div>
                                <InputLabel for="ce_tipo" value="Jurisdicción / Ámbito *" class="text-[15.5px] font-bold text-gray-900" />
                                <select
                                    id="ce_tipo"
                                    v-model="form.ce_tipo"
                                    class="mt-1.5 block w-full rounded-xl border-gray-300 py-3 px-3.5 text-[15.5px] font-medium focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 bg-white"
                                    required
                                >
                                    <option value="Nacional">Nacional (Bolivia)</option>
                                    <option value="Internacional">Internacional</option>
                                    <option value="Otro">Otro Ámbito</option>
                                </select>
                                <InputError :message="form.errors.ce_tipo" class="mt-1" />
                            </div>
                        </div>

                        <!-- CARD CARGO E INSTITUCIÓN -->
                        <div class="bg-white rounded-2xl p-6 border border-gray-200/90 shadow-2xs">
                            <h2 class="text-[17px] font-black text-gray-900 tracking-tight border-b border-gray-100 pb-3 mb-4">
                                Filiación Institucional
                            </h2>

                            <div class="space-y-4">
                                <div>
                                    <InputLabel for="cargo_id" value="Cargo en el Comité *" class="text-[15.5px] font-bold text-gray-900" />
                                    <select
                                        id="cargo_id"
                                        v-model="form.cargo_id"
                                        class="mt-1.5 block w-full rounded-xl border-gray-300 py-3 px-3.5 text-[15.5px] font-medium focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 bg-white"
                                        required
                                    >
                                        <option value="" disabled>Selecciona el cargo...</option>
                                        <option v-for="c in cargos" :key="c.id" :value="c.id">{{ c.ci_nombre }}</option>
                                    </select>
                                    <InputError :message="form.errors.cargo_id" class="mt-1" />
                                </div>

                                <div>
                                    <InputLabel for="institucion_id" value="Institución de Procedencia *" class="text-[15.5px] font-bold text-gray-900" />
                                    <select
                                        id="institucion_id"
                                        v-model="form.institucion_id"
                                        class="mt-1.5 block w-full rounded-xl border-gray-300 py-3 px-3.5 text-[15.5px] font-medium focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 bg-white"
                                        required
                                    >
                                        <option value="" disabled>Selecciona la institución...</option>
                                        <option v-for="inst in instituciones" :key="inst.id" :value="inst.id">
                                            {{ inst.inst_nombre }}
                                        </option>
                                    </select>
                                    <InputError :message="form.errors.institucion_id" class="mt-1" />
                                </div>
                            </div>
                        </div>

                        <!-- CARD RESUMEN -->
                        <div class="bg-white rounded-2xl p-6 border border-gray-200/90 shadow-2xs">
                            <h2 class="text-[17px] font-black text-gray-900 tracking-tight border-b border-gray-100 pb-3 mb-3">
                                Ficha de Revisor
                            </h2>
                            <div class="text-[14px] text-gray-700 font-medium space-y-2.5">
                                <p><span class="font-bold text-gray-900">Usuario Vinculado:</span> {{ miembro.persona?.user?.email || 'N/A' }}</p>
                                <p><span class="font-bold text-gray-900">Institución:</span> {{ miembro.institucion?.inst_nombre || 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BARRA INFERIOR DE ACCIONES -->
                <div class="mt-8 flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                    <Link :href="route('admin.comite-editorial.index')">
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
