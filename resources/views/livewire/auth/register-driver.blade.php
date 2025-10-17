<?php

use App\Models\User;
use App\Models\Driver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rules;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming driver registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            // Enforce firstname.lastname@bouesti.edu.ng format
            'email' => [
                'required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class,
                'regex:/^[a-z]+\.[a-z]+@bouesti\.edu\.ng$/'
            ],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        // Generate random 3D avatar URL (DiceBear bottts-neutral)
        $seed = (string) Str::uuid();
        $validated['avatar_url'] = "https://api.dicebear.com/7.x/bottts-neutral/svg?seed={$seed}&backgroundType=gradientLinear&radius=50";

        event(new Registered(($user = User::create($validated))));

        // Assign role: driver
        try {
            $user->assignRole('driver');
        } catch (\Throwable $e) {
            // Ignore role assignment failure in early setup
        }

        // Create a basic driver profile linked to this user
        try {
            Driver::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'vehicle_name' => 'TBD',
                    'plate_number' => 'TBD',

                    'status' => 'pending',
                    'is_available' => true,
                ],
            );
        } catch (\Throwable $e) {
            // Ignore if driver table is not ready yet
        }

        Auth::login($user);
        Session::regenerate();

        // Redirect to driver dashboard
        $this->redirectIntended(route('driver.dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Register as Driver')" :description="__('Enter your details to create a driver account')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form method="POST" wire:submit="register" class="flex flex-col gap-6">
        <!-- Name -->
        <flux:input
            wire:model="name"
            :label="__('Name')"
            type="text"
            required
            autofocus
            autocomplete="name"
            :placeholder="__('Full name')"
        />

        <!-- Email Address -->
        <flux:input
            wire:model="email"
            :label="__('Driver Email (firstname.lastname@bouesti.edu.ng)')"
            type="email"
            required
            autocomplete="email"
            placeholder="firstname.lastname@bouesti.edu.ng"
        />

        <!-- Password -->
        <flux:input
            wire:model="password"
            :label="__('Password')"
            type="password"
            required
            autocomplete="new-password"
            :placeholder="__('Password')"
            viewable
        />

        <!-- Confirm Password -->
        <flux:input
            wire:model="password_confirmation"
            :label="__('Confirm password')"
            type="password"
            required
            autocomplete="new-password"
            :placeholder="__('Confirm password')"
            viewable
        />

        <div class="flex items-center justify-end">
            <flux:button type="submit" variant="primary" class="w-full">
                {{ __('Create driver account') }}
            </flux:button>
        </div>
    </form>

    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
        <span>{{ __('Already have an account?') }}</span>
        <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
    </div>
</div>