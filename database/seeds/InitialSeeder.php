<?php

use Illuminate\Database\Seeder;
use App\User;
use App\Models\Role;
use App\Models\Permission;

class InitialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // create initial user account
        $users = [
            [
                'name' => 'Developer',
                'email' => 'developer@developer.com',
                'password' => bcrypt('p@ssw0rd'),
            ],
            [
                'name' => 'Super Administrator',
                'email' => 'superadmin@admin.com',
                'password' => bcrypt('superadmin'),
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }

        // create initial roles
        $roles = [
            [
                'name' => 'developer',
                'display_name' => 'Developer',
                'description' => 'Developer account role',
            ],
            [
                'name' => 'super-admin',
                'display_name' => 'Super Administrator',
                'description' => 'Super administrator account role',
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }

        // create initial permissions
        $permissions = [
            [
                'name' => 'users-list',
                'display_name' => 'Users List',
                'description' => 'Ability to display all users',
            ],
            [
                'name' => 'manage-users',
                'display_name' => 'Manage Users',
                'description' => 'Ability to create and modify user',
            ],
            [
                'name' => 'roles-list',
                'display_name' => 'Roles List',
                'description' => 'Ability to display all available roles',
            ],
            [
                'name' => 'manage-roles',
                'display_name' => 'Manage Roles',
                'description' => 'Ability to create and modify roles',
            ],
            [
                'name' => 'permissions-list',
                'display_name' => 'Permissions List',
                'description' => 'Ability to display all available permissions',
            ],
            [
                'name' => 'manage-permissions',
                'display_name' => 'Manage Permissions',
                'description' => 'Ability to create and modify permissions',
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
        
        // assign all permission to developer role
        $developer = Role::where('name','developer')->first();
        foreach(Permission::all() as $perm){
            $developer->attachPermission($perm);
        }

        // assign developer role to developer user
        User::where('name','Developer')->first()->attachRole($developer);

        // assign permission to super-admin role
        $super = Role::where('name','super-admin')->first();
        foreach(Permission::all() as $perm){
            // don't assign if manages-permissions
            if($perm->name !== 'manage-permissions')
                $super->attachPermission($perm);
        }

        // assign super administrator user to super-admin role
        User::where('name','Super Administrator')->first()->attachRole($super);
    }
}
