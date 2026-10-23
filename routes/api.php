<?php

use App\Http\Controllers\Api\V1\AreaTematicaController;
use App\Http\Controllers\Api\V1\ArticuloController;
use App\Http\Controllers\Api\V1\AutorController;
use App\Http\Controllers\Api\V1\CargoInstitucionalController;
use App\Http\Controllers\Api\V1\CmsBancoDatosController;
use App\Http\Controllers\Api\V1\CmsComiteEditorialController;
use App\Http\Controllers\Api\V1\CmsComunicadoController;
use App\Http\Controllers\Api\V1\CmsConfiguracionController;
use App\Http\Controllers\Api\V1\CmsCursoController;
use App\Http\Controllers\Api\V1\CmsGaleriaController;
use App\Http\Controllers\Api\V1\CmsNoticiasController;
use App\Http\Controllers\Api\V1\CmsReglamentoInternoController;
use App\Http\Controllers\Api\V1\CmsRevistasVariasController;
use App\Http\Controllers\Api\V1\CmsRevistaVarianzaController;
use App\Http\Controllers\Api\V1\ContactoController;
use App\Http\Controllers\Api\V1\CursoModalidadController;
use App\Http\Controllers\Api\V1\EdicionVarianzaController;
use App\Http\Controllers\Api\V1\EspecialidadController;
use App\Http\Controllers\Api\V1\EtiquetaArtApiController;
use App\Http\Controllers\Api\V1\GradoAcademicoController;
use App\Http\Controllers\Api\V1\InstitucionController;
use App\Http\Controllers\Api\V1\InstruccionAutorApiController;
use App\Http\Controllers\Api\V1\LineaInvestigacionController;
use App\Http\Controllers\Api\V1\MemoriasInvestigacionController;
use App\Http\Controllers\Api\V1\PersonalController;
use App\Http\Controllers\Api\V1\ServicioController;
use App\Http\Controllers\Api\V1\SobreNosotrosController;
use App\Http\Controllers\Api\V1\TipoArticuloController;
use App\Http\Controllers\Api\V1\TipoAutorController;
use App\Http\Controllers\Api\V1\TipoCursoController;
use App\Http\Controllers\Api\V1\TipoParticipacionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/lineas-investigacion', [LineaInvestigacionController::class, 'lineasTodos']);
Route::get('/lineas-investigacion/{id}', [LineaInvestigacionController::class, 'lineasPorId']);

Route::get('/sobre-nosotros', [SobreNosotrosController::class, 'show']);

Route::get('/servicios', [ServicioController::class, 'index']);
Route::get('/servicios/{id}', [ServicioController::class, 'show']);

Route::get('/noticias', [CmsNoticiasController::class, 'index']);
Route::get('/noticias/{id}', [CmsNoticiasController::class, 'show']);

Route::get('/contacto', [ContactoController::class, 'show']);

// Módulo Cursos API (REST)
Route::get('/tipo-cursos', [TipoCursoController::class, 'index']);
Route::get('/tipo-cursos/{id}', [TipoCursoController::class, 'show']);

Route::get('/curso-modalidades', [CursoModalidadController::class, 'index']);
Route::get('/curso-modalidades/{id}', [CursoModalidadController::class, 'show']);

Route::get('/cursos', [CmsCursoController::class, 'index']);
Route::get('/cursos/{id}', [CmsCursoController::class, 'show']);

// Módulo Publicaciones, Autores e Investigación API (REST)
Route::get('/especialidades', [EspecialidadController::class, 'index']);
Route::get('/especialidades/{id}', [EspecialidadController::class, 'show']);

Route::get('/grado-academicos', [GradoAcademicoController::class, 'index']);
Route::get('/grado-academicos/{id}', [GradoAcademicoController::class, 'show']);

Route::get('/tipo-autores', [TipoAutorController::class, 'index']);
Route::get('/tipo-autores/{id}', [TipoAutorController::class, 'show']);

Route::get('/tipo-participaciones', [TipoParticipacionController::class, 'index']);
Route::get('/tipo-participaciones/{id}', [TipoParticipacionController::class, 'show']);

