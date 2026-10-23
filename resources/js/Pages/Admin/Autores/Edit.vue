<script setup>
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import EditorJs from '@/Components/EditorJs.vue';
import AccountAccessSection from '@/Components/AccountAccessSection.vue';

const props = defineProps({
    autor: {
        type: Object,
        required: true,
    },
    tipos: {
        type: Array,
        default: () => [],
    },
    especialidades: {
        type: Array,
        default: () => [],
    },
    grados: {
        type: Array,
        default: () => [],
    },
    roles: {
        type: Array,
        default: () => ['autor'],
    },
});

const form = useForm({
    // Datos de Persona y Usuario
    p_nombre: props.autor?.persona?.p_nombre || '',
    p_apellido_paterno: props.autor?.persona?.p_apellido_paterno || '',
    p_apellido_materno: props.autor?.persona?.p_apellido_materno || '',
    p_correo: props.autor?.persona?.p_correo || props.autor?.persona?.user?.email || '',
    p_celular: props.autor?.persona?.p_celular || '',
    p_sitio_web: props.autor?.persona?.p_sitio_web || '',
    foto: null,

    // Cuenta de Usuario
    crear_usuario: Boolean(props.autor?.persona?.user_id),
    password: '',
    rol: (props.autor?.persona?.user?.roles && props.autor?.persona?.user?.roles.length > 0)
        ? (props.autor.persona.user.roles[0].name || props.autor.persona.user.roles[0])
        : 'autor',
    user_is_active: props.autor?.persona?.user ? Boolean(props.autor.persona.user.is_active) : true,

    // Datos de Autor
    tipo_autor_id: props.autor.tipo_autor_id ?? '',
    especialidades: (props.autor.especialidades && props.autor.especialidades.length > 0)
        ? props.autor.especialidades.map(e => e.id)
        : (props.autor.especialidad_id ? [props.autor.especialidad_id] : []),
    grado_academico_id: props.autor.grado_academico_id ?? '',
    at_slug: props.autor.at_slug ?? '',
    at_intereses_cientificos: props.autor.at_intereses_cientificos ?? '',
    at_biografia: props.autor.at_biografia ?? { blocks: [] },
    at_estado: Boolean(props.autor.at_estado),
});

const photoInput = ref(null);
const photoPreview = ref(props.autor?.persona?.foto_url || null);

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
    photoPreview.value = props.autor?.persona?.foto_url || null;
};

const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'PUT',
    })).post(route('admin.autores.update', props.autor.id), {
        preserveScroll: true,
        forceFormData: true,
    });
};
</script>

