<script setup>
import { ref, watch, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NotificationsPanel from '@/Components/NotificationsPanel.vue';
import { toast } from 'vue3-toastify';

const page = usePage();

const notificationsOpen = ref(false);

const pendingNotificationsCount = computed(() => {
    return page.props.solicitudes_pendientes_count ?? (page.props.solicitudes_pendientes?.length || 0);
});

const isAutor = computed(() => {
    const user = page.props.auth?.user;
    return !!(user?.is_autor && !user?.is_admin);
});

const isAdmin = computed(() => {
    return !!page.props.auth?.user?.is_admin;
});

watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) toast.success(flash.success);
        if (flash?.error) toast.error(flash.error);
        if (flash?.warning) toast.warning(flash.warning);
        if (flash?.info) toast.info(flash.info);
    },
    {
        deep: true,
        immediate: true,
    }
);

defineProps({
    title: {
        type: String,
        default: '',
    },
});

// Sidebar abierto por defecto solo en escritorio (>= 1024px)
const sidebarOpen = ref(typeof window !== 'undefined' ? window.innerWidth >= 1024 : true);

// Cerrar sidebar en móvil al navegar entre páginas
router.on('navigate', () => {
    if (typeof window !== 'undefined' && window.innerWidth < 1024) {
        sidebarOpen.value = false;
    }
});

const handleAsideClick = (e) => {
    if (e.target.closest('a') && typeof window !== 'undefined' && window.innerWidth < 1024) {
        sidebarOpen.value = false;
    }
};

const logout = () => {
    router.post(route('logout'));
};

// Agrupadores colapsables de navegación para evitar fatiga cognitiva (Simplicity & Consistency)
const isRevistaActive = computed(() => {
    return route().current('admin.ediciones-varianza.*') ||
        route().current('admin.tipo-articulos.*') ||
        route().current('admin.instrucciones-autores.*') ||
        route().current('admin.revista-varianza.*') ||
        route().current('admin.revistas-varias.*');
});

const isInvestigacionActive = computed(() => {
    return route().current('admin.articulos.*') ||
        route().current('admin.etiquetas-art.*') ||
        route().current('admin.memorias-investigacion.*') ||
        route().current('admin.autores.*') ||
        route().current('admin.especialidades.*') ||
        route().current('admin.grado-academicos.*') ||
        route().current('admin.tipo-autores.*') ||
        route().current('admin.tipo-participaciones.*');
});

const isBancoActive = computed(() => {
    return route().current('admin.banco-datos.*') ||
        route().current('admin.area-tematicas.*');
});

const isCursosActive = computed(() => {
    return route().current('admin.cursos.*') ||
        route().current('admin.tipo-cursos.*') ||
        route().current('admin.curso-modalidades.*');
});

const isInstitucionalActive = computed(() => {
    return route().current('admin.personal.*') ||
        route().current('admin.comite-editorial.*') ||
        route().current('admin.cargos-institucionales.*') ||
        route().current('admin.instituciones.*');
});

const isPortalActive = computed(() => {
    return route().current('admin.lineas-investigacion.*') ||
        route().current('admin.sobre-nosotros.*') ||
        route().current('admin.servicios.*') ||
        route().current('admin.noticias.*') ||
        route().current('admin.galeria.*') ||
        route().current('admin.contacto.*') ||
        route().current('admin.comunicados.*') ||
        route().current('admin.reglamento-interno.*');
});

const isSistemaActive = computed(() => {
    return route().current('admin.configuracion.*') ||
        route().current('admin.usuarios.*') ||
        route().current('admin.roles.*') ||
        route().current('admin.permisos.*');
});

const openGroups = ref({
    revista: isRevistaActive.value,
    investigacion: isInvestigacionActive.value || (!isRevistaActive.value && !isBancoActive.value && !isCursosActive.value && !isInstitucionalActive.value && !isPortalActive.value && !isSistemaActive.value),
    banco: isBancoActive.value,
    cursos: isCursosActive.value,
    institucional: isInstitucionalActive.value,
    portal: isPortalActive.value,
    sistema: isSistemaActive.value,
});

const toggleGroup = (group) => {
    openGroups.value[group] = !openGroups.value[group];
};
</script>

