<?php

namespace Database\Seeders;

use App\Models\NavigationMenu;
use Illuminate\Database\Seeder;

class NavigationMenuSeeder extends Seeder
{
    public function run(): void
    {
        $menus = [
            [
                'id'         => 1,
                'parent_id'  => null,
                'label'      => 'Dashboard',
                'icon'       => 'home',
                'route'      => '/dashboard',
                'permission' => null,
                'sort_order' => 1,
            ],
            [
                'id'         => 2,
                'parent_id'  => null,
                'label'      => 'Settings',
                'icon'       => 'settings',
                'route'      => null,
                'permission' => null,
                'sort_order' => 2,
            ],
            [
                'id'         => 3,
                'parent_id'  => 2,
                'label'      => 'Users',
                'icon'       => 'users',
                'route'      => '/users',
                'permission' => 'user_access.read',
                'sort_order' => 1,
            ],
            [
                'id'         => 4,
                'parent_id'  => 2,
                'label'      => 'Roles',
                'icon'       => 'shield',
                'route'      => '/roles',
                'permission' => 'role_access.read',
                'sort_order' => 2,
            ],
            [
                'id'         => 5,
                'parent_id'  => 2,
                'label'      => 'Permissions',
                'icon'       => 'key',
                'route'      => '/permissions',
                'permission' => 'permission_access.read',
                'sort_order' => 3,
            ],
            [
                'id'         => 6,
                'parent_id'  => 2,
                'label'      => 'Password Policy',
                'icon'       => 'lock-closed',
                'route'      => '/settings/password-policy',
                'permission' => 'user_access.update',
                'sort_order' => 4,
            ],
            [
                'id'         => 7,
                'parent_id'  => 2,
                'label'      => 'Menu Manager',
                'icon'       => 'bars-3',
                'route'      => '/settings/navigation',
                'permission' => 'manage_settings',
                'sort_order' => 5,
            ],
        ];

        foreach ($menus as $menu) {
            NavigationMenu::updateOrCreate(
                ['id' => $menu['id']],
                $menu
            );
        }
    }
}