Route::get('/autores', [AutorController::class, 'index']);
Route::get('/autores/{id}', [AutorController::class, 'show']);

// Etiquetas de Artículos API (REST)
Route::get('/etiquetas-art', [EtiquetaArtApiController::class, 'index']);
Route::get('/etiquetas-art/{idOrSlug}', [EtiquetaArtApiController::class, 'show']);

Route::get('/articulos', [ArticuloController::class, 'index']);
Route::get('/articulos/{idOrSlug}', [ArticuloController::class, 'show']);
Route::get('/articulos/{idOrSlug}/similares', [ArticuloController::class, 'similares']);
Route::get('/articulos/{idOrSlug}/mas-leidos-autor', [ArticuloController::class, 'masLeidosAutor']);

Route::get('/memorias-investigacion', [MemoriasInvestigacionController::class, 'index']);
Route::get('/memorias-investigacion/{id}', [MemoriasInvestigacionController::class, 'show']);

// Módulo Banco de Datos API (REST)
Route::get('/area-tematicas', [AreaTematicaController::class, 'index']);
Route::get('/area-tematicas/{id}', [AreaTematicaController::class, 'show']);

Route::get('/banco-datos', [CmsBancoDatosController::class, 'index']);
Route::get('/banco-datos/{id}', [CmsBancoDatosController::class, 'show']);

// Módulo Institucional / Equipo API (REST)
Route::get('/cargos-institucionales', [CargoInstitucionalController::class, 'index']);
Route::get('/cargos-institucionales/{id}', [CargoInstitucionalController::class, 'show']);

Route::get('/instituciones', [InstitucionController::class, 'index']);
Route::get('/instituciones/{id}', [InstitucionController::class, 'show']);

Route::get('/comite-editorial', [CmsComiteEditorialController::class, 'index']);
Route::get('/comite-editorial/{id}', [CmsComiteEditorialController::class, 'show']);

Route::get('/personal', [PersonalController::class, 'index']);
Route::get('/personal/{id}', [PersonalController::class, 'show']);

// Módulo Comunicados & Novedades Emergentes API (REST)
Route::get('/comunicados', [CmsComunicadoController::class, 'index']);
Route::get('/comunicados/activo', [CmsComunicadoController::class, 'activo']);
Route::get('/comunicados/{id}', [CmsComunicadoController::class, 'show']);

// Módulo Revista Varianza & Ediciones Científicas API (REST)
Route::get('/revista-varianza', [CmsRevistaVarianzaController::class, 'show']);
Route::get('/tipo-articulos', [TipoArticuloController::class, 'index']);
Route::get('/ediciones-varianza', [EdicionVarianzaController::class, 'index']);
Route::get('/ediciones-varianza/actual', [EdicionVarianzaController::class, 'actual']);
Route::get('/ediciones-varianza/{idOrSlug}', [EdicionVarianzaController::class, 'show']);
Route::get('/instrucciones-autores', [InstruccionAutorApiController::class, 'index']);
Route::get('/instrucciones-autores/{id}', [InstruccionAutorApiController::class, 'show']);

// Módulo Galería de Imágenes API (REST)
Route::get('/galeria', [CmsGaleriaController::class, 'index']);
Route::get('/galeria/destacados', [CmsGaleriaController::class, 'destacados']);
Route::get('/galeria/categorias', [CmsGaleriaController::class, 'categorias']);
Route::get('/galeria/{id}', [CmsGaleriaController::class, 'show']);

// Módulo Revistas de Referencia API (REST)
Route::get('/revistas-varias', [CmsRevistasVariasController::class, 'index']);
Route::get('/revistas-varias/{id}', [CmsRevistasVariasController::class, 'show']);

// Módulo Reglamento Interno Institucional API (REST)
Route::get('/reglamento-interno', [CmsReglamentoInternoController::class, 'show']);

// Módulo Configuración Institucional API (REST)
Route::get('/configuracion', [CmsConfiguracionController::class, 'show']);
