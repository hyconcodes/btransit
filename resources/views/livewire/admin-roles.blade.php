<?php

use Livewire\Volt\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

new class extends Component {
    public array $roles = [];
    public array $permissions = [];
    public array $rolePermissions = [];
    public string $roleName = '';
    public string $permissionName = '';
    public ?string $status = null;
    public bool $confirmingDelete = false;
    public ?int $deleteRoleId = null;
    public ?string $deleteRoleName = null;

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $this->roles = Role::orderBy('name')->get()->map(fn ($r) => ['id' => $r->id, 'name' => $r->name])->toArray();
        $this->permissions = Permission::orderBy('name')->pluck('name')->toArray();
        $this->rolePermissions = [];
        foreach (Role::orderBy('name')->get() as $role) {
            $this->rolePermissions[$role->id] = $role->permissions->pluck('name')->toArray();
        }
    }

    public function createRole(): void
    {
        try {
            $this->validate(['roleName' => ['required', 'string', 'max:50']]);
        } catch (ValidationException $e) {
            foreach ($e->validator->errors()->all() as $msg) {
                $this->dispatch('toast', type: 'error', message: $msg);
            }
            return;
        }
        Role::findOrCreate($this->roleName);
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $this->status = 'Role created successfully.';
        $this->dispatch('toast', type: 'success', message: 'Role created successfully.');
        $this->roleName = '';
        $this->loadData();
    }

    public function createPermission(): void
    {
        try {
            $this->validate(['permissionName' => ['required', 'string', 'max:100']]);
        } catch (ValidationException $e) {
            foreach ($e->validator->errors()->all() as $msg) {
                $this->dispatch('toast', type: 'error', message: $msg);
            }
            return;
        }
        Permission::findOrCreate($this->permissionName, 'web');
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $this->status = 'Permission created successfully.';
        $this->dispatch('toast', type: 'success', message: 'Permission created successfully.');
        $this->permissionName = '';
        $this->loadData();
    }

    public function sync(int $roleId): void
    {
        try {
            $role = Role::findOrFail($roleId);
        } catch (ModelNotFoundException $e) {
            $this->dispatch('toast', type: 'error', message: 'Role not found.');
            return;
        }
        $names = $this->rolePermissions[$roleId] ?? [];
        $role->syncPermissions($names);
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $this->status = 'Role permissions updated.';
        $this->dispatch('toast', type: 'success', message: 'Role permissions updated.');
        $this->loadData();
    }

    public function deleteRole(int $roleId): void
    {
        try {
            $role = Role::findOrFail($roleId);
        } catch (ModelNotFoundException $e) {
            $this->dispatch('toast', type: 'error', message: 'Role not found.');
            return;
        }
        if (strtolower($role->name) === 'superadmin') {
            $this->status = 'Cannot delete the superadmin role.';
            $this->dispatch('toast', type: 'error', message: 'Cannot delete the superadmin role.');
            return;
        }

        $role->delete();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $this->status = 'Role deleted.';
        $this->dispatch('toast', type: 'success', message: 'Role deleted.');
        $this->loadData();
    }

    public function promptDelete(int $roleId): void
    {
        try {
            $role = Role::findOrFail($roleId);
        } catch (ModelNotFoundException $e) {
            $this->dispatch('toast', type: 'error', message: 'Role not found.');
            return;
        }
        if (strtolower($role->name) === 'superadmin') {
            $this->status = 'Cannot delete the superadmin role.';
            $this->dispatch('toast', type: 'error', message: 'Cannot delete the superadmin role.');
            return;
        }
        $this->deleteRoleId = $roleId;
        $this->deleteRoleName = $role->name;
        $this->confirmingDelete = true;
        $this->dispatch('toast', type: 'info', message: 'Confirm deletion for role: ' . $role->name);
    }

    public function cancelDelete(): void
    {
        $this->confirmingDelete = false;
        $this->deleteRoleId = null;
        $this->deleteRoleName = null;
        $this->dispatch('toast', type: 'info', message: 'Deletion cancelled.');
    }

    public function confirmDelete(): void
    {
        if ($this->deleteRoleId) {
            $this->deleteRole($this->deleteRoleId);
        }
        $this->cancelDelete();
    }
}; ?>

