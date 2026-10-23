<?php

namespace App\Models;

use App\Models\Concerns\NormalizesEditorJsBlocks;
use Illuminate\Database\Eloquent\Model;

class CmsContacto extends Model
{
    use NormalizesEditorJsBlocks;

    protected $table = 'cms_contactos';

    protected $fillable = [
        'cc_telefono',
        'cc_dia_inicio',
        'cc_dia_fin',
        'cc_hora_apertura',
        'cc_hora_cierre',
        'cc_direccion',
        'cc_email',
        'cc_contenido',
    ];

    protected function casts(): array
    {
        return [
            'cc_telefono' => 'array',
            'cc_email' => 'array',
            'cc_contenido' => 'array',
        ];
    }

    /**
     * Normaliza los bloques de Editor.js garantizando compatibilidad total
     * con @editorjs/list v2 y previniendo DataCloneError.
     */
    public function getCcContenidoAttribute($value): ?array
    {
        return $this->normalizeEditorJsBlocks($value);
    }
}
