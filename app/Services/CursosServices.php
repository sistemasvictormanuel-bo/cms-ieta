<?php

namespace App\Services;

use App\Models\CmsCursos;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CursosServices
{
    public function obtenerTodos(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = CmsCursos::with(['media', 'tipoCurso', 'modalidadCurso', 'docente'])->latest('id');

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);

            $query->where(function ($q) use ($term) {
                if (is_numeric($term)) {
                    $q->orWhere('id', (int) $term);
                }
                $q->orWhere('ct_titulo', 'like', "%{$term}%")
                    ->orWhere('ct_slug', 'like', "%{$term}%");
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function obtenerPorId(int $id): CmsCursos
    {
        return CmsCursos::with(['media', 'tipoCurso', 'modalidadCurso', 'docente'])->findOrFail($id);
    }

    public function guardarCurso(array $data, ?UploadedFile $imagen = null): CmsCursos
    {
        try {
            DB::beginTransaction();

            $baseSlug = ! empty($data['ct_slug'])
                ? Str::slug($data['ct_slug'])
                : Str::slug($data['ct_titulo'] ?? 'curso');

            $slug = $baseSlug;
            $counter = 1;
            while (CmsCursos::where('ct_slug', $slug)->exists()) {
                $slug = "{$baseSlug}-{$counter}";
                $counter++;
            }

            $registro = CmsCursos::create([
                'ct_titulo' => $data['ct_titulo'] ?? null,
                'tipo_curso_id' => $data['tipo_curso_id'] ?? null,
                'modalidad_curso_id' => $data['modalidad_curso_id'] ?? null,
                'docente_id' => ! empty($data['docente_id']) ? $data['docente_id'] : null,
                'ct_fecha_inicio' => $data['ct_fecha_inicio'] ?? null,
                'ct_fecha_fin' => $data['ct_fecha_fin'] ?? null,
                'ct_hora_inicio' => $data['ct_hora_inicio'] ?? null,
                'ct_hora_fin' => $data['ct_hora_fin'] ?? null,
                'ct_duracion' => $data['ct_duracion'] ?? null,
                'ct_descripcion' => $data['ct_descripcion'] ?? null,
                'ct_contenido' => $data['ct_contenido'] ?? ['blocks' => []],
                'ct_email_info' => $data['ct_email_info'] ?? null,
                'ct_telefono_ref' => $data['ct_telefono_ref'] ?? null,
                'ct_slug' => $slug,
                'ct_activo' => isset($data['ct_activo']) ? (bool) $data['ct_activo'] : true,
                'ct_costo' => isset($data['ct_costo']) && $data['ct_costo'] !== '' ? $data['ct_costo'] : 0,
            ]);

            if ($imagen) {
                $registro->addMedia($imagen)->toMediaCollection('portada_curso');
            }

            DB::commit();

            return $registro->load(['media', 'tipoCurso', 'modalidadCurso', 'docente']);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Actualiza un curso existente y su imagen de portada opcional.
     */
    public function actualizarCurso(int $id, array $data, ?UploadedFile $imagen = null): CmsCursos
    {
        try {
            DB::beginTransaction();

            $registro = $this->obtenerPorId($id);

            $slug = $registro->ct_slug;
            if (! empty($data['ct_slug']) && $data['ct_slug'] !== $registro->ct_slug) {
                $baseSlug = Str::slug($data['ct_slug']);
                $slug = $baseSlug;
                $counter = 1;
                while (CmsCursos::where('ct_slug', $slug)->where('id', '!=', $id)->exists()) {
                    $slug = "{$baseSlug}-{$counter}";
                    $counter++;
                }
            } elseif (isset($data['ct_titulo']) && $data['ct_titulo'] !== $registro->ct_titulo && empty($data['ct_slug'])) {
                $baseSlug = Str::slug($data['ct_titulo']);
                $slug = $baseSlug;
                $counter = 1;
                while (CmsCursos::where('ct_slug', $slug)->where('id', '!=', $id)->exists()) {
                    $slug = "{$baseSlug}-{$counter}";
                    $counter++;
                }
            }

            $registro->update([
                'ct_titulo' => $data['ct_titulo'] ?? $registro->ct_titulo,
                'tipo_curso_id' => $data['tipo_curso_id'] ?? $registro->tipo_curso_id,
                'modalidad_curso_id' => $data['modalidad_curso_id'] ?? $registro->modalidad_curso_id,
                'docente_id' => array_key_exists('docente_id', $data) ? (! empty($data['docente_id']) ? $data['docente_id'] : null) : $registro->docente_id,
                'ct_fecha_inicio' => $data['ct_fecha_inicio'] ?? $registro->ct_fecha_inicio,
                'ct_fecha_fin' => $data['ct_fecha_fin'] ?? $registro->ct_fecha_fin,
                'ct_hora_inicio' => array_key_exists('ct_hora_inicio', $data) ? $data['ct_hora_inicio'] : $registro->ct_hora_inicio,
                'ct_hora_fin' => array_key_exists('ct_hora_fin', $data) ? $data['ct_hora_fin'] : $registro->ct_hora_fin,
                'ct_duracion' => array_key_exists('ct_duracion', $data) ? $data['ct_duracion'] : $registro->ct_duracion,
                'ct_descripcion' => array_key_exists('ct_descripcion', $data) ? $data['ct_descripcion'] : $registro->ct_descripcion,
                'ct_contenido' => array_key_exists('ct_contenido', $data) ? $data['ct_contenido'] : $registro->ct_contenido,
                'ct_email_info' => array_key_exists('ct_email_info', $data) ? $data['ct_email_info'] : $registro->ct_email_info,
                'ct_telefono_ref' => array_key_exists('ct_telefono_ref', $data) ? $data['ct_telefono_ref'] : $registro->ct_telefono_ref,
                'ct_slug' => $slug,
                'ct_activo' => isset($data['ct_activo']) ? (bool) $data['ct_activo'] : $registro->ct_activo,
                'ct_costo' => array_key_exists('ct_costo', $data) ? ($data['ct_costo'] !== '' ? $data['ct_costo'] : null) : $registro->ct_costo,
            ]);

            // Si envían una nueva imagen, limpiamos la colección anterior y agregamos la nueva
            if ($imagen) {
                $registro->clearMediaCollection('portada_curso');
                $registro->addMedia($imagen)->toMediaCollection('portada_curso');
            }

            DB::commit();

            return $registro->fresh(['media', 'tipoCurso', 'modalidadCurso', 'docente']);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Elimina un curso y sus recursos multimedia asociados.
     */
    public function eliminarCurso(int $id): bool
    {
        try {
            DB::beginTransaction();

            $registro = $this->obtenerPorId($id); // O CmsCursos::findOrFail($id);

            // Limpiamos los archivos multimedia de Spatie asociados a este curso
            $registro->clearMediaCollection('portada_curso');

            $eliminado = (bool) $registro->delete();

            DB::commit();

            return $eliminado;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