<template>
    <div class="min-h-screen bg-[#F8FAFC] text-slate-900 font-sans antialiased flex flex-col">
        <Head :title="title" />

        <Banner />

        <!-- Overlay móvil cuando el menú está abierto -->
        <div
            v-if="sidebarOpen"
            @click="sidebarOpen = false"
            class="fixed inset-0 bg-black/40 backdrop-blur-xs z-40 lg:hidden transition-opacity duration-300"
        ></div>

        <!-- ======================================================== -->
        <!-- SIDEBAR (ESTILO LIMPIO BLANCO CON ACTIVO AZUL/ÍNDIGO)     -->
        <!-- ======================================================== -->
        <aside
            @click="handleAsideClick"
            :class="[
                sidebarOpen ? 'translate-x-0' : '-translate-x-full'
            ]"
            class="w-72 fixed inset-y-0 left-0 z-50 bg-white border-r border-slate-200/90 shadow-sm flex flex-col transition-transform duration-300 ease-in-out"
        >
            <!-- Cabecera / Logo (Círculo azul + Título + Subtítulo) -->
            <div class="h-16 flex items-center justify-between px-6 border-b border-slate-100 bg-white shrink-0">
                <Link :href="route('dashboard')" class="flex items-center space-x-3 group">
                    <!-- Logo Imagen (si existe) o Círculo Fallback "IE" -->
                    <div
                        v-if="$page.props.institucion?.logo_url"
                        class="size-10 rounded-xl overflow-hidden bg-white border border-slate-200/80 flex items-center justify-center p-0.5 shadow-xs group-hover:scale-105 transition-transform duration-200 shrink-0"
                    >
                        <img
                            :src="$page.props.institucion.logo_url"
                            :alt="$page.props.institucion.siglas || 'IETA'"
                            class="size-full object-contain"
                        />
                    </div>
                    <div
                        v-else
                        class="size-10 rounded-full bg-[#35449F] flex items-center justify-center text-white font-black text-sm shadow-xs group-hover:scale-105 transition-transform duration-200 shrink-0"
                    >
                        {{ $page.props.institucion?.siglas?.substring(0, 2) || 'IE' }}
                    </div>
                    <div>
                        <span class="text-lg font-black text-slate-900 tracking-tight leading-none block">
                            {{ $page.props.institucion?.siglas || 'IETA' }}
                        </span>
                        <span class="text-[11px] font-semibold text-slate-500 block leading-tight">
                            {{ $page.props.institucion?.slogan || 'Estadística • UMSA' }}
                        </span>
                    </div>
                </Link>

                <!-- Botón cerrar rápido en móvil -->
                <button
                    @click="sidebarOpen = false"
                    type="button"
                    class="lg:hidden p-1.5 text-slate-400 hover:text-slate-800 rounded-lg hover:bg-slate-100 transition cursor-pointer"
                >
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Menú de Enlaces Lateral con Scroll Suave -->
            <div class="flex-1 overflow-y-auto px-4 py-5 space-y-6 scrollbar-thin scrollbar-thumb-slate-200">
                <!-- MENÚ ESPECIAL PARA ROL AUTOR -->
                <template v-if="isAutor">
                    <div>
                        <div class="flex items-center justify-between px-3 mb-2">
                            <p class="text-[12px] font-extrabold uppercase tracking-wider text-slate-400">
                                Portal del Investigador
                            </p>
                            <span class="px-1.5 py-0.5 text-[9px] font-bold tracking-wider uppercase bg-emerald-100 text-emerald-700 rounded">
                                Autor IETA
                            </span>
                        </div>
                        <nav class="space-y-1">
                            <Link
                                :href="route('admin.articulos.index')"
                                :class="[
                                    route().current('admin.articulos.*')
                                        ? 'bg-[#35449F] text-white font-bold shadow-xs'
                                        : 'text-slate-700 hover:text-slate-900 hover:bg-slate-100/80 font-medium'
                                ]"
                                class="flex items-center space-x-3.5 px-4 py-3 rounded-xl text-[15px] font-semibold transition-all duration-200 group"
                            >
                                <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span>Mis Artículos Científicos</span>
                            </Link>

                            <Link
                                :href="route('profile.show')"
                                :class="[
                                    route().current('profile.show')
                                        ? 'bg-[#35449F] text-white font-bold shadow-xs'
                                        : 'text-slate-700 hover:text-slate-900 hover:bg-slate-100/80 font-medium'
                                ]"
                                class="flex items-center space-x-3.5 px-4 py-2.5 rounded-xl text-[15px] font-semibold transition-all duration-200 group"
                            >
                                <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span>Mi Perfil</span>
                            </Link>

                            <Link
                                :href="route('two-factor.setup')"
                                :class="[
                                    route().current('two-factor.setup')
                                        ? 'bg-[#35449F] text-white font-bold shadow-xs'
                                        : 'text-slate-700 hover:text-slate-900 hover:bg-slate-100/80 font-medium'
                                ]"
                                class="flex items-center space-x-3.5 px-4 py-2.5 rounded-xl text-[15px] font-semibold transition-all duration-200 group"
                            >
                                <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                <span>Seguridad / 2FA</span>
                            </Link>
                        </nav>
                    </div>
                </template>

                <!-- MENÚ COMPLETO ADMINISTRATIVO -->
                <template v-else>
                <!-- Sección: Principal / Dashboard -->
                <div>
                    <nav class="space-y-1">
                        <Link
                            :href="route('dashboard')"
                            :class="[
                                route().current('dashboard')
                                    ? 'bg-[#35449F] text-white font-bold shadow-xs'
                                    : 'text-slate-700 hover:text-slate-900 hover:bg-slate-100/80 font-medium'
                            ]"
                            class="flex items-center space-x-3.5 px-4 py-3 rounded-xl text-[15.5px] font-semibold transition-all duration-200 group"
                        >
                            <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            <span>Dashboard</span>
                        </Link>
                    </nav>
                </div>

                <!-- Sección: Revista Varianza (Científica) -->
                <div class="border-t border-slate-100 pt-3">
                    <button
                        type="button"
                        @click="toggleGroup('revista')"
                        class="w-full flex items-center justify-between text-left px-3 py-2 text-xs font-extrabold uppercase tracking-wider text-slate-500 hover:text-slate-800 hover:bg-slate-50 rounded-xl transition cursor-pointer select-none"
                    >
                        <div class="flex items-center gap-2">
                            <span :class="isRevistaActive ? 'text-[#35449F]' : 'text-slate-500'">
                                Revista Varianza
                            </span>
                            <span class="px-1.5 py-0.5 text-[9px] font-bold tracking-wider uppercase bg-blue-100 text-[#35449F] rounded">
                                Indexada
                            </span>
                        </div>
                        <svg
                            class="w-4 h-4 shrink-0 text-slate-400 transition-transform duration-200"
                            :class="openGroups.revista ? 'rotate-90' : ''"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <nav v-show="openGroups.revista" class="space-y-1 mt-1 pl-1">
                        <Link
                            :href="route('admin.ediciones-varianza.index')"
                            :class="[
                                route().current('admin.ediciones-varianza.*')
                                    ? 'bg-[#35449F] text-white font-bold shadow-xs'
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium'
                            ]"
                            class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150 group"
                        >
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <span>Ediciones Varianza</span>
                        </Link>

                        <Link
                            :href="route('admin.tipo-articulos.index')"
                            :class="[
                                route().current('admin.tipo-articulos.*')
                                    ? 'bg-[#35449F] text-white font-bold shadow-xs'
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium'
                            ]"
                            class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150 group"
                        >
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                            <span>Tipos de Artículos</span>
                        </Link>

                        <Link
                            :href="route('admin.instrucciones-autores.index')"
                            :class="[
                                route().current('admin.instrucciones-autores.*')
                                    ? 'bg-[#35449F] text-white font-bold shadow-xs'
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium'
                            ]"
                            class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150 group"
                        >
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Instrucciones Autores</span>
                        </Link>

                        <Link
                            :href="route('admin.revista-varianza.edit')"
                            :class="[
                                route().current('admin.revista-varianza.*')
                                    ? 'bg-[#35449F] text-white font-bold shadow-xs'
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium'
                            ]"
                            class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150 group"
                        >
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                            </svg>
                            <span>Perfil CMS Revista</span>
                        </Link>

                        <Link
                            :href="route('admin.revistas-varias.index')"
                            :class="[
                                route().current('admin.revistas-varias.*')
                                    ? 'bg-[#35449F] text-white font-bold shadow-xs'
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium'
                            ]"
                            class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150 group"
                        >
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <span>Revistas de Referencia</span>
                        </Link>
                    </nav>
                </div>

                <!-- Sección: Investigación & Publicaciones -->
                <div class="border-t border-slate-100 pt-3">
                    <button
                        type="button"
                        @click="toggleGroup('investigacion')"
                        class="w-full flex items-center justify-between text-left px-3 py-2 text-xs font-extrabold uppercase tracking-wider text-slate-500 hover:text-slate-800 hover:bg-slate-50 rounded-xl transition cursor-pointer select-none"
                    >
                        <span class="text-left flex-1 pr-2 leading-tight" :class="isInvestigacionActive ? 'text-[#35449F]' : 'text-slate-500'">
                            Investigación & Publicaciones
                        </span>
                        <svg
                            class="w-4 h-4 shrink-0 text-slate-400 transition-transform duration-200"
                            :class="openGroups.investigacion ? 'rotate-90' : ''"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <nav v-show="openGroups.investigacion" class="space-y-1 mt-1 pl-1">
                        <Link
                            :href="route('admin.articulos.index')"
                            :class="[
                                route().current('admin.articulos.*')
                                    ? 'bg-[#35449F] text-white font-bold shadow-xs'
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium'
                            ]"
                            class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150 group"
                        >
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Artículos Científicos</span>
                        </Link>

                        <Link
                            :href="route('admin.autores.index')"
                            :class="[
                                route().current('admin.autores.*')
                                    ? 'bg-[#35449F] text-white font-bold shadow-xs'
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium'
                            ]"
                            class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150 group"
                        >
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span>Autores & Investigadores</span>
                        </Link>

                        <Link
                            :href="route('admin.memorias-investigacion.index')"
                            :class="[
                                route().current('admin.memorias-investigacion.*')
                                    ? 'bg-[#35449F] text-white font-bold shadow-xs'
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium'
                            ]"
                            class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150 group"
                        >
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            <span>Memorias de Investigación</span>
                        </Link>

                        <Link
                            :href="route('admin.etiquetas-art.index')"
                            :class="[
                                route().current('admin.etiquetas-art.*')
                                    ? 'bg-[#35449F] text-white font-bold shadow-xs'
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium'
                            ]"
                            class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150 group"
                        >
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                            <span>Etiquetas de Artículos</span>
                        </Link>

                        <Link
                            :href="route('admin.especialidades.index')"
                            :class="[
                                route().current('admin.especialidades.*')
                                    ? 'bg-[#35449F] text-white font-bold shadow-xs'
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium'
                            ]"
                            class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150 group"
                        >
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                            <span>Especialidades</span>
                        </Link>

                        <Link
                            :href="route('admin.grado-academicos.index')"
                            :class="[
                                route().current('admin.grado-academicos.*')
                                    ? 'bg-[#35449F] text-white font-bold shadow-xs'
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium'
                            ]"
                            class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150 group"
                        >
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                            </svg>
                            <span>Grados Académicos</span>
                        </Link>

                        <Link
                            :href="route('admin.tipo-autores.index')"
                            :class="[
                                route().current('admin.tipo-autores.*')
                                    ? 'bg-[#35449F] text-white font-bold shadow-xs'
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium'
                            ]"
                            class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150 group"
                        >
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>Tipos de Autor</span>
                        </Link>

                        <Link
                            :href="route('admin.tipo-participaciones.index')"
                            :class="[
                                route().current('admin.tipo-participaciones.*')
                                    ? 'bg-[#35449F] text-white font-bold shadow-xs'
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium'
                            ]"
                            class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150 group"
                        >
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            <span>Tipos de Participación</span>
                        </Link>
                    </nav>
                </div>

                <!-- Sección: Banco de Datos -->
                <div class="border-t border-slate-100 pt-3">
                    <button
                        type="button"
                        @click="toggleGroup('banco')"
                        class="w-full flex items-center justify-between text-left px-3 py-2 text-xs font-extrabold uppercase tracking-wider text-slate-500 hover:text-slate-800 hover:bg-slate-50 rounded-xl transition cursor-pointer select-none"
                    >
                        <span :class="isBancoActive ? 'text-[#35449F]' : 'text-slate-500'">
                            Banco de Datos
                        </span>
                        <svg
                            class="w-4 h-4 shrink-0 text-slate-400 transition-transform duration-200"
                            :class="openGroups.banco ? 'rotate-90' : ''"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <nav v-show="openGroups.banco" class="space-y-1 mt-1 pl-1">
                        <Link
                            :href="route('admin.banco-datos.index')"
                            :class="[
                                route().current('admin.banco-datos.*')
                                    ? 'bg-[#35449F] text-white font-bold shadow-xs'
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium'
                            ]"
                            class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150 group"
                        >
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                            </svg>
                            <span>Bases de Datos</span>
                        </Link>

                        <Link
                            :href="route('admin.area-tematicas.index')"
                            :class="[
                                route().current('admin.area-tematicas.*')
                                    ? 'bg-[#35449F] text-white font-bold shadow-xs'
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium'
                            ]"
                            class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150 group"
                        >
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                            <span>Áreas Temáticas</span>
                        </Link>
                    </nav>
                </div>

                <!-- Sección: Cursos & Modalidades -->
                <div class="border-t border-slate-100 pt-3">
                    <button
                        type="button"
                        @click="toggleGroup('cursos')"
                        class="w-full flex items-center justify-between text-left px-3 py-2 text-xs font-extrabold uppercase tracking-wider text-slate-500 hover:text-slate-800 hover:bg-slate-50 rounded-xl transition cursor-pointer select-none"
                    >
                        <span :class="isCursosActive ? 'text-[#35449F]' : 'text-slate-500'">
                            Cursos & Modalidades
                        </span>
                        <svg
                            class="w-4 h-4 shrink-0 text-slate-400 transition-transform duration-200"
                            :class="openGroups.cursos ? 'rotate-90' : ''"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <nav v-show="openGroups.cursos" class="space-y-1 mt-1 pl-1">
                        <Link
                            :href="route('admin.cursos.index')"
                            :class="[
                                route().current('admin.cursos.*')
                                    ? 'bg-[#35449F] text-white font-bold shadow-xs'
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium'
                            ]"
                            class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150 group"
                        >
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                            </svg>
                            <span>Cursos</span>
                        </Link>

                        <Link
                            :href="route('admin.tipo-cursos.index')"
                            :class="[
                                route().current('admin.tipo-cursos.*')
                                    ? 'bg-[#35449F] text-white font-bold shadow-xs'
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium'
                            ]"
                            class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150 group"
                        >
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                            </svg>
                            <span>Tipo de Cursos</span>
                        </Link>

                        <Link
                            :href="route('admin.curso-modalidades.index')"
                            :class="[
                                route().current('admin.curso-modalidades.*')
                                    ? 'bg-[#35449F] text-white font-bold shadow-xs'
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium'
                            ]"
                            class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150 group"
                        >
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span>Modalidades</span>
                        </Link>
                    </nav>
                </div>

                <!-- Sección: Equipo & Institucional -->
                <div class="border-t border-slate-100 pt-3">
                    <button
                        type="button"
                        @click="toggleGroup('institucional')"
                        class="w-full flex items-center justify-between text-left px-3 py-2 text-xs font-extrabold uppercase tracking-wider text-slate-500 hover:text-slate-800 hover:bg-slate-50 rounded-xl transition cursor-pointer select-none"
                    >
                        <span :class="isInstitucionalActive ? 'text-[#35449F]' : 'text-slate-500'">
                            Equipo & Personal
                        </span>
                        <svg
                            class="w-4 h-4 shrink-0 text-slate-400 transition-transform duration-200"
                            :class="openGroups.institucional ? 'rotate-90' : ''"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <nav v-show="openGroups.institucional" class="space-y-1 mt-1 pl-1">
                        <Link
                            :href="route('admin.personal.index')"
                            :class="[
                                route().current('admin.personal.*')
                                    ? 'bg-[#35449F] text-white font-bold shadow-xs'
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium'
                            ]"
                            class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150 group"
                        >
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <span>Personal</span>
                        </Link>

                        <Link
                            :href="route('admin.comite-editorial.index')"
                            :class="[
                                route().current('admin.comite-editorial.*')
                                    ? 'bg-[#35449F] text-white font-bold shadow-xs'
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium'
                            ]"
                            class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150 group"
                        >
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>Comité Editorial</span>
                        </Link>

                        <Link
                            :href="route('admin.cargos-institucionales.index')"
                            :class="[
                                route().current('admin.cargos-institucionales.*')
                                    ? 'bg-[#35449F] text-white font-bold shadow-xs'
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium'
                            ]"
                            class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150 group"
                        >
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span>Cargos</span>
                        </Link>

                        <Link
                            :href="route('admin.instituciones.index')"
                            :class="[
                                route().current('admin.instituciones.*')
                                    ? 'bg-[#35449F] text-white font-bold shadow-xs'
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium'
                            ]"
                            class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150 group"
                        >
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <span>Instituciones</span>
                        </Link>
                    </nav>
                </div>

                <!-- Sección: Portal Web -->
                <div class="border-t border-slate-100 pt-3">
                    <button
                        type="button"
                        @click="toggleGroup('portal')"
                        class="w-full flex items-center justify-between text-left px-3 py-2 text-xs font-extrabold uppercase tracking-wider text-slate-500 hover:text-slate-800 hover:bg-slate-50 rounded-xl transition cursor-pointer select-none"
                    >
                        <span :class="isPortalActive ? 'text-[#35449F]' : 'text-slate-500'">
                            Portal Web Institucional
                        </span>
                        <svg
                            class="w-4 h-4 shrink-0 text-slate-400 transition-transform duration-200"
                            :class="openGroups.portal ? 'rotate-90' : ''"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <nav v-show="openGroups.portal" class="space-y-1 mt-1 pl-1">
                        <Link
                            :href="route('admin.lineas-investigacion.index')"
                            :class="[
                                route().current('admin.lineas-investigacion.*')
                                    ? 'bg-[#35449F] text-white font-bold shadow-xs'
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium'
                            ]"
                            class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150 group"
                        >
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            <span>Líneas de Investigación</span>
                        </Link>

                        <Link
                            :href="route('admin.sobre-nosotros.edit')"
                            :class="[
                                route().current('admin.sobre-nosotros.*')
                                    ? 'bg-[#35449F] text-white font-bold shadow-xs'
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium'
                            ]"
                            class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150 group"
                        >
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <span>Sobre Nosotros</span>
                        </Link>

                        <Link
                            :href="route('admin.servicios.index')"
                            :class="[
                                route().current('admin.servicios.*')
                                    ? 'bg-[#35449F] text-white font-bold shadow-xs'
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium'
                            ]"
                            class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150 group"
                        >
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span>Servicios</span>
                        </Link>

                        <Link
                            :href="route('admin.noticias.index')"
                            :class="[
                                route().current('admin.noticias.*')
                                    ? 'bg-[#35449F] text-white font-bold shadow-xs'
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium'
                            ]"
                            class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150 group"
                        >
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                            </svg>
                            <span>Noticias & Novedades</span>
                        </Link>

                        <Link
                            :href="route('admin.galeria.index')"
                            :class="[
                                route().current('admin.galeria.*')
                                    ? 'bg-[#35449F] text-white font-bold shadow-xs'
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium'
                            ]"
                            class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150 group"
                        >
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>Galería de Imágenes</span>
                        </Link>

                        <Link
                            :href="route('admin.contacto.edit')"
                            :class="[
                                route().current('admin.contacto.*')
                                    ? 'bg-[#35449F] text-white font-bold shadow-xs'
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium'
                            ]"
                            class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150 group"
                        >
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <span>Contacto</span>
                        </Link>

                        <Link
                            :href="route('admin.comunicados.index')"
                            :class="[
                                route().current('admin.comunicados.*')
                                    ? 'bg-[#35449F] text-white font-bold shadow-xs'
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium'
                            ]"
                            class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150 group"
                        >
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                            </svg>
                            <span>Comunicados & Popups</span>
                        </Link>

                        <Link
                            :href="route('admin.reglamento-interno.edit')"
                            :class="[
                                route().current('admin.reglamento-interno.*')
                                    ? 'bg-[#35449F] text-white font-bold shadow-xs'
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium'
                            ]"
                            class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150 group"
                        >
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Reglamento Interno</span>
                        </Link>
                    </nav>
                </div>

                <!-- Sección: Configuración del Sistema (Solo Administrador) -->
                <div v-if="isAdmin" class="border-t border-slate-100 pt-3">
                    <button
                        type="button"
                        @click="toggleGroup('sistema')"
                        class="w-full flex items-center justify-between text-left px-3 py-2 text-xs font-extrabold uppercase tracking-wider text-slate-500 hover:text-slate-800 hover:bg-slate-50 rounded-xl transition cursor-pointer select-none"
                    >
                        <span :class="isSistemaActive ? 'text-[#35449F]' : 'text-slate-500'">
                            Configuración & Acceso
                        </span>
                        <svg
                            class="w-4 h-4 shrink-0 text-slate-400 transition-transform duration-200"
                            :class="openGroups.sistema ? 'rotate-90' : ''"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <nav v-show="openGroups.sistema" class="space-y-1 mt-1 pl-1">
                        <Link
                            :href="route('admin.configuracion.edit')"
                            :class="[
                                route().current('admin.configuracion.*')
                                    ? 'bg-[#35449F] text-white font-bold shadow-xs'
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium'
                            ]"
                            class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150 group"
                        >
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>Configuración General</span>
                        </Link>

                        <Link
                            :href="route('admin.usuarios.index')"
                            :class="[
                                route().current('admin.usuarios.*')
                                    ? 'bg-[#35449F] text-white font-bold shadow-xs'
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium'
                            ]"
                            class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150 group"
                        >
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <span>Usuarios</span>
                        </Link>

                        <Link
                            :href="route('admin.roles.index')"
                            :class="[
                                route().current('admin.roles.*')
                                    ? 'bg-[#35449F] text-white font-bold shadow-xs'
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium'
                            ]"
                            class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150 group"
                        >
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            <span>Roles</span>
                        </Link>

                        <Link
                            :href="route('admin.permisos.index')"
                            :class="[
                                route().current('admin.permisos.*')
                                    ? 'bg-[#35449F] text-white font-bold shadow-xs'
                                    : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80 font-medium'
                            ]"
                            class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-150 group"
                        >
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                            <span>Permisos</span>
                        </Link>
                    </nav>
                </div>
                </template>
            </div>

            <!-- Footer Usuario en Sidebar (Abajo) -->
            <div class="p-3.5 border-t border-slate-100 bg-slate-50/70 shrink-0">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center space-x-2.5 min-w-0">
                        <img
                            v-if="$page.props.auth?.user?.profile_photo_url"
                            :src="$page.props.auth.user.profile_photo_url"
                            :alt="$page.props.auth?.user?.name"
                            class="size-9 rounded-full object-cover border-2 border-[#35449F] shrink-0"
                        />
                        <div v-else class="size-9 rounded-full bg-[#35449F] text-white font-black flex items-center justify-center text-xs shadow-xs shrink-0">
                            {{ $page.props.auth?.user?.name?.charAt(0) || 'A' }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-[13.5px] font-bold text-slate-800 truncate leading-tight">
                                {{ $page.props.auth?.user?.name }}
                            </p>
                            <p class="text-[12px] text-slate-500 truncate font-medium">
                                {{ $page.props.auth?.user?.email }}
                            </p>
                        </div>
                    </div>

                    <button
                        @click="logout"
                        title="Cerrar sesión"
                        type="button"
                        class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition cursor-pointer shrink-0"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </div>
            </div>
        </aside>

        <!-- ======================================================== -->
        <!-- ÁREA PRINCIPAL RESPONSIVA (Se expande si se oculta)       -->
        <!-- ======================================================== -->
        <div
            :class="[
                sidebarOpen ? 'lg:pl-72' : 'lg:pl-0'
            ]"
            class="flex flex-col flex-1 transition-all duration-300 ease-in-out min-w-0"
        >
            <!-- Topbar Sticky con botón Hamburguesa funcional en escritorio y móvil -->
            <header class="h-16 bg-white border-b border-slate-200/90 sticky top-0 z-30 flex items-center justify-between px-4 sm:px-6 lg:px-8 shadow-2xs">
                <div class="flex items-center gap-3 min-w-0">
                    <!-- Botón de Menú: Oculta y muestra con deslizador suave -->
                    <button
                        @click="sidebarOpen = !sidebarOpen"
                        type="button"
                        class="shrink-0 p-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-xl border border-slate-200 transition focus:outline-none cursor-pointer"
                        :title="sidebarOpen ? 'Ocultar menú lateral' : 'Mostrar menú lateral'"
                    >
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <!-- Indicador o Título de la sección en Topbar -->
                    <span class="text-sm sm:text-base font-bold text-slate-800 truncate max-w-[200px] sm:max-w-xs md:max-w-md">
                        {{ title || 'Panel IETA' }}
                    </span>
                </div>

                <!-- Área Derecha de Topbar: Notificaciones + Dropdown de Usuario -->
                <div class="shrink-0 flex items-center space-x-2.5 sm:space-x-3">
                    <!-- Botón de Notificaciones con Badge -->
                    <button
                        @click="notificationsOpen = true"
                        type="button"
                        class="relative p-2 text-slate-600 hover:text-[#35449F] hover:bg-slate-100 rounded-xl border border-slate-200 transition focus:outline-none cursor-pointer"
                        title="Notificaciones y Solicitudes"
                    >
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                        </svg>

                        <!-- Badge con animación si hay solicitudes pendientes -->
                        <span
                            v-if="pendingNotificationsCount > 0"
                            class="absolute -top-1 -right-1 flex size-5 items-center justify-center rounded-full bg-rose-600 text-[10px] font-black text-white ring-2 ring-white shadow-xs"
                        >
                            <span class="absolute inline-flex size-full animate-ping rounded-full bg-rose-400 opacity-75"></span>
                            <span class="relative">{{ pendingNotificationsCount > 9 ? '9+' : pendingNotificationsCount }}</span>
                        </span>
                    </button>

                    <Dropdown align="right" width="64">
                        <template #trigger>
                            <button type="button" class="inline-flex items-center space-x-2.5 px-3 py-1.5 border border-slate-200 text-[13.5px] font-bold rounded-xl text-slate-800 bg-white hover:bg-slate-50 transition cursor-pointer">
                                <div class="size-7 rounded-full bg-[#35449F] text-white font-extrabold flex items-center justify-center text-xs">
                                    {{ $page.props.auth?.user?.name?.charAt(0) || 'U' }}
                                </div>
                                <span class="hidden sm:inline">{{ $page.props.auth?.user?.name }}</span>
                                <svg class="size-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>
                        </template>

                        <template #content>
                            <div class="block px-4 py-2.5 text-[12px] font-bold text-slate-500 uppercase tracking-wider truncate border-b border-slate-100">
                                {{ $page.props.auth?.user?.email }}
                            </div>

                            <DropdownLink :href="route('profile.show')" class="text-[13.5px] font-medium py-2.5">
                                Mi Perfil y Seguridad (2FA)
                            </DropdownLink>

                            <DropdownLink v-if="$page.props.jetstream?.hasApiFeatures" :href="route('api-tokens.index')" class="text-[13.5px] font-medium py-2.5">
                                API Tokens
                            </DropdownLink>

                            <div class="border-t border-slate-100" />

                            <form @submit.prevent="logout">
                                <DropdownLink as="button" class="text-rose-600 font-bold text-[13.5px] py-2.5">
                                    Cerrar Sesión
                                </DropdownLink>
                            </form>
                        </template>
                    </Dropdown>
                </div>
            </header>

            <!-- Cabecera de Página / Hero de Módulo (Limpia, espaciosa y 100% responsiva) -->
            <div v-if="$slots.header" class="bg-white border-b border-slate-200/80 px-4 sm:px-6 lg:px-8 py-4 sm:py-5 shadow-2xs">
                <div class="max-w-7xl mx-auto">
                    <slot name="header" />
                </div>
            </div>

            <!-- Contenido Principal -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 bg-[#F8FAFC]">
                <slot />
            </main>
        </div>

        <!-- Panel Lateral Derecho de Notificaciones y Solicitudes -->
        <NotificationsPanel
            :open="notificationsOpen"
            @close="notificationsOpen = false"
        />
    </div>
</template>