<div class="p-6 space-y-6 bg-[var(--bg-light)] dark:bg-[var(--bg-dark)]">
    <h1 class="tw-heading text-[var(--neutral-text)] dark:text-white">Roles & Permissions</h1>

    @if($status)
        <div class="mt-4 rounded-lg p-3 text-sm ring-1"
             style="background-color:#ecfdf5;color:var(--success);border-color:#d1fae5"
             class="dark:bg-zinc-800 dark:text-green-400 dark:ring-zinc-700">
            {{ $status }}
        </div>
    @endif

    <div class="mt-8 grid gap-6 md:grid-cols-2">
        <div class="card dark:bg-zinc-900 dark:border dark:border-zinc-700 dark:text-white">
            <h2 class="text-lg font-semibold">Create New Role</h2>
            <form wire:submit.prevent="createRole" class="mt-4 space-y-3">
                <label class="block text-sm text-[var(--neutral-text)] dark:text-zinc-200">Role Name</label>
                <input type="text" wire:model.defer="roleName" required class="w-full rounded-lg border border-zinc-300 px-3 py-2 focus:ring-2 focus:ring-[var(--primary-green)] focus:border-[var(--primary-green)] dark:bg-zinc-800 dark:border-zinc-700 dark:text-white" placeholder="e.g. moderator" />
                <flux:button type="submit" variant="primary" class="btn-primary">Create Role</flux:button>
            </form>
        </div>

        <div class="card dark:bg-zinc-900 dark:border dark:border-zinc-700 dark:text-white">
            <h2 class="text-lg font-semibold">Create New Permission</h2>
            <form wire:submit.prevent="createPermission" class="mt-4 space-y-3">
                <label class="block text-sm text-[var(--neutral-text)] dark:text-zinc-200">Permission Name</label>
                <input type="text" wire:model.defer="permissionName" required class="w-full rounded-lg border border-zinc-300 px-3 py-2 focus:ring-2 focus:ring-[var(--primary-green)] focus:border-[var(--primary-green)] dark:bg-zinc-800 dark:border-zinc-700 dark:text-white" placeholder="e.g. can.export.reports" />
                <flux:button type="submit" variant="primary" class="btn-accent">Create Permission</flux:button>
            </form>
        </div>
    </div>

    <div class="mt-10">
        <h2 class="text-lg font-semibold text-[var(--neutral-text)] dark:text-white">Assign Permissions to Roles</h2>
        <div class="mt-4 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach($roles as $role)
                <div class="card dark:bg-zinc-900 dark:border dark:border-zinc-700 dark:text-white">
                    <div class="flex items-center justify-between">
                        <div class="font-medium text-[var(--neutral-text)] dark:text-white">{{ $role['name'] }}</div>
                        @if (strtolower($role['name']) !== 'superadmin')
                            <flux:button wire:click="promptDelete({{ $role['id'] }})" variant="ghost" class="btn-danger">Delete</flux:button>
                        @endif
                    </div>
                    <form wire:submit.prevent="sync({{ $role['id'] }})" class="mt-4">
                        <div class="grid grid-cols-1 gap-2">
                            @foreach($permissions as $perm)
                                <label class="flex items-center gap-2 text-sm text-[var(--neutral-text)] dark:text-zinc-200">
                                    <input type="checkbox" wire:model.defer="rolePermissions.{{ $role['id'] }}" value="{{ $perm }}" class="rounded border-zinc-300 text-[var(--primary-green)] focus:ring-[var(--primary-green)] dark:bg-zinc-800 dark:border-zinc-700" />
                                    <span>{{ $perm }}</span>
                                </label>
                            @endforeach
                        </div>
                        <div class="mt-4 flex justify-end">
                            <flux:button type="submit" variant="primary" class="btn-primary">Save Changes</flux:button>
                        </div>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
    @if($confirmingDelete)
        <div class="fixed inset-0 z-50 flex items-center justify-center" x-data>
            <div class="absolute inset-0 bg-black/40 dark:bg-black/60"></div>
            <div class="relative card max-w-md w-full dark:bg-zinc-900 dark:border dark:border-zinc-700 dark:text-white">
                <h3 class="text-lg font-semibold text-[var(--neutral-text)] dark:text-white">Delete Role</h3>
                <p class="tw-body mt-2 dark:text-zinc-200">Are you sure you want to delete the role "{{ $deleteRoleName }}"? This action cannot be undone.</p>
                <div class="mt-6 flex justify-end gap-3">
                    <flux:button wire:click="cancelDelete" variant="ghost" class="btn-outline-primary">Cancel</flux:button>
                    <flux:button wire:click="confirmDelete" variant="primary" class="btn-danger">Delete</flux:button>
                </div>
            </div>
        </div>
    @endif
</div>
