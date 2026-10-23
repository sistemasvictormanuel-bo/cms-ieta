<?php

namespace App\Services;

use App\Models\TipoArticulo;
use Illuminate\Database\Eloquent\Collection;

class TipoArticuloService
{
    public function obtenerTodos(): Collection
    {
        return TipoArticulo::withCount('articulos')->orderBy('id')->get();
    }

    public function obtenerListaParaSelect(): Collection
    {
        return TipoArticulo::where('tar_estado', true)
            ->orderBy('id')
            ->get(['id', 'tar_nombre']);
    }

    public function obtenerPorId(int $id): TipoArticulo
    {
        return TipoArticulo::withCount('articulos')->findOrFail($id);
    }

    public function guardar(array $data): TipoArticulo
    {
        return TipoArticulo::create([
            'tar_nombre' => trim($data['tar_nombre']),
            'tar_descripcion' => $data['tar_descripcion'] ?? null,
            'tar_estado' => isset($data['tar_estado']) ? (bool) $data['tar_estado'] : true,
        ]);
    }

    public function actualizar(int $id, array $data): TipoArticulo
    {
        $registro = $this->obtenerPorId($id);
        $registro->update([
            'tar_nombre' => trim($data['tar_nombre'] ?? $registro->tar_nombre),
            'tar_descripcion' => array_key_exists('tar_descripcion', $data) ? $data['tar_descripcion'] : $registro->tar_descripcion,
            'tar_estado' => isset($data['tar_estado']) ? (bool) $data['tar_estado'] : $registro->tar_estado,
        ]);

        return $registro;
    }

    public function eliminar(int $id): bool
    {
        $registro = $this->obtenerPorId($id);

        return (bool) $registro->delete();
    }
}
