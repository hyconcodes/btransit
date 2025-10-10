<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        foreach (['superadmin', 'driver', 'user'] as $roleName) {
            Role::findOrCreate($roleName);
        }

        // Create a default superadmin
        $email = env('SEED_SUPERADMIN_EMAIL', 'admin@example.com');
        $password = env('SEED_SUPERADMIN_PASSWORD', 'password');

        $user = User::firstOrCreate(
            ['email' => $email],
            ['name' => 'Super Admin', 'password' => bcrypt($password)]
        );

        $user->assignRole('superadmin');
    }
}