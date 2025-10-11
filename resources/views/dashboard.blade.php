<div class="p-6 space-y-6">
    <h1 class="tw-heading">BTransit Dashboard</h1>
    <p class="tw-body">Welcome to BTransit. Choose a role dashboard:</p>
    <div class="card flex gap-3">
        <a href="{{ route('admin.dashboard') }}" class="btn-primary">Superadmin</a>
        <a href="{{ route('driver.dashboard') }}" class="btn-primary">Driver</a>
        <a href="{{ route('user.dashboard') }}" class="btn-primary">User</a>
    </div>
</div>
