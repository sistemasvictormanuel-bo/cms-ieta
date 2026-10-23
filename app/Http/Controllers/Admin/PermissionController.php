<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePermissionRequest;
use App\Http\Requests\UpdatePermissionRequest;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\PermissionRegistrar;

class PermissionController extends Controller
{
    /**
     * Display a listing of permissions with module groupings and roles.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $moduleFilter = $request->input('modulo');
        $roleFilter = $request->input('role');
        $perPage = (int) $request->input('per_page', 20);

        $query = Permission::with('roles')
            ->latest('id');

        if ($search !== null && trim($search) !== '') {
            $term = trim($search);
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhere('modulo', 'like', "%{$term}%");
            });
        }

        if ($moduleFilter !== null && $moduleFilter !== '') {
            $query->where('modulo', $moduleFilter);
        }

        if ($roleFilter !== null && $roleFilter !== '') {
            $query->whereHas('roles', function ($q) use ($roleFilter) {
                $q->where('name', $roleFilter);
            });
        }

        $permissions = $query->paginate($perPage)->withQueryString();

        $permissions->getCollection()->transform(function (Permission $permission) {
            return [
                'id' => $permission->id,
                'name' => $permission->name,
                'modulo' => $permission->modulo ?? 'General',
                'description' => $permission->description,
                'is_protected' => $permission->isProtected(),
                'roles' => $permission->roles->pluck('name')->toArray(),
                'created_at' => $permission->created_at?->format('d/m/Y H:i'),
            ];
        });

        // Obtener la lista completa de módulos existentes únicos
        $modules = Permission::select('modulo')
            ->distinct()
            ->whereNotNull('modulo')
            ->orderBy('modulo')
            ->pluck('modulo');

        // Módulos con conteo de permisos para visualización rápida de chips
        $modulesWithCount = Permission::selectRaw('modulo, count(*) as count')
            ->groupBy('modulo')
            ->orderBy('modulo')
            ->get();

        // Roles disponibles para asignación directa
        $roles = Role::pluck('name');

        return Inertia::render('Admin/Permisos/Index', [
            'permissions' => $permissions,
            'modules' => $modules,
            'modulesWithCount' => $modulesWithCount,
            'roles' => $roles,
            'filters' => [
                'search' => $search,
                'modulo' => $moduleFilter,
                'role' => $roleFilter,
                'per_page' => $perPage,
            ],
            'total' => Permission::count(),
        ]);
    }

    /**
     * Store a newly created permission in storage.
     */
    public function store(StorePermissionRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $permission = Permission::create([
            'name' => strtolower(trim($validated['name'])),
            'guard_name' => 'web',
            'modulo' => trim($validated['modulo']),
            'description' => $validated['description'] ?? null,
        ]);

        if (! empty($validated['roles'])) {
            $permission->syncRoles($validated['roles']);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.permisos.index')
            ->with('success', "Permiso '{$permission->name}' registrado exitosamente para el módulo '{$permission->modulo}'.");
    }

    /**
     * Update the specified permission in storage.
     */
    public function update(UpdatePermissionRequest $request, Permission $permiso): RedirectResponse
    {
        $validated = $request->validated();

        $permiso->update([
            'name' => strtolower(trim($validated['name'])),
            'modulo' => trim($validated['modulo']),
            'description' => $validated['description'] ?? null,
        ]);

        if (isset($validated['roles'])) {
            $permiso->syncRoles($validated['roles']);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.permisos.index')
            ->with('success', "Permiso '{$permiso->name}' actualizado exitosamente.");
    }

    /**
     * Remove the specified permission from storage.
     */
    public function destroy(Permission $permiso): RedirectResponse
    {
        if ($permiso->isProtected()) {
            return redirect()->route('admin.permisos.index')
                ->with('error', "El permiso '{$permiso->name}' es un permiso esencial del sistema y no puede ser eliminado.");
        }

        $name = $permiso->name;
        $permiso->syncRoles([]);
        $permiso->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.permisos.index')
            ->with('success', "Permiso '{$name}' eliminado exitosamente.");
    }
}
