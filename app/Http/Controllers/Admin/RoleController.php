<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    /**
     * Display a listing of roles with their assigned permissions and user counts.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');

        $query = Role::with(['permissions' => function ($q) {
            $q->orderBy('modulo')->orderBy('name');
        }])->withCount('users');

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }

        $roles = $query->orderBy('name')->get()->map(function (Role $role) {
            return [
                'id' => $role->id,
                'name' => $role->name,
                'description' => $role->description,
                'guard_name' => $role->guard_name,
                'is_protected' => $role->isProtected(),
                'users_count' => $role->users_count,
                'permissions_count' => $role->permissions->count(),
                'permissions' => $role->permissions->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'modulo' => $p->modulo ?? 'General',
                    'description' => $p->description,
                ]),
                'permission_names' => $role->permissions->pluck('name')->toArray(),
                'created_at' => $role->created_at?->format('d/m/Y H:i'),
            ];
        });

        // Obtener todos los permisos agrupados por módulo para la matriz de asignación
        $allPermissions = Permission::orderBy('modulo')
            ->orderBy('name')
            ->get(['id', 'name', 'modulo', 'description']);

        $permissionsByModule = $allPermissions->groupBy(function ($item) {
            return $item->modulo ?: 'General';
        });

        $modules = $allPermissions->pluck('modulo')->unique()->filter()->values();

        return Inertia::render('Admin/Roles/Index', [
            'roles' => $roles,
            'allPermissions' => $allPermissions,
            'permissionsByModule' => $permissionsByModule,
            'modules' => $modules,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $role = Role::create([
            'name' => strtolower(trim($validated['name'])),
            'guard_name' => 'web',
            'description' => $validated['description'] ?? null,
        ]);

        if (! empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')
            ->with('success', "Rol '{$role->name}' creado exitosamente.");
    }

    /**
     * Update the specified role in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $validated = $request->validated();

        // Si es rol protegido, mantenemos su nombre original para evitar romper flujos del sistema
        if ($role->isProtected()) {
            $role->update([
                'description' => $validated['description'] ?? $role->description,
            ]);
        } else {
            $role->update([
                'name' => strtolower(trim($validated['name'])),
                'description' => $validated['description'] ?? null,
            ]);
        }

        $permissions = $validated['permissions'] ?? [];

        // Si es administrador, asegurarse de que conserve permisos críticos de gestión
        if (strtolower($role->name) === 'administrador') {
            $critical = ['usuarios.gestionar', 'roles.gestionar', 'permisos.gestionar'];
            $permissions = array_unique(array_merge($permissions, $critical));
        }

        $role->syncPermissions($permissions);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')
            ->with('success', "Rol '{$role->name}' actualizado exitosamente.");
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role): RedirectResponse
    {
        if ($role->isProtected()) {
            return redirect()->route('admin.roles.index')
                ->with('error', "El rol '{$role->name}' es un rol base del sistema protegido y no puede ser eliminado.");
        }

        $usersCount = $role->users()->count();
        if ($usersCount > 0) {
            return redirect()->route('admin.roles.index')
                ->with('error', "No se puede eliminar el rol '{$role->name}' porque tiene {$usersCount} usuario(s) asignado(s). Reasigna los usuarios a otro rol antes de eliminarlo.");
        }

        $roleName = $role->name;
        $role->syncPermissions([]);
        $role->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')
            ->with('success', "Rol '{$roleName}' eliminado exitosamente.");
    }
}
