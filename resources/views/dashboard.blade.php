<div class="p-6 space-y-4">
    <h1 class="text-2xl font-bold">BTransit Dashboard</h1>
    <p>Welcome to BTransit. Use the role dashboards below:</p>
    <div class="flex gap-3">
        <a href="{{ route('admin.dashboard') }}" class="underline text-blue-600">Superadmin</a>
        <a href="{{ route('driver.dashboard') }}" class="underline text-blue-600">Driver</a>
        <a href="{{ route('user.dashboard') }}" class="underline text-blue-600">User</a>
    </div>
</div>
