@php
    // Redirect users to their appropriate dashboard based on role
    $user = auth()->user();
    
    if ($user->hasRole('superadmin')) {
        $redirectRoute = route('admin.dashboard');
    } elseif ($user->hasRole('driver')) {
        $redirectRoute = route('driver.dashboard');
    } elseif ($user->hasRole('user')) {
        $redirectRoute = route('user.dashboard');
    } else {
        // Fallback for users without roles - assign default user role
        $user->assignRole('user');
        $redirectRoute = route('user.dashboard');
    }
@endphp

<script>
    window.location.href = '{{ $redirectRoute }}';
</script>

<div class="p-6 space-y-6">
    <div class="flex items-center justify-center min-h-[200px]">
        <div class="text-center">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-4"></div>
            <h1 class="tw-heading mb-2">Redirecting...</h1>
            <p class="tw-body text-gray-600">Taking you to your dashboard...</p>
        </div>
    </div>
</div>
