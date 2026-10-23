<?php

namespace App\Models\Concerns;

trait NormalizesEditorJsBlocks
{
    /**
     * Normaliza la estructura JSON de bloques de Editor.js para garantizar
     * compatibilidad con @editorjs/list v2 y prevenir errores de renderizado.
     */
    public function normalizeEditorJsBlocks(mixed $value): ?array
    {
        if (is_null($value)) {
            return ['blocks' => []];
        }

        $data = is_array($value) ? $value : json_decode($value, true);

        if (! is_array($data) || ! isset($data['blocks']) || ! is_array($data['blocks'])) {
            return is_array($data) ? $data : ['blocks' => []];
        }

        $data['blocks'] = array_map(function ($block) {
            if (($block['type'] ?? '') === 'list' && isset($block['data'])) {
                $style = $block['data']['style'] ?? 'unordered';
                $block['data']['style'] = $style;
                $block['data']['items'] = $this->normalizeEditorJsListItems($block['data']['items'] ?? []);

                if ($style === 'unordered') {
                    // Omitir 'meta' a nivel raíz para listas desordenadas:
                    // @editorjs/list verifica `!("meta" in e)`. Al no existir la propiedad,
                    // toma la vía rápida directa y no invoca structuredClone().
                    unset($block['data']['meta']);
                } else {
                    $block['data']['meta'] = ! empty($block['data']['meta']) && is_array($block['data']['meta'])
                        ? (object) $block['data']['meta']
                        : (object) ['start' => 1, 'counterType' => 'numeric'];
                }
            }

            // Normalizar URLs de imágenes para asegurar rutas relativas ('/storage/...')
            // evitando que queden fijadas a localhost o IPs específicas
            if (($block['type'] ?? '') === 'image' && isset($block['data'])) {
                if (isset($block['data']['file']['url']) && is_string($block['data']['file']['url'])) {
                    $parsed = parse_url($block['data']['file']['url'], PHP_URL_PATH);
                    if ($parsed && str_starts_with($parsed, '/storage/')) {
                        $block['data']['file']['url'] = $parsed;
                    }
                }
                if (isset($block['data']['url']) && is_string($block['data']['url'])) {
                    $parsed = parse_url($block['data']['url'], PHP_URL_PATH);
                    if ($parsed && str_starts_with($parsed, '/storage/')) {
                        $block['data']['url'] = $parsed;
                    }
                }
            }

            return $block;
        }, $data['blocks']);

        return $data;
    }

    /**
     * Normaliza recursivamente los elementos de lista para Editor.js List v2.
     */
    protected function normalizeEditorJsListItems(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        return array_map(function ($item) {
            if (is_string($item)) {
                return [
                    'content' => $item,
                    'meta' => (object) [],
                    'items' => [],
                ];
            }

            if (is_array($item)) {
                $content = $item['content'] ?? ($item['text'] ?? '');

                return [
                    'content' => (string) $content,
                    'meta' => ! empty($item['meta']) && is_array($item['meta'])
                        ? (object) $item['meta']
                        : (object) [],
                    'items' => isset($item['items']) && is_array($item['items'])
                        ? $this->normalizeEditorJsListItems($item['items'])
                        : [],
                ];
            }

            return ['content' => '', 'meta' => (object) [], 'items' => []];
        }, $items);
    }
}
