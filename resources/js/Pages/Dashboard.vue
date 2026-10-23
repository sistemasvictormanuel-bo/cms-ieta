<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    metrics: {
        type: Object,
        default: () => ({
            total_articulos: 0,
            total_banco_datos: 0,
            total_autores: 0,
            total_cursos: 0,
            total_personal: 0,
            total_comite: 0,
        }),
    },
    chart_series: {
        type: Array,
        default: () => [
            { name: 'Artículos Registrados', data: [0, 0, 0, 0, 0, 0] },
            { name: 'Bases de Datos Agregadas', data: [0, 0, 0, 0, 0, 0] },
        ],
    },
    chart_categories: {
        type: Array,
        default: () => ['May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct'],
    },
    donut_data: {
        type: Object,
        default: () => ({
            series: [0, 0, 0, 0, 0],
            labels: [
                'Artículos Científicos',
                'Banco de Datos',
                'Cursos & Talleres',
                'Investigadores',
                'Equipo Institucional',
            ],
        }),
    },
    articulos_recientes: {
        type: Array,
        default: () => [],
    },
    actividad_reciente: {
        type: Array,
        default: () => [],
    },
    areas_tematicas: {
        type: Array,
        default: () => [],
    },
    flujo_editorial: {
        type: Object,
        default: () => ({
            aprobados: 0,
            en_revision: 0,
            borradores: 0,
            total_ediciones: 0,
        }),
    },
});

// Suma total para cálculo de porcentajes del acervo
const totalAcervo = computed(() => {
    const s = props.donut_data?.series || [];
    return s.reduce((acc, val) => acc + (Number(val) || 0), 0);
});

// Opciones del gráfico de Columnas por Edición Semestral (ApexCharts)
const columnChartOptions = computed(() => ({
    chart: {
        type: 'bar',
        height: 270,
        toolbar: { show: false },
        fontFamily: 'inherit',
    },
    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: '38%',
            borderRadius: 6,
            borderRadiusApplication: 'end',
        },
    },
    colors: ['#35449F', '#06B6D4'], // Azul Institucional IETA y Cyan
    dataLabels: { enabled: false },
    stroke: {
        show: true,
        width: 3,
        colors: ['transparent'],
    },
    xaxis: {
        categories: props.chart_categories?.length ? props.chart_categories : ['Ed. 24 (2024)', 'Ed. 25 (2024)'],
        labels: {
            style: {
                colors: '#64748B',
                fontSize: '12px',
                fontWeight: 700,
            },
        },
        axisBorder: { show: false },
        axisTicks: { show: false },
    },
    yaxis: {
        labels: {
            style: {
                colors: '#64748B',
                fontSize: '12px',
                fontWeight: 600,
            },
            formatter: (val) => Math.round(val),
        },
        min: 0,
    },
    grid: {
        borderColor: '#F1F5F9',
        strokeDashArray: 4,
        yaxis: { lines: { show: true } },
    },
    legend: { show: false },
    tooltip: {
        theme: 'dark',
        y: {
            formatter: (val) => `${val} publicaciones`,
        },
    },
}));

// Opciones del gráfico Donut (ApexCharts)
const donutChartOptions = computed(() => ({
    chart: {
        type: 'donut',
        height: 230,
        fontFamily: 'inherit',
    },
    colors: ['#35449F', '#06B6D4', '#10B981', '#6366F1', '#64748B'], // Colores corporativos IETA
    dataLabels: { enabled: false },
    legend: { show: false },
    stroke: { width: 0 },
    plotOptions: {
        pie: {
            donut: {
                size: '72%',
                labels: {
                    show: true,
                    name: {
                        show: true,
                        fontSize: '12px',
                        fontWeight: 600,
                        color: '#64748B',
                        offsetY: -4,
                    },
                    value: {
                        show: true,
                        fontSize: '22px',
                        fontWeight: 800,
                        color: '#0F172A',
                        offsetY: 6,
                        formatter: () => `${totalAcervo.value}`,
                    },
                    total: {
                        show: true,
                        label: 'TOTAL ACERVO',
                        color: '#64748B',
                        fontSize: '11px',
                        fontWeight: 700,
                        formatter: () => `${totalAcervo.value}`,
                    },
                },
            },
        },
    },
    tooltip: {
        theme: 'dark',
    },
}));

