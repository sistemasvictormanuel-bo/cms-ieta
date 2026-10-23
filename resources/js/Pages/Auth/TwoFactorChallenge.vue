<script setup>
import { nextTick, ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthSplitLayout from './AuthSplitLayout.vue';

const recovery = ref(false);

const form = useForm({
    code: '',
    recovery_code: '',
});

const recoveryCodeInput = ref(null);
const codeInput = ref(null);

const toggleRecovery = async () => {
    recovery.value = !recovery.value;

    await nextTick();

    if (recovery.value) {
        recoveryCodeInput.value?.focus();
        form.code = '';
    } else {
        codeInput.value?.focus();
        form.recovery_code = '';
    }
};

const submit = () => {
    form.post(route('two-factor.login'));
};
</script>

<template>
    <Head title="Verificación en Dos Pasos (TOTP) — IETA" />

    <AuthSplitLayout>
        <!-- CABECERA DE SEGURIDAD 2FA -->
        <div>
            <!-- BADGE DE SEGURIDAD -->
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#35449F]/10 text-[#35449F] text-[12px] font-black tracking-wide uppercase mb-3.5">
                <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                </svg>
                <span>Seguridad Reforzada (2FA / TOTP)</span>
            </div>

            <!-- TÍTULO DINÁMICO -->
            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                {{ !recovery ? 'Verificación de Seguridad' : 'Código de Recuperación' }}
            </h2>

            <!-- DESCRIPCIÓN EXPLICATIVA -->
            <p class="text-[15px] text-slate-600 font-medium mt-1.5 leading-relaxed">
                {{ !recovery
                    ? 'Tu cuenta está protegida. Ingresa el código temporal de 6 dígitos generado por tu aplicación autenticadora.'
                    : 'Ingresa uno de tus códigos de recuperación de emergencia para autenticar tu acceso al sistema.'
                }}
            </p>
        </div>

        <!-- FORMULARIO DE VERIFICACIÓN -->
        <form class="mt-8 space-y-6" @submit.prevent="submit">
            <!-- CASO 1: CÓDIGO TOTP ESTÁNDAR (6 DÍGITOS) -->
            <div v-if="!recovery" class="space-y-3">
                <div class="flex items-center justify-between">
                    <label for="code" class="block text-[15.5px] font-bold text-slate-900">
                        Código de Autenticación
                    </label>
                    <span class="text-xs font-semibold text-slate-500">
                        Google / Microsoft Authenticator
                    </span>
                </div>

                <div class="relative">
                    <input
                        id="code"
                        ref="codeInput"
                        v-model="form.code"
                        type="text"
                        inputmode="numeric"
                        maxlength="7"
                        autofocus
                        autocomplete="one-time-code"
                        placeholder="000 000"
                        class="w-full text-center font-mono tracking-[0.35em] sm:tracking-[0.5em] text-2xl font-black rounded-2xl border border-slate-300 bg-slate-50/70 py-4 px-4 text-slate-900 placeholder:text-slate-300 focus:border-[#35449F] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[#35449F]/10 transition duration-150 shadow-2xs"
                        :class="{ 'border-red-400 bg-red-50/30 focus:border-red-500 focus:ring-red-500/10': form.errors.code }"
                    />
                </div>

                <!-- ERROR CODE -->
                <p v-if="form.errors.code" class="mt-2 text-[13.5px] font-semibold text-red-600 flex items-center gap-1.5">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>{{ form.errors.code }}</span>
                </p>

                <!-- CALLOUT INFORMATIVO TOTP -->
                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80 text-[12.5px] text-slate-600 flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-[#35449F] shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="leading-relaxed">
                        Abre tu teléfono, consulta el código temporal de 6 cifras generado para <strong>IETA UMSA</strong> e introdúcelo antes de que expire el temporizador.
                    </span>
                </div>
            </div>

            <!-- CASO 2: CÓDIGO DE RECUPERACIÓN DE EMERGENCIA -->
            <div v-else class="space-y-3">
                <label for="recovery_code" class="block text-[15.5px] font-bold text-slate-900">
                    Código de Recuperación de Emergencia
                </label>

                <div class="relative">
                    <input
                        id="recovery_code"
                        ref="recoveryCodeInput"
                        v-model="form.recovery_code"
                        type="text"
                        autocomplete="one-time-code"
                        placeholder="xxxxx-xxxxx"
                        class="w-full text-center font-mono tracking-widest text-lg font-bold rounded-2xl border border-slate-300 bg-slate-50/70 py-4 px-4 text-slate-900 placeholder:text-slate-300 focus:border-[#35449F] focus:bg-white focus:outline-none focus:ring-4 focus:ring-[#35449F]/10 transition duration-150 shadow-2xs"
                        :class="{ 'border-red-400 bg-red-50/30 focus:border-red-500 focus:ring-red-500/10': form.errors.recovery_code }"
                    />
                </div>

                <!-- ERROR RECOVERY CODE -->
                <p v-if="form.errors.recovery_code" class="mt-2 text-[13.5px] font-semibold text-red-600 flex items-center gap-1.5">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>{{ form.errors.recovery_code }}</span>
                </p>

                <!-- CALLOUT INFORMATIVO RECOVERY -->
                <div class="p-3.5 bg-amber-50 rounded-xl border border-amber-200/80 text-[12.5px] text-amber-800 flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span class="leading-relaxed">
                        Cada clave de emergencia es de un solo uso. Si agotaste tus códigos o no tienes acceso, solicita asistencia al administrador del IETA.
                    </span>
                </div>
            </div>

            <!-- BOTÓN PRINCIPAL DE ENVÍO -->
            <div class="pt-2">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full h-11 px-4 rounded-xl bg-[#14161C] hover:bg-[#20242D] active:scale-[0.99] text-white text-[14.5px] font-bold shadow-xs hover:shadow-sm transition duration-150 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed"
                >
                    <svg
                        v-if="form.processing"
                        class="w-4 h-4 shrink-0 animate-spin text-white"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    <span>{{ form.processing ? 'Verificando código...' : 'Confirmar y Acceder' }}</span>
                </button>
            </div>

            <!-- CONMUTADOR ENTRE TOTP Y RECUPERACIÓN -->
            <div class="pt-1 text-center">
                <button
                    type="button"
                    class="text-[13.5px] font-bold text-[#35449F] hover:text-[#25327a] hover:underline cursor-pointer transition-colors inline-flex items-center gap-1.5"
                    @click.prevent="toggleRecovery"
                >
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path v-if="!recovery" stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                        <path v-else stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    <span>
                        {{ !recovery ? '¿No tienes acceso a tu app? Usar clave de recuperación' : 'Volver a autenticación por código TOTP móvil' }}
                    </span>
                </button>
            </div>
        </form>

        <!-- FOOTER: RETORNO AL INICIO DE SESIÓN -->
        <template #footer>
            <div class="flex items-center justify-between text-[13.5px] font-semibold text-slate-600">
                <Link :href="route('login')" class="hover:text-[#35449F] transition flex items-center gap-1.5">
                    <span>← Cancelar y volver al Login</span>
                </Link>
                <span class="text-slate-400 text-xs">IETA UMSA © 2026</span>
            </div>
        </template>
    </AuthSplitLayout>
</template>
