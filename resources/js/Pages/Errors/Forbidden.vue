<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    message: {
        type: String,
        default: 'No tienes los permisos necesarios para acceder a esta sección o recurso.',
    },
});
</script>

<template>
    <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col justify-center items-center px-6 py-12 relative overflow-hidden font-sans">
        <Head title="Acceso Denegado (403) - IETA" />

        <!-- Background glow -->
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-red-600/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-amber-600/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="w-full max-w-lg bg-slate-900/80 border border-slate-800 rounded-2xl p-8 backdrop-blur-xl shadow-2xl text-center relative z-10">
            <!-- Icon -->
            <div class="mx-auto w-16 h-16 rounded-2xl bg-red-500/10 border border-red-500/20 flex items-center justify-center text-red-400 mb-6 shadow-inner">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>

            <!-- Code & Title -->
            <span class="inline-block px-3 py-1 text-xs font-semibold uppercase tracking-wider text-red-400 bg-red-950/60 rounded-full border border-red-800/40 mb-3">
                Error 403 • Acceso Denegado
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight mb-3">
                Área Restringida
            </h1>

            <p class="text-slate-400 text-sm sm:text-base leading-relaxed mb-8">
                {{ message }}
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                <Link
                    :href="route('dashboard')"
                    class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold shadow-lg shadow-blue-600/20 transition-all focus:outline-none focus:ring-2 focus:ring-blue-500/50"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Volver al Inicio
                </Link>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-medium transition-all focus:outline-none focus:ring-2 focus:ring-slate-600"
                >
                    Cerrar Sesión
                </Link>
            </div>
        </div>

        <div class="mt-8 text-xs text-slate-500 text-center">
            Instituto de Estadística Teórica y Aplicada (IETA) • Seguridad y Control de Accesos
        </div>
    </div>
</template>
