<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import TwoFactorAuthenticationForm from '@/Pages/Profile/Partials/TwoFactorAuthenticationForm.vue';

const page = usePage();
const isEnabled = computed(() => !!page.props.auth?.user?.two_factor_confirmed);
</script>

<template>
    <div class="min-h-screen bg-slate-950 text-slate-100 py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden font-sans">
        <Head title="Configuración de Seguridad 2FA - IETA" />

        <!-- Background glow -->
        <div class="absolute -top-32 -left-32 w-96 h-96 bg-blue-600/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-indigo-600/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-4xl mx-auto relative z-10">
            <!-- Header bar with logo and logout -->
            <div class="flex items-center justify-between pb-8 mb-8 border-b border-slate-800">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-600/20 border border-blue-500/30 flex items-center justify-center text-blue-400 font-bold">
                        IETA
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-white tracking-tight">Portal de Autores e Investigadores</h2>
                        <p class="text-xs text-slate-400">Instituto de Estadística Teórica y Aplicada</p>
                    </div>
                </div>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="text-xs sm:text-sm text-slate-400 hover:text-white px-3 py-1.5 rounded-lg border border-slate-800 hover:bg-slate-800 transition-colors"
                >
                    Cerrar Sesión
                </Link>
            </div>

            <!-- Welcome / Warning Banner -->
            <div class="mb-8 rounded-2xl bg-gradient-to-r from-blue-950/60 to-indigo-950/40 border border-blue-800/40 p-6 backdrop-blur-xl">
                <div class="flex items-start space-x-4">
                    <div class="p-3 bg-blue-500/20 text-blue-400 rounded-xl mt-0.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-white">Configuración Obligatoria de Autenticación 2FA</h1>
                        <p class="text-slate-300 text-sm mt-1 leading-relaxed">
                            Por motivos de seguridad y resguardo de la producción científica en el IETA, todas las cuentas de autor deben activar la autenticación de dos factores antes de acceder a la plataforma.
                        </p>
                        <p class="text-xs text-slate-400 mt-2">
                            Necesitarás una aplicación de autenticación en tu teléfono móvil (como Google Authenticator, Microsoft Authenticator o Authy) para escanear el código QR.
                        </p>
                    </div>
                </div>
            </div>

            <!-- If already enabled, show success button -->
            <div v-if="isEnabled" class="mb-8 p-5 bg-emerald-950/50 border border-emerald-800/50 rounded-2xl flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold">✓</div>
                    <div>
                        <h4 class="text-sm font-semibold text-emerald-300">¡2FA Activado Correctamente!</h4>
                        <p class="text-xs text-emerald-400/80">Tu cuenta ahora está protegida. Ya puedes acceder al panel de tus artículos.</p>
                    </div>
                </div>
                <Link
                    :href="route('dashboard')"
                    class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs sm:text-sm font-semibold shadow-lg shadow-emerald-600/20 transition-all"
                >
                    Ir a mis Artículos →
                </Link>
            </div>

            <!-- Two Factor Component Card -->
            <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 sm:p-8 backdrop-blur-xl shadow-2xl">
                <TwoFactorAuthenticationForm :requires-confirmation="true" />
            </div>

            <div class="mt-8 text-center text-xs text-slate-500">
                Instituto de Estadística Teórica y Aplicada • UMSA • Bolivia
            </div>
        </div>
    </div>
</template>
