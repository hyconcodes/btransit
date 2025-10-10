<?php

use App\Models\Ride;
use App\Models\Payment;
use App\Models\Driver;
use Livewire\Volt\Component;

new class extends Component {
    public int $totalRides = 0;
    public int $completedRides = 0;
    public float $totalRevenue = 0.0;
    public int $approvedDrivers = 0;

    public function mount(): void
    {
        $this->totalRides = Ride::count();
        $this->completedRides = Ride::where('status', 'completed')->count();
        $this->totalRevenue = (float) Payment::where('status', 'success')->sum('amount');
        $this->approvedDrivers = Driver::where('status', 'approved')->count();
    }
}; ?>

<div class="p-6 space-y-6">
    <h2 class="text-xl font-semibold">Superadmin Dashboard</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="border rounded p-4">
            <div class="text-sm">Total Rides</div>
            <div class="text-2xl font-bold">{{ $totalRides }}</div>
        </div>
        <div class="border rounded p-4">
            <div class="text-sm">Completed Rides</div>
            <div class="text-2xl font-bold">{{ $completedRides }}</div>
        </div>
        <div class="border rounded p-4">
            <div class="text-sm">Total Revenue</div>
            <div class="text-2xl font-bold">₦{{ number_format($totalRevenue, 2) }}</div>
        </div>
        <div class="border rounded p-4">
            <div class="text-sm">Approved Drivers</div>
            <div class="text-2xl font-bold">{{ $approvedDrivers }}</div>
        </div>
    </div>
    <div>
        <flux:link :href="route('admin.drivers')" variant="primary">Manage Drivers</flux:link>
    </div>
</div>