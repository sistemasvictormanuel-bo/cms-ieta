<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Este seeder debe ejecutarse al final de DatabaseSeeder para que User::count()
     * solo sea > 0 cuando la base de datos se haya poblado completamente con éxito.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $adminRole = Role::firstOrCreate(['name' => 'administrador', 'guard_name' => 'web']);

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

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
