<?php

use App\Models\Ride;
use App\Models\Payment;
use App\Models\Driver;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public array $rides = [];
    public bool $is_available = true;
    public array $fareOffer = [];
    public bool $showCompleteModal = false;
    public ?int $completeRideId = null;

    public function mount(): void
    {
        // Ensure a driver profile exists for the authenticated user
        if (Auth::check()) {
            $driver = Auth::user()->driver;
            if (! $driver) {
                $driver = Driver::firstOrCreate(
                    ['user_id' => Auth::id()],
                    [
                        'vehicle_name' => 'TBD',
                        'plate_number' => 'TBD',
                        'charge_rate' => 0,
                        'status' => 'pending',
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
        if ((float) $ride->fare <= 0) {
            return;
        }
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

    public function promptComplete(int $id): void
    {
        $ride = Ride::findOrFail($id);
        if ($ride->status !== 'in_progress') {
            return;
        }
        $this->completeRideId = $id;
        $this->showCompleteModal = true;
    }

    public function completeWithCash(): void
    {
        if (! $this->completeRideId) {
            return;
        }

        $ride = Ride::findOrFail($this->completeRideId);
        if ($ride->status !== 'in_progress') {
            return;
        }

        $payment = Payment::firstOrCreate(
            ['ride_id' => $ride->id],
            ['amount' => $ride->fare, 'payment_method' => 'cash', 'status' => 'pending']
        );

        $payment->update(['status' => 'success', 'paid_at' => now(), 'payment_method' => 'cash']);
        $ride->update(['payment_status' => 'paid', 'status' => 'completed', 'payment_method' => 'cash']);

        $this->showCompleteModal = false;
        $this->completeRideId = null;
        $this->refreshRides();
    }

    public function closeModal(): void
    {
        $this->showCompleteModal = false;
        $this->completeRideId = null;
    }

    public function confirmCash(int $id): void
    {
        $ride = Ride::findOrFail($id);
        if ($ride->status !== 'accepted' || (float) $ride->fare <= 0) {
            return;
        }
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
                    'vehicle_name' => 'TBD',
                    'plate_number' => 'TBD',
                    'charge_rate' => 0,
                    'status' => 'pending',
                    'is_available' => true,
                ],
            );
        }

        $driver->is_available = ! (bool) $driver->is_available;
        $driver->save();
        $this->is_available = (bool) $driver->is_available;
    }

    public function proposeAndAccept(int $id): void
    {
        $ride = Ride::findOrFail($id);
        $amount = (float) ($this->fareOffer[$id] ?? 0);

        if ($amount <= 0) {
            return;
        }

        $ride->fare = $amount;
        $ride->status = 'accepted';
        $ride->save();
        $this->refreshRides();
    }
}; ?>

<div class="p-6 space-y-6">
    <h2 class="tw-heading">My Rides</h2>

    <div class="flex items-center gap-3">
        <div class="text-sm">Availability: <span class="font-semibold">{{ $is_available ? 'Available' : 'Unavailable' }}</span></div>
        <button
            type="button"
            wire:click="toggleAvailability"
            wire:loading.attr="disabled"
            wire:target="toggleAvailability"
            aria-pressed="{{ $is_available ? 'true' : 'false' }}"
            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none {{ $is_available ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600' }}"
        >
            <span
                class="inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform duration-200 {{ $is_available ? 'translate-x-5' : 'translate-x-1' }}"
                wire:loading.remove
                wire:target="toggleAvailability"
            ></span>

            <span
                class="absolute inset-0 flex items-center justify-center"
                wire:loading
                wire:target="toggleAvailability"
            >
                <flux:icon.loading variant="mini" />
            </span>
        </button>
    </div>

    <div class="grid gap-3">
        @forelse ($rides as $r)
            <div class="card grid gap-2">
                <div class="font-medium">{{ $r['pickup'] }} → {{ $r['destination'] }}</div>
                <div class="tw-body">Fare: ₦{{ number_format($r['fare'], 2) }} | Status: <span class="font-semibold">{{ $r['status'] }}</span></div>
                <div class="tw-body">Payment: {{ $r['payment_status'] }} ({{ $r['payment_method'] }})</div>
                @if($r['status'] === 'accepted' && $r['payment_status'] === 'pending')
                    <div class="text-xs text-gray-600">Passenger can now pay via cash or Paystack.</div>
                @endif

                <div class="flex gap-2">
                    @if($r['status'] === 'pending')
                        <flux:input type="number" step="0.01" min="0" wire:model="fareOffer.{{ $r['id'] }}" placeholder="Set fare (₦)" class="w-40" />
                        <flux:button wire:click="proposeAndAccept({{ $r['id'] }})" variant="primary" class="btn-primary">Set Fare & Accept</flux:button>
                        <flux:button wire:click="accept({{ $r['id'] }})" variant="primary" class="btn-primary">Accept</flux:button>
                        <flux:button wire:click="reject({{ $r['id'] }})" variant="danger" class="btn-outline-primary">Reject</flux:button>
                    @elseif($r['status'] === 'accepted')
                        <flux:button wire:click="start({{ $r['id'] }})" variant="primary" class="btn-primary">Start</flux:button>
                    @elseif($r['status'] === 'in_progress')
                        <flux:button wire:click="promptComplete({{ $r['id'] }})" variant="primary" class="btn-primary">Complete</flux:button>
                    @endif

                    @if($r['status'] === 'accepted' && $r['payment_method'] === 'cash' && $r['payment_status'] !== 'paid')
                        <flux:button wire:click="confirmCash({{ $r['id'] }})" variant="ghost" class="btn-accent">Confirm Cash</flux:button>
                    @endif
                </div>
            </div>
        @empty
            <div>No rides yet.</div>
        @endforelse
    </div>
    
    <flux:modal
        name="complete-ride-modal"
        class="max-w-md md:min-w-md"
        @close="closeModal"
        wire:model="showCompleteModal"
    >
        <div class="space-y-6">
            <div class="space-y-2 text-center">
                <flux:heading size="lg">Confirm Cash Completion</flux:heading>
                <flux:text>
                    Completing this ride means the passenger has paid in cash and you received the payment.
                </flux:text>
            </div>

            <div class="flex items-center space-x-3">
                <flux:button
                    variant="outline"
                    class="flex-1"
                    wire:click="closeModal"
                >
                    Cancel
                </flux:button>

                <flux:button
                    variant="primary"
                    wire:click="completeWithCash"
                >
                    Confirm and Complete
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>