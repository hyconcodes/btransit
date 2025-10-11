<?php

use App\Models\Ride;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public int $assigned = 0;
    public int $inProgress = 0;
    public int $completed = 0;

    public function mount(): void
    {
        $driverId = optional(Auth::user()->driver)->id;
        $this->assigned = Ride::where('driver_id', $driverId)->whereIn('status', ['pending', 'accepted'])->count();
        $this->inProgress = Ride::where('driver_id', $driverId)->where('status', 'in_progress')->count();
        $this->completed = Ride::where('driver_id', $driverId)->where('status', 'completed')->count();
    }
}; ?>

<div class="p-6 space-y-6">
    <h2 class="tw-heading">Driver Dashboard</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="card">
            <div class="tw-body">Assigned</div>
            <div class="text-2xl font-bold">{{ $assigned }}</div>
        </div>
        <div class="card">
            <div class="tw-body">In Progress</div>
            <div class="text-2xl font-bold">{{ $inProgress }}</div>
        </div>
        <div class="card">
            <div class="tw-body">Completed</div>
            <div class="text-2xl font-bold">{{ $completed }}</div>
        </div>
    </div>
    <div>
        <flux:link :href="route('driver.rides')" variant="primary" class="btn-primary">Manage Rides</flux:link>
    </div>
</div>