<?php

use App\Models\Driver;
use App\Models\Ride;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public array $approvedDrivers = [];
    public int $completedRides = 0;

    public function mount(): void
    {
        $this->approvedDrivers = Driver::where('status', 'approved')->where('is_available', true)->orderBy('vehicle_name')->get()->toArray();
        $this->completedRides = Ride::where('user_id', Auth::id())->where('status', 'completed')->count();
    }
}; ?>

<div class="p-6 space-y-6">
    <h2 class="text-xl font-semibold">User Dashboard</h2>
    <div class="grid gap-2">
        <div class="border rounded p-4">
            <div class="text-sm">Completed Rides</div>
            <div class="text-2xl font-bold">{{ $completedRides }}</div>
        </div>
        <div class="border rounded p-4">
            <div class="text-sm font-medium">Available Drivers</div>
            <ul class="list-disc ms-5">
                @forelse($approvedDrivers as $d)
                    <li>{{ $d['vehicle_name'] }} — ₦{{ number_format($d['charge_rate'], 2) }}</li>
                @empty
                    <li>No drivers available</li>
                @endforelse
            </ul>
        </div>
    </div>
    <div>
        <flux:link :href="route('user.rides.book')" variant="primary">Book a Ride</flux:link>
    </div>
</div>