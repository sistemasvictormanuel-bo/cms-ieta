<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import LottieAnimation from '@/Components/LottieAnimation.vue';

const page = usePage();

const enabling = ref(false);
const confirming = ref(false);
const disabling = ref(false);
const qrCode = ref(null);
const setupKey = ref(null);
const recoveryCodes = ref([]);
const errorMessage = ref('');
const copiedKey = ref(false);
const copiedCodes = ref(false);

const confirmationForm = useForm({
    code: '',
});

const user = computed(() => page.props.auth?.user);

const twoFactorEnabled = computed(
    () => !enabling.value && !!page.props.auth?.user?.two_factor_enabled,
);

const twoFactorConfirmed = computed(
    () => !!page.props.auth?.user?.two_factor_confirmed,
);

const roleName = computed(() => {
    if (user.value?.is_admin) return 'Administrador';
    if (user.value?.is_autor) return 'Autor / Investigador';
    const roles = user.value?.roles || [];
    return roles.length > 0 ? roles[0] : 'Usuario';
});

onMounted(() => {
    // Si ya tiene clave en la BD pero no se ha confirmado el código TOTP
    if (twoFactorEnabled.value && !twoFactorConfirmed.value) {
        confirming.value = true;
        showQrCode();
        showSetupKey();
    } else if (twoFactorConfirmed.value) {
        showRecoveryCodes();
    }
});

watch(twoFactorEnabled, (val) => {
    if (!val) {
        confirmationForm.reset();
        confirmationForm.clearErrors();
        qrCode.value = null;
        setupKey.value = null;
        confirming.value = false;
        recoveryCodes.value = [];
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
        ]),
        onError: (err) => {
            console.error('Error enabling 2FA:', err);
            errorMessage.value = 'No se pudo generar la clave de autenticación. Intenta nuevamente.';
        },
        onFinish: () => {
            enabling.value = false;
            confirming.value = true;
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
            errorMessage.value = 'Error al cargar el código QR de seguridad.';
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
            recoveryCodes.value = response.data || [];
        })
        .catch(err => {
            console.error('Error fetching recovery codes:', err);
        });
};

const confirmTwoFactorAuthentication = () => {
    errorMessage.value = '';

    confirmationForm.post(route('two-factor.confirm'), {
        errorBag: 'confirmTwoFactorAuthentication',
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
            errorMessage.value = 'Error al reiniciar la configuración.';
        },
        onFinish: () => {
            disabling.value = false;
        },
    });
};

const copySetupKey = () => {
    if (!setupKey.value) return;
    navigator.clipboard.writeText(setupKey.value).then(() => {
        copiedKey.value = true;
        setTimeout(() => { copiedKey.value = false; }, 2500);
    });
};

const copyAllRecoveryCodes = () => {
    if (!recoveryCodes.value.length) return;
    const text = recoveryCodes.value.join('\n');
    navigator.clipboard.writeText(text).then(() => {
        copiedCodes.value = true;
        setTimeout(() => { copiedCodes.value = false; }, 2500);
    });
};
</script>

