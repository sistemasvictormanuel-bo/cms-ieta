<?php

namespace Database\Seeders;

use App\Models\AreaTematicas;
use App\Models\CargoInstitucional;
use App\Models\CmsConfiguracion;
use App\Models\CursoModalidades;
use App\Models\Especialidades;
use App\Models\GradoAcademico;
use App\Models\Instituciones;
use App\Models\TipoAutor;
use App\Models\TipoCurso;
use App\Models\TipoParticipacion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogosBaseSeeder extends Seeder
{
    /**
     * Semillero de catálogos base e institucionales del IETA.
     * Diseñado de manera 100% idempotente (updateOrCreate con IDs explícitos).
     */
    public function run(): void
    {
        Model::unguard();

        // 1. Tipos de Autor / Investigador
        $tiposAutor = [
            ['id' => 1, 'ta_nombre' => 'Docente Titular', 'ta_descripcion' => 'Docente titular e investigador de planta.', 'ta_estado' => true],
            ['id' => 2, 'ta_nombre' => 'Docente Externo', 'ta_descripcion' => 'Docente o investigador colaborador externo.', 'ta_estado' => true],
            ['id' => 3, 'ta_nombre' => 'Docente Investigador', 'ta_descripcion' => 'Docente enfocado primordialmente a proyectos de investigación.', 'ta_estado' => true],
            ['id' => 4, 'ta_nombre' => 'Estudiante Investigador', 'ta_descripcion' => 'Estudiante de grado o postgrado en rol de tesista o auxiliar.', 'ta_estado' => true],
            ['id' => 5, 'ta_nombre' => 'Docente Emérito', 'ta_descripcion' => 'Docente emérito destacado de la carrera.', 'ta_estado' => true],
            ['id' => 6, 'ta_nombre' => 'Docente', 'ta_descripcion' => 'Docente o autor general sin categoría específica asignada.', 'ta_estado' => true],
        ];
        foreach ($tiposAutor as $item) {
            TipoAutor::updateOrCreate(['id' => $item['id']], $item);
        }

        // 2. Grados Académicos
        $grados = [
            ['id' => 1, 'ga_nombre' => 'Licenciatura', 'ga_abreviatura' => 'Lic.', 'ga_descripcion' => 'Grado académico de pregrado.'],
            ['id' => 2, 'ga_nombre' => 'Maestría', 'ga_abreviatura' => 'M.Sc.', 'ga_descripcion' => 'Grado académico de postgrado maestría.'],
            ['id' => 3, 'ga_nombre' => 'Doctorado', 'ga_abreviatura' => 'Ph.D.', 'ga_descripcion' => 'Grado académico de doctorado.'],
        ];
        foreach ($grados as $item) {
            GradoAcademico::updateOrCreate(['id' => $item['id']], $item);
        }

        // 3. Especialidades Científicas
        $especialidades = [
            ['id' => 1, 'ep_nombre' => 'Ciencia de Datos', 'ep_descripcion' => 'Machine learning, big data y analítica computacional.', 'ep_estado' => true],
            ['id' => 2, 'ep_nombre' => 'Estadística Aplicada', 'ep_descripcion' => 'Métodos estadísticos aplicados a ciencias sociales y económicas.', 'ep_estado' => true],
            ['id' => 3, 'ep_nombre' => 'Bioestadística y Epidemiología', 'ep_descripcion' => 'Modelado de supervivencia, ensayos clínicos y salud pública.', 'ep_estado' => true],
            ['id' => 4, 'ep_nombre' => 'Econometría Financiera', 'ep_descripcion' => 'Riesgo financiero, series temporales y volatilidad.', 'ep_estado' => true],
            ['id' => 5, 'ep_nombre' => 'Muestreo y Encuestas', 'ep_descripcion' => 'Diseño muestral complejo, inferencia y encuestas de hogares.', 'ep_estado' => true],
            ['id' => 6, 'ep_nombre' => 'Series de Tiempo', 'ep_descripcion' => 'Pronósticos, modelos ARIMA/GARCH y análisis espectral.', 'ep_estado' => true],
        ];
        foreach ($especialidades as $item) {
            Especialidades::updateOrCreate(['id' => $item['id']], $item);
        }

        // 4. Tipos de Participación en Artículos
        $participaciones = [
            ['id' => 1, 'tp_nombre' => 'Autor Principal', 'tp_descripcion' => 'Investigador principal responsable del manuscrito.'],
            ['id' => 2, 'tp_nombre' => 'Coautor', 'tp_descripcion' => 'Investigador colaborador en análisis y redacción.'],
            ['id' => 3, 'tp_nombre' => 'Autor de Correspondencia', 'tp_descripcion' => 'Punto de contacto para consultas científicas y editoriales.'],
        ];
        foreach ($participaciones as $item) {
            TipoParticipacion::updateOrCreate(['id' => $item['id']], $item);
        }

        // 5. Cargos Institucionales
        $cargos = [
            ['id' => 1, 'ci_nombre' => 'Director IETA', 'ci_descripcion' => 'Máxima autoridad ejecutiva y científica del instituto.'],
            ['id' => 2, 'ci_nombre' => 'Secretaría', 'ci_descripcion' => 'Gestión administrativa y correspondencia institucional.'],
            ['id' => 3, 'ci_nombre' => 'Investigador', 'ci_descripcion' => 'Personal científico asignado a proyectos y líneas activas.'],
            ['id' => 4, 'ci_nombre' => 'Portera y Mensajera', 'ci_descripcion' => 'Personal de apoyo operativo y servicios auxiliares.'],
        ];
        foreach ($cargos as $item) {
            CargoInstitucional::updateOrCreate(['id' => $item['id']], $item);
        }

        // 6. Instituciones de Afiliación
        Instituciones::updateOrCreate(
            ['id' => 1],
            [
                'inst_nombre' => 'Universidad Mayor de San Andrés (UMSA)',
                'inst_pais' => 'Bolivia',
                'inst_ciudad' => 'La Paz',
            ]
        );

        // 7. Tipos de Cursos & Modalidades
        $tiposCurso = [
            ['id' => 1, 'tc_nombre' => 'Taller', 'tc_descripcion' => 'Capacitación práctica intensiva de corta duración.'],
            ['id' => 2, 'tc_nombre' => 'Curso', 'tc_descripcion' => 'Programa formativo modular de profundización teórica y práctica.'],
        ];
        foreach ($tiposCurso as $item) {
            TipoCurso::updateOrCreate(['id' => $item['id']], $item);
        }

        $modalidades = [
            ['id' => 1, 'mc_nombre' => 'Virtual', 'mc_descripcion' => '100% sincrónico y asincrónico por plataforma digital.'],
            ['id' => 2, 'mc_nombre' => 'Presencial', 'mc_descripcion' => 'Sesiones presenciales en aulas del IETA / UMSA.'],
            ['id' => 3, 'mc_nombre' => 'Semipresencial', 'mc_descripcion' => 'Combinación híbrida de teoría online y práctica presencial.'],
        ];
        foreach ($modalidades as $item) {
            CursoModalidades::updateOrCreate(['id' => $item['id']], $item);
        }

        // 8. Áreas Temáticas del Banco de Datos
        $areas = [
            ['id' => 1, 'at_nombre' => 'Agropecuario', 'at_descripcion' => 'Estadísticas del sector agropecuario y productivo rural.'],
            ['id' => 2, 'at_nombre' => 'Demografía y Población', 'at_descripcion' => 'Dinámica poblacional, censos y proyecciones.'],
            ['id' => 3, 'at_nombre' => 'Vivienda y Servicios Básicos', 'at_descripcion' => 'Infraestructura habitacional y acceso a servicios.'],
            ['id' => 4, 'at_nombre' => 'Economía y Pobreza', 'at_descripcion' => 'Ingresos, estratificación socioeconómica y canasta básica.'],
            ['id' => 5, 'at_nombre' => 'Mercado Laboral y Empleo', 'at_descripcion' => 'Ocupación, desempleo y condiciones laborales.'],
        ];
        foreach ($areas as $item) {
            AreaTematicas::updateOrCreate(['id' => $item['id']], $item);
        }

        // 9. Configuración Institucional Básica
        CmsConfiguracion::updateOrCreate(
            ['id' => 1],
            [
                'cfg_nombre_institucion' => 'Instituto de Estadística Teórica y Aplicada',
                'cfg_siglas' => 'IETA',
                'cfg_slogan' => 'Investigación, rigor científico y análisis estadístico',
                'cfg_descripcion_corta' => 'Instituto de investigación científica adscrito a la Universidad Mayor de San Andrés (UMSA), dedicado al avance teórico y práctico de la ciencia estadística.',
                'cfg_email_principal' => 'contacto@ieta.umsa.bo',
                'cfg_telefono_principal' => '+591 2 2441234',
                'cfg_direccion_principal' => 'Campus Universitario Cota Cota, La Paz - Bolivia',
                'cfg_redes_sociales' => [
                    'facebook' => 'https://facebook.com/ieta.umsa',
                    'twitter' => null,
                    'linkedin' => null,
                    'youtube' => null,
                    'instagram' => null,
                ],
                'cfg_copyright' => '© '.date('Y').' Instituto de Estadística Teórica y Aplicada (IETA). UMSA. Todos los derechos reservados.',
            ]
        );

        Model::reguard();

        // 10. Sincronizar secuencias de PostgreSQL
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("SELECT setval(pg_get_serial_sequence('tipo_autores', 'id'), COALESCE(MAX(id), 1)) FROM tipo_autores;");
            DB::statement("SELECT setval(pg_get_serial_sequence('grado_academicos', 'id'), COALESCE(MAX(id), 1)) FROM grado_academicos;");
            DB::statement("SELECT setval(pg_get_serial_sequence('especialidades', 'id'), COALESCE(MAX(id), 1)) FROM especialidades;");
            DB::statement("SELECT setval(pg_get_serial_sequence('tipo_participaciones', 'id'), COALESCE(MAX(id), 1)) FROM tipo_participaciones;");
            DB::statement("SELECT setval(pg_get_serial_sequence('cargo_institucionales', 'id'), COALESCE(MAX(id), 1)) FROM cargo_institucionales;");
            DB::statement("SELECT setval(pg_get_serial_sequence('instituciones', 'id'), COALESCE(MAX(id), 1)) FROM instituciones;");
            DB::statement("SELECT setval(pg_get_serial_sequence('tipo_cursos', 'id'), COALESCE(MAX(id), 1)) FROM tipo_cursos;");
            DB::statement("SELECT setval(pg_get_serial_sequence('curso_modalidades', 'id'), COALESCE(MAX(id), 1)) FROM curso_modalidades;");
            DB::statement("SELECT setval(pg_get_serial_sequence('area_tematicas', 'id'), COALESCE(MAX(id), 1)) FROM area_tematicas;");
            DB::statement("SELECT setval(pg_get_serial_sequence('cms_configuracion', 'id'), COALESCE(MAX(id), 1)) FROM cms_configuracion;");
        }
    }
}
