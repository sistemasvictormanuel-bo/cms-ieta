<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import ActionSection from '@/Components/ActionSection.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    requiresConfirmation: {
        type: Boolean,
        default: true,
    },
});

const page = usePage();
const enabling = ref(false);
const confirming = ref(false);
const disabling = ref(false);
const qrCode = ref(null);
const setupKey = ref(null);
const recoveryCodes = ref([]);
const errorMessage = ref('');

const confirmationForm = useForm({
    code: '',
});

const twoFactorEnabled = computed(
    () => ! enabling.value && !! page.props.auth.user?.two_factor_enabled,
);

const twoFactorConfirmed = computed(
    () => !! page.props.auth.user?.two_factor_confirmed,
);

onMounted(() => {
    // Si ya existe clave secreta en la BD pero no se ha confirmado el código TOTP,
    // abrir automáticamente la vista de escaneo y confirmación de QR.
    if (twoFactorEnabled.value && ! twoFactorConfirmed.value) {
        confirming.value = true;
        showQrCode();
        showSetupKey();
    }
});

watch(twoFactorEnabled, () => {
    if (! twoFactorEnabled.value) {
        confirmationForm.reset();
        confirmationForm.clearErrors();
        qrCode.value = null;
        setupKey.value = null;
        confirming.value = false;
    }
});

const enableTwoFactorAuthentication = () => {
    enabling.value = true;
    errorMessage.value = '';

    router.post(route('two-factor.enable'), {}, {
        preserveScroll: true,
        onSuccess: () => Promise.all([
            showQrCode(),
            showSetupKey(),
            showRecoveryCodes(),
        ]),
        onError: (err) => {
            console.error('Error enabling 2FA:', err);
            errorMessage.value = 'No se pudo generar la clave 2FA. Por favor, intenta nuevamente.';
        },
        onFinish: () => {
            enabling.value = false;
            confirming.value = props.requiresConfirmation;
        },
    });
};

const showQrCode = () => {
    return axios.get(route('two-factor.qr-code'))
        .then(response => {
            qrCode.value = response.data.svg;
        })
        .catch(err => {
            console.error('Error fetching QR code:', err);
            errorMessage.value = 'Error al cargar el código QR.';
        });
};

const showSetupKey = () => {
    return axios.get(route('two-factor.secret-key'))
        .then(response => {
            setupKey.value = response.data.secretKey;
        })
        .catch(err => {
            console.error('Error fetching secret key:', err);
        });
};

const showRecoveryCodes = () => {
    return axios.get(route('two-factor.recovery-codes'))
        .then(response => {
            recoveryCodes.value = response.data;
        })
        .catch(err => {
            console.error('Error fetching recovery codes:', err);
        });
};

const confirmTwoFactorAuthentication = () => {
    errorMessage.value = '';

    confirmationForm.post(route('two-factor.confirm'), {
        errorBag: "confirmTwoFactorAuthentication",
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            confirming.value = false;
            qrCode.value = null;
            setupKey.value = null;
            showRecoveryCodes();
        },
        onError: (err) => {
            console.error('Error confirming 2FA code:', err);
        },
    });
};

const regenerateRecoveryCodes = () => {
    axios
        .post(route('two-factor.recovery-codes'))
        .then(() => showRecoveryCodes())
        .catch(err => {
            console.error('Error regenerating recovery codes:', err);
        });
};

const disableTwoFactorAuthentication = () => {
    disabling.value = true;
    errorMessage.value = '';

    router.delete(route('two-factor.disable'), {
        preserveScroll: true,
        onSuccess: () => {
            disabling.value = false;
            confirming.value = false;
            qrCode.value = null;
            setupKey.value = null;
            recoveryCodes.value = [];
        },
        onError: (err) => {
            console.error('Error disabling 2FA:', err);
            errorMessage.value = 'Error al cancelar o desactivar 2FA.';
        },
        onFinish: () => {
            disabling.value = false;
        },
    });
};
</script>

