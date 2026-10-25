<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'users.view',
            'users.manage',
            'roles.manage',
            'catalog.view',
            'catalog.manage',
            'anime.manage',
            'manga.manage',
            'music.manage',
            'games.manage',
            'content.create',
            'content.publish',
            'content.delete',
            'reports.view',
            'reports.manage',
            'moderation.manage',
            'settings.manage',
            'finances.view',
            // 🔍 Historial, ayuda y personajes (solo superadmin/admin)
            'history.view',
            'help.manage',
            'characters.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $roles = [
            // Superadmin: todos los permisos (usuarios creados antes del sistema de roles)
            'superadmin' => $permissions,
            'admin' => $permissions,
            'editor' => [
                'catalog.view',
                'catalog.manage',
                'anime.manage',
                'manga.manage',
                'music.manage',
                'games.manage',
                'content.create',
                'content.publish',
            ],
            'moderator' => [
                'catalog.view',
                'content.delete',
                'reports.view',
                'reports.manage',
                'moderation.manage',
            ],
            'support' => [
                'users.view',
                'reports.view',
            ],
            'creator' => [
                'catalog.view',
                'content.create',
                'content.publish',
            ],
            'user' => [
                'catalog.view',
            ],
        ];

        foreach ($roles as $name => $rolePermissions) {
            Role::findOrCreate($name, 'web')->syncPermissions($rolePermissions);
        }
    }
}
