<?php

namespace App\Services;

use App\Models\CmsBancoDatos;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;

class CmsBancoDatosService
{
    public function obtenerTodos(?string $search = null, ?string $tipo = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = CmsBancoDatos::with(['areasTematicas', 'media'])->latest('id');

        if ($tipo !== null && trim($tipo) !== '') {
            $query->where('bdd_tipo_dato', $tipo);
        }

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);
            $query->where(function ($q) use ($term) {
                if (is_numeric($term)) {
                    $q->orWhere('id', (int) $term)
                        ->orWhere('bdd_anio', (int) $term);
                }
                $q->orWhere('bdd_codigo', 'like', "%{$term}%")
                    ->orWhere('bdd_titulo', 'like', "%{$term}%")
                    ->orWhere('bdd_descripcion', 'like', "%{$term}%")
                    ->orWhere('bdd_locacion', 'like', "%{$term}%")
                    ->orWhere('bdd_url', 'like', "%{$term}%")
                    ->orWhereHas('areasTematicas', function ($aq) use ($term) {
                        $aq->where('at_nombre', 'like', "%{$term}%");
                    });
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function obtenerListado(): Collection
    {
        return CmsBancoDatos::with(['areasTematicas', 'media'])->orderBy('bdd_titulo')->get();
    }

    public function obtenerPorId(int $id): CmsBancoDatos
    {
        return CmsBancoDatos::with(['areasTematicas', 'media'])->findOrFail($id);
    }

    public function guardar(array $data, ?UploadedFile $archivo = null, ?UploadedFile $portada = null): CmsBancoDatos
    {
        try {
            DB::beginTransaction();

            $registro = CmsBancoDatos::create([
                'bdd_tipo_dato' => $data['bdd_tipo_dato'],
                'bdd_anio' => $data['bdd_anio'],
                'bdd_codigo' => $data['bdd_codigo'],
                'bdd_titulo' => $data['bdd_titulo'],
                'bdd_descripcion' => $data['bdd_descripcion'] ?? null,
                'bdd_locacion' => $data['bdd_locacion'] ?? null,
                'bdd_url' => $data['bdd_url'] ?? null,
            ]);

            if (isset($data['areas']) && is_array($data['areas'])) {
                $registro->areasTematicas()->sync($data['areas']);
            }

            if ($archivo) {
                $registro->addMedia($archivo)->toMediaCollection('bdd_archivo');
            }

            if ($portada) {
                $registro->addMedia($portada)->toMediaCollection('bdd_portada');
            }

            DB::commit();

            return $registro->load(['areasTematicas', 'media']);
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function actualizar(int $id, array $data, ?UploadedFile $archivo = null, ?UploadedFile $portada = null): CmsBancoDatos
    {
        try {
            DB::beginTransaction();

            $registro = $this->obtenerPorId($id);

            $registro->update([
                'bdd_tipo_dato' => $data['bdd_tipo_dato'] ?? $registro->bdd_tipo_dato,
                'bdd_anio' => $data['bdd_anio'] ?? $registro->bdd_anio,
                'bdd_codigo' => $data['bdd_codigo'] ?? $registro->bdd_codigo,
                'bdd_titulo' => $data['bdd_titulo'] ?? $registro->bdd_titulo,
                'bdd_descripcion' => array_key_exists('bdd_descripcion', $data) ? $data['bdd_descripcion'] : $registro->bdd_descripcion,
                'bdd_locacion' => array_key_exists('bdd_locacion', $data) ? $data['bdd_locacion'] : $registro->bdd_locacion,
                'bdd_url' => array_key_exists('bdd_url', $data) ? $data['bdd_url'] : $registro->bdd_url,
            ]);

            if (isset($data['areas']) && is_array($data['areas'])) {
                $registro->areasTematicas()->sync($data['areas']);
            }

            if ($archivo) {
                $registro->clearMediaCollection('bdd_archivo');
                $registro->addMedia($archivo)->toMediaCollection('bdd_archivo');
            }

            if ($portada) {
                $registro->clearMediaCollection('bdd_portada');
                $registro->addMedia($portada)->toMediaCollection('bdd_portada');
            }

            DB::commit();

            return $registro->fresh(['areasTematicas', 'media']);
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function eliminar(int $id): bool
    {
        try {
            DB::beginTransaction();

            $registro = $this->obtenerPorId($id);
            $registro->clearMediaCollection('bdd_archivo');
            $registro->clearMediaCollection('bdd_portada');
            $registro->areasTematicas()->detach();
            $eliminado = (bool) $registro->delete();

            DB::commit();

            return $eliminado;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
