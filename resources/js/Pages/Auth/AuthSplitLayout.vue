<script setup>
import AuthHeroPanel from './AuthHeroPanel.vue';
</script>

<template>
    <div class="min-h-screen w-full bg-[#EAEEF3] flex items-center justify-center p-4 sm:p-6 lg:p-10 font-sans text-slate-800 antialiased selection:bg-[#35449F] selection:text-white">
        <!-- CANVAS FLOTANTE REDONDEADO (INSPIRADO EN LA CAPTURA) -->
        <div class="w-full max-w-5xl bg-white rounded-[28px] sm:rounded-[36px] shadow-[0_20px_50px_-12px_rgba(0,0,0,0.08)] border border-slate-200/80 overflow-hidden grid grid-cols-1 md:grid-cols-2 min-h-[620px]">
            <!-- PANEL HERO IZQUIERDO -->
            <AuthHeroPanel class="hidden md:flex" />

            <!-- PANEL DERECHO: FORMULARIO DE ACCESO -->
            <div class="relative flex flex-col justify-between p-8 sm:p-12 lg:p-14 bg-white">
                <div>
                    <slot />
                </div>

                <div class="mt-8 pt-4 border-t border-slate-100 text-center">
                    <slot name="footer" />
                </div>
            </div>
        </div>
    </div>
</template>
