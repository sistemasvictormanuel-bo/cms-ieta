<script setup>
import AuthHeroPanel from './AuthHeroPanel.vue';

defineProps({
    heroVariant: {
        type: String,
        default: 'default', // 'default' | 'security'
    },
});
</script>

<template>
    <div class="min-h-screen w-full bg-[#EAEEF3] flex items-center justify-center p-3 sm:p-6 lg:p-10 font-sans text-slate-800 antialiased selection:bg-[#35449F] selection:text-white">
        <!-- CANVAS FLOTANTE REDONDEADO CON ANCHO EXPANDIDO (max-w-6xl) -->
        <div class="w-full max-w-6xl bg-white rounded-[28px] sm:rounded-[36px] shadow-[0_25px_65px_-15px_rgba(20,30,80,0.14)] border border-slate-200/80 overflow-hidden grid grid-cols-1 md:grid-cols-2 min-h-[600px]">
            <!-- PANEL HERO (IZQUIERDA EN DESKTOP / ARRIBA EN MÓVIL) -->
            <AuthHeroPanel :variant="heroVariant" />

            <!-- PANEL DERECHO: FORMULARIO DE ACCESO -->
            <div class="relative flex flex-col justify-between p-7 sm:p-11 lg:p-14 bg-white">
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
