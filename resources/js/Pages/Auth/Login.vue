<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthSplitLayout from './AuthSplitLayout.vue';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const togglePassword = () => {
    showPassword.value = !showPassword.value;
};

const submit = () => {
    form.transform((data) => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Iniciar Sesión — Panel Administrativo IETA" />

    <AuthSplitLayout>
        <!-- CABECERA DEL FORMULARIO -->
        <div>
            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                Iniciar Sesión
            </h2>
            <p class="text-[15px] text-slate-600 font-medium mt-1">
                Ingresa tus credenciales para acceder al Panel Administrativo
            </p>
        </div>

        <!-- MENSAJE DE ESTADO (FLASH STATUS) -->
        <div
            v-if="status"
            class="mt-4 rounded-xl bg-emerald-50 border border-emerald-200/80 p-3 text-[13.5px] font-semibold text-emerald-800 flex items-center gap-2"
        >
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ status }}</span>
        </div>

        <!-- FORMULARIO DE ACCESO -->
        <form class="mt-8 space-y-5" @submit.prevent="submit">
            <!-- CAMPO: CORREO ELECTRÓNICO -->
            <div>
                <label for="email" class="block text-[15.5px] font-bold text-slate-900 mb-1.5">
                    Correo Electrónico
                </label>
                <div class="relative">
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="tu-correo@ieta.edu.bo"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-[15.5px] font-medium text-slate-900 placeholder:text-slate-400 focus:border-[#35449F] focus:outline-none focus:ring-4 focus:ring-[#35449F]/10 transition duration-150 shadow-2xs"
                        :class="{ 'border-red-400 focus:border-red-500 focus:ring-red-500/10': form.errors.email }"
                    />
                </div>
                <p v-if="form.errors.email" class="mt-1.5 text-[13.5px] font-semibold text-red-600">
                    {{ form.errors.email }}
                </p>
            </div>

            <!-- CAMPO: CONTRASEÑA CON TOGGLE VISUAL (EYE ICON) -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="block text-[15.5px] font-bold text-slate-900">
                        Contraseña
                    </label>
                </div>
                <div class="relative">
                    <input
                        id="password"
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••••••"
                        class="w-full rounded-xl border border-slate-300 bg-white pl-4 pr-11 py-3 text-[15.5px] font-medium text-slate-900 placeholder:text-slate-400 focus:border-[#35449F] focus:outline-none focus:ring-4 focus:ring-[#35449F]/10 transition duration-150 shadow-2xs"
                        :class="{ 'border-red-400 focus:border-red-500 focus:ring-red-500/10': form.errors.password }"
                    />

                    <!-- Botón para ver/ocultar contraseña (Ojo de la referencia) -->
                    <button
                        type="button"
                        @click="togglePassword"
                        class="absolute right-3 top-1/2 -translate-y-1/2 p-1 text-slate-400 hover:text-slate-600 focus:outline-none transition cursor-pointer"
                        title="Alternar visibilidad"
                    >
                        <!-- Ícono Ojo Cerrado / Abierto -->
                        <svg v-if="!showPassword" class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                        </svg>
                        <svg v-else class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
                <p v-if="form.errors.password" class="mt-1.5 text-[13.5px] font-semibold text-red-600">
                    {{ form.errors.password }}
                </p>
            </div>

            <!-- FILA: RECORDAR SESIÓN & OLVIDÉ CONTRASEÑA -->
            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input
                        v-model="form.remember"
                        type="checkbox"
                        class="w-4 h-4 rounded-md border-slate-300 text-[#35449F] focus:ring-[#35449F]/30 cursor-pointer"
                    />
                    <span class="text-[13.5px] font-semibold text-slate-700">Recordar sesión</span>
                </label>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-[13.5px] font-bold text-[#35449F] hover:underline transition"
                >
                    ¿Olvidaste tu contraseña?
                </Link>
            </div>

            <!-- BOTÓN PRINCIPAL DE INICIO DE SESIÓN -->
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
                    <span>{{ form.processing ? 'Verificando acceso...' : 'Iniciar Sesión' }}</span>
                </button>
            </div>

            <!-- NOTA DE SEGURIDAD / CONDICIONES -->
            <p class="text-[12.5px] text-slate-500 text-center leading-normal pt-2">
                Acceso exclusivo para personal docente, administrativo e investigadores autorizados por el IETA.
            </p>
        </form>

        <!-- FOOTER: ENLACE AL PORTAL INSTITUCIONAL -->
        <template #footer>
            <div class="flex items-center justify-center gap-4 text-[13.5px] font-semibold text-slate-600">
                <Link href="/" class="hover:text-[#35449F] transition flex items-center gap-1.5">
                    <span>← Volver al Portal Académico</span>
                </Link>
                <span>•</span>
                <span class="text-slate-500">IETA UMSA © 2026</span>
            </div>
        </template>
    </AuthSplitLayout>
</template>
