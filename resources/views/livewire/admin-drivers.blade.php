<?php

use App\Models\Driver;
use Livewire\Volt\Component;

new class extends Component {
    public array $drivers = [];

    public function mount(): void
    {
        $this->refreshDrivers();
    }

    public function refreshDrivers(): void
    {
        $this->drivers = Driver::with('user')->orderByDesc('created_at')->get()->toArray();
    }

    public function toggleApproval(int $id): void
    {
        $driver = Driver::findOrFail($id);
        $driver->status = $driver->status === 'approved' ? 'pending' : 'approved';
        $driver->save();
        $this->refreshDrivers();
    }

    public function toggleAvailability(int $id): void
    {
        $driver = Driver::findOrFail($id);
        $driver->is_available = ! (bool) $driver->is_available;
        $driver->save();
        $this->refreshDrivers();
    }
}; ?>

<div class="p-6 space-y-6">
    <h2 class="text-xl font-semibold">Driver Management</h2>

    <div class="grid gap-3">
        @forelse ($drivers as $d)
            <div class="flex items-center justify-between border rounded p-3">
                <div>
                    <div class="font-medium">{{ $d['user']['name'] ?? 'Unknown' }}</div>
                    <div class="text-sm">Vehicle: {{ $d['vehicle_name'] }} | Plate: {{ $d['plate_number'] }}</div>
                    <div class="text-sm">Charge Rate: ₦{{ number_format($d['charge_rate'], 2) }}</div>
                    <div class="text-sm">Status: <span class="font-semibold">{{ $d['status'] }}</span></div>
                    <div class="text-sm">Available: <span class="font-semibold">{{ ($d['is_available'] ?? false) ? 'Yes' : 'No' }}</span></div>
                </div>
                <flux:button wire:click="toggleApproval({{ $d['id'] }})" variant="primary">
                    {{ $d['status'] === 'approved' ? 'Set Pending' : 'Approve' }}
                </flux:button>
                <flux:button wire:click="toggleAvailability({{ $d['id'] }})" variant="secondary">
                    {{ ($d['is_available'] ?? false) ? 'Set Unavailable' : 'Set Available' }}
                </flux:button>
            </div>
        @empty
            <div>No drivers yet.</div>
        @endforelse
    </div>
</div>