<?php

use App\Models\User;
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
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            // Enforce firstname.matric_no@bouesti.edu.ng format
            'email' => [
                'required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class,
                'regex:/^[a-z]+\.[A-Za-z0-9]+@bouesti\.edu\.ng$/'
            ],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        // Extract matric_no from email local part (after the dot)
        // Example: firstname.matric_no@bouesti.edu.ng -> matric_no
        [$local] = explode('@', $validated['email']);
        $parts = explode('.', $local);
        $validated['matric_no'] = $parts[1] ?? null;

        // Generate random 3D avatar URL (DiceBear bottts-neutral)
        $seed = (string) Str::uuid();
        $validated['avatar_url'] = "https://api.dicebear.com/7.x/bottts-neutral/svg?seed={$seed}&backgroundType=gradientLinear&radius=50";

        event(new Registered(($user = User::create($validated))));

        Auth::login($user);

        // Assign role: user
        try {
            $user->assignRole('user');
        } catch (\Throwable $e) {
            // Role may not exist yet during setup; ignore to prevent registration failure
        }

        Session::regenerate();

        // Redirect to user dashboard
        $this->redirectIntended(route('user.dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Create an account')" :description="__('Enter your details below to create your account')" />

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
            :label="__('Email address')"
            type="email"
            required
            autocomplete="email"
            placeholder="email@example.com"
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
            <flux:button type="submit" variant="primary" class="w-full" data-test="register-user-button">
                {{ __('Create account') }}
            </flux:button>
        </div>
    </form>

    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
        <span>{{ __('Already have an account?') }}</span>
        <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
    </div>
</div>
