<?php

use App\Models\Driver;
use App\Models\Ride;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public array $drivers = [];
    public int $completedRides = 0;

    public function mount(): void
    {
        $this->drivers = Driver::where('status', 'approved')->orderBy('charge_rate')->get()->toArray();
        $this->completedRides = Ride::where('user_id', Auth::id())->where('status', 'completed')->count();
    }
}; ?>

<div class="p-6 space-y-6">
    <flux:card class="p-4">
        <div class="font-medium">Approved Drivers</div>
        <div class="mt-3 grid gap-3 md:grid-cols-2">
            @forelse ($drivers as $d)
                <div class="border rounded p-3">
                    <div class="font-semibold">{{ $d['vehicle_name'] }} ({{ $d['plate_number'] }})</div>
                    <div class="text-sm">Rate: ₦{{ number_format($d['charge_rate'], 2) }}</div>
                </div>
            @empty
                <div>No approved drivers yet.</div>
            @endforelse
        </div>
    </flux:card>

    <flux:card class="p-4">
        <div class="font-medium">Your Completed Rides</div>
        <div class="text-2xl font-bold">{{ $completedRides }}</div>
    </flux:card>
</div>