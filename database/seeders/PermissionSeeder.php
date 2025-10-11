<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Clear cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define action permissions
        $permissions = [
            // User actions
            'can.book.ride',
            'can.view.own.rides',
            'can.cancel.ride',
            'can.pay.online',
            'can.pay.cash',
            'can.rate.driver',
            'can.view.available.drivers',

            // Driver actions
            'can.toggle.availability',
            'can.accept.ride',
            'can.complete.ride',
            'can.confirm.cash',
            'can.view.assigned.rides',

            // Admin actions
            'can.view.rides',
            'can.view.payments',
            'can.manage.drivers',
            'can.manage.users',
            'can.view.admin.dashboard',

            // Superadmin actions
            'can.manage.roles',
            'can.manage.permissions',
            'can.manage.payments',
            'can.view.superadmin.dashboard',
        ];

        // Create permissions (guard web)
        foreach ($permissions as $name) {
            Permission::findOrCreate($name, 'web');
        }

        // Assign permissions to roles
        $userRole = Role::findOrCreate('user');
        $driverRole = Role::findOrCreate('driver');
        $adminRole = Role::findOrCreate('superadmin'); // ensure exists before mapping
        $superadminRole = Role::findOrCreate('superadmin');

        // Map role permissions
        $userPermissions = [
            'can.book.ride',
            'can.view.own.rides',
            'can.cancel.ride',
            'can.pay.online',
            'can.pay.cash',
            'can.rate.driver',
            'can.view.available.drivers',
        ];

        $driverPermissions = [
            'can.toggle.availability',
            'can.accept.ride',
            'can.complete.ride',
            'can.confirm.cash',
            'can.view.assigned.rides',
        ];

        $adminPermissions = [
            'can.view.rides',
            'can.view.payments',
            'can.manage.drivers',
            'can.manage.users',
            'can.view.admin.dashboard',
        ];

        // Assign
        $userRole->syncPermissions($userPermissions);
        $driverRole->syncPermissions($driverPermissions);
        // If you later add a distinct 'admin' role, swap to that here.
        // For now, keep superadmin with all permissions and admin-permissions are included below for clarity.

        $superadminRole->syncPermissions(Permission::all());
    }
}