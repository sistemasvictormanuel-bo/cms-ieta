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
use Illuminate\Database\Seeder;

class CatalogosBaseSeeder extends Seeder
{
    /**
     * Semillero de catálogos base e institucionales del IETA.
     * Diseñado de manera idempotente (firstOrCreate / updateOrCreate).
     */
    public function run(): void
    {
        // 1. Tipos de Autor / Investigador
        $tiposAutor = [
            ['id' => 1, 'ta_nombre' => 'Docente Titular', 'ta_descripcion' => 'Docente titular e investigador de planta.', 'ta_estado' => true],
            ['id' => 2, 'ta_nombre' => 'Docente Externo', 'ta_descripcion' => 'Docente o investigador colaborador externo.', 'ta_estado' => true],
            ['id' => 3, 'ta_nombre' => 'Docente Investigador', 'ta_descripcion' => 'Docente enfocado primordialmente a proyectos de investigación.', 'ta_estado' => true],
            ['id' => 4, 'ta_nombre' => 'Estudiante Investigador', 'ta_descripcion' => 'Estudiante de grado o postgrado en rol de tesista o auxiliar.', 'ta_estado' => true],
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
            ['id' => 3, 'ep_nombre' => 'Bioestadística', 'ep_descripcion' => 'Estadística orientada a ciencias de la salud y biológicas.', 'ep_estado' => true],
        ];
        foreach ($especialidades as $item) {
            Especialidades::updateOrCreate(['id' => $item['id']], $item);
        }

        // 4. Tipos de Participación en Artículos
        $participaciones = [
            ['id' => 1, 'tp_nombre' => 'Principal', 'tp_descripcion' => 'Autor principal o primer autor de la investigación.'],
            ['id' => 2, 'tp_nombre' => 'Coautor', 'tp_descripcion' => 'Coautor o colaborador en la investigación.'],
        ];
        foreach ($participaciones as $item) {
            TipoParticipacion::updateOrCreate(['id' => $item['id']], $item);
        }

        // 5. Cargos Institucionales
        $cargos = [
            ['id' => 1, 'ci_nombre' => 'Director IETA', 'ci_descripcion' => 'Máxima autoridad ejecutiva del instituto.'],
            ['id' => 2, 'ci_nombre' => 'Secretaría', 'ci_descripcion' => 'Coordinación administrativa y recepción documental.'],
            ['id' => 3, 'ci_nombre' => 'Investigador', 'ci_descripcion' => 'Personal científico asignado a proyectos y líneas activas.'],
        ];
        foreach ($cargos as $item) {
            CargoInstitucional::updateOrCreate(['id' => $item['id']], $item);
        }

        // 6. Instituciones de Afiliación
        Instituciones::firstOrCreate(
            ['inst_nombre' => 'Universidad Mayor de San Andrés (UMSA)'],
            [
                'id' => 1,
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
            ['id' => 1, 'mc_nombre' => 'Presencial', 'mc_descripcion' => 'Actividades impartidas en instalaciones del IETA / UMSA.'],
            ['id' => 2, 'mc_nombre' => 'Virtual', 'mc_descripcion' => 'Sesiones sincrónicas o asincrónicas por plataformas digitales.'],
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
        CmsConfiguracion::firstOrCreate(
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
                'cfg_copyright' => '© ' . date('Y') . ' Instituto de Estadística Teórica y Aplicada (IETA). UMSA. Todos los derechos reservados.',
            ]
        );
    }
}