<template>
    <AppLayout title="Editar Autor">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 w-full">
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('admin.autores.index')"
                        class="size-10 rounded-xl bg-white border border-gray-200 flex items-center justify-center text-gray-600 hover:text-gray-900 hover:border-gray-300 transition shadow-2xs shrink-0"
                        title="Volver"
                    >
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </Link>
                    <div>
                        <h1 class="font-black text-xl sm:text-2xl text-[#14161C] tracking-tight">
                            Editar Autor: {{ autor.persona?.nombre_completo || `#${autor.id}` }}
                        </h1>
                        <p class="text-[13px] sm:text-[14.5px] text-gray-600 font-medium">
                            Actualiza la información personal, fotografía y perfil académico del investigador
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 sm:gap-2.5">
                    <Link :href="route('admin.autores.show', autor.id)">
                        <SecondaryButton class="cursor-pointer text-[14px] sm:text-[15px] font-bold px-4 py-2 sm:py-2.5">Ver Perfil</SecondaryButton>
                    </Link>
                    <PrimaryButton :disabled="form.processing" @click="submit" class="cursor-pointer text-[14px] sm:text-[15.5px] font-bold px-4 sm:px-5 py-2 sm:py-2.5">
                        {{ form.processing ? 'Guardando...' : 'Actualizar Autor' }}
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
                                    Información Personal & Cuenta
                                </h2>
                                <p class="text-xs text-slate-500 font-medium mt-0.5">
                                    Datos de identidad oficial y canales de contacto del investigador
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
                                        placeholder="https://orcid.org/0000-0002-... o sitio personal"
                                    />
                                    <p class="text-[13.5px] text-gray-500 font-medium mt-1">
                                        Opcional. Algunos autores cuentan con ORCID, ResearchGate o sitio profesional; otros no.
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
                            :user="autor?.persona?.user"
                            entity-label="Autor"
                        />

                        <!-- CARD 2: FOTO DE PERFIL (SPATIE MEDIA LIBRARY) -->
                        <div class="bg-white rounded-2xl p-6 border border-gray-200/90 shadow-2xs">
                            <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                                <div>
                                    <h2 class="text-[17px] font-black text-gray-900 tracking-tight flex items-center gap-2">
                                        <span>Foto de Perfil</span>
                                        <span class="text-xs font-bold text-slate-500 bg-slate-100 px-2.5 py-0.5 rounded-full">
                                            Opcional
                                        </span>
                                    </h2>
                                    <p class="text-xs text-slate-500 font-medium mt-0.5">
                                        Fotografía oficial del investigador. Formatos recomendados: JPG, PNG o WebP.
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

                            <!-- Preview o Foto Actual -->
                            <div v-if="photoPreview" class="flex items-center gap-5 p-4 rounded-xl bg-gray-50/70 border border-gray-200">
                                <img
                                    :src="photoPreview"
                                    alt="Foto Perfil"
                                    class="size-20 rounded-full object-cover shadow-xs border-2 border-white ring-2 ring-[#35449F]/20"
                                />
                                <div>
                                    <p class="text-[15.5px] font-bold text-gray-900">
                                        {{ form.foto ? 'Nueva fotografía seleccionada' : 'Fotografía actual del investigador' }}
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

                            <!-- Dropzone si no tenía foto -->
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
                                <p class="text-[13px] text-gray-500 font-medium mt-0.5">Haz clic para seleccionar una imagen JPG, PNG o WEBP</p>
                            </div>
                            <InputError :message="form.errors.foto" class="mt-2" />
                        </div>

                        <!-- CARD 3: TRAYECTORIA Y BIOGRAFÍA -->
                        <div class="bg-white rounded-2xl p-6 border border-gray-200/90 shadow-2xs">
                            <div class="border-b border-gray-100 pb-3 mb-5">
                                <h2 class="text-[17px] font-black text-gray-900 tracking-tight">
                                    Trayectoria Científica & Biografía
                                </h2>
                                <p class="text-[13.5px] text-gray-500 font-medium mt-0.5">
                                    Contenido enriquecido y líneas de investigación
                                </p>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <InputLabel for="at_intereses_cientificos" value="Intereses Científicos" class="text-[15.5px] font-bold text-gray-900" />
                                    <textarea
                                        id="at_intereses_cientificos"
                                        v-model="form.at_intereses_cientificos"
                                        rows="3"
                                        class="mt-1.5 block w-full rounded-xl border-gray-300 p-3.5 text-[15.5px] font-medium focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 bg-white"
                                        placeholder="Ej: Políticas de Desarrollo, Economía Ambiental, Energías Renovables..."
                                    ></textarea>
                                    <InputError :message="form.errors.at_intereses_cientificos" class="mt-1" />
                                </div>

                                <div>
                                    <InputLabel value="Biografía Oficial (Editor Enriquecido)" class="text-[15.5px] font-bold text-gray-900 mb-2" />
                                    <div class="border border-gray-200 rounded-xl p-4 bg-gray-50/30">
                                        <EditorJs
                                            v-model="form.at_biografia"
                                            placeholder="Redacta la trayectoria, distinciones y publicaciones del autor..."
                                        />
                                    </div>
                                    <InputError :message="form.errors.at_biografia" class="mt-1" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- COLUMNA LATERAL (4 COLUMNAS) -->
                    <div class="lg:col-span-4 space-y-6">
                        <!-- CARD ESTADO: ACTIVO / INACTIVO -->
                        <div class="bg-white rounded-2xl p-6 border border-gray-200/90 shadow-2xs">
                            <h2 class="text-[17px] font-black text-gray-900 tracking-tight border-b border-gray-100 pb-3 mb-4">
                                Estado en el Portal
                            </h2>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-[15.5px] font-bold text-gray-900">
                                        {{ form.at_estado ? 'Autor Activo' : 'Autor Inactivo' }}
                                    </p>
                                    <p class="text-[13px] text-gray-500 font-medium mt-0.5">
                                        {{ form.at_estado ? 'Visible en el repositorio y filtros.' : 'Oculto de las consultas públicas.' }}
                                    </p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input
                                        type="checkbox"
                                        v-model="form.at_estado"
                                        class="sr-only peer"
                                    />
                                    <div class="w-12 h-6.5 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[3px] after:left-[3px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#35449F]"></div>
                                </label>
                            </div>
                        </div>

                        <!-- CARD ORGANIZACIÓN CIENTÍFICA -->
                        <div class="bg-white rounded-2xl p-6 border border-gray-200/90 shadow-2xs">
                            <h2 class="text-[17px] font-black text-gray-900 tracking-tight border-b border-gray-100 pb-3 mb-4">
                                Clasificación Institucional
                            </h2>

                            <div class="space-y-4">
                                <div>
                                    <InputLabel for="tipo_autor_id" value="Tipo de Autor *" class="text-[15.5px] font-bold text-gray-900" />
                                    <select
                                        id="tipo_autor_id"
                                        v-model="form.tipo_autor_id"
                                        class="mt-1.5 block w-full rounded-xl border-gray-300 py-3 px-3.5 text-[15.5px] font-medium focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 bg-white"
                                        required
                                    >
                                        <option value="" disabled>Seleccionar tipo...</option>
                                        <option v-for="t in tipos" :key="t.id" :value="t.id">{{ t.ta_nombre }}</option>
                                    </select>
                                    <InputError :message="form.errors.tipo_autor_id" class="mt-1" />
                                </div>

                                <div>
                                    <InputLabel for="grado_academico_id" value="Grado Académico *" class="text-[15.5px] font-bold text-gray-900" />
                                    <select
                                        id="grado_academico_id"
                                        v-model="form.grado_academico_id"
                                        class="mt-1.5 block w-full rounded-xl border-gray-300 py-3 px-3.5 text-[15.5px] font-medium focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 bg-white"
                                        required
                                    >
                                        <option value="" disabled>Seleccionar grado...</option>
                                        <option v-for="g in grados" :key="g.id" :value="g.id">
                                            {{ g.ga_abreviatura ? `[${g.ga_abreviatura}] ` : '' }}{{ g.ga_nombre }}
                                        </option>
                                    </select>
                                    <InputError :message="form.errors.grado_academico_id" class="mt-1" />
                                </div>

                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <InputLabel value="Especialidades *" class="text-[15.5px] font-bold text-gray-900" />
                                        <span class="text-xs text-indigo-600 font-bold bg-indigo-50 px-2 py-0.5 rounded-full">
                                            {{ form.especialidades.length }} seleccionada(s)
                                        </span>
                                    </div>
                                    <div class="border border-gray-200 rounded-xl p-2.5 bg-gray-50/50 max-h-48 overflow-y-auto space-y-1">
                                        <label
                                            v-for="e in especialidades"
                                            :key="e.id"
                                            class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-white cursor-pointer transition text-[13.5px] font-medium text-slate-800 select-none"
                                            :class="{ 'bg-white shadow-2xs text-[#35449F] font-bold ring-1 ring-[#35449F]/20': form.especialidades.includes(e.id) }"
                                        >
                                            <input
                                                type="checkbox"
                                                :value="e.id"
                                                v-model="form.especialidades"
                                                class="rounded border-gray-300 text-[#35449F] focus:ring-[#35449F]"
                                            />
                                            <span class="flex-1">{{ e.ep_nombre }}</span>
                                        </label>
                                        <p v-if="especialidades.length === 0" class="text-xs text-gray-400 p-2">
                                            No hay especialidades registradas.
                                        </p>
                                    </div>
                                    <p class="text-[12px] text-gray-500 font-medium mt-1">
                                        Puedes seleccionar una o más especialidades para el autor.
                                    </p>
                                    <InputError :message="form.errors.especialidades" class="mt-1" />
                                </div>

                                <div>
                                    <InputLabel for="at_slug" value="Slug Identificador" class="text-[15.5px] font-bold text-gray-900" />
                                    <TextInput
                                        id="at_slug"
                                        v-model="form.at_slug"
                                        type="text"
                                        class="mt-1.5 block w-full text-[15.5px] font-medium"
                                    />
                                    <InputError :message="form.errors.at_slug" class="mt-1" />
                                </div>
                            </div>
                        </div>

                        <!-- CARD INFORMACIÓN DE FICHA -->
                        <div class="bg-white rounded-2xl p-6 border border-gray-200/90 shadow-2xs">
                            <h2 class="text-[17px] font-black text-gray-900 tracking-tight border-b border-gray-100 pb-3 mb-3">
                                Resumen del Investigador
                            </h2>
                            <div class="text-[14px] text-gray-700 font-medium space-y-2.5">
                                <p><span class="font-bold text-gray-900">Usuario Vinculado:</span> {{ autor.persona?.user?.email || 'N/A' }}</p>
                                <p><span class="font-bold text-gray-900">Artículos Publicados:</span> {{ autor.articulos_count ?? 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BARRA INFERIOR DE ACCIONES -->
                <div class="mt-8 flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                    <Link :href="route('admin.autores.index')">
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
