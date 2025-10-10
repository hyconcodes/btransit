<?php

use App\Models\Ride;
use App\Models\Payment;
use App\Models\Driver;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public array $rides = [];
    public bool $is_available = true;

    public function mount(): void
    {
        // Ensure a driver profile exists for the authenticated user
        if (Auth::check()) {
            $driver = Auth::user()->driver;
            if (! $driver) {
                $driver = Driver::firstOrCreate(
                    ['user_id' => Auth::id()],
                    [
                        'status' => 'pending_approval',
                        'is_available' => true,
                    ],
                );
            }
            $this->is_available = (bool) $driver->is_available;
        } else {
            $this->is_available = true;
        }
        $this->refreshRides();
    }

    public function refreshRides(): void
    {
        $driverId = optional(Auth::user()->driver)->id;
        $this->rides = Ride::where('driver_id', $driverId)
            ->orderByDesc('created_at')
            ->get()
            ->toArray();
    }

    public function accept(int $id): void
    {
        $ride = Ride::findOrFail($id);
        $ride->status = 'accepted';
        $ride->save();
        $this->refreshRides();
    }

    public function reject(int $id): void
    {
        $ride = Ride::findOrFail($id);
        $ride->status = 'cancelled';
        $ride->save();
        $this->refreshRides();
    }

    public function start(int $id): void
    {
        $ride = Ride::findOrFail($id);
        $ride->status = 'in_progress';
        $ride->save();
        $this->refreshRides();
    }

    public function complete(int $id): void
    {
        $ride = Ride::findOrFail($id);
        $ride->status = 'completed';
        $ride->save();
        $this->refreshRides();
    }

    public function confirmCash(int $id): void
    {
        $ride = Ride::findOrFail($id);
        $payment = Payment::firstOrCreate(
            ['ride_id' => $ride->id],
            ['amount' => $ride->fare, 'payment_method' => 'cash', 'status' => 'pending']
        );

        $payment->update(['status' => 'success', 'paid_at' => now()]);
        $ride->update(['payment_status' => 'paid']);
        $this->refreshRides();
    }

    public function toggleAvailability(): void
    {
        if (! Auth::check()) {
            return;
        }

        $driver = Auth::user()->driver;
        if (! $driver) {
            $driver = Driver::firstOrCreate(
                ['user_id' => Auth::id()],
                [
                    'status' => 'pending_approval',
                    'is_available' => true,
                ],
            );
        }

        $driver->is_available = ! (bool) $driver->is_available;
        $driver->save();
        $this->is_available = (bool) $driver->is_available;
    }
}; ?>

<div class="p-6 space-y-6">
    <h2 class="text-xl font-semibold">My Rides</h2>

    <div class="flex items-center gap-3">
        <div class="text-sm">Availability: <span class="font-semibold">{{ $is_available ? 'Available' : 'Unavailable' }}</span></div>
        <flux:button wire:click="toggleAvailability" variant="primary">Toggle Availability</flux:button>
    </div>

    <div class="grid gap-3">
        @forelse ($rides as $r)
            <div class="border rounded p-3 grid gap-2">
                <div class="font-medium">{{ $r['pickup'] }} → {{ $r['destination'] }}</div>
                <div class="text-sm">Fare: ₦{{ number_format($r['fare'], 2) }} | Status: <span class="font-semibold">{{ $r['status'] }}</span></div>
                <div class="text-sm">Payment: {{ $r['payment_status'] }} ({{ $r['payment_method'] }})</div>

                <div class="flex gap-2">
                    @if($r['status'] === 'pending')
                        <flux:button wire:click="accept({{ $r['id'] }})" variant="primary">Accept</flux:button>
                        <flux:button wire:click="reject({{ $r['id'] }})" variant="danger">Reject</flux:button>
                    @elseif($r['status'] === 'accepted')
                        <flux:button wire:click="start({{ $r['id'] }})" variant="primary">Start</flux:button>
                    @elseif($r['status'] === 'in_progress')
                        <flux:button wire:click="complete({{ $r['id'] }})" variant="success">Complete</flux:button>
                    @endif

                    @if($r['payment_method'] === 'cash' && $r['payment_status'] !== 'paid')
                        <flux:button wire:click="confirmCash({{ $r['id'] }})" variant="secondary">Confirm Cash</flux:button>
                    @endif
                </div>
            </div>
        @empty
            <div>No rides yet.</div>
        @endforelse
    </div>
</div>