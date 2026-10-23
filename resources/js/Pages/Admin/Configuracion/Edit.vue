<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    configuracion: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    _method: 'put',
    cfg_nombre_institucion: props.configuracion?.cfg_nombre_institucion || 'Instituto de Estadística Teórica y Aplicada',
    cfg_siglas: props.configuracion?.cfg_siglas || 'IETA',
    cfg_slogan: props.configuracion?.cfg_slogan || '',
    cfg_descripcion_corta: props.configuracion?.cfg_descripcion_corta || '',
    cfg_email_principal: props.configuracion?.cfg_email_principal || '',
    cfg_telefono_principal: props.configuracion?.cfg_telefono_principal || '',
    cfg_direccion_principal: props.configuracion?.cfg_direccion_principal || '',
    cfg_redes_sociales: {
        facebook: props.configuracion?.cfg_redes_sociales?.facebook || '',
        twitter: props.configuracion?.cfg_redes_sociales?.twitter || '',
        linkedin: props.configuracion?.cfg_redes_sociales?.linkedin || '',
        youtube: props.configuracion?.cfg_redes_sociales?.youtube || '',
        instagram: props.configuracion?.cfg_redes_sociales?.instagram || '',
    },
    cfg_copyright: props.configuracion?.cfg_copyright || '',
    cfg_logo_principal: null,
    cfg_logo_blanco: null,
    cfg_favicon: null,
});

const previewLogoPrincipal = ref(props.configuracion?.logo_principal_url || null);
const previewLogoBlanco = ref(props.configuracion?.logo_blanco_url || null);
const previewFavicon = ref(props.configuracion?.favicon_url || null);

const handleLogoPrincipalChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.cfg_logo_principal = file;
        previewLogoPrincipal.value = URL.createObjectURL(file);
    }
};

const handleLogoBlancoChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.cfg_logo_blanco = file;
        previewLogoBlanco.value = URL.createObjectURL(file);
    }
};

const handleFaviconChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.cfg_favicon = file;
        previewFavicon.value = URL.createObjectURL(file);
    }
};