<template>
    <div class="min-h-screen w-full bg-[#EAEEF3] flex items-center justify-center p-4 sm:p-6 lg:p-10 font-sans text-slate-800 antialiased selection:bg-[#35449F] selection:text-white">
        <Head title="Configuración de Seguridad 2FA — IETA" />

        <!-- CANVAS PRINCIPAL ELEGANTE EN FONDO CLARO -->
        <div class="w-full max-w-3xl bg-white rounded-[28px] sm:rounded-[36px] shadow-[0_20px_50px_-12px_rgba(0,0,0,0.08)] border border-slate-200/80 overflow-hidden p-6 sm:p-10 lg:p-12 relative">
            
            <!-- BARRA SUPERIOR: BRANDING INSTITUCIONAL Y USUARIO -->
            <header class="flex flex-wrap items-center justify-between pb-6 mb-8 border-b border-slate-100 gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="size-11 rounded-2xl bg-gradient-to-br from-[#35449F] to-[#253278] shadow-sm flex items-center justify-center text-white font-black text-sm tracking-wider">
                        IETA
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-black tracking-widest uppercase text-slate-900">SISTEMA IETA</span>
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 text-[#35449F] border border-blue-200/60 uppercase">
                                {{ roleName }}
                            </span>
                        </div>
                        <p class="text-xs font-semibold text-slate-500">Instituto de Estadística Teórica y Aplicada</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="text-right hidden sm:block">
                        <span class="text-xs font-bold text-slate-800 block leading-tight">{{ user?.name }}</span>
                        <span class="text-[11px] font-medium text-slate-500 block leading-tight">{{ user?.email }}</span>
                    </div>

                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-red-600 px-3 py-2 rounded-xl border border-slate-200 hover:border-red-200 hover:bg-red-50/60 transition-colors cursor-pointer"
                        title="Cerrar sesión actual"
                    >
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                        </svg>
                        <span>Cerrar Sesión</span>
                    </Link>
                </div>
            </header>

            <!-- MENSAJE DE ERROR GLOBAL SI OCURRE -->
            <div
                v-if="errorMessage"
                class="mb-6 rounded-2xl bg-red-50 border border-red-200 p-4 text-[14px] font-semibold text-red-800 flex items-center gap-3"
            >
                <div class="size-8 rounded-xl bg-red-100 flex items-center justify-center text-red-600 shrink-0">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <span>{{ errorMessage }}</span>
            </div>

            <!-- ======================================================== -->
            <!-- CASO 1: 2FA ACTIVADO Y CONFIRMADO (ESTADO FINAL EXITOSO) -->
            <!-- ======================================================== -->
            <section v-if="twoFactorConfirmed" class="space-y-6">
                <!-- Banner de Éxito -->
                <div class="rounded-3xl bg-emerald-50/90 border border-emerald-200/90 p-6 sm:p-8 text-center space-y-4">
                    <div class="flex justify-center">
                        <LottieAnimation
                            src="/animations/success-check.json"
                            :width="110"
                            :height="110"
                            :loop="false"
                        />
                    </div>

                    <div>
                        <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                            ¡Autenticación 2FA Activada!
                        </h2>
                        <p class="text-sm sm:text-base text-slate-600 font-medium max-w-lg mx-auto mt-1 leading-relaxed">
                            Tu cuenta ahora está protegida con seguridad de dos factores. Cada vez que inicies sesión se te solicitará el código temporal de tu aplicación.
                        </p>
                    </div>
                </div>

                <!-- Códigos de Recuperación de Emergencia -->
                <div v-if="recoveryCodes.length > 0" class="rounded-2xl border border-slate-200 bg-slate-50/80 p-5 sm:p-6 space-y-3">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <svg class="size-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <h3 class="text-xs font-black uppercase tracking-wider text-slate-900">
                                Códigos de Recuperación de Emergencia
                            </h3>
                        </div>

                        <button
                            type="button"
                            @click="copyAllRecoveryCodes"
                            class="inline-flex items-center gap-1.5 text-xs font-bold text-[#35449F] hover:text-[#253278] transition-colors cursor-pointer"
                        >
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75" />
                            </svg>
                            <span>{{ copiedCodes ? '¡Códigos Copiados!' : 'Copiar Todos' }}</span>
                        </button>
                    </div>

                    <p class="text-xs text-slate-600 leading-relaxed">
                        Guarda estos códigos en un lugar seguro. Si pierdes el acceso a tu teléfono móvil, te permitirán entrar a tu cuenta.
                    </p>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 p-3.5 bg-white rounded-xl border border-slate-200/90 font-mono text-xs font-bold text-slate-800 text-center select-all">
                        <div v-for="c in recoveryCodes" :key="c" class="p-2 bg-slate-50 rounded-lg border border-slate-100">
                            {{ c }}
                        </div>
                    </div>
                </div>

                <!-- Botón Principal de Acceso al Panel -->
                <div class="pt-4">
                    <Link
                        :href="route('dashboard')"
                        class="w-full inline-flex items-center justify-center gap-2.5 px-6 py-4 rounded-2xl bg-[#35449F] hover:bg-[#283584] text-white text-base font-black shadow-xl shadow-[#35449F]/20 transition-all cursor-pointer transform hover:-translate-y-0.5"
                    >
                        <span>Ingresar a mi Panel de Trabajo</span>
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </Link>
                </div>
            </section>

            <!-- ======================================================== -->
            <!-- CASO 2: EN PROCESO DE ESCANEO Y CONFIRMACIÓN DE CÓDIGO    -->
            <!-- ======================================================== -->
            <section v-else-if="twoFactorEnabled || confirming" class="space-y-8">
                <!-- TÍTULO Y DESCRIPCIÓN -->
                <div class="text-center space-y-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                        <span class="size-2 rounded-full bg-amber-500 animate-ping"></span>
                        Paso de Activación en Curso
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                        Vincula tu Aplicación de Autenticación
                    </h2>
                    <p class="text-sm sm:text-base text-slate-600 font-medium max-w-lg mx-auto">
                        Sigue estos dos sencillos pasos para registrar tu dispositivo y proteger tu cuenta institucional.
                    </p>
                </div>

                <!-- PASO 1: ESCANEAR QR -->
                <div class="rounded-3xl border border-slate-200 bg-slate-50/70 p-6 sm:p-8 space-y-5">
                    <div class="flex items-center gap-3">
                        <div class="size-7 rounded-full bg-[#35449F] text-white flex items-center justify-center font-black text-xs">
                            1
                        </div>
                        <h3 class="text-base font-black text-slate-900 tracking-tight">
                            Escanea el código QR con tu móvil
                        </h3>
                    </div>

                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Abre tu aplicación de autenticación favorita (<strong class="text-slate-800">Google Authenticator</strong>, <strong class="text-slate-800">Microsoft Authenticator</strong> o <strong class="text-slate-800">Authy</strong>) y enfoca este código:
                    </p>

                    <!-- CONTENEDOR DEL CÓDIGO QR -->
                    <div class="flex flex-col items-center justify-center pt-2 pb-1">
                        <div
                            v-if="qrCode"
                            class="bg-white p-4 sm:p-5 rounded-2xl border-2 border-slate-200/90 shadow-sm flex items-center justify-center [&>svg]:w-48 [&>svg]:h-48 sm:[&>svg]:w-56 sm:[&>svg]:h-56"
                            v-html="qrCode"
                        />
                        <div
                            v-else
                            class="w-48 h-48 sm:w-56 sm:h-56 rounded-2xl bg-white border-2 border-slate-200/90 flex flex-col items-center justify-center gap-3 text-slate-400"
                        >
                            <svg class="size-8 animate-spin text-[#35449F]" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-xs font-semibold text-slate-500">Generando código QR...</span>
                        </div>
                    </div>

                    <!-- CLAVE MANUAL DE RESPALDO -->
                    <div v-if="setupKey" class="pt-3 border-t border-slate-200/80 text-center">
                        <p class="text-xs text-slate-500 font-medium">
                            ¿No puedes escanear el código? Ingresa esta clave secreta manualmente:
                        </p>
                        <div class="mt-2 inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-xs font-mono font-bold text-slate-800">
                            <span>{{ setupKey }}</span>
                            <button
                                type="button"
                                @click="copySetupKey"
                                class="text-[#35449F] hover:text-[#253278] font-sans font-bold cursor-pointer ml-1"
                            >
                                {{ copiedKey ? '¡Copiada!' : 'Copiar' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- PASO 2: CONFIRMAR CÓDIGO DE 6 DÍGITOS -->
                <div class="rounded-3xl border border-slate-200 bg-white p-6 sm:p-8 space-y-5 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="size-7 rounded-full bg-[#35449F] text-white flex items-center justify-center font-black text-xs">
                            2
                        </div>
                        <h3 class="text-base font-black text-slate-900 tracking-tight">
                            Introduce el código de 6 dígitos
                        </h3>
                    </div>

                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Ingresa el código numérico temporal que aparece en tu aplicación de autenticación para validar la sincronización:
                    </p>

                    <form class="space-y-5" @submit.prevent="confirmTwoFactorAuthentication">
                        <div class="flex flex-col items-center">
                            <input
                                id="code"
                                v-model="confirmationForm.code"
                                type="text"
                                inputmode="numeric"
                                pattern="[0-9]*"
                                maxlength="6"
                                placeholder="000000"
                                autocomplete="one-time-code"
                                required
                                autofocus
                                class="w-full max-w-xs text-center font-mono text-3xl font-black tracking-[0.35em] py-3.5 px-4 rounded-2xl border-2 border-slate-300 focus:border-[#35449F] focus:outline-none focus:ring-4 focus:ring-[#35449F]/10 text-slate-900 placeholder:text-slate-300 transition duration-150 shadow-2xs"
                                :class="{ 'border-red-400 focus:border-red-500 focus:ring-red-500/10': confirmationForm.errors.code }"
                            />
                            <p v-if="confirmationForm.errors.code" class="mt-2 text-xs sm:text-sm font-semibold text-red-600">
                                {{ confirmationForm.errors.code }}
                            </p>
                        </div>

                        <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-2">
                            <button
                                type="submit"
                                :disabled="confirmationForm.processing || confirmationForm.code.length < 6"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-xl bg-[#35449F] hover:bg-[#283584] text-white text-sm font-bold shadow-lg shadow-[#35449F]/20 disabled:opacity-50 disabled:cursor-not-allowed transition duration-150 cursor-pointer"
                            >
                                <svg v-if="confirmationForm.processing" class="size-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>{{ confirmationForm.processing ? 'Verificando...' : 'Confirmar y Activar 2FA' }}</span>
                            </button>

                            <button
                                type="button"
                                :disabled="disabling"
                                @click="disableTwoFactorAuthentication"
                                class="w-full sm:w-auto px-5 py-3.5 rounded-xl border border-slate-200 text-slate-600 hover:text-slate-900 hover:bg-slate-50 text-xs sm:text-sm font-semibold transition-colors cursor-pointer"
                            >
                                {{ disabling ? 'Cancelando...' : 'Cancelar y Reiniciar' }}
                            </button>
                        </div>
                    </form>
                </div>
            </section>

            <!-- ======================================================== -->
            <!-- CASO 3: ESTADO INICIAL (AÚN NO HA COMENZADO ACTIVACIÓN)  -->
            <!-- ======================================================== -->
            <section v-else class="space-y-8 text-center">
                <!-- ÍCONO PRINCIPAL HERO ANIMADO CON LOTTIE -->
                <div class="size-24 rounded-3xl bg-indigo-50/80 border border-indigo-200/70 text-[#35449F] mx-auto flex items-center justify-center shadow-xs p-2">
                    <LottieAnimation
                        src="/animations/security-auth.json"
                        :width="80"
                        :height="80"
                        :loop="true"
                    />
                </div>

                <!-- TÍTULO Y DESCRIPCIÓN -->
                <div class="space-y-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-[#35449F] border border-blue-200/60">
                        Paso de Seguridad Obligatorio
                    </span>
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black text-slate-900 tracking-tight">
                        Autenticación en Dos Pasos (2FA)
                    </h1>
                    <p class="text-sm sm:text-base text-slate-600 font-medium max-w-xl mx-auto leading-relaxed">
                        Por motivos de seguridad y resguardo de la plataforma institucional del IETA, todas las cuentas deben configurar la autenticación de dos factores antes de acceder a su respectivo panel.
                    </p>
                </div>

                <!-- PASOS EN TARJETAS LIMPIAS -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-left">
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                        <span class="size-7 rounded-xl bg-white border border-slate-200 text-[#35449F] font-black text-xs flex items-center justify-center mb-2 shadow-2xs">1</span>
                        <h4 class="text-xs font-black text-slate-900 uppercase tracking-wide">Instala la App</h4>
                        <p class="text-xs text-slate-600 mt-1">Google Authenticator, Microsoft Authenticator o Authy en tu móvil.</p>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                        <span class="size-7 rounded-xl bg-white border border-slate-200 text-[#35449F] font-black text-xs flex items-center justify-center mb-2 shadow-2xs">2</span>
                        <h4 class="text-xs font-black text-slate-900 uppercase tracking-wide">Escanea el QR</h4>
                        <p class="text-xs text-slate-600 mt-1">Generaremos un código QR único para sincronizar con tu móvil.</p>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                        <span class="size-7 rounded-xl bg-white border border-slate-200 text-[#35449F] font-black text-xs flex items-center justify-center mb-2 shadow-2xs">3</span>
                        <h4 class="text-xs font-black text-slate-900 uppercase tracking-wide">Verifica y Entra</h4>
                        <p class="text-xs text-slate-600 mt-1">Introduce el código de 6 dígitos y accede a tu panel.</p>
                    </div>
                </div>

                <!-- BOTÓN PRINCIPAL DE ACTIVACIÓN -->
                <div class="pt-2">
                    <button
                        type="button"
                        :disabled="enabling"
                        @click="enableTwoFactorAuthentication"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-3 px-8 py-4 rounded-2xl bg-[#35449F] hover:bg-[#283584] text-white text-base font-black shadow-xl shadow-[#35449F]/25 transition duration-150 cursor-pointer disabled:opacity-50 transform hover:-translate-y-0.5"
                    >
                        <svg v-if="enabling" class="size-5 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg v-else class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span>{{ enabling ? 'Generando Código QR...' : 'Comenzar Configuración 2FA' }}</span>
                    </button>
                </div>
            </section>

            <!-- FOOTER DISCRETO -->
            <footer class="mt-10 pt-6 border-t border-slate-100 text-center text-xs font-medium text-slate-400">
                Instituto de Estadística Teórica y Aplicada • Universidad Mayor de San Andrés
            </footer>
        </div>
    </div>
</template>