<template>
    <ActionSection>
        <template #title>
            Autenticación de Dos Factores (2FA)
        </template>

        <template #description>
            Protege tu cuenta con una capa de seguridad adicional mediante códigos TOTP temporales.
        </template>

        <template #content>
            <div class="space-y-4">
                <!-- MENSAJE DE ERROR GLOBAL SI APLICA -->
                <div v-if="errorMessage" class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium">
                    {{ errorMessage }}
                </div>

                <!-- ESTADO 1: TOTALMENTE ACTIVADO Y CONFIRMADO -->
                <div v-if="twoFactorEnabled && twoFactorConfirmed && !confirming" class="flex items-center gap-3 p-4 rounded-2xl bg-emerald-50 border border-emerald-200/80">
                    <div class="size-10 rounded-xl bg-emerald-100 flex items-center justify-center text-emerald-700 shrink-0">
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-[15.5px] font-black text-emerald-900">
                            La autenticación de dos factores está activada
                        </h3>
                        <p class="text-xs text-emerald-700 mt-0.5 font-medium">
                            Se solicitará un código TOTP cada vez que inicies sesión en la plataforma.
                        </p>
                    </div>
                </div>

                <!-- ESTADO 2: PENDIENTE DE CONFIRMACIÓN (SECRET GENERADO, ESCANEO QR REQUERIDO) -->
                <div v-else-if="twoFactorEnabled && (!twoFactorConfirmed || confirming)" class="flex items-center gap-3 p-4 rounded-2xl bg-amber-50 border border-amber-200/80">
                    <div class="size-10 rounded-xl bg-amber-100 flex items-center justify-center text-amber-700 shrink-0">
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-[15.5px] font-black text-amber-900">
                            Completa la activación de la autenticación de dos factores
                        </h3>
                        <p class="text-xs text-amber-700 mt-0.5 font-medium">
                            Escanea el código QR a continuación con tu aplicación (Google Authenticator, Microsoft Authenticator o Authy) e introduce el código de 6 dígitos.
                        </p>
                    </div>
                </div>

                <!-- ESTADO 3: NO ACTIVADO -->
                <div v-else class="flex items-center gap-3 p-4 rounded-2xl bg-slate-50 border border-slate-200">
                    <div class="size-10 rounded-xl bg-slate-200 flex items-center justify-center text-slate-700 shrink-0">
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-[15.5px] font-black text-slate-900">
                            La autenticación de dos factores no está activada
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5 font-medium">
                            Te recomendamos activarla para prevenir accesos no autorizados a tu cuenta del IETA.
                        </p>
                    </div>
                </div>

                <div v-if="!twoFactorEnabled" class="text-[13.5px] text-slate-600 leading-relaxed">
                    <p>
                        Al habilitar 2FA, se te pedirá un token seguro generado en tu aplicación móvil (Google Authenticator, Microsoft Authenticator o Authy) durante cada acceso.
                    </p>
                </div>

                <!-- FLUJO DE CONFIGURACIÓN CON CÓDIGO QR -->
                <div v-if="twoFactorEnabled && (!twoFactorConfirmed || confirming)">
                    <!-- Spinner de carga si el QR aún se está descargando -->
                    <div v-if="!qrCode" class="mt-4 p-8 bg-slate-50 rounded-2xl border border-slate-200 text-center space-y-3">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                        <p class="text-xs font-medium text-slate-600">Cargando código QR seguro...</p>
                    </div>

                    <!-- Contenedor del código QR -->
                    <div v-else class="mt-4 p-5 bg-slate-50 rounded-2xl border border-slate-200 space-y-4">
                        <p class="text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Paso 1: Escanea este código QR con tu móvil
                        </p>

                        <div class="p-3 inline-block bg-white rounded-2xl border border-slate-200 shadow-xs" v-html="qrCode" />

                        <!-- Clave manual alternativa -->
                        <div v-if="setupKey" class="text-xs text-slate-600 space-y-1">
                            <p class="font-bold text-slate-800">
                                ¿No puedes escanear el código QR? Usa esta clave secreta:
                            </p>
                            <div class="inline-block px-3 py-1.5 bg-slate-200 rounded-lg font-mono font-bold text-slate-800 text-[13px] select-all" v-html="setupKey" />
                        </div>

                        <!-- INPUT PARA CONFIRMAR EL CÓDIGO -->
                        <div class="pt-4 border-t border-slate-200 space-y-2">
                            <InputLabel for="code" value="Paso 2: Escribe el código de 6 dígitos generado por tu app *" class="font-bold text-slate-800 text-xs" />

                            <div class="flex flex-wrap items-center gap-3">
                                <TextInput
                                    id="code"
                                    v-model="confirmationForm.code"
                                    type="text"
                                    name="code"
                                    class="w-48 text-center font-mono tracking-widest text-lg font-black"
                                    inputmode="numeric"
                                    maxlength="7"
                                    placeholder="123456"
                                    autofocus
                                    autocomplete="one-time-code"
                                    @keyup.enter="confirmTwoFactorAuthentication"
                                />

                                <PrimaryButton
                                    type="button"
                                    :class="{ 'opacity-25': enabling || confirmationForm.processing }"
                                    :disabled="enabling || confirmationForm.processing"
                                    class="cursor-pointer"
                                    @click="confirmTwoFactorAuthentication"
                                >
                                    {{ confirmationForm.processing ? 'Verificando...' : 'Confirmar Código' }}
                                </PrimaryButton>
                            </div>

                            <InputError :message="confirmationForm.errors.code" class="mt-1" />
                        </div>
                    </div>
                </div>

                <!-- CÓDIGOS DE RECUPERACIÓN (SI ESTÁ CONFIRMADO) -->
                <div v-if="twoFactorEnabled && twoFactorConfirmed && recoveryCodes.length > 0 && !confirming" class="mt-4 p-5 bg-slate-50 rounded-2xl border border-slate-200 space-y-3">
                    <div class="flex items-center gap-2 text-amber-800 font-bold text-xs uppercase tracking-wider">
                        <svg class="size-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>Códigos de Recuperación de Emergencia</span>
                    </div>

                    <p class="text-xs text-slate-600 leading-relaxed">
                        Guarda estos códigos en un lugar seguro (ej. gestor de contraseñas). Te permitirán acceder a tu cuenta si pierdes tu dispositivo de autenticación.
                    </p>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 p-3 bg-white rounded-xl border border-slate-200 font-mono text-xs font-bold text-slate-800 text-center select-all">
                        <div v-for="code in recoveryCodes" :key="code" class="p-1.5 bg-slate-50 rounded-lg">
                            {{ code }}
                        </div>
                    </div>
                </div>

                <!-- ACCIONES Y BOTONES DE CONTROL -->
                <div class="pt-4 border-t border-slate-100 flex flex-wrap items-center gap-3">
                    <!-- BOTÓN ACTIVAR (CUANDO NO ESTÁ HABILITADO) -->
                    <div v-if="!twoFactorEnabled">
                        <PrimaryButton
                            type="button"
                            :class="{ 'opacity-25': enabling }"
                            :disabled="enabling"
                            class="cursor-pointer"
                            @click="enableTwoFactorAuthentication"
                        >
                            <svg class="size-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                            </svg>
                            {{ enabling ? 'Generando código...' : 'Activar Autenticación 2FA' }}
                        </PrimaryButton>
                    </div>

                    <!-- BOTONES CUANDO ESTÁ PENDIENTE DE CONFIRMAR -->
                    <div v-else-if="!twoFactorConfirmed || confirming" class="flex flex-wrap items-center gap-3">
                        <SecondaryButton
                            :class="{ 'opacity-25': disabling }"
                            :disabled="disabling"
                            class="cursor-pointer"
                            @click="disableTwoFactorAuthentication"
                        >
                            {{ disabling ? 'Cancelando...' : 'Cancelar y Reiniciar' }}
                        </SecondaryButton>
                    </div>

                    <!-- BOTONES CUANDO YA ESTÁ CONFIRMADO -->
                    <div v-else class="flex flex-wrap items-center gap-3">
                        <SecondaryButton
                            v-if="recoveryCodes.length > 0"
                            class="cursor-pointer"
                            @click="regenerateRecoveryCodes"
                        >
                            Regenerar Códigos de Recuperación
                        </SecondaryButton>

                        <SecondaryButton
                            v-if="recoveryCodes.length === 0"
                            class="cursor-pointer"
                            @click="showRecoveryCodes"
                        >
                            Mostrar Códigos de Recuperación
                        </SecondaryButton>

                        <DangerButton
                            :class="{ 'opacity-25': disabling }"
                            :disabled="disabling"
                            class="cursor-pointer"
                            @click="disableTwoFactorAuthentication"
                        >
                            {{ disabling ? 'Desactivando...' : 'Desactivar 2FA' }}
                        </DangerButton>
                    </div>
                </div>
            </div>
        </template>
    </ActionSection>
</template>
