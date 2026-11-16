<?php

namespace Database\Seeders;

use App\Models\CmsTraduccion;
use Illuminate\Database\Seeder;

class CmsTraduccionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $traducciones = [
            // ==========================================
            // 1. MÓDULO: NAVEGACIÓN (navegacion)
            // ==========================================
            [
                'modulo' => 'navegacion',
                'clave' => 'nav_inicio',
                'texto_es' => 'Inicio',
                'texto_en' => 'Home',
                'descripcion' => 'Enlace a página de inicio en la barra de navegación superior',
            ],
            [
                'modulo' => 'navegacion',
                'clave' => 'nav_nosotros',
                'texto_es' => 'Sobre Nosotros',
                'texto_en' => 'About Us',
                'descripcion' => 'Enlace institucional a la historia, misión y visión del IETA',
            ],
            [
                'modulo' => 'navegacion',
                'clave' => 'nav_revista',
                'texto_es' => 'Revista Varianza',
                'texto_en' => 'Varianza Journal',
                'descripcion' => 'Enlace al portal de la revista científica oficial',
            ],
            [
                'modulo' => 'navegacion',
                'clave' => 'nav_articulos',
                'texto_es' => 'Artículos Científicos',
                'texto_en' => 'Scientific Articles',
                'descripcion' => 'Enlace al catálogo y buscador de artículos publicados',
            ],
            [
                'modulo' => 'navegacion',
                'clave' => 'nav_investigacion',
                'texto_es' => 'Líneas de Investigación',
                'texto_en' => 'Research Lines',
                'descripcion' => 'Enlace a los ejes temáticos y proyectos de investigación activa',
            ],
            [
                'modulo' => 'navegacion',
                'clave' => 'nav_banco_datos',
                'texto_es' => 'Banco de Datos',
                'texto_en' => 'Data Bank',
                'descripcion' => 'Enlace al repositorio de datasets y microdatos estadísticos',
            ],
            [
                'modulo' => 'navegacion',
                'clave' => 'nav_memorias',
                'texto_es' => 'Memorias de Investigación',
                'texto_en' => 'Research Proceedings',
                'descripcion' => 'Enlace a las memorias y congresos estadísticos',
            ],
            [
                'modulo' => 'navegacion',
                'clave' => 'nav_cursos',
                'texto_es' => 'Cursos y Capacitación',
                'texto_en' => 'Courses & Training',
                'descripcion' => 'Enlace al catálogo de diplomados, talleres y cursos técnicos',
            ],
            [
                'modulo' => 'navegacion',
                'clave' => 'nav_servicios',
                'texto_es' => 'Servicios',
                'texto_en' => 'Services',
                'descripcion' => 'Enlace a servicios de consultoría y procesamiento de datos',
            ],
            [
                'modulo' => 'navegacion',
                'clave' => 'nav_noticias',
                'texto_es' => 'Noticias y Eventos',
                'texto_en' => 'News & Events',
                'descripcion' => 'Enlace a noticias institucionales y agenda científica',
            ],
            [
                'modulo' => 'navegacion',
                'clave' => 'nav_contacto',
                'texto_es' => 'Contacto',
                'texto_en' => 'Contact',
                'descripcion' => 'Enlace a formulario de contacto y ubicación física',
            ],
            [
                'modulo' => 'navegacion',
                'clave' => 'nav_login',
                'texto_es' => 'Acceso Autor / Docente',
                'texto_en' => 'Author / Faculty Login',
                'descripcion' => 'Botón de inicio de sesión al sistema de gestión',
            ],

            // ==========================================
            // 2. MÓDULO: GENERAL Y ACCIONES (general)
            // ==========================================
            [
                'modulo' => 'general',
                'clave' => 'btn_ver_mas',
                'texto_es' => 'Ver más',
                'texto_en' => 'Read more',
                'descripcion' => 'Botón genérico para expandir tarjetas de información',
            ],
            [
                'modulo' => 'general',
                'clave' => 'btn_descargar_pdf',
                'texto_es' => 'Descargar PDF',
                'texto_en' => 'Download PDF',
                'descripcion' => 'Botón para descarga de documentos o artículos científicos',
            ],
            [
                'modulo' => 'general',
                'clave' => 'btn_abrir_enlace',
                'texto_es' => 'Abrir Enlace',
                'texto_en' => 'Open Link',
                'descripcion' => 'Botón para enlaces externos como Google Drive o repositorios',
            ],
            [
                'modulo' => 'general',
                'clave' => 'btn_buscar',
                'texto_es' => 'Buscar',
                'texto_en' => 'Search',
                'descripcion' => 'Botón o acción de búsqueda',
            ],
            [
                'modulo' => 'general',
                'clave' => 'btn_filtrar',
                'texto_es' => 'Filtrar',
                'texto_en' => 'Filter',
                'descripcion' => 'Botón para aplicar filtros',
            ],
            [
                'modulo' => 'general',
                'clave' => 'btn_limpiar',
                'texto_es' => 'Limpiar Filtros',
                'texto_en' => 'Clear Filters',
                'descripcion' => 'Restablecer criterios de búsqueda',
            ],
            [
                'modulo' => 'general',
                'clave' => 'lbl_todos',
                'texto_es' => 'Todos',
                'texto_en' => 'All',
                'descripcion' => 'Etiqueta para opción omnímoda en selectores y filtros',
            ],
            [
                'modulo' => 'general',
                'clave' => 'lbl_cargando',
                'texto_es' => 'Cargando información...',
                'texto_en' => 'Loading information...',
                'descripcion' => 'Mensaje de carga para llamadas asíncronas',
            ],
            [
                'modulo' => 'general',
                'clave' => 'lbl_sin_resultados',
                'texto_es' => 'No se encontraron resultados para los criterios seleccionados.',
                'texto_en' => 'No results found matching your criteria.',
                'descripcion' => 'Estado vacío para listados y filtros',
            ],
            [
                'modulo' => 'general',
                'clave' => 'lbl_publicado_el',
                'texto_es' => 'Publicado el',
                'texto_en' => 'Published on',
                'descripcion' => 'Prefijo de fecha de publicación',
            ],
            [
                'modulo' => 'general',
                'clave' => 'lbl_compartir',
                'texto_es' => 'Compartir',
                'texto_en' => 'Share',
                'descripcion' => 'Botón de compartir en redes sociales',
            ],

            // ==========================================
            // 3. MÓDULO: PORTADA Y HERO (hero)
            // ==========================================
            [
                'modulo' => 'hero',
                'clave' => 'hero_instituto',
                'texto_es' => 'Instituto de Estadística Teórica y Aplicada',
                'texto_en' => 'Institute of Theoretical and Applied Statistics',
                'descripcion' => 'Nombre institucional principal del IETA',
            ],
            [
                'modulo' => 'hero',
                'clave' => 'hero_subtitulo',
                'texto_es' => 'Generando conocimiento científico, formación estadística rigurosa y análisis de datos de alto impacto en Bolivia.',
                'texto_en' => 'Generating scientific knowledge, rigorous statistical training and high-impact data analysis in Bolivia.',
                'descripcion' => 'Lema institucional en la cabecera de la portada',
            ],
            [
                'modulo' => 'hero',
                'clave' => 'hero_btn_revista',
                'texto_es' => 'Explorar Revista Varianza',
                'texto_en' => 'Explore Varianza Journal',
                'descripcion' => 'Llamado a la acción principal hacia las publicaciones',
            ],
            [
                'modulo' => 'hero',
                'clave' => 'hero_btn_banco',
                'texto_es' => 'Acceder a Banco de Datos',
                'texto_en' => 'Access Data Bank',
                'descripcion' => 'Llamado a la acción secundario hacia datasets',
            ],
            [
                'modulo' => 'hero',
                'clave' => 'hero_estadistica_articulos',
                'texto_es' => 'Artículos Indexados',
                'texto_en' => 'Indexed Articles',
                'descripcion' => 'Métrica numérica de publicaciones en portada',
            ],
            [
                'modulo' => 'hero',
                'clave' => 'hero_estadistica_ediciones',
                'texto_es' => 'Ediciones Publicadas',
                'texto_en' => 'Published Editions',
                'descripcion' => 'Métrica de volúmenes de revista en portada',
            ],
            [
                'modulo' => 'hero',
                'clave' => 'hero_estadistica_datasets',
                'texto_es' => 'Datasets Disponibles',
                'texto_en' => 'Available Datasets',
                'descripcion' => 'Métrica de conjuntos de datos en portada',
            ],

            // ==========================================
            // 4. MÓDULO: REVISTA VARIANZA (revista)
            // ==========================================
            [
                'modulo' => 'revista',
                'clave' => 'revista_titulo',
                'texto_es' => 'Revista Varianza',
                'texto_en' => 'Varianza Scientific Journal',
                'descripcion' => 'Título de la revista científica',
            ],
            [
                'modulo' => 'revista',
                'clave' => 'revista_descripcion',
                'texto_es' => 'Órgano oficial de difusión científica de la Carrera de Estadística - UMSA. Publica semestralmente artículos originales de investigación teórico-metodológica y estadística aplicada.',
                'texto_en' => 'Official scientific dissemination journal of the Department of Statistics - UMSA. Publishes semi-annual original theoretical-methodological research and applied statistical papers.',
                'descripcion' => 'Reseña de la revista en la sección científica',
            ],
            [
                'modulo' => 'revista',
                'clave' => 'revista_directrices',
                'texto_es' => 'Directrices para Autores',
                'texto_en' => 'Guidelines for Authors',
                'descripcion' => 'Título de normativas y requisitos de publicación',
            ],
            [
                'modulo' => 'revista',
                'clave' => 'revista_normas_eticas',
                'texto_es' => 'Normas Éticas y Políticas Editoriales',
                'texto_en' => 'Ethical Standards & Editorial Policies',
                'descripcion' => 'Compromiso de integridad científica y antiplagio',
            ],
            [
                'modulo' => 'revista',
                'clave' => 'revista_comite_editorial',
                'texto_es' => 'Comité Editorial',
                'texto_en' => 'Editorial Board',
                'descripcion' => 'Membresía académica nacional e internacional',
            ],
            [
                'modulo' => 'revista',
                'clave' => 'revista_edicion_actual',
                'texto_es' => 'Edición Actual',
                'texto_en' => 'Current Issue',
                'descripcion' => 'Último volumen publicado de la revista',
            ],
            [
                'modulo' => 'revista',
                'clave' => 'revista_ediciones_anteriores',
                'texto_es' => 'Archivo Histórico de Ediciones',
                'texto_en' => 'Issue Archive / Past Editions',
                'descripcion' => 'Colección histórica completa de la revista Varianza',
            ],
            [
                'modulo' => 'revista',
                'clave' => 'lbl_autores',
                'texto_es' => 'Autores / Investigadores',
                'texto_en' => 'Authors / Researchers',
                'descripcion' => 'Créditos autorales en fichas de artículo',
            ],
            [
                'modulo' => 'revista',
                'clave' => 'lbl_resumen',
                'texto_es' => 'Resumen',
                'texto_en' => 'Abstract',
                'descripcion' => 'Encabezado del resumen en español o inglés',
            ],
            [
                'modulo' => 'revista',
                'clave' => 'lbl_palabras_clave',
                'texto_es' => 'Palabras Clave',
                'texto_en' => 'Keywords',
                'descripcion' => 'Etiquetas y descriptores temáticos del artículo',
            ],
            [
                'modulo' => 'revista',
                'clave' => 'lbl_referencias',
                'texto_es' => 'Referencias Bibliográficas',
                'texto_en' => 'References',
                'descripcion' => 'Sección de fuentes y citas bibliográficas',
            ],
            [
                'modulo' => 'revista',
                'clave' => 'lbl_como_citar',
                'texto_es' => 'Cómo citar este artículo',
                'texto_en' => 'How to cite this article',
                'descripcion' => 'Formato de citación en norma APA',
            ],

            // ==========================================
            // 5. MÓDULO: BANCO DE DATOS (banco_datos)
            // ==========================================
            [
                'modulo' => 'banco_datos',
                'clave' => 'banco_titulo',
                'texto_es' => 'Banco de Datos y Estadísticas',
                'texto_en' => 'Data Bank and Statistics',
                'descripcion' => 'Título del repositorio de datos',
            ],
            [
                'modulo' => 'banco_datos',
                'clave' => 'banco_subtitulo',
                'texto_es' => 'Conjuntos de datos, encuestas y registros estadísticos clasificados por áreas temáticas para fines de investigación académica y docencia.',
                'texto_en' => 'Datasets, surveys and statistical records categorized by thematic areas for academic research and teaching.',
                'descripcion' => 'Subtítulo del portal de datos abiertos',
            ],
            [
                'modulo' => 'banco_datos',
                'clave' => 'banco_buscar_placeholder',
                'texto_es' => 'Buscar conjuntos de datos por título, tema o palabras clave...',
                'texto_en' => 'Search datasets by title, topic or keywords...',
                'descripcion' => 'Marcador de posición del buscador de microdatos',
            ],
            [
                'modulo' => 'banco_datos',
                'clave' => 'banco_area_tematica',
                'texto_es' => 'Área Temática',
                'texto_en' => 'Thematic Field',
                'descripcion' => 'Clasificación temática del dataset',
            ],
            [
                'modulo' => 'banco_datos',
                'clave' => 'banco_fuente',
                'texto_es' => 'Fuente de Información',
                'texto_en' => 'Information Source',
                'descripcion' => 'Origen institucional del dataset',
            ],
            [
                'modulo' => 'banco_datos',
                'clave' => 'banco_temporalidad',
                'texto_es' => 'Período Temporal',
                'texto_en' => 'Time Period',
                'descripcion' => 'Años o fechas que abarca el dataset',
            ],
            [
                'modulo' => 'banco_datos',
                'clave' => 'banco_descargar',
                'texto_es' => 'Descargar Archivo',
                'texto_en' => 'Download Dataset File',
                'descripcion' => 'Botón de descarga directa de microdatos',
            ],

            // ==========================================
            // 6. MÓDULO: FOOTER Y CONTACTO (footer_contacto)
            // ==========================================
            [
                'modulo' => 'footer_contacto',
                'clave' => 'footer_derechos',
                'texto_es' => 'Todos los derechos reservados.',
                'texto_en' => 'All rights reserved.',
                'descripcion' => 'Pie de página de derechos de autor',
            ],
            [
                'modulo' => 'footer_contacto',
                'clave' => 'footer_institucion',
                'texto_es' => 'Carrera de Estadística — Facultad de Ciencias Puras y Naturales — Universidad Mayor de San Andrés',
                'texto_en' => 'Department of Statistics — Faculty of Pure and Natural Sciences — Universidad Mayor de San Andrés',
                'descripcion' => 'Filiación institucional universitaria completa',
            ],
            [
                'modulo' => 'footer_contacto',
                'clave' => 'footer_ubicacion',
                'texto_es' => 'Campus Universitario Cota Cota, Calle 27 s/n, Edificio FCPN. La Paz, Bolivia.',
                'texto_en' => 'Cota Cota University Campus, 27th Street, FCPN Building. La Paz, Bolivia.',
                'descripcion' => 'Dirección física del instituto',
            ],
            [
                'modulo' => 'footer_contacto',
                'clave' => 'contacto_titulo',
                'texto_es' => 'Contáctate con el IETA',
                'texto_en' => 'Contact IETA',
                'descripcion' => 'Encabezado de la página de contacto',
            ],
            [
                'modulo' => 'footer_contacto',
                'clave' => 'contacto_nombre',
                'texto_es' => 'Nombre Completo',
                'texto_en' => 'Full Name',
                'descripcion' => 'Campo nombre en formulario',
            ],
            [
                'modulo' => 'footer_contacto',
                'clave' => 'contacto_correo',
                'texto_es' => 'Correo Electrónico',
                'texto_en' => 'Email Address',
                'descripcion' => 'Campo email en formulario',
            ],
            [
                'modulo' => 'footer_contacto',
                'clave' => 'contacto_asunto',
                'texto_es' => 'Asunto',
                'texto_en' => 'Subject',
                'descripcion' => 'Campo asunto en formulario',
            ],
            [
                'modulo' => 'footer_contacto',
                'clave' => 'contacto_mensaje',
                'texto_es' => 'Mensaje',
                'texto_en' => 'Message',
                'descripcion' => 'Campo mensaje en formulario',
            ],
            [
                'modulo' => 'footer_contacto',
                'clave' => 'contacto_enviar',
                'texto_es' => 'Enviar Mensaje',
                'texto_en' => 'Send Message',
                'descripcion' => 'Botón de envío de formulario de contacto',
            ],
        ];

        foreach ($traducciones as $item) {
            CmsTraduccion::updateOrCreate(
                [
                    'modulo' => $item['modulo'],
                    'clave' => $item['clave'],
                ],
                [
                    'texto_es' => $item['texto_es'],
                    'texto_en' => $item['texto_en'],
                    'descripcion' => $item['descripcion'],
                ]
            );
        }
    }
}
