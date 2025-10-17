<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ride;
use App\Models\User;
use App\Models\Driver;
use Illuminate\Support\Carbon;
use Faker\Factory as Faker;

class RideSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // Ensure we have drivers and passengers
        $drivers = Driver::where('status', 'approved')->get();
        if ($drivers->isEmpty()) {
            $drivers = Driver::all();
        }

        $passengers = User::role('user')->get();
        if ($passengers->isEmpty()) {
            // Create at least one passenger if none exist
            $u = User::factory()->create(['name' => 'Seeded Passenger', 'email' => 'seeded.passenger@example.com']);
            $u->assignRole('user');
            $passengers = collect([$u]);
        }

        $pickupOptions = [
            'Campus Gate', 'Main Library', 'Science Complex', 'Student Union', 'Sports Center',
            'Hostel A', 'Hostel B', 'Admin Block', 'ICT Hub', 'Clinic'
        ];
        $destinationOptions = [
            'New Market', 'North Gate', 'South Gate', 'Faculty of Arts', 'Faculty of Engineering',
            'Bus Park', 'Lecture Theatre', 'Chapel', 'Mosque', 'Community Center'
        ];

        $statuses = ['pending', 'accepted', 'in_progress', 'completed', 'cancelled'];
        $paymentMethods = ['paystack', 'cash'];

        // Create 20 varied rides
        for ($i = 0; $i < 20; $i++) {
            $user = $passengers->random();
            $driver = $drivers->random();

            $status = $faker->randomElement($statuses);
            // Schedule future for pending/accepted, past for in_progress/completed/cancelled
            if (in_array($status, ['pending', 'accepted'])) {
                $scheduledAt = Carbon::now()->addDays($faker->numberBetween(0, 7))->setHour($faker->numberBetween(6, 20))->setMinute(0);
            } else {
                $scheduledAt = Carbon::now()->subDays($faker->numberBetween(0, 7))->setHour($faker->numberBetween(6, 20))->setMinute(0);
            }

            $fare = $faker->numberBetween(800, 3500);
            $paymentMethod = $faker->randomElement($paymentMethods);

            // Payment status logic
            if ($status === 'completed') {
                $paymentStatus = $faker->boolean(70) ? 'paid' : 'pending';
            } elseif ($status === 'cancelled') {
                $paymentStatus = $faker->boolean(30) ? 'failed' : 'pending';
            } else {
                $paymentStatus = 'pending';
            }

            Ride::create([
                'user_id' => $user->id,
                'driver_id' => $driver->id,
                'pickup' => $faker->randomElement($pickupOptions),
                'destination' => $faker->randomElement($destinationOptions),
                'scheduled_at' => $scheduledAt,
                'fare' => $fare,
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'status' => $status,
            ]);
        }

        // Seed a few specific unpaid rides to showcase the dashboard section
        $firstPassenger = $passengers->first();
        if ($firstPassenger && $drivers->isNotEmpty()) {
            for ($j = 0; $j < 3; $j++) {
                $driver = $drivers->random();
                Ride::create([
                    'user_id' => $firstPassenger->id,
                    'driver_id' => $driver->id,
                    'pickup' => $faker->randomElement($pickupOptions),
                    'destination' => $faker->randomElement($destinationOptions),
                    'scheduled_at' => Carbon::now()->addDays($faker->numberBetween(1, 5))->setHour(10)->setMinute(0),
                    'fare' => $faker->numberBetween(1200, 2800),
                    'payment_method' => $faker->randomElement($paymentMethods),
                    'payment_status' => 'pending',
                    'status' => $faker->randomElement(['accepted', 'in_progress', 'completed']),
                ]);
            }
        }
    }
}