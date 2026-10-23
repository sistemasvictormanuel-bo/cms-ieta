<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AreaTematicas;
use App\Models\Articulo;
use App\Models\Autor;
use App\Models\CmsBancoDatos;
use App\Models\CmsComiteEditorial;
use App\Models\CmsCursos;
use App\Models\EdicionVarianza;
use App\Models\Personal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response|RedirectResponse
    {
        if ($request->user() && $request->user()->hasRole('autor')) {
            return redirect()->route('admin.articulos.index');
        }

        // Conteos principales del sistema IETA con safe null
        $totalArticulos = Articulo::count() ?? 0;
        $totalBancoDatos = CmsBancoDatos::count() ?? 0;
        $totalAutores = Autor::count() ?? 0;
        $totalCursos = CmsCursos::count() ?? 0;
        $totalPersonal = Personal::count() ?? 0;
        $totalComite = CmsComiteEditorial::count() ?? 0;

        // Publicaciones recientes para la tabla del repositorio
        $articulosRecientes = Articulo::with(['autores.persona', 'tipoArticulo'])
            ->latest('id')
            ->take(6)
            ->get()
            ->map(function ($item) {
                $autorPrincipal = $item->autores->first();
                $nombreAutor = $autorPrincipal?->persona?->nombre_completo ?? 'Investigador IETA';

                return [
                    'id' => $item->id,
                    'titulo' => $item->art_titulo ?? 'Publicación de Investigación',
                    'autor' => $nombreAutor,
                    'plataforma' => $item->tipoArticulo?->ta_nombre ?? 'Artículo Científico',
                    'estado' => $item->art_estado ? 'Publicado' : 'Borrador',
                    'fecha' => $item->created_at ? $item->created_at->format('d M, Y') : 'Reciente',
                ];
            });

        // Áreas temáticas con conteo de bases de datos
        $areasTematicas = AreaTematicas::withCount('bancoDatos')
            ->orderByDesc('banco_datos_count')
            ->take(4)
            ->get()
            ->map(function ($area) {
                return [
                    'nombre' => $area->at_nombre ?? 'Área Temática',
                    'count' => $area->banco_datos_count ?? 0,
                ];
            });

        // FEED DE ACTIVIDAD RECIENTE / LOG DE AUDITORÍA (Inspirado en Bonsai)
        $actividades = collect();

        // 1. Artículos recientes
        $ultimosArticulos = Articulo::with(['autores.persona', 'tipoArticulo'])->latest('id')->take(4)->get();
        foreach ($ultimosArticulos as $art) {
            $autor = $art->autores->first()?->persona?->nombre_completo ?? 'Un investigador';
            $actividades->push([
                'id' => 'art-'.$art->id,
                'modulo' => 'Artículos',
                'color' => 'indigo',
                'icono' => 'document',
                'descripcion' => "{$autor} publicó el artículo \"{$art->art_titulo}\"",
                'detalle' => $art->tipoArticulo?->ta_nombre ?? 'Artículo Científico',
                'fecha' => $art->created_at ? $art->created_at->format('d M, Y H:i') : null,
                'tiempo_relativo' => $art->created_at ? $art->created_at->diffForHumans() : 'Reciente',
                'timestamp' => $art->created_at ? $art->created_at->timestamp : 0,
            ]);
        }

        // 2. Bases de datos recientes
        $ultimosBancos = CmsBancoDatos::latest('id')->take(4)->get();
        foreach ($ultimosBancos as $bdd) {
            $actividades->push([
                'id' => 'bdd-'.$bdd->id,
                'modulo' => 'Banco de Datos',
                'color' => 'cyan',
                'icono' => 'database',
                'descripcion' => "Se registró el recurso \"{$bdd->bdd_titulo}\" en Banco de Datos",
                'detalle' => $bdd->bdd_locacion ? "Locación: {$bdd->bdd_locacion}" : 'Repositorio Científico',
                'fecha' => $bdd->created_at ? $bdd->created_at->format('d M, Y H:i') : null,
                'tiempo_relativo' => $bdd->created_at ? $bdd->created_at->diffForHumans() : 'Reciente',
                'timestamp' => $bdd->created_at ? $bdd->created_at->timestamp : 0,
            ]);
        }

        // 3. Autores registrados recientemente
        $ultimosAutores = Autor::with('persona')->latest('id')->take(3)->get();
        foreach ($ultimosAutores as $aut) {
            $nombre = $aut->persona?->nombre_completo ?? 'Nuevo Autor';
            $actividades->push([
                'id' => 'aut-'.$aut->id,
                'modulo' => 'Autores',
                'color' => 'emerald',
                'icono' => 'user',
                'descripcion' => "Se acreditó al investigador(a) {$nombre}",
                'detalle' => $aut->tipoAutor?->ta_nombre ?? 'Investigador Registrado',
                'fecha' => $aut->created_at ? $aut->created_at->format('d M, Y H:i') : null,
                'tiempo_relativo' => $aut->created_at ? $aut->created_at->diffForHumans() : 'Reciente',
                'timestamp' => $aut->created_at ? $aut->created_at->timestamp : 0,
            ]);
        }

        // 4. Personal incorporado recientemente
        $ultimosPersonales = Personal::with(['persona', 'cargo'])->latest('id')->take(3)->get();
        foreach ($ultimosPersonales as $per) {
            $nombre = $per->persona?->nombre_completo ?? 'Miembro del Personal';
            $cargo = $per->cargo?->ci_nombre ?? 'Personal Institucional';
            $actividades->push([
                'id' => 'per-'.$per->id,
                'modulo' => 'Personal',
                'color' => 'slate',
                'icono' => 'briefcase',
                'descripcion' => "Se incorporó {$nombre} al equipo institucional ({$cargo})",
                'detalle' => "Cargo: {$cargo}",
                'fecha' => $per->created_at ? $per->created_at->format('d M, Y H:i') : null,
                'tiempo_relativo' => $per->created_at ? $per->created_at->diffForHumans() : 'Reciente',
                'timestamp' => $per->created_at ? $per->created_at->timestamp : 0,
            ]);
        }

        // 5. Cursos registrados
        $ultimosCursos = CmsCursos::latest('id')->take(3)->get();
        foreach ($ultimosCursos as $cur) {
            $actividades->push([
                'id' => 'cur-'.$cur->id,
                'modulo' => 'Cursos',
                'color' => 'indigo',
                'icono' => 'academic',
                'descripcion' => "Se habilitó la oferta formativa \"{$cur->cur_titulo}\"",
                'detalle' => 'Curso Académico IETA',
                'fecha' => $cur->created_at ? $cur->created_at->format('d M, Y H:i') : null,
                'tiempo_relativo' => $cur->created_at ? $cur->created_at->diffForHumans() : 'Reciente',
                'timestamp' => $cur->created_at ? $cur->created_at->timestamp : 0,
            ]);
        }

        // Ordenar eventos de actividad de más reciente a más antiguo
        $actividadesRecientes = $actividades
            ->sortByDesc('timestamp')
            ->values()
            ->take(8);

        // Donut Chart: Distribución del Acervo Científico e Institucional
        $donutData = [
            'series' => [
                $totalArticulos,
                $totalBancoDatos,
                $totalCursos,
                $totalAutores,
                $totalPersonal + $totalComite,
            ],
            'labels' => [
                'Artículos Científicos',
                'Bases de Datos',
                'Cursos & Talleres',
                'Investigadores',
                'Equipo Institucional',
            ],
        ];

        // Estadísticas de Producción Científica por Edición Semestral (Revista Varianza)
        $ediciones = EdicionVarianza::withCount('articulos')
            ->orderBy('ev_anio', 'asc')
            ->orderBy('ev_numero', 'asc')
            ->get();

        $chartCategories = [];
        $dataArticulosOriginales = [];
        $dataNotasCientificas = [];

        if ($ediciones->isNotEmpty()) {
            foreach ($ediciones as $ed) {
                $chartCategories[] = "N° {$ed->ev_numero} ({$ed->ev_anio})";

                // Contar artículos originales: preferir conteo directo si existe, o el número declarado en la edición
                $numArticulos = $ed->articulos_count > 0
                    ? max($ed->articulos_count, (int) $ed->ev_num_art_originales)
                    : (int) ($ed->ev_num_art_originales ?? 0);

                $dataArticulosOriginales[] = $numArticulos;
                $dataNotasCientificas[] = (int) ($ed->ev_notas_cientificas ?? 0);
            }
        } else {
            // Fallback si no hay ediciones registradas aún
            $chartCategories = ['2024 - I', '2024 - II', '2025 - I', '2025 - II'];
            $dataArticulosOriginales = [0, 0, 0, $totalArticulos];
            $dataNotasCientificas = [0, 0, 0, 0];
        }

        $chartSeries = [
            [
                'name' => 'Artículos Originales',
                'data' => $dataArticulosOriginales,
            ],
            [
                'name' => 'Notas Científicas',
                'data' => $dataNotasCientificas,
            ],
        ];

        // Estado del Flujo Editorial (Pipeline Científico hacia próximas publicaciones)
        $flujoEditorial = [
            'aprobados' => Articulo::where('estado_revision', 'aprobado')->count(),
            'en_revision' => Articulo::where(function ($q) {
                $q->where('estado_revision', 'pendiente')->orWhereNull('estado_revision');
            })->count(),
            'borradores' => Articulo::where('estado_revision', 'borrador')->orWhere('art_estado', false)->count(),
            'total_ediciones' => $ediciones->count(),
        ];

        return Inertia::render('Dashboard', [
            'metrics' => [
                'total_articulos' => $totalArticulos,
                'total_banco_datos' => $totalBancoDatos,
                'total_autores' => $totalAutores,
                'total_cursos' => $totalCursos,
                'total_personal' => $totalPersonal,
                'total_comite' => $totalComite,
            ],
            'chart_series' => $chartSeries,
            'chart_categories' => $chartCategories,
            'flujo_editorial' => $flujoEditorial,
            'donut_data' => $donutData,
            'articulos_recientes' => $articulosRecientes,
            'actividad_reciente' => $actividadesRecientes,
            'areas_tematicas' => $areasTematicas,
        ]);
    }
}
