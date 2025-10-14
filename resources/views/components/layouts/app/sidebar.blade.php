<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

        @php
            $dashboardRoute = 'dashboard';
            if (auth()->check()) {
                if (auth()->user()->hasRole('superadmin')) {
                    $dashboardRoute = 'admin.dashboard';
                } elseif (auth()->user()->hasRole('driver')) {
                    $dashboardRoute = 'driver.dashboard';
                } elseif (auth()->user()->hasRole('user')) {
                    $dashboardRoute = 'user.dashboard';
                }
            }
        @endphp

        <a href="{{ route($dashboardRoute) }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
            <x-app-logo />
        </a>

        <!-- Sidebar Avatar Card -->
        @auth
            <div class="mt-3 mb-4 flex items-center gap-3 rounded-lg border border-secondary bg-subtle-light p-3 dark:bg-subtle-dark">
                <img src="{{ auth()->user()->avatarUrl() }}" alt="Avatar" class="h-10 w-10 rounded-full border border-secondary" />
                <div class="min-w-0">
                    <div class="truncate text-sm font-semibold text-primary">{{ auth()->user()->name }}</div>
                    <div class="truncate text-xs text-zinc-600 dark:text-zinc-400">{{ auth()->user()->email }}</div>
                </div>
            </div>
        @endauth

        <flux:navlist variant="outline">
            <flux:navlist.group :heading="__('Platform')" class="grid">
                <flux:navlist.item icon="home" :href="route($dashboardRoute)" :current="request()->routeIs($dashboardRoute)"
                    wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
                @role('superadmin')
                    <flux:navlist.item icon="users" :href="route('admin.drivers')"
                        :current="request()->routeIs('admin.drivers')" wire:navigate>
                        {{ __('Driver Management') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="key" :href="route('admin.roles')"
                        :current="request()->routeIs('admin.roles')" wire:navigate>
                        {{ __('Roles & Permissions') }}
                    </flux:navlist.item>
                @endrole
                @role('driver')
                    <flux:navlist.item icon="truck" :href="route('driver.rides')"
                        :current="request()->routeIs('driver.rides')" wire:navigate>
                        {{ __('My Rides') }}
                    </flux:navlist.item>
                @endrole
                @role('user')
                    <flux:navlist.item icon="plus" :href="route('user.rides.book')"
                        :current="request()->routeIs('user.rides.book')" wire:navigate>
                        {{ __('Book a Ride') }}
                    </flux:navlist.item>
                @endrole
            </flux:navlist.group>
        </flux:navlist>

        <flux:spacer />

        {{-- <flux:navlist variant="outline">
            <flux:navlist.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit"
                target="_blank">
                {{ __('Repository') }}
            </flux:navlist.item>

            <flux:navlist.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire"
                target="_blank">
                {{ __('Documentation') }}
            </flux:navlist.item>
        </flux:navlist> --}}

        <!-- Desktop User Menu -->
        <flux:dropdown class="hidden lg:block" position="bottom" align="start">
            <flux:profile :name="auth()->user()->name" :initials="auth()->user()->initials()"
                icon:trailing="chevrons-up-down" data-test="sidebar-menu-button" />

            <flux:menu class="w-[220px]">
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <img src="{{ auth()->user()->avatarUrl() }}" alt="Avatar" class="h-8 w-8 rounded-lg border border-secondary" />
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
                    <flux:radio value="light" icon="sun">{{ __('Light') }}</flux:radio>
                    <flux:radio value="dark" icon="moon">{{ __('Dark') }}</flux:radio>
                </flux:radio.group>

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full"
                        data-test="logout-button">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:sidebar>

    <!-- Mobile User Menu -->
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        <flux:dropdown position="top" align="end">
            <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <img src="{{ auth()->user()->avatarUrl() }}" alt="Avatar" class="h-8 w-8 rounded-lg border border-secondary" />
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}
                    </flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full"
                        data-test="logout-button">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{ $slot }}

    @include('partials.toast')

    @fluxScripts
</body>

</html>
