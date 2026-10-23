<?php

namespace App\Services;

use App\Models\CmsComunicado;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;

class CmsComunicadoService
{
    public function obtenerTodos(
        ?string $search = null,
        ?bool $activo = null,
        ?string $tipo = null,
        int $perPage = 10
    ): LengthAwarePaginator {
        $query = CmsComunicado::with('media')->latest('id');

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);
            $query->where(function ($q) use ($term) {
                if (is_numeric($term)) {
                    $q->orWhere('id', (int) $term);
                }
                $q->orWhere('com_titulo', 'like', "%{$term}%")
                    ->orWhere('com_descripcion', 'like', "%{$term}%")
                    ->orWhere('com_enlace_url', 'like', "%{$term}%");
            });
        }

        if ($activo !== null) {
            $query->where('com_activo', $activo);
        }

        if ($tipo !== null && trim($tipo) !== '') {
            $query->where('com_tipo', $tipo);
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function obtenerPorId(int $id): CmsComunicado
    {
        return CmsComunicado::with('media')->findOrFail($id);
    }

    /**
     * Retorna el comunicado activo y vigente más reciente para el popup emergente.
     * Incrementa automáticamente el contador de visualizaciones.
     */
    public function obtenerActivo(): ?CmsComunicado
    {
        $comunicado = CmsComunicado::with('media')
            ->vigente()
            ->latest('id')
            ->first();

        if ($comunicado) {
            $comunicado->increment('com_vistas');
        }

        return $comunicado;
    }

    public function guardar(array $data, ?UploadedFile $flyer = null): CmsComunicado
    {
        try {
            DB::beginTransaction();

            $registro = CmsComunicado::create([
                'com_titulo' => $data['com_titulo'],
                'com_descripcion' => $data['com_descripcion'] ?? null,
                'com_enlace_url' => ! empty($data['com_enlace_url']) ? $data['com_enlace_url'] : null,
                'com_texto_boton' => ! empty($data['com_texto_boton']) ? $data['com_texto_boton'] : 'Más información',
                'com_tipo' => $data['com_tipo'] ?? 'general',
                'com_fecha_inicio' => $data['com_fecha_inicio'],
                'com_fecha_fin' => ! empty($data['com_fecha_fin']) ? $data['com_fecha_fin'] : null,
                'com_activo' => isset($data['com_activo']) ? (bool) $data['com_activo'] : true,
                'com_vistas' => 0,
            ]);

            if ($flyer) {
                $registro->addMedia($flyer)->toMediaCollection('com_flyer');
            }

            DB::commit();

            return $registro->load('media');
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function actualizar(int $id, array $data, ?UploadedFile $flyer = null): CmsComunicado
    {
        try {
            DB::beginTransaction();

            $registro = $this->obtenerPorId($id);

            $registro->update([
                'com_titulo' => $data['com_titulo'] ?? $registro->com_titulo,
                'com_descripcion' => array_key_exists('com_descripcion', $data) ? $data['com_descripcion'] : $registro->com_descripcion,
                'com_enlace_url' => array_key_exists('com_enlace_url', $data) ? (! empty($data['com_enlace_url']) ? $data['com_enlace_url'] : null) : $registro->com_enlace_url,
                'com_texto_boton' => array_key_exists('com_texto_boton', $data) ? (! empty($data['com_texto_boton']) ? $data['com_texto_boton'] : 'Más información') : $registro->com_texto_boton,
                'com_tipo' => $data['com_tipo'] ?? $registro->com_tipo,
                'com_fecha_inicio' => $data['com_fecha_inicio'] ?? $registro->com_fecha_inicio,
                'com_fecha_fin' => array_key_exists('com_fecha_fin', $data) ? (! empty($data['com_fecha_fin']) ? $data['com_fecha_fin'] : null) : $registro->com_fecha_fin,
                'com_activo' => isset($data['com_activo']) ? (bool) $data['com_activo'] : $registro->com_activo,
            ]);

            if ($flyer) {
                $registro->clearMediaCollection('com_flyer');
                $registro->addMedia($flyer)->toMediaCollection('com_flyer');
            }

            DB::commit();

            return $registro->fresh('media');
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function toggleActivo(int $id): CmsComunicado
    {
        $registro = $this->obtenerPorId($id);
        $registro->update(['com_activo' => ! $registro->com_activo]);

        return $registro;
    }

    public function eliminar(int $id): bool
    {
        try {
            DB::beginTransaction();

            $registro = $this->obtenerPorId($id);
            $registro->clearMediaCollection('com_flyer');
            $eliminado = (bool) $registro->delete();

            DB::commit();

            return $eliminado;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
