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
        $driver = Auth::user()->load('roles');
        // Basic counts for the current driver's rides
        $this->assigned = Ride::where('driver_id', optional($driver->driver)->id)->where('status', 'accepted')->count();
        $this->inProgress = Ride::where('driver_id', optional($driver->driver)->id)->where('status', 'in_progress')->count();
        $this->completed = Ride::where('driver_id', optional($driver->driver)->id)->where('status', 'completed')->count();
    }
}; ?>

<div class="p-6 grid gap-6 md:grid-cols-3">
    <flux:card>
        <div class="text-sm">Assigned</div>
        <div class="text-2xl font-bold">{{ $assigned }}</div>
    </flux:card>
    <flux:card>
        <div class="text-sm">In Progress</div>
        <div class="text-2xl font-bold">{{ $inProgress }}</div>
    </flux:card>
    <flux:card>
        <div class="text-sm">Completed</div>
        <div class="text-2xl font-bold">{{ $completed }}</div>
    </flux:card>
</div>