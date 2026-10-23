<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    open: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close']);

const page = usePage();

const user = computed(() => page.props.auth?.user);
const puedeRevisar = computed(() => {
    return !!(user.value?.is_admin || user.value?.permissions?.includes('articulos.revisar'));
});
const isAutor = computed(() => !!(user.value?.is_autor && !user.value?.is_admin));

const solicitudes = computed(() => page.props.solicitudes_pendientes || []);
const totalCount = computed(() => page.props.solicitudes_pendientes_count || solicitudes.value.length || 0);

// Estados para acciones inline
const approvingId = ref(null);
const approvalMotivo = ref('');
const isApproving = ref(false);

const rejectingId = ref(null);
const rejectMotivo = ref('');
const rejectError = ref('');
const isRejecting = ref(false);

const isRefreshing = ref(false);

// Helper para fecha relativa en español
const formatRelativeTime = (isoString) => {
    if (!isoString) return 'Reciente';
    try {
        const date = new Date(isoString);
        const now = new Date();
        const diffInSeconds = Math.floor((now.getTime() - date.getTime()) / 1000);

        if (diffInSeconds < 60) return 'Hace un momento';
        const diffInMinutes = Math.floor(diffInSeconds / 60);
        if (diffInMinutes < 60) return `Hace ${diffInMinutes} min`;
        const diffInHours = Math.floor(diffInMinutes / 60);
        if (diffInHours < 24) return `Hace ${diffInHours} h`;
        const diffInDays = Math.floor(diffInHours / 24);
        if (diffInDays === 1) return 'Ayer';
        if (diffInDays < 7) return `Hace ${diffInDays} días`;

        return date.toLocaleDateString('es-ES', {
            day: '2-digit',
            month: 'short',
            year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined,
        });
    } catch {
        return 'Fecha no disponible';
    }
};

const refreshData = () => {
    isRefreshing.value = true;
    router.reload({
        only: ['solicitudes_pendientes', 'solicitudes_pendientes_count'],
        onFinish: () => {
            isRefreshing.value = false;
        },
    });
};

// Acciones de revisión
const openApprove = (item) => {
    rejectingId.value = null;
    approvalMotivo.value = '';
    approvingId.value = item.id;
};

const cancelApprove = () => {
    approvingId.value = null;
    approvalMotivo.value = '';
};

const executeApprove = (item) => {
    isApproving.value = true;
    router.post(
        route('admin.articulos.aprobar', item.id),
        { motivo: approvalMotivo.value || null },
        {
            preserveScroll: true,
            onSuccess: () => {
                approvingId.value = null;
                approvalMotivo.value = '';
            },
            onFinish: () => {
                isApproving.value = false;
            },
        }
    );
};

const openReject = (item) => {
    approvingId.value = null;
    rejectMotivo.value = '';
    rejectError.value = '';
    rejectingId.value = item.id;
};

const cancelReject = () => {
    rejectingId.value = null;
    rejectMotivo.value = '';
    rejectError.value = '';
};

const executeReject = (item) => {
    if (!rejectMotivo.value.trim() || rejectMotivo.value.trim().length < 3) {
        rejectError.value = 'Indica un motivo u observaciones de al menos 3 caracteres.';
        return;
    }

    isRejecting.value = true;
    router.post(
        route('admin.articulos.rechazar', item.id),
        { motivo: rejectMotivo.value },
        {
            preserveScroll: true,
            onSuccess: () => {
                rejectingId.value = null;
                rejectMotivo.value = '';
                rejectError.value = '';
            },
            onError: (err) => {
                if (err.motivo) rejectError.value = err.motivo;
            },
            onFinish: () => {
                isRejecting.value = false;
            },
        }
    );
};