const submit = () => {
    form.post(route('admin.configuracion.update'), {
        forceFormData: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout title="Configuración General e Identidad Institucional">
        <template #header>
            <div>
                <h1 class="font-extrabold text-2xl text-[#14161C] tracking-tight">
                    Configuración General & Identidad Institucional
                </h1>
                <p class="text-xs sm:text-sm text-gray-500 font-medium">
                    Ajustes globales del instituto: logotipos, favicon, siglas, redes sociales y textos de copyright consumidos por la API y web pública.
                </p>
            </div>
        </template>

        <div class="max-w-5xl mx-auto pb-12">
            <form @submit.prevent="submit" class="space-y-8">
                <!-- Tarjeta 1: Identidad y Nombres -->
                <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xs border border-gray-100 space-y-6">
                    <h2 class="text-base font-extrabold text-[#14161C] pb-2 border-b border-gray-100 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-[#35449F]/10 text-[#35449F] flex items-center justify-center text-xs font-bold">1</span>
                        Identidad Institucional
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <InputLabel for="cfg_nombre_institucion" value="Nombre Oficial de la Institución *" />
                            <TextInput
                                id="cfg_nombre_institucion"
                                v-model="form.cfg_nombre_institucion"
                                type="text"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError :message="form.errors.cfg_nombre_institucion" class="mt-1.5" />
                        </div>

                        <div>
                            <InputLabel for="cfg_siglas" value="Siglas Oficiales *" />
                            <TextInput
                                id="cfg_siglas"
                                v-model="form.cfg_siglas"
                                type="text"
                                class="mt-1 block w-full font-mono font-bold"
                                placeholder="IETA"
                                required
                            />
                            <InputError :message="form.errors.cfg_siglas" class="mt-1.5" />
                        </div>
                    </div>

                    <div>
                        <InputLabel for="cfg_slogan" value="Lema / Slogan Institucional" />
                        <TextInput
                            id="cfg_slogan"
                            v-model="form.cfg_slogan"
                            type="text"
                            class="mt-1 block w-full text-sm"
                            placeholder="Ej: Excelencia en investigación, modelado y rigor estadístico"
                        />
                        <InputError :message="form.errors.cfg_slogan" class="mt-1.5" />
                    </div>

                    <div>
                        <InputLabel for="cfg_descripcion_corta" value="Descripción Institucional Corta (Meta SEO)" />
                        <textarea
                            id="cfg_descripcion_corta"
                            v-model="form.cfg_descripcion_corta"
                            rows="3"
                            class="mt-1 block w-full rounded-xl border-gray-200 p-3 text-sm focus:border-[#35449F] focus:ring-2 focus:ring-[#35449F]/10 bg-gray-50/50"
                            placeholder="Breve reseña del instituto para los motores de búsqueda y tarjetas de previsualización..."
                        ></textarea>
                        <InputError :message="form.errors.cfg_descripcion_corta" class="mt-1.5" />
                    </div>
                </div>

                <!-- Tarjeta 2: Logotipos y Favicon -->
                <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xs border border-gray-100 space-y-6">
                    <h2 class="text-base font-extrabold text-[#14161C] pb-2 border-b border-gray-100 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-[#35449F]/10 text-[#35449F] flex items-center justify-center text-xs font-bold">2</span>
                        Logotipos & Favicon del Sitio
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Logo Principal (Fondo Claro) -->
                        <div class="p-4 rounded-xl border border-gray-100 bg-gray-50/50 flex flex-col items-center justify-between gap-3 text-center">
                            <div>
                                <span class="font-bold text-xs text-gray-700 block">Logo Principal (Fondo Claro)</span>
                                <span class="text-[11px] text-gray-400">Para barra de navegación regular</span>
                            </div>

                            <div class="size-24 rounded-xl bg-white border border-gray-200 flex items-center justify-center p-2 shadow-xs">
                                <img
                                    v-if="previewLogoPrincipal"
                                    :src="previewLogoPrincipal"
                                    alt="Logo Principal"
                                    class="max-h-full max-w-full object-contain"
                                />
                                <span v-else class="text-[10px] text-gray-300 font-bold uppercase">Sin logo</span>
                            </div>

                            <label class="cursor-pointer px-3 py-1.5 bg-white border border-gray-200 hover:border-[#35449F] rounded-lg text-xs font-bold text-gray-700 hover:text-[#35449F] transition">
                                Subir Logo
                                <input type="file" accept="image/*" @change="handleLogoPrincipalChange" class="hidden" />
                            </label>
                            <InputError :message="form.errors.cfg_logo_principal" class="mt-1 text-center" />
                        </div>

                        <!-- Logo Blanco (Fondo Oscuro) -->
                        <div class="p-4 rounded-xl border border-gray-100 bg-gray-50/50 flex flex-col items-center justify-between gap-3 text-center">
                            <div>
                                <span class="font-bold text-xs text-gray-700 block">Logo Blanco (Fondo Oscuro)</span>
                                <span class="text-[11px] text-gray-400">Para el footer o navbar oscura</span>
                            </div>

                            <div class="size-24 rounded-xl bg-slate-900 border border-slate-800 flex items-center justify-center p-2 shadow-xs">
                                <img
                                    v-if="previewLogoBlanco"
                                    :src="previewLogoBlanco"
                                    alt="Logo Blanco"
                                    class="max-h-full max-w-full object-contain"
                                />
                                <span v-else class="text-[10px] text-slate-600 font-bold uppercase">Sin logo</span>
                            </div>

                            <label class="cursor-pointer px-3 py-1.5 bg-white border border-gray-200 hover:border-[#35449F] rounded-lg text-xs font-bold text-gray-700 hover:text-[#35449F] transition">
                                Subir Logo
                                <input type="file" accept="image/*" @change="handleLogoBlancoChange" class="hidden" />
                            </label>
                            <InputError :message="form.errors.cfg_logo_blanco" class="mt-1 text-center" />
                        </div>

                        <!-- Favicon -->
                        <div class="p-4 rounded-xl border border-gray-100 bg-gray-50/50 flex flex-col items-center justify-between gap-3 text-center">
                            <div>
                                <span class="font-bold text-xs text-gray-700 block">Favicon (Pestaña Navegador)</span>
                                <span class="text-[11px] text-gray-400">Ícono formato .ico, .png o .svg</span>
                            </div>

                            <div class="size-24 rounded-xl bg-white border border-gray-200 flex items-center justify-center p-2 shadow-xs">
                                <img
                                    v-if="previewFavicon"
                                    :src="previewFavicon"
                                    alt="Favicon"
                                    class="size-10 object-contain"
                                />
                                <span v-else class="text-[10px] text-gray-300 font-bold uppercase">Sin favicon</span>
                            </div>

                            <label class="cursor-pointer px-3 py-1.5 bg-white border border-gray-200 hover:border-[#35449F] rounded-lg text-xs font-bold text-gray-700 hover:text-[#35449F] transition">
                                Subir Favicon
                                <input type="file" accept="image/x-icon,image/png,image/svg+xml" @change="handleFaviconChange" class="hidden" />
                            </label>
                            <InputError :message="form.errors.cfg_favicon" class="mt-1 text-center" />
                        </div>
                    </div>
                </div>

                <!-- Tarjeta 3: Redes Sociales Institucionales -->
                <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xs border border-gray-100 space-y-6">
                    <h2 class="text-base font-extrabold text-[#14161C] pb-2 border-b border-gray-100 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-[#35449F]/10 text-[#35449F] flex items-center justify-center text-xs font-bold">3</span>
                        Redes Sociales Institucionales
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="red_facebook" value="Facebook (URL)" />
                            <TextInput
                                id="red_facebook"
                                v-model="form.cfg_redes_sociales.facebook"
                                type="url"
                                class="mt-1 block w-full text-sm font-mono"
                                placeholder="https://facebook.com/ieta.umsa"
                            />
                        </div>

                        <div>
                            <InputLabel for="red_twitter" value="X / Twitter (URL)" />
                            <TextInput
                                id="red_twitter"
                                v-model="form.cfg_redes_sociales.twitter"
                                type="url"
                                class="mt-1 block w-full text-sm font-mono"
                                placeholder="https://x.com/ieta_umsa"
                            />
                        </div>

                        <div>
                            <InputLabel for="red_linkedin" value="LinkedIn (URL)" />
                            <TextInput
                                id="red_linkedin"
                                v-model="form.cfg_redes_sociales.linkedin"
                                type="url"
                                class="mt-1 block w-full text-sm font-mono"
                                placeholder="https://linkedin.com/company/ieta-umsa"
                            />
                        </div>

                        <div>
                            <InputLabel for="red_youtube" value="YouTube (URL)" />
                            <TextInput
                                id="red_youtube"
                                v-model="form.cfg_redes_sociales.youtube"
                                type="url"
                                class="mt-1 block w-full text-sm font-mono"
                                placeholder="https://youtube.com/@ieta_umsa"
                            />
                        </div>

                        <div class="md:col-span-2">
                            <InputLabel for="red_instagram" value="Instagram (URL)" />
                            <TextInput
                                id="red_instagram"
                                v-model="form.cfg_redes_sociales.instagram"
                                type="url"
                                class="mt-1 block w-full text-sm font-mono"
                                placeholder="https://instagram.com/ieta.umsa"
                            />
                        </div>
                    </div>
                </div>

                <!-- Tarjeta 4: Contacto Rápido y Pie de Página -->
                <div class="bg-white rounded-2xl p-6 md:p-8 shadow-xs border border-gray-100 space-y-6">
                    <h2 class="text-base font-extrabold text-[#14161C] pb-2 border-b border-gray-100 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-[#35449F]/10 text-[#35449F] flex items-center justify-center text-xs font-bold">4</span>
                        Datos de Cabecera y Pie de Página (Footer)
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <InputLabel for="cfg_email_principal" value="Correo Electrónico de Contacto" />
                            <TextInput
                                id="cfg_email_principal"
                                v-model="form.cfg_email_principal"
                                type="email"
                                class="mt-1 block w-full text-sm"
                                placeholder="contacto@ieta.umsa.bo"
                            />
                            <InputError :message="form.errors.cfg_email_principal" class="mt-1.5" />
                        </div>

                        <div>
                            <InputLabel for="cfg_telefono_principal" value="Teléfono Institucional" />
                            <TextInput
                                id="cfg_telefono_principal"
                                v-model="form.cfg_telefono_principal"
                                type="text"
                                class="mt-1 block w-full text-sm"
                                placeholder="+591 2 2441234"
                            />
                            <InputError :message="form.errors.cfg_telefono_principal" class="mt-1.5" />
                        </div>

                        <div>
                            <InputLabel for="cfg_direccion_principal" value="Dirección Principal" />
                            <TextInput
                                id="cfg_direccion_principal"
                                v-model="form.cfg_direccion_principal"
                                type="text"
                                class="mt-1 block w-full text-sm"
                                placeholder="Campus Cota Cota, La Paz"
                            />
                            <InputError :message="form.errors.cfg_direccion_principal" class="mt-1.5" />
                        </div>
                    </div>

                    <div>
                        <InputLabel for="cfg_copyright" value="Texto de Copyright / Pie de Página" />
                        <TextInput
                            id="cfg_copyright"
                            v-model="form.cfg_copyright"
                            type="text"
                            class="mt-1 block w-full text-sm"
                        />
                        <InputError :message="form.errors.cfg_copyright" class="mt-1.5" />
                    </div>
                </div>

                <!-- Botón Guardar -->
                <div class="flex items-center justify-end pt-4">
                    <PrimaryButton type="submit" :disabled="form.processing" class="cursor-pointer">
                        Guardar Configuración Institucional
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
