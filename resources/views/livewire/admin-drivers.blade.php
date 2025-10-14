<?php

use App\Models\Driver;
use App\Models\Ride;
use App\Models\Payment;
use Illuminate\Support\Carbon;
use Livewire\Volt\Component;

new class extends Component {
    public array $drivers = [];
    public bool $showDriverRidesModal = false;
    public ?int $selectedDriverId = null;
    public array $driverRides = [];
    public array $paymentsByRide = [];

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
    
    public function openDriverRides(int $id): void
    {
        $driver = Driver::findOrFail($id);
        $this->selectedDriverId = $id;
        $this->driverRides = Ride::where('driver_id', $id)->orderByDesc('created_at')->get()->toArray();
        $this->paymentsByRide = [];
        foreach ($this->driverRides as $ride) {
            $this->paymentsByRide[$ride['id']] = Payment::where('ride_id', $ride['id'])->orderByDesc('created_at')->get()->toArray();
        }
        $this->showDriverRidesModal = true;
    }

    public function closeDriverRides(): void
    {
        $this->showDriverRidesModal = false;
        $this->selectedDriverId = null;
        $this->driverRides = [];
        $this->paymentsByRide = [];
    }
}; ?>

<div class="p-6 space-y-6">
    <h2 class="tw-heading">Driver Management</h2>

    <div class="grid gap-3">
        @forelse ($drivers as $d)
            <div class="card flex items-center justify-between">
                <div>
                    <div class="font-medium">{{ $d['user']['name'] ?? 'Unknown' }}</div>
                    <div class="tw-body">Vehicle: {{ $d['vehicle_name'] }} | Plate: {{ $d['plate_number'] }}</div>
                    <div class="tw-body">Charge Rate: ₦{{ number_format($d['charge_rate'], 2) }}</div>
                    <div class="tw-body">Status: <span class="font-semibold">{{ $d['status'] }}</span></div>
                    <div class="tw-body">Available: <span class="font-semibold">{{ ($d['is_available'] ?? false) ? 'Yes' : 'No' }}</span></div>
                </div>
                <flux:button wire:click="toggleApproval({{ $d['id'] }})" variant="primary" class="btn-primary">
                    {{ $d['status'] === 'approved' ? 'Set Pending' : 'Approve' }}
                </flux:button>
                <flux:button wire:click="toggleAvailability({{ $d['id'] }})" variant="ghost" class="btn-outline-primary">
                    {{ ($d['is_available'] ?? false) ? 'Set Unavailable' : 'Set Available' }}
                </flux:button>
                <flux:button wire:click="openDriverRides({{ $d['id'] }})" variant="outline" class="btn-outline-primary">
                    View Rides
                </flux:button>
            </div>
        @empty
            <div class="tw-body">No drivers yet.</div>
        @endforelse
    </div>
</div>

<flux:modal
    name="driver-rides-modal"
    variant="dialog"
    class="max-w-2xl"
    wire:model="showDriverRidesModal"
    @close="$set('showDriverRidesModal', false)"
>
    <div class="grid gap-4">
        <div class="tw-heading">Driver Rides</div>
        <div class="grid gap-3">
            @forelse($driverRides as $r)
                <div class="card">
                    <div class="font-medium">{{ $r['pickup'] }} → {{ $r['destination'] }}</div>
                    <div class="tw-body">Fare: ₦{{ number_format($r['fare'], 2) }} · Status: {{ $r['status'] }}</div>
                    <div class="tw-body">Payment: {{ $r['payment_status'] }} ({{ $r['payment_method'] }})</div>
                    <div class="tw-heading mt-2">Payment History</div>
                    <div class="grid gap-2">
                        @forelse(($paymentsByRide[$r['id']] ?? []) as $p)
                            <div class="card">
                                <div class="tw-body">Amount: ₦{{ number_format($p['amount'], 2) }}</div>
                                <div class="tw-body">Method: {{ $p['payment_method'] }}</div>
                                <div class="tw-body">Status: {{ $p['status'] }}</div>
                                <div class="tw-body">Date: {{ Carbon::parse($p['created_at'])->format('M j, Y g:ia') }}</div>
                            </div>
                        @empty
                            <div class="tw-body">No payments yet.</div>
                        @endforelse
                    </div>
                </div>
            @empty
                <div class="tw-body">No rides for this driver.</div>
            @endforelse
        </div>
        <div class="flex items-center gap-2">
            <flux:button variant="outline" wire:click="closeDriverRides">Close</flux:button>
        </div>
    </div>
</flux:modal>