// Manejo de tecla Escape
const handleKeydown = (e) => {
    if (e.key === 'Escape' && props.open) {
        emit('close');
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <Teleport to="body">
        <!-- Overlay backdrop -->
        <Transition
            enter-active-class="transition-opacity duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="open"
                @click="emit('close')"
                class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-50 cursor-pointer"
                aria-hidden="true"
            ></div>
        </Transition>

        <!-- Drawer Lateral Derecho -->
        <Transition
            enter-active-class="transition-transform duration-300 ease-out"
            enter-from-class="translate-x-full"
            enter-to-class="translate-x-0"
            leave-active-class="transition-transform duration-250 ease-in"
            leave-from-class="translate-x-0"
            leave-to-class="translate-x-full"
        >
            <aside
                v-if="open"
                class="fixed inset-y-0 right-0 z-50 w-full max-w-md sm:max-w-lg bg-white shadow-2xl flex flex-col border-l border-slate-200/90 overflow-hidden"
                role="dialog"
                aria-label="Panel de Notificaciones y Solicitudes"
            >
                <!-- Cabecera del Panel -->
                <div class="p-5 border-b border-slate-100 bg-white/95 backdrop-blur-sm flex items-center justify-between gap-3 shrink-0">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="size-10 rounded-xl bg-gradient-to-br from-[#35449F] to-[#5163C7] text-white flex items-center justify-center shadow-md shadow-[#35449F]/20 shrink-0">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <h2 class="font-extrabold text-slate-900 text-lg tracking-tight">
                                    Notificaciones
                                </h2>
                                <span
                                    v-if="totalCount > 0"
                                    class="px-2 py-0.5 rounded-full text-xs font-black bg-rose-50 text-rose-600 border border-rose-200/60"
                                >
                                    {{ totalCount }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 font-medium truncate">
                                <template v-if="puedeRevisar">
                                    Solicitudes de artículos para revisión editorial
                                </template>
                                <template v-else-if="isAutor">
                                    Estado de tus artículos científicos enviados
                                </template>
                                <template v-else>
                                    Novedades del sistema IETA
                                </template>
                            </p>
                        </div>
                    </div>

                    <!-- Botones de Acción de la Cabecera -->
                    <div class="flex items-center gap-1.5 shrink-0">
                        <button
                            @click="refreshData"
                            :disabled="isRefreshing"
                            type="button"
                            title="Actualizar bandeja"
                            class="p-2 text-slate-400 hover:text-[#35449F] hover:bg-slate-100 rounded-xl transition cursor-pointer disabled:opacity-50"
                        >
                            <svg
                                class="w-5 h-5 shrink-0"
                                :class="{ 'animate-spin': isRefreshing }"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </button>

                        <button
                            @click="emit('close')"
                            type="button"
                            title="Cerrar panel (Esc)"
                            class="p-2 text-slate-400 hover:text-slate-800 hover:bg-slate-100 rounded-xl transition cursor-pointer"
                        >
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Sub-barra informativa o de estado -->
                <div class="px-5 py-2.5 bg-slate-50/80 border-b border-slate-100 flex items-center justify-between text-xs text-slate-600 font-semibold">
                    <div class="flex items-center gap-2">
                        <span class="size-2 rounded-full" :class="totalCount > 0 ? 'bg-amber-500 animate-pulse' : 'bg-emerald-500'"></span>
                        <span>
                            <template v-if="puedeRevisar">
                                {{ totalCount }} {{ totalCount === 1 ? 'solicitud pendiente' : 'solicitudes pendientes' }}
                            </template>
                            <template v-else>
                                {{ solicitudes.length }} {{ solicitudes.length === 1 ? 'artículo registrado' : 'artículos registrados' }}
                            </template>
                        </span>
                    </div>

                    <Link
                        v-if="puedeRevisar"
                        :href="route('admin.articulos.index', { estado_revision: 'pendiente' })"
                        @click="emit('close')"
                        class="text-[#35449F] hover:underline font-bold"
                    >
                        Ver todos en tabla &rarr;
                    </Link>
                </div>

                <!-- Lista con Scroll Suave -->
                <div class="flex-1 overflow-y-auto p-4 sm:p-5 space-y-4 scrollbar-thin scrollbar-thumb-slate-200">
                    <!-- ============================================== -->
                    <!-- MODO: REVISORES (ADMINISTRADORES / EDITORES)    -->
                    <!-- ============================================== -->
                    <template v-if="puedeRevisar">
                        <!-- Estado Vacío -->
                        <div
                            v-if="solicitudes.length === 0"
                            class="py-14 px-6 text-center flex flex-col items-center justify-center space-y-3"
                        >
                            <div class="size-16 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-600 flex items-center justify-center shadow-xs">
                                <svg class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="font-extrabold text-slate-800 text-base">
                                ¡Bandeja de revisión al día!
                            </h3>
                            <p class="text-xs text-slate-500 max-w-xs leading-relaxed">
                                No tienes solicitudes pendientes de artículos mandados por autores en este momento.
                            </p>
                        </div>

                        <!-- Tarjetas de Solicitudes Pendientes -->
                        <div
                            v-for="item in solicitudes"
                            :key="item.id"
                            class="bg-white rounded-2xl border border-slate-200/90 p-4 sm:p-4.5 shadow-2xs hover:shadow-md hover:border-slate-300 transition-all duration-200 space-y-3 relative group"
                        >
                            <!-- Top de la Tarjeta: Tipo y Tiempo Relativo -->
                            <div class="flex items-center justify-between gap-2">
                                <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold uppercase tracking-wider bg-[#35449F]/10 text-[#35449F] border border-[#35449F]/15">
                                    {{ item.tipo_articulo }}
                                </span>
                                <span class="text-[11px] font-semibold text-slate-400 flex items-center gap-1 shrink-0">
                                    <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    {{ formatRelativeTime(item.created_at) }}
                                </span>
                            </div>

                            <!-- Título del Artículo -->
                            <h4 class="font-extrabold text-slate-900 text-sm leading-snug line-clamp-2">
                                {{ item.art_titulo }}
                            </h4>

                            <!-- Autores y Creador -->
                            <div class="text-xs text-slate-500 space-y-1">
                                <div class="flex items-start gap-1.5">
                                    <svg class="size-3.5 text-slate-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                    </svg>
                                    <span class="font-medium">
                                        <strong class="text-slate-700">Autores:</strong> {{ item.autores_texto }}
                                    </span>
                                </div>

                                <div v-if="item.creado_por && item.creado_por.email" class="flex items-center gap-1.5 text-[11px] text-slate-400">
                                    <svg class="size-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                    </svg>
                                    <span class="truncate">Enviado por: {{ item.creado_por.name }} ({{ item.creado_por.email }})</span>
                                </div>
                            </div>

                            <!-- Resumen breve -->
                            <p v-if="item.art_resumen" class="text-xs text-slate-600 line-clamp-2 bg-slate-50 p-2 rounded-lg border border-slate-100 italic">
                                &ldquo;{{ item.art_resumen }}&rdquo;
                            </p>

                            <!-- Documento adjunto si existe -->
                            <div v-if="item.archivo_url" class="flex items-center justify-between text-xs pt-0.5">
                                <a
                                    :href="item.archivo_url"
                                    target="_blank"
                                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#35449F] hover:text-[#253275] underline"
                                >
                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>
                                    Ver archivo manuscrito (PDF/DOC)
                                </a>
                            </div>

                            <!-- CAJA INLINE: CONFIRMACIÓN DE APROBACIÓN -->
                            <div
                                v-if="approvingId === item.id"
                                class="p-3.5 rounded-xl bg-emerald-50/90 border border-emerald-200/80 space-y-2.5 animate-fadeIn"
                            >
                                <div class="flex items-center gap-2 text-emerald-800 text-xs font-bold">
                                    <svg class="size-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>¿Aprobar y publicar este artículo?</span>
                                </div>
                                <input
                                    v-model="approvalMotivo"
                                    type="text"
                                    placeholder="Nota opcional para el autor / editorial..."
                                    class="w-full text-xs px-2.5 py-1.5 rounded-lg border border-emerald-200 bg-white text-slate-800 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                />
                                <div class="flex items-center justify-end gap-2 pt-1">
                                    <button
                                        @click="cancelApprove"
                                        type="button"
                                        class="px-2.5 py-1 text-xs font-semibold text-slate-600 hover:text-slate-800 transition"
                                    >
                                        Cancelar
                                    </button>
                                    <button
                                        @click="executeApprove(item)"
                                        :disabled="isApproving"
                                        type="button"
                                        class="px-3 py-1 text-xs font-bold bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition shadow-xs flex items-center gap-1 cursor-pointer disabled:opacity-50"
                                    >
                                        <span v-if="isApproving">Aprobando...</span>
                                        <span v-else>Sí, Aprobar</span>
                                    </button>
                                </div>
                            </div>

                            <!-- CAJA INLINE: MOTIVO DE RECHAZO -->
                            <div
                                v-else-if="rejectingId === item.id"
                                class="p-3.5 rounded-xl bg-rose-50/90 border border-rose-200/80 space-y-2.5 animate-fadeIn"
                            >
                                <div class="flex items-center gap-2 text-rose-800 text-xs font-bold">
                                    <svg class="size-4 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                    </svg>
                                    <span>Motivo u observaciones de rechazo:</span>
                                </div>
                                <textarea
                                    v-model="rejectMotivo"
                                    rows="2"
                                    placeholder="Explica qué corregir o por qué no procede..."
                                    class="w-full text-xs px-2.5 py-1.5 rounded-lg border border-rose-200 bg-white text-slate-800 focus:outline-none focus:ring-1 focus:ring-rose-500"
                                ></textarea>
                                <p v-if="rejectError" class="text-[11px] text-rose-600 font-semibold">
                                    {{ rejectError }}
                                </p>
                                <div class="flex items-center justify-end gap-2 pt-1">
                                    <button
                                        @click="cancelReject"
                                        type="button"
                                        class="px-2.5 py-1 text-xs font-semibold text-slate-600 hover:text-slate-800 transition"
                                    >
                                        Cancelar
                                    </button>
                                    <button
                                        @click="executeReject(item)"
                                        :disabled="isRejecting"
                                        type="button"
                                        class="px-3 py-1 text-xs font-bold bg-rose-600 text-white rounded-lg hover:bg-rose-700 transition shadow-xs flex items-center gap-1 cursor-pointer disabled:opacity-50"
                                    >
                                        <span v-if="isRejecting">Rechazando...</span>
                                        <span v-else>Confirmar Rechazo</span>
                                    </button>
                                </div>
                            </div>

                            <!-- BOTONES DE ACCIÓN PRINCIPALES -->
                            <div v-else class="flex items-center justify-between gap-2 pt-2 border-t border-slate-100">
                                <Link
                                    :href="route('admin.articulos.show', item.id)"
                                    @click="emit('close')"
                                    class="px-3 py-1.5 text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition flex items-center gap-1.5"
                                >
                                    <svg class="size-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Revisar
                                </Link>

                                <div class="flex items-center gap-2">
                                    <button
                                        @click="openReject(item)"
                                        type="button"
                                        class="px-3 py-1.5 text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200/60 rounded-xl transition flex items-center gap-1 cursor-pointer"
                                    >
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Rechazar
                                    </button>

                                    <button
                                        @click="openApprove(item)"
                                        type="button"
                                        class="px-3 py-1.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition shadow-xs flex items-center gap-1 cursor-pointer"
                                    >
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                        </svg>
                                        Aprobar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- ============================================== -->
                    <!-- MODO: AUTOR (ESTADO DE SUS ARTÍCULOS)          -->
                    <!-- ============================================== -->
                    <template v-else-if="isAutor">
                        <div
                            v-if="solicitudes.length === 0"
                            class="py-14 px-6 text-center flex flex-col items-center justify-center space-y-3"
                        >
                            <div class="size-16 rounded-2xl bg-slate-50 border border-slate-200 text-slate-400 flex items-center justify-center">
                                <svg class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                            </div>
                            <h3 class="font-extrabold text-slate-800 text-base">
                                Sin artículos enviados aún
                            </h3>
                            <p class="text-xs text-slate-500 max-w-xs leading-relaxed">
                                Puedes enviar un nuevo artículo científico para que sea revisado por el comité editorial.
                            </p>
                            <Link
                                :href="route('admin.articulos.create')"
                                @click="emit('close')"
                                class="mt-2 px-4 py-2 text-xs font-bold text-white bg-[#35449F] hover:bg-[#2c3986] rounded-xl transition shadow-xs"
                            >
                                Enviar Artículo Científico
                            </Link>
                        </div>

                        <div
                            v-for="item in solicitudes"
                            :key="item.id"
                            class="bg-white rounded-2xl border border-slate-200/90 p-4 space-y-3 shadow-2xs hover:shadow-md transition"
                        >
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">
                                    {{ item.tipo_articulo }}
                                </span>
                                <!-- Badge de Estado -->
                                <span
                                    v-if="item.estado_revision === 'aprobado'"
                                    class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800"
                                >
                                    Aprobado y Publicado
                                </span>
                                <span
                                    v-else-if="item.estado_revision === 'rechazado'"
                                    class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800"
                                >
                                    Rechazado con Observaciones
                                </span>
                                <span
                                    v-else
                                    class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800"
                                >
                                    En Revisión Editorial
                                </span>
                            </div>

                            <h4 class="font-extrabold text-slate-900 text-sm leading-snug">
                                {{ item.art_titulo }}
                            </h4>

                            <div v-if="item.estado_revision === 'rechazado' && item.revision_motivo" class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-800 space-y-1">
                                <p class="font-bold">Observaciones del editor:</p>
                                <p class="italic leading-relaxed">{{ item.revision_motivo }}</p>
                            </div>

                            <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-xs">
                                <span class="text-slate-400">
                                    Actualizado: {{ formatRelativeTime(item.updated_at || item.created_at) }}
                                </span>

                                <Link
                                    :href="route('admin.articulos.show', item.id)"
                                    @click="emit('close')"
                                    class="font-bold text-[#35449F] hover:underline"
                                >
                                    Ver Detalle &rarr;
                                </Link>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Footer del Panel -->
                <div class="p-4 border-t border-slate-100 bg-slate-50/70 shrink-0 flex items-center justify-between text-xs text-slate-500">
                    <span class="font-medium">IETA • Revista Varianza</span>
                    <button
                        @click="emit('close')"
                        type="button"
                        class="px-3 py-1 font-bold text-slate-600 hover:text-slate-900 transition cursor-pointer"
                    >
                        Cerrar panel
                    </button>
                </div>
            </aside>
        </Transition>
    </Teleport>
</template>
