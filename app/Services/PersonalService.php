<?php

namespace App\Services;

use App\Models\Persona;
use App\Models\Personal;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class PersonalService
{
    public function obtenerTodos(?string $search = null, ?bool $estado = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = Personal::with(['persona.media', 'cargo'])->latest('id');

        if ($estado !== null) {
            $query->where('pers_estado', $estado);
        }

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);
            $query->where(function ($q) use ($term) {
                if (is_numeric($term)) {
                    $q->orWhere('id', (int) $term);
                }
                $q->orWhere('pers_descripcion', 'like', "%{$term}%")
                    ->orWhereHas('persona', function ($pq) use ($term) {
                        $pq->where('p_nombre', 'like', "%{$term}%")
                            ->orWhere('p_apellido_paterno', 'like', "%{$term}%")
                            ->orWhere('p_apellido_materno', 'like', "%{$term}%")
                            ->orWhere('p_correo', 'like', "%{$term}%")
                            ->orWhere('p_celular', 'like', "%{$term}%");
                    })
                    ->orWhereHas('cargo', function ($cq) use ($term) {
                        $cq->where('ci_nombre', 'like', "%{$term}%");
                    });
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function obtenerListado(): Collection
    {
        return Personal::with(['persona.media', 'cargo'])
            ->where('pers_estado', true)
            ->get();
    }

    public function obtenerPorId(int $id): Personal
    {
        return Personal::with(['persona.user.roles', 'persona.media', 'cargo'])->findOrFail($id);
    }

    public function guardar(array $data): Personal
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

                $rol = ! empty($data['rol']) ? $data['rol'] : 'administrador';
                $user->assignRole($rol);
                $userId = $user->id;
            }

            // 2. Crear Persona vinculada
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

            // 3. Crear Personal
            $registro = Personal::create([
                'persona_id' => $persona->id,
                'cargo_id' => $data['cargo_id'],
                'pers_descripcion' => $data['pers_descripcion'] ?? null,
                'pers_fecha_ingreso' => $data['pers_fecha_ingreso'] ?? null,
                'pers_fecha_retiro' => $data['pers_fecha_retiro'] ?? null,
                'pers_estado' => isset($data['pers_estado']) ? (bool) $data['pers_estado'] : true,
            ]);

            DB::commit();

            return $registro->load(['persona.user.roles', 'cargo']);
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function actualizar(int $id, array $data): Personal
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
                        // Crear usuario para un personal existente
                        $password = ! empty($data['password']) ? Hash::make($data['password']) : Hash::make('Password123!');
                        $user = User::create([
                            'name' => $fullName,
                            'email' => $data['p_correo'] ?? $persona->p_correo,
                            'password' => $password,
                            'is_active' => isset($data['user_is_active']) ? (bool) $data['user_is_active'] : true,
                            'email_verified_at' => now(),
                        ]);

                        $rol = ! empty($data['rol']) ? $data['rol'] : 'administrador';
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
                'pers_descripcion' => array_key_exists('pers_descripcion', $data) ? $data['pers_descripcion'] : $registro->pers_descripcion,
                'pers_fecha_ingreso' => array_key_exists('pers_fecha_ingreso', $data) ? $data['pers_fecha_ingreso'] : $registro->pers_fecha_ingreso,
                'pers_fecha_retiro' => array_key_exists('pers_fecha_retiro', $data) ? $data['pers_fecha_retiro'] : $registro->pers_fecha_retiro,
                'pers_estado' => isset($data['pers_estado']) ? (bool) $data['pers_estado'] : $registro->pers_estado,
            ]);

            DB::commit();

            return $registro->fresh(['persona.user', 'cargo']);
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
