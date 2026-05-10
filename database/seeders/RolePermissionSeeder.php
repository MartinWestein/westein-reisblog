<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Seed roles and permissions.
     *
     * Idempotent: kan veilig meerdere keren gedraaid worden.
     */
    public function run(): void
    {
        // Cache leeg na seeding (Spatie cached de permissies in geheugen)
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Permissies aanmaken
        $permissions = [
            // Posts
            'posts.viewAny',
            'posts.view',
            'posts.create',
            'posts.update.own',
            'posts.update.any',
            'posts.delete.own',
            'posts.delete.any',
            'posts.publish',

            // Reacties
            'comments.moderate',
            'comments.delete',

            // Content (Destinations, Locations, Categories, Tags, Routes, Pages, FamilyMembers)
            'content.manage',

            // Media
            'media.upload',
            'media.delete',

            // Newsletter & subscribers
            'newsletters.manage',
            'subscribers.manage',

            // Gebruikers & rollen
            'users.manage',
            'roles.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // 2. Rollen aanmaken
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $editor = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $author = Role::firstOrCreate(['name' => 'auteur', 'guard_name' => 'web']);
        $member = Role::firstOrCreate(['name' => 'lid', 'guard_name' => 'web']);

        // 3. Permissies aan rollen koppelen

        // Admin: krijgt alles via Gate::before in AppServiceProvider.
        // Voor de zekerheid syncen we hier ook expliciet — mocht Gate::before
        // ooit verdwijnen, blijft admin volledig functioneel.
        $admin->syncPermissions(Permission::all());

        // Editor
        $editor->syncPermissions([
            'posts.viewAny',
            'posts.view',
            'posts.create',
            'posts.update.own',
            'posts.update.any',
            'posts.delete.own',
            'posts.delete.any',
            'posts.publish',
            'comments.moderate',
            'comments.delete',
            'content.manage',
            'media.upload',
            'media.delete',
            'newsletters.manage',
            'subscribers.manage',
        ]);

        // Auteur
        $author->syncPermissions([
            'posts.viewAny',
            'posts.view',
            'posts.create',
            'posts.update.own',
            'posts.delete.own',
            'media.upload',
        ]);

        // Lid: geen admin-permissies (mag alleen reageren — afgeschermd via
        // `auth`-middleware op de comment-route, niet via Spatie Permission).

        // Cache opnieuw leeg na sync
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