// Formateador seguro de números
const formatNum = (num) => {
    if (num === null || num === undefined) return '0';
    return Number(num).toLocaleString();
};
</script>

<template>
    <AppLayout title="Panel de Control">
        <div class="space-y-6">
            <!-- ======================================================== -->
            <!-- HEADER DE DASHBOARD INSTITUCIONAL IETA                   -->
            <!-- ======================================================== -->
            <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/90 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <span>Panel de Gestión & Producción Científica IETA</span>
                    </h1>
                    <p class="text-sm text-slate-600 font-medium mt-1">
                        Métricas de Investigación, Repositorio Institucional y Producción Académica
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    <!-- Pill Live Status -->
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                        <span class="size-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        SISTEMA ACTIVO
                    </div>

                    <!-- Botón de acceso directo a Nuevo Artículo -->
                    <Link
                        :href="route('admin.articulos.create')"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50 transition shadow-2xs cursor-pointer"
                    >
                        <svg class="size-4 text-[#35449F]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>Nuevo Artículo</span>
                    </Link>

                    <!-- Botón de Banco de Datos -->
                    <Link
                        :href="route('admin.banco-datos.create')"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#35449F] hover:bg-[#2a3680] text-white text-xs font-bold shadow-xs transition cursor-pointer"
                    >
                        <span>+ Cargar Base de Datos</span>
                    </Link>
                </div>
            </div>

            <!-- ======================================================== -->
            <!-- 5 TARJETAS DE MÉTRICAS CON VALORES REALES Y SAFE NULL    -->
            <!-- ======================================================== -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Tarjeta 1: Artículos Científicos (Borde Azul IETA) -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-2xs border-t-4 border-t-[#35449F]">
                    <p class="text-xs font-extrabold uppercase tracking-wider text-slate-600">ARTÍCULOS CIENTÍFICOS</p>
                    <p class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-1.5">
                        {{ formatNum(metrics?.total_articulos) }}
                    </p>
                    <div class="flex items-center gap-2 mt-2 text-xs">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-indigo-50 text-indigo-700">
                            Publicaciones
                        </span>
                        <span class="text-slate-500 font-medium">En Repositorio</span>
                    </div>
                </div>

                <!-- Tarjeta 2: Banco de Datos (Borde Cyan) -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-2xs border-t-4 border-t-cyan-500">
                    <p class="text-xs font-extrabold uppercase tracking-wider text-slate-600">BANCO DE DATOS</p>
                    <p class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-1.5">
                        {{ formatNum(metrics?.total_banco_datos) }}
                    </p>
                    <div class="flex items-center gap-2 mt-2 text-xs">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-cyan-50 text-cyan-700">
                            Bases Activas
                        </span>
                        <span class="text-slate-500 font-medium">Descargables</span>
                    </div>
                </div>

                <!-- Tarjeta 3: Investigadores / Autores (Borde Esmeralda) -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-2xs border-t-4 border-t-emerald-500">
                    <p class="text-xs font-extrabold uppercase tracking-wider text-slate-600">INVESTIGADORES</p>
                    <p class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-1.5">
                        {{ formatNum(metrics?.total_autores) }}
                    </p>
                    <div class="flex items-center gap-2 mt-2 text-xs">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-emerald-50 text-emerald-700">
                            Autores
                        </span>
                        <span class="text-slate-500 font-medium">Acreditados</span>
                    </div>
                </div>

                <!-- Tarjeta 4: Cursos y Programas (Borde Violeta) -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-2xs border-t-4 border-t-indigo-500">
                    <p class="text-xs font-extrabold uppercase tracking-wider text-slate-600">CURSOS & TALLERES</p>
                    <p class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-1.5">
                        {{ formatNum(metrics?.total_cursos) }}
                    </p>
                    <div class="flex items-center gap-2 mt-2 text-xs">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-indigo-50 text-indigo-700">
                            Oferta Formativa
                        </span>
                        <span class="text-slate-500 font-medium">Disponibles</span>
                    </div>
                </div>

                <!-- Tarjeta 5: Personal & Comités (Borde Slate) -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-2xs border-t-4 border-t-slate-600">
                    <p class="text-xs font-extrabold uppercase tracking-wider text-slate-600">EQUIPO & COMITÉ</p>
                    <p class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-1.5">
                        {{ formatNum((metrics?.total_personal ?? 0) + (metrics?.total_comite ?? 0)) }}
                    </p>
                    <div class="flex items-center gap-2 mt-2 text-xs">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-slate-100 text-slate-700">
                            {{ metrics?.total_personal ?? 0 }} Staff
                        </span>
                        <span class="text-slate-500 font-medium">{{ metrics?.total_comite ?? 0 }} Comité</span>
                    </div>
                </div>
            </div>

            <!-- ======================================================== -->
            <!-- FILA MEDIA: GRÁFICO DE PRODUCCIÓN (AREA) + DONUT ACERVO  -->
            <!-- ======================================================== -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Gráfico de Columnas por Edición Semestral (8 columnas) -->
                <div class="lg:col-span-8 bg-white rounded-2xl p-6 border border-slate-200/90 shadow-2xs flex flex-col justify-between">
                    <div>
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                            <div>
                                <div class="flex items-center gap-2.5">
                                    <h2 class="text-base sm:text-lg font-black text-slate-900 tracking-tight">
                                        Producción Científica por Edición (Revista Varianza)
                                    </h2>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase bg-indigo-50 text-[#35449F] border border-indigo-200/60">
                                        Semestral • 2 Vol/Año
                                    </span>
                                </div>
                                <p class="text-[13px] text-slate-500 font-medium mt-0.5">
                                    Artículos originales y notas científicas publicadas en cada volumen indexado
                                </p>
                            </div>

                            <!-- Indicadores de leyendas superiores -->
                            <div class="flex items-center gap-4 text-xs font-bold shrink-0">
                                <div class="flex items-center gap-1.5">
                                    <span class="size-2.5 rounded-sm bg-[#35449F]"></span>
                                    <span class="text-slate-700">Artículos Originales</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="size-2.5 rounded-sm bg-cyan-500"></span>
                                    <span class="text-slate-700">Notas Científicas</span>
                                </div>
                            </div>
                        </div>

                        <!-- Componente ApexCharts (Column Bar) -->
                        <div class="mt-2 -ml-2">
                            <apexchart
                                type="bar"
                                height="270"
                                :options="columnChartOptions"
                                :series="chart_series"
                            />
                        </div>
                    </div>

                    <!-- Resumen del Flujo Editorial hacia la Próxima Edición -->
                    <div class="mt-4 pt-3 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                        <div class="p-2.5 rounded-xl bg-emerald-50/70 border border-emerald-100/80 flex items-center justify-between">
                            <div>
                                <span class="text-[11px] font-extrabold text-emerald-800 block">Listos / Aprobados</span>
                                <span class="text-[10px] text-emerald-600 font-medium">Para próxima edición</span>
                            </div>
                            <span class="text-xl font-black text-emerald-900">{{ flujo_editorial?.aprobados ?? 0 }}</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-amber-50/70 border border-amber-100/80 flex items-center justify-between">
                            <div>
                                <span class="text-[11px] font-extrabold text-amber-800 block">En Evaluación</span>
                                <span class="text-[10px] text-amber-600 font-medium">Revisión por pares</span>
                            </div>
                            <span class="text-xl font-black text-amber-900">{{ flujo_editorial?.en_revision ?? 0 }}</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between">
                            <div>
                                <span class="text-[11px] font-extrabold text-slate-700 block">Borradores</span>
                                <span class="text-[10px] text-slate-500 font-medium">En preparación</span>
                            </div>
                            <span class="text-xl font-black text-slate-800">{{ flujo_editorial?.borradores ?? 0 }}</span>
                        </div>
                    </div>
                </div>

                <!-- Gráfico Donut (4 columnas) -->
                <div class="lg:col-span-4 bg-white rounded-2xl p-6 border border-slate-200/90 shadow-2xs flex flex-col justify-between">
                    <div>
                        <h2 class="text-base sm:text-lg font-extrabold text-slate-900 tracking-tight">
                            Composición del Acervo Digital
                        </h2>
                        <p class="text-[13px] text-slate-500 font-medium mb-3 mt-0.5">
                            Distribución de activos y recursos registrados
                        </p>

                        <div v-if="totalAcervo > 0" class="flex flex-col items-center justify-center pt-1">
                            <apexchart
                                type="donut"
                                height="220"
                                :options="donutChartOptions"
                                :series="donut_data.series || [0, 0, 0, 0, 0]"
                            />
                        </div>
                        <div v-else class="py-12 text-center text-slate-400 text-xs">
                            <svg class="size-9 mx-auto text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                            </svg>
                            <span class="font-medium text-slate-500">Sin registros para calcular distribución</span>
                        </div>

                        <!-- Lista detallada del Donut -->
                        <div class="mt-3 space-y-2.5 text-[13px] border-t border-slate-100 pt-3.5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="size-3 rounded-[3px] bg-[#35449F]"></span>
                                    <span class="font-semibold text-slate-700">Artículos Científicos</span>
                                </div>
                                <div class="text-right font-bold text-slate-900">
                                    {{ donut_data.series?.[0] ?? 0 }}
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="size-3 rounded-[3px] bg-cyan-500"></span>
                                    <span class="font-semibold text-slate-700">Banco de Datos</span>
                                </div>
                                <div class="text-right font-bold text-slate-900">
                                    {{ donut_data.series?.[1] ?? 0 }}
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="size-3 rounded-[3px] bg-emerald-500"></span>
                                    <span class="font-semibold text-slate-700">Cursos & Talleres</span>
                                </div>
                                <div class="text-right font-bold text-slate-900">
                                    {{ donut_data.series?.[2] ?? 0 }}
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="size-3 rounded-[3px] bg-indigo-500"></span>
                                    <span class="font-semibold text-slate-700">Investigadores</span>
                                </div>
                                <div class="text-right font-bold text-slate-900">
                                    {{ donut_data.series?.[3] ?? 0 }}
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="size-3 rounded-[3px] bg-slate-500"></span>
                                    <span class="font-semibold text-slate-700">Equipo Institucional</span>
                                </div>
                                <div class="text-right font-bold text-slate-900">
                                    {{ donut_data.series?.[4] ?? 0 }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ======================================================== -->
            <!-- FILA INFERIOR: REPOSITORIO RECIENTE + FEED DE ACTIVIDAD  -->
            <!-- ======================================================== -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Tabla de Artículos Recientes (7 columnas) -->
                <div class="lg:col-span-7 bg-white rounded-2xl p-6 border border-slate-200/90 shadow-2xs flex flex-col justify-between">
                    <div>
                        <div class="pb-3.5 border-b border-slate-100 mb-3 flex items-center justify-between">
                            <div>
                                <h2 class="text-base sm:text-lg font-extrabold text-slate-900 tracking-tight">
                                    Últimos Artículos en Repositorio
                                </h2>
                                <p class="text-[13px] text-slate-500 font-medium">
                                    Publicaciones e investigaciones académicas acreditadas
                                </p>
                            </div>
                            <Link :href="route('admin.articulos.index')" class="text-xs font-bold text-[#35449F] hover:underline">
                                Ver todos →
                            </Link>
                        </div>

                        <div v-if="articulos_recientes && articulos_recientes.length > 0" class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead>
                                    <tr class="text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                                        <th class="py-3 pr-3">TÍTULO DEL ARTÍCULO</th>
                                        <th class="py-3 px-3">INVESTIGADOR</th>
                                        <th class="py-3 px-3">TIPO</th>
                                        <th class="py-3 pl-3 text-right">FECHA</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                                    <tr v-for="item in articulos_recientes" :key="item.id" class="hover:bg-slate-50/70 transition-colors">
                                        <td class="py-3.5 pr-3 font-bold text-slate-900 truncate max-w-[220px]" :title="item.titulo">
                                            {{ item.titulo }}
                                        </td>
                                        <td class="py-3.5 px-3 text-slate-600 font-semibold truncate max-w-[140px]">
                                            {{ item.autor }}
                                        </td>
                                        <td class="py-3.5 px-3">
                                            <span class="px-2.5 py-0.5 rounded text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200/80 inline-block">
                                                {{ item.plataforma }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 pl-3 text-right text-slate-500 text-xs font-semibold whitespace-nowrap">
                                            {{ item.fecha }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Estado vacío con Safe Null -->
                        <div v-else class="py-12 text-center text-slate-400">
                            <svg class="size-11 mx-auto text-slate-300 mb-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p class="text-sm font-bold text-slate-700">No se registran artículos en el sistema aún</p>
                            <p class="text-xs text-slate-500 font-medium mt-0.5">Comienza creando tu primera publicación científica</p>
                            <Link :href="route('admin.articulos.create')" class="mt-3.5 inline-block px-4 py-2 bg-[#35449F] text-white rounded-xl text-xs font-bold hover:bg-[#2a3680]">
                                + Registrar Artículo
                            </Link>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-3.5 border-t border-slate-100 text-xs text-slate-500 font-medium mt-4">
                        <span>Total de artículos en acervo: <strong class="text-slate-800">{{ metrics?.total_articulos ?? 0 }}</strong></span>
                        <Link :href="route('admin.articulos.index')" class="font-bold text-[#35449F] hover:text-[#2a3680] transition">
                            Gestionar Repositorio →
                        </Link>
                    </div>
                </div>

                <!-- ======================================================== -->
                <!-- MÓDULO DE ACTIVIDAD / LOG RECIENTE (INSPIRACIÓN BONSAI)   -->
                <!-- ======================================================== -->
                <div class="lg:col-span-5 bg-white rounded-2xl p-6 border border-slate-200/90 shadow-2xs flex flex-col justify-between">
                    <div>
                        <div class="pb-3.5 border-b border-slate-100 mb-4 flex items-center justify-between">
                            <div>
                                <h2 class="text-base sm:text-lg font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                                    <span>Registro de Actividad / Log</span>
                                    <span class="size-2 rounded-full bg-emerald-500"></span>
                                </h2>
                                <p class="text-[13px] text-slate-500 font-medium">
                                    Operaciones y eventos recientes de los usuarios
                                </p>
                            </div>
                            <span class="text-xs font-bold text-slate-500 bg-slate-50 px-2.5 py-1 rounded-lg border border-slate-200">
                                En vivo
                            </span>
                        </div>

                        <!-- Timeline Bonsai Style -->
                        <div v-if="actividad_reciente && actividad_reciente.length > 0" class="relative pl-6 space-y-4 before:absolute before:left-2 before:top-2 before:bottom-2 before:w-[2px] before:bg-slate-200">
                            <div
                                v-for="(act, idx) in actividad_reciente"
                                :key="act.id || idx"
                                class="relative group"
                            >
                                <!-- Nodo circular del timeline (Hollow circle estilo Bonsai) -->
                                <div
                                    class="absolute -left-6 top-1 size-4 rounded-full bg-white border-2 border-emerald-500 group-hover:bg-emerald-500 group-hover:scale-110 transition-transform duration-200"
                                ></div>

                                <!-- Contenido de la actividad -->
                                <div>
                                    <p class="text-[13px] text-slate-800 leading-snug">
                                        <span class="font-bold text-slate-900">{{ act.descripcion }}</span>
                                    </p>
                                    <div class="flex items-center gap-2 mt-1 text-xs text-slate-500 font-medium">
                                        <span
                                            :class="{
                                                'bg-indigo-50 text-indigo-700 border-indigo-200/70': act.color === 'indigo',
                                                'bg-cyan-50 text-cyan-700 border-cyan-200/70': act.color === 'cyan',
                                                'bg-emerald-50 text-emerald-700 border-emerald-200/70': act.color === 'emerald',
                                                'bg-slate-100 text-slate-700 border-slate-200': act.color === 'slate',
                                            }"
                                            class="px-2 py-0.5 rounded text-[11px] font-bold border inline-block"
                                        >
                                            {{ act.modulo }}
                                        </span>
                                        <span>•</span>
                                        <span>{{ act.tiempo_relativo }}</span>
                                        <span v-if="act.fecha" class="text-slate-400">• {{ act.fecha }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Estado vacío seguro para Actividad -->
                        <div v-else class="py-12 text-center text-slate-400">
                            <div class="size-10 rounded-full bg-slate-50 border border-slate-200 flex items-center justify-center mx-auto mb-2.5 text-slate-400">
                                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <p class="text-sm font-bold text-slate-700">Sin actividad reciente registrada</p>
                            <p class="text-xs text-slate-500 font-medium mt-0.5">Los eventos de publicaciones y autores aparecerán aquí automáticamente</p>
                        </div>
                    </div>

                    <div class="pt-3.5 border-t border-slate-100 text-xs text-slate-500 font-medium flex items-center justify-between mt-4">
                        <span>Eventos monitoreados en tiempo real</span>
                        <span class="font-bold text-slate-700">IETA Core Audit</span>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
