<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    variant: {
        type: String,
        default: 'default', // 'default' | 'security'
    },
});

const page = usePage();

const logoUrl = computed(() => {
    return page.props.institucion?.logo_blanco_url || page.props.institucion?.logo_url || null;
});
</script>

<template>
    <div class="relative bg-gradient-to-br from-[#1A267D] via-[#263799] to-[#35449F] text-white overflow-hidden select-none flex flex-col justify-between">
        <!-- PATRÓN SUTIL DE PUNTOS MATRICIALES ESTADÍSTICOS (FONDO PURO SVG) -->
        <div class="absolute inset-0 pointer-events-none opacity-20">
            <svg class="w-full h-full text-white" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="auth-grid-dots" width="28" height="28" patternUnits="userSpaceOnUse">
                        <circle cx="3" cy="3" r="1.5" fill="currentColor" />
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#auth-grid-dots)" />
            </svg>
            <div class="absolute inset-0 bg-radial from-transparent via-[#263799]/40 to-[#1A267D]"></div>
        </div>

        <!-- RESPLANDORES ORBITALES SUAVES -->
        <div class="absolute -top-24 -left-24 size-80 rounded-full bg-blue-400/15 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-12 size-80 rounded-full bg-indigo-300/15 blur-3xl pointer-events-none"></div>

        <!-- ============================================================ -->
        <!-- ONDA ORGÁNICA / NUBE VERTICAL PARA ESCRITORIO (md: y superior) -->
        <!-- ============================================================ -->
        <div class="hidden md:block absolute inset-y-0 right-0 h-full w-24 lg:w-32 pointer-events-none z-10">
            <svg
                class="w-full h-full"
                viewBox="0 0 120 600"
                preserveAspectRatio="none"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
            >
                <!-- Capa 1: Sombra suave posterior -->
                <path
                    d="M 120 0 L 60 0 C 20 40, 10 110, 40 160 C 5 210, 0 280, 25 320 C 5 370, 10 440, 35 480 C 15 530, 25 580, 50 600 L 120 600 Z"
                    fill="rgba(255, 255, 255, 0.14)"
                />
                <!-- Capa 2: Resplandor intermedio -->
                <path
                    d="M 120 0 L 75 0 C 40 45, 30 110, 55 160 C 25 215, 20 280, 45 325 C 25 375, 30 440, 55 485 C 35 535, 45 580, 70 600 L 120 600 Z"
                    fill="rgba(255, 255, 255, 0.25)"
                />
                <!-- Capa 3: Nube frontal sólida blanca -->
                <path
                    d="M 120 0 L 90 0 C 55 50, 45 115, 70 165 C 40 220, 35 285, 60 330 C 40 380, 45 445, 70 490 C 50 540, 60 580, 85 600 L 120 600 Z"
                    fill="#FFFFFF"
                />
            </svg>
        </div>

        <!-- ============================================================ -->
        <!-- ONDA ORGÁNICA / NUBE HORIZONTAL PARA MÓVIL (< md)            -->
        <!-- ============================================================ -->
        <div class="block md:hidden absolute inset-x-0 bottom-0 w-full h-10 sm:h-14 pointer-events-none z-10">
            <svg
                class="w-full h-full"
                viewBox="0 0 600 80"
                preserveAspectRatio="none"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
            >
                <!-- Capa 1: Sombra suave -->
                <path
                    d="M 0 80 L 0 50 C 40 15, 110 10, 160 35 C 210 5, 280 0, 320 25 C 370 5, 440 10, 480 30 C 530 10, 580 20, 600 45 L 600 80 Z"
                    fill="rgba(255, 255, 255, 0.14)"
                />
                <!-- Capa 2: Intermedia -->
                <path
                    d="M 0 80 L 0 60 C 45 30, 110 25, 160 45 C 215 20, 280 15, 325 35 C 375 20, 440 25, 485 45 C 535 25, 580 35, 600 58 L 600 80 Z"
                    fill="rgba(255, 255, 255, 0.25)"
                />
                <!-- Capa 3: Nube frontal sólida blanca -->
                <path
                    d="M 0 80 L 0 70 C 50 45, 115 38, 165 58 C 220 32, 285 28, 330 48 C 380 32, 445 38, 490 58 C 540 38, 580 48, 600 70 L 600 80 Z"
                    fill="#FFFFFF"
                />
            </svg>
        </div>

        <!-- ============================================================ -->
        <!-- CONTENIDO: VERSIÓN ESCRITORIO (md:flex)                      -->
        <!-- ============================================================ -->
        <div class="hidden md:flex relative z-20 flex-col justify-between h-full p-8 lg:p-12 pr-16 lg:pr-24">
            <!-- Header superior -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <span class="text-xs font-black tracking-widest uppercase text-white/90">IETA • UMSA</span>
                </div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-[11px] font-bold text-white shadow-2xs">
                    <span class="size-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>v1.0 • 2026</span>
                </div>
            </div>

            <!-- Centro: Emblema y Títulos -->
            <div class="my-auto py-8 text-center space-y-4">
                <!-- Badge circular con Logo o Ícono institucional -->
                <div class="size-24 rounded-full bg-white/95 backdrop-blur-md p-3.5 border-2 border-white/40 shadow-[0_12px_35px_rgba(0,0,0,0.22)] flex items-center justify-center mx-auto transition hover:scale-105 duration-300">
                    <!-- Si hay logo subido en configuraciones (y no es variante seguridad pura) -->
                    <img
                        v-if="logoUrl && variant !== 'security'"
                        :src="logoUrl"
                        alt="Logo Institucional IETA"
                        class="w-full h-full object-contain"
                    />
                    <!-- Ícono Variante Seguridad (2FA) -->
                    <svg v-else-if="variant === 'security'" class="size-11 text-[#1E2B85]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                    </svg>
                    <!-- Fallback: Ícono referente a Revista / Libro Científico / PC -->
                    <svg v-else class="size-11 text-[#1E2B85]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>

                <div>
                    <span class="text-xs font-bold uppercase tracking-widest text-blue-200 block">
                        {{ variant === 'security' ? 'Protección Criptográfica' : 'Bienvenido al' }}
                    </span>
                    <h2 class="text-2xl lg:text-[26px] xl:text-[28px] font-black tracking-tight text-white mt-1.5 leading-snug">
                        {{ variant === 'security' ? 'Verificación 2FA' : 'Instituto de Estadística Teórica y Aplicada' }}
                    </h2>
                </div>

                <p class="text-[13.5px] text-blue-100/90 leading-relaxed max-w-sm mx-auto font-medium">
                    {{ variant === 'security'
                        ? 'Acceso blindado mediante algoritmos de clave temporal única para salvaguardar información científica confidencial.'
                        : 'Plataforma institucional de gestión científica, académica y divulgación editorial.'
                    }}
                </p>
            </div>

            <!-- Footer inferior institucional -->
            <div class="pt-4 border-t border-white/15 text-[11.5px] text-blue-200/80 font-medium flex items-center justify-between">
                <span>Facultad de Ciencias Puras y Naturales</span>
                <span class="font-bold">UMSA</span>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- CONTENIDO: VERSIÓN MÓVIL (< md)                              -->
        <!-- ============================================================ -->
        <div class="flex md:hidden relative z-20 flex-col items-center text-center pt-8 pb-14 px-6 space-y-2">
            <div class="size-16 rounded-full bg-white/95 backdrop-blur-md p-2.5 border-2 border-white/40 shadow-md flex items-center justify-center">
                <img
                    v-if="logoUrl && variant !== 'security'"
                    :src="logoUrl"
                    alt="Logo Institucional"
                    class="w-full h-full object-contain"
                />
                <svg v-else-if="variant === 'security'" class="size-8 text-[#1E2B85]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                </svg>
                <svg v-else class="size-8 text-[#1E2B85]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
            <div>
                <span class="text-[11px] font-bold uppercase tracking-widest text-blue-200">
                    {{ variant === 'security' ? 'Autenticación 2FA' : 'Plataforma Institucional' }}
                </span>
                <h1 class="text-lg font-black text-white tracking-tight leading-tight max-w-xs mx-auto">
                    {{ variant === 'security' ? 'Verificación de Seguridad' : 'Instituto de Estadística Teórica y Aplicada' }}
                </h1>
            </div>
        </div>
    </div>
</template>
