<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Autor;
use App\Models\Persona;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', User::class);

        $search = $request->input('search');
        $roleFilter = $request->input('role');
        $statusFilter = $request->input('status');
        $perPage = (int) $request->input('per_page', 10);

        $query = User::with(['roles', 'persona.autor'])
            ->latest('id');

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        }

        if ($roleFilter) {
            $query->whereHas('roles', function ($q) use ($roleFilter) {
                $q->where('name', $roleFilter);
            });
        }

        if ($statusFilter !== null && $statusFilter !== '') {
            $query->where('is_active', $statusFilter === 'active');
        }

        $usuarios = $query->paginate($perPage)->withQueryString();

        // Mapear propiedades para el frontend
        $usuarios->getCollection()->transform(function (User $user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'profile_photo_url' => $user->profile_photo_url,
                'is_active' => (bool) $user->is_active,
                'two_factor_enabled' => $user->hasEnabledTwoFactorAuthentication(),
                'roles' => $user->getRoleNames(),
                'created_at' => $user->created_at?->format('d/m/Y H:i'),
                'autor_asociado' => $user->persona?->autor ? [
                    'id' => $user->persona->autor->id,
                    'nombre' => $user->persona->nombre_completo,
                ] : null,
            ];
        });

        return Inertia::render('Admin/Usuarios/Index', [
            'usuarios' => $usuarios,
            'roles' => Role::pluck('name'),
            'filters' => [
                'search' => $search,
                'role' => $roleFilter,
                'status' => $statusFilter,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', User::class);

        // Obtener autores disponibles que no tengan usuario asociado
        $autores = Autor::with('persona')
            ->whereHas('persona', function ($q) {
                $q->whereNull('user_id');
            })
            ->get()
            ->map(fn (Autor $a) => [
                'id' => $a->id,
                'nombre' => $a->persona?->nombre_completo ?? 'Sin nombre',
                'correo' => $a->persona?->p_correo ?? '',
            ]);

        return Inertia::render('Admin/Usuarios/Create', [
            'roles' => Role::pluck('name'),
            'autores' => $autores,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', User::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'is_active' => ['boolean'],
            'autor_id' => ['nullable', 'exists:cms_autores,id'],
        ], [
            'name.required' => 'El nombre completo es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'role.required' => 'Debes asignar un rol al usuario.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_active' => $validated['is_active'] ?? true,
            'email_verified_at' => now(),
        ]);

        $user->assignRole($validated['role']);

        // Si se vinculó un autor explícitamente
        if (! empty($validated['autor_id'])) {
            $autor = Autor::find($validated['autor_id']);
            if ($autor && $autor->persona) {
                $autor->persona->update(['user_id' => $user->id]);
            }
        } else {
            // Vincular automáticamente si existe una Persona con el mismo correo
            $persona = Persona::where('p_correo', $user->email)->first();
            if ($persona && ! $persona->user_id) {
                $persona->update(['user_id' => $user->id]);
            }
        }

        return redirect()->route('admin.usuarios.index')
            ->with('success', "Usuario '{$user->name}' registrado exitosamente con rol {$validated['role']}.");
    }

    public function edit(User $usuario): Response
    {
        Gate::authorize('update', $usuario);

        $autorActual = $usuario->persona?->autor;

        // Autores disponibles para vincular (sin usuario o el actualmente asignado a este usuario)
        $autores = Autor::with('persona')
            ->whereHas('persona', function ($q) use ($usuario) {
                $q->whereNull('user_id')->orWhere('user_id', $usuario->id);
            })
            ->get()
            ->map(fn (Autor $a) => [
                'id' => $a->id,
                'nombre' => $a->persona?->nombre_completo ?? 'Sin nombre',
                'correo' => $a->persona?->p_correo ?? '',
            ]);

        return Inertia::render('Admin/Usuarios/Edit', [
            'usuario' => [
                'id' => $usuario->id,
                'name' => $usuario->name,
                'email' => $usuario->email,
                'is_active' => (bool) $usuario->is_active,
                'role' => $usuario->roles->first()?->name ?? 'autor',
                'autor_id' => $autorActual?->id,
                'two_factor_enabled' => $usuario->hasEnabledTwoFactorAuthentication(),
            ],
            'roles' => Role::pluck('name'),
            'autores' => $autores,
        ]);
    }

    public function update(Request $request, User $usuario): RedirectResponse
    {
        Gate::authorize('update', $usuario);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($usuario->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'is_active' => ['boolean'],
            'autor_id' => ['nullable', 'exists:cms_autores,id'],
        ], [
            'name.required' => 'El nombre completo es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.unique' => 'Este correo ya pertenece a otro usuario.',
            'password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'role.required' => 'Debes asignar un rol al usuario.',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'is_active' => $validated['is_active'] ?? $usuario->is_active,
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $usuario->update($updateData);
        $usuario->syncRoles([$validated['role']]);

        // Manejar vínculo con Autor/Persona
        if (array_key_exists('autor_id', $validated)) {
            // Desvincular personas previas si las había
            Persona::where('user_id', $usuario->id)->update(['user_id' => null]);

            if (! empty($validated['autor_id'])) {
                $autor = Autor::find($validated['autor_id']);
                if ($autor && $autor->persona) {
                    $autor->persona->update(['user_id' => $usuario->id]);
                }
            }
        }

        return redirect()->route('admin.usuarios.index')
            ->with('success', "Usuario '{$usuario->name}' actualizado exitosamente.");
    }

    public function destroy(User $usuario): RedirectResponse
    {
        Gate::authorize('delete', $usuario);

        if (auth()->id() === $usuario->id) {
            return redirect()->route('admin.usuarios.index')
                ->with('error', 'No puedes eliminar tu propia cuenta de usuario en sesión.');
        }

        // Desvincular de persona si existía
        Persona::where('user_id', $usuario->id)->update(['user_id' => null]);

        $nombre = $usuario->name;
        $usuario->delete();

        return redirect()->route('admin.usuarios.index')
            ->with('success', "Usuario '{$nombre}' eliminado exitosamente.");
    }

    public function toggleActivo(User $usuario): RedirectResponse
    {
        Gate::authorize('update', $usuario);

        if (auth()->id() === $usuario->id) {
            return redirect()->back()
                ->with('error', 'No puedes desactivar tu propia cuenta mientras estás en sesión.');
        }

        $usuario->is_active = ! $usuario->is_active;
        $usuario->save();

        $estado = $usuario->is_active ? 'activada' : 'desactivada';

        return redirect()->back()
            ->with('success', "La cuenta de '{$usuario->name}' fue {$estado} correctamente.");
    }
}
