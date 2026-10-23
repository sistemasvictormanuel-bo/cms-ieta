<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Matriz de Permisos del Sistema categorizados por Módulo
        $permissions = [
            // Módulo: Artículos
            [
                'name' => 'articulos.crear',
                'modulo' => 'Artículos',
                'description' => 'Postular y crear nuevos artículos científicos',
            ],
            [
                'name' => 'articulos.editar-propios',
                'modulo' => 'Artículos',
                'description' => 'Editar artículos propios en borrador u observados',
            ],
            [
                'name' => 'articulos.ver-propios',
                'modulo' => 'Artículos',
                'description' => 'Ver y monitorear el estado de artículos propios',
            ],
            [
                'name' => 'articulos.revisar',
                'modulo' => 'Artículos',
                'description' => 'Aprobar, observar o rechazar artículos en revisión',
            ],
            [
                'name' => 'articulos.gestionar-todos',
                'modulo' => 'Artículos',
                'description' => 'Ver, editar y gestionar todos los artículos de la revista',
            ],
            [
                'name' => 'articulos.eliminar',
                'modulo' => 'Artículos',
                'description' => 'Eliminar artículos del sistema',
            ],

            // Módulo: Usuarios y Seguridad
            [
                'name' => 'usuarios.gestionar',
                'modulo' => 'Usuarios y Seguridad',
                'description' => 'Crear, editar, activar/desactivar y eliminar cuentas de usuario',
            ],
            [
                'name' => 'roles.gestionar',
                'modulo' => 'Usuarios y Seguridad',
                'description' => 'Crear nuevos roles y asignar permisos a los roles',
            ],
            [
                'name' => 'permisos.gestionar',
                'modulo' => 'Usuarios y Seguridad',
                'description' => 'Crear y gestionar permisos para módulos actuales o futuros',
            ],
            [
                'name' => 'perfil.editar-propio',
                'modulo' => 'Usuarios y Seguridad',
                'description' => 'Modificar perfil propio, contraseña y seguridad 2FA',
            ],

            // Módulo: Revista y Publicaciones
            [
                'name' => 'revista.gestionar',
                'modulo' => 'Revista y Publicaciones',
                'description' => 'Gestionar perfil institucional de Revista Varianza y directrices',
            ],
            [
                'name' => 'ediciones.gestionar',
                'modulo' => 'Revista y Publicaciones',
                'description' => 'Crear y administrar volúmenes y ediciones de la revista',
            ],
            [
                'name' => 'instrucciones.gestionar',
                'modulo' => 'Revista y Publicaciones',
                'description' => 'Administrar normativas y guías para autores postulantes',
            ],
            [
                'name' => 'memorias.gestionar',
                'modulo' => 'Revista y Publicaciones',
                'description' => 'Gestionar memorias de investigación y congresos científicos',
            ],
            [
                'name' => 'autores.gestionar',
                'modulo' => 'Revista y Publicaciones',
                'description' => 'Administrar catálogo de autores, grados y afiliaciones',
            ],

            // Módulo: Banco de Datos
            [
                'name' => 'banco-datos.gestionar',
                'modulo' => 'Banco de Datos',
                'description' => 'Administrar datasets, áreas temáticas y recursos descargables',
            ],
            [
                'name' => 'banco-datos.exportar',
                'modulo' => 'Banco de Datos',
                'description' => 'Exportar datos e inventarios en formato Excel / CSV',
            ],

            // Módulo: Cursos y Capacitación
            [
                'name' => 'cursos.gestionar',
                'modulo' => 'Cursos y Capacitación',
                'description' => 'Crear, editar y publicar cursos, modalidades y categorías',
            ],

            // Módulo: CMS y Portal Web
            [
                'name' => 'cms.gestionar',
                'modulo' => 'CMS y Portal Web',
                'description' => 'Configuración general del portal web e información institucional',
            ],
            [
                'name' => 'noticias.gestionar',
                'modulo' => 'CMS y Portal Web',
                'description' => 'Crear, publicar y gestionar noticias y novedades',
            ],
            [
                'name' => 'galeria.gestionar',
                'modulo' => 'CMS y Portal Web',
                'description' => 'Administrar galerías fotográficas y eventos institucionales',
            ],
            [
                'name' => 'comunicados.gestionar',
                'modulo' => 'CMS y Portal Web',
                'description' => 'Publicar comunicados urgentes y banners emergentes',
            ],
            [
                'name' => 'servicios.gestionar',
                'modulo' => 'CMS y Portal Web',
                'description' => 'Administrar catálogo de servicios científicos y técnicos',
            ],

            // Módulo: Institucional
            [
                'name' => 'personal.gestionar',
                'modulo' => 'Institucional',
                'description' => 'Administrar nómina de investigadores, personal y cargos',
            ],
            [
                'name' => 'comite.gestionar',
                'modulo' => 'Institucional',
                'description' => 'Gestionar miembros del comité editorial nacional e internacional',
            ],
            [
                'name' => 'reglamento.gestionar',
                'modulo' => 'Institucional',
                'description' => 'Publicar y actualizar el reglamento interno y normativas',
            ],
        ];

        foreach ($permissions as $permData) {
            Permission::updateOrCreate(
                ['name' => $permData['name'], 'guard_name' => 'web'],
                [
                    'modulo' => $permData['modulo'],
                    'description' => $permData['description'],
                ]
            );
        }

        // 2. Roles del Sistema
        $adminRole = Role::updateOrCreate(
            ['name' => 'administrador', 'guard_name' => 'web'],
            [
                'description' => 'Acceso y control total sobre todos los módulos y configuraciones del sistema IETA.',
            ]
        );
        $adminRole->syncPermissions(Permission::all());

        $autorRole = Role::updateOrCreate(
            ['name' => 'autor', 'guard_name' => 'web'],
            [
                'description' => 'Acceso específico para autores e investigadores: postulación de artículos y gestión de perfil.',
            ]
        );
        $autorRole->syncPermissions([
            'articulos.crear',
            'articulos.editar-propios',
            'articulos.ver-propios',
            'perfil.editar-propio',
        ]);

        // 3. Asignar rol de administrador al usuario principal o crear uno por defecto
        $users = User::all();
        if ($users->isNotEmpty()) {
            foreach ($users as $user) {
                if ($user->roles->isEmpty()) {
                    $user->assignRole($adminRole);
                }
                if ($user->is_active === null) {
                    $user->is_active = true;
                    $user->save();
                }
            }
        } else {
            $admin = User::firstOrCreate(
                ['email' => 'admin@ieta.edu.bo'],
                [
                    'name' => 'Administrador IETA',
                    'password' => Hash::make('Admin*2026'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
            $admin->assignRole($adminRole);
        }

        // Limpiar caché de nuevo tras finalizar
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
