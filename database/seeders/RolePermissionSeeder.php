<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Posts
            'posts.viewAny', 'posts.view', 'posts.create',
            'posts.update.own', 'posts.update.any',
            'posts.delete.own', 'posts.delete.any',
            'posts.publish',

            // Comments
            'comments.moderate', 'comments.delete',

            // Content (destinations, locations, categories, tags)
            'content.manage',

            // Media
            'media.upload', 'media.delete',

            // Routes (NIEUW)
            'routes.manage',

            // Newsletter
            'newsletters.manage', 'subscribers.manage',

            // Pages (NIEUW)
            'pages.manage',

            // Family members (NIEUW)
            'family.manage',

            // Users & roles
            'users.manage', 'roles.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $editor = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $author = Role::firstOrCreate(['name' => 'auteur', 'guard_name' => 'web']);
        $member = Role::firstOrCreate(['name' => 'lid', 'guard_name' => 'web']);

        $admin->syncPermissions(Permission::all());

        $editor->syncPermissions([
            'posts.viewAny', 'posts.view', 'posts.create',
            'posts.update.own', 'posts.update.any',
            'posts.delete.own', 'posts.delete.any',
            'posts.publish',
            'comments.moderate', 'comments.delete',
            'content.manage',
            'media.upload', 'media.delete',
            'routes.manage',
            'newsletters.manage', 'subscribers.manage',
            'pages.manage',
        ]);

        $author->syncPermissions([
            'posts.viewAny', 'posts.view', 'posts.create',
            'posts.update.own', 'posts.delete.own',
            'media.upload',
            'routes.manage',
        ]);

        // 'lid' krijgt geen admin-permissies — reageren wordt via auth-middleware geregeld

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
