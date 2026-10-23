<template>
    <div class="relative flex flex-col justify-between p-8 sm:p-10 lg:p-12 bg-slate-50/80 border-r border-slate-200/70 overflow-hidden select-none">
        <!-- PATRÓN DE FONDO: MATRIZ DE PUNTOS / HALFTONE ESTADÍSTICO -->
        <div class="absolute inset-0 pointer-events-none opacity-40">
            <svg class="w-full h-full text-slate-300" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="halftone-matrix" width="24" height="24" patternUnits="userSpaceOnUse">
                        <circle cx="3" cy="3" r="1.5" fill="currentColor" />
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#halftone-matrix)" />
            </svg>
            <!-- Degradado suave de superposición -->
            <div class="absolute inset-0 bg-radial from-transparent via-slate-50/60 to-slate-50"></div>
        </div>

        <!-- ONDAS DECORATIVAS ESTADÍSTICAS SUTILES (FONDO LATERAL) -->
        <div class="absolute -left-12 top-1/2 -translate-y-1/2 w-48 h-96 opacity-15 pointer-events-none flex items-center gap-1.5">
            <div class="w-2 h-16 bg-[#35449F] rounded-full animate-pulse"></div>
            <div class="w-2 h-32 bg-[#35449F] rounded-full"></div>
            <div class="w-2 h-48 bg-[#35449F] rounded-full animate-pulse"></div>
            <div class="w-2 h-64 bg-[#35449F] rounded-full"></div>
            <div class="w-2 h-40 bg-[#35449F] rounded-full"></div>
            <div class="w-2 h-20 bg-[#35449F] rounded-full animate-pulse"></div>
        </div>

        <!-- ========================================== -->
        <!-- HEADER SUPERIOR DEL HERO: BRANDING IETA    -->
        <!-- ========================================== -->
        <div class="relative z-10 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="size-10 rounded-xl bg-white border border-slate-200 shadow-xs flex items-center justify-center text-[#35449F]">
                    <!-- Ícono de Datos / Barras estadísticas -->
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13h2v7H3v-7zm6-5h2v12H9V8zm6-4h2v16h-2V4zm6 9h2v7h-2v-7z" />
                    </svg>
                </div>
                <div>
                    <span class="text-sm font-black tracking-widest uppercase text-slate-900">IETA</span>
                    <span class="text-[12px] font-bold text-slate-500 block -mt-0.5">SISTEMA CIENTÍFICO</span>
                </div>
            </div>

            <!-- Pill indicador -->
            <div class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-white/90 border border-slate-200 text-[12px] font-bold text-slate-700 shadow-2xs">
                <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>v2.0 • 2026</span>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TARJETA FLOTANTE CENTRAL (INSPIRACIÓN IMAGEN) -->
        <!-- ========================================== -->
        <div class="relative z-10 my-8">
            <div class="bg-white/95 backdrop-blur-md rounded-2xl p-5 sm:p-6 border border-slate-200/90 shadow-[0_15px_35px_-5px_rgba(0,0,0,0.06)] relative max-w-md">
                <!-- Pestañas simuladas superiores -->
                <div class="flex items-center gap-2 pb-3 mb-3 border-b border-slate-100">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50/80 text-[#35449F] text-[13px] font-bold border border-indigo-200/60 shadow-2xs">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Investigación Científica</span>
                    </div>

                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl text-slate-500 text-[13px] font-semibold hover:text-slate-700 transition">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                        </svg>
                        <span>Banco de Datos</span>
                    </div>
                </div>

                <!-- Contenido descriptivo -->
                <p class="text-[14px] text-slate-700 font-medium leading-relaxed">
                    Plataforma integral para la gestión y difusión del acervo científico, investigaciones estadísticas, repositorio documental y acreditación de autores.
                </p>

                <!-- Etiquetas / Categorías científicas -->
                <div class="mt-4 flex flex-wrap items-center gap-2 pt-3 border-t border-slate-100">
                    <span class="px-2.5 py-1 rounded-lg text-[12.5px] font-bold bg-slate-100 text-slate-800 border border-slate-200/70">
                        📊 Econometría
                    </span>
                    <span class="px-2.5 py-1 rounded-lg text-[12.5px] font-bold bg-slate-100 text-slate-800 border border-slate-200/70">
                        🧬 Bioestadística
                    </span>
                    <span class="px-2.5 py-1 rounded-lg text-[12.5px] font-bold bg-slate-100 text-slate-800 border border-slate-200/70">
                        📈 Modelos
                    </span>
                    <span class="px-2.5 py-1 rounded-lg text-[12.5px] font-bold bg-slate-100 text-slate-800 border border-slate-200/70">
                        📚 Repositorio
                    </span>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TÍTULO PRINCIPAL INFERIOR (EL TÍTULO PEDIDO) -->
        <!-- ========================================== -->
        <div class="relative z-10 pt-4">
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 leading-tight tracking-tight font-serif sm:font-sans">
                Instituto de Estadística Teórica y Aplicada
            </h1>
            <p class="text-base sm:text-lg font-extrabold text-[#35449F] tracking-tight mt-1 flex items-center gap-2">
                <span>Panel Administrativo</span>
                <span class="size-2 rounded-full bg-[#35449F]"></span>
            </p>
            <p class="text-[13px] text-slate-500 font-medium mt-1">
                Universidad Mayor de San Andrés • La Paz, Bolivia
            </p>
        </div>
    </div>
</template>
