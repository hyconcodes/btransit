<?php

use App\Models\Ride;
use App\Models\Payment;
use App\Models\Driver;
use Livewire\Volt\Component;

new class extends Component {
    public int $totalRides = 0;
    public int $completedRides = 0;
    public float $totalRevenue = 0;
    public int $approvedDrivers = 0;

    public function mount(): void
    {
        $this->totalRides = Ride::count();
        $this->completedRides = Ride::where('status', 'completed')->count();
        $this->totalRevenue = (float) Payment::where('status', 'success')->sum('amount');
        $this->approvedDrivers = Driver::where('status', 'approved')->count();
    }
}; ?>

<div class="p-6 grid gap-6 md:grid-cols-4">
    <div class="bg-white shadow rounded p-4">
        <div class="text-sm text-gray-600">Total Rides</div>
        <div class="text-2xl font-bold text-gray-900">{{ $totalRides }}</div>
    </div>
    <div class="bg-white shadow rounded p-4">
        <div class="text-sm text-gray-600">Completed Rides</div>
        <div class="text-2xl font-bold text-gray-900">{{ $completedRides }}</div>
    </div>
    <div class="bg-white shadow rounded p-4">
        <div class="text-sm text-gray-600">Revenue (₦)</div>
        <div class="text-2xl font-bold text-gray-900">{{ number_format($totalRevenue, 2) }}</div>
    </div>
    <div class="bg-white shadow rounded p-4">
        <div class="text-sm text-gray-600">Approved Drivers</div>
        <div class="text-2xl font-bold text-gray-900">{{ $approvedDrivers }}</div>
    </div>
</div>