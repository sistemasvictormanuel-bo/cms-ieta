<?php

namespace App\Models;

use App\Models\Concerns\NormalizesEditorJsBlocks;
use Illuminate\Database\Eloquent\Model;

class CmsSobreNosotros extends Model
{
    use NormalizesEditorJsBlocks;

    protected $table = 'cms_sobre_nosotros';

    protected $fillable = [
        'sn_contenido',
        'sn_historia',
        'sn_mision',
        'sn_vision',
        'sn_objetivos',
    ];

    protected function casts(): array
    {
        return [
            'sn_contenido' => 'array',
            'sn_objetivos' => 'array',
        ];
    }

    /**
     * Garantiza que el contenido estructurado de Editor.js entregue bloques de lista
     * normalizados compatibles con @editorjs/list v2.
     */
    public function getSnContenidoAttribute($value): ?array
    {
        return $this->normalizeEditorJsBlocks($value);
    }

    public function getSnObjetivosAttribute($value): ?array
    {
        return $this->normalizeEditorJsBlocks($value);
    }
}
