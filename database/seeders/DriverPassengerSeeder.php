<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Driver;
use Faker\Factory as Faker;

class DriverPassengerSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // Seed demo driver and passenger for easy testing
        $demoDriverUser = \App\Models\User::firstWhere('email', 'driver@example.com') ?? \App\Models\User::factory()->create([
            'name' => 'Demo Driver',
            'email' => 'driver@example.com',
        ]);
        $demoDriverUser->assignRole('driver');
        \App\Models\Driver::firstOrCreate([
            'user_id' => $demoDriverUser->id,
        ], [
            'vehicle_name' => 'Toyota Corolla',
            'plate_number' => 'ABC-1234',
            'status' => 'approved',
            'is_available' => true,
        ]);

        $demoPassengerUser = \App\Models\User::firstWhere('email', 'user@example.com') ?? \App\Models\User::factory()->create([
            'name' => 'Demo User',
            'email' => 'user@example.com',
        ]);
        $demoPassengerUser->assignRole('user');

        // Seed drivers (users with driver role + driver records)
        $vehicleOptions = [
            'Toyota Corolla', 'Honda Civic', 'Nissan Altima', 'Ford Focus', 'Hyundai Elantra',
            'Kia Rio', 'Volkswagen Golf', 'Toyota Camry', 'Honda Accord', 'Mazda 3',
        ];

        $driverCount = (int) env('SEED_DRIVER_COUNT', 10);
        for ($i = 0; $i < $driverCount; $i++) {
            $user = User::factory()->create();
            $user->assignRole('driver');

            Driver::create([
                'user_id' => $user->id,
                'vehicle_name' => $faker->randomElement($vehicleOptions),
                'plate_number' => strtoupper($faker->bothify('???-####')),
                'status' => $faker->boolean(80) ? 'approved' : 'pending',
                'is_available' => $faker->boolean(70),
            ]);
        }

        // Seed passengers (regular users with user role)
        $passengerCount = (int) env('SEED_PASSENGER_COUNT', 20);
        for ($i = 0; $i < $passengerCount; $i++) {
            $user = User::factory()->create();
            $user->assignRole('user');
        }
    }
}