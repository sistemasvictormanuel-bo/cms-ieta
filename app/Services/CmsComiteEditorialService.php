<?php

namespace App\Services;

use App\Models\CmsComiteEditorial;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class CmsComiteEditorialService
{
    public function obtenerTodos(?string $search = null, ?string $tipo = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = CmsComiteEditorial::with(['persona.media', 'cargo', 'institucion'])->latest('id');

        if ($tipo !== null && trim($tipo) !== '') {
            $query->where('ce_tipo', $tipo);
        }

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);
            $query->where(function ($q) use ($term) {
                if (is_numeric($term)) {
                    $q->orWhere('id', (int) $term);
                }
                $q->orWhere('ce_tipo', 'like', "%{$term}%")
                    ->orWhereHas('persona', function ($pq) use ($term) {
                        $pq->where('p_nombre', 'like', "%{$term}%")
                            ->orWhere('p_apellido_paterno', 'like', "%{$term}%")
                            ->orWhere('p_apellido_materno', 'like', "%{$term}%")
                            ->orWhere('p_correo', 'like', "%{$term}%");
                    })
                    ->orWhereHas('cargo', function ($cq) use ($term) {
                        $cq->where('ci_nombre', 'like', "%{$term}%");
                    })
                    ->orWhereHas('institucion', function ($iq) use ($term) {
                        $iq->where('inst_nombre', 'like', "%{$term}%")
                            ->orWhere('inst_pais', 'like', "%{$term}%");
                    });
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function obtenerListado(): Collection
    {
        return CmsComiteEditorial::with(['persona.media', 'cargo', 'institucion'])->get();
    }

    public function obtenerPorId(int $id): CmsComiteEditorial
    {
        return CmsComiteEditorial::with(['persona.user.roles', 'persona.media', 'cargo', 'institucion'])->findOrFail($id);
    }

    public function guardar(array $data): CmsComiteEditorial
    {
        try {
            DB::beginTransaction();

            $fullName = trim("{$data['p_nombre']} {$data['p_apellido_paterno']}".(! empty($data['p_apellido_materno']) ? " {$data['p_apellido_materno']}" : ''));
            $crearUsuario = filter_var($data['crear_usuario'] ?? false, FILTER_VALIDATE_BOOLEAN);

            $userId = null;
            if ($crearUsuario) {
                $password = ! empty($data['password']) ? Hash::make($data['password']) : Hash::make('Password123!');
                $user = User::create([
                    'name' => $fullName,
                    'email' => $data['p_correo'],
                    'password' => $password,
                    'is_active' => isset($data['user_is_active']) ? (bool) $data['user_is_active'] : true,
                    'email_verified_at' => now(),
                ]);

                $rol = ! empty($data['rol']) ? $data['rol'] : 'autor';
                $user->assignRole($rol);
                $userId = $user->id;
            }

            // 2. Crear Persona
            $persona = Persona::create([
                'p_nombre' => $data['p_nombre'],
                'p_apellido_paterno' => $data['p_apellido_paterno'],
                'p_apellido_materno' => $data['p_apellido_materno'] ?? null,
                'p_celular' => $data['p_celular'] ?? null,
                'p_correo' => $data['p_correo'],
                'p_sitio_web' => $data['p_sitio_web'] ?? null,
                'user_id' => $userId,
            ]);

            // Foto de perfil opcional con Spatie Media Library
            if (! empty($data['foto']) && $data['foto'] instanceof UploadedFile) {
                $persona->clearMediaCollection('foto_perfil');
                $persona->addMedia($data['foto'])->toMediaCollection('foto_perfil');
            }

            // 3. Crear Comité Editorial
            $registro = CmsComiteEditorial::create([
                'persona_id' => $persona->id,
                'cargo_id' => $data['cargo_id'],
                'institucion_id' => $data['institucion_id'],
                'ce_tipo' => $data['ce_tipo'],
            ]);

            DB::commit();

            return $registro->load(['persona.user.roles', 'cargo', 'institucion']);
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function actualizar(int $id, array $data): CmsComiteEditorial
    {
        try {
            DB::beginTransaction();

            $registro = $this->obtenerPorId($id);

            // Actualizar Persona y User si se envían datos
            if (! empty($data['p_nombre']) && ! empty($data['p_apellido_paterno'])) {
                $persona = $registro->persona;
                if ($persona) {
                    $fullName = trim("{$data['p_nombre']} {$data['p_apellido_paterno']}".(! empty($data['p_apellido_materno']) ? " {$data['p_apellido_materno']}" : ''));
                    $crearUsuario = filter_var($data['crear_usuario'] ?? false, FILTER_VALIDATE_BOOLEAN);

                    if ($persona->user) {
                        $userUpdate = [
                            'name' => $fullName,
                            'email' => $data['p_correo'] ?? $persona->user->email,
                        ];
                        if (! empty($data['password'])) {
                            $userUpdate['password'] = Hash::make($data['password']);
                        }
                        if (isset($data['user_is_active'])) {
                            $userUpdate['is_active'] = (bool) $data['user_is_active'];
                        }
                        $persona->user->update($userUpdate);

                        if (! empty($data['rol'])) {
                            $persona->user->syncRoles([$data['rol']]);
                        }
                    } elseif ($crearUsuario) {
                        // Crear usuario para un miembro del comité existente
                        $password = ! empty($data['password']) ? Hash::make($data['password']) : Hash::make('Password123!');
                        $user = User::create([
                            'name' => $fullName,
                            'email' => $data['p_correo'] ?? $persona->p_correo,
                            'password' => $password,
                            'is_active' => isset($data['user_is_active']) ? (bool) $data['user_is_active'] : true,
                            'email_verified_at' => now(),
                        ]);

                        $rol = ! empty($data['rol']) ? $data['rol'] : 'autor';
                        $user->assignRole($rol);

                        $persona->update(['user_id' => $user->id]);
                    }

                    $persona->update([
                        'p_nombre' => $data['p_nombre'],
                        'p_apellido_paterno' => $data['p_apellido_paterno'],
                        'p_apellido_materno' => $data['p_apellido_materno'] ?? null,
                        'p_celular' => $data['p_celular'] ?? null,
                        'p_correo' => $data['p_correo'] ?? $persona->p_correo,
                        'p_sitio_web' => $data['p_sitio_web'] ?? null,
                    ]);

                    if (! empty($data['foto']) && $data['foto'] instanceof UploadedFile) {
                        $persona->clearMediaCollection('foto_perfil');
                        $persona->addMedia($data['foto'])->toMediaCollection('foto_perfil');
                    }
                }
            }

            $registro->update([
                'cargo_id' => $data['cargo_id'] ?? $registro->cargo_id,
                'institucion_id' => $data['institucion_id'] ?? $registro->institucion_id,
                'ce_tipo' => $data['ce_tipo'] ?? $registro->ce_tipo,
            ]);

            DB::commit();

            return $registro->fresh(['persona.user', 'cargo', 'institucion']);
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
            $eliminado = (bool) $registro->delete();

            DB::commit();

            return $eliminado;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
