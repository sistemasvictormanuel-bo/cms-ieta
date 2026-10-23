<?php

namespace App\Services;

use App\Models\Autor;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Throwable;

class AutorService
{
    public function obtenerTodos(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = Autor::with(['persona.media', 'tipoAutor', 'especialidades', 'gradoAcademico'])->latest('id');

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);
            $query->where(function ($q) use ($term) {
                if (is_numeric($term)) {
                    $q->orWhere('id', (int) $term);
                }
                $q->orWhere('at_slug', 'like', "%{$term}%")
                    ->orWhere('at_intereses_cientificos', 'like', "%{$term}%")
                    ->orWhereHas('persona', function ($pq) use ($term) {
                        $pq->where('p_nombre', 'like', "%{$term}%")
                            ->orWhere('p_apellido_paterno', 'like', "%{$term}%")
                            ->orWhere('p_apellido_materno', 'like', "%{$term}%")
                            ->orWhere('p_correo', 'like', "%{$term}%");
                    })
                    ->orWhereHas('especialidades', function ($eq) use ($term) {
                        $eq->where('ep_nombre', 'like', "%{$term}%");
                    });
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function obtenerListado(): Collection
    {
        return Autor::with(['persona.media', 'gradoAcademico', 'especialidades','persona'])
            ->where('at_estado', true)
            ->get();
    }

    public function obtenerPorId(int $id, bool $soloAprobados = false): Autor
    {
        $relations = [
            'persona.user.roles',
            'persona.media',
            'tipoAutor',
            'especialidades',
            'gradoAcademico',
        ];

        if ($soloAprobados) {
            $relations['articulos'] = function ($q) {
                $q->where('estado_revision', 'aprobado')->where('art_estado', true)->latest('id');
            };
        } else {
            $relations[] = 'articulos';
        }

        return Autor::with($relations)->findOrFail($id);
    }

    public function obtenerPorSlug(string $slug, bool $soloAprobados = false): Autor
    {
        $relations = [
            'persona.user.roles',
            'persona.media',
            'tipoAutor',
            'especialidades',
            'gradoAcademico',
        ];

        if ($soloAprobados) {
            $relations['articulos'] = function ($q) {
                $q->where('estado_revision', 'aprobado')->where('art_estado', true)->latest('id');
            };
        } else {
            $relations[] = 'articulos';
        }

        return Autor::with($relations)
            ->where('at_slug', $slug)
            ->firstOrFail();
    }

    public function guardar(array $data): Autor
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

            // 2. Crear Persona vinculada (o no) al usuario
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

            $baseName = $persona->nombre_completo;
            $baseSlug = ! empty($data['at_slug'])
                ? Str::slug($data['at_slug'])
                : Str::slug($baseName);

            $slug = $baseSlug ?: 'autor';
            $counter = 1;
            while (Autor::where('at_slug', $slug)->exists()) {
                $slug = "{$baseSlug}-{$counter}";
                $counter++;
            }

            // 3. Crear Autor
            $registro = Autor::create([
                'persona_id' => $persona->id,
                'tipo_autor_id' => $data['tipo_autor_id'],
                'grado_academico_id' => $data['grado_academico_id'],
                'at_biografia' => $data['at_biografia'] ?? ['blocks' => []],
                'at_slug' => $slug,
                'at_intereses_cientificos' => $data['at_intereses_cientificos'] ?? null,
                'at_estado' => isset($data['at_estado']) ? (bool) $data['at_estado'] : true,
            ]);

            // Sincronizar especialidades N:M
            if (! empty($data['especialidades'])) {
                $registro->especialidades()->sync($data['especialidades']);
            } elseif (! empty($data['especialidad_id'])) {
                $registro->especialidades()->sync([$data['especialidad_id']]);
            }

            DB::commit();

            return $registro->load(['persona.user.roles', 'tipoAutor', 'especialidades', 'gradoAcademico']);
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function actualizar(int $id, array $data): Autor
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
                        // Crear usuario para un autor existente
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

            $slug = $registro->at_slug;
            if (! empty($data['at_slug']) && $data['at_slug'] !== $registro->at_slug) {
                $baseSlug = Str::slug($data['at_slug']);
                $slug = $baseSlug;
                $counter = 1;
                while (Autor::where('at_slug', $slug)->where('id', '!=', $id)->exists()) {
                    $slug = "{$baseSlug}-{$counter}";
                    $counter++;
                }
            }

            $registro->update([
                'tipo_autor_id' => $data['tipo_autor_id'] ?? $registro->tipo_autor_id,
                'grado_academico_id' => $data['grado_academico_id'] ?? $registro->grado_academico_id,
                'at_biografia' => array_key_exists('at_biografia', $data) ? $data['at_biografia'] : $registro->at_biografia,
                'at_slug' => $slug,
                'at_intereses_cientificos' => array_key_exists('at_intereses_cientificos', $data) ? $data['at_intereses_cientificos'] : $registro->at_intereses_cientificos,
                'at_estado' => isset($data['at_estado']) ? (bool) $data['at_estado'] : $registro->at_estado,
            ]);

            // Sincronizar especialidades N:M
            if (isset($data['especialidades'])) {
                $registro->especialidades()->sync($data['especialidades']);
            } elseif (isset($data['especialidad_id'])) {
                $registro->especialidades()->sync([$data['especialidad_id']]);
            }

            DB::commit();

            return $registro->fresh(['persona.user', 'tipoAutor', 'especialidades', 'gradoAcademico', 'articulos']);
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
