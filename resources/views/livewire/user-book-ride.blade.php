<?php

use App\Models\Driver;
use App\Models\Ride;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public array $drivers = [];
    public ?int $driver_id = null;
    public string $pickup = '';
    public string $destination = '';
    public string $payment_method = 'cash';

    public function mount(): void
    {
        $this->drivers = Driver::where('status', 'approved')->orderBy('charge_rate')->get()->toArray();
    }

    public function bookRide(): void
    {
        $this->validate([
            'driver_id' => ['required', 'integer'],
            'pickup' => ['required', 'string', 'min:3'],
            'destination' => ['required', 'string', 'min:3'],
            'payment_method' => ['required', 'in:paystack,cash'],
        ]);

        $driver = Driver::findOrFail($this->driver_id);
        $fare = (float) $driver->charge_rate;

        $ride = Ride::create([
            'user_id' => Auth::id(),
            'driver_id' => $driver->id,
            'pickup' => $this->pickup,
            'destination' => $this->destination,
            'fare' => $fare,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_method === 'cash' ? 'pending' : 'pending',
            'status' => 'pending',
        ]);

        if ($this->payment_method === 'cash') {
            Payment::create([
                'ride_id' => $ride->id,
                'amount' => $fare,
                'payment_method' => 'cash',
                'status' => 'pending',
            ]);

            $this->dispatch('ride-booked', id: $ride->id);
            $this->redirect(route('user.dashboard'), navigate: true);
        } else {
            $this->redirect(route('payment.initialize'), navigate: true, parameters: ['ride_id' => $ride->id]);
        }
    }
}; ?>

<div class="p-6 space-y-6">
    <h2 class="text-xl font-semibold">Book a Ride</h2>

    <form wire:submit="bookRide" class="grid gap-4 max-w-xl">
        <label class="grid gap-1">
            <span class="text-sm">Driver</span>
            <select wire:model="driver_id" class="border rounded p-2">
                <option value="">Select driver</option>
                @foreach($drivers as $d)
                    <option value="{{ $d['id'] }}">{{ $d['vehicle_name'] }} (₦{{ number_format($d['charge_rate'], 2) }})</option>
                @endforeach
            </select>
        </label>

        <flux:input wire:model="pickup" label="Pickup" />
        <flux:input wire:model="destination" label="Destination" />

        <label class="grid gap-1">
            <span class="text-sm">Payment Method</span>
            <select wire:model="payment_method" class="border rounded p-2">
                <option value="cash">Cash</option>
                <option value="paystack">Paystack</option>
            </select>
        </label>

        <flux:button type="submit" variant="primary">Book Ride</flux:button>
    </form>
</div>