<?php

use App\Http\Controllers\Admin\AreaTematicasController;
use App\Http\Controllers\Admin\ArticuloController;
use App\Http\Controllers\Admin\AutorController;
use App\Http\Controllers\Admin\CargoInstitucionalController;
use App\Http\Controllers\Admin\CmsBancoDatosController;
use App\Http\Controllers\Admin\CmsComiteEditorialController;
use App\Http\Controllers\Admin\CmsComunicadoController;
use App\Http\Controllers\Admin\CmsConfiguracionController;
use App\Http\Controllers\Admin\CmsCursosController;
use App\Http\Controllers\Admin\CmsGaleriaController;
use App\Http\Controllers\Admin\CmsNoticiasController;
use App\Http\Controllers\Admin\CmsReglamentoInternoController;
use App\Http\Controllers\Admin\CmsRevistasVariasController;
use App\Http\Controllers\Admin\CmsRevistaVarianzaController;
use App\Http\Controllers\Admin\ContactoController;
use App\Http\Controllers\Admin\CursoModalidadesController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EdicionVarianzaController;
use App\Http\Controllers\Admin\EditorUploadController;
use App\Http\Controllers\Admin\EspecialidadesController;
use App\Http\Controllers\Admin\EtiquetaArtController;
use App\Http\Controllers\Admin\GradoAcademicoController;
use App\Http\Controllers\Admin\InstitucionesController;
use App\Http\Controllers\Admin\InstruccionAutorController;
use App\Http\Controllers\Admin\LineaInvestigacionController;
use App\Http\Controllers\Admin\MemoriasInvestigacionController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PersonalController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\ServicioController;
use App\Http\Controllers\Admin\SobreNosotrosController;
use App\Http\Controllers\Admin\TipoArticuloController;
use App\Http\Controllers\Admin\TipoAutorController;
use App\Http\Controllers\Admin\TipoCursoController;
use App\Http\Controllers\Admin\TipoParticipacionController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Pantalla de configuración y activación obligatoria de 2FA
    Route::get('/two-factor-setup', function () {
        return Inertia::render('Auth/TwoFactorSetup');
    })->name('two-factor.setup');

    // Rutas protegidas que requieren 2FA habilitado para roles que lo exijan (autores)
    Route::middleware(['ensure.2fa'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::prefix('admin')->name('admin.')->group(function () {
            // Módulo Artículos Científicos (Accesible por Administradores y Autores bajo ArticuloPolicy)
            Route::post('articulos/{articulo}/aprobar', [ArticuloController::class, 'aprobar'])
                ->name('articulos.aprobar');
            Route::post('articulos/{articulo}/rechazar', [ArticuloController::class, 'rechazar'])
                ->name('articulos.rechazar');
            Route::get('articulos/exportar-excel', [ArticuloController::class, 'export'])
                ->name('articulos.export');
            Route::get('articulos/plantilla-excel', [ArticuloController::class, 'plantilla'])
                ->name('articulos.plantilla');
            Route::post('articulos/importar-excel', [ArticuloController::class, 'import'])
                ->name('articulos.import');
            Route::resource('articulos', ArticuloController::class);

            // MÓDULOS EXCLUSIVOS PARA ADMINISTRADOR
            Route::middleware(['role:administrador'])->group(function () {
                // Subida de imágenes para Editor nurvito
                Route::post('/editor/upload-image', [EditorUploadController::class, 'uploadImage'])
                    ->name('editor.upload');

                // Módulo Usuarios y RBAC
                Route::patch('usuarios/{usuario}/toggle-activo', [UserController::class, 'toggleActivo'])
                    ->name('usuarios.toggle-activo');
                Route::resource('usuarios', UserController::class);
                Route::resource('roles', RoleController::class);
                Route::resource('permisos', PermissionController::class);

                // Módulo Líneas de Investigación
                Route::resource('lineas-investigacion', LineaInvestigacionController::class);

                // Módulo Sobre Nosostros (Singleton)
                Route::get('/sobre-nosotros', [SobreNosotrosController::class, 'edit'])
                    ->name('sobre-nosotros.edit');
                Route::put('/sobre-nosotros', [SobreNosotrosController::class, 'update'])
                    ->name('sobre-nosotros.update');

                // Módulo Servicios (CRUD Completo)
                Route::resource('servicios', ServicioController::class);

                // Módulo Noticias y Novedades (CMS)
                Route::patch('noticias/{id}/toggle-destacado', [CmsNoticiasController::class, 'toggleDestacado'])
                    ->name('noticias.toggle-destacado');
                Route::patch('noticias/{id}/toggle-publicado', [CmsNoticiasController::class, 'togglePublicado'])
                    ->name('noticias.toggle-publicado');
                Route::resource('noticias', CmsNoticiasController::class);

                // Módulo Contacto (Singleton / Registro Único)
                Route::get('/contacto', [ContactoController::class, 'edit'])
                    ->name('contacto.edit');
                Route::put('/contacto', [ContactoController::class, 'update'])
                    ->name('contacto.update');

                // Módulo Cursos y Paramétricas
                Route::resource('tipo-cursos', TipoCursoController::class);
                Route::resource('curso-modalidades', CursoModalidadesController::class);
                Route::resource('cursos', CmsCursosController::class);

                // Módulo Publicaciones, Autores e Investigación
                Route::resource('especialidades', EspecialidadesController::class);
                Route::resource('grado-academicos', GradoAcademicoController::class);
                Route::resource('tipo-autores', TipoAutorController::class);
                Route::resource('tipo-participaciones', TipoParticipacionController::class);
                Route::get('autores/exportar-excel', [AutorController::class, 'export'])
                    ->name('autores.export');
                Route::get('autores/plantilla-excel', [AutorController::class, 'plantilla'])
                    ->name('autores.plantilla');
                Route::post('autores/importar-excel', [AutorController::class, 'import'])
                    ->name('autores.import');
                Route::resource('autores', AutorController::class);

                // Módulo Etiquetas de Artículos (N:M)
                Route::post('etiquetas-art/store-rapida', [EtiquetaArtController::class, 'storeRapida'])
                    ->name('etiquetas-art.store-rapida');
                Route::resource('etiquetas-art', EtiquetaArtController::class);

                // Módulo Tipos de Artículos Científicos
                Route::post('tipo-articulos/store-rapida', [TipoArticuloController::class, 'storeRapida'])
                    ->name('tipo-articulos.store-rapida');
                Route::resource('tipo-articulos', TipoArticuloController::class);

                // Módulo Revista Varianza (Perfil Institucional) y Ediciones
                Route::get('/revista-varianza', [CmsRevistaVarianzaController::class, 'edit'])
                    ->name('revista-varianza.edit');
                Route::put('/revista-varianza', [CmsRevistaVarianzaController::class, 'update'])
                    ->name('revista-varianza.update');
                Route::resource('ediciones-varianza', EdicionVarianzaController::class);

                // Módulo Instrucciones para Autores (Directrices Revista Varianza)
                Route::patch('instrucciones-autores/{id}/toggle-activo', [InstruccionAutorController::class, 'toggleActivo'])
                    ->name('instrucciones-autores.toggle-activo');
                Route::resource('instrucciones-autores', InstruccionAutorController::class);

                Route::resource('memorias-investigacion', MemoriasInvestigacionController::class);

                // Módulo Banco de Datos
                Route::resource('area-tematicas', AreaTematicasController::class);
                Route::get('banco-datos/exportar-excel', [CmsBancoDatosController::class, 'export'])
                    ->name('banco-datos.export');
                Route::get('banco-datos/plantilla-excel', [CmsBancoDatosController::class, 'plantilla'])
                    ->name('banco-datos.plantilla');
                Route::post('banco-datos/importar-excel', [CmsBancoDatosController::class, 'import'])
                    ->name('banco-datos.import');
                Route::resource('banco-datos', CmsBancoDatosController::class);

                // Módulo Institucional / Equipo
                Route::resource('cargos-institucionales', CargoInstitucionalController::class);
                Route::resource('instituciones', InstitucionesController::class);
                Route::resource('comite-editorial', CmsComiteEditorialController::class);
                Route::resource('personal', PersonalController::class);

                // Módulo Comunicados y Novedades Emergentes (Popups)
                Route::patch('comunicados/{id}/toggle-activo', [CmsComunicadoController::class, 'toggleActivo'])
                    ->name('comunicados.toggle-activo');
                Route::resource('comunicados', CmsComunicadoController::class);

                // Módulo Galería de Imágenes CMS
                Route::patch('galeria/{id}/toggle-destacado', [CmsGaleriaController::class, 'toggleDestacado'])
                    ->name('galeria.toggle-destacado');
                Route::patch('galeria/{id}/toggle-activo', [CmsGaleriaController::class, 'toggleActivo'])
                    ->name('galeria.toggle-activo');
                Route::resource('galeria', CmsGaleriaController::class);

                // Módulo Revistas de Referencia (Revistas Varias)
                Route::patch('revistas-varias/{id}/toggle-activo', [CmsRevistasVariasController::class, 'toggleActivo'])
                    ->name('revistas-varias.toggle-activo');
                Route::resource('revistas-varias', CmsRevistasVariasController::class);

                // Módulo Reglamento Interno Institucional (Singleton)
                Route::get('/reglamento-interno', [CmsReglamentoInternoController::class, 'edit'])
                    ->name('reglamento-interno.edit');
                Route::post('/reglamento-interno', [CmsReglamentoInternoController::class, 'update'])
                    ->name('reglamento-interno.update');
                Route::delete('/reglamento-interno/archivo', [CmsReglamentoInternoController::class, 'eliminarArchivo'])
                    ->name('reglamento-interno.eliminar-archivo');

                // Módulo Configuración Institucional (Singleton)
                Route::get('/configuracion', [CmsConfiguracionController::class, 'edit'])
                    ->name('configuracion.edit');
                Route::put('/configuracion', [CmsConfiguracionController::class, 'update'])
                    ->name('configuracion.update');
            });
        });
    });
});
